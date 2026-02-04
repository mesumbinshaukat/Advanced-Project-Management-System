<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProjectDocumentationFields extends Migration
{
    public function up()
    {
        $fields = [
            'documentation' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'description',
            ],
            'repository_url' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'documentation',
            ],
            'staging_url' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'repository_url',
            ],
            'production_url' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'staging_url',
            ],
            'health_status' => [
                'type' => 'ENUM',
                'constraint' => ['healthy', 'warning', 'critical'],
                'default' => 'healthy',
                'after' => 'status',
            ],
        ];
        
        $this->forge->addColumn('projects', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('projects', ['documentation', 'repository_url', 'staging_url', 'production_url', 'health_status']);
    }
}
