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
$routes->get('auth/session-status', 'Auth::sessionStatus', ['filter' => 'auth']);
$routes->get('forgot-password', 'Auth::forgotPassword');
$routes->post('forgot-password', 'Auth::sendResetCode');
$routes->get('verify-reset-code/(:any)', 'Auth::verifyResetCode/$1');
$routes->post('verify-reset-code/(:any)', 'Auth::submitResetCode/$1');
$routes->post('resend-reset-code/(:any)', 'Auth::resendResetCode/$1');
$routes->get('reset-password/(:any)', 'Auth::resetPassword/$1');
$routes->post('reset-password/(:any)', 'Auth::updatePassword/$1');
// seed-users route removed for security — use CLI: php spark db:seed UserSeeder
$routes->get('guest', 'Home::index');
$routes->get('status', 'Home::status');
$routes->get('search', 'Search::index');
$routes->get('schedules', 'Schedules::index');
$routes->get('schedules/status', 'Schedules::status');
$routes->get('fares', 'Fares::index');
$routes->get('api/fares', 'Fares::apiData');
$routes->get('history', 'History::index', ['filter' => 'auth:admin,staff']);
$routes->post('contact/send', 'Contact::send');
$routes->get('manual', 'Manual::index');
$routes->get('user-manual', 'Manual::index');

// User Profile Avatar Upload & Removal (Admin and Staff)
$routes->post('profile/upload-avatar', 'Profile::uploadAvatar', ['filter' => 'auth:admin,staff']);
$routes->post('profile/remove-avatar', 'Profile::removeAvatar', ['filter' => 'auth:admin,staff']);

// Change Password with Code Authentication (Dispatcher only)
$routes->get('change-password', 'Auth::changePassword', ['filter' => 'auth:staff']);
$routes->post('change-password/send-code', 'Auth::sendChangePasswordCode', ['filter' => 'auth:staff']);
$routes->post('change-password/update', 'Auth::updateChangedPassword', ['filter' => 'auth:staff']);

// Public API for real-time queue sync (no auth required - read-only)
$routes->get('api/queue-status', 'Api\QueueStatus::index');
$routes->get('api/check-vehicle-availability/(:num)', 'Api\QueueStatus::checkAvailability/$1');
$routes->get('api/announcements', 'Api\Announcements::index');

// Route & Fare Management — Admin only (route CRUD + fare/discount management)
$routes->group('admin/routes', ['filter' => 'auth:admin'], function ($routes) {
    $routes->get('', 'Admin\Routes::index');
    // Fare & Discount Management
    $routes->post('discounts/store', 'Admin\Routes::storeDiscount');
    $routes->post('discounts/update/(:num)', 'Admin\Routes::updateDiscount/$1');
    $routes->post('discounts/delete/(:num)', 'Admin\Routes::deleteDiscount/$1');
    // Route CRUD
    $routes->get('create', 'Admin\Routes::create');
    $routes->post('store', 'Admin\Routes::store');
    $routes->get('edit/(:num)', 'Admin\Routes::edit/$1');
    $routes->post('update/(:num)', 'Admin\Routes::update/$1');
    $routes->post('update_group/(:num)', 'Admin\Routes::updateGroup/$1');
    $routes->post('delete/(:num)', 'Admin\Routes::delete/$1');
    $routes->post('delete-fare/(:num)', 'Admin\Routes::deleteFare/$1');
    $routes->post('delete_group/(:num)', 'Admin\Routes::deleteGroup/$1');
    $routes->post('deactivate_group/(:num)', 'Admin\Routes::deactivateGroup/$1');
    $routes->post('activate_group/(:num)', 'Admin\Routes::activateGroup/$1');
    $routes->post('bulk-action', 'Admin\Routes::bulkAction');
});

// Vehicle Register — Admin only (per DFD 2.1: only Admin inputs vehicle records)
$routes->group('admin/vehicles', ['filter' => 'auth:admin'], function ($routes) {
    $routes->get('', 'Admin\Vehicles::index');
    $routes->get('check-plate', 'Admin\Vehicles::checkPlate');
    $routes->get('edit/(:num)', 'Admin\Vehicles::edit/$1');
    $routes->post('store', 'Admin\Vehicles::store');
    $routes->post('update/(:num)', 'Admin\Vehicles::update/$1');
    $routes->post('delete/(:num)', 'Admin\Vehicles::delete/$1');
    $routes->post('deactivate/(:num)', 'Admin\Vehicles::deactivate/$1');
    $routes->post('activate/(:num)', 'Admin\Vehicles::activate/$1');
    $routes->post('bulk-action', 'Admin\Vehicles::bulkAction');
});

// Vehicle type configuration — Admin and Super Admin
$routes->post('admin/vehicle-types/store', 'Admin\VehicleTypes::store', ['filter' => 'auth:admin']);
$routes->post('admin/vehicle-types/update/(:num)', 'Admin\VehicleTypes::update/$1', ['filter' => 'auth:admin']);
$routes->post('admin/vehicle-types/delete/(:num)', 'Admin\VehicleTypes::delete/$1', ['filter' => 'auth:admin']);

// Announcements (Accessible by both Admin and Staff)
$routes->group('admin/announcements', ['filter' => 'auth:admin,staff'], function ($routes) {
    $routes->get('', 'Admin\Announcements::index');
    $routes->get('create', 'Admin\Announcements::create');
    $routes->post('store', 'Admin\Announcements::store');
    $routes->get('edit/(:num)', 'Admin\Announcements::edit/$1');
    $routes->post('update/(:num)', 'Admin\Announcements::update/$1');
    $routes->post('delete/(:num)', 'Admin\Announcements::delete/$1');
    $routes->post('delete-all', 'Admin\Announcements::deleteAll');
    $routes->post('deleteAll', 'Admin\Announcements::deleteAll');
    $routes->post('bulk-action', 'Admin\Announcements::bulkAction');
});

// System Branding & Settings — Super Admin only
$routes->group('admin/settings', ['filter' => 'auth:super_admin'], function ($routes) {
    $routes->get('', 'Admin\Settings::index');
    $routes->post('update-branding', 'Admin\Settings::updateBranding');
    $routes->post('update-themes', 'Admin\Settings::updateThemes');
    $routes->post('update-footer', 'Admin\Settings::updateFooter');
    $routes->post('upload-logo', 'Admin\Settings::uploadLogo');
    $routes->post('reset-logo', 'Admin\Settings::resetLogo');
    $routes->post('upload-background', 'Admin\Settings::uploadBackground');
    $routes->post('reset-background', 'Admin\Settings::resetBackground');
    $routes->post('upload-slideshow-slot', 'Admin\Settings::uploadSlideshowSlot');
    $routes->post('add-slideshow-slot', 'Admin\Settings::addSlideshowSlot');
    $routes->post('delete-slideshow-slot', 'Admin\Settings::deleteSlideshowSlot');
    $routes->post('reset-slideshow-slot', 'Admin\Settings::resetSlideshowSlot');
    $routes->post('reset-all-slideshow', 'Admin\Settings::resetAllSlideshow');
    $routes->post('update-bg-mode', 'Admin\Settings::updateBackgroundMode');
    $routes->post('save-bg-mode', 'Admin\Settings::saveBgMode');
    $routes->post('upload-login-card', 'Admin\Settings::uploadLoginCard');
    $routes->post('reset-login-card', 'Admin\Settings::resetLoginCard');
    $routes->post('update-operations', 'Admin\Settings::updateOperations');
    $routes->get('content', 'Admin\Settings::content');
    $routes->post('content', 'Admin\Settings::updateContent');
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
    $routes->post('users/deactivate/(:num)', 'Admin\Users::deactivate/$1');
    $routes->post('users/activate/(:num)', 'Admin\Users::activate/$1');
    $routes->post('users/delete/(:num)', 'Admin\Users::delete/$1');
    $routes->post('users/upload-avatar/(:num)', 'Admin\Users::uploadAvatar/$1');
    $routes->post('users/remove-avatar/(:num)', 'Admin\Users::removeAvatar/$1');
    $routes->post('users/bulk-action', 'Admin\Users::bulkAction');

    // Terminals
    $routes->get('terminals', 'Admin\Terminals::index');
    $routes->get('terminals/create', 'Admin\Terminals::create');
    $routes->post('terminals/store', 'Admin\Terminals::store');
    $routes->get('terminals/edit/(:num)', 'Admin\Terminals::edit/$1');
    $routes->post('terminals/update/(:num)', 'Admin\Terminals::update/$1');
    $routes->post('terminals/delete/(:num)', 'Admin\Terminals::delete/$1');
    $routes->post('terminals/bulk-action', 'Admin\Terminals::bulkAction');

    // History
    $routes->get('history', 'Admin\History::index');
    $routes->get('history/print', 'Admin\History::print');

    // Logs
    $routes->get('logs', 'Admin\Logs::index');
    $routes->get('logs/print', 'Admin\Logs::print');


    // Departure Rules
    $routes->get('departure-rules', 'Admin\DepartureRules::index');
    $routes->get('departure-rules/create', 'Admin\DepartureRules::create');
    $routes->post('departure-rules/store', 'Admin\DepartureRules::store');
    $routes->get('departure-rules/edit/(:num)', 'Admin\DepartureRules::edit/$1');
    $routes->post('departure-rules/update/(:num)', 'Admin\DepartureRules::update/$1');
    $routes->post('departure-rules/delete/(:num)', 'Admin\DepartureRules::delete/$1');

    // User Manual & Help Guide
    $routes->get('manual', 'Manual::admin');
    $routes->get('help', 'Manual::adminHelp');
});

$routes->group('staff', ['filter' => 'auth:staff'], function ($routes) {
    $routes->get('dashboard', 'Staff\Dashboard::index');
    $routes->get('departures', 'Staff\Departures::index');
    $routes->get('departures/print', 'Staff\Departures::report');

    // Queue
    $routes->get('queue', 'Staff\Queue::index');
    $routes->post('queue/add', 'Staff\Queue::add');
    $routes->post('queue/update/(:num)/(:segment)', 'Staff\Queue::updateStatus/$1/$2');
    $routes->post('queue/setPassengers/(:num)', 'Staff\Queue::setPassengers/$1');
    $routes->post('queue/updateDriver/(:num)', 'Staff\Queue::updateDriver/$1');
    $routes->post('queue/undoCancel/(:num)', 'Staff\Queue::undoCancel/$1');
    $routes->post('queue/reorder', 'Staff\Queue::reorder');
    $routes->post('queue/cancel-selected', 'Staff\Queue::cancelSelected');
    $routes->get('queue/cancel-selection', 'Staff\Queue::cancelSelection');

    // Departure rules for assigned destinations and their terminal-wide defaults.
    $routes->get('departure-rules', 'Admin\DepartureRules::index');
    $routes->get('departure-rules/create', 'Admin\DepartureRules::create');
    $routes->post('departure-rules/store', 'Admin\DepartureRules::store');
    $routes->get('departure-rules/edit/(:num)', 'Admin\DepartureRules::edit/$1');
    $routes->post('departure-rules/update/(:num)', 'Admin\DepartureRules::update/$1');
    $routes->post('departure-rules/delete/(:num)', 'Admin\DepartureRules::delete/$1');
    $routes->post('queue/round', 'Staff\Queue::setRound');
    $routes->post('queue/tick', 'Staff\Queue::tick');

    // User Manual & Help Guide
    $routes->get('manual', 'Manual::staff');
    $routes->get('help', 'Manual::staffHelp');
});

