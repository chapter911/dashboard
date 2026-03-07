<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ForceNormalizeLaporanHarianColumns extends Migration
{
    private const TABLE = 'laporan_harian';

    /**
     * @var array<string, string>
     */
    private array $renameMap = [
        'NOAGENDA' => 'no_agenda',
        'UNITUPI' => 'unit_upi',
        'UNITAP' => 'unit_ap',
        'UNITUP' => 'unit_up',
        'NOMORPDL' => 'nomor_pdl',
        'TGLPENGADUAN' => 'tgl_pengaduan',
        'TGLTINDAKANPENGADUAN' => 'tgl_tindakan_pengaduan',
        'TGLBAYAR' => 'tgl_bayar',
        'TGLAKTIVASI' => 'tgl_aktivasi',
        'TGLPENANGGUHAN' => 'tgl_penangguhan',
        'TGLRESTITUSI' => 'tgl_restitusi',
        'TGLREMAJA' => 'tgl_remaja',
        'TGLNYALA' => 'tgl_nyala',
        'TGLBATAL' => 'tgl_batal',
        'PETUGASPENGADUAN' => 'petugas_pengaduan',
        'PETUGASTINDAKANPENGADUAN' => 'petugas_tindakan_pengaduan',
        'PETUGASAKTIVASI' => 'petugas_aktivasi',
        'PETUGASPENANGGUHAN' => 'petugas_penangguhan',
        'PETUGASRESTITUSI' => 'petugas_restitusi',
        'PETUGASREMAJA' => 'petugas_remaja',
        'PETUGASBATAL' => 'petugas_batal',
        'TGLREKAP' => 'tgl_rekap',
        'KDPEMBMETER' => 'kd_pemb_meter',
        'FAKMKWH' => 'fakm_kwh',
        'FAKMKVARH' => 'fakm_kvarh',
    ];

    /**
     * Definisi kolom saat di-rename.
     *
     * @var array<string, string>
     */
    private array $columnDefinitions = [
        'NOAGENDA' => 'BIGINT NULL DEFAULT NULL',
        'UNITUPI' => 'INT NULL DEFAULT NULL',
        'UNITAP' => 'INT NULL DEFAULT NULL',
        'UNITUP' => 'INT NULL DEFAULT NULL',
        'NOMORPDL' => 'INT NULL DEFAULT NULL',
        'TGLPENGADUAN' => 'DATE NULL DEFAULT NULL',
        'TGLTINDAKANPENGADUAN' => 'DATE NULL DEFAULT NULL',
        'TGLBAYAR' => 'DATE NULL DEFAULT NULL',
        'TGLAKTIVASI' => 'DATE NULL DEFAULT NULL',
        'TGLPENANGGUHAN' => 'DATE NULL DEFAULT NULL',
        'TGLRESTITUSI' => 'DATE NULL DEFAULT NULL',
        'TGLREMAJA' => 'DATE NULL DEFAULT NULL',
        'TGLNYALA' => 'DATE NULL DEFAULT NULL',
        'TGLBATAL' => 'DATE NULL DEFAULT NULL',
        'PETUGASPENGADUAN' => 'VARCHAR(255) NULL DEFAULT NULL',
        'PETUGASTINDAKANPENGADUAN' => 'VARCHAR(255) NULL DEFAULT NULL',
        'PETUGASAKTIVASI' => 'VARCHAR(255) NULL DEFAULT NULL',
        'PETUGASPENANGGUHAN' => 'VARCHAR(255) NULL DEFAULT NULL',
        'PETUGASRESTITUSI' => 'VARCHAR(255) NULL DEFAULT NULL',
        'PETUGASREMAJA' => 'VARCHAR(255) NULL DEFAULT NULL',
        'PETUGASBATAL' => 'VARCHAR(255) NULL DEFAULT NULL',
        'TGLREKAP' => 'DATE NULL DEFAULT NULL',
        'KDPEMBMETER' => 'VARCHAR(255) NULL DEFAULT NULL',
        'FAKMKWH' => 'BIGINT NULL DEFAULT NULL',
        'FAKMKVARH' => 'BIGINT NULL DEFAULT NULL',
    ];

    public function up()
    {
        if (! $this->db->tableExists(self::TABLE)) {
            return;
        }

        $columnMap = $this->getCurrentColumnMap();

        foreach ($this->renameMap as $legacy => $new) {
            $legacyKey = strtolower($legacy);
            $newKey = strtolower($new);

            if (! isset($columnMap[$legacyKey])) {
                continue;
            }

            // Kalau kolom baru sudah ada, tidak usah diubah lagi.
            if (isset($columnMap[$newKey])) {
                continue;
            }

            $actualLegacyName = $columnMap[$legacyKey];
            $definition = $this->columnDefinitions[$legacy] ?? 'VARCHAR(255) NULL DEFAULT NULL';

            $sql = sprintf(
                'ALTER TABLE %s CHANGE COLUMN `%s` `%s` %s',
                self::TABLE,
                $actualLegacyName,
                $new,
                $definition
            );

            $this->db->query($sql);
        }

        $this->ensureIndexes();
    }

    public function down()
    {
        // Tidak di-rollback untuk menghindari konflik nama kolom legacy.
    }

    /**
     * @return array<string, string>
     */
    private function getCurrentColumnMap(): array
    {
        $result = $this->db->query('SHOW COLUMNS FROM ' . self::TABLE)->getResultArray();
        $map = [];

        foreach ($result as $row) {
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

        if (! isset($indexes['idx_laporan_harian_idpel'])) {
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
