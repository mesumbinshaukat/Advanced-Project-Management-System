<?php

namespace App\Services;

use App\Models\TaskModel;
use App\Models\TimeEntryModel;
use App\Models\DailyCheckInModel;
use App\Models\ActivityLogModel;

class PerformanceService
{
    protected $taskModel;
    protected $timeEntryModel;
    protected $checkInModel;
    protected $activityModel;

    public function __construct()
    {
        $this->taskModel = new TaskModel();
        $this->timeEntryModel = new TimeEntryModel();
        $this->checkInModel = new DailyCheckInModel();
        $this->activityModel = new ActivityLogModel();
    }

    public function calculatePerformanceScore($userId, $days = 30)
    {
        $deadlineScore = $this->calculateDeadlineScore($userId, $days);
        $speedScore = $this->calculateSpeedScore($userId, $days);
        $engagementScore = $this->calculateEngagementScore($userId, $days);
        
        $overallScore = ($deadlineScore * 0.4) + ($speedScore * 0.3) + ($engagementScore * 0.3);
        
        $db = \Config\Database::connect();
        $db->table('users')->where('id', $userId)->update([
            'performance_score' => round($overallScore, 2),
            'deadline_score' => round($deadlineScore, 2),
            'speed_score' => round($speedScore, 2),
            'engagement_score' => round($engagementScore, 2),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return [
            'overall' => round($overallScore, 2),
            'deadline' => round($deadlineScore, 2),
            'speed' => round($speedScore, 2),
            'engagement' => round($engagementScore, 2),
        ];
    }

    protected function calculateDeadlineScore($userId, $days)
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        $completedTasks = $this->taskModel
            ->where('assigned_to', $userId)
            ->where('status', 'done')
            ->where('completed_at >=', $startDate)
            ->findAll();

        if (empty($completedTasks)) {
            return 50;
        }

        $onTimeCount = 0;
        $totalCount = count($completedTasks);

        foreach ($completedTasks as $task) {
            if ($task['deadline']) {
                $completedDate = strtotime($task['completed_at']);
                $deadlineDate = strtotime($task['deadline'] . ' 23:59:59');
                
                if ($completedDate <= $deadlineDate) {
                    $onTimeCount++;
                }
            } else {
                $onTimeCount++;
            }
        }

        $onTimeRate = ($onTimeCount / $totalCount) * 100;
        
        if ($onTimeRate >= 95) return 100;
        if ($onTimeRate >= 85) return 90;
        if ($onTimeRate >= 75) return 80;
        if ($onTimeRate >= 65) return 70;
        if ($onTimeRate >= 50) return 60;
        return 40;
    }

    protected function calculateSpeedScore($userId, $days)
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        $tasks = $this->taskModel
            ->where('assigned_to', $userId)
            ->where('status', 'done')
            ->where('completed_at >=', $startDate)
            ->where('estimated_hours >', 0)
            ->where('actual_hours >', 0)
            ->findAll();

        if (empty($tasks)) {
            return 50;
        }

        $efficiencyRatios = [];
        foreach ($tasks as $task) {
            $ratio = $task['estimated_hours'] / $task['actual_hours'];
            $efficiencyRatios[] = $ratio;
        }

        $avgEfficiency = array_sum($efficiencyRatios) / count($efficiencyRatios);
        
        if ($avgEfficiency >= 1.2) return 100;
        if ($avgEfficiency >= 1.0) return 90;
        if ($avgEfficiency >= 0.9) return 80;
        if ($avgEfficiency >= 0.8) return 70;
        if ($avgEfficiency >= 0.7) return 60;
        return 50;
    }

    protected function calculateEngagementScore($userId, $days)
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        $checkIns = $this->checkInModel
            ->where('user_id', $userId)
            ->where('date >=', $startDate)
            ->countAllResults();

        $activities = $this->activityModel
            ->where('user_id', $userId)
            ->where('created_at >=', $startDate)
            ->countAllResults();

        $timeEntries = $this->timeEntryModel
            ->where('user_id', $userId)
            ->where('date >=', $startDate)
            ->countAllResults();

        $checkInRate = ($checkIns / $days) * 100;
        $activityRate = min(100, ($activities / $days) * 10);
        $timeEntryRate = min(100, ($timeEntries / $days) * 20);

        $engagementScore = ($checkInRate * 0.4) + ($activityRate * 0.3) + ($timeEntryRate * 0.3);

        return min(100, $engagementScore);
    }

    public function getPerformanceTrend($userId, $months = 3)
    {
        $trend = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $startDate = date('Y-m-01', strtotime("-{$i} months"));
            $endDate = date('Y-m-t', strtotime("-{$i} months"));
            $daysInMonth = date('t', strtotime($startDate));
            
            $completedTasks = $this->taskModel
                ->where('assigned_to', $userId)
                ->where('status', 'done')
                ->where('completed_at >=', $startDate)
                ->where('completed_at <=', $endDate . ' 23:59:59')
                ->countAllResults();

            $trend[] = [
                'month' => date('M Y', strtotime($startDate)),
                'tasks_completed' => $completedTasks,
            ];
        }

        return $trend;
    }

    public function getAllDevelopersPerformance()
    {
        $db = \Config\Database::connect();
        
        $developers = $db->table('users')
            ->select('users.id, users.username, users.performance_score, users.deadline_score, users.speed_score, users.engagement_score, users.last_check_in')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
            ->where('auth_groups_users.group', 'developer')
            ->orderBy('users.performance_score', 'DESC')
            ->get()
            ->getResultArray();

        foreach ($developers as &$dev) {
            $dev['tasks_completed_30d'] = $this->taskModel
                ->where('assigned_to', $dev['id'])
                ->where('status', 'done')
                ->where('completed_at >=', date('Y-m-d', strtotime('-30 days')))
                ->countAllResults();
        }

        return $developers;
    }

    public function updateAllPerformanceScores()
    {
        $db = \Config\Database::connect();
        
        $developers = $db->table('users')
            ->select('users.id')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
            ->where('auth_groups_users.group', 'developer')
            ->get()
            ->getResultArray();

        foreach ($developers as $dev) {
            $this->calculatePerformanceScore($dev['id']);
        }

        return count($developers);
    }
}
