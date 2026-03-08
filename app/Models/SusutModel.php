<?php

namespace App\Models;

use CodeIgniter\Model;

class SusutModel extends Model
{
    protected $table = 'trn_target_susut';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'unit_id',
        'bulan',
        'tahun',
        'nilai',
        'created_by',
    ];

    /**
     * Map unit_id to trn_analisa_pembelian column.
     *
     * @var array<int, string>
     */
    private array $unitColumnMap = [
        54110 => 'menteng',
        54130 => 'cempaka_putih',
        54210 => 'bandengan',
        54310 => 'bulungan',
        54330 => 'kebun_jeruk',
        54360 => 'ciputat',
        54380 => 'bintaro',
        54410 => 'jati_negara',
        54420 => 'pondok_kopi',
        54510 => 'tanjung_priok',
        54530 => 'marunda',
        54630 => 'cengkareng',
        54710 => 'kramat_jati',
        54720 => 'ciracas',
        54730 => 'pondok_gede',
        54740 => 'lenteng_agung',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function getMonths(): array
    {
        return $this->db->table('mst_bulan')
            ->select('bulan, nama_bulan, singkatan')
            ->orderBy('bulan', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getUnits(bool $includeUid = true, string $sort = 'urutan'): array
    {
        $builder = $this->db->table('mst_unit')
            ->select('unit_id, unit_name, urutan')
            ->where('is_active', 1);

        if (! $includeUid) {
            $builder->where('unit_id <>', 54000);
        }

        if ($sort === 'name') {
            $builder->orderBy('unit_name', 'ASC');
        } else {
            $builder->orderBy('urutan', 'ASC')->orderBy('unit_name', 'ASC');
        }

        return $builder->get()->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getTargetByYear(int $year): array
    {
        return $this->db->table('trn_target_susut')
            ->select('unit_id, bulan, tahun, nilai')
            ->where('tahun', $year)
            ->get()
            ->getResultArray();
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    public function replaceTargetByYear(int $year, array $rows): void
    {
        $this->db->transStart();

        $this->db->table('trn_target_susut')->where('tahun', $year)->delete();

        if ($rows !== []) {
            $this->db->table('trn_target_susut')->insertBatch($rows, null, 500);
        }

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new \RuntimeException('Gagal menyimpan data target susut.');
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getSusutRowsByYear(int $year): array
    {
        $units = $this->getUnits(false);
        $unitIds = array_map(static fn(array $u): int => (int) ($u['unit_id'] ?? 0), $units);

        $analisaRows = $this->db->table('trn_analisa_pembelian')
            ->select('periode, metode, urutan, menteng, cempaka_putih, bandengan, bulungan, kebun_jeruk, ciputat, bintaro, jati_negara, pondok_kopi, tanjung_priok, marunda, cengkareng, kramat_jati, ciracas, pondok_gede, lenteng_agung')
            ->where('YEAR(periode)', $year, false)
            ->get()
            ->getResultArray();

        $pssdRows = $this->db->table('trn_pssd')
            ->select('unit_id, MONTH(periode) AS bulan, SUM(CAST(total_kwh AS DECIMAL(22,4))) AS pssd', false)
            ->where('YEAR(periode)', $year, false)
            ->groupBy('unit_id, MONTH(periode)')
            ->get()
            ->getResultArray();

        $tulRows = $this->db->table('trn_tul')
            ->select('unit_id, MONTH(periode) AS bulan, SUM(COALESCE(pemakaian_jumlah, 0)) AS tul', false)
            ->where('YEAR(periode)', $year, false)
            ->groupBy('unit_id, MONTH(periode)')
            ->get()
            ->getResultArray();

        $eminRows = $this->db->table('trn_emin')
            ->select('unit_id, MONTH(periode) AS bulan, SUM(COALESCE(emin, 0)) AS emin', false)
            ->where('YEAR(periode)', $year, false)
            ->groupBy('unit_id, MONTH(periode)')
            ->get()
            ->getResultArray();

        $nettoByUnitMonth = [];
        $brutoByUnitMonth = [];

        foreach ($analisaRows as $row) {
            $month = (int) date('n', strtotime((string) ($row['periode'] ?? '')));
            $metode = strtolower(trim((string) ($row['metode'] ?? '')));
            $urutan = strtoupper(trim((string) ($row['urutan'] ?? '')));

            foreach ($unitIds as $unitId) {
                $column = $this->unitColumnMap[$unitId] ?? null;
                if ($column === null) {
                    continue;
                }

                $value = (float) ($row[$column] ?? 0);

                if ($metode === 'netto') {
                    $nettoByUnitMonth[$unitId][$month] = ($nettoByUnitMonth[$unitId][$month] ?? 0.0) + $value;
                }

                if ($metode === 'penerimaan' && in_array($urutan, ['A.', 'B.', 'C.', 'D.', 'E.', 'F.', 'G.'], true)) {
                    $brutoByUnitMonth[$unitId][$month] = ($brutoByUnitMonth[$unitId][$month] ?? 0.0) + $value;
                }
            }
        }

        $pssdByUnitMonth = $this->toMatrix($pssdRows, 'pssd');
        $tulByUnitMonth = $this->toMatrix($tulRows, 'tul');
        $eminByUnitMonth = $this->toMatrix($eminRows, 'emin');

        $results = [];
        foreach ($units as $unit) {
            $unitId = (int) ($unit['unit_id'] ?? 0);
            $unitName = (string) ($unit['unit_name'] ?? '');

            $runningNetto = 0.0;
            $runningBruto = 0.0;
            $runningSusutNetto = 0.0;

            for ($month = 1; $month <= 12; $month++) {
                $netto = (float) ($nettoByUnitMonth[$unitId][$month] ?? 0);
                $bruto = (float) ($brutoByUnitMonth[$unitId][$month] ?? 0);
                $pssd = (float) ($pssdByUnitMonth[$unitId][$month] ?? 0);
                $tul = (float) ($tulByUnitMonth[$unitId][$month] ?? 0);
                $emin = (float) ($eminByUnitMonth[$unitId][$month] ?? 0);

                $runningNetto += $netto;
                $runningBruto += $bruto;

                $susutBulananNetto = $netto - $pssd - $tul + $emin;
                $susutBulananBruto = $bruto - $pssd - $tul + $emin;
                $runningSusutNetto += $susutBulananNetto;

                $results[] = [
                    'unit_id' => $unitId,
                    'unit_name' => $unitName,
                    'periode' => sprintf('%04d-%02d-01', $year, $month),
                    'netto' => $netto,
                    'netto_cumulative' => $runningNetto,
                    'bruto' => $bruto,
                    'bruto_cumulative' => $runningBruto,
                    'pssd' => $pssd,
                    'tul' => $tul,
                    'emin' => $emin,
                    'susut_bulanan_netto' => $susutBulananNetto,
                    'susut_bulanan_bruto' => $susutBulananBruto,
                    'netto_tt' => $this->percent($susutBulananNetto, $netto),
                    'netto_cumulative_tt' => $this->percent($runningSusutNetto, $runningNetto),
                    // Keep legacy behavior: bruto percentage uses netto-based susut numerator.
                    'bruto_tt' => $this->percent($susutBulananNetto, $bruto),
                    'bruto_cumulative_tt' => $this->percent($runningSusutNetto, $runningBruto),
                ];
            }
        }

        return $results;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getSusutUidRowsByYear(int $year): array
    {
        $sql = <<<'SQL'
WITH data_pembelian AS (
    SELECT
        periode,
        SUM(CASE WHEN urutan IN ('A.','B.','C.','D.','E.','F.') AND metode = 'penerimaan' THEN uid END) AS terima_uid,
        SUM(CASE WHEN urutan IN ('A','B') AND metode = 'pengiriman' THEN uid END) AS pengiriman,
        SUM(CASE WHEN metode = 'netto' THEN uid END) AS netto
    FROM trn_analisa_pembelian
    WHERE YEAR(periode) = ?
    GROUP BY periode
),
kwh_siap_jual AS (
    SELECT
        pssd.periode,
        dp.terima_uid - SUM(CAST(pssd.total_kwh AS DECIMAL(22,4))) AS total_kwh
    FROM trn_pssd pssd
    LEFT JOIN data_pembelian dp ON pssd.periode = dp.periode
    WHERE YEAR(pssd.periode) = ?
    GROUP BY pssd.periode
),
kwh_jual AS (
    SELECT
        periode,
        pemakaian_jumlah
    FROM trn_tul
    WHERE tarif = 'JUMLAH' AND YEAR(periode) = ?
),
data_emin AS (
    SELECT
        periode,
        SUM(emin) AS emin
    FROM trn_emin
    WHERE unit_id = 54000 AND YEAR(periode) = ?
    GROUP BY periode
),
kalkulasi AS (
    SELECT
        dp.periode,
        COALESCE(dp.terima_uid, 0) AS terima_uid,
        ROUND(
            COALESCE(ksj.total_kwh, 0)
            - COALESCE(dp.pengiriman, 0)
            - COALESCE(kj.pemakaian_jumlah, 0)
            + COALESCE(de.emin, 0),
            0
        ) AS susut_bulanan
    FROM data_pembelian dp
    JOIN kwh_siap_jual ksj ON dp.periode = ksj.periode
    JOIN kwh_jual kj ON dp.periode = kj.periode
    JOIN data_emin de ON dp.periode = de.periode
),
get_akumulasi AS (
    SELECT
        a.periode,
        a.terima_uid,
        a.susut_bulanan,
        SUM(b.terima_uid) AS akumulasi_terima_uid,
        SUM(b.susut_bulanan) AS akumulasi_susut_bulanan
    FROM kalkulasi a
    LEFT JOIN kalkulasi b ON a.periode >= b.periode
    GROUP BY a.periode, a.terima_uid, a.susut_bulanan
)
SELECT
    periode,
    terima_uid,
    susut_bulanan,
    akumulasi_terima_uid,
    akumulasi_susut_bulanan,
    CASE
        WHEN terima_uid = 0 THEN NULL
        ELSE susut_bulanan * 100 / terima_uid
    END AS persentase,
    CASE
        WHEN akumulasi_terima_uid = 0 THEN NULL
        ELSE akumulasi_susut_bulanan * 100 / akumulasi_terima_uid
    END AS akumulasi_persentase
FROM get_akumulasi
ORDER BY periode ASC
SQL;

        $query = $this->db->query($sql, [$year, $year, $year, $year]);

        return $query->getResultArray();
    }

    public function getDashboardSusutBulanan(int $year, int $month, string $jenis = 'netto', int $unitId = 54000): ?float
    {
        $row = $this->db->table('trn_susut')
            ->select('susut')
            ->where('tahun', $year)
            ->where('bulan', $month)
            ->where('LOWER(jenis)', strtolower($jenis))
            ->where('unit_id', $unitId)
            ->orderBy('id', 'DESC')
            ->get(1)
            ->getRowArray();

        if (! is_array($row) || ! array_key_exists('susut', $row)) {
            return null;
        }

        return (float) $row['susut'];
    }

    public function getDashboardSusutKumulatif(int $year, int $month, string $jenis = 'netto', int $unitId = 54000): ?float
    {
        $row = $this->db->table('trn_susut')
            ->select('AVG(susut) AS kumulatif', false)
            ->where('tahun', $year)
            ->where('bulan <=', $month)
            ->where('LOWER(jenis)', strtolower($jenis))
            ->where('unit_id', $unitId)
            ->get()
            ->getRowArray();

        if (! is_array($row) || ! array_key_exists('kumulatif', $row) || $row['kumulatif'] === null) {
            return null;
        }

        return (float) $row['kumulatif'];
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return array<int, array<int, float>>
     */
    private function toMatrix(array $rows, string $valueKey): array
    {
        $matrix = [];

        foreach ($rows as $row) {
            $unitId = (int) ($row['unit_id'] ?? 0);
            $month = (int) ($row['bulan'] ?? 0);
            $value = (float) ($row[$valueKey] ?? 0);
            $matrix[$unitId][$month] = $value;
        }

        return $matrix;
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return array<int, float>
     */
    private function toMonthMap(array $rows, string $valueKey): array
    {
        $map = [];

        foreach ($rows as $row) {
            $month = (int) ($row['bulan'] ?? 0);
            $map[$month] = (float) ($row[$valueKey] ?? 0);
        }

        return $map;
    }

    private function percent(float $numerator, float $denominator): ?float
    {
        if (abs($denominator) < 0.0000001) {
            return null;
        }

        return round(($numerator * 100) / $denominator, 2);
    }
}
