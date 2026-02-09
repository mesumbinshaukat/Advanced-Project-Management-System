<?php

namespace App\Models;

use CodeIgniter\Model;

class MessageModel extends Model
{
    protected $table = 'messages';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'project_id',
        'task_id',
        'user_id',
        'parent_id',
        'content',
        'is_read',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = '';
    protected $validationRules = [
        'project_id' => 'required|integer',
        'content' => 'required|min_length[1]|max_length[5000]',
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;

    protected $afterInsert = ['logActivity'];

    protected function logActivity(array $data)
    {
        $activityModel = new ActivityLogModel();
        
        if (isset($data['result']) && $data['result']) {
            $activityModel->logActivity(
                'message',
                is_array($data['id']) ? $data['id'][0] : $data['id'],
                'create'
            );
        }

        return $data;
    }

    public function getProjectMessages($projectId, $taskId = null)
    {
        try {
            $builder = $this->select('messages.*, users.username')
                ->join('users', 'users.id = messages.user_id')
                ->where('messages.project_id', $projectId);

            if ($taskId) {
                $builder->where('messages.task_id', $taskId);
            } else {
                $builder->where('messages.task_id', null);
            }

            return $builder->orderBy('messages.created_at', 'ASC')->findAll();
        } catch (\Throwable $e) {
            $errorFile = WRITEPATH . 'logs/error_debug.log';
            $errorMsg = date('Y-m-d H:i:s') . ' - MessageModel::getProjectMessages - ' . get_class($e) . ': ' . $e->getMessage() . "\n";
            $errorMsg .= "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
            $errorMsg .= "Trace:\n" . $e->getTraceAsString() . "\n\n";
            file_put_contents($errorFile, $errorMsg, FILE_APPEND);
            throw $e;
        }
    }

    public function getThreadedMessages($projectId, $taskId = null)
    {
        try {
            $messages = $this->getProjectMessages($projectId, $taskId);
        
            $threaded = [];
            $lookup = [];

            foreach ($messages as $key => $message) {
                $messages[$key]['replies'] = [];
                $lookup[$message['id']] = $key;

                if ($message['parent_id'] === null) {
                    $threaded[] = $key;
                } else {
                    if (isset($lookup[$message['parent_id']])) {
                        $parentKey = $lookup[$message['parent_id']];
                        $messages[$parentKey]['replies'][] = $key;
                    }
                }
            }

            $result = [];
            foreach ($threaded as $key) {
                $result[] = $this->buildMessageTree($messages, $key);
            }

            return $result;
        } catch (\Throwable $e) {
            $errorFile = WRITEPATH . 'logs/error_debug.log';
            $errorMsg = date('Y-m-d H:i:s') . ' - MessageModel::getThreadedMessages - ' . get_class($e) . ': ' . $e->getMessage() . "\n";
            $errorMsg .= "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
            $errorMsg .= "Trace:\n" . $e->getTraceAsString() . "\n\n";
            file_put_contents($errorFile, $errorMsg, FILE_APPEND);
            throw $e;
        }
    }

    private function buildMessageTree($messages, $key)
    {
        $message = $messages[$key];
        if (!empty($message['replies'])) {
            $replies = [];
            foreach ($message['replies'] as $replyKey) {
                $replies[] = $this->buildMessageTree($messages, $replyKey);
            }
            $message['replies'] = $replies;
        }
        return $message;
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
            ->countAllResults();
    }
}
