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
        <h5 class="mb-0">Data EMIN</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">Upload Excel</button>
    </div>
    <div class="card-body">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success" role="alert"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <form id="eminFilterForm" class="row g-3 mb-3" method="post" action="<?= site_url('C_Emin/data') ?>">
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
            <table class="table table-bordered table-striped" id="emin-summary-table">
                <thead>
                    <tr>
                        <th>Unit ID</th>
                        <th>Unit</th>
                        <th class="text-end">Lembar</th>
                        <th class="text-end">Pelanggan</th>
                        <th class="text-end">EMIN Awal</th>
                        <th class="text-end">kWh Rill</th>
                        <th class="text-end">EMIN</th>
                    </tr>
                </thead>
                <tbody id="emin-summary-body"></tbody>
                <tfoot id="emin-summary-foot"></tfoot>
            </table>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-bordered table-striped" id="emin-table" style="width:100%;">
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th>Periode Rekening</th>
                        <th>Tarif</th>
                        <th class="text-end">Lembar</th>
                        <th class="text-end">Pelanggan</th>
                        <th class="text-end">EMIN Awal</th>
                        <th class="text-end">kWh Rill</th>
                        <th class="text-end">EMIN</th>
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
                <h5 class="modal-title">Upload Data EMIN</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= site_url('C_Emin/upload') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Periode</label>
                        <input type="month" class="form-control" name="periode" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Periode Rekening</label>
                        <input type="month" class="form-control" name="periode_rekening" required>
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
                <h5 class="modal-title">Detail EMIN</h5>
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
                <h5 class="modal-title">Edit EMIN</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editEminForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" id="edit_id">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Tarif</label><input class="form-control" name="tarif" id="edit_tarif" required></div>
                        <div class="col-md-4"><label class="form-label">Lembar</label><input class="form-control" type="number" step="any" name="lembar" id="edit_lembar"></div>
                        <div class="col-md-4"><label class="form-label">Pelanggan</label><input class="form-control" type="number" step="any" name="pelanggan" id="edit_pelanggan"></div>
                        <div class="col-md-4"><label class="form-label">EMIN Awal</label><input class="form-control" type="number" step="any" name="emin_awal" id="edit_emin_awal"></div>
                        <div class="col-md-4"><label class="form-label">kWh Rill</label><input class="form-control" type="number" step="any" name="kwh_rill" id="edit_kwh_rill"></div>
                        <div class="col-md-4"><label class="form-label">EMIN</label><input class="form-control" type="number" step="any" name="emin" id="edit_emin"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSaveEditEmin">Simpan</button>
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
    var $filterForm = $('#eminFilterForm');

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

    function fmtMonth(val) {
        if (!val) return '';
        var d = new Date(val);
        if (isNaN(d.getTime())) return val;
        return d.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
    }

    function loadSummary() {
        return $.ajax({
            url: '<?= site_url('C_Emin/summary-per-unit') ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                periode: $('#filter_periode').val(),
                [csrfFieldName]: currentCsrf()
            },
            success: function (rows, _status, xhr) {
                applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
                var $body = $('#emin-summary-body').empty();
                var $foot = $('#emin-summary-foot').empty();

                if (!Array.isArray(rows) || rows.length === 0) {
                    $body.append('<tr><td colspan="7" class="text-center">Tidak ada data.</td></tr>');
                    return;
                }

                rows.forEach(function (row) {
                    var html = '<tr>' +
                        '<td>' + (row.unit_id || '') + '</td>' +
                        '<td>' + (row.unit_name || '') + '</td>' +
                        '<td class="text-end">' + fmtNumber(row.total_lembar) + '</td>' +
                        '<td class="text-end">' + fmtNumber(row.total_pelanggan) + '</td>' +
                        '<td class="text-end">' + fmtNumber(row.total_emin_awal) + '</td>' +
                        '<td class="text-end">' + fmtNumber(row.total_kwh_rill) + '</td>' +
                        '<td class="text-end">' + fmtNumber(row.total_emin) + '</td>' +
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

    var table = $('#emin-table').DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        scrollX: true,
        pageLength: 25,
        order: [[0, 'desc']],
        ajax: {
            url: '<?= site_url('C_Emin/data') ?>',
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
            { data: 'periode', render: function (d) { return fmtMonth(d); } },
            { data: 'periode_rekening', render: function (d) { return fmtMonth(d); } },
            { data: 'tarif' },
            { data: 'lembar', className: 'text-end', render: function (d) { return fmtNumber(d); } },
            { data: 'pelanggan', className: 'text-end', render: function (d) { return fmtNumber(d); } },
            { data: 'emin_awal', className: 'text-end', render: function (d) { return fmtNumber(d); } },
            { data: 'kwh_rill', className: 'text-end', render: function (d) { return fmtNumber(d); } },
            { data: 'emin', className: 'text-end', render: function (d) { return fmtNumber(d); } }
        ]
    });

    $('#filter_periode, #filter_unit').on('change', function () {
        table.ajax.reload();
    });

    $(document).on('click', '.btn-detail', function () {
        var id = $(this).data('id');
        $.ajax({
            url: '<?= site_url('C_Emin/detail') ?>',
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
            url: '<?= site_url('C_Emin/detail') ?>',
            type: 'POST',
            dataType: 'json',
            data: { id: id, [csrfFieldName]: currentCsrf() },
            success: function (row, _status, xhr) {
                applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
                $('#edit_id').val(row.id || '');
                $('#edit_tarif').val(row.tarif || '');
                $('#edit_lembar').val(row.lembar || 0);
                $('#edit_pelanggan').val(row.pelanggan || 0);
                $('#edit_emin_awal').val(row.emin_awal || 0);
                $('#edit_kwh_rill').val(row.kwh_rill || 0);
                $('#edit_emin').val(row.emin || 0);
                bootstrap.Modal.getOrCreateInstance(document.getElementById('editModal')).show();
            }
        });
    });

    $('#btnSaveEditEmin').on('click', function () {
        $.ajax({
            url: '<?= site_url('C_Emin/update') ?>',
            type: 'POST',
            dataType: 'json',
            data: $('#editEminForm').serialize(),
            success: function (_res, _status, xhr) {
                applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
                bootstrap.Modal.getOrCreateInstance(document.getElementById('editModal')).hide();
                reloadAll();
            },
            error: function (xhr) {
                applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
                Swal.fire('Gagal', 'Gagal mengubah data EMIN.', 'error');
            }
        });
    });

    $(document).on('click', '.btn-delete', function () {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Hapus data EMIN?',
            text: 'Data tidak dapat dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url: '<?= site_url('C_Emin/delete') ?>',
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
