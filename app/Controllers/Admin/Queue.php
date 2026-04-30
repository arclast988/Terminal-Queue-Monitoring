<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VehicleModel;
use App\Models\RouteModel;
use App\Models\QueueModel;
use App\Models\LogModel;

class Queue extends BaseController
{
    protected $queueModel;
    protected $vehicleModel;
    protected $routeModel;
    protected $logModel;

    public function __construct()
    {
        $this->queueModel = new QueueModel();
        $this->vehicleModel = new VehicleModel();
        $this->routeModel = new RouteModel();
        $this->logModel = new LogModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Admin Queue Management',
            'queue' => $this->queueModel->select('queue.*, vehicles.plate_number, routes.origin, routes.destination, vehicles.type as vehicle_type, vehicles.capacity')
                                  ->join('vehicles', 'vehicles.id = queue.vehicle_id')
                                  ->join('routes', 'routes.id = queue.route_id')
                                  ->whereIn('queue.status', ['waiting', 'boarding'])
                                  ->orderBy('queue.position', 'ASC')
                                  ->findAll(),
            'vehicles' => $this->vehicleModel->where('status', 'active')->findAll(),
            'routes' => $this->routeModel->findAll(),
        ];

        return view('admin/queue/index', $data);
    }

    public function add()
    {
        // Validate input
        $rules = [
            'vehicle_id'   => 'required|integer',
            'route_id'     => 'required|integer',
            'wait_minutes' => 'required|integer',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->with('error', 'Invalid input. Please fill all fields correctly.');
        }

        $vehicleId  = (int)$this->request->getPost('vehicle_id');
        $routeId    = (int)$this->request->getPost('route_id');
        $waitMinutes = (int)$this->request->getPost('wait_minutes');

        // Check if vehicle is already in queue
        $existing = $this->queueModel->where('vehicle_id', $vehicleId)
                                     ->whereIn('status', ['waiting', 'boarding'])
                                     ->first();
        if ($existing) {
            return redirect()->back()->with('warning', 'This vehicle is already in the queue.');
        }

        $vehicle = $this->vehicleModel->find($vehicleId);
        $platNumber = $vehicle ? $vehicle['plate_number'] : $vehicleId;

        $lastPosition = $this->queueModel->whereIn('status', ['waiting', 'boarding'])
                                         ->orderBy('position', 'DESC')
                                         ->first();
        $newPosition = ($lastPosition) ? $lastPosition['position'] + 1 : 1;

        $now = date('Y-m-d H:i:s');
        $estDep = date('Y-m-d H:i:s', strtotime("+$waitMinutes minutes"));

        $this->queueModel->insert([
            'vehicle_id' => $vehicleId,
            'route_id' => $routeId,
            'status' => 'waiting',
            'position' => $newPosition,
            'arrival_time' => $now,
            'estimated_departure' => $estDep
        ]);

        $route = $this->routeModel->find($routeId);
        $dest = $route ? $route['destination'] : $routeId;
        $this->logActivity('Add to queue', "Added $platNumber to queue for $dest.");

        $this->broadcastUpdate('queue_update', ['action' => 'add', 'role' => 'admin']);

        return redirect()->to('admin/queue')->with('success', 'Vehicle added to queue');
    }

    public function update($id, $status)
    {
        // Validate status against allowed values
        $validStatuses = ['waiting', 'boarding', 'departed', 'canceled'];
        if (!in_array($status, $validStatuses)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Invalid status']);
            }
            return redirect()->back()->with('error', 'Invalid status value.');
        }

        $queueItem = $this->queueModel->find($id);
        if (!$queueItem) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Item not found']);
            }
            return redirect()->back()->with('error', 'Item not found');
        }

        $updateData = ['status' => $status];
        if ($status == 'departed') {
            $updateData['departure_time'] = date('Y-m-d H:i:s');
            $updateData['position'] = 0; // Remove from active positions
        }

        // Use transaction to prevent race conditions during position reordering
        $db = \Config\Database::connect();
        $db->transStart();

        $this->queueModel->update($id, $updateData);

        // Reorder positions if departed or canceled
        if ($status == 'departed' || $status == 'canceled') {
            $remainingQueue = $this->queueModel->whereIn('status', ['waiting', 'boarding'])
                                               ->orderBy('position', 'ASC')
                                               ->findAll();
            $pos = 1;
            foreach ($remainingQueue as $item) {
                $this->queueModel->update($item['id'], ['position' => $pos++]);
            }
        }

        $db->transComplete();

        $this->logActivity('Queue Status Update', "Queue ID $id status changed to $status (Admin)");

        $this->broadcastUpdate('queue_update', ['action' => 'status_change', 'id' => $id, 'new_status' => $status, 'role' => 'admin']);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => 'Status updated']);
        }

        $ref = $this->request->getGet('ref');
        $redirectPath = ($ref == 'admin/dashboard') ? 'admin/dashboard' : 'admin/queue';
        return redirect()->to($redirectPath)->with('success', 'Status updated');
    }

    public function updatePassengers($id, $action)
    {
        // Sanitize action in case query string is included
        if (strpos($action, '?') !== false) {
            $parts = explode('?', $action);
            $action = $parts[0];
        }

        $queueItem = $this->queueModel->find($id);
        if (!$queueItem) {
            if ($this->request->isAJAX()) return $this->response->setJSON(['success' => false, 'message' => 'Item not found']);
            return redirect()->back()->with('error', 'Item not found');
        }

        $vehicle = $this->vehicleModel->find($queueItem['vehicle_id']);
        $currentCount = $queueItem['current_passengers'];

        $didUpdate = false;
        $newCount = $currentCount;
        if ($action == 'increment') {
            if ($currentCount < $vehicle['capacity']) {
                $this->queueModel->update($id, ['current_passengers' => $currentCount + 1]);
                $newCount = $currentCount + 1;
                $didUpdate = true;
            }
        } elseif ($action == 'decrement') {
            if ($currentCount > 0) {
                $this->queueModel->update($id, ['current_passengers' => $currentCount - 1]);
                $newCount = $currentCount - 1;
                $didUpdate = true;
            }
        } elseif ($action == 'max') {
            $this->queueModel->update($id, ['current_passengers' => $vehicle['capacity']]);
            $newCount = $vehicle['capacity'];
            $didUpdate = true;
        }

        if ($didUpdate) {
            $this->broadcastUpdate('queue_update', [
                'action' => 'passenger_change',
                'id' => $id,
                'new_count' => $newCount,
                'capacity' => (int)$vehicle['capacity'],
                'role' => 'admin'
            ]);
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'new_count' => $newCount,
                'capacity' => (int)$vehicle['capacity'],
                'is_full' => (int)$newCount >= (int)$vehicle['capacity']
            ]);
        }

        return redirect()->to('admin/queue');
    }

    /**
     * Set passengers to a specific count (used by debounce module).
     * Accepts POST JSON body: { "count": N }
     */
    public function setPassengers($id)
    {
        $queueItem = $this->queueModel->find($id);
        if (!$queueItem) {
            return $this->response->setJSON(['success' => false, 'message' => 'Item not found']);
        }

        $vehicle = $this->vehicleModel->find($queueItem['vehicle_id']);
        $input = $this->request->getJSON(true);
        $newCount = (int)($input['count'] ?? 0);

        // Clamp to valid range
        $newCount = max(0, min($newCount, (int)$vehicle['capacity']));

        $this->queueModel->update($id, ['current_passengers' => $newCount]);

        $this->broadcastUpdate('queue_update', [
            'action' => 'passenger_change',
            'id' => $id,
            'new_count' => $newCount,
            'capacity' => (int)$vehicle['capacity'],
            'role' => 'admin'
        ]);

        return $this->response->setJSON([
            'success' => true,
            'new_count' => $newCount,
            'capacity' => (int)$vehicle['capacity'],
            'is_full' => $newCount >= (int)$vehicle['capacity']
        ]);
    }
}

