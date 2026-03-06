<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$photoPath = trim((string) ($user['profile_photo_path'] ?? ''));
$photoUrl = $photoPath !== '' ? base_url($photoPath) : null;
?>
<div class="row g-4">
    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Foto Profil</h5>
            </div>
            <div class="card-body text-center">
                <?php if (is_string($photoUrl) && $photoUrl !== ''): ?>
                    <img
                        src="<?= esc($photoUrl) ?>"
                        alt="Foto profil"
                        class="rounded-circle mb-3"
                        style="width: 120px; height: 120px; object-fit: cover;"
                    >
                <?php else: ?>
                    <div
                        class="rounded-circle bg-label-primary d-inline-flex align-items-center justify-content-center mb-3"
                        style="width: 120px; height: 120px; font-size: 2rem; font-weight: 700;"
                    >
                        <?= esc(strtoupper(mb_substr((string) ($user['nama'] ?? $user['username'] ?? 'U'), 0, 1))) ?>
                    </div>
                <?php endif; ?>

                <?php $errors = session()->getFlashdata('errors'); ?>
                <?php if (! empty($errors) && is_array($errors)): ?>
                    <div class="alert alert-danger text-start" role="alert">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?= site_url('profile/photo') ?>" method="post" enctype="multipart/form-data" novalidate>
                    <?= csrf_field() ?>
                    <div class="mb-3 text-start">
                        <label class="form-label" for="profile_photo">Upload Foto Baru</label>
                        <input
                            type="file"
                            id="profile_photo"
                            name="profile_photo"
                            class="form-control"
                            accept="image/png,image/jpeg,image/webp"
                            required
                        >
                        <div class="form-text">Format PNG/JPG/WEBP, maksimal 2 MB.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti ti-upload me-1"></i> Simpan Foto Profil
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Informasi Profil</h5>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                    <i class="ti ti-lock me-1"></i> Ganti Password
                </button>
            </div>
            <div class="card-body">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success" role="alert"><?= esc(session()->getFlashdata('success')) ?></div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle mb-0">
                        <tbody>
                            <tr>
                                <th style="width: 220px;">Username</th>
                                <td><?= esc((string) ($user['username'] ?? '-')) ?></td>
                            </tr>
                            <tr>
                                <th>Nama</th>
                                <td><?= esc((string) ($user['nama'] ?? '-')) ?></td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td><?= esc((string) ($user['email'] ?? '-')) ?></td>
                            </tr>
                            <tr>
                                <th>Group ID</th>
                                <td><?= esc((string) ($user['group_id'] ?? '-')) ?></td>
                            </tr>
                            <tr>
                                <th>Unit ID</th>
                                <td><?= esc((string) ($user['unit_id'] ?? '-')) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
