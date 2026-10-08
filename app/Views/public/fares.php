<!DOCTYPE html>
<html lang="en">

<head>
    <?= view('partials/maze_snippet') ?>
    <meta charset="UTF-8">
    <?= $this->include('partials/interaction_policy') ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="only light">
    <title>Route Fares · <?= esc(app_system_title()) ?></title>
    <?= view('partials/app_install') ?>

    <!-- Modern Fonts -->
    <?= view('partials/font_assets', ['fontIcons' => true]) ?>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= app_asset_url('assets/vendor/fontawesome/css/all.min.css') ?>">
    <!-- Bootstrap Icons (Fallback) -->
    <link rel="stylesheet" href="<?= app_asset_url('assets/vendor/bootstrap-icons/font/bootstrap-icons.css') ?>">
    <link rel="stylesheet" href="<?= app_asset_url('assets/css/design-system.css') ?>">
    <link rel="stylesheet" href="<?= app_asset_url('assets/css/modern-frontend.css') ?>">
    <link rel="stylesheet" href="<?= app_asset_url('assets/css/responsive.css') ?>">
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
            --shadow-sm: 0 2px 10px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 15px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 8px 30px rgba(0, 0, 0, 0.12);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent !important;
        }

        button,
        .btn,
        input[type="button"],
        input[type="submit"],
        [role="button"],
        a,
        .search-bar button,
        .guest-clear-search-btn {
            -webkit-tap-highlight-color: transparent !important;
        }

        button:focus:not(:focus-visible),
        .btn:focus:not(:focus-visible),
        [role="button"]:focus:not(:focus-visible),
        a:focus:not(:focus-visible) {
            outline: none !important;
            box-shadow: none !important;
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
            width: 68px;
            height: 68px;
            min-width: 68px;
            flex-shrink: 0;
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
            background: var(--primary, #1e3a8a);
            color: var(--on-primary, white) !important;
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
            background: var(--primary-dark, #172554) !important;
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
            z-index: 2;
        }

        /* --- Main Content --- */
        .container {
            width: 90%;
            max-width: 1400px;
            margin: 25px auto 40px !important;
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
            border-color: var(--primary, #B71C1C);
            box-shadow: 0 0 0 4px var(--primary-soft, rgba(214, 40, 40, 0.15)), var(--shadow-lg);
        }

        .search-bar input {
            flex: 1;
            border: none;
            padding: 15px 12px;
            font-size: 16px;
            outline: none;
            background: transparent;
            font-family: inherit;
        }

        #guest-fare-clear-btn {
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
            #guest-fare-clear-btn {
            }
        }

        .fare-search-empty {
            padding: 22px;
            margin-bottom: 20px;
            text-align: center;
            color: #475569;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
        }
        .fare-search-empty[hidden] { display: none; }

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
            border-top: 4px solid var(--fare-accent, #c62828) !important;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        /* Entrance animation only on initial page load, not during live sync */
        .fares-grid.initial-load .fare-card {
            animation: fadeInUp 0.5s ease-out both;
        }

        .fare-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: var(--fare-accent, var(--primary));
        }

        .fare-card.vehicle-type-van     { --fare-accent: var(--vehicle-van, #c62828); }
        .fare-card.vehicle-type-jeepney { --fare-accent: var(--vehicle-jeepney, #1565c0); }
        .fare-card.vehicle-type-minibus { --fare-accent: var(--vehicle-minibus, #2e7d32); }
        .fare-card.vehicle-type-bus     { --fare-accent: var(--vehicle-bus, #ea580c); }

        .fares-grid.initial-load .fare-card.vehicle-type-van     { animation-delay: 0.1s; }
        .fares-grid.initial-load .fare-card.vehicle-type-jeepney { animation-delay: 0.2s; }
        .fares-grid.initial-load .fare-card.vehicle-type-minibus { animation-delay: 0.3s; }
        .fares-grid.initial-load .fare-card.vehicle-type-bus     { animation-delay: 0.4s; }

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
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 0;
            transition: transform var(--transition);
        }

        .card-header h3 img {
            width: 45px;
            height: 38px;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }

        .card-header h3 [data-vt-icon-box],
        .fare-card-vt-icon {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 38px !important;
            height: 38px !important;
            min-width: 38px !important;
            min-height: 38px !important;
            border-radius: 10px !important;
            flex-shrink: 0 !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-header h3 [data-vt-icon-box] i,
        .fare-card-vt-icon i {
            color: #ffffff !important;
            font-size: 16px !important;
            display: inline-block;
            line-height: 1;
        }

        .fare-card:hover .card-header h3 [data-vt-icon-box],
        .fare-card:hover .fare-card-vt-icon {
            transform: scale(1.08);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .fare-card-vt-icon.is-light,
        [data-vt-icon-box].is-light {
            border: 1.5px solid #cbd5e1 !important;
        }
        .fare-card-vt-icon.is-light i,
        [data-vt-icon-box].is-light i {
            color: #0f172a !important;
        }

        /* Essential utility fallbacks for public views without Bootstrap */
        .text-white { color: #ffffff !important; }
        .rounded-2 { border-radius: 8px !important; }
        .d-inline-flex { display: inline-flex !important; }
        .align-items-center { align-items: center !important; }
        .justify-content-center { justify-content: center !important; }

        .fare-card:hover .card-header h3 {
            transform: scale(1.02);
        }

        .type-badge {
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 12px;
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
            min-height: 220px;
            display: flex;
            flex-direction: column;
        }

        .fare-empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 50px 25px;
            min-height: 220px;
            height: 100%;
            flex: 1;
            color: #64748b;
            font-size: 15px;
            font-weight: 500;
            gap: 12px;
            background: #fafbfc;
        }

        .fare-empty-state i {
            font-size: 32px;
            color: #94a3b8;
            opacity: 0.6;
        }

        .fare-empty-state span {
            color: #64748b;
            font-size: 14.5px;
            font-weight: 600;
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

        #discountSection.initial-load {
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

            .logo { width: 48px; height: 48px; min-width: 48px; border-radius: 10px; flex-shrink: 0; }

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

            .mobile-toggle {
                display: block;
                z-index: 1004;
                position: relative;
            }

            .hero {
                padding: 25px 5% 20px !important;
            }

            .hero h2 {
                font-size: 28px;
            }

            .hero p {
                font-size: 14px;
                margin-bottom: 16px;
            }

            .container {
                width: 92% !important;
                margin: 20px auto 30px !important;
            }

            .fares-grid {
                grid-template-columns: 1fr;
            }

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
        }

        @media (max-width: 420px) {
            .hero {
                padding: 18px 4% 14px !important;
            }
            .hero h2 {
                font-size: 22px !important;
            }
            .hero p {
                font-size: 12.5px !important;
                margin-bottom: 10px !important;
            }
            .container {
                width: 96% !important;
                padding: 0 !important;
            }
            .search-container {
                max-width: 100% !important;
            }
            .search-bar {
                padding: 4px 6px;
            }
            .search-bar input {
                padding: 7px 10px;
                font-size: 12px;
            }
            .fare-card {
                padding: 10px !important;
                border-radius: 12px !important;
                margin-bottom: 12px !important;
            }
        }
    </style>
    <?= $this->include('partials/interaction_assets') ?>
    <link rel="stylesheet" href="<?= app_asset_url('assets/css/guest-shell.css') ?>">
</head>

<body class="guest-theme">

    <?= view('templates/guest_header', [
        'interaction_assets_loaded' => true,
        'guest_shell_assets_loaded' => true,
        'announcements'      => $announcements ?? [],
        'breadcrumb_current' => 'Fares',
    ]) ?>

    <!-- Hero Section -->
    <section class="hero">
        <h2>Route Fares</h2>
        <p>Check official fare rates for all destinations to ensure fair pricing.</p>

        <div class="search-container">
            <div class="search-bar guest-page-search" role="search" style="position: relative;">
                <i class="fas fa-search guest-page-search-icon" aria-hidden="true"></i>
                <input type="text" id="fareSearch" placeholder="Search destination or vehicle type" aria-label="Search fares" autocomplete="off">
                <button type="button" class="guest-clear-search-btn" id="guest-fare-clear-btn" onclick="clearGuestFareSearch()" style="display: none !important;" title="Clear search" aria-label="Clear fare search">
                    <i class="fas fa-times-circle"></i>
                </button>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container">

        <div class="fare-search-empty" id="fareSearchEmpty" role="status" hidden>No fares match your search. Try another destination or vehicle type.</div>

        <div class="fares-grid initial-load" id="faresGrid">
            <!-- Van Routes -->
            <div class="fare-card vehicle-type-van" id="fare-card-van" data-vehicle-type="van">
                <div class="card-header">
                    <?php
                        $vanPhoto = vehicle_type_photo('van');
                        $vanColor = vehicle_type_color('van');
                        $vanIcon = vehicle_type_icon('van');
                        $vanIsLight = (contrast_text_color($vanColor) === '#0f172a');
                        $vanIconColor = $vanIsLight ? '#0f172a' : '#ffffff';
                        $vanIconBorder = $vanIsLight ? 'border: 1.5px solid #cbd5e1;' : '';
                    ?>
                    <h3>
                        <?php if (!empty($vanPhoto)): ?>
                            <img src="<?= esc($vanPhoto) ?>" alt="Van" style="width: 36px; height: 36px; object-fit: contain;" data-vt-photo="van">
                        <?php else: ?>
                            <span class="fare-card-vt-icon <?= $vanIsLight ? 'is-light' : '' ?>" style="background-color: var(--vehicle-van, <?= esc($vanColor) ?>); color: <?= esc($vanIconColor) ?>; <?= $vanIconBorder ?>" data-vt-icon-box="van">
                                <i class="fas <?= esc($vanIcon) ?>" style="color: <?= esc($vanIconColor) ?> !important;"></i>
                            </span>
                        <?php endif; ?>
                        Van Routes
                    </h3>
                </div>
                <div class="fare-list">
                    <?php if (!empty($van_routes)): ?>
                        <?php foreach ($van_routes as $route): ?>
                            <div class="fare-item" style="flex-direction: column; align-items: stretch;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div class="dest-info">
                                        <div class="f-dest"><?= strtoupper(esc($route['origin'])) ?> - <?= strtoupper(esc($route['destination'])) ?></div>
                                        <div class="f-origin">From: <?= strtoupper(esc($route['origin'])) ?></div>
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
                                        <span style="font-size: 13.5px; color: #334155; font-weight: 600;">
                                            <i class="fas fa-tag" style="font-size: 12px; color: #64748b; margin-right: 6px;"></i>
                                            <?= esc($df['label']) ?> <span style="color: #64748b; font-size: 12.5px; font-weight: 500;">(<?= number_format($df['discount_percent'], 0) ?>% off)</span>
                                        </span>
                                        <span style="font-size: 14px; font-weight: 700; color: #16a34a;">₱<?= number_format($df['amount'], 2) ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="fare-empty-state">
                            <i class="fas fa-route"></i>
                            <span>No van fares listed.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Jeepney Routes -->
            <div class="fare-card vehicle-type-jeepney" id="fare-card-jeepney" data-vehicle-type="jeepney">
                <div class="card-header">
                    <?php
                        $jeepPhoto = vehicle_type_photo('jeepney');
                        $jeepColor = vehicle_type_color('jeepney');
                        $jeepIcon = vehicle_type_icon('jeepney');
                        $jeepIsLight = (contrast_text_color($jeepColor) === '#0f172a');
                        $jeepIconColor = $jeepIsLight ? '#0f172a' : '#ffffff';
                        $jeepIconBorder = $jeepIsLight ? 'border: 1.5px solid #cbd5e1;' : '';
                    ?>
                    <h3>
                        <?php if (!empty($jeepPhoto)): ?>
                            <img src="<?= esc($jeepPhoto) ?>" alt="Jeepney" style="width: 36px; height: 36px; object-fit: contain;" data-vt-photo="jeepney">
                        <?php else: ?>
                            <span class="fare-card-vt-icon <?= $jeepIsLight ? 'is-light' : '' ?>" style="background-color: var(--vehicle-jeepney, <?= esc($jeepColor) ?>); color: <?= esc($jeepIconColor) ?>; <?= $jeepIconBorder ?>" data-vt-icon-box="jeepney">
                                <i class="fas <?= esc($jeepIcon) ?>" style="color: <?= esc($jeepIconColor) ?> !important;"></i>
                            </span>
                        <?php endif; ?>
                        Jeepney Routes
                    </h3>
                </div>
                <div class="fare-list">
                    <?php if (!empty($jeepney_routes)): ?>
                        <?php foreach ($jeepney_routes as $route): ?>
                            <div class="fare-item" style="flex-direction: column; align-items: stretch;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div class="dest-info">
                                        <div class="f-dest"><?= strtoupper(esc($route['origin'])) ?> - <?= strtoupper(esc($route['destination'])) ?></div>
                                        <div class="f-origin">From: <?= strtoupper(esc($route['origin'])) ?></div>
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
                                        <span style="font-size: 13.5px; color: #334155; font-weight: 600;">
                                            <i class="fas fa-tag" style="font-size: 12px; color: #64748b; margin-right: 6px;"></i>
                                            <?= esc($df['label']) ?> <span style="color: #64748b; font-size: 12.5px; font-weight: 500;">(<?= number_format($df['discount_percent'], 0) ?>% off)</span>
                                        </span>
                                        <span style="font-size: 14px; font-weight: 700; color: #16a34a;">₱<?= number_format($df['amount'], 2) ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="fare-empty-state">
                            <i class="fas fa-route"></i>
                            <span>No jeepney fares listed.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Minibus Routes -->
            <div class="fare-card vehicle-type-minibus" id="fare-card-minibus" data-vehicle-type="minibus">
                <div class="card-header">
                    <?php
                        $minibusPhoto = vehicle_type_photo('minibus');
                        $minibusColor = vehicle_type_color('minibus');
                        $minibusIcon = vehicle_type_icon('minibus');
                        $minibusIsLight = (contrast_text_color($minibusColor) === '#0f172a');
                        $minibusIconColor = $minibusIsLight ? '#0f172a' : '#ffffff';
                        $minibusIconBorder = $minibusIsLight ? 'border: 1.5px solid #cbd5e1;' : '';
                    ?>
                    <h3>
                        <?php if (!empty($minibusPhoto)): ?>
                            <img src="<?= esc($minibusPhoto) ?>" alt="Minibus" style="width: 36px; height: 36px; object-fit: contain;" data-vt-photo="minibus">
                        <?php else: ?>
                            <span class="fare-card-vt-icon <?= $minibusIsLight ? 'is-light' : '' ?>" style="background-color: var(--vehicle-minibus, <?= esc($minibusColor) ?>); color: <?= esc($minibusIconColor) ?>; <?= $minibusIconBorder ?>" data-vt-icon-box="minibus">
                                <i class="fas <?= esc($minibusIcon) ?>" style="color: <?= esc($minibusIconColor) ?> !important;"></i>
                            </span>
                        <?php endif; ?>
                        Minibus Routes
                    </h3>
                </div>
                <div class="fare-list">
                    <?php if (!empty($minibus_routes)): ?>
                        <?php foreach ($minibus_routes as $route): ?>
                            <div class="fare-item" style="flex-direction: column; align-items: stretch;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div class="dest-info">
                                        <div class="f-dest"><?= strtoupper(esc($route['origin'])) ?> - <?= strtoupper(esc($route['destination'])) ?></div>
                                        <div class="f-origin">From: <?= strtoupper(esc($route['origin'])) ?></div>
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
                                        <span style="font-size: 13.5px; color: #334155; font-weight: 600;">
                                            <i class="fas fa-tag" style="font-size: 12px; color: #64748b; margin-right: 6px;"></i>
                                            <?= esc($df['label']) ?> <span style="color: #64748b; font-size: 12.5px; font-weight: 500;">(<?= number_format($df['discount_percent'], 0) ?>% off)</span>
                                        </span>
                                        <span style="font-size: 14px; font-weight: 700; color: #16a34a;">₱<?= number_format($df['amount'], 2) ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="fare-empty-state">
                            <i class="fas fa-route"></i>
                            <span>No minibus fares listed.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php foreach (($vehicleTypes ?? []) as $vehicleType): ?>
                <?php if (in_array($vehicleType['slug'], ['van', 'jeepney', 'minibus'], true)) continue; ?>
                <?php $typeRoutes = ($routesByType ?? [])[$vehicleType['slug']] ?? []; ?>
                <?php 
                    $vtSlug = $vehicleType['slug'];
                    $vtColor = !empty($vehicleType['color']) ? $vehicleType['color'] : vehicle_type_color($vtSlug);
                    $vtIcon = !empty($vehicleType['icon']) ? $vehicleType['icon'] : vehicle_type_icon($vtSlug);
                    $vtPhoto = vehicle_type_photo($vtSlug);
                    $vtIsLight = (contrast_text_color($vtColor) === '#0f172a');
                    $vtIconColor = $vtIsLight ? '#0f172a' : '#ffffff';
                    $vtIconBorder = $vtIsLight ? 'border: 1.5px solid #cbd5e1;' : '';
                ?>
                <div class="fare-card <?= vehicle_type_class($vtSlug) ?>" id="fare-card-<?= esc($vtSlug) ?>" data-vehicle-type="<?= esc($vtSlug) ?>" style="--fare-accent: <?= esc($vtColor) ?>;">
                    <div class="card-header">
                        <h3>
                            <?php if (!empty($vtPhoto)): ?>
                                <img src="<?= esc($vtPhoto) ?>" alt="<?= esc($vehicleType['name']) ?>" data-vt-photo="<?= esc(vehicle_type_key($vtSlug)) ?>">
                            <?php else: ?>
                                <span class="fare-card-vt-icon <?= $vtIsLight ? 'is-light' : '' ?>" style="background-color: var(--vehicle-<?= esc($vtSlug) ?>, <?= esc($vtColor) ?>); color: <?= esc($vtIconColor) ?>; <?= $vtIconBorder ?>" data-vt-icon-box="<?= esc($vtSlug) ?>">
                                    <i class="fas <?= esc($vtIcon) ?>" style="color: <?= esc($vtIconColor) ?> !important;"></i>
                                </span>
                            <?php endif; ?>
                            <?= esc($vehicleType['name']) ?> Routes
                        </h3>
                    </div>
                    <div class="fare-list">
                        <?php if (!empty($typeRoutes)): ?>
                            <?php foreach ($typeRoutes as $route): ?>
                                <div class="fare-item" style="flex-direction: column; align-items: stretch;">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <div class="dest-info">
                                            <div class="f-dest"><?= strtoupper(esc($route['origin'])) ?> - <?= strtoupper(esc($route['destination'])) ?></div>
                                            <div class="f-origin">From: <?= strtoupper(esc($route['origin'])) ?></div>
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
                                            <span style="font-size: 13.5px; color: #334155; font-weight: 600;"><i class="fas fa-tag" style="font-size: 12px; color: #64748b; margin-right: 6px;"></i><?= esc($df['label']) ?> <span style="color: #64748b; font-size: 12.5px; font-weight: 500;">(<?= number_format($df['discount_percent'], 0) ?>% off)</span></span>
                                            <span style="font-size: 14px; font-weight: 700; color: #16a34a;">₱<?= number_format($df['amount'], 2) ?></span>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="fare-empty-state">
                                <i class="fas fa-route"></i>
                                <span>No <?= esc(strtolower($vehicleType['name'])) ?> fares listed.</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>

        <!-- Passenger Discount Rates -->
        <div id="discountSection" class="initial-load">
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
            <p style="color: #0f172a; font-size: 15px; font-weight: 600; margin-bottom: 24px; text-shadow: 0 1px 2px rgba(255, 255, 255, 0.9);">
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
    function applyFareSearch() {
        var input = document.getElementById('fareSearch');
        var q = input ? input.value.toLowerCase().trim() : '';
        var cards = document.querySelectorAll('#faresGrid .fare-card');
        var visibleCards = 0;

        cards.forEach(function(card) {
            var cardHeader = card.querySelector('.card-header h3');
            var headerText = cardHeader ? cardHeader.textContent.toLowerCase() : '';
            var items = card.querySelectorAll('.fare-item');
            var emptyState = card.querySelector('.fare-empty-state');
            var matchCount = 0;

            // Check if search matches vehicle type (e.g. "van", "jeepney", "minibus", "bus")
            var headerMatches = (q !== '' && headerText.indexOf(q) !== -1);

            items.forEach(function(item) {
                var itemText = item.textContent.toLowerCase();
                if (q === '' || headerMatches || itemText.indexOf(q) !== -1) {
                    item.style.display = '';
                    matchCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            if (emptyState) {
                if (q === '' || headerMatches) {
                    emptyState.style.display = '';
                } else {
                    emptyState.style.display = 'none';
                }
            }

            // If user is searching, hide cards that have 0 matches; show all cards when query is cleared
            if (q === '') {
                card.style.display = '';
            } else {
                if (matchCount > 0 || (emptyState && headerMatches)) {
                    card.style.display = '';
                    visibleCards++;
                } else {
                    card.style.display = 'none';
                }
            }
        });
        var noResults = document.getElementById('fareSearchEmpty');
        if (noResults) noResults.hidden = q === '' || visibleCards > 0;
    }

    function toggleGuestFareClear(val) {
        var btn = document.getElementById('guest-fare-clear-btn');
        if (btn) {
            if (val && val.trim().length > 0) {
                btn.style.setProperty('display', 'inline-flex', 'important');
            } else {
                btn.style.setProperty('display', 'none', 'important');
            }
        }
    }
    function clearGuestFareSearch() {
        var input = document.getElementById('fareSearch');
        if (input) {
            input.value = '';
            toggleGuestFareClear('');
            applyFareSearch();
            input.focus();
        }
    }

    // Live filter for fare search input
    document.addEventListener('input', function(e) {
        if (e.target && e.target.id === 'fareSearch') {
            toggleGuestFareClear(e.target.value);
            applyFareSearch();
        }
    });

    // ══════════════════════════════════════════════════
    //  Auto-Refresh: Fare Rates + Discounts (every 10s)
    // ══════════════════════════════════════════════════
    var _fareFingerprint = '';
    var _fareFetchPending = false;
    var _cardFingerprints = {};
    var _discountFingerprint = '';
    var _vehicleTypesFingerprint = null;
    var _isAdmin = <?= in_array(session()->get('role'), ['super_admin', 'admin'], true) ? 'true' : 'false' ?>;
    var _adminEditBaseUrl = '<?= base_url('admin/routes/edit/') ?>';

    // Remove initial-load entrance animation class after page has settled (1.2s)
    setTimeout(function() {
        var grid = document.getElementById('faresGrid');
        if (grid) grid.classList.remove('initial-load');
        var disc = document.getElementById('discountSection');
        if (disc) disc.classList.remove('initial-load');
    }, 1200);

    // Stable content fingerprint that ignores timestamps, cached_at, and sync_token
    function makeContentFingerprint(data) {
        if (!data) return '';
        return JSON.stringify({
            vt: (data.vehicle_types || []).map(function(v) {
                return { slug: v.slug, name: v.name, color: v.color, image: v.image };
            }),
            routes: data.routes_by_type || {},
            discounts: (data.discounts || []).map(function(d) {
                return { id: d.id, type: d.type, label: d.label, percent: d.discount_percent, active: d.is_active };
            })
        });
    }

    function makeTypeRoutesFingerprint(routes) {
        if (!routes || !routes.length) return 'empty';
        return JSON.stringify(routes.map(function(r) {
            return {
                id: r.id,
                origin: r.origin,
                destination: r.destination,
                fare: r.fare,
                df: r.discounted_fares || {}
            };
        }));
    }

    function formatFare(n) {
        return '₱' + Number(n).toLocaleString('en-PH', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    function escapeHtml(value) {
        return String(value === null || value === undefined ? '' : value).replace(/[&<>"']/g, function(character) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[character];
        });
    }

    function buildFareListContent(label, routes) {
        var html = '';
        if (routes && routes.length > 0) {
            routes.forEach(function(route) {
                var origin = escapeHtml(route.origin || '');
                var destination = escapeHtml(route.destination || '');
                var fareDisplay = formatFare(route.fare);
                var actionHtml = _isAdmin
                    ? '<div class="d-flex align-items-center gap-2">'
                        + '<div class="price-tag">' + fareDisplay + '</div>'
                        + '<a href="' + _adminEditBaseUrl + '/' + encodeURIComponent(route.id) + '" class="btn btn-sm btn-outline-primary shadow-sm" style="border-radius: 8px; padding: 5px 10px;" title="Edit Fare"><i class="fas fa-edit"></i></a>'
                        + '</div>'
                    : '<div class="price-tag">' + fareDisplay + '</div>';

                html += '<div class="fare-item" style="flex-direction: column; align-items: stretch;">'
                    + '<div style="display: flex; justify-content: space-between; align-items: center;">'
                    + '<div class="dest-info">'
                    + '<div class="f-dest">' + origin.toUpperCase() + ' - ' + destination.toUpperCase() + '</div>'
                    + '<div class="f-origin">From: ' + origin + '</div>'
                    + '</div>'
                    + actionHtml
                    + '</div>';

                // Render discounted fares
                if (route.discounted_fares && Object.keys(route.discounted_fares).length > 0) {
                    html += '<div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #e2e8f0;">';
                    Object.keys(route.discounted_fares).forEach(function(key) {
                        var df = route.discounted_fares[key];
                        html += '<div style="display: flex; justify-content: space-between; align-items: center; padding: 4px 0;">'
                            + '<span style="font-size: 13.5px; color: #334155; font-weight: 600;">'
                            + '<i class="fas fa-tag" style="font-size: 12px; color: #64748b; margin-right: 6px;"></i>'
                            + escapeHtml(df.label) + ' <span style="color: #64748b; font-size: 12.5px; font-weight: 500;">(' + Number(df.discount_percent).toFixed(0) + '% off)</span>'
                            + '</span>'
                            + '<span style="font-size: 14px; font-weight: 700; color: #16a34a;">' + formatFare(df.amount) + '</span>'
                            + '</div>';
                    });
                    html += '</div>';
                }

                html += '</div>';
            });
        } else {
            var typeName = escapeHtml(label.replace(/\s*routes$/i, '').trim().toLowerCase());
            html += '<div class="fare-empty-state">'
                + '<i class="fas fa-route"></i>'
                + '<span>No ' + typeName + ' fares listed.</span>'
                + '</div>';
        }
        return html;
    }

    function buildFareCard(type, label, badgeClass, photoUrl, routes, color, icon) {
        var typeClass = type ? ' vehicle-type-' + type : '';
        var accent = color || '#c62828';
        var vtIcon = icon || 'fa-bus';
        var headerVisual = photoUrl
            ? '<img src="' + photoUrl + '" alt="' + label + '" style="width:36px;height:36px;object-fit:contain;" data-vt-photo="' + escapeHtml(type) + '">'
            : '<span class="fare-card-vt-icon me-2" style="background-color:' + accent + ';color:#ffffff;" data-vt-icon-box="' + escapeHtml(type) + '"><i class="fas ' + vtIcon + '" style="color:#ffffff !important;"></i></span>';
        return '<div class="fare-card' + typeClass + '" id="fare-card-' + escapeHtml(type) + '" data-vehicle-type="' + escapeHtml(type) + '" style="--fare-accent: ' + accent + ';">'
            + '<div class="card-header">'
            + '<h3>' + headerVisual + ' ' + label + '</h3>'
            + '</div>'
            + '<div class="fare-list">' + buildFareListContent(label, routes) + '</div>'
            + '</div>';
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
            + '<p style="color: #0f172a; font-size: 15px; font-weight: 600; margin-bottom: 24px; text-shadow: 0 1px 2px rgba(255, 255, 255, 0.9);">Show your valid ID to avail the discount on any route.</p>'
            + '<div class="fares-grid">';

        discounts.forEach(function(disc) {
            var meta = metaMap[disc.type] || defaultMeta;
            html += '<div class="fare-card" style="text-align: center;">'
                + '<div class="card-header">'
                + '<h3><i class="fas ' + meta.icon + '" style="color: ' + meta.label_color + '; font-size: 22px;"></i> ' + escapeHtml(disc.label) + '</h3>'
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
            var globalFp = makeContentFingerprint(data);

            var vehicleTypes = Array.isArray(data.vehicle_types) ? data.vehicle_types : [];
            var currentTypesFp = JSON.stringify(vehicleTypes.map(function(v) {
                return v.slug + ':' + (v.photo || '') + ':' + (v.icon || '') + ':' + (v.color || '');
            }));
            var newDiscFp = JSON.stringify(data.discounts || []);

            // First fetch: seed the fingerprints without re-rendering server-rendered DOM
            if (_fareFingerprint === '') {
                _fareFingerprint = globalFp;
                _vehicleTypesFingerprint = currentTypesFp;
                _discountFingerprint = newDiscFp;
                vehicleTypes.forEach(function(vehicleType) {
                    var slug = vehicleType.slug;
                    var routes = (data.routes_by_type && data.routes_by_type[slug])
                        || data[slug + '_routes']
                        || [];
                    _cardFingerprints[slug] = makeTypeRoutesFingerprint(routes);
                });
                return;
            }

            // If absolutely nothing changed, do nothing!
            if (globalFp === _fareFingerprint && currentTypesFp === _vehicleTypesFingerprint) {
                return;
            }
            _fareFingerprint = globalFp;

            var grid = document.getElementById('faresGrid');
            var typesChanged = (_vehicleTypesFingerprint !== null && _vehicleTypesFingerprint !== currentTypesFp);
            _vehicleTypesFingerprint = currentTypesFp;

            if (grid) {
                if (typesChanged || grid.children.length === 0) {
                    // Vehicle types added/removed: rebuild grid without re-triggering entrance animation
                    grid.classList.remove('initial-load');
                    grid.innerHTML = vehicleTypes.map(function(vehicleType) {
                        var routes = (data.routes_by_type && data.routes_by_type[vehicleType.slug])
                            || data[vehicleType.slug + '_routes']
                            || [];
                        _cardFingerprints[vehicleType.slug] = makeTypeRoutesFingerprint(routes);
                        var cardPhoto = vehicleType.photo || '';
                        var cardIcon = vehicleType.icon || 'fa-bus';
                        return buildFareCard(
                            vehicleType.slug,
                            escapeHtml(vehicleType.name) + ' Routes',
                            '',
                            cardPhoto,
                            routes,
                            vehicleType.color || '#c62828',
                            cardIcon
                        );
                    }).join('');
                } else {
                    // Granular update: ONLY update the specific card whose routes changed!
                    vehicleTypes.forEach(function(vehicleType) {
                        var slug = vehicleType.slug;
                        var routes = (data.routes_by_type && data.routes_by_type[slug])
                            || data[slug + '_routes']
                            || [];
                        var newFp = makeTypeRoutesFingerprint(routes);

                        if (_cardFingerprints[slug] !== undefined && _cardFingerprints[slug] === newFp) {
                            // No change for this vehicle type card — DO NOT TOUCH!
                            return;
                        }
                        _cardFingerprints[slug] = newFp;

                        var cardEl = document.getElementById('fare-card-' + slug)
                            || grid.querySelector('[data-vehicle-type="' + slug + '"]');
                        if (cardEl) {
                            var fareList = cardEl.querySelector('.fare-list');
                            if (fareList) {
                                // Smoothly update only the fare list of this card
                                fareList.innerHTML = buildFareListContent(escapeHtml(vehicleType.name) + ' Routes', routes);
                            }
                        } else {
                            var temp = document.createElement('div');
                            var cardPhoto = vehicleType.photo || '';
                            var cardIcon = vehicleType.icon || 'fa-bus';
                            temp.innerHTML = buildFareCard(
                                slug,
                                escapeHtml(vehicleType.name) + ' Routes',
                                '',
                                cardPhoto,
                                routes,
                                vehicleType.color || '#c62828',
                                cardIcon
                            );
                            if (temp.firstElementChild) {
                                grid.appendChild(temp.firstElementChild);
                            }
                        }
                    });
                }
                applyFareSearch();
            }

            // Rebuild discount section ONLY if discounts changed
            if (_discountFingerprint !== newDiscFp) {
                _discountFingerprint = newDiscFp;
                var discSection = document.getElementById('discountSection');
                if (discSection) {
                    discSection.classList.remove('initial-load');
                    if (data.discounts && data.discounts.length > 0) {
                        discSection.innerHTML = buildDiscountCards(data.discounts);
                        discSection.style.display = '';
                    } else {
                        discSection.innerHTML = '';
                        discSection.style.display = 'none';
                    }
                }
            }
        })
        .catch(function(err) { console.error('Fare fetch error:', err); })
        .finally(function() { _fareFetchPending = false; });
    }

    // WebSocket events refresh fares immediately. The adaptive poll is only a
    // recovery/safety path and pauses while the tab is hidden.
    fetchFareData();
    var _farePollTimer = null;
    function scheduleFarePoll() {
        if (_farePollTimer) clearTimeout(_farePollTimer);
        var wsConnected = window.QueueWS && window.QueueWS.isConnected && window.QueueWS.isConnected();
        var delay = wsConnected ? 120000 : 30000;
        _farePollTimer = setTimeout(function() {
            if (!document.hidden) fetchFareData();
            scheduleFarePoll();
        }, delay);
    }
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            fetchFareData();
            scheduleFarePoll();
        }
    });
    scheduleFarePoll();
    document.addEventListener('vt-colors-updated', function() { fetchFareData(); });
    document.addEventListener('pttm:ws-vehicle_type_update', function() { fetchFareData(); });
    document.addEventListener('pttm:ws-fare_update', function() { fetchFareData(); });
    document.addEventListener('pttm:ws-branding_updated', function() { fetchFareData(); });

    // Multi-tab sync via BroadcastChannel
    if (window.BroadcastChannel) {
        try {
            var _faresBc = new BroadcastChannel('pttm_queue_channel');
            _faresBc.onmessage = function(e) {
                if (e.data && (e.data.type === 'fare_update' || e.data.type === 'vehicle_type_update')) {
                    fetchFareData();
                }
            };
        } catch(e) {}
    }

    </script>
</body>

</html>
