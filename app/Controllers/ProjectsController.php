<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProjectModel;
use App\Models\ClientModel;
use App\Models\ProjectUserModel;

class ProjectsController extends BaseController
{
    public function index()
    {
        $user = auth()->user();
        $isAdmin = $user->inGroup('admin');
        
        $projectModel = new ProjectModel();
        $projects = $projectModel->getProjectsForUser($user->id, $isAdmin);
        
        $data = [
            'title' => 'Projects',
            'projects' => $projects,
            'isAdmin' => $isAdmin,
        ];

        return view('projects/index', $data);
    }

    public function view($id)
    {
        $user = auth()->user();
        $isAdmin = $user->inGroup('admin');
        
        $projectModel = new ProjectModel();
        $project = $projectModel->find($id);
        
        if (!$project) {
            return redirect()->to('/projects')->with('error', 'Project not found');
        }

        if (!$isAdmin) {
            $projectUserModel = new ProjectUserModel();
            if (!$projectUserModel->isUserAssignedToProject($id, $user->id)) {
                return redirect()->to('/projects')->with('error', 'You do not have access to this project');
            }
        }

        $health = $projectModel->getProjectHealth($id);
        $projectUserModel = new ProjectUserModel();
        $assignedUsers = $projectUserModel->getProjectUsers($id);
        
        $availableDevelopers = [];
        if ($isAdmin) {
            $db = \Config\Database::connect();
            $assignedUserIds = array_column($assignedUsers, 'user_id');
            
            $builder = $db->table('users')
                ->select('users.id, users.username, auth_identities.secret as email')
                ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
                ->join('auth_identities', 'auth_identities.user_id = users.id', 'left')
                ->where('auth_groups_users.group', 'developer')
                ->where('users.active', 1)
                ->where('auth_identities.type', 'email_password');
            
            if (!empty($assignedUserIds)) {
                $builder->whereNotIn('users.id', $assignedUserIds);
            }
            
            $availableDevelopers = $builder->get()->getResultArray();
        }
        
        $data = [
            'title' => $project['name'],
            'project' => $project,
            'health' => $health,
            'assigned_users' => $assignedUsers,
            'available_developers' => $availableDevelopers,
            'isAdmin' => $isAdmin,
        ];

        return view('projects/view', $data);
    }

    public function create()
    {
        $clientModel = new ClientModel();
        $clients = $clientModel->where('is_active', 1)->findAll();
        
        $data = [
            'title' => 'Create Project',
            'clients' => $clients,
        ];

        return view('projects/create', $data);
    }

    public function edit($id)
    {
        $projectModel = new ProjectModel();
        $project = $projectModel->find($id);
        
        if (!$project) {
            return redirect()->to('/projects')->with('error', 'Project not found');
        }

        $clientModel = new ClientModel();
        $clients = $clientModel->where('is_active', 1)->findAll();
        
        $data = [
            'title' => 'Edit Project',
            'project' => $project,
            'clients' => $clients,
        ];

        return view('projects/edit', $data);
    }
}
