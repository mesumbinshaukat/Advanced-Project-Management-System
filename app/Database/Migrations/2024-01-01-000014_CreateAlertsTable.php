<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAlertsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'type' => [
                'type' => 'ENUM',
                'constraint' => ['deadline_risk', 'inactivity', 'overload', 'budget_risk', 'performance_drop', 'blocker'],
                'default' => 'deadline_risk',
            ],
            'severity' => [
                'type' => 'ENUM',
                'constraint' => ['low', 'medium', 'high', 'critical'],
                'default' => 'medium',
            ],
            'entity_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'entity_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'message' => [
                'type' => 'TEXT',
            ],
            'action_url' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'is_resolved' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'resolved_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addKey('type');
        $this->forge->addKey('severity');
        $this->forge->addKey('entity_type');
        $this->forge->addKey('entity_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('is_resolved');
        $this->forge->createTable('alerts');
    }

    public function down()
    {
        $this->forge->dropTable('alerts');
    }
}
