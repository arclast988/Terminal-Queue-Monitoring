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

        $users = $model->orderBy('full_name', 'ASC')->findAll();

        // Sort alphabetically case-insensitively by full_name (or username if full_name is empty)
        usort($users, function ($a, $b) {
            $nameA = trim($a['full_name'] ?? '') ?: ($a['username'] ?? '');
            $nameB = trim($b['full_name'] ?? '') ?: ($b['username'] ?? '');
            return strcasecmp($nameA, $nameB);
        });

        $countActive = 0;
        $countAdmin = 0;
        $countDispatcher = 0;
        $countArchived = 0;

        // Attach assigned routes label and calculate counts
        foreach ($users as &$user) {
            $status = $user['status'] ?? 'active';
            $user['status'] = $status;

            if ($status === 'archived') {
                $countArchived++;
            } else {
                $countActive++;
                if ($user['role'] === 'super_admin' || $user['role'] === 'admin') {
                    $countAdmin++;
                } else {
                    $countDispatcher++;
                }
            }

            if ($user['role'] === 'super_admin' || $user['role'] === 'admin') {
                $user['assigned_routes_label'] = 'All Routes';
            } else {
                $label = $userRouteModel->getRouteLabelsForUser($user['id']);
                $user['assigned_routes_label'] = $label;
            }
        }

        $data['users'] = $users;
        $data['countActive'] = $countActive;
        $data['countAdmin'] = $countAdmin;
        $data['countDispatcher'] = $countDispatcher;
        $data['countArchived'] = $countArchived;

        return view('admin/users/index', $data);
    }

    public function create()
    {
        $routeModel = new RouteModel();
        $data = [
            'routes' => $routeModel->withOrigin()->where('routes.status', 'active')->orderBy('destination', 'ASC')->findAll(),
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
            'password' => 'required|min_length[8]',
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

        // Validate avatar upload if provided
        $avatarFile = $this->request->getFile('avatar');
        $hasAvatar = ($avatarFile && $avatarFile->isValid() && !$avatarFile->hasMoved());
        if ($hasAvatar) {
            $maxSizeBytes = 4 * 1024 * 1024;
            if ($avatarFile->getSize() > $maxSizeBytes) {
                return redirect()->back()->withInput()->with('error', 'The profile image exceeds the 4MB maximum allowed size.');
            }

            $allowedMimes = ['image/jpeg', 'image/jpg', 'image/pjpeg', 'image/png', 'image/webp', 'image/gif'];
            $mime = $avatarFile->getMimeType();
            if (!in_array(strtolower($mime), $allowedMimes, true)) {
                return redirect()->back()->withInput()->with('error', 'Invalid image file type. Only JPG, PNG, WEBP, and GIF images are allowed.');
            }

            $imageInfo = @getimagesize($avatarFile->getTempName());
            if ($imageInfo === false) {
                return redirect()->back()->withInput()->with('error', 'Uploaded file is not a valid image.');
            }
        }

        $data = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('username'),
            'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'full_name' => $this->request->getPost('full_name'),
            'role' => $role,
        ];

        $userId = $model->insert($data);

        // Save avatar if uploaded
        if ($hasAvatar && $userId) {
            $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'avatars';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0755, true);
            }

            $ext = $avatarFile->guessExtension() ?: 'jpg';
            $newFilename = 'avatar_' . $userId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

            if ($avatarFile->move($uploadDir, $newFilename)) {
                $relPath = 'uploads/avatars/' . $newFilename;
                $model->update($userId, ['profile_image' => $relPath]);
            }
        }

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
            'routes' => $routeModel->withOrigin()->where('routes.status', 'active')->orderBy('destination', 'ASC')->findAll(),
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
            $rules['password'] = 'min_length[8]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $role = $this->request->getPost('role');
        $selectedRoutes = $this->parseRouteIds();

        // A second super administrator must never be created by editing an
        // existing account. Use the transfer command so the former holder is
        // demoted in the same transaction.
        if ($role === 'super_admin' && $targetUser['role'] !== 'super_admin') {
            return redirect()->back()->withInput()->with('error', 'Only one super admin is allowed. Transfer the role instead of promoting another account.');
        }

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

    public function deactivate($id)
    {
        $currentRole = session()->get('role');
        $model = new UserModel();

        $targetUser = $model->find($id);
        if (!$targetUser) {
            return redirect()->to('/admin/users')->with('error', 'User not found.');
        }

        // Prevent admin from deactivating their own account
        if ((int)$id === (int)session()->get('id')) {
            return redirect()->to('/admin/users')->with('error', 'You cannot deactivate your own account.');
        }

        // Prevent deactivating super_admin
        if ($targetUser['role'] === 'super_admin') {
            return redirect()->to('/admin/users')->with('error', 'The super admin account cannot be deactivated.');
        }

        // Regular admin cannot deactivate other admin accounts
        if ($currentRole !== 'super_admin' && $targetUser['role'] === 'admin') {
            return redirect()->to('/admin/users')->with('error', 'You cannot deactivate other admin accounts.');
        }

        $model->update($id, ['status' => 'archived']);

        $displayName = !empty($targetUser['full_name']) ? $targetUser['full_name'] : $targetUser['username'];
        $this->logActivity('Deactivate user', 'Deactivated user "' . $displayName . '" (' . $targetUser['username'] . ') and moved to archive');

        $redirectTab = $this->request->getPost('redirect_tab') ?? $this->request->getGet('tab') ?? 'active';
        return redirect()->to('/admin/users' . ($redirectTab === 'archived' ? '?tab=archived' : ''))->with('success', 'User "' . $displayName . '" has been deactivated and moved to archive.');
    }

    public function activate($id)
    {
        $currentRole = session()->get('role');
        $model = new UserModel();

        $targetUser = $model->find($id);
        if (!$targetUser) {
            return redirect()->to('/admin/users')->with('error', 'User not found.');
        }

        // Regular admin cannot activate other admin accounts unless super_admin
        if ($currentRole !== 'super_admin' && $targetUser['role'] === 'admin') {
            return redirect()->to('/admin/users')->with('error', 'You cannot activate other admin accounts.');
        }

        $model->update($id, ['status' => 'active']);

        $displayName = !empty($targetUser['full_name']) ? $targetUser['full_name'] : $targetUser['username'];
        $this->logActivity('Activate user', 'Reactivated user "' . $displayName . '" (' . $targetUser['username'] . ')');

        $redirectTab = $this->request->getPost('redirect_tab') ?? $this->request->getGet('tab') ?? 'archived';
        return redirect()->to('/admin/users' . ($redirectTab === 'archived' ? '?tab=archived' : ''))->with('success', 'User "' . $displayName . '" has been reactivated successfully.');
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
        $this->logActivity('Delete user', 'Permanently deleted user "' . $displayName . '" (' . $targetUser['username'] . ')');
        $redirectTab = $this->request->getPost('redirect_tab') ?? $this->request->getGet('tab') ?? 'archived';
        return redirect()->to('/admin/users' . ($redirectTab === 'archived' ? '?tab=archived' : ''))->with('success', 'User "' . $displayName . '" permanently deleted successfully.');
    }

    public function bulkAction()
    {
        $currentRole = session()->get('role');
        $currentUserId = (int) session()->get('id');
        $model = new UserModel();

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
            return redirect()->to('/admin/users' . ($redirectTab ? '?tab=' . $redirectTab : ''))->with('error', 'No users selected for bulk action.');
        }

        $users = $model->whereIn('id', $ids)->findAll();
        if (empty($users)) {
            return redirect()->to('/admin/users' . ($redirectTab ? '?tab=' . $redirectTab : ''))->with('error', 'Selected users not found.');
        }

        $count = 0;

        if ($action === 'deactivate') {
            foreach ($users as $u) {
                if ((int)$u['id'] === $currentUserId) continue;
                if ($u['role'] === 'super_admin') continue;
                if ($currentRole !== 'super_admin' && $u['role'] === 'admin') continue;

                if (($u['status'] ?? 'active') !== 'archived') {
                    $model->update($u['id'], ['status' => 'archived']);
                    $count++;
                }
            }
            $this->logActivity('Bulk deactivate users', "Deactivated $count user(s) and moved to archive.");
            return redirect()->to('/admin/users?tab=active')->with('success', "$count user(s) deactivated and moved to archive.");
        } elseif ($action === 'activate') {
            foreach ($users as $u) {
                if ($currentRole !== 'super_admin' && $u['role'] === 'admin') continue;
                if (($u['status'] ?? 'active') === 'archived') {
                    $model->update($u['id'], ['status' => 'active']);
                    $count++;
                }
            }
            $this->logActivity('Bulk activate users', "Activated $count user(s).");
            return redirect()->to('/admin/users?tab=archived')->with('success', "$count user(s) reactivated successfully.");
        } elseif ($action === 'delete') {
            foreach ($users as $u) {
                if ((int)$u['id'] === $currentUserId) continue;
                if ($u['role'] === 'super_admin') continue;
                if ($currentRole !== 'super_admin' && $u['role'] === 'admin') continue;
                if (($u['status'] ?? 'active') === 'archived') {
                    $model->delete($u['id']);
                    $count++;
                }
            }
            $this->logActivity('Bulk delete users', "Permanently deleted $count archived user(s).");
            return redirect()->to('/admin/users?tab=archived')->with('success', "$count archived user(s) permanently deleted.");
        }

        return redirect()->to('/admin/users' . ($redirectTab ? '?tab=' . $redirectTab : ''))->with('error', 'Invalid bulk action.');
    }

    public function uploadAvatar($id)
    {
        $currentRole = session()->get('role');
        $currentUserId = (int)session()->get('id');
        $userModel = new UserModel();

        $targetUser = $userModel->find($id);
        if (!$targetUser) {
            return $this->response->setStatusCode(404)->setJSON([
                'success'    => false,
                'message'    => 'User not found.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        // Super Admin avatar protection: ONLY the super admin themselves can change their own avatar
        if ($targetUser['role'] === 'super_admin' && $currentUserId !== (int)$id) {
            return $this->response->setStatusCode(403)->setJSON([
                'success'    => false,
                'message'    => 'Only the Super Admin can change their own profile picture.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        // Regular admin cannot change other admin avatars (only super_admin or the admin themselves)
        if ($currentRole !== 'super_admin' && $targetUser['role'] === 'admin' && $currentUserId !== (int)$id) {
            return $this->response->setStatusCode(403)->setJSON([
                'success'    => false,
                'message'    => 'You do not have permission to change this admin\'s profile picture.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        $avatarFile = $this->request->getFile('avatar');
        if (!$avatarFile || !$avatarFile->isValid() || $avatarFile->hasMoved()) {
            return $this->response->setStatusCode(400)->setJSON([
                'success'    => false,
                'message'    => 'Please select a valid image file to upload.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        // Validate file size (max 4MB)
        $maxSizeBytes = 4 * 1024 * 1024;
        if ($avatarFile->getSize() > $maxSizeBytes) {
            return $this->response->setStatusCode(400)->setJSON([
                'success'    => false,
                'message'    => 'The image exceeds the 4MB maximum allowed size.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        // Validate MIME type
        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/pjpeg', 'image/png', 'image/webp', 'image/gif'];
        $mime = $avatarFile->getMimeType();
        if (!in_array(strtolower($mime), $allowedMimes, true)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success'    => false,
                'message'    => 'Invalid file type. Only JPG, PNG, WEBP, and GIF images are allowed.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        // Validate that the file is genuinely an image via getimagesize
        $imageInfo = @getimagesize($avatarFile->getTempName());
        if ($imageInfo === false) {
            return $this->response->setStatusCode(400)->setJSON([
                'success'    => false,
                'message'    => 'Uploaded file is not a valid image.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'avatars';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        // Delete old custom avatar if it exists
        if (!empty($targetUser['profile_image'])) {
            $oldPath = FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $targetUser['profile_image']);
            if (is_file($oldPath) && str_starts_with(realpath($oldPath) ?: '', realpath($uploadDir) ?: '')) {
                @unlink($oldPath);
            }
        }

        // Generate safe random filename
        $ext = $avatarFile->guessExtension() ?: 'jpg';
        $newFilename = 'avatar_' . $id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        if (!$avatarFile->move($uploadDir, $newFilename)) {
            return $this->response->setStatusCode(500)->setJSON([
                'success'    => false,
                'message'    => 'Failed to save uploaded image.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        $relPath = 'uploads/avatars/' . $newFilename;
        $userModel->update($id, ['profile_image' => $relPath]);

        if ($currentUserId === (int)$id) {
            session()->set('profile_image', $relPath);
        }

        $imageUrl = base_url($relPath) . '?v=' . time();

        $this->broadcastUpdate('user_avatar_updated', [
            'user_id'   => (int)$id,
            'image_url' => $imageUrl,
            'action'    => 'upload',
        ]);

        $displayName = !empty($targetUser['full_name']) ? $targetUser['full_name'] : $targetUser['username'];
        $this->logActivity('Update user avatar', 'Updated profile picture for "' . $displayName . '" (' . $targetUser['username'] . ')');

        return $this->response->setJSON([
            'success'    => true,
            'message'    => 'Profile image updated successfully.',
            'image_url'  => $imageUrl,
            'csrf_token' => csrf_token(),
            'csrf_hash'  => csrf_hash(),
        ]);
    }

    public function removeAvatar($id)
    {
        $currentRole = session()->get('role');
        $currentUserId = (int)session()->get('id');
        $userModel = new UserModel();

        $targetUser = $userModel->find($id);
        if (!$targetUser) {
            return $this->response->setStatusCode(404)->setJSON([
                'success'    => false,
                'message'    => 'User not found.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        // Super Admin avatar protection: ONLY the super admin themselves can change their own avatar
        if ($targetUser['role'] === 'super_admin' && $currentUserId !== (int)$id) {
            return $this->response->setStatusCode(403)->setJSON([
                'success'    => false,
                'message'    => 'Only the Super Admin can remove their own profile picture.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        // Regular admin cannot remove other admin avatars
        if ($currentRole !== 'super_admin' && $targetUser['role'] === 'admin' && $currentUserId !== (int)$id) {
            return $this->response->setStatusCode(403)->setJSON([
                'success'    => false,
                'message'    => 'You do not have permission to remove this admin\'s profile picture.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        if (!empty($targetUser['profile_image'])) {
            $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'avatars';
            $oldPath = FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $targetUser['profile_image']);
            if (is_file($oldPath) && str_starts_with(realpath($oldPath) ?: '', realpath($uploadDir) ?: '')) {
                @unlink($oldPath);
            }

            $userModel->update($id, ['profile_image' => null]);
            if ($currentUserId === (int)$id) {
                session()->remove('profile_image');
            }

            $this->broadcastUpdate('user_avatar_updated', [
                'user_id'   => (int)$id,
                'image_url' => null,
                'action'    => 'remove',
            ]);

            $displayName = !empty($targetUser['full_name']) ? $targetUser['full_name'] : $targetUser['username'];
            $this->logActivity('Remove user avatar', 'Removed profile picture for "' . $displayName . '" (' . $targetUser['username'] . ')');
        }

        return $this->response->setJSON([
            'success'    => true,
            'message'    => 'Profile picture removed successfully.',
            'csrf_token' => csrf_token(),
            'csrf_hash'  => csrf_hash(),
        ]);
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
