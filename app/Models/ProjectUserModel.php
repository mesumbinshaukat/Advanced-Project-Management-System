<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectUserModel extends Model
{
    protected $table = 'project_users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'project_id',
        'user_id',
        'role',
        'assigned_at'
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

    protected $validationRules = [
        'project_id' => 'required|is_natural_no_zero',
        'user_id' => 'required|is_natural_no_zero',
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = ['logActivity'];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = ['logActivity'];

    public function assignUserToProject($projectId, $userId, $role = 'member')
    {
        $existing = $this->where('project_id', $projectId)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            return $this->update($existing['id'], ['role' => $role]);
        }

        return $this->insert([
            'project_id' => $projectId,
            'user_id' => $userId,
            'role' => $role,
            'assigned_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function removeUserFromProject($projectId, $userId)
    {
        return $this->where('project_id', $projectId)
            ->where('user_id', $userId)
            ->delete();
    }

    public function getProjectUsers($projectId)
    {
        return $this->select('project_users.*, users.username, auth_identities.secret as email')
            ->join('users', 'users.id = project_users.user_id')
            ->join('auth_identities', 'auth_identities.user_id = users.id', 'left')
            ->where('project_id', $projectId)
            ->where('auth_identities.type', 'email_password')
            ->findAll();
    }

    public function isUserAssignedToProject($projectId, $userId)
    {
        return $this->where('project_id', $projectId)
            ->where('user_id', $userId)
            ->countAllResults() > 0;
    }

    protected function logActivity(array $data)
    {
        $activityModel = new ActivityLogModel();
        $action = isset($data['id']) ? 'assigned' : 'removed';
        
        if (isset($data['data'])) {
            $activityModel->logActivity(
                'project_user',
                $data['data']['project_id'] ?? 0,
                $action,
                'User ' . $action . ' to/from project'
            );
        }
        
        return $data;
    }
}
