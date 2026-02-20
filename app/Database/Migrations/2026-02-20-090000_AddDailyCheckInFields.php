<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDailyCheckInFields extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('yesterday_accomplishments', 'daily_check_ins')) {
            $this->forge->addColumn('daily_check_ins', [
                'yesterday_accomplishments' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'mood',
                ],
            ]);
        }

        if (!$this->db->fieldExists('today_plan', 'daily_check_ins')) {
            $this->forge->addColumn('daily_check_ins', [
                'today_plan' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'yesterday_accomplishments',
                ],
            ]);
        }

        if (!$this->db->fieldExists('blockers', 'daily_check_ins')) {
            $this->forge->addColumn('daily_check_ins', [
                'blockers' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'today_plan',
                ],
            ]);
        }

        if (!$this->db->fieldExists('needs_help', 'daily_check_ins')) {
            $this->forge->addColumn('daily_check_ins', [
                'needs_help' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'after' => 'blockers',
                ],
            ]);
        }
    }

    public function down()
    {
        $fields = ['needs_help', 'blockers', 'today_plan', 'yesterday_accomplishments'];
        foreach ($fields as $field) {
            if ($this->db->fieldExists($field, 'daily_check_ins')) {
                $this->forge->dropColumn('daily_check_ins', $field);
            }
        }
    }
}
