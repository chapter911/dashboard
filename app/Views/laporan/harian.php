<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$units = is_array($units ?? null) ? $units : [];
$alasanOptions = is_array($alasanOptions ?? null) ? $alasanOptions : [];
$dayaOptions = is_array($dayaOptions ?? null) ? $dayaOptions : [];
$filters = is_array($filters ?? null) ? $filters : [];
$rows = is_array($rows ?? null) ? $rows : [];
$page = (int) ($page ?? 1);
$perPage = (int) ($perPage ?? 50);
$total = (int) ($total ?? 0);
$pager = $pager ?? null;
?>

<div class="row">
    <div class="col-12">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success" role="alert"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Filter Laporan Harian</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importModal" type="button">Upload CSV</button>
    </div>
    <div class="card-body">
        <form method="get" action="<?= site_url('C_Laporan/Harian') ?>" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Unit</label>
                <select class="form-select" name="unit">
                    <option value="*">Semua Unit</option>
                    <?php foreach ($units as $unit): ?>
                        <?php $unitId = (string) ($unit['unit_id'] ?? ''); ?>
                        <option value="<?= esc($unitId) ?>" <?= ($filters['unit'] ?? '*') === $unitId ? 'selected' : '' ?>><?= esc((string) ($unit['unit_name'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tahun Meter Lama</label>
                <select class="form-select" name="tahun_meter_lama">
                    <option value="*">Semua</option>
                    <option value="0" <?= ($filters['tahun_meter_lama'] ?? '*') === '0' ? 'selected' : '' ?>>Tidak Diketahui</option>
                    <?php for ($year = (int) date('Y'); $year >= 1990; $year--): ?>
                        <option value="<?= esc((string) $year) ?>" <?= ($filters['tahun_meter_lama'] ?? '*') === (string) $year ? 'selected' : '' ?>><?= esc((string) $year) ?></option>
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
                    <option value="*">Semua</option>
                    <option value="1 Fasa" <?= ($filters['fasa'] ?? '*') === '1 Fasa' ? 'selected' : '' ?>>1 Fasa</option>
                    <option value="3 Fasa" <?= ($filters['fasa'] ?? '*') === '3 Fasa' ? 'selected' : '' ?>>3 Fasa</option>
                    <?php foreach ($dayaOptions as $daya): ?>
                        <?php $nilaiDaya = (string) ($daya['daya'] ?? ''); if ($nilaiDaya === '') continue; ?>
                        <option value="<?= esc($nilaiDaya) ?>" <?= ($filters['fasa'] ?? '*') === $nilaiDaya ? 'selected' : '' ?>><?= esc($nilaiDaya) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Alasan Ganti Meter (multi)</label>
                <select class="form-select" name="alasan[]" multiple size="5">
                    <?php foreach ($alasanOptions as $alasan): ?>
                        <?php $val = (string) ($alasan['alasan_ganti_meter'] ?? ''); if ($val === '') continue; ?>
                        <option value="<?= esc($val) ?>" <?= in_array($val, $filters['alasan'] ?? [], true) ? 'selected' : '' ?>><?= esc($val) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tanggal Peremajaan</label>
                <input class="form-control" type="date" name="tgl_peremajaan" value="<?= esc((string) ($filters['tgl_peremajaan'] ?? '')) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Cari</label>
                <input class="form-control" type="text" name="search" value="<?= esc((string) ($filters['search'] ?? '')) ?>" placeholder="IDPEL / Nama / No Agenda">
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-primary" type="submit">Terapkan</button>
                <a class="btn btn-label-secondary" href="<?= site_url('C_Laporan/Harian') ?>">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Data Laporan Harian</h5>
        <small class="text-muted">Total data: <?= esc((string) $total) ?></small>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover table-sm mb-0">
            <thead>
                <tr>
                    <th>No Agenda</th>
                    <th>Unit AP</th>
                    <th>IDPEL</th>
                    <th>Nama</th>
                    <th>Tarif</th>
                    <th>Daya</th>
                    <th>Alasan Ganti Meter</th>
                    <th>Tgl Remaja</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows === []): ?>
                    <tr><td colspan="8" class="text-center text-muted">Tidak ada data.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= esc((string) ($row['no_agenda'] ?? '-')) ?></td>
                        <td><?= esc((string) ($row['unit_ap'] ?? '-')) ?></td>
                        <td><?= esc((string) ($row['idpel'] ?? '-')) ?></td>
                        <td><?= esc((string) ($row['nama'] ?? '-')) ?></td>
                        <td><?= esc((string) ($row['tarif'] ?? '-')) ?></td>
                        <td><?= esc((string) ($row['daya'] ?? '-')) ?></td>
                        <td><?= esc((string) ($row['alasan_ganti_meter'] ?? '-')) ?></td>
                        <td><?= esc((string) ($row['tgl_remaja'] ?? '-')) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pager): ?>
        <div class="card-body pt-3">
            <?= $pager->links() ?>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= site_url('C_Laporan/Harian/import') ?>" method="post" enctype="multipart/form-data" novalidate>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Import Laporan Harian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">File yang didukung: <strong>CSV/TXT delimiter titik-koma (;)</strong>.</div>
                    <input type="file" class="form-control" name="file_import" accept=".csv,.txt" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
