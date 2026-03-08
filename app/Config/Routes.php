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
$routes->post('dashboard/getChartIndex', 'Dashboard::getChartIndex', ['filter' => 'auth']);
$routes->post('dashboard/getChartHitrate', 'Dashboard::getChartHitrate', ['filter' => 'auth']);
$routes->post('Dashboard/getChartIndex', 'Dashboard::getChartIndex', ['filter' => 'auth']);
$routes->post('Dashboard/getChartHitrate', 'Dashboard::getChartHitrate', ['filter' => 'auth']);
$routes->get('setting', 'Setting::index', ['filter' => 'auth']);
$routes->post('setting', 'Setting::update', ['filter' => 'auth']);
$routes->get('setting/application', 'Setting::application', ['filter' => 'auth']);
$routes->post('setting/application', 'Setting::updateApplication', ['filter' => 'auth']);
$routes->post('setting/application/tools/migrate', 'Setting::runMigrateCommand', ['filter' => 'auth']);
$routes->post('setting/application/tools/seed', 'Setting::runSeederCommand', ['filter' => 'auth']);
$routes->post('setting/application/tools/snake-case', 'Setting::runSnakeCaseScenario', ['filter' => 'auth']);
$routes->post('setting/application/tools/git-pull', 'Setting::runGitPullCommand', ['filter' => 'auth']);
$routes->get('setting/application/tools/seeders', 'Setting::listSeederOptions', ['filter' => 'auth']);
$routes->post('setting/application/sync/init', 'Setting::initProductionSync', ['filter' => 'auth']);
$routes->get('setting/application/sync/state', 'Setting::getProductionSyncState', ['filter' => 'auth']);
$routes->post('setting/application/sync/resume', 'Setting::resumeProductionSync', ['filter' => 'auth']);
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
$routes->post('C_Laporan/Harian/data', 'C_Laporan::harianData', ['filter' => 'auth']);
$routes->post('C_Laporan/Harian/import', 'C_Laporan::importHarian', ['filter' => 'auth']);
$routes->get('C_Laporan/Target', 'C_Laporan::target', ['filter' => 'auth']);
$routes->post('C_Laporan/Target/data', 'C_Laporan::targetData', ['filter' => 'auth']);
$routes->post('C_Laporan/Target/save', 'C_Laporan::saveTarget', ['filter' => 'auth']);
$routes->post('C_Laporan/Target/delete', 'C_Laporan::deleteTarget', ['filter' => 'auth']);
$routes->get('C_Laporan/Realisasi', 'C_Laporan::realisasi', ['filter' => 'auth']);
$routes->post('C_Laporan/Realisasi/data', 'C_Laporan::realisasiData', ['filter' => 'auth']);
$routes->get('C_Laporan/Saldo', 'C_Laporan::saldo', ['filter' => 'auth']);
$routes->post('C_Laporan/Saldo/data', 'C_Laporan::saldoData', ['filter' => 'auth']);
$routes->post('C_Laporan/Saldo/update', 'C_Laporan::updateSaldo', ['filter' => 'auth']);

$routes->get('C_Susut', 'C_Susut::index', ['filter' => 'auth']);
$routes->get('C_Susut/index', 'C_Susut::index', ['filter' => 'auth']);
$routes->post('C_Susut/getDataSusut', 'C_Susut::getDataSusut', ['filter' => 'auth']);
$routes->get('C_Susut/target_susut', 'C_Susut::target_susut', ['filter' => 'auth']);
$routes->post('C_Susut/get_target_susut_data', 'C_Susut::get_target_susut_data', ['filter' => 'auth']);
$routes->get('C_Susut/download_format_target_susut', 'C_Susut::download_format_target_susut', ['filter' => 'auth']);
$routes->post('C_Susut/upload_target_susut', 'C_Susut::upload_target_susut', ['filter' => 'auth']);
$routes->post('C_Susut/getComparisonData', 'C_Susut::getComparisonData', ['filter' => 'auth']);

$routes->get('C_TUL', 'C_TUL::index', ['filter' => 'auth']);
$routes->get('C_TUL/Index', 'C_TUL::index', ['filter' => 'auth']);
$routes->post('C_TUL/data', 'C_TUL::data', ['filter' => 'auth']);
$routes->post('C_TUL/upload', 'C_TUL::upload', ['filter' => 'auth']);
$routes->post('C_TUL/summary-per-unit', 'C_TUL::summaryPerUnit', ['filter' => 'auth']);
$routes->post('C_TUL/detail', 'C_TUL::detail', ['filter' => 'auth']);
$routes->post('C_TUL/update', 'C_TUL::update', ['filter' => 'auth']);
$routes->post('C_TUL/delete', 'C_TUL::delete', ['filter' => 'auth']);
$routes->get('C_TUL/dashboard', 'C_TUL::dashboard', ['filter' => 'auth']);
$routes->get('C_TUL/Dashboard', 'C_TUL::dashboard', ['filter' => 'auth']);
$routes->post('C_TUL/dashboard/data', 'C_TUL::dashboardData', ['filter' => 'auth']);
$routes->post('C_TUL/dashboard/chart', 'C_TUL::chartData', ['filter' => 'auth']);
$routes->get('C_TUL/grafik', 'C_TUL::grafik', ['filter' => 'auth']);
$routes->get('C_TUL/Grafik', 'C_TUL::grafik', ['filter' => 'auth']);
$routes->post('C_TUL/grafik/pie', 'C_TUL::pieComparisonData', ['filter' => 'auth']);
$routes->post('C_TUL/grafik/kwh', 'C_TUL::kwhJualTable', ['filter' => 'auth']);

$routes->get('C_Emin', 'C_Emin::index', ['filter' => 'auth']);
$routes->get('C_Emin/Index', 'C_Emin::index', ['filter' => 'auth']);
$routes->post('C_Emin/data', 'C_Emin::data', ['filter' => 'auth']);
$routes->post('C_Emin/upload', 'C_Emin::upload', ['filter' => 'auth']);
$routes->post('C_Emin/summary-per-unit', 'C_Emin::summaryPerUnit', ['filter' => 'auth']);
$routes->post('C_Emin/detail', 'C_Emin::detail', ['filter' => 'auth']);
$routes->post('C_Emin/update', 'C_Emin::update', ['filter' => 'auth']);
$routes->post('C_Emin/delete', 'C_Emin::delete', ['filter' => 'auth']);
$routes->get('C_Emin/dashboard', 'C_Emin::dashboard', ['filter' => 'auth']);
$routes->get('C_Emin/Dashboard', 'C_Emin::dashboard', ['filter' => 'auth']);
$routes->post('C_Emin/dashboard/data', 'C_Emin::dashboardData', ['filter' => 'auth']);

$routes->get('C_P2TL', 'C_P2TL::index', ['filter' => 'auth']);
$routes->get('C_P2TL/Index', 'C_P2TL::index', ['filter' => 'auth']);
$routes->post('C_P2TL/getViewIndex', 'C_P2TL::getViewIndex', ['filter' => 'auth']);
$routes->post('C_P2TL/getDataIndex', 'C_P2TL::getDataIndex', ['filter' => 'auth']);
$routes->post('C_P2TL/getChartIndex', 'C_P2TL::getChartIndex', ['filter' => 'auth']);
$routes->post('C_P2TL/getDataHitrate', 'C_P2TL::getDataHitrate', ['filter' => 'auth']);
$routes->post('C_P2TL/getChartHitrate', 'C_P2TL::getChartHitrate', ['filter' => 'auth']);
$routes->get('C_P2TL/exportData', 'C_P2TL::exportData', ['filter' => 'auth']);
$routes->get('C_P2TL/exportDataHitrate', 'C_P2TL::exportDataHitrate', ['filter' => 'auth']);

$routes->get('C_P2TL/DataPemakaian', 'C_P2TL::DataPemakaian', ['filter' => 'auth']);
$routes->get('C_P2TL/dataP2TL', 'C_P2TL::dataP2TL', ['filter' => 'auth']);
$routes->post('C_P2TL/ajaxP2TL', 'C_P2TL::ajaxP2TL', ['filter' => 'auth']);
$routes->post('C_P2TL/ajaxDataPemakaian', 'C_P2TL::ajaxDataPemakaian', ['filter' => 'auth']);
$routes->post('C_P2TL/importData', 'C_P2TL::importData', ['filter' => 'auth']);
$routes->post('C_P2TL/importAnalisa', 'C_P2TL::importAnalisa', ['filter' => 'auth']);

$routes->get('C_P2TL/Analisa', 'C_P2TL::Analisa', ['filter' => 'auth']);
$routes->post('C_P2TL/ajaxAnalisa', 'C_P2TL::ajaxAnalisa', ['filter' => 'auth']);
$routes->post('C_P2TL/getAnalisaDetailAjax', 'C_P2TL::getAnalisaDetailAjax', ['filter' => 'auth']);
$routes->post('C_P2TL/getAnalisaGrafikAjax', 'C_P2TL::getAnalisaGrafikAjax', ['filter' => 'auth']);
$routes->get('C_P2TL/exportAnalisaExcel', 'C_P2TL::exportAnalisaExcel', ['filter' => 'auth']);

$routes->get('C_P2TL/Target', 'C_P2TL::target', ['filter' => 'auth']);
$routes->post('C_P2TL/ajaxTarget', 'C_P2TL::ajaxTarget', ['filter' => 'auth']);
$routes->post('C_P2TL/ajaxTargetHarian', 'C_P2TL::ajaxTargetHarian', ['filter' => 'auth']);
$routes->post('C_P2TL/updateTarget', 'C_P2TL::updateTarget', ['filter' => 'auth']);
$routes->post('C_P2TL/updateTargetHarian', 'C_P2TL::updateTargetHarian', ['filter' => 'auth']);

$routes->get('C_P2TL/HitRate', 'C_P2TL::HitRate', ['filter' => 'auth']);
$routes->post('C_P2TL/ajaxHitRate', 'C_P2TL::ajaxHitRate', ['filter' => 'auth']);
$routes->post('C_P2TL/importHitRate', 'C_P2TL::importHitRate', ['filter' => 'auth']);

$routes->get('C_P2TL/TargetOperasi', 'C_P2TL::TargetOperasi', ['filter' => 'auth']);
$routes->post('C_P2TL/dataTargetOperasi', 'C_P2TL::dataTargetOperasi', ['filter' => 'auth']);
$routes->post('C_P2TL/importTargetOperasi', 'C_P2TL::importTargetOperasi', ['filter' => 'auth']);

$routes->get('C_AnalisaPembelian', 'C_AnalisaPembelian::index', ['filter' => 'auth']);
$routes->get('C_AnalisaPembelian/Index', 'C_AnalisaPembelian::index', ['filter' => 'auth']);
$routes->post('C_AnalisaPembelian/data', 'C_AnalisaPembelian::data', ['filter' => 'auth']);
$routes->post('C_AnalisaPembelian/upload', 'C_AnalisaPembelian::upload', ['filter' => 'auth']);
$routes->post('C_AnalisaPembelian/detail', 'C_AnalisaPembelian::detail', ['filter' => 'auth']);
$routes->post('C_AnalisaPembelian/update', 'C_AnalisaPembelian::update', ['filter' => 'auth']);
$routes->post('C_AnalisaPembelian/delete', 'C_AnalisaPembelian::delete', ['filter' => 'auth']);

$routes->get('C_PSSD', 'C_PSSD::index', ['filter' => 'auth']);
$routes->get('C_PSSD/Index', 'C_PSSD::index', ['filter' => 'auth']);
$routes->post('C_PSSD/data', 'C_PSSD::data', ['filter' => 'auth']);
$routes->post('C_PSSD/upload', 'C_PSSD::upload', ['filter' => 'auth']);
$routes->post('C_PSSD/summary-per-unit', 'C_PSSD::summaryPerUnit', ['filter' => 'auth']);
$routes->post('C_PSSD/detail', 'C_PSSD::detail', ['filter' => 'auth']);
$routes->post('C_PSSD/update', 'C_PSSD::update', ['filter' => 'auth']);
$routes->post('C_PSSD/delete', 'C_PSSD::delete', ['filter' => 'auth']);
