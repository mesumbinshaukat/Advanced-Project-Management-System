<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDailyCheckInsTable extends Migration
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
            'check_in_date' => [
                'type' => 'DATE',
            ],
            'mood' => [
                'type' => 'ENUM',
                'constraint' => ['great', 'good', 'okay', 'struggling', 'blocked'],
                'default' => 'okay',
            ],
            'yesterday_accomplishments' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'today_plan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'blockers' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'needs_help' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
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
        $this->forge->addKey('check_in_date');
        $this->forge->addKey(['user_id', 'check_in_date'], false, true);
        $this->forge->createTable('daily_check_ins');
    }

    public function down()
    {
        $this->forge->dropTable('daily_check_ins');
    }
}
