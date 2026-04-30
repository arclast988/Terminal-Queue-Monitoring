<?php

namespace App\Controllers\Staff;

use App\Controllers\BaseController;
use App\Models\QueueModel;
use App\Models\VehicleModel;
use App\Models\RouteModel;
use App\Models\TripStatusHistoryModel;

class Queue extends BaseController
{
    protected $queueModel;
    protected $vehicleModel;
    protected $routeModel;
    protected $historyModel;

    public function __construct()
    {
        $this->queueModel = new QueueModel();
        $this->vehicleModel = new VehicleModel();
        $this->routeModel = new RouteModel();
        $this->historyModel = new TripStatusHistoryModel();
    }

    public function index()
    {
        // Get current queue with details
        $queue = $this->queueModel->select('queue.*, vehicles.plate_number, vehicles.type as vehicle_type, routes.origin, routes.destination, vehicles.capacity')
            ->join('vehicles', 'vehicles.id = queue.vehicle_id')
            ->join('routes', 'routes.id = queue.route_id')
            ->whereIn('queue.status', ['waiting', 'boarding'])
            ->orderBy('queue.position', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Queue Management',
            'queue' => $queue,
            'vehicles' => $this->vehicleModel->where('status', 'active')->findAll(),
            'routes' => $this->routeModel->findAll()
        ];

        return view('staff/queue/index', $data);
    }

    public function add()
    {
        $rules = [
            'vehicle_id' => 'required|integer',
            'route_id' => 'required|integer',
            'wait_minutes' => 'required|integer'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('error', 'Invalid Input');
        }

        $vehicleId = $this->request->getPost('vehicle_id');
        $routeId = $this->request->getPost('route_id');

        // Check if vehicle is already in queue
        $existingQueue = $this->queueModel->where('vehicle_id', $vehicleId)
            ->whereIn('status', ['waiting', 'boarding'])
            ->first();

        if ($existingQueue) {
            $vehicle = $this->vehicleModel->find($vehicleId);
            $platNumber = $vehicle['plate_number'] ?? 'Vehicle';
            return redirect()->back()->with('warning', $platNumber . ' is already in the queue!');
        }

        // Calculate next position
        $lastPosition = $this->queueModel->selectMax('position')->first();
        $nextPosition = ($lastPosition['position'] ?? 0) + 1;

        $waitMinutes = (int) $this->request->getPost('wait_minutes');
        $arrivalTime = date('Y-m-d H:i:s');
        $estimatedDeparture = date('Y-m-d H:i:s', strtotime("+$waitMinutes minutes"));

        $queueId = $this->queueModel->insert([
            'vehicle_id' => $vehicleId,
            'route_id' => $routeId,
            'status' => 'waiting',
            'position' => $nextPosition,
            'arrival_time' => $arrivalTime,
            'estimated_departure' => $estimatedDeparture
        ]);

        // Log history
        $this->historyModel->insert([
            'queue_id' => $queueId,
            'status' => 'arrived/waiting',
            'timestamp' => date('Y-m-d H:i:s'),
            'updated_by_user_id' => session()->get('id')
        ]);

        $vehicle = $this->vehicleModel->find($vehicleId);
        $route = $this->routeModel->find($routeId);
        $this->logActivity('Add to queue', 'Added ' . ($vehicle['plate_number'] ?? 'vehicle') . ' to queue for ' . ($route['destination'] ?? 'route') . '.');

        // Fetch complete queue item for broadcast
        $queueItem = $this->queueModel->select('queue.*, vehicles.plate_number, vehicles.type as vehicle_type, vehicles.capacity, routes.origin, routes.destination')
            ->join('vehicles', 'vehicles.id = queue.vehicle_id')
            ->join('routes', 'routes.id = queue.route_id')
            ->where('queue.id', $queueId)
            ->first();

        $this->broadcastUpdate('queue_update', [
            'action' => 'add',
            'queue_item' => $queueItem
        ]);

        return redirect()->to('/staff/queue')->with('success', 'Vehicle added to queue.');
    }

    public function updateStatus($id, $status)
    {
        // Sanitize status in case query string is included
        if (strpos($status, '?') !== false) {
            $parts = explode('?', $status);
            $status = $parts[0];
        }

        $validStatuses = ['waiting', 'boarding', 'departed', 'canceled'];

        if (!in_array($status, $validStatuses)) {
            return redirect()->back()->with('error', 'Invalid status.');
        }

        $data = ['status' => $status];
        if ($status == 'departed') {
            $data['departure_time'] = date('Y-m-d H:i:s');
            $data['position'] = 0; // Remove from active positions
        }

        // Use transaction to prevent race conditions during position reordering
        $db = \Config\Database::connect();
        $db->transStart();

        $this->queueModel->update($id, $data);

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

        // Log history
        $this->historyModel->insert([
            'queue_id' => $id,
            'status' => $status,
            'timestamp' => date('Y-m-d H:i:s'),
            'updated_by_user_id' => session()->get('id')
        ]);

        $item = $this->queueModel->select('vehicles.plate_number, routes.origin, routes.destination')
            ->join('vehicles', 'vehicles.id = queue.vehicle_id')
            ->join('routes', 'routes.id = queue.route_id')
            ->where('queue.id', $id)
            ->first();
        $label = $item ? $item['plate_number'] . ' (' . $item['destination'] . ')' : 'queue #' . $id;
        $actionLabel = $status === 'boarding' ? 'Start Boarding' : ($status === 'departed' ? 'Depart Vehicle' : ($status === 'canceled' ? 'Cancel Trip' : $status));
        $this->logActivity($actionLabel, $actionLabel . ' for ' . $label . '.');

        // Fetch updated queue item for broadcast
        $updatedItem = $this->queueModel->select('queue.*, vehicles.plate_number, vehicles.type as vehicle_type, vehicles.capacity, routes.origin, routes.destination')
            ->join('vehicles', 'vehicles.id = queue.vehicle_id')
            ->join('routes', 'routes.id = queue.route_id')
            ->where('queue.id', $id)
            ->first();

        $this->broadcastUpdate('queue_update', [
            'action' => 'status_change',
            'id' => $id,
            'status' => $status,
            'queue_item' => $updatedItem
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => 'Status updated']);
        }

        $ref = $this->request->getVar('ref');
        $redirectUrl = $ref === 'dashboard' ? 'staff/dashboard' : 'staff/queue';
        return redirect()->to(base_url($redirectUrl))->with('success', 'Status updated.');
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
            // Set to maximum capacity
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
                'role' => 'staff'
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

        $ref = $this->request->getVar('ref');
        $redirectUrl = $ref === 'dashboard' ? 'admin/dashboard' : 'staff/queue';
        return redirect()->to(base_url($redirectUrl));
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
            'role' => 'staff'
        ]);

        return $this->response->setJSON([
            'success' => true,
            'new_count' => $newCount,
            'capacity' => (int)$vehicle['capacity'],
            'is_full' => $newCount >= (int)$vehicle['capacity']
        ]);
    }
}

