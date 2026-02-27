<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TimeEntryModel;
use App\Models\TaskModel;
use CodeIgniter\Shield\Models\UserModel;

class TimeEntriesController extends BaseController
{
    public function index()
    {
        $user = auth()->user();
        $isAdmin = $user->inGroup('admin');
        
        $timeModel = new TimeEntryModel();
        $entries = $timeModel->getTimeEntriesForUser($user->id, $isAdmin);
        
        $data = [
            'title' => 'Time Entries',
            'entries' => $entries,
            'isAdmin' => $isAdmin,
        ];

        return view('time_entries/index', $data);
    }

    public function create()
    {
        $user = auth()->user();
        $isAdmin = $user->inGroup('admin');
        
        $taskModel = new TaskModel();
        $tasks = $taskModel->getTasksForUser($user->id, $isAdmin);
        
        $data = [
            'title' => 'Log Time Entry',
            'tasks' => $tasks,
        ];

        return view('time_entries/create', $data);
    }

    public function tracker()
    {
        $taskModel = new TaskModel();
        $timeModel = new TimeEntryModel();
        $authUser = auth()->user();
        $authUserId = auth()->id();
        $isAdmin = $authUser->inGroup('admin');

        $selectedUserParam = $this->request->getGet('user');
        $selectedUserId = null;
        if (!empty($selectedUserParam) && ctype_digit($selectedUserParam)) {
            $selectedUserId = (int) $selectedUserParam;
        }

        $isViewingAll = $isAdmin && $selectedUserId === null;
        $targetUserId = $isViewingAll ? null : ($selectedUserId ?? $authUserId);
        $selectedUserName = $isViewingAll ? 'All Developers' : ($targetUserId === $authUserId ? $authUser->username : 'User #' . $targetUserId);

        $myTasks = [];
        if (!$isViewingAll) {
            $myTasks = $taskModel
                ->select('tasks.*, projects.name as project_name')
                ->join('projects', 'projects.id = tasks.project_id')
                ->where('tasks.assigned_to', $targetUserId)
                ->whereIn('tasks.status', ['backlog', 'todo', 'in_progress', 'review'])
                ->where('tasks.deleted_at', null)
                ->orderBy('tasks.status', 'ASC')
                ->orderBy('tasks.priority', 'DESC')
                ->findAll();
        }

        $recentEntriesBuilder = $timeModel
            ->select('time_entries.*, tasks.title as task_title, projects.name as project_name, users.username as user_name')
            ->join('tasks', 'tasks.id = time_entries.task_id', 'left')
            ->join('projects', 'projects.id = tasks.project_id', 'left')
            ->join('users', 'users.id = time_entries.user_id', 'left');

        if ($targetUserId !== null) {
            $recentEntriesBuilder->where('time_entries.user_id', $targetUserId);
        }

        $recentEntries = $recentEntriesBuilder
            ->orderBy('time_entries.date', 'DESC')
            ->orderBy('time_entries.created_at', 'DESC')
            ->findAll(20);

        $taskOptions = $myTasks;
        $taskOptionIds = array_column($taskOptions, 'id');

        foreach ($recentEntries as $entry) {
            if (!empty($entry['task_id']) && !in_array($entry['task_id'], $taskOptionIds, true)) {
                $taskOptions[] = [
                    'id' => $entry['task_id'],
                    'title' => $entry['task_title'] ?? 'Task #' . $entry['task_id'],
                    'project_name' => $entry['project_name'] ?? '',
                ];
                $taskOptionIds[] = $entry['task_id'];
            }
        }

        $todayHoursBuilder = $timeModel
            ->selectSum('hours')
            ->where('date', date('Y-m-d'));

        if ($targetUserId !== null) {
            $todayHoursBuilder->where('user_id', $targetUserId);
        }

        $todayHours = $todayHoursBuilder->first();

        $users = [];
        if ($isAdmin) {
            $userModel = new UserModel();
            $users = $userModel
                ->select('id, username')
                ->where('active', 1)
                ->orderBy('username', 'ASC')
                ->asArray()
                ->findAll();

            if (!$isViewingAll && $targetUserId !== null) {
                foreach ($users as $user) {
                    if ($user['id'] === $targetUserId) {
                        $selectedUserName = $user['username'];
                        break;
                    }
                }
            } else {
                $selectedUserName = 'All Developers';
            }
        }

        return view('time_entries/tracker', [
            'title' => 'Time Tracker',
            'my_tasks' => $myTasks,
            'recent_entries' => $recentEntries,
            'today_hours' => $todayHours['hours'] ?? 0,
            'users' => $users,
            'is_admin' => $isAdmin,
            'selected_user_id' => $isViewingAll ? '' : $targetUserId,
            'current_user_id' => $authUserId,
            'selected_user_name' => $selectedUserName,
            'task_options' => $taskOptions,
            'is_viewing_all' => $isViewingAll,
        ]);
    }

    public function store()
    {
        $timeModel = new TimeEntryModel();
        $data = $this->request->getPost();
        $data['user_id'] = auth()->id();

        if (!$timeModel->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $timeModel->errors());
        }

        return redirect()->to('/time/tracker')->with('success', 'Time entry saved successfully');
    }

    public function heartbeat()
    {
        if (!auth()->user()) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'Session expired. Please refresh and log in again.',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'timestamp' => time(),
        ]);
    }
}
