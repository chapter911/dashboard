<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$units = is_array($units ?? null) ? $units : [];
$filters = is_array($filters ?? null) ? $filters : [];
$rows = is_array($rows ?? null) ? $rows : [];
$tahun = ($filters['tahun'] ?? '*') === '*' ? (int) ($currentYear ?? date('Y')) : (int) $filters['tahun'];
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
        <h5 class="mb-0">Filter Target</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#targetModal" type="button">Tambah Target</button>
    </div>
    <div class="card-body">
        <form method="get" action="<?= site_url('C_Laporan/Target') ?>" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Tahun</label>
                <input class="form-control" type="number" min="2000" max="2100" name="tahun" value="<?= esc((string) $tahun) ?>">
            </div>
            <div class="col-md-5">
                <label class="form-label">Unit</label>
                <select class="form-select" name="unit_id">
                    <option value="*">Semua Unit</option>
                    <?php foreach ($units as $unit): ?>
                        <?php $uid = (string) ($unit['unit_id'] ?? ''); ?>
                        <option value="<?= esc($uid) ?>" <?= ($filters['unit_id'] ?? '*') === $uid ? 'selected' : '' ?>><?= esc((string) ($unit['unit_name'] ?? '')) ?></option>
                    <?php endforeach; ?>
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
        <h5 class="mb-0">Daftar Target Tahun <?= esc((string) $tahun) ?></h5>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Unit</th>
                    <th class="text-end">Target Tua</th>
                    <th class="text-end">Target Rusak</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows === []): ?>
                    <tr><td colspan="5" class="text-center text-muted">Belum ada target.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $row): ?>
                    <?php
                    $targetTua = (int) ($row['target_tua'] ?? 0);
                    $targetRusak = (int) ($row['target_rusak'] ?? 0);
                    ?>
                    <tr>
                        <td><?= esc((string) ($row['unit_name'] ?? '-')) ?></td>
                        <td class="text-end"><?= esc(number_format($targetTua, 0, ',', '.')) ?></td>
                        <td class="text-end"><?= esc(number_format($targetRusak, 0, ',', '.')) ?></td>
                        <td class="text-end fw-semibold"><?= esc(number_format($targetTua + $targetRusak, 0, ',', '.')) ?></td>
                        <td class="text-end">
                            <button
                                type="button"
                                class="btn btn-sm btn-label-primary btn-edit-target"
                                data-id="<?= esc((string) ($row['id'] ?? '')) ?>"
                                data-unit="<?= esc((string) ($row['unit_id'] ?? '')) ?>"
                                data-tahun="<?= esc((string) ($row['tahun'] ?? $tahun)) ?>"
                                data-target-tua="<?= esc((string) ($row['target_tua'] ?? '0')) ?>"
                                data-target-rusak="<?= esc((string) ($row['target_rusak'] ?? '0')) ?>"
                                data-bs-toggle="modal"
                                data-bs-target="#targetModal"
                            >Edit</button>
                            <form action="<?= site_url('C_Laporan/Target/delete') ?>" method="post" class="d-inline" onsubmit="return confirm('Hapus target ini?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= esc((string) ($row['id'] ?? '')) ?>">
                                <button type="submit" class="btn btn-sm btn-label-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="targetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= site_url('C_Laporan/Target/save') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="target_id">
                <div class="modal-header">
                    <h5 class="modal-title">Simpan Target</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Unit</label>
                        <select class="form-select" name="unit_id" id="unit_id" required>
                            <option value="">Pilih Unit</option>
                            <?php foreach ($units as $unit): ?>
                                <option value="<?= esc((string) ($unit['unit_id'] ?? '')) ?>"><?= esc((string) ($unit['unit_name'] ?? '')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tahun</label>
                        <input class="form-control" type="number" min="2000" max="2100" name="tahun" id="tahun" value="<?= esc((string) $tahun) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Target Tua</label>
                        <input class="form-control" type="number" min="0" name="target_tua" id="target_tua" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Target Rusak</label>
                        <input class="form-control" type="number" min="0" name="target_rusak" id="target_rusak" required>
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
    const btn = event.target.closest('.btn-edit-target');
    if (!btn) {
      return;
    }
    document.getElementById('target_id').value = btn.getAttribute('data-id') || '';
    document.getElementById('unit_id').value = btn.getAttribute('data-unit') || '';
        document.getElementById('tahun').value = btn.getAttribute('data-tahun') || '<?= esc((string) $tahun) ?>';
        document.getElementById('target_tua').value = btn.getAttribute('data-target-tua') || '0';
        document.getElementById('target_rusak').value = btn.getAttribute('data-target-rusak') || '0';
  });

  document.getElementById('targetModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('target_id').value = '';
    document.getElementById('unit_id').value = '';
        document.getElementById('tahun').value = '<?= esc((string) $tahun) ?>';
        document.getElementById('target_tua').value = '';
        document.getElementById('target_rusak').value = '';
  });
</script>
<?= $this->endSection() ?>
