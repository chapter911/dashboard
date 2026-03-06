<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$users = is_array($users ?? null) ? $users : [];
$groups = is_array($groups ?? null) ? $groups : [];
$units = is_array($units ?? null) ? $units : [];
$isSuperAdmin = (bool) ($isSuperAdmin ?? false);
?>

<div class="row">
    <div class="col-12">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success" role="alert"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <?php $errors = session()->getFlashdata('errors'); ?>
        <?php if (is_array($errors) && $errors !== []): ?>
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Daftar User</h5>
                    <small class="text-muted">Tidak ada fitur delete. Gunakan tombol nonaktifkan.</small>
                </div>
                <button
                    type="button"
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#addUserModal"
                >
                    <i class="ti ti-plus me-1"></i> Tambah User
                </button>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Group</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($users === []): ?>
                            <tr><td colspan="6" class="text-center text-muted">Belum ada data user.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($users as $user): ?>
                            <?php
                            $username = (string) ($user['username'] ?? '');
                            $active = (int) ($user['is_active'] ?? 0) === 1;
                            $nextStatus = $active ? 0 : 1;
                            ?>
                            <tr>
                                <td><?= esc($username) ?></td>
                                <td><?= esc((string) ($user['nama'] ?? '')) ?></td>
                                <td><?= esc((string) ($user['email'] ?? '')) ?></td>
                                <td><?= esc((string) ($user['group_name'] ?? '-')) ?></td>
                                <td>
                                    <?php if ($active): ?>
                                        <span class="badge bg-label-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-label-secondary">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary me-1 btn-edit-user"
                                        data-username="<?= esc($username) ?>"
                                        data-nama="<?= esc((string) ($user['nama'] ?? '')) ?>"
                                        data-email="<?= esc((string) ($user['email'] ?? '')) ?>"
                                        data-group="<?= esc((string) ($user['group_id'] ?? '')) ?>"
                                        data-unit="<?= esc((string) ($user['unit_id'] ?? '')) ?>"
                                        data-web="<?= esc((string) ($user['web_access'] ?? 0)) ?>"
                                        data-android="<?= esc((string) ($user['android_access'] ?? 0)) ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editUserModal"
                                    >
                                        Edit
                                    </button>

                                    <form action="<?= site_url('C_Utility/User/toggle-active') ?>" method="post" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="username" value="<?= esc($username) ?>">
                                        <input type="hidden" name="is_active" value="<?= esc((string) $nextStatus) ?>">
                                        <button class="btn btn-sm <?= $active ? 'btn-outline-warning' : 'btn-outline-success' ?>" type="submit">
                                            <?= $active ? 'Nonaktifkan' : 'Aktifkan' ?>
                                        </button>
                                    </form>

                                    <?php if ($isSuperAdmin): ?>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-danger ms-1 btn-reset-password"
                                            data-username="<?= esc($username) ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#resetPasswordModal"
                                        >
                                            Reset Password
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?= site_url('C_Utility/User/create') ?>" method="post" novalidate>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Tambah User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="add_username">Username</label>
                            <input id="add_username" name="username" type="text" class="form-control" value="<?= esc(old('username')) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="add_nama">Nama</label>
                            <input id="add_nama" name="nama" type="text" class="form-control" value="<?= esc(old('nama')) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="add_email">Email</label>
                            <input id="add_email" name="email" type="email" class="form-control" value="<?= esc(old('email')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="add_group_id">Group</label>
                            <select id="add_group_id" name="group_id" class="form-select" required>
                                <option value="">Pilih Group</option>
                                <?php foreach ($groups as $group): ?>
                                    <option value="<?= esc((string) ($group['group_id'] ?? '')) ?>" <?= old('group_id') === (string) ($group['group_id'] ?? '') ? 'selected' : '' ?>>
                                        <?= esc((string) ($group['group_name'] ?? '')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="add_unit_id">Unit</label>
                            <select id="add_unit_id" name="unit_id" class="form-select">
                                <option value="">-</option>
                                <?php foreach ($units as $unit): ?>
                                    <option value="<?= esc((string) ($unit['unit_id'] ?? '')) ?>" <?= old('unit_id') === (string) ($unit['unit_id'] ?? '') ? 'selected' : '' ?>>
                                        <?= esc((string) ($unit['unit_name'] ?? '')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="add_password">Password</label>
                            <input id="add_password" name="password" type="password" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="add_password_confirmation">Konfirmasi Password</label>
                            <input id="add_password_confirmation" name="password_confirmation" type="password" class="form-control" required>
                        </div>
                        <div class="col-md-4 d-flex gap-3 align-items-center mt-4">
                            <div class="form-check form-switch">
                                <input type="hidden" name="web_access" value="0">
                                <input class="form-check-input" type="checkbox" id="add_web_access" name="web_access" value="1" <?= old('web_access', '1') === '1' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="add_web_access">Web Access</label>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="android_access" value="0">
                                <input class="form-check-input" type="checkbox" id="add_android_access" name="android_access" value="1" <?= old('android_access', '1') === '1' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="add_android_access">Android Access</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?= site_url('C_Utility/User/update') ?>" method="post" novalidate>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="edit_username">Username</label>
                            <input id="edit_username" name="username" type="text" class="form-control" readonly required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="edit_nama">Nama</label>
                            <input id="edit_nama" name="nama" type="text" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="edit_email">Email</label>
                            <input id="edit_email" name="email" type="email" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="edit_group_id">Group</label>
                            <select id="edit_group_id" name="group_id" class="form-select" required>
                                <option value="">Pilih Group</option>
                                <?php foreach ($groups as $group): ?>
                                    <option value="<?= esc((string) ($group['group_id'] ?? '')) ?>">
                                        <?= esc((string) ($group['group_name'] ?? '')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="edit_unit_id">Unit</label>
                            <select id="edit_unit_id" name="unit_id" class="form-select">
                                <option value="">-</option>
                                <?php foreach ($units as $unit): ?>
                                    <option value="<?= esc((string) ($unit['unit_id'] ?? '')) ?>">
                                        <?= esc((string) ($unit['unit_name'] ?? '')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex gap-3 align-items-center mt-4">
                            <div class="form-check form-switch">
                                <input type="hidden" name="web_access" value="0">
                                <input class="form-check-input" type="checkbox" id="edit_web_access" name="web_access" value="1">
                                <label class="form-check-label" for="edit_web_access">Web Access</label>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="android_access" value="0">
                                <input class="form-check-input" type="checkbox" id="edit_android_access" name="android_access" value="1">
                                <label class="form-check-label" for="edit_android_access">Android Access</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($isSuperAdmin): ?>
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= site_url('C_Utility/User/reset-password') ?>" method="post" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="username" id="reset_username" value="">
                <div class="modal-header">
                    <h5 class="modal-title">Reset Password User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Reset password untuk user: <strong id="reset_username_text">-</strong></p>
                    <div class="mb-3">
                        <label class="form-label" for="new_password">Password Baru</label>
                        <input id="new_password" name="new_password" type="password" class="form-control" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="new_password_confirmation">Konfirmasi Password Baru</label>
                        <input id="new_password_confirmation" name="new_password_confirmation" type="password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    (function () {
        const editButtons = document.querySelectorAll('.btn-edit-user');
        const resetButtons = document.querySelectorAll('.btn-reset-password');

        editButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                document.getElementById('edit_username').value = button.getAttribute('data-username') || '';
                document.getElementById('edit_nama').value = button.getAttribute('data-nama') || '';
                document.getElementById('edit_email').value = button.getAttribute('data-email') || '';
                document.getElementById('edit_group_id').value = button.getAttribute('data-group') || '';
                document.getElementById('edit_unit_id').value = button.getAttribute('data-unit') || '';
                document.getElementById('edit_web_access').checked = (button.getAttribute('data-web') || '0') === '1';
                document.getElementById('edit_android_access').checked = (button.getAttribute('data-android') || '0') === '1';
            });
        });

        resetButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const username = button.getAttribute('data-username') || '';
                document.getElementById('reset_username').value = username;
                document.getElementById('reset_username_text').textContent = username;
            });
        });

        <?php if (old('username') !== null || old('nama') !== null): ?>
        if (window.bootstrap) {
            const addModalElement = document.getElementById('addUserModal');
            if (addModalElement) {
                const addModal = new bootstrap.Modal(addModalElement);
                addModal.show();
            }
        }
        <?php endif; ?>
    })();
</script>
<?= $this->endSection() ?>
