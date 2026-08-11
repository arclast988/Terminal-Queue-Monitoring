<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\QueueModel;
use App\Models\VehicleTypeModel;

class History extends BaseController
{
    public function index()
    {
        $search = $this->request->getGet('q');
        $fromDate = $this->request->getGet('from_date');
        $toDate = $this->request->getGet('to_date');
        $destFilter = $this->request->getGet('destination');
        $typeFilter = $this->request->getGet('vehicle_type');

        $todayStr = date('Y-m-d');
        $monthStr = date('Y-m');
        $yearStr  = date('Y');

        // --- Stats (single query with conditional aggregation) ---
        $db = \Config\Database::connect();
        $statsResult = $db->table('queue')
            ->select("
                COUNT(*) as total,
                SUM(CASE WHEN departure_time LIKE '{$todayStr}%' THEN 1 ELSE 0 END) as today,
                SUM(CASE WHEN departure_time LIKE '{$monthStr}%' THEN 1 ELSE 0 END) as month,
                SUM(CASE WHEN departure_time LIKE '{$yearStr}%' THEN 1 ELSE 0 END) as year
            ")
            ->where('status', 'departed')
            ->where('departure_time IS NOT NULL')
            ->get()
            ->getRow();

        $totalAll   = (int) ($statsResult->total ?? 0);
        $totalToday = (int) ($statsResult->today ?? 0);
        $totalMonth = (int) ($statsResult->month ?? 0);
        $totalYear  = (int) ($statsResult->year ?? 0);

        // --- Options for Filter Modal ---
        $routeModel = new \App\Models\RouteModel();
        $vehicleTypeModel = new VehicleTypeModel();
        
        $destinations = $routeModel->select('destination')->distinct()->orderBy('destination', 'ASC')->findAll();
        $vehicleTypes = $vehicleTypeModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll();

        // --- Departure list (paginated, searchable) ---
        $queueModel = new QueueModel();
        $builder = $this->_getFilteredBuilder($search, $fromDate, $toDate, $destFilter, $typeFilter);
        $departures = $builder->orderBy('queue.departure_time', 'DESC')->paginate(20);

        $data = [
            'title'        => 'Departure History',
            'stats'        => [
                'total'   => $totalAll,
                'today'   => $totalToday,
                'month'   => $totalMonth,
                'year'    => $totalYear,
            ],
            'departures'   => $departures,
            'destinations' => $routeModel->select('destination, vehicle_type')->distinct()->orderBy('destination', 'ASC')->findAll(),
            'vehicleTypes' => $vehicleTypes,
            'pager'        => $queueModel->pager,
            'search'       => $search,
            'from_date'    => $fromDate,
            'to_date'      => $toDate,
            'destination'  => $destFilter,
            'vehicle_type' => $typeFilter,
        ];

        return view('admin/history/index', $data);
    }

    public function print()
    {
        $search = $this->request->getGet('q');
        $fromDate = $this->request->getGet('from_date');
        $toDate = $this->request->getGet('to_date');
        $destFilter = $this->request->getGet('destination');
        $typeFilter = $this->request->getGet('vehicle_type');

        $builder = $this->_getFilteredBuilder($search, $fromDate, $toDate, $destFilter, $typeFilter);
        $results = $builder->orderBy('queue.departure_time', 'DESC')->findAll();

        $data = [
            'title'        => 'Departure History Report',
            'results'      => $results,
            'search'       => $search,
            'from_date'    => $fromDate,
            'to_date'      => $toDate,
            'destination'  => $destFilter,
            'vehicle_type' => $typeFilter,
            'is_print'     => true
        ];

        return view('admin/history/print_history', $data);
    }

    private function _getFilteredBuilder($search, $fromDate, $toDate, $destination, $vehicleType)
    {
        $queueModel = new QueueModel();
        $builder = $queueModel->select('queue.*, vehicles.plate_number, vehicles.driver_name, vehicles.owner_name, vehicles.type as vehicle_type, routes.destination, terminals.name as origin, queue.departure_time, queue.current_passengers')
                              ->withFullJoins()
                              ->where('queue.status', 'departed')
                              ->where('queue.departure_time IS NOT NULL');

        // Default to current year ONLY if no filters applied
        if (!$fromDate && !$toDate) {
            $builder->like('queue.departure_time', date('Y'), 'after');
        }

        if ($search) {
            $builder->groupStart()
                    ->like('vehicles.plate_number', $search)
                    ->orLike('vehicles.driver_name', $search)
                    ->orLike('routes.destination', $search)
                    ->orLike('terminals.name', $search)
                    ->groupEnd();
        }

        if ($fromDate) {
            $builder->where('queue.departure_time >=', $fromDate . ' 00:00:00');
        }

        if ($toDate) {
            $builder->where('queue.departure_time <=', $toDate . ' 23:59:59');
        }

        if ($destination) {
            $builder->where('routes.destination', $destination);
        }

        if ($vehicleType) {
            $builder->where('vehicles.type', $vehicleType);
        }

        return $builder;
    }

    public function delete($id)
    {
        $queueModel = new QueueModel();
        $departure = $queueModel->find($id);

        if (!$departure || $departure['status'] !== 'departed') {
            return redirect()->to('/admin/history')->with('error', 'Departure record not found.');
        }

        // Fetch vehicle details for activity log
        $vehicleModel = new \App\Models\VehicleModel();
        $vehicle = $vehicleModel->find($departure['vehicle_id']);
        $plateNumber = $vehicle ? $vehicle['plate_number'] : 'Unknown Vehicle';

        $queueModel->delete($id);

        $this->logActivity('Delete Departure Record', "Deleted departure record for vehicle $plateNumber.");

        return redirect()->to('/admin/history')->with('success', 'Departure record deleted successfully.');
    }

    public function deleteAll()
    {
        $queueModel = new QueueModel();
        
        // Find all departed queue records
        $departedCount = $queueModel->where('status', 'departed')->countAllResults();

        if ($departedCount === 0) {
            return redirect()->to('/admin/history')->with('error', 'No departure records to delete.');
        }

        $queueModel->where('status', 'departed')->delete();

        $this->logActivity('Delete All Departure Records', "Deleted all departed queue records ($departedCount records).");

        return redirect()->to('/admin/history')->with('success', 'All departure records deleted successfully.');
    }
}
