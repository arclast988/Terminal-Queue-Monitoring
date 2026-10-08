<?php
declare(strict_types=1);

// Render production views with deterministic data, without a database or login.
// These helpers exist only in this CLI test process; app helpers are untouched.
define('FCPATH', dirname(__DIR__, 2) . '/public/');
$fixturePage = $argv[1] ?? 'login';
if (in_array($fixturePage, ['staff-departures', 'staff-departures-report', 'staff-dashboard'], true)) date_default_timezone_set('Asia/Manila');
$fixtureLong = ($argv[2] ?? '') === 'long';
$fixtureRole = $fixturePage === 'admin-settings' ? 'super_admin' : (str_starts_with($fixturePage, 'staff') ? 'staff' : (str_starts_with($fixturePage, 'admin') ? 'admin' : null));
if (($argv[6] ?? '') === 'super_admin' && str_starts_with($fixturePage, 'admin')) $fixtureRole = 'super_admin';
set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
function esc($value, string $context = 'html'): string { return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function base_url(string $path = ''): string { return '/' . ltrim($path, '/'); }
function current_url(): string { return '/guest'; }
function uri_string(): string { return 'guest'; }
function url_is(string $path): bool { return false; }
function old(string $key, $default = null) { return $default; }
function csrf_field(): string { return '<input type="hidden" name="csrf_fixture" value="unchanged">'; }
function csrf_token(): string { return 'csrf_fixture'; }
function csrf_hash(): string { return 'unchanged'; }
function csrf_header(): string { return 'X-CSRF-TOKEN'; }
function app_name(): string { return $GLOBALS['fixtureLong'] ? 'PalomponTerminalMonitoringWithAnUnbrokenLongName' : 'Palompon Terminal'; }
function app_subtitle(): string { return 'Transit Terminal Monitoring System'; }
function app_system_title(): string { return app_name(); }
function get_system_setting(string $key, $default = null) {
    $theme = ($GLOBALS['argv'][4] ?? '') === 'custom-theme' ? [
        'theme_staff_primary'=>'#155e75', 'theme_staff_nav_bg'=>'#164e63',
        'theme_admin_primary'=>'#7040b0', 'theme_admin_nav_bg'=>'#563084',
    ] : [];
    return $theme[$key] ?? $default;
}
if (in_array($fixturePage, ['guest-contact-draft', 'staff-guide', 'admin-guide'], true)) {
    // Keep the real footer/modals while stubbing only managed settings/config.
    final class FixtureContentManagement { public static function fields(): array { return []; } }
    class_alias(FixtureContentManagement::class, 'Config\\ContentManagement');
    function config(string $name): object { return (object) ['recipients'=>'management@example.com', 'fromEmail'=>'', 'SMTPUser'=>'']; }
}
if (($argv[3] ?? '') !== 'theme') {
    function app_theme_css(): string { return ''; }
}
// Use the real asset version helper in every production-view fixture.
require dirname(__DIR__, 2) . '/app/Common.php';
if (!function_exists('media_url')) {
    function media_url(?string $path): string {
        $path = trim((string) $path);
        if ($path === '' || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) return $path;
        return base_url(ltrim(str_replace('\\', '/', $path), '/'));
    }
}
function app_logo(): string { return '/fixture.svg'; }
function app_bg_mode(): string { return ($GLOBALS['argv'][5] ?? '') === 'slideshow' ? 'slideshow' : 'single'; }
function app_has_custom_bg(): bool { return false; }
function app_bg_image(): string { return '/fixture.svg'; }
function app_bg_slideshow(): array { return app_bg_mode() === 'slideshow' ? ['/fixture.svg', '/fixture-second.svg', '/fixture-third.svg'] : ['/fixture.svg']; }
function app_has_custom_login_card(): bool { return false; }
function app_login_card_image(): string { return '/fixture.svg'; }
function login_kicker(): string { return 'Terminal operations'; }
function login_headline_html(): string { return 'Keep your terminal <span class="accent">moving smoothly.</span>'; }
function login_headline(): string { return 'Keep your terminal moving smoothly.'; }
function app_footer_copyright(): string { return 'Terminal operations'; }
function login_subheadline(): string { return 'Manage queues and departures.'; }
function login_feature1_title(): string { return 'Live queue'; }
function login_feature1_desc(): string { return 'Keep passenger information current.'; }
function login_feature2_title(): string { return 'Secure access'; }
function login_feature2_desc(): string { return 'Your existing account and permissions.'; }
function vehicle_type_colors_css(): string { return ''; }
function vehicle_type_color(string $type): string { return '#1565c0'; }
function contrast_text_color(string $color): string { return '#ffffff'; }
function vehicle_type_icon(string $type): string { return 'fa-bus'; }
function vehicle_type_key(string $type): string { return strtolower($type); }
function vehicle_type_label(string $type): string { return ucfirst($type); }
function vehicle_type_class(string $type): string { return 'vehicle-type-' . esc(strtolower($type)); }
function vehicle_type_photo(string $type): string { return '/fixture.svg'; }
function vehicle_resolved_photo(array $item, string $type): string {
    if (($GLOBALS['argv'][4] ?? '') === 'vehicle-type-photos' && !empty($item['vehicle_photo'] ?? $item['photo'] ?? null)) return media_url($item['vehicle_photo'] ?? $item['photo']);
    return '/fixture.svg';
}
function passenger_color_class(int $count, int $capacity): string { return 'passenger-color-green'; }
function vehicle_type_badge(string $type): string { return '<span class="vehicle-type-chip ' . vehicle_type_class($type) . '">' . esc(vehicle_type_label($type)) . '</span>'; }
function get_db_vehicle_types(): array { return [['id' => 1, 'slug' => 'jeepney', 'name' => 'Jeepney', 'color' => '#1565c0', 'icon' => 'fa-bus', 'photo' => '/fixture.svg']]; }
function session(): object {
    static $session;
    return $session ??= new class {
        public function get(string $key) {
            return match ($key) {
                'isLoggedIn' => $GLOBALS['fixtureRole'] !== null,
                'role' => $GLOBALS['fixtureRole'],
                'full_name' => 'Fixture Dispatcher',
                'username' => 'fixture-user',
                'guest_contact_pending' => ($GLOBALS['fixtureState'] ?? '') === 'empty' ? null : ($GLOBALS['fixtureData']['pending'] ?? null),
                default => null,
            };
        }
        public function getFlashdata(string $key) { return null; }
    };
}
final class ResponsiveFixtureRenderer {
    public function render(string $name, array $data = []): string {
        // Management fixtures include the real footer and Bootstrap/support modals.
        if (($name === 'templates/guestfooter' && $GLOBALS['fixturePage'] !== 'guest-contact-draft') || ($name === 'templates/footer' && !in_array($GLOBALS['fixturePage'], $GLOBALS['managementPages'], true) && ($GLOBALS['argv'][7] ?? '') !== 'shared-dialogs')) return '';
        extract($GLOBALS['fixtureData']);
        extract($data, EXTR_OVERWRITE);
        ob_start();
        require dirname(__DIR__, 2) . '/app/Views/' . $name . '.php';
        return (string) ob_get_clean();
    }
    public function include(string $name): string { return $this->render($name); }
}
function view(string $name, array $data = []): string { return $GLOBALS['fixtureRenderer']->render($name, $data); }
$longValue = $fixtureLong ? str_repeat('LongDestination', 5) : 'Ormoc City';
$item = [
    'id' => 101, 'vehicle_id' => 201, 'terminal_id' => 1, 'position' => 1, 'queue_number' => 1,
    'status' => 'waiting', 'current_passengers' => 8, 'capacity' => 20,
    'plate_number' => 'ABC-123', 'vehicle_type' => 'jeepney', 'vehicle_photo' => null,
    'operator_name' => $longValue, 'owner_name' => $longValue, 'driver_name' => $longValue,
    'origin' => 'Palompon', 'destination' => $longValue, 'fare' => 100, 'price' => 100,
    'arrival_time' => '2026-10-01 08:00:00', 'estimated_departure' => '2026-10-01 09:00:00',
    'departure_time' => '2026-10-01 09:00:00', 'boarding_start' => '2026-10-01 08:45:00', 'round_number' => 1,
];
$route = ['id' => 1, 'terminal_id' => 1, 'origin' => 'Palompon', 'destination' => $longValue, 'fare' => 100, 'vehicle_type' => 'jeepney'];
$terminal = ['id' => 1, 'name' => $longValue, 'location' => $longValue, 'capacity' => 20, 'created_at' => '2026-10-01 08:00:00'];
$user = ['id' => 2, 'username' => 'fixture-dispatcher', 'full_name' => $longValue, 'email' => 'fixture@example.com', 'role' => 'staff', 'status' => 'active', 'created_at' => '2026-10-01 08:00:00', 'assigned_routes' => $longValue, 'profile_image' => null];
$managementPages = ['admin-users', 'admin-vehicles', 'admin-vehicle-edit', 'admin-routes', 'admin-terminals', 'admin-announcements', 'admin-rules', 'admin-history', 'admin-logs', 'admin-settings'];
$vehicle = $item + ['type' => 'jeepney', 'photo' => null, 'route_origin' => 'Palompon', 'route_destination' => $longValue, 'created_at' => '2026-10-01 08:00:00'];
$vehicle['status'] = 'active';
$fixtureData = [
    'title' => 'Fixture', 'body_class' => $fixtureRole . '-theme', 'token' => 'fixture-token', 'error' => null,
    'masked_email' => 'f***@example.com', 'has_email' => true, 'has_active_token' => false, 'invalid' => false, 'noRoutesAssigned' => false,
    'announcements' => [], 'breadcrumb_current' => 'Home', 'skip_breadcrumb' => true,
    'queueRoutes' => [
        ['id'=>1, 'terminal_id'=>1, 'origin'=>'Palompon', 'destination'=>$longValue, 'round_number'=>1, 'round_choices'=>[1,2,3], 'round_intervals'=>[1=>20,2=>25,3=>30]],
        ['id'=>2, 'terminal_id'=>1, 'origin'=>'Palompon', 'destination'=>'BATO', 'round_number'=>2, 'round_choices'=>[1,2,3], 'round_intervals'=>[1=>20,2=>25,3=>30]],
    ],
    'queue' => [$item], 'active_queue' => [$item], 'vehicles' => [$item], 'schedules' => [$item],
    'recent_departures' => [$item], 'routes' => [$route], 'jeepney_routes' => [$route],
    'van_routes' => [], 'minibus_routes' => [], 'routesByType' => ['jeepney' => [$route]],
    'discounts' => [], 'departure_rules' => [], 'vehicleTypes' => get_db_vehicle_types(),
    'vehicle_type' => '', 'destination' => '', 'search' => '', 'all_destinations' => [$longValue],
    'active_dest_counts' => [$longValue => 1], 'total_active_count' => 1,
    'active_results' => [$item], 'total_results' => 1,
    'users' => [$user], 'terminals' => [$terminal], 'rules' => [['id' => 1, 'route_id' => 1, 'terminal_name' => 'Palompon', 'route_destination' => $longValue, 'time_from' => '08:00', 'time_to' => '18:00', 'wait_minutes' => 15, 'label' => $longValue]],
    'departures' => [$item], 'destinations' => [['destination' => $longValue]], 'destinationVehicleTypes' => [],
    'logs' => [$user + ['action' => 'Updated queue', 'details' => $longValue, 'timestamp' => '2026-10-01 08:00:00']],
    'actions' => [['action' => 'Updated queue']], 'stats' => [], 'pager' => null,
    'from_date' => '', 'to_date' => '', 'action_type' => '', 'user_id' => '', 'settings' => [], 'prefix' => 'admin',
    'today' => date('Y-m-d'), 'filters' => ['q'=>'', 'destination'=>'', 'vehicle_type'=>''],
    'groupedRoutes' => [['terminal_name' => 'Palompon', 'destination' => $longValue, 'status' => 'active', 'items' => [$route + ['terminal_id' => 1]]]],
];
if (in_array($fixturePage, ['staff-departures', 'staff-departures-report'], true)) {
    $fixtureData['departures'] = [];
    for ($id = 1; $id <= 37; $id++) {
        $fixtureData['departures'][] = array_replace($item, ['id'=>$id, 'plate_number'=>'TODAY-'.sprintf('%02d',$id),
            'operator_name'=>'Operator '.$id, 'driver_name'=>'Driver '.$id, 'status'=>'departed',
            'departure_time'=>date('Y-m-d').' 20:55:00']);
    }
    $fixtureData['results'] = $fixtureData['departures'];
    $fixtureData['stats'] = ['departures'=>37, 'passengers'=>370];
    $fixtureData['title'] = 'Today Departures';
    if ($fixturePage === 'staff-departures-report') {
        $fixtureData['title'] = "Today's Departure Report";
        $fixtureData['report_heading'] = "Today's Departure Report";
        $fixtureData['today_only_report'] = true;
        $fixtureData['from_date'] = $fixtureData['to_date'] = date('Y-m-d');
        $fixtureData['return_url'] = '/staff/departures';
    }
}
if ($fixturePage === 'staff-dashboard') $fixtureData['active_queue_count'] = 1;
if (in_array($fixturePage, ['staff-rule-create', 'staff-rule-edit', 'staff-rules', 'admin-rule-create', 'admin-rule-edit'], true)) {
    $fixtureData['prefix'] = str_starts_with($fixturePage, 'staff') ? 'staff' : 'admin';
    $fixtureData['title'] = 'Departure Rules';
    $fixtureData['returnRoute'] = 'all';
    $fixtureData['listUrl'] = '/' . $fixtureData['prefix'] . '/departure-rules';
    $fixtureData['selectedRouteId'] = null;
    $fixtureData['rule'] = ['id'=>1, 'terminal_id'=>1, 'route_id'=>null, 'time_from'=>'05:00:00', 'time_to'=>'17:00:00', 'wait_minutes'=>20, 'label'=>'Morning', 'day_of_week'=>null, 'days_of_week'=>'1,2,3', 'round_number'=>1, 'terminal_name'=>'Villaba', 'route_destination'=>null, 'round_scope'=>'1|', 'can_manage'=>true];
    $fixtureData['existingRules'] = [$fixtureData['rule'], array_replace($fixtureData['rule'], ['id'=>2, 'round_number'=>2, 'days_of_week'=>'1,4,7', 'wait_minutes'=>25, 'label'=>'Afternoon'])];
    $fixtureData['rules'] = $fixtureData['existingRules'];
    $fixtureData['destinationsMap'] = ['ORMOC'=>1, 'TACLOBAN'=>2];
    if (($argv[4] ?? '') === 'scoped-rounds') {
        $fixtureData['terminals'] = [['id'=>1,'name'=>'Villaba']];
        $fixtureData['routes'] = [['id'=>1,'terminal_id'=>1,'destination'=>'ORMOC'], ['id'=>2,'terminal_id'=>1,'destination'=>'TACLOBAN']];
        $fixtureData['rule'] = array_replace($fixtureData['rule'], ['route_id'=>2,'route_destination'=>'TACLOBAN','round_scope'=>'1|TACLOBAN','round_number'=>2]);
        $fixtureData['existingRules'] = [$fixtureData['rule']];
        foreach ([1,2,3] as $round) $fixtureData['existingRules'][] = array_replace($fixtureData['rule'], ['id'=>10+$round,'route_id'=>1,'route_destination'=>'ORMOC','round_scope'=>'1|ORMOC','round_number'=>$round]);
        $fixtureData['selectedRouteId'] = 2;
    }
    if (($argv[4] ?? '') === 'conflicting-round') {
        $fixtureData['existingRules'][] = array_replace($fixtureData['rule'], ['id'=>3, 'round_number'=>3]);
    }
    if (($argv[4] ?? '') === 'unique-day-rounds') {
        $fixtureData['terminals'] = [['id'=>1, 'name'=>'Villaba']];
        $fixtureData['routes'] = [['id'=>1, 'terminal_id'=>1, 'destination'=>'ORMOC'], ['id'=>2, 'terminal_id'=>1, 'destination'=>'TACLOBAN']];
        $fixtureData['rule'] = array_replace($fixtureData['rule'], ['route_id'=>2, 'route_destination'=>'TACLOBAN', 'round_scope'=>'1|TACLOBAN', 'days_of_week'=>null]);
        $fixtureData['existingRules'] = [$fixtureData['rule'], array_replace($fixtureData['rule'], ['id'=>2, 'route_id'=>1, 'route_destination'=>'ORMOC', 'round_scope'=>'1|ORMOC', 'days_of_week'=>'1', 'time_from'=>'06:00:00', 'time_to'=>'17:00:00'])];
    }
    if (($argv[4] ?? '') === 'moving-round') {
        $fixtureData['terminals'] = [['id'=>1, 'name'=>'Villaba']];
        $fixtureData['routes'] = [['id'=>1, 'terminal_id'=>1, 'destination'=>'ORMOC'], ['id'=>2, 'terminal_id'=>1, 'destination'=>'TACLOBAN'], ['id'=>3, 'terminal_id'=>1, 'destination'=>'BATO']];
        $fixtureData['rule'] = array_replace($fixtureData['rule'], ['route_id'=>2, 'route_destination'=>'TACLOBAN', 'round_scope'=>'1|TACLOBAN', 'days_of_week'=>null]);
        $fixtureData['existingRules'] = [$fixtureData['rule'], array_replace($fixtureData['rule'], ['id'=>2, 'route_id'=>1, 'route_destination'=>'ORMOC', 'round_scope'=>'1|ORMOC'])];
        $fixtureData['selectedRouteId'] = 2;
    }
}
if ($fixturePage === 'admin-vehicles') $fixtureData['vehicles'] = [$vehicle];
if ($fixturePage === 'admin-vehicle-edit') {
    $fixtureData['vehicle'] = $vehicle + ['default_route_id'=>1, 'dispatch_order'=>2, 'dispatch_position'=>2];
}
if (in_array($fixturePage, ['admin-vehicles','admin-vehicle-edit'], true) && ($argv[4] ?? '') === 'dispatch-register') {
    $fixtureData['routes'] = [array_replace($route,['destination'=>'ORMOC']), array_replace($route,['id'=>2,'destination'=>'BATO'])];
    $fixtureData['vehicleTypes'][] = ['id'=>2,'slug'=>'van','name'=>'Van','color'=>'#b71c1c','icon'=>'fa-bus','photo'=>null];
    $fixtureData['vehicles'] = [
        array_replace($vehicle,['id'=>201,'plate_number'=>'ZZZ-001','type'=>'van','route_terminal_id'=>1,'route_destination'=>'ORMOC','dispatch_order'=>1]),
        array_replace($vehicle,['id'=>202,'plate_number'=>'AAA-002','route_terminal_id'=>1,'route_destination'=>'ORMOC','dispatch_order'=>2]),
        array_replace($vehicle,['id'=>203,'plate_number'=>'BATO-003','route_terminal_id'=>1,'route_destination'=>'BATO','dispatch_order'=>1]),
        array_replace($vehicle,['id'=>204,'plate_number'=>'NO-ROUTE','status'=>'archived','route_destination'=>null,'route_origin'=>null]),
    ];
}

if (in_array($fixturePage, ['admin-vehicles','admin-vehicle-edit'], true) && ($argv[4] ?? '') === 'dispatch-order-difference') {
    $fixtureData['routes'] = [array_replace($route, ['destination'=>'ORMOC'])];
    $fixtureData['vehicles'] = [];
    foreach (range(1,9) as $order) {
        $fixtureData['vehicles'][] = array_replace($vehicle, ['id'=>200+$order, 'plate_number'=>'ORDER-'.sprintf('%02d',$order),
            'default_route_id'=>1, 'route_terminal_id'=>1, 'route_destination'=>'ORMOC', 'dispatch_order'=>$order,
            'dispatch_rotation'=>$order <= 6 ? $order : 0, 'dispatch_rotation_date'=>date('Y-m-d')]);
    }
    $fixtureData['vehicle'] = end($fixtureData['vehicles']);
}

if ($fixturePage === 'guest' && ($argv[4] ?? '') === 'guest-rounds') {
    foreach ([1=>20, 2=>25] as $round=>$minutes) {
        $fixtureData['departure_rules'][] = ['id'=>$round,'label'=>'Round schedule','round_number'=>$round,'days_label'=>'Every day','time_range'=>'12:00 AM – 11:59 PM','wait_minutes'=>$minutes,'interval_label'=>'Every '.$minutes.' min','route_scope'=>'All Routes','route_destination'=>null,'is_active_now'=>$round===1,'active_destinations'=>$round===1?['ORMOC']:[]];
    }
}
if ($fixturePage === 'admin-announcements') $fixtureData['announcements'] = [['id' => 1, 'terminal_name' => 'Palompon', 'severity' => 'info', 'message' => $longValue, 'is_active' => true, 'created_at' => '2026-10-01 08:00:00']];
if ($fixturePage === 'staff-queue') {
    if (in_array(($argv[4] ?? ''), ['round-overdue', 'round-extended'], true)) {
        $extended = ($argv[4] ?? '') === 'round-extended';
        $fixtureData['queue'][0] = array_replace($item, ['status'=>'boarding', 'round_number'=>$extended ? 2 : 1, 'boarding_start'=>date('Y-m-d H:i:s', time()-1800), 'estimated_departure'=>date('Y-m-d H:i:s', time()+($extended ? 600 : -600))]);
        $fixtureData['queueRoutes'][0]['round_number'] = $extended ? 2 : 1;
        $fixtureData['queueRoutes'][0]['round_intervals'][2] = 40;
    }
    if (($argv[4] ?? '') === 'unconfigured-round') {
        unset($fixtureData['queueRoutes'][1]['round_intervals'][2], $fixtureData['queueRoutes'][1]['round_intervals'][3]);
    }
    if (($argv[4] ?? '') === 'single-round') {
        $fixtureData['queueRoutes'][0]['round_intervals'] = [1=>20];
        $fixtureData['queueRoutes'][1]['round_intervals'] = [2=>100];
    }
    if (($argv[4] ?? '') === 'expired-round') {
        unset($fixtureData['queueRoutes'][0]['round_intervals'][1]);
        $fixtureData['queueRoutes'][0]['round_intervals'][2] = 100;
        $fixtureData['queueRoutes'][1]['round_intervals'] = [];
    }
    if (in_array(($argv[4] ?? ''), ['many', 'search-many'], true)) {
        $fixtureData['queue'] = [];
        for ($position = 1; $position <= 10; $position++) {
            $trip = array_replace($item, ['id'=>100+$position, 'position'=>$position, 'plate_number'=>'TRIP-'.$position, 'status'=>$position === 1 ? 'boarding' : 'waiting']);
            if ($position > 5) $trip['destination'] = 'BATO';
            if (($argv[4] ?? '') === 'search-many') {
                $trip['operator_name'] = 'Operator ' . $position;
                $trip['driver_name'] = 'Driver ' . $position;
                $trip['vehicle_type'] = $position % 2 === 0 ? 'van' : 'minibus';
            }
            $fixtureData['queue'][] = $trip;
        }
    }
    $fixtureData['vehicles'] = [$vehicle + ['route_terminal_id'=>1, 'default_route_id'=>1]];
    if (($argv[4] ?? '') === 'empty') $fixtureData['vehicles'] = [];
    if (($argv[4] ?? '') === 'fresh') {
        $newVehicle = $vehicle + ['route_terminal_id'=>1, 'default_route_id'=>2];
        $newVehicle['id'] = 202;
        $newVehicle['plate_number'] = 'NEW-789';
        $newVehicle['route_destination'] = 'BATO';
        $newVehicle['round_number'] = 2;
        $fixtureData['vehicles'][] = $newVehicle;
    }
}
if ($fixturePage === 'staff-queue' && in_array(($argv[4] ?? ''), ['dispatch-before','dispatch-departed','dispatch-reset'], true)) {
    $fixtureData['queue'] = [];
    $fixtureData['queueRoutes'][0]['destination'] = 'ORMOC';
    $fixtureData['vehicles'] = [];
    $order = ($argv[4] ?? '') === 'dispatch-departed' ? [202,203,201] : [201,202,203];
    foreach ($order as $position=>$id) {
        $fixtureData['vehicles'][] = array_replace($vehicle, [
            'id'=>$id, 'plate_number'=>[201=>'ZZZ-001',202=>'AAA-002',203=>'MID-003'][$id],
            'route_terminal_id'=>1, 'route_destination'=>'ORMOC', 'default_route_id'=>1,
            'dispatch_order'=>$id-200, 'dispatch_position'=>$position+1,
            'is_departed'=>($argv[4] ?? '') === 'dispatch-departed' && $id===201,
            'departed_time'=>'11:59 PM', 'round_number'=>1,
        ]);
    }
}
if ($fixturePage === 'admin-vehicles' && ($argv[4] ?? '') === 'register-order') {
    $fixtureData['routes'] = [array_replace($route,['destination'=>'ORMOC']), array_replace($route,['id'=>2,'destination'=>'BATO'])];
    $fixtureData['vehicleTypes'][] = ['id'=>2,'slug'=>'van','name'=>'Van','color'=>'#b71c1c','icon'=>'fa-bus','photo'=>null];
    $orderedVehicle = array_replace($vehicle, ['route_terminal_id'=>1,'route_origin'=>'VILLABA','route_destination'=>'ORMOC']);
    $fixtureData['vehicles'] = [
        array_replace($orderedVehicle,['id'=>203,'plate_number'=>'MID-003','type'=>'van','dispatch_order'=>3]),
        array_replace($orderedVehicle,['id'=>201,'plate_number'=>'ZZZ-001','type'=>'van','dispatch_order'=>1,'dispatch_rotation'=>1,'dispatch_rotation_date'=>date('Y-m-d')]),
        array_replace($orderedVehicle,['id'=>202,'plate_number'=>'AAA-002','dispatch_order'=>2]),
        array_replace($orderedVehicle,['id'=>204,'plate_number'=>'BATO-004','route_destination'=>'BATO','dispatch_order'=>1]),
        array_replace($orderedVehicle,['id'=>205,'plate_number'=>'ARCHIVED-005','status'=>'archived','dispatch_order'=>1]),
        array_replace($orderedVehicle,['id'=>206,'plate_number'=>'MAINTENANCE-006','status'=>'maintenance','dispatch_order'=>2]),
        array_replace($orderedVehicle,['id'=>207,'plate_number'=>'UNASSIGNED-007','route_destination'=>null,'route_origin'=>null]),
    ];
}
if (in_array($fixturePage, ['admin-vehicles','admin-vehicle-edit'], true) && isset($fixtureData['vehicles'])) {
    // The browser workflow uses the bundled framework without Composer installation.
    spl_autoload_register(static function (string $class): void {
        $prefix = 'CodeIgniter\\';
        if (!str_starts_with($class, $prefix)) return;
        $file = dirname(__DIR__, 2) . '/system/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) require_once $file;
    });
    require_once dirname(__DIR__, 2) . '/app/Models/VehicleModel.php';
    $fixtureData['vehicles'] = \App\Models\VehicleModel::sortForRegister($fixtureData['vehicles'], date('Y-m-d'));
    if ($fixturePage === 'admin-vehicle-edit' && ($argv[4] ?? '') === 'dispatch-order-difference') {
        foreach ($fixtureData['vehicles'] as $registered) {
            if ($registered['id'] === $fixtureData['vehicle']['id']) $fixtureData['vehicle'] = $registered;
        }
    }
}
$fixtureRenderer = new ResponsiveFixtureRenderer();
if (($argv[4] ?? '') === 'vehicle-type-photos') {
    $customItem = array_replace($item, ['id'=>102, 'vehicle_id'=>202, 'position'=>2, 'plate_number'=>'CUSTOM-202', 'vehicle_photo'=>'/fixture-custom.svg']);
    $fixtureData['active_queue'] = [$item, $customItem];
    $fixtureData['queue'] = [$item, $customItem];
    $fixtureData['vehicles'] = $fixturePage === 'admin-vehicles'
        ? [$vehicle, array_replace($vehicle, ['id'=>102, 'plate_number'=>'CUSTOM-202', 'photo'=>'/fixture-custom.svg', 'vehicle_photo'=>'/fixture-custom.svg'])]
        : [$item, $customItem];
}
if (($argv[4] ?? '') === 'vehicle-type-edit-photos') {
    $fixtureData['vehicleTypes'] = [
        ['id'=>1, 'slug'=>'minibus', 'name'=>'Minibus', 'color'=>'#2e7d32', 'icon'=>'fa-bus', 'photo'=>'https://res.cloudinary.com/demo/image/upload/v124/vehicle_types/jeepney.png'],
        ['id'=>2, 'slug'=>'van', 'name'=>'Van', 'color'=>'#b71c1c', 'icon'=>'fa-van-shuttle', 'photo'=>'/fixture.svg'],
        ['id'=>3, 'slug'=>'jeepney', 'name'=>'Jeepney', 'color'=>'#1565c0', 'icon'=>'fa-truck-front', 'photo'=>null],
    ];
}
$views = [
    'login' => 'auth/login', 'forgot' => 'auth/forgot_password', 'reset' => 'auth/reset_password', 'verify' => 'auth/verify_code',
    'guest' => 'public/enhanced_dashboard', 'fares' => 'public/fares', 'schedules' => 'public/schedules', 'search' => 'public/search',
    'guest-contact-draft' => 'public/enhanced_dashboard',
    'staff-queue' => 'staff/queue/index', 'staff-schedules' => 'shared/schedules', 'admin-schedules' => 'shared/schedules',
    'staff-password' => 'auth/change_password', 'staff-guide' => 'help/dispatcher', 'admin-guide' => 'help/admin',
    'staff-manual' => 'manual/staff', 'admin-manual' => 'manual/admin',
    'staff-dashboard' => 'staff/dashboard', 'staff-departures' => 'staff/departures/index', 'staff-departures-report' => 'admin/history/print_history',
    'admin-users' => 'admin/users/index', 'admin-vehicles' => 'admin/vehicles/index', 'admin-vehicle-edit' => 'admin/vehicles/edit', 'admin-routes' => 'admin/routes/index',
    'admin-terminals' => 'admin/terminals/index', 'admin-announcements' => 'admin/announcements/index', 'admin-rules' => 'admin/departure-rules/index',
    'admin-history' => 'admin/history/index', 'admin-logs' => 'admin/logs/index', 'admin-settings' => 'admin/settings/index',
    'staff-rule-create' => 'admin/departure-rules/create', 'staff-rule-edit' => 'admin/departure-rules/edit', 'staff-rules' => 'admin/departure-rules/index',
    'admin-rule-create' => 'admin/departure-rules/create', 'admin-rule-edit' => 'admin/departure-rules/edit',
];
if (!isset($views[$fixturePage])) throw new InvalidArgumentException('Unknown fixture page');
$html = $fixtureRenderer->render($views[$fixturePage]);
if ($fixtureRole !== null && !in_array($fixturePage, $managementPages, true)) $html .= '</div></body></html>';
require_once dirname(__DIR__, 2) . '/app/Libraries/InitialStyles.php';
echo \App\Libraries\InitialStyles::prepare($html);
