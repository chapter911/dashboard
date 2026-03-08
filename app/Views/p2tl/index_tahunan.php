<div class="row g-3 mb-3">
    <div class="col-md-3">
        <label class="form-label">Bulan Awal</label>
        <input type="month" class="form-control" id="bulan_awal" onclick="this.showPicker && this.showPicker()" value="<?= date('Y-01') ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label">Bulan Akhir</label>
        <input type="month" class="form-control" id="bulan_akhir" onclick="this.showPicker && this.showPicker()" value="<?= date('Y-m') ?>">
    </div>
    <div class="col-md-2">
        <label class="form-label">Golongan</label>
        <select class="form-select" id="golongan">
            <option value="*">SEMUA</option>
            <option value="0">TANPA GOLONGAN</option>
            <option value="DKRP">DKRP</option>
            <option value="K2">K2</option>
            <option value="KWH">KWH</option>
            <option value="P1">P1</option>
            <option value="P2">P2</option>
            <option value="P3">P3</option>
            <option value="P4">P4</option>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label">Urut Berdasarkan</label>
        <select class="form-select" id="sortir">
            <option value="*">TANPA SORTIR</option>
            <option value="1">PERSENTASE TERBESAR</option>
            <option value="0">PERSENTASE TERKECIL</option>
        </select>
    </div>
    <div class="col-md-2 d-flex align-items-end">
        <button type="button" class="btn btn-primary w-100" id="btnExportDashboardP2tl">Export Data</button>
    </div>
</div>
<div id="ajaxContainer"></div>

<script>
(function () {
    var csrfState = window.__p2tlCsrf || {
        fieldName: '<?= esc(csrf_token()) ?>',
        token: '<?= esc(csrf_hash()) ?>'
    };

    function applyCsrf(xhr) {
        var fresh = xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null;
        if (fresh) {
            csrfState.token = fresh;
            window.__p2tlCsrf = csrfState;
        }
    }

    function getData() {
        $.ajax({
            type: 'POST',
            url: '<?= site_url('C_P2TL/getDataIndex') ?>',
            data: {
                bulan_awal: $('#bulan_awal').val(),
                bulan_akhir: $('#bulan_akhir').val(),
                golongan: $('#golongan').val(),
                sortir: $('#sortir').val(),
                jenis_akumulasi: 'TAHUNAN',
                [csrfState.fieldName]: csrfState.token
            },
            beforeSend: function () {
                Swal.fire({ title: 'Mohon Tunggu', html: 'Mengambil Data', allowOutsideClick: false, showConfirmButton: false, didOpen: function(){ Swal.showLoading(); } });
            },
            success: function (response, _status, xhr) {
                applyCsrf(xhr);
                Swal.close();
                $('#ajaxContainer').html(response);
            },
            error: function (xhr) {
                applyCsrf(xhr);
                Swal.fire('Error', 'Gagal mengambil data (' + xhr.status + ').', 'error');
            }
        });
    }

    $('#bulan_awal, #bulan_akhir, #golongan, #sortir').on('change', getData);
    $('#btnExportDashboardP2tl').on('click', function () {
        var params = new URLSearchParams({
            jenis_akumulasi: 'TAHUNAN',
            bulan_awal: $('#bulan_awal').val() || '',
            bulan_akhir: $('#bulan_akhir').val() || '',
            golongan: $('#golongan').val() || '*',
            sortir: $('#sortir').val() || '*'
        });

        window.location.href = '<?= site_url('C_P2TL/exportData') ?>?' + params.toString();
    });
    getData();
})();
</script>
