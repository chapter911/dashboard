<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSeederRunsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('seeder_runs')) {
            return;
        }

        $this->forge->addField([
            'seeder_class' => [
                'type' => 'VARCHAR',
                'constraint' => 190,
                'null' => false,
            ],
            'file_hash' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'null' => false,
            ],
            'run_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
            ],
            'last_status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
                'default' => 'unknown',
            ],
            'last_output' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'last_run_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_by' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
        ]);

        $this->forge->addKey('seeder_class', true);
        $this->forge->createTable('seeder_runs', true);
    }

    public function down()
    {
        $this->forge->dropTable('seeder_runs', true);
    }
}
