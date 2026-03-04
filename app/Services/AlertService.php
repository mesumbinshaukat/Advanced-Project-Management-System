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
        try {
            $this->checkDeadlineRisks();
        } catch (\Exception $e) {
            log_message('error', 'Error checking deadline risks: ' . $e->getMessage());
        }

        try {
            $this->checkInactivity();
        } catch (\Exception $e) {
            log_message('error', 'Error checking inactivity: ' . $e->getMessage());
        }

        try {
            $this->checkOverload();
        } catch (\Exception $e) {
            log_message('error', 'Error checking overload: ' . $e->getMessage());
        }

        try {
            $this->checkBudgetRisks();
        } catch (\Exception $e) {
            log_message('error', 'Error checking budget risks: ' . $e->getMessage());
        }

        try {
            $this->checkBlockers();
        } catch (\Exception $e) {
            log_message('error', 'Error checking blockers: ' . $e->getMessage());
        }
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
        // Check for missing check-ins
        $checkIns = $this->checkInModel
            ->select('user_id, MAX(check_in_date) as last_check_in')
            ->groupBy('user_id')
            ->findAll();

        foreach ($checkIns as $checkIn) {
            if (!$checkIn['last_check_in']) {
                continue;
            }

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

    public function alertProjectAssignment($projectId, $userId, $assignerName)
    {
        $project = $this->projectModel->find($projectId);
        if (!$project) {
            return false;
        }

        return $this->alertModel->createOrUpdateAlert([
            'type' => 'project_assignment',
            'severity' => 'info',
            'entity_type' => 'project',
            'entity_id' => $projectId,
            'user_id' => $userId,
            'title' => 'Project Assignment',
            'message' => "{$assignerName} assigned you to project '{$project['name']}'",
            'action_url' => "/projects/view/{$projectId}",
        ]);
    }

    public function alertTaskAssignment($taskId, $userId, $assignerName)
    {
        $task = $this->taskModel->find($taskId);
        if (!$task) {
            return false;
        }

        $project = $this->projectModel->find($task['project_id']);
        $projectName = $project ? $project['name'] : 'Unknown Project';

        return $this->alertModel->createOrUpdateAlert([
            'type' => 'task_assignment',
            'severity' => 'info',
            'entity_type' => 'task',
            'entity_id' => $taskId,
            'user_id' => $userId,
            'title' => 'Task Assignment',
            'message' => "{$assignerName} assigned you to task '{$task['title']}' in {$projectName}",
            'action_url' => "/tasks/view/{$taskId}",
        ]);
    }

    public function alertTaskUpdate($taskId, $updaterName, $updateType = 'updated')
    {
        $task = $this->taskModel->find($taskId);
        if (!$task) {
            return false;
        }

        // Alert the admin (if task was updated by developer)
        if ($task['assigned_to']) {
            $this->alertModel->createOrUpdateAlert([
                'type' => 'task_update',
                'severity' => 'info',
                'entity_type' => 'task',
                'entity_id' => $taskId,
                'user_id' => null, // Admin alert
                'title' => 'Task Updated',
                'message' => "{$updaterName} {$updateType} task '{$task['title']}'",
                'action_url' => "/tasks/view/{$taskId}",
            ]);
        }

        return true;
    }

    public function alertProjectCredentialAdded($projectId, $credentialLabel, $adminName)
    {
        $project = $this->projectModel->find($projectId);
        if (!$project) {
            return false;
        }

        // Get all users assigned to this project
        $db = \Config\Database::connect();
        $projectUsers = $db->table('project_users')
            ->select('user_id')
            ->where('project_id', $projectId)
            ->get()
            ->getResultArray();

        // Get users assigned to tasks via legacy assigned_to field
        $legacyTaskUsers = $db->table('tasks')
            ->select('assigned_to as user_id')
            ->distinct()
            ->where('project_id', $projectId)
            ->where('assigned_to IS NOT NULL')
            ->get()
            ->getResultArray();

        // Get users assigned to tasks via task_assignments table (new multi-assignment system)
        $newTaskUsers = $db->table('task_assignments')
            ->select('task_assignments.user_id')
            ->distinct()
            ->join('tasks', 'tasks.id = task_assignments.task_id')
            ->where('tasks.project_id', $projectId)
            ->get()
            ->getResultArray();

        // Merge and deduplicate user IDs
        $allUserIds = [];
        foreach ($projectUsers as $pu) {
            $allUserIds[] = $pu['user_id'];
        }
        foreach ($legacyTaskUsers as $tu) {
            if ($tu['user_id'] && !in_array($tu['user_id'], $allUserIds)) {
                $allUserIds[] = $tu['user_id'];
            }
        }
        foreach ($newTaskUsers as $tu) {
            if ($tu['user_id'] && !in_array($tu['user_id'], $allUserIds)) {
                $allUserIds[] = $tu['user_id'];
            }
        }

        // Create alert for each user
        foreach ($allUserIds as $userId) {
            $this->alertModel->createOrUpdateAlert([
                'type' => 'credential_added',
                'severity' => 'info',
                'entity_type' => 'project',
                'entity_id' => $projectId,
                'user_id' => $userId,
                'title' => 'Project Credentials Added',
                'message' => "{$adminName} added credential '{$credentialLabel}' to project '{$project['name']}'",
                'action_url' => "/projects/view/{$projectId}",
            ]);
        }

        return true;
    }

    public function alertMultipleTaskAssignments($taskId, $userIds, $assignerName)
    {
        $task = $this->taskModel->find($taskId);
        if (!$task) {
            return false;
        }

        $project = $this->projectModel->find($task['project_id']);
        $projectName = $project ? $project['name'] : 'Unknown Project';

        // Create alert for each assigned user
        foreach ($userIds as $userId) {
            $this->alertModel->createOrUpdateAlert([
                'type' => 'task_assignment',
                'severity' => 'info',
                'entity_type' => 'task',
                'entity_id' => $taskId,
                'user_id' => $userId,
                'title' => 'Task Assignment',
                'message' => "{$assignerName} assigned you to task '{$task['title']}' in {$projectName}",
                'action_url' => "/tasks/view/{$taskId}",
            ]);
        }

        return true;
    }

    public function alertTaskSubmittedForReview($taskId, $developerName)
    {
        $task = $this->taskModel->find($taskId);
        if (!$task) {
            return false;
        }

        $project = $this->projectModel->find($task['project_id']);
        $projectName = $project ? $project['name'] : 'Unknown Project';

        // Create alert for all admins
        $db = \Config\Database::connect();
        $admins = $db->table('users')
            ->select('users.id')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
            ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id')
            ->where('auth_groups.name', 'admin')
            ->where('users.active', 1)
            ->get()
            ->getResultArray();

        foreach ($admins as $admin) {
            $this->alertModel->createOrUpdateAlert([
                'type' => 'task_review_request',
                'severity' => 'medium',
                'entity_type' => 'task',
                'entity_id' => $taskId,
                'user_id' => $admin['id'],
                'title' => 'Task Review Request',
                'message' => "{$developerName} submitted task '{$task['title']}' in {$projectName} for review",
                'action_url' => "/tasks/view/{$taskId}",
            ]);
        }

        return true;
    }

    public function alertTaskReviewCompleted($taskId, $reviewStatus, $adminName, $comments = '')
    {
        $task = $this->taskModel->find($taskId);
        if (!$task) {
            return false;
        }

        $project = $this->projectModel->find($task['project_id']);
        $projectName = $project ? $project['name'] : 'Unknown Project';

        // Get all users assigned to this task
        $db = \Config\Database::connect();
        $assignedUsers = [];

        // Legacy assignment
        if ($task['assigned_to']) {
            $assignedUsers[] = $task['assigned_to'];
        }

        // New assignment system
        $taskAssignments = $db->table('task_assignments')
            ->select('user_id')
            ->where('task_id', $taskId)
            ->get()
            ->getResultArray();

        foreach ($taskAssignments as $assignment) {
            if (!in_array($assignment['user_id'], $assignedUsers)) {
                $assignedUsers[] = $assignment['user_id'];
            }
        }

        $statusMessage = $reviewStatus === 'done' ? 'approved and completed' : 'requires revision';
        $messageText = "{$adminName} reviewed task '{$task['title']}' in {$projectName} - {$statusMessage}";
        
        if ($comments) {
            $messageText .= ". Comments: {$comments}";
        }

        // Create alert for each assigned user
        foreach ($assignedUsers as $userId) {
            $this->alertModel->createOrUpdateAlert([
                'type' => 'task_review_completed',
                'severity' => $reviewStatus === 'done' ? 'info' : 'medium',
                'entity_type' => 'task',
                'entity_id' => $taskId,
                'user_id' => $userId,
                'title' => 'Task Review Completed',
                'message' => $messageText,
                'action_url' => "/tasks/view/{$taskId}",
            ]);
        }

        return true;
    }
}
