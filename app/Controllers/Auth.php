<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Auth extends BaseController
{
    private const MAX_ATTEMPTS = 20;
    private const LOCKOUT_SECONDS = 30;

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

            // Reset attempt counter if previous lockout expired
            if (!empty($user['locked_until']) && strtotime($user['locked_until']) <= time()) {
                $model->update($user['id'], [
                    'login_attempts' => 0,
                    'locked_until'   => null,
                ]);
                $user['login_attempts'] = 0;
                $user['locked_until'] = null;
            }

            $pwdVerify = password_verify($password, $user['password_hash']);

            if ($pwdVerify) {
                $model->update($user['id'], [
                    'login_attempts' => 0,
                    'locked_until' => null,
                ]);
                $cache = \Config\Services::cache();
                $cache->delete($this->cacheKey());

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
                $data['locked_until'] = date('Y-m-d H:i:s', time() + self::LOCKOUT_SECONDS);
            }
            $model->update($user['id'], $data);

            $msg = $attempts >= self::MAX_ATTEMPTS
                ? "Account locked due to too many failed attempts. Try again in " . self::LOCKOUT_SECONDS . " seconds."
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
        if (!$lockedUntil) {
            return '0 seconds';
        }
        $remaining = max(0, strtotime($lockedUntil) - time());
        return "{$remaining} seconds";
    }

    private function cacheKey(): string
    {
        return 'login_ip_' . hash('sha256', $this->request->getIPAddress());
    }

    private function isIpLocked(): bool
    {
        $cache = \Config\Services::cache();
        $key = $this->cacheKey();
        $data = $cache->get($key);

        if (!is_array($data) || !isset($data['attempts'])) {
            return false;
        }

        if (isset($data['locked_until'])) {
            if ($data['locked_until'] > time()) {
                return true;
            }
            $cache->delete($key);
            return false;
        }

        if (isset($data['first_attempt']) && (time() - $data['first_attempt'] > self::LOCKOUT_SECONDS)) {
            $cache->delete($key);
            return false;
        }

        return false;
    }

    private function getIpLockoutRemaining(): string
    {
        $cache = \Config\Services::cache();
        $key = $this->cacheKey();
        $data = $cache->get($key);

        $remaining = is_array($data) && isset($data['locked_until']) ? ($data['locked_until'] - time()) : 0;
        $remaining = max(0, $remaining);
        return "{$remaining} seconds";
    }

    private function trackIpAttempt(): void
    {
        $cache = \Config\Services::cache();
        $key = $this->cacheKey();
        $data = $cache->get($key);
        $now = time();

        if (!is_array($data) || (isset($data['locked_until']) && $data['locked_until'] <= $now) || (isset($data['first_attempt']) && ($now - $data['first_attempt'] > self::LOCKOUT_SECONDS))) {
            $attempts = 1;
            $firstAttempt = $now;
        } else {
            $attempts = ($data['attempts'] ?? 0) + 1;
            $firstAttempt = $data['first_attempt'] ?? $now;
        }

        $entry = [
            'attempts'      => $attempts,
            'first_attempt' => $firstAttempt,
        ];

        if ($attempts >= self::MAX_ATTEMPTS) {
            $entry['locked_until'] = $now + self::LOCKOUT_SECONDS;
        }

        $cache->save($key, $entry, self::LOCKOUT_SECONDS * 2);
    }

    public function forgotPassword()
    {
        if (session()->get('isLoggedIn')) {
            return $this->redirectBasedOnRole();
        }
        return view('auth/forgot_password');
    }

    public function sendResetCode()
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

        $email = $user['email'] ?? '';
        if (empty($email)) {
            if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
                $email = $username;
            } else {
                $session->setFlashdata('error', 'No email address associated with this account. Please contact your administrator.');
                return redirect()->to('/forgot-password');
            }
        }

        $db = \Config\Database::connect();

        // Rate limiting: check if a token was created for this username within the last 60 seconds
        $recentToken = $db->table('password_reset_tokens')
            ->where('username', $username)
            ->where('used', 0)
            ->where('created_at >', date('Y-m-d H:i:s', strtotime('-60 seconds')))
            ->get()
            ->getRow();
        if ($recentToken) {
            $session->setFlashdata('error', 'Please wait at least 60 seconds before requesting another verification code.');
            return redirect()->to('/forgot-password');
        }

        // Generate 6-digit code and token
        $resetCode = sprintf('%06d', random_int(0, 999999));
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        // Invalidate old tokens for this username to prevent replay attacks
        $db->table('password_reset_tokens')
            ->where('username', $username)
            ->update(['used' => 1]);

        $db->table('password_reset_tokens')->insert([
            'username'      => $username,
            'token'         => $token,
            'reset_code'    => $resetCode,
            'email'         => $email,
            'expires_at'    => $expiresAt,
            'used'          => 0,
            'verified'      => 0,
            'code_attempts' => 0,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        $cache = \Config\Services::cache();
        $cache->save('otp_resend_' . $token, true, 60);

        // Load Email Service
        $emailSvc = $this->getConfiguredEmailService();
        $emailSvc->setTo($email);
        $emailSvc->setSubject('[Palompon Transit] Password Reset Verification Code');
        $emailSvc->setMessage($this->buildOtpEmailHtml($username, $resetCode, false));

        $devMsg = '';
        if (!$emailSvc->send()) {
            if (ENVIRONMENT === 'development') {
                $devMsg = ' (Local Dev OTP Code: ' . $resetCode . ')';
            } else {
                $session->setFlashdata('error', 'Failed to send verification email. Please check server SMTP configuration.');
                return redirect()->to('/forgot-password');
            }
        }

        $session->setFlashdata('success', 'Verification code sent to your email.' . $devMsg);
        return redirect()->to("/verify-reset-code/{$token}");
    }

    public function verifyResetCode($token)
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
            session()->setFlashdata('error', 'This link is invalid or has expired.');
            return redirect()->to('/forgot-password');
        }

        return view('auth/verify_code', [
            'token'        => $token,
            'masked_email' => $this->maskEmail($record->email),
        ]);
    }

    public function submitResetCode($token)
    {
        $session = session();
        $db = \Config\Database::connect();
        $resetCode = $this->request->getVar('reset_code');

        $record = $db->table('password_reset_tokens')
            ->where('token', $token)
            ->where('used', 0)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->get()
            ->getRow();

        if (!$record) {
            $session->setFlashdata('error', 'This reset session has expired. Please try again.');
            return redirect()->to('/forgot-password');
        }

        if ($record->code_attempts >= 5) {
            $db->table('password_reset_tokens')
                ->where('id', $record->id)
                ->update(['used' => 1]);

            $session->setFlashdata('error', 'Too many failed verification attempts. This request has been invalidated. Please start over.');
            return redirect()->to('/forgot-password');
        }

        if ($record->reset_code !== $resetCode) {
            $newAttempts = $record->code_attempts + 1;
            
            $db->table('password_reset_tokens')
                ->where('id', $record->id)
                ->update(['code_attempts' => $newAttempts]);

            if ($newAttempts >= 5) {
                $db->table('password_reset_tokens')
                    ->where('id', $record->id)
                    ->update(['used' => 1]);
                $session->setFlashdata('error', 'Too many failed verification attempts. This request has been invalidated. Please start over.');
                return redirect()->to('/forgot-password');
            }

            $remaining = 5 - $newAttempts;
            $session->setFlashdata('error', "Invalid verification code. Please check your email and try again. ({$remaining} attempts remaining)");
            return redirect()->to("/verify-reset-code/{$token}");
        }

        // Successfully verified code, extend expires_at and mark verified
        $extendedExpiry = date('Y-m-d H:i:s', strtotime('+30 minutes'));
        $db->table('password_reset_tokens')
            ->where('id', $record->id)
            ->update([
                'verified'   => 1,
                'expires_at' => $extendedExpiry
            ]);

        $session->setFlashdata('success', 'Verification code confirmed. You can now reset your password.');
        return redirect()->to("/reset-password/{$token}");
    }

    public function resendResetCode($token)
    {
        $session = session();
        $db = \Config\Database::connect();

        $cache = \Config\Services::cache();
        $cacheKey = 'otp_resend_' . $token;
        if ($cache->get($cacheKey)) {
            $session->setFlashdata('error', 'Please wait at least 60 seconds before requesting another verification code.');
            return redirect()->to("/verify-reset-code/{$token}");
        }

        $record = $db->table('password_reset_tokens')
            ->where('token', $token)
            ->where('used', 0)
            ->get()
            ->getRow();

        if (!$record) {
            $session->setFlashdata('error', 'Reset session not found. Please start over.');
            return redirect()->to('/forgot-password');
        }

        // Generate new 6-digit code and update expiry
        $resetCode = sprintf('%06d', random_int(0, 999999));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        $db->table('password_reset_tokens')
            ->where('id', $record->id)
            ->update([
                'reset_code'    => $resetCode,
                'expires_at'    => $expiresAt,
                'verified'      => 0,
                'code_attempts' => 0
            ]);

        $cache->save($cacheKey, true, 60);

        // Load Email Service
        $emailSvc = $this->getConfiguredEmailService();
        $emailSvc->setTo($record->email);
        $emailSvc->setSubject('[Palompon Transit] New Password Reset Verification Code');
        $emailSvc->setMessage($this->buildOtpEmailHtml($record->username, $resetCode, true));

        $devMsg = '';
        if (!$emailSvc->send()) {
            if (ENVIRONMENT === 'development') {
                $devMsg = ' (Local Dev OTP Code: ' . $resetCode . ')';
            } else {
                $session->setFlashdata('error', 'Failed to send new verification email. Please check server SMTP configuration.');
                return redirect()->to("/verify-reset-code/{$token}");
            }
        }

        $session->setFlashdata('success', 'A new verification code has been sent.' . $devMsg);
        return redirect()->to("/verify-reset-code/{$token}");
    }

    private function maskEmail(string $email): string
    {
        $emailParts = explode('@', $email);
        $namePart = $emailParts[0];
        $domainPart = $emailParts[1] ?? '';
        
        if (strlen($namePart) <= 2) {
            $maskedName = str_repeat('*', strlen($namePart));
        } else {
            $maskedName = substr($namePart, 0, 1) . str_repeat('*', strlen($namePart) - 2) . substr($namePart, -1);
        }
        return $maskedName . '@' . $domainPart;
    }

    private function buildOtpEmailHtml(string $username, string $code, bool $isResend = false): string
    {
        $introText = $isResend
            ? 'Here is your new verification code to reset the password for the account associated with the username: <strong>' . esc($username) . '</strong>.'
            : 'Someone has requested a verification code to reset the password for the account associated with the username: <strong>' . esc($username) . '</strong>.';
        
        return '
        <div style="font-family: \'Google Sans\', Roboto, Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 40px 20px; border: 1px solid #e0e0e0; border-radius: 12px; background: #ffffff;">
            <div style="text-align: center; margin-bottom: 24px;">
                <span style="font-size: 26px; font-weight: bold; color: #1565c0; letter-spacing: 0.5px;">Palompon Transit</span>
            </div>
            <div style="padding: 10px 0;">
                <h2 style="font-size: 20px; color: #202124; margin-bottom: 16px; font-weight: 600;">Verify your identity</h2>
                <p style="font-size: 14px; color: #5f6368; line-height: 1.5; margin-bottom: 24px;">
                    ' . $introText . '
                </p>
                <p style="font-size: 14px; color: #5f6368; line-height: 1.5; margin-bottom: 8px;">
                    Use this verification code to proceed:
                </p>
                <div style="background: #f1f3f4; padding: 16px 24px; border-radius: 8px; font-size: 32px; font-weight: bold; text-align: center; letter-spacing: 6px; color: #1565c0; margin-bottom: 24px; border: 1px dashed #bbdefb;">
                    ' . $code . '
                </div>
                <p style="font-size: 12px; color: #70757a; line-height: 1.5; margin-bottom: 0;">
                    This code will expire in 10 minutes. If you did not request this, please ignore this email.
                </p>
            </div>
        </div>';
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
            ->where('verified', 1)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->get()
            ->getRow();

        if (!$record) {
            return view('auth/reset_password', [
                'token' => $token,
                'error' => 'This reset session is invalid, unverified, or has expired.',
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
            ->where('verified', 1)
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
            case 'super_admin':
            case 'admin':
                return redirect()->to('/admin/dashboard');
            case 'staff':
                return redirect()->to('/staff/dashboard');
            default:
                return redirect()->to('/guest');
        }
    }
}
