<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$units = is_array($units ?? null) ? $units : [];
$filters = is_array($filters ?? null) ? $filters : [];
$bulanOptions = is_array($bulanOptions ?? null) ? $bulanOptions : [];
?>

<link rel="stylesheet" href="<?= base_url('assets/vendor/libs/select2/select2.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') ?>">
<style>
    .text-nowrap {
        white-space: nowrap;
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
    <div class="card-body">
        <form method="post" action="<?= site_url('C_Laporan/Saldo/data') ?>" class="row g-3" id="saldoFilterForm">
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
                <button class="btn btn-primary w-100" type="button" id="btn-filter-saldo">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Data Saldo Pelanggan</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive text-nowrap">
        <table class="table table-bordered table-striped" id="saldoTable">
            <thead>
                <tr>
                    <th>V_BULAN_REKAP</th>
                    <th>UNITUP</th>
                    <th>IDPEL</th>
                    <th>NAMA</th>
                    <th>NAMAPNJ</th>
                    <th>TARIF</th>
                    <th>DAYA</th>
                    <th>KDPT_2</th>
                    <th>THBLMUT</th>
                    <th>JENIS_MK</th>
                    <th>JENISLAYANAN</th>
                    <th>FRT</th>
                    <th>KOGOL</th>
                    <th>FKMKWH</th>
                    <th>NOMOR_METER_KWH</th>
                    <th>TANGGAL_PASANG_RUBAH_APP</th>
                    <th>MERK_METER_KWH</th>
                    <th>TYPE_METER_KWH</th>
                    <th>TAHUN_TERA_METER_KWH</th>
                    <th>TAHUN_BUAT_METER_KWH</th>
                    <th>NOMOR_GARDU</th>
                    <th>NOMOR_JURUSAN_TIANG</th>
                    <th>NAMA_GARDU</th>
                    <th>KAPASITAS_TRAFO</th>
                    <th>NOMOR_METER_PREPAID</th>
                    <th>PRODUCT</th>
                    <th>KOORDINAT_X</th>
                    <th>KOORDINAT_Y</th>
                    <th>KDAM</th>
                    <th>KDPEMBMETER</th>
                    <th>KET_KDPEMBMETER</th>
                    <th>STATUS_DIL</th>
                    <th>KRN</th>
                    <th>VKRN</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        </div>
    </div>
</div>

<div class="modal fade" id="saldoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= site_url('C_Laporan/Saldo/update') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Update Saldo Pelanggan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">IDPEL</label>
                        <input class="form-control" type="text" name="idpel" id="saldo_idpel" readonly required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bulan Rekap</label>
                        <input class="form-control" type="number" name="v_bulan_rekap" id="saldo_bulan" readonly required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input class="form-control" type="text" name="nama" id="saldo_nama">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tarif</label>
                        <input class="form-control" type="text" name="tarif" id="saldo_tarif">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Daya</label>
                        <input class="form-control" type="number" min="0" name="daya" id="saldo_daya">
                    </div>
                    <div class="mt-3">
                        <label class="form-label">No Meter KWH</label>
                        <input class="form-control" type="number" min="0" name="nomor_meter_kwh" id="saldo_meter">
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
        var $form = $('#saldoFilterForm');
        var swalActive = false;

        $('.select2').select2({ width: '100%' });

        var showLoading = function () {
            if (typeof Swal === 'undefined') {
                return;
            }

            swalActive = true;
            Swal.fire({
                title: 'Mohon Tunggu',
                html: 'Mengambil data saldo...',
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

        var table = $('#saldoTable').DataTable({
            autoWidth: false,
            processing: true,
            serverSide: true,
            searching: false,
            pageLength: 10,
            scrollX: true,
            ajax: {
                url: '<?= site_url('C_Laporan/Saldo/data') ?>',
                type: 'POST',
                data: function (d) {
                    d.unit = $('#filter_unit').val();
                    d.bulan = $('#filter_bulan').val();
                    d.idpel = $('#filter_idpel').val();
                    d['<?= esc(csrf_token()) ?>'] = $form.find('input[name="<?= esc(csrf_token()) ?>"]').val();
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
                            text: 'Gagal memuat data saldo.'
                        });
                    }
                }
            },
            columns: [
                { data: 'v_bulan_rekap', defaultContent: '-' },
                { data: 'unit_up', defaultContent: '-' },
                { data: 'idpel', defaultContent: '-' },
                { data: 'nama', defaultContent: '-', className: 'text-nowrap' },
                { data: 'nama_pnj', defaultContent: '-', className: 'text-nowrap' },
                { data: 'tarif', defaultContent: '-' },
                { data: 'daya', defaultContent: '-' },
                { data: 'kdpt_2', defaultContent: '-' },
                { data: 'thbl_mut', defaultContent: '-' },
                { data: 'jenis_mk', defaultContent: '-' },
                { data: 'jenis_layanan', defaultContent: '-' },
                { data: 'frt', defaultContent: '-' },
                { data: 'kogol', defaultContent: '-' },
                { data: 'fkmkwh', defaultContent: '-' },
                { data: 'nomor_meter_kwh', defaultContent: '-' },
                { data: 'tanggal_pasang_rubah_app', defaultContent: '-' },
                { data: 'merk_meter_kwh', defaultContent: '-' },
                { data: 'type_meter_kwh', defaultContent: '-' },
                { data: 'tahun_tera_meter_kwh', defaultContent: '-' },
                { data: 'tahun_buat_meter_kwh', defaultContent: '-' },
                { data: 'nomor_gardu', defaultContent: '-' },
                { data: 'nomor_jurusan_tiang', defaultContent: '-' },
                { data: 'nama_gardu', defaultContent: '-' },
                { data: 'kapasitas_trafo', defaultContent: '-' },
                { data: 'nomor_meter_prepaid', defaultContent: '-' },
                { data: 'product', defaultContent: '-' },
                { data: 'koordinat_x', defaultContent: '-' },
                { data: 'koordinat_y', defaultContent: '-' },
                { data: 'kdam', defaultContent: '-' },
                { data: 'kd_pemb_meter', defaultContent: '-' },
                { data: 'ket_kdpembmeter', defaultContent: '-' },
                { data: 'status_dil', defaultContent: '-' },
                { data: 'krn', defaultContent: '-' },
                { data: 'vkrn', defaultContent: '-' },
                { data: 'aksi', orderable: false, searchable: false }
            ]
        });

        $('#saldoTable').on('preXhr.dt', function () {
            showLoading();
        });

        $('#saldoTable').on('xhr.dt', function (_e, _settings, _json, xhr) {
            var freshCsrf = xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null;
            if (freshCsrf) {
                $form.find('input[name="<?= esc(csrf_token()) ?>"]').val(freshCsrf);
            }
            hideLoading();
        });

        $('#btn-filter-saldo').on('click', function () {
            table.draw();
        });

        $('#filter_unit, #filter_bulan').on('change', function () {
            table.draw();
        });

        $('#filter_idpel').on('input', function () {
            table.draw();
        });

        document.addEventListener('click', function (event) {
            var btn = event.target.closest('.btn-edit-saldo');
            if (!btn) {
                return;
            }

            document.getElementById('saldo_idpel').value = btn.getAttribute('data-idpel') || '';
            document.getElementById('saldo_bulan').value = btn.getAttribute('data-bulan') || '';
            document.getElementById('saldo_nama').value = btn.getAttribute('data-nama') || '';
            document.getElementById('saldo_tarif').value = btn.getAttribute('data-tarif') || '';
            document.getElementById('saldo_daya').value = btn.getAttribute('data-daya') || '';
            document.getElementById('saldo_meter').value = btn.getAttribute('data-meter') || '';
        });
    });
</script>
<?= $this->endSection() ?>
