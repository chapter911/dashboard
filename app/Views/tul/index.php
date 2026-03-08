<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$userGroupId = (int) ($userGroupId ?? 0);
$selectedUnitId = (int) ($selectedUnitId ?? 0);
$units = is_array($units ?? null) ? $units : [];
$currentPeriod = (string) ($currentPeriod ?? date('Y-m'));
?>

<link rel="stylesheet" href="<?= base_url('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') ?>">

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Data TUL</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">Upload Excel</button>
    </div>
    <div class="card-body">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success" role="alert"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <form id="tulFilterForm" class="row g-3 mb-3" method="post" action="<?= site_url('C_TUL/data') ?>">
            <?= csrf_field() ?>
            <div class="col-md-3">
                <label class="form-label">Periode</label>
                <input type="month" class="form-control" id="filter_periode" name="periode" value="<?= esc($currentPeriod) ?>">
            </div>

            <?php if ($userGroupId === 1): ?>
                <div class="col-md-3">
                    <label class="form-label">Unit</label>
                    <select class="form-select" id="filter_unit" name="unit_id">
                        <option value="">Semua Unit</option>
                        <?php foreach ($units as $unit): ?>
                            <?php $unitId = (int) ($unit['unit_id'] ?? 0); ?>
                            <option value="<?= esc((string) $unitId) ?>" <?= $unitId === $selectedUnitId ? 'selected' : '' ?>>
                                <?= esc((string) ($unit['unit_name'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <input type="hidden" id="filter_unit" name="unit_id" value="<?= esc((string) $selectedUnitId) ?>">
            <?php endif; ?>
        </form>

        <div class="table-responsive mb-4">
            <table class="table table-bordered table-striped" id="tul-summary-table">
                <thead>
                    <tr>
                        <th>Unit ID</th>
                        <th>Unit</th>
                        <th class="text-end">Jumlah Pelanggan</th>
                        <th class="text-end">Total Daya (kVA)</th>
                        <th class="text-end">Pemakaian kWh</th>
                        <th class="text-end">Biaya Beban (Rp)</th>
                        <th class="text-end">Rupiah kWh</th>
                        <th class="text-end">Total Rupiah</th>
                    </tr>
                </thead>
                <tbody id="tul-summary-body"></tbody>
                <tfoot id="tul-summary-foot"></tfoot>
            </table>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-bordered table-striped" id="tul-table" style="width:100%;">
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th>Unit</th>
                        <th>Tarif</th>
                        <th class="text-end">Pelanggan</th>
                        <th class="text-end">Daya</th>
                        <th class="text-end">Pemakaian Jumlah</th>
                        <th class="text-end">Pemakaian LWBP</th>
                        <th class="text-end">Pemakaian WBP</th>
                        <th class="text-end">Kelebihan Kvarh</th>
                        <th class="text-end">Biaya Beban</th>
                        <th class="text-end">Biaya KWH</th>
                        <th class="text-end">Biaya Kelebihan Kvarh</th>
                        <th class="text-end">Biaya TTLB</th>
                        <th class="text-end">Jumlah</th>
                        <th>Created By</th>
                        <th>Created At</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Data TUL</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= site_url('C_TUL/upload') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Periode</label>
                        <input type="month" class="form-control" name="periode" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">File Excel</label>
                        <input type="file" class="form-control" name="excel_file" accept=".xlsx,.xls" required>
                    </div>
                    <?php if ($userGroupId === 1): ?>
                        <div class="mb-3">
                            <label class="form-label">Unit</label>
                            <select class="form-select" name="unit_id" required>
                                <option value="">Pilih Unit</option>
                                <?php foreach ($units as $unit): ?>
                                    <option value="<?= esc((string) ((int) ($unit['unit_id'] ?? 0))) ?>">
                                        <?= esc((string) ($unit['unit_name'] ?? '')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="unit_id" value="<?= esc((string) $selectedUnitId) ?>">
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail TUL</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detailModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit TUL</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editTulForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" id="edit_id">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Tarif</label><input class="form-control" name="tarif" id="edit_tarif" required></div>
                        <div class="col-md-4"><label class="form-label">Pelanggan</label><input class="form-control" type="number" step="any" name="pelanggan" id="edit_pelanggan"></div>
                        <div class="col-md-4"><label class="form-label">Daya</label><input class="form-control" type="number" step="any" name="daya" id="edit_daya"></div>
                        <div class="col-md-4"><label class="form-label">Pemakaian Jumlah</label><input class="form-control" type="number" step="any" name="pemakaian_jumlah" id="edit_pemakaian_jumlah"></div>
                        <div class="col-md-4"><label class="form-label">Pemakaian LWBP</label><input class="form-control" type="number" step="any" name="pemakaian_lwbp" id="edit_pemakaian_lwbp"></div>
                        <div class="col-md-4"><label class="form-label">Pemakaian WBP</label><input class="form-control" type="number" step="any" name="pemakaian_wbp" id="edit_pemakaian_wbp"></div>
                        <div class="col-md-4"><label class="form-label">Kelebihan Kvarh</label><input class="form-control" type="number" step="any" name="pemakaian_kelebihan_kvarh" id="edit_pemakaian_kelebihan_kvarh"></div>
                        <div class="col-md-4"><label class="form-label">Biaya Beban</label><input class="form-control" type="number" step="any" name="biaya_beban" id="edit_biaya_beban"></div>
                        <div class="col-md-4"><label class="form-label">Biaya KWH</label><input class="form-control" type="number" step="any" name="biaya_kwh" id="edit_biaya_kwh"></div>
                        <div class="col-md-4"><label class="form-label">Biaya Kelebihan Kvarh</label><input class="form-control" type="number" step="any" name="biaya_kelebihan_kvarh" id="edit_biaya_kelebihan_kvarh"></div>
                        <div class="col-md-4"><label class="form-label">Biaya TTLB</label><input class="form-control" type="number" step="any" name="biaya_ttlb" id="edit_biaya_ttlb"></div>
                        <div class="col-md-4"><label class="form-label">Jumlah</label><input class="form-control" type="number" step="any" name="jumlah" id="edit_jumlah"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSaveEditTul">Simpan</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') ?>"></script>
<script>
$(function () {
    var csrfFieldName = '<?= esc(csrf_token()) ?>';
    var $filterForm = $('#tulFilterForm');

    function currentCsrf() {
        return $filterForm.find('input[name="' + csrfFieldName + '"]').val();
    }

    function applyCsrf(token) {
        if (!token) return;
        $('input[name="' + csrfFieldName + '"]').val(token);
    }

    function fmtNumber(val) {
        return Number(val || 0).toLocaleString('id-ID');
    }

    function loadSummary() {
        return $.ajax({
            url: '<?= site_url('C_TUL/summary-per-unit') ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                periode: $('#filter_periode').val(),
                [csrfFieldName]: currentCsrf()
            },
            success: function (rows, _status, xhr) {
                applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
                var $body = $('#tul-summary-body').empty();
                var $foot = $('#tul-summary-foot').empty();

                if (!Array.isArray(rows) || rows.length === 0) {
                    $body.append('<tr><td colspan="8" class="text-center">Tidak ada data.</td></tr>');
                    return;
                }

                rows.forEach(function (row) {
                    var html = '<tr>' +
                        '<td>' + (row.unit_id || '') + '</td>' +
                        '<td>' + (row.unit_name || '') + '</td>' +
                        '<td class="text-end">' + fmtNumber(row.total_pelanggan) + '</td>' +
                        '<td class="text-end">' + fmtNumber(row.total_daya) + '</td>' +
                        '<td class="text-end">' + fmtNumber(row.total_pemakaian_jumlah) + '</td>' +
                        '<td class="text-end">' + fmtNumber(row.total_biaya_beban) + '</td>' +
                        '<td class="text-end">' + fmtNumber(row.total_biaya_kwh) + '</td>' +
                        '<td class="text-end">' + fmtNumber(row.total_jumlah) + '</td>' +
                        '</tr>';

                    if (String(row.unit_id) === '54000') {
                        $foot.append(html.replace('<tr>', '<tr class="fw-semibold">'));
                    } else {
                        $body.append(html);
                    }
                });
            },
            error: function (xhr) {
                applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
            }
        });
    }

    var table = $('#tul-table').DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        scrollX: true,
        pageLength: 25,
        order: [[0, 'desc']],
        ajax: {
            url: '<?= site_url('C_TUL/data') ?>',
            type: 'POST',
            data: function (d) {
                d.periode = $('#filter_periode').val();
                d.unit_id = $('#filter_unit').val();
                d[csrfFieldName] = currentCsrf();
            },
            dataSrc: function (json) {
                return json.data || [];
            },
            complete: function (xhr) {
                applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
                // Refresh summary only after CSRF token is synchronized with latest response.
                loadSummary();
            }
        },
        columns: [
            { data: 'periode', render: function (d) { return d ? new Date(d).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' }) : ''; } },
            { data: 'unit_name' },
            { data: 'tarif' },
            { data: 'pelanggan', className: 'text-end', render: function (d) { return fmtNumber(d); } },
            { data: 'daya', className: 'text-end', render: function (d) { return fmtNumber(d); } },
            { data: 'pemakaian_jumlah', className: 'text-end', render: function (d) { return fmtNumber(d); } },
            { data: 'pemakaian_lwbp', className: 'text-end', render: function (d) { return fmtNumber(d); } },
            { data: 'pemakaian_wbp', className: 'text-end', render: function (d) { return fmtNumber(d); } },
            { data: 'pemakaian_kelebihan_kvarh', className: 'text-end', render: function (d) { return fmtNumber(d); } },
            { data: 'biaya_beban', className: 'text-end', render: function (d) { return fmtNumber(d); } },
            { data: 'biaya_kwh', className: 'text-end', render: function (d) { return fmtNumber(d); } },
            { data: 'biaya_kelebihan_kvarh', className: 'text-end', render: function (d) { return fmtNumber(d); } },
            { data: 'biaya_ttlb', className: 'text-end', render: function (d) { return fmtNumber(d); } },
            { data: 'jumlah', className: 'text-end', render: function (d) { return fmtNumber(d); } },
            { data: 'created_by' },
            { data: 'created_at' },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (row) {
                    return '<button class="btn btn-sm btn-outline-info me-1 btn-detail" data-id="' + row.id + '">Detail</button>' +
                        '<button class="btn btn-sm btn-outline-primary me-1 btn-edit" data-id="' + row.id + '">Edit</button>' +
                        '<button class="btn btn-sm btn-outline-danger btn-delete" data-id="' + row.id + '">Hapus</button>';
                }
            }
        ]
    });

    var isReloading = false;

    function reloadAll() {
        if (isReloading) {
            return;
        }

        isReloading = true;

        table.ajax.reload(function () {
            isReloading = false;
        }, false);
    }

    $('#filter_periode, #filter_unit').on('change', reloadAll);

    $(document).on('click', '.btn-detail', function () {
        var id = $(this).data('id');
        $.ajax({
            url: '<?= site_url('C_TUL/detail') ?>',
            type: 'POST',
            dataType: 'json',
            data: { id: id, [csrfFieldName]: currentCsrf() },
            success: function (row, _status, xhr) {
                applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
                var html = '<div class="row g-2">';
                Object.keys(row || {}).forEach(function (key) {
                    html += '<div class="col-md-6"><div class="small text-muted">' + key + '</div><div>' + row[key] + '</div></div>';
                });
                html += '</div>';
                $('#detailModalBody').html(html);
                bootstrap.Modal.getOrCreateInstance(document.getElementById('detailModal')).show();
            }
        });
    });

    $(document).on('click', '.btn-edit', function () {
        var id = $(this).data('id');
        $.ajax({
            url: '<?= site_url('C_TUL/detail') ?>',
            type: 'POST',
            dataType: 'json',
            data: { id: id, [csrfFieldName]: currentCsrf() },
            success: function (row, _status, xhr) {
                applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
                $('#edit_id').val(row.id || '');
                $('#edit_tarif').val(row.tarif || '');
                $('#edit_pelanggan').val(row.pelanggan || 0);
                $('#edit_daya').val(row.daya || 0);
                $('#edit_pemakaian_jumlah').val(row.pemakaian_jumlah || 0);
                $('#edit_pemakaian_lwbp').val(row.pemakaian_lwbp || 0);
                $('#edit_pemakaian_wbp').val(row.pemakaian_wbp || 0);
                $('#edit_pemakaian_kelebihan_kvarh').val(row.pemakaian_kelebihan_kvarh || 0);
                $('#edit_biaya_beban').val(row.biaya_beban || 0);
                $('#edit_biaya_kwh').val(row.biaya_kwh || 0);
                $('#edit_biaya_kelebihan_kvarh').val(row.biaya_kelebihan_kvarh || 0);
                $('#edit_biaya_ttlb').val(row.biaya_ttlb || 0);
                $('#edit_jumlah').val(row.jumlah || 0);
                bootstrap.Modal.getOrCreateInstance(document.getElementById('editModal')).show();
            }
        });
    });

    $('#btnSaveEditTul').on('click', function () {
        $.ajax({
            url: '<?= site_url('C_TUL/update') ?>',
            type: 'POST',
            dataType: 'json',
            data: $('#editTulForm').serialize(),
            success: function (_res, _status, xhr) {
                applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
                bootstrap.Modal.getOrCreateInstance(document.getElementById('editModal')).hide();
                reloadAll();
            },
            error: function (xhr) {
                applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
                Swal.fire('Gagal', 'Gagal mengubah data TUL.', 'error');
            }
        });
    });

    $(document).on('click', '.btn-delete', function () {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Hapus data TUL?',
            text: 'Data tidak dapat dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url: '<?= site_url('C_TUL/delete') ?>',
                type: 'POST',
                dataType: 'json',
                data: { id: id, [csrfFieldName]: currentCsrf() },
                success: function (_res, _status, xhr) {
                    applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
                    reloadAll();
                }
            });
        });
    });

});
</script>
<?= $this->endSection() ?>
