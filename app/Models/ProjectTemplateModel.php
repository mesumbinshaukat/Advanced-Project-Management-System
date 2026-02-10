<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectTemplateModel extends Model
{
    protected $table = 'project_templates';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'name',
        'description',
        'default_priority',
        'default_budget',
        'task_templates',
        'created_by',
        'is_active',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = '';
    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[255]',
        'default_priority' => 'in_list[low,medium,high,urgent]',
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;

    protected $beforeInsert = ['encodeTaskTemplates'];
    protected $beforeUpdate = ['encodeTaskTemplates'];
    protected $afterFind = ['decodeTaskTemplates'];

    protected function encodeTaskTemplates(array $data)
    {
        if (isset($data['data']['task_templates']) && is_array($data['data']['task_templates'])) {
            $data['data']['task_templates'] = json_encode($data['data']['task_templates']);
        }
        return $data;
    }

    protected function decodeTaskTemplates(array $data)
    {
        if (isset($data['data'])) {
            if (is_array($data['data'])) {
                foreach ($data['data'] as &$row) {
                    if (isset($row['task_templates']) && is_string($row['task_templates'])) {
                        $row['task_templates'] = json_decode($row['task_templates'], true);
                    }
                }
            } else {
                if (isset($data['data']['task_templates']) && is_string($data['data']['task_templates'])) {
                    $data['data']['task_templates'] = json_decode($data['data']['task_templates'], true);
                }
            }
        }
        return $data;
    }

    public function getActiveTemplates()
    {
        return $this->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();
    }
}
