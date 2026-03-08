<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="card mb-3">
    <div class="card-header">
        <h3 class="mb-3">Dashboard P2TL</h3>
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Jenis Akumulasi</label>
                <select class="form-select" id="jenis_akumulasi" name="jenis_akumulasi">
                    <option value="TAHUNAN" selected>TAHUNAN</option>
                    <option value="BULANAN">BULANAN</option>
                    <option value="MINGGUAN">MINGGUAN</option>
                    <option value="HARIAN">HARIAN</option>
                    <option value="TRIWULAN">TRIWULAN</option>
                    <option value="SEMESTER">SEMESTER</option>
                </select>
            </div>
        </div>
    </div>
    <div class="card-body" id="viewContainer"></div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="mb-3">Dashboard Hit Rate</h3>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Tanggal Awal</label>
                <input type="date" class="form-control" id="tanggal_awal" onclick="this.showPicker && this.showPicker()" value="<?= date('Y-01-01') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Akhir</label>
                <input type="date" class="form-control" id="tanggal_akhir" onclick="this.showPicker && this.showPicker()" value="<?= date('Y-m-t') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Urut Berdasarkan</label>
                <select class="form-select" id="sortir_hitrate">
                    <option value="*">TANPA SORTIR</option>
                    <option value="1">PERSENTASE TERBESAR</option>
                    <option value="0">PERSENTASE TERKECIL</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="button" class="btn btn-primary w-100" id="btnExportHitrate">Export Data</button>
            </div>
        </div>
    </div>
    <div class="card-body" id="hitrateContainer"></div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    var csrfFieldName = '<?= esc(csrf_token()) ?>';
    var csrfToken = '<?= esc(csrf_hash()) ?>';

    window.__p2tlCsrf = {
        fieldName: csrfFieldName,
        token: csrfToken
    };

    function applyCsrf(xhr) {
        var fresh = xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null;
        if (fresh) {
            csrfToken = fresh;
            window.__p2tlCsrf.token = fresh;
        }
    }

    function post(url, data, onSuccess) {
        data = data || {};
        data[window.__p2tlCsrf.fieldName] = window.__p2tlCsrf.token;

        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            beforeSend: function () {
                Swal.fire({
                    title: 'Mohon Tunggu',
                    html: 'Mengambil Data',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: function () { Swal.showLoading(); }
                });
            },
            success: function (response, _status, xhr) {
                applyCsrf(xhr);
                Swal.close();
                onSuccess(response);
            },
            error: function (xhr) {
                applyCsrf(xhr);
                Swal.fire('Error', 'Gagal mengambil data (' + xhr.status + ').', 'error');
            }
        });
    }

    function getView(done) {
        post('<?= site_url('C_P2TL/getViewIndex') ?>', {
            jenis_akumulasi: $('#jenis_akumulasi').val()
        }, function (response) {
            $('#viewContainer').html(response);
            if (typeof done === 'function') {
                done();
            }
        });
    }

    function getDataHitrate() {
        post('<?= site_url('C_P2TL/getDataHitrate') ?>', {
            tanggal_awal: $('#tanggal_awal').val(),
            tanggal_akhir: $('#tanggal_akhir').val(),
            sortir_hitrate: $('#sortir_hitrate').val()
        }, function (response) {
            $('#hitrateContainer').html(response);
        });
    }

    $('#jenis_akumulasi').on('change', getView);
    $('#tanggal_awal, #tanggal_akhir, #sortir_hitrate').on('change', getDataHitrate);
    $('#btnExportHitrate').on('click', function () {
        var params = new URLSearchParams({
            tanggal_awal: $('#tanggal_awal').val() || '',
            tanggal_akhir: $('#tanggal_akhir').val() || '',
            sortir_hitrate: $('#sortir_hitrate').val() || '*'
        });

        window.location.href = '<?= site_url('C_P2TL/exportDataHitrate') ?>?' + params.toString();
    });

    // Chain initial requests to avoid CSRF token race when token is regenerated per POST.
    getView(function () {
        getDataHitrate();
    });
})();
</script>
<?= $this->endSection() ?>
