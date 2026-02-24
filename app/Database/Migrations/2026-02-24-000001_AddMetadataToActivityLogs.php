<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMetadataToActivityLogs extends Migration
{
    public function up()
    {
        $fields = [
            'metadata' => [
                'type' => 'JSON',
                'null' => true,
                'after' => 'new_values',
            ],
        ];

        if (! $this->db->fieldExists('metadata', 'activity_logs')) {
            $this->forge->addColumn('activity_logs', $fields);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('metadata', 'activity_logs')) {
            $this->forge->dropColumn('activity_logs', 'metadata');
        }
    }
}
