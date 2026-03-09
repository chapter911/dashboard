<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class OptimizeP2TLDataPemakaianLookup extends Migration
{
    public function up()
    {
        $this->ensureP2TLAnalisaIndex();
        $this->ensureMasterPelangganLookupIndex();
    }

    public function down()
    {
        if ($this->db->tableExists('trn_p2tl_analisa')) {
            $this->dropIndexIfExists('trn_p2tl_analisa', 'idx_p2tl_analisa_periode_unit_idpel');
        }

        if ($this->db->tableExists('mst_data_induk_langganan')) {
            $this->dropIndexIfExists('mst_data_induk_langganan', 'idx_mst_pelanggan_idpel_bulan_gardu');
        }
    }

    private function ensureP2TLAnalisaIndex(): void
    {
        if (! $this->db->tableExists('trn_p2tl_analisa')) {
            return;
        }

        if (! $this->db->fieldExists('periode', 'trn_p2tl_analisa')
            || ! $this->db->fieldExists('unit_id', 'trn_p2tl_analisa')
            || ! $this->db->fieldExists('idpel', 'trn_p2tl_analisa')) {
            return;
        }

        if ($this->hasIndexByColumns('trn_p2tl_analisa', ['periode', 'unit_id', 'idpel'])) {
            return;
        }

        $this->db->query('CREATE INDEX idx_p2tl_analisa_periode_unit_idpel ON trn_p2tl_analisa (periode, unit_id, idpel)');
    }

    private function ensureMasterPelangganLookupIndex(): void
    {
        if (! $this->db->tableExists('mst_data_induk_langganan')) {
            return;
        }

        if (! $this->db->fieldExists('idpel', 'mst_data_induk_langganan')
            || ! $this->db->fieldExists('v_bulan_rekap', 'mst_data_induk_langganan')
            || ! $this->db->fieldExists('nomor_gardu', 'mst_data_induk_langganan')) {
            return;
        }

        if ($this->hasIndexByColumns('mst_data_induk_langganan', ['idpel', 'v_bulan_rekap', 'nomor_gardu'])) {
            return;
        }

        $this->db->query('CREATE INDEX idx_mst_pelanggan_idpel_bulan_gardu ON mst_data_induk_langganan (idpel, v_bulan_rekap, nomor_gardu)');
    }

    /**
     * @param list<string> $columns
     */
    private function hasIndexByColumns(string $table, array $columns): bool
    {
        $rows = $this->db->query('SHOW INDEX FROM ' . $table)->getResultArray();
        if ($rows === []) {
            return false;
        }

        $target = array_map(static fn(string $col): string => strtolower($col), $columns);
        $indexColumns = [];

        foreach ($rows as $row) {
            $keyName = (string) ($row['Key_name'] ?? '');
            $seq = (int) ($row['Seq_in_index'] ?? 0);
            $columnName = strtolower((string) ($row['Column_name'] ?? ''));

            if ($keyName === '' || $seq < 1 || $columnName === '') {
                continue;
            }

            if (! isset($indexColumns[$keyName])) {
                $indexColumns[$keyName] = [];
            }

            $indexColumns[$keyName][$seq - 1] = $columnName;
        }

        foreach ($indexColumns as $cols) {
            ksort($cols);
            if (array_values($cols) === $target) {
                return true;
            }
        }

        return false;
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        $rows = $this->db->query('SHOW INDEX FROM ' . $table . " WHERE Key_name = '" . $indexName . "'")->getResultArray();
        if ($rows === []) {
            return;
        }

        $this->db->query('DROP INDEX ' . $indexName . ' ON ' . $table);
    }
}
