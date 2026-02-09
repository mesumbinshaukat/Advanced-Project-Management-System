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
        'user_id',
        'project_id',
        'task_id',
        'content',
        'is_decision',
        'is_pinned',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = '';
    protected $validationRules = [
        'content' => 'required',
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
            $action = 'create';
        } elseif (isset($data['id'])) {
            $action = 'update';
        }

        if ($action && isset($data['id'])) {
            $activityModel->logActivity(
                'note',
                is_array($data['id']) ? $data['id'][0] : $data['id'],
                $action
            );
        }

        return $data;
    }

    public function getProjectNotes($projectId)
    {
        try {
            return $this->select('notes.*, users.username')
                ->join('users', 'users.id = notes.user_id')
                ->where('notes.project_id', $projectId)
                ->orderBy('notes.is_pinned', 'DESC')
                ->orderBy('notes.created_at', 'DESC')
                ->findAll();
        } catch (\Throwable $e) {
            $errorFile = WRITEPATH . 'logs/error_debug.log';
            $errorMsg = date('Y-m-d H:i:s') . ' - NoteModel::getProjectNotes - ' . get_class($e) . ': ' . $e->getMessage() . "\n";
            $errorMsg .= "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
            $errorMsg .= "Trace:\n" . $e->getTraceAsString() . "\n\n";
            file_put_contents($errorFile, $errorMsg, FILE_APPEND);
            throw $e;
        }
    }

    public function getTaskNotes($taskId)
    {
        try {
            return $this->select('notes.*, users.username')
                ->join('users', 'users.id = notes.user_id')
                ->where('notes.task_id', $taskId)
                ->orderBy('notes.is_pinned', 'DESC')
                ->orderBy('notes.created_at', 'DESC')
                ->findAll();
        } catch (\Throwable $e) {
            $errorFile = WRITEPATH . 'logs/error_debug.log';
            $errorMsg = date('Y-m-d H:i:s') . ' - NoteModel::getTaskNotes - ' . get_class($e) . ': ' . $e->getMessage() . "\n";
            $errorMsg .= "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
            $errorMsg .= "Trace:\n" . $e->getTraceAsString() . "\n\n";
            file_put_contents($errorFile, $errorMsg, FILE_APPEND);
            throw $e;
        }
    }
}
