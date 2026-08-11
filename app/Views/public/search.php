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
    <link rel="stylesheet" href="<?= base_url('assets/css/design-system.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/responsive.css') ?>">
    <style>
        :root {
            --primary: #1E40AF;
            --primary-dark: #1E3A8A;
            --accent: #FFA726;
            --accent-dark: #F57C00;
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
            overflow-x: clip;
            line-height: 1.6;
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
            width: 58px;
            height: 58px;
            object-fit: contain;
            border-radius: 10px;
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
            filter: drop-shadow(0 2px 5px rgba(0, 0, 0, 0.15));
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
            position: relative;
            padding: 40px 5% 55px;
            text-align: center;
            overflow: hidden;
            min-height: 245px;
        }
        .hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background-repeat: no-repeat;
            background-position: center center;
            background-size: cover;
            opacity: 0.22;
            z-index: 0;
            animation: heroBgSlideshow 30s infinite ease-in-out;
        }
        @keyframes heroBgSlideshow {
            0%, 17% { background-image: url('<?= base_url('images/bg/bg1_townhall.png') ?>'); opacity: 0.22; }
            19% { opacity: 0.05; }
            20%, 37% { background-image: url('<?= base_url('images/bg/bg2_aerial_port.png') ?>'); opacity: 0.22; }
            39% { opacity: 0.05; }
            40%, 57% { background-image: url('<?= base_url('images/bg/bg3_aerial_town.png') ?>'); opacity: 0.22; }
            59% { opacity: 0.05; }
            60%, 77% { background-image: url('<?= base_url('images/bg/bg4_terminal_exterior.png') ?>'); opacity: 0.22; }
            79% { opacity: 0.05; }
            80%, 97% { background-image: url('<?= base_url('images/bg/bg5_terminal_bay.png') ?>'); opacity: 0.22; }
            99% { opacity: 0.05; }
        }
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.22);
            z-index: 1;
        }

        .hero > * {
            position: relative;
            z-index: 2;
        }

        .hero h2 {
            font-size: 38px;
            color: #000000;
            font-weight: 800;
            margin-bottom: 10px;
            position: relative;
            z-index: 2;
        }

        .hero p {
            font-size: 16px;
            color: #000000;
            max-width: 700px;
            margin: 0 auto 24px;
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
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: var(--transition);
        }

        .search-bar:focus-within {
            border-color: #B71C1C;
            box-shadow: 0 0 0 4px rgba(214, 40, 40, 0.15), var(--shadow-lg);
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
            background: #B71C1C;
            color: white;
            border: none;
            padding: 0 35px;
            border-radius: 50px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
        }

        .search-bar button:hover {
            background: #8B0000;
            transform: scale(1.03);
        }

        .filter-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-top: 18px;
            background: rgba(255,255,255,0.85);
            color: var(--primary-dark);
            font-weight: 600;
            font-size: 14px;
            padding: 8px 18px;
            border-radius: 50px;
            backdrop-filter: blur(6px);
        }
        .filter-badge a {
            color: var(--primary-dark);
            text-decoration: none;
            font-size: 18px;
            line-height: 1;
            opacity: 0.7;
            transition: var(--transition);
        }
        .filter-badge a:hover { opacity: 1; }

        /* --- Main Content --- */
        .container {
            width: 90%;
            max-width: 1200px;
            margin: -30px auto 40px !important;
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

        .back-row {
            margin-bottom: 24px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: white;
            color: var(--primary);
            border: 2px solid rgba(21,101,192,0.2);
            padding: 10px 22px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            transition: var(--transition);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .back-link i {
            transition: transform var(--transition);
        }

        .back-link:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .back-link:hover i {
            transform: translateX(-4px);
        }

        .back-link:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.25), var(--shadow-sm);
        }

        @media (max-width: 420px) {
            .back-link span { display: none; }
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
            background: rgba(30, 64, 175, 0.03);
            transform: translateX(4px);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .results-header {
            animation: fadeInUp 0.5s ease-out both;
        }

        .results-section {
            animation: fadeInUp 0.5s ease-out both;
            animation-delay: 0.15s;
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
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .route-info small {
            color: var(--text-muted);
            font-size: 12px;
        }

        .route-info strong {
            color: var(--primary-dark);
            font-weight: 700;
        }

        .time-display {
            font-size: 16px;
            font-weight: 800;
            color: var(--primary);
            background: var(--primary-soft);
            padding: 5px 12px;
            border-radius: 8px;
            display: inline-block;
        }

        .driver-cell {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
            color: var(--text-main);
            font-size: 14px;
        }
        .driver-cell i {
            color: var(--text-muted);
            font-size: 12px;
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
            header { padding: 10px 5%; min-height: 65px; border-bottom: 1px solid #eee; }
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

            .mobile-toggle {
                display: block;
                z-index: 1004;
                position: relative;
            }

            .logo-text h1 { font-size: 15px; letter-spacing: -0.2px; }
            .logo-text p { font-size: 8px; }
            .logo { width: 40px; height: 40px; border-radius: 8px; }
            .hero { padding: 50px 5% 80px; }
            .container { margin-top: -30px !important; }
            .hero h2 { font-size: 28px; }
            .search-bar {
                flex-direction: column;
                border-radius: 18px;
                padding: 10px;
                gap: 8px;
            }
            .search-bar input {
                padding: 12px 16px;
                text-align: center;
                width: 100%;
            }
            .search-bar button {
                padding: 12px;
                border-radius: 12px;
                width: 100%;
            }
            .results-table, 
            .results-table thead, 
            .results-table tbody, 
            .results-table th, 
            .results-table td, 
            .results-table tr { 
                display: block; 
                width: 100%;
            }
            .results-table thead { 
                display: none; 
            }
            .results-table tr {
                border: 1px solid #edf2f7;
                border-radius: 15px;
                margin-bottom: 15px;
                padding: 15px;
                background: white;
                box-shadow: var(--shadow-sm);
                transition: var(--transition);
            }
            .results-table tr:hover {
                transform: translateY(-3px);
                box-shadow: var(--shadow-md);
                border-color: var(--primary);
            }
            .results-table td {
                padding: 10px 0;
                border: none;
                display: flex;
                justify-content: space-between;
                align-items: center;
                text-align: right;
                border-bottom: 1px solid #f1f5f9;
            }
            .results-table td:last-child {
                border-bottom: none;
            }
            .results-table td::before {
                content: attr(data-label);
                font-weight: 700;
                color: var(--text-muted);
                font-size: 11px;
                text-transform: uppercase;
                text-align: left;
            }
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

    <?= view('templates/guest_header', [
        'announcements'      => $announcements ?? [],
        'breadcrumb_current' => 'Search Results',
    ]) ?>

    <!-- Hero Section with Search -->
    <section class="hero">
        <h2>Search Results</h2>
        <p>Find and track your vehicle information below.</p>

        <div class="search-container">
            <form method="get" action="<?= base_url('search') ?>" class="search-bar">
                <input
                    type="text"
                    name="q"
                    placeholder="Search by Plate Number, Driver Name, or Destination..."
                    value="<?= esc($search) ?>"
                    autocomplete="off"
                    autofocus
                >
                <button type="submit">SEARCH</button>
            </form>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container">

        <div class="back-row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
            <a href="<?= base_url('guest') ?>" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Live Monitor
            </a>
            <?php if (!empty($search)): ?>
                <div class="filter-badge" style="margin: 0;">
                    <span><i class="fas fa-info-circle"></i> <?= $total_results ?> result<?= $total_results != 1 ? 's' : '' ?> found for "<strong><?= esc($search) ?></strong>"</span>
                    <a href="<?= base_url('search') ?>">&times;</a>
                </div>
            <?php endif; ?>
        </div>


        <!-- Active Queue Results -->
        <?php if (!empty($active_results)): ?>
        <div class="results-section">
            <div class="results-section-header">
                <h3><i class="fas fa-clock"></i> Currently in Terminal</h3>
                <span class="badge-count"><?= count($active_results) ?> vehicle<?= count($active_results) != 1 ? 's' : '' ?></span>
            </div>
            <div>
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Queue #</th>
                            <th>Plate Number</th>
                            <th>Driver</th>
                            <th>Type</th>
                            <th>Route</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($active_results as $item): ?>
                        <tr class="<?= $item['status'] == 'boarding' ? 'boarding' : '' ?>">
                            <td data-label="Queue #">
                                <span class="time-display">#<?= esc($item['position'] ?? '—') ?></span>
                            </td>
                            <td data-label="Plate Number">
                                <span class="plate-number"><?= esc($item['plate_number']) ?></span>
                            </td>
                            <td data-label="Driver">
                                <div class="driver-cell">
                                    <i class="fas fa-user-tie"></i>
                                    <?= esc($item['driver_name'] ?? $item['owner_name'] ?? '—') ?>
                                </div>
                            </td>
                            <td data-label="Type">
                                <?php $vType = $item['vehicle_type'] ?? ''; ?>
                                <div class="vehicle-type-cell">
                                    <span class="vehicle-type-icon <?= vehicle_type_class($vType) ?>">
                                        <img src="<?= base_url('images/' . vehicle_type_image($vType)) ?>" alt="<?= esc($vType) ?>" style="height:36px;width:auto;" title="<?= vehicle_type_label($vType) ?>">
                                    </span>
                                    <?= vehicle_type_badge($vType) ?>
                                </div>
                            </td>
                            <td data-label="Route">
                                <div class="route-info">
                                    <span style="color: var(--text-muted); font-size: 13px;"><?= esc($item['origin']) ?></span>
                                    <i class="fas fa-arrow-right" style="color: var(--primary); font-size: 12px;"></i>
                                    <span style="font-weight: 700; color: var(--primary-dark);"><?= esc($item['destination']) ?></span>
                                </div>
                            </td>
                            <td data-label="Status">
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
            <div>
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Plate Number</th>
                            <th>Driver</th>
                            <th>Type</th>
                            <th>Route</th>
                            <th>Departure Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($departed_results as $item): ?>
                        <tr>
                            <td data-label="Plate Number">
                                <span class="plate-number"><?= esc($item['plate_number']) ?></span>
                            </td>
                            <td data-label="Driver">
                                <div class="driver-cell">
                                    <i class="fas fa-user-tie"></i>
                                    <?= esc($item['driver_name'] ?? $item['owner_name'] ?? '—') ?>
                                </div>
                            </td>
                            <td data-label="Type">
                                <?php $vType = $item['vehicle_type'] ?? ''; ?>
                                <div class="vehicle-type-cell">
                                    <span class="vehicle-type-icon <?= vehicle_type_class($vType) ?>">
                                        <img src="<?= base_url('images/' . vehicle_type_image($vType)) ?>" alt="<?= esc($vType) ?>" style="height:36px;width:auto;" title="<?= vehicle_type_label($vType) ?>">
                                    </span>
                                    <?= vehicle_type_badge($vType) ?>
                                </div>
                            </td>
                            <td data-label="Route">
                                <div class="route-info">
                                    <span style="color: var(--text-muted); font-size: 13px;"><?= esc($item['origin']) ?></span>
                                    <i class="fas fa-arrow-right" style="color: var(--primary); font-size: 12px;"></i>
                                    <span style="font-weight: 700; color: var(--primary-dark);"><?= esc($item['destination']) ?></span>
                                </div>
                            </td>
                            <td data-label="Departure Time">
                                <span class="time-display">
                                    <?= date('g:i A', strtotime($item['departure_time'])) ?>
                                </span>
                                <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                                    <?= date('M d, Y', strtotime($item['departure_time'])) ?>
                                </div>
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
                    <li><i class="fas fa-check-circle"></i> Driver name</li>
                    <li><i class="fas fa-check-circle"></i> Destination city</li>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <!-- Footer -->
    <?= $this->include('templates/guestfooter') ?>

</body>
</html>
