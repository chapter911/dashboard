<?php

namespace App\Controllers;

use App\Models\AppSettingModel;
use Config\Database;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Database\BaseConnection;
use Throwable;

class Setting extends BaseController
{
    private const BRANDING_UPLOAD_DIR = 'uploads/branding';
    private const SYNC_STATE_KEY = 'prod_sync_state';
    private const SYNC_BATCH_SIZE = 2000;
    private const SYNC_BATCH_MAX = 50000;
    private const SEEDER_RUNS_TABLE = 'seeder_runs';

    public function index(): string
    {
        return $this->application();
    }

    public function application(): string
    {
        $settingModel = new AppSettingModel();
        $appName = 'Dashboard PLN';
        $primaryColor = '#0a66c2';
        $autoLogoutMinutes = 30;

        try {
            $appName = $settingModel->getValue('app_name', 'Dashboard PLN') ?? 'Dashboard PLN';
            $storedPrimaryColor = $settingModel->getValue('app_primary_color', '#0a66c2');
            $storedAutoLogout = $settingModel->getValue('auto_logout_minutes', '30');

            if ($this->isValidHexColor($storedPrimaryColor)) {
                $primaryColor = strtolower((string) $storedPrimaryColor);
            }

            $autoLogoutMinutes = $this->normalizeAutoLogoutMinutes($storedAutoLogout, 30);
        } catch (Throwable $e) {
            log_message('warning', 'SETTING_INDEX_LOAD_FAILED: {message}', ['message' => $e->getMessage()]);
        }

        return view('setting/index', [
            'title' => 'Setting Aplikasi',
            'pageHeading' => 'Setting Aplikasi',
            'activeSettingTab' => 'application',
            'canRunMaintenanceTools' => $this->canRunMaintenanceTools(),
            'shellExecAvailable' => $this->isShellFunctionEnabled('exec'),
            'seederOptions' => $this->getSeederOptionsPayload(),
            'syncTableOptions' => $this->getSyncTableOptions(),
            'errorLogFiles' => $this->getRecentErrorLogFiles(3),
            'formData' => [
                'app_name' => old('app_name', $appName),
                'app_primary_color' => old('app_primary_color', $primaryColor),
                'auto_logout_minutes' => old('auto_logout_minutes', (string) $autoLogoutMinutes),
            ],
        ]);
    }
        // AJAX endpoint for log file content
        public function logContent()
        {
            $filename = $this->request->getGet('file');
            $logDir = WRITEPATH . 'logs';
            $allowed = $this->getRecentErrorLogFiles(3);
            if (! is_string($filename) || ! in_array($filename, $allowed, true)) {
                return $this->response->setStatusCode(404)->setBody('File not found.');
            }
            $safeFilename = basename($filename);
            $filePath = $logDir . DIRECTORY_SEPARATOR . $safeFilename;
            if (! is_file($filePath) || ! is_readable($filePath)) {
                return $this->response->setStatusCode(404)->setBody('File not found.');
            }
            $content = $this->readLogTail($filePath, 1024 * 1024);
            if ($content === '') {
                return $this->response->setStatusCode(404)->setBody('File kosong atau tidak dapat dibaca.');
            }

            return $this->response->setHeader('Content-Type', 'text/plain')->setBody($content);
        }

    /**
     * @return list<string>
     */
    private function getRecentErrorLogFiles(int $limit = 3): array
    {
        $logDir = WRITEPATH . 'logs';
        if (! is_dir($logDir)) {
            return [];
        }

        $entries = scandir($logDir);
        if (! is_array($entries)) {
            return [];
        }

        $candidates = [];
        foreach ($entries as $entry) {
            if (! is_string($entry)) {
                continue;
            }

            // Accept only standard CI log filenames: log-YYYY-MM-DD.log
            if (preg_match('/^log-\d{4}-\d{2}-\d{2}\.log$/', $entry) !== 1) {
                continue;
            }

            $path = $logDir . DIRECTORY_SEPARATOR . $entry;
            if (! is_file($path) || ! is_readable($path)) {
                continue;
            }

            if (! $this->containsErrorEntry($path)) {
                continue;
            }

            $mtime = @filemtime($path);
            $candidates[] = [
                'name' => $entry,
                'mtime' => is_int($mtime) ? $mtime : 0,
            ];
        }

        usort($candidates, static function (array $a, array $b): int {
            return $b['mtime'] <=> $a['mtime'];
        });

        $result = [];
        foreach (array_slice($candidates, 0, max(1, $limit)) as $item) {
            $result[] = (string) $item['name'];
        }

        return $result;
    }

    private function containsErrorEntry(string $filePath): bool
    {
        $handle = @fopen($filePath, 'rb');
        if ($handle === false) {
            return false;
        }

        try {
            while (! feof($handle)) {
                $line = fgets($handle);
                if ($line === false) {
                    continue;
                }

                if (strpos($line, 'ERROR -') !== false) {
                    return true;
                }
            }
        } finally {
            fclose($handle);
        }

        return false;
    }

    private function readLogTail(string $filePath, int $maxBytes = 1048576): string
    {
        $size = @filesize($filePath);
        if (! is_int($size) || $size <= 0) {
            return '';
        }

        $offset = max(0, $size - max(1, $maxBytes));
        $handle = @fopen($filePath, 'rb');
        if ($handle === false) {
            return '';
        }

        try {
            if ($offset > 0) {
                fseek($handle, $offset);
                // Drop partial first line when reading from the middle of file.
                fgets($handle);
            }

            $content = stream_get_contents($handle);
            return is_string($content) ? $content : '';
        } finally {
            fclose($handle);
        }
    }

    public function update()
    {
        return $this->updateApplication();
    }

    public function updateApplication()
    {
        $rules = [
            'app_name' => 'required|min_length[3]|max_length[100]',
            'app_primary_color' => 'required|regex_match[/^#[0-9a-fA-F]{6}$/]',
            'auto_logout_minutes' => 'required|integer|greater_than_equal_to[1]|less_than_equal_to[1440]',
            'app_logo' => 'if_exist|max_size[app_logo,2048]|is_image[app_logo]|mime_in[app_logo,image/png,image/jpeg,image/webp]',
            'login_background' => 'if_exist|max_size[login_background,4096]|is_image[login_background]|mime_in[login_background,image/png,image/jpeg,image/webp]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $settingModel = new AppSettingModel();
        $updatedBy = (string) session('username');

        try {
            $settingModel->setValue('app_name', trim((string) $this->request->getPost('app_name')), $updatedBy);
            $settingModel->setValue('app_primary_color', strtolower((string) $this->request->getPost('app_primary_color')), $updatedBy);
            $settingModel->setValue(
                'auto_logout_minutes',
                (string) $this->normalizeAutoLogoutMinutes($this->request->getPost('auto_logout_minutes'), 30),
                $updatedBy
            );

            $logoFile = $this->request->getFile('app_logo');
            if ($this->hasValidUpload($logoFile)) {
                $oldPath = $settingModel->getValue('app_logo_path');
                $newPath = $this->storeUpload($logoFile, 'logo');
                $settingModel->setValue('app_logo_path', $newPath, $updatedBy);
                $this->deleteOldUploadedFile($oldPath);
            }

            $backgroundFile = $this->request->getFile('login_background');
            if ($this->hasValidUpload($backgroundFile)) {
                $oldPath = $settingModel->getValue('login_background_path');
                $newPath = $this->storeUpload($backgroundFile, 'login-bg');
                $settingModel->setValue('login_background_path', $newPath, $updatedBy);
                $this->deleteOldUploadedFile($oldPath);
            }
        } catch (Throwable $e) {
            log_message('error', 'SETTING_UPDATE_FAILED: {message}', ['message' => $e->getMessage()]);

            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan setting aplikasi.');
        }

        return redirect()->to('/setting/application')->with('success', 'Setting aplikasi berhasil diperbarui.');
    }

    public function menu(): string
    {
        $db = db_connect();

        return view('setting/menu', [
            'title' => 'Setting Menu',
            'pageHeading' => 'Setting Menu',
            'activeSettingTab' => 'menu',
            'menuLv1' => $this->getMenuRows($db, 'menu_lv1', 'id, label, link, icon, old_icon, ordering', ['ordering' => 'ASC']),
            'menuLv2' => $this->getMenuRows($db, 'menu_lv2', 'id, label, link, icon, header, ordering', ['ordering' => 'ASC']),
            'menuLv3' => $this->getMenuRows($db, 'menu_lv3', 'id, label, link, icon, header, ordering', ['ordering' => 'ASC']),
        ]);
    }

    public function updateMenu()
    {
        $db = db_connect();

        $lv1 = $this->toArray($this->request->getPost('lv1'));
        $lv2 = $this->toArray($this->request->getPost('lv2'));
        $lv3 = $this->toArray($this->request->getPost('lv3'));

        if ($lv1 === [] && $lv2 === [] && $lv3 === []) {
            return redirect()->back()->with('error', 'Tidak ada perubahan menu yang dikirim.');
        }

        try {
            $db->transException(true)->transStart();

            $this->applyMenuUpdates($db, 'menu_lv1', $lv1, [
                'label' => 'required',
                'link' => 'nullable',
                'icon' => 'required',
                'old_icon' => 'nullable',
                'ordering' => 'int',
            ]);

            $this->applyMenuUpdates($db, 'menu_lv2', $lv2, [
                'label' => 'required',
                'link' => 'nullable',
                'icon' => 'required',
                'header' => 'required',
                'ordering' => 'int',
            ]);

            $this->applyMenuUpdates($db, 'menu_lv3', $lv3, [
                'label' => 'required',
                'link' => 'nullable',
                'icon' => 'required',
                'header' => 'required',
                'ordering' => 'int',
            ]);

            $db->transComplete();
        } catch (Throwable $e) {
            log_message('error', 'SETTING_MENU_UPDATE_FAILED: {message}', ['message' => $e->getMessage()]);

            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan konfigurasi menu.');
        }

        return redirect()->to('/setting/menu')->with('success', 'Konfigurasi menu berhasil diperbarui.');
    }

    public function initProductionSync()
    {
        if (ENVIRONMENT !== 'development') {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Fitur sinkronisasi hanya tersedia di environment development.',
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ])->setStatusCode(403);
        }

        if (! $this->canRunMaintenanceTools()) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Akses ditolak. Hanya admin yang dapat menjalankan fitur ini.',
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ])->setStatusCode(403);
        }

        $rules = [
            'sync_confirmation' => 'required|in_list[1]',
            'sync_scope' => 'permit_empty|in_list[all,selected]',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Input koneksi production tidak valid.',
                'errors' => $this->validator->getErrors(),
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ])->setStatusCode(422);
        }

        $sourceConfig = $this->buildSyncSourceConfigFromEnv();

        if ($sourceConfig === null) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Kredensial source database belum lengkap di ENV.',
                'errors' => [
                    'env' => 'Lengkapi ENV: SYNC_SOURCE_HOST, SYNC_SOURCE_PORT, SYNC_SOURCE_DATABASE, SYNC_SOURCE_USERNAME, SYNC_SOURCE_PASSWORD.',
                ],
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ])->setStatusCode(422);
        }

        $syncScope = strtolower(trim((string) $this->request->getPost('sync_scope')));
        if (! in_array($syncScope, ['all', 'selected'], true)) {
            $syncScope = 'all';
        }

        $selectedTableNames = [];
        $selectedInput = $this->request->getPost('selected_tables');
        if (is_array($selectedInput)) {
            foreach ($selectedInput as $tableName) {
                $normalized = trim((string) $tableName);
                if ($normalized === '') {
                    continue;
                }

                if (preg_match('/^[A-Za-z0-9_]+$/', $normalized) !== 1) {
                    continue;
                }

                $selectedTableNames[$normalized] = true;
            }
        }

        if ($syncScope === 'selected' && $selectedTableNames === []) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Pilih minimal satu tabel untuk sinkronisasi.',
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ])->setStatusCode(422);
        }

        try {
            $sourceDb = Database::connect($sourceConfig, false);
            $targetDb = db_connect();

            $sourceTables = $sourceDb->listTables();
            $targetTables = $targetDb->listTables();

            $targetMap = [];
            foreach ($targetTables as $table) {
                if (is_string($table) && $table !== '') {
                    $targetMap[$table] = true;
                }
            }

            $tables = [];
            $totalRows = 0;

            foreach ($sourceTables as $table) {
                if (! is_string($table) || $table === '' || $table === 'migrations') {
                    continue;
                }

                if ($syncScope === 'selected' && ! isset($selectedTableNames[$table])) {
                    continue;
                }

                if (! isset($targetMap[$table])) {
                    $created = $this->createTargetTableFromSource($sourceDb, $targetDb, $table);
                    if (! $created) {
                        log_message('warning', 'SYNC_SKIP_TABLE_MISSING_TARGET {table}', ['table' => $table]);
                        continue;
                    }

                    $targetMap[$table] = true;
                }

                $rowCount = (int) $sourceDb->table($table)->countAllResults();
                $primaryKey = $this->discoverSinglePrimaryKey($sourceDb, $table);
                $tables[] = [
                    'name' => $table,
                    'row_count' => $rowCount,
                    'offset' => 0,
                    'primary_key' => $primaryKey,
                    'last_pk' => null,
                    'batch_size' => $this->getSyncBatchSize(),
                    'truncated' => false,
                ];
                $totalRows += $rowCount;
            }

            if ($tables === []) {
                return $this->response->setJSON([
                    'ok' => false,
                    'message' => $syncScope === 'selected'
                        ? 'Tabel terpilih tidak tersedia di source/target database.'
                        : 'Tidak ada tabel yang bisa disinkronkan.',
                    'csrfToken' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                ])->setStatusCode(422);
            }

            $syncState = [
                'source' => $sourceConfig,
                'tables' => $tables,
                'table_index' => 0,
                'processed_rows' => 0,
                'total_rows' => $totalRows,
                'total_tables' => count($tables),
                'sync_scope' => $syncScope,
                'selected_tables' => array_keys($selectedTableNames),
                'started_at' => date('Y-m-d H:i:s'),
                'started_by' => (string) session('username'),
            ];

            session()->set(self::SYNC_STATE_KEY, $syncState);
        } catch (Throwable $e) {
            log_message('error', 'PROD_SYNC_INIT_FAILED: {message}', ['message' => $e->getMessage()]);

            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Gagal terhubung ke database production atau membaca metadata tabel.',
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ])->setStatusCode(500);
        }

        return $this->response->setJSON([
            'ok' => true,
            'message' => 'Sinkronisasi dimulai untuk ' . count($tables) . ' tabel.',
            'progress' => 0,
            'syncMeta' => $this->buildSyncMeta($syncState),
            'csrfToken' => csrf_token(),
            'csrfHash' => csrf_hash(),
        ]);
    }

    public function processProductionSyncStep()
    {
        if (ENVIRONMENT !== 'development') {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Fitur sinkronisasi hanya tersedia di environment development.',
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ])->setStatusCode(403);
        }

        if (! $this->canRunMaintenanceTools()) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Akses ditolak. Hanya admin yang dapat menjalankan fitur ini.',
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ])->setStatusCode(403);
        }

        $state = session(self::SYNC_STATE_KEY);
        if (! is_array($state)) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Tidak ada proses sinkronisasi aktif.',
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ])->setStatusCode(409);
        }

        try {
            $sourceDb = Database::connect($state['source'] ?? [], false);
            $targetDb = db_connect();

            $tableIndex = (int) ($state['table_index'] ?? 0);
            $tables = is_array($state['tables'] ?? null) ? $state['tables'] : [];
            $totalRows = (int) ($state['total_rows'] ?? 0);
            $processedRows = (int) ($state['processed_rows'] ?? 0);

            if ($tableIndex >= count($tables)) {
                session()->remove(self::SYNC_STATE_KEY);

                return $this->response->setJSON([
                    'ok' => true,
                    'done' => true,
                    'progress' => 100,
                    'message' => 'Sinkronisasi selesai.',
                    'syncMeta' => $this->buildSyncMeta($state),
                    'csrfToken' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                ]);
            }

            $table = $tables[$tableIndex];
            $tableName = (string) ($table['name'] ?? '');
            $offset = (int) ($table['offset'] ?? 0);
            $rowCount = (int) ($table['row_count'] ?? 0);
            $primaryKey = trim((string) ($table['primary_key'] ?? ''));
            $lastPk = $table['last_pk'] ?? null;
            $truncated = (bool) ($table['truncated'] ?? false);
            $batchSize = (int) ($table['batch_size'] ?? $this->getSyncBatchSize());
            if ($batchSize < 100) {
                $batchSize = 100;
            }
            $batchMax = $this->getSyncBatchMax();
            if ($batchSize > $batchMax) {
                $batchSize = $batchMax;
            }

            if ($tableName === '') {
                $state['table_index'] = $tableIndex + 1;
                session()->set(self::SYNC_STATE_KEY, $state);

                return $this->response->setJSON([
                    'ok' => true,
                    'done' => false,
                    'progress' => $this->computeSyncProgress($processedRows, $totalRows, $state),
                    'message' => 'Melanjutkan sinkronisasi...',
                    'syncMeta' => $this->buildSyncMeta($state),
                    'csrfToken' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                ]);
            }

            $targetDb->query('SET FOREIGN_KEY_CHECKS = 0');

            if (! $truncated) {
                $targetDb->table($tableName)->truncate();
                $tables[$tableIndex]['truncated'] = true;
                $truncated = true;
            }

            if ($rowCount <= 0) {
                $state['table_index'] = $tableIndex + 1;
                $state['tables'] = $tables;
                $targetDb->query('SET FOREIGN_KEY_CHECKS = 1');
                session()->set(self::SYNC_STATE_KEY, $state);

                return $this->response->setJSON([
                    'ok' => true,
                    'done' => false,
                    'progress' => $this->computeSyncProgress($processedRows, $totalRows, $state),
                    'message' => 'Tabel ' . $tableName . ' kosong, lanjut ke tabel berikutnya.',
                    'syncMeta' => $this->buildSyncMeta($state),
                    'csrfToken' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                ]);
            }

            $rowBatchCount = 0;
            $batchTuneMessage = '';
            try {
                // For large tables, keyset pagination is significantly faster than OFFSET.
                if ($primaryKey !== '') {
                    $builder = $sourceDb->table($tableName)
                        ->orderBy($primaryKey, 'ASC')
                        ->limit($batchSize);

                    if ($lastPk !== null && $lastPk !== '') {
                        $builder->where($primaryKey . ' >', $lastPk);
                    }

                    $rows = $builder->get()->getResultArray();
                } else {
                    $rows = $sourceDb->table($tableName)
                        ->limit($batchSize, $offset)
                        ->get()
                        ->getResultArray();
                }

                $rowBatchCount = count($rows);

                if ($rowBatchCount > 0) {
                    try {
                        $targetDb->table($tableName)->insertBatch($rows, null, $batchSize);
                    } catch (Throwable $insertError) {
                        $isDuplicateKeyError = stripos($insertError->getMessage(), 'Duplicate entry') !== false;
                        if (! $isDuplicateKeyError) {
                            throw $insertError;
                        }

                        // Resume-safe behavior: when a prior step already inserted part/all rows,
                        // continue by ignoring duplicates for this batch.
                        $targetDb->table($tableName)->ignore(true)->insertBatch($rows, null, $batchSize);
                    }

                    $offset += $rowBatchCount;
                    $processedRows += $rowBatchCount;
                    $tables[$tableIndex]['offset'] = $offset;

                    if ($primaryKey !== '') {
                        $lastRow = $rows[$rowBatchCount - 1] ?? [];
                        if (is_array($lastRow) && array_key_exists($primaryKey, $lastRow)) {
                            $tables[$tableIndex]['last_pk'] = $lastRow[$primaryKey];
                        }
                    }

                    // Adaptive scale-up: if batch is full and stable, increase gradually.
                    if ($rowBatchCount >= $batchSize && $batchSize < $batchMax) {
                        $scaledBatch = (int) min($batchMax, max($batchSize + 100, (int) floor($batchSize * 1.25)));
                        if ($scaledBatch > $batchSize) {
                            $tables[$tableIndex]['batch_size'] = $scaledBatch;
                            $batchTuneMessage = ' Batch dinaikkan otomatis ke ' . $scaledBatch . '.';
                        }
                    }
                }

                unset($rows);
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            } catch (Throwable $e) {
                $isMemoryError = stripos($e->getMessage(), 'Allowed memory size') !== false;
                if ($isMemoryError && $batchSize > 100) {
                    $nextBatchSize = max(100, (int) floor($batchSize / 2));
                    $tables[$tableIndex]['batch_size'] = $nextBatchSize;
                    $state['tables'] = $tables;
                    $state['processed_rows'] = $processedRows;

                    $targetDb->query('SET FOREIGN_KEY_CHECKS = 1');
                    session()->set(self::SYNC_STATE_KEY, $state);

                    return $this->response->setJSON([
                        'ok' => true,
                        'done' => false,
                        'progress' => $this->computeSyncProgress($processedRows, $totalRows, $state),
                        'message' => 'Memori hampir habis di tabel ' . $tableName . '. Batch otomatis diturunkan ke ' . $nextBatchSize . ' dan proses dilanjutkan.',
                        'syncMeta' => $this->buildSyncMeta($state),
                        'csrfToken' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                    ]);
                }

                throw $e;
            }

            if ($offset >= $rowCount || $rowBatchCount === 0) {
                $state['table_index'] = $tableIndex + 1;
            }

            $state['tables'] = $tables;
            $state['processed_rows'] = $processedRows;

            $targetDb->query('SET FOREIGN_KEY_CHECKS = 1');
            session()->set(self::SYNC_STATE_KEY, $state);

            $isDone = (int) $state['table_index'] >= count($tables);
            if ($isDone) {
                session()->remove(self::SYNC_STATE_KEY);
            }

            return $this->response->setJSON([
                'ok' => true,
                'done' => $isDone,
                'progress' => $this->computeSyncProgress($processedRows, $totalRows, $state),
                'message' => $isDone
                    ? 'Sinkronisasi selesai.'
                    : 'Sinkronisasi tabel ' . $tableName . ' berjalan...' . $batchTuneMessage,
                'syncMeta' => $this->buildSyncMeta($state),
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ]);
        } catch (Throwable $e) {
            log_message('error', 'PROD_SYNC_STEP_FAILED: {message}', ['message' => $e->getMessage()]);

            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Sinkronisasi gagal: ' . $e->getMessage(),
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ])->setStatusCode(500);
        }
    }

    public function getProductionSyncState()
    {
        if (ENVIRONMENT !== 'development') {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Fitur sinkronisasi hanya tersedia di environment development.',
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ])->setStatusCode(403);
        }

        if (! $this->canRunMaintenanceTools()) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Akses ditolak. Hanya admin yang dapat menjalankan fitur ini.',
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ])->setStatusCode(403);
        }

        $state = $this->getActiveSyncState();
        if ($state === null) {
            return $this->response->setJSON([
                'ok' => true,
                'active' => false,
                'progress' => 0,
                'message' => 'Tidak ada proses sinkronisasi aktif.',
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ]);
        }

        $tableIndex = (int) ($state['table_index'] ?? 0);
        $tables = is_array($state['tables'] ?? null) ? $state['tables'] : [];
        $processedRows = (int) ($state['processed_rows'] ?? 0);
        $totalRows = (int) ($state['total_rows'] ?? 0);
        $tableName = '';
        if ($tableIndex >= 0 && $tableIndex < count($tables)) {
            $tableName = (string) ($tables[$tableIndex]['name'] ?? '');
        }

        return $this->response->setJSON([
            'ok' => true,
            'active' => true,
            'progress' => $this->computeSyncProgress($processedRows, $totalRows, $state),
            'message' => $tableName !== ''
                ? 'Sinkronisasi terhenti di tabel ' . $tableName . '. Lanjutkan proses untuk meneruskan.'
                : 'Sinkronisasi masih memiliki proses yang belum selesai.',
            'syncMeta' => $this->buildSyncMeta($state),
            'csrfToken' => csrf_token(),
            'csrfHash' => csrf_hash(),
        ]);
    }

    public function resumeProductionSync()
    {
        if (ENVIRONMENT !== 'development') {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Fitur sinkronisasi hanya tersedia di environment development.',
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ])->setStatusCode(403);
        }

        if (! $this->canRunMaintenanceTools()) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Akses ditolak. Hanya admin yang dapat menjalankan fitur ini.',
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ])->setStatusCode(403);
        }

        $state = $this->getActiveSyncState();
        if ($state === null) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Tidak ada proses sinkronisasi yang dapat dilanjutkan.',
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ])->setStatusCode(409);
        }

        $processedRows = (int) ($state['processed_rows'] ?? 0);
        $totalRows = (int) ($state['total_rows'] ?? 0);

        return $this->response->setJSON([
            'ok' => true,
            'message' => 'Melanjutkan sinkronisasi dari progress terakhir.',
            'progress' => $this->computeSyncProgress($processedRows, $totalRows, $state),
            'syncMeta' => $this->buildSyncMeta($state),
            'csrfToken' => csrf_token(),
            'csrfHash' => csrf_hash(),
        ]);
    }

    /**
     * @param array<string,mixed> $state
     * @return array<string,mixed>
     */
    private function buildSyncMeta(array $state): array
    {
        $tableIndex = (int) ($state['table_index'] ?? 0);
        $tables = is_array($state['tables'] ?? null) ? $state['tables'] : [];
        $currentTable = null;

        if ($tableIndex >= 0 && $tableIndex < count($tables)) {
            $row = $tables[$tableIndex];
            if (is_array($row)) {
                $currentTable = [
                    'name' => (string) ($row['name'] ?? ''),
                    'offset' => (int) ($row['offset'] ?? 0),
                    'row_count' => (int) ($row['row_count'] ?? 0),
                    'batch_size' => (int) ($row['batch_size'] ?? $this->getSyncBatchSize()),
                ];
            }
        }

        return [
            'table_index' => $tableIndex,
            'total_tables' => (int) ($state['total_tables'] ?? count($tables)),
            'processed_rows' => (int) ($state['processed_rows'] ?? 0),
            'total_rows' => (int) ($state['total_rows'] ?? 0),
            'current_table' => $currentTable,
        ];
    }

    private function getSyncBatchSize(): int
    {
        $raw = $this->envFirst([
            'SYNC_BATCH_SIZE',
            'sync.batch_size',
        ]);

        $max = $this->getSyncBatchMax();

        if (! is_numeric($raw)) {
            return min(self::SYNC_BATCH_SIZE, $max);
        }

        $size = (int) $raw;
        if ($size < 100) {
            $size = 100;
        }

        if ($size > $max) {
            $size = $max;
        }

        return $size;
    }

    private function getSyncBatchMax(): int
    {
        $raw = $this->envFirst([
            'SYNC_BATCH_MAX',
            'sync.batch_max',
        ]);

        if (! is_numeric($raw)) {
            return self::SYNC_BATCH_MAX;
        }

        $size = (int) $raw;
        if ($size < 100) {
            $size = 100;
        }

        if ($size > self::SYNC_BATCH_MAX) {
            $size = self::SYNC_BATCH_MAX;
        }

        return $size;
    }

    private function discoverSinglePrimaryKey(BaseConnection $db, string $tableName): ?string
    {
        try {
            $fields = $db->getFieldData($tableName);
        } catch (Throwable $e) {
            return null;
        }

        $primaryCandidates = [];
        foreach ($fields as $field) {
            if (! is_object($field)) {
                continue;
            }

            $isPrimary = (int) ($field->primary_key ?? 0) === 1;
            $name = trim((string) ($field->name ?? ''));

            if ($isPrimary && $name !== '') {
                $primaryCandidates[] = $name;
            }
        }

        if (count($primaryCandidates) !== 1) {
            return null;
        }

        return $primaryCandidates[0];
    }

    public function runMigrateCommand()
    {
        if (! $this->canRunMaintenanceTools()) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Anda tidak memiliki hak akses untuk menjalankan command maintenance.',
                'output' => '',
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ])->setStatusCode(403);
        }

        try {
            $migrations = service('migrations');
            $result = $migrations->latest();

            if ($result === false) {
                return $this->response->setJSON([
                    'ok' => false,
                    'message' => 'Migrasi gagal dijalankan.',
                    'output' => 'Migration runner mengembalikan status gagal. Periksa tabel migrations dan konfigurasi database.',
                    'csrfToken' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                ])->setStatusCode(500);
            }
        } catch (Throwable $e) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Migrasi gagal dijalankan.',
                'output' => $e->getMessage(),
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ])->setStatusCode(500);
        }

        return $this->response->setJSON([
            'ok' => true,
            'message' => 'Migrasi berhasil dijalankan.',
            'output' => 'Database sudah di-upgrade ke versi migration terbaru.',
            'csrfToken' => csrf_token(),
            'csrfHash' => csrf_hash(),
        ]);
    }

    public function runSeederCommand()
    {
        if (! $this->canRunMaintenanceTools()) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Anda tidak memiliki hak akses untuk menjalankan command maintenance.',
                'output' => '',
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ])->setStatusCode(403);
        }

        $seeder = trim((string) $this->request->getPost('seeder_class'));

        $availableSeeders = $this->discoverSeederFiles();
        if (! isset($availableSeeders[$seeder])) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Seeder tidak ditemukan di app/Database/Seeds.',
                'output' => '',
                'seederOptions' => $this->getSeederOptionsPayload(),
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ])->setStatusCode(422);
        }

        if ($seeder === '' || preg_match('/^[A-Za-z_\\\\][A-Za-z0-9_\\\\]*$/', $seeder) !== 1) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Nama class seeder tidak valid.',
                'output' => '',
                'seederOptions' => $this->getSeederOptionsPayload(),
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ])->setStatusCode(422);
        }

        try {
            $seederRunner = Database::seeder();
            $seederRunner->call($seeder);
            $result = [
                'ok' => true,
                'message' => 'Seeder berhasil dijalankan.',
                'output' => 'Seeder ' . $seeder . ' berhasil dieksekusi.',
            ];
        } catch (Throwable $e) {
            $result = [
                'ok' => false,
                'message' => 'Seeder gagal dijalankan.',
                'output' => $e->getMessage(),
            ];
        }

        $this->recordSeederRun(
            $seeder,
            (string) ($availableSeeders[$seeder]['hash'] ?? ''),
            $result['ok'] ? 'success' : 'failed',
            $result['output']
        );

        return $this->response->setJSON([
            'ok' => $result['ok'],
            'message' => $result['message'],
            'output' => $result['output'],
            'seederOptions' => $this->getSeederOptionsPayload(),
            'csrfToken' => csrf_token(),
            'csrfHash' => csrf_hash(),
        ])->setStatusCode($result['ok'] ? 200 : 500);
    }

    public function runSnakeCaseScenario()
    {
        if (! $this->canRunMaintenanceTools()) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Anda tidak memiliki hak akses untuk menjalankan command maintenance.',
                'output' => '',
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ])->setStatusCode(403);
        }

        $db = db_connect();
        $before = $this->collectNonSnakeCaseColumns($db);

        if ($before === []) {
            return $this->response->setJSON([
                'ok' => true,
                'message' => 'Penamaan field sudah sesuai snake_case.',
                'output' => 'Tidak ditemukan kolom non-snake_case pada tabel yang tersedia.',
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ]);
        }

        $migrationClasses = [
            \App\Database\Migrations\MigrateLegacyLaporanHarianSchema::class,
            \App\Database\Migrations\ForceNormalizeLaporanHarianColumns::class,
            \App\Database\Migrations\NormalizeRemainingLaporanHarianSnakeCase::class,
            \App\Database\Migrations\NormalizeTrnSaldoPelangganSnakeCase::class,
        ];

        $executed = [];
        $autoRenamed = [];
        $autoSkipped = [];

        try {
            $db->transException(true)->transStart();

            foreach ($migrationClasses as $className) {
                if (! class_exists($className)) {
                    continue;
                }

                $migration = new $className($db, Database::forge($db));
                if (! method_exists($migration, 'up')) {
                    continue;
                }

                $migration->up();
                $executed[] = $className;
            }

            [$autoRenamed, $autoSkipped] = $this->normalizeAllColumnsToSnakeCase($db);

            $db->transComplete();
        } catch (Throwable $e) {
            log_message('error', 'SNAKE_CASE_SCENARIO_FAILED: {message}', ['message' => $e->getMessage()]);

            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Gagal menjalankan skenario snake_case.',
                'output' => $e->getMessage(),
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ])->setStatusCode(500);
        }

        $after = $this->collectNonSnakeCaseColumns($db);

        $formatIssues = static function (array $issues): string {
            if ($issues === []) {
                return '-';
            }

            $lines = [];
            foreach ($issues as $table => $columns) {
                $lines[] = $table . ': ' . implode(', ', $columns);
            }

            return implode("\n", $lines);
        };

        $output = implode("\n", [
            'Kolom non-snake_case sebelum normalisasi:',
            $formatIssues($before),
            '',
            'Migration scenario yang dijalankan:',
            $executed === [] ? '-' : implode("\n", $executed),
            '',
            'Auto rename tambahan (generic snake_case):',
            $autoRenamed === [] ? '-' : implode("\n", $autoRenamed),
            '',
            'Kolom yang dilewati (potensi konflik):',
            $autoSkipped === [] ? '-' : implode("\n", $autoSkipped),
            '',
            'Kolom non-snake_case sesudah normalisasi:',
            $formatIssues($after),
        ]);

        return $this->response->setJSON([
            'ok' => true,
            'message' => $after === []
                ? 'Skenario snake_case berhasil dieksekusi (huruf kecil).'
                : 'Skenario snake_case dijalankan, masih ada kolom yang belum snake_case.',
            'output' => $output,
            'csrfToken' => csrf_token(),
            'csrfHash' => csrf_hash(),
        ]);
    }

    public function runGitPullCommand()
    {
        if (! $this->canRunMaintenanceTools()) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Anda tidak memiliki hak akses untuk menjalankan command maintenance.',
                'output' => '',
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ])->setStatusCode(403);
        }

        if (ENVIRONMENT !== 'production') {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Fitur git pull hanya tersedia di production.',
                'output' => '',
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ])->setStatusCode(403);
        }

        $status = $this->inspectGitPullStatus();
        if (! $status['ok']) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => $status['message'],
                'output' => $status['output'],
                'branch' => $status['branch'],
                'upstream' => $status['upstream'],
                'pendingCount' => $status['pendingCount'],
                'aheadCount' => $status['aheadCount'],
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ])->setStatusCode(500);
        }

        $result = $this->executeShellCommand('git -C ' . escapeshellarg(ROOTPATH) . ' pull --ff-only');
        $latestStatus = $result['ok'] ? $this->inspectGitPullStatus(false) : [
            'branch' => $status['branch'],
            'upstream' => $status['upstream'],
            'pendingCount' => $status['pendingCount'],
            'aheadCount' => $status['aheadCount'],
        ];

        return $this->response->setJSON([
            'ok' => $result['ok'],
            'message' => $result['message'],
            'output' => $result['output'],
            'branch' => $latestStatus['branch'] ?? '',
            'upstream' => $latestStatus['upstream'] ?? '',
            'pendingCount' => $latestStatus['pendingCount'] ?? 0,
            'aheadCount' => $latestStatus['aheadCount'] ?? 0,
            'csrfToken' => csrf_token(),
            'csrfHash' => csrf_hash(),
        ])->setStatusCode($result['ok'] ? 200 : 500);
    }

    public function gitPullStatus()
    {
        if (! $this->canRunMaintenanceTools()) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Anda tidak memiliki hak akses untuk menjalankan command maintenance.',
                'output' => '',
                'pendingCount' => 0,
                'aheadCount' => 0,
                'branch' => '',
                'upstream' => '',
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ])->setStatusCode(403);
        }

        if (ENVIRONMENT !== 'production') {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Fitur git pull hanya tersedia di production.',
                'output' => '',
                'pendingCount' => 0,
                'aheadCount' => 0,
                'branch' => '',
                'upstream' => '',
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ])->setStatusCode(403);
        }

        $status = $this->inspectGitPullStatus();

        return $this->response->setJSON([
            'ok' => $status['ok'],
            'message' => $status['message'],
            'output' => $status['output'],
            'pendingCount' => $status['pendingCount'],
            'aheadCount' => $status['aheadCount'],
            'branch' => $status['branch'],
            'upstream' => $status['upstream'],
            'hasUpdates' => $status['pendingCount'] > 0,
            'csrfToken' => csrf_token(),
            'csrfHash' => csrf_hash(),
        ])->setStatusCode($status['ok'] ? 200 : 500);
    }

    public function listSeederOptions()
    {
        if (! $this->canRunMaintenanceTools()) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Akses ditolak. Hanya admin yang dapat melihat daftar seeder maintenance.',
                'options' => ['pending' => [], 'all' => []],
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ])->setStatusCode(403);
        }

        return $this->response->setJSON([
            'ok' => true,
            'message' => 'Daftar seeder berhasil dimuat.',
            'options' => $this->getSeederOptionsPayload(),
            'csrfToken' => csrf_token(),
            'csrfHash' => csrf_hash(),
        ]);
    }

    private function hasValidUpload(?UploadedFile $file): bool
    {
        return $file instanceof UploadedFile && $file->isValid() && ! $file->hasMoved();
    }

    private function storeUpload(UploadedFile $file, string $prefix): string
    {
        $targetDirectory = FCPATH . self::BRANDING_UPLOAD_DIR;

        if (! is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0755, true);
        }

        $extension = strtolower((string) $file->getClientExtension());
        $safeExtension = preg_replace('/[^a-z0-9]+/', '', $extension) ?: 'png';
        $fileName = $prefix . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $safeExtension;
        $file->move($targetDirectory, $fileName, true);

        return self::BRANDING_UPLOAD_DIR . '/' . $fileName;
    }

    private function deleteOldUploadedFile(?string $path): void
    {
        if (! is_string($path) || $path === '') {
            return;
        }

        if (strpos($path, self::BRANDING_UPLOAD_DIR . '/') !== 0) {
            return;
        }

        $absolutePath = FCPATH . $path;
        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }

    private function isValidHexColor(?string $color): bool
    {
        return is_string($color) && preg_match('/^#[0-9a-fA-F]{6}$/', $color) === 1;
    }

    /**
     * @param mixed $value
     */
    private function normalizeAutoLogoutMinutes($value, int $fallback): int
    {
        if (! is_numeric($value)) {
            return $fallback;
        }

        $minutes = (int) $value;
        if ($minutes < 1) {
            return 1;
        }

        if ($minutes > 1440) {
            return 1440;
        }

        return $minutes;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getMenuRows(BaseConnection $db, string $table, string $select, array $orderBy): array
    {
        if (! $db->tableExists($table)) {
            return [];
        }

        $builder = $db->table($table)->select($select);
        foreach ($orderBy as $column => $direction) {
            $builder->orderBy($column, $direction);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * @param mixed $value
     *
     * @return array<string, array<string, mixed>>
     */
    private function toArray($value): array
    {
        return is_array($value) ? $value : [];
    }

    /**
     * @param array<string, array<string, mixed>> $rows
     * @param array<string, string> $schema
     */
    private function applyMenuUpdates(BaseConnection $db, string $table, array $rows, array $schema): void
    {
        if ($rows === [] || ! $db->tableExists($table)) {
            return;
        }

        foreach ($rows as $id => $row) {
            if (! is_array($row)) {
                continue;
            }

            $menuId = trim((string) $id);
            if ($menuId === '') {
                continue;
            }

            $payload = [];
            foreach ($schema as $field => $type) {
                if (! array_key_exists($field, $row)) {
                    continue;
                }

                $value = $row[$field];

                if ($type === 'int') {
                    $payload[$field] = $this->normalizeIntOrNull($value);
                    continue;
                }

                if ($type === 'required') {
                    $normalized = trim((string) $value);
                    if ($normalized === '') {
                        // Keep existing value when submitted value is empty.
                        continue;
                    }

                    $payload[$field] = mb_substr($normalized, 0, 255);
                    continue;
                }

                if ($type === 'nullable') {
                    $normalized = trim((string) $value);
                    $payload[$field] = $normalized === '' ? null : mb_substr($normalized, 0, 255);
                }
            }

            if ($payload === []) {
                continue;
            }

            $db->table($table)->where('id', $menuId)->update($payload);
        }
    }

    /**
     * @param mixed $value
     */
    private function normalizeIntOrNull($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function computeSyncProgress(int $processedRows, int $totalRows, array $state): float
    {
        if ($totalRows > 0) {
            return min(100, round(($processedRows / max(1, $totalRows)) * 100, 2));
        }

        $tableIndex = (int) ($state['table_index'] ?? 0);
        $totalTables = (int) ($state['total_tables'] ?? 0);

        if ($totalTables <= 0) {
            return 100;
        }

        return min(100, round(($tableIndex / $totalTables) * 100, 2));
    }

    private function canRunMaintenanceTools(): bool
    {
        return $this->resolveIsSuperAdministrator();
    }

    /**
     * @return array{ok:bool,message:string,output:string,pendingCount:int,aheadCount:int,branch:string,upstream:string}
     */
    private function inspectGitPullStatus(bool $refreshRemote = true): array
    {
        if (! $this->isShellFunctionEnabled('exec')) {
            return [
                'ok' => false,
                'message' => 'Perintah gagal dijalankan karena fungsi exec() dinonaktifkan di server.',
                'output' => 'Server hosting menonaktifkan exec(). Jalankan perintah lewat SSH/terminal server atau minta provider mengaktifkan fungsi shell execution.',
                'pendingCount' => 0,
                'aheadCount' => 0,
                'branch' => '',
                'upstream' => '',
            ];
        }

        $rootPath = escapeshellarg(ROOTPATH);
        $gitCheck = $this->executeShellCommandDetailed('git -C ' . $rootPath . ' rev-parse --is-inside-work-tree');
        if (! $gitCheck['ok']) {
            return [
                'ok' => false,
                'message' => 'Direktori aplikasi bukan working tree git yang valid.',
                'output' => $gitCheck['output'],
                'pendingCount' => 0,
                'aheadCount' => 0,
                'branch' => '',
                'upstream' => '',
            ];
        }

        if ($refreshRemote) {
            $fetchResult = $this->executeShellCommandDetailed('git -C ' . $rootPath . ' fetch --quiet');
            if (! $fetchResult['ok']) {
                return [
                    'ok' => false,
                    'message' => 'Gagal memeriksa update dari remote repository.',
                    'output' => $fetchResult['output'],
                    'pendingCount' => 0,
                    'aheadCount' => 0,
                    'branch' => '',
                    'upstream' => '',
                ];
            }
        }

        $branchResult = $this->executeShellCommandDetailed('git -C ' . $rootPath . ' rev-parse --abbrev-ref HEAD');
        if (! $branchResult['ok']) {
            return [
                'ok' => false,
                'message' => 'Gagal membaca branch aktif repository.',
                'output' => $branchResult['output'],
                'pendingCount' => 0,
                'aheadCount' => 0,
                'branch' => '',
                'upstream' => '',
            ];
        }

        $upstreamResult = $this->executeShellCommandDetailed(
            'git -C ' . $rootPath . ' rev-parse --abbrev-ref --symbolic-full-name ' . escapeshellarg('@{upstream}')
        );
        if (! $upstreamResult['ok']) {
            return [
                'ok' => false,
                'message' => 'Branch aktif belum terhubung ke upstream remote.',
                'output' => $upstreamResult['output'],
                'pendingCount' => 0,
                'aheadCount' => 0,
                'branch' => trim($branchResult['output']),
                'upstream' => '',
            ];
        }

        $countResult = $this->executeShellCommandDetailed(
            'git -C ' . $rootPath . ' rev-list --left-right --count ' . escapeshellarg('HEAD...@{upstream}')
        );
        if (! $countResult['ok']) {
            return [
                'ok' => false,
                'message' => 'Gagal menghitung selisih commit terhadap upstream.',
                'output' => $countResult['output'],
                'pendingCount' => 0,
                'aheadCount' => 0,
                'branch' => trim($branchResult['output']),
                'upstream' => trim($upstreamResult['output']),
            ];
        }

        $counts = preg_split('/\s+/', trim($countResult['output']));
        $aheadCount = isset($counts[0]) ? (int) $counts[0] : 0;
        $pendingCount = isset($counts[1]) ? (int) $counts[1] : 0;
        $branch = trim($branchResult['output']);
        $upstream = trim($upstreamResult['output']);
        $statusMessage = $pendingCount > 0
            ? 'Terdapat ' . $pendingCount . ' commit yang belum di-pull dari ' . $upstream . '.'
            : 'Repository sudah terbaru.';
        $statusOutput = implode("\n", array_filter([
            'Branch aktif: ' . $branch,
            'Upstream: ' . $upstream,
            'Commit lokal belum di-push: ' . $aheadCount,
            'Commit remote belum di-pull: ' . $pendingCount,
        ], static fn ($line) => $line !== ''));

        return [
            'ok' => true,
            'message' => $statusMessage,
            'output' => $statusOutput,
            'pendingCount' => $pendingCount,
            'aheadCount' => $aheadCount,
            'branch' => $branch,
            'upstream' => $upstream,
        ];
    }

    /**
     * @return array{pending:array<int,array<string,string>>,all:array<int,array<string,string>>}
     */
    private function getSeederOptionsPayload(): array
    {
        if (! $this->canRunMaintenanceTools()) {
            return ['pending' => [], 'all' => []];
        }

        $seederFiles = $this->discoverSeederFiles();
        $runMap = $this->getSeederRunMap();

        $pending = [];
        $all = [];

        foreach ($seederFiles as $className => $meta) {
            $hash = (string) ($meta['hash'] ?? '');
            $history = $runMap[$className] ?? null;
            $lastHash = is_array($history) ? (string) ($history['file_hash'] ?? '') : '';

            $status = 'up-to-date';
            $statusLabel = 'Sudah terbaru';

            if ($history === null) {
                $status = 'new';
                $statusLabel = 'Belum dijalankan';
            } elseif ($lastHash !== '' && $lastHash !== $hash) {
                $status = 'updated';
                $statusLabel = 'Perlu diperbarui';
            }

            $row = [
                'class' => $className,
                'status' => $status,
                'label' => $className . ' - ' . $statusLabel,
            ];

            $all[] = $row;

            if ($status === 'new' || $status === 'updated') {
                $pending[] = $row;
            }
        }

        return [
            'pending' => $pending,
            'all' => $all,
        ];
    }

    /**
     * @return array<string,array{path:string,hash:string}>
     */
    private function discoverSeederFiles(): array
    {
        $pattern = APPPATH . 'Database/Seeds/*.php';
        $files = glob($pattern) ?: [];
        sort($files);

        $seeders = [];
        foreach ($files as $filePath) {
            $className = pathinfo($filePath, PATHINFO_FILENAME);
            if ($className === '') {
                continue;
            }

            $hash = (string) @sha1_file($filePath);
            $seeders[$className] = [
                'path' => $filePath,
                'hash' => $hash,
            ];
        }

        return $seeders;
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    private function getSeederRunMap(): array
    {
        $db = db_connect();
        if (! $db->tableExists(self::SEEDER_RUNS_TABLE)) {
            return [];
        }

        $rows = $db->table(self::SEEDER_RUNS_TABLE)
            ->select('seeder_class, file_hash, run_count, last_status, last_run_at')
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $className = (string) ($row['seeder_class'] ?? '');
            if ($className === '') {
                continue;
            }

            $map[$className] = $row;
        }

        return $map;
    }

    private function recordSeederRun(string $className, string $hash, string $status, string $output): void
    {
        $db = db_connect();
        if (! $db->tableExists(self::SEEDER_RUNS_TABLE)) {
            return;
        }

        $existing = $db->table(self::SEEDER_RUNS_TABLE)
            ->select('run_count')
            ->where('seeder_class', $className)
            ->get()
            ->getRowArray();

        $runCount = (int) ($existing['run_count'] ?? 0) + 1;
        $payload = [
            'seeder_class' => $className,
            'file_hash' => $hash,
            'run_count' => $runCount,
            'last_status' => $status,
            'last_output' => mb_substr($output, 0, 65000),
            'last_run_at' => date('Y-m-d H:i:s'),
            'updated_by' => (string) session('username'),
        ];

        if ($existing === null) {
            $db->table(self::SEEDER_RUNS_TABLE)->insert($payload);
            return;
        }

        $db->table(self::SEEDER_RUNS_TABLE)
            ->where('seeder_class', $className)
            ->update($payload);
    }

    /**
     * @return array{ok:bool,message:string,output:string}
     */
    private function executeSparkCommand(string $sparkArguments): array
    {
        $phpBinary = PHP_BINARY !== '' ? PHP_BINARY : 'php';
        $sparkPath = ROOTPATH . 'spark';
        $command = escapeshellarg($phpBinary) . ' ' . escapeshellarg($sparkPath) . ' ' . $sparkArguments;

        return $this->executeShellCommand($command);
    }

    /**
     * @return array{ok:bool,message:string,output:string}
     */
    private function executeShellCommand(string $command): array
    {
        if (! $this->isShellFunctionEnabled('exec')) {
            return [
                'ok' => false,
                'message' => 'Perintah gagal dijalankan karena fungsi exec() dinonaktifkan di server.',
                'output' => 'Server hosting menonaktifkan exec(). Jalankan perintah lewat SSH/terminal server atau minta provider mengaktifkan fungsi shell execution.',
            ];
        }

        $output = [];
        $exitCode = 1;

        exec($command . ' 2>&1', $output, $exitCode);

        $commandOutput = trim(implode("\n", $output));

        return [
            'ok' => $exitCode === 0,
            'message' => $exitCode === 0 ? 'Perintah berhasil dijalankan.' : 'Perintah gagal dijalankan.',
            'output' => $commandOutput,
        ];
    }

    /**
     * @return array{ok:bool,output:string,exitCode:int}
     */
    private function executeShellCommandDetailed(string $command): array
    {
        if (! $this->isShellFunctionEnabled('exec')) {
            return [
                'ok' => false,
                'output' => 'Server hosting menonaktifkan exec().',
                'exitCode' => 127,
            ];
        }

        $output = [];
        $exitCode = 1;

        exec($command . ' 2>&1', $output, $exitCode);

        return [
            'ok' => $exitCode === 0,
            'output' => trim(implode("\n", $output)),
            'exitCode' => $exitCode,
        ];
    }

    /**
     * @return array<string,array<int,string>>
     */
    private function collectNonSnakeCaseColumns(BaseConnection $db): array
    {
        $tables = $db->listTables();
        $issues = [];

        foreach ($tables as $table) {
            if (! is_string($table) || $table === '' || $table === 'migrations') {
                continue;
            }

            try {
                $fields = $db->getFieldData($table);
            } catch (Throwable $e) {
                continue;
            }

            $nonSnakeColumns = [];
            foreach ($fields as $field) {
                if (! is_object($field)) {
                    continue;
                }

                $name = trim((string) ($field->name ?? ''));
                if ($name === '') {
                    continue;
                }

                if (preg_match('/^[a-z][a-z0-9_]*$/', $name) === 1) {
                    continue;
                }

                $nonSnakeColumns[] = $name;
            }

            if ($nonSnakeColumns !== []) {
                $issues[$table] = $nonSnakeColumns;
            }
        }

        return $issues;
    }

    /**
     * @return array{0: array<int,string>, 1: array<int,string>}
     */
    private function normalizeAllColumnsToSnakeCase(BaseConnection $db): array
    {
        $tables = $db->listTables();
        $renamed = [];
        $skipped = [];

        foreach ($tables as $table) {
            if (! is_string($table) || $table === '' || $table === 'migrations') {
                continue;
            }

            try {
                $rows = $db->query('SHOW FULL COLUMNS FROM `' . str_replace('`', '``', $table) . '`')->getResultArray();
            } catch (Throwable $e) {
                continue;
            }

            if (! is_array($rows) || $rows === []) {
                continue;
            }

            // Refresh map each round because column names can change while iterating.
            $refreshMap = static function () use ($db, $table): array {
                $result = $db->query('SHOW COLUMNS FROM `' . str_replace('`', '``', $table) . '`')->getResultArray();
                $map = [];
                foreach ($result as $item) {
                    $name = (string) ($item['Field'] ?? '');
                    if ($name !== '') {
                        $map[strtolower($name)] = $name;
                    }
                }
                return $map;
            };

            $columnMap = $refreshMap();

            foreach ($rows as $row) {
                $oldNameRaw = trim((string) ($row['Field'] ?? ''));
                if ($oldNameRaw === '') {
                    continue;
                }

                $oldLookup = strtolower($oldNameRaw);
                $actualOldName = $columnMap[$oldLookup] ?? $oldNameRaw;
                $newName = $this->toSnakeCaseColumnName($actualOldName);

                if ($newName === '' || $newName === $actualOldName) {
                    continue;
                }

                $newLookup = strtolower($newName);
                if (isset($columnMap[$newLookup]) && $columnMap[$newLookup] !== $actualOldName) {
                    $skipped[] = $table . '.' . $actualOldName . ' -> ' . $newName . ' (target sudah ada)';
                    continue;
                }

                $definition = $this->buildColumnDefinitionFromShowFull($row);

                $ok = $this->renameColumnToSnakeCase($db, $table, $actualOldName, $newName, $definition);
                if ($ok) {
                    $renamed[] = $table . '.' . $actualOldName . ' -> ' . $newName;
                    $columnMap = $refreshMap();
                } else {
                    $skipped[] = $table . '.' . $actualOldName . ' -> ' . $newName . ' (rename gagal)';
                }
            }
        }

        return [$renamed, $skipped];
    }

    private function toSnakeCaseColumnName(string $name): string
    {
        $value = trim($name);
        if ($value === '') {
            return '';
        }

        $value = preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $value) ?? $value;
        $value = preg_replace('/[^A-Za-z0-9]+/', '_', $value) ?? $value;
        $value = strtolower(trim($value, '_'));

        if ($value === '') {
            return '';
        }

        if (preg_match('/^[0-9]/', $value) === 1) {
            $value = 'col_' . $value;
        }

        return $value;
    }

    /**
     * @param array<string,mixed> $row
     */
    private function buildColumnDefinitionFromShowFull(array $row): string
    {
        $type = trim((string) ($row['Type'] ?? 'VARCHAR(255)'));
        $null = strtoupper((string) ($row['Null'] ?? 'YES')) === 'NO' ? 'NOT NULL' : 'NULL';
        $defaultValue = $row['Default'] ?? null;
        $extra = trim((string) ($row['Extra'] ?? ''));

        $parts = [$type, $null];

        if ($defaultValue !== null) {
            $defaultText = (string) $defaultValue;
            $upperDefault = strtoupper($defaultText);

            if (in_array($upperDefault, ['CURRENT_TIMESTAMP', 'CURRENT_TIMESTAMP()', 'NULL'], true)) {
                $parts[] = 'DEFAULT ' . $upperDefault;
            } else {
                $parts[] = "DEFAULT '" . str_replace("'", "\\'", $defaultText) . "'";
            }
        }

        if ($extra !== '') {
            $parts[] = $extra;
        }

        return implode(' ', $parts);
    }

    private function renameColumnToSnakeCase(
        BaseConnection $db,
        string $table,
        string $oldName,
        string $newName,
        string $definition
    ): bool {
        $escTable = str_replace('`', '``', $table);
        $escOld = str_replace('`', '``', $oldName);
        $escNew = str_replace('`', '``', $newName);

        try {
            if (strtolower($oldName) === strtolower($newName)) {
                $tmp = 'tmp_' . substr(md5($table . '_' . $oldName . '_' . $newName), 0, 12);
                $escTmp = str_replace('`', '``', $tmp);

                $db->query(sprintf(
                    'ALTER TABLE `%s` CHANGE COLUMN `%s` `%s` %s',
                    $escTable,
                    $escOld,
                    $escTmp,
                    $definition
                ));

                $db->query(sprintf(
                    'ALTER TABLE `%s` CHANGE COLUMN `%s` `%s` %s',
                    $escTable,
                    $escTmp,
                    $escNew,
                    $definition
                ));

                return true;
            }

            $db->query(sprintf(
                'ALTER TABLE `%s` CHANGE COLUMN `%s` `%s` %s',
                $escTable,
                $escOld,
                $escNew,
                $definition
            ));

            return true;
        } catch (Throwable $e) {
            log_message('warning', 'SNAKE_CASE_RENAME_FAILED {table}.{old}->{new}: {message}', [
                'table' => $table,
                'old' => $oldName,
                'new' => $newName,
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private function isShellFunctionEnabled(string $functionName): bool
    {
        if (! function_exists($functionName)) {
            return false;
        }

        $disabled = (string) ini_get('disable_functions');
        if ($disabled === '') {
            return true;
        }

        $disabledFunctions = array_map('trim', explode(',', strtolower($disabled)));

        return ! in_array(strtolower($functionName), $disabledFunctions, true);
    }

    /**
     * @return array<string,mixed>|null
     */
    private function buildSyncSourceConfigFromEnv(): ?array
    {
        $host = $this->envFirst([
            'SYNC_SOURCE_HOST',
            'sync.source.host',
            'database.sync.hostname',
        ]);

        $portRaw = $this->envFirst([
            'SYNC_SOURCE_PORT',
            'sync.source.port',
            'database.sync.port',
        ]);

        $database = $this->envFirst([
            'SYNC_SOURCE_DATABASE',
            'sync.source.database',
            'database.sync.database',
        ]);

        $username = $this->envFirst([
            'SYNC_SOURCE_USERNAME',
            'sync.source.username',
            'database.sync.username',
        ]);

        $password = $this->envFirst([
            'SYNC_SOURCE_PASSWORD',
            'sync.source.password',
            'database.sync.password',
        ]);

        if ($host === '' || $database === '' || $username === '') {
            return null;
        }

        $port = is_numeric($portRaw) ? (int) $portRaw : 3306;
        if ($port < 1 || $port > 65535) {
            $port = 3306;
        }

        return [
            'DBDriver' => 'MySQLi',
            'hostname' => $host,
            'port' => $port,
            'database' => $database,
            'username' => $username,
            'password' => $password,
            'DBPrefix' => '',
            'pConnect' => false,
            'DBDebug' => false,
            'charset' => 'utf8mb4',
            'DBCollat' => 'utf8mb4_general_ci',
        ];
    }

    private function envFirst(array $keys): string
    {
        foreach ($keys as $key) {
            $value = getenv($key);
            if ($value === false) {
                continue;
            }

            $text = trim((string) $value);
            if ($text !== '') {
                return $text;
            }
        }

        return '';
    }

    /**
     * @return array<string,mixed>|null
     */
    private function getActiveSyncState(): ?array
    {
        $state = session(self::SYNC_STATE_KEY);
        if (! is_array($state)) {
            return null;
        }

        $tables = is_array($state['tables'] ?? null) ? $state['tables'] : [];
        if ($tables === []) {
            return null;
        }

        return $state;
    }

    /**
     * @return array<int,array{name:string,row_count:int|null,target_exists:bool}>
     */
    private function getSyncTableOptions(): array
    {
        if (! $this->canRunMaintenanceTools() || ENVIRONMENT !== 'development') {
            return [];
        }

        try {
            $targetDb = db_connect();
            $targetTables = $targetDb->listTables();
        } catch (Throwable $e) {
            log_message('warning', 'SYNC_TABLE_OPTIONS_FAILED: {message}', ['message' => $e->getMessage()]);
            return [];
        }

        $targetFiltered = array_values(array_filter($targetTables, static function ($table): bool {
            return is_string($table) && $table !== '' && $table !== 'migrations';
        }));

        sort($targetFiltered);

        $targetMap = [];
        foreach ($targetFiltered as $tableName) {
            $targetMap[$tableName] = true;
        }

        $sourceConfig = $this->buildSyncSourceConfigFromEnv();
        if ($sourceConfig === null) {
            return array_map(static fn(string $table): array => [
                'name' => $table,
                'row_count' => null,
                'target_exists' => true,
            ], $targetFiltered);
        }

        try {
            $sourceDb = Database::connect($sourceConfig, false);
            $sourceTables = $sourceDb->listTables();
        } catch (Throwable $e) {
            log_message('warning', 'SYNC_SOURCE_TABLE_OPTIONS_FAILED: {message}', ['message' => $e->getMessage()]);
            return array_map(static fn(string $table): array => [
                'name' => $table,
                'row_count' => null,
                'target_exists' => true,
            ], $targetFiltered);
        }

        $sourceNames = [];
        foreach ($sourceTables as $tableName) {
            if (! is_string($tableName) || $tableName === '' || $tableName === 'migrations') {
                continue;
            }

            $sourceNames[] = $tableName;
        }

        $sourceNames = array_values(array_unique($sourceNames));
        sort($sourceNames);

        $rows = [];
        foreach ($sourceNames as $tableName) {
            $rowCount = null;

            try {
                $rowCount = (int) $sourceDb->table($tableName)->countAllResults();
            } catch (Throwable $e) {
                log_message('warning', 'SYNC_SOURCE_TABLE_COUNT_FAILED {table}: {message}', [
                    'table' => $tableName,
                    'message' => $e->getMessage(),
                ]);
            }

            $rows[] = [
                'name' => $tableName,
                'row_count' => $rowCount,
                'target_exists' => isset($targetMap[$tableName]),
            ];
        }

        // If source contains no table metadata, keep development tables as fallback.
        if ($rows === []) {
            $rows = array_map(static fn(string $table): array => [
                'name' => $table,
                'row_count' => null,
                'target_exists' => true,
            ], $targetFiltered);
        }

        return $rows;
    }

    private function createTargetTableFromSource(BaseConnection $sourceDb, BaseConnection $targetDb, string $tableName): bool
    {
        $escapedTable = str_replace('`', '``', $tableName);

        try {
            $createRow = $sourceDb->query('SHOW CREATE TABLE `' . $escapedTable . '`')->getRowArray();
        } catch (Throwable $e) {
            log_message('warning', 'SYNC_CREATE_TABLE_READ_FAILED {table}: {message}', [
                'table' => $tableName,
                'message' => $e->getMessage(),
            ]);
            return false;
        }

        if (! is_array($createRow)) {
            return false;
        }

        $createSql = (string) ($createRow['Create Table'] ?? '');
        if ($createSql === '') {
            return false;
        }

        try {
            $targetDb->query($createSql);
        } catch (Throwable $e) {
            log_message('warning', 'SYNC_CREATE_TABLE_EXEC_FAILED {table}: {message}', [
                'table' => $tableName,
                'message' => $e->getMessage(),
            ]);
            return false;
        }

        return true;
    }
}
