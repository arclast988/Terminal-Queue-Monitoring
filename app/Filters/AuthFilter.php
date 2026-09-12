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
        
        // Optional: Role-based checking if arguments provided
        if ($arguments) {
            $role = session()->get('role');

            // Super admin bypasses all role gates
            if ($role === 'super_admin') {
                return;
            }

            if (!in_array($role, $arguments)) {
                // Unauthorized access for this role
                $previous = previous_url();
                $current  = current_url();
                if (empty($previous) || $previous === $current) {
                    $defaultUrl = ($role === 'staff') ? '/staff/queue' : '/admin/dashboard';
                    return redirect()->to($defaultUrl)->with('error', 'You do not have access to this page.');
                }
                return redirect()->back()->with('error', 'You do not have access to this page.');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing here
    }
}
