<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Route Fares - Palompon Transit</title>

    <!-- Modern Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap Icons (Fallback) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/responsive.css') ?>">

    <style>
        :root {
            --primary: #1565c0;
            --primary-dark: #0d47a1;
            --accent: #FFC107;
            --accent-dark: #FF9800;
            --success: #43a047;
            --success-dark: #2e7d32;
            --warning: #ef6c00;
            --info: #1976d2;
            --text-main: #2c3e50;
            --text-muted: #66788a;
            --bg-body: #f5f7fa;
            --white: #ffffff;
            --shadow-sm: 0 2px 10px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 15px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 8px 30px rgba(0, 0, 0, 0.12);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg-body);
            color: var(--text-main);
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* --- Advisory Bar --- */
        .advisory-bar {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 10px 5%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            font-size: 14px;
            text-align: center;
            position: relative;
            z-index: 1001;
        }

        .advisory-icon {
            width: 24px;
            height: 24px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-weight: 800;
            flex-shrink: 0;
        }

        .advisory-text {
            overflow: hidden;
            white-space: nowrap;
        }

        .marquee {
            display: inline-block;
            padding-left: 100%;
            animation: marquee 15s linear infinite;
        }

        @keyframes marquee {
            0% {
                transform: translate(0, 0);
            }

            100% {
                transform: translate(-100%, 0);
            }
        }

        /* --- Header & Navigation --- */
        header {
            background: var(--white);
            padding: 15px 5%;
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1010;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
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
            color: var(--primary-dark);
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .logo-text p {
            font-size: 10px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
        }

        .nav-menu {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .nav-menu a {
            text-decoration: none;
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 600;
            transition: var(--transition);
            position: relative;
            padding: 8px 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            border-radius: 6px;
        }

        .nav-menu a i {
            font-size: 16px;
        }

        .nav-menu a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: var(--transition);
        }

        .nav-menu a:hover,
        .nav-menu a.active {
            color: var(--primary);
            background: rgba(21, 101, 192, 0.05);
        }

        .nav-menu a:hover::after,
        .nav-menu a.active::after {
            width: 100%;
        }

        .nav-menu a.login-btn {
            background: #1e3a8a;
            color: white !important;
            padding: 10px 25px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-menu a.login-btn:hover {
            transform: translateY(-1px);
            background: #172554 !important;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }

        .nav-menu a.login-btn.btn-success { background: #059669; }
        .nav-menu a.login-btn.btn-success:hover { background: #047857 !important; }

        .lang-switcher {
            padding: 8px 12px;
            border-radius: 6px;
            transition: var(--transition);
            cursor: pointer;
        }

        .lang-switcher:hover {
            background: rgba(21, 101, 192, 0.05);
            color: var(--primary) !important;
        }

        /* --- Mobile Toggle --- */
        .mobile-toggle {
            display: none;
            font-size: 24px;
            color: var(--primary);
            cursor: pointer;
        }

        /* --- Breadcrumb --- */
        .breadcrumb-section {
            background: white;
            padding: 12px 5%;
            font-size: 13px;
            border-bottom: 1px solid #edf2f7;
        }

        .breadcrumb-section i {
            margin: 0 8px;
            font-size: 10px;
            color: #cbd5e0;
        }

        .breadcrumb-section a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        /* --- Hero Section --- */
        .hero {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            padding: 80px 5%;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.2) 0%, transparent 40%),
                radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.2) 0%, transparent 40%);
        }

        .hero h2 {
            font-size: 48px;
            color: var(--primary-dark);
            font-weight: 800;
            margin-bottom: 20px;
            position: relative;
        }

        .hero p {
            font-size: 18px;
            color: var(--primary-dark);
            opacity: 0.8;
            max-width: 700px;
            margin: 0 auto 40px;
            position: relative;
        }

        /* --- Main Content --- */
        .container {
            width: 90%;
            max-width: 1400px;
            margin: -40px auto 40px;
            position: relative;
            z-index: 20;
        }

        /* --- Search Component --- */
        .search-container {
            background: white;
            padding: 20px 25px;
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            gap: 15px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .search-container input {
            flex: 1;
            border: none;
            outline: none;
            font-size: 16px;
            color: var(--text-main);
            background: transparent;
        }

        /* --- Fares Grid --- */
        .fares-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 300px), 1fr));
            gap: clamp(15px, 2vw, 30px);
            margin-bottom: clamp(30px, 5vw, 60px);
        }

        .fare-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: var(--transition);
            border: 1px solid #edf2f7;
        }

        .fare-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .card-header {
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            border-bottom: 1px solid #f1f5f9;
        }

        .card-header h3 {
            font-size: 20px;
            font-weight: 800;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 0;
        }

        .type-badge {
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .van-badge {
            background: #e3f2fd;
            color: #1565c0;
        }

        .jeepney-badge {
            background: #fff8e1;
            color: #e65100;
        }

        .minibus-badge {
            background: #f3e5f5;
            color: #6a1b9a;
        }

        .fare-list {
            max-height: 600px;
            overflow-y: auto;
        }

        .fare-item {
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f8fafc;
            transition: var(--transition);
        }

        .fare-item:last-child {
            border-bottom: none;
        }

        .fare-item:hover {
            background: #f8fafc;
        }

        .f-dest {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 3px;
        }

        .f-origin {
            font-size: 12px;
            color: var(--text-muted);
        }

        .price-tag {
            font-size: 18px;
            font-weight: 800;
            color: var(--success-dark);
            background: #e8f5e9;
            padding: 5px 12px;
            border-radius: 8px;
        }



        /* --- Mobile Overlay --- */
        .nav-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1002;
            display: none;
            opacity: 0;
            transition: var(--transition);
        }

        .nav-overlay.active {
            display: block;
            opacity: 1;
        }

        /* --- Responsive Queries --- */
        @media (max-width: 768px) {
            .advisory-bar {
                padding: 8px 5%;
                font-size: 11px;
            }

            header {
                padding: 10px 5%;
                min-height: 65px;
                border-bottom: 1px solid #eee;
            }

            .logo { width: 40px; height: 40px; border-radius: 8px; }

            .logo-text h1 {
                font-size: 15px;
                letter-spacing: -0.2px;
            }

            .logo-text p {
                font-size: 8px;
            }

            .nav-menu {
                position: fixed;
                top: 0;
                right: -100%;
                width: 260px;
                height: 100vh;
                background: white;
                flex-direction: column;
                justify-content: flex-start;
                padding: 70px 25px 30px;
                box-shadow: -5px 0 25px rgba(0, 0, 0, 0.08);
                transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                z-index: 1003;
                gap: 12px;
            }

            .nav-menu.open {
                right: 0;
            }

            .nav-menu a {
                width: 100%;
                padding: 12px 18px;
                border-radius: 12px;
                background: #f8fafc;
                font-size: 15px;
                font-weight: 600;
            }

            .nav-menu a.active {
                background: #e3f2fd;
                color: var(--primary);
            }

            .nav-menu a.login-btn { background: #1e3a8a; color: white !important; text-align: center; justify-content: center; margin-top: 10px; margin-left: 0 !important; }
            .nav-menu a.login-btn:hover, .nav-menu a.login-btn:active { background: #172554 !important; transform: scale(0.98); }
            .nav-menu a.login-btn.btn-success { background: #059669; }
            .nav-menu a.login-btn.btn-success:hover, .nav-menu a.login-btn.btn-success:active { background: #047857 !important; }

            .mobile-toggle {
                display: block;
                z-index: 1004;
                position: relative;
            }

            .hero {
                padding: 60px 5%;
            }

            .hero h2 {
                font-size: 32px;
            }

            .hero p {
                font-size: 15px;
            }

            .fares-grid {
                grid-template-columns: 1fr;
            }


        }

    </style>
</head>

<body>

    <!-- Mobile Overlay -->
    <div class="nav-overlay" id="navOverlay" onclick="toggleMenu()"></div>

    <!-- Mobile Close Button - outside menu panel so position:fixed works at any zoom -->
    <button class="mobile-menu-close" id="mobileMenuClose" onclick="toggleMenu()" aria-label="Close menu">
        <i class="fas fa-times"></i>
    </button>

    <!-- Advisory Bar -->
    <div class="advisory-bar">
        <div class="advisory-icon"><i class="fas fa-bullhorn"></i></div>
        <div class="advisory-text">
            <div class="marquee">
                <?php if (!empty($announcements) && is_array($announcements)): ?>
                    <?= esc(implode(' | ', array_column($announcements, 'message'))) ?>
                <?php else: ?>
                    Welcome to Palompon Transit Terminal. Check schedules and fares for your trip.
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Header & Navigation -->
    <header class="guest-header">
        <div style="display: flex; align-items: center; gap: 40px;">
            <a href="<?= base_url('guest') ?>" class="logo-section">
                <img src="<?= base_url('images/9HFScgVg_400x400.png') ?>" alt="Logo" class="logo">
                <div class="logo-text">
                    <h1>Palompon Transit</h1>
                    <p>Terminal Monitor</p>
                </div>
            </a>

            <div class="header-info" style="display: flex; flex-direction: column; gap: 8px; font-size: 13px; color: var(--text-muted); border-left: 1px solid #eee; padding-left: 20px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-map-marker-alt" style="color: #FF9800; font-size: 16px;"></i>
                    <span style="font-weight: 500;">Central Terminal, Palompon, Leyte</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-clock" style="color: var(--primary); font-size: 16px;"></i>
                    <span id="headerClock" style="font-weight: 500;"><?= date('h:i:s A') ?></span>
                </div>
            </div>
        </div>

        <div class="nav-menu" id="navMenu">
            <a href="<?= base_url('guest') ?>" class="<?= current_url() == base_url('guest') ? 'active' : '' ?>"><i class="fas fa-home"></i> Home</a>
            <a href="<?= base_url('schedules') ?>" class="<?= (strpos(uri_string(), 'schedules') !== false) ? 'active' : '' ?>"><i class="fas fa-calendar-alt"></i> Schedules</a>
            <a href="<?= base_url('fares') ?>" class="<?= (strpos(uri_string(), 'fares') !== false) || (current_url() == base_url('fares')) ? 'active' : '' ?>"><i class="fas fa-tags"></i> Fares</a>
            
            <div class="lang-switcher" style="display: flex; align-items: center; gap: 6px; font-size: 14px; color: var(--text-muted);">
                <i class="fas fa-globe"></i>
                <span>EN</span>
                <i class="fas fa-chevron-down" style="font-size: 11px;"></i>
            </div>

            <?php if (session()->get('isLoggedIn')): ?>
                <?php 
                    $dashboardUrl = '/';
                    if (session()->get('role') == 'admin') $dashboardUrl = '/admin/dashboard';
                    elseif (session()->get('role') == 'staff') $dashboardUrl = '/staff/dashboard';
                ?>
                <a href="<?= base_url($dashboardUrl) ?>" class="login-btn btn-success" style="margin-left: 10px;">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
                <a href="<?= base_url('logout') ?>" style="color: #e53e3e; padding: 8px 15px; border-radius: 6px; transition: var(--transition); display: flex; align-items: center;">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            <?php else: ?>
                <a href="<?= base_url('login') ?>" class="login-btn" style="margin-left: 10px;">
                    <i class="fas fa-sign-in-alt"></i> LogIn
                </a>
            <?php endif; ?>
        </div>

        <div class="mobile-toggle" onclick="toggleMenu()">
            <i class="fas fa-bars"></i>
        </div>
    </header>

    <!-- Breadcrumbs -->
    <div class="breadcrumb-section">
        <a href="<?= base_url('guest') ?>">Home</a> <i class="fas fa-chevron-right"></i> <span>Fares</span>
    </div>

    <!-- Hero Section -->
    <section class="hero">
        <h2>Route Fares</h2>
        <p>Check official fare rates for all destinations to ensure fair pricing.</p>
    </section>

    <!-- Main Content -->
    <div class="container">

        <div class="fares-grid" id="faresGrid">
            <!-- Van Routes -->
            <div class="fare-card">
                <div class="card-header">
                    <h3><img src="<?= base_url('images/van.png') ?>" alt="Van" style="width: 45px; height: auto; object-fit: contain;"> Van Routes</h3>
                </div>
                <div class="fare-list">
                    <?php if (!empty($van_routes)): ?>
                        <?php foreach ($van_routes as $route): ?>
                            <div class="fare-item" style="flex-direction: column; align-items: stretch;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div class="dest-info">
                                        <div class="f-dest"><?= strtoupper(esc($route['origin'])) ?> - <?= strtoupper(esc($route['destination'])) ?></div>
                                        <div class="f-origin">From: <?= esc($route['origin']) ?></div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="price-tag">₱<?= number_format($route['fare'], 0) ?></div>
                                        <?php if (session()->get('role') === 'admin'): ?>
                                            <a href="<?= base_url('admin/routes/edit/' . $route['id']) ?>" class="btn btn-sm btn-outline-primary shadow-sm" style="border-radius: 8px; padding: 5px 10px;" title="Edit Fare">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if (!empty($route['discounted_fares'])): ?>
                                <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #e2e8f0;">
                                    <?php foreach ($route['discounted_fares'] as $type => $df): ?>
                                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 4px 0;">
                                        <span style="font-size: 13px; color: #475569; font-weight: 500;">
                                            <i class="fas fa-tag" style="font-size: 10px; color: #94a3b8; margin-right: 5px;"></i>
                                            <?= esc($df['label']) ?> <span style="color: #94a3b8; font-size: 12px;">(<?= number_format($df['discount_percent'], 0) ?>% off)</span>
                                        </span>
                                        <span style="font-size: 14px; font-weight: 700; color: #16a34a;">₱<?= number_format($df['amount'], 2) ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-5 px-5" style="padding-left: 30px;">No van fares listed.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Jeepney Routes -->
            <div class="fare-card">
                <div class="card-header">
                    <h3><img src="<?= base_url('images/jeep.png') ?>" alt="Jeepney" style="width: 45px; height: auto; object-fit: contain;"> Jeepney Routes</h3>
                </div>
                <div class="fare-list">
                    <?php if (!empty($jeepney_routes)): ?>
                        <?php foreach ($jeepney_routes as $route): ?>
                            <div class="fare-item" style="flex-direction: column; align-items: stretch;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div class="dest-info">
                                        <div class="f-dest"><?= strtoupper(esc($route['origin'])) ?> - <?= strtoupper(esc($route['destination'])) ?></div>
                                        <div class="f-origin">From: <?= esc($route['origin']) ?></div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="price-tag">₱<?= number_format($route['fare'], 0) ?></div>
                                        <?php if (session()->get('role') === 'admin'): ?>
                                            <a href="<?= base_url('admin/routes/edit/' . $route['id']) ?>" class="btn btn-sm btn-outline-primary shadow-sm" style="border-radius: 8px; padding: 5px 10px;" title="Edit Fare">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if (!empty($route['discounted_fares'])): ?>
                                <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #e2e8f0;">
                                    <?php foreach ($route['discounted_fares'] as $type => $df): ?>
                                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 4px 0;">
                                        <span style="font-size: 13px; color: #475569; font-weight: 500;">
                                            <i class="fas fa-tag" style="font-size: 10px; color: #94a3b8; margin-right: 5px;"></i>
                                            <?= esc($df['label']) ?> <span style="color: #94a3b8; font-size: 12px;">(<?= number_format($df['discount_percent'], 0) ?>% off)</span>
                                        </span>
                                        <span style="font-size: 14px; font-weight: 700; color: #16a34a;">₱<?= number_format($df['amount'], 2) ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-5 px-5" style="padding-left: 30px;">No jeepney fares listed.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Mini Bus Routes -->
            <div class="fare-card">
                <div class="card-header">
                    <h3><img src="<?= base_url('images/minibus.png') ?>" alt="Minibus" style="width: 45px; height: auto; object-fit: contain;"> Mini Bus Routes</h3>
                </div>
                <div class="fare-list">
                    <?php if (!empty($minibus_routes)): ?>
                        <?php foreach ($minibus_routes as $route): ?>
                            <div class="fare-item" style="flex-direction: column; align-items: stretch;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div class="dest-info">
                                        <div class="f-dest"><?= strtoupper(esc($route['origin'])) ?> - <?= strtoupper(esc($route['destination'])) ?></div>
                                        <div class="f-origin">From: <?= esc($route['origin']) ?></div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="price-tag">₱<?= number_format($route['fare'], 0) ?></div>
                                        <?php if (session()->get('role') === 'admin'): ?>
                                            <a href="<?= base_url('admin/routes/edit/' . $route['id']) ?>" class="btn btn-sm btn-outline-primary shadow-sm" style="border-radius: 8px; padding: 5px 10px;" title="Edit Fare">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if (!empty($route['discounted_fares'])): ?>
                                <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #e2e8f0;">
                                    <?php foreach ($route['discounted_fares'] as $type => $df): ?>
                                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 4px 0;">
                                        <span style="font-size: 13px; color: #475569; font-weight: 500;">
                                            <i class="fas fa-tag" style="font-size: 10px; color: #94a3b8; margin-right: 5px;"></i>
                                            <?= esc($df['label']) ?> <span style="color: #94a3b8; font-size: 12px;">(<?= number_format($df['discount_percent'], 0) ?>% off)</span>
                                        </span>
                                        <span style="font-size: 14px; font-weight: 700; color: #16a34a;">₱<?= number_format($df['amount'], 2) ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-5 px-5" style="padding-left: 30px;">No mini bus fares listed.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Passenger Discount Rates -->
        <div id="discountSection">
        <?php if (!empty($discounts)): ?>
        <?php
        $discountMeta = [
            'pwd'            => ['icon' => 'fa-wheelchair',     'badge_class' => 'van-badge',     'label_color' => '#1565c0'],
            'senior_citizen' => ['icon' => 'fa-user-shield',    'badge_class' => 'minibus-badge', 'label_color' => '#6a1b9a'],
            'student'        => ['icon' => 'fa-graduation-cap', 'badge_class' => 'jeepney-badge', 'label_color' => '#e65100'],
        ];
        ?>
        <div style="margin-bottom: 60px;">
            <h3 style="font-size: 22px; font-weight: 800; color: var(--primary-dark); margin-bottom: 6px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-percent" style="color: var(--primary);"></i> Passenger Discount Rates
            </h3>
            <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 24px;">
                Show your valid ID to avail the discount on any route.
            </p>
            <div class="fares-grid">
                <?php foreach ($discounts as $disc):
                    if (!$disc['is_active']) continue;
                    $meta = $discountMeta[$disc['type']] ?? ['icon' => 'fa-tag', 'badge_class' => 'van-badge', 'label_color' => '#1565c0'];
                ?>
                <div class="fare-card" style="text-align: center;">
                    <div class="card-header">
                        <h3>
                            <i class="fas <?= $meta['icon'] ?>" style="color: <?= $meta['label_color'] ?>; font-size: 22px;"></i>
                            <?= esc($disc['label']) ?>
                        </h3>
                        <span class="type-badge <?= $meta['badge_class'] ?>"><?= number_format($disc['discount_percent'], 0) ?>% OFF</span>
                    </div>
                    <div style="padding: 40px 20px;">
                        <div style="font-size: 64px; font-weight: 800; color: <?= $meta['label_color'] ?>; line-height: 1;">
                            <?= number_format($disc['discount_percent'], 0) ?><span style="font-size: 32px;">%</span>
                        </div>
                        <div style="color: var(--text-muted); font-size: 14px; margin-top: 8px;">off the regular fare</div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        </div>


    </div>

    <!-- Footer -->
    <?= $this->include('templates/guestfooter') ?>

    <script>
    // Live header clock
    setInterval(() => {
        const el = document.getElementById('headerClock');
        if (el) el.innerText = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
    }, 1000);

    // Mobile menu toggle
    function toggleMenu() {
        const m = document.getElementById('navMenu'), o = document.getElementById('navOverlay');
        const toggleIcon = document.querySelector('.mobile-toggle i');
        const toggleBtn = document.querySelector('.mobile-toggle');
        const closeBtn = document.getElementById('mobileMenuClose');

        if (!m) return;

        m.classList.toggle('open');
        if (o) o.classList.toggle('active');

        if (m.classList.contains('open')) {
            if (toggleIcon) toggleIcon.classList.replace('fa-bars', 'fa-times');
            if (toggleBtn) toggleBtn.style.visibility = 'hidden';
            if (closeBtn) closeBtn.classList.add('active');
            document.body.style.overflow = 'hidden';
        } else {
            if (toggleIcon) toggleIcon.classList.replace('fa-times', 'fa-bars');
            if (toggleBtn) toggleBtn.style.visibility = 'visible';
            if (closeBtn) closeBtn.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    // ══════════════════════════════════════════════════
    //  Auto-Refresh: Fare Rates + Discounts (every 10s)
    // ══════════════════════════════════════════════════
    var _fareFingerprint = '';
    var _fareFetchPending = false;

    function makeFP(obj) { return JSON.stringify(obj); }

    function formatFare(n) {
        return '₱' + Number(n).toLocaleString('en-PH', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    function buildFareCard(type, label, badgeClass, imgSrc, routes) {
        var html = '<div class="fare-card">'
            + '<div class="card-header">'
            + '<h3><img src="' + imgSrc + '" alt="' + label + '" style="width: 45px; height: auto; object-fit: contain;"> ' + label + '</h3>'
            + '</div>'
            + '<div class="fare-list">';

        if (routes.length > 0) {
            routes.forEach(function(route) {
                html += '<div class="fare-item" style="flex-direction: column; align-items: stretch;">'
                    + '<div style="display: flex; justify-content: space-between; align-items: center;">'
                    + '<div class="dest-info">'
                    + '<div class="f-dest">' + route.origin.toUpperCase() + ' - ' + route.destination.toUpperCase() + '</div>'
                    + '<div class="f-origin">From: ' + route.origin + '</div>'
                    + '</div>'
                    + '<div class="price-tag">' + formatFare(route.fare) + '</div>'
                    + '</div>';

                // Render discounted fares
                if (route.discounted_fares && Object.keys(route.discounted_fares).length > 0) {
                    html += '<div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #e2e8f0;">';
                    Object.keys(route.discounted_fares).forEach(function(key) {
                        var df = route.discounted_fares[key];
                        html += '<div style="display: flex; justify-content: space-between; align-items: center; padding: 4px 0;">'
                            + '<span style="font-size: 13px; color: #475569; font-weight: 500;">'
                            + '<i class="fas fa-tag" style="font-size: 10px; color: #94a3b8; margin-right: 5px;"></i>'
                            + df.label + ' <span style="color: #94a3b8; font-size: 12px;">(' + Number(df.discount_percent).toFixed(0) + '% off)</span>'
                            + '</span>'
                            + '<span style="font-size: 14px; font-weight: 700; color: #16a34a;">' + formatFare(df.amount) + '</span>'
                            + '</div>';
                    });
                    html += '</div>';
                }

                html += '</div>';
            });
        } else {
            html += '<p class="text-muted text-center py-5 px-5">No fares listed.</p>';
        }

        html += '</div></div>';
        return html;
    }

    function buildDiscountCards(discounts) {
        var metaMap = {
            'pwd':            { icon: 'fa-wheelchair',     badge_class: 'van-badge',     label_color: '#1565c0' },
            'senior_citizen': { icon: 'fa-user-shield',    badge_class: 'minibus-badge', label_color: '#6a1b9a' },
            'student':        { icon: 'fa-graduation-cap', badge_class: 'jeepney-badge', label_color: '#e65100' }
        };
        var defaultMeta = { icon: 'fa-tag', badge_class: 'van-badge', label_color: '#1565c0' };

        var html = '<div style="margin-bottom: 60px;">'
            + '<h3 style="font-size: 22px; font-weight: 800; color: var(--primary-dark); margin-bottom: 6px; display: flex; align-items: center; gap: 10px;">'
            + '<i class="fas fa-percent" style="color: var(--primary);"></i> Passenger Discount Rates</h3>'
            + '<p style="color: var(--text-muted); font-size: 14px; margin-bottom: 24px;">Show your valid ID to avail the discount on any route.</p>'
            + '<div class="fares-grid">';

        discounts.forEach(function(disc) {
            var meta = metaMap[disc.type] || defaultMeta;
            html += '<div class="fare-card" style="text-align: center;">'
                + '<div class="card-header">'
                + '<h3><i class="fas ' + meta.icon + '" style="color: ' + meta.label_color + '; font-size: 22px;"></i> ' + disc.label + '</h3>'
                + '<span class="type-badge ' + meta.badge_class + '">' + Number(disc.discount_percent).toFixed(0) + '% OFF</span>'
                + '</div>'
                + '<div style="padding: 40px 20px;">'
                + '<div style="font-size: 64px; font-weight: 800; color: ' + meta.label_color + '; line-height: 1;">'
                + Number(disc.discount_percent).toFixed(0) + '<span style="font-size: 32px;">%</span></div>'
                + '<div style="color: var(--text-muted); font-size: 14px; margin-top: 8px;">off the regular fare</div>'
                + '</div></div>';
        });

        html += '</div></div>';
        return html;
    }

    function fetchFareData() {
        if (_fareFetchPending) return;
        _fareFetchPending = true;

        fetch('<?= base_url('api/fares') ?>?_=' + Date.now())
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var fp = makeFP(data);
            if (fp === _fareFingerprint) return; // No change
            _fareFingerprint = fp;

            var baseImgUrl = '<?= base_url("images/") ?>';

            // Rebuild fare grid
            var grid = document.getElementById('faresGrid');
            if (grid) {
                grid.innerHTML =
                    buildFareCard('', 'Van Routes', 'van-badge', baseImgUrl + 'van.png', data.van_routes)
                    + buildFareCard('', 'Jeepney Routes', 'jeepney-badge', baseImgUrl + 'jeep.png', data.jeepney_routes)
                    + buildFareCard('', 'Mini Bus Routes', 'minibus-badge', baseImgUrl + 'minibus.png', data.minibus_routes);
            }

            // Rebuild discount section
            var discSection = document.getElementById('discountSection');
            if (discSection) {
                if (data.discounts && data.discounts.length > 0) {
                    discSection.innerHTML = buildDiscountCards(data.discounts);
                    discSection.style.display = '';
                } else {
                    discSection.innerHTML = '';
                    discSection.style.display = 'none';
                }
            }
        })
        .catch(function(err) { console.error('Fare fetch error:', err); })
        .finally(function() { _fareFetchPending = false; });
    }

    // Poll every 10 seconds
    setInterval(fetchFareData, 10000);
    </script>
</body>

</html>
