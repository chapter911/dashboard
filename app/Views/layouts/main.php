<!doctype html>
<html
    lang="en"
    class="light-style layout-navbar-fixed layout-menu-fixed"
    dir="ltr"
    data-theme="theme-default"
    data-assets-path="<?= base_url('assets/') ?>/"
    data-template="vertical-menu-template"
>
<head>
    <?php
    $branding = $branding ?? [];
    $appName = $branding['app_name'] ?? 'Dashboard PLN';
    $faviconUrl = $branding['app_logo_url'] ?? base_url('favicon.ico');
    $primaryColor = $branding['app_primary_color'] ?? '#0a66c2';
    $primaryColorDeep = $branding['app_primary_color_deep'] ?? '#074c91';
    $autoLogoutMinutes = (int) ($branding['auto_logout_minutes'] ?? 30);
    if ($autoLogoutMinutes < 1) {
        $autoLogoutMinutes = 1;
    }
    if ($autoLogoutMinutes > 1440) {
        $autoLogoutMinutes = 1440;
    }
    $autoLogoutMs = $autoLogoutMinutes * 60 * 1000;
    $isDevelopmentEnv = ENVIRONMENT === 'development';
    $primaryHex = ltrim((string) $primaryColor, '#');
    $primaryR = ctype_xdigit($primaryHex) && strlen($primaryHex) === 6 ? hexdec(substr($primaryHex, 0, 2)) : 10;
    $primaryG = ctype_xdigit($primaryHex) && strlen($primaryHex) === 6 ? hexdec(substr($primaryHex, 2, 2)) : 102;
    $primaryB = ctype_xdigit($primaryHex) && strlen($primaryHex) === 6 ? hexdec(substr($primaryHex, 4, 2)) : 194;
    $primaryRgb = $primaryR . ', ' . $primaryG . ', ' . $primaryB;
    $pageTitle = trim((string) ($title ?? ''));
    $browserTitle = $appName . ' -' . ($pageTitle !== '' ? ' ' . $pageTitle : '');
    ?>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title><?= esc($browserTitle) ?></title>

    <link rel="icon" href="<?= esc($faviconUrl) ?>" />
    <link rel="stylesheet" href="<?= base_url('assets/vendor/fonts/tabler-icons.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('assets/vendor/fonts/fontawesome.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('assets/vendor/css/core.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('assets/vendor/css/theme-default.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('assets/css/demo.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') ?>" />

    <style>
        :root {
            --app-primary: <?= esc($primaryColor) ?>;
            --app-primary-deep: <?= esc($primaryColorDeep) ?>;
            --bs-primary: var(--app-primary);
            --app-primary-rgb: <?= esc($primaryRgb) ?>;
        }

        .btn-primary,
        .btn-primary:focus,
        .btn-primary:active,
        .btn-primary:hover {
            background-color: var(--app-primary) !important;
            border-color: var(--app-primary) !important;
        }

        .text-primary,
        a,
        a:hover {
            color: var(--app-primary);
        }

        .bg-primary {
            background-color: var(--app-primary) !important;
        }

        .menu-vertical .menu-item .menu-link:hover {
            color: var(--app-primary) !important;
            background: rgba(var(--app-primary-rgb), 0.12) !important;
        }

        .menu-vertical .menu-item.active > .menu-link {
            color: #fff !important;
            background: linear-gradient(90deg, var(--app-primary), var(--app-primary-deep)) !important;
            box-shadow: 0 0.3rem 0.8rem rgba(var(--app-primary-rgb), 0.45) !important;
        }

        .menu-vertical .menu-item.active > .menu-link i,
        .menu-vertical .menu-item.active > .menu-link div {
            color: #fff !important;
        }

        .menu-vertical .menu-item.active > .menu-link.menu-toggle::after {
            color: #fff !important;
            border-color: #fff !important;
        }

        .menu-vertical .menu-item.active > .menu-link::before {
            background: #fff !important;
        }

        .table > :not(caption) > thead > tr > th,
        .table thead th {
            background-color: var(--app-primary) !important;
            color: #fff !important;
            border-color: #fff !important;
        }

        .table > :not(caption) > tfoot > tr > th,
        .table > :not(caption) > tfoot > tr > td,
        .table tfoot th,
        .table tfoot td {
            background-color: var(--app-primary) !important;
            color: #fff !important;
            border-color: #fff !important;
        }

        .table > :not(caption) > tbody > tr > th,
        .table > :not(caption) > tbody > tr > td,
        .table tbody th,
        .table tbody td {
            border-color: rgba(var(--app-primary-rgb), 0.28) !important;
        }

        .table > :not(caption) > tbody > tr > * {
            border-bottom: 1px solid rgba(var(--app-primary-rgb), 0.28) !important;
        }

        .table.table-bordered > :not(caption) > tbody > tr > th,
        .table.table-bordered > :not(caption) > tbody > tr > td,
        .table-bordered > tbody > tr > th,
        .table-bordered > tbody > tr > td {
            border: 1px solid rgba(var(--app-primary-rgb), 0.28) !important;
        }

        .table > tbody > tr:nth-of-type(odd) > *,
        .table-striped > tbody > tr:nth-of-type(odd) > * {
            background-color: rgba(var(--app-primary-rgb), 0.09) !important;
        }

        .table > tbody > tr:nth-of-type(even) > * {
            background-color: rgba(var(--app-primary-rgb), 0.02) !important;
        }

        /* Global DataTables pagination theme follows app primary color. */
        .dataTables_wrapper .dataTables_paginate .pagination .page-item.active .page-link,
        .dataTables_wrapper .dataTables_paginate .pagination .page-item.active > .page-link {
            background-color: var(--app-primary) !important;
            border-color: var(--app-primary) !important;
            color: #fff !important;
            box-shadow: 0 0.2rem 0.5rem rgba(var(--app-primary-rgb), 0.35);
        }

        .dataTables_wrapper .dataTables_paginate .pagination .page-item .page-link {
            color: var(--app-primary) !important;
        }

        .dataTables_wrapper .dataTables_paginate .pagination .page-item:not(.active) .page-link:hover,
        .dataTables_wrapper .dataTables_paginate .pagination .page-item:not(.active) .page-link:focus {
            background-color: rgba(var(--app-primary-rgb), 0.1) !important;
            border-color: rgba(var(--app-primary-rgb), 0.35) !important;
            color: var(--app-primary-deep) !important;
        }

        /* Keep table blocks inside cards from touching card edges. */
        .card > .table-responsive {
            padding: 0 1rem 1rem;
        }

        .card > .table-responsive .table {
            margin-bottom: 0;
        }

        .card > .table-responsive .dataTables_wrapper .row {
            margin-left: 0;
            margin-right: 0;
        }

        .card > .table-responsive .dataTables_wrapper .row > [class*='col-'] {
            padding-left: 0;
            padding-right: 0;
        }

        .swal2-container {
            z-index: 20000 !important;
        }

        <?php if ($isDevelopmentEnv): ?>
        #layout-navbar {
            background: #d32f2f !important;
            border-color: #b71c1c !important;
        }

        #layout-navbar h5,
        #layout-navbar .nav-link,
        #layout-navbar .btn,
        #layout-navbar .dropdown-toggle,
        #layout-navbar i {
            color: #fff !important;
        }

        #layout-navbar .btn-outline-secondary {
            border-color: rgba(255, 255, 255, 0.65) !important;
        }
        <?php endif; ?>
    </style>

    <script src="<?= base_url('assets/vendor/js/helpers.js') ?>"></script>
    <script src="<?= base_url('assets/js/config.js') ?>"></script>
</head>

<body>
    <?php
    $logoUrl = $branding['app_logo_url'] ?? base_url('assets/img/branding/logo.png');
    $uriPath = trim(uri_string(), '/');
    $isDashboard = $uriPath === 'dashboard';
    $isSetting = $uriPath === 'setting';
    ?>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?= $this->include('layouts/partials/sidebar') ?>

            <div class="layout-page">
                <?= $this->include('layouts/partials/navbar') ?>

                <?= $this->include('layouts/partials/content') ?>
            </div>
        </div>
    </div>

    <script src="<?= base_url('assets/vendor/libs/jquery/jquery.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/libs/popper/popper.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/js/bootstrap.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/js/menu.js') ?>"></script>
    <script src="<?= base_url('assets/js/main.js') ?>"></script>
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ganti Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?= site_url('change-password') ?>" method="post" novalidate>
                    <div class="modal-body">
                        <?= csrf_field() ?>

                        <?php if (session()->getFlashdata('password_error')): ?>
                            <div class="alert alert-danger" role="alert"><?= esc(session()->getFlashdata('password_error')) ?></div>
                        <?php endif; ?>

                        <?php $passwordErrors = session()->getFlashdata('password_errors'); ?>
                        <?php if (! empty($passwordErrors) && is_array($passwordErrors)): ?>
                            <div class="alert alert-danger" role="alert">
                                <ul class="mb-0 ps-3">
                                    <?php foreach ($passwordErrors as $error): ?>
                                        <li><?= esc($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label" for="modal_current_password">Password Saat Ini</label>
                            <input
                                type="password"
                                id="modal_current_password"
                                name="current_password"
                                class="form-control"
                                required
                                autocomplete="current-password"
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="modal_new_password">Password Baru</label>
                            <input
                                type="password"
                                id="modal_new_password"
                                name="new_password"
                                class="form-control"
                                required
                                autocomplete="new-password"
                            >
                            <div class="form-text">Minimal 8 karakter, mengandung huruf besar, huruf kecil, angka, dan simbol.</div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label" for="modal_new_password_confirmation">Konfirmasi Password Baru</label>
                            <input
                                type="password"
                                id="modal_new_password_confirmation"
                                name="new_password_confirmation"
                                class="form-control"
                                required
                                autocomplete="new-password"
                            >
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i> Simpan Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function () {
            const autoLogoutMs = <?= (int) $autoLogoutMs ?>;
            const shouldOpenChangePasswordModal = <?= session()->getFlashdata('open_change_password_modal') ? 'true' : 'false' ?>;

            function showSwalLoading(title) {
                if (!window.Swal) {
                    return;
                }

                Swal.fire({
                    title: title,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: function () {
                        Swal.showLoading();
                    }
                });
            }

            document.addEventListener('submit', function (event) {
                const form = event.target;
                if (!(form instanceof HTMLFormElement)) {
                    return;
                }

                const method = (form.getAttribute('method') || 'get').toLowerCase();
                const shouldShowForGet = method === 'get' && form.dataset.loading === '1';

                if ((method !== 'post' && !shouldShowForGet) || form.dataset.skipSwalLoading === '1') {
                    return;
                }

                const title = method === 'post'
                    ? (form.dataset.loadingText || 'Menyimpan data...')
                    : (form.dataset.loadingText || 'Memuat data...');

                showSwalLoading(title);
            });

            document.addEventListener('click', function (event) {
                const trigger = event.target.closest('a[data-loading="1"]');
                if (!trigger) {
                    return;
                }

                if (trigger.dataset.skipSwalLoading === '1') {
                    return;
                }

                const href = trigger.getAttribute('href') || '';
                if (href === '' || href.charAt(0) === '#') {
                    return;
                }

                const title = trigger.dataset.loadingText || 'Memuat data...';
                showSwalLoading(title);
            });

            let idleTimerId = null;
            let warningTimerId = null;
            let warningIntervalId = null;
            let idleWarningOpen = false;
            let isAutoLoggingOut = false;
            const warningDurationMs = 30000;
            const activityEvents = ['mousemove', 'mousedown', 'keydown', 'touchstart', 'scroll'];

            function triggerAutoLogout() {
                isAutoLoggingOut = true;
                const logoutForm = document.getElementById('navbar-logout-form') || document.querySelector('form[action$="/logout"]');
                if (!logoutForm) {
                    window.location.href = '<?= site_url('/') ?>';
                    return;
                }

                logoutForm.submit();
            }

            function clearWarningState() {
                if (warningTimerId !== null) {
                    clearTimeout(warningTimerId);
                    warningTimerId = null;
                }

                if (warningIntervalId !== null) {
                    clearInterval(warningIntervalId);
                    warningIntervalId = null;
                }

                if (idleWarningOpen && window.Swal) {
                    Swal.close();
                }

                idleWarningOpen = false;
            }

            function showIdleWarning() {
                if (!window.Swal || isAutoLoggingOut) {
                    return;
                }

                idleWarningOpen = true;

                Swal.fire({
                    icon: 'warning',
                    title: 'Sesi akan berakhir',
                    html: 'Anda akan logout otomatis dalam <strong id="idle-countdown">30</strong> detik karena tidak ada aktivitas.',
                    timer: warningDurationMs,
                    timerProgressBar: true,
                    showConfirmButton: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonText: 'Saya masih aktif',
                    didOpen: function () {
                        const countdown = Swal.getHtmlContainer()
                            ? Swal.getHtmlContainer().querySelector('#idle-countdown')
                            : null;

                        warningIntervalId = window.setInterval(function () {
                            if (!countdown) {
                                return;
                            }

                            const timerLeftMs = Swal.getTimerLeft();
                            const secondsLeft = Math.max(0, Math.ceil((timerLeftMs || 0) / 1000));
                            countdown.textContent = String(secondsLeft);
                        }, 200);
                    },
                    willClose: function () {
                        if (warningIntervalId !== null) {
                            clearInterval(warningIntervalId);
                            warningIntervalId = null;
                        }

                        idleWarningOpen = false;
                    }
                }).then(function (result) {
                    if (result.isConfirmed && !isAutoLoggingOut) {
                        resetIdleTimer();
                    }
                });
            }

            function resetIdleTimer() {
                if (isAutoLoggingOut) {
                    return;
                }

                if (idleTimerId !== null) {
                    clearTimeout(idleTimerId);
                }

                clearWarningState();

                const warningDelayMs = Math.max(0, autoLogoutMs - warningDurationMs);
                warningTimerId = window.setTimeout(showIdleWarning, warningDelayMs);

                idleTimerId = window.setTimeout(triggerAutoLogout, autoLogoutMs);
            }

            if (autoLogoutMs > 0) {
                activityEvents.forEach(function (eventName) {
                    window.addEventListener(eventName, resetIdleTimer, { passive: true });
                });

                resetIdleTimer();
            }

            if (shouldOpenChangePasswordModal) {
                const modalElement = document.getElementById('changePasswordModal');
                if (modalElement && window.bootstrap && typeof window.bootstrap.Modal === 'function') {
                    const modal = new window.bootstrap.Modal(modalElement);
                    modal.show();
                }
            }
        })();
    </script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
