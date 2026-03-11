<?php

namespace App\Controllers;

use App\Models\LaporanModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as SpreadsheetDate;
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
    private BaseConnection $db;

    public function __construct()
    {
        $this->laporanModel = new LaporanModel();
        $this->db = db_connect();
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
        $filters = $this->collectHarianFilters('get');

        if ($this->request->getGet('alasan') === null) {
            $filters['alasan'] = array_values(array_filter(array_map(
                static fn(array $row): string => trim((string) ($row['alasan_ganti_meter'] ?? '')),
                $this->laporanModel->getAlasanOptions()
            ), static fn(string $value): bool => $value !== ''));
        }

        return view('laporan/harian', [
            'title' => 'Laporan Harian',
            'pageHeading' => 'Laporan Harian',
            'units' => $this->laporanModel->getUnits(),
            'alasanOptions' => $this->laporanModel->getAlasanOptions(),
            'dayaOptions' => $this->laporanModel->getDayaOptions(),
            'filters' => $filters,
        ]);
    }

    public function harianData(): ResponseInterface
    {
        $draw = (int) ($this->request->getPost('draw') ?? 0);
        $start = max(0, (int) ($this->request->getPost('start') ?? 0));
        $length = (int) ($this->request->getPost('length') ?? 10);
        if ($length < 1) {
            $length = 10;
        }

        $filters = $this->collectHarianFilters('post');

        $orderIndex = (int) ($this->request->getPost('order')[0]['column'] ?? 0);
        $orderDir = strtolower((string) ($this->request->getPost('order')[0]['dir'] ?? 'desc'));
        if (! in_array($orderDir, ['asc', 'desc'], true)) {
            $orderDir = 'desc';
        }

        $columnMap = [
            0 => 'no_agenda',
            1 => 'unit_upi',
            2 => 'unit_ap',
            3 => 'unit_up',
            4 => 'nomor_pdl',
            5 => 'idpel',
            6 => 'nama',
            7 => 'alamat',
            8 => 'kddk',
            9 => 'nama_prov',
            10 => 'nama_kab',
            11 => 'nama_kec',
            12 => 'nama_kel',
            13 => 'tarif',
            14 => 'daya',
            15 => 'kdpt',
            16 => 'kdpt_2',
            17 => 'jenis_mk',
            18 => 'rp_token',
            19 => 'rptotal',
            20 => 'tgl_pengaduan',
            21 => 'tgl_tindakan_pengaduan',
            22 => 'tgl_bayar',
            23 => 'tgl_aktivasi',
            24 => 'tgl_penangguhan',
            25 => 'tgl_restitusi',
            26 => 'tgl_remaja',
            27 => 'tgl_nyala',
            28 => 'tgl_batal',
            29 => 'status_permohonan',
            30 => 'id_ganti_meter',
            31 => 'alasan_ganti_meter',
            32 => 'alasan_penangguhan',
            33 => 'keterangan_alasan_penangguhan',
            34 => 'no_meter_baru',
            35 => 'merk_meter_baru',
            36 => 'type_meter_baru',
            37 => 'thtera_meter_baru',
            38 => 'thbuat_meter_baru',
            39 => 'no_meter_lama',
            40 => 'merk_meter_lama',
            41 => 'type_meter_lama',
            42 => 'thtera_meter_lama',
            43 => 'thbuat_meter_lama',
            44 => 'petugas_pengaduan',
            45 => 'petugas_tindakan_pengaduan',
            46 => 'petugas_aktivasi',
            47 => 'petugas_penangguhan',
            48 => 'petugas_restitusi',
            49 => 'petugas_remaja',
            50 => 'petugas_batal',
            51 => 'tgl_rekap',
            52 => 'kd_pemb_meter',
            53 => 'ct_primer_kwh',
            54 => 'ct_sekunder_kwh',
            55 => 'pt_primer_kwh',
            56 => 'pt_sekunder_kwh',
            57 => 'konstanta_kwh',
            58 => 'fakm_kwh',
            59 => 'type_ct_kwh',
            60 => 'ct_primer_kvarh',
            61 => 'ct_sekunder_kvarh',
            62 => 'pt_primer_kvarh',
            63 => 'pt_sekunder_kvarh',
            64 => 'konstanta_kvarh',
            65 => 'fakm_kvarh',
        ];

        $orderBy = $columnMap[$orderIndex] ?? 'id';

        $total = $this->db->table('laporan_harian')->countAllResults();

        $filteredBuilder = $this->laporanModel->getHarianBuilder($filters);
        $filtered = $filteredBuilder->countAllResults(false);

        $rows = $filteredBuilder
            ->orderBy($orderBy, $orderDir)
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $rows,
        ]);
    }

    public function importHarian(): RedirectResponse
    {
        @ini_set('max_execution_time', '300');
        @set_time_limit(300);

        $file = $this->request->getFile('file_import');
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return redirect()->to('/C_Laporan/Harian')->with('error', 'File upload tidak valid.');
        }

        $extension = strtolower((string) $file->getClientExtension());
        if (! in_array($extension, ['csv', 'txt', 'xls', 'xlsx'], true)) {
            return redirect()->to('/C_Laporan/Harian')->with('error', 'Format file harus .csv, .txt, .xls, atau .xlsx.');
        }

        $inserted = 0;
        $updated = 0;
        $rowNumber = 0;
        $importTimestamp = date('Y-m-d H:i:s');
        $payloadByNoAgenda = [];

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
            $rows = [];
            if (in_array($extension, ['xls', 'xlsx'], true)) {
                $spreadsheet = IOFactory::load($file->getTempName());
                $rows = $spreadsheet->getActiveSheet()->toArray(null, true, false, false);
            } else {
                $handle = fopen($file->getTempName(), 'r');
                if (! $handle) {
                    return redirect()->to('/C_Laporan/Harian')->with('error', 'Gagal membaca file upload.');
                }

                $firstLine = fgets($handle);
                if ($firstLine === false) {
                    fclose($handle);

                    return redirect()->to('/C_Laporan/Harian')->with('error', 'File CSV kosong atau tidak dapat dibaca.');
                }

                $delimiter = substr_count($firstLine, ';') >= substr_count($firstLine, ',') ? ';' : ',';
                rewind($handle);

                while (($csvRow = fgetcsv($handle, 0, $delimiter)) !== false) {
                    $rows[] = $csvRow;
                }

                fclose($handle);
            }

            foreach ($rows as $row) {
                if (! is_array($row) || $row === []) {
                    continue;
                }

                if (isset($row[0])) {
                    // Remove UTF-8 BOM if present to avoid false header/data detection.
                    $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $row[0]) ?? (string) $row[0];
                }

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

                $payload['tgl_import'] = $importTimestamp;
                $payloadByNoAgenda[(string) $payload['no_agenda']] = $payload;
            }

            if ($payloadByNoAgenda === []) {
                return redirect()->to('/C_Laporan/Harian')->with('error', 'Tidak ada data valid untuk diimport.');
            }

            $allNoAgenda = array_keys($payloadByNoAgenda);
            $existingMap = [];

            foreach (array_chunk($allNoAgenda, 1000) as $agendaChunk) {
                $existingRows = $this->db->table('laporan_harian')
                    ->select('id, no_agenda')
                    ->whereIn('no_agenda', $agendaChunk)
                    ->get()
                    ->getResultArray();

                foreach ($existingRows as $existingRow) {
                    $existingNoAgenda = (string) ($existingRow['no_agenda'] ?? '');
                    $existingId = (int) ($existingRow['id'] ?? 0);
                    if ($existingNoAgenda !== '' && $existingId > 0) {
                        $existingMap[$existingNoAgenda] = $existingId;
                    }
                }
            }

            $insertRows = [];
            $updateRows = [];

            foreach ($payloadByNoAgenda as $noAgenda => $payload) {
                if (isset($existingMap[$noAgenda])) {
                    $payload['id'] = $existingMap[$noAgenda];
                    $updateRows[] = $payload;
                    continue;
                }

                $insertRows[] = $payload;
            }

            $this->db->transStart();

            if ($insertRows !== []) {
                foreach (array_chunk($insertRows, 500) as $insertChunk) {
                    $this->laporanModel->insertBatch($insertChunk);
                    $inserted += count($insertChunk);
                }
            }

            if ($updateRows !== []) {
                foreach (array_chunk($updateRows, 500) as $updateChunk) {
                    $this->laporanModel->updateBatch($updateChunk, 'id');
                    $updated += count($updateChunk);
                }
            }

            $this->db->transComplete();

            if (! $this->db->transStatus()) {
                return redirect()->to('/C_Laporan/Harian')->with('error', 'Gagal menyimpan hasil import laporan harian.');
            }
        } catch (Throwable $e) {
            log_message('error', 'LAPORAN_HARIAN_IMPORT_FAILED: {message}', ['message' => $e->getMessage()]);

            return redirect()->to('/C_Laporan/Harian')->with('error', 'Gagal import data laporan harian.');
        }

        return redirect()->to('/C_Laporan/Harian')->with('success', 'Import selesai. Insert: ' . $inserted . ', Update: ' . $updated . '.');
    }

    public function target(): string
    {
        $filters = $this->collectTargetFilters('get');

        return view('laporan/target', [
            'title' => 'Target Laporan',
            'pageHeading' => 'Target Laporan',
            'filters' => $filters,
            'units' => $this->laporanModel->getUnits(),
            'currentYear' => (int) date('Y'),
        ]);
    }

    public function targetData(): ResponseInterface
    {
        $draw = (int) ($this->request->getPost('draw') ?? 0);
        $start = max(0, (int) ($this->request->getPost('start') ?? 0));
        $length = (int) ($this->request->getPost('length') ?? 10);
        if ($length < 1) {
            $length = 10;
        }

        $filters = $this->collectTargetFilters('post');

        $orderIndex = (int) ($this->request->getPost('order')[0]['column'] ?? 2);
        $orderDir = strtolower((string) ($this->request->getPost('order')[0]['dir'] ?? 'desc'));
        if (! in_array($orderDir, ['asc', 'desc'], true)) {
            $orderDir = 'desc';
        }

        $total = $this->db->table('trn_target_laporan')->countAllResults();

        $filteredCountBuilder = $this->buildTargetBaseBuilder($filters);
        $recordsFiltered = $filteredCountBuilder->countAllResults();

        $summaryBuilder = $this->buildTargetBaseBuilder($filters);
        $summary = $summaryBuilder
            ->select('COUNT(*) AS total_rows, COALESCE(SUM(t.target_tua), 0) AS total_tua, COALESCE(SUM(t.target_rusak), 0) AS total_rusak', false)
            ->get()
            ->getRowArray();

        $rowsBuilder = $this->buildTargetBaseBuilder($filters)
            ->select('t.id, t.unit_id, t.tahun, t.target_tua, t.target_rusak, u.unit_name');

        if ($orderIndex === 1) {
            $rowsBuilder->orderBy('u.unit_name', $orderDir);
        } elseif ($orderIndex === 2) {
            $rowsBuilder->orderBy('t.tahun', $orderDir);
        } elseif ($orderIndex === 3) {
            $rowsBuilder->orderBy('t.target_tua', $orderDir);
        } elseif ($orderIndex === 4) {
            $rowsBuilder->orderBy('t.target_rusak', $orderDir);
        } elseif ($orderIndex === 5) {
            $rowsBuilder->orderBy('(t.target_tua + t.target_rusak)', $orderDir, false);
        } else {
            $rowsBuilder->orderBy('t.tahun', 'DESC')->orderBy('u.urutan', 'ASC');
        }

        $rows = $rowsBuilder->limit($length, $start)->get()->getResultArray();

        $data = [];
        foreach ($rows as $index => $row) {
            $targetTua = (int) ($row['target_tua'] ?? 0);
            $targetRusak = (int) ($row['target_rusak'] ?? 0);
            $data[] = [
                'no' => $start + $index + 1,
                'unit_name' => (string) ($row['unit_name'] ?? '-'),
                'tahun' => (string) ($row['tahun'] ?? '-'),
                'target_tua' => number_format($targetTua, 0, ',', '.'),
                'target_rusak' => number_format($targetRusak, 0, ',', '.'),
                'total' => number_format($targetTua + $targetRusak, 0, ',', '.'),
                'aksi' => '<button type="button" class="btn btn-sm btn-label-primary btn-edit-target"'
                    . ' data-id="' . esc((string) ($row['id'] ?? '')) . '"'
                    . ' data-unit="' . esc((string) ($row['unit_id'] ?? '')) . '"'
                    . ' data-tahun="' . esc((string) ($row['tahun'] ?? '')) . '"'
                    . ' data-target-tua="' . esc((string) ($row['target_tua'] ?? '0')) . '"'
                    . ' data-target-rusak="' . esc((string) ($row['target_rusak'] ?? '0')) . '">Edit</button>'
                    . ' <button type="button" class="btn btn-sm btn-label-danger btn-delete-target" data-id="'
                    . esc((string) ($row['id'] ?? '')) . '">Hapus</button>',
            ];
        }

        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
            'meta' => [
                'total_rows' => (int) ($summary['total_rows'] ?? 0),
                'total_tua' => (int) ($summary['total_tua'] ?? 0),
                'total_rusak' => (int) ($summary['total_rusak'] ?? 0),
            ],
        ]);
    }

    public function saveTarget(): ResponseInterface|RedirectResponse
    {
        $rules = [
            'id' => 'permit_empty|integer',
            'unit_id' => 'required|integer',
            'tahun' => 'required|integer',
            'target_tua' => 'permit_empty|numeric',
            'target_rusak' => 'permit_empty|numeric',
        ];

        if (! $this->validate($rules)) {
            if ($this->request->isAJAX()) {
                $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Data target tidak valid.',
                    'errors' => $this->validator->getErrors(),
                ])->setStatusCode(422);
            }

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

            if ($this->request->isAJAX()) {
                $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Gagal menyimpan target.',
                ])->setStatusCode(500);
            }

            return redirect()->to('/C_Laporan/Target')->withInput()->with('error', 'Gagal menyimpan target.');
        }

        if ($this->request->isAJAX()) {
            $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Target berhasil disimpan.',
            ]);
        }

        return redirect()->to('/C_Laporan/Target')->with('success', 'Target berhasil disimpan.');
    }

    public function deleteTarget(): ResponseInterface|RedirectResponse
    {
        $rules = ['id' => 'required|integer'];
        if (! $this->validate($rules)) {
            if ($this->request->isAJAX()) {
                $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Permintaan hapus target tidak valid.',
                ])->setStatusCode(422);
            }

            return redirect()->to('/C_Laporan/Target')->with('error', 'Permintaan hapus target tidak valid.');
        }

        try {
            $this->db->table('trn_target_laporan')->where('id', (int) $this->request->getPost('id'))->delete();
        } catch (Throwable $e) {
            log_message('error', 'LAPORAN_TARGET_DELETE_FAILED: {message}', ['message' => $e->getMessage()]);

            if ($this->request->isAJAX()) {
                $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Gagal menghapus target.',
                ])->setStatusCode(500);
            }

            return redirect()->to('/C_Laporan/Target')->with('error', 'Gagal menghapus target.');
        }

        if ($this->request->isAJAX()) {
            $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Target berhasil dihapus.',
            ]);
        }

        return redirect()->to('/C_Laporan/Target')->with('success', 'Target berhasil dihapus.');
    }

    public function realisasi(): string
    {
        $filters = $this->collectRealisasiFilters('get');
        $rows = $this->buildRealisasiRows($filters['type'], $filters['params'], $filters['sort']);

        return view('laporan/realisasi', [
            'title' => 'Realisasi Laporan',
            'pageHeading' => 'Realisasi Laporan',
            'type' => $filters['type'],
            'params' => $filters['params'],
            'sort' => $filters['sort'],
            'rows' => $rows,
        ]);
    }

    public function realisasiData(): string
    {
        $filters = $this->collectRealisasiFilters('post');
        $rows = $this->buildRealisasiRows($filters['type'], $filters['params'], $filters['sort']);

        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        return view('laporan/realisasi_content', [
            'params' => $filters['params'],
            'rows' => $rows,
        ]);
    }

    public function saldo(): string
    {
        $filters = $this->collectSaldoFilters('get');

        return view('laporan/saldo', [
            'title' => 'Saldo Pelanggan',
            'pageHeading' => 'Saldo Pelanggan',
            'units' => $this->laporanModel->getUnits(),
            'bulanOptions' => $this->laporanModel->getSaldoBulanOptions(),
            'filters' => $filters,
        ]);
    }

    public function saldoData(): ResponseInterface
    {
        $draw = (int) ($this->request->getPost('draw') ?? 0);
        $start = max(0, (int) ($this->request->getPost('start') ?? 0));
        $length = (int) ($this->request->getPost('length') ?? 10);
        if ($length < 1) {
            $length = 10;
        }

        $filters = $this->collectSaldoFilters('post');

        $orderIndex = (int) ($this->request->getPost('order')[0]['column'] ?? 0);
        $orderDir = strtolower((string) ($this->request->getPost('order')[0]['dir'] ?? 'desc'));
        if (! in_array($orderDir, ['asc', 'desc'], true)) {
            $orderDir = 'desc';
        }

        $columnMap = [
            0 => 'v_bulan_rekap',
            1 => 'unit_up',
            2 => 'idpel',
            3 => 'nama',
            4 => 'nama_pnj',
            5 => 'tarif',
            6 => 'daya',
            7 => 'kdpt_2',
            8 => 'thbl_mut',
            9 => 'jenis_mk',
            10 => 'jenis_layanan',
            11 => 'frt',
            12 => 'kogol',
            13 => 'fkmkwh',
            14 => 'nomor_meter_kwh',
            15 => 'tanggal_pasang_rubah_app',
            16 => 'merk_meter_kwh',
            17 => 'type_meter_kwh',
            18 => 'tahun_tera_meter_kwh',
            19 => 'tahun_buat_meter_kwh',
            20 => 'nomor_gardu',
            21 => 'nomor_jurusan_tiang',
            22 => 'nama_gardu',
            23 => 'kapasitas_trafo',
            24 => 'nomor_meter_prepaid',
            25 => 'product',
            26 => 'koordinat_x',
            27 => 'koordinat_y',
            28 => 'kdam',
            29 => 'kd_pemb_meter',
            30 => 'ket_kdpembmeter',
            31 => 'status_dil',
            32 => 'krn',
            33 => 'vkrn',
        ];

        $orderBy = $columnMap[$orderIndex] ?? 'idpel';

        $total = $this->db->table('mst_data_induk_langganan')->countAllResults();

        $filteredBuilder = $this->laporanModel->getSaldoBuilder($filters);
        $filtered = $filteredBuilder->countAllResults(false);

        $rows = $filteredBuilder
            ->orderBy($orderBy, $orderDir)
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        foreach ($rows as &$row) {
            $idpel = (string) ($row['idpel'] ?? '');
            $bulan = (string) ($row['v_bulan_rekap'] ?? '');
            $nama = (string) ($row['nama'] ?? '');
            $tarif = (string) ($row['tarif'] ?? '');
            $daya = (string) ($row['daya'] ?? '');
            $meter = (string) ($row['nomor_meter_kwh'] ?? '');

            $row['aksi'] = '<button type="button" class="btn btn-sm btn-label-primary btn-edit-saldo"'
                . ' data-idpel="' . esc($idpel) . '"'
                . ' data-bulan="' . esc($bulan) . '"'
                . ' data-nama="' . esc($nama) . '"'
                . ' data-tarif="' . esc($tarif) . '"'
                . ' data-daya="' . esc($daya) . '"'
                . ' data-meter="' . esc($meter) . '"'
                . ' data-bs-toggle="modal" data-bs-target="#saldoModal">Update</button>';
        }
        unset($row);

        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $rows,
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
            $this->db->table('mst_data_induk_langganan')
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
     * @return array<string, mixed>
     */
    private function collectHarianFilters(string $method = 'get'): array
    {
        $isPost = strtolower($method) === 'post';
        $alasan = $isPost ? $this->request->getPost('alasan') : $this->request->getGet('alasan');

        return [
            'unit' => (string) (($isPost ? $this->request->getPost('unit') : $this->request->getGet('unit')) ?? '*'),
            'tahun_meter_lama' => (string) (($isPost ? $this->request->getPost('tahun_meter_lama') : $this->request->getGet('tahun_meter_lama')) ?? '*'),
            'tarif' => (string) (($isPost ? $this->request->getPost('tarif') : $this->request->getGet('tarif')) ?? '*'),
            'fasa' => (string) (($isPost ? $this->request->getPost('fasa') : $this->request->getGet('fasa')) ?? '*'),
            'tgl_peremajaan' => (string) (($isPost ? $this->request->getPost('tgl_peremajaan') : $this->request->getGet('tgl_peremajaan')) ?? ''),
            'search' => '',
            'alasan' => is_array($alasan) ? $alasan : [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function collectSaldoFilters(string $method = 'get'): array
    {
        $isPost = strtolower($method) === 'post';

        return [
            'unit' => (string) (($isPost ? $this->request->getPost('unit') : $this->request->getGet('unit')) ?? '*'),
            'bulan' => (string) (($isPost ? $this->request->getPost('bulan') : $this->request->getGet('bulan')) ?? '*'),
            'idpel' => trim((string) (($isPost ? $this->request->getPost('idpel') : $this->request->getGet('idpel')) ?? '')),
            'search' => '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function collectTargetFilters(string $method = 'get'): array
    {
        $isPost = strtolower($method) === 'post';

        return [
            'unit_id' => (string) (($isPost ? $this->request->getPost('unit_id') : $this->request->getGet('unit_id')) ?? '*'),
            'tahun' => (string) (($isPost ? $this->request->getPost('tahun') : $this->request->getGet('tahun')) ?? '*'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function collectRealisasiFilters(string $method = 'get'): array
    {
        $isPost = strtolower($method) === 'post';

        $type = (string) (($isPost ? $this->request->getPost('type') : $this->request->getGet('type')) ?? 'tahunan');
        if (! in_array($type, ['tahunan', 'bulanan', 'harian'], true)) {
            $type = 'tahunan';
        }

        $sort = (string) (($isPost ? $this->request->getPost('sort') : $this->request->getGet('sort')) ?? 'none');
        if (! in_array($sort, ['none', 'highest', 'lowest'], true)) {
            $sort = 'none';
        }

        return [
            'type' => $type,
            'sort' => $sort,
            'params' => [
                'tahun' => (int) (($isPost ? $this->request->getPost('tahun') : $this->request->getGet('tahun')) ?? date('Y')),
                'bulan' => (int) (($isPost ? $this->request->getPost('bulan') : $this->request->getGet('bulan')) ?? date('n')),
                'tgl' => (string) (($isPost ? $this->request->getPost('tgl') : $this->request->getGet('tgl')) ?? date('Y-m-d')),
            ],
        ];
    }

    /**
     * @param array<string, mixed> $params
     * @return array<int, array<string, mixed>>
     */
    private function buildRealisasiRows(string $type, array $params, string $sort): array
    {
        $rows = $this->laporanModel->getRealisasiRows($type, $params);

        $rows = array_values(array_filter($rows, static function (array $row): bool {
            $unitName = strtoupper(trim((string) ($row['unit_name'] ?? '')));
            return $unitName !== 'UID JAYA';
        }));

        if ($sort === 'highest') {
            usort($rows, static fn($a, $b) => (($b['percent'] ?? 0) <=> ($a['percent'] ?? 0)));
        } elseif ($sort === 'lowest') {
            usort($rows, static fn($a, $b) => (($a['percent'] ?? 0) <=> ($b['percent'] ?? 0)));
        }

        return $rows;
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function buildTargetBaseBuilder(array $filters)
    {
        $builder = $this->db->table('trn_target_laporan t')
            ->join('mst_unit u', 'u.unit_id = t.unit_id', 'left');

        if (($filters['tahun'] ?? '*') !== '*') {
            $builder->where('t.tahun', (int) $filters['tahun']);
        }

        if (($filters['unit_id'] ?? '*') !== '*') {
            $builder->where('t.unit_id', (int) $filters['unit_id']);
        }

        return $builder;
    }

    /**
     * @param mixed $value
     */
    private function toIsoDate($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            $excelSerial = (float) $value;
            if ($excelSerial > 0) {
                try {
                    return SpreadsheetDate::excelToDateTimeObject($excelSerial)->format('Y-m-d');
                } catch (Throwable $e) {
                    // Continue to string parsing fallback below.
                }
            }
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
