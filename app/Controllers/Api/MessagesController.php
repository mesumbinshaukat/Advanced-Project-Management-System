<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\MessageModel;

class MessagesController extends ResourceController
{
    protected $modelName = 'App\Models\MessageModel';
    protected $format = 'json';

    public function index()
    {
        $projectId = $this->request->getGet('project_id');
        $taskId = $this->request->getGet('task_id');

        if (!$projectId) {
            return $this->failValidationErrors('project_id required');
        }

        $model = new MessageModel();
        $messages = $model->getThreadedMessages($projectId, $taskId);

        return $this->respond($messages);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        $data['user_id'] = auth()->id();

        if (!$this->model->insert($data)) {
            return $this->failValidationErrors($this->model->errors());
        }

        return $this->respondCreated([
            'id' => $this->model->getInsertID(),
            'message' => 'Message posted successfully'
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
