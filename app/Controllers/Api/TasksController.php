<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\TaskModel;
use App\Models\ProjectUserModel;

class TasksController extends ResourceController
{
    use ResponseTrait;

    protected $modelName = 'App\Models\TaskModel';
    protected $format = 'json';

    public function index()
    {
        $user = auth()->user();
        $isAdmin = $user->inGroup('admin');
        
        $projectId = $this->request->getGet('project_id');
        $status = $this->request->getGet('status');
        
        $model = new TaskModel();
        
        if ($projectId) {
            if (!$isAdmin) {
                $projectUserModel = new ProjectUserModel();
                if (!$projectUserModel->isUserAssignedToProject($projectId, $user->id)) {
                    return $this->failForbidden('You do not have access to this project');
                }
            }
            
            $tasks = $model->getTasksByStatus($projectId, $status);
        } else {
            $tasks = $model->getTasksForUser($user->id, $isAdmin);
        }
        
        return $this->respond([
            'status' => 'success',
            'data' => $tasks
        ]);
    }

    public function show($id = null)
    {
        $user = auth()->user();
        $isAdmin = $user->inGroup('admin');
        
        $model = new TaskModel();
        $task = $model->find($id);
        
        if (!$task) {
            return $this->failNotFound('Task not found');
        }

        if (!$isAdmin && $task['assigned_to'] != $user->id) {
            return $this->failForbidden('You do not have access to this task');
        }
        
        return $this->respond([
            'status' => 'success',
            'data' => $task
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $isAdmin = $user->inGroup('admin');
        
        $model = new TaskModel();
        $data = $this->request->getJSON(true);
        
        // Check if developer has access to the project
        if (!$isAdmin && isset($data['project_id'])) {
            $projectUserModel = new ProjectUserModel();
            if (!$projectUserModel->isUserAssignedToProject($data['project_id'], $user->id)) {
                return $this->failForbidden('You do not have access to this project');
            }
        }
        
        $data['created_by'] = auth()->id();
        
        if (!$model->insert($data)) {
            return $this->fail($model->errors());
        }
        
        $taskId = $model->getInsertID();
        
        return $this->respondCreated([
            'status' => 'success',
            'message' => 'Task created successfully',
            'data' => ['id' => $taskId]
        ]);
    }

    public function update($id = null)
    {
        $user = auth()->user();
        $isAdmin = $user->inGroup('admin');
        
        $model = new TaskModel();
        $task = $model->find($id);
        
        if (!$task) {
            return $this->failNotFound('Task not found');
        }

        if (!$isAdmin && $task['assigned_to'] != $user->id) {
            return $this->failForbidden('You can only update your own tasks');
        }
        
        $data = $this->request->getJSON(true);
        
        if (!$isAdmin) {
            $allowedFields = ['status', 'description'];
            $data = array_intersect_key($data, array_flip($allowedFields));
        }
        
        if (!$model->update($id, $data)) {
            return $this->fail($model->errors());
        }
        
        return $this->respond([
            'status' => 'success',
            'message' => 'Task updated successfully'
        ]);
    }

    public function delete($id = null)
    {
        $model = new TaskModel();
        $task = $model->find($id);
        
        if (!$task) {
            return $this->failNotFound('Task not found');
        }
        
        if (!$model->delete($id)) {
            return $this->fail('Failed to delete task');
        }
        
        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'Task deleted successfully'
        ]);
    }

    public function updateStatus($id = null)
    {
        $data = $this->request->getJSON(true);
        $status = $data['status'] ?? null;
        $orderPosition = $data['order_position'] ?? null;
        
        if (!$status) {
            return $this->fail('Status is required');
        }
        
        $model = new TaskModel();
        
        if ($model->updateTaskStatus($id, $status, $orderPosition)) {
            return $this->respond([
                'status' => 'success',
                'message' => 'Task status updated successfully'
            ]);
        }
        
        return $this->fail('Failed to update task status');
    }
}
