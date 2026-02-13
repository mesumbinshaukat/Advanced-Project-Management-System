<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskTemplateModel extends Model
{
    protected $table = 'task_templates';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'name',
        'description',
        'default_priority',
        'estimated_hours',
        'checklist_items',
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

    protected $beforeInsert = ['encodeChecklist'];
    protected $beforeUpdate = ['encodeChecklist'];
    // Note: afterFind callback disabled - decoding handled in controller
    // protected $afterFind = ['decodeChecklist'];

    protected function encodeChecklist(array $data)
    {
        if (isset($data['data']['checklist_items']) && is_array($data['data']['checklist_items'])) {
            $data['data']['checklist_items'] = json_encode($data['data']['checklist_items']);
        }
        return $data;
    }

    protected function decodeChecklist(array $data)
    {
        if (!isset($data['data'])) {
            return $data;
        }

        // Handle multiple rows (findAll returns array of rows)
        if (is_array($data['data']) && !empty($data['data']) && !isset($data['data']['id'])) {
            foreach ($data['data'] as &$row) {
                if (is_array($row) && isset($row['checklist_items']) && is_string($row['checklist_items'])) {
                    $decoded = json_decode($row['checklist_items'], true);
                    $row['checklist_items'] = is_array($decoded) ? $decoded : [];
                }
            }
            unset($row);
        }
        // Handle single row (find, first returns single array with 'id' key)
        elseif (is_array($data['data']) && isset($data['data']['id'])) {
            if (isset($data['data']['checklist_items']) && is_string($data['data']['checklist_items'])) {
                $decoded = json_decode($data['data']['checklist_items'], true);
                $data['data']['checklist_items'] = is_array($decoded) ? $decoded : [];
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
