<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<?php
$branding = $branding ?? [];
$appName = $branding['app_name'] ?? 'Dashboard PLN';
?>
<main class="layout" role="main">
    <section class="panel hero" aria-label="Informasi aplikasi">
        <div class="logo">
            <span class="logo-badge">PLN</span>
            <span><?= esc($appName) ?></span>
        </div>
        <h1>Monitor Kinerja dan Data Operasional dalam Satu Dashboard</h1>
        <p>Silakan login untuk mengakses laporan, analisa, dan monitoring berbasis unit secara terpusat.</p>
    </section>

    <section class="panel form-panel" aria-label="Form login">
        <h2>Masuk ke Sistem</h2>
        <p class="subtitle">Gunakan akun internal yang telah terdaftar.</p>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <?php $errors = session()->getFlashdata('errors'); ?>
        <?php if (! empty($errors) && is_array($errors)): ?>
            <div class="alert alert-error">
                <ul class="error-list">
                    <?php foreach ($errors as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('login') ?>" method="post" novalidate>
            <?= csrf_field() ?>
            <div class="field">
                <label for="username">Username</label>
                <input
                    id="username"
                    name="username"
                    type="text"
                    autocomplete="username"
                    placeholder="Masukkan username"
                    value="<?= esc(old('username')) ?>"
                    required
                >
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    placeholder="Masukkan password"
                    required
                >
            </div>
            <button class="btn" type="submit">Login</button>
        </form>
    </section>
</main>
<?= $this->endSection() ?>
