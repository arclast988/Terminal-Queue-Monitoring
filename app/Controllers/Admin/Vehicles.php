<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VehicleModel;
use App\Models\RouteModel;
use App\Models\VehicleTypeModel;
use App\Services\CloudinaryService;

class Vehicles extends BaseController
{
    protected $vehicleModel;
    protected $routeModel;
    protected $vehicleTypeModel;

    public function __construct()
    {
        $this->vehicleModel = new VehicleModel();
        $this->routeModel = new RouteModel();
        $this->vehicleTypeModel = new VehicleTypeModel();
    }

    public function index()
    {
        // Join routes to get assigned route info - only active routes display as assigned
        $vehicles = $this->vehicleModel
            ->select('vehicles.*, terminals.name as route_origin, routes.destination as route_destination, routes.terminal_id as route_terminal_id, routes.vehicle_type as route_vehicle_type, routes.status as route_status')
            ->join('routes', "routes.id = vehicles.default_route_id AND routes.status = 'active'", 'left')
            ->join('terminals', 'terminals.id = routes.terminal_id', 'left')
            ->orderBy('vehicles.created_at', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Vehicle Register',
            'vehicles' => $vehicles,
            'routes' => $this->routeModel->withActiveFare()->orderBy('destination', 'ASC')->findAll(),
            'vehicleTypes' => $this->vehicleTypeModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll(),
        ];

        return view('admin/vehicles/index', $data);
    }

    /**
     * AJAX endpoint for real-time duplicate plate detection and length validation.
     */
    public function checkPlate()
    {
        $plate = strtoupper(trim((string)$this->request->getGet('plate')));
        $excludeId = (int)$this->request->getGet('exclude_id');

        if ($plate === '') {
            return $this->response->setJSON([
                'valid'     => false,
                'available' => true,
                'status'    => 'empty',
                'message'   => '',
            ]);
        }

        if (strlen($plate) < 4) {
            return $this->response->setJSON([
                'valid'     => false,
                'available' => false,
                'status'    => 'too_short',
                'message'   => 'Plate number must be at least 4 characters.',
            ]);
        }

        if (strlen($plate) > 20) {
            return $this->response->setJSON([
                'valid'     => false,
                'available' => false,
                'status'    => 'too_long',
                'message'   => 'Plate number cannot exceed 20 characters.',
            ]);
        }

        $query = $this->vehicleModel->where('LOWER(plate_number)', strtolower($plate));
        if ($excludeId > 0) {
            $query->where('id !=', $excludeId);
        }

        $existing = $query->first();

        if ($existing) {
            return $this->response->setJSON([
                'valid'     => true,
                'available' => false,
                'status'    => 'duplicate',
                'message'   => 'This plate number is already registered. Please enter a different plate number.',
            ]);
        }

        return $this->response->setJSON([
            'valid'     => true,
            'available' => true,
            'status'    => 'available',
            'message'   => 'Plate number is available.',
        ]);
    }

    public function store()
    {
        $rawPlate = strtoupper(trim((string)($this->request->getPost('plate_number') ?? '')));
        $rawOperator = strtoupper(trim((string)($this->request->getPost('operator_name') ?? '')));
        $_POST['plate_number'] = $rawPlate;
        $_POST['operator_name'] = $rawOperator;

        $rules = [
            'plate_number'  => [
                'rules'  => 'required|min_length[4]|max_length[20]|is_unique[vehicles.plate_number]',
                'errors' => [
                    'required'   => 'Please enter a plate number.',
                    'min_length' => 'Plate number must be at least 4 characters.',
                    'max_length' => 'Plate number cannot exceed 20 characters.',
                    'is_unique'  => 'This plate number is already registered. Please enter a different plate number.',
                ],
            ],
            'operator_name' => [
                'rules'  => 'required|min_length[2]|max_length[100]',
                'errors' => [
                    'required'   => 'Please enter the operator name.',
                    'min_length' => 'Operator name must be at least 2 characters.',
                    'max_length' => 'Operator name cannot exceed 100 characters.',
                ],
            ],
            'driver_name'   => [
                'rules'  => 'required|min_length[2]|max_length[100]',
                'errors' => [
                    'required'   => 'Please enter the driver name.',
                    'min_length' => 'Driver name must be at least 2 characters.',
                    'max_length' => 'Driver name cannot exceed 100 characters.',
                ],
            ],
            'type'          => [
                'rules'  => 'required|max_length[50]',
                'errors' => [
                    'required'   => 'Please select a vehicle type.',
                    'max_length' => 'Vehicle type exceeds maximum allowed length.',
                ],
            ],
            'capacity'      => [
                'rules'  => 'required|integer|greater_than[0]',
                'errors' => [
                    'required'     => 'Please enter passenger capacity.',
                    'integer'      => 'Capacity must be a valid number.',
                    'greater_than' => 'Capacity must be at least 1 passenger.',
                ],
            ],
            'status'        => [
                'rules'  => 'permit_empty|in_list[active,maintenance,archived]',
                'errors' => [
                    'in_list' => 'Status must be active, maintenance, or archived.',
                ],
            ],
            'dispatch_order' => ['rules' => 'permit_empty|integer|greater_than[0]|less_than_equal_to[9999]'],
            'route_id'      => [
                'rules'  => 'required|integer',
                'errors' => [
                    'required' => 'Please select a destination route.',
                    'integer'  => 'Please select a valid destination route.',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Additional case-insensitive plate uniqueness check
        $existing = $this->vehicleModel
            ->where('LOWER(plate_number)', strtolower($rawPlate))
            ->first();
        if ($existing) {
            return redirect()->back()->withInput()->with('errors', [
                'plate_number' => 'This plate number is already registered. Please enter a different plate number.'
            ]);
        }

        $routeId = $this->request->getPost('route_id');
        $route = $this->routeModel->withActiveFare()->find($routeId);
        if (!$route) {
            return redirect()->back()->withInput()->with('error', 'The selected route does not have an assigned fare. Please assign a fare in the Add Fare section before registering vehicles to it.');
        }

        // Validate vehicle type matches route type
        $vehicleType = $this->request->getPost('type');
        if (!$this->vehicleTypeModel->where('slug', $vehicleType)->where('is_active', 1)->first()) {
            return redirect()->back()->withInput()->with('error', 'Please select a valid active vehicle type.');
        }
        if ($route && $route['vehicle_type'] !== $vehicleType) {
            return redirect()->back()->withInput()->with('error', 'Vehicle type (' . ucfirst($vehicleType) . ') must match the route type (' . ucfirst($route['vehicle_type']) . ').');
        }

        // Driver and Operator must not be the same person
        $driverName = trim($this->request->getPost('driver_name'));
        $operatorName = $rawOperator;
        if (strtolower($driverName) === strtolower($operatorName)) {
            return redirect()->back()->withInput()->with('error', 'Driver and Operator must not be the same person.');
        }

        $status = $this->request->getPost('status');
        if (!in_array($status, ['active', 'maintenance'], true)) {
            $status = 'active';
        }

        // Handle vehicle photo upload
        $photoRelPath = null;
        $photoFile = $this->request->getFile('photo');
        if ($photoFile && $photoFile->getError() !== UPLOAD_ERR_NO_FILE) {
            if (!$photoFile->isValid()) {
                return redirect()->back()->withInput()->with('error', 'Photo upload failed: ' . $photoFile->getErrorString());
            }
            $ext = $this->validatedImageExtension($photoFile);
            if ($ext === null) {
                return redirect()->back()->withInput()->with('error', 'Please upload a valid image file (JPG, PNG, WEBP).');
            }

            if ($photoFile->getSize() > 5 * 1024 * 1024) {
                return redirect()->back()->withInput()->with('error', 'The uploaded photo exceeds the 5MB size limit.');
            }

            $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'vehicles';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0755, true);
            }
            $safePlate = preg_replace('/[^a-zA-Z0-9]/', '_', $rawPlate);
            $newFilename = 'veh_' . strtolower($safePlate) . '_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
            if ($photoFile->move($uploadDir, $newFilename)) {
                $photoRelPath = 'uploads/vehicles/' . $newFilename;
                $cloudinary = new CloudinaryService();
                if ($cloudinary->isConfigured()) {
                    $cRes = $cloudinary->uploadLocalFile($uploadDir . DIRECTORY_SEPARATOR . $newFilename, 'vehicles', 'veh_' . strtolower($safePlate) . '_' . time());
                    if ($cRes && !empty($cRes['secure_url'])) {
                        $photoRelPath = $cRes['secure_url'];
                    }
                }
            } else {
                return redirect()->back()->withInput()->with('error', 'Failed to save vehicle photo to upload directory.');
            }
        }

        if (!$this->vehicleModel->saveInDispatchOrder([
            'plate_number'     => $rawPlate,
            'operator_name'    => $rawOperator,
            'owner_name'       => $rawOperator,
            'driver_name'      => $driverName,
            'type'             => $vehicleType,
            'capacity'         => $this->request->getPost('capacity'),
            'status'           => $status,
            'default_route_id' => $routeId,
            'photo'            => $photoRelPath,
            'dispatch_order'   => $this->request->getPost('dispatch_order'),
        ])) {
            return redirect()->back()->withInput()->with('error', 'The vehicle could not be saved. Please try again.');
        }

        $routeLabel = $route ? (strtoupper($route['origin']) . ' → ' . strtoupper($route['destination'])) : 'N/A';
        $this->logActivity('Assign vehicle to route', 'Registered vehicle ' . $rawPlate . ' (' . $vehicleType . ') - Operator: ' . $rawOperator . ' - Driver: ' . $driverName . ' - Route: ' . $routeLabel);
        $this->broadcastUpdate('queue_update', ['action' => 'vehicle_created']);

        return redirect()->to('/admin/vehicles')->with('success', 'Vehicle registered successfully.');
    }

    public function edit($id)
    {
        $vehicle = $this->vehicleModel->find($id);
        if (!$vehicle) {
            return redirect()->to('/admin/vehicles')->with('error', 'Vehicle not found.');
        }

        $data = [
            'title' => 'Edit Vehicle',
            'vehicle' => $vehicle,
            'routes' => $this->routeModel->withActiveFare()->orderBy('destination', 'ASC')->findAll(),
            'vehicleTypes' => $this->vehicleTypeModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll(),
        ];

        return view('admin/vehicles/edit', $data);
    }

    public function update($id)
    {
        $vehicle = $this->vehicleModel->find($id);
        if (!$vehicle) {
            return redirect()->to('/admin/vehicles')->with('error', 'Vehicle not found.');
        }

        $rawPlate = strtoupper(trim((string)($this->request->getPost('plate_number') ?? '')));
        $rawOperator = strtoupper(trim((string)($this->request->getPost('operator_name') ?? '')));
        $_POST['plate_number'] = $rawPlate;
        $_POST['operator_name'] = $rawOperator;

        $rules = [
            'plate_number'  => [
                'rules'  => 'required|min_length[4]|max_length[20]|is_unique[vehicles.plate_number,id,' . $id . ']',
                'errors' => [
                    'required'   => 'Please enter a plate number.',
                    'min_length' => 'Plate number must be at least 4 characters.',
                    'max_length' => 'Plate number cannot exceed 20 characters.',
                    'is_unique'  => 'This plate number is already registered to another vehicle.',
                ],
            ],
            'operator_name' => [
                'rules'  => 'required|min_length[2]|max_length[100]',
                'errors' => [
                    'required'   => 'Please enter the operator name.',
                    'min_length' => 'Operator name must be at least 2 characters.',
                    'max_length' => 'Operator name cannot exceed 100 characters.',
                ],
            ],
            'driver_name'   => [
                'rules'  => 'required|min_length[2]|max_length[100]',
                'errors' => [
                    'required'   => 'Please enter the driver name.',
                    'min_length' => 'Driver name must be at least 2 characters.',
                    'max_length' => 'Driver name cannot exceed 100 characters.',
                ],
            ],
            'type'          => [
                'rules'  => 'required|max_length[50]',
                'errors' => [
                    'required'   => 'Please select a vehicle type.',
                    'max_length' => 'Vehicle type exceeds maximum allowed length.',
                ],
            ],
            'capacity'      => [
                'rules'  => 'required|integer|greater_than[0]',
                'errors' => [
                    'required'     => 'Please enter passenger capacity.',
                    'integer'      => 'Capacity must be a valid number.',
                    'greater_than' => 'Capacity must be at least 1 passenger.',
                ],
            ],
            'status'        => [
                'rules'  => 'required|in_list[active,maintenance,archived]',
                'errors' => [
                    'required' => 'Please select a vehicle status.',
                    'in_list'  => 'Vehicle status must be active, maintenance, or archived.',
                ],
            ],
            'dispatch_order' => ['rules' => 'permit_empty|integer|greater_than[0]|less_than_equal_to[9999]'],
            'route_id'      => [
                'rules'  => 'required|integer',
                'errors' => [
                    'required' => 'Please select a destination route.',
                    'integer'  => 'Please select a valid destination route.',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Additional case-insensitive plate uniqueness check
        $existing = $this->vehicleModel
            ->where('LOWER(plate_number)', strtolower($rawPlate))
            ->where('id !=', $id)
            ->first();
        if ($existing) {
            return redirect()->back()->withInput()->with('errors', [
                'plate_number' => 'This plate number is already registered to another vehicle.'
            ]);
        }

        $routeId = $this->request->getPost('route_id');
        $route = $this->routeModel->withActiveFare()->find($routeId);
        if (!$route) {
            return redirect()->back()->withInput()->with('error', 'The selected route does not have an assigned fare. Please assign a fare in the Add Fare section before assigning vehicles to it.');
        }
        $vehicleType = $this->request->getPost('type');

        if (!$this->vehicleTypeModel->where('slug', $vehicleType)->where('is_active', 1)->first()) {
            return redirect()->back()->withInput()->with('error', 'Please select a valid active vehicle type.');
        }

        if ($route && $route['vehicle_type'] !== $vehicleType) {
            return redirect()->back()->withInput()->with('error', 'Vehicle type (' . ucfirst($vehicleType) . ') must match the route type (' . ucfirst($route['vehicle_type']) . ').');
        }

        // Driver and Operator must not be the same person
        $driverName = trim($this->request->getPost('driver_name'));
        $operatorName = $rawOperator;
        if (strtolower($driverName) === strtolower($operatorName)) {
            return redirect()->back()->withInput()->with('error', 'Driver and Operator must not be the same person.');
        }

        // Handle photo upload / removal
        $photoRelPath = $vehicle['photo'] ?? null;
        $photoChanged = false;
        $cloudinary = new CloudinaryService();

        if ($this->request->getPost('remove_photo') == '1') {
            if (!empty($photoRelPath)) {
                if (str_starts_with($photoRelPath, 'http')) {
                    $cloudinary->deleteImage($photoRelPath);
                } elseif (file_exists(FCPATH . $photoRelPath)) {
                    @unlink(FCPATH . $photoRelPath);
                }
            }
            $photoRelPath = null;
            $photoChanged = true;
        }

        $photoFile = $this->request->getFile('photo');
        if ($photoFile && $photoFile->getError() !== UPLOAD_ERR_NO_FILE) {
            if (!$photoFile->isValid()) {
                return redirect()->back()->withInput()->with('error', 'Photo upload failed: ' . $photoFile->getErrorString());
            }
            $ext = $this->validatedImageExtension($photoFile);
            if ($ext === null) {
                return redirect()->back()->withInput()->with('error', 'Please upload a valid image file (JPG, PNG, WEBP).');
            }

            if ($photoFile->getSize() > 5 * 1024 * 1024) {
                return redirect()->back()->withInput()->with('error', 'The uploaded photo exceeds the 5MB size limit.');
            }

            $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'vehicles';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0755, true);
            }
            $safePlate = preg_replace('/[^a-zA-Z0-9]/', '_', $rawPlate);
            $newFilename = 'veh_' . strtolower($safePlate) . '_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
            if ($photoFile->move($uploadDir, $newFilename)) {
                if (!empty($vehicle['photo'])) {
                    if (str_starts_with($vehicle['photo'], 'http')) {
                        $cloudinary->deleteImage($vehicle['photo']);
                    } elseif (file_exists(FCPATH . $vehicle['photo'])) {
                        @unlink(FCPATH . $vehicle['photo']);
                    }
                }
                $photoRelPath = 'uploads/vehicles/' . $newFilename;
                if ($cloudinary->isConfigured()) {
                    $cRes = $cloudinary->uploadLocalFile($uploadDir . DIRECTORY_SEPARATOR . $newFilename, 'vehicles', 'veh_' . strtolower($safePlate) . '_' . time());
                    if ($cRes && !empty($cRes['secure_url'])) {
                        $photoRelPath = $cRes['secure_url'];
                    }
                }
                $photoChanged = true;
            } else {
                return redirect()->back()->withInput()->with('error', 'Failed to save vehicle photo to upload directory.');
            }
        }

        if (!$photoChanged && $this->inputsUnchanged([
            'plate_number'     => strtoupper(trim((string) ($vehicle['plate_number'] ?? ''))),
            'operator_name'    => strtoupper(trim((string) ($vehicle['operator_name'] ?? ''))),
            'driver_name'      => trim((string) ($vehicle['driver_name'] ?? '')),
            'type'             => $vehicle['type'] ?? '',
            'capacity'         => $vehicle['capacity'] ?? '',
            'status'           => $vehicle['status'] ?? '',
            'default_route_id' => $vehicle['default_route_id'] ?? $vehicle['route_id'] ?? '',
            'dispatch_order'   => $vehicle['dispatch_order'] ?? '',
        ], [
            'plate_number'     => $rawPlate,
            'operator_name'    => $rawOperator,
            'driver_name'      => $driverName,
            'type'             => $vehicleType,
            'capacity'         => $this->request->getPost('capacity'),
            'status'           => $this->request->getPost('status'),
            'default_route_id' => $routeId,
            'dispatch_order'   => $this->request->getPost('dispatch_order') ?? ($vehicle['dispatch_order'] ?? ''),
        ])) {
            return $this->noChangesResponse();
        }

        if (!$this->vehicleModel->saveInDispatchOrder([
            'plate_number'     => $rawPlate,
            'operator_name'    => $rawOperator,
            'owner_name'       => $rawOperator,
            'driver_name'      => $driverName,
            'type'             => $vehicleType,
            'capacity'         => $this->request->getPost('capacity'),
            'status'           => $this->request->getPost('status'),
            'default_route_id' => $routeId,
            'photo'            => $photoRelPath,
            'dispatch_order'   => $this->request->getPost('dispatch_order') ?? ($vehicle['dispatch_order'] ?? ''),
        ], (int) $id)) {
            return redirect()->back()->withInput()->with('error', 'The vehicle could not be saved. Please try again.');
        }

        // Synchronize active queue snapshots so live boards and dispatch cards reflect changes immediately
        $queueModel = new \App\Models\QueueModel();
        $queueModel->where('vehicle_id', $id)
            ->whereIn('status', ['waiting', 'boarding'])
            ->set([
                'plate_number'  => $rawPlate,
                'operator_name' => $rawOperator,
                'driver_name'   => $driverName,
            ])
            ->update();

        // Log route change details if route was reassigned
        $oldRouteId = $vehicle['default_route_id'];
        if ((string) $oldRouteId !== (string) $routeId) {
            $oldRoute = $oldRouteId ? $this->routeModel->withOrigin()->find($oldRouteId) : null;
            $oldLabel = $oldRoute ? (strtoupper($oldRoute['origin']) . ' → ' . strtoupper($oldRoute['destination'])) : 'None';
            $newLabel = $route ? (strtoupper($route['origin']) . ' → ' . strtoupper($route['destination'])) : 'None';
            $this->logActivity('Reassign vehicle route', 'Reassigned ' . $rawPlate . ' from ' . $oldLabel . ' to ' . $newLabel);
        } else {
            $this->logActivity('Update vehicle', 'Updated vehicle ' . $rawPlate);
        }

        $this->broadcastUpdate('queue_update', [
            'action'           => 'vehicle_updated',
            'id'               => (int) $id,
            'plate_number'     => $rawPlate,
            'vehicle_type'     => $vehicleType,
            'photo'            => $photoRelPath ? base_url($photoRelPath) : null,
            'has_custom_photo' => !empty($photoRelPath),
        ]);

        return redirect()->to('/admin/vehicles')->with('success', 'Vehicle updated successfully.');
    }

    public function delete($id)
    {
        $vehicle = $this->vehicleModel->find($id);
        if (!$vehicle) {
            return redirect()->to('/admin/vehicles')->with('error', 'Vehicle not found.');
        }

        if (!empty($vehicle['photo'])) {
            if (str_starts_with($vehicle['photo'], 'http')) {
                (new CloudinaryService())->deleteImage($vehicle['photo']);
            } elseif (file_exists(FCPATH . $vehicle['photo'])) {
                @unlink(FCPATH . $vehicle['photo']);
            }
        }

        $redirectTab = $this->request->getPost('redirect_tab') ?? $this->request->getGet('tab') ?? 'archived';

        if ($this->vehicleModel->delete($id)) {
            $this->logActivity('Delete vehicle', 'Permanently deleted vehicle ' . $vehicle['plate_number'] . '.');
            $this->broadcastUpdate('queue_update', ['action' => 'vehicle_deleted', 'id' => (int) $id]);
            return redirect()->to('/admin/vehicles' . ($redirectTab === 'archived' ? '?tab=archived' : ''))->with('success', 'Vehicle "' . $vehicle['plate_number'] . '" permanently deleted.');
        }
        return redirect()->to('/admin/vehicles' . ($redirectTab === 'archived' ? '?tab=archived' : ''))->with('error', 'Failed to delete vehicle.');
    }

    public function deactivate($id)
    {
        $vehicle = $this->vehicleModel->find($id);
        if (!$vehicle) {
            return redirect()->to('/admin/vehicles')->with('error', 'Vehicle not found.');
        }

        $this->vehicleModel->update($id, ['status' => 'archived']);

        // Cancel any active trips for this archived vehicle so it doesn't stay stranded in the live queue
        $queueModel = new \App\Models\QueueModel();
        $activeTrips = $queueModel->where('vehicle_id', $id)
            ->whereIn('status', ['waiting', 'boarding'])
            ->findAll();
        if (!empty($activeTrips)) {
            $queueModel->where('vehicle_id', $id)
                ->whereIn('status', ['waiting', 'boarding'])
                ->set(['status' => 'canceled'])
                ->update();
            $queueModel->reorderByDeparture();
        }

        $this->logActivity('Deactivate vehicle', 'Deactivated vehicle ' . $vehicle['plate_number'] . '.');
        $this->broadcastUpdate('queue_update', ['action' => 'vehicle_updated', 'id' => (int) $id]);

        $redirectTab = $this->request->getPost('redirect_tab') ?? $this->request->getGet('tab') ?? 'active';
        return redirect()->to('/admin/vehicles' . ($redirectTab === 'archived' ? '?tab=archived' : ''))->with('success', 'Vehicle "' . $vehicle['plate_number'] . '" deactivated and moved to archive.');
    }

    public function activate($id)
    {
        $vehicle = $this->vehicleModel->find($id);
        if (!$vehicle) {
            return redirect()->to('/admin/vehicles')->with('error', 'Vehicle not found.');
        }

        $this->vehicleModel->update($id, ['status' => 'active']);
        $this->logActivity('Activate vehicle', 'Activated vehicle ' . $vehicle['plate_number'] . '.');
        $this->broadcastUpdate('queue_update', ['action' => 'vehicle_updated', 'id' => (int) $id]);

        $redirectTab = $this->request->getPost('redirect_tab') ?? $this->request->getGet('tab') ?? 'archived';
        return redirect()->to('/admin/vehicles' . ($redirectTab === 'archived' ? '?tab=archived' : ''))->with('success', 'Vehicle "' . $vehicle['plate_number'] . '" activated successfully.');
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
            return redirect()->to('/admin/vehicles' . ($redirectTab ? '?tab=' . $redirectTab : ''))->with('error', 'No vehicles selected for bulk action.');
        }

        $vehicles = $this->vehicleModel->whereIn('id', $ids)->findAll();
        if (empty($vehicles)) {
            return redirect()->to('/admin/vehicles' . ($redirectTab ? '?tab=' . $redirectTab : ''))->with('error', 'Selected vehicles not found.');
        }

        $count = 0;
        $queueModel = new \App\Models\QueueModel();

        if ($action === 'deactivate') {
            foreach ($vehicles as $v) {
                if (($v['status'] ?? 'active') !== 'archived') {
                    $this->vehicleModel->update($v['id'], ['status' => 'archived']);
                    $activeTrips = $queueModel->where('vehicle_id', $v['id'])
                        ->whereIn('status', ['waiting', 'boarding'])
                        ->findAll();
                    if (!empty($activeTrips)) {
                        $queueModel->where('vehicle_id', $v['id'])
                            ->whereIn('status', ['waiting', 'boarding'])
                            ->set(['status' => 'canceled'])
                            ->update();
                    }
                    $count++;
                }
            }
            $queueModel->reorderByDeparture();
            $this->logActivity('Bulk deactivate vehicles', "Deactivated $count vehicle(s) and moved to archive.");
            $this->broadcastUpdate('queue_update', ['action' => 'bulk_vehicle_updated']);
            return redirect()->to('/admin/vehicles?tab=active')->with('success', "$count vehicle(s) deactivated and moved to archive.");
        } elseif ($action === 'activate') {
            foreach ($vehicles as $v) {
                if (($v['status'] ?? 'active') === 'archived') {
                    $this->vehicleModel->update($v['id'], ['status' => 'active']);
                    $count++;
                }
            }
            $this->logActivity('Bulk activate vehicles', "Activated $count vehicle(s).");
            $this->broadcastUpdate('queue_update', ['action' => 'bulk_vehicle_updated']);
            return redirect()->to('/admin/vehicles?tab=archived')->with('success', "$count vehicle(s) activated successfully.");
        } elseif ($action === 'delete') {
            foreach ($vehicles as $v) {
                if (($v['status'] ?? 'active') === 'archived') {
                    if (!empty($v['photo'])) {
                        if (str_starts_with($v['photo'], 'http')) {
                            $cloudinary->deleteImage($v['photo']);
                        } elseif (file_exists(FCPATH . $v['photo'])) {
                            @unlink(FCPATH . $v['photo']);
                        }
                    }
                    $this->vehicleModel->delete($v['id']);
                    $count++;
                }
            }
            $this->logActivity('Bulk delete vehicles', "Permanently deleted $count archived vehicle(s).");
            $this->broadcastUpdate('queue_update', ['action' => 'bulk_vehicle_deleted']);
            return redirect()->to('/admin/vehicles?tab=archived')->with('success', "$count archived vehicle(s) permanently deleted.");
        }

        return redirect()->to('/admin/vehicles' . ($redirectTab ? '?tab=' . $redirectTab : ''))->with('error', 'Invalid bulk action.');
    }
}

