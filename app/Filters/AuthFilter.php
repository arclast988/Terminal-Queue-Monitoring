<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->loginRequired($request);
        }

        // Check the current password on every protected request. A password
        // change invalidates other sessions before they can perform an action.
        $userId = session()->get('id');
        if (!$userId) {
            session()->remove(['isLoggedIn', 'id', 'role', 'auth_password_fingerprint']);
            session()->destroy();
            return $this->loginRequired($request);
        }
        $lastAvatarSync = (int) session()->get('profile_image_synced_at');
        if ($userId) {
            try {
                $dbUser = $this->loadAccount((int) $userId);
                $fingerprint = session()->get('auth_password_fingerprint');
                if (! $dbUser || ($dbUser['status'] ?? 'active') !== 'active'
                    || ($dbUser['role'] ?? null) !== session()->get('role')
                    || !is_string($fingerprint) || $fingerprint === ''
                    || empty($dbUser['password_hash'])
                    || !hash_equals($fingerprint, hash('sha256', $dbUser['password_hash']))) {
                    session()->remove(['isLoggedIn', 'id', 'role', 'auth_password_fingerprint']);
                    session()->destroy();
                    return $this->loginRequired($request);
                }
                if (time() - $lastAvatarSync >= 60 && array_key_exists('profile_image', $dbUser)) {
                    session()->set('profile_image', $dbUser['profile_image']);
                    session()->set('profile_image_synced_at', time());
                }
            } catch (\Throwable $e) {
                // Keep the session during an outage, but reject unchecked access.
                return service('response')->setStatusCode(503)->setHeader('Cache-Control', 'no-store')
                    ->setJSON(['success' => false, 'message' => 'Unable to verify your session. Please try again shortly.']);
            }
        }
        
        // Optional: Role-based checking if arguments provided
        if ($arguments) {
            $role = session()->get('role');

            $hasDirectAccess = in_array($role, $arguments, true);
            $inheritsAdminAccess = $role === 'super_admin' && in_array('admin', $arguments, true);

            // Super Admin inherits Admin access, but staff-only routes remain
            // dispatcher-only. This prevents accidental queue operations.
            if ($hasDirectAccess || $inheritsAdminAccess) {
                return;
            }

            // Unauthorized access for this role
            if ($role === 'super_admin') {
                return redirect()->to('/admin/dashboard')->with('error', 'Dispatcher-only pages are restricted to dispatchers.');
            }

            $defaultUrl = ($role === 'staff') ? '/staff/queue' : '/admin/dashboard';
            return redirect()->to($defaultUrl)->with('error', 'You do not have access to this page.');
        }
    }

    protected function loadAccount(int $userId): ?array
    {
        return (new \App\Models\UserModel())
            ->select('profile_image, status, role, password_hash')
            ->find($userId);
    }

    private function loginRequired(RequestInterface $request)
    {
        if ($request instanceof \CodeIgniter\HTTP\IncomingRequest && $request->isAJAX()) {
            return service('response')->setStatusCode(401)->setHeader('Cache-Control', 'no-store')->setJSON([
                'success' => false,
                'session_expired' => true,
                'login_url' => base_url('login'),
                'message' => 'Your session has ended. Please sign in again.',
            ]);
        }
        return redirect()->to('/login');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing here
    }
}
