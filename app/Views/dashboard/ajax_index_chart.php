<?php
$data = is_array($data ?? null) ? $data : [];
$totalTarget = 0.0;
$totalRealisasi = 0.0;

foreach ($data as $row) {
    $totalTarget += (float) ($row['target'] ?? 0);
    $totalRealisasi += (float) ($row['realisasi'] ?? 0);
}

$realisasiPct = $totalTarget > 0 ? ($totalRealisasi * 100 / $totalTarget) : 0;
$sisaPct = max(0, 100 - $realisasiPct);
$topRows = array_slice($data, 0, 3);
$bottomRows = array_slice(array_reverse($data), 0, 3);
$hasData = count($data) > 0;
?>

<div class="row g-3">
    <div class="col-lg-6">
        <div id="chart_doughnut_dashboard" style="min-height:280px;"></div>
    </div>
    <div class="col-lg-6">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead>
                    <tr>
                        <th>Top Performance</th>
                        <th>Bottom Performance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($i = 0; $i < 3; $i++): ?>
                        <?php
                        $top = $topRows[$i] ?? null;
                        $bot = $bottomRows[$i] ?? null;
                        ?>
                        <tr>
                            <td style="background:#ccffc3;">
                                <?php if (is_array($top)): ?>
                                    <?php
                                    $targetTop = (float) ($top['target'] ?? 0);
                                    $realisasiTop = (float) ($top['realisasi'] ?? 0);
                                    $pctTop = $targetTop > 0 ? ($realisasiTop * 100 / $targetTop) : 0;
                                    ?>
                                    <?= esc((string) ($top['unit_name'] ?? '-')) ?> - <?= number_format($pctTop, 0, ',', '.') ?>%
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td style="background:#ffc3c3;">
                                <?php if (is_array($bot)): ?>
                                    <?php
                                    $targetBot = (float) ($bot['target'] ?? 0);
                                    $realisasiBot = (float) ($bot['realisasi'] ?? 0);
                                    $pctBot = $targetBot > 0 ? ($realisasiBot * 100 / $targetBot) : 0;
                                    ?>
                                    <?= esc((string) ($bot['unit_name'] ?? '-')) ?> - <?= number_format($pctBot, 0, ',', '.') ?>%
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function () {
    var target = document.querySelector('#chart_doughnut_dashboard');
    if (!target || typeof ApexCharts === 'undefined') {
        return;
    }

    if (window.dashboardDonutChart) {
        window.dashboardDonutChart.destroy();
    }

    window.dashboardDonutChart = new ApexCharts(target, {
        chart: {
            type: 'donut',
            height: 280,
        },
        labels: [
            'Realisasi <?= number_format($realisasiPct, 0, ',', '.') ?>%',
            'Sisa Target <?= number_format($sisaPct, 0, ',', '.') ?>%'
        ],
        series: [<?= $hasData ? number_format($realisasiPct, 2, '.', '') : '0' ?>, <?= $hasData ? number_format($sisaPct, 2, '.', '') : '0' ?>],
        colors: ['#36a2eb', '#ff6384'],
        dataLabels: {
            enabled: true,
            formatter: function (val) {
                return Number(val).toFixed(1) + '%';
            }
        },
        legend: {
            position: 'bottom'
        }
    });

    window.dashboardDonutChart.render();
})();
</script>
