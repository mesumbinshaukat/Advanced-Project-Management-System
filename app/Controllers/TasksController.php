<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TaskModel;
use App\Models\ProjectModel;
use App\Models\ProjectUserModel;

class TasksController extends BaseController
{
    public function index()
    {
        $user = auth()->user();
        $isAdmin = $user->inGroup('admin');
        
        $taskModel = new TaskModel();
        $tasks = $taskModel->getTasksForUser($user->id, $isAdmin);
        
        $data = [
            'title' => 'Tasks',
            'tasks' => $tasks,
            'isAdmin' => $isAdmin,
        ];

        return view('tasks/index', $data);
    }

    public function kanban($projectId = null)
    {
        $user = auth()->user();
        $isAdmin = $user->inGroup('admin');
        
        if (!$projectId) {
            return redirect()->to('/projects')->with('error', 'Project ID is required');
        }

        $projectModel = new ProjectModel();
        $project = $projectModel->find($projectId);
        
        if (!$project) {
            return redirect()->to('/projects')->with('error', 'Project not found');
        }

        if (!$isAdmin) {
            $projectUserModel = new ProjectUserModel();
            if (!$projectUserModel->isUserAssignedToProject($projectId, $user->id)) {
                return redirect()->to('/projects')->with('error', 'You do not have access to this project');
            }
        }

        $taskModel = new TaskModel();
        $statuses = ['backlog', 'todo', 'in_progress', 'review', 'done'];
        $tasksByStatus = [];
        
        foreach ($statuses as $status) {
            $tasksByStatus[$status] = $taskModel->getTasksByStatus($projectId, $status);
        }
        
        $data = [
            'title' => 'Kanban Board - ' . $project['name'],
            'project' => $project,
            'tasks_by_status' => $tasksByStatus,
            'isAdmin' => $isAdmin,
        ];

        return view('tasks/kanban', $data);
    }

    public function create($projectId = null)
    {
        $user = auth()->user();
        $isAdmin = $user->inGroup('admin');
        
        $projectModel = new ProjectModel();
        $projects = $projectModel->getProjectsForUser($user->id, $isAdmin);
        
        $data = [
            'title' => 'Create Task',
            'projects' => $projects,
            'selected_project_id' => $projectId,
        ];

        return view('tasks/create', $data);
    }
}
