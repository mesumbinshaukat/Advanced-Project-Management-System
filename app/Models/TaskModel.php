<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskModel extends Model
{
    protected $table = 'tasks';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'project_id',
        'title',
        'description',
        'status',
        'priority',
        'assigned_to',
        'estimated_hours',
        'actual_hours',
        'start_date',
        'deadline',
        'completed_at',
        'order_position',
        'created_by',
        'is_blocker',
        'blocker_reason'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'project_id' => 'required|is_natural_no_zero',
        'title' => 'required|min_length[3]|max_length[255]',
        'status' => 'permit_empty|in_list[backlog,todo,in_progress,review,done]',
        'priority' => 'permit_empty|in_list[low,medium,high,urgent]',
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = ['logActivity'];
    protected $beforeUpdate = ['checkCompletion'];
    protected $afterUpdate = ['logActivity', 'updateProjectMetrics'];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = ['logActivity'];

    public function getTasksForUser($userId, $isAdmin = false)
    {
        $builder = $this->select('tasks.*, projects.name as project_name, users.username as assigned_to_name')
            ->join('projects', 'projects.id = tasks.project_id')
            ->join('users', 'users.id = tasks.assigned_to', 'left');

        if (!$isAdmin) {
            $builder->where('tasks.assigned_to', $userId);
        }

        return $builder->findAll();
    }

    public function getTasksByStatus($projectId, $status = null)
    {
        try {
            $builder = $this->select('tasks.*, users.username as assigned_username')
                ->join('users', 'users.id = tasks.assigned_to', 'left')
                ->where('tasks.project_id', $projectId);
            
            if ($status) {
                $builder->where('tasks.status', $status);
            }
            
            return $builder->orderBy('tasks.order_position', 'ASC')->findAll();
        } catch (\Throwable $e) {
            $errorFile = WRITEPATH . 'logs/error_debug.log';
            $errorMsg = date('Y-m-d H:i:s') . ' - TaskModel::getTasksByStatus - ' . get_class($e) . ': ' . $e->getMessage() . "\n";
            $errorMsg .= "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
            $errorMsg .= "Trace:\n" . $e->getTraceAsString() . "\n\n";
            file_put_contents($errorFile, $errorMsg, FILE_APPEND);
            throw $e;
        }
    }

    public function updateTaskStatus($taskId, $status, $orderPosition = null)
    {
        $data = ['status' => $status];
        
        if ($orderPosition !== null) {
            $data['order_position'] = $orderPosition;
        }
        
        if ($status === 'done') {
            $data['completed_at'] = date('Y-m-d H:i:s');
        }
        
        return $this->update($taskId, $data);
    }

    protected function checkCompletion(array $data)
    {
        if (isset($data['data']['status']) && $data['data']['status'] === 'done') {
            $data['data']['completed_at'] = date('Y-m-d H:i:s');
        }
        
        return $data;
    }

    protected function updateProjectMetrics(array $data)
    {
        return $data;
    }

    protected function logActivity(array $data)
    {
        $activityModel = new ActivityLogModel();
        $action = isset($data['id']) ? 'update' : 'create';
        
        if (isset($data['data'])) {
            $activityModel->logActivity(
                'task',
                $data['id'][0] ?? $data['id'] ?? 0,
                $action
            );
        }
        
        return $data;
    }
}
