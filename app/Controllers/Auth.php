<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Auth extends BaseController
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;

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
            if ($this->isUserLocked($user)) {
                $remaining = $this->getLockoutRemaining($user['locked_until']);
                $session->setFlashdata('error', "Account locked. Try again in {$remaining}.");
                return redirect()->to('/login');
            }

            $pwdVerify = password_verify($password, $user['password_hash']);

            if ($pwdVerify) {
                $model->update($user['id'], [
                    'login_attempts' => 0,
                    'locked_until' => null,
                ]);

                $ses_data = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role'],
                    'full_name' => $user['full_name'],
                    'isLoggedIn' => TRUE
                ];
                $session->set($ses_data);
                $session->regenerate();

                $this->logActivity('Login', ucfirst($user['role']) . ' logged in (' . $user['username'] . ')');

                return $this->redirectBasedOnRole();
            }

            $attempts = $user['login_attempts'] + 1;
            $data = ['login_attempts' => $attempts];
            if ($attempts >= self::MAX_ATTEMPTS) {
                $data['locked_until'] = date('Y-m-d H:i:s', strtotime('+' . self::LOCKOUT_MINUTES . ' minutes'));
            }
            $model->update($user['id'], $data);

            $msg = $attempts >= self::MAX_ATTEMPTS
                ? "Account locked due to too many failed attempts. Try again in " . self::LOCKOUT_MINUTES . " minutes."
                : 'Invalid username or password.';

            $session->setFlashdata('error', $msg);
            return redirect()->to('/login');
        }

        if ($this->isIpLocked()) {
            $remaining = $this->getIpLockoutRemaining();
            $session->setFlashdata('error', "Too many failed attempts. Try again in {$remaining}.");
            return redirect()->to('/login');
        }

        $this->trackIpAttempt();
        $session->setFlashdata('error', 'Invalid username or password.');
        return redirect()->to('/login');
    }

    private function isUserLocked(array $user): bool
    {
        if ($user['login_attempts'] < self::MAX_ATTEMPTS) {
            return false;
        }
        if ($user['locked_until'] === null) {
            return false;
        }
        return strtotime($user['locked_until']) > time();
    }

    private function getLockoutRemaining(?string $lockedUntil): string
    {
        $remaining = strtotime($lockedUntil) - time();
        $mins = ceil($remaining / 60);
        return $mins >= 2 ? "{$mins} minutes" : "{$remaining} seconds";
    }

    private function cacheKey(): string
    {
        return 'login_ip_' . md5($this->request->getIPAddress());
    }

    private function isIpLocked(): bool
    {
        $cache = \Config\Services::cache();
        $key = $this->cacheKey();
        $data = $cache->get($key);

        if (!$data || !isset($data['attempts'])) {
            return false;
        }

        if ($data['attempts'] < self::MAX_ATTEMPTS) {
            return false;
        }

        if (!isset($data['locked_until'])) {
            return false;
        }

        return $data['locked_until'] > time();
    }

    private function getIpLockoutRemaining(): string
    {
        $cache = \Config\Services::cache();
        $key = $this->cacheKey();
        $data = $cache->get($key);

        $remaining = ($data['locked_until'] ?? time()) - time();
        $mins = ceil($remaining / 60);
        return $mins >= 2 ? "{$mins} minutes" : "{$remaining} seconds";
    }

    private function trackIpAttempt(): void
    {
        $cache = \Config\Services::cache();
        $key = $this->cacheKey();
        $data = $cache->get($key);

        $attempts = ($data['attempts'] ?? 0) + 1;
        $entry = ['attempts' => $attempts];

        if ($attempts >= self::MAX_ATTEMPTS) {
            $entry['locked_until'] = time() + (self::LOCKOUT_MINUTES * 60);
        }

        $cache->save($key, $entry, self::LOCKOUT_MINUTES * 60);
    }

    public function forgotPassword()
    {
        if (session()->get('isLoggedIn')) {
            return $this->redirectBasedOnRole();
        }
        return view('auth/forgot_password');
    }

    public function sendResetLink()
    {
        $session = session();
        $username = $this->request->getVar('username');

        if (empty($username)) {
            $session->setFlashdata('error', 'Please enter your username.');
            return redirect()->to('/forgot-password');
        }

        $model = new UserModel();
        $user = $model->where('username', $username)->first();

        if (!$user) {
            $session->setFlashdata('error', 'Username not found.');
            return redirect()->to('/forgot-password');
        }

        $db = \Config\Database::connect();
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $db->table('password_reset_tokens')->insert([
            'username' => $username,
            'token' => $token,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to("/reset-password/{$token}");
    }

    public function resetPassword($token)
    {
        if (session()->get('isLoggedIn')) {
            return $this->redirectBasedOnRole();
        }

        $db = \Config\Database::connect();
        $record = $db->table('password_reset_tokens')
            ->where('token', $token)
            ->where('used', 0)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->get()
            ->getRow();

        if (!$record) {
            return view('auth/reset_password', [
                'token' => $token,
                'error' => 'This reset link is invalid or has expired.',
            ]);
        }

        return view('auth/reset_password', [
            'token' => $token,
            'error' => null,
        ]);
    }

    public function updatePassword($token)
    {
        $session = session();
        $db = \Config\Database::connect();

        $record = $db->table('password_reset_tokens')
            ->where('token', $token)
            ->where('used', 0)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->get()
            ->getRow();

        if (!$record) {
            $session->setFlashdata('error', 'This reset link is invalid or has expired.');
            return redirect()->to('/login');
        }

        $password = $this->request->getVar('password');
        $confirm = $this->request->getVar('confirm_password');

        if (strlen($password) < 6) {
            return view('auth/reset_password', [
                'token' => $token,
                'error' => 'Password must be at least 6 characters.',
            ]);
        }

        if ($password !== $confirm) {
            return view('auth/reset_password', [
                'token' => $token,
                'error' => 'Passwords do not match.',
            ]);
        }

        $model = new UserModel();
        $user = $model->where('username', $record->username)->first();

        if (!$user) {
            $session->setFlashdata('error', 'User not found.');
            return redirect()->to('/login');
        }

        $model->update($user['id'], [
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        $db->table('password_reset_tokens')
            ->where('id', $record->id)
            ->update(['used' => 1]);

        $this->logActivity('Password Reset', 'Password reset for ' . $record->username);

        $session->setFlashdata('success', 'Password reset successful. You can now log in.');
        return redirect()->to('/login');
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
