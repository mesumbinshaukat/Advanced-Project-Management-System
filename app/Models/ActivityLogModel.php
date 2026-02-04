<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    protected $table = 'activity_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'user_id',
        'entity_type',
        'entity_id',
        'action',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
    ];
    protected array $castHandlers = [];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = '';
    protected $deletedField = '';

    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    public function logActivity($entityType, $entityId, $action, $description = '', $oldValues = null, $newValues = null)
    {
        $request = \Config\Services::request();
        $userId = auth()->id() ?? 0;

        $data = [
            'user_id' => $userId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => $request->getIPAddress(),
            'user_agent' => $request->getUserAgent()->getAgentString(),
        ];

        return $this->insert($data);
    }

    public function getRecentActivity($limit = 50, $entityType = null, $entityId = null)
    {
        $builder = $this->select('activity_logs.*, users.username')
            ->join('users', 'users.id = activity_logs.user_id', 'left')
            ->orderBy('activity_logs.created_at', 'DESC')
            ->limit($limit);

        if ($entityType) {
            $builder->where('entity_type', $entityType);
        }

        if ($entityId) {
            $builder->where('entity_id', $entityId);
        }

        return $builder->findAll();
    }
}
