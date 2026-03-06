<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$type = (string) ($type ?? 'tahunan');
$params = is_array($params ?? null) ? $params : [];
$sort = (string) ($sort ?? 'none');
$rows = is_array($rows ?? null) ? $rows : [];
?>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Filter Realisasi</h5>
    </div>
    <div class="card-body">
        <form method="get" action="<?= site_url('C_Laporan/Realisasi') ?>" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Mode</label>
                <select class="form-select" name="type" id="type_realisasi">
                    <option value="tahunan" <?= $type === 'tahunan' ? 'selected' : '' ?>>Tahunan</option>
                    <option value="bulanan" <?= $type === 'bulanan' ? 'selected' : '' ?>>Bulanan</option>
                    <option value="harian" <?= $type === 'harian' ? 'selected' : '' ?>>Harian</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tahun</label>
                <input class="form-control" type="number" min="2000" max="2100" name="tahun" value="<?= esc((string) ($params['tahun'] ?? date('Y'))) ?>">
            </div>
            <div class="col-md-2" id="filter_bulan">
                <label class="form-label">Bulan</label>
                <input class="form-control" type="number" min="1" max="12" name="bulan" value="<?= esc((string) ($params['bulan'] ?? date('n'))) ?>">
            </div>
            <div class="col-md-2" id="filter_tgl">
                <label class="form-label">Tanggal</label>
                <input class="form-control" type="date" name="tgl" value="<?= esc((string) ($params['tgl'] ?? date('Y-m-d'))) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Urutkan</label>
                <select class="form-select" name="sort">
                    <option value="none" <?= $sort === 'none' ? 'selected' : '' ?>>Default</option>
                    <option value="highest" <?= $sort === 'highest' ? 'selected' : '' ?>>Persentase Tertinggi</option>
                    <option value="lowest" <?= $sort === 'lowest' ? 'selected' : '' ?>>Persentase Terendah</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary" type="submit">Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Realisasi vs Target</h5>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Unit</th>
                    <th class="text-end">Target Tua</th>
                    <th class="text-end">Target Rusak</th>
                    <th class="text-end">Target Total</th>
                    <th class="text-end">Realisasi Tua</th>
                    <th class="text-end">Realisasi Rusak</th>
                    <th class="text-end">Realisasi Total</th>
                    <th class="text-end">Persentase</th>
                    <th class="text-end">Kurang</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows === []): ?>
                    <tr><td colspan="9" class="text-center text-muted">Tidak ada data realisasi.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $row): ?>
                    <?php
                    $targetTotal = (int) ($row['target_total'] ?? 0);
                    $realTotal = (int) ($row['real_total'] ?? 0);
                    $persen = (float) ($row['percent'] ?? 0);
                    $kurang = max($targetTotal - $realTotal, 0);
                    ?>
                    <tr>
                        <td><?= esc((string) ($row['unit_name'] ?? '-')) ?></td>
                        <td class="text-end"><?= esc(number_format((int) ($row['target_tua'] ?? 0), 0, ',', '.')) ?></td>
                        <td class="text-end"><?= esc(number_format((int) ($row['target_rusak'] ?? 0), 0, ',', '.')) ?></td>
                        <td class="text-end"><?= esc(number_format($targetTotal, 0, ',', '.')) ?></td>
                        <td class="text-end"><?= esc(number_format((int) ($row['real_tua'] ?? 0), 0, ',', '.')) ?></td>
                        <td class="text-end"><?= esc(number_format((int) ($row['real_rusak'] ?? 0), 0, ',', '.')) ?></td>
                        <td class="text-end"><?= esc(number_format($realTotal, 0, ',', '.')) ?></td>
                        <td class="text-end"><?= esc(number_format($persen, 2, ',', '.')) ?>%</td>
                        <td class="text-end"><?= esc(number_format($kurang, 0, ',', '.')) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  (function () {
    const typeEl = document.getElementById('type_realisasi');
    const bulanEl = document.getElementById('filter_bulan');
    const tglEl = document.getElementById('filter_tgl');

    function syncFilter() {
      const mode = typeEl.value;
      bulanEl.style.display = mode === 'bulanan' ? '' : 'none';
      tglEl.style.display = mode === 'harian' ? '' : 'none';
    }

    typeEl.addEventListener('change', syncFilter);
    syncFilter();
  })();
</script>
<?= $this->endSection() ?>
