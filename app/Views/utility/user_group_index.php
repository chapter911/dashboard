<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$groups = is_array($groups ?? null) ? $groups : [];
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
                <h5 class="mb-0">Daftar User Group</h5>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserGroupModal">
                    <i class="ti ti-plus me-1"></i> Tambah Group
                </button>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Group</th>
                            <th>Remark</th>
                            <th>Status</th>
                            <th>Total User</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($groups === []): ?>
                            <tr><td colspan="6" class="text-center text-muted">Belum ada data user group.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($groups as $group): ?>
                            <?php
                            $groupId = (int) ($group['group_id'] ?? 0);
                            $isActive = (string) ($group['is_active'] ?? '0') === '1';
                            $totalUser = (int) ($group['total_user'] ?? 0);
                            ?>
                            <tr>
                                <td><?= esc((string) $groupId) ?></td>
                                <td><?= esc((string) ($group['group_name'] ?? '')) ?></td>
                                <td><?= esc((string) ($group['remark'] ?? '')) ?></td>
                                <td>
                                    <?php if ($isActive): ?>
                                        <span class="badge bg-label-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-label-secondary">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc((string) $totalUser) ?></td>
                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-info me-1 btn-access-menu"
                                        data-group-name="<?= esc((string) ($group['group_name'] ?? '')) ?>"
                                        data-access-url="<?= esc(site_url('C_Utility/UserGroup/access/' . $groupId) . '?embed=1') ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#accessMenuModal"
                                    >
                                        Akses Menu
                                    </button>

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary me-1 btn-edit-group"
                                        data-id="<?= esc((string) $groupId) ?>"
                                        data-name="<?= esc((string) ($group['group_name'] ?? '')) ?>"
                                        data-remark="<?= esc((string) ($group['remark'] ?? '')) ?>"
                                        data-active="<?= $isActive ? '1' : '0' ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editUserGroupModal"
                                    >
                                        Edit
                                    </button>

                                    <form action="<?= site_url('C_Utility/UserGroup/delete') ?>" method="post" class="d-inline" onsubmit="return confirm('Hapus user group ini?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="group_id" value="<?= esc((string) $groupId) ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit" <?= $groupId === 1 ? 'disabled' : '' ?>>
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addUserGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= site_url('C_Utility/UserGroup/create') ?>" method="post" novalidate>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Tambah User Group</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="add_group_name">Nama Group</label>
                        <input id="add_group_name" name="group_name" type="text" class="form-control" value="<?= esc(old('group_name')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="add_remark">Remark</label>
                        <input id="add_remark" name="remark" type="text" class="form-control" value="<?= esc(old('remark')) ?>">
                    </div>
                    <div class="form-check form-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" id="add_is_active" name="is_active" value="1" <?= old('is_active', '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="add_is_active">Aktif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Group</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editUserGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= site_url('C_Utility/UserGroup/update') ?>" method="post" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" id="edit_group_id" name="group_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User Group</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="edit_group_name">Nama Group</label>
                        <input id="edit_group_name" name="group_name" type="text" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="edit_remark">Remark</label>
                        <input id="edit_remark" name="remark" type="text" class="form-control">
                    </div>
                    <div class="form-check form-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                        <label class="form-check-label" for="edit_is_active">Aktif</label>
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

<div class="modal fade" id="accessMenuModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Akses Menu Group: <span id="access_group_name">-</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <iframe
                    id="access_menu_iframe"
                    src="about:blank"
                    style="width: 100%; height: 70vh; border: 0;"
                    loading="lazy"
                    referrerpolicy="same-origin"
                ></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="btn_save_access_modal">
                    Simpan Akses
                </button>
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    (function () {
        const editButtons = document.querySelectorAll('.btn-edit-group');
        const accessButtons = document.querySelectorAll('.btn-access-menu');
        const accessIframe = document.getElementById('access_menu_iframe');
        const accessGroupName = document.getElementById('access_group_name');
        const accessMenuModal = document.getElementById('accessMenuModal');
        const saveAccessButton = document.getElementById('btn_save_access_modal');
        let accessAction = '';

        function showLoading(message) {
            if (!window.Swal) {
                return;
            }

            Swal.fire({
                title: message,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: function () {
                    Swal.showLoading();
                }
            });
        }

        function closeLoading() {
            if (window.Swal && Swal.isVisible()) {
                Swal.close();
            }
        }

        editButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                document.getElementById('edit_group_id').value = button.getAttribute('data-id') || '';
                document.getElementById('edit_group_name').value = button.getAttribute('data-name') || '';
                document.getElementById('edit_remark').value = button.getAttribute('data-remark') || '';
                document.getElementById('edit_is_active').checked = (button.getAttribute('data-active') || '0') === '1';
            });
        });

        accessButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const accessUrl = button.getAttribute('data-access-url') || 'about:blank';
                const groupName = button.getAttribute('data-group-name') || '-';

                 accessAction = 'fetch';
                 showLoading('Mengambil data akses menu...');

                if (accessIframe) {
                    accessIframe.src = accessUrl;
                }

                if (accessGroupName) {
                    accessGroupName.textContent = groupName;
                }
            });
        });

        if (accessMenuModal) {
            accessMenuModal.addEventListener('hidden.bs.modal', function () {
                if (accessIframe) {
                    accessIframe.src = 'about:blank';
                }

                accessAction = '';
                closeLoading();
            });
        }

        if (saveAccessButton) {
            saveAccessButton.addEventListener('click', function () {
                if (!accessIframe || !accessIframe.contentWindow) {
                    return;
                }

                const iframeDoc = accessIframe.contentWindow.document;
                const form = iframeDoc.getElementById('access_menu_form');

                if (form && typeof form.submit === 'function') {
                    accessAction = 'save';
                    showLoading('Menyimpan akses menu...');
                    form.submit();
                }
            });
        }

        if (accessIframe) {
            accessIframe.addEventListener('load', function () {
                if (accessAction === 'fetch' || accessAction === 'save') {
                    closeLoading();
                    accessAction = '';
                }
            });
        }

        <?php if (old('group_name') !== null): ?>
        if (window.bootstrap) {
            const addModalElement = document.getElementById('addUserGroupModal');
            if (addModalElement) {
                const addModal = new bootstrap.Modal(addModalElement);
                addModal.show();
            }
        }
        <?php endif; ?>
    })();
</script>
<?= $this->endSection() ?>
