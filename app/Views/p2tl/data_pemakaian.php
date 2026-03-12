<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$userGroupId = (int) ($userGroupId ?? 0);
$units = is_array($units ?? null) ? $units : [];
$currentYear = (int) ($currentYear ?? date('Y'));
$selectedUnitId = (int) ($selectedUnitId ?? 0);
$selectedUnitName = (string) ($selectedUnitName ?? '');
?>
<link rel="stylesheet" href="<?= base_url('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') ?>">
<style>
#tableDataPemakaianBulanan thead th {
    background: #0f66b6;
    color: #fff;
    border-color: #0f66b6;
    text-align: center;
    white-space: nowrap;
}
#tableDataPemakaianBulanan tbody td {
    background: #f4f5f7;
    white-space: nowrap;
}
#tableDataPemakaianBulanan tbody td:nth-child(1),
#tableDataPemakaianBulanan tbody td:nth-child(2),
#tableDataPemakaianBulanan tbody td:nth-child(4) {
    text-align: left;
}
#tableDataPemakaianBulanan tbody td:nth-child(n+3) {
    text-align: right;
}
</style>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><?= esc($pageHeading ?? 'Data Pemakaian P2TL') ?></h5>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-2">
                <label class="form-label">Tahun</label>
                <select class="form-select" id="tahun">
                    <?php for ($y = $currentYear + 1; $y >= $currentYear - 5; $y--): ?>
                        <option value="<?= $y ?>" <?= $y === $currentYear ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Unit</label>
                <?php if ($userGroupId === 1): ?>
                    <select class="form-select" id="unit">
                        <option value="*">SEMUA UNIT</option>
                        <?php foreach ($units as $u): ?>
                            <option value="<?= (int) ($u['unit_id'] ?? 0) ?>"><?= esc((string) ($u['unit_name'] ?? '-')) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <select class="form-select" id="unit" disabled>
                        <option value="<?= $selectedUnitId ?>"><?= esc($selectedUnitName !== '' ? $selectedUnitName : (string) $selectedUnitId) ?></option>
                    </select>
                <?php endif; ?>
            </div>
        </div>

        <div class="table-responsive">
            <table id="tableDataPemakaianBulanan" class="table table-bordered w-100" data-skip-global-number-format="1">
                <thead>
                    <tr>
                        <th>IDPEL</th>
                        <th>TARIF</th>
                        <th>DAYA</th>
                        <th>TAHUN</th>
                        <th>JANUARI</th>
                        <th>FEBRUARI</th>
                        <th>MARET</th>
                        <th>APRIL</th>
                        <th>MEI</th>
                        <th>JUNI</th>
                        <th>JULI</th>
                        <th>AGUSTUS</th>
                        <th>SEPTEMBER</th>
                        <th>OKTOBER</th>
                        <th>NOVEMBER</th>
                        <th>DESEMBER</th>
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
(function () {
    var csrfFieldName = '<?= esc(csrf_token()) ?>';
    var csrfToken = '<?= esc(csrf_hash()) ?>';
    var intFormatter = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });

    function formatIntegerCell(data) {
        if (data === null || data === undefined) {
            return '0';
        }

        var raw = String(data).trim();
        if (raw === '') {
            return '0';
        }

        var normalized = raw
            .replace(/\./g, '')
            .replace(/,/g, '.')
            .replace(/[^0-9.-]/g, '');
        var value = Math.round(Number(normalized));

        if (!Number.isFinite(value)) {
            return '0';
        }

        return intFormatter.format(value);
    }

    function applyCsrf(token) {
        if (!token) {
            return;
        }

        csrfToken = token;
        $('input[name="' + csrfFieldName + '"]').val(token);
    }

    var table = $('#tableDataPemakaianBulanan').DataTable({
        processing: true,
        searching: false,
        paging: true,
        pageLength: 10,
        lengthChange: false,
        scrollX: true,
        order: [[0, 'asc']],
        ajax: {
            url: '<?= site_url('C_P2TL/ajaxDashboardPemakaian') ?>',
            type: 'POST',
            data: function (d) {
                d.tahun = $('#tahun').val();
                d.unit = $('#unit').val();
                d[csrfFieldName] = csrfToken;
            },
            dataSrc: function (json) {
                return Array.isArray(json.data) ? json.data : [];
            },
            complete: function (xhr) {
                applyCsrf(xhr.getResponseHeader('X-CSRF-TOKEN'));
            }
        },
        columnDefs: [
            { className: 'text-end', targets: [2,4,5,6,7,8,9,10,11,12,13,14,15] },
            { render: function (data) { return formatIntegerCell(data); }, targets: [2,4,5,6,7,8,9,10,11,12,13,14,15] }
        ]
    });

    $('#tahun, #unit').on('change', function () {
        table.ajax.reload();
    });
})();
</script>
<?= $this->endSection() ?>
