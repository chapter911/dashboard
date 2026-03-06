<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$units = is_array($units ?? null) ? $units : [];
$filters = is_array($filters ?? null) ? $filters : [];
$rows = is_array($rows ?? null) ? $rows : [];
$bulanOptions = is_array($bulanOptions ?? null) ? $bulanOptions : [];
$pager = $pager ?? null;
$total = (int) ($total ?? 0);
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
    <div class="card-header">
        <h5 class="mb-0">Filter Saldo Pelanggan</h5>
    </div>
    <div class="card-body">
        <form method="get" action="<?= site_url('C_Laporan/Saldo') ?>" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Unit</label>
                <select class="form-select" name="unit">
                    <option value="*">Semua Unit</option>
                    <?php foreach ($units as $unit): ?>
                        <?php $uid = (string) ($unit['unit_id'] ?? ''); ?>
                        <option value="<?= esc($uid) ?>" <?= ($filters['unit'] ?? '*') === $uid ? 'selected' : '' ?>><?= esc((string) ($unit['unit_name'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Bulan</label>
                <select class="form-select" name="bulan">
                    <option value="*">Semua Bulan</option>
                    <?php foreach ($bulanOptions as $b): ?>
                        <?php $val = (string) ($b['v_bulan_rekap'] ?? ''); if ($val === '') continue; ?>
                        <option value="<?= esc($val) ?>" <?= ($filters['bulan'] ?? '*') === $val ? 'selected' : '' ?>><?= esc($val) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">IDPEL</label>
                <input class="form-control" type="text" name="idpel" value="<?= esc((string) ($filters['idpel'] ?? '')) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Cari</label>
                <input class="form-control" type="text" name="search" value="<?= esc((string) ($filters['search'] ?? '')) ?>" placeholder="Nama / No Meter">
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button class="btn btn-primary" type="submit">Tampilkan</button>
                <a class="btn btn-label-secondary" href="<?= site_url('C_Laporan/Saldo') ?>">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Data Saldo</h5>
        <small class="text-muted">Total data: <?= esc((string) $total) ?></small>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Bulan Rekap</th>
                    <th>Unit UP</th>
                    <th>IDPEL</th>
                    <th>Nama</th>
                    <th>Tarif / Daya</th>
                    <th>No Meter KWH</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows === []): ?>
                    <tr><td colspan="7" class="text-center text-muted">Belum ada data saldo.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= esc((string) ($row['v_bulan_rekap'] ?? '-')) ?></td>
                        <td><?= esc((string) ($row['unit_up'] ?? '-')) ?></td>
                        <td><?= esc((string) ($row['idpel'] ?? '-')) ?></td>
                        <td><?= esc((string) ($row['nama'] ?? '-')) ?></td>
                        <td><?= esc((string) ($row['tarif'] ?? '-')) ?> / <?= esc((string) ($row['daya'] ?? '-')) ?></td>
                        <td><?= esc((string) ($row['nomor_meter_kwh'] ?? '-')) ?></td>
                        <td class="text-end">
                            <button
                                type="button"
                                class="btn btn-sm btn-label-primary btn-edit-saldo"
                                data-idpel="<?= esc((string) ($row['idpel'] ?? '')) ?>"
                                data-bulan="<?= esc((string) ($row['v_bulan_rekap'] ?? '')) ?>"
                                data-nama="<?= esc((string) ($row['nama'] ?? '')) ?>"
                                data-tarif="<?= esc((string) ($row['tarif'] ?? '')) ?>"
                                data-daya="<?= esc((string) ($row['daya'] ?? '')) ?>"
                                data-meter="<?= esc((string) ($row['nomor_meter_kwh'] ?? '')) ?>"
                                data-bs-toggle="modal"
                                data-bs-target="#saldoModal"
                            >Update</button>
                        </td>
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

<div class="modal fade" id="saldoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= site_url('C_Laporan/Saldo/update') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Update Saldo Pelanggan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">IDPEL</label>
                        <input class="form-control" type="text" name="idpel" id="saldo_idpel" readonly required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bulan Rekap</label>
                        <input class="form-control" type="number" name="v_bulan_rekap" id="saldo_bulan" readonly required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input class="form-control" type="text" name="nama" id="saldo_nama">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tarif</label>
                        <input class="form-control" type="text" name="tarif" id="saldo_tarif">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Daya</label>
                        <input class="form-control" type="number" min="0" name="daya" id="saldo_daya">
                    </div>
                    <div class="mt-3">
                        <label class="form-label">No Meter KWH</label>
                        <input class="form-control" type="number" min="0" name="nomor_meter_kwh" id="saldo_meter">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('click', function (event) {
        const btn = event.target.closest('.btn-edit-saldo');
        if (!btn) {
            return;
        }
        document.getElementById('saldo_idpel').value = btn.getAttribute('data-idpel') || '';
        document.getElementById('saldo_bulan').value = btn.getAttribute('data-bulan') || '';
        document.getElementById('saldo_nama').value = btn.getAttribute('data-nama') || '';
        document.getElementById('saldo_tarif').value = btn.getAttribute('data-tarif') || '';
        document.getElementById('saldo_daya').value = btn.getAttribute('data-daya') || '';
        document.getElementById('saldo_meter').value = btn.getAttribute('data-meter') || '';
    });
</script>
<?= $this->endSection() ?>
