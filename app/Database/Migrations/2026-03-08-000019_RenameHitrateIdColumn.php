<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameHitrateIdColumn extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('trn_hitrate')) {
            return;
        }

        $hasOld = $this->db->fieldExists('id_p2_tl', 'trn_hitrate');
        $hasNew = $this->db->fieldExists('id_p2tl', 'trn_hitrate');

        if ($hasOld && ! $hasNew) {
            $this->db->query('ALTER TABLE `trn_hitrate` RENAME COLUMN `id_p2_tl` TO `id_p2tl`');
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('trn_hitrate')) {
            return;
        }

        $hasOld = $this->db->fieldExists('id_p2_tl', 'trn_hitrate');
        $hasNew = $this->db->fieldExists('id_p2tl', 'trn_hitrate');

        if ($hasNew && ! $hasOld) {
            $this->db->query('ALTER TABLE `trn_hitrate` RENAME COLUMN `id_p2tl` TO `id_p2_tl`');
        }
    }
}
