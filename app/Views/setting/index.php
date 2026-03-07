<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$branding = $branding ?? [];
$appNameCurrent = $branding['app_name'] ?? 'Dashboard PLN';
$primaryColorCurrent = $branding['app_primary_color'] ?? '#0a66c2';
$logoUrl = $branding['app_logo_url'] ?? null;
$loginBackgroundUrl = $branding['login_background_url'] ?? null;
$canRunMaintenanceTools = (bool) ($canRunMaintenanceTools ?? false);
$shellExecAvailable = (bool) ($shellExecAvailable ?? false);
$seederOptions = is_array($seederOptions ?? null) ? $seederOptions : ['pending' => [], 'all' => []];
$pendingSeeders = is_array($seederOptions['pending'] ?? null) ? $seederOptions['pending'] : [];
$allSeeders = is_array($seederOptions['all'] ?? null) ? $seederOptions['all'] : [];
$syncTableOptions = is_array($syncTableOptions ?? null) ? $syncTableOptions : [];
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
                        <?php if (ENVIRONMENT === 'production'): ?>
                            <button type="button" class="btn btn-outline-danger" id="btnRunGitPull" <?= $shellExecAvailable ? '' : 'disabled' ?>>
                                <i class="ti ti-git-pull-request me-1"></i> Jalankan git pull (production)
                            </button>
                            <?php if (! $shellExecAvailable): ?>
                                <div class="small text-danger mt-1">
                                    Server menonaktifkan fungsi <code>exec()</code>. Git pull via web tidak tersedia, jalankan lewat SSH/terminal hosting.
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
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
                        <button type="button" class="btn btn-outline-warning" id="btnRunSnakeCaseScenario">
                            <i class="ti ti-writing-sign me-1"></i> Cek & Eksekusi Snake Case
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

                <div class="alert alert-info">
                    Kredensial source database diambil dari ENV server development.
                    <br>Gunakan variabel: <code>SYNC_SOURCE_HOST</code>, <code>SYNC_SOURCE_PORT</code>, <code>SYNC_SOURCE_DATABASE</code>, <code>SYNC_SOURCE_USERNAME</code>, <code>SYNC_SOURCE_PASSWORD</code>.
                </div>

                <form id="productionSyncForm" novalidate>
                    <?= csrf_field() ?>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" value="1" id="sync_confirmation" name="sync_confirmation" required>
                        <label class="form-check-label" for="sync_confirmation">
                            Saya paham proses ini akan menimpa data development.
                        </label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-block mb-2">Mode Sinkronisasi Tabel</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="sync_scope" id="sync_scope_all" value="all" checked>
                            <label class="form-check-label" for="sync_scope_all">Seluruh tabel yang tersedia</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="sync_scope" id="sync_scope_selected" value="selected">
                            <label class="form-check-label" for="sync_scope_selected">Pilih tabel tertentu</label>
                        </div>
                    </div>

                    <div class="border rounded p-3 mb-3 d-none" id="syncSelectedTablesWrapper">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="small text-muted">Pilih satu atau beberapa tabel yang ingin disinkronkan.</div>
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" id="sync_select_all_tables">
                                <label class="form-check-label small" for="sync_select_all_tables">Pilih semua</label>
                            </div>
                        </div>

                        <div class="small text-muted mb-2" id="syncSelectedSummary">Belum ada tabel dipilih.</div>

                        <div class="border rounded p-2" style="max-height: 220px; overflow: auto;">
                            <?php if ($syncTableOptions === []): ?>
                                <div class="small text-muted">Daftar tabel belum tersedia.</div>
                            <?php else: ?>
                                <?php foreach ($syncTableOptions as $tableOption): ?>
                                    <?php
                                    $tableName = (string) ($tableOption['name'] ?? '');
                                    $rowCount = $tableOption['row_count'] ?? null;
                                    $targetExists = (bool) ($tableOption['target_exists'] ?? true);
                                    $rowCountLabel = $rowCount === null ? 'N/A' : number_format((int) $rowCount, 0, ',', '.');
                                    ?>
                                    <?php if ($tableName === '') continue; ?>
                                    <div class="form-check">
                                        <input class="form-check-input sync-table-checkbox" type="checkbox" value="<?= esc($tableName) ?>" id="sync_table_<?= esc($tableName) ?>" name="selected_tables[]" data-row-count="<?= esc((string) ($rowCount === null ? '' : (int) $rowCount)) ?>">
                                        <label class="form-check-label d-flex justify-content-between align-items-center gap-2 flex-wrap" for="sync_table_<?= esc($tableName) ?>">
                                            <code><?= esc($tableName) ?></code>
                                            <span class="d-flex align-items-center gap-1">
                                                <span class="badge bg-label-secondary">~<?= esc($rowCountLabel) ?> baris</span>
                                                <?php if (! $targetExists): ?>
                                                    <span class="badge bg-label-warning">Belum ada di dev, akan dibuat otomatis</span>
                                                <?php endif; ?>
                                            </span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-danger" id="btnStartSync">
                        <i class="ti ti-transfer me-1"></i> Mulai Sinkronisasi
                    </button>
                    <button type="button" class="btn btn-outline-warning ms-2 d-none" id="btnResumeSync">
                        <i class="ti ti-player-track-next me-1"></i> Lanjutkan Sinkronisasi
                    </button>
                </form>

                <div class="mt-3 d-none" id="syncProgressWrapper">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small text-muted" id="syncStatusText">Memulai sinkronisasi...</span>
                        <span class="small fw-semibold" id="syncPercentText">0%</span>
                    </div>
                    <div class="small text-muted mb-2" id="syncMetaText"></div>
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
        const syncMetaText = document.getElementById('syncMetaText');
        const syncButton = document.getElementById('btnStartSync');
        const syncResumeButton = document.getElementById('btnResumeSync');
        const selectedTableWrapper = document.getElementById('syncSelectedTablesWrapper');
        const syncSelectedSummary = document.getElementById('syncSelectedSummary');
        const syncScopeInputs = document.querySelectorAll('input[name="sync_scope"]');
        const syncTableCheckboxes = document.querySelectorAll('.sync-table-checkbox');
        const syncSelectAllTables = document.getElementById('sync_select_all_tables');
        const csrfHeaderName = '<?= esc(csrf_header()) ?>';
        const maintenanceForm = document.getElementById('maintenanceToolForm');
        const btnRunMigrate = document.getElementById('btnRunMigrate');
        const btnRunSeeder = document.getElementById('btnRunSeeder');
        const btnRunSnakeCaseScenario = document.getElementById('btnRunSnakeCaseScenario');
        const btnRunGitPull = document.getElementById('btnRunGitPull');
        const commandOutputWrapper = document.getElementById('commandOutputWrapper');
        const commandStatusText = document.getElementById('commandStatusText');
        const commandOutput = document.getElementById('commandOutput');
        const seederSelect = document.getElementById('seeder_class');

        function updateCsrfToken(token, hash) {
            if (!token || !hash) {
                return;
            }

            if (syncForm) {
                const csrfInput = syncForm.querySelector('input[name="' + token + '"]');
                if (csrfInput) {
                    csrfInput.value = hash;
                }
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

            if (btnRunSnakeCaseScenario) {
                btnRunSnakeCaseScenario.disabled = disabled;
            }

            if (btnRunGitPull) {
                btnRunGitPull.disabled = disabled;
            }

            if (seederSelect) {
                seederSelect.disabled = disabled;
            }
        }

        function getCsrfEntry(formElement) {
            if (!formElement) {
                return null;
            }

            const hiddenInput = formElement.querySelector('input[type="hidden"]');
            if (!hiddenInput || !hiddenInput.name) {
                return null;
            }

            return {
                name: hiddenInput.name,
                value: hiddenInput.value || ''
            };
        }

        function getSyncCsrfEntry() {
            return getCsrfEntry(syncForm);
        }

        function buildSyncRequestPayload() {
            const formData = new FormData(syncForm);
            const csrfEntry = getSyncCsrfEntry();

            if (csrfEntry && csrfEntry.name) {
                formData.set(csrfEntry.name, csrfEntry.value);
            }

            const headers = {
                'X-Requested-With': 'XMLHttpRequest'
            };

            if (csrfEntry && csrfEntry.value) {
                headers[csrfHeaderName] = csrfEntry.value;
                headers['X-CSRF-TOKEN'] = csrfEntry.value;
            }

            return {
                formData: formData,
                headers: headers,
            };
        }

        async function parseResponsePayload(response) {
            const contentType = String(response.headers.get('content-type') || '');
            if (contentType.indexOf('application/json') !== -1) {
                return await response.json();
            }

            return {
                ok: false,
                message: (await response.text()) || 'Respons server tidak valid.',
            };
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
            const csrfEntry = getCsrfEntry(maintenanceForm);

            if (csrfEntry && csrfEntry.name) {
                payload.set(csrfEntry.name, csrfEntry.value);
            }

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
                        'X-Requested-With': 'XMLHttpRequest',
                        [csrfHeaderName]: csrfEntry ? csrfEntry.value : '',
                        'X-CSRF-TOKEN': csrfEntry ? csrfEntry.value : ''
                    }
                });

                const contentType = response.headers.get('content-type') || '';
                const data = contentType.indexOf('application/json') !== -1
                    ? await response.json()
                    : { ok: false, message: await response.text() };
                updateCsrfToken(data.csrfToken, data.csrfHash);

                if (!response.ok || !data.ok) {
                    if (response.status === 403) {
                        throw new Error('Token CSRF tidak valid atau kadaluarsa. Muat ulang halaman lalu coba lagi.');
                    }

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

        function setSyncMeta(meta) {
            if (!syncMetaText) {
                return;
            }

            if (!meta || typeof meta !== 'object') {
                syncMetaText.textContent = '';
                return;
            }

            const current = meta.current_table || null;
            if (!current || !current.name) {
                syncMetaText.textContent = '';
                return;
            }

            const tablePosition = (Number(meta.table_index || 0) + 1) + '/' + Number(meta.total_tables || 0);
            const batchSize = Number(current.batch_size || 0).toLocaleString('id-ID');
            const offset = Number(current.offset || 0).toLocaleString('id-ID');
            const rowCount = Number(current.row_count || 0).toLocaleString('id-ID');

            syncMetaText.textContent =
                'Tabel: ' + String(current.name) +
                ' | Posisi: ' + tablePosition +
                ' | Batch aktif: ' + batchSize +
                ' | Progress tabel: ' + offset + '/' + rowCount;
        }

        function setSyncButtonsState(isRunning) {
            if (syncButton) {
                syncButton.disabled = isRunning;
            }

            if (syncResumeButton) {
                syncResumeButton.disabled = isRunning;
            }
        }

        function setResumeVisible(visible) {
            if (!syncResumeButton) {
                return;
            }

            syncResumeButton.classList.toggle('d-none', !visible);
        }

        function getSelectedSyncScope() {
            const checkedScope = document.querySelector('input[name="sync_scope"]:checked');
            return checkedScope ? String(checkedScope.value || 'all') : 'all';
        }

        function syncSelectedTableUiState() {
            const isSelectedScope = getSelectedSyncScope() === 'selected';

            if (selectedTableWrapper) {
                selectedTableWrapper.classList.toggle('d-none', !isSelectedScope);
            }

            syncTableCheckboxes.forEach(function (checkbox) {
                checkbox.disabled = !isSelectedScope;
            });

            if (syncSelectAllTables) {
                syncSelectAllTables.disabled = !isSelectedScope;
                if (!isSelectedScope) {
                    syncSelectAllTables.checked = false;
                }
            }

            updateSelectedTablesSummary();
        }

        function updateSelectedTablesSummary() {
            if (!syncSelectedSummary) {
                return;
            }

            const selectedRows = Array.from(syncTableCheckboxes).filter(function (checkbox) {
                return !checkbox.disabled && checkbox.checked;
            });

            const selectedCount = selectedRows.length;
            const totalRows = selectedRows.reduce(function (sum, checkbox) {
                const value = Number(checkbox.getAttribute('data-row-count') || 0);
                return sum + (Number.isFinite(value) ? value : 0);
            }, 0);

            if (selectedCount < 1) {
                syncSelectedSummary.textContent = 'Belum ada tabel dipilih.';
                return;
            }

            syncSelectedSummary.textContent = selectedCount + ' tabel dipilih, estimasi ' + totalRows.toLocaleString('id-ID') + ' baris source.';
        }

        async function processSyncStep() {
            try {
                if (!syncForm) {
                    return;
                }

                const requestPayload = buildSyncRequestPayload();
                const response = await fetch('<?= site_url('setting/application/sync/step') ?>', {
                    method: 'POST',
                    body: requestPayload.formData,
                    headers: requestPayload.headers
                });

                const data = await parseResponsePayload(response);
                updateCsrfToken(data.csrfToken, data.csrfHash);

                if (!response.ok || !data.ok) {
                    throw new Error(data.message || 'Sinkronisasi gagal diproses.');
                }

                setSyncProgress(data.progress || 0, data.message || 'Sinkronisasi berjalan...');
                setSyncMeta(data.syncMeta || null);

                if (data.done) {
                    setSyncButtonsState(false);
                    setResumeVisible(false);

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
            } catch (error) {
                setSyncButtonsState(false);
                setResumeVisible(true);

                if (syncProgressWrapper) {
                    syncProgressWrapper.classList.remove('d-none');
                }

                setSyncProgress(Number(syncProgressBar ? syncProgressBar.getAttribute('aria-valuenow') : 0), 'Sinkronisasi terhenti. Gunakan tombol Lanjutkan Sinkronisasi.');

                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sinkronisasi terhenti',
                        text: error && error.message ? error.message : 'Proses berhenti sementara. Anda bisa melanjutkannya.'
                    });
                }
            }
        }

        async function startProductionSync(event) {
            event.preventDefault();

            if (!syncForm || !syncButton) {
                return;
            }

            const selectedScope = getSelectedSyncScope();
            if (selectedScope === 'selected') {
                const selectedCount = Array.from(syncTableCheckboxes).filter(function (checkbox) {
                    return checkbox.checked;
                }).length;

                if (selectedCount < 1) {
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Tabel belum dipilih',
                            text: 'Pilih minimal satu tabel untuk sinkronisasi.'
                        });
                    }
                    return;
                }
            }

            setSyncButtonsState(true);
            setResumeVisible(false);
            if (syncProgressWrapper) {
                syncProgressWrapper.classList.remove('d-none');
            }
            setSyncProgress(0, 'Memvalidasi koneksi production...');

            try {
                const requestPayload = buildSyncRequestPayload();
                const response = await fetch('<?= site_url('setting/application/sync/init') ?>', {
                    method: 'POST',
                    body: requestPayload.formData,
                    headers: requestPayload.headers
                });

                const data = await parseResponsePayload(response);
                updateCsrfToken(data.csrfToken, data.csrfHash);

                if (!response.ok || !data.ok) {
                    throw new Error(data.message || 'Gagal memulai sinkronisasi.');
                }

                setSyncProgress(0, data.message || 'Sinkronisasi dimulai...');
                setSyncMeta(data.syncMeta || null);
                processSyncStep();
            } catch (error) {
                setSyncButtonsState(false);
                setResumeVisible(true);

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

        async function resumeProductionSync() {
            if (!syncForm) {
                return;
            }

            setSyncButtonsState(true);
            if (syncProgressWrapper) {
                syncProgressWrapper.classList.remove('d-none');
            }

            try {
                const requestPayload = buildSyncRequestPayload();
                const response = await fetch('<?= site_url('setting/application/sync/resume') ?>', {
                    method: 'POST',
                    body: requestPayload.formData,
                    headers: requestPayload.headers
                });

                const data = await parseResponsePayload(response);
                updateCsrfToken(data.csrfToken, data.csrfHash);

                if (!response.ok || !data.ok) {
                    throw new Error(data.message || 'Tidak ada proses yang dapat dilanjutkan.');
                }

                setResumeVisible(false);
                setSyncProgress(data.progress || 0, data.message || 'Melanjutkan sinkronisasi...');
                setSyncMeta(data.syncMeta || null);
                processSyncStep();
            } catch (error) {
                setSyncButtonsState(false);
                setResumeVisible(true);

                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal melanjutkan',
                        text: error && error.message ? error.message : 'Tidak dapat melanjutkan sinkronisasi.'
                    });
                }
            }
        }

        async function loadSyncState() {
            if (!syncForm) {
                return;
            }

            try {
                const response = await fetch('<?= site_url('setting/application/sync/state') ?>', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();
                updateCsrfToken(data.csrfToken, data.csrfHash);

                if (!response.ok || !data.ok || !data.active) {
                    setResumeVisible(false);
                    return;
                }

                if (syncProgressWrapper) {
                    syncProgressWrapper.classList.remove('d-none');
                }

                setSyncProgress(data.progress || 0, data.message || 'Terdapat proses sinkronisasi yang belum selesai.');
                setSyncMeta(data.syncMeta || null);
                setResumeVisible(true);
            } catch (error) {
                setResumeVisible(false);
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

        if (syncResumeButton) {
            syncResumeButton.addEventListener('click', resumeProductionSync);
        }

        if (syncScopeInputs.length > 0) {
            syncScopeInputs.forEach(function (input) {
                input.addEventListener('change', syncSelectedTableUiState);
            });
        }

        if (syncSelectAllTables) {
            syncSelectAllTables.addEventListener('change', function () {
                const checked = !!syncSelectAllTables.checked;
                syncTableCheckboxes.forEach(function (checkbox) {
                    if (!checkbox.disabled) {
                        checkbox.checked = checked;
                    }
                });

                updateSelectedTablesSummary();
            });
        }

        if (syncTableCheckboxes.length > 0) {
            syncTableCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    if (!syncSelectAllTables || checkbox.disabled) {
                        return;
                    }

                    const enabledCheckboxes = Array.from(syncTableCheckboxes).filter(function (item) {
                        return !item.disabled;
                    });

                    if (enabledCheckboxes.length === 0) {
                        syncSelectAllTables.checked = false;
                        return;
                    }

                    syncSelectAllTables.checked = enabledCheckboxes.every(function (item) {
                        return item.checked;
                    });

                    updateSelectedTablesSummary();
                });
            });
        }

        syncSelectedTableUiState();
        loadSyncState();

        if (btnRunMigrate) {
            btnRunMigrate.addEventListener('click', function () {
                runMaintenanceCommand('<?= site_url('setting/application/tools/migrate') ?>');
            });
        }

        if (btnRunGitPull) {
            btnRunGitPull.addEventListener('click', function () {
                runMaintenanceCommand('<?= site_url('setting/application/tools/git-pull') ?>');
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

        if (btnRunSnakeCaseScenario) {
            btnRunSnakeCaseScenario.addEventListener('click', function () {
                runMaintenanceCommand('<?= site_url('setting/application/tools/snake-case') ?>');
            });
        }
    })();
</script>
<?= $this->endSection() ?>
