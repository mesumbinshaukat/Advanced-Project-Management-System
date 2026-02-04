<?php

namespace App\Services;

use App\Models\ProjectModel;
use App\Models\FinancialModel;
use App\Models\TimeEntryModel;

class ProfitabilityService
{
    protected $projectModel;
    protected $financialModel;
    protected $timeEntryModel;

    public function __construct()
    {
        $this->projectModel = new ProjectModel();
        $this->financialModel = new FinancialModel();
        $this->timeEntryModel = new TimeEntryModel();
    }

    public function getOverallProfitability()
    {
        $projects = $this->projectModel->where('deleted_at', null)->findAll();
        
        $totalRevenue = 0;
        $totalCost = 0;
        $projectsData = [];

        foreach ($projects as $project) {
            $profitability = $this->getProjectProfitability($project['id']);
            
            $totalRevenue += $profitability['revenue'];
            $totalCost += $profitability['cost'];
            
            $projectsData[] = array_merge($project, $profitability);
        }

        $totalProfit = $totalRevenue - $totalCost;
        $profitMargin = $totalRevenue > 0 ? (($totalProfit / $totalRevenue) * 100) : 0;

        return [
            'total_revenue' => $totalRevenue,
            'total_cost' => $totalCost,
            'total_profit' => $totalProfit,
            'profit_margin' => round($profitMargin, 2),
            'projects' => $projectsData,
        ];
    }

    public function getProjectProfitability($projectId)
    {
        $financial = $this->financialModel->where('project_id', $projectId)->first();
        
        $totalHours = $this->timeEntryModel
            ->selectSum('hours')
            ->join('tasks', 'tasks.id = time_entries.task_id')
            ->where('tasks.project_id', $projectId)
            ->first();

        $hoursLogged = $totalHours['hours'] ?? 0;

        $billableHours = $this->timeEntryModel
            ->selectSum('hours')
            ->join('tasks', 'tasks.id = time_entries.task_id')
            ->where('tasks.project_id', $projectId)
            ->where('time_entries.is_billable', 1)
            ->first();

        $billableHoursTotal = $billableHours['hours'] ?? 0;

        if (!$financial) {
            return [
                'hours_logged' => $hoursLogged,
                'billable_hours' => $billableHoursTotal,
                'revenue' => 0,
                'cost' => 0,
                'profit' => 0,
                'profit_margin' => 0,
                'billing_type' => 'unknown',
            ];
        }

        $revenue = 0;
        if ($financial['billing_type'] === 'hourly') {
            $revenue = $billableHoursTotal * ($financial['hourly_rate'] ?? 0);
        } elseif ($financial['billing_type'] === 'fixed') {
            $revenue = $financial['fixed_price'] ?? 0;
        } elseif ($financial['billing_type'] === 'retainer') {
            $revenue = $financial['fixed_price'] ?? 0;
        }

        $avgDeveloperRate = 50;
        $cost = $hoursLogged * $avgDeveloperRate;

        $profit = $revenue - $cost;
        $profitMargin = $revenue > 0 ? (($profit / $revenue) * 100) : 0;

        $this->financialModel->update($financial['id'], [
            'total_cost' => $cost,
            'total_revenue' => $revenue,
            'profit_margin' => round($profitMargin, 2),
        ]);

        return [
            'hours_logged' => $hoursLogged,
            'billable_hours' => $billableHoursTotal,
            'revenue' => $revenue,
            'cost' => $cost,
            'profit' => $profit,
            'profit_margin' => round($profitMargin, 2),
            'billing_type' => $financial['billing_type'],
            'hourly_rate' => $financial['hourly_rate'] ?? 0,
            'fixed_price' => $financial['fixed_price'] ?? 0,
        ];
    }

    public function getProfitabilityTrend($months = 6)
    {
        $trend = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $startDate = date('Y-m-01', strtotime("-{$i} months"));
            $endDate = date('Y-m-t', strtotime("-{$i} months"));
            
            $monthlyHours = $this->timeEntryModel
                ->selectSum('hours')
                ->where('date >=', $startDate)
                ->where('date <=', $endDate)
                ->first();

            $hoursLogged = $monthlyHours['hours'] ?? 0;
            
            $billableHours = $this->timeEntryModel
                ->selectSum('hours')
                ->where('date >=', $startDate)
                ->where('date <=', $endDate)
                ->where('is_billable', 1)
                ->first();

            $billableHoursTotal = $billableHours['hours'] ?? 0;
            
            $estimatedRevenue = $billableHoursTotal * 75;
            $estimatedCost = $hoursLogged * 50;
            $estimatedProfit = $estimatedRevenue - $estimatedCost;

            $trend[] = [
                'month' => date('M Y', strtotime($startDate)),
                'hours_logged' => $hoursLogged,
                'billable_hours' => $billableHoursTotal,
                'revenue' => $estimatedRevenue,
                'cost' => $estimatedCost,
                'profit' => $estimatedProfit,
            ];
        }

        return $trend;
    }

    public function getTopProfitableProjects($limit = 10)
    {
        $projects = $this->projectModel->where('deleted_at', null)->findAll();
        
        $projectsWithProfit = [];
        foreach ($projects as $project) {
            $profitability = $this->getProjectProfitability($project['id']);
            $projectsWithProfit[] = array_merge($project, $profitability);
        }

        usort($projectsWithProfit, function($a, $b) {
            return $b['profit'] <=> $a['profit'];
        });

        return array_slice($projectsWithProfit, 0, $limit);
    }
}
