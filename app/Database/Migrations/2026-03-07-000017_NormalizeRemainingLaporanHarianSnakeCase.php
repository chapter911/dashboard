<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class NormalizeRemainingLaporanHarianSnakeCase extends Migration
{
    private const TABLE = 'laporan_harian';

    /**
     * @var array<string, array{new: string, definition: string}>
     */
    private array $renameMap = [
        'ID' => ['new' => 'id', 'definition' => 'BIGINT NOT NULL AUTO_INCREMENT'],
        'IDPEL' => ['new' => 'idpel', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'NAMA' => ['new' => 'nama', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'ALAMAT' => ['new' => 'alamat', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'KDDK' => ['new' => 'kddk', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'NAMA_PROV' => ['new' => 'nama_prov', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'NAMA_KAB' => ['new' => 'nama_kab', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'NAMA_KEC' => ['new' => 'nama_kec', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'NAMA_KEL' => ['new' => 'nama_kel', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'TARIF' => ['new' => 'tarif', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'DAYA' => ['new' => 'daya', 'definition' => 'INT NULL DEFAULT NULL'],
        'KDPT' => ['new' => 'kdpt', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'KDPT_2' => ['new' => 'kdpt_2', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'JENIS_MK' => ['new' => 'jenis_mk', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'RP_TOKEN' => ['new' => 'rp_token', 'definition' => 'BIGINT NULL DEFAULT NULL'],
        'RPTOTAL' => ['new' => 'rptotal', 'definition' => 'BIGINT NULL DEFAULT NULL'],
        'STATUS_PERMOHONAN' => ['new' => 'status_permohonan', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'ID_GANTI_METER' => ['new' => 'id_ganti_meter', 'definition' => 'INT NULL DEFAULT NULL'],
        'ALASAN_GANTI_METER' => ['new' => 'alasan_ganti_meter', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'ALASAN_PENANGGUHAN' => ['new' => 'alasan_penangguhan', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'KETERANGAN_ALASAN_PENANGGUHAN' => ['new' => 'keterangan_alasan_penangguhan', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'NO_METER_BARU' => ['new' => 'no_meter_baru', 'definition' => 'BIGINT NULL DEFAULT NULL'],
        'MERK_METER_BARU' => ['new' => 'merk_meter_baru', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'TYPE_METER_BARU' => ['new' => 'type_meter_baru', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'THTERA_METER_BARU' => ['new' => 'thtera_meter_baru', 'definition' => 'INT NULL DEFAULT NULL'],
        'THBUAT_METER_BARU' => ['new' => 'thbuat_meter_baru', 'definition' => 'INT NULL DEFAULT NULL'],
        'NO_METER_LAMA' => ['new' => 'no_meter_lama', 'definition' => 'BIGINT NULL DEFAULT NULL'],
        'MERK_METER_LAMA' => ['new' => 'merk_meter_lama', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'TYPE_METER_LAMA' => ['new' => 'type_meter_lama', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'THTERA_METER_LAMA' => ['new' => 'thtera_meter_lama', 'definition' => 'INT NULL DEFAULT NULL'],
        'THBUAT_METER_LAMA' => ['new' => 'thbuat_meter_lama', 'definition' => 'INT NULL DEFAULT NULL'],
        'CT_PRIMER_KWH' => ['new' => 'ct_primer_kwh', 'definition' => 'BIGINT NULL DEFAULT NULL'],
        'CT_SEKUNDER_KWH' => ['new' => 'ct_sekunder_kwh', 'definition' => 'BIGINT NULL DEFAULT NULL'],
        'PT_PRIMER_KWH' => ['new' => 'pt_primer_kwh', 'definition' => 'BIGINT NULL DEFAULT NULL'],
        'PT_SEKUNDER_KWH' => ['new' => 'pt_sekunder_kwh', 'definition' => 'BIGINT NULL DEFAULT NULL'],
        'KONSTANTA_KWH' => ['new' => 'konstanta_kwh', 'definition' => 'BIGINT NULL DEFAULT NULL'],
        'TYPE_CT_KWH' => ['new' => 'type_ct_kwh', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'CT_PRIMER_KVARH' => ['new' => 'ct_primer_kvarh', 'definition' => 'BIGINT NULL DEFAULT NULL'],
        'CT_SEKUNDER_KVARH' => ['new' => 'ct_sekunder_kvarh', 'definition' => 'BIGINT NULL DEFAULT NULL'],
        'PT_PRIMER_KVARH' => ['new' => 'pt_primer_kvarh', 'definition' => 'BIGINT NULL DEFAULT NULL'],
        'PT_SEKUNDER_KVARH' => ['new' => 'pt_sekunder_kvarh', 'definition' => 'BIGINT NULL DEFAULT NULL'],
        'KONSTANTA_KVARH' => ['new' => 'konstanta_kvarh', 'definition' => 'BIGINT NULL DEFAULT NULL'],
        'TYPE_CT_KVARH' => ['new' => 'type_ct_kvarh', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
    ];

    public function up()
    {
        if (! $this->db->tableExists(self::TABLE)) {
            return;
        }

        foreach ($this->renameMap as $legacyName => $target) {
            $this->renameColumnSafely($legacyName, $target['new'], $target['definition']);
        }

        $this->ensureIndexes();
    }

    public function down()
    {
        // Tidak di-rollback untuk menghindari konflik nama kolom lama.
    }

    private function renameColumnSafely(string $legacyName, string $newName, string $definition): void
    {
        $columnMap = $this->getCurrentColumnMap();
        $legacyKey = strtolower($legacyName);
        $newKey = strtolower($newName);

        if (! isset($columnMap[$legacyKey])) {
            return;
        }

        $actualLegacy = $columnMap[$legacyKey];

        if ($actualLegacy === $newName) {
            return;
        }

        // Jika nama tujuan sudah ada, tidak boleh rename agar tidak bentrok.
        if (isset($columnMap[$newKey]) && $columnMap[$newKey] !== $actualLegacy) {
            return;
        }

        // Rename via temporary column untuk memastikan rename case-only tetap jalan.
        $tmpName = 'tmp_' . substr(md5($actualLegacy . '_' . $newName), 0, 16);

        $sqlToTmp = sprintf(
            'ALTER TABLE %s CHANGE COLUMN `%s` `%s` %s',
            self::TABLE,
            $actualLegacy,
            $tmpName,
            $definition
        );
        $this->db->query($sqlToTmp);

        $sqlToFinal = sprintf(
            'ALTER TABLE %s CHANGE COLUMN `%s` `%s` %s',
            self::TABLE,
            $tmpName,
            $newName,
            $definition
        );
        $this->db->query($sqlToFinal);
    }

    /**
     * @return array<string, string>
     */
    private function getCurrentColumnMap(): array
    {
        $rows = $this->db->query('SHOW COLUMNS FROM ' . self::TABLE)->getResultArray();
        $map = [];

        foreach ($rows as $row) {
            $name = (string) ($row['Field'] ?? '');
            if ($name === '') {
                continue;
            }

            $map[strtolower($name)] = $name;
        }

        return $map;
    }

    private function ensureIndexes(): void
    {
        $indexes = $this->db->getIndexData(self::TABLE);

        if (! isset($indexes['idx_laporan_harian_idpel']) && $this->db->fieldExists('idpel', self::TABLE)) {
            $this->db->query('CREATE INDEX idx_laporan_harian_idpel ON laporan_harian(idpel)');
        }

        if (! isset($indexes['idx_laporan_harian_tglrekap_unitup'])
            && $this->db->fieldExists('tgl_rekap', self::TABLE)
            && $this->db->fieldExists('unit_up', self::TABLE)
        ) {
            $this->db->query('CREATE INDEX idx_laporan_harian_tglrekap_unitup ON laporan_harian(tgl_rekap, unit_up)');
        }
    }
}
