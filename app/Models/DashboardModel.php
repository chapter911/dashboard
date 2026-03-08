<?php

namespace App\Models;

use CodeIgniter\Model;

class DashboardModel extends Model
{
    /**
     * @return list<array<string, mixed>>
     */
    public function getAkumulasiTahunan(string $startDate, string $endDate, string $sortir = '1'): array
    {
        $sql = "SELECT
                    u.unit_id,
                    u.unit_name,
                    COALESCE(tr.tahun, YEAR(?)) AS tahun,
                    COALESCE(tr.target_tahunan, 0) AS target,
                    COALESCE(SUM(p.kwh), 0) AS realisasi,
                    COALESCE((SUM(p.kwh) / NULLIF(tr.target_tahunan, 0) * 100), 0) AS persentase
                FROM mst_unit u
                LEFT JOIN trn_target_realisasi tr
                    ON u.unit_id = tr.unit_id
                    AND tr.tahun = YEAR(?)
                LEFT JOIN trn_p2tl p
                    ON u.unit_id = p.unit_id
                    AND p.tanggal_register BETWEEN ? AND ?
                WHERE u.is_active = 1
                    AND u.unit_id <> 54000
                GROUP BY u.unit_id, u.unit_name, tr.tahun, tr.target_tahunan";

        if ($sortir === '1') {
            $sql .= ' ORDER BY persentase DESC';
        } elseif ($sortir === '0') {
            $sql .= ' ORDER BY persentase ASC';
        } else {
            $sql .= ' ORDER BY u.urutan ASC, u.unit_name ASC';
        }

        return $this->db->query($sql, [$endDate, $endDate, $startDate, $endDate])->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getAkumulasiBulanan(int $year, int $month, string $sortir = '1'): array
    {
        $sql = <<<'SQL'
WITH unit_data AS (
    SELECT
        u.urutan AS urutan,
        u.unit_id AS unit_id,
        u.unit_name AS unit_name,
        b.bulan AS bulan,
        b.nama_bulan AS nama_bulan
    FROM mst_unit u
    JOIN mst_bulan b
),
get_realisasi AS (
    SELECT
        ud.urutan AS urutan,
        ud.unit_id AS unit_id,
        ud.unit_name AS unit_name,
        ud.bulan AS bulan,
        ud.nama_bulan AS nama_bulan,
        YEAR(p.tanggal_register) AS tahun,
        CAST(tr.target_tahunan AS SIGNED) AS target_tahunan,
        COALESCE(SUM(p.kwh), 0) AS realisasi
    FROM unit_data ud
    LEFT JOIN trn_target_realisasi tr
        ON ud.unit_id = tr.unit_id
    LEFT JOIN trn_p2tl p
        ON ud.unit_id = p.unit_id
        AND ud.bulan = MONTH(p.tanggal_register)
    GROUP BY ud.unit_id, YEAR(p.tanggal_register), ud.bulan
),
akumulasi AS (
    SELECT
        gr.urutan AS urutan,
        gr.unit_id AS unit_id,
        gr.unit_name AS unit_name,
        gr.bulan AS bulan,
        gr.nama_bulan AS nama_bulan,
        gr.tahun AS tahun,
        gr.target_tahunan AS target_tahunan,
        gr.realisasi AS realisasi,
        CAST((gr.target_tahunan - COALESCE(SUM(p.kwh), 0)) / (13 - gr.bulan) AS SIGNED) AS target,
        COALESCE(SUM(p.kwh), 0) AS akumulasi_realisasi
    FROM get_realisasi gr
    LEFT JOIN trn_p2tl p
        ON gr.unit_id = p.unit_id
        AND gr.bulan > MONTH(p.tanggal_register)
    GROUP BY gr.unit_id, gr.bulan, gr.tahun
)
SELECT
    a.urutan,
    a.unit_id,
    a.unit_name,
    a.bulan,
    a.nama_bulan,
    a.tahun,
    a.target_tahunan,
    a.realisasi,
    a.target,
    a.akumulasi_realisasi,
    a.realisasi * 100 / NULLIF(a.target, 0) AS persentase
FROM akumulasi a
WHERE a.tahun = ?
    AND a.bulan = ?
SQL;

        if ($sortir === '1') {
            $sql .= ' ORDER BY persentase DESC';
        } elseif ($sortir === '0') {
            $sql .= ' ORDER BY persentase ASC';
        } else {
            $sql .= ' ORDER BY urutan ASC, unit_name ASC';
        }

        return $this->db->query($sql, [$year, $month])->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getTemuanTahunan(int $year): array
    {
        $sql = "SELECT
                    gol,
                    COUNT(*) AS jumlah_pelanggan,
                    COALESCE(SUM(kwh), 0) AS total_kwh
                FROM trn_p2tl
                WHERE YEAR(tanggal_register) = ?
                GROUP BY gol";

        return $this->db->query($sql, [$year])->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getHitrateRange(string $startDate, string $endDate, string $sortir = '1'): array
    {
        $joinColumn = 'unit_ulp';
        if ($this->db->fieldExists('unit_up3', 'trn_hitrate')) {
            $joinColumn = 'unit_up3';
        } elseif ($this->db->fieldExists('unit_id', 'trn_hitrate')) {
            $joinColumn = 'unit_id';
        }

        $countColumn = $this->db->fieldExists('id_p2tl', 'trn_hitrate') ? 'id_p2tl' : 'waktu_periksa';

        $sql = "SELECT
                    u.unit_id,
                    u.unit_name,
                    COUNT(h.{$countColumn}) AS jumlah_periksa,
                    SUM(CASE WHEN h.update_status = 'Periksa - Sesuai' THEN 1 ELSE 0 END) AS jumlah_sesuai,
                    SUM(CASE WHEN h.update_status = 'Temuan - K1' THEN 1 ELSE 0 END) AS jumlah_k1,
                    SUM(CASE WHEN h.update_status = 'Temuan - K2' THEN 1 ELSE 0 END) AS jumlah_k2,
                    SUM(CASE WHEN h.update_status = 'Temuan - P1' THEN 1 ELSE 0 END) AS jumlah_p1,
                    SUM(CASE WHEN h.update_status = 'Temuan - P2' THEN 1 ELSE 0 END) AS jumlah_p2,
                    SUM(CASE WHEN h.update_status = 'Temuan - P3' THEN 1 ELSE 0 END) AS jumlah_p3,
                    SUM(CASE WHEN h.update_status = 'Temuan - P4' THEN 1 ELSE 0 END) AS jumlah_p4,
                    SUM(CASE WHEN h.update_status <> 'Periksa - Sesuai' THEN 1 ELSE 0 END) AS jumlah_temuan,
                    COALESCE(
                        SUM(CASE WHEN h.update_status <> 'Periksa - Sesuai' THEN 1 ELSE 0 END) * 100
                        / NULLIF(COUNT(h.{$countColumn}), 0),
                        0
                    ) AS persentase
                FROM mst_unit u
                LEFT JOIN trn_hitrate h
                    ON u.unit_id = h.{$joinColumn}
                WHERE u.is_active = 1
                    AND DATE(h.waktu_periksa) BETWEEN ? AND ?
                GROUP BY u.unit_id, u.unit_name";

        if ($sortir === '1') {
            $sql .= ' ORDER BY persentase DESC';
        } elseif ($sortir === '0') {
            $sql .= ' ORDER BY persentase ASC';
        } else {
            $sql .= ' ORDER BY u.urutan ASC, u.unit_name ASC';
        }

        return $this->db->query($sql, [$startDate, $endDate])->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getGantiMeterPraPascaByUnit(int $year): array
    {
        $sql = "SELECT
                    u.unit_id,
                    u.unit_name,
                    SUM(CASE WHEN lh.tarif LIKE '%T' THEN 1 ELSE 0 END) AS pra,
                    SUM(CASE WHEN lh.tarif NOT LIKE '%T' THEN 1 ELSE 0 END) AS pasca
                FROM mst_unit u
                LEFT JOIN laporan_harian lh
                    ON u.unit_id = lh.unit_ap
                    AND YEAR(lh.tgl_remaja) = ?
                    AND (
                        lh.alasan_ganti_meter IS NULL
                        OR lh.alasan_ganti_meter NOT IN (
                            'Meter ex P2TL',
                            'Program Migrasi AMI',
                            'Program pemeliharaan meter (kasus khusus)'
                        )
                    )
                WHERE u.is_active = 1
                GROUP BY u.unit_id, u.unit_name
                ORDER BY u.urutan ASC, u.unit_name ASC";

        return $this->db->query($sql, [$year])->getResultArray();
    }
}
