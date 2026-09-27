<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Auth extends BaseController
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_SECONDS = 900;

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
        $username = trim((string) $this->request->getVar('username'));
        $password = (string) $this->request->getVar('password');

        $user = $model->where('username', $username)
            ->orWhere('email', $username)
            ->first();

        if ($user) {
            if (($user['status'] ?? 'active') === 'archived') {
                $session->setFlashdata('error', 'This account has been deactivated. Please contact the administrator.');
                return redirect()->to('/login');
            }

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
                    'profile_image' => $user['profile_image'] ?? null,
                    'profile_image_synced_at' => time(),
                    'isLoggedIn' => TRUE
                ];
                $session->set($ses_data);
                $session->regenerate();

                $this->logActivity('Login', ucfirst($user['role']) . ' logged in (' . $user['username'] . ')');

                return $this->redirectBasedOnRole();
            }

            $attempts = (int) ($user['login_attempts'] ?? 0) + 1;
            $data = ['login_attempts' => $attempts];
            if ($attempts >= self::MAX_ATTEMPTS) {
                $data['locked_until'] = date('Y-m-d H:i:s', time() + self::LOCKOUT_SECONDS);
            }
            $model->update($user['id'], $data);

            $msg = $attempts >= self::MAX_ATTEMPTS
                ? "Account locked due to too many failed attempts. Try again in 15 minutes."
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
        if (((int) ($user['login_attempts'] ?? 0)) < self::MAX_ATTEMPTS) {
            return false;
        }
        if (empty($user['locked_until'])) {
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
        if ($remaining >= 60) {
            $mins = (int) ceil($remaining / 60);
            return "{$mins} minute" . ($mins === 1 ? '' : 's');
        }
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
        $username = trim((string) $this->request->getVar('username'));

        if (empty($username)) {
            $session->setFlashdata('error', 'Please enter your username or email address.');
            return redirect()->to('/forgot-password')->withInput();
        }

        $model = new UserModel();
        $user = $model->where('username', $username)->orWhere('email', $username)->first();

        if (!$user || ($user['status'] ?? 'active') === 'archived') {
            // Generic message to prevent username/email enumeration.
            $session->setFlashdata('error', 'If an account exists for that username or email, a verification code has been sent.');
            return redirect()->to('/forgot-password')->withInput();
        }

        $accountUsername = $user['username'];
        $email = $user['email'] ?? '';
        if (empty($email)) {
            if (filter_var($accountUsername, FILTER_VALIDATE_EMAIL)) {
                $email = $accountUsername;
            } else {
                $session->setFlashdata('error', 'No email address associated with this account. Please contact your administrator.');
                return redirect()->to('/forgot-password')->withInput();
            }
        }

        $db = \Config\Database::connect();

        // Rate limiting: check if a token was created for this username within the last 60 seconds
        $recentToken = $db->table('password_reset_tokens')
            ->where('username', $accountUsername)
            ->where('used', 0)
            ->where('created_at >', date('Y-m-d H:i:s', strtotime('-60 seconds')))
            ->get()
            ->getRow();
        if ($recentToken) {
            $session->setFlashdata('error', 'A verification code was already requested recently. Please check your email or wait before requesting another.');
            return redirect()->to("/verify-reset-code/{$recentToken->token}");
        }

        // Generate 6-digit code and token
        $resetCode = sprintf('%06d', random_int(0, 999999));
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        // Invalidate old unused tokens for this username
        $db->table('password_reset_tokens')
            ->where('username', $accountUsername)
            ->update(['used' => 1]);

        $db->table('password_reset_tokens')->insert([
            'user_id'       => $user['id'] ?? null,
            'username'      => $accountUsername,
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

        $devMsg = '';
        if (!$this->sendConfiguredHtmlEmail(
            $email,
            '[' . app_name() . '] Password Reset Verification Code',
            $this->buildOtpEmailHtml($accountUsername, $resetCode, false)
        )) {
            log_message('error', 'Failed to send OTP verification email.');
            if (ENVIRONMENT === 'development') {
                $devMsg = ' (Local Dev OTP Code: ' . $resetCode . ')';
            } else {
                // Do not leave a dead reset session or rate-limit after a
                // message that never reached the account owner.
                $db->table('password_reset_tokens')->where('token', $token)->delete();
                $cache->delete('otp_resend_' . $token);
                $session->setFlashdata('error', $this->getEmailDeliveryErrorMessage());
                return redirect()->to('/forgot-password')->withInput();
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
            session()->setFlashdata('error', 'This reset link is invalid or has expired. Please request a new code.');
            return redirect()->to('/forgot-password');
        }

        if ($record->verified == 1) {
            return redirect()->to("/reset-password/{$token}");
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
        $resetCode = trim((string) $this->request->getVar('reset_code'));

        $record = $db->table('password_reset_tokens')
            ->where('token', $token)
            ->where('used', 0)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->get()
            ->getRow();

        if (!$record) {
            $session->setFlashdata('error', 'This reset session has expired. Please request a new code.');
            return redirect()->to('/forgot-password');
        }

        if (empty($resetCode) || strlen($resetCode) !== 6 || !ctype_digit($resetCode)) {
            $session->setFlashdata('error', 'Please enter the complete 6-digit verification code.');
            return redirect()->to("/verify-reset-code/{$token}");
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
            $session->setFlashdata('error', "Invalid verification code. Please check your email and try again. ({$remaining} attempt" . ($remaining === 1 ? '' : 's') . " remaining)");
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

        $devMsg = '';
        if (!$this->sendConfiguredHtmlEmail(
            $record->email,
            '[' . app_name() . '] New Password Reset Verification Code',
            $this->buildOtpEmailHtml($record->username, $resetCode, true)
        )) {
            log_message('error', 'Failed to resend OTP verification email.');
            if (ENVIRONMENT === 'development') {
                $devMsg = ' (Local Dev OTP Code: ' . $resetCode . ')';
            } else {
                // Permit an immediate retry when no message was delivered.
                $cache->delete($cacheKey);
                $session->setFlashdata('error', $this->getEmailDeliveryErrorMessage());
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
                <span style="font-size: 26px; font-weight: bold; color: #1565c0; letter-spacing: 0.5px;">' . esc(app_name()) . '</span>
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
                'token'   => $token,
                'error'   => 'This reset session is invalid, unverified, or has expired.',
                'invalid' => true,
            ]);
        }

        return view('auth/reset_password', [
            'token'   => $token,
            'error'   => null,
            'invalid' => false,
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

        $password = (string) $this->request->getVar('password');
        $confirm = (string) $this->request->getVar('confirm_password');

        if (strlen($password) < 8) {
            return view('auth/reset_password', [
                'token'   => $token,
                'error'   => 'Password must be at least 8 characters.',
                'invalid' => false,
            ]);
        }

        if ($password !== $confirm) {
            return view('auth/reset_password', [
                'token'   => $token,
                'error'   => 'Passwords do not match.',
                'invalid' => false,
            ]);
        }

        $model = new UserModel();
        $user = $model->where('username', $record->username)->first();

        if (!$user) {
            $session->setFlashdata('error', 'User not found.');
            return redirect()->to('/login');
        }

        $model->update($user['id'], [
            'password_hash'  => password_hash($password, PASSWORD_DEFAULT),
            'login_attempts' => 0,
            'locked_until'   => null,
        ]);

        $db->table('password_reset_tokens')
            ->where('id', $record->id)
            ->update(['used' => 1]);

        $this->logActivity('Password Reset', 'Password reset for ' . $record->username);

        $session->setFlashdata('success', 'Password reset successful. You can now log in.');
        return redirect()->to('/login');
    }

    public function changePassword()
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Only dispatchers use this self-service email OTP change password flow; Admins manage passwords in User Management
        if ($session->get('role') !== 'staff') {
            return redirect()->to('/admin/users');
        }

        $userId = $session->get('id');
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            $session->setFlashdata('error', 'User account not found.');
            return redirect()->to('/login');
        }

        $email = $user['email'] ?? '';
        if (empty($email) && filter_var($user['username'], FILTER_VALIDATE_EMAIL)) {
            $email = $user['username'];
        }

        $db = \Config\Database::connect();
        $hasActiveToken = (bool) $db->table('password_reset_tokens')
            ->where('username', $user['username'])
            ->where('used', 0)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->countAllResults();

        return view('auth/change_password', [
            'title'            => 'Change Password',
            'user'             => $user,
            'email'            => $email,
            'masked_email'     => !empty($email) ? $this->maskEmail($email) : 'No email registered',
            'has_email'        => !empty($email),
            'has_active_token' => $hasActiveToken,
        ]);
    }

    public function sendChangePasswordCode()
    {
        $session = session();
        $isAjax = $this->request->isAJAX();

        if (!$session->get('isLoggedIn')) {
            if ($isAjax) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized'])->setStatusCode(401);
            }
            return redirect()->to('/login');
        }

        if ($session->get('role') !== 'staff') {
            if ($isAjax) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Change password is only available for dispatchers. Admins can update passwords in User Management.'])->setStatusCode(403);
            }
            return redirect()->to('/admin/users');
        }

        $userId = $session->get('id');
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            if ($isAjax) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'User account not found.']);
            }
            $session->setFlashdata('error', 'User account not found.');
            return redirect()->to('/change-password');
        }

        $email = $user['email'] ?? '';
        if (empty($email) && filter_var($user['username'], FILTER_VALIDATE_EMAIL)) {
            $email = $user['username'];
        }

        if (empty($email)) {
            $msg = 'No email address is associated with your account. Please contact your system administrator.';
            if ($isAjax) {
                return $this->response->setJSON(['status' => 'error', 'message' => $msg]);
            }
            $session->setFlashdata('error', $msg);
            return redirect()->to('/change-password');
        }

        $db = \Config\Database::connect();

        // Rate limiting: check if a token was created for this username within the last 60 seconds
        $recentToken = $db->table('password_reset_tokens')
            ->where('username', $user['username'])
            ->where('used', 0)
            ->where('created_at >', date('Y-m-d H:i:s', strtotime('-60 seconds')))
            ->get()
            ->getRow();

        if ($recentToken) {
            $msg = 'A verification code was already requested recently. Please check your email or wait 60 seconds.';
            if ($isAjax) {
                return $this->response->setJSON(['status' => 'error', 'message' => $msg]);
            }
            $session->setFlashdata('error', $msg);
            return redirect()->to('/change-password');
        }

        // Generate 6-digit code and token
        $resetCode = sprintf('%06d', random_int(0, 999999));
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        // Invalidate old unused tokens for this username
        $db->table('password_reset_tokens')
            ->where('username', $user['username'])
            ->update(['used' => 1]);

        $db->table('password_reset_tokens')->insert([
            'user_id'       => $user['id'],
            'username'      => $user['username'],
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
        $cache->save('otp_resend_cp_' . $user['id'], true, 60);

        $devMsg = '';
        if (!$this->sendConfiguredHtmlEmail(
            $email,
            '[' . app_name() . '] Password Change Verification Code',
            $this->buildChangePasswordEmailHtml($user['full_name'] ?: $user['username'], $resetCode)
        )) {
            log_message('error', 'Failed to send OTP verification email for password change.');
            if (ENVIRONMENT === 'development') {
                $devMsg = ' (Local Dev OTP Code: ' . $resetCode . ')';
            } else {
                $db->table('password_reset_tokens')->where('token', $token)->delete();
                $cache->delete('otp_resend_cp_' . $user['id']);
                $msg = $this->getEmailDeliveryErrorMessage();
                if ($isAjax) {
                    return $this->response->setJSON(['status' => 'error', 'message' => $msg]);
                }
                $session->setFlashdata('error', $msg);
                return redirect()->to('/change-password');
            }
        }

        $successMsg = 'Verification code sent to ' . $this->maskEmail($email) . '.' . $devMsg;
        if ($isAjax) {
            return $this->response->setJSON([
                'status'   => 'success',
                'message'  => $successMsg,
                'cooldown' => 60,
            ]);
        }

        $session->setFlashdata('success', $successMsg);
        return redirect()->to('/change-password');
    }

    public function updateChangedPassword()
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = $session->get('id');
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            $session->setFlashdata('error', 'User account not found.');
            return redirect()->to('/login');
        }

        $currentPassword = (string) $this->request->getPost('current_password');
        $verificationCode = trim((string) $this->request->getPost('verification_code'));
        $newPassword = (string) $this->request->getPost('new_password');
        $confirmPassword = (string) $this->request->getPost('confirm_password');

        // 1. Verify current password
        if (empty($currentPassword) || !password_verify($currentPassword, $user['password_hash'])) {
            $session->setFlashdata('error', 'Your current password is incorrect.');
            return redirect()->to('/change-password')->withInput();
        }

        // 2. Validate new password length and confirmation
        if (strlen($newPassword) < 8) {
            $session->setFlashdata('error', 'New password must be at least 8 characters in length.');
            return redirect()->to('/change-password')->withInput();
        }

        if ($newPassword !== $confirmPassword) {
            $session->setFlashdata('error', 'New password and confirm password do not match.');
            return redirect()->to('/change-password')->withInput();
        }

        if ($currentPassword === $newPassword) {
            $session->setFlashdata('error', 'New password must be different from your current password.');
            return redirect()->to('/change-password')->withInput();
        }

        // 3. Verify OTP Code
        if (empty($verificationCode) || strlen($verificationCode) !== 6 || !ctype_digit($verificationCode)) {
            $session->setFlashdata('error', 'Please enter a valid 6-digit verification code.');
            return redirect()->to('/change-password')->withInput();
        }

        $db = \Config\Database::connect();
        $record = $db->table('password_reset_tokens')
            ->where('username', $user['username'])
            ->where('used', 0)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->orderBy('id', 'DESC')
            ->get()
            ->getRow();

        if (!$record) {
            $session->setFlashdata('error', 'Verification code has expired or was not requested. Please click "Get Code" to request a new code.');
            return redirect()->to('/change-password')->withInput();
        }

        if ($record->code_attempts >= 5) {
            $db->table('password_reset_tokens')->where('id', $record->id)->update(['used' => 1]);
            $session->setFlashdata('error', 'Too many failed verification attempts. Please request a new code.');
            return redirect()->to('/change-password');
        }

        if ($record->reset_code !== $verificationCode) {
            $newAttempts = $record->code_attempts + 1;
            $db->table('password_reset_tokens')->where('id', $record->id)->update(['code_attempts' => $newAttempts]);

            if ($newAttempts >= 5) {
                $db->table('password_reset_tokens')->where('id', $record->id)->update(['used' => 1]);
                $session->setFlashdata('error', 'Too many failed verification attempts. Please request a new code.');
                return redirect()->to('/change-password');
            }

            $remaining = 5 - $newAttempts;
            $session->setFlashdata('error', "Invalid verification code. Please check your email. ({$remaining} attempt" . ($remaining === 1 ? '' : 's') . " remaining)");
            return redirect()->to('/change-password')->withInput();
        }

        // 4. Update password
        $userModel->update($user['id'], [
            'password_hash'  => password_hash($newPassword, PASSWORD_DEFAULT),
            'login_attempts' => 0,
            'locked_until'   => null,
        ]);

        $db->table('password_reset_tokens')->where('id', $record->id)->update([
            'used'     => 1,
            'verified' => 1,
        ]);

        $this->logActivity('Password Changed', 'Password changed with code authentication for ' . $user['username']);

        $session->setFlashdata('success', 'Your password has been changed successfully.');
        return $this->redirectBasedOnRole();
    }

    private function buildChangePasswordEmailHtml(string $displayName, string $code): string
    {
        return '
        <div style="font-family: \'Google Sans\', Roboto, Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 40px 20px; border: 1px solid #e0e0e0; border-radius: 12px; background: #ffffff;">
            <div style="text-align: center; margin-bottom: 24px;">
                <span style="font-size: 26px; font-weight: bold; color: #047857; letter-spacing: 0.5px;">' . esc(app_name()) . '</span>
            </div>
            <div style="padding: 10px 0;">
                <h2 style="font-size: 20px; color: #202124; margin-bottom: 16px; font-weight: 600;">Password Change Verification</h2>
                <p style="font-size: 14px; color: #5f6368; line-height: 1.5; margin-bottom: 20px;">
                    Hello <strong>' . esc($displayName) . '</strong>,
                </p>
                <p style="font-size: 14px; color: #5f6368; line-height: 1.5; margin-bottom: 24px;">
                    A request was made to change the password on your ' . esc(app_name()) . ' account. Please enter the verification code below to authorize this change:
                </p>
                <div style="background: #f0fdf4; padding: 16px 24px; border-radius: 8px; font-size: 32px; font-weight: bold; text-align: center; letter-spacing: 6px; color: #047857; margin-bottom: 24px; border: 1px dashed #86efac;">
                    ' . $code . '
                </div>
                <p style="font-size: 12px; color: #70757a; line-height: 1.5; margin-bottom: 0;">
                    This code is valid for 10 minutes. If you did not make this request, please review your account immediately or notify the administrator.
                </p>
            </div>
        </div>';
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

    private function getEmailDeliveryErrorMessage(): string
    {
        $config = config('Email');
        $hasBrevo = trim($config->brevoApiKey) !== '' && filter_var(trim($config->fromEmail ?: $config->SMTPUser), FILTER_VALIDATE_EMAIL);
        $hasSmtp  = trim($config->SMTPUser) !== '' && trim($config->SMTPPass) !== '';

        if (!$hasBrevo && !$hasSmtp) {
            return 'Email service is not yet configured on this server. Please configure BREVO_API_KEY (or SMTP credentials) in your server environment variables.';
        }

        return 'Failed to send verification email. Please check server email/SMTP configuration.';
    }

}
