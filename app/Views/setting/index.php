<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$branding = $branding ?? [];
$appNameCurrent = $branding['app_name'] ?? 'Dashboard PLN';
$primaryColorCurrent = $branding['app_primary_color'] ?? '#0a66c2';
$logoUrl = $branding['app_logo_url'] ?? null;
$loginBackgroundUrl = $branding['login_background_url'] ?? null;
$canRunMaintenanceTools = (bool) ($canRunMaintenanceTools ?? false);
$seederOptions = is_array($seederOptions ?? null) ? $seederOptions : ['pending' => [], 'all' => []];
$pendingSeeders = is_array($seederOptions['pending'] ?? null) ? $seederOptions['pending'] : [];
$allSeeders = is_array($seederOptions['all'] ?? null) ? $seederOptions['all'] : [];
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

        <?php if ($canRunMaintenanceTools): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">Tools Maintenance</h6>
            </div>
            <div class="card-body">
                <form id="maintenanceToolForm" novalidate>
                    <?= csrf_field() ?>

                    <div class="d-grid gap-2 mb-3">
                        <button type="button" class="btn btn-outline-primary" id="btnRunMigrate">
                            <i class="ti ti-database-export me-1"></i> Jalankan php spark migrate
                        </button>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="seeder_class">Seeder Class</label>
                        <select id="seeder_class" name="seeder_class" class="form-select">
                            <?php if ($pendingSeeders !== []): ?>
                                <optgroup label="Perlu Dijalankan / Diperbarui">
                                    <?php foreach ($pendingSeeders as $seed): ?>
                                        <option value="<?= esc((string) ($seed['class'] ?? '')) ?>">
                                            <?= esc((string) ($seed['label'] ?? ($seed['class'] ?? 'Seeder'))) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endif; ?>

                            <?php if ($allSeeders !== []): ?>
                                <optgroup label="Semua Seeder">
                                    <?php foreach ($allSeeders as $seed): ?>
                                        <option value="<?= esc((string) ($seed['class'] ?? '')) ?>">
                                            <?= esc((string) ($seed['label'] ?? ($seed['class'] ?? 'Seeder'))) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php else: ?>
                                <option value="">Seeder tidak ditemukan</option>
                            <?php endif; ?>
                        </select>
                        <div class="form-text">Dropdown memprioritaskan seeder yang belum dijalankan atau perlu diperbarui.</div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary" id="btnRunSeeder">
                            <i class="ti ti-playstation-circle me-1"></i> Jalankan php spark db:seed
                        </button>
                    </div>
                </form>

                <div class="mt-3 d-none" id="commandOutputWrapper">
                    <div class="small text-muted mb-1" id="commandStatusText">Menjalankan perintah...</div>
                    <pre id="commandOutput" class="bg-light border rounded p-2 mb-0" style="max-height:220px;overflow:auto;white-space:pre-wrap;"></pre>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (ENVIRONMENT === 'development' && $canRunMaintenanceTools): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">Sinkronisasi Data Production -> Development</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    Gunakan hanya di server development. Proses ini akan menimpa data tabel development.
                </div>

                <form id="productionSyncForm" novalidate>
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label" for="source_host">Host Production</label>
                        <input type="text" id="source_host" name="source_host" class="form-control" placeholder="127.0.0.1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="source_port">Port</label>
                        <input type="number" id="source_port" name="source_port" class="form-control" value="3306" min="1" max="65535" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="source_database">Database Production</label>
                        <input type="text" id="source_database" name="source_database" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="source_username">Username Production</label>
                        <input type="text" id="source_username" name="source_username" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="source_password">Password Production</label>
                        <input type="password" id="source_password" name="source_password" class="form-control" autocomplete="off">
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" value="1" id="sync_confirmation" name="sync_confirmation" required>
                        <label class="form-check-label" for="sync_confirmation">
                            Saya paham proses ini akan menimpa data development.
                        </label>
                    </div>

                    <button type="submit" class="btn btn-danger" id="btnStartSync">
                        <i class="ti ti-transfer me-1"></i> Mulai Sinkronisasi
                    </button>
                </form>

                <div class="mt-3 d-none" id="syncProgressWrapper">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small text-muted" id="syncStatusText">Memulai sinkronisasi...</span>
                        <span class="small fw-semibold" id="syncPercentText">0%</span>
                    </div>
                    <div class="progress" style="height: 12px;">
                        <div
                            id="syncProgressBar"
                            class="progress-bar progress-bar-striped progress-bar-animated"
                            role="progressbar"
                            style="width: 0%;"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            aria-valuenow="0"
                        ></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    (function () {
        const colorInput = document.getElementById('app_primary_color');
        const textInput = document.getElementById('app_primary_color_text');
        const syncForm = document.getElementById('productionSyncForm');
        const syncProgressWrapper = document.getElementById('syncProgressWrapper');
        const syncProgressBar = document.getElementById('syncProgressBar');
        const syncPercentText = document.getElementById('syncPercentText');
        const syncStatusText = document.getElementById('syncStatusText');
        const syncButton = document.getElementById('btnStartSync');
        const maintenanceForm = document.getElementById('maintenanceToolForm');
        const btnRunMigrate = document.getElementById('btnRunMigrate');
        const btnRunSeeder = document.getElementById('btnRunSeeder');
        const commandOutputWrapper = document.getElementById('commandOutputWrapper');
        const commandStatusText = document.getElementById('commandStatusText');
        const commandOutput = document.getElementById('commandOutput');
        const seederSelect = document.getElementById('seeder_class');

        function updateCsrfToken(token, hash) {
            if (!token || !hash || !syncForm) {
                return;
            }

            const csrfInput = syncForm.querySelector('input[name="' + token + '"]');
            if (csrfInput) {
                csrfInput.value = hash;
            }

            if (maintenanceForm) {
                const maintenanceCsrf = maintenanceForm.querySelector('input[name="' + token + '"]');
                if (maintenanceCsrf) {
                    maintenanceCsrf.value = hash;
                }
            }
        }

        function setCommandState(statusText, outputText) {
            if (commandOutputWrapper) {
                commandOutputWrapper.classList.remove('d-none');
            }

            if (commandStatusText) {
                commandStatusText.textContent = statusText;
            }

            if (commandOutput) {
                commandOutput.textContent = outputText || '-';
            }
        }

        function setCommandButtonsDisabled(disabled) {
            if (btnRunMigrate) {
                btnRunMigrate.disabled = disabled;
            }

            if (btnRunSeeder) {
                btnRunSeeder.disabled = disabled;
            }

            if (seederSelect) {
                seederSelect.disabled = disabled;
            }
        }

        function renderSeederOptions(options) {
            if (!seederSelect || !options || typeof options !== 'object') {
                return;
            }

            const pending = Array.isArray(options.pending) ? options.pending : [];
            const all = Array.isArray(options.all) ? options.all : [];
            const current = String(seederSelect.value || '');

            seederSelect.innerHTML = '';

            function appendGroup(label, rows) {
                if (!Array.isArray(rows) || rows.length === 0) {
                    return;
                }

                const group = document.createElement('optgroup');
                group.label = label;

                rows.forEach(function (row) {
                    const option = document.createElement('option');
                    option.value = String(row.class || '');
                    option.textContent = String(row.label || row.class || 'Seeder');
                    group.appendChild(option);
                });

                seederSelect.appendChild(group);
            }

            appendGroup('Perlu Dijalankan / Diperbarui', pending);
            appendGroup('Semua Seeder', all);

            if (!seederSelect.options.length) {
                const emptyOption = document.createElement('option');
                emptyOption.value = '';
                emptyOption.textContent = 'Seeder tidak ditemukan';
                seederSelect.appendChild(emptyOption);
            }

            if (current !== '') {
                seederSelect.value = current;
            }

            if (seederSelect.value === '' && seederSelect.options.length > 0) {
                seederSelect.selectedIndex = 0;
            }
        }

        async function refreshSeederOptions() {
            try {
                const response = await fetch('<?= site_url('setting/application/tools/seeders') ?>', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();
                updateCsrfToken(data.csrfToken, data.csrfHash);

                if (!response.ok || !data.ok) {
                    return;
                }

                renderSeederOptions(data.options || {});
            } catch (error) {
                // Keep existing options when refresh fails.
            }
        }

        async function runMaintenanceCommand(endpoint, extraData) {
            if (!maintenanceForm) {
                return;
            }

            const payload = new FormData(maintenanceForm);

            if (extraData && typeof extraData === 'object') {
                Object.keys(extraData).forEach(function (key) {
                    payload.set(key, extraData[key]);
                });
            }

            setCommandButtonsDisabled(true);
            setCommandState('Menjalankan perintah...', 'Mohon tunggu...');

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    body: payload,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();
                updateCsrfToken(data.csrfToken, data.csrfHash);

                if (!response.ok || !data.ok) {
                    throw new Error((data && data.message) ? data.message : 'Perintah gagal dijalankan.');
                }

                setCommandState(data.message || 'Perintah berhasil dijalankan.', data.output || '(Tanpa output)');

                if (endpoint.indexOf('/tools/seed') !== -1) {
                    if (data.seederOptions) {
                        renderSeederOptions(data.seederOptions);
                    } else {
                        refreshSeederOptions();
                    }
                }
            } catch (error) {
                const message = error && error.message ? error.message : 'Terjadi kesalahan saat menjalankan perintah.';
                setCommandState('Perintah gagal.', message);

                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Eksekusi gagal',
                        text: message
                    });
                }
            } finally {
                setCommandButtonsDisabled(false);
            }
        }

        function setSyncProgress(value, text) {
            const normalized = Math.max(0, Math.min(100, Number(value) || 0));

            if (syncProgressBar) {
                syncProgressBar.style.width = normalized + '%';
                syncProgressBar.setAttribute('aria-valuenow', String(normalized));
            }

            if (syncPercentText) {
                syncPercentText.textContent = normalized.toFixed(2).replace(/\.00$/, '') + '%';
            }

            if (syncStatusText && typeof text === 'string' && text !== '') {
                syncStatusText.textContent = text;
            }
        }

        async function processSyncStep() {
            if (!syncForm) {
                return;
            }

            const formData = new FormData(syncForm);
            const response = await fetch('<?= site_url('setting/application/sync/step') ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();
            updateCsrfToken(data.csrfToken, data.csrfHash);

            if (!response.ok || !data.ok) {
                throw new Error(data.message || 'Sinkronisasi gagal diproses.');
            }

            setSyncProgress(data.progress || 0, data.message || 'Sinkronisasi berjalan...');

            if (data.done) {
                if (syncButton) {
                    syncButton.disabled = false;
                }

                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Selesai',
                        text: data.message || 'Sinkronisasi berhasil selesai.'
                    });
                }

                return;
            }

            window.setTimeout(processSyncStep, 150);
        }

        async function startProductionSync(event) {
            event.preventDefault();

            if (!syncForm || !syncButton) {
                return;
            }

            syncButton.disabled = true;
            if (syncProgressWrapper) {
                syncProgressWrapper.classList.remove('d-none');
            }
            setSyncProgress(0, 'Memvalidasi koneksi production...');

            try {
                const formData = new FormData(syncForm);
                const response = await fetch('<?= site_url('setting/application/sync/init') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();
                updateCsrfToken(data.csrfToken, data.csrfHash);

                if (!response.ok || !data.ok) {
                    throw new Error(data.message || 'Gagal memulai sinkronisasi.');
                }

                setSyncProgress(0, data.message || 'Sinkronisasi dimulai...');
                processSyncStep();
            } catch (error) {
                if (syncButton) {
                    syncButton.disabled = false;
                }

                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Sinkronisasi gagal',
                        text: error && error.message ? error.message : 'Terjadi kesalahan saat sinkronisasi.'
                    });
                }

                setSyncProgress(0, 'Gagal memulai sinkronisasi.');
            }
        }

        if (!colorInput || !textInput) {
            // Continue to keep sync handler active even when color fields are absent.
        } else {
            colorInput.addEventListener('input', function () {
                textInput.value = colorInput.value;
            });
        }

        if (syncForm) {
            syncForm.addEventListener('submit', startProductionSync);
        }

        if (btnRunMigrate) {
            btnRunMigrate.addEventListener('click', function () {
                runMaintenanceCommand('<?= site_url('setting/application/tools/migrate') ?>');
            });
        }

        if (btnRunSeeder) {
            btnRunSeeder.addEventListener('click', function () {
                const seederInput = document.getElementById('seeder_class');
                const seederName = seederInput ? String(seederInput.value || '').trim() : '';

                if (seederName === '') {
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Seeder belum diisi',
                            text: 'Masukkan nama class seeder terlebih dahulu.'
                        });
                    }

                    return;
                }

                runMaintenanceCommand('<?= site_url('setting/application/tools/seed') ?>', {
                    seeder_class: seederName
                });
            });
        }
    })();
</script>
<?= $this->endSection() ?>
