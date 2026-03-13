<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$p2tlTahunan = is_array($p2tlTahunan ?? null) ? $p2tlTahunan : [];
$p2tlBulanan = is_array($p2tlBulanan ?? null) ? $p2tlBulanan : [];
$temuanTahunan = is_array($temuanTahunan ?? null) ? $temuanTahunan : [];
$hitrate = is_array($hitrate ?? null) ? $hitrate : [];
$performance = is_array($performance ?? null) ? $performance : [];
$currentYear = (int) ($currentYear ?? date('Y'));
$susutBulanan = (float) ($susutBulanan ?? 0);
$susutKumulatif = (float) ($susutKumulatif ?? 0);

$pelangganP = 0;
$kwhP = 0;
$pelangganK = 0;
$kwhK = 0;
foreach ($temuanTahunan as $row) {
    $gol = strtoupper((string) ($row['gol'] ?? ''));
    $jumlah = (int) ($row['jumlah_pelanggan'] ?? 0);
    $kwh = (float) ($row['total_kwh'] ?? 0);

    if (in_array($gol, ['P1', 'P2', 'P3', 'P4'], true)) {
        $pelangganP += $jumlah;
        $kwhP += $kwh;
    } elseif (in_array($gol, ['K1', 'K2', 'KWH'], true)) {
        $pelangganK += $jumlah;
        $kwhK += $kwh;
    }
}

$sumTahunanTarget = array_sum(array_map(static fn(array $r): float => (float) ($r['target'] ?? 0), $p2tlTahunan));
$sumTahunanRealisasi = array_sum(array_map(static fn(array $r): float => (float) ($r['realisasi'] ?? 0), $p2tlTahunan));
$sumBulananTarget = array_sum(array_map(static fn(array $r): float => (float) ($r['target'] ?? 0), $p2tlBulanan));
$sumBulananRealisasi = array_sum(array_map(static fn(array $r): float => (float) ($r['realisasi'] ?? 0), $p2tlBulanan));

$sumPeriksa = array_sum(array_map(static fn(array $r): int => (int) ($r['jumlah_periksa'] ?? 0), $hitrate));
$sumSesuai = array_sum(array_map(static fn(array $r): int => (int) ($r['jumlah_sesuai'] ?? 0), $hitrate));
$sumTemuan = array_sum(array_map(static fn(array $r): int => (int) ($r['jumlah_temuan'] ?? 0), $hitrate));

$avgHitrate = count($hitrate) > 0
    ? (array_sum(array_map(static fn(array $r): float => (float) ($r['persentase'] ?? 0), $hitrate)) / count($hitrate))
    : 0;

$compliancePct = $sumPeriksa > 0 ? ($sumSesuai * 100 / $sumPeriksa) : 0;
$p2tlPercent = $sumTahunanTarget > 0 ? ($sumTahunanRealisasi * 100 / $sumTahunanTarget) : 0;
$bulananPercent = $sumBulananTarget > 0 ? ($sumBulananRealisasi * 100 / $sumBulananTarget) : 0;

$topPerformance = array_slice($performance, 0, 3);
$bottomPerformance = array_slice($performance, -3);

$hitrateSorted = $hitrate;
usort($hitrateSorted, static fn(array $a, array $b): int => ((float) ($b['persentase'] ?? 0)) <=> ((float) ($a['persentase'] ?? 0)));
$hitrateTop = array_slice($hitrateSorted, 0, 5);
?>

<link rel="stylesheet" href="<?= base_url('assets/vendor/libs/apex-charts/apex-charts.css') ?>">

<style>
    .kpi-value {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1.1;
    }

    .kpi-value-primary {
        color: rgb(var(--app-primary-rgb));
    }

    .kpi-caption {
        font-size: 0.85rem;
        color: #8a8d93;
    }

    .metric-pill {
        border: 1px solid rgba(var(--app-primary-rgb), 0.25);
        border-radius: 0.75rem;
        padding: 0.75rem 0.9rem;
        background: linear-gradient(180deg, rgba(var(--app-primary-rgb), 0.09), rgba(var(--app-primary-rgb), 0.02));
    }

    .mini-table td,
    .mini-table th {
        padding: 0.55rem 0.6rem;
    }

    .unit-performance-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .unit-performance-item {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        margin-bottom: 1rem;
    }

    .unit-performance-item:last-child {
        margin-bottom: 0;
    }

    .unit-performance-icon {
        width: 2.2rem;
        height: 2.2rem;
        border-radius: 0.5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .unit-performance-icon.top {
        background: rgba(46, 204, 113, 0.18);
        color: #2ecc71;
    }

    .unit-performance-icon.bottom {
        background: rgba(255, 77, 79, 0.14);
        color: #ff4d4f;
    }

    .unit-performance-name {
        margin: 0;
        font-size: 1rem;
        font-weight: 600;
        line-height: 1.2;
    }

    .unit-performance-meta {
        color: #6f7482;
        font-size: 0.875rem;
    }

    .unit-performance-pct {
        font-weight: 700;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
    }

    .unit-performance-pct.top {
        color: #2ecc71;
    }

    .unit-performance-pct.bottom {
        color: #ff4d4f;
    }
</style>

<div class="row g-4 mb-3">
    <div class="col-xl-3 col-md-6">
        <div class="card h-100">
            <div class="card-header pb-2">
                <h6 class="mb-0">Susut Bulanan</h6>
                <small class="text-muted"><?= date('F Y') ?></small>
            </div>
            <div class="card-body">
                <div class="kpi-value kpi-value-primary"><?= number_format($susutBulanan, 1, ',', '.') ?>%</div>
                <div class="kpi-caption mt-1">Kinerja susut bulan berjalan</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card h-100">
            <div class="card-header pb-2">
                <h6 class="mb-0">Susut Kumulatif</h6>
                <small class="text-muted">YTD <?= esc((string) $currentYear) ?></small>
            </div>
            <div class="card-body">
                <div class="kpi-value kpi-value-primary"><?= number_format($susutKumulatif, 1, ',', '.') ?>%</div>
                <div class="kpi-caption mt-1">Akumulasi sampai bulan ini</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card h-100">
            <div class="card-header pb-2">
                <h6 class="mb-0">Temuan Pelanggaran</h6>
                <small class="text-muted">Kategori P1-P4</small>
            </div>
            <div class="card-body">
                <div class="fw-bold fs-5 mb-1"><?= number_format($pelangganP, 0, ',', '.') ?> Pelanggan</div>
                <div class="text-muted"><?= number_format($kwhP, 0, ',', '.') ?> kWh (<?= ($kwhP + $kwhK) > 0 ? number_format($kwhP * 100 / ($kwhP + $kwhK), 0, ',', '.') : '0' ?>%)</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card h-100">
            <div class="card-header pb-2">
                <h6 class="mb-0">Temuan Kelainan</h6>
                <small class="text-muted">Kategori K1-K2</small>
            </div>
            <div class="card-body">
                <div class="fw-bold fs-5 mb-1"><?= number_format($pelangganK, 0, ',', '.') ?> Pelanggan</div>
                <div class="text-muted"><?= number_format($kwhK, 0, ',', '.') ?> kWh (<?= ($kwhP + $kwhK) > 0 ? number_format($kwhK * 100 / ($kwhP + $kwhK), 0, ',', '.') : '0' ?>%)</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6"><div class="metric-pill"><div class="kpi-caption">P2TL YTD</div><div class="fw-bold fs-5"><?= number_format($p2tlPercent, 2, ',', '.') ?>%</div></div></div>
                    <div class="col-lg-3 col-md-6"><div class="metric-pill"><div class="kpi-caption">P2TL Bulanan</div><div class="fw-bold fs-5"><?= number_format($bulananPercent, 2, ',', '.') ?>%</div></div></div>
                    <div class="col-lg-3 col-md-6"><div class="metric-pill"><div class="kpi-caption">Compliance Hitrate</div><div class="fw-bold fs-5"><?= number_format($compliancePct, 2, ',', '.') ?>%</div></div></div>
                    <div class="col-lg-3 col-md-6"><div class="metric-pill"><div class="kpi-caption">Rata-rata Hitrate</div><div class="fw-bold fs-5"><?= number_format($avgHitrate, 2, ',', '.') ?>%</div></div></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-3">
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Progress P2TL</h5>
                <small class="text-muted">Realisasi vs target tahun <?= esc((string) $currentYear) ?></small>
            </div>
            <div class="card-body row">
                <div class="col-lg-5 col-md-12 mb-3 mb-lg-0">
                    <div class="mb-2"><strong>Total Realisasi</strong><div><?= number_format($sumTahunanRealisasi, 0, ',', '.') ?></div></div>
                    <div class="mb-2"><strong>Target Tahunan</strong><div><?= number_format($sumTahunanTarget, 0, ',', '.') ?></div></div>
                    <div class="mb-2"><strong>Realisasi Bulan Ini</strong><div><?= number_format($sumBulananRealisasi, 0, ',', '.') ?></div></div>
                    <div><strong>Target Bulan Ini</strong><div><?= number_format($sumBulananTarget, 0, ',', '.') ?></div></div>
                </div>
                <div class="col-lg-7 col-md-12">
                    <div id="chart_ganti_meter"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Hitrate</h5>
                <small class="text-muted">Performa pemeriksaan tahun <?= esc((string) $currentYear) ?></small>
            </div>
            <div class="card-body row">
                <div class="col-lg-5 col-md-12 mb-3 mb-lg-0">
                    <div class="mb-2"><strong>Total Periksa</strong><div><?= number_format($sumPeriksa, 0, ',', '.') ?></div></div>
                    <div class="mb-2"><strong>Jumlah Sesuai</strong><div><?= number_format($sumSesuai, 0, ',', '.') ?></div></div>
                    <div class="mb-2"><strong>Jumlah Temuan</strong><div><?= number_format($sumTemuan, 0, ',', '.') ?></div></div>
                    <div><strong>Avg Hitrate</strong><div><?= number_format($avgHitrate, 2, ',', '.') ?>%</div></div>
                </div>
                <div class="col-lg-7 col-md-12">
                    <div id="chart_hit_rate"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Unit Performance</h5>
                <small class="text-muted">Performa Realisasi Tahunan</small>
            </div>
            <div class="card-body">
                <ul class="unit-performance-list">
                    <?php foreach ($topPerformance as $row): ?>
                        <?php
                        $target = (float) ($row['target'] ?? 0);
                        $realisasi = (float) ($row['realisasi'] ?? 0);
                        $pct = $target > 0 ? ($realisasi * 100 / $target) : 0;
                        ?>
                        <li class="unit-performance-item">
                            <div class="unit-performance-icon top"><i class="ti ti-bolt"></i></div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="unit-performance-name"><?= esc((string) ($row['unit_name'] ?? '-')) ?></h6>
                                    <small class="unit-performance-meta"><?= number_format($realisasi, 0, ',', '.') ?> / <?= number_format($target, 0, ',', '.') ?></small>
                                </div>
                                <div class="unit-performance-pct top"><i class="ti ti-chevron-up"></i><?= number_format($pct, 0, ',', '.') ?>%</div>
                            </div>
                        </li>
                    <?php endforeach; ?>

                    <?php foreach ($bottomPerformance as $row): ?>
                        <?php
                        $target = (float) ($row['target'] ?? 0);
                        $realisasi = (float) ($row['realisasi'] ?? 0);
                        $pct = $target > 0 ? ($realisasi * 100 / $target) : 0;
                        ?>
                        <li class="unit-performance-item">
                            <div class="unit-performance-icon bottom"><i class="ti ti-bolt"></i></div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="unit-performance-name"><?= esc((string) ($row['unit_name'] ?? '-')) ?></h6>
                                    <small class="unit-performance-meta"><?= number_format($realisasi, 0, ',', '.') ?> / <?= number_format($target, 0, ',', '.') ?></small>
                                </div>
                                <div class="unit-performance-pct bottom"><i class="ti ti-chevron-down"></i><?= number_format($pct, 0, ',', '.') ?>%</div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Top Hitrate Unit</h5>
                <span class="badge bg-label-primary">Top 5</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mini-table mb-0">
                    <thead>
                        <tr>
                            <th>Unit</th>
                            <th class="text-end">Periksa</th>
                            <th class="text-end">Temuan</th>
                            <th class="text-end">Hitrate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($hitrateTop === []): ?>
                            <tr><td colspan="4" class="text-center text-muted">Belum ada data hitrate.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($hitrateTop as $row): ?>
                            <tr>
                                <td><?= esc((string) ($row['unit_name'] ?? '-')) ?></td>
                                <td class="text-end"><?= number_format((float) ($row['jumlah_periksa'] ?? 0), 0, ',', '.') ?></td>
                                <td class="text-end"><?= number_format((float) ($row['jumlah_temuan'] ?? 0), 0, ',', '.') ?></td>
                                <td class="text-end fw-semibold"><?= number_format((float) ($row['persentase'] ?? 0), 2, ',', '.') ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Komposisi Temuan</h5>
                <span class="badge bg-label-primary">Pelanggaran vs Kelainan</span>
            </div>
            <div class="card-body">
                <?php $totalTemuanKwh = $kwhP + $kwhK; ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Pelanggaran</span>
                        <strong><?= $totalTemuanKwh > 0 ? number_format($kwhP * 100 / $totalTemuanKwh, 1, ',', '.') : '0,0' ?>%</strong>
                    </div>
                    <div class="progress" style="height:8px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $totalTemuanKwh > 0 ? number_format($kwhP * 100 / $totalTemuanKwh, 2, '.', '') : '0' ?>%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Kelainan</span>
                        <strong><?= $totalTemuanKwh > 0 ? number_format($kwhK * 100 / $totalTemuanKwh, 1, ',', '.') : '0,0' ?>%</strong>
                    </div>
                    <div class="progress" style="height:8px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $totalTemuanKwh > 0 ? number_format($kwhK * 100 / $totalTemuanKwh, 2, '.', '') : '0' ?>%"></div>
                    </div>
                </div>
                <div class="small text-muted mt-3">
                    Total energi temuan: <strong><?= number_format($totalTemuanKwh, 0, ',', '.') ?> kWh</strong>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/vendor/libs/apex-charts/apexcharts.js') ?>"></script>
<script>
(function () {
    if (typeof ApexCharts === 'undefined' || typeof config === 'undefined') {
        return;
    }

    var cardColor = config.colors.cardColor;
    var labelColor = config.colors.textMuted;
    var headingColor = config.colors.headingColor;

    var p2tlPercent = <?= number_format($p2tlPercent, 2, '.', '') ?>;
    var hitratePercent = <?= number_format($avgHitrate, 2, '.', '') ?>;

    var chartP2tl = new ApexCharts(document.querySelector('#chart_ganti_meter'), {
        series: [p2tlPercent],
        labels: [''],
        chart: { height: 300, type: 'radialBar' },
        plotOptions: {
            radialBar: {
                offsetY: 6,
                startAngle: -130,
                endAngle: 130,
                hollow: { size: '62%' },
                track: { background: cardColor, strokeWidth: '100%' },
                dataLabels: {
                    name: { offsetY: -18, color: labelColor, fontSize: '12px', fontWeight: '400', fontFamily: 'Public Sans' },
                    value: { offsetY: 6, color: headingColor, fontSize: '34px', fontWeight: '600', fontFamily: 'Public Sans' }
                }
            }
        },
        colors: ['#e74c3c'],
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'dark',
                shadeIntensity: 0.45,
                gradientToColors: ['#2ecc71'],
                inverseColors: true,
                opacityFrom: 1,
                opacityTo: 0.7,
                stops: [30, 70, 100]
            }
        },
        stroke: { dashArray: 10 },
        grid: { padding: { top: -20, bottom: 0 } }
    });
    chartP2tl.render();

    var chartHitrate = new ApexCharts(document.querySelector('#chart_hit_rate'), {
        series: [hitratePercent],
        labels: ['Persentase Hitrate'],
        chart: { height: 300, type: 'radialBar' },
        plotOptions: {
            radialBar: {
                offsetY: 6,
                startAngle: -130,
                endAngle: 130,
                hollow: { size: '62%' },
                track: { background: cardColor, strokeWidth: '100%' },
                dataLabels: {
                    name: { offsetY: -18, color: labelColor, fontSize: '12px', fontWeight: '400', fontFamily: 'Public Sans' },
                    value: { offsetY: 6, color: headingColor, fontSize: '34px', fontWeight: '600', fontFamily: 'Public Sans' }
                }
            }
        },
        colors: [config.colors.primary],
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'dark',
                shadeIntensity: 0.45,
                gradientToColors: [config.colors.primary],
                inverseColors: true,
                opacityFrom: 1,
                opacityTo: 0.7,
                stops: [30, 70, 100]
            }
        },
        stroke: { dashArray: 10 },
        grid: { padding: { top: -20, bottom: 0 } }
    });
    chartHitrate.render();
})();
</script>
<?= $this->endSection() ?>
