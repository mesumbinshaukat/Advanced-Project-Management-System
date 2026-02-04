<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProjectModel;
use App\Models\TaskModel;
use App\Models\TimeEntryModel;
use App\Models\ActivityLogModel;
use App\Models\FinancialModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $user = auth()->user();
        $isAdmin = $user->inGroup('admin');
        
        $data = [
            'title' => 'Dashboard',
            'user' => $user,
            'isAdmin' => $isAdmin,
        ];

        if ($isAdmin) {
            $data = array_merge($data, $this->getAdminDashboardData($user->id));
        } else {
            $data = array_merge($data, $this->getDeveloperDashboardData($user->id));
        }

        return view('dashboard/index', $data);
    }

    private function getAdminDashboardData($userId)
    {
        $projectModel = new ProjectModel();
        $taskModel = new TaskModel();
        $timeModel = new TimeEntryModel();
        $activityModel = new ActivityLogModel();
        $financialModel = new FinancialModel();

        $projects = $projectModel->findAll();
        $totalProjects = count($projects);
        $activeProjects = count(array_filter($projects, fn($p) => $p['status'] === 'active'));

        $allTasks = $taskModel->findAll();
        $totalTasks = count($allTasks);
        $completedTasks = count(array_filter($allTasks, fn($t) => $t['status'] === 'done'));
        $overdueTasks = count(array_filter($allTasks, function($t) {
            return $t['deadline'] && strtotime($t['deadline']) < time() && $t['status'] !== 'done';
        }));

        $recentActivity = $activityModel->getRecentActivity(10);

        $projectsWithHealth = [];
        foreach (array_slice($projects, 0, 5) as $project) {
            $health = $projectModel->getProjectHealth($project['id']);
            $project['health'] = $health;
            $projectsWithHealth[] = $project;
        }

        return [
            'stats' => [
                'total_projects' => $totalProjects,
                'active_projects' => $activeProjects,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'overdue_tasks' => $overdueTasks,
            ],
            'projects' => $projectsWithHealth,
            'recent_activity' => $recentActivity,
        ];
    }

    private function getDeveloperDashboardData($userId)
    {
        $projectModel = new ProjectModel();
        $taskModel = new TaskModel();
        $timeModel = new TimeEntryModel();

        $projects = $projectModel->getProjectsForUser($userId, false);
        $tasks = $taskModel->getTasksForUser($userId, false);

        $myTasks = array_filter($tasks, fn($t) => $t['status'] !== 'done');
        $completedToday = array_filter($tasks, function($t) {
            return $t['status'] === 'done' && 
                   $t['completed_at'] && 
                   date('Y-m-d', strtotime($t['completed_at'])) === date('Y-m-d');
        });

        $todayEntries = $timeModel->where('user_id', $userId)
            ->where('date', date('Y-m-d'))
            ->findAll();
        
        $hoursToday = array_sum(array_column($todayEntries, 'hours'));

        $tasksByStatus = [
            'backlog' => count(array_filter($tasks, fn($t) => $t['status'] === 'backlog')),
            'todo' => count(array_filter($tasks, fn($t) => $t['status'] === 'todo')),
            'in_progress' => count(array_filter($tasks, fn($t) => $t['status'] === 'in_progress')),
            'review' => count(array_filter($tasks, fn($t) => $t['status'] === 'review')),
            'done' => count(array_filter($tasks, fn($t) => $t['status'] === 'done')),
        ];

        return [
            'stats' => [
                'my_projects' => count($projects),
                'my_tasks' => count($myTasks),
                'completed_today' => count($completedToday),
                'hours_today' => $hoursToday,
            ],
            'projects' => $projects,
            'tasks' => array_slice($myTasks, 0, 10),
            'tasks_by_status' => $tasksByStatus,
        ];
    }
}
