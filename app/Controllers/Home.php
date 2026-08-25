<?php

namespace App\Controllers;

use App\Models\QueueModel;
use App\Models\AnnouncementModel;

class Home extends BaseController
{
    public function index()
    {
        if (session()->get('isLoggedIn')) {
            $role = session()->get('role');
            if (in_array($role, ['super_admin', 'admin'], true)) {
                return redirect()->to('/admin/dashboard');
            } elseif ($role === 'staff') {
                return redirect()->to('/staff/dashboard');
            }
        }

        helper('fare');

        $queueModel = new QueueModel();
        $routeModel = new \App\Models\RouteModel();

        $announcements = [];
        try {
            $announcements = $this->getActiveAnnouncements();
        } catch (\Throwable $e) {
            // Table may not exist yet; show guest page without announcements
        }

        $active_queue = $queueModel->select('queue.*, queue.estimated_departure, vehicles.plate_number, vehicles.operator_name, vehicles.driver_name, vehicles.type as vehicle_type, terminals.name as origin, routes.destination, queue.current_passengers, vehicles.capacity')
            ->withFullJoins()
            ->whereIn('queue.status', ['waiting', 'boarding'])
            ->orderBy('queue.position', 'ASC')
            ->orderBy('routes.destination', 'ASC')
            ->findAll();


        $data = [
            'title' => 'Live Terminal Monitor',
            'active_queue' => $active_queue,
            'recent_departures' => $queueModel->select('queue.*, vehicles.plate_number, vehicles.capacity, queue.current_passengers, vehicles.driver_name, terminals.name as origin, routes.destination')
                ->withFullJoins()
                ->where('queue.status', 'departed')
                ->like('queue.departure_time', date('Y-m-d'), 'after')
                ->orderBy('queue.departure_time', 'DESC')
                ->limit(10)
                ->findAll(),
            'total_departures_today' => $queueModel->where('status', 'departed')
                ->like('departure_time', date('Y-m-d'), 'after')
                ->countAllResults(),
            'departure_rules' => $this->getDepartureRules(),
            'route_average_departures' => $this->getRouteAverageDepartures(),
            'routes' => enrich_routes_with_discounts($routeModel->withOrigin()->orderBy('destination', 'ASC')->findAll()),
            'announcements' => $announcements
        ];

        return view('public/enhanced_dashboard', $data);
    }

    public function status()
    {
        helper('fare');

        // Cache the heavy joins/aggregation for a couple of seconds so that
        // many simultaneous public-dashboard polls share one computation.
        // The sync token is read live on every request (so clients still
        // detect changes), and broadcastUpdate() clears this key so a staff
        // action shows up on the next poll immediately.
        $payload = cache('rt_home_status');
        if (! is_array($payload)) {
            $queueModel = new QueueModel();

            $active_queue = $queueModel->select('queue.*, queue.estimated_departure, vehicles.plate_number, vehicles.operator_name, vehicles.driver_name, vehicles.type as vehicle_type, terminals.name as origin, routes.destination, queue.current_passengers, vehicles.capacity')
                ->withFullJoins()
                ->whereIn('queue.status', ['waiting', 'boarding'])
                ->orderBy('queue.position', 'ASC')
                ->orderBy('routes.destination', 'ASC')
                ->findAll();

            foreach ($active_queue as &$item) {
                $item['estimated_departure_formatted'] = !empty($item['estimated_departure']) ? date('h:i A', strtotime($item['estimated_departure'])) : 'N/A';
                $item['percent'] = min(100, ($item['current_passengers'] / max(1, $item['capacity'])) * 100);
            }
            unset($item);

            $recent_departures = $queueModel->select('queue.*, vehicles.plate_number, terminals.name as origin, routes.destination')
                ->withFullJoins()
                ->where('queue.status', 'departed')
                ->like('queue.departure_time', date('Y-m-d'), 'after')
                ->orderBy('queue.departure_time', 'DESC')
                ->limit(10)
                ->findAll();

            foreach ($recent_departures as &$dept) {
                $dept['departure_time_formatted'] = date('h:i A', strtotime($dept['departure_time']));
            }
            unset($dept);

            $total_departures_today = $queueModel->where('status', 'departed')
                ->like('departure_time', date('Y-m-d'), 'after')
                ->countAllResults();

            $routeModel = new \App\Models\RouteModel();

            $payload = [
                'active_queue' => $active_queue,
                'recent_departures' => $recent_departures,
                'total_departures_today' => $total_departures_today,
                'departure_rules' => $this->getDepartureRules(),
                'route_average_departures' => $this->getRouteAverageDepartures(),
                'routes' => enrich_routes_with_discounts($routeModel->withOrigin()->orderBy('destination', 'ASC')->findAll())
            ];

            cache()->save('rt_home_status', $payload, 2);
        }

        // Always read the sync token live so clients keep detecting changes.
        $payload['sync_token'] = @file_get_contents(WRITEPATH . 'sync_token.txt') ?: '0';

        return $this->response
            ->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0')
            ->setJSON($payload);
    }

    private function getDepartureRules(): array
    {
        $departureRuleModel = new \App\Models\DepartureRuleModel();
        $rules = $departureRuleModel
            ->select('departure_rules.*, terminals.name as terminal_name, routes.destination as route_destination')
            ->join('terminals', 'terminals.id = departure_rules.terminal_id', 'left')
            ->join('routes', 'routes.id = departure_rules.route_id', 'left')
            ->orderBy('departure_rules.time_from', 'ASC')
            ->findAll();

        $currentTime = date('H:i:s');

        $formatted = [];
        foreach ($rules as $rule) {
            $timeFrom = !empty($rule['time_from']) ? date('g:i A', strtotime($rule['time_from'])) : 'Anytime';
            $timeTo = !empty($rule['time_to']) ? date('g:i A', strtotime($rule['time_to'])) : 'Anytime';
            $waitMins = (int) ($rule['wait_minutes'] ?? 30);

            $isActive = false;
            if (!empty($rule['time_from']) && !empty($rule['time_to'])) {
                $from = $rule['time_from'];
                $to = $rule['time_to'];
                if ($to >= '23:59:00') {
                    $isActive = ($currentTime >= $from);
                } else {
                    $isActive = ($currentTime >= $from && $currentTime < $to);
                }
            }

            $scope = 'All Routes';
            if (!empty($rule['route_destination'])) {
                $scope = (!empty($rule['terminal_name']) ? $rule['terminal_name'] : 'Palompon') . ' → ' . $rule['route_destination'];
            }

            $formatted[] = [
                'id' => $rule['id'],
                'label' => $rule['label'] ?: 'Standard Schedule',
                'time_from' => $rule['time_from'],
                'time_to' => $rule['time_to'],
                'time_from_formatted' => $timeFrom,
                'time_to_formatted' => $timeTo,
                'time_range' => $timeFrom . ' – ' . $timeTo,
                'wait_minutes' => $waitMins,
                'interval_label' => 'Every ' . $this->formatIntervalMinutes($waitMins),
                'route_scope' => $scope,
                'terminal_name' => $rule['terminal_name'] ?? 'Palompon Central Terminal',
                'is_active_now' => $isActive,
            ];
        }

        return $formatted;
    }

    private function getRouteAverageDepartures(): array
    {
        $queueModel = new QueueModel();

        $departures = $queueModel
            ->select('terminals.name as origin, routes.destination, queue.departure_time')
            ->join('routes', 'routes.id = queue.route_id')
            ->join('terminals', 'terminals.id = routes.terminal_id')
            ->where('queue.status', 'departed')
            ->where('queue.departure_time IS NOT NULL', null, false)
            ->like('queue.departure_time', date('Y-m-d'), 'after')
            ->orderBy('terminals.name', 'ASC')
            ->orderBy('routes.destination', 'ASC')
            ->orderBy('queue.departure_time', 'ASC')
            ->findAll();

        $grouped = [];
        foreach ($departures as $departure) {
            $origin = (string) ($departure['origin'] ?? '');
            $destination = (string) ($departure['destination'] ?? '');
            $key = strtoupper($origin) . '|' . strtoupper($destination);

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'origin' => strtoupper($origin),
                    'destination' => strtoupper($destination),
                    'departure_times' => [],
                ];
            }

            $grouped[$key]['departure_times'][] = strtotime($departure['departure_time']);
        }

        $rows = [];
        foreach ($grouped as $group) {
            $times = array_values(array_filter($group['departure_times']));
            $departureCount = count($times);
            $hasInterval = $departureCount > 1;
            $intervalMinutes = null;
            $intervalLabel = '';

            if ($hasInterval) {
                $gaps = [];
                for ($i = 1; $i < $departureCount; $i++) {
                    $gapSeconds = $times[$i] - $times[$i - 1];
                    if ($gapSeconds > 0) {
                        $gaps[] = $gapSeconds;
                    }
                }

                if (!empty($gaps)) {
                    $intervalMinutes = (int) round((array_sum($gaps) / count($gaps)) / 60);
                    $intervalLabel = 'Every ' . $this->formatIntervalMinutes($intervalMinutes);
                } else {
                    $hasInterval = false;
                }
            }

            if (!$hasInterval && $departureCount > 0) {
                $intervalLabel = 'First departure at ' . date('h:i A', $times[0]);
            }

            $rows[] = [
                'origin' => $group['origin'],
                'destination' => $group['destination'],
                'departure_count' => $departureCount,
                'has_interval' => $hasInterval,
                'interval_minutes' => $intervalMinutes,
                'interval_label' => $intervalLabel,
            ];
        }

        return $rows;
    }

    private function formatIntervalMinutes(int $minutes): string
    {
        if ($minutes < 60) {
            return $minutes . ' min';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes === 0) {
            return $hours . 'h';
        }

        return $hours . 'h ' . $remainingMinutes . 'm';
    }
}
