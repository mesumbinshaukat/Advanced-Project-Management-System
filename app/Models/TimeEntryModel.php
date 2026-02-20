<?php

namespace App\Models;

use CodeIgniter\Model;

class TimeEntryModel extends Model
{
    protected $table = 'time_entries';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'task_id',
        'user_id',
        'hours',
        'description',
        'date',
        'is_billable'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'is_billable' => 'boolean',
    ];
    protected array $castHandlers = [];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'task_id' => 'permit_empty|is_natural_no_zero',
        'user_id' => 'required|is_natural_no_zero',
        'hours' => 'required|decimal|greater_than[0]',
        'date' => 'required|valid_date',
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = ['logActivity', 'updateTaskHours'];
    protected $beforeUpdate = [];
    protected $afterUpdate = ['logActivity', 'updateTaskHours'];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = ['logActivity'];

    public function getTimeEntriesForUser($userId, $isAdmin = false, $filters = [])
    {
        $builder = $this->select('time_entries.*, tasks.title as task_title, projects.name as project_name')
            ->join('tasks', 'tasks.id = time_entries.task_id')
            ->join('projects', 'projects.id = tasks.project_id');

        if (!$isAdmin) {
            $builder->where('time_entries.user_id', $userId);
        }

        if (isset($filters['start_date'])) {
            $builder->where('time_entries.date >=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $builder->where('time_entries.date <=', $filters['end_date']);
        }

        if (isset($filters['project_id'])) {
            $builder->where('projects.id', $filters['project_id']);
        }

        return $builder->orderBy('time_entries.date', 'DESC')->findAll();
    }

    public function getTotalHoursByTask($taskId)
    {
        $result = $this->selectSum('hours')
            ->where('task_id', $taskId)
            ->first();
        
        return $result['hours'] ?? 0;
    }

    protected function updateTaskHours(array $data)
    {
        if (isset($data['data']['task_id'])) {
            $taskId = $data['data']['task_id'];
            $totalHours = $this->getTotalHoursByTask($taskId);
            
            $taskModel = new TaskModel();
            $taskModel->update($taskId, ['actual_hours' => $totalHours]);
        }
        
        return $data;
    }

    protected function logActivity(array $data)
    {
        $activityModel = new ActivityLogModel();
        $action = isset($data['id']) ? 'update' : 'create';
        
        if (isset($data['data'])) {
            $activityModel->logActivity(
                'time_entry',
                $data['id'][0] ?? $data['id'] ?? 0,
                $action,
                'Time entry ' . $action . 'd'
            );
        }
        
        return $data;
    }
}
