<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\RouteModel;
use App\Models\TerminalModel;
use App\Models\FareDiscountModel;
use App\Models\FareModel;
use App\Models\VehicleTypeModel;

class Routes extends BaseController
{
    protected $routeModel;
    protected $terminalModel;
    protected $discountModel;
    protected $vehicleTypeModel;

    public function __construct()
    {
        $this->routeModel       = new RouteModel();
        $this->terminalModel    = new TerminalModel();
        $this->discountModel    = new FareDiscountModel();
        $this->vehicleTypeModel = new VehicleTypeModel();
    }

    private function isActiveVehicleType(string $slug): bool
    {
        return (bool) $this->vehicleTypeModel->where('slug', $slug)->where('is_active', 1)->first();
    }

    private function activeVehicleTypeSlugs(): array
    {
        return array_column($this->vehicleTypeModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll(), 'slug');
    }

    private function activeVehicleTypes(): array
    {
        return $this->vehicleTypeModel
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    public function index()
    {
        helper('fare');

        $routes = $this->routeModel->withOrigin()
                                   ->select('terminals.name as terminal_name')
                                   ->findAll();
        $routes = enrich_routes_with_discounts($routes);

        $groupedRoutes = [];
        foreach ($routes as $route) {
            $key = $route['terminal_id'] . '_' . $route['destination'];
            if (!isset($groupedRoutes[$key])) {
                $groupedRoutes[$key] = [
                    'terminal_name' => $route['terminal_name'] ?? $route['origin'] ?? '',
                    'destination'   => $route['destination'],
                    'status'        => 'active',
                    'items'         => []
                ];
            }
            $groupedRoutes[$key]['items'][] = $route;
        }

        $countActiveGroups = 0;
        $countArchivedGroups = 0;
        foreach ($groupedRoutes as &$group) {
            $allArchived = true;
            foreach ($group['items'] as $it) {
                if (($it['status'] ?? 'active') !== 'archived') {
                    $allArchived = false;
                    break;
                }
            }
            $group['status'] = $allArchived ? 'archived' : 'active';
            if ($group['status'] === 'archived') {
                $countArchivedGroups++;
            } else {
                $countActiveGroups++;
            }
        }
        unset($group);

        $data = [
            'title'               => 'Manage Routes',
            'routes'              => $routes,
            'groupedRoutes'       => $groupedRoutes,
            'countActiveGroups'   => $countActiveGroups,
            'countArchivedGroups' => $countArchivedGroups,
        ];

        return view('admin/routes/index', $data);
    }

    /**
     * Get distinct destinations from the routes table for dropdown population.
     */
    private function getDistinctLocations(): array
    {
        $db = \Config\Database::connect();

        $destsResult  = $db->query("SELECT DISTINCT destination FROM routes WHERE destination IS NOT NULL AND destination != '' ORDER BY destination ASC")->getResultArray();
        $destinations = array_column($destsResult, 'destination');
        $allLocations = array_values(array_filter(array_map('strtoupper', array_unique($destinations))));
        sort($allLocations);

        return [
            'origins'       => [],
            'destinations'  => $destinations,
            'all_locations' => $allLocations,
        ];
    }

    private function ensureRegularDiscount(int $terminalId): array
    {
        $regular = $this->discountModel
            ->where('terminal_id', $terminalId)
            ->where('type', 'regular')
            ->first();

        if ($regular) {
            return $regular;
        }

        $id = $this->discountModel->insert([
            'terminal_id'      => $terminalId,
            'type'             => 'regular',
            'label'            => 'Regular Fare',
            'discount_percent' => 0.00,
            'is_active'        => 1,
        ]);

        return $this->discountModel->find($id);
    }

    private function getDiscountsForFareCalculation(int $terminalId): array
    {
        $this->ensureRegularDiscount($terminalId);

        return $this->discountModel
            ->where('terminal_id', $terminalId)
            ->groupStart()
                ->where('type', 'regular')
                ->orWhere('is_active', 1)
            ->groupEnd()
            ->findAll();
    }

    private function replaceRouteFares(int $routeId, int $terminalId, float $baseFare): void
    {
        $fareModel = new FareModel();
        $db = \Config\Database::connect();
        $db->transStart();

        $fareModel->where('route_id', $routeId)->delete();

        $rows = [];
        foreach ($this->getDiscountsForFareCalculation($terminalId) as $discount) {
            $amount = ($discount['type'] === 'regular')
                ? $baseFare
                : round($baseFare * (1 - ((float) $discount['discount_percent'] / 100)), 2);

            $rows[] = [
                'route_id'         => $routeId,
                'fare_discount_id' => $discount['id'],
                'amount'           => $amount,
            ];
        }

        if (! empty($rows)) {
            $fareModel->insertBatch($rows);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            log_message('error', 'Failed to replace fares for route {id}', ['id' => $routeId]);
        }
    }

    private function getRegularFare(int $routeId): float
    {
        $fareModel = new FareModel();
        $row = $fareModel
            ->select('fares.amount')
            ->join('fare_discounts', 'fare_discounts.id = fares.fare_discount_id')
            ->where('fares.route_id', $routeId)
            ->where('fare_discounts.type', 'regular')
            ->first();

        return $row ? (float) $row['amount'] : 0.00;
    }

    /**
     * Removing a route must only remove the assignment from registered vehicles.
     * Do this explicitly so the behavior is safe even when an older database has
     * an incorrect cascading foreign key.
     */
    private function unassignVehiclesFromRoutes(array $routeIds): void
    {
        $routeIds = array_values(array_filter(array_map('intval', $routeIds)));
        if (empty($routeIds)) {
            return;
        }

        $db = \Config\Database::connect();
        $routeColumn = $db->fieldExists('default_route_id', 'vehicles')
            ? 'default_route_id'
            : 'route_id';

        $db->table('vehicles')
            ->whereIn($routeColumn, $routeIds)
            ->update([$routeColumn => null]);
    }

    private function recalculateFaresForDiscount(array $discount): void
    {
        if ($discount['type'] === 'regular') {
            return;
        }

        $fareModel = new FareModel();
        $routes = $this->routeModel
            ->where('terminal_id', (int) $discount['terminal_id'])
            ->findAll();

        foreach ($routes as $route) {
            $baseFare = $this->getRegularFare((int) $route['id']);
            $amount = round($baseFare * (1 - ((float) $discount['discount_percent'] / 100)), 2);
            $existing = $fareModel
                ->where('route_id', $route['id'])
                ->where('fare_discount_id', $discount['id'])
                ->first();

            if ($existing) {
                $fareModel->update($existing['id'], ['amount' => $amount]);
                continue;
            }

            $fareModel->insert([
                'route_id'         => $route['id'],
                'fare_discount_id' => $discount['id'],
                'amount'           => $amount,
            ]);
        }
    }

    public function create()
    {
        $locations = $this->getDistinctLocations();

        $data = [
            'title'         => 'Add New Route',
            'terminals'     => $this->terminalModel->findAll(),
            'vehicleTypes'  => $this->activeVehicleTypes(),
            'origins'       => $locations['origins'],
            'destinations'  => $locations['destinations'],
            'all_locations' => $locations['all_locations'],
        ];
        return view('admin/routes/create', $data);
    }

    public function store()
    {
        $selectedTypes = $this->request->getPost('vehicle_types');
        $referer = (string) $this->request->getHeaderLine('Referer');
        $target  = (strpos($referer, 'fares') !== false) ? '/fares' : '/admin/routes';

        // ── Flow 1: Route creation from Add Route form (vehicle_types[] checkboxes, no fares) ──
        if (is_array($selectedTypes) && !$this->request->getPost('fare')) {
            $terminalId  = $this->request->getPost('terminal_id');
            $terminal    = $this->terminalModel->find($terminalId);
            $origin      = strtoupper(trim($terminal['name'] ?? ''));
            $destination = strtoupper(trim($this->request->getPost('destination')));

            if (empty($destination) || strlen($destination) < 2) {
                return redirect()->back()->withInput()->with('error', 'Please provide a valid destination (at least 2 characters).');
            }

            $activeTypes = $this->activeVehicleTypeSlugs();
            $typesToCreate = array_values(array_intersect($selectedTypes, $activeTypes));

            if (empty($typesToCreate)) {
                return redirect()->back()->withInput()->with('error', 'Please select at least one valid vehicle type.');
            }

            $createdRoutes = 0;

            foreach ($typesToCreate as $vType) {
                $existing = $this->routeModel->where('terminal_id', $terminalId)
                                             ->where('destination', $destination)
                                             ->where('vehicle_type', $vType)
                                             ->first();
                if (!$existing) {
                    $insertedId = $this->routeModel->insert([
                        'destination'  => $destination,
                        'terminal_id'  => $terminalId,
                        'vehicle_type' => $vType,
                        'status'       => 'active',
                    ]);
                    if ($insertedId) {
                        $createdRoutes++;
                        (new \App\Models\UserRouteModel())->autoAssignNewRouteToStaff((int)$insertedId, (int)$terminalId, $destination);
                    }
                }
            }

            if ($createdRoutes > 0) {
                $this->logActivity('Create route', "$origin → $destination ($createdRoutes vehicle type(s)).");
                $this->broadcastUpdate('fare_update', ['action' => 'route_created']);
                return redirect()->to('/admin/routes')->with('success', "Route ($origin → $destination) created with $createdRoutes vehicle type(s). Fares can be assigned in Fare Management.");
            } else {
                return redirect()->back()->withInput()->with('error', 'This route already exists for the selected vehicle type(s).');
            }
        }

        // ── Flow 2: Fare assignment from the Add Fare modal (sends fare + vehicle_types[] or vehicle_type) ──
        $rawTypes = $this->request->getPost('vehicle_types');
        if (empty($rawTypes)) {
            $singleType = $this->request->getPost('vehicle_type');
            $rawTypes = $singleType ? [$singleType] : [];
        }
        if (!is_array($rawTypes)) {
            $rawTypes = [$rawTypes];
        }

        $rules = [
            'destination'  => 'required|min_length[2]|max_length[100]',
            'fare'         => 'required|decimal|greater_than[0]',
            'terminal_id'  => 'required|integer|is_not_unique[terminals.id]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $activeSlugs = $this->activeVehicleTypeSlugs();
        $selectedTypes = array_values(array_intersect($rawTypes, $activeSlugs));

        if (empty($selectedTypes)) {
            return redirect()->back()->withInput()->with('error', 'Please select at least one active vehicle type.');
        }

        $terminalId  = (int) $this->request->getPost('terminal_id');
        $terminal    = $this->terminalModel->find($terminalId);
        $origin      = strtoupper(trim($terminal['name'] ?? ''));
        $destination = strtoupper(trim($this->request->getPost('destination')));
        $fare        = (float) $this->request->getPost('fare');

        $fareModel      = new FareModel();
        $assignedCount  = 0;
        $assignedTypes  = [];
        $alreadyHadFare = [];
        $notExisting    = [];

        foreach ($selectedTypes as $vType) {
            $existing = $this->routeModel->where('terminal_id', $terminalId)
                                         ->where('destination', $destination)
                                         ->where('vehicle_type', $vType)
                                         ->first();
            if (!$existing) {
                $notExisting[] = ucfirst($vType);
                continue;
            }

            $existingFare = $fareModel->where('route_id', $existing['id'])->first();
            if ($existingFare && (float)($existingFare['amount'] ?? 0) > 0) {
                $alreadyHadFare[] = ucfirst($vType);
                continue;
            }

            $this->replaceRouteFares((int)$existing['id'], (int)$terminalId, $fare);
            $assignedCount++;
            $assignedTypes[] = ucfirst($vType);
            $this->broadcastUpdate('fare_update', ['action' => 'fare_assigned', 'id' => (int)$existing['id']]);
        }

        if ($assignedCount > 0) {
            $typesStr = implode(', ', $assignedTypes);
            $this->logActivity('Assign fare to route', "$origin → $destination ($typesStr, ₱$fare).");
            
            $fareFormatted = number_format($fare, 2);
            $msg = "Fare (₱{$fareFormatted}) assigned successfully to route $origin → $destination for $typesStr.";
            if (!empty($alreadyHadFare)) {
                $msg .= " Note: " . implode(', ', $alreadyHadFare) . " already had active fares and were skipped.";
            }
            return redirect()->to($target)->with('success', $msg);
        }

        if (!empty($alreadyHadFare)) {
            return redirect()->back()->withInput()->with('error', "Route ($origin → $destination) for " . implode(', ', $alreadyHadFare) . " already has active fares. Please edit the existing fare(s) instead.");
        }

        return redirect()->back()->withInput()->with('error', "Route ($origin → $destination) was not found for the selected vehicle type(s). Adding a new fare only assigns fares to existing routes. Please create the route in Route Management first.");
    }

    public function edit($id)
    {
        helper('fare');

        $route = $this->routeModel
            ->withOrigin()
            ->find($id);

        if (!$route) {
            return redirect()->to('/admin/routes')->with('error', 'Route not found.');
        }

        // Find all sibling routes in the same group (same terminal and destination)
        $groupRoutes = $this->routeModel
            ->where('terminal_id', $route['terminal_id'])
            ->where('destination', $route['destination'])
            ->findAll();

        $groupRoutes = enrich_routes_with_discounts($groupRoutes);

        // Put fares into a key-value array by vehicle type
        $vehicleTypes = $this->activeVehicleTypes();
        $fares = array_fill_keys(array_column($vehicleTypes, 'slug'), null);
        foreach ($groupRoutes as $r) {
            $fares[$r['vehicle_type']] = $r['fare'];
        }

        $locations = $this->getDistinctLocations();

        $data = [
            'title'         => 'Edit Route',
            'route'         => $route,
            'fares'         => $fares,
            'terminals'     => $this->terminalModel->findAll(),
            'vehicleTypes'  => $vehicleTypes,
            'origins'       => $locations['origins'],
            'destinations'  => $locations['destinations'],
            'all_locations' => $locations['all_locations'],
        ];

        return view('admin/routes/edit', $data);
    }

    public function update($id)
    {
        $existingRoute = $this->routeModel
            ->withOrigin()
            ->find($id);

        if (!$existingRoute) {
            return redirect()->to('/admin/routes')->with('error', 'Route not found.');
        }

        $rules = [
            'destination'  => 'required|min_length[2]|max_length[100]',
            'fare'         => 'required|decimal|greater_than[0]',
            'terminal_id'  => 'required|integer|is_not_unique[terminals.id]',
            'vehicle_type' => 'required|alpha_dash|max_length[50]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $terminalId  = $this->request->getPost('terminal_id');
        $terminal    = $this->terminalModel->find($terminalId);
        $origin      = strtoupper(trim($terminal['name'] ?? ''));
        $destination = strtoupper(trim($this->request->getPost('destination')));
        $vehicleType = $this->request->getPost('vehicle_type');
        if (!$this->isActiveVehicleType($vehicleType)) {
            return redirect()->back()->withInput()->with('error', 'Please select a valid active vehicle type.');
        }
        $newFare     = $this->request->getPost('fare');
        $oldFare     = $this->getRegularFare((int) $id);
        if ($oldFare <= 0 && isset($existingRoute['fare'])) {
            $oldFare = (float) $existingRoute['fare'];
        }

        if ($this->inputsUnchanged([
            'destination'  => $existingRoute['destination'] ?? '',
            'terminal_id'  => $existingRoute['terminal_id'] ?? '',
            'vehicle_type' => $existingRoute['vehicle_type'] ?? '',
        ], [
            'destination'  => $destination,
            'terminal_id'  => $terminalId,
            'vehicle_type' => $vehicleType,
        ]) && abs($oldFare - (float) $newFare) <= 0.00001) {
            return $this->noChangesResponse();
        }

        $collision = $this->routeModel->where('terminal_id', $terminalId)
                                      ->where('destination', $destination)
                                      ->where('vehicle_type', $vehicleType)
                                      ->where('id !=', $id)
                                      ->first();
        if ($collision) {
            return redirect()->back()->withInput()->with('error', "A route already exists for $origin → $destination ($vehicleType). Update that one instead.");
        }

        // --- GROUP RENAMING PREVENTION OF DESYNC ---
        $oldTerminalId  = $existingRoute['terminal_id'];
        $oldDestination = $existingRoute['destination'];

        if ($oldTerminalId != $terminalId || $oldDestination !== $destination) {
            $this->routeModel->where('terminal_id', $oldTerminalId)
                             ->where('destination', $oldDestination)
                             ->set([
                                 'destination' => $destination,
                                 'terminal_id' => $terminalId
                             ])
                             ->update();
        }
        // -------------------------------------------

        $updated = $this->routeModel->update($id, [
            'destination'  => $destination,
            'terminal_id'  => $terminalId,
            'vehicle_type' => $vehicleType,
        ]);

        if (!$updated) {
            return redirect()->back()->withInput()->with('error', 'Failed to update route.');
        }

        $this->replaceRouteFares((int) $id, (int) $terminalId, (float) $newFare);
        $newFareFloat = (float) $newFare;

        if (abs($oldFare - $newFareFloat) > 0.00001) {
            $this->logActivity(
                'Update fare',
                sprintf(
                    '%s route fare changed. Before: PHP %s | After: PHP %s.',
                    strtoupper($existingRoute['origin'] . ' to ' . $existingRoute['destination'] . ' (' . $existingRoute['vehicle_type'] . ')'),
                    number_format($oldFare, 2),
                    number_format($newFareFloat, 2)
                )
            );
        } else {
            $this->logActivity('Update route', $origin . ' to ' . $destination . ' (' . $vehicleType . ').');
        }

        $this->broadcastUpdate('fare_update', ['action' => 'route_updated', 'id' => (int) $id]);

        return redirect()->back()->with('success', 'Route updated successfully.');
    }

    public function updateGroup($id)
    {
        $existingRoute = $this->routeModel->find($id);
        if (!$existingRoute) {
            return redirect()->to('/admin/routes')->with('error', 'Route not found.');
        }

        $oldTerminalId  = $existingRoute['terminal_id'];
        $oldDestination = $existingRoute['destination'];

        $terminalId  = $this->request->getPost('terminal_id');
        $terminal    = $this->terminalModel->find($terminalId);
        $origin      = strtoupper(trim($terminal['name'] ?? ''));
        $destination = strtoupper(trim($this->request->getPost('destination')));

        if (empty($destination) || strlen($destination) < 2) {
            return redirect()->back()->withInput()->with('error', 'Please provide a valid destination (at least 2 characters).');
        }

        // No-change guard: check vehicle type selection + terminal/destination changes.
        $selectedTypes = $this->request->getPost('vehicle_types');
        $selectedTypes = is_array($selectedTypes) ? $selectedTypes : [];
        if ((string) $oldTerminalId === (string) $terminalId
            && (string) $oldDestination === (string) $destination
        ) {
            $groupUnchanged = true;
            foreach ($this->activeVehicleTypeSlugs() as $vType) {
                $isSelected = in_array($vType, $selectedTypes);
                $existing = $this->routeModel->where('terminal_id', $terminalId)
                                             ->where('destination', $destination)
                                             ->where('vehicle_type', $vType)
                                             ->first();
                if ($isSelected && !$existing) {
                    $groupUnchanged = false;
                    break;
                }
                if (!$isSelected && $existing) {
                    $groupUnchanged = false;
                    break;
                }
            }
            if ($groupUnchanged) {
                return $this->noChangesResponse();
            }
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // First, update the terminal_id and destination of all existing sibling routes to prevent desync
        $this->routeModel->where('terminal_id', $oldTerminalId)
                         ->where('destination', $oldDestination)
                         ->set([
                             'terminal_id' => $terminalId,
                             'destination' => $destination
                         ])
                         ->update();

        // Add or remove vehicle types based on checkbox selection (fares are not touched)
        foreach ($this->activeVehicleTypeSlugs() as $vType) {
            $isSelected = in_array($vType, $selectedTypes);

            $existing = $this->routeModel->where('terminal_id', $terminalId)
                                         ->where('destination', $destination)
                                         ->where('vehicle_type', $vType)
                                         ->first();

            if ($isSelected && !$existing) {
                // Add new vehicle type route
                $insertedId = $this->routeModel->insert([
                    'destination'  => $destination,
                    'terminal_id'  => $terminalId,
                    'vehicle_type' => $vType,
                    'status'       => 'active',
                ]);
                if ($insertedId) {
                    (new \App\Models\UserRouteModel())->autoAssignNewRouteToStaff((int)$insertedId, (int)$terminalId, $destination);
                }
            } elseif (!$isSelected && $existing) {
                // Remove unchecked vehicle type route
                try {
                    $this->unassignVehiclesFromRoutes([(int) $existing['id']]);
                    $this->routeModel->delete($existing['id']);
                } catch (\Throwable $e) {
                    log_message('warning', 'Failed to delete route variant during group update: {msg}', ['msg' => $e->getMessage()]);
                }
            }
            // If selected and exists, keep it as-is (fares preserved)
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to update route group.');
        }

        $this->logActivity('Update route group', "$origin → $destination.");
        $this->broadcastUpdate('fare_update', ['action' => 'route_group_updated', 'id' => (int) $id]);

        return redirect()->to('/admin/routes')->with('success', 'Route group updated successfully.');
    }

    public function deleteGroup($id)
    {
        $route = $this->routeModel->find($id);
        if (!$route) {
            return redirect()->to('/admin/routes')->with('error', 'Route not found.');
        }

        $terminalId  = $route['terminal_id'];
        $destination = $route['destination'];

        $groupRoutes = $this->routeModel
            ->where('terminal_id', $terminalId)
            ->where('destination', $destination)
            ->findAll();

        $deletedCount = 0;
        $failedCount = 0;
        $this->unassignVehiclesFromRoutes(array_column($groupRoutes, 'id'));
        foreach ($groupRoutes as $r) {
            try {
                if ($this->routeModel->delete($r['id'])) {
                    $deletedCount++;
                } else {
                    $failedCount++;
                }
            } catch (\Throwable $e) {
                $failedCount++;
            }
        }

        if ($deletedCount > 0) {
            $this->logActivity('Delete route group', "Deleted $deletedCount vehicle type(s) for route to $destination.");
            $this->broadcastUpdate('fare_update', ['action' => 'route_group_deleted']);
        }

        if ($failedCount > 0) {
            return redirect()->to('/admin/routes')->with('warning', "Deleted $deletedCount vehicle type(s), but $failedCount could not be deleted (likely in use by active queues or vehicles).");
        }

        $originLabel = !empty($route['origin']) ? $route['origin'] : 'Terminal';
        $redirectTab = $this->request->getPost('redirect_tab') ?? $this->request->getGet('tab') ?? 'archived';
        return redirect()->to('/admin/routes' . ($redirectTab === 'archived' ? '?tab=archived' : ''))->with('success', 'Route group "' . strtoupper($originLabel) . ' → ' . strtoupper($destination) . '" deleted successfully.');
    }

    public function deactivateGroup($id)
    {
        $route = $this->routeModel->withOrigin()->find($id);
        if (!$route) {
            return redirect()->to('/admin/routes')->with('error', 'Route not found.');
        }

        $terminalId  = $route['terminal_id'];
        $destination = $route['destination'];

        $groupRoutes = $this->routeModel
            ->where('terminal_id', $terminalId)
            ->where('destination', $destination)
            ->findAll();

        $this->unassignVehiclesFromRoutes(array_column($groupRoutes, 'id'));

        $this->routeModel
            ->where('terminal_id', $terminalId)
            ->where('destination', $destination)
            ->set(['status' => 'archived'])
            ->update();

        $originLabel = !empty($route['origin']) ? $route['origin'] : 'Terminal';
        $routeLabel  = strtoupper($originLabel) . ' → ' . strtoupper($destination);

        $this->logActivity('Deactivate route group', "Deactivated route group $routeLabel.");
        $this->broadcastUpdate('fare_update', ['action' => 'route_group_deactivated', 'id' => (int) $id]);

        $redirectTab = $this->request->getPost('redirect_tab') ?? $this->request->getGet('tab') ?? 'active';
        return redirect()->to('/admin/routes' . ($redirectTab === 'archived' ? '?tab=archived' : ''))->with('success', "Route group \"{$routeLabel}\" deactivated and moved to archive.");
    }

    public function activateGroup($id)
    {
        $route = $this->routeModel->withOrigin()->find($id);
        if (!$route) {
            return redirect()->to('/admin/routes')->with('error', 'Route not found.');
        }

        $terminalId  = $route['terminal_id'];
        $destination = $route['destination'];

        $this->routeModel
            ->where('terminal_id', $terminalId)
            ->where('destination', $destination)
            ->set(['status' => 'active'])
            ->update();

        $originLabel = !empty($route['origin']) ? $route['origin'] : 'Terminal';
        $routeLabel  = strtoupper($originLabel) . ' → ' . strtoupper($destination);

        $this->logActivity('Activate route group', "Activated route group $routeLabel.");
        $this->broadcastUpdate('fare_update', ['action' => 'route_group_activated', 'id' => (int) $id]);

        $redirectTab = $this->request->getPost('redirect_tab') ?? $this->request->getGet('tab') ?? 'archived';
        return redirect()->to('/admin/routes' . ($redirectTab === 'archived' ? '?tab=archived' : ''))->with('success', "Route group \"{$routeLabel}\" restored to active routes.");
    }

    public function bulkAction()
    {
        $action = $this->request->getPost('action');
        $rawIds = $this->request->getPost('ids');
        $redirectTab = $this->request->getPost('redirect_tab') ?? 'archived';

        if (is_string($rawIds)) {
            $ids = array_filter(array_map('intval', explode(',', $rawIds)));
        } elseif (is_array($rawIds)) {
            $ids = array_filter(array_map('intval', $rawIds));
        } else {
            $ids = [];
        }

        if (empty($ids)) {
            return redirect()->to('/admin/routes' . ($redirectTab ? '?tab=' . $redirectTab : ''))->with('error', 'No routes selected for bulk action.');
        }

        $routes = $this->routeModel->whereIn('id', $ids)->findAll();
        if (empty($routes)) {
            return redirect()->to('/admin/routes' . ($redirectTab ? '?tab=' . $redirectTab : ''))->with('error', 'Selected routes not found.');
        }

        $count = 0;

        if ($action === 'deactivate') {
            foreach ($routes as $route) {
                $terminalId  = $route['terminal_id'];
                $destination = $route['destination'];
                $groupRoutes = $this->routeModel
                    ->where('terminal_id', $terminalId)
                    ->where('destination', $destination)
                    ->findAll();
                $this->unassignVehiclesFromRoutes(array_column($groupRoutes, 'id'));
                $this->routeModel
                    ->where('terminal_id', $terminalId)
                    ->where('destination', $destination)
                    ->set(['status' => 'archived'])
                    ->update();
                $count++;
            }
            $this->logActivity('Bulk deactivate routes', "Deactivated $count route group(s).");
            $this->broadcastUpdate('fare_update', ['action' => 'bulk_route_group_deactivated']);
            return redirect()->to('/admin/routes?tab=active')->with('success', "$count route group(s) deactivated and moved to archive.");
        } elseif ($action === 'activate') {
            foreach ($routes as $route) {
                $terminalId  = $route['terminal_id'];
                $destination = $route['destination'];
                $this->routeModel
                    ->where('terminal_id', $terminalId)
                    ->where('destination', $destination)
                    ->set(['status' => 'active'])
                    ->update();
                $count++;
            }
            $this->logActivity('Bulk activate routes', "Activated $count route group(s).");
            $this->broadcastUpdate('fare_update', ['action' => 'bulk_route_group_activated']);
            return redirect()->to('/admin/routes?tab=archived')->with('success', "$count route group(s) activated successfully.");
        } elseif ($action === 'delete') {
            $totalDeleted = 0;
            foreach ($routes as $route) {
                $terminalId  = $route['terminal_id'];
                $destination = $route['destination'];
                $groupRoutes = $this->routeModel
                    ->where('terminal_id', $terminalId)
                    ->where('destination', $destination)
                    ->findAll();
                $this->unassignVehiclesFromRoutes(array_column($groupRoutes, 'id'));
                foreach ($groupRoutes as $r) {
                    try {
                        if ($this->routeModel->delete($r['id'])) {
                            $totalDeleted++;
                        }
                    } catch (\Throwable $e) {}
                }
                $count++;
            }
            $this->logActivity('Bulk delete routes', "Permanently deleted $count route group(s) ($totalDeleted route entries).");
            $this->broadcastUpdate('fare_update', ['action' => 'bulk_route_group_deleted']);
            return redirect()->to('/admin/routes?tab=archived')->with('success', "$count route group(s) permanently deleted.");
        }

        return redirect()->to('/admin/routes' . ($redirectTab ? '?tab=' . $redirectTab : ''))->with('error', 'Invalid bulk action.');
    }

    public function deleteFare($id)
    {
        $route = $this->routeModel->withOrigin()->find($id);
        if (!$route) {
            return redirect()->back()->with('error', 'Route not found.');
        }

        $fareModel = new FareModel();
        $fareModel->where('route_id', $id)->delete();

        // Unassign any vehicles that were using this route since it no longer has an active fare
        $this->unassignVehiclesFromRoutes([(int) $id]);

        $routeDesc = strtoupper($route['origin'] ?? '') . ' → ' . strtoupper($route['destination'] ?? '') . ' (' . ucfirst($route['vehicle_type']) . ')';
        $this->logActivity('Delete fare', "Removed fare for route $routeDesc. Route preserved.");
        $this->broadcastUpdate('fare_update', ['action' => 'fare_deleted', 'id' => (int) $id]);

        return redirect()->back()->with('success', "Fare for \"{$routeDesc}\" removed. The route remains saved and is now available in the Add Fare section.");
    }

    public function delete($id)
    {
        $route = $this->routeModel
            ->withOrigin()
            ->find($id);
        $this->unassignVehiclesFromRoutes([(int) $id]);
        if ($this->routeModel->delete($id)) {
            $routeDesc = '';
            if ($route) {
                $routeDesc = strtoupper($route['origin'] ?? '') . ' → ' . strtoupper($route['destination'] ?? '');
                $this->logActivity('Delete route', $routeDesc . '.');
            }
            $this->broadcastUpdate('fare_update', ['action' => 'route_deleted', 'id' => (int) $id]);
            $msg = $routeDesc ? "Fare route \"{$routeDesc}\" deleted successfully." : 'Fare route deleted successfully.';
            return redirect()->back()->with('success', $msg);
        }
        return redirect()->back()->with('error', 'Failed to delete route.');
    }

    // ──────────────────────────────────────────────
    //  Discount Management
    // ──────────────────────────────────────────────

    /**
     * Update a discount record (percentage + label).
     */
    public function updateDiscount($id)
    {
        $discount = $this->discountModel->find($id);

        if (!$discount) {
            return redirect()->back()->with('error', 'Discount record not found.');
        }

        $rules = [
            'discount_percent' => 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
            'label'            => 'required|min_length[2]|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $updatedData = [
            'discount_percent' => $this->request->getPost('discount_percent'),
            'label'            => $this->request->getPost('label'),
            'is_active'        => $this->request->getPost('is_active') ? 1 : 0,
        ];

        if (abs((float) ($discount['discount_percent'] ?? 0) - (float) $updatedData['discount_percent']) <= 0.00001
            && $this->inputsUnchanged([
                'label'     => $discount['label'] ?? '',
                'is_active' => $discount['is_active'] ?? '',
            ], [
                'label'     => $updatedData['label'],
                'is_active' => $updatedData['is_active'],
            ])
        ) {
            return $this->noChangesResponse();
        }

        $this->discountModel->update($id, $updatedData);
        $this->recalculateFaresForDiscount(array_merge($discount, $updatedData));

        $this->logActivity('Update discount', $discount['type'] . ' discount updated to ' . $this->request->getPost('discount_percent') . '%.');
        $this->broadcastUpdate('fare_update', ['action' => 'discount_updated', 'id' => (int) $id]);

        return redirect()->back()->with('success', 'Discount updated successfully.');
    }

    /**
     * Add a new discount type (for custom types beyond PWD/Senior/Student).
     */
    public function storeDiscount()
    {
        $rules = [
            'terminal_id'      => 'required|integer|is_not_unique[terminals.id]',
            'type'             => 'required|min_length[2]|max_length[50]|alpha_dash',
            'label'            => 'required|min_length[2]|max_length[100]',
            'discount_percent' => 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/fares')->with('errors', $this->validator->getErrors());
        }

        $type       = strtolower(trim($this->request->getPost('type')));
        $terminalId = (int) $this->request->getPost('terminal_id');

        // Prevent duplicate types within the same terminal.
        $existing = $this->discountModel->where('terminal_id', $terminalId)->where('type', $type)->first();
        if ($existing) {
            return redirect()->to('/fares')->with('error', 'A discount with type "' . $type . '" already exists for this terminal. Please edit the existing one instead.');
        }

        $discountId = $this->discountModel->insert([
            'terminal_id'      => $terminalId,
            'type'             => $type,
            'label'            => $this->request->getPost('label'),
            'discount_percent' => $this->request->getPost('discount_percent'),
            'is_active'        => 1,
        ]);

        $discount = $this->discountModel->find($discountId);
        if ($discount) {
            $this->recalculateFaresForDiscount($discount);
        }

        $this->logActivity('Add discount', $this->request->getPost('label') . ' discount added at ' . $this->request->getPost('discount_percent') . '%.');
        $this->broadcastUpdate('fare_update', ['action' => 'discount_created']);

        return redirect()->to('/fares')->with('success', 'Discount added successfully.');
    }

    /**
     * Delete a discount record.
     */
    public function deleteDiscount($id)
    {
        $discount = $this->discountModel->find($id);

        if (!$discount) {
            return redirect()->to('/fares')->with('error', 'Discount record not found.');
        }

        if ($discount['type'] === 'regular') {
            return redirect()->to('/fares')->with('error', 'Regular fare cannot be deleted.');
        }

        $this->discountModel->delete($id);

        $this->logActivity('Delete discount', $discount['type'] . ' discount (' . $discount['label'] . ') deleted.');
        $this->broadcastUpdate('fare_update', ['action' => 'discount_deleted', 'id' => (int) $id]);

        return redirect()->to('/fares')->with('success', 'Discount deleted successfully.');
    }
}
