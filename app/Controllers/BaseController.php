<?php

namespace App\Controllers;

use App\Models\AppSettingModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        // $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
        $this->shareBrandingSettings();
        $this->shareSettingMenu();
        $this->shareSidebarMenus();
        $this->shareUserPrivileges();
    }

    private function shareUserPrivileges(): void
    {
        service('renderer')->setVar('isSuperAdministrator', $this->resolveIsSuperAdministrator());
    }

    private function shareBrandingSettings(): void
    {
        $branding = [
            'app_name' => 'Dashboard PLN',
            'app_logo_path' => null,
            'app_logo_url' => base_url('assets/img/branding/logo.png'),
            'login_background_path' => null,
            'login_background_url' => null,
            'app_primary_color' => '#0a66c2',
            'app_primary_color_deep' => '#074c91',
            'auto_logout_minutes' => 30,
        ];

        try {
            $settingModel = new AppSettingModel();
            $appName = $settingModel->getValue('app_name', 'Dashboard PLN');
            $logoPath = $settingModel->getValue('app_logo_path');
            $backgroundPath = $settingModel->getValue('login_background_path');
            $primaryColor = $settingModel->getValue('app_primary_color', '#0a66c2');
            $autoLogoutMinutes = $settingModel->getValue('auto_logout_minutes', '30');

            if (is_string($appName) && trim($appName) !== '') {
                $branding['app_name'] = trim($appName);
            }

            if (is_string($logoPath) && trim($logoPath) !== '') {
                $branding['app_logo_path'] = trim($logoPath);
                $branding['app_logo_url'] = base_url(trim($logoPath));
            }

            if (is_string($backgroundPath) && trim($backgroundPath) !== '') {
                $branding['login_background_path'] = trim($backgroundPath);
                $branding['login_background_url'] = base_url(trim($backgroundPath));
            }

            if ($this->isValidHexColor($primaryColor)) {
                $normalizedColor = strtolower((string) $primaryColor);
                $branding['app_primary_color'] = $normalizedColor;
                $branding['app_primary_color_deep'] = $this->darkenHexColor($normalizedColor, 24);
            }

            if (is_numeric($autoLogoutMinutes)) {
                $normalizedMinutes = (int) $autoLogoutMinutes;
                if ($normalizedMinutes < 1) {
                    $normalizedMinutes = 1;
                }

                if ($normalizedMinutes > 1440) {
                    $normalizedMinutes = 1440;
                }

                $branding['auto_logout_minutes'] = $normalizedMinutes;
            }
        } catch (Throwable $e) {
            // Keep fallback defaults when table is not migrated yet.
            log_message('warning', 'APP_SETTINGS_UNAVAILABLE: {message}', ['message' => $e->getMessage()]);
        }

        service('renderer')->setVar('branding', $branding);
    }

    private function shareSettingMenu(): void
    {
        $settingMenu = [
            'id' => '97',
            'label' => 'Setting',
            'link' => 'setting',
            'icon' => 'ti-settings',
        ];

        try {
            $db = db_connect();

            if ($db->tableExists('menu_lv1')) {
                $row = $db->table('menu_lv1')
                    ->select('id, label, link, icon')
                    ->groupStart()
                    ->where('id', '97')
                    ->orWhere('link', 'setting')
                    ->orWhere('link', '/setting')
                    ->orWhere('label', 'Setting')
                    ->groupEnd()
                    ->orderBy('ordering', 'ASC')
                    ->get()
                    ->getRowArray();

                if (is_array($row) && ! empty($row)) {
                    $settingMenu['id'] = (string) ($row['id'] ?? $settingMenu['id']);
                    $settingMenu['label'] = (string) ($row['label'] ?? $settingMenu['label']);
                    $settingMenu['icon'] = (string) ($row['icon'] ?? $settingMenu['icon']);

                    $link = trim((string) ($row['link'] ?? ''), '/');
                    if ($link !== '' && $link !== '#') {
                        $settingMenu['link'] = $link;
                    }
                }
            }
        } catch (Throwable $e) {
            log_message('warning', 'SETTING_MENU_UNAVAILABLE: {message}', ['message' => $e->getMessage()]);
        }

        service('renderer')->setVar('settingMenu', $settingMenu);
    }

    private function shareSidebarMenus(): void
    {
        $defaultMenus = [
            [
                'id' => 'dashboard',
                'label' => 'Dashboard',
                'icon' => 'ti-smart-home',
                'link' => 'dashboard',
                'children' => [],
            ],
            [
                'id' => '97',
                'label' => 'Setting',
                'icon' => 'ti-settings',
                'link' => 'setting',
                'children' => [],
            ],
        ];

        $sidebarMenus = $defaultMenus;

        try {
            $groupId = session('group_id');
            if ($groupId === null || $groupId === '') {
                service('renderer')->setVar('sidebarMenus', $sidebarMenus);

                return;
            }

            $db = db_connect();

            if (! $db->tableExists('menu_akses') || ! $db->tableExists('menu_lv1')) {
                service('renderer')->setVar('sidebarMenus', $sidebarMenus);

                return;
            }

            $accessRows = $db->table('menu_akses')
                ->select('menu_id')
                ->where('group_id', (int) $groupId)
                ->get()
                ->getResultArray();

            $allowedMenuIds = [];
            foreach ($accessRows as $row) {
                $menuId = trim((string) ($row['menu_id'] ?? ''));
                if ($menuId === '') {
                    continue;
                }

                $allowedMenuIds[$menuId] = true;
            }

            if ($allowedMenuIds === []) {
                service('renderer')->setVar('sidebarMenus', []);

                return;
            }

            $lv1Rows = $db->table('menu_lv1')
                ->select('id, label, link, icon, ordering')
                ->orderBy('ordering', 'ASC')
                ->get()
                ->getResultArray();

            $lv2Rows = $db->tableExists('menu_lv2')
                ? $db->table('menu_lv2')->select('id, label, link, icon, header, ordering')->orderBy('ordering', 'ASC')->get()->getResultArray()
                : [];

            $lv3Rows = $db->tableExists('menu_lv3')
                ? $db->table('menu_lv3')->select('id, label, link, icon, header, ordering')->orderBy('ordering', 'ASC')->get()->getResultArray()
                : [];

            $lv3ByHeader = [];
            foreach ($lv3Rows as $row) {
                $id = trim((string) ($row['id'] ?? ''));
                $header = trim((string) ($row['header'] ?? ''));

                if ($id === '' || $header === '' || ! isset($allowedMenuIds[$id])) {
                    continue;
                }

                $lv3ByHeader[$header][] = [
                    'id' => $id,
                    'label' => (string) ($row['label'] ?? $id),
                    'icon' => (string) ($row['icon'] ?? 'ti-point'),
                    'link' => $this->normalizeMenuLink((string) ($row['link'] ?? '#')),
                    'children' => [],
                ];
            }

            $lv2ByHeader = [];
            foreach ($lv2Rows as $row) {
                $id = trim((string) ($row['id'] ?? ''));
                $header = trim((string) ($row['header'] ?? ''));

                if ($id === '' || $header === '') {
                    continue;
                }

                $children = $lv3ByHeader[$id] ?? [];
                $isAllowed = isset($allowedMenuIds[$id]);

                if (! $isAllowed && $children === []) {
                    continue;
                }

                $lv2ByHeader[$header][] = [
                    'id' => $id,
                    'label' => (string) ($row['label'] ?? $id),
                    'icon' => (string) ($row['icon'] ?? 'ti-point'),
                    'link' => $this->normalizeMenuLink((string) ($row['link'] ?? '#')),
                    'children' => $children,
                ];
            }

            $builtMenus = [];
            foreach ($lv1Rows as $row) {
                $id = trim((string) ($row['id'] ?? ''));
                if ($id === '') {
                    continue;
                }

                $children = $lv2ByHeader[$id] ?? [];
                $isAllowed = isset($allowedMenuIds[$id]);

                if (! $isAllowed && $children === []) {
                    continue;
                }

                $builtMenus[] = [
                    'id' => $id,
                    'label' => (string) ($row['label'] ?? $id),
                    'icon' => (string) ($row['icon'] ?? 'ti-point'),
                    'link' => $this->normalizeMenuLink((string) ($row['link'] ?? '#')),
                    'children' => $children,
                ];
            }

            // Keep direct Dashboard shortcut if user has permission for 02-01.
            if (isset($allowedMenuIds['02-01'])) {
                array_unshift($builtMenus, [
                    'id' => 'dashboard',
                    'label' => 'Dashboard',
                    'icon' => 'ti-smart-home',
                    'link' => 'dashboard',
                    'children' => [],
                ]);
            }

            $sidebarMenus = $this->filterHiddenMenus($builtMenus);
        } catch (Throwable $e) {
            log_message('warning', 'SIDEBAR_MENU_UNAVAILABLE: {message}', ['message' => $e->getMessage()]);
        }

        service('renderer')->setVar('sidebarMenus', $sidebarMenus);
    }

    protected function resolveIsSuperAdministrator(): bool
    {
        $groupId = (int) (session('group_id') ?? 0);
        if ($groupId === 1) {
            return true;
        }

        if ($groupId < 1) {
            return false;
        }

        try {
            $row = db_connect()
                ->table('mst_user_group')
                ->select('group_name')
                ->where('group_id', $groupId)
                ->get()
                ->getRowArray();

            if (! is_array($row)) {
                return false;
            }

            return strtolower(trim((string) ($row['group_name'] ?? ''))) === 'super administrator';
        } catch (Throwable $e) {
            return false;
        }
    }

    private function normalizeMenuLink(string $link): string
    {
        $cleanLink = trim($link);

        if ($cleanLink === '' || $cleanLink === '#') {
            return '#';
        }

        return trim(str_replace('index.php/', '', $cleanLink), '/');
    }

    /**
     * @param list<array<string, mixed>> $menus
     * @return list<array<string, mixed>>
     */
    private function filterHiddenMenus(array $menus): array
    {
        $hiddenLinks = [
            'c_saldo/upload',
            'c_analisapembelian',
            'c_analisapembelian/index',
        ];

        $result = [];
        foreach ($menus as $menu) {
            $link = strtolower(trim((string) ($menu['link'] ?? '#'), '/'));
            $label = strtolower(trim((string) ($menu['label'] ?? '')));

            $children = is_array($menu['children'] ?? null) ? $this->filterHiddenMenus($menu['children']) : [];

            if (in_array($link, $hiddenLinks, true) || $label === 'analisa pelanggan') {
                continue;
            }

            $menu['children'] = $children;
            $result[] = $menu;
        }

        return $result;
    }

    private function isValidHexColor(?string $color): bool
    {
        return is_string($color) && preg_match('/^#[0-9a-fA-F]{6}$/', $color) === 1;
    }

    private function darkenHexColor(string $hexColor, int $percent): string
    {
        $safePercent = max(0, min(100, $percent));
        $factor = (100 - $safePercent) / 100;

        $r = (int) floor(hexdec(substr($hexColor, 1, 2)) * $factor);
        $g = (int) floor(hexdec(substr($hexColor, 3, 2)) * $factor);
        $b = (int) floor(hexdec(substr($hexColor, 5, 2)) * $factor);

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}
