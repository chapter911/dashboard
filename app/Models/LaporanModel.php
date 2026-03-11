<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\BaseBuilder;

class LaporanModel extends Model
{
    protected $table = 'laporan_harian';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'no_agenda',
        'unit_upi',
        'unit_ap',
        'unit_up',
        'nomor_pdl',
        'idpel',
        'nama',
        'alamat',
        'kddk',
        'nama_prov',
        'nama_kab',
        'nama_kec',
        'nama_kel',
        'tarif',
        'daya',
        'kdpt',
        'kdpt_2',
        'jenis_mk',
        'rp_token',
        'rptotal',
        'tgl_pengaduan',
        'tgl_tindakan_pengaduan',
        'tgl_bayar',
        'tgl_aktivasi',
        'tgl_penangguhan',
        'tgl_restitusi',
        'tgl_remaja',
        'tgl_nyala',
        'tgl_batal',
        'status_permohonan',
        'id_ganti_meter',
        'alasan_ganti_meter',
        'alasan_penangguhan',
        'keterangan_alasan_penangguhan',
        'no_meter_baru',
        'merk_meter_baru',
        'type_meter_baru',
        'thtera_meter_baru',
        'thbuat_meter_baru',
        'no_meter_lama',
        'merk_meter_lama',
        'type_meter_lama',
        'thtera_meter_lama',
        'thbuat_meter_lama',
        'petugas_pengaduan',
        'petugas_tindakan_pengaduan',
        'petugas_aktivasi',
        'petugas_penangguhan',
        'petugas_restitusi',
        'petugas_remaja',
        'petugas_batal',
        'tgl_rekap',
        'tgl_import',
        'kd_pemb_meter',
        'ct_primer_kwh',
        'ct_sekunder_kwh',
        'pt_primer_kwh',
        'pt_sekunder_kwh',
        'konstanta_kwh',
        'fakm_kwh',
        'type_ct_kwh',
        'ct_primer_kvarh',
        'ct_sekunder_kvarh',
        'pt_primer_kvarh',
        'pt_sekunder_kvarh',
        'konstanta_kvarh',
        'fakm_kvarh',
        'type_ct_kvarh',
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
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getAlasanOptions(): array
    {
        return $this->db->table('laporan_harian')
            ->select('alasan_ganti_meter')
            ->where('alasan_ganti_meter IS NOT NULL', null, false)
            ->where('TRIM(alasan_ganti_meter) <>', '')
            ->groupBy('alasan_ganti_meter')
            ->orderBy('alasan_ganti_meter', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getDayaOptions(): array
    {
        return $this->db->table('mst_daya')
            ->select('daya')
            ->groupBy('daya')
            ->orderBy('daya', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getHarianBuilder(array $filters): BaseBuilder
    {
        $builder = $this->db->table('laporan_harian');
        $this->applyHarianFilters($builder, $filters);

        return $builder->orderBy('id', 'DESC');
    }

    private function applyHarianFilters(BaseBuilder $builder, array $filters): void
    {
        if (! empty($filters['tgl_awal'])) {
            $builder->where('tgl_remaja >=', $filters['tgl_awal']);
        }

        if (! empty($filters['tgl_akhir'])) {
            $builder->where('tgl_remaja <=', $filters['tgl_akhir']);
        }

        if (($filters['unit'] ?? '*') !== '*') {
            $builder->where('unit_ap', (int) $filters['unit']);
        }

        $alasan = $filters['alasan'] ?? [];
        if (is_array($alasan) && $alasan !== []) {
            $builder->whereIn('alasan_ganti_meter', $alasan);
        }

        $tahunMeterLama = (string) ($filters['tahun_meter_lama'] ?? '*');
        if ($tahunMeterLama !== '*' && $tahunMeterLama !== '') {
            if ($tahunMeterLama === '0') {
                $builder->where('thbuat_meter_lama IS NULL', null, false);
            } else {
                $builder->where('thbuat_meter_lama', (int) $tahunMeterLama);
            }
        }

        $tarifType = (string) ($filters['tarif'] ?? '*');
        if ($tarifType === 'pra') {
            $builder->like('tarif', 'T');
        } elseif ($tarifType === 'paska') {
            $builder->notLike('tarif', 'T');
        }

        $fasa = (string) ($filters['fasa'] ?? '*');
        if ($fasa !== '*' && $fasa !== '') {
            if ($fasa === '1 Fasa') {
                $builder->where('daya <=', 5500);
            } elseif ($fasa === '3 Fasa') {
                $builder->where('daya >', 5500);
            } elseif (is_numeric($fasa)) {
                $builder->where('daya', (int) $fasa);
            }
        }

        if (! empty($filters['tgl_peremajaan'])) {
            $builder->where('tgl_remaja', $filters['tgl_peremajaan']);
        }

        if (! empty($filters['search'])) {
            $search = (string) $filters['search'];
            $builder->groupStart()
                ->like('idpel', $search)
                ->orLike('nama', $search)
                ->orLike('no_agenda', $search)
                ->groupEnd();
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getDashboardSummary(array $filters): array
    {
        $units = $this->getUnits();
        $summaryBuilder = $this->db->table('mst_unit');
        $summaryBuilder->join('laporan_harian', 'mst_unit.unit_id = laporan_harian.unit_ap', 'left');
        $summaryBuilder->join('mst_daya', 'mst_daya.daya = laporan_harian.daya', 'left');
        $this->applyDashboardFilters($summaryBuilder, $filters);

        $summaryRowsRaw = $summaryBuilder
            ->select(
                "mst_unit.unit_id AS unit_ap,
                mst_unit.unit_name AS unit_name,
                SUM(CASE WHEN (YEAR(laporan_harian.tgl_remaja) - laporan_harian.thbuat_meter_lama) BETWEEN 0 AND 5
                THEN 1 ELSE 0 END) AS usia0,
                SUM(CASE WHEN (YEAR(laporan_harian.tgl_remaja) - laporan_harian.thbuat_meter_lama) BETWEEN 6 AND 10
                THEN 1 ELSE 0 END) AS usia6,
                SUM(CASE WHEN (YEAR(laporan_harian.tgl_remaja) - laporan_harian.thbuat_meter_lama) BETWEEN 11 AND 15
                THEN 1 ELSE 0 END) AS usia11,
                SUM(CASE WHEN (YEAR(laporan_harian.tgl_remaja) - laporan_harian.thbuat_meter_lama) BETWEEN 16 AND 2000
                THEN 1 ELSE 0 END) AS usia16,
                SUM(CASE WHEN ((YEAR(laporan_harian.tgl_remaja) - laporan_harian.thbuat_meter_lama) IS NULL
                    OR (YEAR(laporan_harian.tgl_remaja) - laporan_harian.thbuat_meter_lama) > 2000)
                THEN 1 ELSE 0 END) AS usia_blank,
                COUNT(laporan_harian.unit_ap) AS total",
                false
            )
            ->groupBy('mst_unit.unit_id, mst_unit.unit_name')
            ->orderBy('mst_unit.urutan', 'ASC')
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($summaryRowsRaw as $row) {
            $unitKey = (int) ($row['unit_ap'] ?? 0);
            $map[$unitKey] = [
                'usia0' => (int) ($row['usia0'] ?? 0),
                'usia6' => (int) ($row['usia6'] ?? 0),
                'usia11' => (int) ($row['usia11'] ?? 0),
                'usia16' => (int) ($row['usia16'] ?? 0),
                'usia_blank' => (int) ($row['usia_blank'] ?? 0),
                'total' => (int) ($row['total'] ?? 0),
            ];
        }

        $summaryRows = [];
        foreach ($units as $unit) {
            $unitId = (int) ($unit['unit_id'] ?? 0);
            $sum = $map[$unitId] ?? [
                'usia0' => 0,
                'usia6' => 0,
                'usia11' => 0,
                'usia16' => 0,
                'usia_blank' => 0,
                'total' => 0,
            ];

            $summaryRows[] = [
                'unit_id' => $unitId,
                'unit_name' => (string) ($unit['unit_name'] ?? ''),
                'usia0' => $sum['usia0'],
                'usia6' => $sum['usia6'],
                'usia11' => $sum['usia11'],
                'usia16' => $sum['usia16'],
                'usia_blank' => $sum['usia_blank'],
                'total' => $sum['total'],
            ];
        }

        $sortir = (string) ($filters['sortir'] ?? '*');
        if ($sortir === '1') {
            usort($summaryRows, static fn($a, $b) => ((int) ($b['total'] ?? 0)) <=> ((int) ($a['total'] ?? 0)));
        } elseif ($sortir === '0') {
            usort($summaryRows, static fn($a, $b) => ((int) ($a['total'] ?? 0)) <=> ((int) ($b['total'] ?? 0)));
        }

        $reasonBuilder = $this->db->table('laporan_harian');
        $reasonBuilder->join('mst_daya', 'mst_daya.daya = laporan_harian.daya', 'left');
        $this->applyDashboardFilters($reasonBuilder, $filters);

        $reasonRows = $reasonBuilder
            ->select('alasan_ganti_meter, COUNT(*) AS total')
            ->where('alasan_ganti_meter IS NOT NULL', null, false)
            ->where('TRIM(alasan_ganti_meter) <>', '')
            ->groupBy('alasan_ganti_meter')
            ->orderBy('total', 'DESC')
            ->get()
            ->getResultArray();

        return [
            'summaryRows' => $summaryRows,
            'reasonRows' => $reasonRows,
        ];
    }

    private function applyDashboardFilters(BaseBuilder $builder, array $filters): void
    {
        if (($filters['unit'] ?? '*') !== '*') {
            $builder->where('laporan_harian.unit_ap', (int) $filters['unit']);
        }

        $alasan = $filters['alasan'] ?? [];
        if (is_array($alasan) && $alasan !== []) {
            $builder->whereIn('laporan_harian.alasan_ganti_meter', $alasan);
        }

        $tahunMeterLama = (string) ($filters['tahun_meter_lama'] ?? '*');
        if ($tahunMeterLama !== '*') {
            $builder->where('laporan_harian.thbuat_meter_lama', $tahunMeterLama);
        }

        $tarifType = (string) ($filters['tarif'] ?? '*');
        if ($tarifType === 'pra') {
            $builder->like('laporan_harian.tarif', 'T');
        } elseif ($tarifType === 'paska') {
            $builder->notLike('laporan_harian.tarif', 'T');
        }

        $fasa = (string) ($filters['fasa'] ?? '*');
        if ($fasa !== '*') {
            $builder->groupStart()
                ->where('mst_daya.daya', $fasa)
                ->orWhere('mst_daya.jenis', $fasa)
                ->groupEnd();
        }

        if (! empty($filters['tgl_awal'])) {
            $builder->where('laporan_harian.tgl_remaja >=', $filters['tgl_awal']);
        }

        if (! empty($filters['tgl_akhir'])) {
            $builder->where('laporan_harian.tgl_remaja <=', $filters['tgl_akhir']);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getTargetRows(array $filters): array
    {
        $builder = $this->db->table('trn_target_laporan t')
            ->select('t.id, t.unit_id, t.tahun, t.target_tua, t.target_rusak, u.unit_name')
            ->join('mst_unit u', 'u.unit_id = t.unit_id', 'left')
            ->orderBy('t.tahun', 'DESC')
            ->orderBy('u.urutan', 'ASC');

        if (($filters['tahun'] ?? '*') !== '*') {
            $builder->where('t.tahun', (int) $filters['tahun']);
        }

        if (($filters['unit_id'] ?? '*') !== '*') {
            $builder->where('t.unit_id', (int) $filters['unit_id']);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getTargetsByYear(int $tahun): array
    {
        return $this->db->table('trn_target_laporan')
            ->select('id, unit_id, tahun, target_tua, target_rusak')
            ->where('tahun', $tahun)
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getRealisasiRows(string $type, array $params): array
    {
        $tahun = (int) ($params['tahun'] ?? date('Y'));
        $bulan = (int) ($params['bulan'] ?? date('n'));
        $tanggal = (string) ($params['tgl'] ?? date('Y-m-d'));

        $units = $this->getUnits();
        $targets = $this->db->table('trn_target_laporan')
            ->select('unit_id, target_tua, target_rusak')
            ->where('tahun', $tahun)
            ->get()
            ->getResultArray();

        $targetMap = [];
        foreach ($targets as $target) {
            $targetMap[(int) ($target['unit_id'] ?? 0)] = [
                'target_tua' => (int) ($target['target_tua'] ?? 0),
                'target_rusak' => (int) ($target['target_rusak'] ?? 0),
            ];
        }

        $realBuilder = $this->db->table('laporan_harian')
            ->select("unit_ap,
                SUM(CASE WHEN alasan_ganti_meter = 'Program meter tua' THEN 1 ELSE 0 END) AS real_tua,
                SUM(CASE WHEN (alasan_ganti_meter NOT IN ('Program meter tua', 'Meter ex P2TL', 'Program pemeliharaan meter (kasus khusus)')
                    AND alasan_ganti_meter IS NOT NULL AND alasan_ganti_meter <> '') THEN 1 ELSE 0 END) AS real_rusak", false)
            ->groupBy('unit_ap');

        if ($type === 'harian') {
            $realBuilder->where('tgl_remaja', $tanggal);
        } elseif ($type === 'bulanan') {
            $realBuilder->where('YEAR(tgl_remaja)', $tahun, false);
            $realBuilder->where('MONTH(tgl_remaja)', $bulan, false);
        } else {
            $realBuilder->where('YEAR(tgl_remaja)', $tahun, false);
        }

        $realRows = $realBuilder->get()->getResultArray();
        $realMap = [];
        foreach ($realRows as $real) {
            $realMap[(int) ($real['unit_ap'] ?? 0)] = [
                'real_tua' => (int) ($real['real_tua'] ?? 0),
                'real_rusak' => (int) ($real['real_rusak'] ?? 0),
            ];
        }

        $divisor = 1;
        if ($type === 'harian') {
            $isLeap = (int) date('L', strtotime($tahun . '-01-01')) === 1;
            $divisor = $isLeap ? 366 : 365;
        } elseif ($type === 'bulanan') {
            $divisor = 12;
        }

        $results = [];
        foreach ($units as $unit) {
            $unitId = (int) ($unit['unit_id'] ?? 0);
            $target = $targetMap[$unitId] ?? ['target_tua' => 0, 'target_rusak' => 0];
            $real = $realMap[$unitId] ?? ['real_tua' => 0, 'real_rusak' => 0];

            $adjTargetTua = (int) ceil(((int) $target['target_tua']) / $divisor);
            $adjTargetRusak = (int) ceil(((int) $target['target_rusak']) / $divisor);
            $targetTotal = $adjTargetTua + $adjTargetRusak;
            $realTotal = (int) $real['real_tua'] + (int) $real['real_rusak'];
            $percent = $targetTotal > 0 ? ($realTotal / $targetTotal) * 100 : 0;

            $results[] = [
                'unit_id' => $unitId,
                'unit_name' => (string) ($unit['unit_name'] ?? ''),
                'target_tua' => $adjTargetTua,
                'target_rusak' => $adjTargetRusak,
                'target_total' => $targetTotal,
                'real_tua' => (int) $real['real_tua'],
                'real_rusak' => (int) $real['real_rusak'],
                'real_total' => $realTotal,
                'percent' => $percent,
            ];
        }

        return $results;
    }

    public function getSaldoBuilder(array $filters): BaseBuilder
    {
        $builder = $this->db->table('mst_data_induk_langganan');

        if (($filters['unit'] ?? '*') !== '*') {
            $builder->where('unit_up', (int) $filters['unit']);
        }

        if (($filters['bulan'] ?? '*') !== '*') {
            $builder->where('v_bulan_rekap', (int) $filters['bulan']);
        }

        if (! empty($filters['idpel'])) {
            $builder->where('idpel', (int) $filters['idpel']);
        }

        if (! empty($filters['search'])) {
            $search = (string) $filters['search'];
            $builder->groupStart()
                ->like('idpel', $search)
                ->orLike('nama', $search)
                ->orLike('nomor_meter_kwh', $search)
                ->groupEnd();
        }

        return $builder->orderBy('v_bulan_rekap', 'DESC')->orderBy('idpel', 'ASC');
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getSaldoBulanOptions(): array
    {
        return $this->db->table('mst_data_induk_langganan')
            ->select('v_bulan_rekap')
            ->groupBy('v_bulan_rekap')
            ->orderBy('v_bulan_rekap', 'DESC')
            ->get()
            ->getResultArray();
    }
}
