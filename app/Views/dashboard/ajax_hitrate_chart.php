<?php
$data = is_array($data ?? null) ? $data : [];
$maxPersentase = 0.0;

$totalPeriksa = 0;
$totalSesuai = 0;
$totalK1 = 0;
$totalK2 = 0;
$totalP1 = 0;
$totalP2 = 0;
$totalP3 = 0;
$totalP4 = 0;
$totalTemuan = 0;

foreach ($data as $row) {
    $maxPersentase = max($maxPersentase, (float) ($row['persentase'] ?? 0));
    $totalPeriksa += (int) ($row['jumlah_periksa'] ?? 0);
    $totalSesuai += (int) ($row['jumlah_sesuai'] ?? 0);
    $totalK1 += (int) ($row['jumlah_k1'] ?? 0);
    $totalK2 += (int) ($row['jumlah_k2'] ?? 0);
    $totalP1 += (int) ($row['jumlah_p1'] ?? 0);
    $totalP2 += (int) ($row['jumlah_p2'] ?? 0);
    $totalP3 += (int) ($row['jumlah_p3'] ?? 0);
    $totalP4 += (int) ($row['jumlah_p4'] ?? 0);
    $totalTemuan += (int) ($row['jumlah_temuan'] ?? 0);
}
$maxPersentase = ceil($maxPersentase);
?>

<div class="table-responsive mb-3">
    <table class="table table-bordered table-sm align-middle mb-0">
        <thead>
            <tr>
                <th>NO</th>
                <th>UNIT</th>
                <th>PERIKSA</th>
                <th>SESUAI</th>
                <th>P1</th>
                <th>P2</th>
                <th>P3</th>
                <th>P4</th>
                <th>K1</th>
                <th>K2</th>
                <th>TOTAL TEMUAN</th>
                <th>PERSENTASE</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; ?>
            <?php foreach ($data as $row): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= esc((string) ($row['unit_name'] ?? '-')) ?></td>
                    <td class="text-end"><?= number_format((int) ($row['jumlah_periksa'] ?? 0), 0, ',', '.') ?></td>
                    <td class="text-end"><?= number_format((int) ($row['jumlah_sesuai'] ?? 0), 0, ',', '.') ?></td>
                    <td class="text-end"><?= number_format((int) ($row['jumlah_p1'] ?? 0), 0, ',', '.') ?></td>
                    <td class="text-end"><?= number_format((int) ($row['jumlah_p2'] ?? 0), 0, ',', '.') ?></td>
                    <td class="text-end"><?= number_format((int) ($row['jumlah_p3'] ?? 0), 0, ',', '.') ?></td>
                    <td class="text-end"><?= number_format((int) ($row['jumlah_p4'] ?? 0), 0, ',', '.') ?></td>
                    <td class="text-end"><?= number_format((int) ($row['jumlah_k1'] ?? 0), 0, ',', '.') ?></td>
                    <td class="text-end"><?= number_format((int) ($row['jumlah_k2'] ?? 0), 0, ',', '.') ?></td>
                    <td class="text-end"><?= number_format((int) ($row['jumlah_temuan'] ?? 0), 0, ',', '.') ?></td>
                    <td class="text-end"><?= number_format((float) ($row['persentase'] ?? 0), 2, ',', '.') ?> %</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" class="text-center">TOTAL UID</th>
                <th class="text-end"><?= number_format($totalPeriksa, 0, ',', '.') ?></th>
                <th class="text-end"><?= number_format($totalSesuai, 0, ',', '.') ?></th>
                <th class="text-end"><?= number_format($totalP1, 0, ',', '.') ?></th>
                <th class="text-end"><?= number_format($totalP2, 0, ',', '.') ?></th>
                <th class="text-end"><?= number_format($totalP3, 0, ',', '.') ?></th>
                <th class="text-end"><?= number_format($totalP4, 0, ',', '.') ?></th>
                <th class="text-end"><?= number_format($totalK1, 0, ',', '.') ?></th>
                <th class="text-end"><?= number_format($totalK2, 0, ',', '.') ?></th>
                <th class="text-end"><?= number_format($totalTemuan, 0, ',', '.') ?></th>
                <th class="text-end"><?= $totalPeriksa > 0 ? number_format($totalTemuan * 100 / $totalPeriksa, 2, ',', '.') : '0,00' ?> %</th>
            </tr>
        </tfoot>
    </table>
</div>

<div id="chart_hitrate_dashboard" style="min-height:260px;"></div>

<script>
(function () {
    var target = document.querySelector('#chart_hitrate_dashboard');
    if (!target || typeof ApexCharts === 'undefined') {
        return;
    }

    if (window.dashboardHitrateChart) {
        window.dashboardHitrateChart.destroy();
    }

    window.dashboardHitrateChart = new ApexCharts(target, {
        chart: {
            type: 'bar',
            height: 260,
            toolbar: { show: false }
        },
        plotOptions: {
            bar: {
                horizontal: true,
                dataLabels: { position: 'top' }
            }
        },
        series: [{
            name: 'Hitrate',
            data: [
                <?php foreach ($data as $row): ?>
                <?= number_format((float) ($row['persentase'] ?? 0), 4, '.', '') ?>,
                <?php endforeach; ?>
            ]
        }],
        xaxis: {
            categories: [
                <?php foreach ($data as $row): ?>
                '<?= esc((string) ($row['unit_name'] ?? '-')) ?>',
                <?php endforeach; ?>
            ],
            max: <?= (int) $maxPersentase + 5 ?>
        },
        dataLabels: {
            enabled: true,
            formatter: function (val) { return Number(val).toFixed(2) + '%'; }
        },
        colors: ['#36a2eb'],
        title: {
            text: 'Hitrate P2TL',
            align: 'left'
        }
    });

    window.dashboardHitrateChart.render();
})();
</script>
