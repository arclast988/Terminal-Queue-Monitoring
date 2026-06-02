<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\QueueModel;

class History extends BaseController
{
    public function index()
    {
        $search = $this->request->getGet('q');
        $fromDate = $this->request->getGet('from_date');
        $toDate = $this->request->getGet('to_date');
        $destFilter = $this->request->getGet('destination');
        $typeFilter = $this->request->getGet('vehicle_type');

        // --- Stats (fresh query for each to avoid filter stacking) ---
        $queueModel1 = new QueueModel();
        $totalAll = $queueModel1->where('queue.status', 'departed')
                                 ->where('queue.departure_time IS NOT NULL')
                                 ->countAllResults();

        $queueModel2 = new QueueModel();
        $totalToday = $queueModel2->where('queue.status', 'departed')
                                   ->where('queue.departure_time IS NOT NULL')
                                   ->where('DATE(queue.departure_time)', date('Y-m-d'))
                                   ->countAllResults();

        $queueModel3 = new QueueModel();
        $totalMonth = $queueModel3->where('queue.status', 'departed')
                                   ->where('queue.departure_time IS NOT NULL')
                                   ->where('YEAR(queue.departure_time)', date('Y'))
                                   ->where('MONTH(queue.departure_time)', date('m'))
                                   ->countAllResults();

        $queueModel4 = new QueueModel();
        $totalYear = $queueModel4->where('queue.status', 'departed')
                                  ->where('queue.departure_time IS NOT NULL')
                                  ->where('YEAR(queue.departure_time)', date('Y'))
                                  ->countAllResults();

        // --- Options for Filter Modal ---
        $routeModel = new \App\Models\RouteModel();
        $vehicleModel = new \App\Models\VehicleModel();
        
        $destinations = $routeModel->select('destination')->distinct()->orderBy('destination', 'ASC')->findAll();
        $vehicleTypes = $vehicleModel->select('type')->distinct()->orderBy('type', 'ASC')->findAll();

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
            'vehicleTypes' => array_column($vehicleModel->select('type')->distinct()->orderBy('type', 'ASC')->findAll(), 'type'),
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
                              ->join('vehicles', 'vehicles.id = queue.vehicle_id')
                              ->join('routes', 'routes.id = queue.route_id')
                              ->join('terminals', 'terminals.id = routes.terminal_id')
                              ->where('queue.status', 'departed')
                              ->where('queue.departure_time IS NOT NULL');

        // Default to current year ONLY if no filters applied
        if (!$fromDate && !$toDate) {
            $builder->where('YEAR(queue.departure_time)', date('Y'));
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
            $builder->where('DATE(queue.departure_time) >=', $fromDate);
        }

        if ($toDate) {
            $builder->where('DATE(queue.departure_time) <=', $toDate);
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
