<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\MessageModel;
use App\Models\ProjectModel;
use App\Models\ProjectUserModel;

class MessagesController extends ResourceController
{
    protected $modelName = 'App\Models\MessageModel';
    protected $format = 'json';

    private function checkProjectAccess($projectId)
    {
        $user = auth()->user();
        $isAdmin = $user->inGroup('admin');
        
        if (!$isAdmin) {
            $projectUserModel = new ProjectUserModel();
            if (!$projectUserModel->isUserAssignedToProject($projectId, $user->id)) {
                return false;
            }
        }
        return true;
    }

    public function index()
    {
        $projectId = $this->request->getGet('project_id');
        $taskId = $this->request->getGet('task_id');
        $sinceId = $this->request->getGet('since_id');

        if (!$projectId) {
            return $this->failValidationErrors('project_id required');
        }

        if (!$this->checkProjectAccess($projectId)) {
            return $this->failForbidden('You do not have access to this project');
        }

        $model = new MessageModel();
        $messages = $model->getThreadedMessages($projectId, $taskId);

        $user = auth()->user();
        return $this->respond([
            'messages' => $messages,
            'current_user_id' => $user->id,
            'current_username' => $user->username
        ]);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        $data['user_id'] = auth()->id();
        $data['message'] = trim($data['message'] ?? '');

        if (empty($data['message'])) {
            return $this->fail('Message cannot be empty', 400);
        }

        if (!isset($data['project_id'])) {
            return $this->failValidationErrors('project_id required');
        }

        if (!$this->checkProjectAccess($data['project_id'])) {
            return $this->failForbidden('You do not have access to this project');
        }

        if (!$this->model->insert($data)) {
            return $this->failValidationErrors($this->model->errors());
        }

        $messageId = $this->model->getInsertID();
        $message = $this->model->select('messages.*, users.username')
            ->join('users', 'users.id = messages.user_id')
            ->find($messageId);

        return $this->respondCreated([
            'message' => $message,
            'success' => 'Message posted successfully'
        ]);
    }

    public function markRead($id = null)
    {
        $model = new MessageModel();
        $model->markAsRead($id, auth()->id());

        return $this->respond(['message' => 'Message marked as read']);
    }

    public function unreadCount()
    {
        $projectId = $this->request->getGet('project_id');
        
        if (!$projectId) {
            return $this->failValidationErrors('project_id required');
        }

        $model = new MessageModel();
        $count = $model->getUnreadCount($projectId, auth()->id());

        return $this->respond(['count' => $count]);
    }
}
