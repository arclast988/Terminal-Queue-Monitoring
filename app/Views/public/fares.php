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
        }

        .logo {
            width: 58px;
            height: 58px;
            object-fit: contain;
            border-radius: 10px;
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
            filter: drop-shadow(0 2px 5px rgba(0, 0, 0, 0.15));
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
            position: relative;
            padding: 40px 5% 55px;
            text-align: center;
            overflow: hidden;
            min-height: 245px;
        }
        .hero::after {
            display: none !important;
        }
        .hero::before {
            display: none !important;
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
            z-index: 2;
        }

        /* --- Main Content --- */
        .container {
            width: 90%;
            max-width: 1400px;
            margin: -30px auto 40px !important;
            position: relative;
            z-index: 20;
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

        /* --- Fares Grid --- */
        .fares-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 300px), 1fr));
            gap: clamp(15px, 2vw, 30px);
            margin-bottom: clamp(30px, 5vw, 60px);
            align-items: stretch;
        }

        .fare-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: var(--transition);
            border: 1px solid #edf2f7;
            animation: fadeInUp 0.5s ease-out both;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .fare-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .fare-card.vehicle-type-van { animation-delay: 0.1s; }
        .fare-card.vehicle-type-jeepney { animation-delay: 0.2s; }
        .fare-card.vehicle-type-minibus { animation-delay: 0.3s; }

        .card-header {
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            border-bottom: 1px solid #f1f5f9;
            transition: background var(--transition);
        }

        .card-header h3 {
            font-size: 20px;
            font-weight: 800;
            color: #000000;
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 0;
            transition: transform var(--transition);
        }

        .fare-card:hover .card-header h3 {
            transform: scale(1.02);
        }

        .fare-card.vehicle-type-jeepney .card-header { border-top: 3px solid #1565c0; }
        .fare-card.vehicle-type-van     .card-header { border-top: 3px solid #c62828; }
        .fare-card.vehicle-type-minibus .card-header { border-top: 3px solid #2e7d32; }
        .fare-card.vehicle-type-jeepney .card-header h3 { color: #000000; }
        .fare-card.vehicle-type-van     .card-header h3 { color: #000000; }
        .fare-card.vehicle-type-minibus .card-header h3 { color: #000000; }
        .fare-card.vehicle-type-jeepney:hover { border-color: #1565c0; }
        .fare-card.vehicle-type-van:hover     { border-color: #c62828; }
        .fare-card.vehicle-type-minibus:hover { border-color: #2e7d32; }

        .type-badge {
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            transition: var(--transition);
        }

        .fare-card:hover .type-badge {
            transform: scale(1.05);
        }

        .van-badge {
            background: var(--vehicle-van-soft, #ffebee);
            color: var(--vehicle-van, #c62828);
        }

        .jeepney-badge {
            background: var(--vehicle-jeepney-soft, #e3f2fd);
            color: var(--vehicle-jeepney, #1565c0);
        }

        .minibus-badge {
            background: var(--vehicle-minibus-soft, #e8f5e9);
            color: var(--vehicle-minibus, #2e7d32);
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
            background: rgba(30, 64, 175, 0.03);
            padding-left: 30px;
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
            transition: var(--transition);
        }

        .fare-item:hover .price-tag {
            transform: scale(1.08);
            box-shadow: var(--shadow-sm);
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

        #discountSection {
            animation: fadeInUp 0.6s ease-out both;
            animation-delay: 0.4s;
        }



        /* --- Responsive Queries --- */
        @media (max-width: 768px) {
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

    <?= view('templates/guest_header', [
        'announcements'      => $announcements ?? [],
        'breadcrumb_current' => 'Fares',
    ]) ?>

    <!-- Hero Section -->
    <section class="hero">
        <h2>Route Fares</h2>
        <p>Check official fare rates for all destinations to ensure fair pricing.</p>

        <div class="search-container">
            <form class="search-bar" onsubmit="return false;">
                <input type="text" id="fareSearch" placeholder="Search by Route, Destination, or Origin..." autocomplete="off">
                <button type="button" onclick="document.getElementById('fareSearch').focus();">SEARCH</button>
            </form>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container">

        <div class="fares-grid" id="faresGrid">
            <!-- Van Routes -->
            <div class="fare-card vehicle-type-van">
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
                                        <?php if (in_array(session()->get('role'), ['super_admin', 'admin'], true)): ?>
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
            <div class="fare-card vehicle-type-jeepney">
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
                                        <?php if (in_array(session()->get('role'), ['super_admin', 'admin'], true)): ?>
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
            <div class="fare-card vehicle-type-minibus">
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
                                        <?php if (in_array(session()->get('role'), ['super_admin', 'admin'], true)): ?>
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
            <?php foreach (($vehicleTypes ?? []) as $vehicleType): ?>
                <?php if (in_array($vehicleType['slug'], ['van', 'jeepney', 'minibus'], true)) continue; ?>
                <?php $typeRoutes = ($routesByType ?? [])[$vehicleType['slug']] ?? []; ?>
                <div class="fare-card <?= vehicle_type_class($vehicleType['slug']) ?>">
                    <div class="card-header">
                        <h3><img src="<?= base_url('images/' . vehicle_type_image($vehicleType['slug'])) ?>" alt="<?= esc($vehicleType['name']) ?>" style="width: 45px; height: auto; object-fit: contain;"> <?= esc($vehicleType['name']) ?> Routes</h3>
                    </div>
                    <div class="fare-list">
                        <?php if (!empty($typeRoutes)): ?>
                            <?php foreach ($typeRoutes as $route): ?>
                                <div class="fare-item" style="flex-direction: column; align-items: stretch;">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <div class="dest-info">
                                            <div class="f-dest"><?= strtoupper(esc($route['origin'])) ?> - <?= strtoupper(esc($route['destination'])) ?></div>
                                            <div class="f-origin">From: <?= esc($route['origin']) ?></div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="price-tag">₱<?= number_format($route['fare'], 0) ?></div>
                                            <?php if (in_array(session()->get('role'), ['super_admin', 'admin'], true)): ?>
                                                <a href="<?= base_url('admin/routes/edit/' . $route['id']) ?>" class="btn btn-sm btn-outline-primary shadow-sm" style="border-radius: 8px; padding: 5px 10px;" title="Edit Fare"><i class="fas fa-edit"></i></a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if (!empty($route['discounted_fares'])): ?>
                                    <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #e2e8f0;">
                                        <?php foreach ($route['discounted_fares'] as $df): ?>
                                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 4px 0;">
                                            <span style="font-size: 13px; color: #475569; font-weight: 500;"><i class="fas fa-tag" style="font-size: 10px; color: #94a3b8; margin-right: 5px;"></i><?= esc($df['label']) ?> <span style="color: #94a3b8; font-size: 12px;">(<?= number_format($df['discount_percent'], 0) ?>% off)</span></span>
                                            <span style="font-size: 14px; font-weight: 700; color: #16a34a;">₱<?= number_format($df['amount'], 2) ?></span>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center py-5 px-5">No <?= esc(strtolower($vehicleType['name'])) ?> fares listed.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

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
            <h3 style="font-size: 22px; font-weight: 800; color: #000000; margin-bottom: 6px; display: flex; align-items: center; gap: 10px;">
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
    // Live filter for fare search input
    document.addEventListener('input', function(e) {
        if (e.target && e.target.id === 'fareSearch') {
            var q = e.target.value.toLowerCase().trim();
            var items = document.querySelectorAll('.fare-item');
            items.forEach(function(item) {
                var text = item.textContent.toLowerCase();
                item.style.display = (q === '' || text.indexOf(q) !== -1) ? '' : 'none';
            });
        }
    });

    // ══════════════════════════════════════════════════
    //  Auto-Refresh: Fare Rates + Discounts (every 10s)
    // ══════════════════════════════════════════════════
    var _fareFingerprint = '';
    var _fareFetchPending = false;

    function makeFP(obj) { return JSON.stringify(obj); }

    function formatFare(n) {
        return '₱' + Number(n).toLocaleString('en-PH', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    function escapeHtml(value) {
        return String(value === null || value === undefined ? '' : value).replace(/[&<>"']/g, function(character) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[character];
        });
    }

    function buildFareCard(type, label, badgeClass, imgSrc, routes) {
        var typeClass = type ? ' vehicle-type-' + type : '';
        var html = '<div class="fare-card' + typeClass + '">'
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
            + '<h3 style="font-size: 22px; font-weight: 800; color: #000000; margin-bottom: 6px; display: flex; align-items: center; gap: 10px;">'
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
                var vehicleTypes = Array.isArray(data.vehicle_types) ? data.vehicle_types : [];
                grid.innerHTML = vehicleTypes.map(function(vehicleType) {
                    var routes = (data.routes_by_type && data.routes_by_type[vehicleType.slug])
                        || data[vehicleType.slug + '_routes']
                        || [];
                    return buildFareCard(
                        vehicleType.slug,
                        escapeHtml(vehicleType.name) + ' Routes',
                        '',
                        baseImgUrl + (vehicleType.image || 'minibus.png'),
                        routes
                    );
                }).join('');
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

    </script>
</body>

</html>
