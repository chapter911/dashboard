<div class="row g-3 mb-3">
    <div class="col-md-3">
        <label class="form-label">Tanggal</label>
        <input type="date" class="form-control" id="tanggal" onclick="this.showPicker && this.showPicker()" value="<?= date('Y-m-d') ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label">Urut Berdasarkan</label>
        <select class="form-select" id="sortir">
            <option value="*">TANPA SORTIR</option>
            <option value="1">PERSENTASE TERBESAR</option>
            <option value="0">PERSENTASE TERKECIL</option>
        </select>
    </div>
    <div class="col-md-3 d-flex align-items-end">
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
                tanggal: $('#tanggal').val(),
                sortir: $('#sortir').val(),
                jenis_akumulasi: 'HARIAN',
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

    $('#tanggal, #sortir').on('change', getData);
    $('#btnExportDashboardP2tl').on('click', function () {
        var params = new URLSearchParams({
            jenis_akumulasi: 'HARIAN',
            tanggal: $('#tanggal').val() || '',
            sortir: $('#sortir').val() || '*'
        });

        window.location.href = '<?= site_url('C_P2TL/exportData') ?>?' + params.toString();
    });
    getData();
})();
</script>
