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

$nameParts = explode(' ', trim($fullName));
$initials = '';
if (!empty($nameParts[0])) {
    $initials .= strtoupper(substr($nameParts[0], 0, 1));
}
if (count($nameParts) >= 2 && !empty($nameParts[count($nameParts) - 1])) {
    $initials .= strtoupper(substr($nameParts[count($nameParts) - 1], 0, 1));
}
$defaultAvatarSvg = <<<SVG
<svg class="profile-identicon-svg" viewBox="0 0 64 64" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <circle cx="32" cy="32" r="31" fill="#F1F5F9" stroke="#CBD5E1" stroke-width="2" />
    <path d="M32 32.5a9.5 9.5 0 1 0 0-19 9.5 9.5 0 0 0 0 19zm0 5c-9.2 0-17 5.8-17 13.5 0 1 .8 1.8 1.8 1.8h30.4c1 0 1.8-.8 1.8-1.8 0-7.7-7.8-13.5-17-13.5z" fill="#475569" />
</svg>
SVG;
?>

<div class="change-password-page form-entry-page fade-in">
    <!-- Standard Page Header -->
    <div class="page-header-modern mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 w-100">
            <div>
                <h1 class="page-title-modern mb-1">
                    <i class="bi bi-shield-lock-fill" style="color: var(--primary, #15803d);"></i>
                    Account Security & Password
                </h1>
                <p class="text-muted mb-0" style="font-size: 13.5px;">
                    Manage your credentials and protect your account with 2-step email verification
                </p>
            </div>
            <a href="<?= $backUrl ?>" class="btn-modern btn-modern-outline">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- 2-Column Responsive Layout -->
    <div class="row g-4">
        <!-- Left Column: User Identity & Security Guidance -->
        <div class="col-lg-4 order-2 order-lg-1">
            <!-- Profile Overview Card -->
            <div class="modern-card shadow-modern mb-4">
                <div class="modern-card-body text-center p-4">
                    <div class="profile-avatar-wrapper mb-3">
                        <div class="avatar-circle-lg <?= $sessionRole === 'super_admin' ? 'avatar-super-admin' : ($sessionRole === 'admin' ? 'avatar-admin' : 'avatar-dispatcher') ?>">
                            <?php if (!empty($profileImage)): ?>
                                <img src="<?= esc(media_url($profileImage)) ?>" alt="<?= esc($fullName) ?>" class="avatar-img-lg">
                            <?php else: ?>
                                <?= $defaultAvatarSvg ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h4 class="fw-bold mb-1" style="font-size: 1.15rem; color: #0f172a;"><?= esc($fullName) ?></h4>
                    <div class="mb-3">
                        <?php if ($sessionRole === 'super_admin'): ?>
                            <span class="profile-role-pill pill-super_admin">
                                <i class="fas fa-shield-alt"></i> Super Admin
                            </span>
                        <?php elseif ($sessionRole === 'admin'): ?>
                            <span class="profile-role-pill pill-admin">
                                <i class="fas fa-shield-alt"></i> Admin
                            </span>
                        <?php else: ?>
                            <span class="profile-role-pill pill-staff">
                                <i class="fas fa-user-gear"></i> Dispatcher
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="p-2 rounded-3 bg-light text-start mb-2" style="font-size: 13px; border: 1px solid #e2e8f0;">
                        <div class="d-flex align-items-center text-muted">
                            <i class="bi bi-envelope-at me-2 text-primary"></i>
                            <span class="text-truncate"><strong>Username / Email:</strong> <?= esc($user['username'] ?? '') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Password Guidelines Card -->
            <div class="modern-card shadow-modern">
                <div class="modern-card-header py-3 px-4" style="border-bottom: 1px solid #f1f5f9; background: #fafafa;">
                    <span class="modern-card-title" style="font-size: 14px; font-weight: 700; color: #334155;">
                        <i class="bi bi-shield-check text-primary me-2"></i>Security Guidelines
                    </span>
                </div>
                <div class="modern-card-body p-3" style="font-size: 13px; color: #475569;">
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-1" style="font-size: 13px;"></i>
                            <span>Password must contain at least <strong>8 characters</strong>.</span>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-1" style="font-size: 13px;"></i>
                            <span>Never share your password or email OTP code with anyone.</span>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-clock-history text-muted mt-1" style="font-size: 13px;"></i>
                            <span>Verification codes expire in <strong>10 minutes</strong>.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Right Column: Password Update Form Card -->
        <div class="col-lg-8 order-1 order-lg-2">
            <div class="modern-card shadow-modern">
                <div class="modern-card-header py-3 px-4 d-flex align-items-center justify-content-between" style="border-bottom: 1px solid #f1f5f9;">
                    <span class="modern-card-title" style="font-size: 15px; font-weight: 700; color: #0f172a;">
                        <i class="bi bi-key-fill text-danger me-2"></i>Update Password
                    </span>
                    <span class="badge bg-light text-muted border" style="font-size: 11px; font-weight: 600;">
                        OTP Required
                    </span>
                </div>

                <div class="modern-card-body p-4">
                    <!-- Flash Notifications -->
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert-modern alert-modern-success mb-4 fade-in">
                            <i class="bi bi-check-circle-fill alert-modern-icon"></i>
                            <div><?= session()->getFlashdata('success') ?></div>
                        </div>
                    <?php endif; ?>

                    <?php
                    $flashError = session()->getFlashdata('error');
                    $isCurrentPwError = !empty($flashError) && (stripos($flashError, 'current password') !== false);
                    $isCodeError = !empty($flashError) && (stripos($flashError, 'verification code') !== false || stripos($flashError, 'get code') !== false || stripos($flashError, 'expired') !== false);
                    ?>

                    <?php if (!empty($flashError)): ?>
                        <div class="alert-modern alert-modern-danger mb-4 fade-in" data-permanent="true">
                            <i class="bi bi-exclamation-triangle-fill alert-modern-icon"></i>
                            <div><?= esc($flashError) ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('errors')): ?>
                        <div class="alert-modern alert-modern-danger mb-4 fade-in" data-permanent="true">
                            <i class="bi bi-exclamation-triangle-fill alert-modern-icon"></i>
                            <div>
                                <?php if (is_array(session()->getFlashdata('errors'))): ?>
                                    <ul class="mb-0 ps-3">
                                        <?php foreach (session()->getFlashdata('errors') as $err): ?>
                                            <li><?= esc($err) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <?= esc(session()->getFlashdata('errors')) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Form -->
                    <form action="<?= base_url('change-password/update') ?>" method="POST" id="changePasswordForm" class="cp-enterprise-form">
                        <?= csrf_field() ?>

                        <!-- Current Password -->
                        <div class="form-group-modern mb-3">
                            <label for="current_password" class="form-label-modern">
                                Current Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-password-wrapper">
                                <input type="password" 
                                       name="current_password" 
                                       id="current_password" 
                                       class="form-control-modern input-with-toggle <?= $isCurrentPwError ? 'is-invalid' : '' ?>"
                                       placeholder="Enter your current password" 
                                       required 
                                       oninvalid="this.setCustomValidity(this.validity.valueMissing ? 'Please fill out this field.' : '')"
                                       oninput="this.setCustomValidity('')"
                                       autocomplete="current-password">
                                <button type="button" class="btn-toggle-eye" onclick="togglePasswordVisibility('current_password', this)" title="Show/hide password" aria-label="Toggle password visibility">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <?php if ($isCurrentPwError): ?>
                                <div class="text-danger small mt-1 fw-semibold d-flex align-items-center gap-1" id="currentPwFeedback">
                                    <i class="bi bi-exclamation-circle-fill"></i> <?= esc($flashError) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- 6-Digit Email Verification Code Group -->
                        <div class="form-group-modern mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1 otp-heading">
                                <label for="verification_code" class="form-label-modern mb-0">
                                    6-Digit Verification Code <span class="text-danger">*</span>
                                </label>
                                <span class="badge text-secondary bg-light" style="font-size: 11.5px; font-weight: 500;">
                                    Sent to <?= esc($masked_email) ?>
                                </span>
                            </div>

                            <div class="d-flex gap-2 otp-entry-row">
                                <div class="position-relative flex-grow-1">
                                    <input type="text" 
                                           name="verification_code" 
                                           id="verification_code" 
                                           class="form-control-modern otp-input-field <?= $isCodeError ? 'is-invalid' : '' ?>"
                                           placeholder="000000" 
                                           maxlength="6" 
                                           pattern="\d{6}" 
                                           inputmode="numeric" 
                                           autocomplete="one-time-code" 
                                           value="<?= esc(old('verification_code', '')) ?>"
                                           required
                                           oninvalid="this.setCustomValidity(this.validity.valueMissing ? 'Please click \'Get Code\' to receive your code, then enter it here.' : 'Please enter a valid 6-digit code.')"
                                           oninput="this.setCustomValidity('')">
                                </div>
                                <button type="button" 
                                        id="btnSendOtp" 
                                        class="btn-modern btn-modern-primary btn-otp-action flex-shrink-0 <?= !$has_email ? 'disabled' : '' ?>" 
                                        <?= !$has_email ? 'disabled' : '' ?>>
                                    <i class="bi bi-send me-1"></i>
                                    <span id="btnSendOtpText">Get Code</span>
                                </button>
                            </div>
                            <?php if ($isCodeError): ?>
                                <div class="text-danger small mt-1 fw-semibold d-flex align-items-center gap-1" id="codeFeedback">
                                    <i class="bi bi-exclamation-circle-fill"></i> <?= esc($flashError) ?>
                                </div>
                            <?php endif; ?>

                            <!-- Real-time dynamic OTP status notice -->
                            <div id="otpStatusBox" class="mt-2 p-2 rounded-3" style="display: none; font-size: 13px; font-weight: 500;"></div>
                            
                            <div class="form-text" style="font-size: 12px; color: #64748b;">
                                Click <strong>Get Code</strong> to receive your 6-digit confirmation code via email.
                            </div>
                        </div>

                        <hr class="my-4" style="border-color: #f1f5f9;">

                        <!-- New Password -->
                        <div class="form-group-modern mb-3">
                            <label for="new_password" class="form-label-modern">
                                New Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-password-wrapper">
                                <input type="password" 
                                       name="new_password" 
                                       id="new_password" 
                                       class="form-control-modern input-with-toggle" 
                                       placeholder="Enter at least 8 characters" 
                                       minlength="8" 
                                       required 
                                       oninvalid="this.setCustomValidity(this.validity.valueMissing ? 'Please fill out this field.' : (this.validity.tooShort ? 'Please enter at least 8 characters.' : ''))"
                                       oninput="this.setCustomValidity('')"
                                       autocomplete="new-password">
                                <button type="button" class="btn-toggle-eye" onclick="togglePasswordVisibility('new_password', this)" title="Show/hide password" aria-label="Toggle password visibility">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="mt-1" id="pwRequirements">
                                <span class="req-item" id="reqLength">
                                    <i class="bi bi-check-circle"></i> Minimum 8 characters
                                </span>
                            </div>
                        </div>

                        <!-- Confirm New Password -->
                        <div class="form-group-modern mb-4">
                            <label for="confirm_password" class="form-label-modern">
                                Confirm New Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-password-wrapper">
                                <input type="password" 
                                       name="confirm_password" 
                                       id="confirm_password" 
                                       class="form-control-modern input-with-toggle" 
                                       placeholder="Re-enter your new password" 
                                       minlength="8" 
                                       required 
                                       oninvalid="this.setCustomValidity(this.validity.valueMissing ? 'Please fill out this field.' : '')"
                                       oninput="this.setCustomValidity('')"
                                       autocomplete="new-password">
                                <button type="button" class="btn-toggle-eye" onclick="togglePasswordVisibility('confirm_password', this)" title="Show/hide password" aria-label="Toggle password visibility">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div id="matchFeedback" class="mt-1" style="display: none; font-size: 12.5px; font-weight: 600;"></div>
                        </div>

                        <!-- Form Actions -->
                        <div class="change-password-actions d-flex align-items-center justify-content-end gap-2 pt-3" style="border-top: 1px solid #f1f5f9;">
                            <a href="<?= $backUrl ?>" class="btn-modern btn-modern-outline">
                                Cancel
                            </a>
                            <button type="submit" id="btnSubmitPassword" class="btn-modern btn-modern-success">
                                <i class="bi bi-check2-circle me-1"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════ -->
<!--  System Warning Alert Modal (No "localhost says")  -->
<!-- ══════════════════════════════════════════════════ -->
<div class="modal fade" id="systemWarningModal" tabindex="-1" aria-labelledby="systemWarningLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px; margin: 1.75rem auto;">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.18); overflow: hidden; background: #ffffff;">
            <div class="modal-body text-center" style="padding: 32px 24px 22px;">
                <!-- Icon badge: soft pink/peach rounded square with warning triangle -->
                <div style="width: 60px; height: 60px; border-radius: 16px; background-color: #fee2e2; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px;">
                    <i class="fas fa-triangle-exclamation" style="font-size: 26px; color: #1e293b;"></i>
                </div>

                <h4 id="systemWarningLabel" style="font-weight: 700; font-size: 1.3rem; color: #1e293b; margin-bottom: 12px; letter-spacing: -0.01em;">
                    Notice
                </h4>
                <p id="systemWarningMessage" style="font-size: 0.95rem; color: #334155; line-height: 1.5; margin: 0 auto; max-width: 290px;">
                </p>
            </div>

            <div class="modal-footer" style="border-top: 1px solid #f1f5f9; padding: 16px 24px 20px; display: flex; justify-content: center; background: #ffffff;">
                <button type="button" class="btn" id="systemWarningOkBtn" data-bs-dismiss="modal" style="min-width: 130px; height: 42px; background: var(--primary, #15803d); border: none; color: #ffffff; font-weight: 600; font-size: 14.5px; border-radius: 8px; transition: all 0.15s ease; box-shadow: 0 2px 6px rgba(21, 128, 61, 0.25);">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.change-password-page .modern-card {
    background: #ffffff !important;
    backdrop-filter: none !important;
    -webkit-backdrop-filter: none !important;
    border: 1px solid rgba(226, 232, 240, 0.8) !important;
}

.change-password-page {
    max-width: 1060px;
    margin: 0 auto;
    padding-bottom: 3rem;
}

/* Big Avatar on Left Card */
.avatar-circle-lg {
    width: 68px;
    height: 68px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 22px;
    color: #fff;
    margin: 0 auto;
    overflow: hidden;
}
.avatar-img-lg {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.avatar-super-admin {
    background: var(--primary, #B71C1C);
    border: 3px solid rgba(183, 28, 28, 0.2);
}
.avatar-admin {
    background: #dc2626;
    border: 3px solid rgba(220, 38, 38, 0.15);
}
.avatar-dispatcher {
    background: var(--primary, #15803d);
    border: 3px solid rgba(21, 128, 61, 0.2);
}

/* Modern Card Form Enhancements */
.input-password-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.form-control-modern.input-with-toggle {
    padding-right: 54px !important;
}

.btn-toggle-eye {
    position: absolute;
    right: 0;
    width: 50px;
    height: 48px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: none;
    border: none;
    color: #94a3b8;
    padding: 0;
    font-size: 20px;
    cursor: pointer;
    border-radius: 6px;
    transition: color 0.15s ease;
}
.btn-toggle-eye:hover {
    color: #334155;
}

.otp-input-field {
    letter-spacing: 4px;
    font-weight: 700;
    font-size: 17px !important;
    text-align: center;
    font-family: monospace, -apple-system, sans-serif;
}

.btn-otp-action {
    min-width: 120px;
    height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.form-control-modern.is-invalid {
    border-color: #ef4444 !important;
    background-color: #fef2f2 !important;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15) !important;
}
.dark .form-control-modern.is-invalid,
[data-bs-theme="dark"] .form-control-modern.is-invalid {
    border-color: #f87171 !important;
    background-color: rgba(239, 68, 68, 0.1) !important;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.25) !important;
}
.btn-pulse-attention {
    outline: 2px solid var(--primary, #15803d);
    outline-offset: 2px;
}

/* Requirements & Feedback */
.req-item {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: #94a3b8;
    font-size: 12px;
    font-weight: 500;
}
.req-item.req-valid {
    color: #059669;
}
.req-item.req-valid i {
    color: #059669;
}

.match-success {
    color: #059669 !important;
}
.match-error {
    color: #dc2626 !important;
}

/* OTP Status Colors */
.status-success {
    background: #ecfdf5 !important;
    color: #065f46 !important;
    border: 1px solid #a7f3d0 !important;
}
.status-error {
    background: #fef2f2 !important;
    color: #991b1b !important;
    border: 1px solid #fecaca !important;
}
#systemWarningOkBtn:hover {
    background: var(--primary-dark, #166534) !important;
    color: #ffffff !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(21, 128, 61, 0.35) !important;
}
#systemWarningOkBtn:active {
    transform: translateY(0);
}

/* Update Password Button Dynamic States */
#btnSubmitPassword.btn-modern-success {
    background: #e2e8f0;
    color: #64748b !important;
    border: 1.5px solid #cbd5e1;
    box-shadow: none;
    transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    cursor: pointer;
}
#btnSubmitPassword.btn-modern-success:hover:not(.is-valid-form) {
    background: #cbd5e1;
    color: #334155 !important;
}
#btnSubmitPassword.btn-modern-success.is-valid-form {
    background: linear-gradient(135deg, var(--primary, #16a34a) 0%, var(--primary, #15803d) 100%) !important;
    color: #ffffff !important;
    border-color: transparent !important;
    box-shadow: 0 4px 14px rgba(22, 163, 74, 0.35) !important;
}
#btnSubmitPassword.btn-modern-success.is-valid-form:hover {
    background: linear-gradient(135deg, #22c55e 0%, var(--primary, #16a34a) 100%) !important;
    color: #ffffff !important;
    box-shadow: 0 6px 20px rgba(22, 163, 74, 0.45) !important;
    transform: none;
}

@media (max-width: 991.98px) {
    .change-password-page .modern-card {
        background: #ffffff !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
    }
    .change-password-page form > .change-password-actions.d-flex.justify-content-end.gap-2:last-child {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        width: 100% !important;
        gap: 8px !important;
        margin-top: 0 !important;
    }

    .change-password-page form > .change-password-actions.d-flex.justify-content-end.gap-2:last-child > .btn-modern {
        width: 100% !important;
        min-width: 0 !important;
        min-height: 44px !important;
        margin: 0 !important;
        padding: 8px 6px !important;
        font-size: 14px !important;
        white-space: nowrap !important;
        justify-content: center !important;
    }

    .change-password-page .modern-card-body.p-4 {
        padding: clamp(16px, 3vw, 24px) !important;
    }

    .change-password-page .page-header-modern a.btn-modern {
        margin-top: 4px;
    }
}
@media (max-width: 480px) {
    .otp-heading { flex-wrap: wrap; gap: 4px 8px; }
    .otp-heading .badge { max-width: 100%; white-space: normal; overflow-wrap: anywhere; text-align: left; }
    .otp-entry-row { flex-direction: column; }
    .otp-entry-row > .position-relative,
    .otp-entry-row .btn-otp-action { width: 100%; }
    .change-password-page form > .change-password-actions.d-flex.justify-content-end.gap-2:last-child {
        grid-template-columns: minmax(0, 1fr) !important;
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
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        }
    } else {
        input.type = 'password';
        if (icon) {
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }
}

// System Warning Modal Helper (In-System warning dialog matching Picture 1 style, zero "localhost says")
var systemWarningModalEl = null;
var systemWarningModalInstance = null;
var pendingWarningFocusEl = null;

function showSystemWarning(message, title, focusEl) {
    if (!systemWarningModalEl) {
        systemWarningModalEl = document.getElementById('systemWarningModal');
    }
    if (!systemWarningModalEl) {
        console.warn('System warning:', message);
        return;
    }

    var titleEl = document.getElementById('systemWarningLabel');
    var msgEl = document.getElementById('systemWarningMessage');

    if (titleEl) titleEl.textContent = title || 'Notice';
    if (msgEl) msgEl.textContent = message || '';

    pendingWarningFocusEl = focusEl || null;

    if (window.bootstrap && window.bootstrap.Modal) {
        if (!systemWarningModalInstance) {
            systemWarningModalInstance = window.bootstrap.Modal.getOrCreateInstance(systemWarningModalEl, {
                backdrop: true,
                keyboard: true
            });
        }
        systemWarningModalInstance.show();

        setTimeout(function() {
            var okBtn = document.getElementById('systemWarningOkBtn');
            if (okBtn) okBtn.focus();
        }, 120);
    }
}

// Global safety intercept on this page: native browser dialogs route to showSystemWarning
window.alert = function(msg) {
    showSystemWarning(msg, 'Notice');
};

// Live Validation: Length & Password Match
document.addEventListener('DOMContentLoaded', function() {
    var newPwInput = document.getElementById('new_password');
    var confirmPwInput = document.getElementById('confirm_password');
    var curPwInput = document.getElementById('current_password');
    var codeInput = document.getElementById('verification_code');
    var reqLength = document.getElementById('reqLength');
    var matchFeedback = document.getElementById('matchFeedback');
    var btnSendOtp = document.getElementById('btnSendOtp');
    var btnSendOtpText = document.getElementById('btnSendOtpText');
    var otpStatusBox = document.getElementById('otpStatusBox');
    var cpForm = document.getElementById('changePasswordForm');
    var btnSubmitPassword = document.getElementById('btnSubmitPassword');

    // Attach listener to restore focus to invalid input field after warning modal is dismissed
    systemWarningModalEl = document.getElementById('systemWarningModal');
    if (systemWarningModalEl) {
        systemWarningModalEl.addEventListener('hidden.bs.modal', function() {
            if (pendingWarningFocusEl) {
                try {
                    pendingWarningFocusEl.focus();
                    pendingWarningFocusEl.classList.add('is-invalid');
                    setTimeout(function() {
                        pendingWarningFocusEl.classList.remove('is-invalid');
                    }, 2500);
                } catch (e) {}
                pendingWarningFocusEl = null;
            }
        });
    }

    <?php if (session()->getFlashdata('error')): ?>
    // Display server-side error in the system warning dialog for seamless unified UX
    setTimeout(function() {
        showSystemWarning(<?= json_encode((string) session()->getFlashdata('error')) ?>, 'Notice', <?= $isCurrentPwError ? "document.getElementById('current_password')" : ($isCodeError ? "document.getElementById('verification_code')" : "null") ?>);
    }, 200);
    <?php endif; ?>

    // Clear custom validity on input
    [curPwInput, codeInput, newPwInput, confirmPwInput].forEach(function(el) {
        if (el) {
            el.addEventListener('input', function() {
                el.setCustomValidity('');
            });
        }
    });

    function updateFormValidity() {
        var cur = curPwInput ? curPwInput.value.trim() : '';
        var code = codeInput ? codeInput.value.trim() : '';
        var p1 = newPwInput ? newPwInput.value : '';
        var p2 = confirmPwInput ? confirmPwInput.value : '';

        var isValid = (cur.length > 0) &&
                      (code.length === 6 && /^\d{6}$/.test(code)) &&
                      (p1.length >= 8) &&
                      (p1 === p2) &&
                      (cur !== p1);

        if (btnSubmitPassword) {
            if (isValid) {
                btnSubmitPassword.classList.add('is-valid-form');
            } else {
                btnSubmitPassword.classList.remove('is-valid-form');
            }
        }
    }

    function checkRequirements() {
        var val = newPwInput ? newPwInput.value : '';
        if (val.length >= 8) {
            reqLength.classList.add('req-valid');
        } else {
            reqLength.classList.remove('req-valid');
        }
        checkMatch();
        updateFormValidity();
    }

    function checkMatch() {
        var p1 = newPwInput ? newPwInput.value : '';
        var p2 = confirmPwInput ? confirmPwInput.value : '';
        if (!p2) {
            matchFeedback.style.display = 'none';
            matchFeedback.innerHTML = '';
            updateFormValidity();
            return;
        }
        matchFeedback.style.display = 'block';
        if (p1 === p2) {
            matchFeedback.className = 'mt-1 match-success';
            matchFeedback.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Passwords match';
        } else {
            matchFeedback.className = 'mt-1 match-error';
            matchFeedback.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i> Passwords do not match';
        }
        updateFormValidity();
    }

    var otpCodeSent = <?= !empty($has_active_token) ? 'true' : 'false' ?>;

    function highlightGetCodeButton() {
        if (!btnSendOtp) return;
        btnSendOtp.classList.add('btn-pulse-attention');
        setTimeout(function() {
            btnSendOtp.classList.remove('btn-pulse-attention');
        }, 1800);
        try {
            btnSendOtp.focus();
        } catch (e) {}
    }

    if (curPwInput) {
        curPwInput.addEventListener('input', function() {
            curPwInput.classList.remove('is-invalid');
            var fb = document.getElementById('currentPwFeedback');
            if (fb) fb.style.display = 'none';
            updateFormValidity();
        });
        curPwInput.addEventListener('change', updateFormValidity);
    }
    if (codeInput) {
        codeInput.addEventListener('input', function() {
            codeInput.classList.remove('is-invalid');
            var fb = document.getElementById('codeFeedback');
            if (fb) fb.style.display = 'none';
            updateFormValidity();
        });
        codeInput.addEventListener('change', updateFormValidity);
    }
    if (newPwInput) {
        newPwInput.addEventListener('input', checkRequirements);
        newPwInput.addEventListener('change', updateFormValidity);
    }
    if (confirmPwInput) {
        confirmPwInput.addEventListener('input', checkMatch);
        confirmPwInput.addEventListener('change', updateFormValidity);
    }

    // Initial check on load
    checkRequirements();
    checkMatch();
    updateFormValidity();

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
                    otpCodeSent = true;
                    otpStatusBox.className = 'mt-2 p-2 rounded-3 status-success';
                    otpStatusBox.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> ' + (data.message || 'Verification code sent to your email.');
                    startOtpCooldown(data.cooldown || 60);
                    var codeInput = document.getElementById('verification_code');
                    if (codeInput) {
                        codeInput.classList.remove('is-invalid');
                        codeInput.focus();
                    }
                } else {
                    otpStatusBox.className = 'mt-2 p-2 rounded-3 status-error';
                    otpStatusBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> ' + (data.message || 'Could not send verification code.');
                    btnSendOtp.disabled = false;
                    btnSendOtpText.textContent = originalText;
                    showSystemWarning(data.message || 'Could not send verification code.', 'Notice');
                }
            })
            .catch(function(err) {
                otpStatusBox.style.display = 'block';
                otpStatusBox.className = 'mt-2 p-2 rounded-3 status-error';
                otpStatusBox.innerHTML = '<i class="bi bi-exclamation-circle-fill me-1"></i> Connection error. Please check your network and try again.';
                btnSendOtp.disabled = false;
                btnSendOtpText.textContent = originalText;
                showSystemWarning('Connection error. Please check your network and try again.', 'Notice');
            });
        });
    }

    // Client-side Form Submit Validation (All warnings from system modal, zero localhost alert)
    if (cpForm) {
        cpForm.addEventListener('submit', function(e) {
            var p1 = newPwInput ? newPwInput.value : '';
            var p2 = confirmPwInput ? confirmPwInput.value : '';
            var code = codeInput ? codeInput.value.trim() : '';
            var cur = curPwInput ? curPwInput.value : '';

            if (!cur) {
                e.preventDefault();
                showSystemWarning('Please enter your current password.', 'Notice', curPwInput);
                return false;
            }

            if (!code) {
                e.preventDefault();
                otpStatusBox.style.display = 'block';
                otpStatusBox.className = 'mt-2 p-2 rounded-3 status-error';
                otpStatusBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Please click <strong>Get Code</strong> to receive your 6-digit confirmation code via email.';
                highlightGetCodeButton();
                showSystemWarning('Please click "Get Code" first to receive your confirmation code.', 'Notice', codeInput);
                return false;
            }

            if (!otpCodeSent) {
                e.preventDefault();
                otpStatusBox.style.display = 'block';
                otpStatusBox.className = 'mt-2 p-2 rounded-3 status-error';
                otpStatusBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> You have not requested a code yet. Please click <strong>Get Code</strong> first.';
                highlightGetCodeButton();
                showSystemWarning('You have not requested a code yet. Please click "Get Code" first.', 'Notice', codeInput);
                return false;
            }

            if (code.length !== 6 || !/^\d{6}$/.test(code)) {
                e.preventDefault();
                showSystemWarning('Please enter a valid 6-digit code.', 'Notice', codeInput);
                return false;
            }

            if (!p1) {
                e.preventDefault();
                showSystemWarning('Please enter your new password.', 'Notice', newPwInput);
                return false;
            }

            if (p1.length < 8) {
                e.preventDefault();
                showSystemWarning('New password must be at least 8 characters long.', 'Notice', newPwInput);
                return false;
            }

            if (cur && p1 && cur === p1) {
                e.preventDefault();
                showSystemWarning('New password must be different from your current password.', 'Notice', newPwInput);
                return false;
            }

            if (!p2) {
                e.preventDefault();
                showSystemWarning('Please confirm your new password.', 'Notice', confirmPwInput);
                return false;
            }

            if (p1 !== p2) {
                e.preventDefault();
                showSystemWarning('New password and confirm password do not match.', 'Notice', confirmPwInput);
                return false;
            }
        });
    }
});
</script>

<?= view('templates/footer') ?>
