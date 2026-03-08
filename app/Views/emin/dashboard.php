<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$userGroupId = (int) ($userGroupId ?? 0);
$units = is_array($units ?? null) ? $units : [];
$currentYear = (int) ($currentYear ?? date('Y'));
?>

<link rel="stylesheet" href="<?= base_url('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') ?>">

<div class="card">
    <div class="card-header"><h5 class="mb-0">Dashboard EMIN</h5></div>
    <div class="card-body">
        <form id="dashboardEminFilterForm" class="row g-3 mb-3" method="post" action="<?= site_url('C_Emin/dashboard/data') ?>">
            <?= csrf_field() ?>
            <div class="col-md-3">
                <label class="form-label">Tahun</label>
                <select class="form-select" id="filter_year" name="year">
                    <?php for ($year = $currentYear + 1; $year >= $currentYear - 5; $year--): ?>
                        <option value="<?= esc((string) $year) ?>" <?= $year === $currentYear ? 'selected' : '' ?>><?= esc((string) $year) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <?php if ($userGroupId === 1): ?>
                <div class="col-md-3">
                    <label class="form-label">Unit</label>
                    <select class="form-select" id="filter_unit" name="unit_id">
                        <option value="">Semua Unit</option>
                        <?php foreach ($units as $unit): ?>
                            <option value="<?= esc((string) ((int) ($unit['unit_id'] ?? 0))) ?>"><?= esc((string) ($unit['unit_name'] ?? '')) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <input type="hidden" id="filter_unit" name="unit_id" value="">
            <?php endif; ?>
        </form>

        <div class="table-responsive text-nowrap">
            <table id="dashboard-emin-table" class="table table-bordered table-striped" style="width:100%;">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Unit</th>
                        <th>Gol Tarif</th>
                        <th>Daya</th>
                        <th>Jan</th>
                        <th>Feb</th>
                        <th>Mar</th>
                        <th>Apr</th>
                        <th>Mei</th>
                        <th>Jun</th>
                        <th>Jul</th>
                        <th>Agu</th>
                        <th>Sep</th>
                        <th>Okt</th>
                        <th>Nov</th>
                        <th>Des</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') ?>"></script>
<script>
$(function () {
    var csrfFieldName = '<?= esc(csrf_token()) ?>';
    var $form = $('#dashboardEminFilterForm');

    function currentCsrf() {
        return $form.find('input[name="' + csrfFieldName + '"]').val();
    }

    function applyCsrf(token) {
        if (!token) return;
        $('input[name="' + csrfFieldName + '"]').val(token);
    }

    var table = $('#dashboard-emin-table').DataTable({
        processing: true,
        searching: false,
        scrollX: true,
        pageLength: 25,
        order: [[1, 'asc'], [2, 'asc'], [3, 'asc']],
        ajax: {
            url: '<?= site_url('C_Emin/dashboard/data') ?>',
            type: 'POST',
            data: function (d) {
                d.year = $('#filter_year').val();
                d.unit_id = $('#filter_unit').val();
                d[csrfFieldName] = currentCsrf();
            },
            dataSrc: function (json) { return json.data || []; },
            complete: function (xhr) { applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null); }
        },
        columnDefs: [
            { targets: [4,5,6,7,8,9,10,11,12,13,14,15], className: 'text-end' }
        ]
    });

    $('#filter_year, #filter_unit').on('change', function () {
        table.ajax.reload();
    });
});
</script>
<?= $this->endSection() ?>
