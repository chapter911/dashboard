<nav class="layout-navbar container-fluid navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
    <?php
    $currentUsername = (string) session('username');
    $currentName = trim((string) session('nama'));
    $displayName = $currentName !== '' ? $currentName : $currentUsername;
    $profilePhotoPath = trim((string) session('profile_photo_path'));
    $profilePhotoUrl = $profilePhotoPath !== '' ? base_url($profilePhotoPath) : null;
    ?>
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="ti ti-menu-2 ti-sm"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center w-100" id="navbar-collapse">
        <div class="navbar-nav align-items-center me-auto">
            <h5 class="mb-0"><?= esc($pageHeading ?? 'Dashboard') ?></h5>
        </div>

        <ul class="navbar-nav flex-row align-items-center ms-auto">
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a
                    class="nav-link dropdown-toggle hide-arrow d-flex align-items-center gap-2"
                    href="javascript:void(0);"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                >
                    <span class="avatar avatar-sm rounded-circle overflow-hidden <?= $profilePhotoUrl ? '' : 'bg-label-primary' ?>">
                        <?php if (is_string($profilePhotoUrl) && $profilePhotoUrl !== ''): ?>
                            <img src="<?= esc($profilePhotoUrl) ?>" alt="Avatar" class="w-100 h-100" style="object-fit: cover;">
                        <?php else: ?>
                            <span class="avatar-initial rounded-circle fw-semibold">
                                <?= esc(strtoupper(mb_substr($displayName, 0, 1))) ?>
                            </span>
                        <?php endif; ?>
                    </span>
                    <span class="d-none d-md-inline fw-semibold"><?= esc($displayName) ?></span>
                </a>

                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="<?= site_url('profile') ?>" data-loading="1" data-loading-text="Membuka profil...">
                            <i class="ti ti-user me-2"></i>
                            <span>Profil</span>
                        </a>
                    </li>
                    <li>
                        <button
                            type="button"
                            class="dropdown-item"
                            data-bs-toggle="modal"
                            data-bs-target="#changePasswordModal"
                        >
                            <i class="ti ti-lock me-2"></i>
                            <span>Ganti Password</span>
                        </button>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <form id="navbar-logout-form" action="<?= site_url('logout') ?>" method="post" class="m-0" data-skip-swal-loading="1">
                            <?= csrf_field() ?>
                            <button class="dropdown-item text-danger" type="submit">
                                <i class="ti ti-logout me-2"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
