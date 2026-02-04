<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RolesPermissionsSeeder extends Seeder
{
    public function run()
    {
        echo "Roles and permissions are configured in app/Config/AuthGroups.php\n";
        echo "Groups: admin, developer\n";
        echo "Seeder completed successfully.\n";
    }
}
