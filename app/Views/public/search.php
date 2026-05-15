<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Palompon Transit</title>
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
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
            --shadow-sm: 0 2px 10px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 15px rgba(0,0,0,0.08);
            --shadow-lg: 0 8px 30px rgba(0,0,0,0.12);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
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
            0% { transform: translate(0, 0); }
            100% { transform: translate(-100%, 0); }
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
            color: inherit;
        }

        .logo {
            width: 45px;
            height: 45px;
            object-fit: contain;
            border-radius: 10px;
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

        .nav-menu a:hover, .nav-menu a.active {
            color: var(--primary);
            background: rgba(21, 101, 192, 0.05);
        }

        .nav-menu a:hover::after, .nav-menu a.active::after {
            width: 100%;
        }

        .login-btn {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white !important;
            padding: 10px 25px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(21, 101, 192, 0.3);
            text-decoration: none;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(21, 101, 192, 0.4);
        }

        .lang-switcher {
            padding: 8px 12px;
            border-radius: 6px;
            transition: var(--transition);
            cursor: pointer;
            font-size: 14px;
            color: var(--text-muted);
        }

        .lang-switcher:hover {
            background: rgba(21, 101, 192, 0.05);
            color: var(--primary);
        }

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
            padding: 60px 5%;
            text-align: center;
            position: relative;
            overflow: hidden;
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

        /* --- Search Component --- */
        .search-container {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 10;
        }

        .search-bar {
            background: white;
            padding: 8px;
            border-radius: 50px;
            display: flex;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255,255,255,0.3);
            gap: 8px;
        }

        .search-bar input {
            flex: 1;
            border: none;
            padding: 15px 25px;
            font-size: 16px;
            outline: none;
            background: transparent;
            font-family: inherit;
        }

        .search-bar button {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0 35px;
            border-radius: 50px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
        }

        .search-bar button:hover {
            background: var(--primary-dark);
        }

        /* --- Main Content --- */
        .container {
            width: 90%;
            max-width: 1200px;
            margin: -40px auto 40px;
            position: relative;
            z-index: 20;
        }

        .results-header {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 30px;
            border: 1px solid #edf2f7;
        }

        .results-header h2 {
            font-size: 28px;
            color: var(--primary-dark);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .results-info {
            font-size: 14px;
            color: var(--text-muted);
            margin-top: 15px;
        }

        .badge-info {
            background: rgba(25, 118, 210, 0.1);
            color: var(--primary);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 20px;
            transition: var(--transition);
        }

        .back-link:hover {
            gap: 12px;
            color: var(--primary-dark);
        }

        /* --- Results Sections --- */
        .results-section {
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 30px;
            border: 1px solid #edf2f7;
            overflow: hidden;
        }

        .results-section-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 20px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .results-section-header h3 {
            font-size: 18px;
            font-weight: 800;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .results-section-header i {
            font-size: 20px;
        }

        .badge-count {
            background: rgba(255,255,255,0.25);
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 700;
        }

        .section-departures-header {
            background: linear-gradient(135deg, #64748b, #475569) !important;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
        }

        .results-table thead {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .results-table th {
            padding: 18px;
            text-align: left;
            font-size: 13px;
            font-weight: 700;
            color: var(--primary-dark);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .results-table td {
            padding: 18px;
            border-bottom: 1px solid #edf2f7;
            font-size: 14px;
            color: var(--text-main);
        }

        .results-table tbody tr {
            transition: var(--transition);
        }

        .results-table tbody tr:hover {
            background: #f8fafc;
        }

        .results-table tbody tr.boarding {
            background: rgba(67, 160, 71, 0.05);
        }

        .vehicle-icon {
            width: 50px;
            height: 50px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            border-radius: 10px;
            margin-right: 8px;
        }

        .vehicle-icon img {
            width: 40px;
            height: 40px;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }

        .plate-number {
            font-weight: 700;
            color: var(--primary-dark);
            font-size: 15px;
        }

        .route-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .route-info small {
            color: var(--text-muted);
            font-size: 12px;
        }

        .route-info strong {
            color: var(--primary-dark);
            font-weight: 700;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-boarding {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-waiting {
            background: #fff3e0;
            color: #ef6c00;
        }

        .status-departed {
            background: #f1f5f9;
            color: #475569;
        }

        .no-results {
            text-align: center;
            padding: 60px 30px;
        }

        .no-results i {
            font-size: 64px;
            color: var(--text-muted);
            margin-bottom: 20px;
            display: block;
            opacity: 0.5;
        }

        .no-results h3 {
            font-size: 22px;
            color: var(--text-main);
            margin-bottom: 10px;
        }

        .no-results p {
            color: var(--text-muted);
            margin-bottom: 20px;
        }

        .tips-list {
            text-align: left;
            display: inline-flex;
            flex-direction: column;
            gap: 8px;
            font-size: 14px;
            color: var(--text-muted);
        }

        .tips-list li {
            list-style: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .tips-list li i {
            color: var(--success);
            font-size: 14px;
        }

        @media (max-width: 992px) {
            .header-info { display: none !important; }
        }

        @media (max-width: 768px) {
            header { padding: 10px 5%; flex-direction: column; gap: 15px; }
            .nav-menu { flex-direction: column; width: 100%; }
            .logo-text h1 { font-size: 16px; }
            .hero { padding: 40px 5%; }
            .hero h2 { font-size: 32px; }
            .results-table { font-size: 13px; }
            .results-table th, .results-table td { padding: 12px; }
            .results-section-header { flex-direction: column; align-items: flex-start; }
            .vehicle-icon { width: 40px; height: 40px; margin-right: 6px; }
        }

        .blink {
            animation: blinker 1.5s linear infinite;
        }
        @keyframes blinker {
            50% { opacity: 0.3; }
        }
    </style>
</head>
<body>

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

        <nav class="nav-menu" id="navMenu">
            <a href="<?= base_url('guest') ?>" class="<?= current_url() == base_url('guest') ? 'active' : '' ?>"><i class="fas fa-home"></i> Home</a>
            <a href="<?= base_url('schedules') ?>" class="<?= (strpos(uri_string(), 'schedules') !== false) ? 'active' : '' ?>"><i class="fas fa-calendar-alt"></i> Schedules</a>
            <a href="<?= base_url('fares') ?>" class="<?= (strpos(uri_string(), 'fares') !== false) ? 'active' : '' ?>"><i class="fas fa-tags"></i> Fares</a>
            
            <div class="lang-switcher" style="display: flex; align-items: center; gap: 6px;">
                <i class="fas fa-globe"></i>
                <span>EN</span>
                <i class="fas fa-chevron-down" style="font-size: 11px;"></i>
            </div>

            <?php if (session()->get('isLoggedIn')): ?>
                <a href="<?= base_url('/staff/dashboard') ?>" class="login-btn" style="background: var(--success);">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
            <?php else: ?>
                <a href="<?= base_url('login') ?>" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> LogIn
                </a>
            <?php endif; ?>
        </nav>

        <div class="mobile-toggle" onclick="toggleMenu()">
            <i class="fas fa-bars"></i>
        </div>
    </header>

    <!-- Breadcrumbs -->
    <div class="breadcrumb-section">
        <a href="<?= base_url('guest') ?>">Home</a> <i class="fas fa-chevron-right"></i>
        <a href="<?= base_url('guest') ?>">Dashboard</a> <i class="fas fa-chevron-right"></i>
        <span>Search Results</span>
    </div>

    <!-- Hero Section with Search -->
    <section class="hero">
        <h2><i class="fas fa-search"></i> Search Results</h2>
        <p>Find and track your vehicle information below.</p>

        <div class="search-container">
            <form method="get" action="<?= base_url('search') ?>" class="search-bar">
                <input type="text" name="q" placeholder="Search by Plate Number, Owner Name, or Destination..." value="<?= esc($search) ?>" autofocus>
                <button type="submit">Search</button>
            </form>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container">
        <a href="<?= base_url('guest') ?>" class="back-link"><i class="fas fa-arrow-left"></i> Back to Live Monitor</a>

        <!-- Results Info -->
        <div class="results-info">
            <span class="badge-info">
                <i class="fas fa-info-circle"></i> <?= $total_results ?> result<?= $total_results != 1 ? 's' : '' ?> found for "<strong><?= esc($search) ?></strong>"
            </span>
        </div>

        <!-- Active Queue Results -->
        <?php if (!empty($active_results)): ?>
        <div class="results-section">
            <div class="results-section-header">
                <h3><i class="fas fa-clock"></i> Currently in Terminal</h3>
                <span class="badge-count"><?= count($active_results) ?> vehicle<?= count($active_results) != 1 ? 's' : '' ?></span>
            </div>
            <div style="overflow-x: auto;">
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Position/Type</th>
                            <th>Plate Number</th>
                            <th>Owner</th>
                            <th>Route</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($active_results as $item): ?>
                        <tr class="<?= $item['status'] == 'boarding' ? 'boarding' : '' ?>">
                            <td>
                                <div class="vehicle-icon">
                                    <?php
                                        $imgMap = [
                                            'van' => 'van.png',
                                            'jeepney' => 'jeep.png',
                                            'minibus' => 'minibus.png'
                                        ];
                                        $vType = strtolower($item['vehicle_type'] ?? '');
                                        $imgFile = $imgMap[$vType] ?? 'van.png';
                                    ?>
                                    <img src="<?= base_url('images/' . $imgFile) ?>" alt="<?= esc($vType) ?>">
                                </div>
                                <?php if ($vType !== 'jeepney' && !empty($item['position'])): ?>
                                    <span style="font-weight: 700; color: var(--primary);">#<?= $item['position'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td><span class="plate-number"><?= esc($item['plate_number']) ?></span></td>
                            <td><?= esc($item['owner_name'] ?? $item['driver_name'] ?? '—') ?></td>
                            <td>
                                <div class="route-info">
                                    <small><?= esc($item['origin']) ?></small>
                                    <i class="fas fa-arrow-right" style="font-size: 10px; color: var(--primary);"></i>
                                    <strong><?= esc($item['destination']) ?></strong>
                                </div>
                            </td>
                            <td>
                                <?php if ($item['status'] == 'boarding'): ?>
                                    <span class="status-badge status-boarding blink">BOARDING</span>
                                <?php else: ?>
                                    <span class="status-badge status-waiting">WAITING</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Departed Results -->
        <?php if (!empty($departed_results)): ?>
        <div class="results-section">
            <div class="results-section-header section-departures-header">
                <h3><i class="fas fa-plane-departure"></i> Recent Departures</h3>
                <span class="badge-count"><?= count($departed_results) ?> departure<?= count($departed_results) != 1 ? 's' : '' ?></span>
            </div>
            <div style="overflow-x: auto;">
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Plate Number</th>
                            <th>Owner</th>
                            <th>Route</th>
                            <th>Departure Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($departed_results as $item): ?>
                        <tr>
                            <td>
                                <div class="vehicle-icon">
                                    <?php
                                        $imgMap = [
                                            'van' => 'van.png',
                                            'jeepney' => 'jeep.png',
                                            'minibus' => 'minibus.png'
                                        ];
                                        $vType = strtolower($item['vehicle_type'] ?? '');
                                        $imgFile = $imgMap[$vType] ?? 'van.png';
                                    ?>
                                    <img src="<?= base_url('images/' . $imgFile) ?>" alt="<?= esc($vType) ?>">
                                </div>
                            </td>
                            <td><span class="plate-number"><?= esc($item['plate_number']) ?></span></td>
                            <td><?= esc($item['owner_name'] ?? $item['driver_name'] ?? '—') ?></td>
                            <td>
                                <div class="route-info">
                                    <small><?= esc($item['origin']) ?></small>
                                    <i class="fas fa-arrow-right" style="font-size: 10px; color: var(--primary);"></i>
                                    <strong><?= esc($item['destination']) ?></strong>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge status-departed">
                                    <?= date('M d, Y h:i A', strtotime($item['departure_time'])) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- No Results -->
        <?php if ($total_results == 0): ?>
        <div class="results-section">
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h3>No Results Found</h3>
                <p>No vehicles found matching "<strong><?= esc($search) ?></strong>"</p>
                <p>Try searching with:</p>
                <ul class="tips-list">
                    <li><i class="fas fa-check-circle"></i> Plate number (e.g., ABC-1234)</li>
                    <li><i class="fas fa-check-circle"></i> Owner name</li>
                    <li><i class="fas fa-check-circle"></i> Destination city</li>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
    // Live header clock
    setInterval(() => {
        const el = document.getElementById('headerClock');
        if (el) el.innerText = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
    }, 1000);

    // Mobile menu toggle
    function toggleMenu() {
        const m = document.getElementById('navMenu'), o = document.getElementById('navOverlay');
        if (m) m.classList.toggle('open');
        if (o) o.classList.toggle('active');
    }
    </script>
</body>
</html>
