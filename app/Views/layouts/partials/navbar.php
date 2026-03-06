<nav class="layout-navbar container-fluid navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
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
            <li class="nav-item">
                <form action="<?= site_url('logout') ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <button class="btn btn-danger btn-sm" type="submit">
                        <i class="ti ti-logout me-1"></i> Logout
                    </button>
                </form>
            </li>
        </ul>
    </div>
</nav>
