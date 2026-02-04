<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPerformanceFieldsToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'performance_score' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 0,
                'after' => 'active',
            ],
            'deadline_score' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 0,
                'after' => 'performance_score',
            ],
            'speed_score' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 0,
                'after' => 'deadline_score',
            ],
            'engagement_score' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 0,
                'after' => 'speed_score',
            ],
            'last_check_in' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'engagement_score',
            ],
            'last_activity' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'last_check_in',
            ],
        ];
        
        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['performance_score', 'deadline_score', 'speed_score', 'engagement_score', 'last_check_in', 'last_activity']);
    }
}
