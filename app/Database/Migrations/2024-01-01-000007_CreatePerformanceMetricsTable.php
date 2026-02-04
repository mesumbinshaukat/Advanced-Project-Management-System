<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePerformanceMetricsTable extends Migration
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
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'project_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'metric_date' => [
                'type' => 'DATE',
            ],
            'tasks_completed' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'hours_logged' => [
                'type' => 'DECIMAL',
                'constraint' => '8,2',
                'default' => 0,
            ],
            'efficiency_score' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 0,
            ],
            'quality_score' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 0,
            ],
            'on_time_delivery' => [
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
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('project_id');
        $this->forge->addKey('metric_date');
        $this->forge->createTable('performance_metrics');
    }

    public function down()
    {
        $this->forge->dropTable('performance_metrics');
    }
}
