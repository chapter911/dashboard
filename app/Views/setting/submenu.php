<?php
$activeSettingTab = $activeSettingTab ?? 'application';
?>

<div class="row mb-4">
    <div class="col-12">
        <ul class="nav nav-pills">
            <li class="nav-item">
                <a
                    class="nav-link <?= $activeSettingTab === 'application' ? 'active' : '' ?>"
                    href="<?= site_url('setting/application') ?>"
                    data-loading="1"
                    data-loading-text="Membuka setting aplikasi..."
                >
                    Application
                </a>
            </li>
            <li class="nav-item">
                <a
                    class="nav-link <?= $activeSettingTab === 'menu' ? 'active' : '' ?>"
                    href="<?= site_url('setting/menu') ?>"
                    data-loading="1"
                    data-loading-text="Membuka setting menu..."
                >
                    Menu
                </a>
            </li>
        </ul>
    </div>
</div>
