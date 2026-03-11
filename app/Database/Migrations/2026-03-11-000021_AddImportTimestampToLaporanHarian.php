<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddImportTimestampToLaporanHarian extends Migration
{
    private const TABLE = 'laporan_harian';
    private const COLUMN = 'tgl_import';

    public function up()
    {
        if (! $this->db->tableExists(self::TABLE)) {
            return;
        }

        if ($this->db->fieldExists(self::COLUMN, self::TABLE)) {
            return;
        }

        $this->db->query(
            'ALTER TABLE ' . self::TABLE
            . ' ADD COLUMN ' . self::COLUMN . ' DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER tgl_rekap'
        );
    }

    public function down()
    {
        if (! $this->db->tableExists(self::TABLE) || ! $this->db->fieldExists(self::COLUMN, self::TABLE)) {
            return;
        }

        $this->db->query('ALTER TABLE ' . self::TABLE . ' DROP COLUMN ' . self::COLUMN);
    }
}
