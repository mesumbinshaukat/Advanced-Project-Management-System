<?php

namespace App\Models;

use CodeIgniter\Shield\Models\UserIdentityModel as ShieldUserIdentityModel;

/**
 * Fixed UserIdentityModel that handles null validation object
 * This fixes a bug in Shield v1.0.0 where validation is null during identity updates
 */
class UserIdentityModelFixed extends ShieldUserIdentityModel
{
    /**
     * Override update to handle null validation gracefully
     */
    public function update($id = null, $data = null): bool
    {
        // Initialize validation if it's null
        if ($this->validation === null) {
            $this->validation = \Config\Services::validation();
        }
        
        return parent::update($id, $data);
    }
}
