<?php
$rows = is_array($data ?? null) ? $data : [];
$categories = [];
$series = [];
foreach ($rows as $r) {
    $total = (float) ($r['jumlah_periksa'] ?? 0);
    $temuan = (float) ($r['jumlah_temuan'] ?? 0);
    $hr = $total > 0 ? ($temuan / $total * 100) : (float) ($r['persentase'] ?? 0);
    $categories[] = (string) ($r['unit_name'] ?? '-');
    $series[] = round($hr, 2);
}
?>
<script>
(function () {
    var el = document.getElementById('chartHitrate');
    if (!el) return;
    if (el._chart) {
        el._chart.destroy();
    }

    var options = {
        chart: { type: 'bar', height: 340, toolbar: { show: true } },
        series: [{ name: 'Hitrate (%)', data: <?= json_encode($series) ?> }],
        xaxis: { categories: <?= json_encode($categories) ?> },
        yaxis: { max: 100, min: 0, title: { text: 'Persentase' } },
        dataLabels: { enabled: true, formatter: function (v) { return v.toFixed(2) + '%'; } },
        plotOptions: { bar: { distributed: true, borderRadius: 4 } },
        tooltip: { y: { formatter: function (v) { return v.toFixed(2) + '%'; } } }
    };

    el._chart = new ApexCharts(el, options);
    el._chart.render();
})();
</script>
