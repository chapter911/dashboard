<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAppSettings extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'setting_key' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'setting_value' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'updated_by' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('setting_key', true);
        $this->forge->createTable('app_settings', true);

        $builder = $this->db->table('app_settings');
        $builder->insertBatch([
            [
                'setting_key' => 'app_name',
                'setting_value' => 'Dashboard PLN',
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'setting_key' => 'app_logo_path',
                'setting_value' => null,
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'setting_key' => 'login_background_path',
                'setting_value' => null,
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('app_settings', true);
    }
}
