<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\TaskModel;
use App\Models\ProjectUserModel;
use App\Models\TaskAssignmentModel;
use App\Services\AlertService;

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
        
        // Extract assigned_to array before inserting task
        $assignedUserIds = $data['assigned_to'] ?? [];
        unset($data['assigned_to']);
        
        // Set assigned_to to first user if provided, otherwise null
        $data['assigned_to'] = is_array($assignedUserIds) && count($assignedUserIds) > 0 ? $assignedUserIds[0] : null;
        
        if (!$model->insert($data)) {
            return $this->fail($model->errors());
        }
        
        $taskId = $model->getInsertID();
        
        // Create task assignments for all selected users
        if (is_array($assignedUserIds) && count($assignedUserIds) > 0) {
            $assignmentModel = new TaskAssignmentModel();
            foreach ($assignedUserIds as $userId) {
                $assignmentModel->assignUser($taskId, $userId);
            }

            // Trigger real-time alerts for all assigned developers
            $alertService = new AlertService();
            $alertService->alertMultipleTaskAssignments($taskId, $assignedUserIds, $user->username);
        }
        
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

        // Check if user has access to this task
        $assignmentModel = new TaskAssignmentModel();
        $assignedUserIds = $assignmentModel->getAssignedUserIds($id);
        
        if (!$isAdmin && !in_array($user->id, $assignedUserIds)) {
            return $this->failForbidden('You can only update your own tasks');
        }
        
        $data = $this->request->getJSON(true);
        
        if (!$isAdmin) {
            $allowedFields = ['status', 'description'];
            $data = array_intersect_key($data, array_flip($allowedFields));
        }
        
        // Extract assigned_to array if present
        $newAssignedUserIds = $data['assigned_to'] ?? null;
        if ($newAssignedUserIds !== null) {
            unset($data['assigned_to']);
            // Set assigned_to to first user if provided, otherwise null
            $data['assigned_to'] = is_array($newAssignedUserIds) && count($newAssignedUserIds) > 0 ? $newAssignedUserIds[0] : null;
        }
        
        if (!$model->update($id, $data)) {
            return $this->fail($model->errors());
        }
        
        // Sync task assignments if provided
        if ($newAssignedUserIds !== null) {
            $assignmentModel->syncAssignments($id, is_array($newAssignedUserIds) ? $newAssignedUserIds : []);
        }

        // Trigger alert if developer updated the task (notify admin)
        if (!$isAdmin) {
            $alertService = new AlertService();
            $alertService->alertTaskUpdate($id, $user->username, 'updated');
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
        $user = auth()->user();
        $isAdmin = $user->inGroup('admin');
        
        $data = $this->request->getJSON(true);
        $status = $data['status'] ?? null;
        $orderPosition = $data['order_position'] ?? null;
        
        if (!$status) {
            return $this->fail('Status is required');
        }
        
        $model = new TaskModel();
        $task = $model->find($id);
        
        if (!$task) {
            return $this->failNotFound('Task not found');
        }
        
        // Check permissions
        $assignmentModel = new TaskAssignmentModel();
        $assignedUserIds = $assignmentModel->getAssignedUserIds($id);
        
        if (!$isAdmin && !in_array($user->id, $assignedUserIds)) {
            return $this->failForbidden('You can only update your own tasks');
        }
        
        // Handle special status transitions
        if ($status === 'submitted_for_review' && !$isAdmin) {
            // Developer submitting task for review
            $alertService = new AlertService();
            $alertService->alertTaskSubmittedForReview($id, $user->username);
        } elseif (in_array($status, ['done', 'needs_revision']) && $isAdmin) {
            // Admin completing review
            $alertService = new AlertService();
            $alertService->alertTaskReviewCompleted($id, $status, $user->username);
        }
        
        if ($model->updateTaskStatus($id, $status, $orderPosition)) {
            return $this->respond([
                'status' => 'success',
                'message' => 'Task status updated successfully'
            ]);
        }
        
        return $this->fail('Failed to update task status');
    }
    
    public function submitForReview($id = null)
    {
        $user = auth()->user();
        
        if ($user->inGroup('admin')) {
            return $this->failForbidden('Admins cannot submit tasks for review');
        }
        
        $model = new TaskModel();
        $task = $model->find($id);
        
        if (!$task) {
            return $this->failNotFound('Task not found');
        }
        
        // Check if user is assigned to this task
        $assignmentModel = new TaskAssignmentModel();
        $assignedUserIds = $assignmentModel->getAssignedUserIds($id);
        
        if (!in_array($user->id, $assignedUserIds)) {
            return $this->failForbidden('You can only submit your own tasks for review');
        }
        
        // Only allow submission from todo or in_progress status
        if (!in_array($task['status'], ['todo', 'in_progress'])) {
            return $this->fail('Task must be in todo or in progress status to submit for review');
        }
        
        // Update status to submitted_for_review
        if ($model->updateTaskStatus($id, 'submitted_for_review')) {
            // Create alert for admin
            $alertService = new AlertService();
            $alertService->alertTaskSubmittedForReview($id, $user->username);
            
            return $this->respond([
                'status' => 'success',
                'message' => 'Task submitted for review successfully'
            ]);
        }
        
        return $this->fail('Failed to submit task for review');
    }
    
    public function reviewTask($id = null)
    {
        $user = auth()->user();
        
        if (!$user->inGroup('admin')) {
            return $this->failForbidden('Only administrators can review tasks');
        }
        
        $data = $this->request->getJSON(true);
        $reviewStatus = $data['status'] ?? null;
        $reviewComments = $data['comments'] ?? '';
        
        if (!in_array($reviewStatus, ['done', 'needs_revision'])) {
            return $this->fail('Invalid review status. Must be "done" or "needs_revision"');
        }
        
        $model = new TaskModel();
        $task = $model->find($id);
        
        if (!$task) {
            return $this->failNotFound('Task not found');
        }
        
        if ($task['status'] !== 'submitted_for_review') {
            return $this->fail('Task must be submitted for review to be reviewed');
        }
        
        // Update task status
        $updateData = ['status' => $reviewStatus];
        if ($reviewStatus === 'done') {
            $updateData['completed_at'] = date('Y-m-d H:i:s');
        }
        
        if ($model->update($id, $updateData)) {
            // Create alert for the assigned developer
            $alertService = new AlertService();
            $alertService->alertTaskReviewCompleted($id, $reviewStatus, $user->username, $reviewComments);
            
            return $this->respond([
                'status' => 'success',
                'message' => 'Task review completed successfully'
            ]);
        }
        
        return $this->fail('Failed to complete task review');
    }
}
