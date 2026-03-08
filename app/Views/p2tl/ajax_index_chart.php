<?php
$rows = is_array($data ?? null) ? $data : [];
$categories = [];
$seriesTarget = [];
$seriesRealisasi = [];
$seriesPersentase = [];
foreach ($rows as $r) {
    $target = (float) ($r['target'] ?? 0);
    $realisasi = (float) ($r['realisasi'] ?? 0);
    $persen = $target > 0 ? ($realisasi / $target * 100) : (float) ($r['persentase'] ?? 0);
    $categories[] = (string) ($r['unit_name'] ?? '-');
    $seriesTarget[] = round($target, 2);
    $seriesRealisasi[] = round($realisasi, 2);
    $seriesPersentase[] = round($persen, 2);
}
?>
<script>
(function () {
    var el = document.getElementById('chartIndex');
    if (!el) return;
    if (el._chart) {
        el._chart.destroy();
    }

    var options = {
        chart: { type: 'bar', height: 340, toolbar: { show: true } },
        series: [
            { name: 'Target', data: <?= json_encode($seriesTarget) ?> },
            { name: 'Realisasi', data: <?= json_encode($seriesRealisasi) ?> },
            { name: '%', data: <?= json_encode($seriesPersentase) ?> }
        ],
        xaxis: { categories: <?= json_encode($categories) ?> },
        yaxis: [
            { title: { text: 'Jumlah' } },
            { opposite: true, title: { text: 'Persentase' } }
        ],
        dataLabels: { enabled: false },
        stroke: { width: [0, 0, 2] },
        plotOptions: { bar: { columnWidth: '45%' } }
    };

    el._chart = new ApexCharts(el, options);
    el._chart.render();
})();
</script>
