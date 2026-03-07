<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$units = is_array($units ?? null) ? $units : [];
$filters = is_array($filters ?? null) ? $filters : [];
$selectedTahun = (string) ($filters['tahun'] ?? '*');
$currentYear = (int) ($currentYear ?? date('Y'));
?>

<link rel="stylesheet" href="<?= base_url('assets/vendor/libs/select2/select2.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') ?>">

<div class="row">
    <div class="col-12">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success" role="alert"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Filter Target</h5>
            <small class="text-muted">Kelola target penggantian meter per unit dan tahun.</small>
        </div>
        <button class="btn btn-primary" id="btnAddTarget" data-bs-toggle="modal" data-bs-target="#targetModal" type="button">Tambah Target</button>
    </div>
    <div class="card-body">
        <form method="post" action="<?= site_url('C_Laporan/Target/data') ?>" class="row g-3" id="targetFilterForm">
            <?= csrf_field() ?>
            <div class="col-md-3">
                <label class="form-label">Tahun</label>
                <select class="form-select" name="tahun" id="target_filter_tahun">
                    <option value="*" <?= $selectedTahun === '*' ? 'selected' : '' ?>>Semua Tahun</option>
                    <?php for ($year = $currentYear + 1; $year >= $currentYear - 5; $year--): ?>
                        <option value="<?= esc((string) $year) ?>" <?= $selectedTahun === (string) $year ? 'selected' : '' ?>><?= esc((string) $year) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">Unit</label>
                <select class="form-select select2" name="unit_id" id="target_filter_unit">
                    <option value="*">Semua Unit</option>
                    <?php foreach ($units as $unit): ?>
                        <?php $uid = (string) ($unit['unit_id'] ?? ''); ?>
                        <option value="<?= esc($uid) ?>" <?= ($filters['unit_id'] ?? '*') === $uid ? 'selected' : '' ?>><?= esc((string) ($unit['unit_name'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary me-2" type="button" id="btnFilterTarget">Tampilkan</button>
                <button class="btn btn-label-secondary" type="button" id="btnResetTarget">Reset</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Jumlah Unit Target</div>
                <div class="fs-4 fw-semibold" id="targetSummaryRows">0</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Total Target Tua</div>
                <div class="fs-4 fw-semibold" id="targetSummaryTua">0</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Total Target Rusak</div>
                <div class="fs-4 fw-semibold" id="targetSummaryRusak">0</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Daftar Target Tahunan</h5>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-bordered table-striped mb-0" id="targetTable">
            <thead>
                <tr>
                    <th style="width: 64px;">No</th>
                    <th>Unit</th>
                    <th style="width: 120px;">Tahun</th>
                    <th class="text-end">Target Tua</th>
                    <th class="text-end">Target Rusak</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="targetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= site_url('C_Laporan/Target/save') ?>" id="targetForm">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="target_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="targetModalTitle">Simpan Target</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Unit</label>
                        <select class="form-select select2" name="unit_id" id="unit_id" required>
                            <option value="">Pilih Unit</option>
                            <?php foreach ($units as $unit): ?>
                                <option value="<?= esc((string) ($unit['unit_id'] ?? '')) ?>"><?= esc((string) ($unit['unit_name'] ?? '')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tahun</label>
                        <input class="form-control" type="number" min="2000" max="2100" name="tahun" id="tahun" value="<?= esc((string) $currentYear) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Target Tua</label>
                        <input class="form-control" type="number" min="0" name="target_tua" id="target_tua" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Target Rusak</label>
                        <input class="form-control" type="number" min="0" name="target_rusak" id="target_rusak" required>
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

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/vendor/libs/select2/select2.js') ?>"></script>
<script src="<?= base_url('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') ?>"></script>
<script>
    $(function () {
        var $filterForm = $('#targetFilterForm');
        var $targetForm = $('#targetForm');
        var swalActive = false;

        var formatNumber = function (value) {
            var number = Number(value || 0);
            return number.toLocaleString('id-ID');
        };

        var applyCsrf = function (freshToken) {
            if (!freshToken) {
                return;
            }

            var selector = 'input[name="<?= esc(csrf_token()) ?>"]';
            $filterForm.find(selector).val(freshToken);
            $targetForm.find(selector).val(freshToken);
        };

        var showLoading = function (text) {
            if (typeof Swal === 'undefined') {
                return;
            }

            swalActive = true;
            Swal.fire({
                title: 'Mohon Tunggu',
                html: text,
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: function () {
                    Swal.showLoading();
                }
            });
        };

        var hideLoading = function () {
            if (!swalActive || typeof Swal === 'undefined') {
                return;
            }
            Swal.close();
            swalActive = false;
        };

        $('.select2').select2({ width: '100%' });

        var table = $('#targetTable').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            pageLength: 10,
            order: [[2, 'desc']],
            ajax: {
                url: '<?= site_url('C_Laporan/Target/data') ?>',
                type: 'POST',
                data: function (d) {
                    d.unit_id = $('#target_filter_unit').val();
                    d.tahun = $('#target_filter_tahun').val();
                    d['<?= esc(csrf_token()) ?>'] = $filterForm.find('input[name="<?= esc(csrf_token()) ?>"]').val();
                },
                dataSrc: function (json) {
                    if (json && json.meta) {
                        $('#targetSummaryRows').text(formatNumber(json.meta.total_rows));
                        $('#targetSummaryTua').text(formatNumber(json.meta.total_tua));
                        $('#targetSummaryRusak').text(formatNumber(json.meta.total_rusak));
                    }
                    return json.data || [];
                },
                error: function () {
                    hideLoading();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Gagal', 'Gagal memuat data target.', 'error');
                    }
                }
            },
            columns: [
                { data: 'no', orderable: false },
                { data: 'unit_name' },
                { data: 'tahun' },
                { data: 'target_tua', className: 'text-end' },
                { data: 'target_rusak', className: 'text-end' },
                { data: 'total', className: 'text-end fw-semibold' },
                { data: 'aksi', orderable: false, searchable: false, className: 'text-end' }
            ]
        });

        $('#targetTable').on('preXhr.dt', function () {
            showLoading('Mengambil data target...');
        });

        $('#targetTable').on('xhr.dt', function (_e, _settings, _json, xhr) {
            applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
            hideLoading();
        });

        $('#btnFilterTarget').on('click', function () {
            table.draw();
        });

        $('#target_filter_tahun, #target_filter_unit').on('change', function () {
            table.draw();
        });

        $('#btnResetTarget').on('click', function () {
            $('#target_filter_tahun').val('*');
            $('#target_filter_unit').val('*').trigger('change.select2');
            table.draw();
        });

        var resetModal = function () {
            $('#targetModalTitle').text('Simpan Target');
            $('#target_id').val('');
            $('#unit_id').val('').trigger('change.select2');
            $('#tahun').val('<?= esc((string) $currentYear) ?>');
            $('#target_tua').val('');
            $('#target_rusak').val('');
        };

        $('#btnAddTarget').on('click', function () {
            resetModal();
        });

        $(document).on('click', '.btn-edit-target', function () {
            var $btn = $(this);
            $('#targetModalTitle').text('Edit Target');
            $('#target_id').val($btn.data('id') || '');
            $('#unit_id').val(String($btn.data('unit') || '')).trigger('change.select2');
            $('#tahun').val(String($btn.data('tahun') || '<?= esc((string) $currentYear) ?>'));
            $('#target_tua').val(String($btn.data('target-tua') || '0'));
            $('#target_rusak').val(String($btn.data('target-rusak') || '0'));
            $('#targetModal').modal('show');
        });

        $(document).on('click', '.btn-delete-target', function () {
            var id = $(this).data('id');
            if (!id) {
                return;
            }

            Swal.fire({
                title: 'Hapus target?',
                text: 'Data yang dihapus tidak dapat dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }

                showLoading('Menghapus target...');
                $.ajax({
                    url: '<?= site_url('C_Laporan/Target/delete') ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id: id,
                        '<?= esc(csrf_token()) ?>': $filterForm.find('input[name="<?= esc(csrf_token()) ?>"]').val()
                    },
                    success: function (response, _textStatus, xhr) {
                        applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
                        hideLoading();
                        if (response && response.status) {
                            Swal.fire('Berhasil', response.message || 'Target berhasil dihapus.', 'success');
                            table.draw(false);
                        } else {
                            Swal.fire('Gagal', (response && response.message) || 'Gagal menghapus target.', 'error');
                        }
                    },
                    error: function () {
                        hideLoading();
                        Swal.fire('Gagal', 'Gagal menghapus target.', 'error');
                    }
                });
            });
        });

        $targetForm.on('submit', function (e) {
            e.preventDefault();
            showLoading('Menyimpan target...');

            $.ajax({
                url: '<?= site_url('C_Laporan/Target/save') ?>',
                type: 'POST',
                dataType: 'json',
                data: $targetForm.serialize(),
                success: function (response, _textStatus, xhr) {
                    applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
                    hideLoading();

                    if (response && response.status) {
                        $('#targetModal').modal('hide');
                        Swal.fire('Berhasil', response.message || 'Target berhasil disimpan.', 'success');
                        table.draw(false);
                    } else {
                        Swal.fire('Gagal', (response && response.message) || 'Gagal menyimpan target.', 'error');
                    }
                },
                error: function (xhr) {
                    applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
                    hideLoading();
                    Swal.fire('Gagal', 'Gagal menyimpan target.', 'error');
                }
            });
        });

        $('#targetModal').on('hidden.bs.modal', function () {
            resetModal();
        });
    });
</script>
<?= $this->endSection() ?>
