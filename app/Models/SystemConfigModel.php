<?php

namespace App\Models;

use CodeIgniter\Model;

class SystemConfigModel extends Model
{
    protected $table          = 'system_config';
    protected $primaryKey     = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['config_key', 'config_value'];
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    private $encryptionKey;

    public function __construct()
    {
        parent::__construct();
        $this->encryptionKey = '7a3b9c2e8f1d6a5b4c9e2f8a1d7b3c9e2f8a1d7b3c9e2f8a1d7b3c9e2f8a1d7';
    }

    private function encrypt($data)
    {
        $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $this->encryptionKey, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    private function decrypt($data)
    {
        $data = base64_decode($data);
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        return openssl_decrypt($encrypted, 'aes-256-cbc', $this->encryptionKey, 0, $iv);
    }

    public function setConfig($key, $value)
    {
        $encryptedValue = $this->encrypt($value);
        return $this->replace(['config_key' => $key, 'config_value' => $encryptedValue]);
    }

    public function getConfig($key)
    {
        $config = $this->where('config_key', $key)->first();
        if ($config) {
            return $this->decrypt($config['config_value']);
        }
        return null;
    }

    public function hasConfig($key)
    {
        return $this->where('config_key', $key)->countAllResults() > 0;
    }
}
