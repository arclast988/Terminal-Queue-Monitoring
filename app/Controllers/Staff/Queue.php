<?php

namespace App\Controllers\Staff;

use App\Controllers\BaseController;
use App\Models\QueueModel;
use App\Models\VehicleModel;
use App\Models\RouteModel;
use App\Models\DepartureRuleModel;
use App\Models\UserRouteModel;

class Queue extends BaseController
{
    protected $queueModel;
    protected $vehicleModel;
    protected $routeModel;

    public function __construct()
    {
        $this->queueModel = new QueueModel();
        $this->vehicleModel = new VehicleModel();
        $this->routeModel = new RouteModel();
    }

    /**
     * Get the assigned route IDs for the current user.
     * Returns null for admin (unrestricted), or array of route_ids for staff.
     */
    private function getAssignedRouteIds(): ?array
    {
        if (session()->get('role') === 'admin') {
            return null; // Admin sees everything
        }

        $userRouteModel = new UserRouteModel();
        return $userRouteModel->getRouteIdsForUser((int) session()->get('id'));
    }

    /**
     * Check if the current user has access to a specific route.
     */
    private function hasRouteAccess(int $routeId): bool
    {
        $assignedIds = $this->getAssignedRouteIds();
        if ($assignedIds === null) {
            return true; // Admin
        }
        return in_array($routeId, $assignedIds);
    }

    /**
     * Check if the current user has access to a specific queue entry.
     */
    private function hasQueueAccess(int $queueId): bool
    {
        $item = $this->queueModel->find($queueId);
        if (!$item) {
            return false;
        }
        return $this->hasRouteAccess((int) $item['route_id']);
    }

    public function index()
    {
        $assignedRouteIds = $this->getAssignedRouteIds();

        // Build queue query
        $builder = $this->queueModel->select('queue.*, vehicles.plate_number, vehicles.type as vehicle_type, terminals.name as origin, routes.destination, vehicles.capacity')
            ->join('vehicles', 'vehicles.id = queue.vehicle_id')
            ->join('routes', 'routes.id = queue.route_id')
            ->join('terminals', 'terminals.id = routes.terminal_id')
            ->whereIn('queue.status', ['waiting', 'boarding']);

        // Filter by assigned routes for staff
        if ($assignedRouteIds !== null) {
            if (!empty($assignedRouteIds)) {
                $builder->whereIn('queue.route_id', $assignedRouteIds);
            } else {
                $builder->where('queue.route_id', 0);
            }
        }

        $queue = $builder->orderBy('queue.position', 'ASC')->findAll();

        // Filter vehicles: only show vehicles assigned to dispatcher's routes
        $freshVehicleModel = new VehicleModel();
        if ($assignedRouteIds !== null) {
            if (!empty($assignedRouteIds)) {
                $vehicles = $freshVehicleModel
                    ->select('vehicles.*, terminals.name as route_origin, routes.destination as route_destination')
                    ->join('routes', 'routes.id = vehicles.route_id', 'left')
                    ->join('terminals', 'terminals.id = routes.terminal_id', 'left')
                    ->where('vehicles.status', 'active')
                    ->where('vehicles.route_id IS NOT NULL')
                    ->whereIn('vehicles.route_id', $assignedRouteIds)
                    ->findAll();
            } else {
                $vehicles = [];
            }
        } else {
            $vehicles = $freshVehicleModel
                ->select('vehicles.*, terminals.name as route_origin, routes.destination as route_destination')
                ->join('routes', 'routes.id = vehicles.route_id', 'left')
                ->join('terminals', 'terminals.id = routes.terminal_id', 'left')
                ->where('vehicles.status', 'active')
                ->where('vehicles.route_id IS NOT NULL')
                ->findAll();
        }

        $data = [
            'title' => 'Queue Management',
            'queue' => $queue,
            'vehicles' => $vehicles,
            'noRoutesAssigned' => ($assignedRouteIds !== null && empty($assignedRouteIds)),
        ];

        return view('staff/queue/index', $data);
    }

    public function add()
    {
        $rules = [
            'vehicle_id' => 'required|integer',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('error', 'Invalid Input');
        }

        $vehicleId = $this->request->getPost('vehicle_id');

        // Get vehicle and its assigned route
        $vehicle = $this->vehicleModel->find($vehicleId);
        if (!$vehicle) {
            return redirect()->back()->with('error', 'Vehicle not found.');
        }

        $routeId = $vehicle['route_id'];
        if (empty($routeId)) {
            return redirect()->back()->with('error', 'This vehicle has no assigned route. Please contact the administrator.');
        }

        $route = $this->routeModel->find($routeId);
        if (!$route) {
            return redirect()->back()->with('error', 'The route assigned to this vehicle no longer exists.');
        }

        // Server-side route authorization check
        if (!$this->hasRouteAccess((int) $routeId)) {
            $this->logActivity('Unauthorized queue action attempt', 'Dispatcher "' . session()->get('username') . '" tried to add vehicle to unassigned route ID ' . $routeId . '.');
            return redirect()->back()->with('error', "You don't have access to this route.");
        }

        // Check if vehicle is already in queue
        $existingQueue = $this->queueModel->where('vehicle_id', $vehicleId)
            ->whereIn('status', ['waiting', 'boarding'])
            ->first();

        if ($existingQueue) {
            return redirect()->back()->with('warning', $vehicle['plate_number'] . ' is already in the queue!');
        }
        
        // Check for recent departure (within 1 minute)
        $oneMinuteAgo = date('Y-m-d H:i:s', strtotime('-1 minute'));
        $recentDeparture = $this->queueModel->where('vehicle_id', $vehicleId)
            ->where('status', 'departed')
            ->where('departure_time >=', $oneMinuteAgo)
            ->orderBy('departure_time', 'DESC')
            ->first();

        if ($recentDeparture) {
            $departTime = strtotime($recentDeparture['departure_time']);
            $secondsAgo = time() - $departTime;
            return redirect()->back()->with('error', 'This vehicle departed only ' . $secondsAgo . ' seconds ago. Please wait 1 minute before adding it back.');
        }

        // Calculate next position
        $lastPosition = $this->queueModel->selectMax('position')->first();
        $nextPosition = ($lastPosition['position'] ?? 0) + 1;

        // Auto-apply departure rule based on current time and terminal
        $departureRuleModel = new DepartureRuleModel();
        $currentTime = date('H:i:s'); // Server local time (Asia/Manila)
        $terminalId  = (int) ($route['terminal_id'] ?? 1);
        $matchedRule = $departureRuleModel->getRuleForTime($currentTime, $terminalId, (int) $routeId);
        $waitMinutes = (int) $matchedRule['wait_minutes'];
        $ruleLabel = $matchedRule['label'] ?? 'Default';

        // Log warning if no rule matched (using fallback)
        if (empty($matchedRule['time_from'])) {
            $this->logActivity('No departure rule matched', 'No departure rule covers ' . date('g:i A') . '. Using default of 30 minutes.');
        }

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

        $this->logActivity('Add to queue', 'Added ' . ($vehicle['plate_number'] ?? 'vehicle') . ' to queue for ' . ($route['destination'] ?? 'route') . '. Rule: ' . $ruleLabel . ' (' . $waitMinutes . ' min).');

        // Fetch complete queue item for broadcast
        $queueItem = $this->queueModel->select('queue.*, vehicles.plate_number, vehicles.type as vehicle_type, vehicles.capacity, terminals.name as origin, routes.destination')
            ->join('vehicles', 'vehicles.id = queue.vehicle_id')
            ->join('routes', 'routes.id = queue.route_id')
            ->join('terminals', 'terminals.id = routes.terminal_id')
            ->where('queue.id', $queueId)
            ->first();

        $this->broadcastUpdate('queue_update', [
            'action' => 'add',
            'queue_item' => $queueItem
        ]);

        $estTime = date('h:i A', strtotime($estimatedDeparture));
        return redirect()->to('/staff/queue')->with('success', $vehicle['plate_number'] . ' added to queue. Est. departure: ' . $estTime . ' (' . $ruleLabel . ', ' . $waitMinutes . ' min wait).');
    }

    public function updateStatus($id, $status)
    {
        // Server-side route authorization check
        if (!$this->hasQueueAccess((int) $id)) {
            $this->logActivity('Unauthorized queue action attempt', 'Dispatcher "' . session()->get('username') . '" tried to update status of queue #' . $id . ' on an unassigned route.');
            return redirect()->back()->with('error', "You don't have access to this route.");
        }

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


        $item = $this->queueModel->select('vehicles.plate_number, terminals.name as origin, routes.destination')
            ->join('vehicles', 'vehicles.id = queue.vehicle_id')
            ->join('routes', 'routes.id = queue.route_id')
            ->join('terminals', 'terminals.id = routes.terminal_id')
            ->where('queue.id', $id)
            ->first();
        $label = $item ? $item['plate_number'] . ' (' . $item['destination'] . ')' : 'queue #' . $id;
        $actionLabel = $status === 'boarding' ? 'Start Boarding' : ($status === 'departed' ? 'Depart Vehicle' : ($status === 'canceled' ? 'Cancel Trip' : $status));
        $this->logActivity($actionLabel, $actionLabel . ' for ' . $label . '.');

        // Fetch updated queue item for broadcast
        $updatedItem = $this->queueModel->select('queue.*, vehicles.plate_number, vehicles.type as vehicle_type, vehicles.capacity, terminals.name as origin, routes.destination')
            ->join('vehicles', 'vehicles.id = queue.vehicle_id')
            ->join('routes', 'routes.id = queue.route_id')
            ->join('terminals', 'terminals.id = routes.terminal_id')
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
        // Server-side route authorization check
        if (!$this->hasQueueAccess((int) $id)) {
            $this->logActivity('Unauthorized queue action attempt', 'Dispatcher "' . session()->get('username') . '" tried to update passengers of queue #' . $id . ' on an unassigned route.');
            return redirect()->back()->with('error', "You don't have access to this route.");
        }

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
        $redirectUrl = $ref === 'dashboard' ? 'staff/dashboard' : 'staff/queue';
        return redirect()->to(base_url($redirectUrl));
    }

    /**
     * Set passengers to a specific count (used by debounce module).
     * Accepts POST JSON body: { "count": N }
     */
    public function setPassengers($id)
    {
        // Server-side route authorization check
        if (!$this->hasQueueAccess((int) $id)) {
            return $this->response->setJSON(['success' => false, 'message' => "You don't have access to this route."]);
        }

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
