<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectModel extends Model
{
    protected $table = 'projects';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'client_id',
        'name',
        'description',
        'status',
        'start_date',
        'deadline',
        'priority',
        'created_by'
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
        'client_id' => 'permit_empty|is_natural_no_zero',
        'name' => 'required|min_length[3]|max_length[255]',
        'status' => 'permit_empty|in_list[active,on_hold,completed,archived]',
        'priority' => 'permit_empty|in_list[low,medium,high,urgent]',
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = ['logActivity'];
    protected $beforeUpdate = [];
    protected $afterUpdate = ['logActivity'];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = ['logActivity'];

    public function getProjectsForUser($userId, $isAdmin = false)
    {
        if ($isAdmin) {
            return $this->select('projects.*, clients.name as client_name')
                ->join('clients', 'clients.id = projects.client_id', 'left')
                ->findAll();
        }

        return $this->select('projects.*, clients.name as client_name')
            ->join('clients', 'clients.id = projects.client_id', 'left')
            ->join('project_users', 'project_users.project_id = projects.id')
            ->where('project_users.user_id', $userId)
            ->findAll();
    }

    public function getProjectHealth($projectId)
    {
        $taskModel = new TaskModel();
        $tasks = $taskModel->where('project_id', $projectId)->findAll();
        
        $total = count($tasks);
        $completed = count(array_filter($tasks, fn($t) => $t['status'] === 'done'));
        $overdue = count(array_filter($tasks, function($t) {
            return $t['deadline'] && strtotime($t['deadline']) < time() && $t['status'] !== 'done';
        }));

        return [
            'total_tasks' => $total,
            'completed_tasks' => $completed,
            'overdue_tasks' => $overdue,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
        ];
    }

    protected function logActivity(array $data)
    {
        $activityModel = new ActivityLogModel();
        $action = isset($data['id']) ? 'update' : 'create';
        
        if (isset($data['data'])) {
            $activityModel->logActivity(
                'project',
                $data['id'][0] ?? $data['id'] ?? 0,
                $action,
                'Project ' . $action . 'd'
            );
        }
        
        return $data;
    }
}
