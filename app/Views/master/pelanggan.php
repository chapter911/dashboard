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
                <div class="mb-2">
                    <div class="progress" style="height: 18px;">
                        <div
                            id="autoResumeProgressBar"
                            class="progress-bar progress-bar-striped progress-bar-animated"
                            role="progressbar"
                            style="width: <?= esc((string) $resumePercent) ?>%;"
                            aria-valuenow="<?= esc((string) $resumePercent) ?>"
                            aria-valuemin="0"
                            aria-valuemax="100"
                        ><?= esc((string) $resumePercent) ?>%</div>
                    </div>
                    <div class="small text-muted mt-1" id="autoResumeProgressMeta">
                        <?= esc((string) $resumeDoneRows) ?> / <?= esc((string) $resumeDataRows) ?> baris diproses
                    </div>
                </div>
                <div class="small mb-2" id="autoResumeStatus"></div>
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" role="switch" id="autoResumeToggle" checked>
                    <label class="form-check-label" for="autoResumeToggle">Auto lanjutkan import</label>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <form method="post" action="<?= site_url('C_Master/Pelanggan/resume') ?>" class="d-inline" data-skip-swal-loading="1">
                        <?= csrf_field() ?>
                        <input type="hidden" name="resume_token" value="<?= esc($resumeToken) ?>">
                        <button class="btn btn-warning btn-sm" type="submit">Lanjutkan Import</button>
                    </form>
                    <button
                        class="btn btn-primary btn-sm"
                        type="button"
                        id="btnAutoResumeImport"
                        data-token="<?= esc($resumeToken) ?>"
                        data-url="<?= site_url('C_Master/Pelanggan/resume/auto') ?>"
                    >
                        Lanjutkan Otomatis
                    </button>
                    <form method="post" action="<?= site_url('C_Master/Pelanggan/resume/cancel') ?>" class="d-inline" data-skip-swal-loading="1" onsubmit="return confirm('Batalkan resume import? Progress sementara akan dihapus.');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="resume_token" value="<?= esc($resumeToken) ?>">
                        <button class="btn btn-outline-danger btn-sm" type="submit">Batalkan Import</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
        <form method="post" action="<?= site_url('C_Master/Pelanggan/import') ?>" enctype="multipart/form-data" class="row g-3" data-skip-swal-loading="1">
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

    var $autoResumeBtn = $('#btnAutoResumeImport');
    var $autoResumeStatus = $('#autoResumeStatus');
    var $autoResumeToggle = $('#autoResumeToggle');
    var $autoResumeProgressBar = $('#autoResumeProgressBar');
    var $autoResumeProgressMeta = $('#autoResumeProgressMeta');
    var autoResumeRunning = false;
    var autoResumeXhr = null;
    var autoResumeRetryCount = 0;
    var autoResumeBaseRetryDelayMs = 1500;
    var autoResumeMaxRetryDelayMs = 10000;
    var autoResumePrefKey = 'pelanggan_import_auto_resume_enabled';

    var setAutoProgress = function (pct) {
        if (!$autoResumeProgressBar.length) return;

        var num = parseInt(pct, 10);
        if (isNaN(num)) {
            return;
        }

        num = Math.max(0, Math.min(100, num));
        $autoResumeProgressBar
            .css('width', num + '%')
            .attr('aria-valuenow', num)
            .text(num + '%');

        if (num >= 100) {
            $autoResumeProgressBar.removeClass('progress-bar-animated');
        }
    };

    var setAutoProgressMeta = function (processedRows, totalRows) {
        if (!$autoResumeProgressMeta.length) return;

        var processed = parseInt(processedRows, 10);
        var total = parseInt(totalRows, 10);
        if (isNaN(processed) || isNaN(total) || total < 1) {
            return;
        }

        if (processed < 0) processed = 0;
        if (processed > total) processed = total;

        $autoResumeProgressMeta.text(processed + ' / ' + total + ' baris diproses');
    };

    setAutoProgress(<?= (int) $resumePercent ?>);
    setAutoProgressMeta(<?= (int) $resumeDoneRows ?>, <?= (int) $resumeDataRows ?>);

    var setAutoStatus = function (message, isError) {
        if (!$autoResumeStatus.length) return;
        $autoResumeStatus
            .toggleClass('text-danger', !!isError)
            .toggleClass('text-muted', !isError)
            .text(message || '');
    };

    var handleAutoResume = function () {
        if (!autoResumeRunning || !$autoResumeBtn.length) return;

        var retryDelay = Math.min(autoResumeMaxRetryDelayMs, autoResumeBaseRetryDelayMs * Math.max(1, autoResumeRetryCount));

        autoResumeXhr = $.ajax({
            url: $autoResumeBtn.data('url'),
            type: 'POST',
            dataType: 'json',
            skipSwalLoading: true,
            data: (function () {
                var payload = { resume_token: $autoResumeBtn.data('token') };
                payload[csrfName] = $form.find('input[name="' + csrfName + '"]').val();
                return payload;
            })(),
            success: function (resp, _textStatus, xhr) {
                var freshCsrf = xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null;
                if (freshCsrf) {
                    syncCsrfToken(freshCsrf);
                }

                if (!resp || !resp.status) {
                    setAutoStatus('Respons server tidak valid. Coba lanjutkan manual.', true);
                    autoResumeRunning = false;
                    $autoResumeBtn.prop('disabled', false).text('Lanjutkan Otomatis');
                    return;
                }

                if (resp.status === 'in_progress') {
                    autoResumeRetryCount = 0;
                    var pct = typeof resp.progress_percent !== 'undefined' ? resp.progress_percent : '?';
                    var inserted = typeof resp.inserted_rows !== 'undefined' ? resp.inserted_rows : '?';
                    var processed = typeof resp.processed_rows !== 'undefined' ? resp.processed_rows : null;
                    var totalRows = typeof resp.total_rows !== 'undefined' ? resp.total_rows : null;
                    setAutoProgress(pct);
                    setAutoProgressMeta(processed, totalRows);
                    setAutoStatus('Proses berjalan: ' + pct + '% (tersimpan: ' + inserted + ' baris).', false);
                    window.setTimeout(handleAutoResume, 500);
                    return;
                }

                if (resp.status === 'success') {
                    autoResumeRetryCount = 0;
                    setAutoProgress(100);
                    setAutoProgressMeta(resp.total_rows, resp.total_rows);
                    setAutoStatus(resp.message || 'Import selesai.', false);
                    window.location.reload();
                    return;
                }

                setAutoStatus(resp.message || 'Resume otomatis gagal. Coba lanjutkan manual.', true);
                autoResumeRunning = false;
                $autoResumeBtn.prop('disabled', false).text('Lanjutkan Otomatis');
            },
            error: function (_xhr, textStatus) {
                if (textStatus === 'abort') {
                    return;
                }

                if (!autoResumeRunning) {
                    return;
                }

                autoResumeRetryCount += 1;
                setAutoStatus('Koneksi ke server terputus. Mencoba lagi otomatis dalam ' + Math.ceil(retryDelay / 1000) + ' detik (percobaan ke-' + autoResumeRetryCount + ').', true);
                window.setTimeout(function () {
                    if (autoResumeRunning) {
                        handleAutoResume();
                    }
                }, retryDelay);
            },
            complete: function () {
                autoResumeXhr = null;
            }
        });
    };

    $autoResumeBtn.on('click', function () {
        if (autoResumeRunning) {
            autoResumeRunning = false;
            autoResumeRetryCount = 0;
            if (autoResumeXhr && typeof autoResumeXhr.abort === 'function') {
                autoResumeXhr.abort();
            }
            autoResumeXhr = null;
            $(this).prop('disabled', false).text('Lanjutkan Otomatis');
            setAutoStatus('Resume otomatis dihentikan oleh pengguna.', true);
            return;
        }

        autoResumeRunning = true;
        autoResumeRetryCount = 0;
        $(this).prop('disabled', false).text('Hentikan Otomatis');
        setAutoStatus('Memulai resume otomatis...', false);
        handleAutoResume();
    });

    if ($autoResumeToggle.length) {
        try {
            var savedPref = window.localStorage.getItem(autoResumePrefKey);
            if (savedPref !== null) {
                $autoResumeToggle.prop('checked', savedPref === '1');
            }
        } catch (e) {
            // ignore localStorage access errors
        }

        $autoResumeToggle.on('change', function () {
            try {
                window.localStorage.setItem(autoResumePrefKey, $(this).is(':checked') ? '1' : '0');
            } catch (e) {
                // ignore localStorage access errors
            }
        });
    }

    if ($autoResumeBtn.length && (!$autoResumeToggle.length || $autoResumeToggle.is(':checked'))) {
        window.setTimeout(function () {
            if (!autoResumeRunning) {
                $autoResumeBtn.trigger('click');
            }
        }, 350);
    }
});
</script>
<?= $this->endSection() ?>
