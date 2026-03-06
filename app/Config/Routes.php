<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth::index');
$routes->post('login', 'Auth::login');
$routes->post('logout', 'Auth::logout');
$routes->get('dashboard', 'Dashboard::index', ['filter' => 'auth']);
