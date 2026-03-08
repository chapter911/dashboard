<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$userGroupId = (int) ($userGroupId ?? 0);
$units = is_array($units ?? null) ? $units : [];
$golonganTarif = is_array($golonganTarif ?? null) ? $golonganTarif : [];
$currentYear = (int) ($currentYear ?? date('Y'));
?>

<link rel="stylesheet" href="<?= base_url('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/vendor/libs/apex-charts/apex-charts.css') ?>">

<div class="card">
    <div class="card-header"><h5 class="mb-0">Dashboard TUL</h5></div>
    <div class="card-body">
        <form id="dashboardTulFilterForm" class="row g-3 mb-3" method="post" action="<?= site_url('C_TUL/dashboard/data') ?>">
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
            <div class="col-md-3">
                <label class="form-label">Golongan Tarif</label>
                <select class="form-select" id="filter_gol_tarif" name="gol_tarif">
                    <option value="">Semua Golongan</option>
                    <?php foreach ($golonganTarif as $gol): ?>
                        <option value="<?= esc((string) ($gol['gol_tarif'] ?? '')) ?>"><?= esc((string) ($gol['gol_tarif'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <div id="tulChart" style="min-height:400px;" class="mb-4"></div>

        <div class="table-responsive text-nowrap">
            <table id="dashboard-tul-table" class="table table-bordered table-striped" style="width:100%;">
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
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">Total</th>
                        <th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/vendor/libs/apex-charts/apexcharts.js') ?>"></script>
<script src="<?= base_url('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') ?>"></script>
<script>
$(function () {
    var csrfFieldName = '<?= esc(csrf_token()) ?>';
    var $form = $('#dashboardTulFilterForm');

    function currentCsrf() {
        return $form.find('input[name="' + csrfFieldName + '"]').val();
    }

    function applyCsrf(token) {
        if (!token) return;
        $('input[name="' + csrfFieldName + '"]').val(token);
    }

    var tulChart = new ApexCharts(document.querySelector('#tulChart'), {
        chart: { type: 'line', height: 400, toolbar: { show: true }, zoom: { enabled: false } },
        series: [{ name: 'Total Pemakaian', data: [] }],
        xaxis: { categories: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'] },
        dataLabels: { enabled: true, formatter: function (v) { return Number(v || 0) === 0 ? '' : Number(v).toLocaleString('id-ID'); } },
        stroke: { curve: 'straight' },
        yaxis: { labels: { formatter: function (v) { return Number(v).toLocaleString('id-ID'); } } },
        title: { text: 'Grafik Total Pemakaian Jumlah', align: 'left' }
    });
    tulChart.render();

    function parseLocaleNumber(value) {
        if (typeof value === 'number') {
            return value;
        }

        if (typeof value !== 'string') {
            return 0;
        }

        var normalized = value.replace(/\./g, '').replace(',', '.');
        var parsed = Number(normalized);
        return Number.isFinite(parsed) ? parsed : 0;
    }

    function updateChartFromRows(rows) {
        var monthlyTotals = Array(12).fill(0);
        var safeRows = Array.isArray(rows) ? rows : [];

        safeRows.forEach(function (row) {
            // Row format: [no, unit, gol, daya, jan..des]
            for (var monthIndex = 0; monthIndex < 12; monthIndex++) {
                monthlyTotals[monthIndex] += parseLocaleNumber(row[monthIndex + 4]);
            }
        });

        tulChart.updateSeries([{ name: 'Total Pemakaian', data: monthlyTotals }]);
    }

    var table = $('#dashboard-tul-table').DataTable({
        processing: true,
        searching: false,
        scrollX: true,
        pageLength: 25,
        ajax: {
            url: '<?= site_url('C_TUL/dashboard/data') ?>',
            type: 'POST',
            data: function (d) {
                d.year = $('#filter_year').val();
                d.unit_id = $('#filter_unit').val();
                d.gol_tarif = $('#filter_gol_tarif').val();
                d[csrfFieldName] = currentCsrf();
            },
            dataSrc: function (json) {
                var rows = json.data || [];
                updateChartFromRows(rows);
                return rows;
            },
            complete: function (xhr) { applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null); }
        },
        footerCallback: function () {
            var api = this.api();
            function intVal(i) {
                if (typeof i === 'string') {
                    return Number(i.replace(/\./g, '').replace(',', '.')) || 0;
                }
                return Number(i || 0);
            }
            for (var col = 4; col <= 15; col++) {
                var total = api.column(col).data().reduce(function (a, b) { return intVal(a) + intVal(b); }, 0);
                $(api.column(col).footer()).html(Number(total).toLocaleString('id-ID'));
            }
        }
    });

    var isReloading = false;

    function reloadAll() {
        if (isReloading) {
            return;
        }

        isReloading = true;

        table.ajax.reload(function () {
            isReloading = false;
        }, false);
    }

    $('#filter_year, #filter_unit, #filter_gol_tarif').on('change', reloadAll);
});
</script>
<?= $this->endSection() ?>
