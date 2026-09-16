<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VehicleTypeModel;

class VehicleTypes extends BaseController
{
    public function store()
    {
        $name = trim((string) $this->request->getPost('name'));
        $slug = strtolower((string) preg_replace('/[^a-z0-9]+/i', '_', $name));
        $slug = trim($slug, '_');

        if ($name === '' || strlen($name) < 2 || strlen($name) > 80 || $slug === '' || strlen($slug) > 50) {
            return redirect()->to('/admin/vehicles')->with('error', 'Enter a vehicle type name between 2 and 80 characters.');
        }

        $types = new VehicleTypeModel();
        if ($types->where('slug', $slug)->first()) {
            return redirect()->to('/admin/vehicles')->with('error', 'That vehicle type already exists.');
        }

        // Color handling
        $color = trim((string) $this->request->getPost('color'));
        if ($color === '' || ! preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            // Pick an unused distinct color from the palette
            $palette = ['#c62828', '#1565c0', '#2e7d32', '#ea580c', '#7c3aed', '#0891b2', '#e11d48', '#ca8a04', '#475569', '#059669', '#4f46e5', '#db2777'];
            $usedColors = array_filter(array_column($types->findAll(), 'color'));
            $available = array_values(array_diff($palette, $usedColors));
            $color = ! empty($available) ? $available[0] : vehicle_type_color($slug);
        }

        // Icon handling
        $icon = trim((string) $this->request->getPost('icon'));
        if ($icon === '') {
            $icon = vehicle_type_icon($slug);
        }

        // Photo handling
        $photoRelPath = null;
        $photoFile = $this->request->getFile('photo');
        if ($photoFile && $photoFile->getError() !== UPLOAD_ERR_NO_FILE) {
            if (! $photoFile->isValid()) {
                return redirect()->back()->with('error', 'Photo upload failed: ' . $photoFile->getErrorString());
            }
            $allowedMimes = ['image/jpeg', 'image/jpg', 'image/pjpeg', 'image/png', 'image/x-png', 'image/webp', 'image/gif'];
            $clientExt = strtolower($photoFile->getClientExtension() ?: $photoFile->guessExtension() ?: '');
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if (! in_array(strtolower($photoFile->getMimeType()), $allowedMimes, true) && ! in_array($clientExt, $allowedExts, true)) {
                return redirect()->back()->with('error', 'Please upload a valid image file (JPG, PNG, WEBP, GIF).');
            }

            if ($photoFile->getSize() > 5 * 1024 * 1024) {
                return redirect()->back()->with('error', 'The uploaded photo exceeds the 5MB size limit.');
            }

            $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'vehicle_types';
            if (! is_dir($uploadDir)) {
                @mkdir($uploadDir, 0755, true);
            }
            $ext = $clientExt ?: 'png';
            $newFilename = 'vt_' . $slug . '_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
            if ($photoFile->move($uploadDir, $newFilename)) {
                $photoRelPath = 'uploads/vehicle_types/' . $newFilename;
            } else {
                return redirect()->back()->with('error', 'Failed to save uploaded photo to destination directory.');
            }
        }

        $types->insert([
            'name'      => $name,
            'slug'      => $slug,
            'color'     => $color,
            'icon'      => $icon,
            'photo'     => $photoRelPath,
            'is_active' => 1,
        ]);

        $this->logActivity('Add vehicle type', 'Added vehicle type ' . $name . ' (' . $color . ', ' . $icon . ').');
        get_db_vehicle_types(true);
        $this->broadcastUpdate('vehicle_type_update', [
            'action' => 'create',
            'slug'   => $slug,
            'name'   => $name,
            'color'  => $color,
            'icon'   => $icon,
            'photo'  => $photoRelPath ? base_url($photoRelPath) : null,
            'colors' => get_db_vehicle_types(),
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success'    => true,
                'message'    => $name . ' was added successfully.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
                'data'       => [
                    'name'  => $name,
                    'slug'  => $slug,
                    'color' => $color,
                    'icon'  => $icon,
                    'photo' => $photoRelPath ? base_url($photoRelPath) : null,
                ],
            ]);
        }

        return redirect()->to('/admin/vehicles')->with('success', $name . ' was added. It is now available for vehicles and fares.');
    }

    public function delete($id)
    {
        $types = new VehicleTypeModel();
        $type  = $types->find($id);

        if (!$type) {
            return redirect()->back()->with('error', 'Vehicle type not found.');
        }

        $slug = $type['slug'];
        $name = $type['name'];

        $db = \Config\Database::connect();

        // 1. Find all routes with this vehicle type
        $routeModel = new \App\Models\RouteModel();
        $routes     = $routeModel->where('vehicle_type', $slug)->findAll();
        $routeIds   = array_column($routes, 'id');

        // 2. Find all vehicles with this vehicle type or assigned to these routes.
        // Vehicles are registered assets and must not be deleted with a fare card.
        $vehicleModel   = new \App\Models\VehicleModel();
        $vehicleBuilder = $vehicleModel->groupStart()->where('type', $slug);
        if (!empty($routeIds)) {
            $vehicleRouteColumn = $db->fieldExists('default_route_id', 'vehicles')
                ? 'default_route_id'
                : 'route_id';
            $vehicleBuilder->orWhereIn($vehicleRouteColumn, $routeIds);
        }
        $vehicleBuilder->groupEnd();
        $vehicles   = $vehicleBuilder->findAll();
        $vehicleIds = array_column($vehicles, 'id');

        if (! empty($vehicleIds)) {
            return redirect()->back()->with(
                'error',
                'Cannot delete vehicle type "' . $name . '" while registered vehicles use it. Reassign or remove those vehicles first.'
            );
        }

        // Safety: refuse to hard-delete while active trips exist. This
        // prevents accidental loss of waiting/boarding queue state.
        if (! empty($routeIds) || ! empty($vehicleIds)) {
            $activeCheck = $db->table('queue')->whereIn('status', ['waiting', 'boarding']);
            $activeCheck->groupStart();
            $hasCondition = false;
            if (! empty($routeIds)) {
                $activeCheck->whereIn('route_id', $routeIds);
                $hasCondition = true;
            }
            if (! empty($vehicleIds)) {
                if ($hasCondition) {
                    $activeCheck->orWhereIn('vehicle_id', $vehicleIds);
                } else {
                    $activeCheck->whereIn('vehicle_id', $vehicleIds);
                }
            }
            $activeCheck->groupEnd();
            $activeCount = (int) $activeCheck->countAllResults();
            if ($activeCount > 0) {
                return redirect()->back()->with('error', 'Cannot delete vehicle type "' . $name . '" while ' . $activeCount . ' vehicle(s) are still waiting/boarding. Depart or cancel those trips first.');
            }
        }

        $db->transStart();

        // 3. Delete queue records for affected routes and vehicles
        if (!empty($routeIds) || !empty($vehicleIds)) {
            $queueBuilder = $db->table('queue');
            $queueBuilder->groupStart();
            $hasQueueCondition = false;
            if (!empty($routeIds)) {
                $queueBuilder->whereIn('route_id', $routeIds);
                $hasQueueCondition = true;
            }
            if (!empty($vehicleIds)) {
                if ($hasQueueCondition) {
                    $queueBuilder->orWhereIn('vehicle_id', $vehicleIds);
                } else {
                    $queueBuilder->whereIn('vehicle_id', $vehicleIds);
                }
            }
            $queueBuilder->groupEnd();
            $queueBuilder->delete();
        }

        // 4. Delete departure rules for affected routes
        if (!empty($routeIds)) {
            $db->table('departure_rules')->whereIn('route_id', $routeIds)->delete();
        }

        // 5. Delete staff route assignments (user_routes) for affected routes
        if (!empty($routeIds)) {
            $db->table('user_routes')->whereIn('route_id', $routeIds)->delete();
        }

        // 6. Delete fares for affected routes
        if (!empty($routeIds)) {
            $db->table('fares')->whereIn('route_id', $routeIds)->delete();
        }

        // 7. Delete the routes themselves
        if (!empty($routeIds)) {
            $routeModel->whereIn('id', $routeIds)->delete();
        }

        // 8. Delete the vehicle type. Registered vehicles were checked above
        // and are intentionally never deleted by this operation.
        $types->delete($id);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Failed to delete vehicle type "' . $name . '".');
        }

        $this->logActivity('Delete vehicle type', 'Deleted vehicle type ' . $name . ' (' . $slug . ') and associated routes and fares.');
        get_db_vehicle_types(true);
        $this->broadcastUpdate('vehicle_type_update', ['action' => 'delete', 'slug' => $slug, 'colors' => get_db_vehicle_types()]);

        return redirect()->back()->with('success', 'Vehicle type "' . $name . '" and its connected fares and routes were deleted successfully.');
    }

    public function update($id)
    {
        $types = new VehicleTypeModel();
        $type  = $types->find($id);

        if (!$type) {
            return redirect()->back()->with('error', 'Vehicle type not found.');
        }

        $name = trim((string) $this->request->getPost('name'));
        if ($name === '' || strlen($name) < 2 || strlen($name) > 80) {
            return redirect()->back()->with('error', 'Enter a vehicle type name between 2 and 80 characters.');
        }

        $oldSlug = $type['slug'];
        $newSlug = strtolower((string) preg_replace('/[^a-z0-9]+/i', '_', $name));
        $newSlug = trim($newSlug, '_');

        // Check for duplicate slug on other types
        $existing = $types->where('slug', $newSlug)->where('id !=', $id)->first();
        if ($existing) {
            return redirect()->back()->with('error', 'A vehicle type with that name/slug already exists.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        if ($oldSlug !== $newSlug) {
            $db->table('routes')->where('vehicle_type', $oldSlug)->update(['vehicle_type' => $newSlug]);
            $db->table('vehicles')->where('type', $oldSlug)->update(['type' => $newSlug]);
        }

        $color = trim((string) $this->request->getPost('color'));
        if ($color === '' || ! preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            $color = $type['color'] ?? vehicle_type_color($newSlug);
        }

        $icon = trim((string) $this->request->getPost('icon'));
        if ($icon === '') {
            $icon = $type['icon'] ?? vehicle_type_icon($newSlug);
        }

        // Photo handling
        $photoRelPath = $type['photo'] ?? null;
        $removePhoto = $this->request->getPost('remove_photo');
        $photoFile = $this->request->getFile('photo');

        if ($photoFile && $photoFile->getError() !== UPLOAD_ERR_NO_FILE) {
            if (! $photoFile->isValid()) {
                return redirect()->back()->with('error', 'Photo upload failed: ' . $photoFile->getErrorString());
            }
            $allowedMimes = ['image/jpeg', 'image/jpg', 'image/pjpeg', 'image/png', 'image/x-png', 'image/webp', 'image/gif'];
            $clientExt = strtolower($photoFile->getClientExtension() ?: $photoFile->guessExtension() ?: '');
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if (! in_array(strtolower($photoFile->getMimeType()), $allowedMimes, true) && ! in_array($clientExt, $allowedExts, true)) {
                return redirect()->back()->with('error', 'Please upload a valid image file (JPG, PNG, WEBP, GIF).');
            }

            if ($photoFile->getSize() > 5 * 1024 * 1024) {
                return redirect()->back()->with('error', 'The uploaded photo exceeds the 5MB size limit.');
            }

            $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'vehicle_types';
            if (! is_dir($uploadDir)) {
                @mkdir($uploadDir, 0755, true);
            }
            if (! empty($type['photo'])) {
                $oldFull = FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $type['photo']);
                if (is_file($oldFull)) {
                    @unlink($oldFull);
                }
            }
            $ext = $clientExt ?: 'png';
            $newFilename = 'vt_' . $newSlug . '_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
            if ($photoFile->move($uploadDir, $newFilename)) {
                $photoRelPath = 'uploads/vehicle_types/' . $newFilename;
            } else {
                return redirect()->back()->with('error', 'Failed to save uploaded photo to destination directory.');
            }
        } elseif ($removePhoto === '1' || $removePhoto === 'true') {
            if (! empty($type['photo'])) {
                $oldFull = FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $type['photo']);
                if (is_file($oldFull)) {
                    @unlink($oldFull);
                }
            }
            $photoRelPath = null;
        }

        if ($this->inputsUnchanged([
            'name'  => trim((string) ($type['name'] ?? '')),
            'slug'  => $type['slug'] ?? '',
            'color' => $type['color'] ?? '',
            'icon'  => $type['icon'] ?? '',
            'photo' => $type['photo'] ?? null,
        ], [
            'name'  => $name,
            'slug'  => $newSlug,
            'color' => $color,
            'icon'  => $icon,
            'photo' => $photoRelPath,
        ])) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success'    => true,
                    'no_change'  => true,
                    'message'    => 'No changes were detected.',
                    'csrf_token' => csrf_token(),
                    'csrf_hash'  => csrf_hash(),
                ]);
            }
            return $this->noChangesResponse();
        }

        $types->update($id, [
            'name'  => $name,
            'slug'  => $newSlug,
            'color' => $color,
            'icon'  => $icon,
            'photo' => $photoRelPath,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success'    => false,
                    'message'    => 'Failed to update vehicle type.',
                    'csrf_token' => csrf_token(),
                    'csrf_hash'  => csrf_hash(),
                ]);
            }
            return redirect()->back()->with('error', 'Failed to update vehicle type.');
        }

        $this->logActivity('Update vehicle type', 'Updated vehicle type ' . $type['name'] . ' to ' . $name . '.');
        get_db_vehicle_types(true);
        $this->broadcastUpdate('vehicle_type_update', [
            'action'   => 'update',
            'id'       => (int) $id,
            'old_slug' => $oldSlug,
            'new_slug' => $newSlug,
            'name'     => $name,
            'color'    => $color,
            'icon'     => $icon,
            'photo'    => $photoRelPath ? base_url($photoRelPath) : null,
            'colors'   => get_db_vehicle_types(),
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success'    => true,
                'message'    => 'Vehicle type updated successfully to "' . $name . '".',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
                'data'       => [
                    'id'    => (int) $id,
                    'name'  => $name,
                    'slug'  => $newSlug,
                    'color' => $color,
                    'icon'  => $icon,
                    'photo' => $photoRelPath ? base_url($photoRelPath) : null,
                ],
            ]);
        }

        return redirect()->back()->with('success', 'Vehicle type updated successfully to "' . $name . '".');
    }
}
