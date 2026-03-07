<?php

namespace App\Controllers;

use App\Models\LaporanModel;
use CodeIgniter\HTTP\RedirectResponse;
use Throwable;

class C_Laporan extends BaseController
{
    private const HARIAN_IMPORT_COLUMNS = [
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

    private LaporanModel $laporanModel;

    public function __construct()
    {
        $this->laporanModel = new LaporanModel();
    }

    public function index(): string
    {
        $filters = $this->collectDashboardFilters('get');
        $alasanOptions = $this->laporanModel->getAlasanOptions();

        // Legacy behavior: on initial load all alasan options are selected.
        // This means dashboard totals only include rows with non-empty alasan.
        if ($this->request->getGet('alasan') === null) {
            $filters['alasan'] = array_values(array_filter(array_map(
                static fn(array $row): string => trim((string) ($row['alasan_ganti_meter'] ?? '')),
                $alasanOptions
            ), static fn(string $value): bool => $value !== ''));
        }

        return view('laporan/index', [
            'title' => 'Laporan Dashboard',
            'pageHeading' => 'Laporan Dashboard',
            'units' => $this->laporanModel->getUnits(),
            'alasanOptions' => $alasanOptions,
            'dayaOptions' => $this->laporanModel->getDayaOptions(),
            'filters' => $filters,
        ]);
    }

    public function getDataIndex(): string
    {
        $filters = $this->collectDashboardFilters('post');
        $dashboard = $this->laporanModel->getDashboardSummary($filters);

        // CSRF token is regenerated on each successful POST; send the fresh hash
        // so the frontend can update subsequent AJAX requests.
        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        return view('laporan/index_table', [
            'summaryRows' => $dashboard['summaryRows'],
            'reasonRows' => $dashboard['reasonRows'],
        ]);
    }

    public function harian(): string
    {
        $filters = [
            'unit' => (string) ($this->request->getGet('unit') ?? '*'),
            'tahun_meter_lama' => (string) ($this->request->getGet('tahun_meter_lama') ?? '*'),
            'tarif' => (string) ($this->request->getGet('tarif') ?? '*'),
            'fasa' => (string) ($this->request->getGet('fasa') ?? '*'),
            'tgl_peremajaan' => (string) ($this->request->getGet('tgl_peremajaan') ?? ''),
            'search' => trim((string) ($this->request->getGet('search') ?? '')),
            'alasan' => $this->request->getGet('alasan'),
        ];

        if (! is_array($filters['alasan'])) {
            $filters['alasan'] = [];
        }

        $builder = $this->laporanModel->getHarianBuilder($filters);

        $perPage = 50;
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $total = $builder->countAllResults(false);
        $rows = $builder->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();

        $pager = service('pager');
        $pager->makeLinks($page, $perPage, $total, 'default_full');

        return view('laporan/harian', [
            'title' => 'Laporan Harian',
            'pageHeading' => 'Laporan Harian',
            'units' => $this->laporanModel->getUnits(),
            'alasanOptions' => $this->laporanModel->getAlasanOptions(),
            'dayaOptions' => $this->laporanModel->getDayaOptions(),
            'filters' => $filters,
            'rows' => $rows,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'pager' => $pager,
        ]);
    }

    public function importHarian(): RedirectResponse
    {
        $file = $this->request->getFile('file_import');
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return redirect()->to('/C_Laporan/Harian')->with('error', 'File upload tidak valid.');
        }

        $extension = strtolower((string) $file->getClientExtension());
        if (! in_array($extension, ['csv', 'txt'], true)) {
            return redirect()->to('/C_Laporan/Harian')->with('error', 'Hanya file CSV/TXT yang didukung di project ini.');
        }

        $handle = fopen($file->getTempName(), 'r');
        if (! $handle) {
            return redirect()->to('/C_Laporan/Harian')->with('error', 'Gagal membaca file upload.');
        }

        $inserted = 0;
        $updated = 0;
        $rowNumber = 0;

        $dateColumns = [
            'tgl_pengaduan',
            'tgl_tindakan_pengaduan',
            'tgl_bayar',
            'tgl_aktivasi',
            'tgl_penangguhan',
            'tgl_restitusi',
            'tgl_remaja',
            'tgl_nyala',
            'tgl_batal',
            'tgl_rekap',
        ];

        try {
            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                $rowNumber++;

                if ($rowNumber === 1) {
                    $firstCell = strtoupper(trim((string) ($row[0] ?? '')));
                    if (in_array($firstCell, ['NOAGENDA', 'NO_AGENDA'], true)) {
                        continue;
                    }
                }

                $noAgendaRaw = trim((string) ($row[0] ?? ''));
                if ($noAgendaRaw === '') {
                    continue;
                }

                $payload = [];
                foreach (self::HARIAN_IMPORT_COLUMNS as $index => $column) {
                    $value = isset($row[$index]) ? trim((string) $row[$index]) : null;
                    $payload[$column] = $value === '' ? null : $value;
                }

                foreach ($dateColumns as $dateColumn) {
                    $payload[$dateColumn] = $this->toIsoDate($payload[$dateColumn] ?? null);
                }

                $exists = $this->laporanModel->where('no_agenda', $payload['no_agenda'])->first();
                if (is_array($exists)) {
                    $this->laporanModel->update((int) ($exists['id'] ?? 0), $payload);
                    $updated++;
                } else {
                    $this->laporanModel->insert($payload, false);
                    $inserted++;
                }
            }
        } catch (Throwable $e) {
            fclose($handle);
            log_message('error', 'LAPORAN_HARIAN_IMPORT_FAILED: {message}', ['message' => $e->getMessage()]);

            return redirect()->to('/C_Laporan/Harian')->with('error', 'Gagal import data CSV laporan harian.');
        }

        fclose($handle);

        return redirect()->to('/C_Laporan/Harian')->with('success', 'Import selesai. Insert: ' . $inserted . ', Update: ' . $updated . '.');
    }

    public function target(): string
    {
        $filters = [
            'unit_id' => (string) ($this->request->getGet('unit_id') ?? '*'),
            'tahun' => (string) ($this->request->getGet('tahun') ?? '*'),
        ];

        $rows = $this->laporanModel->getTargetRows($filters);

        return view('laporan/target', [
            'title' => 'Target Laporan',
            'pageHeading' => 'Target Laporan',
            'filters' => $filters,
            'units' => $this->laporanModel->getUnits(),
            'rows' => $rows,
            'currentYear' => (int) date('Y'),
        ]);
    }

    public function saveTarget(): RedirectResponse
    {
        $rules = [
            'id' => 'permit_empty|integer',
            'unit_id' => 'required|integer',
            'tahun' => 'required|integer',
            'target_tua' => 'permit_empty|numeric',
            'target_rusak' => 'permit_empty|numeric',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/C_Laporan/Target')->withInput()->with('errors', $this->validator->getErrors());
        }

        $id = (int) ($this->request->getPost('id') ?? 0);
        $unitId = (int) $this->request->getPost('unit_id');
        $tahun = (int) $this->request->getPost('tahun');

        $payload = [
            'unit_id' => $unitId,
            'tahun' => $tahun,
            'target_tua' => (int) ($this->request->getPost('target_tua') ?: 0),
            'target_rusak' => (int) ($this->request->getPost('target_rusak') ?: 0),
        ];

        try {
            $table = $this->db->table('trn_target_laporan');

            if ($id > 0) {
                $table->where('id', $id)->update($payload);
            } else {
                $exists = $table->where('unit_id', $unitId)->where('tahun', $tahun)->get()->getRowArray();
                if (is_array($exists)) {
                    $table->where('id', (int) ($exists['id'] ?? 0))->update($payload);
                } else {
                    $table->insert($payload);
                }
            }
        } catch (Throwable $e) {
            log_message('error', 'LAPORAN_TARGET_SAVE_FAILED: {message}', ['message' => $e->getMessage()]);

            return redirect()->to('/C_Laporan/Target')->withInput()->with('error', 'Gagal menyimpan target.');
        }

        return redirect()->to('/C_Laporan/Target')->with('success', 'Target berhasil disimpan.');
    }

    public function deleteTarget(): RedirectResponse
    {
        $rules = ['id' => 'required|integer'];
        if (! $this->validate($rules)) {
            return redirect()->to('/C_Laporan/Target')->with('error', 'Permintaan hapus target tidak valid.');
        }

        try {
            $this->db->table('trn_target_laporan')->where('id', (int) $this->request->getPost('id'))->delete();
        } catch (Throwable $e) {
            log_message('error', 'LAPORAN_TARGET_DELETE_FAILED: {message}', ['message' => $e->getMessage()]);

            return redirect()->to('/C_Laporan/Target')->with('error', 'Gagal menghapus target.');
        }

        return redirect()->to('/C_Laporan/Target')->with('success', 'Target berhasil dihapus.');
    }

    public function realisasi(): string
    {
        $type = (string) ($this->request->getGet('type') ?? 'tahunan');
        if (! in_array($type, ['tahunan', 'bulanan', 'harian'], true)) {
            $type = 'tahunan';
        }

        $params = [
            'tahun' => (int) ($this->request->getGet('tahun') ?? date('Y')),
            'bulan' => (int) ($this->request->getGet('bulan') ?? date('n')),
            'tgl' => (string) ($this->request->getGet('tgl') ?? date('Y-m-d')),
        ];

        $rows = $this->laporanModel->getRealisasiRows($type, $params);

        $sort = (string) ($this->request->getGet('sort') ?? 'none');
        if ($sort === 'highest') {
            usort($rows, static fn($a, $b) => ($b['percent'] <=> $a['percent']));
        } elseif ($sort === 'lowest') {
            usort($rows, static fn($a, $b) => ($a['percent'] <=> $b['percent']));
        }

        return view('laporan/realisasi', [
            'title' => 'Realisasi Laporan',
            'pageHeading' => 'Realisasi Laporan',
            'type' => $type,
            'params' => $params,
            'sort' => $sort,
            'rows' => $rows,
        ]);
    }

    public function saldo(): string
    {
        $filters = [
            'unit' => (string) ($this->request->getGet('unit') ?? '*'),
            'bulan' => (string) ($this->request->getGet('bulan') ?? '*'),
            'idpel' => trim((string) ($this->request->getGet('idpel') ?? '')),
            'search' => trim((string) ($this->request->getGet('search') ?? '')),
        ];

        $builder = $this->laporanModel->getSaldoBuilder($filters);
        $perPage = 50;
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $total = $builder->countAllResults(false);
        $rows = $builder->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();

        $pager = service('pager');
        $pager->makeLinks($page, $perPage, $total, 'default_full');

        return view('laporan/saldo', [
            'title' => 'Saldo Pelanggan',
            'pageHeading' => 'Saldo Pelanggan',
            'units' => $this->laporanModel->getUnits(),
            'bulanOptions' => $this->laporanModel->getSaldoBulanOptions(),
            'filters' => $filters,
            'rows' => $rows,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'pager' => $pager,
        ]);
    }

    public function updateSaldo(): RedirectResponse
    {
        $rules = [
            'idpel' => 'required|integer',
            'v_bulan_rekap' => 'required|integer',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/C_Laporan/Saldo')->withInput()->with('error', 'Data update saldo tidak valid.');
        }

        $idpel = (int) $this->request->getPost('idpel');
        $bulan = (int) $this->request->getPost('v_bulan_rekap');

        $allowed = [
            'nama', 'nama_pnj', 'tarif', 'daya', 'kdpt_2', 'thbl_mut', 'jenis_mk', 'jenis_layanan',
            'frt', 'kogol', 'fkmkwh', 'nomor_meter_kwh', 'merk_meter_kwh', 'type_meter_kwh',
            'tahun_tera_meter_kwh', 'tahun_buat_meter_kwh', 'nomor_gardu', 'nomor_jurusan_tiang',
            'nama_gardu', 'kapasitas_trafo', 'nomor_meter_prepaid', 'product', 'koordinat_x',
            'koordinat_y', 'kdam', 'kd_pemb_meter', 'ket_kdpembmeter', 'status_dil', 'krn', 'vkrn',
        ];

        $payload = [];
        foreach ($allowed as $field) {
            if ($this->request->getPost($field) !== null) {
                $value = trim((string) $this->request->getPost($field));
                $payload[$field] = $value === '' ? null : $value;
            }
        }

        try {
            $this->db->table('trn_saldo_pelanggan')
                ->where('idpel', $idpel)
                ->where('v_bulan_rekap', $bulan)
                ->update($payload);
        } catch (Throwable $e) {
            log_message('error', 'LAPORAN_SALDO_UPDATE_FAILED: {message}', ['message' => $e->getMessage()]);

            return redirect()->to('/C_Laporan/Saldo')->withInput()->with('error', 'Gagal update saldo pelanggan.');
        }

        return redirect()->to('/C_Laporan/Saldo')->with('success', 'Data saldo berhasil diperbarui.');
    }

    /**
     * @return array<string, mixed>
     */
    private function collectDashboardFilters(string $method = 'get'): array
    {
        $isPost = strtolower($method) === 'post';

        $alasan = $isPost ? $this->request->getPost('alasan') : $this->request->getGet('alasan');

        return [
            'unit' => (string) (($isPost ? $this->request->getPost('unit') : $this->request->getGet('unit')) ?? '*'),
            'tahun_meter_lama' => (string) (($isPost ? $this->request->getPost('tahun_meter_lama') : $this->request->getGet('tahun_meter_lama')) ?? '*'),
            'tarif' => (string) (($isPost ? $this->request->getPost('tarif') : $this->request->getGet('tarif')) ?? '*'),
            'fasa' => (string) (($isPost ? $this->request->getPost('fasa') : $this->request->getGet('fasa')) ?? '*'),
            'tgl_awal' => (string) (($isPost ? $this->request->getPost('tgl_awal') : $this->request->getGet('tgl_awal')) ?? date('Y-m-01')),
            'tgl_akhir' => (string) (($isPost ? $this->request->getPost('tgl_akhir') : $this->request->getGet('tgl_akhir')) ?? date('Y-m-d')),
            'sortir' => (string) (($isPost ? $this->request->getPost('sortir') : $this->request->getGet('sortir')) ?? '*'),
            'alasan' => is_array($alasan) ? $alasan : [],
        ];
    }

    /**
     * @param mixed $value
     */
    private function toIsoDate($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $raw = str_replace('/', '-', $raw);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw) === 1) {
            return $raw;
        }

        $ts = strtotime($raw);
        if ($ts === false) {
            return null;
        }

        return date('Y-m-d', $ts);
    }
}
