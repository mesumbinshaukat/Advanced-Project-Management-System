<?php

namespace App\Models;

use CodeIgniter\Model;

class MessageModel extends Model
{
    protected $table = 'messages';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'project_id',
        'task_id',
        'user_id',
        'parent_id',
        'message',
        'is_read',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    protected $validationRules = [
        'project_id' => 'required|integer',
        'message' => 'required',
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;

    protected $afterInsert = ['logActivity'];

    protected function logActivity(array $data)
    {
        $activityModel = new ActivityLogModel();
        
        if (isset($data['result']) && $data['result']) {
            $activityModel->insert([
                'user_id' => auth()->id() ?? 0,
                'entity_type' => 'message',
                'entity_id' => is_array($data['id']) ? $data['id'][0] : $data['id'],
                'action' => 'created',
                'description' => 'Posted message',
                'ip_address' => service('request')->getIPAddress(),
                'user_agent' => service('request')->getUserAgent()->getAgentString(),
            ]);
        }

        return $data;
    }

    public function getProjectMessages($projectId, $taskId = null)
    {
        $builder = $this->select('messages.*, users.username')
            ->join('users', 'users.id = messages.user_id')
            ->where('messages.project_id', $projectId)
            ->where('messages.deleted_at', null);

        if ($taskId) {
            $builder->where('messages.task_id', $taskId);
        } else {
            $builder->where('messages.task_id', null);
        }

        return $builder->orderBy('messages.created_at', 'ASC')->findAll();
    }

    public function getThreadedMessages($projectId, $taskId = null)
    {
        $messages = $this->getProjectMessages($projectId, $taskId);
        
        $threaded = [];
        $lookup = [];

        foreach ($messages as $message) {
            $message['replies'] = [];
            $lookup[$message['id']] = &$message;

            if ($message['parent_id'] === null) {
                $threaded[] = &$message;
            } else {
                if (isset($lookup[$message['parent_id']])) {
                    $lookup[$message['parent_id']]['replies'][] = &$message;
                }
            }
        }

        return $threaded;
    }

    public function markAsRead($messageId, $userId)
    {
        return $this->update($messageId, ['is_read' => 1]);
    }

    public function getUnreadCount($projectId, $userId)
    {
        return $this->where('project_id', $projectId)
            ->where('user_id !=', $userId)
            ->where('is_read', 0)
            ->where('deleted_at', null)
            ->countAllResults();
    }
}
