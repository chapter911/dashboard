<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$menuLv1 = is_array($menuLv1 ?? null) ? $menuLv1 : [];
$menuLv2 = is_array($menuLv2 ?? null) ? $menuLv2 : [];
$menuLv3 = is_array($menuLv3 ?? null) ? $menuLv3 : [];
$iconOptions = [
    'ti-point',
    'ti-smart-home',
    'ti-settings',
    'ti-menu-2',
    'ti-folder',
    'ti-folder-open',
    'ti-layout-dashboard',
    'ti-users',
    'ti-user',
    'ti-shield-check',
    'ti-lock',
    'ti-key',
    'ti-file-text',
    'ti-report-analytics',
    'ti-chart-bar',
    'ti-database',
    'ti-building',
    'ti-clipboard-list',
    'ti-calendar-event',
    'ti-tool',
    'ti-bolt',
    'ti-apps',
];

$lv3ByHeader = [];
foreach ($menuLv3 as $row) {
    $header = (string) ($row['header'] ?? '');
    if ($header === '') {
        continue;
    }

    $lv3ByHeader[$header][] = $row;
}

$lv2ByHeader = [];
foreach ($menuLv2 as $row) {
    $header = (string) ($row['header'] ?? '');
    if ($header === '') {
        continue;
    }

    $id = (string) ($row['id'] ?? '');
    $row['children'] = $id !== '' ? ($lv3ByHeader[$id] ?? []) : [];
    $lv2ByHeader[$header][] = $row;
}
?>

<style>
    .menu-tree-wrapper {
        background: #f8fafc;
        border-radius: 1rem;
        padding: 1.25rem;
    }

    .menu-level {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .menu-level-2,
    .menu-level-3 {
        margin-top: 0.7rem;
        margin-left: 2rem;
        padding-left: 1.2rem;
        border-left: 2px dashed #cfd8e3;
    }

    .menu-node {
        margin-bottom: 0.7rem;
    }

    .menu-toolbar {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 0.9rem;
    }

    .menu-unsaved-indicator {
        display: none;
        font-size: 0.85rem;
        color: #7a4b00;
        background: #fff3cd;
        border: 1px solid #ffe69c;
        border-radius: 999px;
        padding: 0.25rem 0.7rem;
        margin-right: auto;
    }

    .menu-unsaved-indicator.show {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .menu-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.9rem;
        background: #eef2f7;
        border: 1px solid #d7dee8;
        border-radius: 0.85rem;
        padding: 0.7rem 0.95rem;
    }

    .menu-row-main {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        min-width: 0;
    }

    .menu-drag {
        color: #6b7a90;
        cursor: grab;
    }

    .menu-drag:active {
        cursor: grabbing;
    }

    .menu-order-badge {
        min-width: 22px;
        height: 22px;
        border-radius: 999px;
        background: #dbe4ef;
        color: #45607f;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.72rem;
        font-weight: 700;
    }

    .menu-icon {
        color: #17385f;
    }

    .menu-label-input {
        border: 0;
        background: transparent;
        font-weight: 600;
        color: #0b2647;
        min-width: 180px;
        width: 100%;
    }

    .menu-label-input:focus {
        outline: none;
        box-shadow: none;
    }

    .menu-link-pill {
        border: 0;
        border-radius: 999px;
        background: #e4eaf1;
        color: #5b6c82;
        font-size: 0.85rem;
        text-align: right;
        padding: 0.3rem 0.65rem;
        min-width: 180px;
        max-width: 280px;
    }

    .menu-link-pill:focus {
        outline: none;
        box-shadow: none;
    }

    .menu-meta-grid {
        display: none;
        grid-template-columns: 160px 120px 120px;
        gap: 0.45rem;
        margin-top: 0.35rem;
        margin-left: 2.2rem;
    }

    .menu-node.expanded > .menu-meta-grid {
        display: grid;
    }

    .menu-node-sortable-ghost {
        opacity: 0.45;
    }

    .menu-node-sortable-chosen .menu-row {
        border-color: #9fb2c8;
        box-shadow: 0 0.35rem 1rem rgba(31, 58, 92, 0.12);
    }

    .menu-meta-grid input {
        font-size: 0.8rem;
        height: 30px;
    }

    .menu-parent-invalid {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.15rem rgba(220, 53, 69, 0.12) !important;
    }

    .menu-parent-hint {
        margin-top: 0.45rem;
        margin-left: 2.2rem;
        font-size: 0.78rem;
        color: #dc3545;
        display: none;
    }

    .menu-parent-hint.show {
        display: block;
    }

    .icon-badge-preview {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        background: #e4eaf1;
        color: #29486d;
    }

    .icon-picker-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(145px, 1fr));
        gap: 0.55rem;
        max-height: 320px;
        overflow: auto;
    }

    .icon-picker-item {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        border: 1px solid #d7dee8;
        background: #f8fafc;
        color: #2a4568;
        border-radius: 0.6rem;
        padding: 0.42rem 0.5rem;
        cursor: pointer;
        text-align: left;
        font-size: 0.78rem;
    }

    .icon-picker-item:hover,
    .icon-picker-item.active {
        border-color: var(--app-primary);
        background: rgba(var(--app-primary-rgb), 0.1);
    }

    @media (max-width: 991px) {
        .menu-row {
            flex-direction: column;
            align-items: stretch;
        }

        .menu-link-pill {
            min-width: 100%;
            max-width: 100%;
            text-align: left;
        }

        .menu-meta-grid {
            grid-template-columns: 1fr;
            margin-left: 0;
        }
    }
</style>

<div class="row">
    <div class="col-12">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success" role="alert"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-1">Konfigurasi Menu</h5>
                <small class="text-muted">Atur urutan, icon, label, link, dan relasi parent menu.</small>
            </div>
            <div class="card-body">
                <form action="<?= site_url('setting/menu') ?>" method="post" novalidate>
                    <?= csrf_field() ?>

                    <div class="menu-toolbar">
                        <span class="menu-unsaved-indicator" id="menu_unsaved_indicator">
                            <i class="ti ti-alert-circle"></i> Perubahan belum disimpan
                        </span>
                        <button type="button" class="btn btn-sm btn-label-secondary" id="btn_expand_all_menu_detail">Tampilkan Detail</button>
                        <button type="button" class="btn btn-sm btn-label-secondary" id="btn_collapse_all_menu_detail">Sembunyikan Detail</button>
                    </div>

                    <div class="menu-tree-wrapper mb-4">
                        <ul class="menu-level menu-level-1 js-sortable" data-level="lv1">
                            <?php if ($menuLv1 === []): ?>
                                <li class="text-muted">Data menu belum tersedia.</li>
                            <?php endif; ?>

                            <?php foreach ($menuLv1 as $lv1): ?>
                                <?php
                                $lv1Id = (string) ($lv1['id'] ?? '');
                                $lv1Children = $lv2ByHeader[$lv1Id] ?? [];
                                $lv1IconValue = (string) ($lv1['icon'] ?? 'ti-point');
                                ?>
                                <li class="menu-node" data-level="lv1" data-menu-id="<?= esc($lv1Id) ?>">
                                    <div class="menu-row">
                                        <div class="menu-row-main">
                                            <span class="menu-order-badge js-order-badge">0</span>
                                            <i class="ti ti-menu-2 menu-drag js-drag-handle"></i>
                                            <i class="ti ti-folder menu-icon"></i>
                                            <input type="text" class="menu-label-input" name="lv1[<?= esc($lv1Id) ?>][label]" value="<?= esc((string) ($lv1['label'] ?? '')) ?>">
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary btn-open-icon-modal"
                                                data-target="icon_lv1_<?= esc($lv1Id) ?>"
                                                data-current-icon="<?= esc($lv1IconValue) ?>"
                                            >
                                                <i class="ti <?= esc($lv1IconValue !== '' ? $lv1IconValue : 'ti-point') ?> me-1 js-icon-preview" data-target="icon_lv1_<?= esc($lv1Id) ?>"></i>
                                                Icon
                                            </button>
                                            <input type="text" class="menu-link-pill" name="lv1[<?= esc($lv1Id) ?>][link]" value="<?= esc((string) ($lv1['link'] ?? '#')) ?>">
                                            <button type="button" class="btn btn-sm btn-label-secondary btn-menu-detail">Detail</button>
                                        </div>
                                    </div>
                                    <div class="menu-meta-grid">
                                        <input type="hidden" id="icon_lv1_<?= esc($lv1Id) ?>" name="lv1[<?= esc($lv1Id) ?>][icon]" value="<?= esc($lv1IconValue) ?>" class="js-icon-value">
                                        <span class="icon-badge-preview"><i class="ti <?= esc($lv1IconValue !== '' ? $lv1IconValue : 'ti-point') ?> js-icon-preview" data-target="icon_lv1_<?= esc($lv1Id) ?>"></i></span>
                                        <input type="text" class="form-control form-control-sm" name="lv1[<?= esc($lv1Id) ?>][old_icon]" value="<?= esc((string) ($lv1['old_icon'] ?? '')) ?>" placeholder="Old Icon">
                                        <input type="number" class="form-control form-control-sm js-order-input" data-level="lv1" data-menu-id="<?= esc($lv1Id) ?>" name="lv1[<?= esc($lv1Id) ?>][ordering]" value="<?= esc((string) ($lv1['ordering'] ?? '')) ?>" placeholder="Urutan">
                                    </div>

                                    <?php if ($lv1Children !== []): ?>
                                        <ul class="menu-level menu-level-2 js-sortable" data-level="lv2">
                                            <?php foreach ($lv1Children as $lv2): ?>
                                                <?php
                                                $lv2Id = (string) ($lv2['id'] ?? '');
                                                $lv2Children = is_array($lv2['children'] ?? null) ? $lv2['children'] : [];
                                                $lv2IconValue = (string) ($lv2['icon'] ?? 'ti-point');
                                                ?>
                                                <li class="menu-node" data-level="lv2" data-menu-id="<?= esc($lv2Id) ?>">
                                                    <div class="menu-row">
                                                        <div class="menu-row-main">
                                                            <span class="menu-order-badge js-order-badge">0</span>
                                                            <i class="ti ti-menu-2 menu-drag js-drag-handle"></i>
                                                            <i class="ti ti-folder menu-icon"></i>
                                                            <input type="text" class="menu-label-input" name="lv2[<?= esc($lv2Id) ?>][label]" value="<?= esc((string) ($lv2['label'] ?? '')) ?>">
                                                        </div>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <button
                                                                type="button"
                                                                class="btn btn-sm btn-outline-primary btn-open-icon-modal"
                                                                data-target="icon_lv2_<?= esc($lv2Id) ?>"
                                                                data-current-icon="<?= esc($lv2IconValue) ?>"
                                                            >
                                                                <i class="ti <?= esc($lv2IconValue !== '' ? $lv2IconValue : 'ti-point') ?> me-1 js-icon-preview" data-target="icon_lv2_<?= esc($lv2Id) ?>"></i>
                                                                Icon
                                                            </button>
                                                            <input type="text" class="menu-link-pill" name="lv2[<?= esc($lv2Id) ?>][link]" value="<?= esc((string) ($lv2['link'] ?? '#')) ?>">
                                                            <button type="button" class="btn btn-sm btn-label-secondary btn-menu-detail">Detail</button>
                                                        </div>
                                                    </div>
                                                    <div class="menu-meta-grid">
                                                        <input type="hidden" id="icon_lv2_<?= esc($lv2Id) ?>" name="lv2[<?= esc($lv2Id) ?>][icon]" value="<?= esc($lv2IconValue) ?>" class="js-icon-value">
                                                        <span class="icon-badge-preview"><i class="ti <?= esc($lv2IconValue !== '' ? $lv2IconValue : 'ti-point') ?> js-icon-preview" data-target="icon_lv2_<?= esc($lv2Id) ?>"></i></span>
                                                        <input type="text" class="form-control form-control-sm js-parent-input" data-parent-level="lv1" data-level="lv2" name="lv2[<?= esc($lv2Id) ?>][header]" value="<?= esc((string) ($lv2['header'] ?? '')) ?>" placeholder="Header LV1">
                                                        <input type="number" class="form-control form-control-sm js-order-input" data-level="lv2" data-menu-id="<?= esc($lv2Id) ?>" name="lv2[<?= esc($lv2Id) ?>][ordering]" value="<?= esc((string) ($lv2['ordering'] ?? '')) ?>" placeholder="Urutan">
                                                    </div>
                                                    <div class="menu-parent-hint">Header parent LV1 tidak ditemukan.</div>

                                                    <?php if ($lv2Children !== []): ?>
                                                        <ul class="menu-level menu-level-3 js-sortable" data-level="lv3">
                                                            <?php foreach ($lv2Children as $lv3): ?>
                                                                <?php
                                                                $lv3Id = (string) ($lv3['id'] ?? '');
                                                                $lv3IconValue = (string) ($lv3['icon'] ?? 'ti-point');
                                                                ?>
                                                                <li class="menu-node" data-level="lv3" data-menu-id="<?= esc($lv3Id) ?>">
                                                                    <div class="menu-row">
                                                                        <div class="menu-row-main">
                                                                            <span class="menu-order-badge js-order-badge">0</span>
                                                                            <i class="ti ti-menu-2 menu-drag js-drag-handle"></i>
                                                                            <i class="ti ti-folder menu-icon"></i>
                                                                            <input type="text" class="menu-label-input" name="lv3[<?= esc($lv3Id) ?>][label]" value="<?= esc((string) ($lv3['label'] ?? '')) ?>">
                                                                        </div>
                                                                        <div class="d-flex align-items-center gap-2">
                                                                            <button
                                                                                type="button"
                                                                                class="btn btn-sm btn-outline-primary btn-open-icon-modal"
                                                                                data-target="icon_lv3_<?= esc($lv3Id) ?>"
                                                                                data-current-icon="<?= esc($lv3IconValue) ?>"
                                                                            >
                                                                                <i class="ti <?= esc($lv3IconValue !== '' ? $lv3IconValue : 'ti-point') ?> me-1 js-icon-preview" data-target="icon_lv3_<?= esc($lv3Id) ?>"></i>
                                                                                Icon
                                                                            </button>
                                                                            <input type="text" class="menu-link-pill" name="lv3[<?= esc($lv3Id) ?>][link]" value="<?= esc((string) ($lv3['link'] ?? '#')) ?>">
                                                                            <button type="button" class="btn btn-sm btn-label-secondary btn-menu-detail">Detail</button>
                                                                        </div>
                                                                    </div>
                                                                    <div class="menu-meta-grid">
                                                                        <input type="hidden" id="icon_lv3_<?= esc($lv3Id) ?>" name="lv3[<?= esc($lv3Id) ?>][icon]" value="<?= esc($lv3IconValue) ?>" class="js-icon-value">
                                                                        <span class="icon-badge-preview"><i class="ti <?= esc($lv3IconValue !== '' ? $lv3IconValue : 'ti-point') ?> js-icon-preview" data-target="icon_lv3_<?= esc($lv3Id) ?>"></i></span>
                                                                        <input type="text" class="form-control form-control-sm js-parent-input" data-parent-level="lv2" data-level="lv3" name="lv3[<?= esc($lv3Id) ?>][header]" value="<?= esc((string) ($lv3['header'] ?? '')) ?>" placeholder="Header LV2">
                                                                        <input type="number" class="form-control form-control-sm js-order-input" data-level="lv3" data-menu-id="<?= esc($lv3Id) ?>" name="lv3[<?= esc($lv3Id) ?>][ordering]" value="<?= esc((string) ($lv3['ordering'] ?? '')) ?>" placeholder="Urutan">
                                                                    </div>
                                                                    <div class="menu-parent-hint">Header parent LV2 tidak ditemukan.</div>
                                                                </li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <button class="btn btn-primary" type="submit">
                        <i class="ti ti-device-floppy me-1"></i> Simpan Konfigurasi Menu
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="iconPickerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Icon Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" for="icon_picker_search">Cari Icon</label>
                    <input type="text" id="icon_picker_search" class="form-control" placeholder="Ketik: home, user, settings, dll">
                </div>
                <div class="icon-picker-grid" id="icon_picker_grid">
                    <?php foreach ($iconOptions as $iconOption): ?>
                        <button type="button" class="icon-picker-item" data-icon="<?= esc($iconOption) ?>">
                            <i class="ti <?= esc($iconOption) ?>"></i>
                            <span><?= esc($iconOption) ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
                <hr>
                <label class="form-label" for="icon_picker_custom">Icon Custom</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="ti ti-wand"></i></span>
                    <input type="text" id="icon_picker_custom" class="form-control" placeholder="Contoh: ti-bell-ringing">
                    <button type="button" class="btn btn-outline-primary" id="btn_apply_custom_icon">Pakai Icon Custom</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
    (function () {
        const root = document.querySelector('.menu-tree-wrapper');
        const form = document.querySelector('form[action="<?= site_url('setting/menu') ?>"]');
        const unsavedIndicator = document.getElementById('menu_unsaved_indicator');
        const storageKey = 'setting-menu-expanded-nodes-v1';
        const iconPickerModalEl = document.getElementById('iconPickerModal');
        const iconPickerSearch = document.getElementById('icon_picker_search');
        const iconPickerGrid = document.getElementById('icon_picker_grid');
        const iconPickerCustom = document.getElementById('icon_picker_custom');
        const applyCustomIconButton = document.getElementById('btn_apply_custom_icon');

        if (!root || !form || !iconPickerModalEl || !iconPickerGrid) {
            return;
        }

        const iconPickerModal = window.bootstrap ? new bootstrap.Modal(iconPickerModalEl) : null;
        let activeIconTarget = '';

        let hasUnsavedChanges = false;

        function setUnsavedState(isUnsaved) {
            hasUnsavedChanges = isUnsaved;
            if (!unsavedIndicator) {
                return;
            }

            unsavedIndicator.classList.toggle('show', isUnsaved);
        }

        function getMenuIdsByLevel(level) {
            const ids = {};
            root.querySelectorAll('.menu-node[data-level="' + level + '"]').forEach(function (node) {
                const id = (node.getAttribute('data-menu-id') || '').trim();
                if (id !== '') {
                    ids[id] = true;
                }
            });

            return ids;
        }

        function validateParentHeaders() {
            const lv1Ids = getMenuIdsByLevel('lv1');
            const lv2Ids = getMenuIdsByLevel('lv2');
            let isValid = true;

            root.querySelectorAll('.js-parent-input').forEach(function (input) {
                const parentLevel = input.getAttribute('data-parent-level') || '';
                const value = (input.value || '').trim();
                const node = input.closest('.menu-node');
                const hint = node ? node.querySelector('.menu-parent-hint') : null;

                if (value === '') {
                    input.classList.remove('menu-parent-invalid');
                    if (hint) {
                        hint.classList.remove('show');
                    }
                    return;
                }

                const exists = parentLevel === 'lv1' ? !!lv1Ids[value] : !!lv2Ids[value];
                if (exists) {
                    input.classList.remove('menu-parent-invalid');
                    if (hint) {
                        hint.classList.remove('show');
                    }
                    return;
                }

                isValid = false;
                input.classList.add('menu-parent-invalid');
                if (hint) {
                    hint.classList.add('show');
                }
            });

            return isValid;
        }

        function applyIconToTarget(targetId, iconClass) {
            const selectedIcon = (iconClass || 'ti-point').trim() || 'ti-point';
            const hiddenInput = document.getElementById(targetId);
            if (!hiddenInput) {
                return;
            }

            hiddenInput.value = selectedIcon;

            const previewIcon = root.querySelector('.js-icon-preview[data-target="' + targetId + '"]');
            if (previewIcon) {
                previewIcon.className = 'ti ' + selectedIcon + ' js-icon-preview';
            }

            root.querySelectorAll('.js-icon-preview[data-target="' + targetId + '"]').forEach(function (iconEl) {
                iconEl.className = 'ti ' + selectedIcon + ' js-icon-preview';
            });

            root.querySelectorAll('.btn-open-icon-modal[data-target="' + targetId + '"]').forEach(function (button) {
                button.dataset.currentIcon = selectedIcon;
            });

            setUnsavedState(true);
        }

        function filterIconItems(keyword) {
            const query = (keyword || '').trim().toLowerCase();
            iconPickerGrid.querySelectorAll('.icon-picker-item').forEach(function (button) {
                const iconName = (button.getAttribute('data-icon') || '').toLowerCase();
                const match = query === '' || iconName.indexOf(query) >= 0;
                button.style.display = match ? '' : 'none';
            });
        }

        function markActiveIcon(iconClass) {
            const selectedIcon = (iconClass || '').trim();
            iconPickerGrid.querySelectorAll('.icon-picker-item').forEach(function (button) {
                const iconName = button.getAttribute('data-icon') || '';
                button.classList.toggle('active', iconName === selectedIcon);
            });
        }

        function getExpandedNodeKeys() {
            const keys = [];
            root.querySelectorAll('.menu-node.expanded').forEach(function (node) {
                const level = node.getAttribute('data-level') || '';
                const menuId = node.getAttribute('data-menu-id') || '';
                if (level !== '' && menuId !== '') {
                    keys.push(level + ':' + menuId);
                }
            });

            return keys;
        }

        function saveExpandedState() {
            try {
                localStorage.setItem(storageKey, JSON.stringify(getExpandedNodeKeys()));
            } catch (error) {
                // Ignore storage errors.
            }
        }

        function restoreExpandedState() {
            try {
                const raw = localStorage.getItem(storageKey);
                if (!raw) {
                    return;
                }

                const keys = JSON.parse(raw);
                if (!Array.isArray(keys)) {
                    return;
                }

                const lookup = {};
                keys.forEach(function (key) {
                    lookup[String(key)] = true;
                });

                root.querySelectorAll('.menu-node').forEach(function (node) {
                    const level = node.getAttribute('data-level') || '';
                    const menuId = node.getAttribute('data-menu-id') || '';
                    const key = level + ':' + menuId;
                    if (lookup[key]) {
                        node.classList.add('expanded');
                        const button = node.querySelector('.btn-menu-detail');
                        if (button) {
                            button.textContent = 'Tutup Detail';
                        }
                    }
                });
            } catch (error) {
                // Ignore parse/storage errors.
            }
        }

        function refreshOrdering(sortableList) {
            const level = sortableList.getAttribute('data-level') || '';
            const items = Array.from(sortableList.children).filter(function (element) {
                return element.classList.contains('menu-node');
            });

            items.forEach(function (item, index) {
                const menuId = item.getAttribute('data-menu-id') || '';
                const orderInput = item.querySelector('.js-order-input[data-level="' + level + '"][data-menu-id="' + menuId + '"]');
                if (orderInput) {
                    orderInput.value = String(index + 1);
                }

                const badge = item.querySelector('.js-order-badge');
                if (badge) {
                    badge.textContent = String(index + 1);
                }
            });
        }

        root.querySelectorAll('.js-sortable').forEach(function (listElement) {
            refreshOrdering(listElement);

            if (window.Sortable) {
                Sortable.create(listElement, {
                    handle: '.js-drag-handle',
                    animation: 150,
                    ghostClass: 'menu-node-sortable-ghost',
                    chosenClass: 'menu-node-sortable-chosen',
                    onEnd: function () {
                        refreshOrdering(listElement);
                        setUnsavedState(true);
                        validateParentHeaders();
                    }
                });
            }
        });

        restoreExpandedState();
        validateParentHeaders();

        root.addEventListener('click', function (event) {
            const detailButton = event.target.closest('.btn-menu-detail');
            if (detailButton) {
                const menuNode = detailButton.closest('.menu-node');
                if (!menuNode) {
                    return;
                }

                const expanded = menuNode.classList.toggle('expanded');
                detailButton.textContent = expanded ? 'Tutup Detail' : 'Detail';
                saveExpandedState();
                return;
            }

            const openIconButton = event.target.closest('.btn-open-icon-modal');
            if (!openIconButton) {
                return;
            }

            activeIconTarget = openIconButton.getAttribute('data-target') || '';
            if (activeIconTarget === '') {
                return;
            }

            const currentIcon = openIconButton.getAttribute('data-current-icon') || 'ti-point';

            if (iconPickerSearch) {
                iconPickerSearch.value = '';
            }

            if (iconPickerCustom) {
                iconPickerCustom.value = currentIcon;
            }

            filterIconItems('');
            markActiveIcon(currentIcon);

            if (iconPickerModal) {
                iconPickerModal.show();
            }
        });

        if (iconPickerGrid) {
            iconPickerGrid.addEventListener('click', function (event) {
                const iconButton = event.target.closest('.icon-picker-item');
                if (!iconButton || activeIconTarget === '') {
                    return;
                }

                const iconClass = iconButton.getAttribute('data-icon') || 'ti-point';
                applyIconToTarget(activeIconTarget, iconClass);
                markActiveIcon(iconClass);

                if (iconPickerModal) {
                    iconPickerModal.hide();
                }
            });
        }

        if (iconPickerSearch) {
            iconPickerSearch.addEventListener('input', function () {
                filterIconItems(iconPickerSearch.value || '');
            });
        }

        if (applyCustomIconButton) {
            applyCustomIconButton.addEventListener('click', function () {
                if (activeIconTarget === '') {
                    return;
                }

                const customValue = iconPickerCustom ? (iconPickerCustom.value || '').trim() : '';
                const iconClass = customValue === '' ? 'ti-point' : customValue;
                applyIconToTarget(activeIconTarget, iconClass);
                markActiveIcon(iconClass);

                if (iconPickerModal) {
                    iconPickerModal.hide();
                }
            });
        }

        const expandButton = document.getElementById('btn_expand_all_menu_detail');
        const collapseButton = document.getElementById('btn_collapse_all_menu_detail');

        if (expandButton) {
            expandButton.addEventListener('click', function () {
                root.querySelectorAll('.menu-node').forEach(function (node) {
                    node.classList.add('expanded');
                });

                root.querySelectorAll('.btn-menu-detail').forEach(function (button) {
                    button.textContent = 'Tutup Detail';
                });

                saveExpandedState();
            });
        }

        if (collapseButton) {
            collapseButton.addEventListener('click', function () {
                root.querySelectorAll('.menu-node').forEach(function (node) {
                    node.classList.remove('expanded');
                });

                root.querySelectorAll('.btn-menu-detail').forEach(function (button) {
                    button.textContent = 'Detail';
                });

                saveExpandedState();
            });
        }

        form.addEventListener('input', function () {
            setUnsavedState(true);
            validateParentHeaders();
        });

        form.addEventListener('change', function () {
            setUnsavedState(true);
            validateParentHeaders();
        });

        form.addEventListener('submit', function (event) {
            const valid = validateParentHeaders();
            if (!valid) {
                event.preventDefault();

                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        text: 'Masih ada header parent menu yang tidak valid. Periksa field yang berwarna merah.'
                    });
                }

                return;
            }

            setUnsavedState(false);
            saveExpandedState();
        });

        window.addEventListener('beforeunload', function (event) {
            if (!hasUnsavedChanges) {
                return;
            }

            event.preventDefault();
            event.returnValue = '';
        });
    })();
</script>
<?= $this->endSection() ?>
