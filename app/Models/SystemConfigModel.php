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
        try {
            $data = base64_decode($data, true);
            if ($data === false) {
                log_message('error', 'SystemConfigModel::decrypt() - base64_decode failed');
                return null;
            }

            $ivLength = openssl_cipher_iv_length('aes-256-cbc');
            if (strlen($data) < $ivLength) {
                log_message('error', 'SystemConfigModel::decrypt() - Data too short to contain IV');
                return null;
            }

            $iv = substr($data, 0, $ivLength);
            $encrypted = substr($data, $ivLength);
            
            $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', $this->encryptionKey, 0, $iv);
            
            if ($decrypted === false) {
                log_message('error', 'SystemConfigModel::decrypt() - openssl_decrypt failed');
                return null;
            }
            
            return $decrypted;
        } catch (\Exception $e) {
            log_message('error', 'SystemConfigModel::decrypt() - Exception: ' . $e->getMessage());
            return null;
        }
    }

    public function setConfig($key, $value)
    {
        $encryptedValue = $this->encrypt($value);
        return $this->replace(['config_key' => $key, 'config_value' => $encryptedValue]);
    }

    public function getConfig($key)
    {
        log_message('debug', 'SystemConfigModel::getConfig() - Fetching config key: ' . $key);
        
        $config = $this->where('config_key', $key)->first();
        if (!$config) {
            log_message('error', 'SystemConfigModel::getConfig() - Config key not found in database: ' . $key);
            return null;
        }

        log_message('debug', 'SystemConfigModel::getConfig() - Config found, attempting decryption');
        
        $decrypted = $this->decrypt($config['config_value']);
        
        if ($decrypted === null) {
            log_message('error', 'SystemConfigModel::getConfig() - Decryption failed for key: ' . $key);
            return null;
        }

        log_message('debug', 'SystemConfigModel::getConfig() - Decryption successful for key: ' . $key);
        return $decrypted;
    }

    public function hasConfig($key)
    {
        return $this->where('config_key', $key)->countAllResults() > 0;
    }
}
