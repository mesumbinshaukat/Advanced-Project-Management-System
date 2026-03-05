<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTaskSubmissionChecklist extends Migration
{
    public function up()
    {
        // Create task_submission_checklists table
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
            'is_responsive' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'no_ai_generated_text' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'all_links_working' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'code_reviewed' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'functionality_tested' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'cross_browser_tested' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'additional_notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'submitted_at' => [
                'type' => 'DATETIME',
                'null' => false,
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
        $this->forge->addKey('task_id');
        $this->forge->addKey('user_id');
        
        // Add foreign keys
        $this->forge->addForeignKey('task_id', 'tasks', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('task_submission_checklists');
    }

    public function down()
    {
        $this->forge->dropTable('task_submission_checklists');
    }
}
