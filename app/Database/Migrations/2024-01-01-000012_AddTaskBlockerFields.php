<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTaskBlockerFields extends Migration
{
    public function up()
    {
        $fields = [
            'is_blocked' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'status',
            ],
            'blocker_reason' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'is_blocked',
            ],
            'tags' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'description',
            ],
        ];
        
        $this->forge->addColumn('tasks', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tasks', ['is_blocked', 'blocker_reason', 'tags']);
    }
}
