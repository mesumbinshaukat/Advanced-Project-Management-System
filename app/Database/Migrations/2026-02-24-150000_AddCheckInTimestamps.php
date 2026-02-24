<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCheckInTimestamps extends Migration
{
    public function up()
    {
        $fields = [];

        if (!$this->db->fieldExists('checked_in_at', 'daily_check_ins')) {
            $fields['checked_in_at'] = [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'check_in_date',
            ];
        }

        if (!$this->db->fieldExists('checked_out_at', 'daily_check_ins')) {
            $fields['checked_out_at'] = [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'checked_in_at',
            ];
        }

        if (!$this->db->fieldExists('checkout_ready', 'daily_check_ins')) {
            $fields['checkout_ready'] = [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'checked_out_at',
            ];
        }

        if (!empty($fields)) {
            $this->forge->addColumn('daily_check_ins', $fields);
        }

        // Backfill check-in timestamps for existing records so users can check out immediately
        $this->db->query(
            "UPDATE daily_check_ins
             SET checked_in_at = COALESCE(checked_in_at, created_at, CONCAT(check_in_date, ' 09:00:00'))
             WHERE checked_in_at IS NULL"
        );

        // Ensure legacy records keep checkout disabled until the next check-in cycle
        $this->db->query("UPDATE daily_check_ins SET checkout_ready = 0 WHERE checkout_ready IS NULL");
    }

    public function down()
    {
        $fields = ['checkout_ready', 'checked_out_at', 'checked_in_at'];

        foreach ($fields as $field) {
            if ($this->db->fieldExists($field, 'daily_check_ins')) {
                $this->forge->dropColumn('daily_check_ins', $field);
            }
        }
    }
}
