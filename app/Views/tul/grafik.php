<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$userGroupId = (int) ($userGroupId ?? 0);
$units = is_array($units ?? null) ? $units : [];
$currentYear = (int) ($currentYear ?? date('Y'));
?>

<link rel="stylesheet" href="<?= base_url('assets/vendor/libs/apex-charts/apex-charts.css') ?>">

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Perbandingan Penjualan per Jenis Tegangan</h5>
    </div>
    <div class="card-body">
        <form id="grafikTulFilterForm" class="row g-3 mb-4" method="post">
            <?= csrf_field() ?>
            <div class="col-md-2">
                <label class="form-label">Tipe Periode</label>
                <select class="form-select" id="filter_period_type" name="period_type">
                    <option value="yearly">Tahunan</option>
                    <option value="monthly">Bulanan</option>
                </select>
            </div>
            <div class="col-md-2" id="year_left_container">
                <label class="form-label">Tahun (Kiri)</label>
                <select class="form-select" id="filter_year_left" name="year_left">
                    <?php for ($year = $currentYear + 1; $year >= $currentYear - 5; $year--): ?>
                        <option value="<?= esc((string) $year) ?>" <?= $year === ($currentYear - 1) ? 'selected' : '' ?>><?= esc((string) $year) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2" id="year_right_container">
                <label class="form-label">Tahun (Kanan)</label>
                <select class="form-select" id="filter_year_right" name="year_right">
                    <?php for ($year = $currentYear + 1; $year >= $currentYear - 5; $year--): ?>
                        <option value="<?= esc((string) $year) ?>" <?= $year === $currentYear ? 'selected' : '' ?>><?= esc((string) $year) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2" id="month_left_container" style="display:none;">
                <label class="form-label">Periode (Kiri)</label>
                <input type="month" class="form-control" id="filter_month_left" value="<?= esc(date('Y-m', strtotime('-1 month'))) ?>">
            </div>
            <div class="col-md-2" id="month_right_container" style="display:none;">
                <label class="form-label">Periode (Kanan)</label>
                <input type="month" class="form-control" id="filter_month_right" value="<?= esc(date('Y-m')) ?>">
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
                <input type="hidden" id="filter_unit" value="">
            <?php endif; ?>
        </form>

        <div class="row mb-4">
            <div class="col-md-6"><div id="pieChartLeft" style="min-height:380px;"></div></div>
            <div class="col-md-6"><div id="pieChartRight" style="min-height:380px;"></div></div>
        </div>

        <h6 class="mb-3">kWh Jual</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="kwhJualTable">
                <thead>
                    <tr>
                        <th>Segmen</th>
                        <th id="header_left">kWh Jual <span id="period_left_label"></span></th>
                        <th id="header_right">kWh Jual <span id="period_right_label"></span></th>
                        <th>Growth (%)</th>
                    </tr>
                </thead>
                <tbody id="kwhJualTableBody"></tbody>
                <tfoot>
                    <tr class="fw-semibold">
                        <td>Total</td>
                        <td id="total_left"></td>
                        <td id="total_right"></td>
                        <td id="total_growth"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/vendor/libs/apex-charts/apexcharts.js') ?>"></script>
<script>
$(function () {
    var csrfFieldName = '<?= esc(csrf_token()) ?>';
    var $form = $('#grafikTulFilterForm');

    function currentCsrf() {
        return $form.find('input[name="' + csrfFieldName + '"]').val();
    }

    function applyCsrf(token) {
        if (!token) return;
        $('input[name="' + csrfFieldName + '"]').val(token);
    }

    function getPeriodValues() {
        var periodType = $('#filter_period_type').val();
        if (periodType === 'monthly') {
            return {
                period_type: periodType,
                period_left: $('#filter_month_left').val(),
                period_right: $('#filter_month_right').val()
            };
        }

        return {
            period_type: periodType,
            period_left: $('#filter_year_left').val(),
            period_right: $('#filter_year_right').val()
        };
    }

    function formatPeriodLabel(period, type) {
        if (type === 'monthly') {
            var d = new Date((period || '') + '-01');
            if (!isNaN(d.getTime())) {
                return d.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' });
            }
        }
        return period;
    }

    function togglePeriodInputs() {
        var monthly = $('#filter_period_type').val() === 'monthly';
        $('#year_left_container, #year_right_container').toggle(!monthly);
        $('#month_left_container, #month_right_container').toggle(monthly);
    }

    var pieOptions = {
        series: [],
        chart: { type: 'pie', height: 380 },
        labels: [],
        colors: ['#008FFB', '#00E396', '#FEB019'],
        legend: { position: 'bottom' },
        dataLabels: {
            enabled: true,
            formatter: function (val, opts) {
                return (opts.w.config.labels[opts.seriesIndex] || '') + ': ' + val.toFixed(2) + '%';
            }
        },
        tooltip: {
            y: {
                formatter: function (val) { return Number(val).toLocaleString('id-ID'); }
            }
        }
    };

    var leftChart = new ApexCharts(document.querySelector('#pieChartLeft'), Object.assign({}, pieOptions, { title: { text: '', align: 'center' } }));
    var rightChart = new ApexCharts(document.querySelector('#pieChartRight'), Object.assign({}, pieOptions, { title: { text: '', align: 'center' } }));
    leftChart.render();
    rightChart.render();

    function updatePieCharts() {
        var periods = getPeriodValues();

        return $.ajax({
            url: '<?= site_url('C_TUL/grafik/pie') ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                period_left: periods.period_left,
                period_right: periods.period_right,
                period_type: periods.period_type,
                unit_id: $('#filter_unit').val(),
                [csrfFieldName]: currentCsrf()
            },
            success: function (response, _status, xhr) {
                applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
                var leftLabel = formatPeriodLabel(periods.period_left, periods.period_type);
                var rightLabel = formatPeriodLabel(periods.period_right, periods.period_type);

                leftChart.updateOptions({ labels: response.left.categories || [], title: { text: 'Penjualan ' + leftLabel, align: 'center' } });
                leftChart.updateSeries(response.left.series || []);

                rightChart.updateOptions({ labels: response.right.categories || [], title: { text: 'Penjualan ' + rightLabel, align: 'center' } });
                rightChart.updateSeries(response.right.series || []);
            },
            error: function (xhr) {
                applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
            }
        });
    }

    function updateKwhTable() {
        var periods = getPeriodValues();

        return $.ajax({
            url: '<?= site_url('C_TUL/grafik/kwh') ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                period_left: periods.period_left,
                period_right: periods.period_right,
                period_type: periods.period_type,
                unit_id: $('#filter_unit').val(),
                [csrfFieldName]: currentCsrf()
            },
            success: function (response, _status, xhr) {
                applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);

                $('#period_left_label').text(formatPeriodLabel(response.period_left, response.period_type));
                $('#period_right_label').text(formatPeriodLabel(response.period_right, response.period_type));

                var $body = $('#kwhJualTableBody').empty();
                (response.data || []).forEach(function (row) {
                    var growth = Number(row.growth || 0);
                    var klass = growth >= 0 ? 'text-success' : 'text-danger';
                    var sign = growth >= 0 ? '+' : '';

                    $body.append(
                        '<tr>' +
                            '<td>' + (row.segmen || '-') + '</td>' +
                            '<td class="text-end">' + Number(row.kwh_left || 0).toLocaleString('id-ID') + '</td>' +
                            '<td class="text-end">' + Number(row.kwh_right || 0).toLocaleString('id-ID') + '</td>' +
                            '<td class="text-end ' + klass + '">' + sign + growth.toFixed(2) + '%</td>' +
                        '</tr>'
                    );
                });

                var totalGrowth = Number((response.total || {}).growth || 0);
                var totalClass = totalGrowth >= 0 ? 'text-success' : 'text-danger';
                var totalSign = totalGrowth >= 0 ? '+' : '';

                $('#total_left').text(Number((response.total || {}).kwh_left || 0).toLocaleString('id-ID'));
                $('#total_right').text(Number((response.total || {}).kwh_right || 0).toLocaleString('id-ID'));
                $('#total_growth').html('<span class="' + totalClass + '">' + totalSign + totalGrowth.toFixed(2) + '%</span>');
            },
            error: function (xhr) {
                applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
            }
        });
    }

    var isRefreshing = false;

    function refreshAll() {
        if (isRefreshing) {
            return;
        }

        isRefreshing = true;

        var pieRequest = updatePieCharts();
        if (pieRequest && typeof pieRequest.always === 'function') {
            pieRequest.always(function () {
                var kwhRequest = updateKwhTable();
                if (kwhRequest && typeof kwhRequest.always === 'function') {
                    kwhRequest.always(function () {
                        isRefreshing = false;
                    });
                    return;
                }

                isRefreshing = false;
            });

            return;
        }

        isRefreshing = false;
    }

    $('#filter_period_type').on('change', function () {
        togglePeriodInputs();
        refreshAll();
    });
    $('#filter_year_left, #filter_year_right, #filter_month_left, #filter_month_right, #filter_unit').on('change', refreshAll);

    togglePeriodInputs();
    refreshAll();
});
</script>
<?= $this->endSection() ?>
