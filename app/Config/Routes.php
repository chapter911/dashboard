<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth::index');
$routes->post('login', 'Auth::login');
$routes->post('logout', 'Auth::logout');
$routes->get('profile', 'Auth::profile', ['filter' => 'auth']);
$routes->post('profile/photo', 'Auth::updateProfilePhoto', ['filter' => 'auth']);
$routes->get('change-password', 'Auth::changePassword', ['filter' => 'auth']);
$routes->post('change-password', 'Auth::updatePassword', ['filter' => 'auth']);
$routes->get('dashboard', 'Dashboard::index', ['filter' => 'auth']);
$routes->get('setting', 'Setting::index', ['filter' => 'auth']);
$routes->post('setting', 'Setting::update', ['filter' => 'auth']);
$routes->get('setting/application', 'Setting::application', ['filter' => 'auth']);
$routes->post('setting/application', 'Setting::updateApplication', ['filter' => 'auth']);
$routes->post('setting/application/tools/migrate', 'Setting::runMigrateCommand', ['filter' => 'auth']);
$routes->post('setting/application/tools/seed', 'Setting::runSeederCommand', ['filter' => 'auth']);
$routes->post('setting/application/tools/git-pull', 'Setting::runGitPullCommand', ['filter' => 'auth']);
$routes->get('setting/application/tools/seeders', 'Setting::listSeederOptions', ['filter' => 'auth']);
$routes->post('setting/application/sync/init', 'Setting::initProductionSync', ['filter' => 'auth']);
$routes->post('setting/application/sync/step', 'Setting::processProductionSyncStep', ['filter' => 'auth']);
$routes->get('setting/menu', 'Setting::menu', ['filter' => 'auth']);
$routes->post('setting/menu', 'Setting::updateMenu', ['filter' => 'auth']);

$routes->get('C_Utility/User', 'C_Utility::userIndex', ['filter' => 'auth']);
$routes->post('C_Utility/User/create', 'C_Utility::createUser', ['filter' => 'auth']);
$routes->post('C_Utility/User/update', 'C_Utility::updateUser', ['filter' => 'auth']);
$routes->post('C_Utility/User/toggle-active', 'C_Utility::toggleActive', ['filter' => 'auth']);
$routes->post('C_Utility/User/reset-password', 'C_Utility::resetPassword', ['filter' => 'auth']);

$routes->get('C_Utility/UserGroup', 'C_Utility::userGroupIndex', ['filter' => 'auth']);
$routes->post('C_Utility/UserGroup/create', 'C_Utility::createUserGroup', ['filter' => 'auth']);
$routes->post('C_Utility/UserGroup/update', 'C_Utility::updateUserGroup', ['filter' => 'auth']);
$routes->post('C_Utility/UserGroup/delete', 'C_Utility::deleteUserGroup', ['filter' => 'auth']);
$routes->get('C_Utility/UserGroup/access/(:num)', 'C_Utility::userGroupAccess/$1', ['filter' => 'auth']);
$routes->post('C_Utility/UserGroup/access/(:num)', 'C_Utility::saveUserGroupAccess/$1', ['filter' => 'auth']);

$routes->get('C_Utility/LoginHistory', 'C_Utility::loginHistoryIndex', ['filter' => 'auth']);

$routes->get('C_Master/KategoriTegangan', 'C_Master::kategoriTegangan', ['filter' => 'auth']);
$routes->post('C_Master/KategoriTegangan/save', 'C_Master::saveKategoriTegangan', ['filter' => 'auth']);
$routes->post('C_Master/KategoriTegangan/delete', 'C_Master::deleteKategoriTegangan', ['filter' => 'auth']);

$routes->get('C_Laporan', 'C_Laporan::index', ['filter' => 'auth']);
$routes->get('C_Laporan/Index', 'C_Laporan::index', ['filter' => 'auth']);
$routes->post('C_Laporan/getDataIndex', 'C_Laporan::getDataIndex', ['filter' => 'auth']);
$routes->get('C_Laporan/Harian', 'C_Laporan::harian', ['filter' => 'auth']);
$routes->post('C_Laporan/Harian/import', 'C_Laporan::importHarian', ['filter' => 'auth']);
$routes->get('C_Laporan/Target', 'C_Laporan::target', ['filter' => 'auth']);
$routes->post('C_Laporan/Target/save', 'C_Laporan::saveTarget', ['filter' => 'auth']);
$routes->post('C_Laporan/Target/delete', 'C_Laporan::deleteTarget', ['filter' => 'auth']);
$routes->get('C_Laporan/Realisasi', 'C_Laporan::realisasi', ['filter' => 'auth']);
$routes->get('C_Laporan/Saldo', 'C_Laporan::saldo', ['filter' => 'auth']);
$routes->post('C_Laporan/Saldo/update', 'C_Laporan::updateSaldo', ['filter' => 'auth']);
