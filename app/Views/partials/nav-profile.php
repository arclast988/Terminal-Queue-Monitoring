<?php
/**
 * GitHub-style User Profile Dropdown with Avatar Management
 * Rendered inside .nav-menu by partials/nav-admin.php and partials/nav-dispatcher.php
 */
$sessionRole = session()->get('role');
$roleLabel   = match($sessionRole) {
    'super_admin' => 'Super Admin',
    'admin'       => 'Admin',
    'staff'       => 'Dispatcher',
    default       => 'User',
};

$fullName = session()->get('full_name') ?: (session()->get('username') ?: 'Administrator');
$username = session()->get('username') ?: 'admin';
$isAdmin  = in_array($sessionRole, ['super_admin', 'admin'], true);
$profileImage = session()->get('profile_image');
$hasCustomImage = !empty($profileImage);

// Modern executive user avatar silhouette
$identiconSvg = <<<SVG
<svg class="profile-identicon-svg" viewBox="0 0 64 64" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <circle cx="32" cy="32" r="31" fill="#F1F5F9" stroke="#CBD5E1" stroke-width="2" />
    <path d="M32 32.5a9.5 9.5 0 1 0 0-19 9.5 9.5 0 0 0 0 19zm0 5c-9.2 0-17 5.8-17 13.5 0 1 .8 1.8 1.8 1.8h30.4c1 0 1.8-.8 1.8-1.8 0-7.7-7.8-13.5-17-13.5z" fill="#475569" />
</svg>
SVG;
?>
<div class="user-profile-dropdown" id="userProfileDropdown">
    <!-- Navbar Profile Trigger Button: Icon on top, name directly below -->
    <button class="profile-trigger-btn" id="userProfileBtn" type="button" aria-expanded="false" aria-haspopup="true" title="<?= esc($fullName) ?> (<?= esc($roleLabel) ?>)">
        <div class="profile-avatar-circle" id="navProfileTriggerAvatar">
            <?php if ($hasCustomImage): ?>
                <img src="<?= base_url(esc($profileImage)) ?>" alt="<?= esc($fullName) ?>" class="profile-avatar-img user-avatar-preview">
            <?php else: ?>
                <?= $identiconSvg ?>
            <?php endif; ?>
        </div>
        <div class="profile-trigger-info">
            <span class="profile-trigger-name"><?= esc($fullName) ?></span>
            <i class="fas fa-caret-down profile-trigger-caret"></i>
        </div>
    </button>

    <!-- GitHub-Style Profile Dropdown Card -->
    <div class="profile-dropdown-card" id="userProfileMenu" role="menu" aria-labelledby="userProfileBtn">
        <!-- User Info Header -->
        <div class="profile-dropdown-header">
            <!-- Clickable Avatar with Camera Badge & Mini Action Menu -->
            <div class="profile-header-avatar" id="btnHeaderAvatarClick" role="button" tabindex="0" title="Click to edit or delete photo" aria-label="Profile picture actions" aria-haspopup="true" aria-expanded="false">
                <div class="profile-avatar-circle profile-avatar-large" id="navProfileHeaderAvatar">
                    <?php if ($hasCustomImage): ?>
                        <img src="<?= base_url(esc($profileImage)) ?>" alt="<?= esc($fullName) ?>" class="profile-avatar-img user-avatar-preview">
                    <?php else: ?>
                        <?= $identiconSvg ?>
                    <?php endif; ?>
                </div>
                <span class="profile-avatar-hover-badge" title="Manage avatar">
                    <i class="fas fa-camera"></i>
                </span>

                <!-- Simple Click Mini-Menu (No big modal) -->
                <div class="avatar-mini-menu" id="avatarMiniMenu" style="display: none;" role="menu" aria-label="Avatar options">
                    <button type="button" class="avatar-mini-item" id="miniMenuEditBtn" role="menuitem">
                        <i class="fas fa-edit"></i>
                        <span id="miniMenuEditText"><?= $hasCustomImage ? 'Edit Photo' : 'Upload Photo' ?></span>
                    </button>
                    <button type="button" class="avatar-mini-item item-delete <?= $hasCustomImage ? '' : 'd-none' ?>" id="miniMenuDeleteBtn" role="menuitem">
                        <i class="fas fa-trash-alt"></i>
                        <span>Delete (Default)</span>
                    </button>
                </div>
            </div>

            <div class="profile-header-meta">
                <div class="profile-header-name-row">
                    <strong class="profile-user-fullname"><?= esc($fullName) ?></strong>
                    <span class="profile-online-badge"><span class="online-dot"></span> Online</span>
                </div>
                <div class="profile-user-handle">@<?= esc($username) ?></div>
                <div class="profile-role-badge-row">
                    <span class="profile-role-pill pill-<?= esc($sessionRole) ?>">
                        <i class="fas fa-shield-alt"></i> <?= esc($roleLabel) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Hidden File Input for Avatar Upload -->
        <input type="file" id="profileAvatarFileInput" accept="image/png,image/jpeg,image/jpg,image/webp,image/gif" style="display:none;" aria-label="Upload profile image">
        
        <!-- Dynamic Upload Status Notification -->
        <div class="profile-avatar-status" id="profileAvatarStatus" style="display:none;"></div>

        <div class="profile-dropdown-divider"></div>

        <?php if ($sessionRole === 'staff'): ?>
        <!-- Account & Security (Dispatcher only) -->
        <div class="profile-dropdown-section">
            <div class="profile-section-label">Account & Security</div>
            <a href="<?= base_url('change-password') ?>" class="profile-dropdown-item" role="menuitem">
                <i class="fas fa-key"></i>
                <span class="item-text">Change Password</span>
                <span class="profile-badge-chip chip-green">Auth</span>
            </a>
        </div>

        <div class="profile-dropdown-divider"></div>
        <?php endif; ?>

        <!-- Help Guide -->
        <div class="profile-dropdown-section">
            <div class="profile-section-label">Help & Support</div>
            <a href="<?= base_url($isAdmin ? 'admin/help' : 'staff/help') ?>" class="profile-dropdown-item" role="menuitem">
                <i class="fas fa-circle-question"></i>
                <span class="item-text">Help Guide</span>
                <span class="profile-badge-chip chip-green">Guide</span>
            </a>
        </div>

        <div class="profile-dropdown-divider"></div>

        <!-- Sign Out Action (with existing modal confirmation) -->
        <div class="profile-dropdown-footer">
            <a href="<?= base_url('logout') ?>" class="profile-dropdown-item item-logout" data-bs-toggle="modal" data-bs-target="#logoutModal" onclick="return confirmLogout(event);" role="button" aria-haspopup="dialog">
                <i class="fas fa-sign-out-alt"></i>
                <span class="item-text">Sign out</span>
            </a>
        </div>
    </div>
</div>
