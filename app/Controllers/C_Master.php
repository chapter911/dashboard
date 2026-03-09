<?php

namespace App\Controllers;

use App\Models\KategoriTeganganModel;
use App\Models\PelangganMasterModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use Throwable;

class C_Master extends BaseController
{
    private const ALLOWED_KATEGORI = ['TR', 'TM', 'TT'];
    private const TABLE_PELANGGAN = 'mst_data_induk_langganan';
    private const PELANGGAN_IMPORT_STATE_PREFIX = 'pelanggan_import_';
    private const PELANGGAN_IMPORT_CHUNK_SIZE = 1000;
    private const PELANGGAN_IMPORT_BATCH_SIZE = 500;
    private const PELANGGAN_IMPORT_MAX_CHUNKS_PER_REQUEST = 1;
    private const PELANGGAN_IMPORT_MAX_UPLOAD_MB = 40;

    public function kategoriTegangan(): string
    {
        $model = new KategoriTeganganModel();

        try {
            $rows = $model->getKategoriByTarif();
            $tarifOptions = $model->getTarifOptions();
        } catch (Throwable $e) {
            log_message('error', 'KATEGORI_TEGANGAN_LOAD_FAILED: {message}', ['message' => $e->getMessage()]);
            session()->setFlashdata('error', 'Gagal memuat data kategori tegangan.');
            $rows = [];
            $tarifOptions = [];
        }

        return view('master/kategori_tegangan', [
            'title' => 'Kategori Tegangan',
            'pageHeading' => 'Kategori Tegangan',
            'rows' => $rows,
            'tarifOptions' => $tarifOptions,
            'kategoriOptions' => self::ALLOWED_KATEGORI,
        ]);
    }

    public function saveKategoriTegangan(): RedirectResponse
    {
        $rules = [
            'id' => 'permit_empty|integer',
            'tarif' => 'required|max_length[100]',
            'kategori_tegangan' => 'required|in_list[TR,TM,TT]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/C_Master/KategoriTegangan')->withInput()->with('errors', $this->validator->getErrors());
        }

        $id = (int) ($this->request->getPost('id') ?? 0);
        $tarif = strtoupper(trim((string) $this->request->getPost('tarif')));
        $kategori = strtoupper(trim((string) $this->request->getPost('kategori_tegangan')));

        if ($tarif === '') {
            return redirect()->to('/C_Master/KategoriTegangan')->withInput()->with('error', 'Tarif wajib diisi.');
        }

        if (! in_array($kategori, self::ALLOWED_KATEGORI, true)) {
            return redirect()->to('/C_Master/KategoriTegangan')->withInput()->with('error', 'Kategori tegangan tidak valid.');
        }

        $model = new KategoriTeganganModel();
        $payload = [
            'tarif' => $tarif,
            'kategori_tegangan' => $kategori,
            'created_by' => (string) (session('username') ?? 'system'),
        ];

        try {
            if ($id > 0) {
                $existing = $model->find($id);
                if (! is_array($existing)) {
                    return redirect()->to('/C_Master/KategoriTegangan')->with('error', 'Data tidak ditemukan.');
                }

                $sameTarif = $model->where('tarif', $tarif)->where('id !=', $id)->first();
                if (is_array($sameTarif)) {
                    return redirect()->to('/C_Master/KategoriTegangan')->withInput()->with('error', 'Tarif sudah digunakan data lain.');
                }

                $model->update($id, $payload);

                return redirect()->to('/C_Master/KategoriTegangan')->with('success', 'Kategori tegangan berhasil diperbarui.');
            }

            $existingByTarif = $model->findByTarif($tarif);
            if (is_array($existingByTarif)) {
                $model->update((int) ($existingByTarif['id'] ?? 0), $payload);

                return redirect()->to('/C_Master/KategoriTegangan')->with('success', 'Kategori tegangan berhasil diperbarui.');
            }

            $model->insert($payload, false);
        } catch (Throwable $e) {
            log_message('error', 'KATEGORI_TEGANGAN_SAVE_FAILED: {message}', ['message' => $e->getMessage()]);

            return redirect()->to('/C_Master/KategoriTegangan')->withInput()->with('error', 'Gagal menyimpan kategori tegangan.');
        }

        return redirect()->to('/C_Master/KategoriTegangan')->with('success', 'Kategori tegangan berhasil ditambahkan.');
    }

    public function deleteKategoriTegangan(): RedirectResponse
    {
        $rules = [
            'id' => 'required|integer',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/C_Master/KategoriTegangan')->with('error', 'Permintaan hapus tidak valid.');
        }

        $id = (int) $this->request->getPost('id');
        $model = new KategoriTeganganModel();

        try {
            $row = $model->find($id);
            if (! is_array($row)) {
                return redirect()->to('/C_Master/KategoriTegangan')->with('error', 'Data tidak ditemukan.');
            }

            $model->delete($id);
        } catch (Throwable $e) {
            log_message('error', 'KATEGORI_TEGANGAN_DELETE_FAILED: {message}', ['message' => $e->getMessage()]);

            return redirect()->to('/C_Master/KategoriTegangan')->with('error', 'Gagal menghapus data kategori tegangan.');
        }

        return redirect()->to('/C_Master/KategoriTegangan')->with('success', 'Kategori tegangan berhasil dihapus.');
    }

    public function pelanggan(): string
    {
        $model = new PelangganMasterModel();
        $filters = [
            'unit' => (string) ($this->request->getGet('unit') ?? '*'),
            'bulan' => (string) ($this->request->getGet('bulan') ?? '*'),
            'idpel' => trim((string) ($this->request->getGet('idpel') ?? '')),
        ];

        $resumeState = null;
        $resumeToken = (string) (session('pelanggan_import_resume_token') ?? '');
        if ($resumeToken !== '') {
            $resumeState = $this->loadPelangganImportState($resumeToken);
            if (! is_array($resumeState)) {
                session()->remove('pelanggan_import_resume_token');
            }
        }

        return view('master/pelanggan', [
            'title' => 'Data Induk Langganan',
            'pageHeading' => 'Data Induk Langganan',
            'units' => $model->getUnits(),
            'bulanOptions' => $model->getBulanOptions(),
            'filters' => $filters,
            'maxUploadMb' => $this->getRecommendedUploadLimitMb(),
            'resumeState' => $resumeState,
        ]);
    }

    public function pelangganData(): ResponseInterface
    {
        $draw = (int) ($this->request->getPost('draw') ?? 0);
        $start = max(0, (int) ($this->request->getPost('start') ?? 0));
        $length = (int) ($this->request->getPost('length') ?? 10);
        if ($length < 1) {
            $length = 10;
        }

        $filters = [
            'unit' => (string) ($this->request->getPost('unit') ?? '*'),
            'bulan' => (string) ($this->request->getPost('bulan') ?? '*'),
            'idpel' => trim((string) ($this->request->getPost('idpel') ?? '')),
            'search' => '',
        ];

        $orderIndex = (int) ($this->request->getPost('order')[0]['column'] ?? 0);
        $orderDir = strtolower((string) ($this->request->getPost('order')[0]['dir'] ?? 'desc'));
        if (! in_array($orderDir, ['asc', 'desc'], true)) {
            $orderDir = 'desc';
        }

        $columnMap = [
            0 => 'v_bulan_rekap',
            1 => 'unitup',
            2 => 'idpel',
            3 => 'nama',
            4 => 'namapnj',
            5 => 'tarif',
            6 => 'daya',
            7 => 'kdpt_2',
            8 => 'thblmut',
            9 => 'jenis_mk',
            10 => 'jenislayanan',
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
            29 => 'kdpembmeter',
            30 => 'ket_kdpembmeter',
            31 => 'status_dil',
            32 => 'krn',
            33 => 'vkrn',
        ];
        $orderBy = $columnMap[$orderIndex] ?? 'idpel';

        $model = new PelangganMasterModel();
        $total = $model->countAll();
        $filteredBuilder = $model->getBuilder($filters);
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

    public function importPelanggan(): RedirectResponse
    {
        $file = $this->request->getFile('excel_file');
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return redirect()->to('/C_Master/Pelanggan')->with('error', 'File upload tidak valid.');
        }

        $extension = strtolower((string) $file->getClientExtension());
        if (! in_array($extension, ['xlsx', 'xls'], true)) {
            return redirect()->to('/C_Master/Pelanggan')->with('error', 'Format file harus .xlsx atau .xls.');
        }

        $importRequirementError = $this->validateImportRequirements($extension);
        if ($importRequirementError !== null) {
            return redirect()->to('/C_Master/Pelanggan')->with('error', $importRequirementError);
        }

        $maxUploadMb = $this->getRecommendedUploadLimitMb();
        if ($file->getSizeByUnit('mb') > $maxUploadMb) {
            return redirect()->to('/C_Master/Pelanggan')->with('error', 'Ukuran file melebihi batas upload aplikasi (' . $maxUploadMb . ' MB).');
        }

        $tempName = $file->getRandomName();
        $targetPath = WRITEPATH . 'uploads/' . $tempName;

        try {
            @set_time_limit(0);
            @ini_set('memory_limit', '1024M');

            $currentToken = (string) (session('pelanggan_import_resume_token') ?? '');
            if ($currentToken !== '') {
                $this->cleanupPelangganImportState($currentToken, true);
                session()->remove('pelanggan_import_resume_token');
            }

            $file->move(WRITEPATH . 'uploads', $tempName);

            $token = bin2hex(random_bytes(16));
            $state = [
                'token' => $token,
                'file_path' => $targetPath,
                'total_rows' => 0,
                'header_row' => 1,
                'next_row' => 2,
                'column_map' => [],
                'deleted_periods' => [],
                'inserted_rows' => 0,
                'failed_at_row' => null,
                'initialized' => false,
                'updated_at' => date('c'),
            ];
            $this->savePelangganImportState($state);
            session()->set('pelanggan_import_resume_token', $token);

            return redirect()->to('/C_Master/Pelanggan')->with('success', 'File berhasil diupload. Import dijalankan bertahap untuk mencegah timeout, mohon tunggu proses auto-resume sampai selesai.');
        } catch (Throwable $e) {
            log_message('error', 'MASTER_PELANGGAN_IMPORT_FAILED: {message}', ['message' => $e->getMessage()]);

            $friendlyMessage = $this->buildFriendlyImportErrorMessage($e->getMessage());

            return redirect()->to('/C_Master/Pelanggan')->with('error', $friendlyMessage);
        }
    }

    public function resumeImportPelanggan(): RedirectResponse
    {
        $token = trim((string) ($this->request->getPost('resume_token') ?? session('pelanggan_import_resume_token') ?? ''));
        if ($token === '') {
            return redirect()->to('/C_Master/Pelanggan')->with('error', 'Token resume import tidak ditemukan.');
        }

        return $this->continuePelangganImportByToken($token);
    }

    public function resumeImportPelangganAuto(): ResponseInterface
    {
        $token = trim((string) ($this->request->getPost('resume_token') ?? session('pelanggan_import_resume_token') ?? ''));
        if ($token === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Token resume import tidak ditemukan.',
            ]);
        }

        $result = $this->processPelangganImportByToken($token);
        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        return $this->response->setJSON($result);
    }

    public function cancelResumeImportPelanggan(): RedirectResponse
    {
        $token = trim((string) ($this->request->getPost('resume_token') ?? session('pelanggan_import_resume_token') ?? ''));
        if ($token === '') {
            return redirect()->to('/C_Master/Pelanggan')->with('error', 'Tidak ada proses resume import yang dapat dibatalkan.');
        }

        $this->cleanupPelangganImportState($token, true);
        session()->remove('pelanggan_import_resume_token');

        return redirect()->to('/C_Master/Pelanggan')->with('success', 'Resume import berhasil dibatalkan dan progress sementara dibersihkan.');
    }

    private function continuePelangganImportByToken(string $token): RedirectResponse
    {
        $result = $this->processPelangganImportByToken($token);

        if (($result['status'] ?? '') === 'success' || ($result['status'] ?? '') === 'in_progress') {
            return redirect()->to('/C_Master/Pelanggan')->with('success', (string) ($result['message'] ?? ''));
        }

        return redirect()->to('/C_Master/Pelanggan')->with('error', (string) ($result['message'] ?? 'Import data induk langganan gagal diproses.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function processPelangganImportByToken(string $token): array
    {
        $state = $this->loadPelangganImportState($token);
        if (! is_array($state)) {
            session()->remove('pelanggan_import_resume_token');

            return [
                'status' => 'error',
                'message' => 'Progress import tidak ditemukan atau sudah dibersihkan.',
            ];
        }

        $filePath = (string) ($state['file_path'] ?? '');
        if ($filePath === '' || ! is_file($filePath)) {
            $this->cleanupPelangganImportState($token, false);
            session()->remove('pelanggan_import_resume_token');

            return [
                'status' => 'error',
                'message' => 'File sumber import tidak ditemukan. Upload ulang file untuk memulai ulang.',
            ];
        }

        try {
            @set_time_limit(0);
            @ini_set('memory_limit', '1024M');

            $resumeExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $importRequirementError = $this->validateImportRequirements($resumeExtension);
            if ($importRequirementError !== null) {
                return [
                    'status' => 'error',
                    'message' => $importRequirementError,
                ];
            }

            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);

            if (! $this->initializePelangganImportState($reader, $state)) {
                return [
                    'status' => 'error',
                    'message' => 'Kolom wajib IDPEL dan V_BULAN_REKAP tidak ditemukan pada file.',
                ];
            }

            $isCompleted = $this->runPelangganImportChunks($reader, $state);

            if (! $isCompleted) {
                $state['updated_at'] = date('c');
                $this->savePelangganImportState($state);
                session()->set('pelanggan_import_resume_token', $token);

                $progressPercent = $this->calculateImportProgressPercent($state);
                $insertedRows = (int) ($state['inserted_rows'] ?? 0);
                $processedRows = max(0, ((int) ($state['next_row'] ?? 2)) - 2);
                $totalDataRows = max(1, ((int) ($state['total_rows'] ?? 1)) - 1);

                return [
                    'status' => 'in_progress',
                    'message' => 'Import sedang berjalan bertahap (' . $progressPercent . '%). Data tersimpan sementara: ' . $insertedRows . ' baris. Klik "Lanjutkan Import" untuk melanjutkan.',
                    'progress_percent' => $progressPercent,
                    'inserted_rows' => $insertedRows,
                    'processed_rows' => $processedRows,
                    'total_rows' => $totalDataRows,
                ];
            }

            if ((int) ($state['inserted_rows'] ?? 0) < 1) {
                $this->cleanupPelangganImportState($token, true);
                session()->remove('pelanggan_import_resume_token');

                return [
                    'status' => 'error',
                    'message' => 'Tidak ada data valid yang berhasil diimport.',
                ];
            }

            $insertedRows = (int) ($state['inserted_rows'] ?? 0);
            $progressPercent = $this->calculateImportProgressPercent($state);
            $processedRows = max(0, ((int) ($state['next_row'] ?? 2)) - 2);
            $totalDataRows = max(1, ((int) ($state['total_rows'] ?? 1)) - 1);
            $this->cleanupPelangganImportState($token, true);
            session()->remove('pelanggan_import_resume_token');

            return [
                'status' => 'success',
                'message' => 'Import data induk langganan berhasil (' . $progressPercent . '%). Total baris tersimpan: ' . $insertedRows . '.',
                'progress_percent' => $progressPercent,
                'inserted_rows' => $insertedRows,
                'processed_rows' => $processedRows,
                'total_rows' => $totalDataRows,
            ];
        } catch (Throwable $e) {
            $state['failed_at_row'] = (int) ($state['next_row'] ?? 2);
            $state['updated_at'] = date('c');
            $this->savePelangganImportState($state);
            session()->set('pelanggan_import_resume_token', $token);

            log_message('error', 'MASTER_PELANGGAN_IMPORT_RESUME_FAILED: {message}', ['message' => $e->getMessage()]);

            $failedRow = (int) ($state['failed_at_row'] ?? 2);
            $progressPercent = $this->calculateImportProgressPercent($state);
            $processedRows = max(0, ((int) ($state['next_row'] ?? 2)) - 2);
            $totalDataRows = max(1, ((int) ($state['total_rows'] ?? 1)) - 1);
            $friendlyMessage = $this->buildFriendlyImportErrorMessage($e->getMessage());

            return [
                'status' => 'error',
                'message' => $friendlyMessage . ' Import gagal di sekitar baris ' . $failedRow . ' (progress ' . $progressPercent . '%). Klik tombol "Lanjutkan Import" untuk melanjutkan proses.',
                'progress_percent' => $progressPercent,
                'failed_at_row' => $failedRow,
                'processed_rows' => $processedRows,
                'total_rows' => $totalDataRows,
            ];
        }
    }

    /**
     * @param array<string, mixed> $state
     */
    private function initializePelangganImportState($reader, array &$state): bool
    {
        $alreadyInitialized = (bool) ($state['initialized'] ?? false);
        $hasMap = is_array($state['column_map'] ?? null) && (array) $state['column_map'] !== [];
        $hasTotal = (int) ($state['total_rows'] ?? 0) > 1;

        if ($alreadyInitialized && $hasMap && $hasTotal) {
            return true;
        }

        $filePath = (string) ($state['file_path'] ?? '');
        if ($filePath === '' || ! is_file($filePath)) {
            return false;
        }

        $headerDetection = $this->detectImportHeader($reader, $filePath, 20);
        if (! is_array($headerDetection)) {
            return false;
        }

        $columnMap = is_array($headerDetection['column_map'] ?? null) ? $headerDetection['column_map'] : [];
        $headerRowNumber = (int) ($headerDetection['header_row'] ?? 1);
        $worksheets = $reader->listWorksheetInfo($filePath);
        $dimensionTotalRows = (int) ($worksheets[0]['totalRows'] ?? 0);
        $totalRows = $this->detectActualImportTotalRows(
            $filePath,
            $columnMap,
            $headerRowNumber,
            $dimensionTotalRows
        );

        if ($totalRows <= $headerRowNumber) {
            return false;
        }

        $state['column_map'] = $columnMap;
        $state['header_row'] = $headerRowNumber;
        $state['total_rows'] = $totalRows;
        $state['next_row'] = max((int) ($state['next_row'] ?? 2), $headerRowNumber + 1);
        $state['initialized'] = true;
        $state['updated_at'] = date('c');
        $this->savePelangganImportState($state);

        return true;
    }

    /**
     * @param array<string, string> $columnMap
     */
    private function detectActualImportTotalRows(string $filePath, array $columnMap, int $headerRowNumber, int $fallbackTotalRows): int
    {
        $fallback = max($headerRowNumber, $fallbackTotalRows);
        $idpelColumn = trim((string) ($columnMap['idpel'] ?? ''));
        if ($idpelColumn === '') {
            return $fallback;
        }

        try {
            $dataReader = IOFactory::createReaderForFile($filePath);
            $dataReader->setReadDataOnly(true);

            if (method_exists($dataReader, 'setReadEmptyCells')) {
                $dataReader->setReadEmptyCells(false);
            }

            $sheet = $dataReader->load($filePath)->getActiveSheet();
            $highestDataRow = (int) $sheet->getHighestDataRow($idpelColumn);

            if ($highestDataRow > $headerRowNumber) {
                return $highestDataRow;
            }
        } catch (Throwable) {
            return $fallback;
        }

        return $fallback;
    }

    private function validateImportRequirements(string $extension): ?string
    {
        if ($extension === 'xlsx' && ! class_exists('ZipArchive')) {
            return 'Import .xlsx membutuhkan ekstensi PHP zip (ZipArchive). Aktifkan ekstensi zip pada server, lalu coba lagi. Sementara itu Anda bisa gunakan file .xls.';
        }

        return null;
    }

    private function buildFriendlyImportErrorMessage(string $rawMessage): string
    {
        if (stripos($rawMessage, 'ZipArchive') !== false) {
            return 'Import .xlsx membutuhkan ekstensi PHP zip (ZipArchive). Aktifkan ekstensi zip pada server, lalu coba lagi.';
        }

        return 'Import data induk langganan gagal diproses. ';
    }

    /**
     * @param array<string, mixed> $state
     */
    private function calculateImportProgressPercent(array $state): int
    {
        $totalRows = max(1, ((int) ($state['total_rows'] ?? 1)) - 1);
        $doneRows = max(0, ((int) ($state['next_row'] ?? 2)) - 2);

        return min(100, (int) floor(($doneRows / $totalRows) * 100));
    }

    /**
     * @param array<string, mixed> $state
     */
    private function runPelangganImportChunks($reader, array &$state): bool
    {
        $db = db_connect();
        $totalRows = max(2, (int) ($state['total_rows'] ?? 2));
        $nextRow = max(2, (int) ($state['next_row'] ?? 2));
        $columnMap = is_array($state['column_map'] ?? null) ? $state['column_map'] : [];
        $deletedPeriods = is_array($state['deleted_periods'] ?? null) ? $state['deleted_periods'] : [];
        $insertedRows = (int) ($state['inserted_rows'] ?? 0);
        $chunksProcessed = 0;

        for ($startRow = $nextRow; $startRow <= $totalRows; $startRow += self::PELANGGAN_IMPORT_CHUNK_SIZE) {
            $chunkFilter = new SheetChunkReadFilter($startRow, self::PELANGGAN_IMPORT_CHUNK_SIZE);
            $reader->setReadFilter($chunkFilter);
            $sheet = $reader->load((string) $state['file_path'])->getActiveSheet();

            $endRow = min($totalRows, $startRow + self::PELANGGAN_IMPORT_CHUNK_SIZE - 1);
            $chunkRows = [];
            $chunkPeriods = [];

            for ($row = $startRow; $row <= $endRow; $row++) {
                $payload = [];
                foreach ($columnMap as $field => $columnLetter) {
                    $payload[$field] = $this->normalizeImportedValue(
                        (string) $field,
                        $sheet->getCell((string) $columnLetter . $row)->getValue()
                    );
                }

                $idpel = (string) ($payload['idpel'] ?? '');
                $period = (string) ($payload['v_bulan_rekap'] ?? '');
                if ($idpel === '' || $period === '') {
                    continue;
                }

                if (! isset($deletedPeriods[$period])) {
                    $chunkPeriods[$period] = true;
                }

                $chunkRows[] = $payload;
            }

            $db->transStart();
            foreach (array_keys($chunkPeriods) as $period) {
                $db->table(self::TABLE_PELANGGAN)->where('v_bulan_rekap', (int) $period)->delete();
            }

            if ($chunkRows !== []) {
                $batchRows = [];
                foreach ($chunkRows as $payload) {
                    $batchRows[] = $payload;
                    if (count($batchRows) >= self::PELANGGAN_IMPORT_BATCH_SIZE) {
                        $db->table(self::TABLE_PELANGGAN)->insertBatch($batchRows);
                        $insertedRows += count($batchRows);
                        $batchRows = [];
                    }
                }

                if ($batchRows !== []) {
                    $db->table(self::TABLE_PELANGGAN)->insertBatch($batchRows);
                    $insertedRows += count($batchRows);
                }
            }

            $db->transComplete();
            if (! $db->transStatus()) {
                throw new \RuntimeException('Transaksi import per chunk gagal.');
            }

            foreach (array_keys($chunkPeriods) as $period) {
                $deletedPeriods[$period] = true;
            }

            $state['deleted_periods'] = $deletedPeriods;
            $state['inserted_rows'] = $insertedRows;
            $state['next_row'] = $endRow + 1;
            $state['failed_at_row'] = null;
            $state['updated_at'] = date('c');
            $this->savePelangganImportState($state);

            $chunksProcessed++;
            if ($chunksProcessed >= self::PELANGGAN_IMPORT_MAX_CHUNKS_PER_REQUEST) {
                break;
            }
        }

        return ((int) ($state['next_row'] ?? 2)) > $totalRows;
    }

    /**
     * @param array<string, mixed> $state
     */
    private function savePelangganImportState(array $state): void
    {
        $token = (string) ($state['token'] ?? '');
        if ($token === '') {
            throw new \RuntimeException('Token state import tidak valid.');
        }

        $path = $this->getPelangganImportStatePath($token);
        file_put_contents($path, json_encode($state, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }

    /**
     * @return array<string, mixed>|null
     */
    private function loadPelangganImportState(string $token): ?array
    {
        $path = $this->getPelangganImportStatePath($token);
        if (! is_file($path)) {
            return null;
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        return is_array($decoded) ? $decoded : null;
    }

    private function cleanupPelangganImportState(string $token, bool $deleteSourceFile): void
    {
        $state = $this->loadPelangganImportState($token);
        if ($deleteSourceFile && is_array($state)) {
            $filePath = (string) ($state['file_path'] ?? '');
            if ($filePath !== '' && is_file($filePath)) {
                @unlink($filePath);
            }
        }

        $statePath = $this->getPelangganImportStatePath($token);
        if (is_file($statePath)) {
            @unlink($statePath);
        }
    }

    private function getPelangganImportStatePath(string $token): string
    {
        $safeToken = preg_replace('/[^a-z0-9]/', '', strtolower($token)) ?? '';
        if ($safeToken === '') {
            throw new \RuntimeException('Token import tidak valid.');
        }

        return WRITEPATH . 'uploads/' . self::PELANGGAN_IMPORT_STATE_PREFIX . $safeToken . '.json';
    }

    /**
     * @param array<string, mixed> $headerRow
     * @return array<string, string>
     */
    private function buildImportColumnMap(array $headerRow): array
    {
        $aliases = [
            'vbulanrekap' => 'v_bulan_rekap',
            'unitup' => 'unitup',
            'namapnj' => 'namapnj',
            'thblmut' => 'thblmut',
            'jenislayanan' => 'jenislayanan',
            'nomormeterkwh' => 'nomor_meter_kwh',
            'tanggalpasangrubahapp' => 'tanggal_pasang_rubah_app',
            'merkmeterkwh' => 'merk_meter_kwh',
            'typemeterkwh' => 'type_meter_kwh',
            'tahunterameterkwh' => 'tahun_tera_meter_kwh',
            'tahunbuatmeterkwh' => 'tahun_buat_meter_kwh',
            'nomorgardu' => 'nomor_gardu',
            'nomorjurusantiang' => 'nomor_jurusan_tiang',
            'namagardu' => 'nama_gardu',
            'kapasitastrafo' => 'kapasitas_trafo',
            'nomormeterprepaid' => 'nomor_meter_prepaid',
            'koordinatx' => 'koordinat_x',
            'koordinaty' => 'koordinat_y',
            'kdpembmeter' => 'kdpembmeter',
            'ketkdpembmeter' => 'ket_kdpembmeter',
        ];

        $allowedFields = [
            'v_bulan_rekap', 'unitup', 'idpel', 'nama', 'namapnj', 'tarif', 'daya', 'kdpt_2', 'thblmut',
            'jenis_mk', 'jenislayanan', 'frt', 'kogol', 'fkmkwh', 'nomor_meter_kwh', 'tanggal_pasang_rubah_app',
            'merk_meter_kwh', 'type_meter_kwh', 'tahun_tera_meter_kwh', 'tahun_buat_meter_kwh', 'nomor_gardu',
            'nomor_jurusan_tiang', 'nama_gardu', 'kapasitas_trafo', 'nomor_meter_prepaid', 'product', 'koordinat_x',
            'koordinat_y', 'kdam', 'kdpembmeter', 'ket_kdpembmeter', 'status_dil', 'krn', 'vkrn',
        ];

        $map = [];
        foreach ($headerRow as $column => $header) {
            $label = strtolower(preg_replace('/[^a-z0-9]+/i', '', (string) $header) ?? '');
            if ($label === '') {
                continue;
            }

            $field = $aliases[$label] ?? $label;
            if (! in_array($field, $allowedFields, true)) {
                continue;
            }

            $map[$field] = (string) $column;
        }

        return $map;
    }

    /**
     * @return array{header_row:int, column_map:array<string, string>}|null
     */
    private function detectImportHeader($reader, string $filePath, int $maxScanRows = 20): ?array
    {
        try {
            $reader->setReadFilter(new SheetChunkReadFilter(1, max(1, $maxScanRows)));
            $sheet = $reader->load($filePath)->getActiveSheet();
            $highestColumn = $sheet->getHighestColumn();
            $scanUntil = min($maxScanRows, (int) $sheet->getHighestRow());

            for ($rowNumber = 1; $rowNumber <= $scanUntil; $rowNumber++) {
                $headerRow = $sheet->rangeToArray(
                    'A' . $rowNumber . ':' . $highestColumn . $rowNumber,
                    null,
                    true,
                    true,
                    true
                )[$rowNumber] ?? [];

                $columnMap = $this->buildImportColumnMap($headerRow);
                if (isset($columnMap['idpel']) && isset($columnMap['v_bulan_rekap'])) {
                    return [
                        'header_row' => $rowNumber,
                        'column_map' => $columnMap,
                    ];
                }
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }

    private function normalizeImportedValue(string $field, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $raw = is_string($value) ? trim($value) : $value;
        if ($raw === '') {
            return null;
        }

        $intFields = [
            'v_bulan_rekap', 'unitup', 'idpel', 'daya', 'thblmut', 'kogol', 'fkmkwh', 'nomor_meter_kwh',
            'tanggal_pasang_rubah_app', 'tahun_tera_meter_kwh', 'tahun_buat_meter_kwh', 'kapasitas_trafo',
            'nomor_meter_prepaid', 'product', 'krn', 'vkrn',
        ];
        $floatFields = ['koordinat_x', 'koordinat_y'];

        if (in_array($field, $intFields, true)) {
            if (is_numeric($raw)) {
                return (int) round((float) $raw);
            }

            $normalized = preg_replace('/[^0-9\-]/', '', (string) $raw) ?? '';
            return $normalized === '' ? null : (int) $normalized;
        }

        if (in_array($field, $floatFields, true)) {
            if (is_numeric($raw)) {
                return (float) $raw;
            }

            $normalized = str_replace(',', '.', (string) $raw);
            return is_numeric($normalized) ? (float) $normalized : null;
        }

        return (string) $raw;
    }

    private function getRecommendedUploadLimitMb(): int
    {
        $uploadMax = $this->toBytes((string) ini_get('upload_max_filesize'));
        $postMax = $this->toBytes((string) ini_get('post_max_size'));

        $effective = min($uploadMax, $postMax);
        if ($effective <= 0) {
            return self::PELANGGAN_IMPORT_MAX_UPLOAD_MB;
        }

        $serverLimitMb = max(1, (int) floor($effective / (1024 * 1024)));

        // Application guardrail: pelanggan import must never exceed 40 MB.
        return min(self::PELANGGAN_IMPORT_MAX_UPLOAD_MB, $serverLimitMb);
    }

    private function toBytes(string $value): int
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return 0;
        }

        $unit = strtolower(substr($trimmed, -1));
        $number = (float) $trimmed;

        if ($unit === 'g') {
            return (int) ($number * 1024 * 1024 * 1024);
        }
        if ($unit === 'm') {
            return (int) ($number * 1024 * 1024);
        }
        if ($unit === 'k') {
            return (int) ($number * 1024);
        }

        return (int) $number;
    }
}

class SheetChunkReadFilter implements IReadFilter
{
    private int $startRow;
    private int $endRow;

    public function __construct(int $startRow, int $chunkSize)
    {
        $this->startRow = $startRow;
        $this->endRow = $startRow + $chunkSize - 1;
    }

    public function readCell($columnAddress, $row, $worksheetName = ''): bool
    {
        if ($row === 1) {
            return true;
        }

        return $row >= $this->startRow && $row <= $this->endRow;
    }
}
