<?php

namespace App\Models;

use CodeIgniter\Model;

class AlertModel extends Model
{
    protected $table = 'alerts';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'type',
        'severity',
        'entity_type',
        'entity_id',
        'user_id',
        'title',
        'message',
        'action_url',
        'is_resolved',
        'resolved_at',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $validationRules = [
        'type' => 'required|in_list[deadline_risk,inactivity,overload,budget_risk,performance_drop,blocker]',
        'severity' => 'required|in_list[low,medium,high,critical]',
        'entity_type' => 'required',
        'entity_id' => 'required|integer',
        'title' => 'required',
        'message' => 'required',
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;

    public function getActiveAlerts($severity = null)
    {
        $builder = $this->where('is_resolved', 0);
        
        if ($severity) {
            $builder->where('severity', $severity);
        }
        
        return $builder->orderBy('severity', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function getAlertsByType($type)
    {
        return $this->where('type', $type)
            ->where('is_resolved', 0)
            ->orderBy('severity', 'DESC')
            ->findAll();
    }

    public function getUserAlerts($userId)
    {
        return $this->where('user_id', $userId)
            ->where('is_resolved', 0)
            ->orderBy('severity', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function resolveAlert($alertId)
    {
        return $this->update($alertId, [
            'is_resolved' => 1,
            'resolved_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function createOrUpdateAlert($data)
    {
        $existing = $this->where('entity_type', $data['entity_type'])
            ->where('entity_id', $data['entity_id'])
            ->where('type', $data['type'])
            ->where('is_resolved', 0)
            ->first();

        if ($existing) {
            return $this->update($existing['id'], $data);
        }

        return $this->insert($data);
    }
}
