<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminal Status - <?= esc(app_name()) ?></title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= esc(app_logo()) ?>">
    <link rel="shortcut icon" href="<?= esc(app_logo()) ?>">
    <link rel="apple-touch-icon" href="<?= esc(app_logo()) ?>">
    <!-- Font Awesome for Icons (using CDN as fallback, assuming FontAwesome is preferred for "classy" UI) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="stylesheet" href="<?= base_url('assets/css/design-system.css') ?>?v=3.2">
    <link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>?v=3.2">
    <link rel="stylesheet" href="<?= base_url('assets/css/responsive.css') ?>?v=3.2">
    <?= vehicle_type_colors_css() ?>
    <?= app_theme_css() ?>
    <style>
        :root {
            --primary: var(--primary, #C62828);
            --primary-dark: var(--primary-dark, #8E1B1B);
            --primary-soft: var(--primary-soft, rgba(198, 40, 40, 0.12));
            --on-primary: var(--on-primary, #ffffff);
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

        .lang-switch {
            padding: 8px 15px;
            border-radius: 6px;
            transition: var(--transition);
        }

        .lang-switch:hover {
            background: rgba(21, 101, 192, 0.05);
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
            z-index: 1;
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
            padding-right: 185px !important;
            font-size: 16px;
            outline: none;
            background: transparent;
            font-family: inherit;
        }

        #guest-hero-clear-btn {
            right: 155px !important;
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

        /* --- Stats Grid --- */
        .container {
            width: 90%;
            max-width: 1400px;
            margin: 25px auto 40px !important;
            position: relative;
            z-index: 20;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, max-content));
            justify-content: center;
            gap: 20px;
            margin-bottom: 40px;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(220px, max-content));
                justify-content: center;
                gap: 12px;
            }
            
            .stat-card {
                padding: 14px;
                flex-direction: row;
                text-align: left;
                border-radius: 16px;
            }
            
            .stat-icon-wrapper {
                width: 44px;
                height: 44px;
                font-size: 18px;
            }
            
            .stat-info .value {
                font-size: 24px;
            }
            
            .stat-info .label {
                font-size: 11px;
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
                padding-right: 140px !important;
                font-size: 13.5px;
                text-align: left;
                min-width: 0;
                flex: 1;
                width: auto;
            }
            #guest-hero-clear-btn {
                right: 110px !important;
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
        }

        @media (max-width: 480px) {
            .search-bar input {
                padding-right: 120px !important;
            }
            #guest-hero-clear-btn {
                right: 95px !important;
            }
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(220px, max-content));
                justify-content: center;
                gap: 8px;
            }
            
            .container {
                width: 95%;
                margin: -30px auto 30px;
            }
        }

        html body .stats-grid .stat-card,
        html body .stats-grid a.stat-card,
        html body .stats-grid button.stat-card {
            margin: 0 !important;
            margin-top: 0 !important;
            margin-bottom: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            align-self: stretch !important;
            height: 100% !important;
            min-height: 84px !important;
            box-sizing: border-box !important;
            text-decoration: none !important;
            color: inherit !important;
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

        .stat-card {
            background: white;
            padding: 18px 20px;
            border-radius: 20px;
            box-shadow: var(--shadow-md);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.03);
            height: 100%;
            min-height: 84px;
            box-sizing: border-box;
            align-self: stretch;
            margin: 0 !important;
            animation: fadeInUp 0.5s ease-out both;
        }

        .stats-grid .stat-card:nth-child(1) {
            animation-delay: 0.1s;
        }

        .stats-grid .stat-card:nth-child(2) {
            animation-delay: 0.2s;
        }

        .stats-grid .stat-card:nth-child(3) {
            animation-delay: 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-soft, rgba(30, 64, 175, 0.1));
        }

        .stat-card-link {
            text-decoration: none;
            color: inherit;
            cursor: pointer;
            margin: 0 !important;
        }

        .stat-card-link:focus,
        .stat-card-button:focus {
            outline: none;
            box-shadow: 0 0 0 3px var(--primary-soft, rgba(21, 101, 192, 0.25)), var(--shadow-md);
        }

        .stat-card .stat-icon-wrapper {
            transition: transform var(--transition);
        }

        .stat-card:hover .stat-icon-wrapper {
            transform: scale(1.15) rotate(5deg);
        }

        .stat-card-button {
            width: 100%;
            height: 100%;
            border: 1px solid rgba(0, 0, 0, 0.03);
            background: white;
            color: inherit;
            cursor: pointer;
            font: inherit;
            text-align: left;
            box-sizing: border-box;
            align-self: stretch;
            margin: 0 !important;
        }


        .route-average-modal {
            position: fixed !important;
            inset: 0 !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(15, 23, 42, 0);
            z-index: 100005 !important;
            transition: background 0.3s ease-out;
            box-sizing: border-box;
        }

        .route-average-modal.is-open {
            display: flex !important;
            background: rgba(15, 23, 42, 0.65) !important;
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
        }

        .route-average-dialog {
            width: min(580px, 94vw);
            max-height: min(80vh, 660px);
            margin: auto;
            overflow: hidden;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            display: flex;
            flex-direction: column;
            transform: scale(0.92) translateY(20px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            z-index: 1;
        }

        .route-average-modal.is-open .route-average-dialog {
            transform: scale(1) translateY(0);
            opacity: 1;
        }

        .route-average-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            padding: 24px 24px 18px;
            border-bottom: 1px solid #e2e8f0;
            background: #ffffff;
            flex-shrink: 0;
        }

        .route-average-title {
            margin: 0 0 4px;
            color: var(--primary-dark);
            font-size: 20px;
            font-weight: 800;
        }

        .route-average-subtitle {
            margin: 0;
            color: var(--text-muted);
            font-size: 13px;
            line-height: 1.4;
        }

        .route-average-close {
            width: 38px;
            height: 38px;
            border: none;
            border-radius: 12px;
            background: #f1f5f9;
            color: var(--text-main);
            cursor: pointer;
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: all 0.2s ease;
        }

        .route-average-close:hover {
            background: #fee2e2;
            color: #ef4444;
            transform: scale(1.05);
        }

        /* --- Departure Rules Modal Route Filter --- */
        .rules-filter-bar {
            padding: 12px 24px;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .rules-filter-label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--text-muted);
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .rules-filter-chips {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .rules-route-chip {
            padding: 6px 14px;
            border-radius: 20px;
            border: 1.5px solid #e2e8f0;
            background: #ffffff;
            color: #475569;
            font-size: 12.5px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            font-family: inherit;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            user-select: none;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        }

        .rules-route-chip:hover {
            border-color: var(--primary, #D62828);
            color: var(--primary, #D62828);
            background: var(--primary-soft, rgba(214, 40, 40, 0.06));
            transform: translateY(-1px);
        }

        .rules-route-chip.active {
            background: var(--primary, #C62828) !important;
            border-color: var(--primary, #C62828) !important;
            color: var(--on-primary, #ffffff) !important;
            box-shadow: 0 3px 8px var(--primary-soft, rgba(0, 0, 0, 0.25));
            transform: translateY(0);
        }

        .rules-route-chip .chip-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 19px;
            height: 19px;
            border-radius: 10px;
            background: #e2e8f0;
            color: #475569;
            font-size: 11px;
            font-weight: 800;
            padding: 0 5px;
            line-height: 1;
            transition: all 0.2s ease;
        }

        .rules-route-chip.active .chip-count {
            background: rgba(255, 255, 255, 0.25);
            color: var(--on-primary, #ffffff);
        }

        .route-average-body {
            padding: 20px 24px 24px;
            overflow-y: auto;
            flex: 1;
            background: #ffffff;
        }

        .route-average-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .route-average-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            transition: all 0.2s ease;
        }

        .route-average-item.is-active-rule {
            border-color: #22c55e;
            background: #f0fdf4;
            box-shadow: 0 2px 8px rgba(34, 197, 94, 0.12);
        }

        .badge-active-now {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #dcfce7;
            color: #15803d;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 3px 8px;
            border-radius: 20px;
            border: 1px solid #bbf7d0;
        }

        .active-pulse-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #16a34a;
            box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.3);
            animation: activeDotPulse 1.8s infinite;
        }

        @keyframes activeDotPulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.4); opacity: 0.6; }
        }

        .route-average-route {
            font-size: 15px;
            font-weight: 800;
            color: var(--primary-dark);
        }

        .route-average-count {
            margin-top: 3px;
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .route-average-time {
            color: var(--success-dark);
            font-size: 18px;
            font-weight: 800;
            white-space: nowrap;
        }

        .route-average-empty {
            padding: 22px;
            border-radius: 14px;
            background: #f8fafc;
            color: var(--text-muted);
            text-align: center;
            font-weight: 600;
        }

        .stat-icon-wrapper {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .si-blue {
            background: #e3f2fd;
            color: #1565c0;
        }

        .si-gold {
            background: #fff8e1;
            color: #ffa000;
        }

        .si-green {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .si-purple {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .stat-info {
            min-width: 0;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .stat-info .value {
            font-size: 28px;
            font-weight: 800;
            color: var(--text-main);
            display: block;
            line-height: 1.2;
        }

        .stat-info .label {
            font-size: 12px;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            min-height: 2.6em;
            display: flex;
            align-items: center;
            line-height: 1.3;
        }

        /* --- Section Titles --- */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            animation: fadeInUp 0.5s ease-out both;
            animation-delay: 0.15s;
        }

        .section-title {
            font-size: 24px;
            font-weight: 800;
            color: #000000;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .live-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 800;
            color: var(--success);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: #e8f5e9;
            padding: 6px 14px;
            border-radius: 12px;
        }

        .dot-pulse {
            width: 8px;
            height: 8px;
            background: var(--success);
            border-radius: 50%;
            animation: pulse-dot 1.5s infinite;
        }

        @keyframes pulse-dot {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(76, 175, 80, 0.7);
            }

            70% {
                transform: scale(1);
                box-shadow: 0 0 0 10px rgba(76, 175, 80, 0);
            }

            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(76, 175, 80, 0);
            }
        }

        /* --- Main Content Layout --- */
        .main-layout {
            display: block;
        }

        /* --- Quick Filter Bar --- */
        .quick-filter-bar {
            background: white;
            border-radius: 16px;
            padding: 16px 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-sm);
            border: 1px solid #edf2f7;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            animation: fadeInUp 0.5s ease-out both;
            animation-delay: 0.2s;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .filter-label {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--text-muted);
            white-space: nowrap;
        }

        .filter-chip {
            padding: 7px 16px;
            border-radius: 20px;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            font-family: inherit;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            user-select: none;
        }

        .filter-chip:hover {
            border-color: var(--primary, #D62828);
            color: var(--primary, #D62828);
            background: var(--primary-soft, rgba(214, 40, 40, 0.06));
        }

        .filter-chip.active {
            background: var(--primary, #C62828);
            border-color: var(--primary, #C62828);
            color: var(--on-primary, white);
            box-shadow: 0 3px 8px var(--primary-soft, rgba(0, 0, 0, 0.25));
        }

        .filter-chip .chip-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            height: 20px;
            border-radius: 10px;
            background: #e2e8f0;
            color: #475569;
            font-size: 11px;
            font-weight: 800;
            padding: 0 6px;
            line-height: 1;
            transition: all 0.2s ease;
            pointer-events: none;
        }

        .filter-chip.active .chip-count {
            background: rgba(255, 255, 255, 0.25);
            color: var(--on-primary, #ffffff);
        }

        .filter-chip:hover:not(.active) .chip-count {
            background: var(--primary-soft, rgba(214, 40, 40, 0.12));
            color: var(--primary, #D62828);
        }

        .no-results-message {
            padding: 40px 20px;
            text-align: center;
            color: var(--text-muted);
            background: white;
            border-radius: 16px;
            border: 1px dashed #e2e8f0;
            font-size: 15px;
            font-weight: 500;
        }

        /* --- Empty Queue Card --- */
        .empty-queue-card {
            background: white;
            border-radius: 20px;
            padding: 55px 30px;
            text-align: center !important;
            box-shadow: var(--shadow-md);
            border: 1px dashed #cbd5e1;
            margin: 15px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            box-sizing: border-box;
            animation: fadeInUp 0.5s ease-out both;
            animation-delay: 0.25s;
        }

        .empty-queue-card .empty-icon-wrapper {
            width: 72px;
            height: 72px;
            background: rgba(30, 64, 175, 0.06);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            color: var(--primary);
            margin: 0 auto 16px;
        }

        .empty-queue-card h3 {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 8px;
            text-align: center !important;
        }

        .empty-queue-card p {
            font-size: 15px;
            color: var(--text-muted);
            margin: 0 auto;
            max-width: 520px;
            text-align: center !important;
            line-height: 1.55;
        }

        /* --- Fare Badge inside queue card --- */
        .fare-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #e8f5e9;
            color: #2e7d32;
            font-size: 13px;
            font-weight: 800;
            padding: 4px 11px;
            border-radius: 20px;
            margin-top: 6px;
            letter-spacing: 0.2px;
        }

        .fare-badge i {
            font-size: 11px;
        }

        /* --- Modern Queue Card Design --- */
        .queue-container {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .queue-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 18px 24px;
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 16px -2px rgba(15, 23, 42, 0.05), 0 2px 6px -1px rgba(15, 23, 42, 0.03);
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
            position: relative;
            overflow: hidden;
        }

        /* Smooth departure board transitions: cards only animate when entering or departing */
        .queue-card.card-enter {
            animation: cardEnter 0.35s ease-out both;
        }
        .queue-card.card-leave {
            animation: cardLeave 0.3s ease-in both;
            pointer-events: none;
        }
        @keyframes cardEnter {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes cardLeave {
            from { opacity: 1; transform: translateY(0); }
            to   { opacity: 0; transform: translateY(-12px); }
        }

        .queue-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 5px;
            background: var(--card-stripe-color, #1565c0);
            transition: background 0.25s ease;
        }

        .queue-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px -4px rgba(15, 23, 42, 0.1), 0 4px 10px -2px rgba(15, 23, 42, 0.05);
            border-color: #cbd5e1;
        }

        /* Left: Vehicle Identity */
        .queue-card-left {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .vehicle-thumb-box {
            position: relative;
            width: 74px;
            height: 68px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            border-radius: 16px;
            border: 1.5px solid #e2e8f0;
            padding: 6px;
            transition: transform 0.2s ease;
        }

        .queue-card:hover .vehicle-thumb-box {
            transform: scale(1.04);
        }

        .vehicle-thumb-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.12));
        }

        .queue-badge-pill {
            position: absolute;
            top: -6px;
            right: -6px;
            color: #ffffff;
            font-size: 12px;
            font-weight: 800;
            padding: 3px 8px;
            min-width: 26px;
            text-align: center;
            border-radius: 20px;
            border: 2px solid #ffffff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.25);
            line-height: 1;
        }

        /* Center: Body Information */
        .queue-card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-width: 0;
        }

        .queue-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }

        .operator-plate-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .operator-badge,
        .plate-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #f1f5f9;
            color: #1e293b;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            padding: 3px 10px;
            font-size: 13.5px;
            font-weight: 700;
        }

        .operator-badge .operator-val {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
        }

        .plate-badge .plate-val {
            font-family: 'Courier New', monospace;
            font-size: 14.5px;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: #0f172a;
        }

        .queue-card-route-driver {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .route-pill-badge {
            display: inline-flex;
            align-items: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 5px 12px;
            font-size: 13.5px;
            gap: 6px;
        }

        .route-origin {
            color: #64748b;
            font-weight: 600;
        }

        .route-arrow {
            color: #94a3b8;
            font-size: 12px;
        }

        .route-dest {
            color: #0f172a;
            font-weight: 800;
        }

        .driver-info-pill {
            display: inline-flex;
            align-items: center;
            font-size: 13.5px;
            color: #334155;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 5px 12px;
            gap: 4px;
        }

        .queue-card-bottom-row {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-top: 2px;
        }

        .fare-badge-modern {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
            border-radius: 10px;
            padding: 4px 12px;
            font-size: 13.5px;
            font-weight: 700;
        }

        .passenger-status-block {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
            position: relative;
        }

        .passenger-pop-anchor {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 20px;
            flex-shrink: 0;
        }

        .passenger-delta-badge {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
            user-select: none;
            font-size: 11.5px;
            font-weight: 800;
            padding: 2px 6px;
            border-radius: 12px;
            line-height: 1.1;
            display: inline-flex;
            align-items: center;
            gap: 2px;
            white-space: nowrap;
            z-index: 30;
            animation: ghostFloatUp 1.8s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }

        .passenger-delta-badge.pop-increment {
            color: #15803d;
            background: #dcfce7;
            border: 1.5px solid #86efac;
            box-shadow: 0 4px 14px rgba(34, 197, 94, 0.4);
        }

        .passenger-delta-badge.pop-decrement {
            color: #b91c1c;
            background: #fee2e2;
            border: 1.5px solid #fca5a5;
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.4);
        }

        @keyframes ghostFloatUp {
            0% {
                opacity: 0;
                transform: translate3d(-50%, 8px, 0) scale(0.65);
                filter: blur(2px);
            }
            15% {
                opacity: 1;
                transform: translate3d(-50%, -4px, 0) scale(1.15);
                filter: blur(0);
            }
            32% {
                transform: translate3d(-50%, -10px, 0) scale(1);
            }
            70% {
                opacity: 0.95;
                transform: translate3d(-50%, -24px, 0) scale(1);
                filter: blur(0);
            }
            100% {
                opacity: 0;
                transform: translate3d(-50%, -40px, 0) scale(0.85);
                filter: blur(1.5px);
            }
        }

        .passenger-count-row {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13.5px;
            color: #334155;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .badge-full-tag {
            background: #ef4444;
            color: #ffffff;
            font-size: 10px;
            font-weight: 800;
            padding: 2px 6px;
            border-radius: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            flex-shrink: 0;
        }

        .progress-modern {
            height: 11px !important;
            background: #e2e8f0 !important;
            border-radius: 12px !important;
            flex: 1 !important;
            min-width: 80px !important;
            max-width: 180px !important;
            width: auto !important;
            overflow: hidden !important;
            margin: 0 !important;
            display: flex !important;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.08) !important;
        }

        .progress-modern .progress-bar,
        .progress-bar {
            height: 100%;
            border-radius: 12px;
            transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.4s ease;
        }

        /* Right: Departure Ticket Container */
        .queue-card-departure {
            flex-shrink: 0;
            width: 175px;
            background: #f8fafc;
            color: #1e293b;
            border: 1.5px solid #e2e8f0;
            border-radius: 16px;
            padding: 14px 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 8px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
        }

        .dep-status-box {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
        }

        .dep-time-col {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 4px;
            width: 100%;
        }

        .dep-header-label {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
            line-height: 1;
        }

        .dep-time-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }

        .dep-time-highlight {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
            line-height: 1.1;
        }

        .dep-status-tag {
            font-size: 12px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            line-height: 1.2;
            white-space: nowrap;
        }

        .dep-status-tag i {
            font-size: 11.5px;
            line-height: 1;
        }

        .dep-status-tag.status-waiting {
            background: #fffbeb;
            color: #b45309;
            border: 1.5px solid #fde68a;
        }

        .dep-status-tag.status-boarding {
            background: #f0fdf4;
            color: #15803d;
            border: 1.5px solid #86efac;
            box-shadow: 0 0 0 1px rgba(34, 197, 94, 0.15);
        }

        .dep-status-tag.status-departed {
            background: #f1f5f9;
            color: #64748b;
            border: 1.5px solid #cbd5e1;
        }

        .dep-status-tag.status-canceled {
            background: #fef2f2;
            color: #b91c1c;
            border: 1.5px solid #fecaca;
        }

        .countdown-timer {
            font-size: 12px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 8px;
            display: inline-block;
            letter-spacing: 0.3px;
        }

        .countdown-timer.cd-plenty,
        .countdown-timer.cd-green { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .countdown-timer.cd-yellow { background: #fef9c3; color: #a16207; border: 1px solid #fef08a; }
        .countdown-timer.cd-soon,
        .countdown-timer.cd-orange { background: #fff3e0; color: #e65100; border: 1px solid #ffe0b2; }
        .countdown-timer.cd-imminent,
        .countdown-timer.cd-red { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; animation: cdPulse 1s infinite; }
        .countdown-timer.cd-passed,
        .countdown-timer.cd-overdue { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; animation: cdPulse 1.2s infinite; }

        @keyframes cdPulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        /* --- Right Sidebar (Fares) --- */
        .sidebar-section {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: var(--shadow-sm);
        }

        .sidebar-title {
            font-size: 18px;
            font-weight: 800;
            color: var(--primary-dark);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .fare-search {
            margin-bottom: 20px;
        }

        .fare-search input {
            width: 100%;
            padding: 12px 15px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            outline: none;
            font-size: 13px;
            transition: var(--transition);
        }

        .fare-search input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(21, 101, 192, 0.1);
        }

        .fare-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-height: 500px;
            overflow-y: auto;
            padding-right: 5px;
        }

        .fare-item {
            padding: 12px;
            border-radius: 12px;
            background: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #e2e8f0;
            transition: var(--transition);
        }

        .fare-item:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        .f-dest {
            font-weight: 700;
            font-size: 14px;
            color: var(--primary-dark);
        }

        .f-type {
            font-size: 12px;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
        }

        .f-price {
            font-weight: 800;
            color: var(--success-dark);
            font-size: 16px;
        }

        /* Custom Scrollbar for Fare List */
        .fare-list::-webkit-scrollbar {
            width: 4px;
        }

        .fare-list::-webkit-scrollbar-track {
            background: transparent;
        }

        .fare-list::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 10px;
        }

        /* --- Responsive Queries --- */
        @media (max-width: 640px) {
            .quick-filter-bar {
                padding: 12px 15px;
            }
            .filter-chip {
                padding: 6px 12px;
                font-size: 12px;
            }
            .filter-chip .chip-count {
                font-size: 10px;
                min-width: 18px;
                height: 18px;
                padding: 0 4px;
            }
        }

        @media (max-width: 768px) {
            header {
                padding: 10px 5%;
                min-height: 65px;
                border-bottom: 1px solid #eee;
            }

            .logo {
                width: 40px;
                height: 40px;
                border-radius: 8px;
            }

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
        }

        @media (max-width: 991px) {
            .queue-card {
                flex-direction: column;
                align-items: stretch;
                gap: 14px;
                padding: 16px;
            }

            .queue-card-left {
                flex-direction: row;
                justify-content: flex-start;
                align-items: center;
                gap: 12px;
                padding-bottom: 10px;
                border-bottom: 1px solid #f1f5f9;
            }

            .vehicle-thumb-box {
                width: 60px;
                height: 54px;
            }

            .queue-card-departure {
                width: 100%;
                flex-direction: row;
                flex-wrap: nowrap;
                justify-content: space-between;
                align-items: center;
                padding: 12px 16px;
                gap: 12px;
            }

            .dep-status-box {
                width: auto;
                justify-content: flex-start;
            }

            .dep-time-col {
                width: auto;
                align-items: flex-end;
                text-align: right;
                gap: 3px;
            }

            .dep-time-group {
                align-items: flex-end;
                gap: 3px;
            }

            .dep-time-highlight {
                font-size: 18px;
            }
        }

        @media (max-width: 768px) {
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

            .search-bar {
                padding: 6px 8px;
                flex-direction: row;
                border-radius: 50px;
                align-items: center;
                gap: 0;
            }

            .search-bar input {
                padding: 10px 14px;
                font-size: 13.5px;
                text-align: left;
                min-width: 0;
                flex: 1;
                width: auto;
            }

            .search-bar button {
                padding: 0 18px;
                height: 42px;
                border-radius: 50px;
                font-size: 12.5px;
                font-weight: 700;
                white-space: nowrap;
                flex-shrink: 0;
                width: auto;
            }

            .container {
                width: 92% !important;
                margin: 20px auto 30px !important;
            }

            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(220px, max-content));
                justify-content: center;
                gap: 12px;
            }

            .stat-card {
                padding: 12px;
                flex-direction: row;
                text-align: left;
            }

            .route-average-modal {
                padding: 14px;
                align-items: flex-end;
            }

            .route-average-dialog {
                max-height: 86vh;
                border-radius: 18px;
            }

            .route-average-header,
            .rules-filter-bar,
            .route-average-body {
                padding-left: 18px;
                padding-right: 18px;
            }

            .route-average-item {
                align-items: flex-start;
                flex-direction: column;
                gap: 8px;
            }
        }

        @media (max-width: 480px) {
            .rules-filter-bar {
                padding: 10px 14px;
                gap: 8px;
            }

            .rules-route-chip {
                padding: 5px 11px;
                font-size: 12px;
            }

            .queue-card {
                padding: 12px;
                gap: 12px;
                border-radius: 14px;
            }

            .operator-badge,
            .plate-badge {
                font-size: 12.5px;
                padding: 2px 7px;
            }

            .route-pill-badge, .driver-info-pill {
                font-size: 12px;
                padding: 4px 9px;
            }

            .queue-card-departure {
                padding: 10px 14px;
                gap: 8px;
            }

            .dep-status-tag {
                font-size: 11px;
                padding: 4px 10px;
                gap: 5px;
            }

            .dep-status-tag i {
                font-size: 10.5px;
            }

            .dep-header-label {
                font-size: 10px;
            }

            .dep-time-highlight {
                font-size: 16px;
            }

            .countdown-timer {
                font-size: 11px;
                padding: 2px 8px;
            }

            .passenger-status-block {
                min-width: 0;
                gap: 6px;
            }
            .passenger-count-row {
                font-size: 12.5px;
                gap: 4px;
            }
            .badge-full-tag {
                font-size: 9.5px;
                padding: 1.5px 5px;
            }
            .progress-modern {
                height: 11px !important;
                flex: 1 !important;
                width: auto !important;
                min-width: 80px !important;
                max-width: 180px !important;
            }
            .passenger-pop-anchor {
                width: 28px;
                height: 18px;
            }
            .passenger-delta-badge {
                font-size: 10.5px;
                padding: 1.5px 5px;
            }
        }

        @media (max-width: 420px) {
            .hero h2 {
                font-size: 20px;
            }
            .hero p {
                font-size: 12.5px !important;
            }
            .stat-info .value {
                font-size: 16px;
            }
            .queue-pos {
                width: 40px;
                height: 40px;
            }
            .queue-pos span:last-child {
                font-size: 16px;
            }
            .queue-card {
                padding: 10px !important;
                gap: 10px !important;
                border-radius: 12px !important;
            }
            .operator-badge,
            .plate-badge {
                font-size: 11.5px !important;
                padding: 2px 6px !important;
            }
            .route-pill-badge, .driver-info-pill {
                font-size: 11px !important;
                padding: 3px 7px !important;
            }
            .queue-card-departure {
                padding: 8px 10px !important;
                gap: 6px !important;
            }
            .dep-status-tag {
                font-size: 10.5px !important;
                padding: 3px 8px !important;
                gap: 4px !important;
            }
            .dep-status-tag i {
                font-size: 10px !important;
            }
            .dep-header-label {
                font-size: 9.5px !important;
            }
            .dep-time-highlight {
                font-size: 15px !important;
            }
            .countdown-timer {
                font-size: 10.5px !important;
                padding: 2px 6px !important;
            }
            .passenger-status-block {
                min-width: 0 !important;
                gap: 5px !important;
            }
            .passenger-count-row {
                font-size: 11.5px !important;
                gap: 3px !important;
            }
            .badge-full-tag {
                font-size: 9px !important;
                padding: 1px 4px !important;
            }
            .progress-modern {
                height: 11px !important;
                flex: 1 !important;
                width: auto !important;
                min-width: 80px !important;
                max-width: 180px !important;
            }
            .passenger-pop-anchor {
                width: 26px !important;
            }
            .passenger-delta-badge {
                font-size: 10px !important;
                padding: 1px 4px !important;
            }
        }

        /* ------- Support Widget Styles ------- */
        .support-widget-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-top: 14px;
        }

        .support-widget {
            background: linear-gradient(135deg, #1a2a4a 0%, #243350 100%);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 20px 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            color: white;
            text-decoration: none;
        }

        .support-widget:hover {
            border-color: var(--primary, #C62828);
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        }

        .support-widget .sw-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 4px;
        }

        .support-widget .sw-icon.sw-blue {
            background: rgba(37, 99, 235, .25);
            color: #60a5fa;
        }

        .support-widget .sw-icon.sw-red {
            background: rgba(220, 38, 38, .25);
            color: #f87171;
        }

        .support-widget .sw-icon.sw-green {
            background: rgba(34, 197, 94, .25);
            color: #4ade80;
        }

        .support-widget .sw-icon.sw-yellow {
            background: rgba(234, 179, 8, .25);
            color: #facc15;
        }

        .support-widget strong {
            font-size: 14px;
            font-weight: 700;
            display: block;
        }

        .support-widget span {
            font-size: 11px;
            opacity: .65;
        }


        @media (max-width: 530px) {
            .support-widget-grid {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }

            .support-widget {
                padding: 15px 10px;
            }

            .support-widget .sw-icon {
                width: 40px;
                height: 40px;
                font-size: 18px;
            }

            .support-widget strong {
                font-size: 13px;
            }
        }

    </style>
</head>

<body class="guest-theme">

    <?= view('templates/guest_header', [
        'announcements' => $announcements ?? [],
    ]) ?>

    <!-- Hero Section -->
    <section class="hero">
        <h2>Live Terminal Status</h2>
        <p>Monitor arrivals, departures, and current queue positions seamlessly from your device.</p>

        <div class="search-container">
            <form action="<?= base_url('search') ?>" method="get" class="search-bar" style="position: relative;">
                <input type="text" name="q" id="guest-hero-search-input" placeholder="Search by Plate Number, Destination, or Driver..." required autocomplete="off" oninput="toggleGuestHeroClear(this.value)">
                <button type="button" class="guest-clear-search-btn" id="guest-hero-clear-btn" onclick="clearGuestHeroSearch()" style="display: none !important;" title="Clear search">
                    <i class="fas fa-times-circle"></i>
                </button>
                <button type="submit">TRACK STATUS</button>
            </form>
        </div>
    </section>

    <div class="container">
        <!-- Stats Summary -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon-wrapper si-blue"><i class="fas fa-car-side"></i></div>
                <div class="stat-info">
                    <span class="value" id="count-queued"><?= count($active_queue) ?></span>
                    <span class="label">Vehicles in Queue</span>
                </div>
            </div>
            <a href="<?= base_url('schedules') ?>" class="stat-card stat-card-link" aria-label="View schedules">
                <div class="stat-icon-wrapper si-gold"><i class="fas fa-route"></i></div>
                <div class="stat-info">
                    <span class="value" id="count-routes"><?= count(array_unique(array_column($routes ?? [], 'destination'))) ?></span>
                    <span class="label">Operating Routes</span>
                </div>
            </a>
            <button type="button" class="stat-card stat-card-button" id="routeAverageCard" onclick="openRouteAverageModal()" aria-haspopup="dialog" aria-controls="routeAverageModal">
                <div class="stat-icon-wrapper si-purple"><i class="fas fa-clock"></i></div>
                <div class="stat-info">
                    <span class="value">View</span>
                    <span class="label">Departure Rules</span>
                </div>
            </button>
        </div>

        <?php
        $fareMap = [];
        foreach (($routes ?? []) as $r) {
            $fkey = strtolower(trim($r['origin'])) . '|' . strtolower(trim($r['destination'])) . '|' . strtolower(trim($r['vehicle_type']));
            $fareMap[$fkey] = $r['fare'];
        }
        $uniqueDestinations = [];
        $destCounts = [];
        foreach (($active_queue ?? []) as $qi) {
            $d = $qi['destination'] ?? '';
            if ($d) {
                if (!in_array($d, $uniqueDestinations)) {
                    $uniqueDestinations[] = $d;
                }
                $destCounts[$d] = ($destCounts[$d] ?? 0) + 1;
            }
        }
        $totalQueueCount = count($active_queue ?? []);
        ?>

        <div class="main-layout">
            <!-- Terminal Queue (full width) -->
            <section class="queue-section">
                <div class="section-header">
                    <h3 class="section-title"><i class="fas fa-stream"></i> Terminal Queue</h3>
                    <div class="live-indicator">
                        <div class="dot-pulse"></div>
                        Live Monitor
                    </div>
                </div>

                <!-- Quick Filters -->
                <div class="quick-filter-bar" id="quickFilterBar">
                    <div class="filter-group" id="filterDestGroup">
                        <button class="filter-chip active" data-filter="all" data-type="all">
                            All Routes <span class="chip-count"><?= $totalQueueCount ?></span>
                        </button>
                        <?php foreach ($uniqueDestinations as $dest): ?>
                        <button class="filter-chip" data-filter="<?= esc(strtolower($dest)) ?>" data-type="destination">
                            <?= esc(strtoupper($dest)) ?> <span class="chip-count"><?= (int)($destCounts[$dest] ?? 0) ?></span>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div id="queueNoResults" class="no-results-message" style="display:none;">
                    <i class="fas fa-search" style="font-size:28px;margin-bottom:10px;opacity:0.4;display:block;"></i>
                    No vehicles match the selected filter.
                </div>

                <div class="queue-container" id="queueList">
                    <?php if (!empty($active_queue)): ?>
                        <?php foreach ($active_queue as $item): ?>
                            <?php
                            $vType = $item['vehicle_type'] ?? '';
                            $hasCustomPhoto = !empty($item['vehicle_photo'] ?? $item['photo'] ?? null);
                            $photoUrl = vehicle_resolved_photo($item, $vType);
                            $vColor = vehicle_type_color($vType);
                            $vContrast = contrast_text_color($vColor);
                            $vIsLight = ($vContrast === '#0f172a');
                            $fkey = strtolower(trim($item['origin'])) . '|' . strtolower(trim($item['destination'])) . '|' . strtolower(trim($vType));
                            $cardFare = $fareMap[$fkey] ?? null;
                            ?>
                            <div class="queue-card queue-card-<?= esc($vType) ?>"
                                 style="--card-stripe-color: <?= esc($vColor) ?>; border-left: 5px solid <?= esc($vColor) ?> !important;"
                                 data-vehicle-type="<?= esc($vType) ?>"
                                 data-destination="<?= esc(strtolower($item['destination'])) ?>"
                                 data-queue-id="<?= esc($item['id'] ?? '') ?>"
                                 data-plate="<?= esc($item['plate_number'] ?? '') ?>">
                                
                                <!-- Left: Vehicle Identity Block -->
                                <div class="queue-card-left">
                                    <div class="vehicle-thumb-box vehicle-type-<?= esc($vType) ?>" style="background: <?= esc($vColor) ?>12 !important; border-color: <?= esc($vColor) ?>35 !important;">
                                        <?php if (!empty($photoUrl)): ?>
                                            <img src="<?= esc($photoUrl) ?>" alt="<?= esc(vehicle_type_label($vType)) ?>" class="vehicle-thumb-img <?= $hasCustomPhoto ? 'vehicle-custom-photo' : '' ?>" <?= $hasCustomPhoto ? 'data-vehicle-custom-photo="true"' : ('data-vt-photo="' . esc(vehicle_type_key($vType)) . '"') ?>>
                                        <?php else: ?>
                                            <i class="fas <?= esc(vehicle_type_icon($vType)) ?>" style="font-size: 28px; color: <?= esc($vColor) ?>;"></i>
                                        <?php endif; ?>
                                        <span class="queue-badge-pill" style="background: <?= esc($vColor) ?>; color: <?= esc($vContrast) ?>; <?= $vIsLight ? 'border: 2px solid #cbd5e1;' : '' ?>">
                                            #<?= $item['position'] ?>
                                        </span>
                                    </div>
                                    <?= vehicle_type_badge($vType) ?>
                                </div>

                                <!-- Center: Primary Vehicle & Route Information -->
                                <div class="queue-card-body">
                                    <div class="queue-card-header">
                                        <div class="operator-plate-wrap">
                                            <?php $opName = !empty($item['operator_name']) ? $item['operator_name'] : ($item['driver_name'] ?? 'N/A'); ?>
                                            <span class="operator-badge">
                                                <span class="text-muted fw-semibold">Operator:</span> <span class="operator-val"><?= esc($opName) ?></span>
                                            </span>
                                            <span class="plate-badge">
                                                <span class="text-muted fw-semibold">Plate Number:</span> <span class="plate-val"><?= esc($item['plate_number']) ?></span>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="queue-card-route-driver">
                                        <div class="route-pill-badge">
                                            <i class="fas fa-map-marker-alt text-danger"></i>
                                            <span class="route-origin"><?= strtoupper(esc($item['origin'])) ?></span>
                                            <i class="fas fa-arrow-right route-arrow"></i>
                                            <span class="route-dest"><?= strtoupper(esc($item['destination'])) ?></span>
                                        </div>
                                        <div class="driver-info-pill">
                                            <i class="fas fa-user-tie text-primary"></i>
                                            <span class="text-muted">Driver:</span> <strong><?= esc($item['driver_name'] ?? 'N/A') ?></strong>
                                        </div>
                                    </div>

                                    <div class="queue-card-bottom-row">
                                        <?php if ($cardFare !== null): ?>
                                        <div class="fare-badge-modern">
                                            <i class="fas fa-tag text-success"></i> ₱<?= number_format($cardFare, 0) ?> Fare
                                        </div>
                                        <?php endif; ?>

                                        <div class="passenger-status-block">
                                            <div class="passenger-count-row">
                                                <i class="fas fa-users text-primary"></i>
                                                <span class="passenger-count-text"><strong class="passenger-count-num <?= passenger_color_class((int)$item['current_passengers'], (int)$item['capacity']) ?>"><?= $item['current_passengers'] ?></strong> / <?= $item['capacity'] ?> Onboard</span>
                                                <?php if ((int) $item['current_passengers'] >= (int) $item['capacity']): ?>
                                                    <span class="badge-full-tag">FULL</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="progress progress-modern">
                                                <?php $percent = min(100, ($item['current_passengers'] / max(1, $item['capacity'])) * 100); ?>
                                                <?php
                                                    if ($percent >= 90) {
                                                        $barColor = '#ef4444'; // Red
                                                    } elseif ($percent >= 70) {
                                                        $barColor = '#f97316'; // Orange
                                                    } elseif ($percent >= 50) {
                                                        $barColor = '#eab308'; // Yellow
                                                    } else {
                                                        $barColor = '#22c55e'; // Green
                                                    }
                                                ?>
                                                <div class="progress-bar" style="width: <?= $percent ?>%; height: 100%; background: <?= $barColor ?>;"></div>
                                            </div>
                                            <div class="passenger-pop-anchor" data-pop-id="<?= esc($item['id'] ?? '') ?>" data-pop-plate="<?= esc($item['plate_number'] ?? '') ?>"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right: Departure Time Ticket / Card -->
                                <div class="queue-card-departure">
                                    <div class="dep-status-box">
                                        <div class="dep-status-tag <?= $item['status'] === 'boarding' ? 'status-boarding' : 'status-waiting' ?>">
                                            <?php if ($item['status'] == 'boarding'): ?>
                                                <i class="fas fa-clock"></i> Boarding
                                            <?php else: ?>
                                                <i class="fas fa-hourglass-half"></i> Waiting
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="dep-time-col">
                                        <div class="dep-header-label">EST. DEPARTURE</div>
                                        <div class="dep-time-group">
                                            <div class="dep-time-highlight">
                                                <?php
                                                if ($item['estimated_departure']):
                                                    echo date('h:i A', strtotime($item['estimated_departure']));
                                                else:
                                                    echo 'Waiting';
                                                endif;
                                                ?>
                                            </div>
                                            <?php if ($item['status'] === 'boarding' && !empty($item['estimated_departure'])): ?>
                                                <div class="countdown-timer" data-departure="<?= date('c', strtotime($item['estimated_departure'])) ?>"></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-queue-card">
                            <div class="empty-icon-wrapper">
                                <i class="fas fa-bus-alt"></i>
                            </div>
                            <h3>No Vehicles Currently in Queue</h3>
                            <p>There are no active vehicles waiting or boarding right now. Please check back shortly for live updates.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

        </div>
    </div>

    <!-- Departure Time Rules Modal (root-level for proper viewport centering & stacking) -->
    <div class="route-average-modal modal" id="routeAverageModal" aria-hidden="true">
        <div class="route-average-dialog" role="dialog" aria-modal="true" aria-labelledby="routeAverageTitle">
            <div class="route-average-header">
                <div>
                    <h3 class="route-average-title" id="routeAverageTitle">Departure Time Rules</h3>
                    <p class="route-average-subtitle">Scheduled departure intervals configured for <?= esc(app_name()) ?> Terminal.</p>
                </div>
                <button type="button" class="route-average-close" id="routeAverageClose" onclick="closeRouteAverageModal()" aria-label="Close departure rules modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <?php
                // Extract destination counts and general rule counts
                $rulesList = $departure_rules ?? [];
                $totalRulesCount = count($rulesList);
                $destMap = [];
                $generalCount = 0;

                foreach ($rulesList as $r) {
                    $dest = !empty($r['route_destination']) ? strtoupper(trim($r['route_destination'])) : null;
                    if (empty($dest) && !empty($r['route_scope'])) {
                        if (strpos($r['route_scope'], '→') !== false) {
                            $parts = explode('→', $r['route_scope']);
                            $dest = strtoupper(trim(end($parts)));
                        } elseif (strpos($r['route_scope'], '->') !== false) {
                            $parts = explode('->', $r['route_scope']);
                            $dest = strtoupper(trim(end($parts)));
                        }
                    }

                    if (!empty($dest) && strtolower($dest) !== 'all routes' && strtolower($dest) !== 'all') {
                        $destMap[$dest] = ($destMap[$dest] ?? 0) + 1;
                    } else {
                        $generalCount++;
                    }
                }
            ?>

            <!-- Route Filter Bar (only shown when specific route rules exist) -->
            <?php if (!empty($destMap)): ?>
            <div class="rules-filter-bar">
                <span class="rules-filter-label">
                    <i class="fas fa-filter"></i> Route:
                </span>
                <div class="rules-filter-chips" id="rulesRouteFilterGroup">
                    <button type="button" class="rules-route-chip active" data-route="all">
                        <i class="fas fa-route"></i> All Routes
                        <span class="chip-count"><?= $totalRulesCount ?></span>
                    </button>
                    <?php foreach ($destMap as $destName => $cnt): ?>
                        <button type="button" class="rules-route-chip" data-route="<?= esc(strtolower($destName), 'attr') ?>">
                            <i class="fas fa-map-marker-alt"></i> <?= esc($destName) ?>
                            <span class="chip-count"><?= $cnt ?></span>
                        </button>
                    <?php endforeach; ?>
                    <?php if ($generalCount > 0): ?>
                        <button type="button" class="rules-route-chip" data-route="general">
                            <i class="fas fa-sliders-h"></i> Terminal Default
                            <span class="chip-count"><?= $generalCount ?></span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="route-average-body">
                <div class="route-average-list" id="routeAverageList">
                    <?php if (!empty($rulesList)): ?>
                        <?php foreach ($rulesList as $rule): ?>
                            <?php
                                $rDest = !empty($rule['route_destination']) ? strtoupper(trim($rule['route_destination'])) : null;
                                if (empty($rDest) && !empty($rule['route_scope'])) {
                                    if (strpos($rule['route_scope'], '→') !== false) {
                                        $parts = explode('→', $rule['route_scope']);
                                        $rDest = strtoupper(trim(end($parts)));
                                    } elseif (strpos($rule['route_scope'], '->') !== false) {
                                        $parts = explode('->', $rule['route_scope']);
                                        $rDest = strtoupper(trim(end($parts)));
                                    }
                                }
                                $destSlug = (!empty($rDest) && strtolower($rDest) !== 'all routes') ? strtolower($rDest) : 'general';
                            ?>
                            <div class="route-average-item <?= !empty($rule['is_active_now']) ? 'is-active-rule' : '' ?>" data-rule-dest="<?= esc($destSlug, 'attr') ?>" data-rule-route="<?= esc($rule['route_scope'] ?? 'All Routes', 'attr') ?>">
                                <div style="min-width: 0; flex: 1;">
                                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                        <div class="route-average-route">
                                            <i class="fas fa-clock" style="color: var(--primary); margin-right: 6px; font-size: 12px;"></i><?= esc($rule['time_range']) ?>
                                        </div>
                                        <?php if (!empty($rule['is_active_now'])): ?>
                                            <span class="badge-active-now">
                                                <span class="active-pulse-dot"></span> Active Now
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="route-average-count">
                                        <?= esc($rule['label']) ?> &bull; <?= esc($rule['route_scope']) ?>
                                    </div>
                                </div>
                                <div class="route-average-time"><?= esc($rule['interval_label']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="route-average-empty">No departure rules configured.</div>
                    <?php endif; ?>
                </div>

                <!-- No results message (hidden by default) -->
                <div class="route-average-empty" id="rulesNoResults" style="display: none; margin-top: 10px;">
                    <i class="fas fa-search" style="font-size: 20px; display: block; margin-bottom: 6px; opacity: 0.5;"></i>
                    No departure rules match the selected route.
                </div>
            </div>
        </div>
    </div>

    <script>
    // Global modal openers for Departure Rules
    window.openRouteAverageModal = function() {
        var modal = document.getElementById('routeAverageModal');
        if (!modal) return;
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
        document.body.classList.add('modal-open');
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    window.closeRouteAverageModal = function() {
        var modal = document.getElementById('routeAverageModal');
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
    };
    var openRouteAverageModal = window.openRouteAverageModal;
    var closeRouteAverageModal = window.closeRouteAverageModal;

    // Departure Rules route filter
    var activeRulesFilter = 'all';

    function applyDepartureRulesFilter() {
        var items = document.querySelectorAll('#routeAverageList .route-average-item');
        var visible = 0;
        items.forEach(function(item) {
            var itemDest = item.getAttribute('data-rule-dest') || 'general';
            var show = false;

            if (activeRulesFilter === 'all') {
                show = true;
            } else if (activeRulesFilter === 'general') {
                show = (itemDest === 'general');
            } else {
                show = (itemDest === activeRulesFilter);
            }

            if (show) {
                item.style.display = '';
                visible++;
            } else {
                item.style.display = 'none';
            }
        });

        var noRes = document.getElementById('rulesNoResults');
        if (noRes) {
            noRes.style.display = (visible === 0 && items.length > 0) ? '' : 'none';
        }
    }

    (function() {
        var filterGroup = document.getElementById('rulesRouteFilterGroup');
        if (!filterGroup) return;

        filterGroup.addEventListener('click', function(e) {
            var chip = e.target.closest('.rules-route-chip');
            if (!chip) return;

            // Update active chip styles
            filterGroup.querySelectorAll('.rules-route-chip').forEach(function(c) {
                c.classList.remove('active');
            });
            chip.classList.add('active');

            activeRulesFilter = chip.dataset.route || 'all';
            applyDepartureRulesFilter();
        });
    })();
    </script>

    <?= $this->include('templates/guestfooter') ?>

    <!-- WebSocket is the fast path; polling remains the fallback. -->
    <script src="<?= base_url('js/queue-sync.js?v=20260906') ?>"></script>
    <script>
        var _fetchPending = false;
        var _fetchQueued = false;
        var baseUrl = '<?= base_url() ?>';
        var departureRules = <?= json_encode($departure_rules ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        var fareMap = <?= json_encode($fareMap ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        var vehicleTypeMeta = <?= json_encode(function_exists('get_db_vehicle_types') ? get_db_vehicle_types() : [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

        // --- Active filter state ---
        var activeFilterType = 'all';
        var activeFilterValue = 'all';

        function applyQueueFilter() {
            var cards = document.querySelectorAll('#queueList .queue-card');
            var visible = 0;
            cards.forEach(function (card) {
                var show = false;
                if (activeFilterType === 'all') {
                    show = true;
                } else if (activeFilterType === 'vehicle') {
                    show = (card.dataset.vehicleType || '') === activeFilterValue;
                } else if (activeFilterType === 'destination') {
                    show = (card.dataset.destination || '') === activeFilterValue;
                }
                if (show) {
                    if (card.style.display === 'none') {
                        card.style.display = '';
                        card.style.animation = 'none';
                        card.offsetHeight; /* trigger reflow */
                        card.style.animation = '';
                    } else {
                        card.style.display = '';
                    }
                    visible++;
                } else {
                    card.style.display = 'none';
                }
            });
            var noRes = document.getElementById('queueNoResults');
            if (noRes) noRes.style.display = (visible === 0 && cards.length > 0) ? '' : 'none';
        }

        function initFilterChips() {
            document.querySelectorAll('#filterDestGroup .filter-chip').forEach(function (chip) {
                chip.addEventListener('click', function () {
                    document.querySelectorAll('#filterDestGroup .filter-chip').forEach(function (c) { c.classList.remove('active'); });
                    this.classList.add('active');
                    activeFilterType = this.dataset.type;
                    activeFilterValue = this.dataset.filter;
                    applyQueueFilter();
                });
            });
        }

        function getFareBadgeHtml(origin, destination, vType) {
            var key = origin.toLowerCase().trim() + '|' + destination.toLowerCase().trim() + '|' + vType.toLowerCase().trim();
            var fare = fareMap[key];
            if (fare === undefined || fare === null) return '';
            return '<div class="fare-badge"><i class="fas fa-tag"></i> &#8369;' + Number(fare).toLocaleString('en-PH', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + ' Fare</div>';
        }

        // Smooth Scrolling for Anchor Links
        document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                var targetId = this.getAttribute('href').substring(1);
                var targetElement = document.getElementById(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({ behavior: 'smooth' });
                    var menu = document.getElementById('navMenu');
                    if (menu.classList.contains('open')) {
                        toggleMenu();
                    }
                }
            });
        });

        // ObserveItems stub kept for backwards compatibility with dynamic polling
        function observeItems() {
            // Handled via pure CSS @keyframes fadeInUp for immediate, smooth cascading entrance
        }

        // Vehicle Types Metadata (Colors, Icons, Images, Labels) for dynamic card rendering
        var vehicleTypeMeta = <?= json_encode(array_reduce(
            array_keys(get_db_vehicle_types() + ['van'=>1,'jeepney'=>1,'minibus'=>1,'bus'=>1,'taxi'=>1,'car'=>1,'tricecle'=>1,'tricycle'=>1,'motorcycle'=>1]),
            function($acc, $k) {
                $color = vehicle_type_color($k);
                $acc[$k] = [
                    'color'    => $color,
                    'contrast' => contrast_text_color($color),
                    'photo'    => vehicle_type_photo($k),
                    'icon'     => vehicle_type_icon($k),
                    'label'    => vehicle_type_label($k),
                ];
                return $acc;
            },
            []
        )) ?>;

        // Fingerprint cache to avoid flickering DOM rewrites when data hasn't changed
        var _lastQueueFingerprint = '';
        var _lastDepartureRulesFingerprint = makeFingerprint(departureRules);

        function makeFingerprint(arr) {
            return JSON.stringify(arr);
        }

        function renderDepartureRules(rows) {
            var list = document.getElementById('routeAverageList');
            if (!list) return;

            list.innerHTML = '';
            if (!rows || rows.length === 0) {
                var empty = document.createElement('div');
                empty.className = 'route-average-empty';
                empty.textContent = 'No departure rules configured.';
                list.appendChild(empty);
                var noRes = document.getElementById('rulesNoResults');
                if (noRes) noRes.style.display = 'none';
                return;
            }

            rows.forEach(function (rule) {
                var item = document.createElement('div');
                item.className = 'route-average-item' + (rule.is_active_now ? ' is-active-rule' : '');

                var dest = rule.route_destination;
                if (!dest && rule.route_scope) {
                    if (rule.route_scope.indexOf('→') !== -1) {
                        var parts = rule.route_scope.split('→');
                        dest = parts[parts.length - 1].trim();
                    } else if (rule.route_scope.indexOf('->') !== -1) {
                        var parts = rule.route_scope.split('->');
                        dest = parts[parts.length - 1].trim();
                    }
                }
                var destSlug = (dest && dest.toLowerCase() !== 'all routes') ? dest.toLowerCase() : 'general';
                item.setAttribute('data-rule-dest', destSlug);
                item.setAttribute('data-rule-route', rule.route_scope || 'All Routes');

                var leftWrap = document.createElement('div');
                leftWrap.style.minWidth = '0';
                leftWrap.style.flex = '1';

                var timeRow = document.createElement('div');
                timeRow.style.display = 'flex';
                timeRow.style.alignItems = 'center';
                timeRow.style.gap = '8px';
                timeRow.style.flexWrap = 'wrap';

                var time = document.createElement('div');
                time.className = 'route-average-route';
                time.innerHTML = '<i class="fas fa-clock" style="color:var(--primary);margin-right:6px;font-size:12px;"></i>' + (rule.time_range || (rule.time_from_formatted + ' – ' + rule.time_to_formatted));

                timeRow.appendChild(time);

                if (rule.is_active_now) {
                    var activeBadge = document.createElement('span');
                    activeBadge.className = 'badge-active-now';
                    activeBadge.innerHTML = '<span class="active-pulse-dot"></span> Active Now';
                    timeRow.appendChild(activeBadge);
                }

                var labelScope = document.createElement('div');
                labelScope.className = 'route-average-count';
                labelScope.textContent = (rule.label || 'Standard') + ' \u2022 ' + (rule.route_scope || 'All Routes');

                leftWrap.appendChild(timeRow);
                leftWrap.appendChild(labelScope);

                var rightWrap = document.createElement('div');
                rightWrap.className = 'route-average-time';
                rightWrap.textContent = rule.interval_label || ('Every ' + (rule.wait_minutes || 30) + ' min');

                item.appendChild(leftWrap);
                item.appendChild(rightWrap);
                list.appendChild(item);
            });

            // Sync chip counts if filter group exists
            var filterGroup = document.getElementById('rulesRouteFilterGroup');
            if (filterGroup && rows && rows.length > 0) {
                var counts = { all: rows.length, general: 0 };
                rows.forEach(function(r) {
                    var d = r.route_destination;
                    if (!d && r.route_scope) {
                        if (r.route_scope.indexOf('→') !== -1) {
                            var p = r.route_scope.split('→');
                            d = p[p.length - 1].trim();
                        } else if (r.route_scope.indexOf('->') !== -1) {
                            var p = r.route_scope.split('->');
                            d = p[p.length - 1].trim();
                        }
                    }
                    var s = (d && d.toLowerCase() !== 'all routes') ? d.toLowerCase() : 'general';
                    counts[s] = (counts[s] || 0) + 1;
                });
                filterGroup.querySelectorAll('.rules-route-chip').forEach(function(chip) {
                    var routeKey = chip.dataset.route;
                    var badge = chip.querySelector('.chip-count');
                    if (badge && counts[routeKey] !== undefined) {
                        badge.textContent = counts[routeKey];
                    }
                });
            }

            // Re-apply active filter to maintain user's view during real-time updates
            if (typeof applyDepartureRulesFilter === 'function') {
                applyDepartureRulesFilter();
            }
        }


        function getContrastTextColor(hex) {
            if (!hex) return '#ffffff';
            hex = hex.replace('#', '');
            if (hex.length === 3) {
                hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
            }
            if (hex.length !== 6) return '#ffffff';
            var r = parseInt(hex.substring(0, 2), 16);
            var g = parseInt(hex.substring(2, 4), 16);
            var b = parseInt(hex.substring(4, 6), 16);
            var yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
            return (yiq >= 150) ? '#0f172a' : '#ffffff';
        }

        // Passenger state tracking for floating ghost popups (+1 / -1)
        var _prevPassengerMap = {};

        function initPassengerTracking() {
            document.querySelectorAll('#queueList .queue-card').forEach(function(card) {
                var key = card.getAttribute('data-queue-id') || card.getAttribute('data-plate');
                var countRow = card.querySelector('.passenger-count-row strong');
                if (key && countRow) {
                    _prevPassengerMap[key] = parseInt(countRow.innerText, 10) || 0;
                }
            });
        }
        initPassengerTracking();

        function triggerPassengerPop(anchorEl, diff) {
            if (!anchorEl || diff === 0) return;
            var badge = document.createElement('div');
            var isPositive = diff > 0;
            badge.className = 'passenger-delta-badge ' + (isPositive ? 'pop-increment' : 'pop-decrement');
            var sign = isPositive ? '+' : '';
            badge.innerHTML = sign + diff;
            anchorEl.appendChild(badge);
            setTimeout(function() {
                if (badge && badge.parentNode) {
                    badge.parentNode.removeChild(badge);
                }
            }, 1900);
        }

        function buildQueueCardHtml(item, isEnter) {
            var percent = Math.min(100, (Number(item.current_passengers) / Math.max(1, Number(item.capacity))) * 100);
            var barColor = percent >= 90 ? '#ef4444' : (percent >= 70 ? '#f97316' : (percent >= 50 ? '#eab308' : '#22c55e'));
            var isFull = Number(item.current_passengers) >= Number(item.capacity);
            var fullBadge = isFull ? '<span class="badge-full-tag">FULL</span>' : '';
            var statusClass = (item.status === 'boarding') ? 'status-boarding' : 'status-waiting';
            var statusIcon = (item.status === 'boarding') ? '<i class="fas fa-clock"></i> Boarding' : '<i class="fas fa-hourglass-half"></i> Waiting';

            var vType = (item.vehicle_type || '').toLowerCase();
            var vMeta = (vehicleTypeMeta && vehicleTypeMeta[vType]) ? vehicleTypeMeta[vType] : {
                color: '#1565c0',
                contrast: '#ffffff',
                photo: null,
                icon: 'fa-bus',
                label: vType ? (vType.charAt(0).toUpperCase() + vType.slice(1)) : 'Van'
            };
            var posColor = vMeta.color || '#1565c0';
            var hasCustom = !!(item.has_custom_photo || item.vehicle_photo || item.photo);
            var photoUrl = (item.photo_url || (item.vehicle_photo ? ('/uploads/vehicles/' + item.vehicle_photo) : (item.photo ? ('/uploads/vehicles/' + item.photo) : ''))) || vMeta.photo || '';
            var vTypeLabel = vMeta.label || (vType ? (vType.charAt(0).toUpperCase() + vType.slice(1)) : 'Van');
            var posContrast = vMeta.contrast || (typeof getContrastTextColor === 'function' ? getContrastTextColor(posColor) : '#ffffff');
            var isLight = (posContrast === '#0f172a');
            var pillBorder = isLight ? 'border: 2px solid #cbd5e1;' : 'border: 2px solid #ffffff;';
            var badgeStyle = isLight 
                ? 'background: #f1f5f9 !important; background-color: #f1f5f9 !important; color: #0f172a !important; border: 1.5px solid #cbd5e1 !important; font-weight: 800;'
                : 'background: ' + posColor + '18 !important; background-color: ' + posColor + '18 !important; color: ' + posColor + ' !important; border: 1.5px solid ' + posColor + '44 !important; font-weight: 800;';

            var fareBadge = getFareBadgeHtml(item.origin || '', item.destination || '', vType);
            var opName = item.operator_name || item.driver_name || 'N/A';
            var estDepIso = item.estimated_departure ? item.estimated_departure.replace(' ', 'T') : null;
            var extraClass = isEnter ? ' card-enter' : '';

            var iconOrImg = photoUrl
                ? ('<img src="' + photoUrl + '" alt="' + vTypeLabel + '" class="vehicle-thumb-img' + (hasCustom ? ' vehicle-custom-photo' : '') + '" ' + (hasCustom ? 'data-vehicle-custom-photo="true"' : ('data-vt-photo="' + vType + '"')) + '>')
                : ('<i class="fas ' + (vMeta.icon || 'fa-bus') + '" style="font-size: 28px; color: ' + posColor + ';"></i>');

            return '<div class="queue-card queue-card-' + vType + extraClass + '" style="--card-stripe-color:' + posColor + '; border-left: 5px solid ' + posColor + ' !important;" data-vehicle-type="' + vType + '" data-destination="' + (item.destination || '').toLowerCase() + '" data-queue-id="' + item.id + '" data-plate="' + item.plate_number + '">'
                + '<div class="queue-card-left">'
                + '<div class="vehicle-thumb-box vehicle-type-' + vType + '" style="background:' + posColor + '12 !important; border-color:' + posColor + '35 !important;">'
                + iconOrImg
                + '<span class="queue-badge-pill" style="background:' + posColor + '; color:' + posContrast + '; ' + pillBorder + '">#' + item.position + '</span>'
                + '</div>'
                + '<span class="vehicle-type-chip vehicle-type-' + vType + '" style="' + badgeStyle + '">' + vTypeLabel + '</span>'
                + '</div>'
                + '<div class="queue-card-body">'
                + '<div class="queue-card-header">'
                + '<div class="operator-plate-wrap">'
                + '<span class="operator-badge"><span class="text-muted fw-semibold">Operator:</span> <span class="operator-val">' + opName + '</span></span>'
                + '<span class="plate-badge"><span class="text-muted fw-semibold">Plate Number:</span> <span class="plate-val">' + item.plate_number + '</span></span>'
                + '</div>'
                + '</div>'
                + '<div class="queue-card-route-driver">'
                + '<div class="route-pill-badge">'
                + '<i class="fas fa-map-marker-alt text-danger"></i>'
                + '<span class="route-origin">' + (item.origin || '').toUpperCase() + '</span>'
                + '<i class="fas fa-arrow-right route-arrow"></i>'
                + '<span class="route-dest">' + (item.destination || '').toUpperCase() + '</span>'
                + '</div>'
                + '<div class="driver-info-pill">'
                + '<i class="fas fa-user-tie text-primary"></i>'
                + '<span class="text-muted">Driver:</span> <strong>' + (item.driver_name || 'N/A') + '</strong>'
                + '</div>'
                + '</div>'
                + '<div class="queue-card-bottom-row">'
                + fareBadge
                + '<div class="passenger-status-block">'
                + '<div class="passenger-count-row">'
                + '<i class="fas fa-users text-primary"></i>'
                + '<span class="passenger-count-text"><strong class="passenger-count-num ' + (typeof getPassengerColorClass === 'function' ? getPassengerColorClass(item.current_passengers, item.capacity) : (percent >= 90 ? 'passenger-color-red' : (percent >= 70 ? 'passenger-color-orange' : (percent >= 50 ? 'passenger-color-yellow' : 'passenger-color-green')))) + '">' + item.current_passengers + '</strong> / ' + item.capacity + ' Onboard</span>'
                + fullBadge
                + '</div>'
                + '<div class="progress progress-modern">'
                + '<div class="progress-bar" style="width:' + percent + '%; height: 100%; background:' + barColor + ';"></div>'
                + '</div>'
                + '<div class="passenger-pop-anchor" data-pop-id="' + item.id + '" data-pop-plate="' + item.plate_number + '"></div>'
                + '</div>'
                + '</div>'
                + '</div>'
                + '<div class="queue-card-departure">'
                + '<div class="dep-status-box">'
                + '<div class="dep-status-tag ' + statusClass + '">' + statusIcon + '</div>'
                + '</div>'
                + '<div class="dep-time-col">'
                + '<div class="dep-header-label">EST. DEPARTURE</div>'
                + '<div class="dep-time-group">'
                + '<div class="dep-time-highlight">' + (item.estimated_departure_formatted || 'TBA') + '</div>'
                + (item.status === 'boarding' && estDepIso ? '<div class="countdown-timer" data-departure="' + estDepIso + '"></div>' : '')
                + '</div>'
                + '</div>'
                + '</div>'
                + '</div>';
        }

        function updateQueueCardInPlace(card, item, passengerDeltas) {
            var percent = Math.min(100, (Number(item.current_passengers) / Math.max(1, Number(item.capacity))) * 100);
            var barColor = percent >= 90 ? '#ef4444' : (percent >= 70 ? '#f97316' : (percent >= 50 ? '#eab308' : '#22c55e'));
            var isFull = Number(item.current_passengers) >= Number(item.capacity);
            var pColorClass = typeof getPassengerColorClass === 'function' ? getPassengerColorClass(item.current_passengers, item.capacity) : (percent >= 90 ? 'passenger-color-red' : (percent >= 70 ? 'passenger-color-orange' : (percent >= 50 ? 'passenger-color-yellow' : 'passenger-color-green')));

            // 1. Update passenger count text without rebuilding container
            var countText = card.querySelector('.passenger-count-text');
            var newCountHtml = '<strong class="passenger-count-num ' + pColorClass + '">' + item.current_passengers + '</strong> / ' + item.capacity + ' Onboard';
            if (countText) {
                if (countText.innerHTML !== newCountHtml) {
                    countText.innerHTML = newCountHtml;
                }
            } else {
                var countSpan = card.querySelector('.passenger-count-row span');
                if (countSpan && countSpan.innerHTML !== newCountHtml) {
                    countSpan.innerHTML = newCountHtml;
                }
            }

            // 2. Update FULL tag
            var fullBadge = card.querySelector('.badge-full-tag');
            var countRowContainer = card.querySelector('.passenger-count-row');
            if (isFull && !fullBadge && countRowContainer) {
                var tag = document.createElement('span');
                tag.className = 'badge-full-tag';
                tag.textContent = 'FULL';
                countRowContainer.appendChild(tag);
            } else if (!isFull && fullBadge) {
                fullBadge.remove();
            }

            // 3. Smoothly update progress bar width and color
            var pBar = card.querySelector('.progress-modern .progress-bar, .progress-bar');
            if (pBar) {
                pBar.style.width = percent + '%';
                pBar.style.background = barColor;
            }

            // 4. Update status tag (waiting vs boarding)
            var statusClass = (item.status === 'boarding') ? 'status-boarding' : 'status-waiting';
            var statusIcon = (item.status === 'boarding') ? '<i class="fas fa-clock"></i> Boarding' : '<i class="fas fa-hourglass-half"></i> Waiting';
            var depTag = card.querySelector('.dep-status-tag');
            if (depTag && !depTag.classList.contains(statusClass)) {
                depTag.className = 'dep-status-tag ' + statusClass;
                depTag.innerHTML = statusIcon;
            }

            // 5. Update estimated departure text
            var depHighlight = card.querySelector('.dep-time-highlight');
            var depText = item.estimated_departure_formatted || 'TBA';
            if (depHighlight && depHighlight.textContent !== depText) {
                depHighlight.textContent = depText;
            }

            // 6. Update countdown timer
            var estDepIso = item.estimated_departure ? item.estimated_departure.replace(' ', 'T') : null;
            var cdTimer = card.querySelector('.countdown-timer');
            if (item.status === 'boarding' && estDepIso) {
                if (!cdTimer) {
                    cdTimer = document.createElement('div');
                    cdTimer.className = 'countdown-timer';
                    var depTimeGroup = card.querySelector('.dep-time-group');
                    if (depTimeGroup) depTimeGroup.appendChild(cdTimer);
                }
                cdTimer.dataset.departure = estDepIso;
            } else if (cdTimer && item.status !== 'boarding') {
                cdTimer.remove();
            }

            // 7. Update position badge
            var posPill = card.querySelector('.queue-badge-pill');
            var expectedPos = '#' + item.position;
            if (posPill && posPill.textContent !== expectedPos) {
                posPill.textContent = expectedPos;
            }

            // 8. Update operator & driver if changed
            var opVal = card.querySelector('.operator-val');
            var expectedOp = item.operator_name || item.driver_name || 'N/A';
            if (opVal && opVal.textContent !== expectedOp) opVal.textContent = expectedOp;

            var driverVal = card.querySelector('.driver-info-pill strong');
            var expectedDriver = item.driver_name || 'N/A';
            if (driverVal && driverVal.textContent !== expectedDriver) driverVal.textContent = expectedDriver;

            // 9. Update dataset attributes for filtering
            card.dataset.destination = (item.destination || '').toLowerCase();
            card.dataset.vehicleType = (item.vehicle_type || '').toLowerCase();

            // 10. Update vehicle thumbnail / custom photo / fallback icon in place
            var thumbBox = card.querySelector('.vehicle-thumb-box');
            if (thumbBox) {
                var vType = (item.vehicle_type || '').toLowerCase();
                var vMeta = (vehicleTypeMeta && vehicleTypeMeta[vType]) ? vehicleTypeMeta[vType] : {
                    color: '#1565c0',
                    contrast: '#ffffff',
                    photo: null,
                    icon: 'fa-bus',
                    label: vType ? (vType.charAt(0).toUpperCase() + vType.slice(1)) : 'Van'
                };
                var posColor = vMeta.color || '#1565c0';
                var hasCustom = !!(item.has_custom_photo || item.vehicle_photo || item.photo);
                var photoUrl = (item.photo_url || (item.vehicle_photo ? ('/uploads/vehicles/' + item.vehicle_photo) : (item.photo ? ('/uploads/vehicles/' + item.photo) : ''))) || vMeta.photo || '';
                var vTypeLabel = vMeta.label || (vType ? (vType.charAt(0).toUpperCase() + vType.slice(1)) : 'Van');

                var currentImg = thumbBox.querySelector('img');
                var currentI = thumbBox.querySelector('i');
                var badgePill = thumbBox.querySelector('.queue-badge-pill');

                if (photoUrl) {
                    if (currentImg) {
                        var curSrc = currentImg.getAttribute('src') || '';
                        if (curSrc !== photoUrl && currentImg.src !== photoUrl) {
                            currentImg.src = photoUrl;
                        }
                        if (hasCustom) {
                            currentImg.classList.add('vehicle-custom-photo');
                            currentImg.setAttribute('data-vehicle-custom-photo', 'true');
                            currentImg.removeAttribute('data-vt-photo');
                        } else {
                            currentImg.classList.remove('vehicle-custom-photo');
                            currentImg.removeAttribute('data-vehicle-custom-photo');
                            currentImg.setAttribute('data-vt-photo', vType);
                        }
                        currentImg.alt = vTypeLabel;
                        if (currentI) currentI.remove();
                    } else {
                        var newImg = document.createElement('img');
                        newImg.src = photoUrl;
                        newImg.alt = vTypeLabel;
                        newImg.className = 'vehicle-thumb-img' + (hasCustom ? ' vehicle-custom-photo' : '');
                        if (hasCustom) {
                            newImg.setAttribute('data-vehicle-custom-photo', 'true');
                        } else {
                            newImg.setAttribute('data-vt-photo', vType);
                        }
                        if (currentI) {
                            thumbBox.replaceChild(newImg, currentI);
                        } else if (badgePill) {
                            thumbBox.insertBefore(newImg, badgePill);
                        } else {
                            thumbBox.appendChild(newImg);
                        }
                    }
                } else {
                    // No photo (removed and no default type photo): replace with FontAwesome icon
                    if (currentImg) {
                        var newI = document.createElement('i');
                        newI.className = 'fas ' + (vMeta.icon || 'fa-bus');
                        newI.style.fontSize = '28px';
                        newI.style.color = posColor;
                        thumbBox.replaceChild(newI, currentImg);
                    } else if (currentI) {
                        currentI.className = 'fas ' + (vMeta.icon || 'fa-bus');
                        currentI.style.color = posColor;
                    }
                }
            }
        }

        // Fetch status for real-time sync (debounced to avoid server/browser spam)
        var _fetchTimer = null;
        function scheduleFetchStatus(delay) {
            delay = typeof delay === 'number' ? delay : 300;
            if (_fetchTimer) clearTimeout(_fetchTimer);
            _fetchTimer = setTimeout(function() {
                _fetchTimer = null;
                fetchStatus();
            }, delay);
        }

        function fetchStatus() {
            if (_fetchPending) {
                _fetchQueued = true;
                return;
            }
            _fetchPending = true;

            fetch('<?= base_url('status') ?>?_=' + Date.now())
                .then(function (response) {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(function (data) {
                    // Update stats (lightweight text-only, no flicker)
                    var countQueued = document.getElementById('count-queued');
                    var countRoutes = document.getElementById('count-routes');
                    if (countQueued) countQueued.innerText = data.active_queue.length;
                    if (countRoutes && data.routes) {
                        var uniqueDests = [];
                        data.routes.forEach(function(r) {
                            if (r.destination && uniqueDests.indexOf(r.destination) === -1) uniqueDests.push(r.destination);
                        });
                        countRoutes.innerText = uniqueDests.length;
                    }

                    if (data.departure_rules) {
                        var departureRulesFP = makeFingerprint(data.departure_rules);
                        if (departureRulesFP !== _lastDepartureRulesFingerprint) {
                            _lastDepartureRulesFingerprint = departureRulesFP;
                            departureRules = data.departure_rules;
                            renderDepartureRules(departureRules);
                        }
                    }

                    if (data.db_vehicle_types) {
                        Object.keys(data.db_vehicle_types).forEach(function (k) {
                            var vt = data.db_vehicle_types[k];
                            if (vt) {
                                vehicleTypeMeta[k] = vehicleTypeMeta[k] || {};
                                if (vt.color) {
                                    var color = vt.color;
                                    vehicleTypeMeta[k].color = color;
                                    vehicleTypeMeta[k].contrast = getContrastTextColor(color);
                                    try {
                                        document.documentElement.style.setProperty('--vehicle-' + k, color);
                                        document.documentElement.style.setProperty('--vehicle-' + k + '-soft', color + '18');
                                    } catch (e) { /* ignore */ }
                                }
                                if (vt.name) vehicleTypeMeta[k].label = vt.name;
                                if (vt.icon) vehicleTypeMeta[k].icon = vt.icon;
                                if (vt.photo) {
                                    vehicleTypeMeta[k].photo = vt.photo;
                                    document.querySelectorAll('img[data-vt-photo="' + k + '"], .vehicle-type-' + k + ' img').forEach(function(img) {
                                        if (img.hasAttribute('data-vehicle-custom-photo') || img.classList.contains('vehicle-custom-photo')) return;
                                        img.src = vt.photo;
                                    });
                                    document.querySelectorAll('[data-vehicle-type="' + k + '"] .vehicle-thumb-box').forEach(function(thumbBox) {
                                        var customImg = thumbBox.querySelector('img[data-vehicle-custom-photo="true"], img.vehicle-custom-photo');
                                        if (customImg) return;
                                        var oldI = thumbBox.querySelector('i');
                                        if (oldI && !thumbBox.querySelector('img')) {
                                            var img = document.createElement('img');
                                            img.src = vt.photo;
                                            img.alt = vehicleTypeMeta[k].label || k;
                                            img.className = 'vehicle-thumb-img';
                                            img.setAttribute('data-vt-photo', k);
                                            thumbBox.replaceChild(img, oldI);
                                        }
                                    });
                                } else {
                                    vehicleTypeMeta[k].photo = null;
                                    document.querySelectorAll('[data-vehicle-type="' + k + '"] .vehicle-thumb-box').forEach(function(thumbBox) {
                                        var customImg = thumbBox.querySelector('img[data-vehicle-custom-photo="true"], img.vehicle-custom-photo');
                                        if (customImg) return;
                                        var oldImg = thumbBox.querySelector('img');
                                        if (oldImg) {
                                            var iEl = document.createElement('i');
                                            iEl.className = 'fas ' + (vehicleTypeMeta[k].icon || 'fa-bus');
                                            iEl.style.fontSize = '28px';
                                            iEl.style.color = vehicleTypeMeta[k].color || 'var(--vehicle-' + k + ')';
                                            thumbBox.replaceChild(iEl, oldImg);
                                        }
                                    });
                                }
                            }
                        });
                    }

                    // Track passenger changes for ghost float animation
                    var passengerDeltas = {};
                    if (data.active_queue) {
                        data.active_queue.forEach(function(item) {
                            var key = item.id ? String(item.id) : (item.plate_number || '');
                            var curr = parseInt(item.current_passengers, 10) || 0;
                            if (key && _prevPassengerMap[key] !== undefined && _prevPassengerMap[key] !== curr) {
                                var diff = curr - _prevPassengerMap[key];
                                if (diff !== 0) {
                                    passengerDeltas[key] = diff;
                                }
                            }
                            _prevPassengerMap[key] = curr;
                        });
                    }

                    // Only process Queue DOM if data exists
                    var queueFP = makeFingerprint([data.active_queue, data.db_vehicle_types]);
                    if (queueFP !== _lastQueueFingerprint) {
                        _lastQueueFingerprint = queueFP;
                        var queueList = document.getElementById('queueList');
                        if (queueList) {
                            if (!data.active_queue || data.active_queue.length === 0) {
                                var emptyHtml = '<div class="empty-queue-card"><div class="empty-icon-wrapper"><i class="fas fa-bus-alt"></i></div><h3>No Vehicles Currently in Queue</h3><p>There are no active vehicles waiting or boarding right now. Please check back shortly for live updates.</p></div>';
                                if (!queueList.querySelector('.empty-queue-card')) {
                                    queueList.innerHTML = emptyHtml;
                                }
                            } else {
                                // Remove empty state card if present
                                var emptyCard = queueList.querySelector('.empty-queue-card');
                                if (emptyCard) emptyCard.remove();

                                // Index existing DOM cards by ID or plate
                                var existingCardMap = {};
                                queueList.querySelectorAll('.queue-card').forEach(function(card) {
                                    var key = card.getAttribute('data-queue-id') || card.getAttribute('data-plate');
                                    if (key) existingCardMap[key] = card;
                                });

                                var activeKeys = {};
                                data.active_queue.forEach(function(item) {
                                    var key = item.id ? String(item.id) : (item.plate_number || '');
                                    activeKeys[key] = true;

                                    var existingCard = existingCardMap[key];
                                    if (existingCard) {
                                        // If card was transitioning out and restored, clear card-leave
                                        existingCard.classList.remove('card-leave');
                                        // IN-PLACE TARGETED UPDATE: Never destroy card, smoothly update numbers & progress
                                        updateQueueCardInPlace(existingCard, item, passengerDeltas);
                                        // Re-appending moves/preserves order without re-rendering or losing state
                                        queueList.appendChild(existingCard);
                                    } else {
                                        // Newly arriving vehicle: build element with smooth card-enter animation
                                        var tempDiv = document.createElement('div');
                                        tempDiv.innerHTML = buildQueueCardHtml(item, true);
                                        var newCard = tempDiv.firstElementChild;
                                        if (newCard) {
                                            queueList.appendChild(newCard);
                                            setTimeout(function() {
                                                if (newCard) newCard.classList.remove('card-enter');
                                            }, 400);
                                        }
                                    }
                                });

                                // Departed or canceled vehicles: smoothly animate removal
                                Object.keys(existingCardMap).forEach(function(key) {
                                    if (!activeKeys[key]) {
                                        var cardToRemove = existingCardMap[key];
                                        if (cardToRemove && !cardToRemove.classList.contains('card-leave')) {
                                            cardToRemove.classList.add('card-leave');
                                            setTimeout(function() {
                                                if (cardToRemove && cardToRemove.parentNode && cardToRemove.classList.contains('card-leave')) {
                                                    cardToRemove.parentNode.removeChild(cardToRemove);
                                                }
                                            }, 320);
                                        }
                                    }
                                });
                            }

                            // Trigger ghost float animation for passenger updates
                            Object.keys(passengerDeltas).forEach(function(key) {
                                var diff = passengerDeltas[key];
                                var anchor = document.querySelector('.passenger-pop-anchor[data-pop-id="' + key + '"], .passenger-pop-anchor[data-pop-plate="' + key + '"]');
                                if (anchor) {
                                    triggerPassengerPop(anchor, diff);
                                }
                            });

                            // Destination quick-filter chips: update counts without losing button state
                            var destGroup = document.getElementById('filterDestGroup');
                            if (destGroup && data.active_queue) {
                                var dests = [];
                                var destCounts = {};
                                data.active_queue.forEach(function (it) {
                                    if (it.destination) {
                                        if (dests.indexOf(it.destination) === -1) dests.push(it.destination);
                                        destCounts[it.destination] = (destCounts[it.destination] || 0) + 1;
                                    }
                                });
                                var prevActive = activeFilterValue;
                                var totalCount = data.active_queue.length;
                                var html = '<button class="filter-chip' + (prevActive === 'all' ? ' active' : '') + '" data-filter="all" data-type="all">All Routes <span class="chip-count">' + totalCount + '</span></button>';
                                dests.forEach(function (d) {
                                    var isActive = prevActive === d.toLowerCase();
                                    var count = destCounts[d] || 0;
                                    html += '<button class="filter-chip' + (isActive ? ' active' : '') + '" data-filter="' + d.toLowerCase() + '" data-type="destination">' + d.toUpperCase() + ' <span class="chip-count">' + count + '</span></button>';
                                });
                                if (destGroup.innerHTML !== html) {
                                    destGroup.innerHTML = html;
                                    initFilterChips();
                                }
                            }

                            applyQueueFilter();
                            observeItems();
                        }
                    }
                })
                .catch(function (error) {
                    console.error('Error fetching status:', error);
                })
                .finally(function () {
                    _fetchPending = false;
                    if (_fetchQueued) {
                        _fetchQueued = false;
                        fetchStatus();
                    }
                });
        }

        // Wire up quick filter chips
        initFilterChips();

        var routeAverageCard = document.getElementById('routeAverageCard');
        var routeAverageModal = document.getElementById('routeAverageModal');
        var routeAverageClose = document.getElementById('routeAverageClose');

        if (routeAverageCard) {
            routeAverageCard.addEventListener('click', openRouteAverageModal);
        }

        if (routeAverageClose) {
            routeAverageClose.addEventListener('click', closeRouteAverageModal);
        }

        if (routeAverageModal) {
            routeAverageModal.addEventListener('click', function (event) {
                if (event.target === routeAverageModal) {
                    closeRouteAverageModal();
                }
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeRouteAverageModal();
            }
        });

        function handleRealtimePassengerChange(data) {
            if (!data) return;
            var pId = data.id;
            var pCount = parseInt(data.new_count, 10);
            var pCap = parseInt(data.capacity, 10);
            if (!pId || isNaN(pCount)) return;

            var card = document.querySelector('.queue-card[data-queue-id="' + pId + '"]');
            if (card) {
                var cap = pCap;
                if (!cap || isNaN(cap)) {
                    var capText = card.querySelector('.passenger-count-text');
                    if (capText) {
                        var parts = capText.textContent.split('/');
                        if (parts.length > 1) cap = parseInt(parts[1], 10) || 14;
                    }
                }
                var itemStub = {
                    id: pId,
                    current_passengers: pCount,
                    capacity: cap || 14,
                    status: card.classList.contains('queue-card-boarding') ? 'boarding' : 'waiting'
                };
                updateQueueCardInPlace(card, itemStub, {});
            } else {
                fetchStatus();
            }
        }

        // Initialize real-time sync. WebSocket messages refresh immediately;
        // polling still runs as the fallback if the socket is unavailable.
        QueueSync.init({
            pollInterval: 15000,
            customRefresh: fetchStatus,
            customWSHandler: function(msg) {
                if (msg && (msg.action === 'passenger_change' || (msg.data && msg.data.action === 'passenger_change'))) {
                    var pData = msg.action === 'passenger_change' ? msg : msg.data;
                    handleRealtimePassengerChange(pData);
                    return;
                }
                fetchStatus();
            }
        });

        // Universal WebSocket document event listeners for full real-time reactivity
        document.addEventListener('pttm:ws-queue_update', function(e) {
            var detail = (e && e.detail) ? e.detail : {};
            var data = detail.data || detail;
            if (data && (data.action === 'passenger_change' || data.type === 'passenger_change')) {
                handleRealtimePassengerChange(data);
                return;
            }
            scheduleFetchStatus(300);
        });
        document.addEventListener('pttm:ws-vehicle_type_update', function() { scheduleFetchStatus(300); });
        document.addEventListener('pttm:ws-fare_update', function() { scheduleFetchStatus(300); });
        document.addEventListener('pttm:ws-operational_settings_updated', function() { scheduleFetchStatus(300); });
        document.addEventListener('pttm:ws-announcement_update', function() { scheduleFetchStatus(300); });
        document.addEventListener('pttm:ws-branding_updated', function() { scheduleFetchStatus(300); });

        // Cross-tab broadcast sync for instant passenger updates across open windows
        var _pttmQueueChannel = null;
        try {
            if (window.BroadcastChannel) {
                _pttmQueueChannel = new BroadcastChannel('pttm_queue_channel');
                _pttmQueueChannel.onmessage = function(e) {
                    if (e.data && (e.data.action === 'passenger_change' || (e.data.data && e.data.data.action === 'passenger_change'))) {
                        var pData = e.data.action === 'passenger_change' ? e.data : e.data.data;
                        handleRealtimePassengerChange(pData);
                    } else {
                        scheduleFetchStatus(300);
                    }
                };
            }
        } catch(e) {}

        window.addEventListener('storage', function(e) {
            if (e.key === 'pttm_queue_sync' && e.newValue) {
                try {
                    var parsed = JSON.parse(e.newValue);
                    if (parsed && parsed.action === 'passenger_change') {
                        handleRealtimePassengerChange(parsed);
                    } else {
                        scheduleFetchStatus(300);
                    }
                } catch(err) {}
            }
        });

        window.addEventListener('beforeunload', function() {
            if (_pttmQueueChannel) {
                try { _pttmQueueChannel.close(); } catch(err) {}
                _pttmQueueChannel = null;
            }
            if (_fetchTimer) {
                clearTimeout(_fetchTimer);
                _fetchTimer = null;
            }
        });

        fetchStatus(); // Initial fetch
        observeItems(); // Initial intersection observer

        // Countdown timer updater
        function updateCountdowns() {
            var timers = document.querySelectorAll('.countdown-timer[data-departure]');
            var now = new Date();
            timers.forEach(function (el) {
                var dep = new Date(el.dataset.departure);
                var diff = dep - now;
                if (isNaN(dep.getTime())) { el.textContent = ''; return; }

                el.classList.remove('cd-plenty', 'cd-green', 'cd-yellow', 'cd-soon', 'cd-orange', 'cd-imminent', 'cd-red', 'cd-passed', 'cd-overdue');

                if (diff <= 0) {
                    var overMin = Math.floor(Math.abs(diff) / 60000);
                    if (overMin < 5) {
                        el.textContent = '⏱ Departing soon!';
                        el.classList.add('cd-imminent', 'cd-red');
                    } else {
                        el.textContent = '⏱ ' + overMin + 'm overdue';
                        el.classList.add('cd-passed', 'cd-overdue');
                    }
                    return;
                }

                var totalSec = Math.floor(diff / 1000);
                var hrs = Math.floor(totalSec / 3600);
                var mins = Math.floor((totalSec % 3600) / 60);
                var secs = totalSec % 60;

                var label = '';
                if (hrs > 0) {
                    label = hrs + 'h ' + mins + 'm';
                } else if (mins > 0) {
                    label = mins + 'm ' + secs + 's';
                } else {
                    label = secs + 's';
                }
                el.textContent = '⏱ ' + label;

                if (totalSec <= 120) {
                    el.classList.add('cd-imminent', 'cd-red');
                } else if (totalSec <= 300) {
                    el.classList.add('cd-soon', 'cd-orange');
                } else if (totalSec <= 600) {
                    el.classList.add('cd-yellow');
                } else {
                    el.classList.add('cd-plenty', 'cd-green');
                }
            });
        }
        setInterval(updateCountdowns, 1000);
        updateCountdowns();

        function toggleGuestHeroClear(val) {
            var btn = document.getElementById('guest-hero-clear-btn');
            if (btn) {
                if (val && val.trim().length > 0) {
                    btn.style.setProperty('display', 'inline-flex', 'important');
                } else {
                    btn.style.setProperty('display', 'none', 'important');
                }
            }
        }
        function clearGuestHeroSearch() {
            var input = document.getElementById('guest-hero-search-input');
            if (input) {
                input.value = '';
                toggleGuestHeroClear('');
                input.focus();
            }
        }
    </script>
</body>

</html>
