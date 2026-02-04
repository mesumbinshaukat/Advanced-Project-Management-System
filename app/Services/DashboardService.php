<?php

namespace App\Services;

use App\Models\ProjectModel;
use App\Models\TaskModel;
use App\Models\TimeEntryModel;
use App\Models\ActivityLogModel;

class DashboardService
{
    protected $projectModel;
    protected $taskModel;
    protected $timeEntryModel;
    protected $activityLogModel;

    public function __construct()
    {
        $this->projectModel = new ProjectModel();
        $this->taskModel = new TaskModel();
        $this->timeEntryModel = new TimeEntryModel();
        $this->activityLogModel = new ActivityLogModel();
    }

    public function getExecutiveDashboard()
    {
        return [
            'project_health' => $this->getProjectHealthIndicators(),
            'critical_alerts' => $this->getCriticalAlerts(),
            'recent_activity' => $this->getRecentActivity(10),
            'team_performance' => $this->getTeamPerformance(),
            'deadline_overview' => $this->getDeadlineOverview(),
        ];
    }

    public function getProjectHealthIndicators()
    {
        $projects = $this->projectModel
            ->select('projects.*, 
                (SELECT COUNT(*) FROM tasks WHERE tasks.project_id = projects.id AND tasks.deleted_at IS NULL) as total_tasks,
                (SELECT COUNT(*) FROM tasks WHERE tasks.project_id = projects.id AND tasks.status = "done" AND tasks.deleted_at IS NULL) as completed_tasks,
                (SELECT COUNT(*) FROM tasks WHERE tasks.project_id = projects.id AND tasks.is_blocked = 1 AND tasks.deleted_at IS NULL) as blocked_tasks,
                (SELECT COUNT(*) FROM tasks WHERE tasks.project_id = projects.id AND tasks.deadline < CURDATE() AND tasks.status != "done" AND tasks.deleted_at IS NULL) as overdue_tasks')
            ->where('projects.status !=', 'archived')
            ->where('projects.deleted_at', null)
            ->findAll();

        foreach ($projects as &$project) {
            $project['completion_rate'] = $project['total_tasks'] > 0 
                ? round(($project['completed_tasks'] / $project['total_tasks']) * 100, 1) 
                : 0;
            
            if ($project['blocked_tasks'] > 0 || $project['overdue_tasks'] > 2) {
                $project['health_status'] = 'critical';
            } elseif ($project['overdue_tasks'] > 0 || $project['completion_rate'] < 30) {
                $project['health_status'] = 'warning';
            } else {
                $project['health_status'] = 'healthy';
            }

            $this->projectModel->update($project['id'], ['health_status' => $project['health_status']]);
        }

        return $projects;
    }

    public function getCriticalAlerts()
    {
        $alerts = [];

        $blockedTasks = $this->taskModel
            ->select('tasks.*, projects.name as project_name')
            ->join('projects', 'projects.id = tasks.project_id')
            ->where('tasks.is_blocked', 1)
            ->where('tasks.deleted_at', null)
            ->findAll();

        foreach ($blockedTasks as $task) {
            $alerts[] = [
                'type' => 'blocker',
                'severity' => 'critical',
                'message' => "Task '{$task['title']}' in {$task['project_name']} is blocked",
                'link' => "/tasks/view/{$task['id']}",
                'created_at' => $task['updated_at'],
            ];
        }

        $overdueTasks = $this->taskModel
            ->select('tasks.*, projects.name as project_name, users.username')
            ->join('projects', 'projects.id = tasks.project_id')
            ->join('users', 'users.id = tasks.assigned_to', 'left')
            ->where('tasks.deadline <', date('Y-m-d'))
            ->where('tasks.status !=', 'done')
            ->where('tasks.deleted_at', null)
            ->orderBy('tasks.deadline', 'ASC')
            ->findAll(5);

        foreach ($overdueTasks as $task) {
            $daysOverdue = floor((time() - strtotime($task['deadline'])) / 86400);
            $alerts[] = [
                'type' => 'overdue',
                'severity' => 'warning',
                'message' => "Task '{$task['title']}' is {$daysOverdue} days overdue (Assigned to: {$task['username']})",
                'link' => "/tasks/view/{$task['id']}",
                'created_at' => $task['deadline'],
            ];
        }

        $unassignedTasks = $this->taskModel
            ->select('tasks.*, projects.name as project_name')
            ->join('projects', 'projects.id = tasks.project_id')
            ->where('tasks.assigned_to', null)
            ->whereIn('tasks.status', ['todo', 'in_progress'])
            ->where('tasks.deleted_at', null)
            ->findAll();

        foreach ($unassignedTasks as $task) {
            $alerts[] = [
                'type' => 'unassigned',
                'severity' => 'info',
                'message' => "Task '{$task['title']}' in {$task['project_name']} needs assignment",
                'link' => "/tasks/view/{$task['id']}",
                'created_at' => $task['created_at'],
            ];
        }

        usort($alerts, function($a, $b) {
            $severityOrder = ['critical' => 0, 'warning' => 1, 'info' => 2];
            return $severityOrder[$a['severity']] <=> $severityOrder[$b['severity']];
        });

        return array_slice($alerts, 0, 10);
    }

    public function getRecentActivity($limit = 10)
    {
        return $this->activityLogModel
            ->select('activity_logs.*, users.username')
            ->join('users', 'users.id = activity_logs.user_id')
            ->orderBy('activity_logs.created_at', 'DESC')
            ->findAll($limit);
    }

    public function getTeamPerformance()
    {
        $db = \Config\Database::connect();
        
        $developers = $db->table('users')
            ->select('users.id, users.username')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
            ->where('auth_groups_users.group', 'developer')
            ->get()
            ->getResultArray();

        $performance = [];
        foreach ($developers as $dev) {
            $tasksCompleted = $this->taskModel
                ->where('assigned_to', $dev['id'])
                ->where('status', 'done')
                ->where('completed_at >=', date('Y-m-d', strtotime('-30 days')))
                ->countAllResults();

            $hoursLogged = $this->timeEntryModel
                ->selectSum('hours')
                ->where('user_id', $dev['id'])
                ->where('date >=', date('Y-m-d', strtotime('-30 days')))
                ->first();

            $performance[] = [
                'id' => $dev['id'],
                'username' => $dev['username'],
                'tasks_completed_30d' => $tasksCompleted,
                'hours_logged_30d' => $hoursLogged['hours'] ?? 0,
            ];
        }

        return $performance;
    }

    public function getDeadlineOverview()
    {
        $upcoming = $this->taskModel
            ->select('tasks.*, projects.name as project_name, users.username')
            ->join('projects', 'projects.id = tasks.project_id')
            ->join('users', 'users.id = tasks.assigned_to', 'left')
            ->where('tasks.deadline >=', date('Y-m-d'))
            ->where('tasks.deadline <=', date('Y-m-d', strtotime('+7 days')))
            ->where('tasks.status !=', 'done')
            ->where('tasks.deleted_at', null)
            ->orderBy('tasks.deadline', 'ASC')
            ->findAll();

        return $upcoming;
    }

    public function getDeveloperDashboard($userId)
    {
        $myTasks = $this->taskModel
            ->select('tasks.*, projects.name as project_name')
            ->join('projects', 'projects.id = tasks.project_id')
            ->where('tasks.assigned_to', $userId)
            ->where('tasks.status !=', 'done')
            ->where('tasks.deleted_at', null)
            ->orderBy('tasks.priority', 'DESC')
            ->orderBy('tasks.deadline', 'ASC')
            ->findAll();

        $myProjects = $this->projectModel
            ->select('projects.*')
            ->join('project_users', 'project_users.project_id = projects.id')
            ->where('project_users.user_id', $userId)
            ->where('projects.deleted_at', null)
            ->findAll();

        $recentActivity = $this->activityLogModel
            ->select('activity_logs.*, users.username')
            ->join('users', 'users.id = activity_logs.user_id')
            ->join('tasks', 'tasks.id = activity_logs.entity_id AND activity_logs.entity_type = "task"', 'left')
            ->where('tasks.assigned_to', $userId)
            ->orWhere('activity_logs.user_id', $userId)
            ->orderBy('activity_logs.created_at', 'DESC')
            ->findAll(10);

        return [
            'my_tasks' => $myTasks,
            'my_projects' => $myProjects,
            'recent_activity' => $recentActivity,
        ];
    }
}
