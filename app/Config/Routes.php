<?php

namespace Config;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('login', 'Auth::index');
$routes->post('login', 'Auth::login');
$routes->get('logout', 'Auth::logout');
// seed-users route removed for security — use CLI: php spark db:seed UserSeeder
$routes->get('guest', 'Home::index');
$routes->get('status', 'Home::status');
$routes->get('search', 'Search::index');
$routes->get('schedules', 'Schedules::index');
$routes->get('schedules/status', 'Schedules::status');
$routes->get('fares', 'Fares::index');
$routes->get('api/fares', 'Fares::apiData');
$routes->get('history', 'History::index');
$routes->post('contact/send', 'Contact::send');

// Public API for real-time queue sync (no auth required - read-only)
$routes->get('api/queue-status', 'Api\QueueStatus::index');
$routes->get('api/check-vehicle-availability/(:num)', 'Api\QueueStatus::checkAvailability/$1');

// Route & Fare Management — View + Fare/Discount (Admin + Staff per DFD 2.3)
$routes->group('admin/routes', ['filter' => 'auth:admin'], function($routes) {
    $routes->get('', 'Admin\Routes::index');
    // Fare & Discount Management (both roles per DFD 2.3)
    $routes->post('discounts/store', 'Admin\Routes::storeDiscount');
    $routes->post('discounts/update/(:num)', 'Admin\Routes::updateDiscount/$1');
    $routes->post('discounts/delete/(:num)', 'Admin\Routes::deleteDiscount/$1');
});
// Route CRUD — Admin only (per DFD 2.1/2.2: only Admin inputs route records)
$routes->group('admin/routes', ['filter' => 'auth:admin'], function($routes) {
    $routes->get('create', 'Admin\Routes::create');
    $routes->post('store', 'Admin\Routes::store');
    $routes->get('edit/(:num)', 'Admin\Routes::edit/$1');
    $routes->post('update/(:num)', 'Admin\Routes::update/$1');
    $routes->post('delete/(:num)', 'Admin\Routes::delete/$1');
});

// Vehicle Register — Admin only (per DFD 2.1: only Admin inputs vehicle records)
$routes->group('admin/vehicles', ['filter' => 'auth:admin'], function($routes) {
    $routes->get('', 'Admin\Vehicles::index');
    $routes->get('edit/(:num)', 'Admin\Vehicles::edit/$1');
    $routes->post('store', 'Admin\Vehicles::store');
    $routes->post('update/(:num)', 'Admin\Vehicles::update/$1');
    $routes->post('delete/(:num)', 'Admin\Vehicles::delete/$1');
});

// Announcements (Accessible by both Admin and Staff)
$routes->group('admin/announcements', ['filter' => 'auth:admin,staff'], function($routes) {
    $routes->get('', 'Admin\Announcements::index');
    $routes->get('create', 'Admin\Announcements::create');
    $routes->post('store', 'Admin\Announcements::store');
    $routes->get('edit/(:num)', 'Admin\Announcements::edit/$1');
    $routes->post('update/(:num)', 'Admin\Announcements::update/$1');
    $routes->post('delete/(:num)', 'Admin\Announcements::delete/$1');
});

// Protected Routes
$routes->group('admin', ['filter' => 'auth:admin'], function ($routes) {
    $routes->get('dashboard', 'Admin\Dashboard::index');

    // User Management
    $routes->get('users', 'Admin\Users::index');
    $routes->get('users/create', 'Admin\Users::create');
    $routes->post('users/store', 'Admin\Users::store');
    $routes->get('users/edit/(:num)', 'Admin\Users::edit/$1');
    $routes->post('users/update/(:num)', 'Admin\Users::update/$1');
    $routes->post('users/delete/(:num)', 'Admin\Users::delete/$1');

    // Terminals
    $routes->get('terminals', 'Admin\Terminals::index');
    $routes->get('terminals/create', 'Admin\Terminals::create');
    $routes->post('terminals/store', 'Admin\Terminals::store');
    $routes->get('terminals/edit/(:num)', 'Admin\Terminals::edit/$1');
    $routes->post('terminals/update/(:num)', 'Admin\Terminals::update/$1');
    $routes->post('terminals/delete/(:num)', 'Admin\Terminals::delete/$1');

    // History
    $routes->get('history', 'Admin\History::index');
    $routes->get('history/export', 'Admin\History::export');
    $routes->get('history/print', 'Admin\History::print');

    // Logs
    $routes->get('logs', 'Admin\Logs::index');
    $routes->post('logs/delete/(:num)', 'Admin\Logs::delete/$1');
    $routes->post('logs/clear', 'Admin\Logs::clear');


    // Departure Rules
    $routes->get('departure-rules', 'Admin\DepartureRules::index');
    $routes->get('departure-rules/create', 'Admin\DepartureRules::create');
    $routes->post('departure-rules/store', 'Admin\DepartureRules::store');
    $routes->get('departure-rules/edit/(:num)', 'Admin\DepartureRules::edit/$1');
    $routes->post('departure-rules/update/(:num)', 'Admin\DepartureRules::update/$1');
    $routes->post('departure-rules/delete/(:num)', 'Admin\DepartureRules::delete/$1');
});

$routes->group('staff', ['filter' => 'auth:staff'], function ($routes) {
    $routes->get('dashboard', 'Staff\Dashboard::index');

    // Queue
    $routes->get('queue', 'Staff\Queue::index');
    $routes->post('queue/add', 'Staff\Queue::add');
    $routes->post('queue/update/(:num)/(:segment)', 'Staff\Queue::updateStatus/$1/$2');
    $routes->post('queue/updatePassengers/(:num)/(:segment)', 'Staff\Queue::updatePassengers/$1/$2');
    $routes->post('queue/setPassengers/(:num)', 'Staff\Queue::setPassengers/$1');

    // Departure Rules (staff view-only access)
    $routes->get('departure-rules', 'Admin\DepartureRules::index');
});
