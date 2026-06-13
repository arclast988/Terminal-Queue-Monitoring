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
            padding: 70px 5% 100px;
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
            gap: 0;
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        .search-bar:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.15), var(--shadow-lg);
        }
        .search-bar input {
            flex: 1;
            border: none;
            padding: 14px 24px;
            font-size: 15px;
            outline: none;
            background: transparent;
            font-family: 'Outfit', sans-serif;
            color: var(--text-main);
            min-width: 0;
        }
        .search-bar input::placeholder { color: var(--text-muted); }
        .search-bar button {
            background: #1e3a8a;
            color: white;
            border: none;
            padding: 0 32px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
            white-space: nowrap;
            font-family: 'Outfit', sans-serif;
            letter-spacing: 0.5px;
        }
        .search-bar button:hover { background: #1565c0; transform: scale(1.03); }

        /* --- Main Content --- */
        .container {
            width: 90%;
            max-width: 1200px;
            margin: -50px auto 60px;
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
            .container { margin-top: -40px; }
            .hero h2 { font-size: 28px; }
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

    <?= view('templates/guest_header', [
        'announcements'      => $announcements ?? [],
        'breadcrumb_current' => 'Search Results',
    ]) ?>

    <!-- Hero Section with Search -->
    <section class="hero">
        <h2><i class="fas fa-search"></i> Search Results</h2>
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
                <button type="submit">
                    <i class="fas fa-search" style="margin-right:7px;"></i>SEARCH
                </button>
            </form>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container">
        <div class="back-row">
            <a href="<?= base_url('guest') ?>" class="back-link">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Live Monitor</span>
            </a>
        </div>

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
                            <th>Driver</th>
                            <th>Route</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($active_results as $item): ?>
                        <tr class="<?= $item['status'] == 'boarding' ? 'boarding' : '' ?>">
                            <td>
                                <?php
                                    $imgMap = [
                                        'van' => 'van.png',
                                        'jeepney' => 'jeep.png',
                                        'minibus' => 'minibus.png'
                                    ];
                                    $vType = strtolower($item['vehicle_type'] ?? '');
                                    $imgFile = $imgMap[$vType] ?? 'van.png';
                                ?>
                                <div class="vehicle-icon vehicle-type-icon <?= vehicle_type_class($vType) ?>">
                                    <img src="<?= base_url('images/' . $imgFile) ?>" alt="<?= esc($vType) ?>">
                                </div>
                                <?php if ($vType !== 'jeepney' && !empty($item['position'])): ?>
                                    <span style="font-weight: 700; color: var(--primary);">#<?= $item['position'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td><span class="plate-number"><?= esc($item['plate_number']) ?></span></td>
                            <td><?= esc($item['driver_name'] ?? $item['owner_name'] ?? '—') ?></td>
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
                            <th>Driver</th>
                            <th>Route</th>
                            <th>Departure Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($departed_results as $item): ?>
                        <tr>
                            <td>
                                <?php
                                    $imgMap = [
                                        'van' => 'van.png',
                                        'jeepney' => 'jeep.png',
                                        'minibus' => 'minibus.png'
                                    ];
                                    $vType = strtolower($item['vehicle_type'] ?? '');
                                    $imgFile = $imgMap[$vType] ?? 'van.png';
                                ?>
                                <div class="vehicle-icon vehicle-type-icon <?= vehicle_type_class($vType) ?>">
                                    <img src="<?= base_url('images/' . $imgFile) ?>" alt="<?= esc($vType) ?>">
                                </div>
                            </td>
                            <td><span class="plate-number"><?= esc($item['plate_number']) ?></span></td>
                            <td><?= esc($item['driver_name'] ?? $item['owner_name'] ?? '—') ?></td>
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
                    <li><i class="fas fa-check-circle"></i> Driver name</li>
                    <li><i class="fas fa-check-circle"></i> Destination city</li>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>

</body>
</html>
