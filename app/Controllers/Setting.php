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
    private const SYNC_BATCH_SIZE = 500;
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
            'formData' => [
                'app_name' => old('app_name', $appName),
                'app_primary_color' => old('app_primary_color', $primaryColor),
                'auto_logout_minutes' => old('auto_logout_minutes', (string) $autoLogoutMinutes),
            ],
        ]);
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
            'source_host' => 'required|max_length[190]',
            'source_port' => 'permit_empty|integer|greater_than_equal_to[1]|less_than_equal_to[65535]',
            'source_database' => 'required|max_length[190]',
            'source_username' => 'required|max_length[190]',
            'source_password' => 'permit_empty|max_length[190]',
            'sync_confirmation' => 'required|in_list[1]',
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

        $sourceConfig = [
            'DBDriver' => 'MySQLi',
            'hostname' => trim((string) $this->request->getPost('source_host')),
            'port' => (int) ($this->request->getPost('source_port') ?: 3306),
            'database' => trim((string) $this->request->getPost('source_database')),
            'username' => trim((string) $this->request->getPost('source_username')),
            'password' => (string) $this->request->getPost('source_password'),
            'DBPrefix' => '',
            'pConnect' => false,
            'DBDebug' => false,
            'charset' => 'utf8mb4',
            'DBCollat' => 'utf8mb4_general_ci',
        ];

        try {
            $sourceDb = Database::connect($sourceConfig, false);
            $targetDb = db_connect();

            $sourceTables = $sourceDb->listTables();
            $targetTables = $targetDb->listTables();

            $targetMap = [];
            foreach ($targetTables as $table) {
                $targetMap[$table] = true;
            }

            $tables = [];
            $totalRows = 0;

            foreach ($sourceTables as $table) {
                if (! isset($targetMap[$table]) || $table === 'migrations') {
                    continue;
                }

                $rowCount = (int) $sourceDb->table($table)->countAllResults();
                $tables[] = [
                    'name' => $table,
                    'row_count' => $rowCount,
                    'offset' => 0,
                    'truncated' => false,
                ];
                $totalRows += $rowCount;
            }

            if ($tables === []) {
                return $this->response->setJSON([
                    'ok' => false,
                    'message' => 'Tidak ada tabel yang bisa disinkronkan.',
                    'csrfToken' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                ])->setStatusCode(422);
            }

            session()->set(self::SYNC_STATE_KEY, [
                'source' => $sourceConfig,
                'tables' => $tables,
                'table_index' => 0,
                'processed_rows' => 0,
                'total_rows' => $totalRows,
                'total_tables' => count($tables),
                'started_at' => date('Y-m-d H:i:s'),
                'started_by' => (string) session('username'),
            ]);
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
            'message' => 'Sinkronisasi dimulai.',
            'progress' => 0,
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
                    'csrfToken' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                ]);
            }

            $table = $tables[$tableIndex];
            $tableName = (string) ($table['name'] ?? '');
            $offset = (int) ($table['offset'] ?? 0);
            $rowCount = (int) ($table['row_count'] ?? 0);
            $truncated = (bool) ($table['truncated'] ?? false);

            if ($tableName === '') {
                $state['table_index'] = $tableIndex + 1;
                session()->set(self::SYNC_STATE_KEY, $state);

                return $this->response->setJSON([
                    'ok' => true,
                    'done' => false,
                    'progress' => $this->computeSyncProgress($processedRows, $totalRows, $state),
                    'message' => 'Melanjutkan sinkronisasi...',
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
                    'csrfToken' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                ]);
            }

            $rows = $sourceDb->table($tableName)
                ->limit(self::SYNC_BATCH_SIZE, $offset)
                ->get()
                ->getResultArray();

            $rowBatchCount = count($rows);

            if ($rowBatchCount > 0) {
                $targetDb->table($tableName)->insertBatch($rows, null, self::SYNC_BATCH_SIZE);
                $offset += $rowBatchCount;
                $processedRows += $rowBatchCount;
                $tables[$tableIndex]['offset'] = $offset;
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
                    : 'Sinkronisasi tabel ' . $tableName . ' berjalan...',
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ]);
        } catch (Throwable $e) {
            log_message('error', 'PROD_SYNC_STEP_FAILED: {message}', ['message' => $e->getMessage()]);
            session()->remove(self::SYNC_STATE_KEY);

            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Sinkronisasi gagal: ' . $e->getMessage(),
                'csrfToken' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ])->setStatusCode(500);
        }
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

        $result = $this->executeShellCommand('git -C ' . escapeshellarg(ROOTPATH) . ' pull --ff-only');

        return $this->response->setJSON([
            'ok' => $result['ok'],
            'message' => $result['message'],
            'output' => $result['output'],
            'csrfToken' => csrf_token(),
            'csrfHash' => csrf_hash(),
        ])->setStatusCode($result['ok'] ? 200 : 500);
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
        return (int) session('group_id') === 1;
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
}
