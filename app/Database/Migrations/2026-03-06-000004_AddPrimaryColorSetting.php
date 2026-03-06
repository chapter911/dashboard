<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPrimaryColorSetting extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('app_settings')) {
            return;
        }

        $builder = $this->db->table('app_settings');
        $exists = $builder->where('setting_key', 'app_primary_color')->countAllResults() > 0;

        if (! $exists) {
            $builder->insert([
                'setting_key' => 'app_primary_color',
                'setting_value' => '#0a66c2',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('app_settings')) {
            return;
        }

        $this->db->table('app_settings')->where('setting_key', 'app_primary_color')->delete();
    }
}
