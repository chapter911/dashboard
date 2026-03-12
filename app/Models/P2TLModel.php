<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

class P2TLModel extends Model
{
    protected $table = 'trn_p2tl';
    protected $primaryKey = 'noagenda';
    protected $returnType = 'array';
    protected $allowedFields = [
        'noagenda',
        'idpel',
        'nama',
        'gol',
        'alamat',
        'daya',
        'kwh',
        'tagihan_beban',
        'tagihan_kwh',
        'tagihan_ts',
        'materai',
        'segel',
        'materia',
        'rpppj',
        'rpujl',
        'rpppn',
        'rupiah_total',
        'rupiah_tunai',
        'rupiah_angsuran',
        'tanggal_register',
        'nomor_register',
        'tanggal_sph',
        'nomor_sph',
        'unit_id',
        'upload_by',
        'upload_date',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function getUnits(): array
    {
        return $this->db->table('mst_unit')
            ->select('unit_id, unit_name, urutan')
            ->where('is_active', 1)
            ->orderBy('urutan', 'ASC')
            ->orderBy('unit_name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<int>
     */
    public function getAvailableAnalisaYears(bool $isAdmin, ?int $userUnitId): array
    {
        $builder = $this->db->table('trn_p2tl_analisa a')
            ->select('DISTINCT YEAR(a.periode) AS tahun', false)
            ->where('a.periode IS NOT NULL', null, false)
            ->orderBy('tahun', 'DESC');

        if (! $isAdmin && $userUnitId !== null) {
            $builder->where('a.unit_id', (string) $userUnitId);
        }

        $rows = $builder->get()->getResultArray();
        $years = [];
        foreach ($rows as $row) {
            $year = (int) ($row['tahun'] ?? 0);
            if ($year > 0) {
                $years[] = $year;
            }
        }

        return $years;
    }

    /**
     * @return list<int>
     */
    public function getAvailableP2TLYears(bool $isAdmin, ?int $userUnitId): array
    {
        $builder = $this->db->table('trn_p2tl p')
            ->select('DISTINCT YEAR(p.tanggal_register) AS tahun', false)
            ->where('p.tanggal_register IS NOT NULL', null, false)
            ->orderBy('tahun', 'DESC');

        if (! $isAdmin && $userUnitId !== null) {
            $builder->where('p.unit_id', (string) $userUnitId);
        }

        $rows = $builder->get()->getResultArray();
        $years = [];
        foreach ($rows as $row) {
            $year = (int) ($row['tahun'] ?? 0);
            if ($year > 0) {
                $years[] = $year;
            }
        }

        return $years;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getAkumulasiTahunan(string $startDate, string $endDate, string $golongan = '*', string $sortir = '1'): array
    {
        $sql = "SELECT
                    u.urutan,
                    u.unit_id,
                    u.unit_name,
                    tr.tahun,
                    COALESCE(tr.target_tahunan, 0) AS target,
                    COALESCE(SUM(p.kwh), 0) AS realisasi,
                    COALESCE((SUM(p.kwh) / NULLIF(tr.target_tahunan, 0) * 100), 0) AS persentase
                FROM mst_unit u
                LEFT JOIN trn_target_realisasi tr
                    ON u.unit_id = tr.unit_id
                    AND tr.tahun = YEAR(?)
                LEFT JOIN trn_p2tl p
                    ON u.unit_id = p.unit_id
                    AND p.tanggal_register BETWEEN ? AND ?";

        $binds = [$endDate, $startDate, $endDate];

        if ($golongan === '0') {
            $sql .= ' AND p.gol IS NULL';
        } elseif ($golongan !== '*' && $golongan !== '') {
            $sql .= ' AND p.gol = ?';
            $binds[] = $golongan;
        }

        $sql .= " WHERE u.unit_id <> 54000
                  GROUP BY u.urutan, u.unit_id, u.unit_name, tr.tahun, tr.target_tahunan";

        if ($sortir === '1') {
            $sql .= ' ORDER BY persentase DESC';
        } elseif ($sortir === '0') {
            $sql .= ' ORDER BY persentase ASC';
        } else {
            $sql .= ' ORDER BY u.urutan ASC, u.unit_name ASC';
        }

        return $this->db->query($sql, $binds)->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getAkumulasiBulanan(int $year, int $month, string $golongan = '*', string $sortir = '1'): array
    {
        $sql = <<<'SQL'
WITH unit_data AS (
    SELECT
        u.urutan AS urutan,
        u.unit_id AS unit_id,
        u.unit_name AS unit_name,
        b.bulan AS bulan
    FROM mst_unit u
    JOIN mst_bulan b
),
get_realisasi AS (
    SELECT
        ud.urutan AS urutan,
        ud.unit_id AS unit_id,
        ud.unit_name AS unit_name,
        ud.bulan AS bulan,
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
    a.tahun,
    a.target_tahunan,
    a.realisasi,
    a.target,
    a.akumulasi_realisasi,
    COALESCE(a.realisasi * 100 / NULLIF(a.target, 0), 0) AS persentase
FROM akumulasi a
WHERE a.tahun = ?
    AND a.bulan = ?
SQL;

        $binds = [$year, $month];

        if ($golongan === '0') {
            $sql = str_replace('GROUP BY ud.unit_id, YEAR(p.tanggal_register), ud.bulan', "AND p.gol IS NULL\n    GROUP BY ud.unit_id, YEAR(p.tanggal_register), ud.bulan", $sql);
        } elseif ($golongan !== '*' && $golongan !== '') {
            $sql = str_replace('GROUP BY ud.unit_id, YEAR(p.tanggal_register), ud.bulan', "AND p.gol = ?\n    GROUP BY ud.unit_id, YEAR(p.tanggal_register), ud.bulan", $sql);
            $binds[] = $golongan;
        }

        if ($sortir === '1') {
            $sql .= ' ORDER BY persentase DESC';
        } elseif ($sortir === '0') {
            $sql .= ' ORDER BY persentase ASC';
        } else {
            $sql .= ' ORDER BY urutan ASC, unit_name ASC';
        }

        return $this->db->query($sql, $binds)->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getAkumulasiHarian(string $date, string $sortir = '1'): array
    {
        $sql = "SELECT
                    u.urutan,
                    u.unit_id,
                    u.unit_name,
                    DATE(p.tanggal_register) AS tanggal_register,
                    COALESCE(SUM(p.kwh), 0) AS realisasi,
                    COALESCE(h.hari_kerja, 0) AS hari_kerja,
                    COALESCE(tr.target_tahunan, 0) AS target_tahunan,
                    COALESCE((tr.target_tahunan / 12 / NULLIF(h.hari_kerja, 0)), 0) AS target,
                    COALESCE(
                        SUM(p.kwh) * 100 / NULLIF((tr.target_tahunan / 12 / NULLIF(h.hari_kerja, 0)), 0),
                        0
                    ) AS persentase
                FROM mst_unit u
                LEFT JOIN trn_target_realisasi tr
                    ON u.unit_id = tr.unit_id
                    AND tr.tahun = YEAR(?)
                LEFT JOIN trn_hari_kerja h
                    ON h.tahun = YEAR(?)
                    AND h.bulan = MONTH(?)
                LEFT JOIN trn_p2tl p
                    ON u.unit_id = p.unit_id
                    AND DATE(p.tanggal_register) = ?
                WHERE u.unit_id <> 54000
                GROUP BY u.urutan, u.unit_id, u.unit_name, DATE(p.tanggal_register), h.hari_kerja, tr.target_tahunan";

        if ($sortir === '1') {
            $sql .= ' ORDER BY persentase DESC';
        } elseif ($sortir === '0') {
            $sql .= ' ORDER BY persentase ASC';
        } else {
            $sql .= ' ORDER BY u.urutan ASC, u.unit_name ASC';
        }

        return $this->db->query($sql, [$date, $date, $date, $date])->getResultArray();
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
                WHERE DATE(h.waktu_periksa) BETWEEN ? AND ?
                GROUP BY u.unit_id, u.unit_name";

        if ($sortir === '1') {
            $sql .= ' ORDER BY LENGTH(persentase) DESC, persentase DESC';
        } elseif ($sortir === '0') {
            $sql .= ' ORDER BY LENGTH(persentase) ASC, persentase ASC';
        }

        return $this->db->query($sql, [$startDate, $endDate])->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getDataPemakaianDatatable(array $filters, int $start, int $length, ?string $search, bool $isAdmin, ?int $userUnitId): array
    {
        $year = (int) ($filters['tahun'] ?? date('Y'));
        $unit = (string) ($filters['unit'] ?? '*');
        $idpel = trim((string) ($filters['idpel'] ?? ''));

        $fromSql = ' FROM trn_p2tl p WHERE 1=1';
        $binds = [];

        if ($year > 0) {
            $fromSql .= ' AND YEAR(p.tanggal_register) = ?';
            $binds[] = $year;
        }

        if (! $isAdmin && $userUnitId !== null) {
            $fromSql .= ' AND p.unit_id = ?';
            $binds[] = $userUnitId;
        } elseif ($unit !== '' && $unit !== '*') {
            $fromSql .= ' AND p.unit_id = ?';
            $binds[] = (int) $unit;
        }

        if ($idpel !== '') {
            $fromSql .= ' AND p.idpel LIKE ?';
            $binds[] = '%' . $idpel . '%';
        }

        $totalSql = 'SELECT COUNT(*) AS total' . $fromSql;
        $total = (int) ($this->db->query($totalSql, $binds)->getRowArray()['total'] ?? 0);

        $searchValue = trim((string) $search);
        $searchSql = '';
        $searchBinds = [];
        if ($searchValue !== '') {
            $searchSql = ' AND (p.noagenda LIKE ? OR p.idpel LIKE ? OR p.nama LIKE ? OR p.gol LIKE ? OR p.alamat LIKE ? OR p.nomor_sph LIKE ?)';
            $searchBinds = array_fill(0, 6, '%' . $searchValue . '%');
        }

        $filtered = $total;
        if ($searchSql !== '') {
            $filteredSql = 'SELECT COUNT(*) AS total' . $fromSql . $searchSql;
            $filtered = (int) ($this->db->query($filteredSql, array_merge($binds, $searchBinds))->getRowArray()['total'] ?? 0);
        }

        $dataSql = 'SELECT
                p.noagenda,
                p.idpel,
                p.nama,
                p.gol,
                p.alamat,
                p.daya,
                p.kwh,
                p.tagihan_beban,
                p.tagihan_kwh,
                p.tagihan_ts,
                p.materai,
                p.segel,
                p.materia,
                p.rpppj,
                p.rpujl,
                p.rpppn,
                p.rupiah_total,
                p.rupiah_tunai,
                p.rupiah_angsuran,
                p.tanggal_register,
                p.nomor_register,
                p.tanggal_sph,
                p.nomor_sph' . $fromSql . $searchSql . ' ORDER BY p.tanggal_register DESC, p.noagenda DESC';

        if ($length > 0) {
            $dataSql .= ' LIMIT ' . (int) $start . ', ' . (int) $length;
        }

        $rows = $this->db->query($dataSql, array_merge($binds, $searchBinds))->getResultArray();

        return [
            'rows' => $rows,
            'total' => $total,
            'filtered' => $filtered,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getDataPemakaianDashboard(int $year, ?int $unitId, bool $isAdmin, ?int $userUnitId): array
    {
        $builder = $this->db->table('trn_p2tl_analisa a')
            ->select('a.idpel, a.tarif, a.daya, YEAR(a.periode) AS tahun, MONTH(a.periode) AS bulan, SUM(COALESCE(a.pemakaian_kwh, 0)) AS pemakaian_kwh', false)
            ->where('YEAR(a.periode)', $year)
            ->groupBy('a.idpel, a.tarif, a.daya, YEAR(a.periode), MONTH(a.periode)')
            ->orderBy('a.idpel', 'ASC')
            ->orderBy('a.tarif', 'ASC')
            ->orderBy('a.daya', 'ASC')
            ->orderBy('MONTH(a.periode)', 'ASC');

        if (! $isAdmin && $userUnitId !== null) {
            $builder->where('a.unit_id', $userUnitId);
        } elseif ($unitId !== null && $unitId > 0) {
            $builder->where('a.unit_id', $unitId);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * @param list<string> $idpels
     *
     * @return array<string, string>
     */
    private function getLatestNomorGarduByIdpels(array $idpels): array
    {
        if ($idpels === []) {
            return [];
        }

        $placeholder = implode(',', array_fill(0, count($idpels), '?'));
        $sql = "SELECT d.idpel, d.nomor_gardu
                FROM mst_data_induk_langganan d
                INNER JOIN (
                    SELECT idpel, MAX(v_bulan_rekap) AS max_bulan_rekap
                    FROM mst_data_induk_langganan
                    WHERE idpel IN ({$placeholder})
                    GROUP BY idpel
                ) x ON x.idpel = d.idpel AND x.max_bulan_rekap = d.v_bulan_rekap";

        $rows = $this->db->query($sql, $idpels)->getResultArray();
        $mapped = [];

        foreach ($rows as $row) {
            $idpel = (string) ($row['idpel'] ?? '');
            if ($idpel === '') {
                continue;
            }

            $mapped[$idpel] = (string) ($row['nomor_gardu'] ?? '');
        }

        return $mapped;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getAnalisaSummaryDatatable(array $filters, int $start, int $length, ?string $search, bool $isAdmin, ?int $userUnitId): array
    {
        $year = (int) ($filters['tahun'] ?? date('Y'));
        $unit = (string) ($filters['unit'] ?? '*');
        $idpel = trim((string) ($filters['idpel'] ?? ''));
        $temuanStatus = (string) ($filters['temuan_status'] ?? '*');

        $unitWhere = '';
        $binds = [$year, $year];

        if (! $isAdmin && $userUnitId !== null) {
            $unitWhere = ' AND a.unit_id = ?';
            $binds[] = (string) $userUnitId;
            $binds[] = (string) $userUnitId;
        } elseif ($unit !== '' && $unit !== '*') {
            $unitWhere = ' AND a.unit_id = ?';
            $binds[] = $unit;
            $binds[] = $unit;
        }

        $baseSql = "SELECT
                s.idpel,
                s.tarif,
                s.daya,
                s.counting_emin,
                s.jam_nyala_rata,
                s.jam_nyala_min,
                s.jam_nyala_max,
                g.rata_rata_daya
            FROM (
                SELECT
                    a.idpel,
                    a.tarif,
                    a.daya,
                    SUM(CASE WHEN ((a.pemakaian_kwh / NULLIF(a.daya, 0)) * 1000) < ((a.daya / 1000) * 40) THEN 1 ELSE 0 END) AS counting_emin,
                    AVG((a.pemakaian_kwh / NULLIF(a.daya, 0)) * 1000) AS jam_nyala_rata,
                    MIN((a.pemakaian_kwh / NULLIF(a.daya, 0)) * 1000) AS jam_nyala_min,
                    MAX((a.pemakaian_kwh / NULLIF(a.daya, 0)) * 1000) AS jam_nyala_max
                FROM trn_p2tl_analisa a
                WHERE YEAR(a.periode) = ?
                    AND a.pemakaian_kwh IS NOT NULL
                    AND a.daya IS NOT NULL{$unitWhere}
                GROUP BY a.idpel, a.tarif, a.daya
            ) s
            LEFT JOIN (
                SELECT
                    a.tarif,
                    a.daya,
                    AVG((a.pemakaian_kwh / NULLIF(a.daya, 0)) * 1000) AS rata_rata_daya
                FROM trn_p2tl_analisa a
                WHERE YEAR(a.periode) = ?
                    AND a.pemakaian_kwh IS NOT NULL
                    AND a.daya IS NOT NULL{$unitWhere}
                GROUP BY a.tarif, a.daya
            ) g ON s.tarif = g.tarif AND s.daya = g.daya";

        $where = '';
        $whereBinds = [];
        if ($idpel !== '') {
            $where .= ($where === '' ? ' WHERE ' : ' AND ') . 'x.idpel LIKE ?';
            $whereBinds[] = '%' . $idpel . '%';
        }

        $searchValue = trim((string) $search);
        if ($searchValue !== '') {
            $where .= ($where === '' ? ' WHERE ' : ' AND ') . '(x.idpel LIKE ? OR x.tarif LIKE ? OR CAST(x.daya AS CHAR) LIKE ?)';
            $whereBinds[] = '%' . $searchValue . '%';
            $whereBinds[] = '%' . $searchValue . '%';
            $whereBinds[] = '%' . $searchValue . '%';
        }

        $allowedTemuanStatuses = ['P1', 'P2', 'P3', 'P4', 'K2'];
        if ($temuanStatus === 'has' || $temuanStatus === 'none' || in_array($temuanStatus, $allowedTemuanStatuses, true)) {
            $temuanExistsSql = "EXISTS (
                SELECT 1
                FROM trn_p2tl p
                WHERE p.idpel = x.idpel
                    AND p.tanggal_register IS NOT NULL
                    AND YEAR(p.tanggal_register) = ?";
            $temuanBinds = [$year];

            if (in_array($temuanStatus, $allowedTemuanStatuses, true)) {
                $temuanExistsSql .= ' AND UPPER(TRIM(p.gol)) = ?';
                $temuanBinds[] = $temuanStatus;
            } else {
                $temuanExistsSql .= " AND UPPER(TRIM(p.gol)) IN ('P1', 'P2', 'P3', 'P4', 'K2')";
            }

            if (! $isAdmin && $userUnitId !== null) {
                $temuanExistsSql .= ' AND p.unit_id = ?';
                $temuanBinds[] = $userUnitId;
            } elseif ($unit !== '' && $unit !== '*') {
                $temuanExistsSql .= ' AND p.unit_id = ?';
                $temuanBinds[] = (int) $unit;
            }

            $temuanExistsSql .= ')';

            $where .= ($where === '' ? ' WHERE ' : ' AND ')
                . ($temuanStatus === 'none' ? 'NOT ' . $temuanExistsSql : $temuanExistsSql);
            $whereBinds = array_merge($whereBinds, $temuanBinds);
        }

        $countSql = 'SELECT COUNT(*) AS total FROM (' . $baseSql . ') x' . $where;
        $total = (int) ($this->db->query($countSql, array_merge($binds, $whereBinds))->getRowArray()['total'] ?? 0);

        $dataSql = 'SELECT * FROM (' . $baseSql . ') x' . $where . ' ORDER BY x.idpel ASC';
        if ($length > 0) {
            $dataSql .= ' LIMIT ' . (int) $start . ', ' . (int) $length;
        }

        $rows = $this->db->query($dataSql, array_merge($binds, $whereBinds))->getResultArray();

        return [
            'rows' => $rows,
            'total' => $total,
            'filtered' => $total,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getAnalisaDetailByIdpel(string $idpel, int $year, bool $isAdmin, ?int $userUnitId, ?int $unitFilter = null): array
    {
        $builder = $this->db->table('trn_p2tl_analisa')
            ->select('idpel, tarif, daya, periode, pemakaian_kwh')
            ->where('idpel', $idpel)
            ->where('YEAR(periode) IN (' . ($year - 2) . ', ' . ($year - 1) . ', ' . $year . ')', null, false)
            ->orderBy('periode', 'ASC');

        if (! $isAdmin && $userUnitId !== null) {
            $builder->where('unit_id', (string) $userUnitId);
        } elseif ($unitFilter !== null && $unitFilter > 0) {
            $builder->where('unit_id', (string) $unitFilter);
        }

        $rows = $builder->get()->getResultArray();
        if ($rows === []) {
            return [
                'has_data' => false,
                'years' => [$year, $year - 1],
                'rows' => [],
            ];
        }

        $years = [$year, $year - 1];
        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $map = [];
        foreach ($rows as $row) {
            $month = (int) date('n', strtotime((string) ($row['periode'] ?? '')));
            $yr = (int) date('Y', strtotime((string) ($row['periode'] ?? '')));
            $daya = (float) ($row['daya'] ?? 0);
            $kwh = (float) ($row['pemakaian_kwh'] ?? 0);
            $jamNyala = $daya > 0 ? (($kwh / $daya) * 1000) : null;
            $map[$month]['pemakaian_kwh'][(string) $yr] = $kwh;
            $map[$month]['jam_nyala'][(string) $yr] = $jamNyala;
        }

        $outRows = [];
        for ($m = 1; $m <= 12; $m++) {
            $outRows[] = [
                'bulan' => $monthNames[$m],
                'pemakaian_kwh' => $map[$m]['pemakaian_kwh'] ?? [],
                'jam_nyala' => $map[$m]['jam_nyala'] ?? [],
            ];
        }

        $first = $rows[0];

        // Build continuous JN series: 36 months [Jan(y-2)..Dec(y-2), Jan(y-1)..Dec(y-1), Jan(y)..Dec(y)]
        $jnContinuous = array_fill(0, 36, null);
        for ($m = 1; $m <= 12; $m++) {
            if (isset($map[$m]['jam_nyala'][(string) ($year - 2)])) {
                $jnContinuous[$m - 1] = $map[$m]['jam_nyala'][(string) ($year - 2)];
            }
            if (isset($map[$m]['jam_nyala'][(string) ($year - 1)])) {
                $jnContinuous[12 + $m - 1] = $map[$m]['jam_nyala'][(string) ($year - 1)];
            }
            if (isset($map[$m]['jam_nyala'][(string) $year])) {
                $jnContinuous[24 + $m - 1] = $map[$m]['jam_nyala'][(string) $year];
            }
        }

        return [
            'has_data' => true,
            'idpel' => $idpel,
            'tarif' => (string) ($first['tarif'] ?? ''),
            'daya' => $first['daya'] !== null ? (float) $first['daya'] : null,
            'years' => $years,
            'rows' => $outRows,
            'jn_continuous' => $jnContinuous,
        ];
    }

    /**
     * @return array<int, list<float|null>>
     */
    public function getAnalisaGrafikByIdpelRange(string $idpel, int $tahunAkhir, int $tahunAwal, bool $isAdmin, ?int $userUnitId, ?int $unitFilter = null): array
    {
        $builder = $this->db->table('trn_p2tl_analisa')
            ->select('YEAR(periode) AS tahun, MONTH(periode) AS bulan, AVG((pemakaian_kwh / NULLIF(daya, 0)) * 1000) AS jam_nyala', false)
            ->where('idpel', $idpel)
            ->where('YEAR(periode) >=', $tahunAwal)
            ->where('YEAR(periode) <=', $tahunAkhir)
            ->groupBy('YEAR(periode), MONTH(periode)')
            ->orderBy('YEAR(periode)', 'ASC')
            ->orderBy('MONTH(periode)', 'ASC');

        if (! $isAdmin && $userUnitId !== null) {
            $builder->where('unit_id', (string) $userUnitId);
        } elseif ($unitFilter !== null && $unitFilter > 0) {
            $builder->where('unit_id', (string) $unitFilter);
        }

        $raw = $builder->get()->getResultArray();

        $series = [];
        for ($year = $tahunAwal; $year <= $tahunAkhir; $year++) {
            $series[$year] = array_fill(0, 12, null);
        }

        foreach ($raw as $row) {
            $year = (int) ($row['tahun'] ?? 0);
            $month = (int) ($row['bulan'] ?? 0);
            if (! isset($series[$year]) || $month < 1 || $month > 12) {
                continue;
            }

            $series[$year][$month - 1] = $row['jam_nyala'] !== null ? (float) $row['jam_nyala'] : null;
        }

        return $series;
    }

    /**
     * @param list<array{label:string,start_date:string,end_date:string}> $periods
     * @return list<array{label:string,start_date:string,end_date:string,count:int,has_temuan:bool,gol_counts:array<string,int>,gol_detail:string}>
     */
    public function getAnalisaTemuanPeriodsByIdpel(string $idpel, array $periods, bool $isAdmin, ?int $userUnitId, ?int $unitFilter = null): array
    {
        $allowedGols = ['P1', 'P2', 'P3', 'P4', 'K2'];
        $results = [];
        foreach ($periods as $period) {
            $builder = $this->db->table('trn_p2tl')
                ->select('UPPER(TRIM(gol)) AS gol, COUNT(*) AS total', false)
                ->where('idpel', $idpel)
                ->where('tanggal_register >=', $period['start_date'])
                ->where('tanggal_register <=', $period['end_date'])
                ->where('tanggal_register IS NOT NULL', null, false)
                ->where("UPPER(TRIM(gol)) IN ('P1', 'P2', 'P3', 'P4', 'K2')", null, false)
                ->groupBy('UPPER(TRIM(gol))');

            if (! $isAdmin && $userUnitId !== null) {
                $builder->where('unit_id', $userUnitId);
            } elseif ($unitFilter !== null && $unitFilter > 0) {
                $builder->where('unit_id', $unitFilter);
            }

            $rows = $builder->get()->getResultArray();
            $golCounts = [];
            foreach ($allowedGols as $gol) {
                $golCounts[$gol] = 0;
            }

            $count = 0;
            foreach ($rows as $row) {
                $gol = strtoupper(trim((string) ($row['gol'] ?? '')));
                if (! in_array($gol, $allowedGols, true)) {
                    continue;
                }

                $rowCount = (int) ($row['total'] ?? 0);
                $golCounts[$gol] += $rowCount;
                $count += $rowCount;
            }

            $detailParts = [];
            foreach ($allowedGols as $gol) {
                if (($golCounts[$gol] ?? 0) > 0) {
                    $detailParts[] = $gol . ': ' . $golCounts[$gol];
                }
            }

            $results[] = [
                'label' => $period['label'],
                'start_date' => $period['start_date'],
                'end_date' => $period['end_date'],
                'count' => $count,
                'has_temuan' => $count > 0,
                'gol_counts' => $golCounts,
                'gol_detail' => $detailParts !== [] ? implode(', ', $detailParts) : '-',
            ];
        }

        return $results;
    }

    /**
     * @return array<int, array<int, array{count:int,has_temuan:bool,gol_counts:array<string,int>,gol_detail:string}>>
     */
    public function getAnalisaTemuanBulananByIdpel(string $idpel, int $tahunAwal, int $tahunAkhir, bool $isAdmin, ?int $userUnitId, ?int $unitFilter = null): array
    {
        $allowedGols = ['P1', 'P2', 'P3', 'P4', 'K2'];

        $builder = $this->db->table('trn_p2tl')
            ->select('YEAR(tanggal_register) AS tahun, MONTH(tanggal_register) AS bulan, UPPER(TRIM(gol)) AS gol, COUNT(*) AS total', false)
            ->where('idpel', $idpel)
            ->where('tanggal_register IS NOT NULL', null, false)
            ->where('YEAR(tanggal_register) >=', $tahunAwal)
            ->where('YEAR(tanggal_register) <=', $tahunAkhir)
            ->where("UPPER(TRIM(gol)) IN ('P1', 'P2', 'P3', 'P4', 'K2')", null, false)
            ->groupBy('YEAR(tanggal_register), MONTH(tanggal_register), UPPER(TRIM(gol))')
            ->orderBy('YEAR(tanggal_register)', 'ASC')
            ->orderBy('MONTH(tanggal_register)', 'ASC');

        if (! $isAdmin && $userUnitId !== null) {
            $builder->where('unit_id', $userUnitId);
        } elseif ($unitFilter !== null && $unitFilter > 0) {
            $builder->where('unit_id', $unitFilter);
        }

        $rows = $builder->get()->getResultArray();

        $result = [];
        for ($year = $tahunAwal; $year <= $tahunAkhir; $year++) {
            $result[$year] = [];
            for ($month = 1; $month <= 12; $month++) {
                $golCounts = [];
                foreach ($allowedGols as $gol) {
                    $golCounts[$gol] = 0;
                }

                $result[$year][$month] = [
                    'count' => 0,
                    'has_temuan' => false,
                    'gol_counts' => $golCounts,
                    'gol_detail' => '-',
                ];
            }
        }

        foreach ($rows as $row) {
            $year = (int) ($row['tahun'] ?? 0);
            $month = (int) ($row['bulan'] ?? 0);
            $gol = strtoupper(trim((string) ($row['gol'] ?? '')));
            $total = (int) ($row['total'] ?? 0);

            if (! isset($result[$year][$month]) || ! in_array($gol, $allowedGols, true)) {
                continue;
            }

            $result[$year][$month]['gol_counts'][$gol] += $total;
            $result[$year][$month]['count'] += $total;
            $result[$year][$month]['has_temuan'] = $result[$year][$month]['count'] > 0;
        }

        for ($year = $tahunAwal; $year <= $tahunAkhir; $year++) {
            for ($month = 1; $month <= 12; $month++) {
                $detailParts = [];
                foreach ($allowedGols as $gol) {
                    if (($result[$year][$month]['gol_counts'][$gol] ?? 0) > 0) {
                        $detailParts[] = $gol . ': ' . $result[$year][$month]['gol_counts'][$gol];
                    }
                }

                $result[$year][$month]['gol_detail'] = $detailParts !== [] ? implode(', ', $detailParts) : '-';
            }
        }

        return $result;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getTargetByYear(int $year): array
    {
        $escapedYear = $this->db->escape($year);

        return $this->db->table('mst_unit u')
            ->select('u.unit_id, u.unit_name, ' . $escapedYear . ' AS tahun, COALESCE(t.target_tahunan, 0) AS target_tahunan', false)
            ->join('trn_target_realisasi t', 'u.unit_id = t.unit_id AND t.tahun = ' . $escapedYear, 'left')
            ->where('u.is_active', 1)
            ->where('u.unit_id <>', 54000)
            ->orderBy('u.urutan', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    public function replaceTargetByYear(int $year, array $rows): void
    {
        $this->db->transStart();

        $this->db->table('trn_target_realisasi')
            ->where('tahun', $year)
            ->delete();

        if ($rows !== []) {
            $this->db->table('trn_target_realisasi')->insertBatch($rows, null, 200);
        }

        $this->db->transComplete();
        if (! $this->db->transStatus()) {
            throw new \RuntimeException('Gagal menyimpan target P2TL.');
        }
    }

    /**
     * @return list<array<string,mixed>>
     */
    public function getTargetHarianByUnitYear(int $unitId, int $year): array
    {
        $table = $this->resolveTargetHarianTable();
        if ($table === null) {
            $rows = [];
            for ($m = 1; $m <= 12; $m++) {
                $rows[] = ['bulan' => $m, 'target_harian' => null];
            }
            return $rows;
        }

        $data = $this->db->table($table)
            ->select('bulan, target_harian')
            ->where('unit_id', $unitId)
            ->where('tahun', $year)
            ->orderBy('bulan', 'ASC')
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($data as $row) {
            $map[(int) ($row['bulan'] ?? 0)] = $row['target_harian'] !== null ? (float) $row['target_harian'] : null;
        }

        $rows = [];
        for ($m = 1; $m <= 12; $m++) {
            $rows[] = [
                'bulan' => $m,
                'target_harian' => $map[$m] ?? null,
            ];
        }

        return $rows;
    }

    /**
     * @param list<float|null> $harian
     */
    public function replaceTargetHarianByUnitYear(int $unitId, int $year, array $harian, string $username): void
    {
        $table = $this->resolveTargetHarianTable();
        if ($table === null) {
            throw new \RuntimeException('Tabel target harian belum tersedia.');
        }

        $this->db->transStart();

        $this->db->table($table)
            ->where('unit_id', $unitId)
            ->where('tahun', $year)
            ->delete();

        $rows = [];
        for ($m = 1; $m <= 12; $m++) {
            $rows[] = [
                'unit_id' => $unitId,
                'tahun' => $year,
                'bulan' => $m,
                'target_harian' => $harian[$m - 1] ?? null,
                'created_by' => $username,
            ];
        }

        if ($rows !== []) {
            $this->db->table($table)->insertBatch($rows, null, 100);
        }

        $this->db->transComplete();
        if (! $this->db->transStatus()) {
            throw new \RuntimeException('Gagal menyimpan target harian.');
        }
    }

    private function resolveTargetHarianTable(): ?string
    {
        if ($this->db->tableExists('trn_target_harian')) {
            return 'trn_target_harian';
        }

        if (
            $this->db->tableExists('trn_target_realisasi')
            && $this->db->fieldExists('bulan', 'trn_target_realisasi')
            && $this->db->fieldExists('target_harian', 'trn_target_realisasi')
        ) {
            return 'trn_target_realisasi';
        }

        return null;
    }

    /**
     * @return array{rows: list<array<string,mixed>>, total:int, filtered:int}
     */
    public function getP2TLDatatable(array $filters, int $start, int $length, ?string $search, bool $isAdmin, ?int $userUnitId): array
    {
        $builder = $this->db->table('trn_p2tl p')
            ->select('p.no_agenda, p.idpel, p.nama, p.gol, p.alamat, p.daya, p.kwh, p.rupiah_total, p.tanggal_register, p.nomor_register, p.unit_id, u.unit_name')
            ->join('mst_unit u', 'u.unit_id = p.unit_id', 'left');

        $unit = (string) ($filters['unit'] ?? '*');
        if (! $isAdmin && $userUnitId !== null) {
            $builder->where('p.unit_id', $userUnitId);
        } elseif ($unit !== '' && $unit !== '*') {
            $builder->where('p.unit_id', (int) $unit);
        }

        $from = (string) ($filters['tanggal_awal'] ?? '');
        $to = (string) ($filters['tanggal_akhir'] ?? '');
        if ($from !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) === 1) {
            $builder->where('DATE(p.tanggal_register) >=', $from);
        }
        if ($to !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) === 1) {
            $builder->where('DATE(p.tanggal_register) <=', $to);
        }

        $idpelFilter = trim((string) ($filters['idpel'] ?? ''));
        if ($idpelFilter !== '') {
            $builder->like('p.idpel', $idpelFilter);
        }

        $totalBuilder = clone $builder;
        $total = (int) $totalBuilder->countAllResults();

        $searchValue = trim((string) $search);
        if ($searchValue !== '') {
            $builder->groupStart()
                ->like('p.no_agenda', $searchValue)
                ->orLike('p.idpel', $searchValue)
                ->orLike('p.nama', $searchValue)
                ->orLike('p.gol', $searchValue)
                ->orLike('u.unit_name', $searchValue)
                ->groupEnd();
        }

        $filteredBuilder = clone $builder;
        $filtered = (int) $filteredBuilder->countAllResults();

        $builder->orderBy('p.tanggal_register', 'DESC')
            ->orderBy('p.no_agenda', 'DESC')
            ->limit($length > 0 ? $length : 10, max(0, $start));

        return [
            'rows' => $builder->get()->getResultArray(),
            'total' => $total,
            'filtered' => $filtered,
        ];
    }

    /**
     * @param list<array<string,mixed>> $rows
     */
    public function upsertP2TLByAgenda(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $this->db->transStart();
        $insertedCount = 0;
        $errors = [];

        foreach ($rows as $idx => $row) {
            $agenda = (string) ($row['noagenda'] ?? '');
            if ($agenda === '') {
                continue;
            }

            try {
                $this->db->table('trn_p2tl')->replace($row);
                $insertedCount++;
            } catch (\Throwable $e) {
                $errors[] = 'Row ' . ($idx + 1) . ' (agenda: ' . $agenda . '): ' . $e->getMessage();
            }
        }

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            $errMsg = 'Gagal menyimpan data P2TL';
            if ($errors !== []) {
                $errMsg .= '. ' . implode(' | ', array_slice($errors, 0, 3));
            }
            throw new \RuntimeException($errMsg);
        }
    }

    /**
     * @return array{rows: list<array<string,mixed>>, total:int, filtered:int}
     */
    public function getHitRateDatatable(array $filters, int $start, int $length, ?string $search, bool $isAdmin, ?int $userUnitId): array
    {
        $builder = $this->db->table('trn_hitrate h');
        $builder->select('h.*');

        $unit = (string) ($filters['unit'] ?? '*');
        $unitColumn = $this->db->fieldExists('unit_up3', 'trn_hitrate') ? 'unit_up3' : ($this->db->fieldExists('unit_ulp', 'trn_hitrate') ? 'unit_ulp' : 'unit_id');
        if (! $isAdmin && $userUnitId !== null && $this->db->fieldExists($unitColumn, 'trn_hitrate')) {
            $builder->where('h.' . $unitColumn, $userUnitId);
        } elseif ($unit !== '' && $unit !== '*' && $this->db->fieldExists($unitColumn, 'trn_hitrate')) {
            $builder->where('h.' . $unitColumn, (int) $unit);
        }

        if ($this->db->fieldExists('waktu_periksa', 'trn_hitrate')) {
            $from = (string) ($filters['tanggal_awal'] ?? '');
            $to = (string) ($filters['tanggal_akhir'] ?? '');
            if ($from !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) === 1) {
                $builder->where('DATE(h.waktu_periksa) >=', $from);
            }
            if ($to !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) === 1) {
                $builder->where('DATE(h.waktu_periksa) <=', $to);
            }
        }

        $totalBuilder = clone $builder;
        $total = (int) $totalBuilder->countAllResults();

        $searchValue = trim((string) $search);
        if ($searchValue !== '') {
            $builder->groupStart();
            foreach (['id_p2tl', 'id_p2_tl', 'idpel', 'nama', 'tarif', 'update_status'] as $field) {
                if ($this->db->fieldExists($field, 'trn_hitrate')) {
                    $builder->orLike('h.' . $field, $searchValue);
                }
            }
            $builder->groupEnd();
        }

        $filteredBuilder = clone $builder;
        $filtered = (int) $filteredBuilder->countAllResults();

        if ($this->db->fieldExists('id_p2tl', 'trn_hitrate')) {
            $builder->orderBy('h.id_p2tl', 'ASC');
        } elseif ($this->db->fieldExists('id_p2_tl', 'trn_hitrate')) {
            $builder->orderBy('h.id_p2_tl', 'ASC');
        } elseif ($this->db->fieldExists('idpel', 'trn_hitrate')) {
            $builder->orderBy('h.idpel', 'ASC');
        } elseif ($this->db->fieldExists('waktu_periksa', 'trn_hitrate')) {
            $builder->orderBy('h.waktu_periksa', 'DESC');
        }

        $builder->limit($length > 0 ? $length : 10, max(0, $start));

        return [
            'rows' => $builder->get()->getResultArray(),
            'total' => $total,
            'filtered' => $filtered,
        ];
    }

    /**
     * @param list<array<string,mixed>> $rows
     */
    public function insertHitrateBatch(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $this->db->transStart();
        $this->db->table('trn_hitrate')->insertBatch($rows, null, 200);
        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new \RuntimeException('Gagal menyimpan data hitrate.');
        }
    }

    /**
     * @return array{rows: list<array<string,mixed>>, total:int, filtered:int}
     */
    public function getTargetOperasiDatatable(array $filters, int $start, int $length, ?string $search, bool $isAdmin, ?int $userUnitId): array
    {
        $builder = $this->db->table('trn_upload_to t')
            ->select('t.idpel, t.nama, t.tarif, t.daya, t.gardu, t.tiang, t.jam_nyala, t.jenis_to, t.latitude, t.longitude, t.subdlpd, t.unit_id');

        $unit = (string) ($filters['unit'] ?? '*');
        if (! $isAdmin && $userUnitId !== null) {
            $builder->where('t.unit_id', $userUnitId);
        } elseif ($unit !== '' && $unit !== '*') {
            $builder->where('t.unit_id', (int) $unit);
        }

        $totalBuilder = clone $builder;
        $total = (int) $totalBuilder->countAllResults();

        $searchValue = trim((string) $search);
        if ($searchValue !== '') {
            $builder->groupStart()
                ->like('t.idpel', $searchValue)
                ->orLike('t.nama', $searchValue)
                ->orLike('t.tarif', $searchValue)
                ->orLike('t.gardu', $searchValue)
                ->groupEnd();
        }

        $filteredBuilder = clone $builder;
        $filtered = (int) $filteredBuilder->countAllResults();

        $builder->orderBy('t.idpel', 'ASC')
            ->limit($length > 0 ? $length : 10, max(0, $start));

        return [
            'rows' => $builder->get()->getResultArray(),
            'total' => $total,
            'filtered' => $filtered,
        ];
    }

    /**
     * @param list<array<string,mixed>> $rows
     */
    public function insertTargetOperasiBatch(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $this->db->transStart();
        $this->db->table('trn_upload_to')->insertBatch($rows, null, 200);
        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new \RuntimeException('Gagal menyimpan data target operasi.');
        }
    }

    /**
     * @return list<array<string,mixed>>
     */
    public function getAnalisaSummaryExport(int $year, string $unit = '*', string $idpel = '', bool $isAdmin = true, ?int $userUnitId = null, string $temuanStatus = '*'): array
    {
        $rows = $this->getAnalisaSummaryDatatable([
            'tahun' => $year,
            'unit' => $unit,
            'idpel' => $idpel,
            'temuan_status' => $temuanStatus,
        ], 0, 1000000, null, $isAdmin, $userUnitId);

        return $rows['rows'];
    }

    public function replaceAnalisaByPeriodUnit(string $period, int $unitId, array $rows): void
    {
        $this->db->transStart();
        $this->db->table('trn_p2tl_analisa')
            ->where('periode', $period)
            ->where('unit_id', $unitId)
            ->delete();

        if ($rows !== []) {
            $this->db->table('trn_p2tl_analisa')->insertBatch($rows, null, 500);
        }

        $this->db->transComplete();
        if (! $this->db->transStatus()) {
            throw new \RuntimeException('Gagal menyimpan data analisa.');
        }
    }
}
