<?php

namespace App\Services;

use App\Models\TaskModel;
use App\Models\TimeEntryModel;
use App\Models\ProjectUserModel;

class AssignmentService
{
    protected $taskModel;
    protected $timeEntryModel;
    protected $projectUserModel;

    public function __construct()
    {
        $this->taskModel = new TaskModel();
        $this->timeEntryModel = new TimeEntryModel();
        $this->projectUserModel = new ProjectUserModel();
    }

    public function suggestAssignment($projectId, $taskId = null)
    {
        $projectUsers = $this->projectUserModel
            ->select('project_users.user_id, users.username')
            ->join('users', 'users.id = project_users.user_id')
            ->where('project_users.project_id', $projectId)
            ->findAll();

        if (empty($projectUsers)) {
            return null;
        }

        $workloads = [];
        foreach ($projectUsers as $user) {
            $workloads[$user['user_id']] = [
                'username' => $user['username'],
                'active_tasks' => $this->getActiveTaskCount($user['user_id']),
                'hours_this_week' => $this->getWeeklyHours($user['user_id']),
                'score' => 0,
            ];
        }

        foreach ($workloads as $userId => &$workload) {
            $workload['score'] = ($workload['active_tasks'] * 10) + ($workload['hours_this_week'] * 0.5);
        }

        uasort($workloads, function($a, $b) {
            return $a['score'] <=> $b['score'];
        });

        $suggested = array_key_first($workloads);
        return [
            'user_id' => $suggested,
            'username' => $workloads[$suggested]['username'],
            'workload' => $workloads[$suggested],
            'all_workloads' => $workloads,
        ];
    }

    public function getActiveTaskCount($userId)
    {
        return $this->taskModel
            ->where('assigned_to', $userId)
            ->whereIn('status', ['todo', 'in_progress', 'review'])
            ->where('deleted_at', null)
            ->countAllResults();
    }

    public function getWeeklyHours($userId)
    {
        $startOfWeek = date('Y-m-d', strtotime('monday this week'));
        $endOfWeek = date('Y-m-d', strtotime('sunday this week'));

        $result = $this->timeEntryModel
            ->selectSum('hours')
            ->where('user_id', $userId)
            ->where('date >=', $startOfWeek)
            ->where('date <=', $endOfWeek)
            ->first();

        return $result['hours'] ?? 0;
    }

    public function getDeveloperWorkload($userId)
    {
        return [
            'active_tasks' => $this->getActiveTaskCount($userId),
            'hours_this_week' => $this->getWeeklyHours($userId),
            'tasks_by_status' => $this->getTasksByStatus($userId),
        ];
    }

    public function getTasksByStatus($userId)
    {
        $tasks = $this->taskModel
            ->select('status, COUNT(*) as count')
            ->where('assigned_to', $userId)
            ->where('deleted_at', null)
            ->groupBy('status')
            ->findAll();

        $statusCounts = [
            'backlog' => 0,
            'todo' => 0,
            'in_progress' => 0,
            'review' => 0,
            'done' => 0,
        ];

        foreach ($tasks as $task) {
            $statusCounts[$task['status']] = $task['count'];
        }

        return $statusCounts;
    }

    public function getAllDevelopersWorkload($projectId = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('users')
            ->select('users.id, users.username')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
            ->where('auth_groups_users.group', 'developer');

        if ($projectId) {
            $builder->join('project_users', 'project_users.user_id = users.id')
                ->where('project_users.project_id', $projectId);
        }

        $developers = $builder->get()->getResultArray();

        $workloads = [];
        foreach ($developers as $dev) {
            $workloads[] = [
                'id' => $dev['id'],
                'username' => $dev['username'],
                'workload' => $this->getDeveloperWorkload($dev['id']),
            ];
        }

        return $workloads;
    }
}
