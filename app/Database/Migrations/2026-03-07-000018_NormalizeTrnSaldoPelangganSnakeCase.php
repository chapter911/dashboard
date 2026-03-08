<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class NormalizeTrnSaldoPelangganSnakeCase extends Migration
{
    private const TABLE = 'mst_data_induk_langganan';

    /**
     * @var array<string, array{new: string, definition: string}>
     */
    private array $renameMap = [
        'V_BULAN_REKAP' => ['new' => 'v_bulan_rekap', 'definition' => 'INT NOT NULL'],
        'UNITUP' => ['new' => 'unit_up', 'definition' => 'INT NULL DEFAULT NULL'],
        'IDPEL' => ['new' => 'idpel', 'definition' => 'BIGINT NOT NULL'],
        'NAMA' => ['new' => 'nama', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'NAMAPNJ' => ['new' => 'nama_pnj', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'TARIF' => ['new' => 'tarif', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'DAYA' => ['new' => 'daya', 'definition' => 'BIGINT NULL DEFAULT NULL'],
        'KDPT_2' => ['new' => 'kdpt_2', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'THBLMUT' => ['new' => 'thbl_mut', 'definition' => 'BIGINT NULL DEFAULT NULL'],
        'JENIS_MK' => ['new' => 'jenis_mk', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'JENISLAYANAN' => ['new' => 'jenis_layanan', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'FRT' => ['new' => 'frt', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'KOGOL' => ['new' => 'kogol', 'definition' => 'BIGINT NULL DEFAULT NULL'],
        'FKMKWH' => ['new' => 'fkmkwh', 'definition' => 'BIGINT NULL DEFAULT NULL'],
        'NOMOR_METER_KWH' => ['new' => 'nomor_meter_kwh', 'definition' => 'BIGINT NULL DEFAULT NULL'],
        'TANGGAL_PASANG_RUBAH_APP' => ['new' => 'tanggal_pasang_rubah_app', 'definition' => 'BIGINT NULL DEFAULT NULL'],
        'MERK_METER_KWH' => ['new' => 'merk_meter_kwh', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'TYPE_METER_KWH' => ['new' => 'type_meter_kwh', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'TAHUN_TERA_METER_KWH' => ['new' => 'tahun_tera_meter_kwh', 'definition' => 'INT NULL DEFAULT NULL'],
        'TAHUN_BUAT_METER_KWH' => ['new' => 'tahun_buat_meter_kwh', 'definition' => 'INT NULL DEFAULT NULL'],
        'NOMOR_GARDU' => ['new' => 'nomor_gardu', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'NOMOR_JURUSAN_TIANG' => ['new' => 'nomor_jurusan_tiang', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'NAMA_GARDU' => ['new' => 'nama_gardu', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'KAPASITAS_TRAFO' => ['new' => 'kapasitas_trafo', 'definition' => 'BIGINT NULL DEFAULT NULL'],
        'NOMOR_METER_PREPAID' => ['new' => 'nomor_meter_prepaid', 'definition' => 'BIGINT NULL DEFAULT NULL'],
        'PRODUCT' => ['new' => 'product', 'definition' => 'BIGINT NULL DEFAULT NULL'],
        'KOORDINAT_X' => ['new' => 'koordinat_x', 'definition' => 'FLOAT NULL DEFAULT NULL'],
        'KOORDINAT_Y' => ['new' => 'koordinat_y', 'definition' => 'FLOAT NULL DEFAULT NULL'],
        'KDAM' => ['new' => 'kdam', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'KDPEMBMETER' => ['new' => 'kd_pemb_meter', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'KET_KDPEMBMETER' => ['new' => 'ket_kdpembmeter', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'STATUS_DIL' => ['new' => 'status_dil', 'definition' => 'VARCHAR(255) NULL DEFAULT NULL'],
        'KRN' => ['new' => 'krn', 'definition' => 'BIGINT NULL DEFAULT NULL'],
        'VKRN' => ['new' => 'vkrn', 'definition' => 'BIGINT NULL DEFAULT NULL'],
    ];

    public function up()
    {
        if (! $this->db->tableExists(self::TABLE)) {
            return;
        }

        foreach ($this->renameMap as $legacyName => $target) {
            $this->renameColumnSafely($legacyName, $target['new'], $target['definition']);
        }

        $this->ensurePrimaryKey();
    }

    public function down()
    {
        // Tidak di-rollback untuk mencegah konflik nama kolom legacy.
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

        if (isset($columnMap[$newKey]) && $columnMap[$newKey] !== $actualLegacy) {
            return;
        }

        // Rename via temp name untuk menangani case-only rename.
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

    private function ensurePrimaryKey(): void
    {
        if (! $this->db->fieldExists('idpel', self::TABLE) || ! $this->db->fieldExists('v_bulan_rekap', self::TABLE)) {
            return;
        }

        $hasPk = false;
        $rows = $this->db->query('SHOW INDEX FROM ' . self::TABLE . " WHERE Key_name = 'PRIMARY'")->getResultArray();
        if ($rows !== []) {
            $hasPk = true;
        }

        if (! $hasPk) {
            $this->db->query('ALTER TABLE mst_data_induk_langganan ADD PRIMARY KEY (idpel, v_bulan_rekap)');
        }
    }
}
