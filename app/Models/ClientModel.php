<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientModel extends Model
{
    protected $table = 'clients';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'name',
        'email',
        'phone',
        'company',
        'address',
        'notes',
        'is_active'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'is_active' => 'boolean',
    ];
    protected array $castHandlers = [];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[255]',
        'email' => 'permit_empty|valid_email|max_length[255]',
        'phone' => 'permit_empty|max_length[50]',
        'company' => 'permit_empty|max_length[255]',
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

    protected function logActivity(array $data)
    {
        $activityModel = new ActivityLogModel();
        $action = isset($data['id']) ? 'update' : 'create';
        
        if (isset($data['data'])) {
            $activityModel->logActivity(
                'client',
                $data['id'][0] ?? $data['id'] ?? 0,
                $action,
                'Client ' . $action . 'd'
            );
        }
        
        return $data;
    }
}
