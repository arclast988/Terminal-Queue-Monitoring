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
                    'items'         => []
                ];
            }
            $groupedRoutes[$key]['items'][] = $route;
        }

        $data = [
            'title'         => 'Manage Routes',
            'routes'        => $routes,
            'groupedRoutes' => $groupedRoutes
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
        $selectedFares = $this->request->getPost('fares');
        if (is_array($selectedFares)) {
            $terminalId  = $this->request->getPost('terminal_id');
            $terminal    = $this->terminalModel->find($terminalId);
            $origin      = strtoupper(trim($terminal['name'] ?? ''));
            $destination = strtoupper(trim($this->request->getPost('destination')));

            if (empty($destination) || strlen($destination) < 2) {
                return redirect()->back()->withInput()->with('error', 'Please provide a valid destination (at least 2 characters).');
            }

            $added = 0;
            foreach ($selectedFares as $vType => $fareVal) {
                if ($this->isActiveVehicleType((string) $vType) && $fareVal !== '' && $fareVal !== null && (float)$fareVal > 0) {
                    $fareFloat = (float) $fareVal;
                    $existing = $this->routeModel->where('terminal_id', $terminalId)
                                                 ->where('destination', $destination)
                                                 ->where('vehicle_type', $vType)
                                                 ->first();
                    if ($existing) {
                        $this->replaceRouteFares((int)$existing['id'], (int)$terminalId, $fareFloat);
                    } else {
                        $insertedId = $this->routeModel->insert([
                            'destination'  => $destination,
                            'terminal_id'  => $terminalId,
                            'vehicle_type' => $vType,
                        ]);
                        if ($insertedId) {
                            $this->replaceRouteFares((int)$insertedId, (int)$terminalId, $fareFloat);
                            (new \App\Models\UserRouteModel())->autoAssignNewRouteToStaff((int)$insertedId, (int)$terminalId, $destination);
                        }
                    }
                    $added++;
                }
            }

            if ($added > 0) {
                $this->logActivity('Create route', "$origin → $destination ($added vehicle type(s)).");
                $this->broadcastUpdate('fare_update', ['action' => 'route_created']);
                return redirect()->to('/admin/routes')->with('success', "Route ($origin → $destination) saved with $added vehicle type(s).");
            } else {
                return redirect()->back()->withInput()->with('error', 'Please enter a valid fare for at least one vehicle type.');
            }
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
        $fare        = $this->request->getPost('fare');

        $existing = $this->routeModel->where('terminal_id', $terminalId)
                                     ->where('destination', $destination)
                                     ->where('vehicle_type', $vehicleType)
                                     ->first();

        if ($existing) {
            return redirect()->back()->withInput()->with('error', "This route ($origin → $destination) already exists for " . ucfirst($vehicleType) . ". Please edit the existing one instead of adding a new one.");
        }

        $insertedId = $this->routeModel->insert([
            'destination'  => $destination,
            'terminal_id'  => $terminalId,
            'vehicle_type' => $vehicleType,
        ]);

        if (!$insertedId) {
            return redirect()->back()->withInput()->with('error', 'Failed to add route.');
        }

        $this->replaceRouteFares((int) $insertedId, (int) $terminalId, (float) $fare);

        $this->logActivity('Create route', "$origin → $destination ($vehicleType, ₱$fare).");
        $this->broadcastUpdate('fare_update', ['action' => 'route_created']);

        return redirect()->to('/admin/routes')->with('success', 'New route and fare added successfully.');
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

        // No-change guard: group rename or any per-type fare difference counts.
        $selectedFaresPreview = $this->request->getPost('fares');
        if (is_array($selectedFaresPreview)
            && (string) $oldTerminalId === (string) $terminalId
            && (string) $oldDestination === (string) $destination
        ) {
            $groupUnchanged = true;
            foreach ($this->activeVehicleTypeSlugs() as $vType) {
                $fareVal = $selectedFaresPreview[$vType] ?? null;
                $hasFare = ($fareVal !== '' && $fareVal !== null && (float) $fareVal > 0);
                $existing = $this->routeModel->where('terminal_id', $terminalId)
                                             ->where('destination', $destination)
                                             ->where('vehicle_type', $vType)
                                             ->first();
                if ($hasFare && !$existing) {
                    $groupUnchanged = false;
                    break;
                }
                if (!$hasFare && $existing) {
                    $groupUnchanged = false;
                    break;
                }
                if ($hasFare && $existing && abs($this->getRegularFare((int) $existing['id']) - (float) $fareVal) > 0.00001) {
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

        // Update, insert, or delete vehicle types based on selected fares
        $selectedFares = $this->request->getPost('fares');
        $added = 0;
        if (is_array($selectedFares)) {
            foreach ($this->activeVehicleTypeSlugs() as $vType) {
                $fareVal = $selectedFares[$vType] ?? null;
                $hasFare = ($fareVal !== '' && $fareVal !== null && (float)$fareVal > 0);

                $existing = $this->routeModel->where('terminal_id', $terminalId)
                                             ->where('destination', $destination)
                                             ->where('vehicle_type', $vType)
                                             ->first();

                if ($hasFare) {
                    $fareFloat = (float) $fareVal;
                    if ($existing) {
                        $this->replaceRouteFares((int)$existing['id'], (int)$terminalId, $fareFloat);
                    } else {
                        $insertedId = $this->routeModel->insert([
                            'destination'  => $destination,
                            'terminal_id'  => $terminalId,
                            'vehicle_type' => $vType,
                        ]);
                        if ($insertedId) {
                            $this->replaceRouteFares((int)$insertedId, (int)$terminalId, $fareFloat);
                            (new \App\Models\UserRouteModel())->autoAssignNewRouteToStaff((int)$insertedId, (int)$terminalId, $destination);
                        }
                    }
                    $added++;
                } else {
                    if ($existing) {
                        try {
                            $this->unassignVehiclesFromRoutes([(int) $existing['id']]);
                            $this->routeModel->delete($existing['id']);
                        } catch (\Throwable $e) {
                            // If delete fails due to dependencies (like queue entries), we just keep it
                            log_message('warning', 'Failed to delete route variant during group update: {msg}', ['msg' => $e->getMessage()]);
                        }
                    }
                }
            }
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

        return redirect()->to('/admin/routes')->with('success', 'Route group deleted successfully.');
    }

    public function delete($id)
    {
        $route = $this->routeModel
            ->withOrigin()
            ->find($id);
        $this->unassignVehiclesFromRoutes([(int) $id]);
        if ($this->routeModel->delete($id)) {
            if ($route) {
                $this->logActivity('Delete route', $route['origin'] . ' → ' . $route['destination'] . '.');
            }
            $this->broadcastUpdate('fare_update', ['action' => 'route_deleted', 'id' => (int) $id]);
            return redirect()->back()->with('success', 'Route deleted successfully.');
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
