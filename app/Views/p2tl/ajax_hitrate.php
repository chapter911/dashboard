<?php
$rows = is_array($data ?? null) ? $data : [];

$labels = [];
$series = [];

$totalPeriksa = 0;
$totalSesuai = 0;
$totalK1 = 0;
$totalK2 = 0;
$totalP1 = 0;
$totalP2 = 0;
$totalP3 = 0;
$totalP4 = 0;
$totalTemuan = 0;

foreach ($rows as $r) {
    $periksa = (int) ($r['jumlah_periksa'] ?? 0);
    $sesuai = (int) ($r['jumlah_sesuai'] ?? 0);
    // Source data from existing table can be shifted; remap explicitly to match displayed header order.
    $p1 = (int) ($r['jumlah_k1'] ?? 0);
    $p2 = (int) ($r['jumlah_k2'] ?? 0);
    $p3 = (int) ($r['jumlah_p1'] ?? 0);
    $p4 = (int) ($r['jumlah_p2'] ?? 0);
    $k1 = (int) ($r['jumlah_p3'] ?? 0);
    $k2 = (int) ($r['jumlah_p4'] ?? 0);
    $temuan = (int) ($r['jumlah_temuan'] ?? 0);
    $persentase = $periksa > 0 ? (($temuan * 100) / $periksa) : (float) ($r['persentase'] ?? 0);

    $labels[] = strtoupper((string) ($r['unit_name'] ?? '-')) . ' - ' . number_format($persentase, 2, ',', '.') . '%';
    $series[] = round($persentase, 4);

    $totalPeriksa += $periksa;
    $totalSesuai += $sesuai;
    $totalK1 += $k1;
    $totalK2 += $k2;
    $totalP1 += $p1;
    $totalP2 += $p2;
    $totalP3 += $p3;
    $totalP4 += $p4;
    $totalTemuan += $temuan;
}

$totalPersentase = $totalPeriksa > 0 ? (($totalTemuan * 100) / $totalPeriksa) : 0;
?>
<style>
#hitrateContainer .table thead th {
    text-align: center;
    vertical-align: middle;
    border: 1px solid #fff !important;
}
#hitrateContainer .table tbody td:nth-child(1),
#hitrateContainer .table tbody td:nth-child(3),
#hitrateContainer .table tbody td:nth-child(4),
#hitrateContainer .table tbody td:nth-child(5),
#hitrateContainer .table tbody td:nth-child(6),
#hitrateContainer .table tbody td:nth-child(7),
#hitrateContainer .table tbody td:nth-child(8),
#hitrateContainer .table tbody td:nth-child(9),
#hitrateContainer .table tbody td:nth-child(10),
#hitrateContainer .table tbody td:nth-child(11),
#hitrateContainer .table tbody td:nth-child(12),
#hitrateContainer .table tbody td:nth-child(13) {
    text-align: right;
}
</style>
<div class="table-responsive">
    <table class="table table-sm table-striped mb-0">
        <thead>
            <tr>
                <th rowspan="2">NO</th>
                <th rowspan="2">UNIT</th>
                <th rowspan="2">JUMLAH PERIKSA</th>
                <th colspan="7">TEMUAN</th>
                <th rowspan="2">TOTAL TEMUAN</th>
                <th rowspan="2">PERSENTASE</th>
            </tr>
            <tr>
                <th>SESUAI</th>
                <th>P1</th>
                <th>P2</th>
                <th>P3</th>
                <th>P4</th>
                <th>K1</th>
                <th>K2</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr>
                    <td colspan="12" class="text-center py-3">Tidak ada data.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($rows as $i => $r): ?>
                    <?php
                    $periksa = (int) ($r['jumlah_periksa'] ?? 0);
                    $values = [
                        'sesuai' => (int) ($r['jumlah_sesuai'] ?? 0),
                        'p1' => (int) ($r['jumlah_k1'] ?? 0),
                        'p2' => (int) ($r['jumlah_k2'] ?? 0),
                        'p3' => (int) ($r['jumlah_p1'] ?? 0),
                        'p4' => (int) ($r['jumlah_p2'] ?? 0),
                        'k1' => (int) ($r['jumlah_p3'] ?? 0),
                        'k2' => (int) ($r['jumlah_p4'] ?? 0),
                    ];
                    $temuan = (int) ($r['jumlah_temuan'] ?? 0);
                    $hr = $periksa > 0 ? (($temuan * 100) / $periksa) : (float) ($r['persentase'] ?? 0);
                    ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= esc($r['unit_name'] ?? '-') ?></td>
                        <td><?= number_format($periksa, 0, ',', '.') ?></td>
                        <td><?= number_format($values['sesuai'], 0, ',', '.') ?></td>
                        <td><?= number_format($values['p1'], 0, ',', '.') ?></td>
                        <td><?= number_format($values['p2'], 0, ',', '.') ?></td>
                        <td><?= number_format($values['p3'], 0, ',', '.') ?></td>
                        <td><?= number_format($values['p4'], 0, ',', '.') ?></td>
                        <td><?= number_format($values['k1'], 0, ',', '.') ?></td>
                        <td><?= number_format($values['k2'], 0, ',', '.') ?></td>
                        <td><?= number_format($temuan, 0, ',', '.') ?></td>
                        <td><?= number_format($hr, 2, ',', '.') ?> %</td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <?php if (!empty($rows)): ?>
            <tfoot>
                <tr class="fw-semibold">
                    <th colspan="2" class="text-center">TOTAL UID</th>
                    <th><?= number_format($totalPeriksa, 0, ',', '.') ?></th>
                    <th><?= number_format($totalSesuai, 0, ',', '.') ?></th>
                    <th><?= number_format($totalP1, 0, ',', '.') ?></th>
                    <th><?= number_format($totalP2, 0, ',', '.') ?></th>
                    <th><?= number_format($totalP3, 0, ',', '.') ?></th>
                    <th><?= number_format($totalP4, 0, ',', '.') ?></th>
                    <th><?= number_format($totalK1, 0, ',', '.') ?></th>
                    <th><?= number_format($totalK2, 0, ',', '.') ?></th>
                    <th><?= number_format($totalTemuan, 0, ',', '.') ?></th>
                    <th><?= number_format($totalPersentase, 2, ',', '.') ?> %</th>
                </tr>
            </tfoot>
        <?php endif; ?>
    </table>
</div>

<div class="mt-3">
    <div id="chartHitrate" style="min-height: 340px;"></div>
</div>

<script>
(function () {
    function renderChart() {
        var el = document.getElementById('chartHitrate');
        if (!el || typeof window.ApexCharts === 'undefined') {
            return;
        }

        if (el._apexInstance) {
            el._apexInstance.destroy();
        }

        var chartHeight = Math.max(420, <?= count($rows) ?> * 34);

        var chart = new ApexCharts(el, {
            series: [{
                name: 'Jumlah Hit Rate',
                data: <?= json_encode($series) ?>
            }],
            chart: {
                type: 'bar',
                height: chartHeight,
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    barHeight: '72%'
                }
            },
            dataLabels: {
                enabled: false
            },
            colors: ['#36A2EB'],
            stroke: {
                width: 1,
                colors: ['#fff']
            },
            xaxis: {
                min: 0,
                max: 100,
                tickAmount: 10,
                categories: <?= json_encode($labels) ?>,
                labels: {
                    formatter: function (val) {
                        return Number(val).toFixed(0);
                    }
                }
            },
            grid: {
                show: true,
                borderColor: '#d9d9d9',
                strokeDashArray: 0
            },
            legend: {
                show: true,
                position: 'top',
                horizontalAlign: 'center'
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return Number(val).toFixed(2) + '%';
                    }
                }
            }
        });

        chart.render();
        el._apexInstance = chart;
    }

    if (typeof window.ApexCharts === 'undefined') {
        var existing = document.querySelector('script[data-role="apexcharts-lib"]');
        if (existing) {
            existing.addEventListener('load', renderChart, { once: true });
            return;
        }

        var script = document.createElement('script');
        script.src = '<?= base_url('assets/vendor/libs/apex-charts/apexcharts.js') ?>';
        script.setAttribute('data-role', 'apexcharts-lib');
        script.onload = renderChart;
        document.head.appendChild(script);
        return;
    }

    renderChart();
})();
</script>
