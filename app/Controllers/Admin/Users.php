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
            if ($user['role'] === 'super_admin' || $user['role'] === 'admin') {
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
            'routes' => $routeModel->withOrigin()->orderBy('destination', 'ASC')->findAll(),
        ];
        return view('admin/users/create', $data);
    }

    public function store()
    {
        $currentRole = session()->get('role');
        $model = new UserModel();

        // Only super_admin can create admin accounts
        $allowedRoles = $currentRole === 'super_admin' ? 'super_admin,admin,staff' : 'admin,staff';

        $rules = [
            'username' => [
                'rules'  => 'required|min_length[3]|valid_email|is_unique[users.username]',
                'errors' => [
                    'required'    => 'The Username is required.',
                    'min_length'  => 'The Username must be at least 3 characters long.',
                    'valid_email' => 'The Username must be a valid email address.',
                    'is_unique'   => 'This username/email is already registered.'
                ]
            ],
            'password' => 'required|min_length[6]',
            'full_name' => 'required|min_length[3]',
            'role' => "required|in_list[{$allowedRoles}]"
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $role = $this->request->getPost('role');
        $selectedRoutes = $this->parseRouteIds();

        // Regular admin cannot create admin accounts
        if ($currentRole !== 'super_admin' && $role === 'admin') {
            return redirect()->back()->withInput()->with('error', 'Only a super admin can create admin accounts.');
        }

        // No one can create super_admin accounts through the UI
        if ($role === 'super_admin') {
            return redirect()->back()->withInput()->with('error', 'Super admin accounts cannot be created through the user interface.');
        }

        // Staff must have at least one route assigned
        if ($role === 'staff' && empty($selectedRoutes)) {
            return redirect()->back()->withInput()->with('error', 'Dispatchers must have at least one route assigned.');
        }

        $data = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('username'),
            'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'full_name' => $this->request->getPost('full_name'),
            'role' => $role,
        ];

        $userId = $model->insert($data);

        // Sync route assignments for staff
        if ($role === 'staff') {
            $userRouteModel = new UserRouteModel();
            $userRouteModel->syncRoutesForUser($userId, $selectedRoutes);

            $routeModel = new RouteModel();
            $routeLabels = [];
            foreach ($selectedRoutes as $rid) {
                $r = $routeModel->withOrigin()->find($rid);
                if ($r) {
                    $routeLabels[] = strtoupper($r['origin']) . ' → ' . strtoupper($r['destination']);
                }
            }
            $this->logActivity('Create user with routes', 'Created dispatcher "' . $data['username'] . '" with routes: ' . implode(', ', array_unique($routeLabels)));
        } else {
            $this->logActivity('Create user', 'Created user "' . $data['username'] . '" with role: ' . $role);
        }

        return redirect()->to('/admin/users')->with('success', 'User created successfully.');
    }

    public function edit($id)
    {
        $currentRole = session()->get('role');
        $model = new UserModel();
        $routeModel = new RouteModel();
        $userRouteModel = new UserRouteModel();

        $user = $model->find($id);
        
        if (!$user) {
            return redirect()->to('/admin/users')->with('error', 'User not found.');
        }

        // Regular admin cannot edit other admin accounts
        if ($currentRole !== 'super_admin' && $user['role'] === 'admin' && (int)$user['id'] !== (int)session()->get('id')) {
            return redirect()->to('/admin/users')->with('error', 'You cannot edit other admin accounts.');
        }

        // No one can edit the super_admin account through the UI
        if ($user['role'] === 'super_admin' && $currentRole !== 'super_admin') {
            return redirect()->to('/admin/users')->with('error', 'You cannot edit the super admin account.');
        }

        $data = [
            'user' => $user,
            'routes' => $routeModel->withOrigin()->orderBy('destination', 'ASC')->findAll(),
            'assignedRouteIds' => $userRouteModel->getRouteIdsForUser($id),
        ];

        return view('admin/users/edit', $data);
    }

    public function update($id)
    {
        $currentRole = session()->get('role');
        $model = new UserModel();
        $userRouteModel = new UserRouteModel();

        $targetUser = $model->find($id);
        if (!$targetUser) {
            return redirect()->to('/admin/users')->with('error', 'User not found.');
        }

        // Regular admin cannot modify other admin accounts
        if ($currentRole !== 'super_admin' && $targetUser['role'] === 'admin' && (int)$id !== (int)session()->get('id')) {
            return redirect()->back()->withInput()->with('error', 'You cannot modify other admin accounts.');
        }

        // No one can modify the super_admin account through the UI
        if ($targetUser['role'] === 'super_admin' && $currentRole !== 'super_admin') {
            return redirect()->to('/admin/users')->with('error', 'You cannot modify the super admin account.');
        }

        // Super admin cannot demote themselves
        if ($currentRole === 'super_admin' && (int)$id === (int)session()->get('id') && $this->request->getPost('role') !== 'super_admin') {
            return redirect()->back()->withInput()->with('error', 'You cannot demote yourself from super admin. Use the CLI transfer command instead.');
        }

        // Only super_admin is allowed to set a role of admin
        $allowedRoles = $currentRole === 'super_admin' ? 'super_admin,admin,staff' : 'admin,staff';

        $rules = [
            'username' => [
                'rules'  => "required|min_length[3]|valid_email|is_unique[users.username,id,{$id}]",
                'errors' => [
                    'required'    => 'The Username is required.',
                    'min_length'  => 'The Username must be at least 3 characters long.',
                    'valid_email' => 'The Username must be a valid email address.',
                    'is_unique'   => 'This username/email is already registered.'
                ]
            ],
            'full_name' => 'required|min_length[3]',
            'role' => "required|in_list[{$allowedRoles}]"
        ];

        if ($this->request->getPost('password')) {
            $rules['password'] = 'min_length[6]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $role = $this->request->getPost('role');
        $selectedRoutes = $this->parseRouteIds();

        // Regular admin cannot change user roles
        if ($currentRole !== 'super_admin' && $role !== $targetUser['role']) {
            return redirect()->back()->withInput()->with('error', 'Only a super admin can change user roles.');
        }

        // Staff must have at least one route assigned
        if ($role === 'staff' && empty($selectedRoutes)) {
            return redirect()->back()->withInput()->with('error', 'Dispatchers must have at least one route assigned.');
        }

        // Get old data for logging
        $oldUser = $targetUser;
        $oldRouteIds = $userRouteModel->getRouteIdsForUser($id);

        $data = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('username'),
            'full_name' => $this->request->getPost('full_name'),
            'role' => $role,
        ];

        if ($this->request->getPost('password')) {
            $data['password_hash'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
        }

        $newRouteIds = ($role === 'staff') ? array_map('intval', $selectedRoutes) : [];
        $oldRouteIdsNorm = array_map('intval', (array) $oldRouteIds);
        sort($newRouteIds);
        sort($oldRouteIdsNorm);

        if (empty($data['password_hash'] ?? null)
            && $this->inputsUnchanged([
                'username'  => $oldUser['username'] ?? '',
                'full_name' => $oldUser['full_name'] ?? '',
                'role'      => $oldUser['role'] ?? '',
            ], [
                'username'  => $data['username'],
                'full_name' => $data['full_name'],
                'role'      => $data['role'],
            ])
            && $newRouteIds === $oldRouteIdsNorm
        ) {
            return $this->noChangesResponse();
        }

        $model->update($id, $data);

        // Sync route assignments
        if ($role === 'staff') {
            $userRouteModel->syncRoutesForUser($id, $selectedRoutes);
        } else {
            // Admin/super_admin users don't need route assignments — clear them
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
                    $r = $routeModel->withOrigin()->find($rid);
                    if ($r) $labels[] = strtoupper($r['origin']) . ' → ' . strtoupper($r['destination']);
                }
                $details .= ' Added: ' . implode(', ', array_unique($labels)) . '.';
            }
            if (!empty($removedRoutes)) {
                $labels = [];
                foreach ($removedRoutes as $rid) {
                    $r = $routeModel->withOrigin()->find($rid);
                    if ($r) $labels[] = strtoupper($r['origin']) . ' → ' . strtoupper($r['destination']);
                }
                $details .= ' Removed: ' . implode(', ', array_unique($labels)) . '.';
            }
            $this->logActivity('Update dispatcher routes', $details);
        }

        return redirect()->to('/admin/users')->with('success', 'User updated successfully.');
    }

    public function delete($id)
    {
        $currentRole = session()->get('role');
        $model = new UserModel();

        $targetUser = $model->find($id);
        if (!$targetUser) {
            return redirect()->to('/admin/users')->with('error', 'User not found.');
        }

        // Prevent admin from deleting their own account
        if ((int)$id === (int)session()->get('id')) {
            return redirect()->to('/admin/users')->with('error', 'You cannot delete your own account.');
        }

        // Regular admin cannot delete other admin accounts
        if ($currentRole !== 'super_admin' && $targetUser['role'] === 'admin') {
            return redirect()->to('/admin/users')->with('error', 'You cannot delete other admin accounts.');
        }

        // No one can delete the super_admin account
        if ($targetUser['role'] === 'super_admin') {
            return redirect()->to('/admin/users')->with('error', 'The super admin account cannot be deleted.');
        }

        $model->delete($id);
        // user_routes cleaned up automatically by ON DELETE CASCADE
        $displayName = !empty($targetUser['full_name']) ? $targetUser['full_name'] : $targetUser['username'];
        return redirect()->to('/admin/users')->with('success', 'User "' . $displayName . '" deleted successfully.');
    }

    private function parseRouteIds(): array
    {
        $postedRoutes = $this->request->getPost('route_ids') ?? [];
        $selectedRoutes = [];
        foreach ($postedRoutes as $val) {
            if (is_string($val) && strpos($val, ',') !== false) {
                $selectedRoutes = array_merge($selectedRoutes, explode(',', $val));
            } else {
                $selectedRoutes[] = $val;
            }
        }
        return array_unique(array_map('intval', $selectedRoutes));
    }
}
