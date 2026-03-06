<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= esc($title ?? 'Akses Menu Group') ?></title>

    <link rel="stylesheet" href="<?= base_url('assets/vendor/css/core.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('assets/vendor/css/theme-default.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('assets/css/demo.css') ?>" />

    <?php
    $branding = $branding ?? [];
    $primaryColor = $branding['app_primary_color'] ?? '#0a66c2';
    ?>
    <style>
        .table > :not(caption) > thead > tr > th,
        .table thead th {
            background-color: <?= esc($primaryColor) ?> !important;
            color: #fff !important;
        }
    </style>
</head>
<body>
<?php
$group = is_array($group ?? null) ? $group : [];
$menus = is_array($menus ?? null) ? $menus : [];
$accessMap = is_array($accessMap ?? null) ? $accessMap : [];
?>

<div class="p-3">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success" role="alert"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <small class="text-muted d-block mb-3">Atur menu dan fitur yang boleh diakses group ini.</small>

    <form id="access_menu_form" action="<?= site_url('C_Utility/UserGroup/access/' . (int) ($group['group_id'] ?? 0)) . '?embed=1' ?>" method="post" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="embed" value="1">

        <div class="table-responsive text-nowrap">
            <table class="table table-bordered table-sm align-middle">
                <thead>
                    <tr>
                        <th style="min-width: 280px;">Menu</th>
                        <th class="text-center">Akses</th>
                        <th class="text-center">Add</th>
                        <th class="text-center">Edit</th>
                        <th class="text-center">Delete</th>
                        <th class="text-center">Export</th>
                        <th class="text-center">Import</th>
                        <th class="text-center">Approval</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $renderRows = static function (array $items, int $level, callable $renderRows, array $accessMap): string {
                        $html = '';
                        foreach ($items as $item) {
                            $id = (string) ($item['id'] ?? '');
                            if ($id === '') {
                                continue;
                            }

                            $label = (string) ($item['label'] ?? $id);
                            $children = is_array($item['children'] ?? null) ? $item['children'] : [];
                            $padding = 12 + ($level * 24);
                            $checked = isset($accessMap[$id]);
                            $features = $accessMap[$id] ?? [];

                            $html .= '<tr>';
                            $html .= '<td style="padding-left: ' . esc((string) $padding) . 'px;">' . esc($id . ' - ' . $label) . '</td>';
                            $html .= '<td class="text-center"><input type="checkbox" name="access[' . esc($id) . ']" value="1" ' . ($checked ? 'checked' : '') . '></td>';
                            $html .= '<td class="text-center"><input type="checkbox" name="fitur_add[' . esc($id) . ']" value="1" ' . (((int) ($features['fitur_add'] ?? 0) === 1) ? 'checked' : '') . '></td>';
                            $html .= '<td class="text-center"><input type="checkbox" name="fitur_edit[' . esc($id) . ']" value="1" ' . (((int) ($features['fitur_edit'] ?? 0) === 1) ? 'checked' : '') . '></td>';
                            $html .= '<td class="text-center"><input type="checkbox" name="fitur_delete[' . esc($id) . ']" value="1" ' . (((int) ($features['fitur_delete'] ?? 0) === 1) ? 'checked' : '') . '></td>';
                            $html .= '<td class="text-center"><input type="checkbox" name="fitur_export[' . esc($id) . ']" value="1" ' . (((int) ($features['fitur_export'] ?? 0) === 1) ? 'checked' : '') . '></td>';
                            $html .= '<td class="text-center"><input type="checkbox" name="fitur_import[' . esc($id) . ']" value="1" ' . (((int) ($features['fitur_import'] ?? 0) === 1) ? 'checked' : '') . '></td>';
                            $html .= '<td class="text-center"><input type="checkbox" name="fitur_approval[' . esc($id) . ']" value="1" ' . (((int) ($features['fitur_approval'] ?? 0) === 1) ? 'checked' : '') . '></td>';
                            $html .= '</tr>';

                            if ($children !== []) {
                                $html .= $renderRows($children, $level + 1, $renderRows, $accessMap);
                            }
                        }

                        return $html;
                    };
                    ?>
                    <?= $renderRows($menus, 0, $renderRows, $accessMap) ?>
                </tbody>
            </table>
        </div>

    </form>
</div>
</body>
</html>
