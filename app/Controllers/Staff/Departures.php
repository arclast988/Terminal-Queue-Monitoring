<?php

namespace App\Controllers\Staff;

use App\Controllers\BaseController;
use App\Models\QueueModel;
use App\Models\UserRouteModel;

class Departures extends BaseController
{
    public function index()
    {
        $today = date('Y-m-d');
        $filters = $this->filters();
        $stats = $this->todayBuilder($today)
            ->select('COUNT(queue.id) AS departures, COALESCE(SUM(queue.current_passengers), 0) AS passengers')
            ->first();
        $destinations = $this->todayBuilder($today)->select('routes.destination')->distinct()
            ->where('routes.destination IS NOT NULL')->orderBy('routes.destination')->findAll();
        $vehicleTypes = $this->todayBuilder($today)->select('vehicles.type AS slug')->distinct()
            ->where('vehicles.type IS NOT NULL')->orderBy('vehicles.type')->findAll();
        $builder = $this->filteredBuilder($today, $filters);
        $departures = $builder->orderBy('queue.departure_time', 'DESC')->orderBy('queue.id', 'DESC')->paginate(20);

        $this->response->setHeader('Cache-Control', 'no-store');
        return view('staff/departures/index', [
            'title' => 'Today Departures', 'today' => $today,
            'departures' => $departures, 'pager' => $builder->pager,
            'stats' => $stats, 'destinations' => $destinations, 'vehicleTypes' => $vehicleTypes,
            'filters' => $filters,
        ]);
    }

    public function report()
    {
        // The service date is fixed by the server, never by report parameters.
        $today = date('Y-m-d');
        $filters = $this->filters();
        $results = $this->filteredBuilder($today, $filters)
            ->orderBy('queue.departure_time', 'DESC')->orderBy('queue.id', 'DESC')->findAll();
        $this->response->setHeader('Cache-Control', 'no-store');
        return view('admin/history/print_history', [
            'title' => "Today's Departure Report", 'report_heading' => "Today's Departure Report",
            'today_only_report' => true, 'results' => $results,
            'search' => $filters['q'], 'destination' => $filters['destination'],
            'vehicle_type' => $filters['vehicle_type'], 'from_date' => $today, 'to_date' => $today,
            'return_url' => base_url('staff/departures'),
        ]);
    }

    private function filters(): array
    {
        $filters = [];
        foreach (['q', 'destination', 'vehicle_type'] as $key) {
            $value = $this->request->getGet($key);
            $filters[$key] = is_string($value) ? mb_substr(trim($value), 0, 150) : '';
        }
        return $filters;
    }

    private function todayBuilder(string $today): QueueModel
    {
        $routeIds = (new UserRouteModel())->getRouteIdsForUser((int) session()->get('id'));
        return (new QueueModel())->withFullJoins()
            ->where('queue.status', 'departed')
            ->where('queue.departure_time >', history_departure_date_boundary($today))
            ->where('queue.departure_time <=', history_departure_date_boundary($today, true))
            ->whereIn('queue.route_id', $routeIds ?: [0]);
    }

    private function filteredBuilder(string $today, array $filters): QueueModel
    {
        $builder = $this->todayBuilder($today)->select("
            queue.*,
            COALESCE(NULLIF(queue.plate_number, ''), vehicles.plate_number) AS plate_number,
            COALESCE(NULLIF(queue.driver_name, ''), vehicles.driver_name) AS driver_name,
            COALESCE(NULLIF(queue.operator_name, ''), NULLIF(vehicles.operator_name, ''), vehicles.owner_name) AS operator_name,
            vehicles.owner_name, vehicles.type AS vehicle_type, routes.destination, terminals.name AS origin
        ");
        if ($filters['q'] !== '') {
            $builder->groupStart()
                ->like("COALESCE(NULLIF(queue.plate_number, ''), vehicles.plate_number)", $filters['q'])
                ->orLike("COALESCE(NULLIF(queue.driver_name, ''), vehicles.driver_name)", $filters['q'])
                ->orLike("COALESCE(NULLIF(queue.operator_name, ''), NULLIF(vehicles.operator_name, ''), vehicles.owner_name)", $filters['q'])
                ->orLike('routes.destination', $filters['q'])->orLike('terminals.name', $filters['q'])
                ->groupEnd();
        }
        if ($filters['destination'] !== '') $builder->where('routes.destination', $filters['destination']);
        if ($filters['vehicle_type'] !== '') $builder->where('vehicles.type', $filters['vehicle_type']);
        return $builder;
    }
}
