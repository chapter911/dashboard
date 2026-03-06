<?php
$branding = $branding ?? [];
$appName = $appName ?? ($branding['app_name'] ?? 'Dashboard PLN');
$logoUrl = $logoUrl ?? ($branding['app_logo_url'] ?? base_url('assets/img/branding/logo.png'));
$uriPath = trim(uri_string(), '/');
$sidebarMenus = $sidebarMenus ?? [];

$isLinkActive = static function (string $link) use ($uriPath): bool {
    $clean = trim($link, '/');

    if ($clean === '' || $clean === '#') {
        return false;
    }

    return $uriPath === $clean || strpos($uriPath, $clean . '/') === 0;
};

$hasActiveChild = static function (array $children, callable $isLinkActive, callable $hasActiveChild): bool {
    foreach ($children as $child) {
        $link = (string) ($child['link'] ?? '#');
        if ($isLinkActive($link)) {
            return true;
        }

        $nested = $child['children'] ?? [];
        if (is_array($nested) && $nested !== [] && $hasActiveChild($nested, $isLinkActive, $hasActiveChild)) {
            return true;
        }
    }

    return false;
};

$renderMenuItems = static function (array $menus, callable $renderMenuItems, callable $isLinkActive, callable $hasActiveChild): string {
    $html = '';

    foreach ($menus as $menu) {
        $label = (string) ($menu['label'] ?? 'Menu');
        $icon = trim((string) ($menu['icon'] ?? 'ti-point'));
        $icon = $icon !== '' ? $icon : 'ti-point';
        $link = (string) ($menu['link'] ?? '#');
        $normalizedLink = trim($link, '/');

        if ($normalizedLink === 'dashboard') {
            $label = 'Dashboards';
            $icon = 'ti-brand-speedtest';
        }

        $children = is_array($menu['children'] ?? null) ? $menu['children'] : [];

        $active = $isLinkActive($link) || ($children !== [] && $hasActiveChild($children, $isLinkActive, $hasActiveChild));
        $itemClass = 'menu-item' . ($active ? ' active open' : '');

        $html .= '<li class="' . esc($itemClass) . '">';

        if ($children !== []) {
            $html .= '<a href="javascript:void(0);" class="menu-link menu-toggle">';
            $html .= '<i class="menu-icon tf-icons ti ' . esc($icon) . '"></i>';
            $html .= '<div>' . esc($label) . '</div>';
            $html .= '</a>';
            $html .= '<ul class="menu-sub">';
            $html .= $renderMenuItems($children, $renderMenuItems, $isLinkActive, $hasActiveChild);
            $html .= '</ul>';
        } else {
            $targetLink = $link === '#' ? 'javascript:void(0);' : site_url($link);
            $html .= '<a href="' . esc($targetLink) . '" class="menu-link">';
            $html .= '<i class="menu-icon tf-icons ti ' . esc($icon) . '"></i>';
            $html .= '<div>' . esc($label) . '</div>';
            $html .= '</a>';
        }

        $html .= '</li>';
    }

    return $html;
};
?>

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="<?= site_url('dashboard') ?>" class="app-brand-link">
            <span class="app-brand-logo demo">
                <img src="<?= esc($logoUrl) ?>" alt="<?= esc($appName) ?>" style="height: 24px;" />
            </span>
            <span class="app-brand-text demo menu-text fw-bold"><?= esc($appName) ?></span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="ti menu-toggle-icon d-none d-xl-block align-middle ti-sm"></i>
            <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <?= $renderMenuItems($sidebarMenus, $renderMenuItems, $isLinkActive, $hasActiveChild) ?>
    </ul>
</aside>
