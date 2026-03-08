<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<style>
#tableHitRate thead th {
    text-align: center;
    vertical-align: middle;
    white-space: nowrap;
}
#tableHitRate tbody td.num {
    text-align: right;
}
#tableHitRate tbody td,
#tableHitRate tbody th {
    white-space: nowrap;
}
</style>

<div class="card">
    <div class="card-body">
        <?php if (session('error')): ?><div class="alert alert-danger"><?= esc(session('error')) ?></div><?php endif; ?>
        <?php if (session('success')): ?><div class="alert alert-success"><?= esc(session('success')) ?></div><?php endif; ?>

        <div class="row mb-3">
            <div class="col-md-8"></div>
            <div class="col-md-4">
                <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalImportHitrate">Upload Hit Rate</button>
            </div>
        </div>

        <div class="table-responsive">
            <table id="tableHitRate" class="table table-bordered table-striped nowrap w-100">
                <thead>
                    <tr>
                        <th>ID_P2TL</th>
                        <th>IDPEL</th>
                        <th>NAMA</th>
                        <th>TARIF</th>
                        <th>DAYA</th>
                        <th>GARDU</th>
                        <th>TIANG</th>
                        <th>LATITUDE</th>
                        <th>LONGITUDE</th>
                        <th>SESUAI_MERK</th>
                        <th>MERK_METER</th>
                        <th>STAND_LWBP</th>
                        <th>STAND_WBP</th>
                        <th>STAND_KVARH</th>
                        <th>KODE_PESAN</th>
                        <th>UPDATE_STATUS</th>
                        <th>PERUNTUKAN</th>
                        <th>CATATAN</th>
                        <th>PEMUTUSAN</th>
                        <th>KWH_TS</th>
                        <th>WAKTU_PERIKSA</th>
                        <th>REGU</th>
                        <th>SUMBER</th>
                        <th>DLPD</th>
                        <th>SUB_DLPD</th>
                        <th>MATERIAL_KWH</th>
                        <th>JENISLAYANAN</th>
                        <th>JENISPENGUKURAN</th>
                        <th>NOMOR_METER</th>
                        <th>TEGANGAN_METER</th>
                        <th>ARUS_METER</th>
                        <th>KONSTANTA_METER</th>
                        <th>WAKTU_METER</th>
                        <th>MATERIAL_MCB</th>
                        <th>MATERIAL_BOX</th>
                        <th>TEGANGAN_R_N</th>
                        <th>TEGANGAN_S_N</th>
                        <th>TEGANGAN_T_N</th>
                        <th>TEGANGAN_R_S</th>
                        <th>TEGANGAN_S_T</th>
                        <th>TEGANGAN_T_R</th>
                        <th>BEBAN_PRIMER_R</th>
                        <th>BEBAN_PRIMER_S</th>
                        <th>BEBAN_PRIMER_T</th>
                        <th>BEBAN_SEKUNDER_R</th>
                        <th>BEBAN_SEKUNDER_S</th>
                        <th>BEBAN_SEKUNDER_T</th>
                        <th>COS_BEBAN_R</th>
                        <th>COS_BEBAN_S</th>
                        <th>COS_BEBAN_T</th>
                        <th>DEVIASI</th>
                        <th>ARUS_CT_PRIMER_R</th>
                        <th>ARUS_CT_PRIMER_S</th>
                        <th>ARUS_CT_PRIMER_T</th>
                        <th>ARUS_CT_SEKUNDER_R</th>
                        <th>ARUS_CT_SEKUNDER_S</th>
                        <th>ARUS_CT_SEKUNDER_T</th>
                        <th>RUPIAH_TS</th>
                        <th>RUPIAH_KWH</th>
                        <th>UNIT_ULP</th>
                        <th>STATUS_KWH</th>
                        <th>NOMOR_BA</th>
                        <th>MATERIAL_CTPT</th>
                        <th>GANTI_MATERIAL</th>
                        <th>DURASI_PERIKSA</th>
                        <th>TRAFO_ARUS_KWH</th>
                        <th>TRAFO_TEGANGAN_KWH</th>
                        <th>FAKTOR_KALI_KWH</th>
                        <th>FX_KWH</th>
                        <th>FX_KVARH</th>
                        <th>FX_PRIMER</th>
                        <th>FX_SEKUNDER</th>
                        <th>KVA</th>
                        <th>N_KWH</th>
                        <th>N_KVARH</th>
                        <th>T_KWH</th>
                        <th>T_KVARH</th>
                        <th>C_KWH</th>
                        <th>C_KVARH</th>
                        <th>IRT_PRIMER</th>
                        <th>IRT_SEKUNDER</th>
                        <th>COS_IRT</th>
                        <th>KWH_P1</th>
                        <th>KVARH_P1</th>
                        <th>KW_PRIMER</th>
                        <th>FAKTOR_KALI_KWH_R</th>
                        <th>DEVIASI_CT_R</th>
                        <th>DEVIASI_CT_S</th>
                        <th>DEVIASI_CT_T</th>
                        <th>IRT_PRIMER_CT</th>
                        <th>IRT_SEKUNDER_CT</th>
                        <th>FAKTOR_KALI_KWH_IRT</th>
                        <th>DEVIASI_CT</th>
                        <th>TAHUN_MTR_BLM</th>
                        <th>NOMOR_MTR_BLM</th>
                        <th>KONDISI_MTR_BLM</th>
                        <th>TAHUN_MTR_SDH</th>
                        <th>NOMOR_MTR_SDH</th>
                        <th>KONDISI_MTR_SDH</th>
                        <th>TAHUN_MON_BLM</th>
                        <th>NOMOR_MON_BLM</th>
                        <th>KONDISI_MON_BLM</th>
                        <th>TAHUN_MON_SDH</th>
                        <th>NOMOR_MON_SDH</th>
                        <th>KONDISI_MON_SDH</th>
                        <th>TAHUN_CT_BLM</th>
                        <th>NOMOR_CT_BLM</th>
                        <th>KONDISI_CT_BLM</th>
                        <th>TAHUN_CT_SDH</th>
                        <th>NOMOR_CT_SDH</th>
                        <th>KONDISI_CT_SDH</th>
                        <th>TAHUN_VT_BLM</th>
                        <th>NOMOR_VT_BLM</th>
                        <th>KONDISI_VT_BLM</th>
                        <th>TAHUN_VT_SDH</th>
                        <th>NOMOR_VT_SDH</th>
                        <th>KONDISI_VT_SDH</th>
                        <th>TAHUN_RELEY_BLM</th>
                        <th>NOMOR_RELEY_BLM</th>
                        <th>KONDISI_RELEY_BLM</th>
                        <th>TAHUN_RELEY_SDH</th>
                        <th>NOMOR_RELEY_SDH</th>
                        <th>KONDISI_RELEY_SDH</th>
                        <th>TAHUN_PEMBATAS_BLM</th>
                        <th>NOMOR_PEMBATAS_BLM</th>
                        <th>KONDISI_PEMBATAS_BLM</th>
                        <th>TAHUN_PEMBATAS_SDH</th>
                        <th>NOMOR_PEMBATAS_SDH</th>
                        <th>KONDISI_PEMBATAS_SDH</th>
                        <th>TAHUN_BOXAPP_BLM</th>
                        <th>NOMOR_BOXAPP_BLM</th>
                        <th>KONDISI_BOXAPP_BLM</th>
                        <th>TAHUN_BOXAPP_SDH</th>
                        <th>NOMOR_BOXAPP_SDH</th>
                        <th>KONDISI_BOXAPP_SDH</th>
                        <th>TAHUN_PLATAPP_BLM</th>
                        <th>NOMOR_PLATAPP_BLM</th>
                        <th>KONDISI_PLATAPP_BLM</th>
                        <th>TAHUN_PLATAPP_SDH</th>
                        <th>NOMOR_PLATAPP_SDH</th>
                        <th>KONDISI_PLATAPP_SDH</th>
                        <th>TAHUN_BOXAMR_BLM</th>
                        <th>NOMOR_BOXAMR_BLM</th>
                        <th>KONDISI_BOXAMR_BLM</th>
                        <th>TAHUN_BOXAMR_SDH</th>
                        <th>NOMOR_BOXAMR_SDH</th>
                        <th>KONDISI_BOXAMR_SDH</th>
                        <th>UNIT_UP3</th>
                        <th>UNIT_UID</th>
                        <th>NIK_PELANGGAN</th>
                        <th>MSISDN_PELANGGAN</th>
                        <th>TS_AP2T</th>
                        <th>NO_AGENDA</th>
                        <th>TANGGAL_SPH</th>
                        <th>TINDAKLANJUT_PEMERIKSAAN</th>
                        <th>USERNAME</th>
                        <th>NAMA_PETUGAS</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalImportHitrate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Hitrate</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="<?= site_url('C_P2TL/importHitRate') ?>" enctype="multipart/form-data" id="formImportHitrate">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <label class="form-label">File (xlsx/xls/csv)</label>
                    <input type="file" class="form-control" name="file_import" accept=".xlsx,.xls,.csv" required>
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
<script>
(function () {
    var datatableCssHref = '<?= base_url('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') ?>';
    if (!document.querySelector('link[data-role="datatable-bs5"]')) {
        var styleLink = document.createElement('link');
        styleLink.rel = 'stylesheet';
        styleLink.href = datatableCssHref;
        styleLink.setAttribute('data-role', 'datatable-bs5');
        document.head.appendChild(styleLink);
    }
})();
</script>
<script src="<?= base_url('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') ?>"></script>
<script>
(function () {
    if (!window.jQuery || !window.jQuery.fn || typeof window.jQuery.fn.DataTable !== 'function') {
        return;
    }

    var csrfFieldName = '<?= esc(csrf_token()) ?>';
    var csrfToken = '<?= esc(csrf_hash()) ?>';

    var table = $('#tableHitRate').DataTable({
        autoWidth: false,
        processing: true,
        serverSide: true,
        ordering: false,
        searching: false,
        paging: true,
        scrollX: true,
        responsive: false,
        ajax: {
            url: '<?= site_url('C_P2TL/ajaxHitRate') ?>',
            type: 'POST',
            data: function (d) {
                d.unit = '*';
                d.tanggal_awal = '';
                d.tanggal_akhir = '';
                d[csrfFieldName] = csrfToken;
            },
            beforeSend: function () {
                Swal.fire({ title: 'Mohon Tunggu', html: 'Memuat data hitrate', allowOutsideClick: false, showConfirmButton: false, didOpen: function(){ Swal.showLoading(); } });
            },
            complete: function (xhr) {
                var fresh = xhr.getResponseHeader('X-CSRF-TOKEN');
                if (fresh) csrfToken = fresh;
                Swal.close();
            },
            error: function (xhr) {
                Swal.close();
                Swal.fire('Gagal', 'Data hitrate gagal dimuat (' + xhr.status + ')', 'error');
            }
        },
        columnDefs: [{ targets: [4], className: 'num' }]
    });

    $('#formImportHitrate').on('submit', function () {
        Swal.fire({ title: 'Mohon Tunggu', html: 'Proses import berlangsung', allowOutsideClick: false, showConfirmButton: false, didOpen: function(){ Swal.showLoading(); } });
    });
})();
</script>
<?= $this->endSection() ?>
