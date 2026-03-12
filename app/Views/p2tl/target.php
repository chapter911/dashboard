<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<style>
#tableTarget thead th {
    text-align: center;
    vertical-align: middle;
    border: 1px solid #fff !important;
}
#tableTarget tbody td:nth-child(1),
#tableTarget tbody td:nth-child(3),
#tableTarget tbody td:nth-child(4),
#tableTarget tbody td:nth-child(5) {
    text-align: right;
}
</style>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><?= esc($pageHeading ?? 'Target P2TL') ?></h5>
    </div>
    <div class="card-body">
        <?php if (session('error')): ?>
            <div class="alert alert-danger"><?= esc(session('error')) ?></div>
        <?php endif; ?>
        <?php if (session('success')): ?>
            <div class="alert alert-success"><?= esc(session('success')) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= site_url('C_P2TL/updateTarget') ?>" id="formTarget">
            <?= csrf_field() ?>
            <div class="row g-3 mb-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Tahun</label>
                    <?php $selectedYear = (int) ($currentYear ?? date('Y')); ?>
                    <select class="form-select" id="tahun" name="tahun">
                        <?php for ($y = 2027; $y >= 2020; $y--): ?>
                            <option value="<?= $y ?>" <?= $y === $selectedYear ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-striped" id="tableTarget">
                    <thead>
                        <tr>
                            <th>UNIT ID</th>
                            <th>UNIT</th>
                            <th>TAHUN</th>
                            <th>TARGET TAHUNAN</th>
                            <th>TARGET BULANAN</th>
                            <th>HARIAN</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Simpan Target</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalHarian" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Target Harian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="<?= site_url('C_P2TL/updateTargetHarian') ?>" id="formTargetHarian">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <input type="hidden" name="unit_id" id="target_harian_unit_id">
                    <input type="hidden" name="tahun" id="target_harian_tahun">
                    <div class="row g-3" id="targetHarianFields">
                        <?php
                        $monthNames = [
                            1 => 'Januari',
                            2 => 'Februari',
                            3 => 'Maret',
                            4 => 'April',
                            5 => 'Mei',
                            6 => 'Juni',
                            7 => 'Juli',
                            8 => 'Agustus',
                            9 => 'September',
                            10 => 'Oktober',
                            11 => 'November',
                            12 => 'Desember',
                        ];
                        ?>
                        <?php foreach ($monthNames as $idx => $name): ?>
                            <div class="col-md-4">
                                <label class="form-label"><?= esc($name) ?></label>
                                <input type="text" class="form-control form-control-sm harian-input" name="harian[]" data-month="<?= $idx ?>" placeholder="0">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
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

    function parseID(value) {
        if (value === null || value === undefined || value === '') return 0;
        var num = Number(String(value).replace(/\./g, '').replace(',', '.'));
        return isNaN(num) ? 0 : num;
    }

    function formatID(value) {
        return Number(value || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
    }

    function loadTarget() {
        $.ajax({
            type: 'POST',
            url: '<?= site_url('C_P2TL/ajaxTarget') ?>',
            data: {
                tahun: $('#tahun').val(),
                [csrfFieldName]: csrfToken
            },
            success: function (res, _status, xhr) {
                applyCsrf(xhr.getResponseHeader('X-CSRF-TOKEN'));

                var rows = (res && res.data) ? res.data : [];
                var html = '';
                rows.forEach(function (r) {
                    html += '<tr>' +
                        '<td>' + r[0] + '<input type="hidden" name="unit_id[]" value="' + r[0] + '"></td>' +
                        '<td>' + r[1] + '</td>' +
                        '<td>' + r[2] + '</td>' +
                        '<td><input type="text" class="form-control form-control-sm target-input" name="target[]" value="' + r[3] + '"></td>' +
                        '<td class="target-bulanan">' + r[4] + '</td>' +
                        '<td class="text-center"><button type="button" class="btn btn-sm btn-info btn-harian" data-unit="' + r[0] + '" data-tahun="' + r[2] + '">INPUT</button></td>' +
                        '</tr>';
                });
                $('#tableTarget tbody').html(html);
            },
            beforeSend: function () {
                Swal.fire({ title: 'Mohon Tunggu', html: 'Memuat data target', allowOutsideClick: false, showConfirmButton: false, didOpen: function(){ Swal.showLoading(); } });
            },
            error: function (xhr) {
                applyCsrf(xhr.getResponseHeader('X-CSRF-TOKEN'));
                Swal.fire('Error', 'Gagal memuat target (' + (xhr && xhr.status ? xhr.status : 'unknown') + ').', 'error');
            },
            complete: function () {
                Swal.close();
            }
        });
    }

    $('#tahun').on('change', loadTarget);

    $('#tableTarget').on('input', '.target-input', function () {
        var raw = $(this).val();
        var tahunan = parseID(raw);
        $(this).closest('tr').find('.target-bulanan').text(formatID(tahunan / 12));
    });

    function openTargetHarian(unitId, tahun) {
        $('#target_harian_unit_id').val(unitId);
        $('#target_harian_tahun').val(tahun);
        $('#targetHarianFields .harian-input').val('');

        $.ajax({
            type: 'POST',
            url: '<?= site_url('C_P2TL/ajaxTargetHarian') ?>',
            data: {
                unit_id: unitId,
                tahun: tahun,
                [csrfFieldName]: csrfToken
            },
            beforeSend: function () {
                Swal.fire({ title: 'Mohon Tunggu', html: 'Memuat target harian', allowOutsideClick: false, showConfirmButton: false, didOpen: function(){ Swal.showLoading(); } });
            },
            success: function (res, _status, xhr) {
                applyCsrf(xhr.getResponseHeader('X-CSRF-TOKEN'));

                var rows = (res && res.data) ? res.data : [];
                rows.forEach(function (r, idx) {
                    var val = r && r.target_harian !== null && r.target_harian !== undefined
                        ? formatID(r.target_harian)
                        : '';
                    $('#targetHarianFields .harian-input').eq(idx).val(val);
                });

                Swal.close();
                $('#modalHarian').modal('show');
            },
            error: function (xhr) {
                applyCsrf(xhr.getResponseHeader('X-CSRF-TOKEN'));
                Swal.fire('Error', 'Gagal memuat target harian (' + (xhr && xhr.status ? xhr.status : 'unknown') + ').', 'error');
            },
            complete: function () {
                Swal.close();
            }
        });
    }

    $('#tableTarget').on('click', '.btn-harian', function () {
        var unitId = $(this).data('unit');
        var tahun = $(this).data('tahun');
        openTargetHarian(unitId, tahun);
    });

    $('#targetHarianFields').on('input', '.harian-input', function () {
        var value = String($(this).val() || '').replace(/[^\d,\.]/g, '');
        var num = parseID(value);
        $(this).val(value === '' ? '' : formatID(num));
    });

    $('#formTarget').on('submit', function () {
        applyCsrf(csrfToken);
        Swal.fire({ title: 'Mohon Tunggu', html: 'Menyimpan target', allowOutsideClick: false, showConfirmButton: false, didOpen: function(){ Swal.showLoading(); } });
    });

    $('#formTargetHarian').on('submit', function () {
        applyCsrf(csrfToken);
        Swal.fire({ title: 'Mohon Tunggu', html: 'Menyimpan target harian', allowOutsideClick: false, showConfirmButton: false, didOpen: function(){ Swal.showLoading(); } });
    });

    loadTarget();
})();
</script>
<?= $this->endSection() ?>
