<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TimeEntryModel;
use App\Models\TaskModel;

class TimeEntriesController extends BaseController
{
    public function index()
    {
        $user = auth()->user();
        $isAdmin = $user->inGroup('admin');
        
        $timeModel = new TimeEntryModel();
        $entries = $timeModel->getTimeEntriesForUser($user->id, $isAdmin);
        
        $data = [
            'title' => 'Time Tracking',
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
            'title' => 'Log Time',
            'tasks' => $tasks,
        ];

        return view('time_entries/create', $data);
    }

    public function tracker()
    {
        $taskModel = new TaskModel();
        $timeModel = new TimeEntryModel();
        $userId = auth()->id();

        $myTasks = $taskModel
            ->select('tasks.*, projects.name as project_name')
            ->join('projects', 'projects.id = tasks.project_id')
            ->where('tasks.assigned_to', $userId)
            ->whereIn('tasks.status', ['todo', 'in_progress', 'review'])
            ->where('tasks.deleted_at', null)
            ->findAll();

        $recentEntries = $timeModel
            ->select('time_entries.*, tasks.title as task_title, projects.name as project_name')
            ->join('tasks', 'tasks.id = time_entries.task_id', 'left')
            ->join('projects', 'projects.id = tasks.project_id', 'left')
            ->where('time_entries.user_id', $userId)
            ->orderBy('time_entries.date', 'DESC')
            ->orderBy('time_entries.created_at', 'DESC')
            ->findAll(20);

        $todayHours = $timeModel
            ->selectSum('hours')
            ->where('user_id', $userId)
            ->where('date', date('Y-m-d'))
            ->first();

        return view('time_entries/tracker', [
            'title' => 'Time Tracker',
            'my_tasks' => $myTasks,
            'recent_entries' => $recentEntries,
            'today_hours' => $todayHours['hours'] ?? 0,
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
