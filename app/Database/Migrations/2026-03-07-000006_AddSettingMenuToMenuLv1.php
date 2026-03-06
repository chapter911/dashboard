<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSettingMenuToMenuLv1 extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('menu_lv1')) {
            return;
        }

        $this->db->query(
            "INSERT INTO menu_lv1 (id, label, link, icon, old_icon, ordering)
             VALUES ('97', 'Setting', 'setting', 'ti-settings', NULL, 97)
             ON DUPLICATE KEY UPDATE
                 label = VALUES(label),
                 link = VALUES(link),
                 icon = VALUES(icon),
                 ordering = VALUES(ordering)"
        );

        if ($this->db->tableExists('menu_akses') && $this->db->tableExists('mst_user_group')) {
            $this->db->query(
                "INSERT IGNORE INTO menu_akses
                    (group_id, menu_id, fitur_add, fitur_edit, fitur_delete, fitur_export, fitur_import, fitur_approval)
                 SELECT group_id, '97', b'1', b'1', b'1', b'1', b'1', b'1'
                 FROM mst_user_group"
            );
        }
    }

    public function down()
    {
        if ($this->db->tableExists('menu_akses')) {
            $this->db->table('menu_akses')->where('menu_id', '97')->delete();
        }

        if ($this->db->tableExists('menu_lv1')) {
            $this->db->table('menu_lv1')->where('id', '97')->delete();
        }
    }
}
