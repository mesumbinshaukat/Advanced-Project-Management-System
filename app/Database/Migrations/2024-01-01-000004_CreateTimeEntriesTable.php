<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTimeEntriesTable extends Migration
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
            'task_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'hours' => [
                'type' => 'DECIMAL',
                'constraint' => '8,2',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'date' => [
                'type' => 'DATE',
            ],
            'is_billable' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addKey('task_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('date');
        $this->forge->addForeignKey('task_id', 'tasks', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('time_entries');
    }

    public function down()
    {
        $this->forge->dropTable('time_entries');
    }
}
