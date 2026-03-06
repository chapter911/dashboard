<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSettingSubmenus extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('menu_lv2')) {
            return;
        }

        $this->db->query(
            "INSERT INTO menu_lv2 (id, label, link, icon, header, ordering)
             VALUES
                ('97-01', 'Application', 'setting/application', 'ti-settings', '97', 1),
                ('97-02', 'Menu', 'setting/menu', 'ti-menu-2', '97', 2)
             ON DUPLICATE KEY UPDATE
                label = VALUES(label),
                link = VALUES(link),
                icon = VALUES(icon),
                header = VALUES(header),
                ordering = VALUES(ordering)"
        );

        if ($this->db->tableExists('menu_akses')) {
            $this->db->query(
                "INSERT IGNORE INTO menu_akses
                    (group_id, menu_id, fitur_add, fitur_edit, fitur_delete, fitur_export, fitur_import, fitur_approval)
                 SELECT group_id, '97-01', fitur_add, fitur_edit, fitur_delete, fitur_export, fitur_import, fitur_approval
                 FROM menu_akses
                 WHERE menu_id = '97'"
            );

            $this->db->query(
                "INSERT IGNORE INTO menu_akses
                    (group_id, menu_id, fitur_add, fitur_edit, fitur_delete, fitur_export, fitur_import, fitur_approval)
                 SELECT group_id, '97-02', fitur_add, fitur_edit, fitur_delete, fitur_export, fitur_import, fitur_approval
                 FROM menu_akses
                 WHERE menu_id = '97'"
            );
        }
    }

    public function down()
    {
        if ($this->db->tableExists('menu_akses')) {
            $this->db->table('menu_akses')->whereIn('menu_id', ['97-01', '97-02'])->delete();
        }

        if ($this->db->tableExists('menu_lv2')) {
            $this->db->table('menu_lv2')->whereIn('id', ['97-01', '97-02'])->delete();
        }
    }
}
