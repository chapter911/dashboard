<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$userGroupId = (int) ($userGroupId ?? 0);
$selectedUnitId = (int) ($selectedUnitId ?? 0);
$selectedUnitName = (string) ($selectedUnitName ?? '');
?>
<style>
#tableTargetOperasi thead th {
    text-align: center;
    vertical-align: middle;
    border: 1px solid #fff !important;
}
#tableTargetOperasi tbody td.num {
    text-align: right;
}
</style>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><?= esc($pageHeading ?? 'Target Operasi P2TL') ?></h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalImportTargetOperasi">Import Target Operasi</button>
    </div>
    <div class="card-body">
        <?php if (session('error')): ?><div class="alert alert-danger"><?= esc(session('error')) ?></div><?php endif; ?>
        <?php if (session('success')): ?><div class="alert alert-success"><?= esc(session('success')) ?></div><?php endif; ?>

        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label">Unit</label>
                <?php if ($userGroupId === 1): ?>
                    <select id="unit" class="form-select">
                        <option value="*">SEMUA UNIT</option>
                        <?php foreach (($units ?? []) as $u): ?>
                            <option value="<?= (int) ($u['unit_id'] ?? 0) ?>"><?= esc($u['unit_name'] ?? '-') ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <select id="unit" class="form-select" disabled>
                        <option value="<?= $selectedUnitId ?>"><?= esc($selectedUnitName !== '' ? $selectedUnitName : (string) $selectedUnitId) ?></option>
                    </select>
                <?php endif; ?>
            </div>
        </div>

        <div class="table-responsive">
            <table id="tableTargetOperasi" class="table table-sm table-striped w-100">
                <thead>
                    <tr>
                        <th>IDPEL</th>
                        <th>NAMA</th>
                        <th>TARIF</th>
                        <th>DAYA</th>
                        <th>GARDU</th>
                        <th>TIANG</th>
                        <th>UNIT</th>
                        <th>JAM NYALA</th>
                        <th>JENIS TO</th>
                        <th>MAP</th>
                        <th>SUB DLPD</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalImportTargetOperasi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Target Operasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="<?= site_url('C_P2TL/importTargetOperasi') ?>" enctype="multipart/form-data" id="formImportTargetOperasi">
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
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    var csrfFieldName = '<?= esc(csrf_token()) ?>';
    var csrfToken = '<?= esc(csrf_hash()) ?>';

    var table = $('#tableTargetOperasi').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        ajax: {
            url: '<?= site_url('C_P2TL/dataTargetOperasi') ?>',
            type: 'POST',
            data: function (d) {
                d.unit = $('#unit').val() || '<?= $selectedUnitId > 0 ? $selectedUnitId : '*' ?>';
                d[csrfFieldName] = csrfToken;
            },
            beforeSend: function () {
                Swal.fire({ title: 'Mohon Tunggu', html: 'Memuat data target operasi', allowOutsideClick: false, showConfirmButton: false, didOpen: function(){ Swal.showLoading(); } });
            },
            complete: function (xhr) {
                var fresh = xhr.getResponseHeader('X-CSRF-TOKEN');
                if (fresh) csrfToken = fresh;
                Swal.close();
            }
        },
        columnDefs: [{ targets: [3, 7], className: 'num' }]
    });

    $('#unit').on('change', function () {
        table.ajax.reload();
    });

    $('#formImportTargetOperasi').on('submit', function () {
        Swal.fire({ title: 'Mohon Tunggu', html: 'Proses import berlangsung', allowOutsideClick: false, showConfirmButton: false, didOpen: function(){ Swal.showLoading(); } });
    });
})();
</script>
<?= $this->endSection() ?>
