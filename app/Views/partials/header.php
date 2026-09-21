<?php
/**
 * Shared site header. Picks the correct nav partial from the session role.
 * Styles: public/assets/css/navigation.css (loaded by templates/navbar.php).
 */
$role    = session()->get('isLoggedIn') ? session()->get('role') : null;
$isAdmin = in_array($role, ['super_admin', 'admin'], true);
$homeUrl = $isAdmin ? 'admin/dashboard' : ($role === 'staff' ? 'staff/dashboard' : '/');
$isActive = static fn (string $path): string => url_is($path) ? 'active' : '';

$sessionRole = session()->get('role');
$roleLabel   = match($sessionRole) {
    'super_admin' => 'Super Admin',
    'admin'       => 'Admin',
    'staff'       => 'Dispatcher',
    default       => 'User',
};
$fullName = session()->get('full_name') ?: (session()->get('username') ?: 'Administrator');
$username = session()->get('username') ?: 'admin';

$currentHeaderUserId = session()->get('id');
$profileImage        = session()->get('profile_image');
if ($currentHeaderUserId) {
    try {
        $dbHeaderUser = (new \App\Models\UserModel())->select('profile_image')->find($currentHeaderUserId);
        if ($dbHeaderUser && array_key_exists('profile_image', $dbHeaderUser)) {
            $profileImage = $dbHeaderUser['profile_image'];
            session()->set('profile_image', $profileImage);
        }
    } catch (\Throwable $e) {
        // Fallback to session value
    }
}
$hasCustomImage = !empty($profileImage);

// Modern executive user avatar silhouette
$identiconSvg = <<<SVG
<svg class="profile-identicon-svg" viewBox="0 0 64 64" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <circle cx="32" cy="32" r="31" fill="#F1F5F9" stroke="#CBD5E1" stroke-width="2" />
    <path d="M32 32.5a9.5 9.5 0 1 0 0-19 9.5 9.5 0 0 0 0 19zm0 5c-9.2 0-17 5.8-17 13.5 0 1 .8 1.8 1.8 1.8h30.4c1 0 1.8-.8 1.8-1.8 0-7.7-7.8-13.5-17-13.5z" fill="#475569" />
</svg>
SVG;
?>
<header id="site-header">
    <div class="header-left-cluster">
        <button class="nav-hamburger-btn" id="siteNavHamburgerBtn" type="button" aria-label="Toggle navigation drawer" aria-expanded="false" title="Main menu">
            <i class="fas fa-bars" id="siteNavHamburgerIcon"></i>
        </button>
        <a href="<?= base_url($homeUrl) ?>" class="logo-section" title="<?= esc(app_name()) ?>">
            <img src="<?= esc(app_logo()) ?>" alt="<?= esc(app_name()) ?> Logo" class="logo">
            <div class="logo-text">
                <h1><?= esc(app_name()) ?></h1>
                <p><?= esc(app_subtitle()) ?></p>
            </div>
        </a>
        <?php if ($isAdmin || $role === 'staff'): ?>
            <div class="operations-header-time" title="Current Philippine time" aria-label="Current Philippine time">
                <i class="fas fa-clock" aria-hidden="true"></i>
                <span class="operations-header-timezone" aria-hidden="true">PHT</span>
                <time id="operationsHeaderClock" datetime="<?= date(DATE_ATOM) ?>" aria-live="off"><?= date('h:i:s A') ?></time>
            </div>
        <?php endif; ?>
    </div>

    <nav class="nav-menu" aria-label="Primary navigation">
        <?php if ($isAdmin): ?>
            <?= view('partials/nav-admin') ?>
        <?php elseif ($role === 'staff'): ?>
            <?= view('partials/nav-dispatcher') ?>
        <?php else: ?>
            <?= view('partials/nav-guest') ?>
        <?php endif; ?>
    </nav>
</header>

<!-- Google Classroom-Style Left Sidebar Drawer & Overlay (Universal Desktop & Mobile) -->
<div class="sidebar-drawer-overlay mobile-nav-overlay" id="sidebarDrawerOverlay"></div>

<aside class="sidebar-drawer" id="siteSidebarDrawer" aria-label="Navigation drawer" aria-hidden="true" inert>
    <div class="sidebar-drawer-header">
        <a href="<?= base_url($homeUrl) ?>" class="drawer-brand-section">
            <img src="<?= esc(app_logo()) ?>" alt="<?= esc(app_name()) ?> Logo" class="drawer-logo">
            <div class="drawer-brand-text">
                <span class="drawer-brand-title"><?= esc(app_name()) ?></span>
                <span class="drawer-brand-subtitle"><?= esc(app_subtitle()) ?></span>
            </div>
        </a>
        <button class="drawer-close-btn" id="drawerCloseBtn" type="button" aria-label="Close navigation menu" title="Close menu">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="sidebar-drawer-body">
        <nav class="sidebar-drawer-nav">
            <?php if ($isAdmin): ?>
                <a href="<?= base_url('admin/dashboard') ?>" class="drawer-nav-item <?= $isActive('admin/dashboard') ?>">
                    <i class="fas fa-gauge-high"></i>
                    <span>Dashboard</span>
                </a>

                <div class="drawer-section-title">Management</div>
                <a href="<?= base_url('admin/terminals') ?>" class="drawer-nav-item <?= $isActive('admin/terminals*') ?>">
                    <i class="fas fa-building"></i>
                    <span>Terminals</span>
                </a>
                <a href="<?= base_url('admin/vehicles') ?>" class="drawer-nav-item <?= $isActive('admin/vehicles*') ?>">
                    <i class="fas fa-bus"></i>
                    <span>Vehicle Register</span>
                </a>
                <a href="<?= base_url('admin/routes') ?>" class="drawer-nav-item <?= $isActive('admin/routes*') ?>">
                    <i class="fas fa-route"></i>
                    <span>Routes</span>
                </a>
                <a href="<?= base_url('admin/users') ?>" class="drawer-nav-item <?= $isActive('admin/users*') ?>">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
                <a href="<?= base_url('admin/departure-rules') ?>" class="drawer-nav-item <?= $isActive('admin/departure-rules*') ?>">
                    <i class="fas fa-clock"></i>
                    <span>Departure Rules</span>
                </a>

                <div class="drawer-section-title">Operations</div>
                <a href="<?= base_url('admin/announcements') ?>" class="drawer-nav-item <?= $isActive('admin/announcements*') ?>">
                    <i class="fas fa-bullhorn"></i>
                    <span>Announcements</span>
                </a>
                <a href="<?= base_url('schedules') ?>" class="drawer-nav-item <?= $isActive('schedules*') ?>">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Schedules</span>
                </a>
                <a href="<?= base_url('fares') ?>" class="drawer-nav-item <?= $isActive('fares*') ?>">
                    <i class="fas fa-tags"></i>
                    <span>Fares</span>
                </a>

                <div class="drawer-section-title">Audit & Records</div>
                <a href="<?= base_url('admin/logs') ?>" class="drawer-nav-item <?= $isActive('admin/logs*') ?>">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Activity Logs</span>
                </a>
                <a href="<?= base_url('admin/history') ?>" class="drawer-nav-item <?= $isActive('admin/history*') ?>">
                    <i class="fas fa-history"></i>
                    <span>Departure History</span>
                </a>

                <?php if (session()->get('role') === 'super_admin'): ?>
                <div class="drawer-section-title">System Settings</div>
                <a href="<?= base_url('admin/settings') ?>" class="drawer-nav-item <?= $isActive('admin/settings') ?>">
                    <i class="fas fa-palette"></i>
                    <span>System Themes</span>
                </a>
                <a href="<?= base_url('admin/settings/content') ?>" class="drawer-nav-item <?= $isActive('admin/settings/content*') ?>">
                    <i class="fas fa-pen-to-square"></i>
                    <span>Content Manager</span>
                </a>
                <?php endif; ?>

                <div class="drawer-section-title">Help & Support</div>
                <a href="<?= base_url('admin/help') ?>" class="drawer-nav-item <?= $isActive('admin/help*') || $isActive('admin/manual*') ?>">
                    <i class="fas fa-circle-question"></i>
                    <span>Help Guide</span>
                </a>

            <?php elseif ($role === 'staff'): ?>
                <a href="<?= base_url('staff/dashboard') ?>" class="drawer-nav-item <?= $isActive('staff/dashboard') ?>">
                    <i class="fas fa-gauge-high"></i>
                    <span>Dashboard</span>
                </a>

                <div class="drawer-section-title">Operations</div>
                <a href="<?= base_url('admin/announcements') ?>" class="drawer-nav-item <?= $isActive('admin/announcements*') ?>">
                    <i class="fas fa-bullhorn"></i>
                    <span>Announcements</span>
                </a>
                <a href="<?= base_url('staff/queue') ?>" class="drawer-nav-item <?= $isActive('staff/queue*') ?>">
                    <i class="fas fa-list-ol"></i>
                    <span>Queue Management</span>
                </a>
                <a href="<?= base_url('staff/departure-rules') ?>" class="drawer-nav-item <?= $isActive('staff/departure-rules*') ?>">
                    <i class="fas fa-clock"></i>
                    <span>Departure Rules</span>
                </a>
                <a href="<?= base_url('schedules') ?>" class="drawer-nav-item <?= $isActive('schedules*') ?>">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Schedules</span>
                </a>
                <a href="<?= base_url('fares') ?>" class="drawer-nav-item <?= $isActive('fares*') ?>">
                    <i class="fas fa-tags"></i>
                    <span>Fares</span>
                </a>

                <div class="drawer-section-title">Help & Support</div>
                <a href="<?= base_url('staff/help') ?>" class="drawer-nav-item <?= $isActive('staff/help*') || $isActive('staff/manual*') ?>">
                    <i class="fas fa-circle-question"></i>
                    <span>Help Guide</span>
                </a>

                <div class="drawer-section-title">Account & Security</div>
                <a href="<?= base_url('change-password') ?>" class="drawer-nav-item <?= $isActive('change-password*') ?>">
                    <i class="fas fa-key"></i>
                    <span>Change Password</span>
                </a>

            <?php else: ?>
                <a href="<?= base_url('/') ?>" class="drawer-nav-item <?= url_is('/') || url_is('guest') ? 'active' : '' ?>">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
                <a href="<?= base_url('schedules') ?>" class="drawer-nav-item <?= $isActive('schedules*') ?>">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Schedules</span>
                </a>
                <a href="<?= base_url('fares') ?>" class="drawer-nav-item <?= $isActive('fares*') ?>">
                    <i class="fas fa-tags"></i>
                    <span>Fares</span>
                </a>
                <div class="drawer-section-title">Account</div>
                <a href="<?= base_url('login') ?>" class="drawer-nav-item">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Login</span>
                </a>
            <?php endif; ?>
        </nav>
    </div>

    <?php if (session()->get('isLoggedIn')): ?>
        <div class="sidebar-drawer-footer">
            <div class="drawer-user-card">
                <div class="drawer-user-avatar">
                    <?php if ($hasCustomImage): ?>
                        <img src="<?= base_url(esc($profileImage)) ?>" alt="<?= esc($fullName) ?>" class="profile-avatar-img user-avatar-preview">
                    <?php else: ?>
                        <?= $identiconSvg ?>
                    <?php endif; ?>
                </div>
                <div class="drawer-user-meta">
                    <span class="drawer-user-name"><?= esc($fullName) ?></span>
                    <span class="profile-role-pill pill-<?= esc($sessionRole) ?>">
                        <i class="<?= in_array($sessionRole, ['super_admin', 'admin'], true) ? 'fas fa-shield-alt' : 'fas fa-user-gear' ?>"></i> <?= esc($roleLabel) ?>
                    </span>
                </div>
            </div>
            <a href="<?= base_url('logout') ?>" class="drawer-logout-btn" data-bs-toggle="modal" data-bs-target="#logoutModal" onclick="return confirmLogout(event);" role="button" aria-haspopup="dialog">
                <i class="fas fa-sign-out-alt"></i>
                <span>Sign out</span>
            </a>
        </div>
    <?php endif; ?>
</aside>

<?php if (session()->get('isLoggedIn')): ?>
    <?= view('partials/logout-modal') ?>
<?php endif; ?>

<script>
    (function initOperationsHeaderClock() {
        var clock = document.getElementById('operationsHeaderClock');
        if (!clock) return;

        var clockTimer = null;
        var fullFormatter = new Intl.DateTimeFormat('en-US', {
            timeZone: 'Asia/Manila',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        });
        var compactFormatter = new Intl.DateTimeFormat('en-US', {
            timeZone: 'Asia/Manila',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
        var compactClockMedia = window.matchMedia('(max-width: 480px)');

        function updateClock() {
            var now = new Date();
            clock.textContent = (compactClockMedia.matches ? compactFormatter : fullFormatter).format(now);
            clock.dateTime = now.toISOString();
        }

        function startClock() {
            if (clockTimer) window.clearInterval(clockTimer);
            updateClock();
            clockTimer = window.setInterval(updateClock, 1000);
        }

        startClock();
        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                if (clockTimer) window.clearInterval(clockTimer);
                clockTimer = null;
                return;
            }
            startClock();
        });
    })();

    function openSidebarDrawer() {
        var drawer       = document.getElementById('siteSidebarDrawer');
        var overlay      = document.getElementById('sidebarDrawerOverlay');
        var mainIcon     = document.getElementById('siteNavHamburgerIcon');
        var hamburgerBtn = document.getElementById('siteNavHamburgerBtn');

        if (drawer) {
            drawer.removeAttribute('inert');
            drawer.classList.add('open');
            drawer.setAttribute('aria-hidden', 'false');
        }
        if (overlay) {
            overlay.classList.add('active');
        }
        if (mainIcon) {
            mainIcon.classList.replace('fa-bars', 'fa-times');
        }
        if (hamburgerBtn) {
            hamburgerBtn.setAttribute('aria-expanded', 'true');
        }
        document.body.classList.add('drawer-open');
    }

    function closeSidebarDrawer() {
        var drawer       = document.getElementById('siteSidebarDrawer');
        var overlay      = document.getElementById('sidebarDrawerOverlay');
        var mainIcon     = document.getElementById('siteNavHamburgerIcon');
        var hamburgerBtn = document.getElementById('siteNavHamburgerBtn');

        if (drawer) {
            // Move keyboard focus out before hiding the drawer. This prevents
            // Chromium's "aria-hidden descendant retained focus" warning.
            if (drawer.contains(document.activeElement)) {
                if (hamburgerBtn) {
                    hamburgerBtn.focus({ preventScroll: true });
                } else if (document.activeElement && typeof document.activeElement.blur === 'function') {
                    document.activeElement.blur();
                }
            }
            drawer.classList.remove('open');
            drawer.setAttribute('aria-hidden', 'true');
            drawer.setAttribute('inert', '');
        }
        if (overlay) {
            overlay.classList.remove('active');
        }
        if (mainIcon) {
            mainIcon.classList.replace('fa-times', 'fa-bars');
        }
        if (hamburgerBtn) {
            hamburgerBtn.setAttribute('aria-expanded', 'false');
        }
        document.body.classList.remove('drawer-open');
    }

    function toggleSidebarDrawer() {
        var drawer = document.getElementById('siteSidebarDrawer');
        if (drawer && drawer.classList.contains('open')) {
            closeSidebarDrawer();
        } else {
            openSidebarDrawer();
        }
    }

    // Backward-compatibility alias
    function toggleAdminMobileMenu() {
        toggleSidebarDrawer();
    }

    function setNavDropdownState(dropdown, isOpen) {
        if (!dropdown) return;
        dropdown.classList.toggle('mobile-open', isOpen);
        var trigger = dropdown.querySelector('.dropbtn');
        if (trigger) trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    }

    function closeNavDropdowns(exceptDropdown) {
        document.querySelectorAll('.nav-menu .dropdown.mobile-open').forEach(function (dropdown) {
            if (dropdown !== exceptDropdown) setNavDropdownState(dropdown, false);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var hamburgerBtn   = document.getElementById('siteNavHamburgerBtn');
        var drawerCloseBtn = document.getElementById('drawerCloseBtn');
        var overlay        = document.getElementById('sidebarDrawerOverlay');
        var mobileToggle   = document.getElementById('mobileMenuToggle');

        if (hamburgerBtn) {
            hamburgerBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                toggleSidebarDrawer();
            });
        }

        if (drawerCloseBtn) {
            drawerCloseBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                closeSidebarDrawer();
            });
        }

        if (overlay) {
            overlay.addEventListener('click', function (e) {
                e.preventDefault();
                closeSidebarDrawer();
            });
        }

        if (mobileToggle) {
            mobileToggle.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                toggleSidebarDrawer();
            });
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeSidebarDrawer();
                closeNavDropdowns();
            }
        });

        // Close drawer when clicking any link inside it
        document.querySelectorAll('.sidebar-drawer-nav a.drawer-nav-item').forEach(function (link) {
            link.addEventListener('click', function () {
                closeSidebarDrawer();
            });
        });

        // Dropdowns open on tap/click at all viewports (hover still works via CSS)
        document.querySelectorAll('.nav-menu .dropdown .dropbtn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var dropdown = this.closest('.dropdown');
                if (!dropdown) return;
                var willOpen = !dropdown.classList.contains('mobile-open');
                closeNavDropdowns(dropdown);
                setNavDropdownState(dropdown, willOpen);

                // Navigation and profile menus must never overlap.
                var openProfile = document.getElementById('userProfileDropdown');
                var openProfileBtn = document.getElementById('userProfileBtn');
                if (willOpen && openProfile) openProfile.classList.remove('open');
                if (willOpen && openProfileBtn) openProfileBtn.setAttribute('aria-expanded', 'false');
            });
        });

        // Close nav dropdowns when clicking outside one of them
        document.addEventListener('click', function (e) {
            if (e.target.closest && e.target.closest('.nav-menu .dropdown')) return;
            closeNavDropdowns();
        });

        // User profile dropdown toggle
        var profileBtn      = document.getElementById('userProfileBtn');
        var profileDropdown = document.getElementById('userProfileDropdown');
        if (profileBtn && profileDropdown) {
            profileBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var isOpen = !profileDropdown.classList.contains('open');
                if (isOpen) closeNavDropdowns();
                profileDropdown.classList.toggle('open', isOpen);
                profileBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });

            document.addEventListener('click', function (e) {
                // Do not close profile dropdown if clicking inside the logout confirmation modal (e.g. "Stay Signed In")
                if (e.target && e.target.closest && (e.target.closest('.logout-btn-cancel') || e.target.closest('#logoutModal'))) {
                    return;
                }
                if (!profileDropdown.contains(e.target)) {
                    profileDropdown.classList.remove('open');
                    profileBtn.setAttribute('aria-expanded', 'false');
                }
            });

            document.addEventListener('keydown', function (e) {
                // If logout modal is currently open, let Bootstrap handle Escape for the modal without closing the dropdown
                var logoutModal = document.getElementById('logoutModal');
                if (logoutModal && logoutModal.classList.contains('show')) {
                    return;
                }
                if (e.key === 'Escape' && profileDropdown.classList.contains('open')) {
                    profileDropdown.classList.remove('open');
                    profileBtn.setAttribute('aria-expanded', 'false');
                }
            });
        }

        // Avatar Mini-Menu & Actions (Click avatar -> Edit / Delete)
        var headerAvatarClick = document.getElementById('btnHeaderAvatarClick');
        var avatarMiniMenu    = document.getElementById('avatarMiniMenu');
        var miniMenuEditBtn   = document.getElementById('miniMenuEditBtn');
        var miniMenuDeleteBtn = document.getElementById('miniMenuDeleteBtn');
        var miniMenuEditText  = document.getElementById('miniMenuEditText');
        var fileInput         = document.getElementById('profileAvatarFileInput');
        var statusDiv         = document.getElementById('profileAvatarStatus');
        var triggerAvatar     = document.getElementById('navProfileTriggerAvatar');
        var headerAvatar      = document.getElementById('navProfileHeaderAvatar');

        var currentLoggedInUserId = <?= (int)(session()->get('id') ?? 0) ?>;
        var defaultIdenticonHtml = '<svg class="profile-identicon-svg" viewBox="0 0 64 64" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><circle cx="32" cy="32" r="31" fill="#F1F5F9" stroke="#CBD5E1" stroke-width="2" /><path d="M32 32.5a9.5 9.5 0 1 0 0-19 9.5 9.5 0 0 0 0 19zm0 5c-9.2 0-17 5.8-17 13.5 0 1 .8 1.8 1.8 1.8h30.4c1 0 1.8-.8 1.8-1.8 0-7.7-7.8-13.5-17-13.5z" fill="#475569" /></svg>';

        function updateAvatarDom(imageUrl) {
            var triggerAvatar = document.getElementById('navProfileTriggerAvatar');
            var headerAvatar  = document.getElementById('navProfileHeaderAvatar');
            var miniMenuDeleteBtn = document.getElementById('miniMenuDeleteBtn');
            var miniMenuEditText  = document.getElementById('miniMenuEditText');
            var drawerAvatar = document.querySelector('.drawer-user-avatar');

            if (imageUrl) {
                var imgHtml = '<img src="' + imageUrl + '" alt="Avatar" class="profile-avatar-img user-avatar-preview">';
                if (triggerAvatar) triggerAvatar.innerHTML = imgHtml;
                if (headerAvatar) headerAvatar.innerHTML = imgHtml;
                if (miniMenuDeleteBtn) {
                    miniMenuDeleteBtn.classList.remove('d-none');
                    miniMenuDeleteBtn.style.display = '';
                }
                if (miniMenuEditText) miniMenuEditText.textContent = 'Edit Photo';
                if (drawerAvatar) drawerAvatar.innerHTML = imgHtml;
            } else {
                if (triggerAvatar) triggerAvatar.innerHTML = defaultIdenticonHtml;
                if (headerAvatar) headerAvatar.innerHTML = defaultIdenticonHtml;
                if (miniMenuDeleteBtn) {
                    miniMenuDeleteBtn.classList.add('d-none');
                    miniMenuDeleteBtn.style.setProperty('display', 'none', 'important');
                }
                if (miniMenuEditText) miniMenuEditText.textContent = 'Upload Photo';
                if (drawerAvatar) drawerAvatar.innerHTML = defaultIdenticonHtml;
            }

            // Also update table row if present on admin/users page for current logged-in user
            if (currentLoggedInUserId) {
                var myTableCircle = document.getElementById('avatar-circle-' + currentLoggedInUserId);
                if (myTableCircle) {
                    if (imageUrl) {
                        myTableCircle.innerHTML = '<img src="' + imageUrl + '" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">';
                    } else {
                        myTableCircle.innerHTML = defaultIdenticonHtml;
                    }
                }
                var myTableWrapper = document.getElementById('avatar-wrapper-' + currentLoggedInUserId);
                if (myTableWrapper) {
                    myTableWrapper.setAttribute('data-img-url', imageUrl || '');
                }
            }
        }
        window.updateAvatarDom = updateAvatarDom;

        function syncUserAvatar(userId, imageUrl) {
            userId = parseInt(userId, 10);
            if (!userId) return;

            // 1. If this avatar belongs to current logged-in user, update navbar, header, drawer
            if (currentLoggedInUserId && userId === currentLoggedInUserId) {
                updateAvatarDom(imageUrl);
            }

            // 2. Update table row elements on admin/users list
            var tableCircle = document.getElementById('avatar-circle-' + userId);
            if (tableCircle) {
                if (imageUrl) {
                    tableCircle.innerHTML = '<img src="' + imageUrl + '" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">';
                } else {
                    tableCircle.innerHTML = defaultIdenticonHtml;
                }
            }
            var tableWrapper = document.getElementById('avatar-wrapper-' + userId);
            if (tableWrapper) {
                tableWrapper.setAttribute('data-img-url', imageUrl || '');
            }

            // 3. Update admin edit user page preview if currently viewing this user
            var editPreview = document.getElementById('editUserAvatarPreview');
            if (editPreview) {
                var editUserIdInput = document.getElementById('editUserId') || document.querySelector('input[name="user_id"]');
                if (!editUserIdInput || parseInt(editUserIdInput.value, 10) === userId) {
                    if (imageUrl) {
                        editPreview.innerHTML = '<img src="' + imageUrl + '" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">';
                    } else {
                        editPreview.innerHTML = defaultIdenticonHtml;
                    }
                }
            }
            var editRemoveBtn = document.getElementById('btnEditRemovePhoto');
            if (editRemoveBtn) {
                if (imageUrl) {
                    editRemoveBtn.classList.remove('d-none');
                    editRemoveBtn.style.setProperty('display', 'inline-flex', 'important');
                } else {
                    editRemoveBtn.classList.add('d-none');
                    editRemoveBtn.style.setProperty('display', 'none', 'important');
                }
            }

            // 4. Update modal avatar preview if open for this user
            if (window._avatarModalUser && parseInt(window._avatarModalUser.id, 10) === userId) {
                window._avatarModalUser.imgUrl = imageUrl || '';
                if (typeof window.updateModalAvatarPreview === 'function') {
                    window.updateModalAvatarPreview(imageUrl || '', window._avatarModalUser.initials, window._avatarModalUser.role);
                }
                var modalRemoveBtn = document.getElementById('modalRemoveAvatarBtn');
                if (modalRemoveBtn) {
                    if (imageUrl) {
                        modalRemoveBtn.classList.remove('d-none');
                        modalRemoveBtn.style.setProperty('display', 'inline-flex', 'important');
                    } else {
                        modalRemoveBtn.classList.add('d-none');
                        modalRemoveBtn.style.setProperty('display', 'none', 'important');
                    }
                }
            }
        }
        window.syncUserAvatar = syncUserAvatar;

        // Cross-tab real-time sync via BroadcastChannel
        var _avatarBroadcastChannel = null;
        try {
            if (typeof window.BroadcastChannel === 'function') {
                _avatarBroadcastChannel = new BroadcastChannel('pttm_avatar_sync');
                _avatarBroadcastChannel.onmessage = function (ev) {
                    if (ev && ev.data && ev.data.user_id) {
                        syncUserAvatar(ev.data.user_id, ev.data.image_url);
                    }
                };
            }
        } catch (e) {}

        window.notifyAvatarSync = function (userId, imageUrl) {
            // 1. Broadcast to BroadcastChannel
            if (_avatarBroadcastChannel) {
                try {
                    _avatarBroadcastChannel.postMessage({ user_id: userId, image_url: imageUrl });
                } catch (e) {}
            }
            // 2. Broadcast to localStorage for all other tabs/windows in same browser profile
            try {
                localStorage.setItem('pttm_avatar_sync', JSON.stringify({
                    user_id: userId,
                    image_url: imageUrl,
                    time: Date.now()
                }));
            } catch (e) {}
            // 3. Sync current page DOM immediately
            syncUserAvatar(userId, imageUrl);
        };

        // Listen for cross-tab updates via localStorage
        window.addEventListener('storage', function (ev) {
            if (ev.key === 'pttm_avatar_sync' && ev.newValue) {
                try {
                    var parsed = JSON.parse(ev.newValue);
                    if (parsed && parsed.user_id) {
                        syncUserAvatar(parsed.user_id, parsed.image_url);
                    }
                } catch (e) {}
            }
        });

        // WebSocket real-time synchronization
        document.addEventListener('pttm:ws-user_avatar_updated', function (e) {
            var payload = (e.detail && e.detail.data) ? e.detail.data : e.detail;
            if (payload && payload.user_id) {
                syncUserAvatar(payload.user_id, payload.image_url);
            }
        });
        document.addEventListener('pttm:ws-message', function (e) {
            var msg = e.detail;
            if (msg && msg.type === 'user_avatar_updated' && msg.data && msg.data.user_id) {
                syncUserAvatar(msg.data.user_id, msg.data.image_url);
            }
        });

        // Focus listener: when returning to tab, re-check storage sync
        window.addEventListener('focus', function () {
            try {
                var s = localStorage.getItem('pttm_avatar_sync');
                if (s) {
                    var p = JSON.parse(s);
                    if (p && p.user_id && (Date.now() - (p.time || 0) < 60000)) {
                        syncUserAvatar(p.user_id, p.image_url);
                    }
                }
            } catch (e) {}
        });

        if (headerAvatarClick && fileInput) {
            function getCsrfToken() {
                var meta = document.querySelector('meta[name="csrf-token"]');
                return meta ? meta.getAttribute('content') : '';
            }

            function setCsrfToken(name, hash) {
                if (!hash) return;
                var meta = document.querySelector('meta[name="csrf-token"]');
                if (meta) meta.setAttribute('content', hash);
            }

            function showAvatarStatus(msg, type, duration) {
                if (!statusDiv) return;
                statusDiv.className = 'profile-avatar-status status-' + type;
                statusDiv.textContent = msg;
                statusDiv.style.display = 'block';
                if (duration) {
                    setTimeout(function () {
                        statusDiv.style.display = 'none';
                    }, duration);
                }
            }

            function toggleAvatarMiniMenu(e) {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                if (!avatarMiniMenu) return;
                var isHidden = avatarMiniMenu.style.display === 'none' || !avatarMiniMenu.style.display;
                avatarMiniMenu.style.display = isHidden ? 'flex' : 'none';
                headerAvatarClick.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
            }

            function closeAvatarMiniMenu() {
                if (avatarMiniMenu) {
                    avatarMiniMenu.style.display = 'none';
                    if (headerAvatarClick) headerAvatarClick.setAttribute('aria-expanded', 'false');
                }
            }

            // Click avatar container -> toggle mini-menu
            headerAvatarClick.addEventListener('click', function (e) {
                if (e.target.closest('#avatarMiniMenu')) return;
                toggleAvatarMiniMenu(e);
            });

            headerAvatarClick.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    if (e.target.closest('#avatarMiniMenu')) return;
                    e.preventDefault();
                    toggleAvatarMiniMenu(e);
                }
            });

            // Click outside avatar -> close mini-menu
            document.addEventListener('click', function (e) {
                if (headerAvatarClick && !headerAvatarClick.contains(e.target)) {
                    closeAvatarMiniMenu();
                }
            });

            // Escape key -> close mini-menu
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && avatarMiniMenu && avatarMiniMenu.style.display !== 'none') {
                    closeAvatarMiniMenu();
                }
            });

            // Edit / Upload button
            if (miniMenuEditBtn) {
                miniMenuEditBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    closeAvatarMiniMenu();
                    if (typeof window.openAvatarModal === 'function') {
                        var curId = <?= (int)(session()->get('id') ?? 0) ?>;
                        var curName = <?= json_encode(session()->get('full_name') ?: (session()->get('username') ?: 'Administrator')) ?>;
                        var curRole = <?= json_encode(session()->get('role') ?: 'admin') ?>;
                        var curImg = triggerAvatar && triggerAvatar.querySelector('img') ? triggerAvatar.querySelector('img').src : '';
                        window.openAvatarModal(curId, curName, curImg, '', curRole);
                    } else {
                        fileInput.value = '';
                        fileInput.click();
                    }
                });
            }

            // Delete (restore default logo) button
            if (miniMenuDeleteBtn) {
                miniMenuDeleteBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    closeAvatarMiniMenu();

                    function executeMiniMenuRemove() {
                        showAvatarStatus('Reverting to default logo...', 'loading');
                        var csrfToken = getCsrfToken();
                        var formData = new FormData();
                        if (csrfToken) {
                            formData.append('csrf_test_name', csrfToken);
                        }

                        var headers = {
                            'X-Requested-With': 'XMLHttpRequest'
                        };
                        if (csrfToken) {
                            headers['X-CSRF-TOKEN'] = csrfToken;
                        }

                        fetch('<?= base_url('profile/remove-avatar') ?>', {
                            method: 'POST',
                            headers: headers,
                            body: formData
                        })
                        .then(function (res) {
                            return res.json().then(function (data) {
                                return { status: res.status, data: data };
                            });
                        })
                        .then(function (result) {
                            if (result.data && result.data.csrf_hash) {
                                setCsrfToken(result.data.csrf_token, result.data.csrf_hash);
                            }
                            if (result.data && result.data.success) {
                                updateAvatarDom(null);
                                if (typeof window.notifyAvatarSync === 'function') {
                                    window.notifyAvatarSync(currentUserId, null);
                                }
                                showAvatarStatus('Reverted to default logo.', 'success', 3000);
                            } else {
                                var err = (result.data && result.data.message) ? result.data.message : 'Failed to reset photo.';
                                showAvatarStatus(err, 'error', 4000);
                            }
                        })
                        .catch(function () {
                            showAvatarStatus('Network error while resetting photo.', 'error', 4000);
                        });
                    }

                    if (typeof window.confirmAction === 'function') {
                        window.confirmAction({
                            title: 'Remove Profile Photo?',
                            message: 'Are you sure you want to revert your profile picture to the default avatar?',
                            confirmText: 'Remove Photo',
                            onConfirm: executeMiniMenuRemove
                        });
                    } else if (confirm('Are you sure you want to revert your profile picture to the default avatar?')) {
                        executeMiniMenuRemove();
                    }
                });
            }

            // File selection -> AJAX Upload
            fileInput.addEventListener('change', function () {
                var file = this.files && this.files[0];
                if (!file) return;

                if (!file.type.match(/^image\/(png|jpe?g|webp|gif)$/i)) {
                    showAvatarStatus('Please select a valid image (JPG, PNG, WEBP, GIF).', 'error', 4000);
                    return;
                }

                if (file.size > 4 * 1024 * 1024) {
                    showAvatarStatus('Image exceeds 4MB maximum size.', 'error', 4000);
                    return;
                }

                showAvatarStatus('Uploading photo...', 'loading');

                var formData = new FormData();
                formData.append('avatar', file);

                var csrfToken = getCsrfToken();
                if (csrfToken) {
                    formData.append('csrf_test_name', csrfToken);
                }

                var headers = {
                    'X-Requested-With': 'XMLHttpRequest'
                };
                if (csrfToken) {
                    headers['X-CSRF-TOKEN'] = csrfToken;
                }

                fetch('<?= base_url('profile/upload-avatar') ?>', {
                    method: 'POST',
                    headers: headers,
                    body: formData
                })
                .then(function (res) {
                    return res.json().then(function (data) {
                        return { status: res.status, data: data };
                    });
                })
                .then(function (result) {
                    if (result.data && result.data.csrf_hash) {
                        setCsrfToken(result.data.csrf_token, result.data.csrf_hash);
                    }
                    if (result.data && result.data.success) {
                        updateAvatarDom(result.data.image_url);
                        if (typeof window.notifyAvatarSync === 'function') {
                            window.notifyAvatarSync(currentUserId, result.data.image_url);
                        }
                        showAvatarStatus('Photo updated successfully!', 'success', 3000);
                    } else {
                        var err = (result.data && result.data.message) ? result.data.message : 'Upload failed.';
                        showAvatarStatus(err, 'error', 4000);
                    }
                })
                .catch(function () {
                    showAvatarStatus('Network error while uploading photo.', 'error', 4000);
                });
            });
        }
    });


    // Clean up dropdowns and close drawer on resize to desktop
    window.addEventListener('resize', function () {
        if (window.innerWidth > 1024) {
            closeSidebarDrawer();
        }
        closeNavDropdowns();
    });

    // Ensure QueueWS WebSocket is connected for global real-time notifications
    window.addEventListener('load', function () {
        if (window.QueueWS && !window.QueueWS.isConnected()) {
            try {
                window.QueueWS.init({ pollingInterval: 20000 });
            } catch (e) {}
        }
    });

    // Listen for real-time branding updates across connected sessions
    document.addEventListener('pttm:ws-branding_updated', function (e) {
        if (e.detail && e.detail.data && typeof window.applyLiveBranding === 'function') {
            window.applyLiveBranding(e.detail.data);
        }
    });
</script>
