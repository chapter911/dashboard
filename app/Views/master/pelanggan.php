<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$units = is_array($units ?? null) ? $units : [];
$filters = is_array($filters ?? null) ? $filters : [];
$bulanOptions = is_array($bulanOptions ?? null) ? $bulanOptions : [];
$maxUploadMb = (int) ($maxUploadMb ?? 64);
$resumeState = is_array($resumeState ?? null) ? $resumeState : null;
$resumeToken = (string) ($resumeState['token'] ?? '');
$resumeTotalRows = (int) ($resumeState['total_rows'] ?? 0);
$resumeNextRow = (int) ($resumeState['next_row'] ?? 2);
$resumeInsertedRows = (int) ($resumeState['inserted_rows'] ?? 0);
$resumeFailedAt = (int) ($resumeState['failed_at_row'] ?? 0);
$resumeUpdatedAtRaw = (string) ($resumeState['updated_at'] ?? '');
$resumeUpdatedAtTs = $resumeUpdatedAtRaw !== '' ? strtotime($resumeUpdatedAtRaw) : false;
$resumeUpdatedAtText = $resumeUpdatedAtTs ? date('d-m-Y H:i:s', $resumeUpdatedAtTs) : '-';
$resumeDoneRows = max(0, $resumeNextRow - 2);
$resumeDataRows = max(1, $resumeTotalRows - 1);
$resumePercent = min(100, (int) floor(($resumeDoneRows / $resumeDataRows) * 100));
?>

<link rel="stylesheet" href="<?= base_url('assets/vendor/libs/select2/select2.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') ?>">
<style>
    .text-nowrap { white-space: nowrap; }
</style>

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
    <div class="card-header"><h5 class="mb-0">Import Data Induk Langganan</h5></div>
    <div class="card-body">
        <?php if ($resumeToken !== ''): ?>
            <div class="alert alert-warning" role="alert">
                <div class="mb-2"><strong>Import sebelumnya belum selesai.</strong></div>
                <div class="small mb-2">
                    Progress: <?= esc((string) $resumePercent) ?>% (<?= esc((string) $resumeDoneRows) ?> / <?= esc((string) $resumeDataRows) ?> baris),
                    tersimpan: <?= esc((string) $resumeInsertedRows) ?> baris
                    <?php if ($resumeFailedAt > 0): ?>
                        , gagal sekitar baris <?= esc((string) $resumeFailedAt) ?>
                    <?php endif; ?>.
                </div>
                <div class="small text-muted mb-2">
                    Update terakhir: <?= esc($resumeUpdatedAtText) ?>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <form method="post" action="<?= site_url('C_Master/Pelanggan/resume') ?>" class="d-inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="resume_token" value="<?= esc($resumeToken) ?>">
                        <button class="btn btn-warning btn-sm" type="submit">Lanjutkan Import</button>
                    </form>
                    <form method="post" action="<?= site_url('C_Master/Pelanggan/resume/cancel') ?>" class="d-inline" onsubmit="return confirm('Batalkan resume import? Progress sementara akan dihapus.');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="resume_token" value="<?= esc($resumeToken) ?>">
                        <button class="btn btn-outline-danger btn-sm" type="submit">Batalkan Resume</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
        <form method="post" action="<?= site_url('C_Master/Pelanggan/import') ?>" enctype="multipart/form-data" class="row g-3">
            <?= csrf_field() ?>
            <div class="col-md-9">
                <label class="form-label">File Excel (.xlsx/.xls)</label>
                <input class="form-control" type="file" name="excel_file" accept=".xlsx,.xls" required>
                <small class="text-muted">Batas upload server saat ini: <?= esc((string) $maxUploadMb) ?> MB.</small>
            </div>
            <div class="col-md-3">
                <label class="form-label d-block invisible">Aksi</label>
                <button class="btn btn-primary w-100" type="submit">Import</button>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="post" action="<?= site_url('C_Master/Pelanggan/data') ?>" class="row g-3" id="pelangganFilterForm">
            <?= csrf_field() ?>
            <div class="col-md-3">
                <label class="form-label">Unit</label>
                <select class="form-select select2" name="unit" id="filter_unit">
                    <option value="*">Semua Unit</option>
                    <?php foreach ($units as $unit): ?>
                        <?php $uid = (string) ($unit['unit_id'] ?? ''); ?>
                        <option value="<?= esc($uid) ?>" <?= ($filters['unit'] ?? '*') === $uid ? 'selected' : '' ?>><?= esc($uid . ' - ' . (string) ($unit['unit_name'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Bulan</label>
                <select class="form-select select2" name="bulan" id="filter_bulan">
                    <option value="*">Semua Bulan</option>
                    <?php foreach ($bulanOptions as $b): ?>
                        <?php $val = (string) ($b['v_bulan_rekap'] ?? ''); if ($val === '') continue; ?>
                        <option value="<?= esc($val) ?>" <?= ($filters['bulan'] ?? '*') === $val ? 'selected' : '' ?>><?= esc($val) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">IDPEL</label>
                <input class="form-control" type="text" name="idpel" id="filter_idpel" value="<?= esc((string) ($filters['idpel'] ?? '')) ?>" placeholder="Cari IDPEL" autocomplete="off">
            </div>
            <div class="col-md-3">
                <label class="form-label">Filter</label>
                <button class="btn btn-primary w-100" type="button" id="btn-filter-pelanggan">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Data Induk Langganan</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive text-nowrap">
            <table class="table table-bordered table-striped" id="pelangganTable">
                <thead>
                    <tr>
                        <th>V_BULAN_REKAP</th><th>UNITUP</th><th>IDPEL</th><th>NAMA</th><th>NAMAPNJ</th><th>TARIF</th><th>DAYA</th>
                        <th>KDPT_2</th><th>THBLMUT</th><th>JENIS_MK</th><th>JENISLAYANAN</th><th>FRT</th><th>KOGOL</th><th>FKMKWH</th>
                        <th>NOMOR_METER_KWH</th><th>TANGGAL_PASANG_RUBAH_APP</th><th>MERK_METER_KWH</th><th>TYPE_METER_KWH</th>
                        <th>TAHUN_TERA_METER_KWH</th><th>TAHUN_BUAT_METER_KWH</th><th>NOMOR_GARDU</th><th>NOMOR_JURUSAN_TIANG</th>
                        <th>NAMA_GARDU</th><th>KAPASITAS_TRAFO</th><th>NOMOR_METER_PREPAID</th><th>PRODUCT</th><th>KOORDINAT_X</th>
                        <th>KOORDINAT_Y</th><th>KDAM</th><th>KDPEMBMETER</th><th>KET_KDPEMBMETER</th><th>STATUS_DIL</th><th>KRN</th><th>VKRN</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/vendor/libs/select2/select2.js') ?>"></script>
<script src="<?= base_url('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') ?>"></script>
<script>
$(function () {
    var $form = $('#pelangganFilterForm');
    var swalActive = false;
    var csrfName = '<?= esc(csrf_token()) ?>';

    $('.select2').select2({ width: '100%' });

    var showLoading = function () {
        if (typeof Swal === 'undefined') return;
        swalActive = true;
        Swal.fire({
            title: 'Mohon Tunggu',
            html: 'Mengambil data pelanggan...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: function () { Swal.showLoading(); }
        });
    };

    var hideLoading = function () {
        if (!swalActive || typeof Swal === 'undefined') return;
        Swal.close();
        swalActive = false;
    };

    var syncCsrfToken = function (tokenValue) {
        if (!tokenValue) return;
        $('input[name="' + csrfName + '"]').val(tokenValue);
    };

    var columns = [
        'v_bulan_rekap','unitup','idpel','nama','namapnj','tarif','daya','kdpt_2','thblmut','jenis_mk','jenislayanan','frt','kogol','fkmkwh',
        'nomor_meter_kwh','tanggal_pasang_rubah_app','merk_meter_kwh','type_meter_kwh','tahun_tera_meter_kwh','tahun_buat_meter_kwh','nomor_gardu',
        'nomor_jurusan_tiang','nama_gardu','kapasitas_trafo','nomor_meter_prepaid','product','koordinat_x','koordinat_y','kdam','kdpembmeter',
        'ket_kdpembmeter','status_dil','krn','vkrn'
    ];

    var dtColumns = columns.map(function (col) {
        return { data: col, defaultContent: '-', className: col === 'nama' || col === 'namapnj' ? 'text-nowrap' : '' };
    });

    var table = $('#pelangganTable').DataTable({
        autoWidth: false,
        processing: true,
        serverSide: true,
        searching: false,
        pageLength: 10,
        scrollX: true,
        ajax: {
            url: '<?= site_url('C_Master/Pelanggan/data') ?>',
            type: 'POST',
            data: function (d) {
                d.unit = $('#filter_unit').val();
                d.bulan = $('#filter_bulan').val();
                d.idpel = $('#filter_idpel').val();
                d[csrfName] = $form.find('input[name="' + csrfName + '"]').val();
            },
            dataSrc: function (json) { return json.data || []; },
            error: function () {
                hideLoading();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal memuat data pelanggan.' });
                }
            }
        },
        columns: dtColumns
    });

    $('#pelangganTable').on('preXhr.dt', function () { showLoading(); });
    $('#pelangganTable').on('xhr.dt', function (_e, _s, _j, xhr) {
        var freshCsrf = xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null;
        if (freshCsrf) {
            syncCsrfToken(freshCsrf);
        }
        hideLoading();
    });

    $('#btn-filter-pelanggan').on('click', function () { table.draw(); });
    $('#filter_idpel').on('keypress', function (e) { if (e.which === 13) { e.preventDefault(); table.draw(); } });
});
</script>
<?= $this->endSection() ?>
