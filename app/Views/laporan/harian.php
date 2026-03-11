<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$units = is_array($units ?? null) ? $units : [];
$alasanOptions = is_array($alasanOptions ?? null) ? $alasanOptions : [];
$dayaOptions = is_array($dayaOptions ?? null) ? $dayaOptions : [];
$filters = is_array($filters ?? null) ? $filters : [];
?>

<link rel="stylesheet" href="<?= base_url('assets/vendor/libs/select2/select2.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') ?>">
<style>
    .select2-container--default .select2-selection--multiple {
        overflow-y: auto;
        max-height: calc(2.25rem + 2px);
    }
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
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Filter Laporan Harian</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importModal" type="button">Upload Data</button>
    </div>
    <div class="card-body">
        <form method="post" action="<?= site_url('C_Laporan/Harian/data') ?>" class="row g-3" id="harianFilterForm">
            <?= csrf_field() ?>
            <div class="col-md-3">
                <label class="form-label">Unit</label>
                <select class="form-select" name="unit">
                    <option value="*">Semua Unit</option>
                    <?php foreach ($units as $unit): ?>
                        <?php $unitId = (string) ($unit['unit_id'] ?? ''); ?>
                        <option value="<?= esc($unitId) ?>" <?= ($filters['unit'] ?? '*') === $unitId ? 'selected' : '' ?>><?= esc((string) ($unit['unit_name'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tahun Meter Lama</label>
                <select class="form-select" name="tahun_meter_lama">
                    <option value="*">Semua</option>
                    <option value="0" <?= ($filters['tahun_meter_lama'] ?? '*') === '0' ? 'selected' : '' ?>>Tidak Diketahui</option>
                    <?php for ($year = (int) date('Y'); $year >= 1990; $year--): ?>
                        <option value="<?= esc((string) $year) ?>" <?= ($filters['tahun_meter_lama'] ?? '*') === (string) $year ? 'selected' : '' ?>><?= esc((string) $year) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tarif</label>
                <select class="form-select" name="tarif">
                    <option value="*" <?= ($filters['tarif'] ?? '*') === '*' ? 'selected' : '' ?>>Semua</option>
                    <option value="pra" <?= ($filters['tarif'] ?? '*') === 'pra' ? 'selected' : '' ?>>PRA</option>
                    <option value="paska" <?= ($filters['tarif'] ?? '*') === 'paska' ? 'selected' : '' ?>>PASKA</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Fasa</label>
                <select class="form-select" name="fasa">
                    <option value="*">Semua</option>
                    <option value="1 Fasa" <?= ($filters['fasa'] ?? '*') === '1 Fasa' ? 'selected' : '' ?>>1 Fasa</option>
                    <option value="3 Fasa" <?= ($filters['fasa'] ?? '*') === '3 Fasa' ? 'selected' : '' ?>>3 Fasa</option>
                    <?php foreach ($dayaOptions as $daya): ?>
                        <?php $nilaiDaya = (string) ($daya['daya'] ?? ''); if ($nilaiDaya === '') continue; ?>
                        <option value="<?= esc($nilaiDaya) ?>" <?= ($filters['fasa'] ?? '*') === $nilaiDaya ? 'selected' : '' ?>><?= esc($nilaiDaya) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Alasan Ganti Meter (multi)</label>
                <select class="form-select select2 w-100" name="alasan[]" multiple data-placeholder="Seluruh Alasan">
                    <?php foreach ($alasanOptions as $alasan): ?>
                        <?php $val = (string) ($alasan['alasan_ganti_meter'] ?? ''); if ($val === '') continue; ?>
                        <option value="<?= esc($val) ?>" <?= in_array($val, $filters['alasan'] ?? [], true) ? 'selected' : '' ?>><?= esc($val) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Tanggal Peremajaan</label>
                <input class="form-control" type="date" name="tgl_peremajaan" value="<?= esc((string) ($filters['tgl_peremajaan'] ?? '')) ?>">
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Data Laporan Harian</h5>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover table-sm mb-0" id="harianTable">
            <thead>
                <tr>
                    <th>NOAGENDA</th>
                    <th>UNITUPI</th>
                    <th>UNITAP</th>
                    <th>UNITUP</th>
                    <th>NOMORPDL</th>
                    <th>IDPEL</th>
                    <th>NAMA</th>
                    <th>ALAMAT</th>
                    <th>KDDK</th>
                    <th>NAMA_PROV</th>
                    <th>NAMA_KAB</th>
                    <th>NAMA_KEC</th>
                    <th>NAMA_KEL</th>
                    <th>TARIF</th>
                    <th>DAYA</th>
                    <th>KDPT</th>
                    <th>KDPT_2</th>
                    <th>JENIS_MK</th>
                    <th>RP_TOKEN</th>
                    <th>RPTOTAL</th>
                    <th>TGLPENGADUAN</th>
                    <th>TGLTINDAKANPENGADUAN</th>
                    <th>TGLBAYAR</th>
                    <th>TGLAKTIVASI</th>
                    <th>TGLPENANGGUHAN</th>
                    <th>TGLRESTITUSI</th>
                    <th>TGLREMAJA</th>
                    <th>TGLNYALA</th>
                    <th>TGLBATAL</th>
                    <th>STATUS_PERMOHONAN</th>
                    <th>ID_GANTI_METER</th>
                    <th>ALASAN_GANTI_METER</th>
                    <th>ALASAN_PENANGGUHAN</th>
                    <th>KETERANGAN_ALASAN_PENANGGUHAN</th>
                    <th>NO_METER_BARU</th>
                    <th>MERK_METER_BARU</th>
                    <th>TYPE_METER_BARU</th>
                    <th>THTERA_METER_BARU</th>
                    <th>THBUAT_METER_BARU</th>
                    <th>NO_METER_LAMA</th>
                    <th>MERK_METER_LAMA</th>
                    <th>TYPE_METER_LAMA</th>
                    <th>THTERA_METER_LAMA</th>
                    <th>THBUAT_METER_LAMA</th>
                    <th>PETUGASPENGADUAN</th>
                    <th>PETUGASTINDAKANPENGADUAN</th>
                    <th>PETUGASAKTIVASI</th>
                    <th>PETUGASPENANGGUHAN</th>
                    <th>PETUGASRESTITUSI</th>
                    <th>PETUGASREMAJA</th>
                    <th>PETUGASBATAL</th>
                    <th>TGLREKAP</th>
                    <th>KDPEMBMETER</th>
                    <th>CT_PRIMER_KWH</th>
                    <th>CT_SEKUNDER_KWH</th>
                    <th>PT_PRIMER_KWH</th>
                    <th>PT_SEKUNDER_KWH</th>
                    <th>KONSTANTA_KWH</th>
                    <th>FAKMKWH</th>
                    <th>TYPE_CT_KWH</th>
                    <th>CT_PRIMER_KVARH</th>
                    <th>CT_SEKUNDER_KVARH</th>
                    <th>PT_PRIMER_KVARH</th>
                    <th>PT_SEKUNDER_KVARH</th>
                    <th>KONSTANTA_KVARH</th>
                    <th>FAKMKVARH</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= site_url('C_Laporan/Harian/import') ?>" method="post" enctype="multipart/form-data" novalidate>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Import Laporan Harian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">File yang didukung: <strong>CSV, TXT, XLS, XLSX</strong>.</div>
                    <input type="file" class="form-control" name="file_import" accept=".csv,.txt,.xls,.xlsx" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Upload Data</button>
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
        var $form = $('#harianFilterForm');
        var csrfName = '<?= esc(csrf_token()) ?>';
        var filterTimer = null;
        var swalActive = false;

        var syncCsrfToken = function (tokenValue) {
            if (! tokenValue) {
                return;
            }

            $('input[name="' + csrfName + '"]').val(tokenValue);
        };

        $('.select2').select2({
            width: '100%',
            placeholder: 'Seluruh Alasan'
        });

        var showLoading = function () {
            if (typeof Swal === 'undefined') {
                return;
            }

            swalActive = true;
            Swal.fire({
                title: 'Mohon Tunggu',
                html: 'Mengambil data laporan harian...',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: function () {
                    Swal.showLoading();
                }
            });
        };

        var hideLoading = function () {
            if (! swalActive || typeof Swal === 'undefined') {
                return;
            }

            Swal.close();
            swalActive = false;
        };

        var table = $('#harianTable').DataTable({
            autoWidth: false,
            processing: true,
            serverSide: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            searching: false,
            scrollX: true,
            order: [[26, 'desc']],
            ajax: {
                url: '<?= site_url('C_Laporan/Harian/data') ?>',
                type: 'POST',
                data: function (d) {
                    d.unit = $form.find('select[name="unit"]').val();
                    d.alasan = $form.find('select[name="alasan[]"]').val();
                    d.tahun_meter_lama = $form.find('select[name="tahun_meter_lama"]').val();
                    d.tarif = $form.find('select[name="tarif"]').val();
                    d.fasa = $form.find('select[name="fasa"]').val();
                    d.tgl_peremajaan = $form.find('input[name="tgl_peremajaan"]').val();
                    d.search = '';
                    d[csrfName] = $form.find('input[name="' + csrfName + '"]').val();
                },
                dataSrc: function (json) {
                    return json.data || [];
                },
                error: function () {
                    hideLoading();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Gagal memuat data laporan harian.'
                        });
                    }
                }
            },
            columns: [
                { data: 'no_agenda', defaultContent: '-' },
                { data: 'unit_upi', defaultContent: '-' },
                { data: 'unit_ap', defaultContent: '-' },
                { data: 'unit_up', defaultContent: '-' },
                { data: 'nomor_pdl', defaultContent: '-' },
                { data: 'idpel', defaultContent: '-' },
                { data: 'nama', defaultContent: '-' },
                { data: 'alamat', defaultContent: '-' },
                { data: 'kddk', defaultContent: '-' },
                { data: 'nama_prov', defaultContent: '-' },
                { data: 'nama_kab', defaultContent: '-' },
                { data: 'nama_kec', defaultContent: '-' },
                { data: 'nama_kel', defaultContent: '-' },
                { data: 'tarif', defaultContent: '-' },
                { data: 'daya', defaultContent: '-' },
                { data: 'kdpt', defaultContent: '-' },
                { data: 'kdpt_2', defaultContent: '-' },
                { data: 'jenis_mk', defaultContent: '-' },
                { data: 'rp_token', defaultContent: '-' },
                { data: 'rptotal', defaultContent: '-' },
                { data: 'tgl_pengaduan', defaultContent: '-' },
                { data: 'tgl_tindakan_pengaduan', defaultContent: '-' },
                { data: 'tgl_bayar', defaultContent: '-' },
                { data: 'tgl_aktivasi', defaultContent: '-' },
                { data: 'tgl_penangguhan', defaultContent: '-' },
                { data: 'tgl_restitusi', defaultContent: '-' },
                { data: 'tgl_remaja', defaultContent: '-' },
                { data: 'tgl_nyala', defaultContent: '-' },
                { data: 'tgl_batal', defaultContent: '-' },
                { data: 'status_permohonan', defaultContent: '-' },
                { data: 'id_ganti_meter', defaultContent: '-' },
                { data: 'alasan_ganti_meter', defaultContent: '-' },
                { data: 'alasan_penangguhan', defaultContent: '-' },
                { data: 'keterangan_alasan_penangguhan', defaultContent: '-' },
                { data: 'no_meter_baru', defaultContent: '-' },
                { data: 'merk_meter_baru', defaultContent: '-' },
                { data: 'type_meter_baru', defaultContent: '-' },
                { data: 'thtera_meter_baru', defaultContent: '-' },
                { data: 'thbuat_meter_baru', defaultContent: '-' },
                { data: 'no_meter_lama', defaultContent: '-' },
                { data: 'merk_meter_lama', defaultContent: '-' },
                { data: 'type_meter_lama', defaultContent: '-' },
                { data: 'thtera_meter_lama', defaultContent: '-' },
                { data: 'thbuat_meter_lama', defaultContent: '-' },
                { data: 'petugas_pengaduan', defaultContent: '-' },
                { data: 'petugas_tindakan_pengaduan', defaultContent: '-' },
                { data: 'petugas_aktivasi', defaultContent: '-' },
                { data: 'petugas_penangguhan', defaultContent: '-' },
                { data: 'petugas_restitusi', defaultContent: '-' },
                { data: 'petugas_remaja', defaultContent: '-' },
                { data: 'petugas_batal', defaultContent: '-' },
                { data: 'tgl_rekap', defaultContent: '-' },
                { data: 'kd_pemb_meter', defaultContent: '-' },
                { data: 'ct_primer_kwh', defaultContent: '-' },
                { data: 'ct_sekunder_kwh', defaultContent: '-' },
                { data: 'pt_primer_kwh', defaultContent: '-' },
                { data: 'pt_sekunder_kwh', defaultContent: '-' },
                { data: 'konstanta_kwh', defaultContent: '-' },
                { data: 'fakm_kwh', defaultContent: '-' },
                { data: 'type_ct_kwh', defaultContent: '-' },
                { data: 'ct_primer_kvarh', defaultContent: '-' },
                { data: 'ct_sekunder_kvarh', defaultContent: '-' },
                { data: 'pt_primer_kvarh', defaultContent: '-' },
                { data: 'pt_sekunder_kvarh', defaultContent: '-' },
                { data: 'konstanta_kvarh', defaultContent: '-' },
                { data: 'fakm_kvarh', defaultContent: '-' }
            ]
        });

        $('#harianTable').on('preXhr.dt', function () {
            showLoading();
        });

        $('#harianTable').on('xhr.dt', function (_e, _settings, _json, xhr) {
            var freshCsrf = xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null;
            syncCsrfToken(freshCsrf);
            hideLoading();
        });

        var queueReload = function (delayMs) {
            if (filterTimer !== null) {
                window.clearTimeout(filterTimer);
            }

            filterTimer = window.setTimeout(function () {
                table.ajax.reload();
            }, delayMs);
        };

        $form.on('change', 'select, input[type="date"]', function () {
            queueReload(200);
        });
    });
</script>
<?= $this->endSection() ?>
