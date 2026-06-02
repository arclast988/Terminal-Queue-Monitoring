<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Auth extends BaseController
{
    public function index()
    {
        if (session()->get('isLoggedIn')) {
            return $this->redirectBasedOnRole();
        }
        return view('auth/login');
    }

    public function login()
    {
        $session = session();
        $model = new UserModel();
        $username = $this->request->getVar('username');
        $password = $this->request->getVar('password');

        $user = $model->where('username', $username)->first();

        if ($user) {
            $pwdVerify = password_verify($password, $user['password_hash']);

            if ($pwdVerify) {
                $ses_data = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role'],
                    'full_name' => $user['full_name'],
                    'isLoggedIn' => TRUE
                ];
                $session->set($ses_data);
                // Regenerate the session ID on privilege change to prevent session fixation.
                $session->regenerate();

                $this->logActivity('Login', ucfirst($user['role']) . ' logged in (' . $user['username'] . ')');

                return $this->redirectBasedOnRole();
            } else {
                $session->setFlashdata('error', 'Invalid username or password.');
                return redirect()->to('/login');
            }
        } else {
            $session->setFlashdata('error', 'Invalid username or password.');
            return redirect()->to('/login');
        }
    }

    public function logout()
    {
        $userId = session()->get('id');
        $role = session()->get('role');
        $username = session()->get('username');
        if ($userId) {
            $this->logActivity('Logout', ($role ? ucfirst($role) . ' ' : '') . ($username ?: 'User') . ' logged out');
        }
        session()->destroy();

        return redirect()->to('/login');
    }

    private function redirectBasedOnRole()
    {
        $role = session()->get('role');
        switch ($role) {
            case 'admin':
                return redirect()->to('/admin/dashboard');
            case 'staff':
                return redirect()->to('/staff/dashboard');
            default:
                return redirect()->to('/guest');
        }
    }
}
