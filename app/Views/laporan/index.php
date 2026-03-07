<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$units = is_array($units ?? null) ? $units : [];
$alasanOptions = is_array($alasanOptions ?? null) ? $alasanOptions : [];
$dayaOptions = is_array($dayaOptions ?? null) ? $dayaOptions : [];
$filters = is_array($filters ?? null) ? $filters : [];
?>

<link rel="stylesheet" href="<?= base_url('assets/vendor/libs/select2/select2.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/vendor/libs/apex-charts/apex-charts.css') ?>">
<style>
    .select2-container--default .select2-selection--multiple {
        overflow-y: auto;
        max-height: calc(2.25rem + 2px);
    }
</style>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Filter Dashboard Laporan</h5>
    </div>
    <div class="card-body">
        <form method="get" action="<?= site_url('C_Laporan/Index') ?>" class="row g-3" id="dashboardFilterForm">
            <?= csrf_field() ?>
            <div class="col-md-3">
                <label class="form-label">UNIT</label>
                <select class="form-select" name="unit">
                    <option value="*">Semua Unit</option>
                    <?php foreach ($units as $unit): ?>
                        <?php $unitId = (string) ($unit['unit_id'] ?? ''); ?>
                        <option value="<?= esc($unitId) ?>" <?= ($filters['unit'] ?? '*') === $unitId ? 'selected' : '' ?>>
                            <?= esc((string) ($unit['unit_name'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Tahun Buat Meter Lama</label>
                <select class="form-select" name="tahun_meter_lama">
                    <option value="*">Semua</option>
                    <option value="0" <?= ($filters['tahun_meter_lama'] ?? '*') === '0' ? 'selected' : '' ?>>Tidak Diketahui</option>
                    <?php for ($year = (int) date('Y'); $year >= 1990; $year--): ?>
                        <option value="<?= esc((string) $year) ?>" <?= ($filters['tahun_meter_lama'] ?? '*') === (string) $year ? 'selected' : '' ?>>
                            <?= esc((string) $year) ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Tarif</label>
                <select class="form-select" name="tarif">
                    <option value="*" <?= ($filters['tarif'] ?? '*') === '*' ? 'selected' : '' ?>>Semua Tarif</option>
                    <option value="pra" <?= ($filters['tarif'] ?? '*') === 'pra' ? 'selected' : '' ?>>PRA</option>
                    <option value="paska" <?= ($filters['tarif'] ?? '*') === 'paska' ? 'selected' : '' ?>>PASKA</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Fasa</label>
                <select class="form-select" name="fasa">
                    <option value="*" <?= ($filters['fasa'] ?? '*') === '*' ? 'selected' : '' ?>>Semua</option>
                    <option value="1 Fasa" <?= ($filters['fasa'] ?? '*') === '1 Fasa' ? 'selected' : '' ?>>1 Fasa</option>
                    <option value="3 Fasa" <?= ($filters['fasa'] ?? '*') === '3 Fasa' ? 'selected' : '' ?>>3 Fasa</option>
                    <?php foreach ($dayaOptions as $daya): ?>
                        <?php $nilaiDaya = (string) ($daya['daya'] ?? ''); ?>
                        <?php if ($nilaiDaya === '') continue; ?>
                        <option value="<?= esc($nilaiDaya) ?>" <?= ($filters['fasa'] ?? '*') === $nilaiDaya ? 'selected' : '' ?>>
                            <?= esc($nilaiDaya) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Alasan Ganti Meter</label>
                <select class="form-select select2 w-100" name="alasan[]" multiple data-placeholder="Seluruh Alasan">
                    <?php foreach ($alasanOptions as $alasan): ?>
                        <?php $val = (string) ($alasan['alasan_ganti_meter'] ?? ''); ?>
                        <?php if ($val === '') continue; ?>
                        <option value="<?= esc($val) ?>" <?= in_array($val, $filters['alasan'] ?? [], true) ? 'selected' : '' ?>>
                            <?= esc($val) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Periode Tanggal Awal</label>
                <input
                    class="form-control"
                    type="date"
                    name="tgl_awal"
                    value="<?= esc((string) (($filters['tgl_awal'] ?? '') !== '' ? $filters['tgl_awal'] : date('Y-m-01'))) ?>"
                >
            </div>

            <div class="col-md-3">
                <label class="form-label">Periode Tanggal Akhir</label>
                <input
                    class="form-control"
                    type="date"
                    name="tgl_akhir"
                    value="<?= esc((string) (($filters['tgl_akhir'] ?? '') !== '' ? $filters['tgl_akhir'] : date('Y-m-d'))) ?>"
                >
            </div>

            <div class="col-md-3">
                <label class="form-label">Urut Berdasarkan</label>
                <select class="form-select" name="sortir">
                    <option value="*" <?= ($filters['sortir'] ?? '*') === '*' ? 'selected' : '' ?>>UP3</option>
                    <option value="1" <?= ($filters['sortir'] ?? '*') === '1' ? 'selected' : '' ?>>TOTAL TERBESAR</option>
                    <option value="0" <?= ($filters['sortir'] ?? '*') === '0' ? 'selected' : '' ?>>TOTAL TERKECIL</option>
                </select>
            </div>

        </form>
    </div>
</div>

<div id="ajaxContainer"></div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/vendor/libs/select2/select2.js') ?>"></script>
<script src="<?= base_url('assets/vendor/libs/apex-charts/apexcharts.js') ?>"></script>
<script>
    $(function () {
        var $form = $('#dashboardFilterForm');
        var $container = $('#ajaxContainer');
        var submitTimer = null;
        window.laporanDashboardCharts = window.laporanDashboardCharts || {
            bar: null,
            pie: null
        };

        var destroyCharts = function () {
            if (window.laporanDashboardCharts.bar) {
                window.laporanDashboardCharts.bar.destroy();
                window.laporanDashboardCharts.bar = null;
            }

            if (window.laporanDashboardCharts.pie) {
                window.laporanDashboardCharts.pie.destroy();
                window.laporanDashboardCharts.pie = null;
            }
        };

        var initDashboardWidgets = function () {
            destroyCharts();

            var dataEl = document.getElementById('dashboardChartData');
            if (! dataEl) {
                return;
            }

            var payload = {};
            try {
                payload = JSON.parse(dataEl.textContent || '{}');
            } catch (e) {
                payload = {};
            }

            var summaryLabels = Array.isArray(payload.summaryLabels) ? payload.summaryLabels : [];
            var summaryTotals = Array.isArray(payload.summaryTotals) ? payload.summaryTotals : [];
            var reasonLabels = Array.isArray(payload.reasonLabels) ? payload.reasonLabels : [];
            var reasonTotals = Array.isArray(payload.reasonTotals) ? payload.reasonTotals : [];

            var sortState = 0; // 0: default, 1: desc, 2: asc
            var originalSummary = summaryLabels.map(function (label, idx) {
                return {
                    label: label,
                    total: Number(summaryTotals[idx] || 0)
                };
            });

            var updateSortButtonIcon = function () {
                var $icon = $('#sortUp3ChartIcon');
                if (! $icon.length) {
                    return;
                }

                if (sortState === 1) {
                    $icon.attr('class', 'ti ti-sort-descending me-1');
                } else if (sortState === 2) {
                    $icon.attr('class', 'ti ti-sort-ascending me-1');
                } else {
                    $icon.attr('class', 'ti ti-arrows-sort me-1');
                }
            };

            var applyBarSort = function () {
                if (! window.laporanDashboardCharts.bar) {
                    return;
                }

                var sorted = originalSummary.slice();
                if (sortState === 1) {
                    sorted.sort(function (a, b) { return b.total - a.total; });
                } else if (sortState === 2) {
                    sorted.sort(function (a, b) { return a.total - b.total; });
                }

                window.laporanDashboardCharts.bar.updateOptions({
                    xaxis: {
                        categories: sorted.map(function (item) { return item.label; }),
                        labels: {
                            rotate: -35
                        }
                    }
                });
                window.laporanDashboardCharts.bar.updateSeries([{
                    name: 'Total',
                    data: sorted.map(function (item) { return item.total; })
                }]);
                updateSortButtonIcon();
            };

            if (summaryLabels.length > 0 && document.querySelector('#chartTotalUp3')) {
                window.laporanDashboardCharts.bar = new ApexCharts(document.querySelector('#chartTotalUp3'), {
                    chart: {
                        type: 'bar',
                        height: 320,
                        toolbar: {
                            show: false
                        }
                    },
                    series: [{
                        name: 'Total',
                        data: summaryTotals
                    }],
                    xaxis: {
                        categories: summaryLabels,
                        labels: {
                            rotate: -35
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Jumlah'
                        }
                    },
                    colors: ['#0a66c2'],
                    dataLabels: {
                        enabled: false
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            columnWidth: '55%'
                        }
                    }
                });
                window.laporanDashboardCharts.bar.render();

                $('#sortUp3ChartBtn').off('click').on('click', function () {
                    sortState = (sortState + 1) % 3;
                    applyBarSort();
                });

                updateSortButtonIcon();
            }

            if (reasonLabels.length > 0 && document.querySelector('#chartDistribusiAlasan')) {
                window.laporanDashboardCharts.pie = new ApexCharts(document.querySelector('#chartDistribusiAlasan'), {
                    chart: {
                        type: 'pie',
                        height: 460
                    },
                    labels: reasonLabels,
                    series: reasonTotals,
                    legend: {
                        position: 'bottom'
                    },
                    stroke: {
                        colors: ['#fff']
                    },
                    dataLabels: {
                        enabled: true
                    }
                });
                window.laporanDashboardCharts.pie.render();
            }
        };

        var loadDashboardData = function () {
            $.ajax({
                type: 'POST',
                url: '<?= site_url('C_Laporan/getDataIndex') ?>',
                data: $form.serialize(),
                beforeSend: function () {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Mohon Tunggu',
                            html: 'Mengambil data dashboard...',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            didOpen: function () {
                                Swal.showLoading();
                            }
                        });
                    }

                    $container.html('<div class="card"><div class="card-body text-center text-muted py-4">Memuat data dashboard...</div></div>');
                },
                success: function (response, _textStatus, jqXHR) {
                    $container.html(response);

                    var freshCsrf = jqXHR ? jqXHR.getResponseHeader('X-CSRF-TOKEN') : null;
                    if (freshCsrf) {
                        $form.find('input[name="<?= esc(csrf_token()) ?>"]').val(freshCsrf);
                    }

                    initDashboardWidgets();
                },
                error: function () {
                    destroyCharts();
                    $container.html('<div class="card"><div class="card-body text-center text-danger py-4">Gagal memuat data dashboard.</div></div>');

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Gagal memuat data dashboard.'
                        });
                    }
                },
                complete: function () {
                    if (typeof Swal !== 'undefined') {
                        Swal.close();
                    }
                }
            });
        };

        var queueSubmit = function () {
            if (! $form.length) {
                return;
            }

            if (submitTimer !== null) {
                window.clearTimeout(submitTimer);
            }

            // Debounce quick successive changes (for select2 multi-select interactions).
            submitTimer = window.setTimeout(function () {
                loadDashboardData();
            }, 250);
        };

        $('.select2').select2({
            width: '100%',
            placeholder: 'Seluruh Alasan'
        });

        $form.on('change', 'select, input[type="date"]', function () {
            queueSubmit();
        });

        loadDashboardData();
    });
</script>
<?= $this->endSection() ?>
