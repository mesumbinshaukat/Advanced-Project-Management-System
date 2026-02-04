<?php

namespace App\Services;

use App\Models\TaskModel;
use App\Models\TimeEntryModel;
use App\Models\ProjectModel;

class CapacityService
{
    protected $taskModel;
    protected $timeEntryModel;
    protected $projectModel;

    public function __construct()
    {
        $this->taskModel = new TaskModel();
        $this->timeEntryModel = new TimeEntryModel();
        $this->projectModel = new ProjectModel();
    }

    public function getCapacityForecast()
    {
        $currentCapacity = $this->getCurrentCapacity();
        $demand = $this->getDemand();
        $utilization = $this->getUtilization();
        
        $capacityGap = $demand['total_hours_needed'] - $currentCapacity['available_hours'];
        $hiringRecommendation = $this->getHiringRecommendation($capacityGap, $utilization);

        return [
            'current_capacity' => $currentCapacity,
            'demand' => $demand,
            'utilization' => $utilization,
            'capacity_gap' => $capacityGap,
            'hiring_recommendation' => $hiringRecommendation,
        ];
    }

    protected function getCurrentCapacity()
    {
        $db = \Config\Database::connect();
        
        $developerCount = $db->table('users')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
            ->where('auth_groups_users.group', 'developer')
            ->where('users.active', 1)
            ->countAllResults();

        $hoursPerDeveloperPerWeek = 40;
        $availableHours = $developerCount * $hoursPerDeveloperPerWeek;

        return [
            'developer_count' => $developerCount,
            'hours_per_week' => $hoursPerDeveloperPerWeek,
            'available_hours' => $availableHours,
        ];
    }

    protected function getDemand()
    {
        $activeTasks = $this->taskModel
            ->whereIn('status', ['backlog', 'todo', 'in_progress', 'review'])
            ->where('deleted_at', null)
            ->findAll();

        $totalEstimatedHours = 0;
        $tasksWithoutEstimate = 0;

        foreach ($activeTasks as $task) {
            if ($task['estimated_hours'] > 0) {
                $totalEstimatedHours += $task['estimated_hours'];
            } else {
                $tasksWithoutEstimate++;
            }
        }

        $avgHoursPerTask = 8;
        $totalEstimatedHours += ($tasksWithoutEstimate * $avgHoursPerTask);

        return [
            'active_tasks' => count($activeTasks),
            'tasks_with_estimate' => count($activeTasks) - $tasksWithoutEstimate,
            'tasks_without_estimate' => $tasksWithoutEstimate,
            'total_hours_needed' => $totalEstimatedHours,
        ];
    }

    protected function getUtilization()
    {
        $startOfWeek = date('Y-m-d', strtotime('monday this week'));
        $endOfWeek = date('Y-m-d', strtotime('sunday this week'));

        $weeklyHours = $this->timeEntryModel
            ->selectSum('hours')
            ->where('date >=', $startOfWeek)
            ->where('date <=', $endOfWeek)
            ->first();

        $hoursLogged = $weeklyHours['hours'] ?? 0;

        $capacity = $this->getCurrentCapacity();
        $utilizationRate = $capacity['available_hours'] > 0 
            ? ($hoursLogged / $capacity['available_hours']) * 100 
            : 0;

        return [
            'hours_logged_this_week' => $hoursLogged,
            'available_hours_this_week' => $capacity['available_hours'],
            'utilization_rate' => round($utilizationRate, 2),
        ];
    }

    protected function getHiringRecommendation($capacityGap, $utilization)
    {
        $weeksOfWork = 4;
        $hoursPerDeveloperPerWeek = 40;
        
        $developersNeeded = ceil($capacityGap / ($hoursPerDeveloperPerWeek * $weeksOfWork));

        $recommendation = [
            'should_hire' => false,
            'developers_needed' => 0,
            'reason' => '',
            'urgency' => 'low',
        ];

        if ($utilization['utilization_rate'] > 90 && $capacityGap > 160) {
            $recommendation['should_hire'] = true;
            $recommendation['developers_needed'] = max(1, $developersNeeded);
            $recommendation['reason'] = 'High utilization (' . round($utilization['utilization_rate'], 1) . '%) and significant capacity gap';
            $recommendation['urgency'] = 'high';
        } elseif ($utilization['utilization_rate'] > 80 && $capacityGap > 80) {
            $recommendation['should_hire'] = true;
            $recommendation['developers_needed'] = max(1, $developersNeeded);
            $recommendation['reason'] = 'Good utilization (' . round($utilization['utilization_rate'], 1) . '%) with growing demand';
            $recommendation['urgency'] = 'medium';
        } elseif ($capacityGap > 320) {
            $recommendation['should_hire'] = true;
            $recommendation['developers_needed'] = max(1, $developersNeeded);
            $recommendation['reason'] = 'Large backlog requires additional capacity';
            $recommendation['urgency'] = 'medium';
        } else {
            $recommendation['reason'] = 'Current capacity is sufficient';
        }

        return $recommendation;
    }

    public function getProjectCapacityAllocation()
    {
        $projects = $this->projectModel
            ->where('status', 'active')
            ->where('deleted_at', null)
            ->findAll();

        $allocations = [];

        foreach ($projects as $project) {
            $tasks = $this->taskModel
                ->where('project_id', $project['id'])
                ->whereIn('status', ['backlog', 'todo', 'in_progress', 'review'])
                ->where('deleted_at', null)
                ->findAll();

            $totalHoursNeeded = 0;
            foreach ($tasks as $task) {
                $totalHoursNeeded += $task['estimated_hours'] ?? 8;
            }

            $allocations[] = [
                'project_id' => $project['id'],
                'project_name' => $project['name'],
                'active_tasks' => count($tasks),
                'hours_needed' => $totalHoursNeeded,
                'weeks_of_work' => ceil($totalHoursNeeded / 40),
            ];
        }

        usort($allocations, function($a, $b) {
            return $b['hours_needed'] <=> $a['hours_needed'];
        });

        return $allocations;
    }
}
