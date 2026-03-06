<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row g-4">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <span class="text-heading fw-medium d-block mb-1">Nama</span>
                <h4 class="card-title mb-0"><?= esc($user['nama'] ?: $user['username']) ?></h4>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <span class="text-heading fw-medium d-block mb-1">Username</span>
                <h4 class="card-title mb-0"><?= esc($user['username']) ?></h4>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <span class="text-heading fw-medium d-block mb-1">Group ID</span>
                <h4 class="card-title mb-0"><?= esc((string) $user['group_id']) ?></h4>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <span class="text-heading fw-medium d-block mb-1">Unit ID</span>
                <h4 class="card-title mb-0"><?= esc((string) $user['unit_id']) ?></h4>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Ringkasan</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">Dashboard sudah menggunakan layout utama Vuexy. Halaman modul berikutnya bisa langsung memakai layout ini dengan <code>$this->extend('layouts/main')</code>.</p>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
