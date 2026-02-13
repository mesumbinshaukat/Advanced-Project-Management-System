<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectTemplateModel extends Model
{
    protected $table = 'project_templates';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'name',
        'description',
        'default_priority',
        'estimated_duration_days',
        'default_budget',
        'task_templates',
        'created_by',
        'is_active',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[255]',
        'default_priority' => 'in_list[low,medium,high,urgent]',
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;

    protected $beforeInsert = ['encodeTaskTemplates'];
    protected $beforeUpdate = ['encodeTaskTemplates'];
    // Note: afterFind callback disabled - decoding handled in controller
    // protected $afterFind = ['decodeTaskTemplates'];

    protected function encodeTaskTemplates(array $data)
    {
        if (isset($data['data']['task_templates']) && is_array($data['data']['task_templates'])) {
            $data['data']['task_templates'] = json_encode($data['data']['task_templates']);
        }
        return $data;
    }

    protected function decodeTaskTemplates(array $data)
    {
        if (!isset($data['data'])) {
            return $data;
        }

        // Handle multiple rows (findAll returns array of rows)
        if (is_array($data['data']) && !empty($data['data']) && !isset($data['data']['id'])) {
            foreach ($data['data'] as &$row) {
                if (is_array($row) && isset($row['task_templates']) && is_string($row['task_templates'])) {
                    $decoded = json_decode($row['task_templates'], true);
                    $row['task_templates'] = is_array($decoded) ? $decoded : [];
                }
            }
            unset($row);
        } 
        // Handle single row (find, first returns single array with 'id' key)
        elseif (is_array($data['data']) && isset($data['data']['id'])) {
            if (isset($data['data']['task_templates']) && is_string($data['data']['task_templates'])) {
                $decoded = json_decode($data['data']['task_templates'], true);
                $data['data']['task_templates'] = is_array($decoded) ? $decoded : [];
            }
        }
        
        return $data;
    }

    public function getActiveTemplates()
    {
        return $this->where('is_active', 1)
            ->where('deleted_at', null)
            ->orderBy('name', 'ASC')
            ->findAll();
    }
}
