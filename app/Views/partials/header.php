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
<header id="site-header">
    <div class="header-left-cluster">
        <button class="nav-hamburger-btn" id="siteNavHamburgerBtn" type="button" aria-label="Toggle navigation drawer" aria-expanded="false" title="Main menu">
            <i class="fas fa-bars" id="siteNavHamburgerIcon"></i>
        </button>
        <a href="<?= base_url($homeUrl) ?>" class="logo-section">
            <img src="<?= base_url('images/9HFScgVg_400x400.png') ?>" alt="Palompon Transit Logo" class="logo">
            <div class="logo-text">
                <h1>Palompon Transit</h1>
                <p>Terminal Monitor</p>
            </div>
        </a>
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

<aside class="sidebar-drawer" id="siteSidebarDrawer" aria-label="Navigation drawer" aria-hidden="true">
    <div class="sidebar-drawer-header">
        <a href="<?= base_url($homeUrl) ?>" class="drawer-brand-section">
            <img src="<?= base_url('images/9HFScgVg_400x400.png') ?>" alt="Palompon Transit Logo" class="drawer-logo">
            <div class="drawer-brand-text">
                <span class="drawer-brand-title">Palompon Transit</span>
                <span class="drawer-brand-subtitle">Terminal Monitor</span>
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
    function openSidebarDrawer() {
        var drawer       = document.getElementById('siteSidebarDrawer');
        var overlay      = document.getElementById('sidebarDrawerOverlay');
        var mainIcon     = document.getElementById('siteNavHamburgerIcon');
        var hamburgerBtn = document.getElementById('siteNavHamburgerBtn');

        if (drawer) {
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
            drawer.classList.remove('open');
            drawer.setAttribute('aria-hidden', 'true');
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
            }
        });

        // Close drawer when clicking any link inside it
        document.querySelectorAll('.sidebar-drawer-nav a.drawer-nav-item').forEach(function (link) {
            link.addEventListener('click', function () {
                closeSidebarDrawer();
            });
        });


        // Mobile: dropdowns open on tap instead of hover
        document.querySelectorAll('.nav-menu .dropdown .dropbtn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                if (window.innerWidth <= 1280) {
                    e.preventDefault();
                    e.stopPropagation();
                    var dropdown = this.closest('.dropdown');
                    if (dropdown) dropdown.classList.toggle('mobile-open');
                }
            });
        });

        // User profile dropdown toggle
        var profileBtn      = document.getElementById('userProfileBtn');
        var profileDropdown = document.getElementById('userProfileDropdown');
        if (profileBtn && profileDropdown) {
            profileBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var isOpen = profileDropdown.classList.toggle('open');
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

        if (headerAvatarClick && fileInput) {
            var defaultIdenticonHtml = '<svg class="profile-identicon-svg" viewBox="0 0 64 64" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><circle cx="32" cy="32" r="31" fill="#F1F5F9" stroke="#CBD5E1" stroke-width="2" /><path d="M32 32.5a9.5 9.5 0 1 0 0-19 9.5 9.5 0 0 0 0 19zm0 5c-9.2 0-17 5.8-17 13.5 0 1 .8 1.8 1.8 1.8h30.4c1 0 1.8-.8 1.8-1.8 0-7.7-7.8-13.5-17-13.5z" fill="#475569" /></svg>';

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

            function updateAvatarDom(imageUrl) {
                var currentUserId = <?= (int)(session()->get('id') ?? 0) ?>;
                if (imageUrl) {
                    var imgHtml = '<img src="' + imageUrl + '" alt="Avatar" class="profile-avatar-img user-avatar-preview">';
                    if (triggerAvatar) triggerAvatar.innerHTML = imgHtml;
                    if (headerAvatar) headerAvatar.innerHTML = imgHtml;
                    if (miniMenuDeleteBtn) miniMenuDeleteBtn.classList.remove('d-none');
                    if (miniMenuEditText) miniMenuEditText.textContent = 'Edit Photo';

                    var drawerAvatar = document.querySelector('.drawer-user-avatar');
                    if (drawerAvatar) drawerAvatar.innerHTML = imgHtml;

                    var tableCircle = document.getElementById('avatar-circle-' + currentUserId);
                    if (tableCircle) {
                        tableCircle.innerHTML = '<img src="' + imageUrl + '" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">';
                    }
                    var editPreview = document.getElementById('editUserAvatarPreview');
                    if (editPreview) {
                        editPreview.innerHTML = '<img src="' + imageUrl + '" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">';
                    }
                } else {
                    if (triggerAvatar) triggerAvatar.innerHTML = defaultIdenticonHtml;
                    if (headerAvatar) headerAvatar.innerHTML = defaultIdenticonHtml;
                    if (miniMenuDeleteBtn) miniMenuDeleteBtn.classList.add('d-none');
                    if (miniMenuEditText) miniMenuEditText.textContent = 'Upload Photo';

                    var drawerAvatar = document.querySelector('.drawer-user-avatar');
                    if (drawerAvatar) drawerAvatar.innerHTML = defaultIdenticonHtml;

                    var tableCircle = document.getElementById('avatar-circle-' + currentUserId);
                    if (tableCircle) {
                        tableCircle.innerHTML = defaultIdenticonHtml;
                    }
                    var editPreview = document.getElementById('editUserAvatarPreview');
                    if (editPreview) {
                        editPreview.innerHTML = defaultIdenticonHtml;
                    }
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
                    fileInput.value = '';
                    fileInput.click();
                });
            }

            // Delete (restore default logo) button
            if (miniMenuDeleteBtn) {
                miniMenuDeleteBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    closeAvatarMiniMenu();

                    if (!confirm('Revert profile picture to default logo?')) return;

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
                            showAvatarStatus('Reverted to default logo.', 'success', 3000);
                        } else {
                            var err = (result.data && result.data.message) ? result.data.message : 'Failed to reset photo.';
                            showAvatarStatus(err, 'error', 4000);
                        }
                    })
                    .catch(function () {
                        showAvatarStatus('Network error while resetting photo.', 'error', 4000);
                    });
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
        document.querySelectorAll('.nav-menu .dropdown.mobile-open').forEach(function (d) {
            d.classList.remove('mobile-open');
        });
    });
</script>
