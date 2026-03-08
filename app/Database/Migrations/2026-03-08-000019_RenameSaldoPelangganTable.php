<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameSaldoPelangganTable extends Migration
{
    private const OLD_TABLE = 'trn_saldo_pelanggan';
    private const NEW_TABLE = 'mst_data_induk_langganan';

    public function up()
    {
        $oldExists = $this->db->tableExists(self::OLD_TABLE);
        $newExists = $this->db->tableExists(self::NEW_TABLE);

        if ($oldExists && ! $newExists) {
            $this->forge->renameTable(self::OLD_TABLE, self::NEW_TABLE);
        }
    }

    public function down()
    {
        $oldExists = $this->db->tableExists(self::OLD_TABLE);
        $newExists = $this->db->tableExists(self::NEW_TABLE);

        if (! $oldExists && $newExists) {
            $this->forge->renameTable(self::NEW_TABLE, self::OLD_TABLE);
        }
    }
}
