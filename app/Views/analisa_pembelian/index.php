<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$currentPeriod = (string) ($currentPeriod ?? date('Y-m'));

$unitColumns = [
    'menteng' => 'Menteng',
    'bandengan' => 'Bandengan',
    'cempaka_putih' => 'Cempaka Putih',
    'jati_negara' => 'Jati Negara',
    'pondok_kopi' => 'Pondok Kopi',
    'tanjung_priok' => 'Tanjung Priok',
    'marunda' => 'Marunda',
    'bulungan' => 'Bulungan',
    'bintaro' => 'Bintaro',
    'kebun_jeruk' => 'Kebun Jeruk',
    'ciputat' => 'Ciputat',
    'kramat_jati' => 'Kramat Jati',
    'lenteng_agung' => 'Lenteng Agung',
    'pondok_gede' => 'Pondok Gede',
    'ciracas' => 'Ciracas',
    'cengkareng' => 'Cengkareng',
    'uid' => 'Distribusi',
];
?>

<link rel="stylesheet" href="<?= base_url('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') ?>">

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Data Analisa Pembelian</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">Upload Excel</button>
    </div>
    <div class="card-body">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success" role="alert"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <form id="analisaFilterForm" class="row g-3 mb-3" method="post" action="<?= site_url('C_AnalisaPembelian/data') ?>">
            <?= csrf_field() ?>
            <div class="col-md-3">
                <label class="form-label">Periode</label>
                <input type="month" class="form-control" id="filter_periode" name="periode" value="<?= esc($currentPeriod) ?>" onclick="this.showPicker && this.showPicker()">
            </div>
        </form>

        <div class="table-responsive mb-4">
            <table class="table table-bordered table-striped" id="analisa-terima-table" style="width:100%;">
                <thead>
                    <tr>
                        <th>Urutan</th>
                        <th>Penerimaan Dari</th>
                        <?php foreach ($unitColumns as $label): ?>
                            <th class="text-end"><?= esc($label) ?></th>
                        <?php endforeach; ?>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <div class="table-responsive mb-4">
            <table class="table table-bordered table-striped" id="analisa-kirim-table" style="width:100%;">
                <thead>
                    <tr>
                        <th>Urutan</th>
                        <th>Pengiriman Ke</th>
                        <?php foreach ($unitColumns as $label): ?>
                            <th class="text-end"><?= esc($label) ?></th>
                        <?php endforeach; ?>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="analisa-netto-table" style="width:100%;">
                <thead>
                    <tr>
                        <th>Urutan</th>
                        <th>Terima Netto</th>
                        <?php foreach ($unitColumns as $label): ?>
                            <th class="text-end"><?= esc($label) ?></th>
                        <?php endforeach; ?>
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
                <h5 class="modal-title">Upload Data Analisa Pembelian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= site_url('C_AnalisaPembelian/upload') ?>" method="post" enctype="multipart/form-data">
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
                <h5 class="modal-title">Detail Analisa Pembelian</h5>
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
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Analisa Pembelian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editAnalisaForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" id="edit_id">
                    <div class="row g-3" id="editAnalisaFields"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSaveEditAnalisa">Simpan</button>
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
    var $filterForm = $('#analisaFilterForm');
    var unitFields = [<?= implode(',', array_map(static fn(string $k): string => "'" . $k . "'", array_keys($unitColumns))) ?>];

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

    function makeColumns() {
        var cols = [
            { data: 'urutan' },
            { data: 'hubungan' }
        ];

        unitFields.forEach(function (field) {
            cols.push({
                data: field,
                className: 'text-end',
                render: function (d) { return fmtNumber(d); }
            });
        });

        cols.push({
            data: null,
            orderable: false,
            searchable: false,
            render: function (row) {
                return '<button class="btn btn-sm btn-outline-info me-1 btn-detail" data-id="' + row.id + '">Detail</button>' +
                    '<button class="btn btn-sm btn-outline-primary me-1 btn-edit" data-id="' + row.id + '">Edit</button>' +
                    '<button class="btn btn-sm btn-outline-danger btn-delete" data-id="' + row.id + '">Hapus</button>';
            }
        });

        return cols;
    }

    function buildTable(selector, metode) {
        return $(selector).DataTable({
            processing: true,
            serverSide: false,
            searching: false,
            scrollX: true,
            pageLength: -1,
            lengthMenu: [[-1], ['All']],
            ordering: false,
            data: [],
            columns: makeColumns()
        });
    }

    var terimaTable = buildTable('#analisa-terima-table', 'penerimaan');
    var kirimTable = buildTable('#analisa-kirim-table', 'pengiriman');
    var nettoTable = buildTable('#analisa-netto-table', 'netto');

    function loadTableData(table, metode) {
        return $.ajax({
            url: '<?= site_url('C_AnalisaPembelian/data') ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                draw: 1,
                start: 0,
                length: -1,
                periode: $('#filter_periode').val(),
                metode: metode,
                [csrfFieldName]: currentCsrf()
            },
            success: function (json, _status, xhr) {
                applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
                table.clear();
                table.rows.add(json.data || []);
                table.draw(false);
            },
            error: function (xhr) {
                applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
                throw xhr;
            }
        });
    }

    var isReloading = false;

    function reloadAll() {
        if (isReloading) {
            return;
        }

        isReloading = true;
        Swal.fire({
            title: 'Mohon Tunggu',
            html: 'Mengambil data Analisa Pembelian...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: function () {
                Swal.showLoading();
            }
        });

        loadTableData(terimaTable, 'penerimaan')
            .then(function () {
                return loadTableData(kirimTable, 'pengiriman');
            })
            .then(function () {
                return loadTableData(nettoTable, 'netto');
            })
            .catch(function () {
                Swal.fire('Gagal', 'Gagal mengambil data Analisa Pembelian.', 'error');
            })
            .always(function () {
                isReloading = false;
                Swal.close();
            });
    }

    function openDetail(id) {
        $.ajax({
            url: '<?= site_url('C_AnalisaPembelian/detail') ?>',
            type: 'POST',
            dataType: 'json',
            data: { id: id, [csrfFieldName]: currentCsrf() },
            success: function (row, _status, xhr) {
                applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
                var html = '<div class="row g-2">';
                Object.keys(row || {}).forEach(function (key) {
                    html += '<div class="col-md-4"><div class="small text-muted">' + key + '</div><div>' + row[key] + '</div></div>';
                });
                html += '</div>';
                $('#detailModalBody').html(html);
                bootstrap.Modal.getOrCreateInstance(document.getElementById('detailModal')).show();
            }
        });
    }

    function openEdit(id) {
        $.ajax({
            url: '<?= site_url('C_AnalisaPembelian/detail') ?>',
            type: 'POST',
            dataType: 'json',
            data: { id: id, [csrfFieldName]: currentCsrf() },
            success: function (row, _status, xhr) {
                applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);

                var editable = ['urutan', 'hubungan', 'unit_id'].concat(unitFields);
                var labels = {
                    urutan: 'Urutan',
                    hubungan: 'Hubungan',
                    unit_id: 'Unit ID',
                    menteng: 'Menteng',
                    bandengan: 'Bandengan',
                    cempaka_putih: 'Cempaka Putih',
                    jati_negara: 'Jati Negara',
                    pondok_kopi: 'Pondok Kopi',
                    tanjung_priok: 'Tanjung Priok',
                    marunda: 'Marunda',
                    bulungan: 'Bulungan',
                    bintaro: 'Bintaro',
                    kebun_jeruk: 'Kebun Jeruk',
                    ciputat: 'Ciputat',
                    kramat_jati: 'Kramat Jati',
                    lenteng_agung: 'Lenteng Agung',
                    pondok_gede: 'Pondok Gede',
                    ciracas: 'Ciracas',
                    cengkareng: 'Cengkareng',
                    uid: 'Distribusi'
                };

                $('#edit_id').val(row.id || '');

                var html = '';
                editable.forEach(function (name) {
                    var type = (name === 'hubungan' || name === 'urutan') ? 'text' : 'number';
                    var step = (type === 'number') ? ' step="1"' : '';
                    var col = (name === 'hubungan') ? 'col-md-6' : 'col-md-3';

                    html += '<div class="' + col + '">' +
                        '<label class="form-label">' + (labels[name] || name) + '</label>' +
                        '<input class="form-control" type="' + type + '"' + step + ' name="' + name + '" value="' + (row[name] ?? '') + '">' +
                        '</div>';
                });

                $('#editAnalisaFields').html(html);
                bootstrap.Modal.getOrCreateInstance(document.getElementById('editModal')).show();
            }
        });
    }

    $(document).on('click', '.btn-detail', function () {
        openDetail($(this).data('id'));
    });

    $(document).on('click', '.btn-edit', function () {
        openEdit($(this).data('id'));
    });

    $(document).on('click', '.btn-delete', function () {
        var id = $(this).data('id');

        Swal.fire({
            title: 'Hapus data Analisa Pembelian?',
            text: 'Data tidak dapat dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url: '<?= site_url('C_AnalisaPembelian/delete') ?>',
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

    $('#btnSaveEditAnalisa').on('click', function () {
        $.ajax({
            url: '<?= site_url('C_AnalisaPembelian/update') ?>',
            type: 'POST',
            dataType: 'json',
            data: $('#editAnalisaForm').serialize(),
            success: function (_res, _status, xhr) {
                applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
                bootstrap.Modal.getOrCreateInstance(document.getElementById('editModal')).hide();
                reloadAll();
            },
            error: function (xhr) {
                applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
                Swal.fire('Gagal', 'Gagal mengubah data Analisa Pembelian.', 'error');
            }
        });
    });

    $('#filter_periode').on('change', reloadAll);

    reloadAll();
});
</script>
<?= $this->endSection() ?>
