<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BackfillTrnLoginEventType extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('trn_login')) {
            return;
        }

        if (! $this->db->fieldExists('event_type', 'trn_login')) {
            return;
        }

        $this->db->query(
            "UPDATE trn_login
             SET event_type = IF((is_logged_in + 0) = 1, 'LOGIN_SUCCESS', 'LOGOUT')
             WHERE COALESCE(event_type, '') = ''"
        );
    }

    public function down()
    {
        // No-op: data backfill should not be automatically reverted.
    }
}
