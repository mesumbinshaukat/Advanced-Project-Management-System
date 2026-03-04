<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTaskReviewWorkflow extends Migration
{
    public function up()
    {
        // Check if tasks table exists
        if (!$this->db->tableExists('tasks')) {
            return;
        }

        // Get current status column definition
        $fields = $this->db->getFieldData('tasks');
        $statusField = null;
        
        foreach ($fields as $field) {
            if ($field->name === 'status') {
                $statusField = $field;
                break;
            }
        }

        if ($statusField) {
            // Update status column to include new review workflow statuses
            $this->forge->modifyColumn('tasks', [
                'status' => [
                    'type' => 'ENUM',
                    'constraint' => ['backlog', 'todo', 'in_progress', 'submitted_for_review', 'needs_revision', 'review', 'done'],
                    'default' => 'backlog',
                    'null' => true,
                ]
            ]);
        }

        // Add review-related columns if they don't exist
        $existingColumns = array_column($fields, 'name');
        
        if (!in_array('review_comments', $existingColumns)) {
            $this->forge->addColumn('tasks', [
                'review_comments' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'completed_at'
                ]
            ]);
        }
        
        if (!in_array('reviewed_by', $existingColumns)) {
            $this->forge->addColumn('tasks', [
                'reviewed_by' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'review_comments'
                ]
            ]);
        }
        
        if (!in_array('reviewed_at', $existingColumns)) {
            $this->forge->addColumn('tasks', [
                'reviewed_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'reviewed_by'
                ]
            ]);
        }
        
        if (!in_array('submitted_for_review_at', $existingColumns)) {
            $this->forge->addColumn('tasks', [
                'submitted_for_review_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'reviewed_at'
                ]
            ]);
        }

        // Add foreign key for reviewed_by if it doesn't exist
        if (in_array('reviewed_by', array_column($this->db->getFieldData('tasks'), 'name'))) {
            // Check if foreign key already exists
            $foreignKeys = $this->db->getForeignKeyData('tasks');
            $reviewedByFKExists = false;
            
            foreach ($foreignKeys as $fk) {
                if ($fk->column_name === 'reviewed_by') {
                    $reviewedByFKExists = true;
                    break;
                }
            }
            
            if (!$reviewedByFKExists) {
                $this->forge->addForeignKey('reviewed_by', 'users', 'id', 'SET NULL', 'CASCADE');
            }
        }
    }

    public function down()
    {
        // Remove the added columns
        if ($this->db->tableExists('tasks')) {
            $fields = $this->db->getFieldData('tasks');
            $existingColumns = array_column($fields, 'name');
            
            // Drop foreign key first if it exists
            $foreignKeys = $this->db->getForeignKeyData('tasks');
            foreach ($foreignKeys as $fk) {
                if ($fk->column_name === 'reviewed_by') {
                    $this->forge->dropForeignKey('tasks', $fk->constraint_name);
                    break;
                }
            }
            
            // Remove added columns
            $columnsToRemove = ['review_comments', 'reviewed_by', 'reviewed_at', 'submitted_for_review_at'];
            foreach ($columnsToRemove as $column) {
                if (in_array($column, $existingColumns)) {
                    $this->forge->dropColumn('tasks', $column);
                }
            }
            
            // Revert status column to original values
            $this->forge->modifyColumn('tasks', [
                'status' => [
                    'type' => 'ENUM',
                    'constraint' => ['backlog', 'todo', 'in_progress', 'review', 'done'],
                    'default' => 'backlog',
                    'null' => true,
                ]
            ]);
        }
    }
}
