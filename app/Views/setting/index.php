<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$branding = $branding ?? [];
$appNameCurrent = $branding['app_name'] ?? 'Dashboard PLN';
$primaryColorCurrent = $branding['app_primary_color'] ?? '#0a66c2';
$logoUrl = $branding['app_logo_url'] ?? null;
$loginBackgroundUrl = $branding['login_background_url'] ?? null;
?>

<div class="row">
    <div class="col-12">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success" role="alert">
                <?= esc(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger" role="alert">
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <?php $errors = session()->getFlashdata('errors'); ?>
        <?php if (! empty($errors) && is_array($errors)): ?>
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Setting Tampilan Aplikasi</h5>
            </div>
            <div class="card-body">
                <form action="<?= site_url('setting/application') ?>" method="post" enctype="multipart/form-data" novalidate>
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label" for="app_name">Nama Aplikasi</label>
                        <input
                            type="text"
                            id="app_name"
                            name="app_name"
                            class="form-control"
                            value="<?= esc($formData['app_name'] ?? $appNameCurrent) ?>"
                            placeholder="Contoh: Dashboard PLN Distribusi"
                            maxlength="100"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="app_primary_color">Warna Primary Aplikasi</label>
                        <div class="d-flex align-items-center gap-2">
                            <input
                                type="color"
                                id="app_primary_color"
                                name="app_primary_color"
                                class="form-control form-control-color"
                                value="<?= esc($formData['app_primary_color'] ?? $primaryColorCurrent) ?>"
                                title="Pilih warna primary aplikasi"
                                required
                            >
                            <input
                                type="text"
                                id="app_primary_color_text"
                                class="form-control"
                                value="<?= esc($formData['app_primary_color'] ?? $primaryColorCurrent) ?>"
                                readonly
                                aria-label="Nilai warna primary"
                            >
                        </div>
                        <div class="form-text">Warna ini digunakan untuk elemen utama aplikasi.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="app_logo">Logo Aplikasi</label>
                        <input
                            type="file"
                            id="app_logo"
                            name="app_logo"
                            class="form-control"
                            accept="image/png,image/jpeg,image/webp"
                        >
                        <div class="form-text">Kosongkan jika tidak ingin mengganti logo. Maksimal 2 MB.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="login_background">Background Login</label>
                        <input
                            type="file"
                            id="login_background"
                            name="login_background"
                            class="form-control"
                            accept="image/png,image/jpeg,image/webp"
                        >
                        <div class="form-text">Kosongkan jika tidak ingin mengganti background login. Maksimal 4 MB.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="auto_logout_minutes">Timer Logout Otomatis (menit)</label>
                        <input
                            type="number"
                            id="auto_logout_minutes"
                            name="auto_logout_minutes"
                            class="form-control"
                            min="1"
                            max="1440"
                            step="1"
                            value="<?= esc($formData['auto_logout_minutes'] ?? '30') ?>"
                            required
                        >
                        <div class="form-text">Pengguna akan otomatis logout jika tidak ada aktivitas sesuai durasi ini.</div>
                    </div>

                    <button class="btn btn-primary" type="submit">
                        <i class="ti ti-device-floppy me-1"></i> Simpan Setting
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Preview Warna Primary</h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded" style="display:inline-block;width:30px;height:30px;background:<?= esc($formData['app_primary_color'] ?? $primaryColorCurrent) ?>;"></span>
                    <code><?= esc($formData['app_primary_color'] ?? $primaryColorCurrent) ?></code>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Preview Logo</h6>
            </div>
            <div class="card-body text-center">
                <img
                    src="<?= esc($logoUrl ?? base_url('assets/img/branding/logo.png')) ?>"
                    alt="Logo aplikasi"
                    style="max-height: 80px; max-width: 100%; object-fit: contain;"
                >
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Preview Background Login</h6>
            </div>
            <div class="card-body">
                <?php if (is_string($loginBackgroundUrl) && $loginBackgroundUrl !== ''): ?>
                    <img
                        src="<?= esc($loginBackgroundUrl) ?>"
                        alt="Background login"
                        class="img-fluid rounded"
                        style="width: 100%; object-fit: cover; max-height: 220px;"
                    >
                <?php else: ?>
                    <div class="border rounded p-3 text-muted">Belum ada background login custom.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">Timer Logout Aktif</h6>
            </div>
            <div class="card-body">
                <span class="badge bg-label-primary">
                    <?= esc((string) ($formData['auto_logout_minutes'] ?? '30')) ?> menit
                </span>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    (function () {
        const colorInput = document.getElementById('app_primary_color');
        const textInput = document.getElementById('app_primary_color_text');

        if (!colorInput || !textInput) {
            return;
        }

        colorInput.addEventListener('input', function () {
            textInput.value = colorInput.value;
        });
    })();
</script>
<?= $this->endSection() ?>
