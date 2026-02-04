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
}
