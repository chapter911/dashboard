<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProfilePhotoToMstUser extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('mst_user')) {
            return;
        }

        if (! $this->db->fieldExists('profile_photo_path', 'mst_user')) {
            $this->forge->addColumn('mst_user', [
                'profile_photo_path' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'after' => 'email',
                ],
            ]);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('mst_user')) {
            return;
        }

        if ($this->db->fieldExists('profile_photo_path', 'mst_user')) {
            $this->forge->dropColumn('mst_user', 'profile_photo_path');
        }
    }
}
