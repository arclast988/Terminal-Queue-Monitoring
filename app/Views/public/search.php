<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - <?= esc(app_name()) ?></title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= esc(app_logo()) ?>">
    <link rel="shortcut icon" href="<?= esc(app_logo()) ?>">
    <link rel="apple-touch-icon" href="<?= esc(app_logo()) ?>">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/design-system.css') ?>?v=3.2">
    <link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>?v=3.2">
    <link rel="stylesheet" href="<?= base_url('assets/css/responsive.css') ?>?v=3.2">
    <?= vehicle_type_colors_css() ?>
    <?= app_theme_css() ?>
    <style>
        :root {
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
            width: 68px;
            height: 68px;
            min-width: 68px;
            flex-shrink: 0;
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
            padding: 35px 5% 25px;
            text-align: center;
            overflow: visible !important;
            min-height: auto;
        }
        .hero::after {
            display: none !important;
        }
        .hero::before {
            display: none !important;
        }

        .hero > * {
            position: relative;
            z-index: 2;
        }

        .hero h2 {
            font-size: 38px;
            color: #000000;
            font-weight: 800;
            margin-bottom: 8px;
            position: relative;
            z-index: 2;
        }

        .hero p {
            font-size: 16px;
            color: #000000;
            max-width: 700px;
            margin: 0 auto 20px;
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
            border-color: var(--primary, #B71C1C);
            box-shadow: 0 0 0 4px var(--primary-soft, rgba(214, 40, 40, 0.15)), var(--shadow-lg);
        }

        .search-bar input {
            flex: 1;
            border: none;
            padding: 15px 25px;
            padding-right: 40px !important;
            font-size: 16px;
            outline: none;
            background: transparent;
            font-family: inherit;
        }

        .search-bar button:not(.guest-clear-search-btn) {
            background: var(--primary, #B71C1C);
            color: var(--on-primary, white);
            border: none;
            padding: 0 35px;
            border-radius: 50px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
        }

        .search-bar button:not(.guest-clear-search-btn):hover {
            background: var(--primary-dark, #8B0000);
        }

        #guest-results-clear-btn {
            position: relative !important;
            right: auto !important;
            top: auto !important;
            transform: none !important;
            flex-shrink: 0;
            align-self: center;
            z-index: 5;
            background: none !important;
            border: none !important;
            cursor: pointer;
            color: #94a3b8;
            padding: 0 4px !important;
            display: none;
            align-items: center;
        }
        @media (max-width: 768px) {
            #guest-results-clear-btn {
            }
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
            max-width: 1400px;
            margin: 25px auto 40px !important;
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
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            box-shadow: var(--shadow-md);
            border: 1px solid #edf2f7;
            margin-bottom: 30px;
            transition: var(--transition);
        }

        .results-section:hover {
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-soft, rgba(198, 40, 40, 0.1));
        }

        .results-section-header {
            padding: 20px 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            background: white !important;
            border-bottom: 1px solid #eee;
        }

        .results-section-header h3 {
            font-size: 20px;
            font-weight: 800;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #000000 !important;
        }

        .results-section-header h3 i {
            font-size: 20px;
            color: var(--primary, #C62828) !important;
        }

        .badge-count {
            background: var(--primary-soft, #fee2e2) !important;
            color: var(--primary, #C62828) !important;
            border: none !important;
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: normal;
        }

        .section-departures-header {
            background: white !important;
        }

        .operator-cell {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 700;
            color: #0f172a;
            font-size: 14px;
        }
        .operator-cell i {
            color: #64748b;
            font-size: 13px;
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
            padding: 15px 25px;
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-muted, #64748b);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .results-table td {
            padding: 20px 25px;
            border-bottom: 1px solid #edf2f7;
            font-size: 14px;
            color: var(--text-main);
        }

        .results-table tbody tr {
            transition: var(--transition);
        }

        .results-table tbody tr:hover {
            background: rgba(30, 64, 175, 0.03);
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
            color: var(--text-main, #1e293b);
            font-family: monospace;
            font-size: 15px;
            background: #f1f5f9;
            padding: 4px 8px;
            border-radius: 6px;
            display: inline-block;
            letter-spacing: 0.5px;
        }

        .operator-cell {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 700;
            color: #0f172a;
            font-size: 14px;
        }
        .operator-cell i {
            color: #64748b;
            font-size: 13px;
        }

        .driver-cell {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
            color: #334155;
            font-size: 14px;
        }
        .driver-cell i {
            color: #64748b;
            font-size: 13px;
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

            .mobile-toggle {
                display: block;
                z-index: 1004;
                position: relative;
            }

            .logo-text h1 { font-size: 15px; letter-spacing: -0.2px; }
            .logo-text p { font-size: 8px; }
            .logo { width: 48px; height: 48px; min-width: 48px; border-radius: 10px; flex-shrink: 0; }
            .hero { padding: 25px 5% 20px !important; }
            .container { width: 92% !important; margin: 20px auto 30px !important; }
            .hero h2 { font-size: 28px; }
            .hero p { font-size: 14px; margin-bottom: 16px; }
            .search-bar {
                flex-direction: row;
                border-radius: 50px;
                padding: 6px 8px;
                align-items: center;
                gap: 0;
            }
            .search-bar input {
                padding: 10px 14px;
                padding-right: 14px !important;
                font-size: 13.5px;
                text-align: left;
                min-width: 0;
                flex: 1;
                width: auto;
            }
            .search-bar button:not(.guest-clear-search-btn) {
                padding: 0 18px;
                height: 42px;
                border-radius: 50px;
                font-size: 12.5px;
                font-weight: 700;
                white-space: nowrap;
                flex-shrink: 0;
                width: auto;
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
                font-size: 12px;
                text-transform: uppercase;
                text-align: left;
            }
            .results-section-header { padding: 16px 20px; flex-wrap: wrap; }
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
<body class="guest-theme">

    <?= view('templates/guest_header', [
        'announcements'      => $announcements ?? [],
        'breadcrumb_current' => 'Search Results',
    ]) ?>

    <!-- Hero Section with Search -->
    <section class="hero">
        <h2>Search Results</h2>
        <p>Find and track your vehicle information below.</p>

        <div class="search-container">
            <form method="get" action="<?= base_url('search') ?>" class="search-bar" style="position: relative;">
                <input
                    type="text"
                    name="q"
                    placeholder="Search by Plate Number, Driver Name, or Destination..."
                    value="<?= esc($search) ?>"
                    autocomplete="off"
                    autofocus
                    id="guest-results-search-input"
                    oninput="toggleGuestResultsClear(this.value)"
                >
                <button type="button" class="guest-clear-search-btn" id="guest-results-clear-btn" onclick="clearGuestResultsSearch()" style="<?= !empty($search) ? 'display: inline-flex !important;' : 'display: none !important;' ?>" title="Clear search">
                    <i class="fas fa-times-circle"></i>
                </button>
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
                            <th>Operator</th>
                            <th>Driver</th>
                            <th>Type</th>
                            <th>Route</th>
                            <th>Est. Departure</th>
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
                            <td data-label="Operator">
                                <div class="operator-cell">
                                    <i class="fas fa-building text-muted"></i>
                                    <?= esc(!empty($item['operator_name']) ? $item['operator_name'] : ($item['driver_name'] ?? '—')) ?>
                                </div>
                            </td>
                            <td data-label="Driver">
                                <div class="driver-cell">
                                    <i class="fas fa-user-tie"></i>
                                    <?= esc($item['driver_name'] ?? $item['owner_name'] ?? '—') ?>
                                </div>
                            </td>
                            <td data-label="Type">
                                <?php
                                    $vType = $item['vehicle_type'] ?? '';
                                    $photoUrl = vehicle_type_photo($vType);
                                ?>
                                <div class="vehicle-type-cell">
                                    <span class="vehicle-type-icon <?= vehicle_type_class($vType) ?>">
                                        <?php if (!empty($photoUrl)): ?>
                                            <img src="<?= esc($photoUrl) ?>" alt="<?= esc($vType) ?>" style="height:36px;width:auto;" title="<?= vehicle_type_label($vType) ?>" data-vt-photo="<?= esc(vehicle_type_key($vType)) ?>">
                                        <?php else: ?>
                                            <i class="fas <?= esc(vehicle_type_icon($vType)) ?>" style="color: <?= esc(vehicle_type_color($vType)) ?>; font-size: 18px;"></i>
                                        <?php endif; ?>
                                    </span>
                                    <?= vehicle_type_badge($vType) ?>
                                </div>
                            </td>
                            <td data-label="Route">
                                <div class="route-info">
                                    <span style="color: var(--text-muted); font-size: 13px;"><?= strtoupper(esc($item['origin'])) ?></span>
                                    <i class="fas fa-arrow-right" style="color: var(--primary); font-size: 12px;"></i>
                                    <span style="font-weight: 700; color: var(--primary-dark);"><?= strtoupper(esc($item['destination'])) ?></span>
                                </div>
                            </td>
                            <td data-label="Est. Departure">
                                <span class="time-display">
                                    <?= !empty($item['estimated_departure']) ? date('g:i A', strtotime($item['estimated_departure'])) : 'TBA' ?>
                                </span>
                                <?php if (!empty($item['capacity'])): ?>
                                <div style="font-size: 12.5px; color: var(--text-muted); margin-top: 2px;">
                                    <span class="passenger-count-num <?= passenger_color_class((int)($item['current_passengers'] ?? 0), (int)($item['capacity'] ?? 1)) ?>"><?= $item['current_passengers'] ?? 0 ?></span>/<?= $item['capacity'] ?> passengers
                                </div>
                                <?php endif; ?>
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

    <script>
        function toggleGuestResultsClear(val) {
            var btn = document.getElementById('guest-results-clear-btn');
            if (btn) {
                if (val && val.trim().length > 0) {
                    btn.style.setProperty('display', 'inline-flex', 'important');
                } else {
                    btn.style.setProperty('display', 'none', 'important');
                }
            }
        }
        function clearGuestResultsSearch() {
            var input = document.getElementById('guest-results-search-input');
            if (input) {
                input.value = '';
                toggleGuestResultsClear('');
                input.focus();
            }
        }
    </script>
</body>
</html>
