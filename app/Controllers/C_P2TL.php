<?php

namespace App\Controllers;

use App\Models\P2TLModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class C_P2TL extends BaseController
{
    private P2TLModel $p2tlModel;
    private BaseConnection $db;

    public function __construct()
    {
        $this->p2tlModel = new P2TLModel();
        $this->db = db_connect();
    }

    public function index(): string
    {
        return view('p2tl/index', [
            'title' => 'Dashboard P2TL',
            'pageHeading' => 'Dashboard P2TL',
        ]);
    }

    public function getViewIndex(): string
    {
        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        $jenis = strtoupper((string) ($this->request->getPost('jenis_akumulasi') ?? 'TAHUNAN'));

        if ($jenis === 'BULANAN') {
            return view('p2tl/index_bulanan');
        }

        if ($jenis === 'HARIAN') {
            return view('p2tl/index_harian');
        }

        if (in_array($jenis, ['MINGGUAN', 'TRIWULAN', 'SEMESTER'], true)) {
            return view('p2tl/index_pengembangan', [
                'jenisAkumulasi' => $jenis,
            ]);
        }

        return view('p2tl/index_tahunan');
    }

    public function getDataIndex(): string
    {
        $payload = $this->resolveAkumulasiPayload();
        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        return view('p2tl/ajax_index', [
            'data' => $payload['data'],
            'jenis_akumulasi' => $payload['jenis_akumulasi'],
        ]);
    }

    public function getChartIndex(): string
    {
        $payload = $this->resolveAkumulasiPayload();
        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        return view('p2tl/ajax_index_chart', [
            'data' => $payload['data'],
            'jenis_akumulasi' => $payload['jenis_akumulasi'],
        ]);
    }

    public function getDataHitrate(): string
    {
        $tanggalAwal = (string) ($this->request->getPost('tanggal_awal') ?? date('Y-01-01'));
        $tanggalAkhir = (string) ($this->request->getPost('tanggal_akhir') ?? date('Y-m-t'));
        $sortir = (string) ($this->request->getPost('sortir_hitrate') ?? '1');

        $rows = $this->p2tlModel->getHitrateRange($tanggalAwal, $tanggalAkhir, $sortir);
        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        return view('p2tl/ajax_hitrate', [
            'data' => $rows,
        ]);
    }

    public function getChartHitrate(): string
    {
        $tanggalAwal = (string) ($this->request->getPost('tanggal_awal') ?? date('Y-01-01'));
        $tanggalAkhir = (string) ($this->request->getPost('tanggal_akhir') ?? date('Y-m-t'));
        $sortir = (string) ($this->request->getPost('sortir_hitrate') ?? '1');

        $rows = $this->p2tlModel->getHitrateRange($tanggalAwal, $tanggalAkhir, $sortir);
        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        return view('p2tl/ajax_hitrate_chart', [
            'data' => $rows,
        ]);
    }

    public function exportData(): ResponseInterface
    {
        $payload = $this->resolveAkumulasiPayload();
        $rows = $payload['data'];
        $jenis = $payload['jenis_akumulasi'];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Dashboard P2TL');

        $sheet->setCellValue('A1', 'UNIT');
        $sheet->setCellValue('B1', $jenis === 'HARIAN' ? 'TANGGAL' : 'PERIODE');
        $sheet->setCellValue('C1', 'TARGET');
        $sheet->setCellValue('D1', 'REALISASI');
        $sheet->setCellValue('E1', 'PERSENTASE');

        $rowNum = 2;
        foreach ($rows as $row) {
            $target = (float) ($row['target'] ?? 0);
            $realisasi = (float) ($row['realisasi'] ?? 0);
            $persen = $target > 0 ? ($realisasi / $target * 100) : (float) ($row['persentase'] ?? 0);

            $sheet->setCellValue('A' . $rowNum, (string) ($row['unit_name'] ?? '-'));
            if ($jenis === 'HARIAN') {
                $sheet->setCellValue('B' . $rowNum, (string) ($row['tanggal_register'] ?? '-'));
            } elseif ($jenis === 'BULANAN') {
                $sheet->setCellValue('B' . $rowNum, (string) (($row['tahun'] ?? '') . '-' . str_pad((string) ($row['bulan'] ?? ''), 2, '0', STR_PAD_LEFT)));
            } else {
                $sheet->setCellValue('B' . $rowNum, (string) ($row['tahun'] ?? '-'));
            }

            $sheet->setCellValue('C' . $rowNum, $target);
            $sheet->setCellValue('D' . $rowNum, $realisasi);
            $sheet->setCellValue('E' . $rowNum, $persen / 100);
            $rowNum++;
        }

        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        $sheet->getStyle('C2:D' . max(2, $rowNum - 1))->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('E2:E' . max(2, $rowNum - 1))->getNumberFormat()->setFormatCode('0.00%');

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $binary = (string) ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="Dashboard_P2TL_' . date('Ymd_His') . '.xlsx"')
            ->setBody($binary);
    }

    public function exportDataHitrate(): ResponseInterface
    {
        $tanggalAwal = (string) ($this->request->getGet('tanggal_awal') ?? date('Y-01-01'));
        $tanggalAkhir = (string) ($this->request->getGet('tanggal_akhir') ?? date('Y-m-t'));
        $sortir = (string) ($this->request->getGet('sortir_hitrate') ?? '1');
        $rows = $this->p2tlModel->getHitrateRange($tanggalAwal, $tanggalAkhir, $sortir);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Hitrate');
        $sheet->fromArray(['NO', 'UNIT', 'PERIKSA', 'TEMUAN', 'PERSENTASE'], null, 'A1');

        $no = 1;
        $rowNum = 2;
        foreach ($rows as $row) {
            $periksa = (float) ($row['jumlah_periksa'] ?? 0);
            $temuan = (float) ($row['jumlah_temuan'] ?? 0);
            $persentase = $periksa > 0 ? ($temuan / $periksa * 100) : (float) ($row['persentase'] ?? 0);
            $sheet->fromArray([
                $no++,
                (string) ($row['unit_name'] ?? '-'),
                $periksa,
                $temuan,
                $persentase / 100,
            ], null, 'A' . $rowNum);
            $rowNum++;
        }

        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        $sheet->getStyle('C2:D' . max(2, $rowNum - 1))->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('E2:E' . max(2, $rowNum - 1))->getNumberFormat()->setFormatCode('0.00%');
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $binary = (string) ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="Dashboard_P2TL_Hitrate_' . date('Ymd_His') . '.xlsx"')
            ->setBody($binary);
    }

    public function ajaxP2TL(): ResponseInterface
    {
        $draw = (int) ($this->request->getPost('draw') ?? 0);
        $start = max(0, (int) ($this->request->getPost('start') ?? 0));
        $length = (int) ($this->request->getPost('length') ?? 10);
        if ($length < 1) {
            $length = 10;
        }

        $filters = [
            'unit' => (string) ($this->request->getPost('unit') ?? '*'),
            'tanggal_awal' => (string) ($this->request->getPost('tanggal_awal') ?? ''),
            'tanggal_akhir' => (string) ($this->request->getPost('tanggal_akhir') ?? ''),
            'idpel' => (string) ($this->request->getPost('idpel') ?? ''),
        ];

        $isAdmin = (int) (session('group_id') ?? 0) === 1;
        $userUnitId = session('unit_id') !== null ? (int) session('unit_id') : null;
        $search = (string) ($this->request->getPost('search')['value'] ?? '');

        $result = $this->p2tlModel->getP2TLDatatable($filters, $start, $length, $search, $isAdmin, $userUnitId);

        $data = [];
        foreach ($result['rows'] as $row) {
            $data[] = [
                (string) ($row['no_agenda'] ?? ''),
                (string) ($row['idpel'] ?? ''),
                (string) ($row['nama'] ?? ''),
                (string) ($row['gol'] ?? ''),
                (string) ($row['alamat'] ?? ''),
                number_format((float) ($row['daya'] ?? 0), 0, ',', '.'),
                number_format((float) ($row['kwh'] ?? 0), 0, ',', '.'),
                number_format((float) ($row['rupiah_total'] ?? 0), 0, ',', '.'),
                (string) ($row['tanggal_register'] ?? ''),
                (string) ($row['nomor_register'] ?? ''),
                (string) ($row['unit_name'] ?? ''),
            ];
        }

        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());
        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $result['total'],
            'recordsFiltered' => $result['filtered'],
            'data' => $data,
        ]);
    }

    public function importData(): RedirectResponse
    {
        @ini_set('max_execution_time', '300');
        @set_time_limit(300);

        $file = $this->request->getFile('file_import');
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return redirect()->to(site_url('C_P2TL/dataP2TL'))->with('error', 'File upload tidak valid.');
        }

        $extension = strtolower((string) $file->getClientExtension());
        if (! in_array($extension, ['xls', 'xlsx'], true)) {
            return redirect()->to(site_url('C_P2TL/dataP2TL'))->with('error', 'Format file harus .xls atau .xlsx.');
        }

        $tempName = $file->getRandomName();
        $targetPath = WRITEPATH . 'uploads/' . $tempName;

        try {
            $file->move(WRITEPATH . 'uploads', $tempName);
            $sheet = IOFactory::load($targetPath)->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, false);

            $payload = [];
            $username = (string) (session('username') ?? 'system');
            $totalRows = count($rows);
            $processedRows = 0;
            $skippedEmpty = 0;
            $skippedP4 = 0;

            foreach ($rows as $i => $r) {
                if ($i < 8) {
                    continue;
                }

                $agenda = trim((string) ($r[1] ?? ''));
                if ($agenda === '') {
                    $skippedEmpty++;
                    continue;
                }

                $kwhRaw = $this->toNumber($r[11] ?? null);
                $gol = trim((string) ($r[7] ?? ''));
                if ($gol === 'P4' && $kwhRaw <= 0) {
                    $skippedP4++;
                    continue;
                }

                $nomorRegister = trim((string) ($r[30] ?? ''));
                $payload[] = [
                    'noagenda' => $agenda,
                    'idpel' => trim((string) ($r[3] ?? '')),
                    'nama' => trim((string) ($r[4] ?? '')),
                    'gol' => $gol,
                    'alamat' => trim((string) ($r[8] ?? '')),
                    'daya' => $this->toNumber($r[10] ?? null),
                    'kwh' => $kwhRaw,
                    'tagihan_beban' => $this->toNumber($r[12] ?? null),
                    'tagihan_kwh' => $this->toNumber($r[13] ?? null),
                    'tagihan_ts' => $this->toNumber($r[15] ?? null),
                    'materai' => $this->toNumber($r[16] ?? null),
                    'segel' => $this->toNumber($r[19] ?? null),
                    'materia' => $this->toNumber($r[20] ?? null),
                    'rpppj' => $this->toNumber($r[22] ?? null),
                    'rpujl' => $this->toNumber($r[23] ?? null),
                    'rpppn' => $this->toNumber($r[24] ?? null),
                    'rupiah_total' => $this->toNumber($r[25] ?? null),
                    'rupiah_tunai' => $this->toNumber($r[27] ?? null),
                    'rupiah_angsuran' => $this->toNumber($r[28] ?? null),
                    'tanggal_register' => $this->normalizeDateCell($r[29] ?? null),
                    'nomor_register' => $nomorRegister,
                    'tanggal_sph' => $this->normalizeDateCell($r[31] ?? null),
                    'nomor_sph' => trim((string) ($r[33] ?? '')),
                    'unit_id' => (int) substr($nomorRegister, 0, 5),
                    'upload_by' => $username,
                    'upload_date' => date('Y-m-d H:i:s'),
                ];
                $processedRows++;
            }

            if ($payload === []) {
                log_message('warning', 'P2TL_IMPORT_NO_VALID_DATA: Total {total}, Skipped empty {empty}, Skipped P4 {p4}', [
                    'total' => $totalRows,
                    'empty' => $skippedEmpty,
                    'p4' => $skippedP4,
                ]);
                return redirect()->to(site_url('C_P2TL/dataP2TL'))->with('error', 'Tidak ada data valid untuk diimport. Total baris: ' . $totalRows . ', Kosong: ' . $skippedEmpty . ', P4 skip: ' . $skippedP4);
            }

            $this->p2tlModel->upsertP2TLByAgenda($payload);

            log_message('info', 'P2TL_IMPORT_SUCCESS: Total {total}, Processed {processed}, Saved {count}', [
                'total' => $totalRows,
                'processed' => $processedRows,
                'count' => count($payload),
            ]);
        } catch (Throwable $e) {
            log_message('error', 'P2TL_IMPORT_DATA_FAILED: {message}', ['message' => $e->getMessage()]);
            return redirect()->to(site_url('C_P2TL/dataP2TL'))->with('error', 'Import data P2TL gagal diproses: ' . $e->getMessage());
        } finally {
            if (is_file($targetPath)) {
                @unlink($targetPath);
            }
        }

        return redirect()->to(site_url('C_P2TL/dataP2TL'))->with('success', 'Data P2TL berhasil diimport. Diproses: ' . $processedRows . ', Disimpan: ' . count($payload) . '.');
    }

    public function dataP2TL(): string
    {
        $isAdmin = (int) (session('group_id') ?? 0) === 1;
        $userUnitId = session('unit_id') !== null ? (int) session('unit_id') : null;
        $years = $this->p2tlModel->getAvailableP2TLYears($isAdmin, $userUnitId);
        $currentYear = $years[0] ?? (int) date('Y');

        $selectedUnitName = '';
        if (! $isAdmin && $userUnitId !== null) {
            foreach ($this->p2tlModel->getUnits() as $unit) {
                if ((int) ($unit['unit_id'] ?? 0) === $userUnitId) {
                    $selectedUnitName = (string) ($unit['unit_name'] ?? '');
                    break;
                }
            }
        }

        return view('p2tl/data', [
            'title' => 'Data P2TL',
            'pageHeading' => 'Data P2TL',
            'units' => $this->p2tlModel->getUnits(),
            'currentYear' => $currentYear,
            'years' => $years,
            'userGroupId' => (int) (session('group_id') ?? 0),
            'selectedUnitId' => (int) (session('unit_id') ?? 0),
            'selectedUnitName' => $selectedUnitName,
        ]);
    }

    public function DataPemakaian(): string
    {
        $isAdmin = (int) (session('group_id') ?? 0) === 1;
        $userUnitId = session('unit_id') !== null ? (int) session('unit_id') : null;
        $selectedUnitName = '';

        if (! $isAdmin && $userUnitId !== null) {
            foreach ($this->p2tlModel->getUnits() as $unit) {
                if ((int) ($unit['unit_id'] ?? 0) === $userUnitId) {
                    $selectedUnitName = (string) ($unit['unit_name'] ?? '');
                    break;
                }
            }
        }

        return view('p2tl/data_pemakaian', [
            'title' => 'Analisa Data Pemakaian P2TL',
            'pageHeading' => 'Analisa Data Pemakaian P2TL',
            'units' => $this->p2tlModel->getUnits(),
            'currentYear' => (int) date('Y'),
            'userGroupId' => (int) (session('group_id') ?? 0),
            'selectedUnitId' => (int) (session('unit_id') ?? 0),
            'selectedUnitName' => $selectedUnitName,
        ]);
    }

    public function ajaxDashboardPemakaian(): ResponseInterface
    {
        $year = (int) ($this->request->getPost('tahun') ?? date('Y'));
        $unit = trim((string) ($this->request->getPost('unit') ?? ''));

        $isAdmin = (int) (session('group_id') ?? 0) === 1;
        $userUnitId = session('unit_id') !== null ? (int) session('unit_id') : null;
        $unitFilter = ($unit !== '' && $unit !== '*') ? (int) $unit : null;

        $rawRows = $this->p2tlModel->getDataPemakaianDashboard($year, $unitFilter, $isAdmin, $userUnitId);

        $grouped = [];
        foreach ($rawRows as $row) {
            $idpel = (string) ($row['idpel'] ?? '');
            $tarif = (string) ($row['tarif'] ?? '');
            $daya = (float) ($row['daya'] ?? 0);
            $tahun = (int) ($row['tahun'] ?? $year);
            $key = $idpel . '|' . $tarif . '|' . $daya . '|' . $tahun;

            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'idpel' => $idpel,
                    'tarif' => $tarif,
                    'daya' => $daya,
                    'tahun' => $tahun,
                    'months' => array_fill(1, 12, 0.0),
                ];
            }

            $bulan = (int) ($row['bulan'] ?? 0);
            if ($bulan >= 1 && $bulan <= 12) {
                $grouped[$key]['months'][$bulan] = (float) ($row['pemakaian_kwh'] ?? 0);
            }
        }

        $rows = [];
        foreach ($grouped as $group) {
            $dayaInt = (int) round((float) $group['daya']);
            $line = [
                $group['idpel'],
                $group['tarif'],
                number_format((float) $dayaInt, 0, ',', '.'),
                (string) $group['tahun'],
            ];

            for ($m = 1; $m <= 12; $m++) {
                $monthValue = (int) round((float) ($group['months'][$m] ?? 0));
                $line[] = number_format((float) $monthValue, 0, ',', '.');
            }

            $rows[] = $line;
        }

        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());
        return $this->response->setJSON(['data' => $rows]);
    }

    public function ajaxDataPemakaian(): ResponseInterface
    {
        $draw = (int) ($this->request->getPost('draw') ?? 0);
        $start = max(0, (int) ($this->request->getPost('start') ?? 0));
        $length = (int) ($this->request->getPost('length') ?? 10);
        if ($length < 1) {
            $length = 10;
        }

        $filters = [
            'tahun' => (int) ($this->request->getPost('tahun') ?? date('Y')),
            'unit' => (string) ($this->request->getPost('unit') ?? '*'),
            'idpel' => (string) ($this->request->getPost('idpel') ?? ''),
        ];

        $isAdmin = (int) (session('group_id') ?? 0) === 1;
        $userUnitId = session('unit_id') !== null ? (int) session('unit_id') : null;
        $search = (string) ($this->request->getPost('search')['value'] ?? '');

        $result = $this->p2tlModel->getDataPemakaianDatatable($filters, $start, $length, $search, $isAdmin, $userUnitId);

        $data = [];
        $no = $start + 1;
        foreach ($result['rows'] as $row) {
            $data[] = [
                (string) $no++,
                (string) ($row['noagenda'] ?? ''),
                (string) ($row['idpel'] ?? ''),
                (string) ($row['nama'] ?? ''),
                (string) ($row['gol'] ?? ''),
                (string) ($row['alamat'] ?? ''),
                number_format((float) ($row['daya'] ?? 0), 0, ',', '.'),
                number_format((float) ($row['kwh'] ?? 0), 0, ',', '.'),
                number_format((float) ($row['tagihan_beban'] ?? 0), 0, ',', '.'),
                number_format((float) ($row['tagihan_kwh'] ?? 0), 0, ',', '.'),
                number_format((float) ($row['tagihan_ts'] ?? 0), 0, ',', '.'),
                number_format((float) ($row['materai'] ?? 0), 0, ',', '.'),
                number_format((float) ($row['segel'] ?? 0), 0, ',', '.'),
                number_format((float) ($row['materia'] ?? 0), 0, ',', '.'),
                number_format((float) ($row['rpppj'] ?? 0), 0, ',', '.'),
                number_format((float) ($row['rpujl'] ?? 0), 0, ',', '.'),
                number_format((float) ($row['rpppn'] ?? 0), 0, ',', '.'),
                number_format((float) ($row['rupiah_total'] ?? 0), 0, ',', '.'),
                number_format((float) ($row['rupiah_tunai'] ?? 0), 0, ',', '.'),
                number_format((float) ($row['rupiah_angsuran'] ?? 0), 0, ',', '.'),
                (string) ($row['tanggal_register'] ?? ''),
                (string) ($row['nomor_register'] ?? ''),
                (string) ($row['tanggal_sph'] ?? ''),
                (string) ($row['nomor_sph'] ?? ''),
            ];
        }

        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $result['total'],
            'recordsFiltered' => $result['filtered'],
            'data' => $data,
        ]);
    }

    public function exportDataPemakaian(): ResponseInterface
    {
        $filters = [
            'tahun' => (int) ($this->request->getGet('tahun') ?? date('Y')),
            'unit' => (string) ($this->request->getGet('unit') ?? '*'),
            'idpel' => (string) ($this->request->getGet('idpel') ?? ''),
        ];

        $search = trim((string) ($this->request->getGet('search') ?? ''));
        $isAdmin = (int) (session('group_id') ?? 0) === 1;
        $userUnitId = session('unit_id') !== null ? (int) session('unit_id') : null;

        $result = $this->p2tlModel->getDataPemakaianDatatable($filters, 0, 0, $search, $isAdmin, $userUnitId);
        $rows = $result['rows'];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data P2TL');

        $headers = [
            'NO', 'NOAGENDA', 'IDPEL', 'NAMA', 'GOL', 'ALAMAT', 'DAYA', 'KWH',
            'TAGIHAN BEBAN', 'TAGIHAN KWH', 'TAGIHAN TS',
            'MATERAI', 'SEGEL', 'MATERIA', 'RPPPJ', 'RPUJL', 'RPPPN',
            'TOTAL', 'TUNAI', 'ANGSURAN',
            'TANGGAL REGISTER', 'NOMOR REGISTER', 'TANGGAL SPH', 'NOMOR SPH',
        ];
        $sheet->fromArray($headers, null, 'A1');

        $no = 1;
        $rowNum = 2;
        foreach ($rows as $row) {
            $sheet->fromArray([
                $no++,
                (string) ($row['noagenda'] ?? ''),
                (string) ($row['idpel'] ?? ''),
                (string) ($row['nama'] ?? ''),
                (string) ($row['gol'] ?? ''),
                (string) ($row['alamat'] ?? ''),
                (float) ($row['daya'] ?? 0),
                (float) ($row['kwh'] ?? 0),
                (float) ($row['tagihan_beban'] ?? 0),
                (float) ($row['tagihan_kwh'] ?? 0),
                (float) ($row['tagihan_ts'] ?? 0),
                (float) ($row['materai'] ?? 0),
                (float) ($row['segel'] ?? 0),
                (float) ($row['materia'] ?? 0),
                (float) ($row['rpppj'] ?? 0),
                (float) ($row['rpujl'] ?? 0),
                (float) ($row['rpppn'] ?? 0),
                (float) ($row['rupiah_total'] ?? 0),
                (float) ($row['rupiah_tunai'] ?? 0),
                (float) ($row['rupiah_angsuran'] ?? 0),
                (string) ($row['tanggal_register'] ?? ''),
                (string) ($row['nomor_register'] ?? ''),
                (string) ($row['tanggal_sph'] ?? ''),
                (string) ($row['nomor_sph'] ?? ''),
            ], null, 'A' . $rowNum);
            $rowNum++;
        }

        $sheet->getStyle('A1:X1')->getFont()->setBold(true);
        $sheet->getStyle('G2:T' . max(2, $rowNum - 1))->getNumberFormat()->setFormatCode('#,##0');

        foreach (range('A', 'X') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $binary = (string) ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="Data_P2TL_' . date('Ymd_His') . '.xlsx"')
            ->setBody($binary);
    }

    public function Analisa(): string
    {
        $isAdmin = (int) (session('group_id') ?? 0) === 1;
        $userUnitId = session('unit_id') !== null ? (int) session('unit_id') : null;
        $availableYears = $this->p2tlModel->getAvailableAnalisaYears($isAdmin, $userUnitId);
        $currentYear = (int) date('Y');
        $selectedYear = $availableYears !== [] ? (int) $availableYears[0] : $currentYear;

        return view('p2tl/analisa', [
            'title' => 'Analisa P2TL',
            'pageHeading' => 'Analisa P2TL',
            'units' => $this->p2tlModel->getUnits(),
            'currentYear' => $currentYear,
            'selectedYear' => $selectedYear,
            'years' => $availableYears,
            'userGroupId' => (int) (session('group_id') ?? 0),
            'selectedUnitId' => (int) (session('unit_id') ?? 0),
            'selectedUnitName' => (string) (session('unit_name') ?? ''),
        ]);
    }

    public function ajaxAnalisa(): ResponseInterface
    {
        $draw = (int) ($this->request->getPost('draw') ?? 0);
        $start = max(0, (int) ($this->request->getPost('start') ?? 0));
        $length = (int) ($this->request->getPost('length') ?? 10);
        if ($length < 1) {
            $length = 10;
        }

        $filters = [
            'tahun' => (int) ($this->request->getPost('tahun') ?? date('Y')),
            'unit' => (string) ($this->request->getPost('unit') ?? '*'),
            'idpel' => (string) ($this->request->getPost('idpel') ?? ''),
            'temuan_status' => (string) ($this->request->getPost('temuan_status') ?? '*'),
        ];

        $isAdmin = (int) (session('group_id') ?? 0) === 1;
        $userUnitId = session('unit_id') !== null ? (int) session('unit_id') : null;
        $search = (string) ($this->request->getPost('search')['value'] ?? '');

        $result = $this->p2tlModel->getAnalisaSummaryDatatable($filters, $start, $length, $search, $isAdmin, $userUnitId);

        $rows = [];
        $no = $start + 1;
        foreach ($result['rows'] as $row) {
            $jamRata = $row['jam_nyala_rata'] !== null ? (float) $row['jam_nyala_rata'] : null;
            $rataDaya = $row['rata_rata_daya'] !== null ? (float) $row['rata_rata_daya'] : null;

            if ($jamRata === null) {
                $dlpd = '-';
            } elseif ($jamRata < 40) {
                $dlpd = '< 40';
            } elseif ($jamRata > 720) {
                $dlpd = '> 720';
            } else {
                $dlpd = 'Normal';
            }

            $kondisi = ($jamRata !== null && $rataDaya !== null && $jamRata > $rataDaya)
                ? 'diatas rata-rata'
                : 'dibawah rata-rata';

            $idpel = (string) ($row['idpel'] ?? '');

            $rows[] = [
                $no++,
                $idpel,
                (string) ($row['tarif'] ?? '-'),
                number_format((float) ($row['daya'] ?? 0), 0, ',', '.'),
                '-',
                number_format((float) ($row['jam_nyala_rata'] ?? 0), 0, ',', '.'),
                number_format((float) ($row['rata_rata_daya'] ?? 0), 0, ',', '.'),
                $kondisi,
                number_format((float) ($row['jam_nyala_min'] ?? 0), 0, ',', '.'),
                number_format((float) ($row['jam_nyala_max'] ?? 0), 0, ',', '.'),
                $dlpd,
                (int) ($row['counting_emin'] ?? 0),
                '<button type="button" class="btn btn-sm btn-primary" onclick="showDetail(\'' . esc($idpel) . '\')">Detail</button>',
            ];
        }

        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $result['total'],
            'recordsFiltered' => $result['filtered'],
            'data' => $rows,
        ]);
    }

    public function getAnalisaDetailAjax(): ResponseInterface
    {
        $idpel = trim((string) ($this->request->getPost('idpel') ?? ''));
        $year = (int) ($this->request->getPost('tahun') ?? date('Y'));
        $unit = (string) ($this->request->getPost('unit') ?? '*');

        if ($idpel === '') {
            return $this->response->setJSON([
                'has_data' => false,
                'years' => [$year, $year - 1],
                'rows' => [],
            ]);
        }

        $isAdmin = (int) (session('group_id') ?? 0) === 1;
        $userUnitId = session('unit_id') !== null ? (int) session('unit_id') : null;
        $unitFilter = ($unit !== '' && $unit !== '*') ? (int) $unit : null;

        $detail = $this->p2tlModel->getAnalisaDetailByIdpel($idpel, $year, $isAdmin, $userUnitId, $unitFilter);
        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        return $this->response->setJSON($detail);
    }

    public function getAnalisaGrafikAjax(): ResponseInterface
    {
        $idpel = trim((string) ($this->request->getPost('idpel') ?? ''));
        $tahunAcuan = (int) ($this->request->getPost('tahun') ?? date('Y'));
        $unit = (string) ($this->request->getPost('unit') ?? '*');

        if ($idpel === '') {
            return $this->response->setJSON([
                'labels' => ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
                'datasets' => [],
            ]);
        }

        $tahunMulai = $tahunAcuan - 2;

        $isAdmin = (int) (session('group_id') ?? 0) === 1;
        $userUnitId = session('unit_id') !== null ? (int) session('unit_id') : null;
        $unitFilter = ($unit !== '' && $unit !== '*') ? (int) $unit : null;

        $chartSeries = $this->p2tlModel->getAnalisaGrafikByIdpelRange($idpel, $tahunAcuan, $tahunMulai, $isAdmin, $userUnitId, $unitFilter);
        $temuanBulanan = $this->p2tlModel->getAnalisaTemuanBulananByIdpel($idpel, $tahunMulai, $tahunAcuan, $isAdmin, $userUnitId, $unitFilter);
        $labels = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $periodMeta = [
            ['label' => 'Periode 1', 'year' => $tahunAcuan, 'color' => '#1d4ed8'],
            ['label' => 'Periode 2', 'year' => $tahunAcuan - 1, 'color' => '#16a34a'],
            ['label' => 'Periode 3', 'year' => $tahunAcuan - 2, 'color' => '#f59e0b'],
        ];

        $datasets = [];
        foreach ($periodMeta as $meta) {
            $year = (int) $meta['year'];
            $yearSeries = $chartSeries[$year] ?? array_fill(0, 12, null);
            $temuanSeries = [];
            $pointBackgroundColors = [];
            $pointBorderColors = [];
            $pointRadii = [];

            for ($month = 1; $month <= 12; $month++) {
                $temuanInfo = $temuanBulanan[$year][$month] ?? [
                    'count' => 0,
                    'has_temuan' => false,
                    'gol_counts' => [],
                    'gol_detail' => '-',
                ];

                $hasTemuan = (bool) ($temuanInfo['has_temuan'] ?? false);
                $temuanSeries[] = $temuanInfo;
                $pointBackgroundColors[] = $hasTemuan ? '#dc3545' : $meta['color'];
                $pointBorderColors[] = $hasTemuan ? '#dc3545' : '#ffffff';
                $pointRadii[] = $hasTemuan ? 5 : 3;
            }

            $datasets[] = [
                'label' => $meta['label'] . ' (' . $year . ')',
                'data' => array_map(static fn ($value) => $value !== null ? (float) $value : null, $yearSeries),
                'borderColor' => $meta['color'],
                'backgroundColor' => $meta['color'],
                'pointBackgroundColor' => $pointBackgroundColors,
                'pointBorderColor' => $pointBorderColors,
                'pointRadius' => $pointRadii,
                'pointHoverRadius' => 7,
                'fill' => false,
                'tension' => 0.2,
                'spanGaps' => false,
                'temuan' => $temuanSeries,
            ];
        }

        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());
        return $this->response->setJSON([
            'labels' => $labels,
            'datasets' => $datasets,
        ]);
    }

    public function target(): string
    {
        return view('p2tl/target', [
            'title' => 'Target P2TL',
            'pageHeading' => 'Target P2TL',
            'units' => $this->p2tlModel->getUnits(),
            'currentYear' => (int) date('Y'),
        ]);
    }

    public function ajaxTarget(): ResponseInterface
    {
        $year = (int) ($this->request->getPost('tahun') ?? date('Y'));
        $rows = $this->p2tlModel->getTargetByYear($year);

        $data = [];
        foreach ($rows as $row) {
            $targetTahunan = (float) ($row['target_tahunan'] ?? 0);
            $data[] = [
                (int) ($row['unit_id'] ?? 0),
                (string) ($row['unit_name'] ?? ''),
                $year,
                number_format($targetTahunan, 0, ',', '.'),
                number_format($targetTahunan / 12, 0, ',', '.'),
            ];
        }

        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        return $this->response->setJSON([
            'data' => $data,
        ]);
    }

    public function updateTarget(): RedirectResponse
    {
        $year = (int) ($this->request->getPost('tahun') ?? 0);
        if ($year < 2000 || $year > ((int) date('Y') + 5)) {
            return redirect()->to(site_url('C_P2TL/Target'))->with('error', 'Tahun target tidak valid.');
        }

        $unitIds = $this->request->getPost('unit_id');
        $targets = $this->request->getPost('target');

        if (! is_array($unitIds) || ! is_array($targets) || count($unitIds) !== count($targets)) {
            return redirect()->to(site_url('C_P2TL/Target'))->with('error', 'Payload target tidak valid.');
        }

        $rows = [];
        $username = (string) (session('username') ?? 'system');

        foreach ($unitIds as $idx => $unitIdRaw) {
            $unitId = (int) $unitIdRaw;
            if ($unitId <= 0) {
                continue;
            }

            $targetRaw = (string) ($targets[$idx] ?? '0');
            $normalized = str_replace(['.', ','], ['', '.'], $targetRaw);
            $target = is_numeric($normalized) ? (float) $normalized : 0.0;

            $rows[] = [
                'unit_id' => $unitId,
                'tahun' => $year,
                'target_tahunan' => $target,
                'created_by' => $username,
                'created_date' => date('Y-m-d'),
                'updated_by' => $username,
                'updated_date' => date('Y-m-d'),
            ];
        }

        try {
            $this->p2tlModel->replaceTargetByYear($year, $rows);
        } catch (Throwable $e) {
            log_message('error', 'P2TL_TARGET_SAVE_FAILED: {message}', ['message' => $e->getMessage()]);
            return redirect()->to(site_url('C_P2TL/Target'))->with('error', 'Gagal menyimpan target P2TL.');
        }

        return redirect()->to(site_url('C_P2TL/Target'))->with('success', 'Target P2TL berhasil diperbarui.');
    }

    public function ajaxTargetHarian(): ResponseInterface
    {
        $unitId = (int) ($this->request->getPost('unit_id') ?? 0);
        $year = (int) ($this->request->getPost('tahun') ?? date('Y'));

        if ($unitId <= 0 || $year < 2000 || $year > 2100) {
            return $this->response->setStatusCode(400)->setJSON([
                'data' => [],
                'message' => 'Parameter target harian tidak valid.',
            ]);
        }

        $rows = $this->p2tlModel->getTargetHarianByUnitYear($unitId, $year);
        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        return $this->response->setJSON([
            'data' => $rows,
        ]);
    }

    public function updateTargetHarian(): RedirectResponse
    {
        $unitId = (int) ($this->request->getPost('unit_id') ?? 0);
        $year = (int) ($this->request->getPost('tahun') ?? 0);
        $harian = $this->request->getPost('harian');

        if ($unitId <= 0 || $year < 2000 || $year > 2100 || ! is_array($harian)) {
            return redirect()->to(site_url('C_P2TL/Target'))->with('error', 'Payload target harian tidak valid.');
        }

        $payload = [];
        for ($i = 0; $i < 12; $i++) {
            $value = trim((string) ($harian[$i] ?? ''));
            if ($value === '') {
                $payload[] = null;
                continue;
            }

            $normalized = str_replace(['.', ','], ['', '.'], $value);
            $payload[] = is_numeric($normalized) ? (float) $normalized : null;
        }

        $username = (string) (session('username') ?? 'system');

        try {
            $this->p2tlModel->replaceTargetHarianByUnitYear($unitId, $year, $payload, $username);
        } catch (Throwable $e) {
            log_message('error', 'P2TL_TARGET_HARIAN_SAVE_FAILED: {message}', ['message' => $e->getMessage()]);
            return redirect()->to(site_url('C_P2TL/Target'))->with('error', 'Gagal menyimpan target harian: ' . $e->getMessage());
        }

        return redirect()->to(site_url('C_P2TL/Target'))->with('success', 'Target harian berhasil diperbarui.');
    }

    public function exportAnalisaExcel(): ResponseInterface
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        $year = (int) ($this->request->getGet('tahun') ?? date('Y'));
        $unit = (string) ($this->request->getGet('unit') ?? '*');
        $idpel = trim((string) ($this->request->getGet('idpel') ?? ''));
        $temuanStatus = (string) ($this->request->getGet('temuan_status') ?? '*');

        $isAdmin = (int) (session('group_id') ?? 0) === 1;
        $userUnitId = session('unit_id') !== null ? (int) session('unit_id') : null;
        $rows = $this->p2tlModel->getAnalisaSummaryExport($year, $unit, $idpel, $isAdmin, $userUnitId, $temuanStatus);

        $preTemuanHeaders = array_map(static fn(int $n): string => (string) $n, range(1, 24));
        $headers = array_merge(
            ['NO', 'IDPEL', 'TARIF', 'DAYA'],
            $preTemuanHeaders,
            ['JN RATA-RATA', 'RATA-RATA GOL', 'KONDISI', 'MIN', 'MAX', 'DLPD', 'COUNT EMIN']
        );

        $unitLabel = 'Semua Unit';
        if (! $isAdmin && $userUnitId !== null) {
            $unitLabel = (string) (session('unit_name') ?? ('Unit ' . $userUnitId));
        } elseif ($unit !== '' && $unit !== '*') {
            $unitLabel = 'Unit ' . (string) ((int) $unit);
            foreach ($this->p2tlModel->getUnits() as $unitRow) {
                if ((string) ($unitRow['unit_id'] ?? '') === (string) ((int) $unit)) {
                    $unitLabel = (string) ($unitRow['unit_name'] ?? $unitLabel);
                    break;
                }
            }
        }

        $temuanLabel = 'Semua';
        $temuanRaw = strtolower(trim($temuanStatus));
        if ($temuanRaw === 'has') {
            $temuanLabel = 'Ada Temuan';
        } elseif ($temuanRaw === 'none') {
            $temuanLabel = 'Tanpa Temuan';
        } elseif ($temuanRaw !== '' && $temuanRaw !== '*') {
            $temuanLabel = strtoupper($temuanStatus);
        }

        $idpelFilter = $idpel !== '' ? $idpel : 'Semua';
        $exportedBy = (string) (session('username') ?? 'system');
        $exportedAt = date('d-m-Y H:i:s');

        $idpels = [];
        foreach ($rows as $row) {
            $normalizedIdpel = trim((string) ($row['idpel'] ?? ''));
            if ($normalizedIdpel === '') {
                continue;
            }

            $idpels[] = $normalizedIdpel;
        }

        $preTemuanJamNyala = $this->p2tlModel->getAnalisaPreTemuanJamNyalaSeries($year, $idpels, $isAdmin, $userUnitId, $unit);
        $exportRows = [];

        $no = 1;
        foreach ($rows as $row) {
            $jamRata = $row['jam_nyala_rata'] !== null ? (float) $row['jam_nyala_rata'] : null;
            $rata = $row['rata_rata_daya'] !== null ? (float) $row['rata_rata_daya'] : null;
            $dlpd = $jamRata === null ? '-' : ($jamRata < 40 ? '< 40' : ($jamRata > 720 ? '> 720' : 'Normal'));
            $kondisi = ($jamRata !== null && $rata !== null && $jamRata > $rata) ? 'diatas rata-rata' : 'dibawah rata-rata';

            $idpelValue = (string) ($row['idpel'] ?? '');
            $series = $preTemuanJamNyala[$idpelValue] ?? [];
            $preTemuanValues = [];
            for ($i = 1; $i <= 24; $i++) {
                $value = $series[$i] ?? null;
                $preTemuanValues[] = $value === null ? '-' : (int) round((float) $value);
            }

            $baseRow = [
                $no++,
                $idpelValue,
                (string) ($row['tarif'] ?? ''),
                (float) ($row['daya'] ?? 0),
            ];

            $tailRow = [
                (float) ($row['jam_nyala_rata'] ?? 0),
                (float) ($row['rata_rata_daya'] ?? 0),
                $kondisi,
                (float) ($row['jam_nyala_min'] ?? 0),
                (float) ($row['jam_nyala_max'] ?? 0),
                $dlpd,
                (int) ($row['counting_emin'] ?? 0),
            ];

            $exportRows[] = array_merge($baseRow, $preTemuanValues, $tailRow);
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Analisa');
        $columnCount = count($headers);
        $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnCount);

        $sheet->mergeCells('A1:' . $lastColumn . '1');
        $sheet->setCellValue('A1', 'EXPORT ANALISA P2TL');
        $sheet->setCellValue('A3', 'Tahun');
        $sheet->setCellValue('B3', (string) $year);
        $sheet->setCellValue('A4', 'Unit');
        $sheet->setCellValue('B4', $unitLabel);
        $sheet->setCellValue('A5', 'IDPEL');
        $sheet->setCellValue('B5', $idpelFilter);
        $sheet->setCellValue('A6', 'Status Temuan');
        $sheet->setCellValue('B6', $temuanLabel);
        $sheet->setCellValue('D3', 'Diexport Oleh');
        $sheet->setCellValue('E3', $exportedBy);
        $sheet->setCellValue('D4', 'Waktu Export');
        $sheet->setCellValue('E4', $exportedAt);

        $headerRow = 8;
        $sheet->fromArray($headers, null, 'A' . $headerRow);

        // Force pre-temuan headers (1..24) as text so they are always visible in Excel.
        for ($i = 1; $i <= 24; $i++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(4 + $i);
            $sheet->setCellValueExplicit(
                $col . $headerRow,
                (string) $i,
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );
        }

        $rowNum = $headerRow + 1;
        foreach ($exportRows as $line) {
            $sheet->fromArray($line, null, 'A' . $rowNum);
            // Keep IDPEL intact (no scientific notation) by writing it as explicit text.
            $sheet->setCellValueExplicit(
                'B' . $rowNum,
                (string) ($line[1] ?? ''),
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );
            $rowNum++;
        }

        $lastDataRow = max($headerRow + 1, $rowNum - 1);

        // Numeric display formatting for DAYA, kolom 1-24, JN rata-rata, rata-rata gol, MIN, MAX, COUNT EMIN.
        $sheet->getStyle('D' . ($headerRow + 1) . ':AD' . $lastDataRow)
            ->getNumberFormat()
            ->setFormatCode('#,##0');
        $sheet->getStyle('B' . ($headerRow + 1) . ':B' . $lastDataRow)
            ->getNumberFormat()
            ->setFormatCode('@');
        $sheet->getStyle('AF' . ($headerRow + 1) . ':AG' . $lastDataRow)
            ->getNumberFormat()
            ->setFormatCode('#,##0');
        $sheet->getStyle('AI' . ($headerRow + 1) . ':AI' . $lastDataRow)
            ->getNumberFormat()
            ->setFormatCode('#,##0');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:' . $lastColumn . '1')->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1:' . $lastColumn . '1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF1F4E78');
        $sheet->getStyle('A1:' . $lastColumn . '1')->getFont()->getColor()->setARGB('FFFFFFFF');

        $sheet->getStyle('A3:A6')->getFont()->setBold(true);
        $sheet->getStyle('D3:D4')->getFont()->setBold(true);

        $sheet->getStyle('A' . $headerRow . ':' . $lastColumn . $headerRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $headerRow . ':' . $lastColumn . $headerRow)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9E1F2');
        $sheet->getStyle('E' . $headerRow . ':AB' . $headerRow)->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $tableRange = 'A' . $headerRow . ':' . $lastColumn . $lastDataRow;
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
            ->getColor()->setARGB('FFBFBFBF');

        $sheet->freezePane('A' . ($headerRow + 1));

        for ($colIndex = 1; $colIndex <= $columnCount; $colIndex++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $binary = (string) ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="Analisa_P2TL_' . date('Ymd_His') . '.xlsx"')
            ->setHeader('Content-Length', (string) strlen($binary))
            ->setBody($binary);
    }

    public function importAnalisa(): RedirectResponse
    {
        $redirectAnalisa = redirect()->to(site_url('C_P2TL/Analisa'));

        $buildAlert = static function (string $icon, string $title, string $text): array {
            return [
                'icon' => $icon,
                'title' => $title,
                'text' => $text,
            ];
        };

        $file = $this->request->getFile('file_import');
        $year = (int) ($this->request->getPost('tahun') ?? 0);
        if ($year < 2000 || $year > 2100) {
            return $redirectAnalisa
                ->with('error', 'Tahun harus dipilih.')
                ->with('import_analisa_alert', $buildAlert('error', 'Import Gagal', 'Tahun harus dipilih.'));
        }

        $month = (int) ($this->request->getPost('bulan') ?? 0);
        if ($month < 1 || $month > 12) {
            return $redirectAnalisa
                ->with('error', 'Bulan harus dipilih.')
                ->with('import_analisa_alert', $buildAlert('error', 'Import Gagal', 'Bulan harus dipilih.'));
        }

        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return $redirectAnalisa
                ->with('error', 'File upload tidak valid.')
                ->with('import_analisa_alert', $buildAlert('error', 'Import Gagal', 'File upload tidak valid.'));
        }

        $extension = strtolower((string) $file->getClientExtension());
        if (! in_array($extension, ['xls', 'xlsx'], true)) {
            return $redirectAnalisa
                ->with('error', 'Format file harus .xls atau .xlsx.')
                ->with('import_analisa_alert', $buildAlert('error', 'Import Gagal', 'Format file harus .xls atau .xlsx.'));
        }

        $tempName = $file->getRandomName();
        $targetPath = WRITEPATH . 'uploads/' . $tempName;
        $totalRows = 0;
        $invalidRows = 0;
        $insertedRows = 0;
        $processedUnits = 0;

        try {
            $file->move(WRITEPATH . 'uploads', $tempName);
            $sheet = IOFactory::load($targetPath)->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, false);
            $period = sprintf('%04d-%02d-01', $year, $month);
            $username = (string) (session('username') ?? 'system');

            $payloadByUnit = [];
            foreach ($rows as $i => $r) {
                if ($i === 0) {
                    continue;
                }

                $idpel = trim((string) ($r[0] ?? ''));
                if ($idpel === '') {
                    continue;
                }

                $totalRows++;

                $unitCode = substr($idpel, 0, 5);
                if ($unitCode === false || strlen($unitCode) < 5 || ! ctype_digit($unitCode)) {
                    $invalidRows++;
                    continue;
                }

                $unitId = (int) $unitCode;
                if ($unitId <= 0) {
                    $invalidRows++;
                    continue;
                }

                $payloadByUnit[$unitId][] = [
                    'idpel' => $idpel,
                    'tarif' => trim((string) ($r[1] ?? '')),
                    'daya' => $this->toNumber($r[2] ?? null),
                    'periode' => $period,
                    'pemakaian_kwh' => $this->toNullableNumber($r[3] ?? null),
                    'unit_id' => $unitId,
                    'created_by' => $username,
                ];
            }

            foreach ($payloadByUnit as $unitId => $payload) {
                $this->p2tlModel->replaceAnalisaByPeriodUnit($period, (int) $unitId, $payload);
                $processedUnits++;
                $insertedRows += count($payload);
            }
        } catch (Throwable $e) {
            log_message('error', 'P2TL_IMPORT_ANALISA_FAILED: {message}', ['message' => $e->getMessage()]);

            if ($insertedRows > 0 || $processedUnits > 0) {
                $text = 'Sebagian data berhasil diproses sebelum terjadi error. '
                    . 'Baris tersimpan: ' . $insertedRows
                    . ', baris tidak valid: ' . $invalidRows
                    . ', unit berhasil diproses: ' . $processedUnits . '.';

                return $redirectAnalisa
                    ->with('error', 'Import analisa diproses sebagian.')
                    ->with('import_analisa_alert', $buildAlert('warning', 'Import Sebagian', $text));
            }

            return $redirectAnalisa
                ->with('error', 'Import analisa gagal diproses.')
                ->with('import_analisa_alert', $buildAlert('error', 'Import Gagal', 'Tidak ada data yang berhasil diproses.'));
        } finally {
            if (is_file($targetPath)) {
                @unlink($targetPath);
            }
        }

        if ($insertedRows === 0) {
            return $redirectAnalisa
                ->with('error', 'Import analisa gagal diproses.')
                ->with('import_analisa_alert', $buildAlert('error', 'Import Gagal', 'Tidak ada baris valid yang dapat diproses.'));
        }

        if ($invalidRows > 0) {
            $text = 'Data masuk sebagian. '
                . 'Baris tersimpan: ' . $insertedRows
                . ', baris tidak valid: ' . $invalidRows . '.';

            return $redirectAnalisa
                ->with('success', 'Import analisa diproses sebagian.')
                ->with('import_analisa_alert', $buildAlert('warning', 'Import Sebagian', $text));
        }

        $text = 'Seluruh data valid berhasil dimasukkan. Total baris: ' . $insertedRows . '.';

        return $redirectAnalisa
            ->with('success', 'Import analisa berhasil.')
            ->with('import_analisa_alert', $buildAlert('success', 'Import Berhasil', $text));
    }

    public function downloadImportAnalisaTemplate(): ResponseInterface
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import Analisa');

        $headers = ['IDPEL', 'TARIF', 'DAYA', 'PEMAKAIAN_KWH'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray(['12345678901', 'R1', 1300, 245], null, 'A2');
        $sheet->fromArray(['522101234567', 'B2', 5500, 1180], null, 'A3');

        $sheet->getStyle('A1:D1')->getFont()->setBold(true);
        $sheet->getStyle('A1:D1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9E1F2');

        // IDPEL dipaksa text agar tidak berubah scientific notation di Excel.
        $sheet->getStyle('A2:A2000')->getNumberFormat()->setFormatCode('@');
        $sheet->getStyle('C2:D2000')->getNumberFormat()->setFormatCode('#,##0');

        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->setCellValue('A5', 'Catatan: Hapus baris contoh (baris 2-3) sebelum upload data final.');
        $sheet->mergeCells('A5:D5');
        $sheet->getStyle('A5')->getFont()->setItalic(true);

        $guideSheet = $spreadsheet->createSheet();
        $guideSheet->setTitle('Petunjuk');
        $guideSheet->fromArray([
            ['Format Import Analisa P2TL'],
            ['1. Gunakan sheet "Template Import Analisa" untuk isi data.'],
            ['2. Kolom wajib: IDPEL, TARIF, DAYA, PEMAKAIAN_KWH.'],
            ['3. IDPEL wajib terisi dan disarankan format text agar digit tidak hilang.'],
            ['4. DAYA dan PEMAKAIAN_KWH harus angka (tanpa satuan).'],
            ['5. Tahun, Bulan, dan Unit dipilih dari form saat proses import.'],
            ['6. File yang didukung: .xlsx atau .xls.'],
        ], null, 'A1');
        $guideSheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
        $guideSheet->getColumnDimension('A')->setWidth(100);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $binary = (string) ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="Template_Import_Analisa_P2TL.xlsx"')
            ->setHeader('Content-Length', (string) strlen($binary))
            ->setBody($binary);
    }

    public function HitRate(): string
    {
        return view('p2tl/hit_rate', [
            'title' => 'Data Hitrate P2TL',
            'pageHeading' => 'Data Hitrate P2TL',
            'units' => $this->p2tlModel->getUnits(),
        ]);
    }

    public function ajaxHitRate(): ResponseInterface
    {
        $draw = (int) ($this->request->getPost('draw') ?? 0);
        $start = max(0, (int) ($this->request->getPost('start') ?? 0));
        $length = (int) ($this->request->getPost('length') ?? 10);
        if ($length < 1) {
            $length = 10;
        }

        $filters = [
            'unit' => (string) ($this->request->getPost('unit') ?? '*'),
            'tanggal_awal' => (string) ($this->request->getPost('tanggal_awal') ?? ''),
            'tanggal_akhir' => (string) ($this->request->getPost('tanggal_akhir') ?? ''),
        ];

        $isAdmin = (int) (session('group_id') ?? 0) === 1;
        $userUnitId = session('unit_id') !== null ? (int) session('unit_id') : null;
        $search = (string) ($this->request->getPost('search')['value'] ?? '');

        $result = $this->p2tlModel->getHitRateDatatable($filters, $start, $length, $search, $isAdmin, $userUnitId);

        $columnSequence = [
            'id_p2tl', 'idpel', 'nama', 'tarif', 'daya', 'gardu', 'tiang', 'latitude', 'longitude',
            'sesuai_merk', 'merk_meter', 'stand_lwbp', 'stand_wbp', 'stand_kvarh', 'kode_pesan',
            'update_status', 'peruntukan', 'catatan', 'pemutusan', 'kwh_ts', 'waktu_periksa', 'regu',
            'sumber', 'dlpd', 'sub_dlpd', 'material_kwh', 'jenislayanan', 'jenispengukuran', 'nomor_meter',
            'tegangan_meter', 'arus_meter', 'konstanta_meter', 'waktu_meter', 'material_mcb', 'material_box',
            'tegangan_r_n', 'tegangan_s_n', 'tegangan_t_n', 'tegangan_r_s', 'tegangan_s_t', 'tegangan_t_r',
            'beban_primer_r', 'beban_primer_s', 'beban_primer_t', 'beban_sekunder_r', 'beban_sekunder_s',
            'beban_sekunder_t', 'cos_beban_r', 'cos_beban_s', 'cos_beban_t', 'deviasi', 'arus_ct_primer_r',
            'arus_ct_primer_s', 'arus_ct_primer_t', 'arus_ct_sekunder_r', 'arus_ct_sekunder_s',
            'arus_ct_sekunder_t', 'rupiah_ts', 'rupiah_kwh', 'unit_ulp', 'status_kwh', 'nomor_ba',
            'material_ctpt', 'ganti_material', 'durasi_periksa', 'trafo_arus_kwh', 'trafo_tegangan_kwh',
            'faktor_kali_kwh', 'fx_kwh', 'fx_kvarh', 'fx_primer', 'fx_sekunder', 'kva', 'n_kwh', 'n_kvarh',
            't_kwh', 't_kvarh', 'c_kwh', 'c_kvarh', 'irt_primer', 'irt_sekunder', 'cos_irt', 'kwh_p1',
            'kvarh_p1', 'kw_primer', 'faktor_kali_kwh_r', 'deviasi_ct_r', 'deviasi_ct_s', 'deviasi_ct_t',
            'irt_primer_ct', 'irt_sekunder_ct', 'faktor_kali_kwh_irt', 'deviasi_ct', 'tahun_mtr_blm',
            'nomor_mtr_blm', 'kondisi_mtr_blm', 'tahun_mtr_sdh', 'nomor_mtr_sdh', 'kondisi_mtr_sdh',
            'tahun_mon_blm', 'nomor_mon_blm', 'kondisi_mon_blm', 'tahun_mon_sdh', 'nomor_mon_sdh',
            'kondisi_mon_sdh', 'tahun_ct_blm', 'nomor_ct_blm', 'kondisi_ct_blm', 'tahun_ct_sdh',
            'nomor_ct_sdh', 'kondisi_ct_sdh', 'tahun_vt_blm', 'nomor_vt_blm', 'kondisi_vt_blm',
            'tahun_vt_sdh', 'nomor_vt_sdh', 'kondisi_vt_sdh', 'tahun_reley_blm', 'nomor_reley_blm',
            'kondisi_reley_blm', 'tahun_reley_sdh', 'nomor_reley_sdh', 'kondisi_reley_sdh',
            'tahun_pembatas_blm', 'nomor_pembatas_blm', 'kondisi_pembatas_blm', 'tahun_pembatas_sdh',
            'nomor_pembatas_sdh', 'kondisi_pembatas_sdh', 'tahun_boxapp_blm', 'nomor_boxapp_blm',
            'kondisi_boxapp_blm', 'tahun_boxapp_sdh', 'nomor_boxapp_sdh', 'kondisi_boxapp_sdh',
            'tahun_platapp_blm', 'nomor_platapp_blm', 'kondisi_platapp_blm', 'tahun_platapp_sdh',
            'nomor_platapp_sdh', 'kondisi_platapp_sdh', 'tahun_boxamr_blm', 'nomor_boxamr_blm',
            'kondisi_boxamr_blm', 'tahun_boxamr_sdh', 'nomor_boxamr_sdh', 'kondisi_boxamr_sdh',
            'unit_up3', 'unit_uid', 'nik_pelanggan', 'msisdn_pelanggan', 'ts_ap2t', 'no_agenda',
            'tanggal_sph', 'tindaklanjut_pemeriksaan', 'username', 'nama_petugas',
        ];

        $readValue = static function (array $row, string $field): string {
            if ($field === 'id_p2tl') {
                foreach (['id_p2tl', 'id_p2_tl', 'ID_P2TL', 'ID_P2_TL'] as $candidate) {
                    if (array_key_exists($candidate, $row)) {
                        return (string) ($row[$candidate] ?? '');
                    }
                }
            }

            if (array_key_exists($field, $row)) {
                return (string) ($row[$field] ?? '');
            }

            $lower = strtolower($field);
            if (array_key_exists($lower, $row)) {
                return (string) ($row[$lower] ?? '');
            }

            $upper = strtoupper($field);
            if (array_key_exists($upper, $row)) {
                return (string) ($row[$upper] ?? '');
            }

            return '';
        };

        $rows = [];
        foreach ($result['rows'] as $row) {
            $line = [];
            foreach ($columnSequence as $field) {
                $line[] = $readValue($row, $field);
            }
            $rows[] = $line;
        }

        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());
        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $result['total'],
            'recordsFiltered' => $result['filtered'],
            'data' => $rows,
        ]);
    }

    public function importHitRate(): RedirectResponse
    {
        $file = $this->request->getFile('file_import');
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return redirect()->to(site_url('C_P2TL/HitRate'))->with('error', 'File upload tidak valid.');
        }

        $extension = strtolower((string) $file->getClientExtension());
        if (! in_array($extension, ['xls', 'xlsx', 'csv'], true)) {
            return redirect()->to(site_url('C_P2TL/HitRate'))->with('error', 'Format file harus .xls/.xlsx/.csv.');
        }

        $tempName = $file->getRandomName();
        $targetPath = WRITEPATH . 'uploads/' . $tempName;

        try {
            $file->move(WRITEPATH . 'uploads', $tempName);

            if ($extension === 'csv') {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
                $reader->setReadDataOnly(true);
                $sheet = $reader->load($targetPath)->getActiveSheet();
            } else {
                $sheet = IOFactory::load($targetPath)->getActiveSheet();
            }

            $rawRows = $sheet->toArray(null, true, true, false);
            if ($rawRows === []) {
                return redirect()->to(site_url('C_P2TL/HitRate'))->with('error', 'File tidak berisi data.');
            }

            $headers = array_map(static fn($h) => strtolower(trim((string) $h)), (array) ($rawRows[0] ?? []));
            $fieldMap = [];
            foreach ($headers as $idx => $header) {
                if ($header === '') {
                    continue;
                }
                $fieldMap[$idx] = str_replace(' ', '_', $header);
            }

            $allowed = $this->db->getFieldNames('trn_hitrate');
            $allowedFlip = array_fill_keys($allowed, true);
            $username = (string) (session('username') ?? 'system');
            $payload = [];

            for ($i = 1; $i < count($rawRows); $i++) {
                $r = (array) $rawRows[$i];
                if (implode('', array_map(static fn($v) => trim((string) $v), $r)) === '') {
                    continue;
                }

                $row = [];
                foreach ($fieldMap as $idx => $field) {
                    if (! isset($allowedFlip[$field])) {
                        continue;
                    }
                    $val = $r[$idx] ?? null;
                    if (in_array($field, ['daya', 'kwh_ts', 'rupiah_ts', 'rupiah_kwh'], true)) {
                        $val = $this->toNumber($val);
                    }
                    $row[$field] = $val;
                }

                if ($row === []) {
                    continue;
                }

                if (isset($allowedFlip['created_by']) && ! isset($row['created_by'])) {
                    $row['created_by'] = $username;
                }

                $payload[] = $row;
            }

            $this->p2tlModel->insertHitrateBatch($payload);
        } catch (Throwable $e) {
            log_message('error', 'P2TL_IMPORT_HITRATE_FAILED: {message}', ['message' => $e->getMessage()]);
            return redirect()->to(site_url('C_P2TL/HitRate'))->with('error', 'Import hitrate gagal diproses.');
        } finally {
            if (is_file($targetPath)) {
                @unlink($targetPath);
            }
        }

        return redirect()->to(site_url('C_P2TL/HitRate'))->with('success', 'Data hitrate berhasil diimport.');
    }

    public function TargetOperasi(): string
    {
        return view('p2tl/target_operasi', [
            'title' => 'Target Operasi P2TL',
            'pageHeading' => 'Target Operasi P2TL',
            'units' => $this->p2tlModel->getUnits(),
            'userGroupId' => (int) (session('group_id') ?? 0),
            'selectedUnitId' => (int) (session('unit_id') ?? 0),
            'selectedUnitName' => (string) (session('unit_name') ?? ''),
        ]);
    }

    public function dataTargetOperasi(): ResponseInterface
    {
        $draw = (int) ($this->request->getPost('draw') ?? 0);
        $start = max(0, (int) ($this->request->getPost('start') ?? 0));
        $length = (int) ($this->request->getPost('length') ?? 10);
        if ($length < 1) {
            $length = 10;
        }

        $filters = ['unit' => (string) ($this->request->getPost('unit') ?? '*')];
        $isAdmin = (int) (session('group_id') ?? 0) === 1;
        $userUnitId = session('unit_id') !== null ? (int) session('unit_id') : null;
        $search = (string) ($this->request->getPost('search')['value'] ?? '');

        $result = $this->p2tlModel->getTargetOperasiDatatable($filters, $start, $length, $search, $isAdmin, $userUnitId);
        $rows = [];
        foreach ($result['rows'] as $row) {
            $rows[] = [
                (string) ($row['idpel'] ?? ''),
                (string) ($row['nama'] ?? ''),
                (string) ($row['tarif'] ?? ''),
                number_format((float) ($row['daya'] ?? 0), 0, ',', '.'),
                (string) ($row['gardu'] ?? ''),
                (string) ($row['tiang'] ?? ''),
                (string) ($row['unit_id'] ?? ''),
                number_format((float) ($row['jam_nyala'] ?? 0), 2, ',', '.'),
                (string) ($row['jenis_to'] ?? ''),
                '<a class="btn btn-sm btn-primary" target="_blank" href="https://maps.google.com/?q=' . urlencode((string) ($row['latitude'] ?? '')) . ',' . urlencode((string) ($row['longitude'] ?? '')) . '">MAP</a>',
                (string) ($row['subdlpd'] ?? ''),
            ];
        }

        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());
        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $result['total'],
            'recordsFiltered' => $result['filtered'],
            'data' => $rows,
        ]);
    }

    public function importTargetOperasi(): RedirectResponse
    {
        $file = $this->request->getFile('file_import');
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return redirect()->to(site_url('C_P2TL/TargetOperasi'))->with('error', 'File upload tidak valid.');
        }

        $extension = strtolower((string) $file->getClientExtension());
        if (! in_array($extension, ['xls', 'xlsx'], true)) {
            return redirect()->to(site_url('C_P2TL/TargetOperasi'))->with('error', 'Format file harus .xls atau .xlsx.');
        }

        $tempName = $file->getRandomName();
        $targetPath = WRITEPATH . 'uploads/' . $tempName;

        try {
            $file->move(WRITEPATH . 'uploads', $tempName);
            $sheet = IOFactory::load($targetPath)->getActiveSheet();
            $raw = $sheet->toArray(null, true, true, false);
            $unitId = (int) (session('unit_id') ?? 0);
            $username = (string) (session('username') ?? 'system');
            $rows = [];

            foreach ($raw as $i => $r) {
                if ($i === 0) {
                    continue;
                }
                $idpel = trim((string) ($r[1] ?? ''));
                if ($idpel === '') {
                    continue;
                }

                $rows[] = [
                    'idpel' => $idpel,
                    'nama' => trim((string) ($r[2] ?? '')),
                    'tarif' => trim((string) ($r[3] ?? '')),
                    'daya' => $this->toNumber($r[4] ?? null),
                    'gardu' => trim((string) ($r[5] ?? '')),
                    'tiang' => trim((string) ($r[6] ?? '')),
                    'jam_nyala' => $this->toNumber($r[7] ?? null),
                    'jenis_to' => trim((string) ($r[8] ?? '')),
                    'latitude' => trim((string) ($r[9] ?? '')),
                    'longitude' => trim((string) ($r[10] ?? '')),
                    'subdlpd' => trim((string) ($r[11] ?? '')),
                    'unit_id' => $unitId,
                    'created_by' => $username,
                ];
            }

            $this->p2tlModel->insertTargetOperasiBatch($rows);
        } catch (Throwable $e) {
            log_message('error', 'P2TL_IMPORT_TARGET_OPERASI_FAILED: {message}', ['message' => $e->getMessage()]);
            return redirect()->to(site_url('C_P2TL/TargetOperasi'))->with('error', 'Import target operasi gagal diproses.');
        } finally {
            if (is_file($targetPath)) {
                @unlink($targetPath);
            }
        }

        return redirect()->to(site_url('C_P2TL/TargetOperasi'))->with('success', 'Data target operasi berhasil diimport.');
    }

    /**
     * @return array{jenis_akumulasi: string, data: array<int, array<string, mixed>>}
     */
    private function resolveAkumulasiPayload(): array
    {
        $jenis = strtoupper((string) ($this->request->getVar('jenis_akumulasi') ?? 'TAHUNAN'));
        $golongan = (string) ($this->request->getVar('golongan') ?? '*');
        $sortir = (string) ($this->request->getVar('sortir') ?? '1');

        if ($jenis === 'BULANAN') {
            $monthInput = (string) ($this->request->getVar('bulan') ?? date('Y-m'));
            $monthDate = preg_match('/^\d{4}-\d{2}$/', $monthInput) === 1 ? $monthInput . '-01' : date('Y-m-01');
            return [
                'jenis_akumulasi' => $jenis,
                'data' => $this->p2tlModel->getAkumulasiBulanan(
                    (int) date('Y', strtotime($monthDate)),
                    (int) date('n', strtotime($monthDate)),
                    $golongan,
                    $sortir
                ),
            ];
        }

        if ($jenis === 'HARIAN') {
            $dateInput = (string) ($this->request->getVar('tanggal') ?? date('Y-m-d'));
            $date = preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateInput) === 1 ? $dateInput : date('Y-m-d');
            return [
                'jenis_akumulasi' => $jenis,
                'data' => $this->p2tlModel->getAkumulasiHarian($date, $sortir),
            ];
        }

        $startInput = (string) ($this->request->getVar('bulan_awal') ?? date('Y-01-01'));
        $endInput = (string) ($this->request->getVar('bulan_akhir') ?? date('Y-m-d'));

        $startDate = preg_match('/^\d{4}-\d{2}(-\d{2})?$/', $startInput) === 1
            ? (strlen($startInput) === 7 ? $startInput . '-01' : $startInput)
            : date('Y-01-01');

        $endDate = preg_match('/^\d{4}-\d{2}(-\d{2})?$/', $endInput) === 1
            ? (strlen($endInput) === 7 ? date('Y-m-t', strtotime($endInput . '-01')) : $endInput)
            : date('Y-m-d');

        return [
            'jenis_akumulasi' => 'TAHUNAN',
            'data' => $this->p2tlModel->getAkumulasiTahunan($startDate, $endDate, $golongan, $sortir),
        ];
    }

    private function toNumber(mixed $value): float
    {
        if (is_string($value)) {
            $normalized = trim($value);
            if ($normalized === '') {
                return 0.0;
            }

            $normalized = str_replace([' ', ','], ['', '.'], $normalized);
            $value = is_numeric($normalized) ? (float) $normalized : 0.0;
        }

        return is_numeric($value) ? (float) $value : 0.0;
    }

    private function toNullableNumber(mixed $value): ?float
    {
        if (is_string($value)) {
            $normalized = trim($value);
            if ($normalized === '') {
                return null;
            }

            $normalized = str_replace([' ', ','], ['', '.'], $normalized);
            return is_numeric($normalized) ? (float) $normalized : null;
        }

        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function normalizeDateCell(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw) === 1) {
            return $raw;
        }

        $ts = strtotime(str_replace('/', '-', $raw));
        if ($ts === false) {
            return null;
        }

        return date('Y-m-d', $ts);
    }
}
