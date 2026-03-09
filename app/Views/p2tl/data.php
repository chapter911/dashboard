<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') ?>">
<style>
#tableDataPemakaian thead th {
    text-align: center;
    vertical-align: middle;
    border: 1px solid #fff !important;
}
#tableDataPemakaian tbody td,
#tableDataPemakaian tbody td:nth-child(n+3) {
    text-align: right;
}
#tableDataPemakaian tbody td:nth-child(1),
#tableDataPemakaian tbody td:nth-child(2) {
    text-align: left;
}
</style>
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><?= esc($pageHeading ?? 'Data Pemakaian Analisa') ?></h5>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label">Tahun</label>
                <select class="form-select" id="tahun">
                    <?php for ($y = (int) date('Y'); $y >= 2020; $y--): ?>
                        <option value="<?= $y ?>" <?= $y === (int) ($currentYear ?? date('Y')) ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Unit</label>
                <select class="form-select" id="unit">
                    <option value="*">SEMUA UNIT</option>
                    <?php foreach (($units ?? []) as $u): ?>
                        <option value="<?= (int) ($u['unit_id'] ?? 0) ?>"><?= esc($u['unit_name'] ?? '-') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">IDPEL</label>
                <input type="text" class="form-control" id="idpel" placeholder="Cari IDPEL...">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-secondary w-100" id="btnFilter">Filter</button>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalImportAnalisa">Upload Analisa</button>
            </div>
        </div>

        <div class="table-responsive">
            <table id="tableDataPemakaian" class="table table-sm table-striped w-100">
                <thead>
                    <tr>
                        <th>IDPEL</th>
                        <th>TARIF</th>
                        <th>DAYA</th>
                        <th>TAHUN</th>
                        <th>JAN</th>
                        <th>FEB</th>
                        <th>MAR</th>
                        <th>APR</th>
                        <th>MEI</th>
                        <th>JUN</th>
                        <th>JUL</th>
                        <th>AGT</th>
                        <th>SEP</th>
                        <th>OKT</th>
                        <th>NOV</th>
                        <th>DES</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalImportData" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Data P2TL</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="<?= site_url('C_P2TL/importData') ?>" enctype="multipart/form-data" id="formImportData">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <label class="form-label">File (xlsx/xls)</label>
                    <input type="file" class="form-control" name="file_import" accept=".xlsx,.xls" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalImportAnalisa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Analisa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="<?= site_url('C_P2TL/importAnalisa') ?>" enctype="multipart/form-data" id="formImportAnalisa">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tahun</label>
                            <select class="form-select" name="tahun" required>
                                <?php for ($y = (int) date('Y'); $y >= 2020; $y--): ?>
                                    <option value="<?= $y ?>" <?= $y === (int) ($currentYear ?? date('Y')) ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bulan</label>
                            <select class="form-select" name="bulan" required>
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?= $m ?>" <?= $m === (int) date('n') ? 'selected' : '' ?>><?= $m ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Unit</label>
                            <select class="form-select" name="unit_id">
                                <option value="">Ikuti Unit User</option>
                                <?php foreach (($units ?? []) as $u): ?>
                                    <option value="<?= (int) ($u['unit_id'] ?? 0) ?>"><?= esc($u['unit_name'] ?? '-') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">File (xlsx/xls)</label>
                            <input type="file" class="form-control" name="file_import" accept=".xlsx,.xls" required>
                            <div class="form-text mt-2">
                                Download format: <a href="<?= site_url('C_P2TL/downloadImportAnalisaTemplate') ?>">Template Import Analisa (.xlsx)</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') ?>"></script>
<script>
(function () {
    var csrfFieldName = '<?= esc(csrf_token()) ?>';
    var csrfToken = '<?= esc(csrf_hash()) ?>';

    if (!$.fn || !$.fn.DataTable) {
        console.error('DataTables plugin belum termuat pada halaman dataP2TL.');
        return;
    }

    var table = $('#tableDataPemakaian').DataTable({
        destroy: true,
        autoWidth: false,
        processing: true,
        serverSide: true,
        searching: true,
        paging: true,
        scrollX: true,
        columns: [
            { data: 0, defaultContent: '' },
            { data: 1, defaultContent: '' },
            { data: 2, defaultContent: '' },
            { data: 3, defaultContent: '' },
            { data: 4, defaultContent: '' },
            { data: 5, defaultContent: '' },
            { data: 6, defaultContent: '' },
            { data: 7, defaultContent: '' },
            { data: 8, defaultContent: '' },
            { data: 9, defaultContent: '' },
            { data: 10, defaultContent: '' },
            { data: 11, defaultContent: '' },
            { data: 12, defaultContent: '' },
            { data: 13, defaultContent: '' },
            { data: 14, defaultContent: '' },
            { data: 15, defaultContent: '' }
        ],
        columnDefs: [
            { className: 'text-end', targets: [2, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15] },
            { className: 'text-center', targets: [3] }
        ],
        ajax: {
            url: '<?= site_url('C_P2TL/ajaxDataPemakaian') ?>',
            type: 'POST',
            data: function (d) {
                d.tahun = $('#tahun').val();
                d.unit = $('#unit').val();
                d.idpel = $('#idpel').val();
                d[csrfFieldName] = csrfToken;
            },
            dataSrc: function (json) {
                var fresh = null;
                try {
                    fresh = json && json[csrfFieldName] ? json[csrfFieldName] : null;
                } catch (e) {
                    fresh = null;
                }
                if (fresh) {
                    csrfToken = fresh;
                }
                return Array.isArray(json.data) ? json.data : [];
            },
            beforeSend: function () {
                Swal.fire({ title: 'Mohon Tunggu', html: 'Mengambil Data', allowOutsideClick: false, showConfirmButton: false, didOpen: function(){ Swal.showLoading(); } });
            },
            complete: function (xhr) {
                var fresh = xhr.getResponseHeader('X-CSRF-TOKEN');
                if (fresh) {
                    csrfToken = fresh;
                }
                Swal.close();
            },
            error: function (xhr) {
                Swal.close();
                Swal.fire('Error', 'Gagal mengambil data (' + (xhr && xhr.status ? xhr.status : 'unknown') + ').', 'error');
            }
        },
        order: [[0, 'asc']],
        pageLength: 10
    });

    window.getData = function () {
        table.ajax.reload();
    };

    $('#tahun, #unit').on('change', function () { window.getData(); });
    $('#idpel').on('keyup', function (e) {
        if (e.key === 'Enter') {
            window.getData();
        }
    });
    $('#btnFilter').on('click', function () { window.getData(); });

    $('#formImportData, #formImportAnalisa').on('submit', function () {
        Swal.fire({ title: 'Mohon Tunggu', html: 'Proses import berlangsung', allowOutsideClick: false, showConfirmButton: false, didOpen: function(){ Swal.showLoading(); } });
    });
})();
</script>
<?= $this->endSection() ?>
