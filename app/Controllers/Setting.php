<?php

namespace App\Controllers;

use App\Models\AppSettingModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Database\BaseConnection;
use Throwable;

class Setting extends BaseController
{
    private const BRANDING_UPLOAD_DIR = 'uploads/branding';

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
}
