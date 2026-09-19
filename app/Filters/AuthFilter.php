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
            return redirect()->to('/login');
        }

        // Keep profile_image synchronized with the database
        $userId = session()->get('id');
        if ($userId) {
            try {
                $userModel = new \App\Models\UserModel();
                $dbUser = $userModel->select('profile_image')->find($userId);
                if ($dbUser && array_key_exists('profile_image', $dbUser)) {
                    session()->set('profile_image', $dbUser['profile_image']);
                }
            } catch (\Throwable $e) {
                // Non-blocking fallback
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

            $previous = previous_url();
            $current  = current_url();
            if (empty($previous) || $previous === $current) {
                $defaultUrl = ($role === 'staff') ? '/staff/queue' : '/admin/dashboard';
                return redirect()->to($defaultUrl)->with('error', 'You do not have access to this page.');
            }
            return redirect()->back()->with('error', 'You do not have access to this page.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing here
    }
}
