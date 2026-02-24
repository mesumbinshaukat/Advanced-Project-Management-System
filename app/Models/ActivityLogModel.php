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
        'metadata',
        'ip_address',
        'user_agent'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
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

    public function logActivity($entityType, $entityId, $action, $description = '', $oldValues = null, $newValues = null, ?int $userIdOverride = null, array $metadata = [])
    {
        $request = \Config\Services::request();
        $userId = $userIdOverride ?? auth()->id() ?? 0;

        $data = [
            'user_id' => $userId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'metadata' => !empty($metadata) ? json_encode($metadata) : null,
            'ip_address' => $request->getIPAddress(),
            'user_agent' => $request->getUserAgent()->getAgentString(),
        ];

        return $this->insert($data);
    }

    public function getDetailedRecentActivity(int $limit = 20, ?int $userId = null)
    {
        $builder = $this->select('activity_logs.*, users.username')
            ->join('users', 'users.id = activity_logs.user_id', 'left')
            ->orderBy('activity_logs.created_at', 'DESC');

        if ($userId !== null) {
            $builder->where('activity_logs.user_id', $userId);
        }

        $results = $builder->findAll($limit);

        foreach ($results as &$row) {
            $row['metadata'] = $row['metadata'] ? json_decode($row['metadata'], true) : [];
            $row['old_values'] = $row['old_values'] ? json_decode($row['old_values'], true) : null;
            $row['new_values'] = $row['new_values'] ? json_decode($row['new_values'], true) : null;
        }

        return $results;
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
