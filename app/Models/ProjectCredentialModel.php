<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectCredentialModel extends Model
{
    protected $table = 'project_credentials';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $allowedFields = [
        'project_id',
        'credential_type',
        'label',
        'username',
        'password',
        'email',
        'api_key',
        'api_secret',
        'url',
        'notes',
        'created_by',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'project_id' => 'required|is_natural_no_zero',
        'credential_type' => 'required|string',
        'label' => 'required|string|max_length[255]',
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert = ['encryptSensitiveFields'];
    protected $afterInsert = ['logActivity'];
    protected $beforeUpdate = ['encryptSensitiveFields'];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = ['decryptSensitiveFields'];
    protected $beforeDelete = [];
    protected $afterDelete = ['logActivity'];

    private $encryptionKey = null;

    public function __construct()
    {
        parent::__construct();
        $this->encryptionKey = getenv('ENCRYPTION_KEY') ?: 'default-encryption-key-change-in-production';
    }

    protected function encryptSensitiveFields(array $data)
    {
        if (isset($data['data'])) {
            $fields = ['password', 'api_key', 'api_secret'];
            foreach ($fields as $field) {
                if (isset($data['data'][$field]) && !empty($data['data'][$field])) {
                    $data['data'][$field] = $this->encryptData($data['data'][$field]);
                }
            }
        }
        return $data;
    }

    protected function decryptSensitiveFields(array $data)
    {
        if (isset($data['data'])) {
            $fields = ['password', 'api_key', 'api_secret'];
            foreach ($fields as $field) {
                if (isset($data['data'][$field]) && !empty($data['data'][$field])) {
                    $data['data'][$field] = $this->decryptData($data['data'][$field]);
                }
            }
        }
        return $data;
    }

    public function encryptData($data)
    {
        if (empty($data)) {
            return $data;
        }
        
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $this->encryptionKey, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    public function decryptData($data)
    {
        if (empty($data)) {
            return $data;
        }
        
        try {
            $data = base64_decode($data);
            $iv = substr($data, 0, openssl_cipher_iv_length('aes-256-cbc'));
            $encrypted = substr($data, openssl_cipher_iv_length('aes-256-cbc'));
            return openssl_decrypt($encrypted, 'aes-256-cbc', $this->encryptionKey, 0, $iv);
        } catch (\Exception $e) {
            return '';
        }
    }

    public function getProjectCredentials($projectId)
    {
        $credentials = $this->where('project_id', $projectId)
            ->select('project_credentials.*, users.username as created_by_username')
            ->join('users', 'users.id = project_credentials.created_by', 'left')
            ->orderBy('project_credentials.credential_type', 'ASC')
            ->orderBy('project_credentials.label', 'ASC')
            ->findAll();
        
        // Manually decrypt sensitive fields since afterFind callback doesn't work with joins
        foreach ($credentials as &$credential) {
            $fields = ['password', 'api_key', 'api_secret'];
            foreach ($fields as $field) {
                if (isset($credential[$field]) && !empty($credential[$field])) {
                    $credential[$field] = $this->decryptData($credential[$field]);
                }
            }
        }
        
        return $credentials;
    }

    public function getCredentialById($id, $projectId)
    {
        return $this->where('id', $id)
            ->where('project_id', $projectId)
            ->first();
    }

    public function addCredential($projectId, $data)
    {
        $data['project_id'] = $projectId;
        $data['created_by'] = auth()->id();
        
        return $this->insert($data);
    }

    public function updateCredential($id, $projectId, $data)
    {
        $credential = $this->getCredentialById($id, $projectId);
        
        if (!$credential) {
            return false;
        }
        
        return $this->update($id, $data);
    }

    public function deleteCredential($id, $projectId)
    {
        return $this->where('id', $id)
            ->where('project_id', $projectId)
            ->delete();
    }

    protected function logActivity(array $data)
    {
        $activityModel = new ActivityLogModel();
        $action = isset($data['id']) ? 'created' : 'deleted';
        
        if (isset($data['data'])) {
            $activityModel->logActivity(
                'project_credential',
                $data['data']['project_id'] ?? 0,
                $action,
                'Project credential ' . $action
            );
        }
        
        return $data;
    }
}
