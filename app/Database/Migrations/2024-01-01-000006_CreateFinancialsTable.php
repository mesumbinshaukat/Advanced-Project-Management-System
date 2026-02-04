<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFinancialsTable extends Migration
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
            'project_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'hourly_rate' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
            ],
            'fixed_price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
            ],
            'total_cost' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
            ],
            'total_revenue' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
            ],
            'profit_margin' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 0,
            ],
            'billing_type' => [
                'type' => 'ENUM',
                'constraint' => ['hourly', 'fixed', 'retainer'],
                'default' => 'hourly',
            ],
            'currency' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'default' => 'USD',
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
        $this->forge->addKey('project_id');
        $this->forge->addForeignKey('project_id', 'projects', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('financials');
    }

    public function down()
    {
        $this->forge->dropTable('financials');
    }
}
