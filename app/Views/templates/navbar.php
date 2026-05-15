<style>
    /* Custom Header Styles - White for Guests */
    header {
        background: white;
        padding: 15px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 18px;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        width: 100%;
        max-width: 100vw;
        z-index: 1000;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        font-family: 'Outfit', sans-serif;
        box-sizing: border-box;
    }

    .logo-section {
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        color: inherit;
        min-width: 0;
        flex: 0 0 auto;
    }

    .logo {
        width: 45px;
        height: 45px;
        object-fit: contain;
        border-radius: 10px;
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .logo-section:hover .logo {
        transform: scale(1.05);
    }

    .logo-text h1 {
        font-size: 18px;
        font-weight: 700;
        line-height: 1.1;
        margin: 0;
        color: #1e293b;
        white-space: nowrap;
    }

    .logo-text p {
        font-size: 11px;
        color: #64748b;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin: 0;
        white-space: nowrap;
    }

    .nav-menu {
        display: flex;
        gap: clamp(10px, 1.2vw, 20px);
        align-items: center;
        justify-content: flex-end;
        min-width: 0;
        flex: 1 1 auto;
    }

    .nav-menu a {
        text-decoration: none;
        color: #1e293b;
        font-weight: 600;
        font-size: 14px;
        transition: color 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }

    .nav-menu a i {
        color: #2563eb;
        font-size: 16px;
    }

    .nav-menu a:hover {
        color: #2563eb;
    }

    .nav-menu a.active {
        color: #2563eb;
        position: relative;
    }

    .nav-menu a.active::after {
        content: '';
        position: absolute;
        bottom: -4px;
        left: 0;
        width: 100%;
        height: 2px;
        background: #2563eb;
        border-radius: 1px;
    }

    /* Guest Login Button - dashboard-style */
    .nav-menu a.login-btn {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: white !important;
        padding: 10px 24px;
        border-radius: 25px;
        font-weight: 700;
        font-size: 14px;
        box-shadow: 0 4px 15px rgba(37, 99, 235, 0.35);
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
    }

    .nav-menu a.login-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.45);
        color: white !important;
    }

    .nav-menu a.login-btn i {
        color: white !important;
    }

    /* Dropdown Menu Styles */
    .dropdown {
        position: relative;
        display: inline-block;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        background-color: white;
        min-width: 160px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        z-index: 1002;
        border-radius: 8px;
        top: 100%;
        left: 0;
        padding: 5px 0;
    }

    .dropdown-content a {
        color: #1e293b;
        padding: 10px 16px;
        text-decoration: none;
        display: block;
        font-size: 14px;
        transition: 0.2s;
    }

    .dropdown-content a:hover {
        background-color: #f1f5f9;
        color: #2563eb;
    }

    .dropdown:hover .dropdown-content {
        display: block;
    }

    .dropbtn {
        background: none;
        border: none;
        cursor: pointer;
        font-family: inherit;
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .dropbtn:hover {
        color: #2563eb;
    }

    /* Admin Profile Styles */
    .admin-profile {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-left: 8px;
        padding-left: 16px;
        border-left: 2px solid #e2e8f0 !important;
        height: 40px;
        flex: 0 0 auto;
    }

    .profile-info {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        justify-content: center;
        line-height: 1.1;
    }

    .profile-name {
        font-weight: 800;
        font-size: 15px;
        color: #1e293b;
        letter-spacing: -0.3px;
        max-width: 170px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .profile-role {
        font-size: 10px;
        background: #1e293b !important;
        color: white;
        padding: 3px 8px;
        border-radius: 6px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 2px;
    }

    .logout-btn-custom {
        background: #2563eb !important;
        color: white !important;
        padding: 8px 16px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 700;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .logout-btn-custom:hover {
        background: #1d4ed8 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
    }

    .mobile-toggle {
        display: none;
        font-size: 24px;
        color: #2563eb;
        cursor: pointer;
    }

    @media (max-width: 1280px) {
        .nav-menu {
            /* Hidden off-screen right by default on mobile */
            position: fixed;
            top: 0;
            right: -100%;
            width: 280px;
            height: 100vh;
            background: white;
            flex-direction: column;
            justify-content: flex-start;
            padding: 70px 20px 30px;
            gap: 8px;
            box-shadow: -5px 0 25px rgba(0,0,0,0.15);
            transition: right 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1050;
            overflow-y: auto;
            display: flex !important;
        }

        .nav-menu.mobile-open {
            right: 0;
        }

        /* Override admin red background inside menu on mobile */
        body.admin-theme .nav-menu {
            background: #C62828;
        }

        .nav-menu .dropdown {
            display: block;
            width: 100%;
        }

        .nav-menu .dropdown-content {
            position: static;
            display: block;
            box-shadow: none;
            background: rgba(0,0,0,0.08);
            border-radius: 8px;
            padding: 5px 0;
            margin-top: 5px;
        }

        .nav-menu .admin-profile {
            flex-direction: column;
            align-items: flex-start;
            height: auto;
            border-left: none !important;
            border-top: 1px solid rgba(255,255,255,0.3);
            padding: 15px 0 0;
            margin: 10px 0 0;
            width: 100%;
        }

        .nav-menu a,
        .dropbtn,
        .logout-btn-custom {
            width: 100%;
            justify-content: flex-start;
        }

        .profile-info {
            align-items: flex-start;
        }

        .profile-name {
            max-width: 100%;
        }

        .mobile-toggle {
            display: block;
            z-index: 1060;
            position: relative;
        }

        /* Overlay behind mobile menu */
        .mobile-nav-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }

        .mobile-nav-overlay.active {
            display: block;
        }
    }

    /* Admin Theme Overrides - Red Header for Admins Only */
    body.admin-theme header {
        background: #C62828;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
    }

    body.admin-theme .logo {
        /* No background required for image logo on admin theme */
    }

    body.admin-theme .logo-text h1 {
        color: white;
    }

    body.admin-theme .logo-text p {
        color: rgba(255, 255, 255, 0.8);
    }

    body.admin-theme .nav-menu a {
        color: white;
    }

    body.admin-theme .nav-menu a i {
        color: rgba(255, 255, 255, 0.9);
    }

    body.admin-theme .nav-menu a:hover {
        color: rgba(255, 255, 255, 0.7);
        transform: translateY(-1px);
    }

    body.admin-theme .nav-menu a.active {
        color: white;
    }

    body.admin-theme .nav-menu a.active::after {
        background: white;
    }

    body.admin-theme .dropbtn {
        color: white;
    }

    body.admin-theme .dropbtn:hover {
        color: rgba(255, 255, 255, 0.7);
    }

    body.admin-theme .dropdown-content {
        background: white;
    }

    body.admin-theme .dropdown-content a {
        color: #1e293b !important;
    }

    body.admin-theme .dropdown-content a:hover {
        background: #f1f5f9;
        color: #C62828 !important;
    }

    body.admin-theme .admin-profile {
        border-left-color: rgba(255, 255, 255, 0.3) !important;
    }

    body.admin-theme .profile-name {
        color: white;
    }

    body.admin-theme .profile-role {
        background: rgba(255, 255, 255, 0.2) !important;
    }

    body.admin-theme .logout-btn-custom {
        background: white !important;
        color: #C62828 !important;
    }

    body.admin-theme .logout-btn-custom i {
        color: #C62828 !important;
    }

    body.admin-theme .logout-btn-custom:hover {
        background: rgba(255, 255, 255, 0.9) !important;
    }

    body.admin-theme .mobile-toggle {
        color: white;
    }

    /* ===== STAFF THEME - Blue Header ===== */
    body.staff-theme header {
        background: #1565c0;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
    }

    body.staff-theme .logo {
        /* No background required for image logo on staff theme */
    }

    body.staff-theme .logo-text h1 {
        color: white;
    }

    body.staff-theme .logo-text p {
        color: rgba(255, 255, 255, 0.8);
    }

    body.staff-theme .nav-menu a {
        color: white;
    }

    body.staff-theme .nav-menu a i {
        color: rgba(255, 255, 255, 0.9);
    }

    body.staff-theme .nav-menu a:hover {
        color: rgba(255, 255, 255, 0.7);
        transform: translateY(-1px);
    }

    body.staff-theme .nav-menu a.active {
        color: white;
    }

    body.staff-theme .nav-menu a.active::after {
        background: white;
    }

    body.staff-theme .dropbtn {
        color: white;
    }

    body.staff-theme .dropbtn:hover {
        color: rgba(255, 255, 255, 0.7);
    }

    body.staff-theme .dropdown-content {
        background: white;
    }

    body.staff-theme .dropdown-content a {
        color: #1e293b !important;
    }

    body.staff-theme .dropdown-content a:hover {
        background: #f1f5f9;
        color: #1565c0 !important;
    }

    body.staff-theme .admin-profile {
        border-left-color: rgba(255, 255, 255, 0.3) !important;
    }

    body.staff-theme .profile-name {
        color: white;
    }

    body.staff-theme .profile-role {
        background: rgba(255, 255, 255, 0.2) !important;
    }

    body.staff-theme .logout-btn-custom {
        background: white !important;
        color: #1565c0 !important;
    }

    body.staff-theme .logout-btn-custom i {
        color: #1565c0 !important;
    }

    body.staff-theme .logout-btn-custom:hover {
        background: rgba(255, 255, 255, 0.9) !important;
    }

    body.staff-theme .mobile-toggle {
        color: white;
    }

    /* Override staff blue background inside menu on mobile */
    @media (max-width: 1280px) {
        body.staff-theme .nav-menu {
            background: #1565c0;
        }
    }

    @media (max-width: 480px) {
        header {
            padding-left: 14px;
            padding-right: 14px;
            gap: 10px;
        }

        .logo {
            width: 40px;
            height: 40px;
        }

        .logo-text h1 {
            font-size: 16px;
        }

        .logo-text p {
            font-size: 9px;
            letter-spacing: 0.6px;
        }

        .nav-menu {
            width: min(86vw, 300px);
        }
    }
</style>

<header id="site-header">
    <?php
    $homeUrl = session()->get('isLoggedIn') ? base_url(session()->get('role') . '/dashboard') : base_url('guest');
    ?>
    <a href="<?= $homeUrl ?>" class="logo-section">
        <img src="<?= base_url('images/9HFScgVg_400x400.png') ?>" alt="Palompon Transit Logo" class="logo">
        <div class="logo-text">
            <h1>Palompon Transit</h1>
            <p>Terminal Monitor</p>
        </div>
    </a>

    <div class="nav-menu">
        <?php if (session()->get('isLoggedIn')): ?>
            <?php $role = session()->get('role'); ?>

            <!-- ADMIN NAVIGATION -->
            <?php if ($role == 'admin'): ?>
                <a href="<?= base_url('admin/dashboard') ?>">Dashboard</a>

                <div class="dropdown">
                    <button class="dropbtn">Management <i class="fas fa-caret-down"></i></button>
                    <div class="dropdown-content">
                        <a href="<?= base_url('admin/terminals') ?>">Terminals</a>
                        <a href="<?= base_url('admin/vehicles') ?>">Vehicle Register</a>
                        <a href="<?= base_url('admin/routes') ?>">Routes</a>
                        <a href="<?= base_url('admin/users') ?>">Users</a>
                        <a href="<?= base_url('admin/departure-rules') ?>">Departure Rules</a>
                    </div>
                </div>


                <a href="<?= base_url('admin/announcements') ?>">Announcements</a>
                <a href="<?= base_url('admin/logs') ?>">Logs</a>
                <a href="<?= base_url('admin/history') ?>"><i class="fas fa-history"></i> History</a>
                <a href="<?= base_url('schedules') ?>"><i class="fas fa-calendar-alt"></i> Schedules</a>
                <a href="<?= base_url('fares') ?>"><i class="fas fa-tags"></i> Fares</a>

                <div class="admin-profile">
                    <div class="profile-info">
                        <span class="profile-name"><?= session()->get('full_name') ?? 'System Administrator' ?></span>
                        <span class="profile-role">Admin</span>
                    </div>
                    <a href="<?= base_url('logout') ?>" class="logout-btn-custom">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>

        <!-- DISPATCHER NAVIGATION -->
            <?php elseif ($role == 'staff'): ?>
                <a href="<?= base_url('staff/dashboard') ?>">Dashboard</a>
                <a href="<?= base_url('admin/vehicles') ?>">Vehicle Register</a>
                <a href="<?= base_url('admin/announcements') ?>">Announcements</a>
                <a href="<?= base_url('staff/queue') ?>">Queue Management</a>
                <a href="<?= base_url('staff/departure-rules') ?>">Departure Rules</a>
                <a href="<?= base_url('schedules') ?>"><i class="fas fa-calendar-alt"></i> Schedules</a>
                <a href="<?= base_url('fares') ?>"><i class="fas fa-tags"></i> Fares</a>

                <div class="admin-profile">
                    <div class="profile-info">
                        <span class="profile-name"><?= session()->get('full_name') ?? 'Dispatcher' ?></span>
                        <span class="profile-role">Dispatcher</span>
                    </div>
                    <a href="<?= base_url('logout') ?>" class="logout-btn-custom">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>


            <?php endif; ?>

        <?php else: ?>
            <!-- GUEST/PUBLIC LINKS (match guest dashboard: Home, Schedules, Fares) -->
            <?php $uri = uri_string(); ?>
            <a href="<?= base_url('guest') ?>" class="<?= ($uri === 'guest') ? 'active' : '' ?>"><i class="fas fa-home"></i>
                Home</a>
            <a href="<?= base_url('schedules') ?>" class="<?= (strpos($uri, 'schedules') !== false) ? 'active' : '' ?>"><i
                    class="fas fa-calendar-alt"></i> Schedules</a>
            <a href="<?= base_url('fares') ?>" class="<?= (strpos($uri, 'fares') !== false) ? 'active' : '' ?>"><i
                    class="fas fa-tags"></i> Fares</a>
            <a href="<?= base_url('login') ?>" class="login-btn"><i class="fas fa-sign-in-alt"></i> Login</a>
        <?php endif; ?>
    </div>

    <div class="mobile-toggle" id="mobileMenuToggle" onclick="toggleAdminMobileMenu()">
        <i class="fas fa-bars" id="mobileMenuIcon"></i>
    </div>
</header>

<!-- Mobile Overlay -->
<div class="mobile-nav-overlay" id="mobileNavOverlay" onclick="toggleAdminMobileMenu()"></div>

<script>
    function toggleAdminMobileMenu() {
        var menu    = document.querySelector('header .nav-menu');
        var overlay = document.getElementById('mobileNavOverlay');
        var icon    = document.getElementById('mobileMenuIcon');
        var isOpen  = menu.classList.contains('mobile-open');

        if (isOpen) {
            menu.classList.remove('mobile-open');
            overlay.classList.remove('active');
            icon.classList.replace('fa-times', 'fa-bars');
            document.body.style.overflow = '';
        } else {
            menu.classList.add('mobile-open');
            overlay.classList.add('active');
            icon.classList.replace('fa-bars', 'fa-times');
            document.body.style.overflow = 'hidden';
        }
    }

    // Close menu if window is resized back to desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth > 1280) {
            var menu    = document.querySelector('header .nav-menu');
            var overlay = document.getElementById('mobileNavOverlay');
            var icon    = document.getElementById('mobileMenuIcon');
            menu.classList.remove('mobile-open');
            overlay.classList.remove('active');
            icon.classList.replace('fa-times', 'fa-bars');
            document.body.style.overflow = '';
        }
    });
</script>
