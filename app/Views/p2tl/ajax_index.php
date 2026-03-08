<?php
$rows = is_array($data ?? null) ? $data : [];
$akumulasi = strtoupper((string) ($jenis_akumulasi ?? 'BULANAN'));
$totalTarget = 0.0;
$totalRealisasi = 0.0;
$labels = [];
$seriesRealisasi = [];
$seriesSisaTarget = [];

foreach ($rows as $r) {
    $target = (float) ($r['target'] ?? 0);
    $realisasi = (float) ($r['realisasi'] ?? 0);
    $persen = $target > 0 ? ($realisasi / $target * 100) : (float) ($r['persentase'] ?? 0);

    $totalTarget += $target;
    $totalRealisasi += $realisasi;
    $labels[] = strtoupper((string) ($r['unit_name'] ?? '-')) . ' - ' . number_format($persen, 0, '.', '') . '%';
    $seriesRealisasi[] = round($persen, 2);
    $seriesSisaTarget[] = round(max(0, 100 - $persen), 2);
}

$totalPersen = $totalTarget > 0 ? ($totalRealisasi / $totalTarget * 100) : 0;
$labels[] = 'UID JAYA - ' . number_format($totalPersen, 0, '.', '') . '%';
$seriesRealisasi[] = round($totalPersen, 2);
$seriesSisaTarget[] = round(max(0, 100 - $totalPersen), 2);

$chartHeight = max(480, count($labels) * 34);
?>
<style>
#ajaxContainer .table thead th {
    text-align: center;
    vertical-align: middle;
    border: 1px solid #fff !important;
}
#ajaxContainer .table tbody td:nth-child(1),
#ajaxContainer .table tbody td:nth-child(3),
#ajaxContainer .table tbody td:nth-child(4),
#ajaxContainer .table tbody td:nth-child(5) {
    text-align: right;
}
</style>
<div class="row g-3">
    <div class="col-lg-5">
        <table class="table table-bordered table-sm table-striped mb-0">
            <thead>
                <tr>
                    <th>NO</th>
                    <th>UNIT</th>
                    <th>TARGET</th>
                    <th>REALISASI</th>
                    <th>(%)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-3">Tidak ada data.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $i => $r): ?>
                        <?php
                        $target = (float) ($r['target'] ?? 0);
                        $realisasi = (float) ($r['realisasi'] ?? 0);
                        $persen = $target > 0 ? ($realisasi / $target * 100) : (float) ($r['persentase'] ?? 0);
                        ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= esc($r['unit_name'] ?? '-') ?></td>
                            <td><?= number_format($target, 0, ',', '.') ?></td>
                            <td><?= number_format($realisasi, 0, ',', '.') ?></td>
                            <td><?= number_format($persen, 0, '.', '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td><?= count($rows) + 1 ?></td>
                        <td>UID JAYA</td>
                        <td><?= number_format($totalTarget, 0, ',', '.') ?></td>
                        <td><?= number_format($totalRealisasi, 0, ',', '.') ?></td>
                        <td><?= number_format($totalPersen, 0, '.', '') ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="col-lg-7">
        <div style="height: 100%; min-height: <?= (int) $chartHeight ?>px; display: flex; align-items: stretch;">
            <div id="chart_batang_<?= esc(strtolower($akumulasi)) ?>" style="width: 100%; min-height: <?= (int) $chartHeight ?>px;"></div>
        </div>
    </div>
</div>

<script>
(function () {
    function renderChart() {
        var id = 'chart_batang_<?= esc(strtolower($akumulasi)) ?>';
        var el = document.getElementById(id);
        if (!el || typeof window.ApexCharts === 'undefined') {
            return;
        }

        if (el._apexInstance) {
            el._apexInstance.destroy();
        }

        var options = {
            chart: {
                type: 'bar',
                height: <?= (int) $chartHeight ?>,
                stacked: false,
                toolbar: { show: false }
            },
            series: [
                {
                    name: 'Realisasi',
                    data: <?= json_encode($seriesRealisasi) ?>
                },
                {
                    name: 'Sisa Target',
                    data: <?= json_encode($seriesSisaTarget) ?>
                }
            ],
            colors: ['#36A2EB', '#F45B7A'],
            plotOptions: {
                bar: {
                    horizontal: true,
                    barHeight: '90%',
                    borderRadius: 2
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                width: 2,
                colors: ['#f3f3f3']
            },
            grid: {
                show: true,
                borderColor: '#d9d9d9',
                strokeDashArray: 0,
                xaxis: { lines: { show: true } },
                yaxis: { lines: { show: true } }
            },
            xaxis: {
                min: 0,
                max: 100,
                tickAmount: 10,
                categories: <?= json_encode($labels) ?>,
                labels: {
                    formatter: function (val) { return Number(val).toFixed(0); }
                }
            },
            yaxis: {
                labels: {
                    maxWidth: 280,
                    style: {
                        colors: '#666',
                        fontSize: '12px',
                        fontWeight: 600
                    }
                }
            },
            legend: {
                show: true,
                position: 'top',
                horizontalAlign: 'left'
            },
            tooltip: {
                y: {
                    formatter: function (val) { return Number(val).toFixed(2) + '%'; }
                }
            }
        };

        el._apexInstance = new ApexCharts(el, options);
        el._apexInstance.render();
    }

    if (typeof window.ApexCharts === 'undefined') {
        var script = document.createElement('script');
        script.src = '<?= base_url('assets/vendor/libs/apex-charts/apexcharts.js') ?>';
        script.onload = renderChart;
        document.head.appendChild(script);
        return;
    }

    renderChart();
})();
</script>
