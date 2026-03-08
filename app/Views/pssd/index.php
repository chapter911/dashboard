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
        <h5 class="mb-0">Data PSSD</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">Upload Excel</button>
    </div>
    <div class="card-body">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success" role="alert"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <form id="pssdFilterForm" class="row g-3 mb-3" method="post" action="<?= site_url('C_PSSD/data') ?>">
            <?= csrf_field() ?>
            <div class="col-md-3">
                <label class="form-label">Periode</label>
                <input type="month" class="form-control" id="filter_periode" name="periode" value="<?= esc($currentPeriod) ?>" onclick="this.showPicker && this.showPicker()">
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
            <table class="table table-bordered table-striped" id="pssd-summary-table">
                <thead>
                    <tr>
                        <th>Unit ID</th>
                        <th>Unit</th>
                        <th class="text-end">Total Jumlah</th>
                        <th class="text-end">Total kWh</th>
                    </tr>
                </thead>
                <tbody id="pssd-summary-body"></tbody>
                <tfoot id="pssd-summary-foot"></tfoot>
            </table>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-bordered table-striped" id="pssd-table" style="width:100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Periode</th>
                        <th>Unit</th>
                        <th>Nama Sheet</th>
                        <th>Jenis Peralatan</th>
                        <th class="text-end">Daya</th>
                        <th class="text-end">Jam Nyala</th>
                        <th class="text-end">Jumlah</th>
                        <th class="text-end">Total kWh</th>
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
                <h5 class="modal-title">Upload Data PSSD</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= site_url('C_PSSD/upload') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Periode</label>
                        <input type="month" class="form-control" name="periode" onclick="this.showPicker && this.showPicker()" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">File Excel</label>
                        <input type="file" class="form-control" name="excel_file" accept=".xlsx,.xls" required>
                    </div>
                    <?php if ($userGroupId === 1): ?>
                        <div class="mb-3">
                            <label class="form-label">Unit (Opsional)</label>
                            <select class="form-select" name="unit_id">
                                <option value="">Semua Unit Sesuai Sheet</option>
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
                <h5 class="modal-title">Detail PSSD</h5>
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
                <h5 class="modal-title">Edit PSSD</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editPssdForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" id="edit_id">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Nama Sheet</label><input class="form-control" name="nama_sheet" id="edit_nama_sheet"></div>
                        <div class="col-md-6"><label class="form-label">Jenis Peralatan</label><input class="form-control" name="jenis_peralatan" id="edit_jenis_peralatan"></div>
                        <div class="col-md-3"><label class="form-label">Daya</label><input class="form-control" type="number" step="any" name="daya" id="edit_daya"></div>
                        <div class="col-md-3"><label class="form-label">Jam Nyala</label><input class="form-control" type="number" step="any" name="jam_nyala" id="edit_jam_nyala"></div>
                        <div class="col-md-3"><label class="form-label">Jumlah</label><input class="form-control" type="number" step="any" name="jumlah" id="edit_jumlah"></div>
                        <div class="col-md-3"><label class="form-label">Total kWh</label><input class="form-control" type="number" step="any" name="total_kwh" id="edit_total_kwh"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSaveEditPssd">Simpan</button>
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
    var $filterForm = $('#pssdFilterForm');

    function currentCsrf() {
        return $filterForm.find('input[name="' + csrfFieldName + '"]').val();
    }

    function applyCsrf(token) {
        if (!token) return;
        $('input[name="' + csrfFieldName + '"]').val(token);
    }

    function fmtNumber(val) {
        return Number(val || 0).toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }

    function fmtMonth(val) {
        if (!val) return '';
        var d = new Date(val);
        if (isNaN(d.getTime())) return val;
        return d.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
    }

    function loadSummary() {
        return $.ajax({
            url: '<?= site_url('C_PSSD/summary-per-unit') ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                periode: $('#filter_periode').val(),
                unit_id: $('#filter_unit').val(),
                [csrfFieldName]: currentCsrf()
            },
            success: function (rows, _status, xhr) {
                applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
                var $body = $('#pssd-summary-body').empty();
                var $foot = $('#pssd-summary-foot').empty();

                if (!Array.isArray(rows) || rows.length === 0) {
                    $body.append('<tr><td colspan="4" class="text-center">Tidak ada data.</td></tr>');
                    return;
                }

                var totalJumlah = 0;
                var totalKwh = 0;

                rows.forEach(function (row) {
                    var jumlah = Number(row.total_jumlah || 0);
                    var kwh = Number(row.total_kwh || 0);

                    totalJumlah += jumlah;
                    totalKwh += kwh;

                    if (String(row.unit_id) === '54000') {
                        return;
                    }

                    $body.append(
                        '<tr>' +
                            '<td>' + (row.unit_id || '') + '</td>' +
                            '<td>' + (row.unit_name || '') + '</td>' +
                            '<td class="text-end">' + fmtNumber(jumlah) + '</td>' +
                            '<td class="text-end">' + fmtNumber(kwh) + '</td>' +
                        '</tr>'
                    );
                });

                $foot.append(
                    '<tr class="fw-semibold">' +
                        '<td>54000</td>' +
                        '<td>UID JAYA</td>' +
                        '<td class="text-end">' + fmtNumber(totalJumlah) + '</td>' +
                        '<td class="text-end">' + fmtNumber(totalKwh) + '</td>' +
                    '</tr>'
                );
            },
            error: function (xhr) {
                applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
            }
        });
    }

    var table = $('#pssd-table').DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        scrollX: true,
        pageLength: 25,
        order: [[0, 'asc']],
        ajax: {
            url: '<?= site_url('C_PSSD/data') ?>',
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
                loadSummary();
            }
        },
        columns: [
            { data: 'id' },
            { data: 'periode', render: function (d) { return fmtMonth(d); } },
            { data: 'unit_name' },
            { data: 'nama_sheet' },
            { data: 'jenis_peralatan' },
            { data: 'daya', className: 'text-end', render: function (d) { return fmtNumber(d); } },
            { data: 'jam_nyala', className: 'text-end', render: function (d) { return fmtNumber(d); } },
            { data: 'jumlah', className: 'text-end', render: function (d) { return fmtNumber(d); } },
            { data: 'total_kwh', className: 'text-end', render: function (d) { return fmtNumber(d); } },
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
            url: '<?= site_url('C_PSSD/detail') ?>',
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
            url: '<?= site_url('C_PSSD/detail') ?>',
            type: 'POST',
            dataType: 'json',
            data: { id: id, [csrfFieldName]: currentCsrf() },
            success: function (row, _status, xhr) {
                applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
                $('#edit_id').val(row.id || '');
                $('#edit_nama_sheet').val(row.nama_sheet || '');
                $('#edit_jenis_peralatan').val(row.jenis_peralatan || '');
                $('#edit_daya').val(row.daya || 0);
                $('#edit_jam_nyala').val(row.jam_nyala || 0);
                $('#edit_jumlah').val(row.jumlah || 0);
                $('#edit_total_kwh').val(row.total_kwh || 0);
                bootstrap.Modal.getOrCreateInstance(document.getElementById('editModal')).show();
            }
        });
    });

    $('#btnSaveEditPssd').on('click', function () {
        $.ajax({
            url: '<?= site_url('C_PSSD/update') ?>',
            type: 'POST',
            dataType: 'json',
            data: $('#editPssdForm').serialize(),
            success: function (_res, _status, xhr) {
                applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
                bootstrap.Modal.getOrCreateInstance(document.getElementById('editModal')).hide();
                reloadAll();
            },
            error: function (xhr) {
                applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
                Swal.fire('Gagal', 'Gagal mengubah data PSSD.', 'error');
            }
        });
    });

    $(document).on('click', '.btn-delete', function () {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Hapus data PSSD?',
            text: 'Data tidak dapat dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url: '<?= site_url('C_PSSD/delete') ?>',
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
