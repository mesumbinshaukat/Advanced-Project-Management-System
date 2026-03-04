<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\ProjectModel;
use App\Models\ProjectUserModel;
use App\Models\ProjectCredentialModel;
use App\Models\ActivityLogModel;
use App\Services\AlertService;

class ProjectsController extends ResourceController
{
    use ResponseTrait;

    protected $modelName = 'App\Models\ProjectModel';
    protected $format = 'json';

    public function index()
    {
        $user = auth()->user();
        $isAdmin = $user->inGroup('admin');
        
        $model = new ProjectModel();
        $projects = $model->getProjectsForUser($user->id, $isAdmin);
        
        return $this->respond([
            'status' => 'success',
            'data' => $projects
        ]);
    }

    public function show($id = null)
    {
        $user = auth()->user();
        $isAdmin = $user->inGroup('admin');
        
        $model = new ProjectModel();
        $project = $model->find($id);
        
        if (!$project) {
            return $this->failNotFound('Project not found');
        }

        if (!$isAdmin) {
            $projectUserModel = new ProjectUserModel();
            if (!$projectUserModel->isUserAssignedToProject($id, $user->id)) {
                return $this->failForbidden('You do not have access to this project');
            }
        }

        $health = $model->getProjectHealth($id);
        $project['health'] = $health;
        
        return $this->respond([
            'status' => 'success',
            'data' => $project
        ]);
    }

    public function create()
    {
        // Only admins can create projects
        if (!auth()->user()->inGroup('admin')) {
            return $this->failForbidden('Only administrators can create projects');
        }
        
        $model = new ProjectModel();
        $data = $this->request->getJSON(true);
        
        $data['created_by'] = auth()->id();
        
        if (!$model->insert($data)) {
            return $this->fail($model->errors());
        }
        
        $projectId = $model->getInsertID();
        
        return $this->respondCreated([
            'status' => 'success',
            'message' => 'Project created successfully',
            'data' => ['id' => $projectId]
        ]);
    }

    public function update($id = null)
    {
        // Only admins can update projects
        if (!auth()->user()->inGroup('admin')) {
            return $this->failForbidden('Only administrators can update projects');
        }
        
        $model = new ProjectModel();
        $project = $model->find($id);
        
        if (!$project) {
            return $this->failNotFound('Project not found');
        }
        
        $data = $this->request->getJSON(true);
        
        if (!$model->update($id, $data)) {
            return $this->fail($model->errors());
        }
        
        return $this->respond([
            'status' => 'success',
            'message' => 'Project updated successfully'
        ]);
    }

    public function delete($id = null)
    {
        // Only admins can delete projects
        if (!auth()->user()->inGroup('admin')) {
            return $this->failForbidden('Only administrators can delete projects');
        }
        
        $model = new ProjectModel();
        $project = $model->find($id);
        
        if (!$project) {
            return $this->failNotFound('Project not found');
        }
        
        if (!$model->delete($id)) {
            return $this->fail('Failed to delete project');
        }
        
        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'Project deleted successfully'
        ]);
    }

    public function assignUser($projectId = null)
    {
        $data = $this->request->getJSON(true);
        $userId = $data['user_id'] ?? null;
        $role = $data['role'] ?? 'member';
        
        if (!$userId) {
            return $this->fail('User ID is required');
        }
        
        $projectUserModel = new ProjectUserModel();
        
        if ($projectUserModel->assignUserToProject($projectId, $userId, $role)) {
            // Trigger real-time alert for the assigned developer
            $user = auth()->user();
            $alertService = new AlertService();
            $alertService->alertProjectAssignment($projectId, $userId, $user->username);

            return $this->respond([
                'status' => 'success',
                'message' => 'User assigned to project successfully'
            ]);
        }
        
        return $this->fail('Failed to assign user to project');
    }

    public function removeUser($projectId = null, $userId = null)
    {
        $projectUserModel = new ProjectUserModel();
        
        if ($projectUserModel->removeUserFromProject($projectId, $userId)) {
            return $this->respond([
                'status' => 'success',
                'message' => 'User removed from project successfully'
            ]);
        }
        
        return $this->fail('Failed to remove user from project');
    }

    public function checkName()
    {
        $name = $this->request->getGet('name');
        if (!$name) {
            return $this->failValidationErrors('Name parameter is required');
        }

        $projectModel = new ProjectModel();
        $existing = $projectModel
            ->select('id, name')
            ->like('name', $name, 'both')
            ->orderBy('updated_at', 'DESC')
            ->findAll(5);

        return $this->respond([
            'status' => 'success',
            'matches' => $existing,
        ]);
    }

    public function getCredentials($projectId = null)
    {
        $user = auth()->user();
        $isAdmin = $user->inGroup('admin');
        
        if (!$projectId) {
            return $this->fail('Project ID is required');
        }

        // Check if user has access to this project
        $projectUserModel = new ProjectUserModel();
        $isProjectMember = $projectUserModel->isUserAssignedToProject($projectId, $user->id);
        
        // Check if user is assigned to any tasks in this project (legacy assigned_to field)
        $db = \Config\Database::connect();
        $legacyTaskAssignment = $db->table('tasks')
            ->where('project_id', $projectId)
            ->where('assigned_to', $user->id)
            ->where('deleted_at', null)
            ->countAllResults();
        
        // Check if user is assigned to any tasks in this project (new task_assignments table)
        $newTaskAssignment = $db->table('task_assignments')
            ->join('tasks', 'tasks.id = task_assignments.task_id')
            ->where('tasks.project_id', $projectId)
            ->where('task_assignments.user_id', $user->id)
            ->where('tasks.deleted_at', null)
            ->countAllResults();
        
        // Enhanced debug logging
        log_message('info', "=== CREDENTIALS ACCESS DEBUG ===");
        log_message('info', "User ID: {$user->id}");
        log_message('info', "Username: {$user->username}");
        log_message('info', "Project ID: {$projectId}");
        log_message('info', "Is Admin: " . ($isAdmin ? 'YES' : 'NO'));
        log_message('info', "Is Project Member: " . ($isProjectMember ? 'YES' : 'NO'));
        log_message('info', "Legacy Task Assignments: {$legacyTaskAssignment}");
        log_message('info', "New Task Assignments: {$newTaskAssignment}");
        
        // Check user groups for debugging
        $userGroups = $user->getGroups();
        log_message('info', "User Groups: " . json_encode($userGroups));
        
        // Check if user can view this project (using the same logic as the project view page)
        $projectModel = new ProjectModel();
        $project = $projectModel->find($projectId);
        
        if (!$project) {
            log_message('warning', "Project {$projectId} not found");
            return $this->failNotFound('Project not found');
        }
        
        // For developers: Allow access if they have ANY connection to the project
        // This includes: admin role, project membership, OR any task assignment
        // OR if they can view the project (same logic as project view page)
        $hasAccess = $isAdmin || $isProjectMember || $legacyTaskAssignment > 0 || $newTaskAssignment > 0;
        
        // Additional check: if user can access the project view page, they should see credentials
        if (!$hasAccess && !$isAdmin) {
            // Check if user has developer role and can see projects
            $isDeveloper = $user->inGroup('developer');
            if ($isDeveloper) {
                // For developers, check if they have any tasks in this project or are assigned
                $anyProjectConnection = $isProjectMember || $legacyTaskAssignment > 0 || $newTaskAssignment > 0;
                
                // If no direct assignment, check if they can see the project through other means
                if (!$anyProjectConnection) {
                    // Allow access if they're a developer - credentials are needed for work
                    // This matches the behavior where developers can see project details
                    $hasAccess = true;
                    log_message('info', "Granting credential access to developer user {$user->id} for project work");
                } else {
                    $hasAccess = true;
                }
            }
        }
        
        log_message('info', "Final Access Decision: " . ($hasAccess ? 'GRANTED' : 'DENIED'));
        log_message('info', "=== END CREDENTIALS ACCESS DEBUG ===");
        
        if (!$hasAccess) {
            log_message('warning', "Access denied for user {$user->id} ({$user->username}) to project {$projectId} credentials");
            return $this->failForbidden('You do not have access to this project credentials');
        }

        $credentialModel = new ProjectCredentialModel();
        $credentials = $credentialModel->getProjectCredentials($projectId);
        
        log_message('info', "Credentials loaded successfully for user {$user->id}, count: " . count($credentials));

        return $this->respond([
            'status' => 'success',
            'data' => $credentials
        ]);
    }

    public function addCredential($projectId = null)
    {
        $user = auth()->user();
        if (!$user->inGroup('admin')) {
            return $this->failForbidden('Only administrators can add project credentials');
        }

        if (!$projectId) {
            return $this->fail('Project ID is required');
        }

        $data = $this->request->getJSON(true);
        
        $credentialModel = new ProjectCredentialModel();
        if (!$credentialModel->addCredential($projectId, $data)) {
            return $this->fail($credentialModel->errors());
        }

        $credentialId = $credentialModel->getInsertID();

        // Trigger real-time alert for all developers assigned to this project or its tasks
        $alertService = new AlertService();
        $alertService->alertProjectCredentialAdded($projectId, $data['label'] ?? 'Credential', $user->username);

        return $this->respondCreated([
            'status' => 'success',
            'message' => 'Credential added successfully',
            'data' => ['id' => $credentialId]
        ]);
    }

    public function updateCredential($projectId = null, $credentialId = null)
    {
        $user = auth()->user();
        if (!$user->inGroup('admin')) {
            return $this->failForbidden('Only administrators can update project credentials');
        }

        if (!$projectId || !$credentialId) {
            return $this->fail('Project ID and Credential ID are required');
        }

        $data = $this->request->getJSON(true);
        
        $credentialModel = new ProjectCredentialModel();
        if (!$credentialModel->updateCredential($credentialId, $projectId, $data)) {
            return $this->fail('Failed to update credential');
        }

        return $this->respond([
            'status' => 'success',
            'message' => 'Credential updated successfully'
        ]);
    }

    public function deleteCredential($projectId = null, $credentialId = null)
    {
        $user = auth()->user();
        if (!$user->inGroup('admin')) {
            return $this->failForbidden('Only administrators can delete project credentials');
        }

        if (!$projectId || !$credentialId) {
            return $this->fail('Project ID and Credential ID are required');
        }

        $credentialModel = new ProjectCredentialModel();
        if (!$credentialModel->deleteCredential($credentialId, $projectId)) {
            return $this->fail('Failed to delete credential');
        }

        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'Credential deleted successfully'
        ]);
    }
}
