<?php

namespace App\Controllers\Staff;

use App\Controllers\BaseController;
use App\Models\QueueModel;
use App\Models\VehicleModel;
use App\Models\RouteModel;
use App\Models\DepartureRuleModel;
use App\Models\TerminalModel;
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
        if (in_array(session()->get('role'), ['super_admin', 'admin'], true)) {
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

    /**
     * Serialize queue-order mutations on PostgreSQL. This uses the same global
     * advisory-lock key as reorderByDeparture(), so add/cancel/restore/manual
     * reorder operations cannot interleave and create duplicate positions.
     */
    private function acquireQueueOrderingLock($db): void
    {
        $driver = strtolower($db->DBDriver ?? '');
        if (!str_contains($driver, 'postgre')) {
            return;
        }

        $lockKey = crc32('queue_recalc_0');
        if ($lockKey > 2147483647) {
            $lockKey -= 4294967296;
        }
        $db->query('SELECT pg_advisory_xact_lock(?)', [(int) $lockKey]);
    }

    /**
     * Return a user-facing error when the terminal cannot accept another
     * active (waiting or boarding) vehicle, otherwise return null.
     */
    private function terminalCapacityError($db, int $terminalId): ?string
    {
        $terminal = (new TerminalModel())->find($terminalId);
        if (!$terminal) {
            return 'The terminal assigned to this route no longer exists.';
        }

        $capacity = (int) ($terminal['capacity'] ?? 0);
        if ($capacity < 1) {
            return 'The vehicle limit for ' . ($terminal['name'] ?? 'this terminal') . ' is not configured.';
        }

        $activeCount = (int) $db->table('queue')
            ->join('routes', 'routes.id = queue.route_id')
            ->where('routes.terminal_id', $terminalId)
            ->whereIn('queue.status', ['waiting', 'boarding'])
            ->countAllResults();

        if ($activeCount >= $capacity) {
            return ($terminal['name'] ?? 'This terminal') . ' has reached its limit of '
                . $capacity . ' active vehicle' . ($capacity === 1 ? '' : 's')
                . '. Depart or cancel a vehicle before adding another.';
        }

        return null;
    }

    public function index()
    {
        $assignedRouteIds = $this->getAssignedRouteIds();

        // Build queue query
        $builder = $this->queueModel->select('
            queue.*, 
            COALESCE(NULLIF(queue.plate_number, \'\'), vehicles.plate_number) as plate_number,
            COALESCE(NULLIF(queue.driver_name, \'\'), vehicles.driver_name) as driver_name,
            COALESCE(NULLIF(queue.operator_name, \'\'), NULLIF(vehicles.operator_name, \'\'), vehicles.owner_name) as operator_name,
            vehicles.owner_name, 
            vehicles.type as vehicle_type, 
            vehicles.photo as vehicle_photo, 
            terminals.name as origin, 
            routes.destination,
            routes.terminal_id,
            vehicles.capacity
        ')
            ->withFullJoins()
            ->whereIn('queue.status', ['waiting', 'boarding']);

        // Filter by assigned routes for staff
        if ($assignedRouteIds !== null) {
            if (!empty($assignedRouteIds)) {
                $builder->whereIn('queue.route_id', $assignedRouteIds);
            } else {
                $builder->where('queue.route_id', 0);
            }
        }

        $queue = $builder
            ->orderBy('routes.destination', 'ASC')
            ->orderBy("CASE WHEN queue.status = 'boarding' THEN 0 ELSE 1 END", 'ASC')
            ->orderBy('queue.position', 'ASC')
            ->findAll();

        // Get list of vehicle_ids that are currently active in queue (waiting or boarding)
        $activeQueuedVehicleIds = $this->queueModel
            ->whereIn('status', ['waiting', 'boarding'])
            ->findColumn('vehicle_id') ?: [];

        // Filter vehicles: only show vehicles assigned to dispatcher's routes AND not currently queued
        $freshVehicleModel = new VehicleModel();
        $vehicleBuilder = $freshVehicleModel
            ->select('vehicles.*, terminals.name as route_origin, routes.destination as route_destination')
            ->join('routes', 'routes.id = vehicles.default_route_id', 'left')
            ->join('terminals', 'terminals.id = routes.terminal_id', 'left')
            ->where('vehicles.status', 'active')
            ->where('vehicles.default_route_id IS NOT NULL');

        if (!empty($activeQueuedVehicleIds)) {
            $vehicleBuilder->whereNotIn('vehicles.id', $activeQueuedVehicleIds);
        }

        if ($assignedRouteIds !== null) {
            if (!empty($assignedRouteIds)) {
                $vehicles = $vehicleBuilder->whereIn('vehicles.default_route_id', $assignedRouteIds)->findAll();
            } else {
                $vehicles = [];
            }
        } else {
            $vehicles = $vehicleBuilder->findAll();
        }

        // Fetch latest departed record per available vehicle (single grouped
        // query) to mark departed status & time without fetching full history.
        if (!empty($vehicles)) {
            $availableVehicleIds = array_column($vehicles, 'id');
            $departedRecords = $this->queueModel
                ->select('vehicle_id, MAX(departure_time) as departure_time')
                ->whereIn('vehicle_id', $availableVehicleIds)
                ->where('status', 'departed')
                ->groupBy('vehicle_id')
                ->findAll();

            $departedMap = [];
            foreach ($departedRecords as $dep) {
                $vId = (int) $dep['vehicle_id'];
                if (!isset($departedMap[$vId])) {
                    $departedMap[$vId] = $dep['departure_time'];
                }
            }

            $currentTime = time();
            $todayDate = date('Y-m-d');
            $filteredVehicles = [];

            foreach ($vehicles as $v) {
                $vId = (int) $v['id'];
                if (isset($departedMap[$vId]) && !empty($departedMap[$vId])) {
                    $depTimestamp = strtotime($departedMap[$vId]);
                    $depDate = date('Y-m-d', $depTimestamp);
                    $elapsedMinutes = ($currentTime - $depTimestamp) / 60;

                    // If departed less than cooldown period ago, hide completely
                    $cooldownMin = vehicle_cooldown_minutes();
                    if ($cooldownMin > 0 && $elapsedMinutes < $cooldownMin) {
                        continue;
                    }

                    // Only show departed badge if the departure was today;
                    // previous-day departures are treated as READY
                    if ($depDate === $todayDate) {
                        $v['is_departed'] = true;
                        $v['departed_time'] = date('g:i A', $depTimestamp);
                        $v['departed_timestamp'] = $depTimestamp;
                    } else {
                        $v['is_departed'] = false;
                        $v['departed_time'] = null;
                        $v['departed_timestamp'] = 0;
                    }
                } else {
                    $v['is_departed'] = false;
                    $v['departed_time'] = null;
                    $v['departed_timestamp'] = 0;
                }

                $filteredVehicles[] = $v;
            }

            // Sort:
            // 1. Group by Destination Alphabetically (e.g. Bato, Maasin, Ormoc)
            // 2. READY vehicles first, DEPARTED vehicles at the bottom of the list (FIFO trip rotation)
            // 3. For same destination & status, sort by Vehicle Type Alphabetically (e.g. Bus, Car, Jeepney, Minibus, Taxi, Tricycle, Van)
            // 4. If DEPARTED of the same type, oldest departure first (FIFO trip rotation)
            // 5. If READY of the same type, sort by plate number alphabetically, then database ID
            usort($filteredVehicles, function($a, $b) {
                // 1. Group by Destination Alphabetically
                $destCmp = strcmp((string)($a['route_destination'] ?? ''), (string)($b['route_destination'] ?? ''));
                if ($destCmp !== 0) {
                    return $destCmp;
                }

                // 2. READY vehicles first, DEPARTED vehicles at bottom
                if ($a['is_departed'] !== $b['is_departed']) {
                    return $a['is_departed'] ? 1 : -1; // ready (false) comes before departed (true)
                }

                // 3. Vehicle Type Alphabetically (e.g. Taxi before Van)
                $typeA = strtolower(vehicle_type_label($a['type'] ?? ''));
                $typeB = strtolower(vehicle_type_label($b['type'] ?? ''));
                $typeCmp = strcmp($typeA, $typeB);
                if ($typeCmp !== 0) {
                    return $typeCmp;
                }

                // 4. If both DEPARTED of the same type, oldest departure first
                if (!empty($a['is_departed'])) {
                    $timeCmp = ($a['departed_timestamp'] ?? 0) <=> ($b['departed_timestamp'] ?? 0);
                    if ($timeCmp !== 0) {
                        return $timeCmp;
                    }
                }

                // 5. If both READY of the same type, sort by plate number alphabetically, then ID
                $plateCmp = strcmp((string)($a['plate_number'] ?? ''), (string)($b['plate_number'] ?? ''));
                if ($plateCmp !== 0) {
                    return $plateCmp;
                }

                return ((int) $a['id']) <=> ((int) $b['id']);
            });

            $vehicles = $filteredVehicles;
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
        $vehicleIds = $this->request->getPost('vehicle_ids');
        if (empty($vehicleIds)) {
            $singleId = $this->request->getPost('vehicle_id');
            if (!empty($singleId)) {
                $vehicleIds = [$singleId];
            }
        }

        if (empty($vehicleIds)) {
            return redirect()->back()->with('error', 'Please select at least one vehicle.');
        }

        if (!is_array($vehicleIds)) {
            $vehicleIds = [$vehicleIds];
        }
        $vehicleIds = array_values(array_unique(array_map('intval', $vehicleIds)));

        $addedCount = 0;
        $addedPlates = [];
        $errors = [];
        $warnings = [];

        $cooldownMin = vehicle_cooldown_minutes();
        $cooldownCutoff = date('Y-m-d H:i:s', strtotime("-{$cooldownMin} minutes"));
        $departureRuleModel = new DepartureRuleModel();
        $currentTime = date('H:i:s');
        $assignedRouteIds = $this->getAssignedRouteIds();
        $terminalModel = new TerminalModel();
        $routeCache = [];
        $terminalStates = [];
        $destinationPositions = [];
        $ruleCache = [];

        $db = \Config\Database::connect();
        $db->transStart();
        $this->acquireQueueOrderingLock($db);

        $baseArrivalTimestamp = time();
        $selectionIndex = 0;

        foreach ($vehicleIds as $vehicleId) {
            $vehicleId = (int) $vehicleId;
            if ($vehicleId <= 0) continue;

            // Get vehicle and its assigned route
            $vehicle = $this->vehicleModel->find($vehicleId);
            if (!$vehicle) {
                $errors[] = 'Vehicle not found.';
                continue;
            }

            $routeId = $vehicle['default_route_id'] ?? $vehicle['route_id'] ?? null;
            if (empty($routeId)) {
                $errors[] = 'Vehicle ' . $vehicle['plate_number'] . ' has no assigned route.';
                continue;
            }

            if (!array_key_exists($routeId, $routeCache)) {
                $routeCache[$routeId] = $this->routeModel->find($routeId);
            }
            $route = $routeCache[$routeId];
            if (!$route) {
                $errors[] = 'The route assigned to vehicle ' . $vehicle['plate_number'] . ' no longer exists.';
                continue;
            }

            // Server-side route authorization check
            if ($assignedRouteIds !== null && !in_array((int) $routeId, $assignedRouteIds)) {
                $this->logActivity('Unauthorized queue action attempt', 'Dispatcher "' . session()->get('username') . '" tried to add vehicle to unassigned route ID ' . $routeId . '.');
                $errors[] = "You don't have access to the route for " . $vehicle['plate_number'] . '.';
                continue;
            }

            // Check if vehicle is already in queue
            $existingQueue = $this->queueModel->where('vehicle_id', $vehicleId)
                ->whereIn('status', ['waiting', 'boarding'])
                ->first();

            if ($existingQueue) {
                $warnings[] = $vehicle['plate_number'] . ' is already in the queue!';
                continue;
            }

            // Enforce the configured departure cooldown shown in the vehicle list.
            if ($cooldownMin > 0) {
                $recentDeparture = $this->queueModel->where('vehicle_id', $vehicleId)
                    ->where('status', 'departed')
                    ->where('departure_time >=', $cooldownCutoff)
                    ->orderBy('departure_time', 'DESC')
                    ->first();

                if ($recentDeparture) {
                    $departTime = strtotime($recentDeparture['departure_time']);
                    $secondsAgo = time() - $departTime;
                    $remainingSeconds = max(0, ($cooldownMin * 60) - $secondsAgo);
                    $remainingMinutes = (int) ceil($remainingSeconds / 60);
                    $errors[] = $vehicle['plate_number'] . ' departed recently. Please wait about ' . $remainingMinutes . ' more minute(s) before adding it back.';
                    continue;
                }
            }

            $terminalId = (int) ($route['terminal_id'] ?? 0);
            if (!array_key_exists($terminalId, $terminalStates)) {
                $terminal = $terminalModel->find($terminalId);
                if (!$terminal) {
                    $terminalStates[$terminalId] = ['error' => 'The terminal assigned to this route no longer exists.'];
                } else {
                    $capacity = (int) ($terminal['capacity'] ?? 0);
                    if ($capacity < 1) {
                        $terminalStates[$terminalId] = ['error' => 'The vehicle limit for ' . ($terminal['name'] ?? 'this terminal') . ' is not configured.'];
                    } else {
                        $activeCount = (int) $db->table('queue')
                            ->join('routes', 'routes.id = queue.route_id')
                            ->where('routes.terminal_id', $terminalId)
                            ->whereIn('queue.status', ['waiting', 'boarding'])
                            ->countAllResults();
                        $terminalStates[$terminalId] = [
                            'name' => $terminal['name'] ?? 'This terminal',
                            'capacity' => $capacity,
                            'active' => $activeCount,
                        ];
                    }
                }
            }
            $terminalState = $terminalStates[$terminalId];
            if (isset($terminalState['error'])) {
                $errors[] = $terminalState['error'];
                continue;
            }
            if ($terminalState['active'] >= $terminalState['capacity']) {
                $capacity = $terminalState['capacity'];
                $errors[] = $terminalState['name'] . ' has reached its limit of '
                    . $capacity . ' active vehicle' . ($capacity === 1 ? '' : 's')
                    . '. Depart or cancel a vehicle before adding another.';
                continue;
            }

            // Positions are shared by every route with the same terminal and
            // destination. Query once per line, then advance locally after inserts.
            $positionKey = $terminalId . '|' . $route['destination'];
            if (!array_key_exists($positionKey, $destinationPositions)) {
                $sameDestinationRouteIds = (new RouteModel())
                    ->where('terminal_id', $route['terminal_id'])
                    ->where('destination', $route['destination'])
                    ->findColumn('id') ?: [$routeId];
                $lastPosition = $this->queueModel
                    ->whereIn('route_id', $sameDestinationRouteIds)
                    ->whereIn('status', ['waiting', 'boarding'])
                    ->selectMax('position')
                    ->first();
                $destinationPositions[$positionKey] = (int) ($lastPosition['position'] ?? 0);
            }
            $nextPosition = $destinationPositions[$positionKey] + 1;

            if (!array_key_exists($routeId, $ruleCache)) {
                $ruleCache[$routeId] = $departureRuleModel->getRuleForTime($currentTime, $terminalId, (int) $routeId);
            }
            $matchedRule = $ruleCache[$routeId];
            $waitMinutes = (int) $matchedRule['wait_minutes'];
            $ruleLabel   = $matchedRule['label'] ?? 'Default';

            // Stagger arrival_time by selection order to guarantee FIFO queue ranking
            $arrivalTime = date('Y-m-d H:i:s', $baseArrivalTimestamp + $selectionIndex);
            $estimatedDeparture = date('Y-m-d H:i:s', strtotime("+$waitMinutes minutes", $baseArrivalTimestamp + $selectionIndex));

            $queueId = $this->queueModel->insert([
                'vehicle_id'          => $vehicleId,
                'driver_name'         => $vehicle['driver_name'] ?? null,
                'operator_name'       => $vehicle['operator_name'] ?? $vehicle['owner_name'] ?? null,
                'plate_number'        => $vehicle['plate_number'] ?? null,
                'route_id'            => $routeId,
                'status'              => 'waiting',
                'position'            => $nextPosition,
                'arrival_time'        => $arrivalTime,
                'estimated_departure' => $estimatedDeparture,
            ]);

            $destinationPositions[$positionKey] = $nextPosition;
            $terminalStates[$terminalId]['active']++;
            $selectionIndex++;
            $addedCount++;
            $addedPlates[] = $vehicle['plate_number'];

            $opName = $vehicle['operator_name'] ?? $vehicle['owner_name'] ?? '';
            $drName = $vehicle['driver_name'] ?? '';
            $this->logActivity('Add to queue', 'Added ' . ($vehicle['plate_number'] ?? 'vehicle') . ' to queue for ' . ($route['destination'] ?? 'route') . '. (Operator: ' . ($opName ?: 'N/A') . ', Driver: ' . ($drName ?: 'N/A') . '). Rule: ' . $ruleLabel . ' (' . $waitMinutes . ' min).');
        }

        // Order the active queue by departure
        if ($addedCount > 0) {
            $this->queueModel->reorderByDeparture();
        }

        $db->transComplete();
        if ($db->transStatus() === false) {
            return redirect()->to('/staff/queue')->with('error', 'The queue could not be updated. Please try again.');
        }

        if ($addedCount > 0) {
            $this->broadcastUpdate('queue_update', [
                'action' => 'add',
                'count'  => $addedCount,
                'plates' => $addedPlates,
            ]);
        }

        if ($addedCount > 0) {
            $successMsg = $addedCount > 8
                ? $addedCount . ' vehicles added to queue.'
                : implode(', ', $addedPlates) . ' added to queue.';
            if (!empty($errors) || !empty($warnings)) {
                $messages = array_values(array_unique(array_merge($warnings, $errors)));
                $extra = implode(' ', array_slice($messages, 0, 8));
                if (count($messages) > 8) $extra .= ' And ' . (count($messages) - 8) . ' more could not be added.';
                return redirect()->to('/staff/queue')->with('success', $successMsg)->with('warning', $extra);
            }
            return redirect()->to('/staff/queue')->with('success', $successMsg);
        }

        $messages = array_values(array_unique(array_merge($warnings, $errors)));
        $errorMsg = $messages ? implode(' ', array_slice($messages, 0, 8)) : 'No vehicles were added to queue.';
        if (count($messages) > 8) $errorMsg .= ' And ' . (count($messages) - 8) . ' more could not be added.';
        return redirect()->to('/staff/queue')->with('error', $errorMsg);
    }

    public function updateStatus($id, $status)
    {
        // Server-side route authorization check
        if (!$this->hasQueueAccess((int) $id)) {
            $this->logActivity('Unauthorized queue action attempt', 'Dispatcher "' . session()->get('username') . '" tried to update status of queue #' . $id . ' on an unassigned route.');
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => "You don't have access to this route."]);
            }
            return redirect()->back()->with('error', "You don't have access to this route.");
        }

        // Sanitize status in case query string is included
        if (strpos($status, '?') !== false) {
            $parts = explode('?', $status);
            $status = $parts[0];
        }

        $validStatuses = ['waiting', 'boarding', 'departed', 'canceled'];

        if (!in_array($status, $validStatuses)) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid status.']);
            }
            return redirect()->back()->with('error', 'Invalid status.');
        }

        $existingItem = $this->queueModel->find($id);
        if (! $existingItem) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Queue item not found.']);
            }
            return redirect()->back()->with('error', 'Queue item not found.');
        }

        $data = ['status' => $status];
        if ($status == 'departed') {
            $data['departure_time'] = date('Y-m-d H:i:s');
            $data['position'] = 0; // Remove from active positions
            $qItem = $this->queueModel->find($id);
            if ($qItem) {
                $veh = (new \App\Models\VehicleModel())->find($qItem['vehicle_id']);
                if ($veh) {
                    $data['driver_name']   = !empty($qItem['driver_name']) ? $qItem['driver_name'] : ($veh['driver_name'] ?? null);
                    $data['operator_name'] = !empty($qItem['operator_name']) ? $qItem['operator_name'] : ($veh['operator_name'] ?? $veh['owner_name'] ?? null);
                    $data['plate_number']  = !empty($qItem['plate_number']) ? $qItem['plate_number'] : ($veh['plate_number'] ?? null);
                }
            }
        } elseif ($status == 'canceled') {
            // Keep the old position as the restore slot. Canceled rows are not
            // included in active queue calculations, so this cannot occupy an
            // active position while the trip is canceled.
            $data['estimated_departure'] = null;
        } elseif ($status == 'boarding') {
            // The departure interval starts NOW (when boarding begins), not when
            // the vehicle was queued. Re-evaluate the rule for the current time.
            $qItem      = $this->queueModel->find($id);
            $route      = $qItem ? $this->routeModel->find($qItem['route_id']) : null;
            $terminalId = (int) ($route['terminal_id'] ?? 1);
            $routeId    = $qItem ? (int) $qItem['route_id'] : null;
            $waitMinutes = (new DepartureRuleModel())->getWaitMinutesForTime(date('H:i:s'), $terminalId, $routeId);
            $data['estimated_departure'] = date('Y-m-d H:i:s', strtotime("+$waitMinutes minutes"));
        } elseif ($status == 'waiting') {
            // If estimated_departure is empty, calculate it
            $qItem = $this->queueModel->find($id);
            if ($qItem && empty($qItem['estimated_departure'])) {
                $route      = $this->routeModel->find($qItem['route_id']);
                $terminalId = (int) ($route['terminal_id'] ?? 1);
                $routeId    = (int) $qItem['route_id'];
                $waitMinutes = (new DepartureRuleModel())->getWaitMinutesForTime(date('H:i:s'), $terminalId, $routeId);
                $data['estimated_departure'] = date('Y-m-d H:i:s', strtotime("+$waitMinutes minutes"));
            }
        }

        // Use transaction to prevent race conditions during position reordering
        $db = \Config\Database::connect();
        $db->transStart();
        $this->acquireQueueOrderingLock($db);

        $wasActive = in_array($existingItem['status'] ?? '', ['waiting', 'boarding'], true);
        $willBeActive = in_array($status, ['waiting', 'boarding'], true);
        if (!$wasActive && $willBeActive) {
            $route = $this->routeModel->find($existingItem['route_id']);
            $capacityError = $route
                ? $this->terminalCapacityError($db, (int) ($route['terminal_id'] ?? 0))
                : 'The route assigned to this trip no longer exists.';
            if ($capacityError !== null) {
                $db->transRollback();
                if ($this->request->isAJAX()) {
                    return $this->response->setStatusCode(409)->setJSON(['success' => false, 'message' => $capacityError]);
                }
                return redirect()->back()->with('error', $capacityError);
            }
        }

        $this->queueModel->update($id, $data);

        // Keep the active queue ordered by departure time (boarding by ETA,
        // then waiting by arrival). Also renumbers after a depart/cancel.
        $this->queueModel->reorderByDeparture();

        $db->transComplete();

        if ($db->transStatus() === false) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Failed to update queue status.']);
            }
            return redirect()->back()->with('error', 'Failed to update queue status.');
        }


        $item = $this->queueModel->select('vehicles.plate_number, vehicles.operator_name, vehicles.driver_name, terminals.name as origin, routes.destination')
            ->withFullJoins()
            ->where('queue.id', $id)
            ->first();
        $label = $item ? $item['plate_number'] . ' (' . $item['destination'] . ')' : 'queue #' . $id;
        $opDriver = $item ? ' (Operator: ' . ($item['operator_name'] ?: 'N/A') . ', Driver: ' . ($item['driver_name'] ?: 'N/A') . ')' : '';
        $actionLabel = $status === 'boarding' ? 'Start Boarding' : ($status === 'departed' ? 'Depart Vehicle' : ($status === 'canceled' ? 'Cancel Trip' : $status));
        $this->logActivity($actionLabel, $actionLabel . ' for ' . $label . '.' . $opDriver);

        // Fetch updated queue item for broadcast
        $updatedItem = $this->queueModel->select('queue.*, vehicles.plate_number, vehicles.type as vehicle_type, vehicles.photo as vehicle_photo, vehicles.capacity, terminals.name as origin, routes.destination')
            ->withFullJoins()
            ->where('queue.id', $id)
            ->first();

        $this->broadcastUpdate('queue_update', [
            'action' => 'status_change',
            'id' => $id,
            'status' => $status,
            'queue_item' => $updatedItem
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Status updated',
                'status' => $status,
                'id' => (int) $id,
                'plate_number' => $updatedItem['plate_number'] ?? '',
                'vehicle_type' => vehicle_type_label($updatedItem['vehicle_type'] ?? '')
            ]);
        }

        $ref = $this->request->getVar('ref');
        $redirectUrl = $ref === 'dashboard' ? 'staff/dashboard' : 'staff/queue';
        return redirect()->to(base_url($redirectUrl))->with('success', 'Status updated.');
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

        if (! in_array($queueItem['status'] ?? '', ['waiting', 'boarding'], true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Passengers can only be updated for waiting or boarding trips.']);
        }

        $vehicle = $this->vehicleModel->find($queueItem['vehicle_id']);
        if (! $vehicle) {
            return $this->response->setJSON(['success' => false, 'message' => 'Vehicle not found']);
        }

        $input = $this->request->getJSON(true);
        $rawCount = $input['count'] ?? 0;
        if (! is_numeric($rawCount)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid passenger count.']);
        }
        $capacity = (int) ($vehicle['capacity'] ?? 14);
        if ($capacity <= 0) {
            $capacity = 14;
        }
        $newCount = (int) $rawCount;

        // Clamp to valid range
        $newCount = max(0, min($newCount, $capacity));

        $db = \Config\Database::connect();
        $db->transStart();
        $this->queueModel->update($id, ['current_passengers' => $newCount]);
        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to update passengers.']);
        }

        $this->broadcastUpdate('queue_update', [
            'action' => 'passenger_change',
            'id' => $id,
            'new_count' => $newCount,
            'capacity' => $capacity,
            'role' => 'staff'
        ]);

        return $this->response->setJSON([
            'success' => true,
            'new_count' => $newCount,
            'capacity' => $capacity,
            'is_full' => $newCount >= $capacity
        ]);
    }

    public function updateDriver($id)
    {
        $queueItem = $this->queueModel->find($id);
        if (!$queueItem) {
            return $this->response->setJSON(['success' => false, 'message' => 'Queue item not found.']);
        }

        if (!$this->hasRouteAccess((int) $queueItem['route_id'])) {
            $this->logActivity('Unauthorized queue action attempt', 'Dispatcher "' . session()->get('username') . '" tried to change driver on unassigned route ID ' . $queueItem['route_id'] . '.');
            return $this->response->setJSON(['success' => false, 'message' => "You don't have access to this route."]);
        }

        $driverName = trim((string) ($this->request->getPost('driver_name') ?? $this->request->getVar('driver_name') ?? ''));
        if ($driverName === '' || strlen($driverName) < 2 || strlen($driverName) > 100) {
            return $this->response->setJSON(['success' => false, 'message' => 'Driver name must be between 2 and 100 characters.']);
        }

        $vehicle = $this->vehicleModel->find($queueItem['vehicle_id']);
        if (!$vehicle) {
            return $this->response->setJSON(['success' => false, 'message' => 'Vehicle not found.']);
        }
        $oldDriverName = $vehicle['driver_name'] ?? 'Unknown';

        // Single transaction: update vehicle record + all active queue rows
        // for this vehicle atomically to avoid interleaving with add()/status.
        $db = \Config\Database::connect();
        $db->transStart();
        $this->vehicleModel->update($vehicle['id'], ['driver_name' => $driverName]);
        $this->queueModel->where('vehicle_id', $vehicle['id'])
            ->whereIn('status', ['waiting', 'boarding'])
            ->set(['driver_name' => $driverName])
            ->update();
        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to update driver.']);
        }

        $this->logActivity('Updated Driver', 'Updated Driver: ' . $oldDriverName . ' for ' . ($vehicle['plate_number'] ?? 'vehicle') . ' (' . ($vehicle['type'] ?? 'N/A') . ') to ' . $driverName . '.');
        $this->broadcastUpdate('queue_update', [
            'action' => 'driver_change',
            'id' => (int) $id,
            'driver_name' => $driverName,
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Driver updated to ' . $driverName . '.',
            'driver_name' => $driverName
        ]);
    }

    /**
     * Save a dispatcher-selected order for one terminal/destination queue line.
     * Boarding vehicles are kept at the front; only waiting vehicles are movable.
     */
    public function reorder()
    {
        $payload = $this->request->getJSON(true);
        if (!is_array($payload)) {
            $payload = $this->request->getPost();
        }

        $rawIds = $payload['queue_ids'] ?? [];
        if (!is_array($rawIds) || empty($rawIds)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Select a queue line to reorder.',
            ]);
        }

        $queueIds = [];
        foreach ($rawIds as $rawId) {
            if (!is_numeric($rawId) || (int) $rawId <= 0) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'The queue order contains an invalid vehicle.',
                ]);
            }
            $queueIds[] = (int) $rawId;
        }

        if (count($queueIds) !== count(array_unique($queueIds))) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'The queue order contains a duplicate vehicle.',
            ]);
        }

        $submittedItems = (new QueueModel())
            ->select('queue.*, routes.terminal_id, routes.destination')
            ->join('routes', 'routes.id = queue.route_id')
            ->whereIn('queue.id', $queueIds)
            ->whereIn('queue.status', ['waiting', 'boarding'])
            ->findAll();

        if (count($submittedItems) !== count($queueIds)) {
            return $this->response->setStatusCode(409)->setJSON([
                'success' => false,
                'message' => 'The queue changed while it was being edited. Refresh and try again.',
            ]);
        }

        $firstItem = $submittedItems[0];
        $terminalId = (int) $firstItem['terminal_id'];
        $destination = (string) $firstItem['destination'];

        foreach ($submittedItems as $item) {
            if ((int) $item['terminal_id'] !== $terminalId || (string) $item['destination'] !== $destination) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Vehicles from different queue lines cannot be reordered together.',
                ]);
            }
            if (!$this->hasRouteAccess((int) $item['route_id'])) {
                $this->logActivity('Unauthorized queue reorder attempt', 'Dispatcher "' . session()->get('username') . '" tried to reorder an unassigned route.');
                return $this->response->setStatusCode(403)->setJSON([
                    'success' => false,
                    'message' => "You don't have access to every vehicle in this queue line.",
                ]);
            }
        }

        $db = \Config\Database::connect();
        $db->transStart();
        $this->acquireQueueOrderingLock($db);

        // Re-read the complete line after locking. Refuse a stale or partial
        // request instead of silently moving vehicles the dispatcher did not see.
        $currentItems = (new QueueModel())
            ->select('queue.*, routes.terminal_id, routes.destination')
            ->join('routes', 'routes.id = queue.route_id')
            ->where('routes.terminal_id', $terminalId)
            ->where('routes.destination', $destination)
            ->whereIn('queue.status', ['waiting', 'boarding'])
            ->orderBy("CASE WHEN queue.status = 'boarding' THEN 0 ELSE 1 END", 'ASC')
            ->orderBy('queue.position', 'ASC')
            ->findAll();

        $currentById = [];
        foreach ($currentItems as $item) {
            if (!$this->hasRouteAccess((int) $item['route_id'])) {
                $db->transRollback();
                return $this->response->setStatusCode(403)->setJSON([
                    'success' => false,
                    'message' => "You don't have access to every vehicle in this queue line.",
                ]);
            }
            $currentById[(int) $item['id']] = $item;
        }

        $currentIds = array_map(static fn(array $item): int => (int) $item['id'], $currentItems);
        $submittedSet = $queueIds;
        sort($currentIds);
        sort($submittedSet);

        if ($currentIds !== $submittedSet) {
            $db->transRollback();
            return $this->response->setStatusCode(409)->setJSON([
                'success' => false,
                'message' => 'The queue changed while it was being edited. The latest order will be loaded.',
            ]);
        }

        $boardingIds = [];
        foreach ($currentItems as $item) {
            if ($item['status'] === 'boarding') {
                $boardingIds[] = (int) $item['id'];
            }
        }

        $waitingIds = [];
        foreach ($queueIds as $queueId) {
            if (($currentById[$queueId]['status'] ?? '') === 'waiting') {
                $waitingIds[] = $queueId;
            }
        }

        $finalOrder = array_merge($boardingIds, $waitingIds);
        foreach ($finalOrder as $index => $queueId) {
            $this->queueModel->update($queueId, ['position' => $index + 1]);
        }

        $this->queueModel->reorderByDeparture();
        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Failed to save the queue order.',
            ]);
        }

        $this->logActivity('Reordered Queue', 'Changed the vehicle order for the ' . $destination . ' queue.');
        $this->broadcastUpdate('queue_update', [
            'action' => 'reorder',
            'destination' => $destination,
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Queue order saved.',
        ]);
    }

    public function undoCancel($id)
    {
        // Server-side route authorization check
        if (!$this->hasQueueAccess((int) $id)) {
            $this->logActivity('Unauthorized queue action attempt', 'Dispatcher "' . session()->get('username') . '" tried to undo cancel on unassigned route queue #' . $id . '.');
            return $this->response->setJSON(['success' => false, 'message' => "You don't have access to this route."]);
        }

        $queueItem = $this->queueModel->find($id);
        if (!$queueItem) {
            return $this->response->setJSON(['success' => false, 'message' => 'Queue item not found.']);
        }

        if ($queueItem['status'] !== 'canceled') {
            return $this->response->setJSON(['success' => false, 'message' => 'This trip is not canceled.']);
        }

        $vehicleId = (int) $queueItem['vehicle_id'];

        // Check if vehicle is already in active queue (waiting or boarding) in another record
        $alreadyQueued = $this->queueModel->where('vehicle_id', $vehicleId)
            ->whereIn('status', ['waiting', 'boarding'])
            ->first();

        if ($alreadyQueued) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Vehicle ' . ($queueItem['plate_number'] ?? '') . ' is already active in the queue.'
            ]);
        }

        // Validate vehicle is active (not archived or in maintenance)
        $vehicle = $this->vehicleModel->find($vehicleId);
        if (!$vehicle || ($vehicle['status'] ?? 'active') !== 'active') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cannot restore trip: This vehicle is currently inactive or archived.'
            ]);
        }

        // Validate route is active (not archived)
        $routeModel = new \App\Models\RouteModel();
        $route = $routeModel->find($queueItem['route_id']);
        if (!$route || ($route['status'] ?? 'active') !== 'active') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cannot restore trip: The assigned route is currently inactive or archived.'
            ]);
        }

        // Restore to waiting
        $db = \Config\Database::connect();
        $db->transStart();
        $this->acquireQueueOrderingLock($db);

        // Re-check after acquiring the queue lock so a concurrent add cannot
        // activate the same vehicle between the earlier validation and restore.
        $alreadyQueued = $this->queueModel->where('vehicle_id', $vehicleId)
            ->whereIn('status', ['waiting', 'boarding'])
            ->first();
        if ($alreadyQueued) {
            $db->transRollback();
            return $this->response->setStatusCode(409)->setJSON([
                'success' => false,
                'message' => 'Vehicle ' . ($queueItem['plate_number'] ?? '') . ' is already active in the queue.',
            ]);
        }

        $capacityError = $this->terminalCapacityError($db, (int) ($route['terminal_id'] ?? 0));
        if ($capacityError !== null) {
            $db->transRollback();
            return $this->response->setStatusCode(409)->setJSON([
                'success' => false,
                'message' => $capacityError,
            ]);
        }

        $sameDestinationRouteIds = (new RouteModel())
            ->where('terminal_id', $route['terminal_id'])
            ->where('destination', $route['destination'])
            ->findColumn('id') ?: [(int) $queueItem['route_id']];

        $activeItems = (new QueueModel())
            ->whereIn('route_id', $sameDestinationRouteIds)
            ->whereIn('status', ['waiting', 'boarding'])
            ->orderBy("CASE WHEN status = 'boarding' THEN 0 ELSE 1 END", 'ASC')
            ->orderBy('position', 'ASC')
            ->findAll();

        $activeIds = array_map(static fn(array $item): int => (int) $item['id'], $activeItems);
        $boardingCount = count(array_filter($activeItems, static fn(array $item): bool => $item['status'] === 'boarding'));
        $savedPosition = (int) ($queueItem['position'] ?? 0);
        $insertIndex = $savedPosition > 0 ? min($savedPosition - 1, count($activeIds)) : count($activeIds);
        $insertIndex = max($boardingCount, $insertIndex);
        array_splice($activeIds, $insertIndex, 0, [(int) $id]);

        $this->queueModel->update($id, [
            'status' => 'waiting',
            'arrival_time' => !empty($queueItem['arrival_time']) ? $queueItem['arrival_time'] : date('Y-m-d H:i:s'),
        ]);

        foreach ($activeIds as $index => $queueId) {
            $this->queueModel->update($queueId, ['position' => $index + 1]);
        }

        // Recalculate queue positions and departure times
        $this->queueModel->reorderByDeparture();

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to restore trip.']);
        }

        $plateNumber = $queueItem['plate_number'] ?? 'Vehicle';
        $this->logActivity('Undo Cancel Trip', 'Restored trip for ' . $plateNumber . ' back to the active queue.');

        // Broadcast update to all clients
        $updatedItem = $this->queueModel->select('queue.*, vehicles.plate_number, vehicles.type as vehicle_type, vehicles.photo as vehicle_photo, vehicles.capacity, terminals.name as origin, routes.destination')
            ->withFullJoins()
            ->where('queue.id', $id)
            ->first();

        $this->broadcastUpdate('queue_update', [
            'action' => 'status_change',
            'id' => (int) $id,
            'status' => 'waiting',
            'queue_item' => $updatedItem
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Trip for ' . $plateNumber . ' has been restored to the queue.',
                'id' => (int) $id,
                'plate_number' => $plateNumber,
                'queue_item' => $updatedItem
            ]);
        }

        return redirect()->to(base_url('staff/queue'))->with('success', 'Trip for ' . $plateNumber . ' has been restored to the queue.');
    }
}
