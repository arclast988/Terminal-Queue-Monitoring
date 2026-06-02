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
            if (!in_array($role, $arguments ?? [])) {
                // Unauthorized access for this role
                return redirect()->back()->with('error', 'You do not have access to this page.');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing here
    }
}
