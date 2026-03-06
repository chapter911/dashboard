<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$rows = is_array($rows ?? null) ? $rows : [];
$tarifOptions = is_array($tarifOptions ?? null) ? $tarifOptions : [];
$kategoriOptions = is_array($kategoriOptions ?? null) ? $kategoriOptions : ['TR', 'TM', 'TT'];
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
                    <h5 class="mb-0">Daftar Kategori Tegangan</h5>
                    <small class="text-muted">Kelola mapping tarif ke kategori tegangan tanpa menggunakan view database.</small>
                </div>
                <button
                    type="button"
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#kategoriTeganganModal"
                    data-mode="create"
                >
                    <i class="ti ti-plus me-1"></i> Tambah / Atur Kategori
                </button>
            </div>

            <div class="table-responsive text-nowrap">
                <table id="kategoriTeganganTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 70px;">No</th>
                            <th>Tarif</th>
                            <th>Kategori Tegangan</th>
                            <th style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $idx => $row): ?>
                            <?php
                            $id = (int) ($row['id'] ?? 0);
                            $tarif = trim((string) ($row['tarif'] ?? ''));
                            $kategori = strtoupper(trim((string) ($row['kategori_tegangan'] ?? '')));
                            ?>
                            <tr>
                                <td><?= esc((string) ($idx + 1)) ?></td>
                                <td><?= esc($tarif !== '' ? $tarif : '-') ?></td>
                                <td>
                                    <?php if ($kategori !== ''): ?>
                                        <span class="badge bg-label-info"><?= esc($kategori) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary btn-edit me-1"
                                        data-id="<?= esc((string) $id) ?>"
                                        data-tarif="<?= esc($tarif) ?>"
                                        data-kategori="<?= esc($kategori) ?>"
                                    >
                                        Edit
                                    </button>

                                    <?php if ($id > 0): ?>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-danger btn-delete"
                                            data-id="<?= esc((string) $id) ?>"
                                            data-tarif="<?= esc($tarif) ?>"
                                        >
                                            Hapus
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

<div class="modal fade" id="kategoriTeganganModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= site_url('C_Master/KategoriTegangan/save') ?>" method="post" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" id="modal_id" name="id" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="kategoriModalTitle">Tambah Kategori Tegangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modal_tarif" class="form-label">Tarif</label>
                        <input
                            type="text"
                            class="form-control"
                            id="modal_tarif"
                            name="tarif"
                            list="tarifOptions"
                            maxlength="100"
                            required
                            placeholder="Contoh: R-1/TR"
                        >
                        <datalist id="tarifOptions">
                            <?php foreach ($tarifOptions as $tarifOption): ?>
                                <option value="<?= esc((string) $tarifOption) ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                        <div class="form-text">Anda dapat memilih tarif yang sudah ada atau mengetik tarif baru.</div>
                    </div>

                    <div class="mb-0">
                        <label for="modal_kategori" class="form-label">Kategori Tegangan</label>
                        <select class="form-select" id="modal_kategori" name="kategori_tegangan" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($kategoriOptions as $option): ?>
                                <option value="<?= esc((string) $option) ?>"><?= esc((string) $option) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteKategoriModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= site_url('C_Master/KategoriTegangan/delete') ?>" method="post" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" id="delete_id" name="id" value="">
                <div class="modal-header">
                    <h5 class="modal-title">Hapus Kategori Tegangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Yakin ingin menghapus kategori untuk tarif <strong id="delete_tarif">-</strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    (function () {
        const datatableCssHref = '<?= base_url('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') ?>';
        if (!document.querySelector('link[data-role="datatable-bs5"]')) {
            const styleLink = document.createElement('link');
            styleLink.rel = 'stylesheet';
            styleLink.href = datatableCssHref;
            styleLink.setAttribute('data-role', 'datatable-bs5');
            document.head.appendChild(styleLink);
        }
    })();
</script>
<script src="<?= base_url('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') ?>"></script>
<script>
    (function () {
        if (window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.DataTable === 'function') {
            window.jQuery('#kategoriTeganganTable').DataTable({
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100],
                order: [[1, 'asc']],
                columnDefs: [
                    { targets: [0, 3], orderable: false, searchable: false }
                ],
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                    infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
                    emptyTable: 'Belum ada data tarif.',
                    zeroRecords: 'Data tidak ditemukan',
                    paginate: {
                        first: 'Awal',
                        last: 'Akhir',
                        next: 'Berikutnya',
                        previous: 'Sebelumnya'
                    }
                }
            });
        }

        const modalElement = document.getElementById('kategoriTeganganModal');
        const deleteModalElement = document.getElementById('deleteKategoriModal');

        const modalId = document.getElementById('modal_id');
        const modalTarif = document.getElementById('modal_tarif');
        const modalKategori = document.getElementById('modal_kategori');
        const modalTitle = document.getElementById('kategoriModalTitle');

        const btnCreate = document.querySelector('[data-target="#kategoriTeganganModal"], [data-bs-target="#kategoriTeganganModal"]');
        const editButtons = document.querySelectorAll('.btn-edit');
        const deleteButtons = document.querySelectorAll('.btn-delete');

        if (btnCreate) {
            btnCreate.addEventListener('click', function () {
                modalId.value = '';
                modalTarif.value = '';
                modalTarif.readOnly = false;
                modalKategori.value = '';
                if (modalTitle) {
                    modalTitle.textContent = 'Tambah Kategori Tegangan';
                }
            });
        }

        editButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                modalId.value = button.getAttribute('data-id') || '';
                modalTarif.value = button.getAttribute('data-tarif') || '';
                modalTarif.readOnly = false;
                modalKategori.value = button.getAttribute('data-kategori') || '';
                if (modalTitle) {
                    modalTitle.textContent = 'Edit Kategori Tegangan';
                }

                if (modalElement && window.bootstrap && typeof window.bootstrap.Modal === 'function') {
                    const modal = window.bootstrap.Modal.getOrCreateInstance(modalElement);
                    modal.show();
                }
            });
        });

        deleteButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const deleteId = document.getElementById('delete_id');
                const deleteTarif = document.getElementById('delete_tarif');

                if (deleteId) {
                    deleteId.value = button.getAttribute('data-id') || '';
                }

                if (deleteTarif) {
                    deleteTarif.textContent = button.getAttribute('data-tarif') || '-';
                }

                if (deleteModalElement && window.bootstrap && typeof window.bootstrap.Modal === 'function') {
                    const deleteModal = window.bootstrap.Modal.getOrCreateInstance(deleteModalElement);
                    deleteModal.show();
                }
            });
        });
    })();
</script>
<?= $this->endSection() ?>
