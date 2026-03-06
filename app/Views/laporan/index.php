<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$units = is_array($units ?? null) ? $units : [];
$alasanOptions = is_array($alasanOptions ?? null) ? $alasanOptions : [];
$dayaOptions = is_array($dayaOptions ?? null) ? $dayaOptions : [];
$filters = is_array($filters ?? null) ? $filters : [];
$summaryRows = is_array($summaryRows ?? null) ? $summaryRows : [];
$reasonRows = is_array($reasonRows ?? null) ? $reasonRows : [];

$totalUsia0 = 0;
$totalUsia6 = 0;
$totalUsia11 = 0;
$totalUsia16 = 0;
$totalBlank = 0;
$totalAll = 0;
?>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Filter Dashboard Laporan</h5>
    </div>
    <div class="card-body">
        <form method="get" action="<?= site_url('C_Laporan/Index') ?>" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Unit</label>
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
                <label class="form-label">Tahun Meter Lama</label>
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
                    <option value="*" <?= ($filters['tarif'] ?? '*') === '*' ? 'selected' : '' ?>>Semua</option>
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

            <div class="col-md-6">
                <label class="form-label">Alasan Ganti Meter (multi)</label>
                <select class="form-select" name="alasan[]" multiple size="6">
                    <?php foreach ($alasanOptions as $alasan): ?>
                        <?php $val = (string) ($alasan['alasan_ganti_meter'] ?? ''); ?>
                        <?php if ($val === '') continue; ?>
                        <option value="<?= esc($val) ?>" <?= in_array($val, $filters['alasan'] ?? [], true) ? 'selected' : '' ?>>
                            <?= esc($val) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6 d-flex align-items-end gap-2">
                <button class="btn btn-primary" type="submit">Terapkan Filter</button>
                <a class="btn btn-label-secondary" href="<?= site_url('C_Laporan/Index') ?>">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Ringkasan Kumulatif Usia Meter</h5>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-bordered table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>UP3</th>
                    <th>&lt;= 5 th</th>
                    <th>6-10 th</th>
                    <th>11-15 th</th>
                    <th>&gt; 15 th</th>
                    <th>Blank</th>
                    <th>Total</th>
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

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Distribusi Alasan Ganti Meter</h5>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Alasan</th>
                    <th style="width: 140px;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($reasonRows === []): ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted">Belum ada data alasan.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($reasonRows as $idx => $reason): ?>
                    <tr>
                        <td><?= esc((string) ($idx + 1)) ?></td>
                        <td><?= esc((string) ($reason['alasan_ganti_meter'] ?? '-')) ?></td>
                        <td class="text-end fw-semibold"><?= esc((string) ($reason['total'] ?? 0)) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
