<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;
use Config\Database;
use Throwable;

class C_Utility extends BaseController
{
    public function userGroupAccess(int $groupId): string
    {
        $db = Database::connect();
        $isEmbedded = (string) ($this->request->getGet('embed') ?? '0') === '1';

        $group = $db->table('mst_user_group')
            ->select('group_id, group_name')
            ->where('group_id', $groupId)
            ->get()
            ->getRowArray();

        if (! is_array($group)) {
            return view('errors/html/error_404');
        }

        $menus = $this->buildMenuAccessTree();
        $accessRows = $db->table('menu_akses')
            ->select('menu_id, fitur_add, fitur_edit, fitur_delete, fitur_export, fitur_import, fitur_approval')
            ->where('group_id', $groupId)
            ->get()
            ->getResultArray();

        $accessMap = [];
        foreach ($accessRows as $row) {
            $menuId = (string) ($row['menu_id'] ?? '');
            if ($menuId === '') {
                continue;
            }

            $accessMap[$menuId] = [
                'fitur_add' => $this->bitToInt($row['fitur_add'] ?? 0),
                'fitur_edit' => $this->bitToInt($row['fitur_edit'] ?? 0),
                'fitur_delete' => $this->bitToInt($row['fitur_delete'] ?? 0),
                'fitur_export' => $this->bitToInt($row['fitur_export'] ?? 0),
                'fitur_import' => $this->bitToInt($row['fitur_import'] ?? 0),
                'fitur_approval' => $this->bitToInt($row['fitur_approval'] ?? 0),
            ];
        }

        $viewName = $isEmbedded ? 'utility/user_group_access_embed' : 'utility/user_group_access';

        return view($viewName, [
            'title' => 'Akses Menu Group',
            'pageHeading' => 'Akses Menu Group: ' . ($group['group_name'] ?? ''),
            'group' => $group,
            'menus' => $menus,
            'accessMap' => $accessMap,
            'isEmbedded' => $isEmbedded,
        ]);
    }

    public function saveUserGroupAccess(int $groupId): RedirectResponse
    {
        $db = Database::connect();
        $isEmbedded = (string) ($this->request->getPost('embed') ?? '0') === '1'
            || (string) ($this->request->getGet('embed') ?? '0') === '1';

        $groupExists = $db->table('mst_user_group')->where('group_id', $groupId)->countAllResults() > 0;
        if (! $groupExists) {
            return redirect()->to('/C_Utility/UserGroup')->with('error', 'User group tidak ditemukan.');
        }

        $selectedMenuIds = $this->request->getPost('access');
        $selectedMenuIds = is_array($selectedMenuIds) ? array_keys($selectedMenuIds) : [];

        $featureAdd = $this->request->getPost('fitur_add');
        $featureEdit = $this->request->getPost('fitur_edit');
        $featureDelete = $this->request->getPost('fitur_delete');
        $featureExport = $this->request->getPost('fitur_export');
        $featureImport = $this->request->getPost('fitur_import');
        $featureApproval = $this->request->getPost('fitur_approval');

        $featureAdd = is_array($featureAdd) ? $featureAdd : [];
        $featureEdit = is_array($featureEdit) ? $featureEdit : [];
        $featureDelete = is_array($featureDelete) ? $featureDelete : [];
        $featureExport = is_array($featureExport) ? $featureExport : [];
        $featureImport = is_array($featureImport) ? $featureImport : [];
        $featureApproval = is_array($featureApproval) ? $featureApproval : [];

        $validMenuIds = $this->getAllMenuIds();
        $selectedMenuIds = array_values(array_filter($selectedMenuIds, static fn($id) => isset($validMenuIds[$id])));

        $db->transStart();

        $db->table('menu_akses')->where('group_id', $groupId)->delete();

        foreach ($selectedMenuIds as $menuId) {
            $db->table('menu_akses')->insert([
                'group_id' => $groupId,
                'menu_id' => $menuId,
                'fitur_add' => $this->toBitBoolInt($featureAdd[$menuId] ?? 0),
                'fitur_edit' => $this->toBitBoolInt($featureEdit[$menuId] ?? 0),
                'fitur_delete' => $this->toBitBoolInt($featureDelete[$menuId] ?? 0),
                'fitur_export' => $this->toBitBoolInt($featureExport[$menuId] ?? 0),
                'fitur_import' => $this->toBitBoolInt($featureImport[$menuId] ?? 0),
                'fitur_approval' => $this->toBitBoolInt($featureApproval[$menuId] ?? 0),
            ]);
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan akses menu group.');
        }

        $redirectUrl = '/C_Utility/UserGroup/access/' . $groupId;
        if ($isEmbedded) {
            $redirectUrl .= '?embed=1';
        }

        return redirect()->to($redirectUrl)->with('success', 'Akses menu berhasil diperbarui.');
    }

    public function userGroupIndex(): string
    {
        $groups = [];

        try {
            $groups = Database::connect()
                ->table('mst_user_group g')
                ->select('g.group_id, g.group_name, g.remark, g.is_active, g.created_by, g.created_date, COUNT(u.username) AS total_user')
                ->join('mst_user u', 'u.group_id = g.group_id', 'left')
                ->groupBy('g.group_id, g.group_name, g.remark, g.is_active, g.created_by, g.created_date')
                ->orderBy('g.group_id', 'ASC')
                ->get()
                ->getResultArray();
        } catch (Throwable $e) {
            log_message('error', 'USER_GROUP_INDEX_LOAD_FAILED: {message}', ['message' => $e->getMessage()]);
            session()->setFlashdata('error', 'Gagal memuat data user group.');
        }

        return view('utility/user_group_index', [
            'title' => 'Manajemen User Group',
            'pageHeading' => 'Manajemen User Group',
            'groups' => $groups,
        ]);
    }

    public function createUserGroup(): RedirectResponse
    {
        $rules = [
            'group_name' => 'required|min_length[3]|max_length[255]',
            'remark' => 'permit_empty|max_length[255]',
            'is_active' => 'permit_empty|in_list[0,1]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/C_Utility/UserGroup')->withInput()->with('errors', $this->validator->getErrors());
        }

        $groupName = trim((string) $this->request->getPost('group_name'));
        $db = Database::connect();

        try {
            $exists = $db->table('mst_user_group')
                ->where('LOWER(group_name)', strtolower($groupName))
                ->countAllResults() > 0;

            if ($exists) {
                return redirect()->to('/C_Utility/UserGroup')->withInput()->with('error', 'Nama group sudah digunakan.');
            }

            $db->table('mst_user_group')->insert([
                'group_name' => $groupName,
                'remark' => trim((string) $this->request->getPost('remark')),
                'is_active' => $this->toBitBoolInt($this->request->getPost('is_active')),
                'created_by' => (string) session('username'),
                'created_date' => date('Y-m-d'),
            ]);
        } catch (Throwable $e) {
            log_message('error', 'USER_GROUP_CREATE_FAILED: {message}', ['message' => $e->getMessage()]);

            return redirect()->to('/C_Utility/UserGroup')->withInput()->with('error', 'Gagal menambahkan user group.');
        }

        return redirect()->to('/C_Utility/UserGroup')->with('success', 'User group berhasil ditambahkan.');
    }

    public function updateUserGroup(): RedirectResponse
    {
        $rules = [
            'group_id' => 'required|integer',
            'group_name' => 'required|min_length[3]|max_length[255]',
            'remark' => 'permit_empty|max_length[255]',
            'is_active' => 'permit_empty|in_list[0,1]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/C_Utility/UserGroup')->withInput()->with('errors', $this->validator->getErrors());
        }

        $groupId = (int) $this->request->getPost('group_id');
        $groupName = trim((string) $this->request->getPost('group_name'));
        $db = Database::connect();

        try {
            $exists = $db->table('mst_user_group')->where('group_id', $groupId)->countAllResults() > 0;
            if (! $exists) {
                return redirect()->to('/C_Utility/UserGroup')->with('error', 'User group tidak ditemukan.');
            }

            $duplicate = $db->table('mst_user_group')
                ->where('group_id !=', $groupId)
                ->where('LOWER(group_name)', strtolower($groupName))
                ->countAllResults() > 0;

            if ($duplicate) {
                return redirect()->to('/C_Utility/UserGroup')->withInput()->with('error', 'Nama group sudah digunakan.');
            }

            $db->table('mst_user_group')->where('group_id', $groupId)->update([
                'group_name' => $groupName,
                'remark' => trim((string) $this->request->getPost('remark')),
                'is_active' => $this->toBitBoolInt($this->request->getPost('is_active')),
            ]);
        } catch (Throwable $e) {
            log_message('error', 'USER_GROUP_UPDATE_FAILED: {message}', ['message' => $e->getMessage()]);

            return redirect()->to('/C_Utility/UserGroup')->withInput()->with('error', 'Gagal mengubah user group.');
        }

        return redirect()->to('/C_Utility/UserGroup')->with('success', 'User group berhasil diubah.');
    }

    public function deleteUserGroup(): RedirectResponse
    {
        $rules = [
            'group_id' => 'required|integer',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/C_Utility/UserGroup')->with('error', 'Permintaan hapus user group tidak valid.');
        }

        $groupId = (int) $this->request->getPost('group_id');

        if ($groupId === 1) {
            return redirect()->to('/C_Utility/UserGroup')->with('error', 'Group Super Administrator tidak dapat dihapus.');
        }

        try {
            $db = Database::connect();

            $usedByUsers = $db->table('mst_user')->where('group_id', $groupId)->countAllResults() > 0;
            if ($usedByUsers) {
                return redirect()->to('/C_Utility/UserGroup')->with('error', 'User group tidak dapat dihapus karena masih digunakan user.');
            }

            $db->table('menu_akses')->where('group_id', $groupId)->delete();
            $db->table('mst_user_group')->where('group_id', $groupId)->delete();
        } catch (Throwable $e) {
            log_message('error', 'USER_GROUP_DELETE_FAILED: {message}', ['message' => $e->getMessage()]);

            return redirect()->to('/C_Utility/UserGroup')->with('error', 'Gagal menghapus user group.');
        }

        return redirect()->to('/C_Utility/UserGroup')->with('success', 'User group berhasil dihapus.');
    }

    public function userIndex(): string
    {
        $db = Database::connect();

        $users = [];
        $groups = [];
        $units = [];

        try {
            $users = $db->table('mst_user u')
                ->select('u.username, u.nama, u.email, u.group_id, u.unit_id, u.jabatan_id, u.is_active, u.web_access, g.group_name')
                ->join('mst_user_group g', 'g.group_id = u.group_id', 'left')
                ->orderBy('u.username', 'ASC')
                ->get()
                ->getResultArray();

            $groups = $db->table('mst_user_group')
                ->select('group_id, group_name')
                ->orderBy('group_name', 'ASC')
                ->get()
                ->getResultArray();

            if ($db->tableExists('mst_unit')) {
                $units = $db->table('mst_unit')
                    ->select('unit_id, unit_name')
                    ->orderBy('unit_name', 'ASC')
                    ->get()
                    ->getResultArray();
            }
        } catch (Throwable $e) {
            log_message('error', 'USER_INDEX_LOAD_FAILED: {message}', ['message' => $e->getMessage()]);
            session()->setFlashdata('error', 'Gagal memuat data user.');
        }

        return view('utility/user_index', [
            'title' => 'Manajemen User',
            'pageHeading' => 'Manajemen User',
            'users' => $users,
            'groups' => $groups,
            'units' => $units,
            'isSuperAdmin' => $this->isSuperAdministrator(),
        ]);
    }

    public function createUser(): RedirectResponse
    {
        $rules = [
            'username' => 'required|min_length[3]|max_length[100]|alpha_numeric_punct',
            'nama' => 'required|min_length[3]|max_length[255]',
            'email' => 'permit_empty|valid_email|max_length[255]',
            'group_id' => 'required|integer',
            'unit_id' => 'permit_empty|integer',
            'password' => 'required|min_length[8]|max_length[255]',
            'password_confirmation' => 'required|matches[password]',
            'web_access' => 'permit_empty|in_list[0,1]',
            'android_access' => 'permit_empty|in_list[0,1]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/C_Utility/User')->withInput()->with('errors', $this->validator->getErrors());
        }

        $password = (string) $this->request->getPost('password');
        if (! $this->isStrongPassword($password)) {
            return redirect()->to('/C_Utility/User')->withInput()->with(
                'error',
                'Password harus minimal 8 karakter dan mengandung huruf besar, huruf kecil, angka, serta simbol.'
            );
        }

        $db = Database::connect();
        $username = trim((string) $this->request->getPost('username'));

        try {
            $exists = $db->table('mst_user')->where('username', $username)->countAllResults() > 0;
            if ($exists) {
                return redirect()->to('/C_Utility/User')->withInput()->with('error', 'Username sudah terdaftar.');
            }

            $db->table('mst_user')->insert([
                'username' => $username,
                'nama' => trim((string) $this->request->getPost('nama')),
                'email' => trim((string) $this->request->getPost('email')),
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'group_id' => (int) $this->request->getPost('group_id'),
                'unit_id' => $this->nullableInt($this->request->getPost('unit_id')),
                'jabatan_id' => null,
                'is_active' => 1,
                'web_access' => $this->toBoolInt($this->request->getPost('web_access')),
                'android_access' => $this->toBoolInt($this->request->getPost('android_access')),
                'created_by' => (string) session('username'),
                'created_date' => date('Y-m-d H:i:s'),
            ]);
        } catch (Throwable $e) {
            log_message('error', 'USER_CREATE_FAILED: {message}', ['message' => $e->getMessage()]);

            return redirect()->to('/C_Utility/User')->withInput()->with('error', 'Gagal menambahkan user.');
        }

        return redirect()->to('/C_Utility/User')->with('success', 'User berhasil ditambahkan.');
    }

    public function updateUser(): RedirectResponse
    {
        $rules = [
            'username' => 'required|min_length[3]|max_length[100]|alpha_numeric_punct',
            'nama' => 'required|min_length[3]|max_length[255]',
            'email' => 'permit_empty|valid_email|max_length[255]',
            'group_id' => 'required|integer',
            'unit_id' => 'permit_empty|integer',
            'web_access' => 'permit_empty|in_list[0,1]',
            'android_access' => 'permit_empty|in_list[0,1]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/C_Utility/User')->withInput()->with('errors', $this->validator->getErrors());
        }

        $username = trim((string) $this->request->getPost('username'));
        $db = Database::connect();

        try {
            $exists = $db->table('mst_user')->where('username', $username)->countAllResults() > 0;
            if (! $exists) {
                return redirect()->to('/C_Utility/User')->with('error', 'User tidak ditemukan.');
            }

            $db->table('mst_user')->where('username', $username)->update([
                'nama' => trim((string) $this->request->getPost('nama')),
                'email' => trim((string) $this->request->getPost('email')),
                'group_id' => (int) $this->request->getPost('group_id'),
                'unit_id' => $this->nullableInt($this->request->getPost('unit_id')),
                'web_access' => $this->toBoolInt($this->request->getPost('web_access')),
                'android_access' => $this->toBoolInt($this->request->getPost('android_access')),
            ]);
        } catch (Throwable $e) {
            log_message('error', 'USER_UPDATE_FAILED: {message}', ['message' => $e->getMessage()]);

            return redirect()->to('/C_Utility/User')->with('error', 'Gagal mengubah user.');
        }

        return redirect()->to('/C_Utility/User')->with('success', 'User berhasil diubah.');
    }

    public function toggleActive(): RedirectResponse
    {
        $rules = [
            'username' => 'required|min_length[3]|max_length[100]',
            'is_active' => 'required|in_list[0,1]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/C_Utility/User')->with('error', 'Permintaan nonaktifkan/aktifkan tidak valid.');
        }

        $username = trim((string) $this->request->getPost('username'));
        $targetActive = (int) $this->request->getPost('is_active');

        if ($username === (string) session('username') && $targetActive === 0) {
            return redirect()->to('/C_Utility/User')->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        try {
            Database::connect()
                ->table('mst_user')
                ->where('username', $username)
                ->update(['is_active' => $targetActive]);
        } catch (Throwable $e) {
            log_message('error', 'USER_TOGGLE_FAILED: {message}', ['message' => $e->getMessage()]);

            return redirect()->to('/C_Utility/User')->with('error', 'Gagal mengubah status user.');
        }

        $message = $targetActive === 1 ? 'User berhasil diaktifkan.' : 'User berhasil dinonaktifkan.';

        return redirect()->to('/C_Utility/User')->with('success', $message);
    }

    public function resetPassword(): RedirectResponse
    {
        if (! $this->isSuperAdministrator()) {
            return redirect()->to('/C_Utility/User')->with('error', 'Hanya Super Administrator yang dapat reset password.');
        }

        $rules = [
            'username' => 'required|min_length[3]|max_length[100]',
            'new_password' => 'required|min_length[8]|max_length[255]',
            'new_password_confirmation' => 'required|matches[new_password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/C_Utility/User')->withInput()->with('errors', $this->validator->getErrors());
        }

        $newPassword = (string) $this->request->getPost('new_password');
        if (! $this->isStrongPassword($newPassword)) {
            return redirect()->to('/C_Utility/User')->with(
                'error',
                'Password baru harus minimal 8 karakter dan mengandung huruf besar, huruf kecil, angka, serta simbol.'
            );
        }

        $username = trim((string) $this->request->getPost('username'));

        try {
            Database::connect()
                ->table('mst_user')
                ->where('username', $username)
                ->update(['password' => password_hash($newPassword, PASSWORD_DEFAULT)]);
        } catch (Throwable $e) {
            log_message('error', 'USER_RESET_PASSWORD_FAILED: {message}', ['message' => $e->getMessage()]);

            return redirect()->to('/C_Utility/User')->with('error', 'Gagal reset password user.');
        }

        return redirect()->to('/C_Utility/User')->with('success', 'Password user berhasil direset.');
    }

    public function loginHistoryIndex(): string
    {
        $db = Database::connect();
        $histories = [];

        try {
            if (! $db->tableExists('trn_login')) {
                session()->setFlashdata('error', 'Tabel trn_login tidak ditemukan.');
            } else {
                $histories = $db->table('trn_login')
                    ->select('id, username, event_type, is_logged_in, ip_address, ip_network, user_agent, notes, created_date')
                    ->orderBy('created_date', 'DESC')
                    ->orderBy('id', 'DESC')
                    ->get()
                    ->getResultArray();
            }
        } catch (Throwable $e) {
            log_message('error', 'LOGIN_HISTORY_LOAD_FAILED: {message}', ['message' => $e->getMessage()]);
            session()->setFlashdata('error', 'Gagal memuat riwayat login.');
        }

        return view('utility/login_history', [
            'title' => 'Login History',
            'pageHeading' => 'Login History',
            'histories' => $histories,
        ]);
    }

    private function isSuperAdministrator(): bool
    {
        $groupId = (int) (session('group_id') ?? 0);
        if ($groupId === 1) {
            return true;
        }

        try {
            $row = Database::connect()
                ->table('mst_user_group')
                ->select('group_name')
                ->where('group_id', $groupId)
                ->get()
                ->getRowArray();

            if (! is_array($row)) {
                return false;
            }

            return strtolower(trim((string) ($row['group_name'] ?? ''))) === 'super administrator';
        } catch (Throwable $e) {
            return false;
        }
    }

    private function isStrongPassword(string $password): bool
    {
        $isLongEnough = strlen($password) >= 8;
        $hasUpper = preg_match('/[A-Z]/', $password) === 1;
        $hasLower = preg_match('/[a-z]/', $password) === 1;
        $hasDigit = preg_match('/\d/', $password) === 1;
        $hasSymbol = preg_match('/[^a-zA-Z\d]/', $password) === 1;

        return $isLongEnough && $hasUpper && $hasLower && $hasDigit && $hasSymbol;
    }

    /**
     * @param mixed $value
     */
    private function nullableInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    /**
     * @param mixed $value
     */
    private function toBoolInt($value): int
    {
        return (string) $value === '1' ? 1 : 0;
    }

    /**
     * @param mixed $value
     */
    private function toBitBoolInt($value): int
    {
        return (string) $value === '1' ? 1 : 0;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildMenuAccessTree(): array
    {
        $db = Database::connect();

        $lv1 = $db->table('menu_lv1')->select('id, label, link, icon, ordering')->orderBy('ordering', 'ASC')->get()->getResultArray();
        $lv2 = $db->tableExists('menu_lv2')
            ? $db->table('menu_lv2')->select('id, label, link, icon, header, ordering')->orderBy('ordering', 'ASC')->get()->getResultArray()
            : [];
        $lv3 = $db->tableExists('menu_lv3')
            ? $db->table('menu_lv3')->select('id, label, link, icon, header, ordering')->orderBy('ordering', 'ASC')->get()->getResultArray()
            : [];

        $lv3ByHeader = [];
        foreach ($lv3 as $row) {
            $header = (string) ($row['header'] ?? '');
            if ($header === '') {
                continue;
            }

            $lv3ByHeader[$header][] = [
                'id' => (string) ($row['id'] ?? ''),
                'label' => (string) ($row['label'] ?? ''),
                'link' => (string) ($row['link'] ?? '#'),
                'children' => [],
            ];
        }

        $lv2ByHeader = [];
        foreach ($lv2 as $row) {
            $header = (string) ($row['header'] ?? '');
            if ($header === '') {
                continue;
            }

            $id = (string) ($row['id'] ?? '');
            $lv2ByHeader[$header][] = [
                'id' => $id,
                'label' => (string) ($row['label'] ?? ''),
                'link' => (string) ($row['link'] ?? '#'),
                'children' => $lv3ByHeader[$id] ?? [],
            ];
        }

        $tree = [];
        foreach ($lv1 as $row) {
            $id = (string) ($row['id'] ?? '');
            $tree[] = [
                'id' => $id,
                'label' => (string) ($row['label'] ?? ''),
                'link' => (string) ($row['link'] ?? '#'),
                'children' => $lv2ByHeader[$id] ?? [],
            ];
        }

        return $tree;
    }

    /**
     * @return array<string, true>
     */
    private function getAllMenuIds(): array
    {
        $db = Database::connect();
        $ids = [];

        foreach (['menu_lv1', 'menu_lv2', 'menu_lv3'] as $table) {
            if (! $db->tableExists($table)) {
                continue;
            }

            $rows = $db->table($table)->select('id')->get()->getResultArray();
            foreach ($rows as $row) {
                $id = (string) ($row['id'] ?? '');
                if ($id !== '') {
                    $ids[$id] = true;
                }
            }
        }

        return $ids;
    }

    /**
     * @param mixed $value
     */
    private function bitToInt($value): int
    {
        if (is_string($value)) {
            return $value !== "\0" && $value !== '' ? 1 : 0;
        }

        return (int) $value === 1 ? 1 : 0;
    }
}
