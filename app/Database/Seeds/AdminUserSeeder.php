<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $email = 'admin@example.com';
        $password = 'admin123';
        
        $db = \Config\Database::connect();
        
        // Check if user exists
        $existingUser = $db->table('users')->where('username', 'admin')->get()->getRow();
        
        if ($existingUser) {
            echo "Admin user exists (ID: {$existingUser->id}). Removing...\n";
            
            // Delete related records
            $db->table('auth_identities')->where('user_id', $existingUser->id)->delete();
            $db->table('auth_groups_users')->where('user_id', $existingUser->id)->delete();
            $db->table('users')->where('id', $existingUser->id)->delete();
            
            echo "✓ Old admin user removed\n";
        }
        
        echo "Creating fresh admin user...\n";
        
        // Create user directly in database
        $db->table('users')->insert([
            'username' => 'admin',
            'active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        $userId = $db->insertID();
        
        // Create identity with proper Shield v1.0.0 structure
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        
        $db->table('auth_identities')->insert([
            'user_id' => $userId,
            'type' => 'email_password',
            'name' => null,
            'secret' => $email,
            'secret2' => $passwordHash,
            'force_reset' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        // Assign admin group
        $db->table('auth_groups_users')->insert([
            'user_id' => $userId,
            'group' => 'admin',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Clear failed login attempts
        $db->table('auth_logins')
            ->where('identifier', $email)
            ->where('success', 0)
            ->delete();
        
        echo "✓ Admin user created (ID: $userId)\n";
        echo "✓ User activated\n";
        echo "✓ Admin group assigned\n";
        
        echo "\n=== Admin User Ready ===\n";
        echo "Email: $email\n";
        echo "Password: $password\n";
        echo "\nNote: If login fails with validation errors, this is a known issue\n";
        echo "with Shield v1.0.0 trying to update identity after authentication.\n";
    }
}
