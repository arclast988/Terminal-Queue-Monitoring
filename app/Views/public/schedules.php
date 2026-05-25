<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Schedules - Palompon Transit</title>

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

        /* --- Filter Box --- */
        .filter-box {
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .filter-form {
            display: grid;
            grid-template-columns: 1fr 1.5fr auto;
            gap: 20px;
            align-items: end;
        }

        .form-group label {
            display: block;
            font-weight: 700;
            font-size: 13px;
            margin-bottom: 8px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            color: var(--text-main);
            background: #f8fafc;
            cursor: pointer;
            transition: var(--transition);
            outline: none;
        }

        .form-group select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(21, 101, 192, 0.1);
            background: white;
        }

        .filter-btn {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
        }

        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .active-filters {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .filter-badge {
            background: #e3f2fd;
            color: var(--primary);
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
        }

        .clear-btn {
            background: none;
            border: 1px solid #e2e8f0;
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 13px;
            color: var(--text-muted);
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: var(--transition);
        }

        .clear-btn:hover {
            background: #f1f5f9;
            color: var(--text-main);
        }

        /* --- Schedule Card/Table --- */
        .schedule-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-md);
            border: 1px solid #edf2f7;
        }

        .card-header {
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            border-bottom: 1px solid #eee;
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

        .count-badge {
            background: #e3f2fd;
            color: var(--primary);
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
        }

        .schedule-table {
            width: 100%;
            border-collapse: collapse;
        }

        .schedule-table thead {
            background: #f8fafc;
        }

        .schedule-table th {
            padding: 15px 25px;
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .schedule-table td {
            padding: 20px 25px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
            color: var(--text-main);
        }

        .schedule-table tr:hover {
            background: #fafbfc;
        }

        .time-display {
            font-size: 16px;
            font-weight: 800;
            color: var(--primary);
            background: #e3f2fd;
            padding: 5px 12px;
            border-radius: 8px;
            display: inline-block;
        }

        .plate-number {
            font-weight: 700;
            color: var(--text-main);
            font-family: monospace;
            font-size: 15px;
            background: #f1f5f9;
            padding: 4px 8px;
            border-radius: 6px;
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
            background: #ede9fe;
            color: #6d28d9;
        }

        .route-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-badge {
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-scheduled {
            background: #e3f2fd;
            color: #1565c0;
        }

        .status-waiting {
            background: #fff8e1;
            color: #ef6c00;
        }

        .status-boarding {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-departed {
            background: #f1f5f9;
            color: #64748b;
        }

        .status-canceled {
            background: #ffebee;
            color: #c62828;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 60px;
            color: #cbd5e0;
            margin-bottom: 20px;
        }

        .empty-state h4 {
            font-size: 20px;
            color: var(--text-main);
            margin-bottom: 10px;
            font-weight: 700;
        }

        .empty-state p {
            color: var(--text-muted);
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
        @media (max-width: 992px) {
            .header-info { display: none !important; }
        }

        @media (max-width: 768px) {
            header { padding: 10px 5%; min-height: 65px; border-bottom: 1px solid #eee; }
            .logo { width: 40px; height: 40px; border-radius: 8px; }
            .logo-text h1 { font-size: 15px; letter-spacing: -0.2px; }
            .logo-text p { font-size: 8px; }

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
                padding: 40px 5%;
            }

            .hero h2 {
                font-size: 28px;
            }

            .hero p {
                font-size: 14px;
                margin-bottom: 20px;
            }

            .filter-box {
                padding: 15px;
                margin-top: -20px;
            }

            .filter-form {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .schedule-table thead {
                display: none;
            }

            .schedule-table,
            .schedule-table tbody,
            .schedule-table tr,
            .schedule-table td {
                display: block;
                width: 100%;
            }

            .schedule-table tr {
                padding: 15px;
                border: 1px solid #edf2f7;
                border-radius: 15px;
                margin-bottom: 15px;
                background: white;
            }

            .schedule-table td {
                padding: 10px 0;
                border: none;
                display: flex;
                justify-content: space-between;
                align-items: center;
                text-align: right;
            }

            .schedule-table td::before {
                content: attr(data-label);
                font-weight: 700;
                color: var(--text-muted);
                font-size: 11px;
                text-transform: uppercase;
                text-align: left;
            }

        }
    </style>
</head>

<body>

    <!-- Mobile Overlay -->
    <div class="nav-overlay" id="navOverlay" onclick="toggleMenu()"></div>

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
    <header>
        <div style="display: flex; align-items: center; gap: 40px;">
            <a href="<?= base_url('guest') ?>" class="logo-section">
                <img src="<?= base_url('images/9HFScgVg_400x400.png') ?>" alt="Logo" class="logo">
                <div class="logo-text">
                    <h1>Palompon Transit </h1>
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
            <a href="<?= base_url('schedules') ?>" class="<?= (strpos(uri_string(), 'schedules') !== false) || (current_url() == base_url('schedules')) ? 'active' : '' ?>"><i class="fas fa-calendar-alt"></i> Schedules</a>
            <a href="<?= base_url('fares') ?>" class="<?= (strpos(uri_string(), 'fares') !== false) ? 'active' : '' ?>"><i class="fas fa-tags"></i> Fares</a>
            
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
        <a href="<?= base_url('guest') ?>">Home</a> <i class="fas fa-chevron-right"></i> <span>Schedules</span>
    </div>

    <!-- Hero Section -->
    <section class="hero">
        <h2>Vehicle Schedules</h2>
        <p>View departure times, vehicle status, and route information for all vans and jeepneys.</p>
    </section>

    <!-- Main Content -->
    <div class="container">
        <!-- Filter Box -->
        <div class="filter-box">
            <form method="get" action="<?= base_url('schedules') ?>" class="filter-form">
                <div class="form-group">
                    <label>Vehicle Type</label>
                    <select name="type" id="vehicleTypeSelect">
                        <option value="">All Types</option>
                        <option value="van" <?= $vehicle_type == 'van' ? 'selected' : '' ?>>Van</option>
                        <option value="jeepney" <?= $vehicle_type == 'jeepney' ? 'selected' : '' ?>>Jeepney</option>
                        <option value="minibus" <?= $vehicle_type == 'minibus' ? 'selected' : '' ?>>Minibus</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Destination</label>
                    <select name="destination" id="destinationSelect">
                        <option value="">All Destinations</option>
                        <optgroup label="Van Destinations" id="vanDestinations">
                            <?php foreach ($van_destinations as $dest): ?>
                                <option value="<?= $dest ?>" <?= $destination == $dest ? 'selected' : '' ?>><?= $dest ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Jeepney Destinations" id="jeepneyDestinations">
                            <?php foreach ($jeepney_destinations as $dest): ?>
                                <option value="<?= $dest ?>" <?= $destination == $dest ? 'selected' : '' ?>><?= $dest ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Minibus Destinations" id="minibusDestinations">
                            <?php foreach ($minibus_destinations as $dest): ?>
                                <option value="<?= $dest ?>" <?= $destination == $dest ? 'selected' : '' ?>><?= $dest ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>
                <button type="submit" class="filter-btn"><i class="fas fa-filter"></i> Apply Filters</button>
            </form>
            <?php if ($vehicle_type || $destination): ?>
                <div class="active-filters">
                    <span class="filter-badge"><?= $vehicle_type ? ucfirst($vehicle_type) : 'All Types' ?></span>
                    <?php if ($destination): ?><span class="filter-badge">Destination:
                            <?= esc($destination) ?></span><?php endif; ?>
                    <a href="<?= base_url('schedules') ?>" class="clear-btn"><i class="fas fa-times"></i> Clear Filters</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Schedule List -->
        <div class="schedule-card">
            <div class="card-header">
                <h3><i class="fas fa-list-alt"></i> Schedule Board</h3>
                <span class="count-badge" id="scheduleCount"><?= count($schedules) ?> Found</span>
            </div>
            <?php if (!empty($schedules)): ?>
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th>Queue #</th>
                            <th>Plate Number</th>
                            <th>Type</th>
                            <th>Route</th>
                            <th>Est. Departure</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="scheduleTableBody">
                        <?php foreach ($schedules as $schedule): ?>
                            <tr>
                                <td data-label="Queue #">
                                    <span class="time-display">#<?= esc($schedule['position']) ?></span>
                                </td>
                                <td data-label="Plate"><span class="plate-number"><?= esc($schedule['plate_number']) ?></span>
                                </td>
                                <td data-label="Type">
                                    <?php
                                        $imgMap = ['van' => 'van.png', 'jeepney' => 'jeep.png', 'minibus' => 'minibus.png'];
                                        $imgFile = $imgMap[$schedule['vehicle_type']] ?? 'van.png';
                                    ?>
                                    <img src="<?= base_url('images/' . $imgFile) ?>" alt="<?= ucfirst($schedule['vehicle_type']) ?>" style="height:36px; width:auto;" title="<?= ucfirst($schedule['vehicle_type']) ?>">
                                    <small class="d-block text-muted" style="font-size:10px;"><?= ucfirst($schedule['vehicle_type']) ?></small>
                                </td>
                                <td data-label="Route">
                                    <div class="route-info">
                                        <span
                                            style="color: var(--text-muted); font-size: 13px;"><?= esc($schedule['origin']) ?></span>
                                        <i class="fas fa-arrow-right" style="color: var(--primary); font-size: 12px;"></i>
                                        <span
                                            style="font-weight: 700; color: var(--primary-dark);"><?= esc($schedule['destination']) ?></span>
                                    </div>
                                </td>
                                <td data-label="Est. Departure">
                                    <?php if ($schedule['status'] === 'departed' && $schedule['departure_time']): ?>
                                        <span class="time-display" style="background: var(--text-muted); color: white;">
                                            <?= date('g:i A', strtotime($schedule['departure_time'])) ?>
                                        </span>
                                        <div style="font-size: 11px; color: var(--text-muted);">Departed</div>
                                    <?php elseif ($schedule['is_full']): ?>
                                        <span class="time-display" style="background: #16a34a; color: white;">
                                            FULL — Ready
                                        </span>
                                    <?php else: ?>
                                        <span class="time-display">
                                            <?= date('g:i A', strtotime($schedule['estimated_departure'])) ?>
                                        </span>
                                        <div style="font-size: 11px; color: var(--text-muted);">
                                            <?= $schedule['current_passengers'] ?>/<?= $schedule['capacity'] ?> passengers
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Status">
                                    <?php
                                    $statusClass = ['scheduled' => 'status-scheduled', 'waiting' => 'status-waiting', 'boarding' => 'status-boarding', 'departed' => 'status-departed', 'canceled' => 'status-canceled'];
                                    ?>
                                    <span class="status-badge <?= $statusClass[$schedule['status']] ?? 'status-departed' ?>">
                                        <?= strtoupper($schedule['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="far fa-calendar-times"></i>
                    <h4>No schedules available</h4>
                    <p><?= $vehicle_type || $destination ? 'Try adjusting your filters.' : 'No vehicles have been scheduled yet.' ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <?= $this->include('templates/guestfooter') ?>

        <script src="<?= base_url('js/ws-client.js') ?>"></script>
        <script src="<?= base_url('js/queue-sync.js') ?>"></script>
        <script>
        var currentType = '<?= esc($vehicle_type) ?>';
        var currentDest = '<?= esc($destination) ?>';
        var _fetchPending = false;

        function fetchSchedulesStatus() {
            if (_fetchPending) return;
            _fetchPending = true;

            var params = [];
            if (currentType) params.push('type=' + encodeURIComponent(currentType));
            if (currentDest) params.push('destination=' + encodeURIComponent(currentDest));
            params.push('_=' + Date.now());
            var fetchUrl = '<?= base_url('schedules/status') ?>?' + params.join('&');

            fetch(fetchUrl)
            .then(function(response) {
                if (!response.ok) throw new Error('Network error');
                return response.json();
            })
            .then(function(data) {
                renderSchedules(data.schedules);
                var badge = document.getElementById('scheduleCount');
                if (badge) badge.innerText = data.count + ' Found';
            })
            .catch(function(e) {
                console.error('Fetch error:', e);
            })
            .finally(function() {
                _fetchPending = false;
            });
        }

        function renderSchedules(schedules) {
            var tbody = document.getElementById('scheduleTableBody');
            var card = document.querySelector('.schedule-card');
            if (!card) return;

            if (schedules.length === 0) {
                // Show empty state, remove table if it exists
                card.innerHTML = '<div class="card-header"><h3><i class="fas fa-list-alt"></i> Schedule Board</h3><span class="count-badge" id="scheduleCount">0 Found</span></div>'
                    + '<div class="empty-state"><i class="far fa-calendar-times"></i><h4>No schedules available</h4></div>';
                return;
            }

            // If tbody doesn't exist (page loaded with empty state), create the table
            if (!tbody) {
                card.innerHTML = '<div class="card-header"><h3><i class="fas fa-list-alt"></i> Schedule Board</h3><span class="count-badge" id="scheduleCount">' + schedules.length + ' Found</span></div>'
                    + '<table class="schedule-table"><thead><tr>'
                    + '<th>Queue #</th><th>Plate Number</th><th>Type</th><th>Route</th><th>Est. Departure</th><th>Status</th>'
                    + '</tr></thead><tbody id="scheduleTableBody"></tbody></table>';
                tbody = document.getElementById('scheduleTableBody');
            }

            var imgMap = { 'van': 'van.png', 'jeepney': 'jeep.png', 'minibus': 'minibus.png' };
            var statusClassMap = { 'scheduled': 'status-scheduled', 'waiting': 'status-waiting', 'boarding': 'status-boarding', 'departed': 'status-departed', 'canceled': 'status-canceled' };
            var html = '';
            schedules.forEach(function(s) {
                var imgFile = imgMap[s.vehicle_type] || 'van.png';
                var typeLabel = s.vehicle_type.charAt(0).toUpperCase() + s.vehicle_type.slice(1);
                var statusClass = statusClassMap[s.status] || 'status-departed';
                var dep = '';
                if (s.status === 'departed' && s.departure_time_formatted) {
                    dep = '<span class="time-display" style="background:#64748b;color:white">' + s.departure_time_formatted + '</span><div style="font-size:11px;color:var(--text-muted)">Departed</div>';
                } else if (s.is_full) {
                    dep = '<span class="time-display" style="background:#16a34a;color:white">FULL</span>';
                } else {
                    dep = '<span class="time-display">' + (s.estimated_departure_formatted || '--:-- --') + '</span><div style="font-size:11px;color:var(--text-muted)">' + s.current_passengers + '/' + s.capacity + ' passengers</div>';
                }
                html += '<tr>' +
                    '<td data-label="Queue #"><span class="time-display">#' + s.position + '</span></td>' +
                    '<td data-label="Plate"><span class="plate-number">' + s.plate_number + '</span></td>' +
                    '<td data-label="Type"><img src="<?= base_url('images/') ?>' + imgFile + '" style="height:36px;width:auto"><small class="d-block" style="font-size:10px;color:#666">' + typeLabel + '</small></td>' +
                    '<td data-label="Route"><div class="route-info"><span style="color:var(--text-muted);font-size:13px">' + s.origin + '</span><i class="fas fa-arrow-right" style="color:var(--primary);font-size:12px"></i><span style="font-weight:700;color:var(--primary-dark)">' + s.destination + '</span></div></td>' +
                    '<td data-label="Est. Departure">' + dep + '</td>' +
                    '<td data-label="Status"><span class="status-badge ' + statusClass + '">' + s.status.toUpperCase() + '</span></td>' +
                '</tr>';
            });
            tbody.innerHTML = html;
        }

        function toggleMenu() {
            var m = document.getElementById('navMenu'), o = document.getElementById('navOverlay');
            if (m) m.classList.toggle('open');
            if (o) o.classList.toggle('active');
        }

        setInterval(function() {
            var el = document.getElementById('headerClock');
            if (el) el.innerText = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
        }, 1000);

        // Initialize real-time sync (WS-only, custom refresh via fetchSchedulesStatus)
        document.addEventListener('DOMContentLoaded', function() {
            QueueSync.init({
                onlyWS:        true,
                pollInterval:  3000,
                customRefresh: fetchSchedulesStatus,
                customWSHandler: function() { fetchSchedulesStatus(); }
            });
            fetchSchedulesStatus(); // Initial load
        });
    </script>
</body>
</html>

