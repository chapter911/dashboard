<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SeedMstUserGroupFromAttachment extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('mst_user_group')) {
            return;
        }

        $this->db->query(
            "INSERT INTO mst_user_group (group_id, group_name, remark, is_active, created_by, created_date)
             VALUES (1, 'Super Administrator', 'Super Administrator', b'1', 'system', '2025-06-20')
             ON DUPLICATE KEY UPDATE
                group_name = VALUES(group_name),
                remark = VALUES(remark),
                is_active = VALUES(is_active),
                created_by = VALUES(created_by),
                created_date = VALUES(created_date)"
        );

        $this->db->query(
            "INSERT INTO mst_user_group (group_id, group_name, remark, is_active, created_by, created_date)
             VALUES (15, 'Admin', 'Admin', b'1', 'admin', '2025-06-23')
             ON DUPLICATE KEY UPDATE
                group_name = VALUES(group_name),
                remark = VALUES(remark),
                is_active = VALUES(is_active),
                created_by = VALUES(created_by),
                created_date = VALUES(created_date)"
        );
    }

    public function down()
    {
        // No rollback delete to avoid removing critical user group data in production.
    }
}
