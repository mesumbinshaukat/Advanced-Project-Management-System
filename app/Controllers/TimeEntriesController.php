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

        $selectedUserId = $this->request->getGet('user');
        $targetUserId = $authUserId;
        $selectedUserName = $authUser->username;

        if ($isAdmin && !empty($selectedUserId) && ctype_digit($selectedUserId)) {
            $targetUserId = (int)$selectedUserId;
        }

        $myTasks = $taskModel
            ->select('tasks.*, projects.name as project_name')
            ->join('projects', 'projects.id = tasks.project_id')
            ->where('tasks.assigned_to', $targetUserId)
            ->whereIn('tasks.status', ['backlog', 'todo', 'in_progress', 'review'])
            ->where('tasks.deleted_at', null)
            ->orderBy('tasks.status', 'ASC')
            ->orderBy('tasks.priority', 'DESC')
            ->findAll();

        $recentEntries = $timeModel
            ->select('time_entries.*, tasks.title as task_title, projects.name as project_name')
            ->join('tasks', 'tasks.id = time_entries.task_id', 'left')
            ->join('projects', 'projects.id = tasks.project_id', 'left')
            ->where('time_entries.user_id', $targetUserId)
            ->orderBy('time_entries.date', 'DESC')
            ->orderBy('time_entries.created_at', 'DESC')
            ->findAll(20);

        $todayHours = $timeModel
            ->selectSum('hours')
            ->where('user_id', $targetUserId)
            ->where('date', date('Y-m-d'))
            ->first();

        $users = [];
        if ($isAdmin) {
            $userModel = new UserModel();
            $users = $userModel
                ->select('id, username')
                ->where('active', 1)
                ->orderBy('username', 'ASC')
                ->asArray()
                ->findAll();

            foreach ($users as $user) {
                if ($user['id'] === $targetUserId) {
                    $selectedUserName = $user['username'];
                    break;
                }
            }
        }

        return view('time_entries/tracker', [
            'title' => 'Time Tracker',
            'my_tasks' => $myTasks,
            'recent_entries' => $recentEntries,
            'today_hours' => $todayHours['hours'] ?? 0,
            'users' => $users,
            'is_admin' => $isAdmin,
            'selected_user_id' => $targetUserId,
            'current_user_id' => $authUserId,
            'selected_user_name' => $selectedUserName,
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
}
