<?= view('templates/header', ['title' => $title ?? 'Change Password']) ?>

<?php
$sessionRole = session()->get('role');
$roleLabel   = match($sessionRole) {
    'super_admin' => 'Super Admin',
    'admin'       => 'Admin',
    'staff'       => 'Dispatcher',
    default       => 'User',
};
$backUrl = in_array($sessionRole, ['super_admin', 'admin'], true) ? base_url('admin/dashboard') : base_url('staff/dashboard');
$fullName = session()->get('full_name') ?: ($user['full_name'] ?? $user['username'] ?? 'User');
$profileImage = session()->get('profile_image') ?: ($user['profile_image'] ?? null);
?>

<div class="change-password-wrapper">
    <div class="change-password-container">
        <!-- Back Link -->
        <div class="mb-3">
            <a href="<?= $backUrl ?>" class="btn-back-dashboard">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Dashboard</span>
            </a>
        </div>

        <!-- Main Card -->
        <div class="change-password-card">
            <!-- Card Header -->
            <div class="cp-card-header">
                <div class="cp-header-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="cp-header-text">
                    <h1 class="cp-title">Change Password</h1>
                    <p class="cp-subtitle">Update your credentials with 6-digit email code authentication</p>
                </div>
            </div>

            <!-- Flash Notifications -->
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger cp-alert" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div><?= session()->getFlashdata('error') ?></div>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success cp-alert" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <div><?= session()->getFlashdata('success') ?></div>
                </div>
            <?php endif; ?>

            <!-- Account Summary Pill -->
            <div class="cp-account-summary">
                <div class="cp-account-avatar">
                    <?php if (!empty($profileImage)): ?>
                        <img src="<?= base_url(esc($profileImage)) ?>" alt="<?= esc($fullName) ?>" class="cp-avatar-img">
                    <?php else: ?>
                        <div class="cp-avatar-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="cp-account-meta">
                    <div class="cp-account-name">
                        <strong><?= esc($fullName) ?></strong>
                        <span class="cp-badge-role"><?= esc($roleLabel) ?></span>
                    </div>
                    <div class="cp-account-email">
                        <i class="fas fa-envelope me-1"></i>
                        <span><?= esc($masked_email) ?></span>
                        <?php if ($has_email): ?>
                            <span class="cp-verified-chip" title="Email registered for code authentication">
                                <i class="fas fa-check"></i> Registered
                            </span>
                        <?php else: ?>
                            <span class="cp-warning-chip" title="No email found for OTP">
                                <i class="fas fa-exclamation-triangle"></i> No Email
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Change Password Form -->
            <form action="<?= base_url('change-password/update') ?>" method="POST" id="changePasswordForm" class="cp-form" novalidate>
                <?= csrf_field() ?>

                <!-- Current Password -->
                <div class="cp-form-group">
                    <label for="current_password" class="cp-form-label">
                        <i class="fas fa-lock me-1 text-muted"></i> Current Password
                    </label>
                    <div class="cp-input-group">
                        <input type="password" 
                               name="current_password" 
                               id="current_password" 
                               class="cp-input" 
                               placeholder="Enter your current password" 
                               required 
                               autocomplete="current-password">
                        <button type="button" class="cp-btn-toggle-pw" onclick="togglePasswordVisibility('current_password', this)" title="Show/hide password" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Verification Code Group -->
                <div class="cp-form-group">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label for="verification_code" class="cp-form-label mb-0">
                            <i class="fas fa-key me-1 text-muted"></i> 6-Digit Email Verification Code
                        </label>
                        <span class="cp-otp-hint">Code expires in 10 minutes</span>
                    </div>
                    <div class="cp-code-row">
                        <input type="text" 
                               name="verification_code" 
                               id="verification_code" 
                               class="cp-input cp-input-code" 
                               placeholder="000000" 
                               maxlength="6" 
                               pattern="\d{6}" 
                               inputmode="numeric" 
                               autocomplete="one-time-code" 
                               required>
                        <button type="button" 
                                id="btnSendOtp" 
                                class="cp-btn-get-code <?= !$has_email ? 'disabled' : '' ?>" 
                                <?= !$has_email ? 'disabled' : '' ?>>
                            <i class="fas fa-paper-plane me-1"></i>
                            <span id="btnSendOtpText">Get Code</span>
                        </button>
                    </div>
                    <!-- Real-time dynamic OTP status notice -->
                    <div id="otpStatusBox" class="cp-otp-status" style="display: none;"></div>
                    <small class="cp-field-help">
                        Click <strong>Get Code</strong> to send a one-time verification code to <strong><?= esc($masked_email) ?></strong>.
                    </small>
                </div>

                <!-- New Password -->
                <div class="cp-form-group">
                    <label for="new_password" class="cp-form-label">
                        <i class="fas fa-shield-alt me-1 text-muted"></i> New Password
                    </label>
                    <div class="cp-input-group">
                        <input type="password" 
                               name="new_password" 
                               id="new_password" 
                               class="cp-input" 
                               placeholder="Enter new password (min. 8 characters)" 
                               minlength="8" 
                               required 
                               autocomplete="new-password">
                        <button type="button" class="cp-btn-toggle-pw" onclick="togglePasswordVisibility('new_password', this)" title="Show/hide password" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="cp-pw-requirements" id="pwRequirements">
                        <span class="req-item" id="reqLength"><i class="fas fa-circle-check"></i> At least 8 characters</span>
                    </div>
                </div>

                <!-- Confirm New Password -->
                <div class="cp-form-group">
                    <label for="confirm_password" class="cp-form-label">
                        <i class="fas fa-check-double me-1 text-muted"></i> Confirm New Password
                    </label>
                    <div class="cp-input-group">
                        <input type="password" 
                               name="confirm_password" 
                               id="confirm_password" 
                               class="cp-input" 
                               placeholder="Re-enter your new password" 
                               minlength="8" 
                               required 
                               autocomplete="new-password">
                        <button type="button" class="cp-btn-toggle-pw" onclick="togglePasswordVisibility('confirm_password', this)" title="Show/hide password" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div id="matchFeedback" class="cp-match-feedback" style="display: none;"></div>
                </div>

                <!-- Form Action Buttons -->
                <div class="cp-actions">
                    <a href="<?= $backUrl ?>" class="cp-btn-cancel">
                        Cancel
                    </a>
                    <button type="submit" id="btnSubmitPassword" class="cp-btn-submit">
                        <i class="fas fa-check"></i>
                        <span>Update Password</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Change Password Container Styles */
.change-password-wrapper {
    width: 100%;
    max-width: 100%;
    padding: 16px 12px 48px;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    box-sizing: border-box;
}

.change-password-container {
    width: 100%;
    max-width: 560px;
    margin: 0 auto;
}

.btn-back-dashboard {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    border-radius: 8px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    color: #475569;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    transition: all .15s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.btn-back-dashboard:hover {
    background: #f8fafc;
    color: #0f172a;
    border-color: #cbd5e1;
    transform: translateX(-2px);
}

.change-password-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.03);
    padding: 28px 30px;
    box-sizing: border-box;
}

/* Header */
.cp-card-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
    padding-bottom: 18px;
    border-bottom: 1px solid #f1f5f9;
}
.cp-header-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(4, 120, 87, 0.25);
}
.cp-header-text {
    flex: 1;
    min-width: 0;
}
.cp-title {
    font-size: 20px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 4px;
    line-height: 1.2;
}
.cp-subtitle {
    font-size: 13px;
    color: #64748b;
    margin: 0;
    line-height: 1.4;
}

/* Alerts */
.cp-alert {
    display: flex;
    align-items: center;
    font-size: 13.5px;
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 20px;
}

/* Account Summary */
.cp-account-summary {
    display: flex;
    align-items: center;
    gap: 14px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 14px 16px;
    margin-bottom: 24px;
}
.cp-account-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    background: #e2e8f0;
    border: 2px solid #ffffff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.06);
}
.cp-avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.cp-avatar-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    font-size: 18px;
}
.cp-account-meta {
    flex: 1;
    min-width: 0;
}
.cp-account-name {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #0f172a;
    margin-bottom: 3px;
}
.cp-badge-role {
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    background: #047857;
    color: #ffffff;
    padding: 2px 8px;
    border-radius: 20px;
}
.cp-account-email {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    color: #64748b;
}
.cp-verified-chip {
    font-size: 10px;
    font-weight: 700;
    color: #047857;
    background: #dcfce7;
    padding: 1px 6px;
    border-radius: 6px;
}
.cp-warning-chip {
    font-size: 10px;
    font-weight: 700;
    color: #b45309;
    background: #fef3c7;
    padding: 1px 6px;
    border-radius: 6px;
}

/* Form */
.cp-form-group {
    margin-bottom: 20px;
}
.cp-form-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 6px;
}
.cp-otp-hint {
    font-size: 11px;
    font-weight: 600;
    color: #047857;
}

.cp-input-group {
    position: relative;
    display: flex;
    align-items: center;
}
.cp-input {
    width: 100%;
    padding: 10px 14px;
    padding-right: 44px;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    font-size: 14px;
    color: #1e293b;
    background: #ffffff;
    transition: all .15s ease;
    box-sizing: border-box;
}
.cp-input:focus {
    outline: none;
    border-color: #059669;
    box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15);
}
.cp-btn-toggle-pw {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    padding: 4px;
    font-size: 15px;
    transition: color .15s ease;
}
.cp-btn-toggle-pw:hover {
    color: #475569;
}

/* 6-Digit Code Row */
.cp-code-row {
    display: flex;
    gap: 10px;
    align-items: stretch;
}
.cp-input-code {
    flex: 1;
    min-width: 0;
    letter-spacing: 4px;
    font-size: 18px;
    font-weight: 700;
    text-align: center;
    font-family: 'Courier New', Courier, monospace;
    padding-right: 14px;
}
.cp-btn-get-code {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px 18px;
    border-radius: 10px;
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    color: #ffffff;
    border: none;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    white-space: nowrap;
    transition: all .15s ease;
    box-shadow: 0 4px 10px rgba(4, 120, 87, 0.2);
}
.cp-btn-get-code:hover:not(:disabled) {
    background: linear-gradient(135deg, #047857 0%, #065f46 100%);
    box-shadow: 0 6px 14px rgba(4, 120, 87, 0.3);
    transform: translateY(-1px);
}
.cp-btn-get-code:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    box-shadow: none;
    transform: none;
}
.cp-field-help {
    display: block;
    margin-top: 6px;
    font-size: 12px;
    color: #64748b;
    line-height: 1.4;
}
.cp-otp-status {
    margin-top: 8px;
    font-size: 12.5px;
    font-weight: 600;
    padding: 8px 12px;
    border-radius: 8px;
}
.cp-otp-status.status-success {
    background: #ecfdf5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}
.cp-otp-status.status-error {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

/* Requirements & Feedback */
.cp-pw-requirements {
    margin-top: 6px;
    font-size: 12px;
}
.req-item {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: #94a3b8;
    font-weight: 500;
}
.req-item.req-valid {
    color: #059669;
}
.cp-match-feedback {
    margin-top: 6px;
    font-size: 12px;
    font-weight: 600;
}
.cp-match-feedback.match-success {
    color: #059669;
}
.cp-match-feedback.match-error {
    color: #dc2626;
}

/* Action Buttons */
.cp-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 28px;
    padding-top: 20px;
    border-top: 1px solid #f1f5f9;
}
.cp-btn-cancel {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 11px 18px;
    border-radius: 10px;
    border: 1.5px solid #cbd5e1;
    background: #ffffff;
    color: #475569;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    text-align: center;
    transition: all .15s ease;
}
.cp-btn-cancel:hover {
    background: #f8fafc;
    border-color: #94a3b8;
    color: #0f172a;
}
.cp-btn-submit {
    flex: 2;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 11px 22px;
    border-radius: 10px;
    border: none;
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    color: #ffffff;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all .15s ease;
    box-shadow: 0 4px 12px rgba(4, 120, 87, 0.25);
}
.cp-btn-submit:hover {
    background: linear-gradient(135deg, #047857 0%, #065f46 100%);
    box-shadow: 0 6px 18px rgba(4, 120, 87, 0.35);
    transform: translateY(-1px);
}

@media (max-width: 480px) {
    .change-password-card {
        padding: 20px 18px;
        border-radius: 14px;
    }
    .cp-header-icon {
        width: 42px;
        height: 42px;
        font-size: 18px;
    }
    .cp-title {
        font-size: 18px;
    }
    .cp-code-row {
        flex-direction: column;
    }
    .cp-btn-get-code {
        width: 100%;
    }
    .cp-actions {
        flex-direction: column-reverse;
    }
    .cp-btn-cancel, .cp-btn-submit {
        width: 100%;
        flex: none;
    }
}
</style>

<script>
// Show/Hide Password Visibility Toggle
function togglePasswordVisibility(inputId, btn) {
    var input = document.getElementById(inputId);
    if (!input) return;
    var icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        if (icon) {
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    } else {
        input.type = 'password';
        if (icon) {
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
}

// Live Validation: Length & Password Match
document.addEventListener('DOMContentLoaded', function() {
    var newPwInput = document.getElementById('new_password');
    var confirmPwInput = document.getElementById('confirm_password');
    var reqLength = document.getElementById('reqLength');
    var matchFeedback = document.getElementById('matchFeedback');
    var btnSendOtp = document.getElementById('btnSendOtp');
    var btnSendOtpText = document.getElementById('btnSendOtpText');
    var otpStatusBox = document.getElementById('otpStatusBox');
    var cpForm = document.getElementById('changePasswordForm');

    function checkRequirements() {
        var val = newPwInput ? newPwInput.value : '';
        if (val.length >= 8) {
            reqLength.classList.add('req-valid');
        } else {
            reqLength.classList.remove('req-valid');
        }
        checkMatch();
    }

    function checkMatch() {
        var p1 = newPwInput ? newPwInput.value : '';
        var p2 = confirmPwInput ? confirmPwInput.value : '';
        if (!p2) {
            matchFeedback.style.display = 'none';
            matchFeedback.innerHTML = '';
            return;
        }
        matchFeedback.style.display = 'block';
        if (p1 === p2) {
            matchFeedback.className = 'cp-match-feedback match-success';
            matchFeedback.innerHTML = '<i class="fas fa-check me-1"></i> Passwords match';
        } else {
            matchFeedback.className = 'cp-match-feedback match-error';
            matchFeedback.innerHTML = '<i class="fas fa-times me-1"></i> Passwords do not match';
        }
    }

    if (newPwInput) newPwInput.addEventListener('input', checkRequirements);
    if (confirmPwInput) confirmPwInput.addEventListener('input', checkMatch);

    // OTP Resend Cooldown Countdown
    var countdownTimer = null;
    function startOtpCooldown(seconds) {
        if (!btnSendOtp) return;
        btnSendOtp.disabled = true;
        var remaining = seconds;
        btnSendOtpText.textContent = 'Resend (' + remaining + 's)';

        if (countdownTimer) clearInterval(countdownTimer);
        countdownTimer = setInterval(function() {
            remaining--;
            if (remaining <= 0) {
                clearInterval(countdownTimer);
                btnSendOtp.disabled = false;
                btnSendOtpText.textContent = 'Get Code';
            } else {
                btnSendOtpText.textContent = 'Resend (' + remaining + 's)';
            }
        }, 1000);
    }

    // Send OTP Code via AJAX
    if (btnSendOtp) {
        btnSendOtp.addEventListener('click', function() {
            if (btnSendOtp.disabled) return;
            
            var originalText = btnSendOtpText.textContent;
            btnSendOtp.disabled = true;
            btnSendOtpText.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Sending...';
            otpStatusBox.style.display = 'none';

            // Fetch CSRF Token
            var csrfMeta = document.querySelector('meta[name="csrf-token"]');
            var csrfHeader = document.querySelector('meta[name="csrf-header"]');
            var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
            var csrfName = csrfHeader ? csrfHeader.getAttribute('content') : 'X-CSRF-TOKEN';

            var formData = new FormData();
            var csrfInput = document.querySelector('input[name="<?= csrf_token() ?>"]');
            if (csrfInput) {
                formData.append('<?= csrf_token() ?>', csrfInput.value);
            }

            fetch('<?= base_url('change-password/send-code') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    [csrfName]: csrfToken
                },
                body: formData
            })
            .then(function(res) {
                return res.json().catch(function() {
                    return { status: 'error', message: 'Server returned an unexpected response. Please try again.' };
                });
            })
            .then(function(data) {
                otpStatusBox.style.display = 'block';
                if (data.status === 'success') {
                    otpStatusBox.className = 'cp-otp-status status-success';
                    otpStatusBox.innerHTML = '<i class="fas fa-check-circle me-1"></i> ' + (data.message || 'Verification code sent to your email.');
                    startOtpCooldown(data.cooldown || 60);
                    // Focus on code input
                    var codeInput = document.getElementById('verification_code');
                    if (codeInput) codeInput.focus();
                } else {
                    otpStatusBox.className = 'cp-otp-status status-error';
                    otpStatusBox.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> ' + (data.message || 'Could not send verification code.');
                    btnSendOtp.disabled = false;
                    btnSendOtpText.textContent = originalText;
                }
            })
            .catch(function(err) {
                otpStatusBox.style.display = 'block';
                otpStatusBox.className = 'cp-otp-status status-error';
                otpStatusBox.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i> Connection error. Please check your network and try again.';
                btnSendOtp.disabled = false;
                btnSendOtpText.textContent = originalText;
            });
        });
    }

    // Client-side Form Submit Validation
    if (cpForm) {
        cpForm.addEventListener('submit', function(e) {
            var p1 = newPwInput ? newPwInput.value : '';
            var p2 = confirmPwInput ? confirmPwInput.value : '';
            var code = document.getElementById('verification_code') ? document.getElementById('verification_code').value.trim() : '';
            var cur = document.getElementById('current_password') ? document.getElementById('current_password').value : '';

            if (!cur) {
                e.preventDefault();
                alert('Please enter your current password.');
                document.getElementById('current_password').focus();
                return false;
            }

            if (!code || code.length !== 6) {
                e.preventDefault();
                alert('Please enter the complete 6-digit verification code.');
                document.getElementById('verification_code').focus();
                return false;
            }

            if (p1.length < 8) {
                e.preventDefault();
                alert('New password must be at least 8 characters long.');
                newPwInput.focus();
                return false;
            }

            if (p1 !== p2) {
                e.preventDefault();
                alert('New password and confirm password do not match.');
                confirmPwInput.focus();
                return false;
            }
        });
    }
});
</script>

<?= view('templates/footer') ?>
