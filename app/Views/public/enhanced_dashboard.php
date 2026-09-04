<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminal Status - Palompon Transit</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= base_url('images/9HFScgVg_400x400.png') ?>">
    <link rel="shortcut icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('apple-touch-icon.png') ?>">
    <!-- Font Awesome for Icons (using CDN as fallback, assuming FontAwesome is preferred for "classy" UI) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="stylesheet" href="<?= base_url('assets/css/design-system.css') ?>?v=3.2">
    <link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>?v=3.2">
    <link rel="stylesheet" href="<?= base_url('assets/css/responsive.css') ?>?v=3.2">
    <?= vehicle_type_colors_css() ?>
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
            gap: 30px;
            align-items: center;
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
        }

        @media (max-width: 480px) {
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
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: rgba(30, 64, 175, 0.1);
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
            box-shadow: 0 0 0 3px rgba(21, 101, 192, 0.25), var(--shadow-md);
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
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100vw;
            height: 100vh;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(15, 23, 42, 0);
            z-index: 99999 !important;
            transition: background 0.3s ease-out;
            box-sizing: border-box;
        }

        .route-average-modal.is-open {
            display: flex !important;
            background: rgba(15, 23, 42, 0.65);
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
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            font-family: inherit;
            white-space: nowrap;
        }

        .filter-chip:hover {
            border-color: #D62828;
            color: #D62828;
            background: rgba(214, 40, 40, 0.06);
        }

        .filter-chip.active {
            background: #000000;
            border-color: #000000;
            color: white;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.25);
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
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
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
            gap: 10px;
            min-width: 200px;
        }

        .passenger-count-row {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13.5px;
            color: #334155;
            white-space: nowrap;
        }

        .badge-full-tag {
            background: #ef4444;
            color: #ffffff;
            font-size: 10.5px;
            font-weight: 800;
            padding: 2px 7px;
            border-radius: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .progress-modern {
            height: 8px;
            background: #e2e8f0;
            border-radius: 10px;
            flex: 1;
            min-width: 80px;
            max-width: 140px;
            overflow: hidden;
            margin: 0;
            display: flex;
        }

        .progress-modern .progress-bar,
        .progress-bar {
            height: 100%;
            border-radius: 10px;
            transition: width 0.4s ease;
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
            gap: 6px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
        }

        .dep-header-label {
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
        }

        .dep-time-highlight {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
            line-height: 1.1;
        }

        .dep-status-tag {
            font-size: 12.5px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            display: inline-flex;
            align-items: center;
        }

        .dep-status-tag.status-waiting {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .dep-status-tag.status-boarding {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .dep-status-tag.status-departed {
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #e2e8f0;
        }

        .countdown-timer {
            font-size: 12px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 8px;
            display: inline-block;
            letter-spacing: 0.3px;
        }

        .countdown-timer.cd-plenty { background: #e8f5e9; color: #2e7d32; }
        .countdown-timer.cd-soon { background: #fff3e0; color: #e65100; }
        .countdown-timer.cd-imminent { background: #ffebee; color: #c62828; animation: cdPulse 1s infinite; }
        .countdown-timer.cd-passed { background: #e3f2fd; color: #1565c0; }

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
                gap: 16px;
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
                flex-wrap: wrap;
                justify-content: space-between;
                align-items: center;
                padding: 10px 16px;
            }

            .dep-time-group {
                display: flex;
                flex-direction: column;
                align-items: center;
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
            .queue-card {
                padding: 12px;
                gap: 12px;
                border-radius: 16px;
            }

            .operator-badge,
            .plate-badge {
                font-size: 13px;
                padding: 2px 7px;
            }

            .route-pill-badge, .driver-info-pill {
                font-size: 12.5px;
                padding: 4px 10px;
            }

            .passenger-status-block {
                min-width: 100%;
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
                font-size: 12px !important;
                padding: 2px 6px !important;
            }
            .route-pill-badge, .driver-info-pill {
                font-size: 11.5px !important;
                padding: 3px 8px !important;
            }
            .passenger-status-block {
                min-width: 100% !important;
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
            border-color: var(--accent);
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

<body>

    <?= view('templates/guest_header', [
        'announcements' => $announcements ?? [],
    ]) ?>

    <!-- Hero Section -->
    <section class="hero">
        <h2>Live Terminal Status</h2>
        <p>Monitor arrivals, departures, and current queue positions seamlessly from your device.</p>

        <div class="search-container">
            <form action="<?= base_url('search') ?>" method="get" class="search-bar">
                <input type="text" name="q" placeholder="Search by Plate Number, Destination, or Driver..." required>
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
            <button type="button" class="stat-card stat-card-button" id="routeAverageCard" aria-haspopup="dialog" aria-controls="routeAverageModal">
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
        foreach (($active_queue ?? []) as $qi) {
            $d = $qi['destination'] ?? '';
            if ($d && !in_array($d, $uniqueDestinations)) $uniqueDestinations[] = $d;
        }
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
                        <button class="filter-chip active" data-filter="all" data-type="all">All Routes</button>
                        <?php foreach ($uniqueDestinations as $dest): ?>
                        <button class="filter-chip" data-filter="<?= esc(strtolower($dest)) ?>" data-type="destination">
                            <i class="fas fa-map-marker-alt" style="font-size:12px;"></i> <?= esc($dest) ?>
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
                            $imgFile = vehicle_type_image($vType);
                            $vColor = vehicle_type_color($vType);
                            $vContrast = contrast_text_color($vColor);
                            $vIsLight = ($vContrast === '#0f172a');
                            $fkey = strtolower(trim($item['origin'])) . '|' . strtolower(trim($item['destination'])) . '|' . strtolower(trim($vType));
                            $cardFare = $fareMap[$fkey] ?? null;
                            ?>
                            <div class="queue-card queue-card-<?= esc($vType) ?>"
                                 style="--card-stripe-color: <?= esc($vColor) ?>; border-left: 5px solid <?= esc($vColor) ?> !important;"
                                 data-vehicle-type="<?= esc($vType) ?>"
                                 data-destination="<?= esc(strtolower($item['destination'])) ?>">
                                
                                <!-- Left: Vehicle Identity Block -->
                                <div class="queue-card-left">
                                    <div class="vehicle-thumb-box vehicle-type-<?= esc($vType) ?>" style="background: <?= esc($vColor) ?>12 !important; border-color: <?= esc($vColor) ?>35 !important;">
                                        <?php if (!empty($imgFile)): ?>
                                            <img src="<?= base_url('images/' . $imgFile) ?>" alt="<?= esc(vehicle_type_label($vType)) ?>" class="vehicle-thumb-img">
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
                                                <span><strong><?= $item['current_passengers'] ?></strong> / <?= $item['capacity'] ?> Onboard</span>
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
                                        </div>
                                    </div>
                                </div>

                                <!-- Right: Departure Time Ticket / Card -->
                                <div class="queue-card-departure">
                                    <div class="dep-status-tag <?= $item['status'] === 'boarding' ? 'status-boarding' : ($item['status'] === 'departed' ? 'status-departed' : 'status-waiting') ?>">
                                        <?php if ($item['status'] == 'departed'): ?>
                                            <i class="fas fa-check-circle me-1"></i> Departed
                                        <?php elseif ($item['status'] == 'boarding'): ?>
                                            <i class="fas fa-clock me-1"></i> Boarding
                                        <?php else: ?>
                                            <i class="fas fa-hourglass-half me-1"></i> Waiting
                                        <?php endif; ?>
                                    </div>
                                    <div class="dep-header-label">EST. DEPARTURE</div>
                                    <div class="dep-time-group">
                                        <div class="dep-time-highlight">
                                            <?php
                                            if ($item['status'] == 'departed' && $item['departure_time']):
                                                echo date('h:i A', strtotime($item['departure_time']));
                                            elseif ($item['estimated_departure']):
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
    <div class="route-average-modal" id="routeAverageModal" aria-hidden="true">
        <div class="route-average-dialog" role="dialog" aria-modal="true" aria-labelledby="routeAverageTitle">
            <div class="route-average-header">
                <div>
                    <h3 class="route-average-title" id="routeAverageTitle">Departure Time Rules</h3>
                    <p class="route-average-subtitle">Scheduled departure intervals configured for Palompon Terminal.</p>
                </div>
                <button type="button" class="route-average-close" id="routeAverageClose" aria-label="Close departure rules modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <?php
                // Extract unique specific route scopes from departure rules (exclude 'All Routes')
                $rulesList = $departure_rules ?? [];
                $uniqueScopes = [];
                foreach ($rulesList as $r) {
                    $s = trim($r['route_scope'] ?? '');
                    if (!empty($s) && strtolower($s) !== 'all routes' && strtolower($s) !== 'all' && !in_array($s, $uniqueScopes)) {
                        $uniqueScopes[] = $s;
                    }
                }
            ?>

            <!-- Route Filter Bar (only shown when specific route rules exist) -->
            <?php if (!empty($uniqueScopes)): ?>
            <div style="padding: 12px 24px 0; border-bottom: 1px solid #e2e8f0; background: #f8fafc; flex-shrink: 0;">
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; padding-bottom: 12px;">
                    <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #64748b; white-space: nowrap;">
                        <i class="fas fa-filter" style="margin-right: 4px;"></i>Route:
                    </span>
                    <div id="rulesRouteFilterGroup" style="display: flex; gap: 6px; flex-wrap: wrap;">
                        <button type="button" class="rules-route-chip active" data-route="all"
                            style="padding: 5px 14px; border-radius: 20px; border: 1.5px solid #2563eb; background: #2563eb; color: #fff; font-size: 12.5px; font-weight: 700; cursor: pointer; transition: all 0.2s; font-family: inherit; white-space: nowrap;">
                            All
                        </button>
                        <?php foreach ($uniqueScopes as $scope): ?>
                            <?php
                                // Extract just the destination for the chip label
                                $chipLabel = $scope;
                                if (strpos($scope, '→') !== false) {
                                    $parts = explode('→', $scope);
                                    $chipLabel = trim(end($parts));
                                }
                            ?>
                            <button type="button" class="rules-route-chip" data-route="<?= esc($scope, 'attr') ?>"
                                style="padding: 5px 14px; border-radius: 20px; border: 1.5px solid #e2e8f0; background: #ffffff; color: #64748b; font-size: 12.5px; font-weight: 700; cursor: pointer; transition: all 0.2s; font-family: inherit; white-space: nowrap;">
                                <?= strtoupper(esc($chipLabel)) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="route-average-body">
                <div class="route-average-list" id="routeAverageList">
                    <?php if (!empty($rulesList)): ?>
                        <?php foreach ($rulesList as $rule): ?>
                            <div class="route-average-item <?= !empty($rule['is_active_now']) ? 'is-active-rule' : '' ?>" data-rule-route="<?= esc($rule['route_scope'] ?? 'All Routes', 'attr') ?>">
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

                    <!-- No results message (hidden by default) -->
                    <div class="route-average-empty" id="rulesNoResults" style="display: none;">No rules match the selected route.</div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Departure Rules route filter
    (function() {
        var filterGroup = document.getElementById('rulesRouteFilterGroup');
        if (!filterGroup) return;

        filterGroup.addEventListener('click', function(e) {
            var chip = e.target.closest('.rules-route-chip');
            if (!chip) return;

            // Update active chip styles
            filterGroup.querySelectorAll('.rules-route-chip').forEach(function(c) {
                c.classList.remove('active');
                c.style.background = '#ffffff';
                c.style.color = '#64748b';
                c.style.borderColor = '#e2e8f0';
            });
            chip.classList.add('active');
            chip.style.background = '#000000';
            chip.style.color = '#fff';
            chip.style.borderColor = '#000000';

            // Filter items
            var route = chip.dataset.route;
            var items = document.querySelectorAll('#routeAverageList .route-average-item');
            var visible = 0;
            items.forEach(function(item) {
                if (route === 'all' || item.dataset.ruleRoute === route || item.dataset.ruleRoute === 'All Routes') {
                    item.style.display = '';
                    visible++;
                } else {
                    item.style.display = 'none';
                }
            });

            var noRes = document.getElementById('rulesNoResults');
            if (noRes) noRes.style.display = (visible === 0) ? '' : 'none';
        });
    })();
    </script>

    <?= $this->include('templates/guestfooter') ?>

    <!-- WebSocket is the fast path; polling remains the fallback. -->
    <script src="<?= base_url('js/ws-client.js?v=20260905') ?>"></script>
    <script src="<?= base_url('js/queue-sync.js?v=20260906') ?>"></script>
    <script>
        var _fetchPending = false;
        var _fetchQueued = false;
        var baseUrl = '<?= base_url() ?>';
        var departureRules = <?= json_encode($departure_rules ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        var fareMap = <?= json_encode($fareMap ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

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
                card.style.display = show ? '' : 'none';
                if (show) visible++;
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

        // Add micro-interaction to queue cards on scroll
        var observerOptions = { threshold: 0.1 };
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = "1";
                    entry.target.style.transform = "translateY(0)";
                }
            });
        }, observerOptions);

        function observeItems() {
            document.querySelectorAll('.queue-card, .stat-card').forEach(function (el) {
                if (!el.dataset.observed) {
                    el.dataset.observed = "true";
                    el.style.opacity = "0";
                    el.style.transform = "translateY(25px)";
                    el.style.transition = "opacity 0.6s cubic-bezier(0.4, 0, 0.2, 1), transform 0.6s cubic-bezier(0.4, 0, 0.2, 1)";
                    observer.observe(el);
                }
            });
        }

        // Vehicle Types Metadata (Colors, Icons, Images, Labels) for dynamic card rendering
        var vehicleTypeMeta = <?= json_encode(array_reduce(
            array_keys(get_db_vehicle_types() + ['van'=>1,'jeepney'=>1,'minibus'=>1,'bus'=>1,'taxi'=>1,'car'=>1,'tricecle'=>1,'tricycle'=>1,'motorcycle'=>1]),
            function($acc, $k) {
                $color = vehicle_type_color($k);
                $acc[$k] = [
                    'color'    => $color,
                    'contrast' => contrast_text_color($color),
                    'image'    => vehicle_type_image($k),
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
                return;
            }

            rows.forEach(function (rule) {
                var item = document.createElement('div');
                item.className = 'route-average-item' + (rule.is_active_now ? ' is-active-rule' : '');

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
        }

        function openRouteAverageModal() {
            var modal = document.getElementById('routeAverageModal');
            if (!modal) return;
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeRouteAverageModal() {
            var modal = document.getElementById('routeAverageModal');
            if (!modal) return;
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
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

        // Fetch status for real-time sync
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
                    var countDepartures = document.getElementById('count-departures');
                    var countRoutes = document.getElementById('count-routes');
                    if (countQueued) countQueued.innerText = data.active_queue.length;
                    if (countDepartures) countDepartures.innerText = data.total_departures_today;
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
                            if (vt && vt.color) {
                                var color = vt.color;
                                vehicleTypeMeta[k] = vehicleTypeMeta[k] || {};
                                vehicleTypeMeta[k].color = color;
                                vehicleTypeMeta[k].contrast = getContrastTextColor(color);
                                if (vt.name) vehicleTypeMeta[k].label = vt.name;
                                if (vt.icon) vehicleTypeMeta[k].icon = vt.icon;
                                // Keep CSS-var icon boxes/chips in sync without reload.
                                try {
                                    document.documentElement.style.setProperty('--vehicle-' + k, color);
                                    document.documentElement.style.setProperty('--vehicle-' + k + '-soft', color + '18');
                                } catch (e) { /* ignore */ }
                            }
                        });
                    }

                    // Only rewrite Queue DOM if data actually changed
                    var queueFP = makeFingerprint([data.active_queue, data.db_vehicle_types]);
                    if (queueFP !== _lastQueueFingerprint) {
                        _lastQueueFingerprint = queueFP;
                        var queueList = document.getElementById('queueList');
                        if (data.active_queue.length > 0) {
                            var queueHtml = '';
                            data.active_queue.forEach(function (item) {
                                var percent = Math.min(100, (Number(item.current_passengers) / Math.max(1, Number(item.capacity))) * 100);
                                var barColor = percent >= 90 ? '#ef4444' : (percent >= 70 ? '#f97316' : (percent >= 50 ? '#eab308' : '#22c55e'));
                                var fullBadge = Number(item.current_passengers) >= Number(item.capacity)
                                    ? '<span class="badge-full-tag">FULL</span>' : '';
                                var statusClass = 'status-waiting';
                                var statusIcon = '<i class="fas fa-hourglass-half me-1"></i> Waiting';
                                if (item.status === 'departed') {
                                    statusClass = 'status-departed';
                                    statusIcon = '<i class="fas fa-check-circle me-1"></i> Departed';
                                } else if (item.status === 'boarding') {
                                    statusClass = 'status-boarding';
                                    statusIcon = '<i class="fas fa-clock me-1"></i> Boarding';
                                }
                                var vType = (item.vehicle_type || '').toLowerCase();
                                var vMeta = (vehicleTypeMeta && vehicleTypeMeta[vType]) ? vehicleTypeMeta[vType] : {
                                    color: '#1565c0',
                                    contrast: '#ffffff',
                                    image: 'van.png',
                                    icon: 'fa-bus',
                                    label: vType.charAt(0).toUpperCase() + vType.slice(1)
                                };
                                var posColor = vMeta.color;
                                var posContrast = vMeta.contrast;
                                var imgFile = vMeta.image || 'van.png';
                                var vTypeLabel = vMeta.label;
                                var isLight = (posContrast === '#0f172a');
                                var pillBorder = isLight ? 'border: 2px solid #cbd5e1;' : 'border: 2px solid #ffffff;';
                                var badgeStyle = isLight 
                                    ? 'background: #f1f5f9 !important; background-color: #f1f5f9 !important; color: #0f172a !important; border: 1.5px solid #cbd5e1 !important; font-weight: 800;'
                                    : 'background: ' + posColor + '18 !important; background-color: ' + posColor + '18 !important; color: ' + posColor + ' !important; border: 1.5px solid ' + posColor + '44 !important; font-weight: 800;';

                                var fareBadge = getFareBadgeHtml(item.origin || '', item.destination || '', vType);
                                var opName = item.operator_name || item.driver_name || 'N/A';
                                var estDepIso = item.estimated_departure ? item.estimated_departure.replace(' ', 'T') : null;
                                
                                queueHtml += '<div class="queue-card queue-card-' + vType + '" style="--card-stripe-color:' + posColor + '; border-left: 5px solid ' + posColor + ' !important;" data-vehicle-type="' + vType + '" data-destination="' + (item.destination || '').toLowerCase() + '">'
                                    + '<div class="queue-card-left">'
                                    + '<div class="vehicle-thumb-box vehicle-type-' + vType + '" style="background:' + posColor + '12 !important; border-color:' + posColor + '35 !important;">'
                                    + '<img src="<?= base_url("images/") ?>' + imgFile + '" alt="' + vTypeLabel + '" class="vehicle-thumb-img">'
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
                                    + '<span><strong>' + item.current_passengers + '</strong> / ' + item.capacity + ' Onboard</span>'
                                    + fullBadge
                                    + '</div>'
                                    + '<div class="progress progress-modern">'
                                    + '<div class="progress-bar" style="width:' + percent + '%; height: 100%; background:' + barColor + ';"></div>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>'
                                    + '<div class="queue-card-departure">'
                                    + '<div class="dep-status-tag ' + statusClass + '">' + statusIcon + '</div>'
                                    + '<div class="dep-header-label">EST. DEPARTURE</div>'
                                    + '<div class="dep-time-group">'
                                    + '<div class="dep-time-highlight">' + (item.estimated_departure_formatted || 'Waiting') + '</div>'
                                    + (item.status === 'boarding' && estDepIso ? '<div class="countdown-timer" data-departure="' + estDepIso + '"></div>' : '')
                                    + '</div>'
                                    + '</div>'
                                    + '</div>';
                            });
                            queueList.innerHTML = queueHtml;
                            // Rebuild destination filter chips from live data
                            var destGroup = document.getElementById('filterDestGroup');
                            if (destGroup) {
                                var dests = [];
                                data.active_queue.forEach(function (it) { if (it.destination && dests.indexOf(it.destination) === -1) dests.push(it.destination); });
                                var prevActive = activeFilterValue;
                                var html = '<button class="filter-chip' + (prevActive === 'all' ? ' active' : '') + '" data-filter="all" data-type="all">All Routes</button>';
                                dests.forEach(function (d) {
                                    var isActive = prevActive === d.toLowerCase();
                                    html += '<button class="filter-chip' + (isActive ? ' active' : '') + '" data-filter="' + d.toLowerCase() + '" data-type="destination">' + d.toUpperCase() + '</button>';
                                });
                                destGroup.innerHTML = html;
                                initFilterChips();
                            }
                        } else {
                            queueList.innerHTML = '<div class="empty-queue-card"><div class="empty-icon-wrapper"><i class="fas fa-bus-alt"></i></div><h3>No Vehicles Currently in Queue</h3><p>There are no active vehicles waiting or boarding right now. Please check back shortly for live updates.</p></div>';
                        }
                        applyQueueFilter();
                        observeItems();
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

        // Initialize real-time sync. WebSocket messages refresh immediately;
        // polling still runs as the fallback if the socket is unavailable.
        QueueSync.init({
            pollInterval: 3000,
            customRefresh: fetchStatus,
            customWSHandler: fetchStatus
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

                el.classList.remove('cd-plenty', 'cd-soon', 'cd-imminent', 'cd-passed');

                if (diff <= 0) {
                    var overMin = Math.floor(Math.abs(diff) / 60000);
                    if (overMin < 5) {
                        el.textContent = '⏱ Departing soon!';
                        el.classList.add('cd-imminent');
                    } else {
                        el.textContent = '⏱ ' + overMin + 'm overdue';
                        el.classList.add('cd-passed');
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
                    el.classList.add('cd-imminent');
                } else if (totalSec <= 600) {
                    el.classList.add('cd-soon');
                } else {
                    el.classList.add('cd-plenty');
                }
            });
        }
        setInterval(updateCountdowns, 1000);
        updateCountdowns();
    </script>
</body>

</html>
