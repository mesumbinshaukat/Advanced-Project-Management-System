<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateProjectsClientAndBudget extends Migration
{
    public function up()
    {
        $this->db->transStart();

        $this->dropClientForeignKey();

        $this->forge->modifyColumn('projects', [
            'client_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
        ]);

        if ($this->db->fieldExists('budget', 'projects')) {
            $this->forge->dropColumn('projects', 'budget');
        }

        $this->db->query('ALTER TABLE `projects` ADD CONSTRAINT `projects_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE SET NULL ON UPDATE CASCADE');

        $this->db->transComplete();
    }

    public function down()
    {
        $this->db->transStart();

        $this->dropClientForeignKey();

        $this->forge->modifyColumn('projects', [
            'client_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
        ]);

        if (!$this->db->fieldExists('budget', 'projects')) {
            $this->forge->addColumn('projects', [
                'budget' => [
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'null' => true,
                    'after' => 'deadline',
                ],
            ]);
        }

        $this->db->query('ALTER TABLE `projects` ADD CONSTRAINT `projects_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE ON UPDATE CASCADE');

        $this->db->transComplete();
    }

    private function dropClientForeignKey(): void
    {
        $foreignKeys = $this->db->getForeignKeyData('projects');
        foreach ($foreignKeys as $foreignKey) {
            if ($foreignKey->column_name === 'client_id') {
                $this->db->query("ALTER TABLE `projects` DROP FOREIGN KEY `{$foreignKey->constraint_name}`");
                break;
            }
        }
    }
}
