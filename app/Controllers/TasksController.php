<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TaskModel;
use App\Models\ProjectModel;
use App\Models\ProjectUserModel;
use App\Models\UserSkillModel;
use App\Models\TaskAssignmentModel;

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
        $statuses = ['backlog', 'todo', 'in_progress', 'submitted_for_review', 'needs_revision', 'review', 'done'];
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
        
        // Get users for assignment dropdown
        $db = \Config\Database::connect();
        $users = $db->table('users')
            ->select('users.id, users.username')
            ->where('users.active', 1)
            ->orderBy('users.username', 'ASC')
            ->get()
            ->getResultArray();

        $userIds = array_column($users, 'id');
        $skillModel = new UserSkillModel();
        $userSkills = $skillModel->getSkillsForUsers($userIds);
        $skillOptions = $skillModel->getAllSkills();
        
        $data = [
            'title' => 'Create Task',
            'projects' => $projects,
            'users' => $users,
            'selected_project_id' => $projectId,
            'user_skills' => $userSkills,
            'skill_options' => $skillOptions,
            'is_admin' => $isAdmin,
        ];

        return view('tasks/create', $data);
    }

    public function view($id)
    {
        $user = auth()->user();
        $isAdmin = $user->inGroup('admin');
        
        $taskModel = new TaskModel();
        $task = $taskModel->find($id);
        
        if (!$task) {
            return redirect()->to('/tasks')->with('error', 'Task not found');
        }

        // Check if user has access to this task
        if (!$isAdmin) {
            $projectUserModel = new ProjectUserModel();
            $taskAssignmentModel = new TaskAssignmentModel();
            $assignedUserIds = $taskAssignmentModel->getAssignedUserIds($id);
            
            if (!$projectUserModel->isUserAssignedToProject($task['project_id'], $user->id) && !in_array($user->id, $assignedUserIds)) {
                return redirect()->to('/tasks')->with('error', 'You do not have access to this task');
            }
        }

        $projectModel = new ProjectModel();
        $project = $projectModel->find($task['project_id']);
        
        // Get assigned developers
        $taskAssignmentModel = new TaskAssignmentModel();
        $assignedDevelopers = $taskAssignmentModel->getAssignedUsers($id);
        
        $data = [
            'title' => $task['title'],
            'task' => $task,
            'project' => $project,
            'assigned_developers' => $assignedDevelopers,
            'isAdmin' => $isAdmin,
        ];

        return view('tasks/view', $data);
    }

    public function reviewRequests()
    {
        $user = auth()->user();
        $isAdmin = $user->inGroup('admin');
        
        // Only admins can access this page
        if (!$isAdmin) {
            return redirect()->to('/tasks')->with('error', 'Access denied');
        }
        
        $taskModel = new TaskModel();
        $projectModel = new ProjectModel();
        
        // Get all tasks that are submitted for review or need revision
        $reviewTasks = $taskModel->select('tasks.*, projects.name as project_name, users.username as assigned_username')
            ->join('projects', 'projects.id = tasks.project_id', 'left')
            ->join('users', 'users.id = tasks.assigned_to', 'left')
            ->whereIn('tasks.status', ['submitted_for_review', 'needs_revision'])
            ->where('tasks.deleted_at', null)
            ->orderBy('tasks.submitted_for_review_at', 'DESC')
            ->findAll();
        
        // Get task assignments for tasks without assigned_to
        $taskAssignmentModel = new TaskAssignmentModel();
        foreach ($reviewTasks as &$task) {
            if (!$task['assigned_to']) {
                $assignedUsers = $taskAssignmentModel->getAssignedUsers($task['id']);
                $task['assigned_developers'] = $assignedUsers;
            }
        }
        
        $data = [
            'title' => 'Task Review Requests',
            'reviewTasks' => $reviewTasks,
            'isAdmin' => $isAdmin,
        ];

        return view('tasks/review_requests', $data);
    }

    public function edit($id)
    {
        $user = auth()->user();
        $isAdmin = $user->inGroup('admin');
        
        $taskModel = new TaskModel();
        $task = $taskModel->find($id);
        
        if (!$task) {
            return redirect()->to('/tasks')->with('error', 'Task not found');
        }

        // Check if user has access to this task
        $assignmentModel = new TaskAssignmentModel();
        $assignedUserIds = $assignmentModel->getAssignedUserIds($id);
        
        if (!$isAdmin && !in_array($user->id, $assignedUserIds)) {
            return redirect()->to('/tasks')->with('error', 'You do not have access to this task');
        }

        $projectModel = new ProjectModel();
        $projects = $projectModel->getProjectsForUser($user->id, $isAdmin);
        
        // Get users for assignment dropdown
        $db = \Config\Database::connect();
        $users = $db->table('users')
            ->select('users.id, users.username')
            ->where('users.active', 1)
            ->get()
            ->getResultArray();
        
        $data = [
            'title' => 'Edit Task',
            'task' => $task,
            'projects' => $projects,
            'users' => $users,
            'assigned_user_ids' => $assignedUserIds,
        ];
        
        return view('tasks/edit', $data);
    }

    public function delete($id)
    {
        $user = auth()->user();
        if (!$user->inGroup('admin')) {
            return redirect()->to('/tasks')->with('error', 'Access denied');
        }

        $taskModel = new TaskModel();
        $task = $taskModel->find($id);

        if (!$task) {
            return redirect()->to('/tasks')->with('error', 'Task not found');
        }

        $taskModel->delete($id);

        return redirect()->to('/tasks')->with('success', 'Task deleted successfully');
    }
}