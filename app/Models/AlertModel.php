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
        'type' => 'required|in_list[deadline_risk,inactivity,overload,budget_risk,performance_drop,blocker,project_assignment,task_assignment,task_update,credential_added,task_review_request,task_review_completed]',
        'severity' => 'required|in_list[low,medium,high,critical,info]',
        'entity_type' => 'required',
        'entity_id' => 'required|integer',
        'title' => 'required',
        'message' => 'required',
    ];
    protected $validationMessages = [];
    protected $skipValidation = true;

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
        try {
            // Only include fields that are in allowedFields
            $filteredData = array_intersect_key($data, array_flip($this->allowedFields));
            
            $existing = $this->where('entity_type', $filteredData['entity_type'] ?? null)
                ->where('entity_id', $filteredData['entity_id'] ?? null)
                ->where('type', $filteredData['type'] ?? null);
            
            // Only add is_resolved check if the column exists
            try {
                $existing->where('is_resolved', 0);
            } catch (\Exception $e) {
                // Column doesn't exist yet, skip this condition
            }
            
            $existingAlert = $existing->first();

            if ($existingAlert) {
                return $this->update($existingAlert['id'], $filteredData);
            }

            return $this->insert($filteredData);
        } catch (\Exception $e) {
            log_message('error', 'Error creating/updating alert: ' . $e->getMessage());
            return false;
        }
    }
}
