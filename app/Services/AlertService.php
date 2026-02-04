<?php

namespace App\Services;

use App\Models\AlertModel;
use App\Models\TaskModel;
use App\Models\ProjectModel;
use App\Models\TimeEntryModel;
use App\Models\DailyCheckInModel;

class AlertService
{
    protected $alertModel;
    protected $taskModel;
    protected $projectModel;
    protected $timeEntryModel;
    protected $checkInModel;

    public function __construct()
    {
        $this->alertModel = new AlertModel();
        $this->taskModel = new TaskModel();
        $this->projectModel = new ProjectModel();
        $this->timeEntryModel = new TimeEntryModel();
        $this->checkInModel = new DailyCheckInModel();
    }

    public function generateAllAlerts()
    {
        $this->checkDeadlineRisks();
        $this->checkInactivity();
        $this->checkOverload();
        $this->checkBudgetRisks();
        $this->checkBlockers();
    }

    protected function checkDeadlineRisks()
    {
        $tasks = $this->taskModel
            ->where('deadline <=', date('Y-m-d', strtotime('+3 days')))
            ->where('deadline >=', date('Y-m-d'))
            ->whereIn('status', ['todo', 'in_progress', 'review'])
            ->where('deleted_at', null)
            ->findAll();

        foreach ($tasks as $task) {
            $daysUntilDeadline = floor((strtotime($task['deadline']) - time()) / 86400);
            
            $severity = 'high';
            if ($daysUntilDeadline <= 1) {
                $severity = 'critical';
            } elseif ($daysUntilDeadline <= 2) {
                $severity = 'high';
            }

            $this->alertModel->createOrUpdateAlert([
                'type' => 'deadline_risk',
                'severity' => $severity,
                'entity_type' => 'task',
                'entity_id' => $task['id'],
                'user_id' => $task['assigned_to'],
                'title' => 'Deadline Approaching',
                'message' => "Task '{$task['title']}' is due in {$daysUntilDeadline} day(s)",
                'action_url' => "/tasks/view/{$task['id']}",
            ]);
        }
    }

    protected function checkInactivity()
    {
        $db = \Config\Database::connect();
        
        $developers = $db->table('users')
            ->select('users.id, users.username, users.last_activity')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
            ->where('auth_groups_users.group', 'developer')
            ->get()
            ->getResultArray();

        foreach ($developers as $dev) {
            if (!$dev['last_activity']) {
                continue;
            }

            $daysSinceActivity = floor((time() - strtotime($dev['last_activity'])) / 86400);

            if ($daysSinceActivity >= 3) {
                $severity = $daysSinceActivity >= 7 ? 'high' : 'medium';

                $this->alertModel->createOrUpdateAlert([
                    'type' => 'inactivity',
                    'severity' => $severity,
                    'entity_type' => 'user',
                    'entity_id' => $dev['id'],
                    'user_id' => $dev['id'],
                    'title' => 'Developer Inactivity',
                    'message' => "{$dev['username']} has been inactive for {$daysSinceActivity} days",
                    'action_url' => "/developers/workload/{$dev['id']}",
                ]);
            }
        }

        $checkIns = $this->checkInModel
            ->select('user_id, MAX(check_in_date) as last_check_in')
            ->groupBy('user_id')
            ->findAll();

        foreach ($checkIns as $checkIn) {
            $daysSinceCheckIn = floor((time() - strtotime($checkIn['last_check_in'])) / 86400);

            if ($daysSinceCheckIn >= 2) {
                $this->alertModel->createOrUpdateAlert([
                    'type' => 'inactivity',
                    'severity' => 'medium',
                    'entity_type' => 'user',
                    'entity_id' => $checkIn['user_id'],
                    'user_id' => $checkIn['user_id'],
                    'title' => 'Missing Check-In',
                    'message' => "No daily check-in for {$daysSinceCheckIn} days",
                    'action_url' => "/check-in",
                ]);
            }
        }
    }

    protected function checkOverload()
    {
        $db = \Config\Database::connect();
        
        $developers = $db->table('users')
            ->select('users.id, users.username')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
            ->where('auth_groups_users.group', 'developer')
            ->get()
            ->getResultArray();

        foreach ($developers as $dev) {
            $activeTasks = $this->taskModel
                ->where('assigned_to', $dev['id'])
                ->whereIn('status', ['todo', 'in_progress', 'review'])
                ->where('deleted_at', null)
                ->countAllResults();

            if ($activeTasks > 10) {
                $severity = $activeTasks > 15 ? 'high' : 'medium';

                $this->alertModel->createOrUpdateAlert([
                    'type' => 'overload',
                    'severity' => $severity,
                    'entity_type' => 'user',
                    'entity_id' => $dev['id'],
                    'user_id' => $dev['id'],
                    'title' => 'Developer Overload',
                    'message' => "{$dev['username']} has {$activeTasks} active tasks",
                    'action_url' => "/developers/workload/{$dev['id']}",
                ]);
            }
        }
    }

    protected function checkBudgetRisks()
    {
        $projects = $this->projectModel
            ->where('status', 'active')
            ->where('budget >', 0)
            ->where('deleted_at', null)
            ->findAll();

        foreach ($projects as $project) {
            $totalHours = $this->timeEntryModel
                ->selectSum('hours')
                ->join('tasks', 'tasks.id = time_entries.task_id')
                ->where('tasks.project_id', $project['id'])
                ->first();

            $hoursLogged = $totalHours['hours'] ?? 0;
            
            $financialModel = new \App\Models\FinancialModel();
            $financial = $financialModel->where('project_id', $project['id'])->first();

            if ($financial && $financial['hourly_rate'] > 0) {
                $estimatedCost = $hoursLogged * $financial['hourly_rate'];
                $budgetUsage = ($estimatedCost / $project['budget']) * 100;

                if ($budgetUsage >= 80) {
                    $severity = $budgetUsage >= 100 ? 'critical' : 'high';

                    $this->alertModel->createOrUpdateAlert([
                        'type' => 'budget_risk',
                        'severity' => $severity,
                        'entity_type' => 'project',
                        'entity_id' => $project['id'],
                        'user_id' => null,
                        'title' => 'Budget Risk',
                        'message' => "Project '{$project['name']}' has used " . round($budgetUsage, 1) . "% of budget",
                        'action_url' => "/projects/view/{$project['id']}",
                    ]);
                }
            }
        }
    }

    protected function checkBlockers()
    {
        $blockedTasks = $this->taskModel
            ->where('is_blocked', 1)
            ->where('deleted_at', null)
            ->findAll();

        foreach ($blockedTasks as $task) {
            $this->alertModel->createOrUpdateAlert([
                'type' => 'blocker',
                'severity' => 'critical',
                'entity_type' => 'task',
                'entity_id' => $task['id'],
                'user_id' => $task['assigned_to'],
                'title' => 'Task Blocked',
                'message' => "Task '{$task['title']}' is blocked: {$task['blocker_reason']}",
                'action_url' => "/tasks/view/{$task['id']}",
            ]);
        }
    }

    public function getActiveAlerts($limit = null, $userId = null)
    {
        $builder = $this->alertModel
            ->where('is_resolved', 0);
        
        if ($userId) {
            $builder->where('user_id', $userId);
        }
        
        $builder->orderBy('severity', 'DESC')
            ->orderBy('created_at', 'DESC');

        if ($limit) {
            return $builder->findAll($limit);
        }

        return $builder->findAll();
    }

    public function resolveAlert($alertId)
    {
        return $this->alertModel->resolveAlert($alertId);
    }
}
