<?php

namespace App\Models;

use CodeIgniter\Model;

class NoteModel extends Model
{
    protected $table = 'notes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'project_id',
        'task_id',
        'user_id',
        'title',
        'content',
        'type',
        'is_pinned',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $validationRules = [
        'content' => 'required',
        'type' => 'required|in_list[note,decision,blocker,update]',
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;

    protected $afterInsert = ['logActivity'];
    protected $afterUpdate = ['logActivity'];
    protected $afterDelete = ['logActivity'];

    protected function logActivity(array $data)
    {
        $activityModel = new ActivityLogModel();
        $action = '';
        
        if (isset($data['result']) && $data['result']) {
            $action = 'created';
        } elseif (isset($data['id'])) {
            $action = isset($data['data']['deleted_at']) ? 'deleted' : 'updated';
        }

        if ($action && isset($data['id'])) {
            $activityModel->insert([
                'user_id' => auth()->id() ?? 0,
                'entity_type' => 'note',
                'entity_id' => is_array($data['id']) ? $data['id'][0] : $data['id'],
                'action' => $action,
                'description' => ucfirst($action) . ' note',
                'ip_address' => service('request')->getIPAddress(),
                'user_agent' => service('request')->getUserAgent()->getAgentString(),
            ]);
        }

        return $data;
    }

    public function getProjectNotes($projectId, $includeDeleted = false)
    {
        $builder = $this->select('notes.*, users.username')
            ->join('users', 'users.id = notes.user_id')
            ->where('notes.project_id', $projectId)
            ->orderBy('notes.is_pinned', 'DESC')
            ->orderBy('notes.created_at', 'DESC');

        if (!$includeDeleted) {
            $builder->where('notes.deleted_at', null);
        }

        return $builder->findAll();
    }

    public function getTaskNotes($taskId, $includeDeleted = false)
    {
        $builder = $this->select('notes.*, users.username')
            ->join('users', 'users.id = notes.user_id')
            ->where('notes.task_id', $taskId)
            ->orderBy('notes.is_pinned', 'DESC')
            ->orderBy('notes.created_at', 'DESC');

        if (!$includeDeleted) {
            $builder->where('notes.deleted_at', null);
        }

        return $builder->findAll();
    }
}
