<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserSkillsTable extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('user_skills')) {
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
                'skill' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
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
            $this->forge->addKey('skill');
            $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');

            $this->forge->createTable('user_skills');
        }
    }

    public function down()
    {
        if ($this->db->tableExists('user_skills')) {
            $this->forge->dropTable('user_skills');
        }
    }
}
