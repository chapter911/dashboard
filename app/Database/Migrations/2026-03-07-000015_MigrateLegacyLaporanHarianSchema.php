<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MigrateLegacyLaporanHarianSchema extends Migration
{
    private const TABLE = 'laporan_harian';

    /**
     * Kolom lama (format export lama) -> kolom baru (snake_case).
     *
     * @var array<string, string>
     */
    private array $legacyToNewMap = [
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

    public function up()
    {
        if (! $this->db->tableExists(self::TABLE)) {
            return;
        }

        $this->addMissingNewColumns();
        $this->copyLegacyDataToNewColumns();
        $this->dropLegacyColumns();
        $this->ensureIndexes();
    }

    public function down()
    {
        // Tidak di-rollback otomatis karena ini migrasi normalisasi dari format lama.
    }

    private function addMissingNewColumns(): void
    {
        $columnDefinitions = [
            'no_agenda' => ['type' => 'BIGINT', 'null' => true],
            'unit_upi' => ['type' => 'INT', 'null' => true],
            'unit_ap' => ['type' => 'INT', 'null' => true],
            'unit_up' => ['type' => 'INT', 'null' => true],
            'nomor_pdl' => ['type' => 'INT', 'null' => true],
            'tgl_pengaduan' => ['type' => 'DATE', 'null' => true],
            'tgl_tindakan_pengaduan' => ['type' => 'DATE', 'null' => true],
            'tgl_bayar' => ['type' => 'DATE', 'null' => true],
            'tgl_aktivasi' => ['type' => 'DATE', 'null' => true],
            'tgl_penangguhan' => ['type' => 'DATE', 'null' => true],
            'tgl_restitusi' => ['type' => 'DATE', 'null' => true],
            'tgl_remaja' => ['type' => 'DATE', 'null' => true],
            'tgl_nyala' => ['type' => 'DATE', 'null' => true],
            'tgl_batal' => ['type' => 'DATE', 'null' => true],
            'petugas_pengaduan' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'petugas_tindakan_pengaduan' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'petugas_aktivasi' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'petugas_penangguhan' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'petugas_restitusi' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'petugas_remaja' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'petugas_batal' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'tgl_rekap' => ['type' => 'DATE', 'null' => true],
            'kd_pemb_meter' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'fakm_kwh' => ['type' => 'BIGINT', 'null' => true],
            'fakm_kvarh' => ['type' => 'BIGINT', 'null' => true],
        ];

        foreach ($columnDefinitions as $column => $definition) {
            if ($this->db->fieldExists($column, self::TABLE)) {
                continue;
            }

            $this->forge->addColumn(self::TABLE, [$column => $definition]);
        }
    }

    private function copyLegacyDataToNewColumns(): void
    {
        foreach ($this->legacyToNewMap as $legacyColumn => $newColumn) {
            if (! $this->db->fieldExists($legacyColumn, self::TABLE) || ! $this->db->fieldExists($newColumn, self::TABLE)) {
                continue;
            }

            $sql = sprintf(
                'UPDATE %s SET %s = COALESCE(%s, %s) WHERE %s IS NOT NULL',
                self::TABLE,
                $newColumn,
                $newColumn,
                $legacyColumn,
                $legacyColumn
            );

            $this->db->query($sql);
        }
    }

    private function dropLegacyColumns(): void
    {
        $dropColumns = [];
        foreach ($this->legacyToNewMap as $legacyColumn => $newColumn) {
            if (! $this->db->fieldExists($legacyColumn, self::TABLE)) {
                continue;
            }

            if (! $this->db->fieldExists($newColumn, self::TABLE)) {
                continue;
            }

            $dropColumns[] = $legacyColumn;
        }

        if ($dropColumns !== []) {
            $this->forge->dropColumn(self::TABLE, $dropColumns);
        }
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
