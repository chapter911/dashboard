<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class InitialSchema extends Migration
{
    public function up()
    {
        $schemaPath = ROOTPATH . 'database/schema/dashboard_tables_only.sql';

        if (! is_file($schemaPath)) {
            throw new RuntimeException('Schema file not found: ' . $schemaPath);
        }

        $sql = file_get_contents($schemaPath);

        if ($sql === false) {
            throw new RuntimeException('Failed to read schema file: ' . $schemaPath);
        }

        // Remove comments to keep statement splitting predictable.
        $sql = preg_replace('/^--.*$/m', '', $sql) ?? $sql;

        $statements = preg_split('/;\s*(\r?\n|$)/', $sql) ?: [];

        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');

        foreach ($statements as $statement) {
            $statement = trim($statement);

            if ($statement === '') {
                continue;
            }

            $this->db->query($statement);
        }

        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function down()
    {
        $tables = [
            'trn_upload_to',
            'trn_tul',
            'trn_target_susut',
            'trn_target_realisasi',
            'trn_target_laporan',
            'trn_susut',
            'trn_saldo_pelanggan',
            'trn_pssd',
            'trn_p2tl_analisa',
            'trn_p2tl',
            'trn_login',
            'trn_kategori_tegangan',
            'trn_hitrate',
            'trn_hari_kerja',
            'trn_emin',
            'trn_analisa_pembelian',
            'mst_user_group',
            'mst_user',
            'mst_unit',
            'mst_jabatan',
            'mst_daya',
            'mst_bulan',
            'menu_lv3',
            'menu_lv2',
            'menu_lv1',
            'menu_akses',
            'laporan_harian',
        ];

        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');

        foreach ($tables as $table) {
            $this->forge->dropTable($table, true);
        }

        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
    }
}
