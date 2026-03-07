<?php
$params = is_array($params ?? null) ? $params : [];
$rows = is_array($rows ?? null) ? $rows : [];

$buildPercentStyle = static function (float $percent): string {
    $normalized = max(0.0, min($percent, 100.0));
    $hue = (int) round(($normalized / 100.0) * 120.0);
    $textColor = $normalized >= 55 ? '#0b3d0b' : '#4a2200';
    return 'background-color:hsl(' . $hue . ',72%,74%);color:' . $textColor . ';';
};

$totalTarget = 0;
$totalRealisasi = 0;
$percentSum = 0.0;
$totalTargetTua = 0;
$totalTargetRusak = 0;
$totalRealisasiTua = 0;
$totalRealisasiRusak = 0;
$bestUnit = '-';
$worstUnit = '-';
$bestPercent = -1.0;
$worstPercent = 1000000.0;

$chartLabels = [];
$chartTarget = [];
$chartRealisasi = [];
$chartPercent = [];

foreach ($rows as $row) {
    $targetTua = (int) ($row['target_tua'] ?? 0);
    $targetRusak = (int) ($row['target_rusak'] ?? 0);
    $realisasiTua = (int) ($row['real_tua'] ?? 0);
    $realisasiRusak = (int) ($row['real_rusak'] ?? 0);
    $target = (int) ($row['target_total'] ?? 0);
    $realisasi = (int) ($row['real_total'] ?? 0);
    $percent = (float) ($row['percent'] ?? 0);
    $unitName = (string) ($row['unit_name'] ?? '-');

    $totalTargetTua += $targetTua;
    $totalTargetRusak += $targetRusak;
    $totalRealisasiTua += $realisasiTua;
    $totalRealisasiRusak += $realisasiRusak;
    $totalTarget += $target;
    $totalRealisasi += $realisasi;
    $percentSum += $percent;

    if ($percent > $bestPercent) {
        $bestPercent = $percent;
        $bestUnit = $unitName;
    }

    if ($percent < $worstPercent) {
        $worstPercent = $percent;
        $worstUnit = $unitName;
    }

    $chartLabels[] = $unitName;
    $chartTarget[] = $target;
    $chartRealisasi[] = $realisasi;
    $chartPercent[] = round($percent, 2);
}

$avgPercent = count($rows) > 0 ? ($percentSum / count($rows)) : 0.0;
$gapTotal = max($totalTarget - $totalRealisasi, 0);
$uidPercent = $totalTarget > 0 ? (($totalRealisasi / $totalTarget) * 100) : 0.0;

$sortedRows = $rows;
usort($sortedRows, static fn(array $a, array $b) => (($b['percent'] ?? 0) <=> ($a['percent'] ?? 0)));
$topRows = array_slice($sortedRows, 0, 10);

$rankLabels = [];
$rankPercent = [];
foreach ($topRows as $row) {
    $rankLabels[] = (string) ($row['unit_name'] ?? '-');
    $rankPercent[] = round((float) ($row['percent'] ?? 0), 2);
}

$chartPayload = json_encode([
    'labels' => $chartLabels,
    'target' => $chartTarget,
    'realisasi' => $chartRealisasi,
    'percent' => $chartPercent,
    'rankLabels' => $rankLabels,
    'rankPercent' => $rankPercent,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

if (! is_string($chartPayload)) {
    $chartPayload = '{}';
}

$chartPayload = str_replace('</script', '<\\/script', $chartPayload);
?>

<script type="application/json" id="realisasiChartData"><?= $chartPayload ?></script>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card h-100 border-primary">
            <div class="card-body">
                <div class="text-muted small">Total Target</div>
                <div class="fs-4 fw-semibold"><?= esc(number_format($totalTarget, 0, ',', '.')) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-success">
            <div class="card-body">
                <div class="text-muted small">Total Realisasi</div>
                <div class="fs-4 fw-semibold text-success"><?= esc(number_format($totalRealisasi, 0, ',', '.')) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-warning">
            <div class="card-body">
                <div class="text-muted small">Rata-rata Pencapaian</div>
                <div class="fs-4 fw-semibold text-warning"><?= esc(number_format($avgPercent, 2, ',', '.')) ?>%</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-danger">
            <div class="card-body">
                <div class="text-muted small">Sisa Gap</div>
                <div class="fs-4 fw-semibold text-danger"><?= esc(number_format($gapTotal, 0, ',', '.')) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Perbandingan Target vs Realisasi</h5>
                <small class="text-muted">Semua Unit</small>
            </div>
            <div class="card-body">
                <div id="chartRealisasiCompare" style="min-height: 340px;"></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Top 10 Pencapaian Unit</h5>
                <small class="text-muted">Urut tertinggi</small>
            </div>
            <div class="card-body">
                <div id="chartRealisasiRanking" style="min-height: 340px;"></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Unit Pencapaian Tertinggi</div>
                <div class="fw-semibold"><?= esc($bestUnit) ?></div>
                <div class="text-success"><?= esc(number_format(max($bestPercent, 0), 2, ',', '.')) ?>%</div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Unit Pencapaian Terendah</div>
                <div class="fw-semibold"><?= esc($worstUnit) ?></div>
                <div class="text-danger"><?= esc(number_format($worstPercent === 1000000.0 ? 0 : $worstPercent, 2, ',', '.')) ?>%</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Realisasi vs Target</h5>
        <small class="text-muted">Detail per unit</small>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover mb-0 table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 64px;" class="text-center">No</th>
                    <th rowspan="2" class="text-center">UP3</th>
                    <th colspan="3" class="text-center">TARGET <?= esc((string) ($params['tahun'] ?? date('Y'))) ?></th>
                    <th colspan="3" class="text-center">REALISASI <?= esc((string) ($params['tahun'] ?? date('Y'))) ?></th>
                    <th rowspan="2" class="text-center" style="min-width: 110px;">%</th>
                </tr>
                <tr>
                    <th class="text-center">TUA</th>
                    <th class="text-center">RUSAK</th>
                    <th class="text-center">TOTAL</th>
                    <th class="text-center">TUA</th>
                    <th class="text-center">RUSAK</th>
                    <th class="text-center">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows === []): ?>
                    <tr><td colspan="9" class="text-center text-muted">Tidak ada data realisasi.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $idx => $row): ?>
                    <?php
                    $targetTotal = (int) ($row['target_total'] ?? 0);
                    $realTotal = (int) ($row['real_total'] ?? 0);
                    $persen = (float) ($row['percent'] ?? 0);

                    $percentStyle = $buildPercentStyle($persen);
                    ?>
                    <tr>
                        <td class="text-center"><?= esc((string) ($idx + 1)) ?></td>
                        <td><?= esc((string) ($row['unit_name'] ?? '-')) ?></td>
                        <td class="text-end"><?= esc(number_format((int) ($row['target_tua'] ?? 0), 0, ',', '.')) ?></td>
                        <td class="text-end"><?= esc(number_format((int) ($row['target_rusak'] ?? 0), 0, ',', '.')) ?></td>
                        <td class="text-end fw-semibold"><?= esc(number_format($targetTotal, 0, ',', '.')) ?></td>
                        <td class="text-end"><?= esc(number_format((int) ($row['real_tua'] ?? 0), 0, ',', '.')) ?></td>
                        <td class="text-end"><?= esc(number_format((int) ($row['real_rusak'] ?? 0), 0, ',', '.')) ?></td>
                        <td class="text-end fw-semibold"><?= esc(number_format($realTotal, 0, ',', '.')) ?></td>
                        <td class="text-end">
                            <span class="badge" style="min-width:72px;<?= esc($percentStyle) ?>"><?= esc(number_format($persen, 2, ',', '.')) ?>%</span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-center" colspan="2">UID</th>
                    <th class="text-end"><?= esc(number_format($totalTargetTua, 0, ',', '.')) ?></th>
                    <th class="text-end"><?= esc(number_format($totalTargetRusak, 0, ',', '.')) ?></th>
                    <th class="text-end"><?= esc(number_format($totalTarget, 0, ',', '.')) ?></th>
                    <th class="text-end"><?= esc(number_format($totalRealisasiTua, 0, ',', '.')) ?></th>
                    <th class="text-end"><?= esc(number_format($totalRealisasiRusak, 0, ',', '.')) ?></th>
                    <th class="text-end"><?= esc(number_format($totalRealisasi, 0, ',', '.')) ?></th>
                    <th class="text-end">
                        <span class="badge" style="min-width:72px;<?= esc($buildPercentStyle($uidPercent)) ?>"><?= esc(number_format($uidPercent, 2, ',', '.')) ?>%</span>
                    </th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
