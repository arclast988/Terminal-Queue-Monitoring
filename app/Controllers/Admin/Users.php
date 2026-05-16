<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\RouteModel;
use App\Models\UserRouteModel;

class Users extends BaseController
{
    public function index()
    {
        $model = new UserModel();
        $userRouteModel = new UserRouteModel();

        $users = $model->findAll();

        // Attach assigned routes label to each user
        foreach ($users as &$user) {
            if ($user['role'] === 'admin') {
                $user['assigned_routes_label'] = 'All Routes';
            } else {
                $label = $userRouteModel->getRouteLabelsForUser($user['id']);
                $user['assigned_routes_label'] = $label;
            }
        }

        $data['users'] = $users;
        return view('admin/users/index', $data);
    }

    public function create()
    {
        $routeModel = new RouteModel();
        $data = [
            'routes' => $routeModel->orderBy('destination', 'ASC')->findAll(),
        ];
        return view('admin/users/create', $data);
    }

    public function store()
    {
        $model = new UserModel();
        
        $rules = [
            'username' => 'required|min_length[3]|is_unique[users.username]',
            'password' => 'required|min_length[6]',
            'full_name' => 'required|min_length[3]',
            'role' => 'required|in_list[admin,staff]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $role = $this->request->getPost('role');
        $selectedRoutes = $this->request->getPost('route_ids') ?? [];

        // Staff must have at least one route assigned
        if ($role === 'staff' && empty($selectedRoutes)) {
            return redirect()->back()->withInput()->with('error', 'Dispatchers must have at least one route assigned.');
        }

        $data = [
            'username' => $this->request->getPost('username'),
            'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'full_name' => $this->request->getPost('full_name'),
            'role' => $role,
        ];

        $userId = $model->insert($data);

        // Sync route assignments for staff
        if ($role === 'staff' && !empty($selectedRoutes)) {
            $userRouteModel = new UserRouteModel();
            $userRouteModel->syncRoutesForUser($userId, $selectedRoutes);

            $routeModel = new RouteModel();
            $routeLabels = [];
            foreach ($selectedRoutes as $rid) {
                $r = $routeModel->find($rid);
                if ($r) {
                    $routeLabels[] = strtoupper($r['origin']) . ' → ' . strtoupper($r['destination']);
                }
            }
            $this->logActivity('Create user with routes', 'Created dispatcher "' . $data['username'] . '" with routes: ' . implode(', ', $routeLabels));
        } else {
            $this->logActivity('Create user', 'Created user "' . $data['username'] . '" with role: ' . $role);
        }

        return redirect()->to('/admin/users')->with('success', 'User created successfully.');
    }

    public function edit($id)
    {
        $model = new UserModel();
        $routeModel = new RouteModel();
        $userRouteModel = new UserRouteModel();

        $user = $model->find($id);
        
        if (!$user) {
            return redirect()->to('/admin/users')->with('error', 'User not found.');
        }

        $data = [
            'user' => $user,
            'routes' => $routeModel->orderBy('destination', 'ASC')->findAll(),
            'assignedRouteIds' => $userRouteModel->getRouteIdsForUser($id),
        ];

        return view('admin/users/edit', $data);
    }

    public function update($id)
    {
        $model = new UserModel();
        $userRouteModel = new UserRouteModel();
        
        $rules = [
            'username' => "required|min_length[3]|is_unique[users.username,id,{$id}]",
            'full_name' => 'required|min_length[3]',
            'role' => 'required|in_list[admin,staff]'
        ];

        if ($this->request->getPost('password')) {
            $rules['password'] = 'min_length[6]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $role = $this->request->getPost('role');
        $selectedRoutes = $this->request->getPost('route_ids') ?? [];

        // Staff must have at least one route assigned
        if ($role === 'staff' && empty($selectedRoutes)) {
            return redirect()->back()->withInput()->with('error', 'Dispatchers must have at least one route assigned.');
        }

        // Get old data for logging
        $oldUser = $model->find($id);
        $oldRouteIds = $userRouteModel->getRouteIdsForUser($id);

        $data = [
            'username' => $this->request->getPost('username'),
            'full_name' => $this->request->getPost('full_name'),
            'role' => $role,
        ];

        if ($this->request->getPost('password')) {
            $data['password_hash'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
        }

        $model->update($id, $data);

        // Sync route assignments
        if ($role === 'staff') {
            $userRouteModel->syncRoutesForUser($id, $selectedRoutes);
        } else {
            // Admin users don't need route assignments — clear them
            $userRouteModel->syncRoutesForUser($id, []);
        }

        // Log route changes
        $addedRoutes = array_diff($selectedRoutes, $oldRouteIds);
        $removedRoutes = array_diff($oldRouteIds, $selectedRoutes);
        if (!empty($addedRoutes) || !empty($removedRoutes) || $oldUser['role'] !== $role) {
            $routeModel = new RouteModel();
            $details = 'Updated dispatcher "' . $data['username'] . '".';
            if (!empty($addedRoutes)) {
                $labels = [];
                foreach ($addedRoutes as $rid) {
                    $r = $routeModel->find($rid);
                    if ($r) $labels[] = strtoupper($r['origin']) . ' → ' . strtoupper($r['destination']);
                }
                $details .= ' Added: ' . implode(', ', $labels) . '.';
            }
            if (!empty($removedRoutes)) {
                $labels = [];
                foreach ($removedRoutes as $rid) {
                    $r = $routeModel->find($rid);
                    if ($r) $labels[] = strtoupper($r['origin']) . ' → ' . strtoupper($r['destination']);
                }
                $details .= ' Removed: ' . implode(', ', $labels) . '.';
            }
            $this->logActivity('Update dispatcher routes', $details);
        }

        return redirect()->to('/admin/users')->with('success', 'User updated successfully.');
    }

    public function delete($id)
    {
        // Prevent admin from deleting their own account
        if ((int)$id === (int)session()->get('id')) {
            return redirect()->to('/admin/users')->with('error', 'You cannot delete your own account.');
        }

        $model = new UserModel();
        $model->delete($id);
        // user_routes cleaned up automatically by ON DELETE CASCADE
        return redirect()->to('/admin/users')->with('success', 'User deleted successfully.');
    }
}
