<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') ?>">
<style>
#tableDataPemakaian thead th {
    text-align: center;
    vertical-align: middle;
    border: 1px solid #fff !important;
}
#tableDataPemakaian tbody td {
    white-space: nowrap;
}
#tableDataPemakaian tbody td:nth-child(2),
#tableDataPemakaian tbody td:nth-child(3),
#tableDataPemakaian tbody td:nth-child(4),
#tableDataPemakaian tbody td:nth-child(5),
#tableDataPemakaian tbody td:nth-child(6) {
    text-align: left;
}
#tableDataPemakaian tbody td:nth-child(n+7):nth-child(-n+20) {
    text-align: right;
}
#tableDataPemakaian tbody td:nth-child(1),
#tableDataPemakaian tbody td:nth-child(21),
#tableDataPemakaian tbody td:nth-child(22),
#tableDataPemakaian tbody td:nth-child(23),
#tableDataPemakaian tbody td:nth-child(24) {
    text-align: center;
}
</style>
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><?= esc($pageHeading ?? 'Data P2TL') ?></h5>
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
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalImportData">Import Data</button>
                <!-- <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalImportAnalisa">Upload Analisa</button> -->
            </div>
        </div>

        <div class="table-responsive">
            <table id="tableDataPemakaian" class="table table-sm table-striped w-100">
                <thead>
                    <tr>
                        <th rowspan="2">NO</th>
                        <th rowspan="2">NOAGENDA</th>
                        <th rowspan="2">IDPEL</th>
                        <th rowspan="2">NAMA</th>
                        <th rowspan="2">GOL</th>
                        <th rowspan="2">ALAMAT</th>
                        <th rowspan="2">DAYA</th>
                        <th rowspan="2">KWH</th>
                        <th colspan="3">TAGIHAN SUSULAN</th>
                        <th colspan="6">RUPIAH BIAYA LAIN-LAIN</th>
                        <th colspan="3">RUPIAH</th>
                        <th colspan="4">PENETAPAN</th>
                    </tr>
                    <tr>
                        <th>BEBAN</th>
                        <th>KWH</th>
                        <th>TS</th>
                        <th>MATERAI</th>
                        <th>SEGEL</th>
                        <th>MATERIA</th>
                        <th>RPPPJ</th>
                        <th>RPUJL</th>
                        <th>RPPPN</th>
                        <th>TOTAL</th>
                        <th>TUNAI</th>
                        <th>ANGSURAN</th>
                        <th>TANGGAL REGISTER</th>
                        <th>NOMOR REGISTER</th>
                        <th>TANGGAL SPH</th>
                        <th>NOMOR SPH</th>
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
                            <div class="mt-2">
                                <a href="<?= site_url('C_P2TL/downloadImportAnalisaTemplate') ?>" class="btn btn-outline-secondary btn-sm">
                                    Download Template Import Analisa (.xlsx)
                                </a>
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

    function applyCsrf(token) {
        if (!token) {
            return;
        }

        csrfToken = token;
        $('input[name="' + csrfFieldName + '"]').val(token);
    }

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
            { data: 15, defaultContent: '' },
            { data: 16, defaultContent: '' },
            { data: 17, defaultContent: '' },
            { data: 18, defaultContent: '' },
            { data: 19, defaultContent: '' },
            { data: 20, defaultContent: '' },
            { data: 21, defaultContent: '' },
            { data: 22, defaultContent: '' },
            { data: 23, defaultContent: '' }
        ],
        columnDefs: [
            { className: 'text-center', targets: [0, 20, 21, 22, 23] },
            { className: 'text-end', targets: [6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19] }
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
                applyCsrf(fresh);
                return Array.isArray(json.data) ? json.data : [];
            },
            beforeSend: function () {
                Swal.fire({ title: 'Mohon Tunggu', html: 'Mengambil Data', allowOutsideClick: false, showConfirmButton: false, didOpen: function(){ Swal.showLoading(); } });
            },
            complete: function (xhr) {
                var fresh = xhr.getResponseHeader('X-CSRF-TOKEN');
                applyCsrf(fresh);
                Swal.close();
            },
            error: function (xhr) {
                applyCsrf(xhr.getResponseHeader('X-CSRF-TOKEN'));
                Swal.close();
                Swal.fire('Error', 'Gagal mengambil data (' + (xhr && xhr.status ? xhr.status : 'unknown') + ').', 'error');
            }
        },
        order: [[20, 'desc'], [1, 'desc']],
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

    $('#formImportData').on('submit', function () {
        applyCsrf(csrfToken);
        Swal.fire({ title: 'Mohon Tunggu', html: 'Proses import berlangsung', allowOutsideClick: false, showConfirmButton: false, didOpen: function(){ Swal.showLoading(); } });
    });
})();
</script>
<?= $this->endSection() ?>
