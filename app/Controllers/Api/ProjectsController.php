<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\ProjectModel;
use App\Models\ProjectUserModel;
use App\Models\ActivityLogModel;

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
}
