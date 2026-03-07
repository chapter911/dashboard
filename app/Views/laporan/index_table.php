<?php
$summaryRows = is_array($summaryRows ?? null) ? $summaryRows : [];
$reasonRows = is_array($reasonRows ?? null) ? $reasonRows : [];

$totalUsia0 = 0;
$totalUsia6 = 0;
$totalUsia11 = 0;
$totalUsia16 = 0;
$totalBlank = 0;
$totalAll = 0;

$summaryLabels = [];
$summaryTotals = [];
foreach ($summaryRows as $summaryRow) {
    $summaryLabels[] = (string) ($summaryRow['unit_name'] ?? '');
    $summaryTotals[] = (int) ($summaryRow['total'] ?? 0);
}

$reasonLabels = [];
$reasonTotals = [];
foreach ($reasonRows as $reasonRow) {
    $label = trim((string) ($reasonRow['alasan_ganti_meter'] ?? ''));
    $total = (int) ($reasonRow['total'] ?? 0);
    if ($label === '') {
        continue;
    }

    $reasonLabels[] = $label;
    $reasonTotals[] = $total;
}

$chartDataJson = json_encode([
    'summaryLabels' => $summaryLabels,
    'summaryTotals' => $summaryTotals,
    'reasonLabels' => $reasonLabels,
    'reasonTotals' => $reasonTotals,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

if (! is_string($chartDataJson)) {
    $chartDataJson = '{}';
}

$chartDataJson = str_replace('</script', '<\\/script', $chartDataJson);
?>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Ringkasan Kumulatif Usia Meter</h5>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-bordered table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th rowspan="2" class="text-center">NO</th>
                    <th rowspan="2" class="text-center">UP3</th>
                    <th colspan="6" class="text-center">KUMULATIF ALASAN PENGGANTIAN<br>USIA kWh METER</th>
                </tr>
                <tr>
                    <th class="text-center">&lt; 5th</th>
                    <th class="text-center">6 ~ 10th</th>
                    <th class="text-center">11 ~ 15th</th>
                    <th class="text-center">&gt; 15th</th>
                    <th class="text-center">BLANK</th>
                    <th class="text-center">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($summaryRows as $idx => $row): ?>
                    <?php
                    $totalUsia0 += (int) ($row['usia0'] ?? 0);
                    $totalUsia6 += (int) ($row['usia6'] ?? 0);
                    $totalUsia11 += (int) ($row['usia11'] ?? 0);
                    $totalUsia16 += (int) ($row['usia16'] ?? 0);
                    $totalBlank += (int) ($row['usia_blank'] ?? 0);
                    $totalAll += (int) ($row['total'] ?? 0);
                    ?>
                    <tr>
                        <td><?= esc((string) ($idx + 1)) ?></td>
                        <td><?= esc((string) ($row['unit_name'] ?? '')) ?></td>
                        <td class="text-end"><?= esc((string) ($row['usia0'] ?? 0)) ?></td>
                        <td class="text-end"><?= esc((string) ($row['usia6'] ?? 0)) ?></td>
                        <td class="text-end"><?= esc((string) ($row['usia11'] ?? 0)) ?></td>
                        <td class="text-end"><?= esc((string) ($row['usia16'] ?? 0)) ?></td>
                        <td class="text-end"><?= esc((string) ($row['usia_blank'] ?? 0)) ?></td>
                        <td class="text-end fw-semibold"><?= esc((string) ($row['total'] ?? 0)) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2">TOTAL UID</th>
                    <th class="text-end"><?= esc((string) $totalUsia0) ?></th>
                    <th class="text-end"><?= esc((string) $totalUsia6) ?></th>
                    <th class="text-end"><?= esc((string) $totalUsia11) ?></th>
                    <th class="text-end"><?= esc((string) $totalUsia16) ?></th>
                    <th class="text-end"><?= esc((string) $totalBlank) ?></th>
                    <th class="text-end"><?= esc((string) $totalAll) ?></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php if ($summaryLabels === [] && $reasonLabels === []): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Grafik Dashboard</h5>
        </div>
        <div class="card-body">
            <div class="text-center text-muted py-4">Belum ada data grafik.</div>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Total Penggantian per UP3</h5>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="sortUp3ChartBtn">
                        <i class="ti ti-arrows-sort me-1" id="sortUp3ChartIcon"></i>
                        Urutkan Chart
                    </button>
                </div>
                <div class="card-body">
                    <div id="chartTotalUp3" style="min-height: 320px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Distribusi Alasan Ganti Meter</h5>
                </div>
                <div class="card-body">
                    <div id="chartDistribusiAlasan" style="min-height: 460px;"></div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script type="application/json" id="dashboardChartData"><?= $chartDataJson ?></script>
