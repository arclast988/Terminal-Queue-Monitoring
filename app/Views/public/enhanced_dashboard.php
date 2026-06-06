<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminal Status - Palompon Transit</title>

    <!-- Adaptive theme boot: high-contrast LIGHT by day (best at 15 ft in sunlight),
         oceanic DARK after sunset. Pure presentation, no data change. -->
    <script>
        (function () {
            try {
                var o = window.localStorage.getItem('tqPublicOverride');
                var h = new Date().getHours();
                var t = (o === 'light' || o === 'dark') ? o : ((h >= 6 && h < 18) ? 'light' : 'dark');
                document.documentElement.setAttribute('data-theme', t);
            } catch (e) {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Sora:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@600;700;800&display=swap" rel="stylesheet">
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

        .nav-menu a.login-btn.btn-success {
            background: #059669;
        }

        .nav-menu a.login-btn.btn-success:hover {
            background: #047857 !important;
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

        /* --- Stats Grid --- */
        .container {
            width: 90%;
            max-width: 1400px;
            margin: -40px auto 40px;
            position: relative;
            z-index: 20;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: var(--shadow-md);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card-link {
            text-decoration: none;
            color: inherit;
            cursor: pointer;
        }

        .stat-card-button {
            width: 100%;
            border: none;
            color: inherit;
            cursor: pointer;
            font: inherit;
            text-align: left;
        }

        .stat-card-button:focus {
            outline: 3px solid rgba(21, 101, 192, 0.25);
            outline-offset: 3px;
        }

        .route-average-modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: rgba(15, 23, 42, 0.55);
            z-index: 2000;
        }

        .route-average-modal.is-open {
            display: flex;
        }

        .route-average-dialog {
            width: min(560px, 100%);
            max-height: min(720px, 90vh);
            overflow: hidden;
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            display: flex;
            flex-direction: column;
        }

        .route-average-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            padding: 24px 24px 16px;
            border-bottom: 1px solid #edf2f7;
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
        }

        .route-average-body {
            padding: 16px 24px 24px;
            overflow-y: auto;
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
        }

        .route-average-route {
            font-size: 14px;
            font-weight: 800;
            color: var(--primary-dark);
        }

        .route-average-count {
            margin-top: 2px;
            font-size: 11px;
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
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .live-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 700;
            color: var(--success);
            text-transform: uppercase;
            background: #e8f5e9;
            padding: 5px 12px;
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
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--text-muted);
            white-space: nowrap;
        }

        .filter-chip {
            padding: 6px 14px;
            border-radius: 20px;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            font-family: inherit;
            white-space: nowrap;
        }

        .filter-chip:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(21, 101, 192, 0.05);
        }

        .filter-chip.active {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
            box-shadow: 0 3px 8px rgba(21, 101, 192, 0.25);
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

        /* --- Queue Section --- */
        .queue-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .queue-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            gap: 25px;
            border: 1px solid #edf2f7;
            transition: var(--transition);
        }

        .queue-card:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-md);
        }

        .queue-pos {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            border-radius: 18px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
            box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
        }

        .queue-pos span:first-child {
            font-size: 10px;
            font-weight: 700;
            opacity: 0.8;
        }

        .queue-pos span:last-child {
            font-size: 28px;
            font-weight: 800;
        }

        .queue-details {
            flex: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .q-info h4 {
            font-size: 18px;
            color: var(--primary-dark);
            margin-bottom: 5px;
        }

        .q-info p {
            font-size: 13px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .q-meta {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-end;
            padding-right: 20px;
        }

        .time-badge {
            font-size: 15px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 4px;
        }

        .countdown-timer {
            font-size: 12px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 8px;
            margin-top: 4px;
            display: inline-block;
            letter-spacing: 0.3px;
            transition: all 0.3s ease;
        }

        .countdown-timer.cd-plenty {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .countdown-timer.cd-soon {
            background: #fff3e0;
            color: #e65100;
        }

        .countdown-timer.cd-imminent {
            background: #ffebee;
            color: #c62828;
            animation: cdPulse 1s ease-in-out infinite;
        }

        .countdown-timer.cd-passed {
            background: #e3f2fd;
            color: #1565c0;
        }

        @keyframes cdPulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.6;
            }
        }

        .status-pill {
            padding: 5px 15px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sp-waiting {
            background: #fff3e0;
            color: #ef6c00;
        }

        .sp-boarding {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .sp-ready {
            background: #e3f2fd;
            color: #1565c0;
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
            font-size: 11px;
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
        @media (max-width: 992px) {
            .header-info {
                display: none !important;
            }
        }

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

            .nav-menu a.login-btn {
                background: #1e3a8a;
                color: white !important;
                text-align: center;
                justify-content: center;
                margin-top: 10px;
                margin-left: 0 !important;
            }

            .nav-menu a.login-btn:hover,
            .nav-menu a.login-btn:active {
                background: #172554 !important;
                transform: scale(0.98);
            }

            .nav-menu a.login-btn.btn-success {
                background: #059669;
            }

            .nav-menu a.login-btn.btn-success:hover,
            .nav-menu a.login-btn.btn-success:active {
                background: #047857 !important;
            }

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

            .search-bar {
                padding: 5px;
                flex-direction: column;
                border-radius: 20px;
            }

            .search-bar input {
                padding: 12px 15px;
                text-align: center;
            }

            .search-bar button {
                padding: 12px;
                border-radius: 15px;
                width: 100%;
            }

            .container {
                width: 92%;
                margin-top: -30px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .stat-card {
                padding: 20px;
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

            /* --- Targeted Horizontal Queue Card --- */
            .queue-card {
                flex-direction: row;
                padding: 15px;
                gap: 12px;
                align-items: center;
                text-align: left;
            }

            .queue-pos {
                width: 55px;
                height: 55px;
                border-radius: 14px;
                position: static;
                flex-shrink: 0;
            }

            .queue-pos span:last-child {
                font-size: 22px;
            }

            .queue-details {
                grid-template-columns: 1fr;
                padding-top: 0;
                gap: 5px;
            }

            .q-info h4 {
                font-size: 16px;
                font-weight: 700;
                margin: 0;
            }

            .q-info p {
                font-size: 11px;
            }

            .capacity-info .progress {
                width: 100% !important;
                max-width: 140px;
            }

            .q-meta {
                align-items: flex-end;
                flex-direction: column;
                border: none;
                padding: 0;
                width: auto;
                gap: 2px;
            }

            .time-badge {
                font-size: 14px;
                margin-bottom: 0;
            }

            .status-pill {
                padding: 4px 12px;
                font-size: 10px;
            }

        }

        @media (max-width: 380px) {
            .hero h2 {
                font-size: 20px;
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

    <!-- Modern terminal theme layer (adaptive day/night) -->
    <link rel="stylesheet" href="<?= base_url('assets/css/terminal-theme.css') ?>">
    <script defer src="<?= base_url('js/theme-toggle.js') ?>"></script>

    <style>
        /* ── Public board: make the existing chrome theme-aware so day/night is cohesive.
              These override the inline styles above (loaded later + higher specificity). ── */
        body.tq-public { background: var(--bg-0) !important; color: var(--ink); }
        body.tq-public::before {
            content:""; position:fixed; inset:0; z-index:-1; pointer-events:none;
            background-color: var(--bg-0); background-image: var(--mesh);
        }

        body.tq-public .hero {
            background: linear-gradient(135deg, color-mix(in srgb,var(--accent) 22%, var(--bg-1)) 0%, var(--bg-1) 100%) !important;
            border-bottom: 1px solid var(--hairline);
        }
        body.tq-public .hero::before { opacity:.5; }
        body.tq-public .hero h2 { color: var(--ink) !important; font-family:'Sora',sans-serif; }
        body.tq-public .hero p { color: var(--ink-dim) !important; opacity:1; }
        body.tq-public .search-bar { background: var(--surface) !important; border:1px solid var(--hairline); -webkit-backdrop-filter:var(--blur); backdrop-filter:var(--blur); }
        body.tq-public .search-bar input { color: var(--ink) !important; }
        body.tq-public .search-bar input::placeholder { color: var(--ink-dim); }
        body.tq-public .search-bar button { background: var(--brand) !important; color: var(--accent-ink) !important; }

        /* stat tiles reuse the existing .stat-card markup + ids, re-themed */
        body.tq-public .stat-card { background: var(--surface) !important; border:1px solid var(--hairline); -webkit-backdrop-filter:var(--blur); backdrop-filter:var(--blur); box-shadow:var(--shadow-sm); }
        body.tq-public .stat-card:hover { box-shadow:var(--shadow); border-color:var(--hairline-strong); }
        body.tq-public .stat-info .value { color: var(--ink) !important; font-family:'JetBrains Mono',monospace; }
        body.tq-public .stat-info .label { color: var(--ink-dim) !important; }
        body.tq-public .si-blue { background:color-mix(in srgb,var(--accent) 16%,transparent); color:var(--accent); }
        body.tq-public .si-green { background:color-mix(in srgb,var(--accent-2) 16%,transparent); color:var(--accent-2); }
        body.tq-public .si-gold { background:color-mix(in srgb,var(--warn) 16%,transparent); color:var(--warn); }
        body.tq-public .si-purple { background:color-mix(in srgb,var(--brand) 16%,transparent); color:var(--brand); }

        body.tq-public .section-title { color: var(--ink) !important; font-family:'Sora',sans-serif; }
        body.tq-public .section-title i { color: var(--brand); }
        body.tq-public .live-indicator { background:color-mix(in srgb,var(--accent-2) 14%,transparent) !important; color:var(--accent-2) !important; border:1px solid color-mix(in srgb,var(--accent-2) 30%,transparent); }
        body.tq-public .quick-filter-bar { background: var(--surface) !important; border:1px solid var(--hairline); -webkit-backdrop-filter:var(--blur); backdrop-filter:var(--blur); }
        body.tq-public .filter-label { color: var(--ink-dim) !important; }
        body.tq-public .no-results-message { background: var(--surface) !important; color: var(--ink-dim) !important; border:1px dashed var(--hairline-strong); }

        /* route-average modal */
        body.tq-public .route-average-dialog { background: var(--surface-solid) !important; border:1px solid var(--hairline-strong); }
        body.tq-public .route-average-header { border-bottom-color: var(--hairline) !important; }
        body.tq-public .route-average-title { color: var(--ink) !important; }
        body.tq-public .route-average-subtitle, body.tq-public .route-average-count { color: var(--ink-dim) !important; }
        body.tq-public .route-average-item { background: var(--surface) !important; border-color: var(--hairline) !important; }
        body.tq-public .route-average-route { color: var(--ink) !important; }
        body.tq-public .route-average-time { color: var(--accent-2) !important; }
        body.tq-public .route-average-close { background:color-mix(in srgb,var(--ink) 10%,transparent) !important; color:var(--ink) !important; }
        body.tq-public .route-average-empty { background: var(--surface) !important; color: var(--ink-dim) !important; }

        /* spacing for the new Now Boarding hero inside the container */
        body.tq-public .tq-now { margin-bottom: 28px; }
    </style>
</head>

<body class="tq-public">

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

        <?php
        // "Now Boarding" = the vehicle currently in boarding status (no ticket entity).
        // Falls back to the next waiting vehicle as "Up Next". Reuses existing fields only.
        $boardingItem = null; $nextWaiting = null;
        foreach (($active_queue ?? []) as $qi) {
            if (($qi['status'] ?? '') === 'boarding' && $boardingItem === null) $boardingItem = $qi;
            if (($qi['status'] ?? '') === 'waiting'  && $nextWaiting  === null) $nextWaiting  = $qi;
        }
        $nowItem = $boardingItem ?: $nextWaiting;
        $nowIsBoarding = $boardingItem !== null;
        $nowPct = $nowItem ? min(100, ($nowItem['current_passengers'] / max(1, $nowItem['capacity'])) * 100) : 0;
        ?>
        <section class="tq-now<?= $nowIsBoarding ? '' : ' tq-now--idle' ?>" id="tqNowServing"
                 data-plate="<?= esc($nowItem['plate_number'] ?? '') ?>">
            <p class="tq-now__label"><span class="tq-live__dot"></span> <?= $nowIsBoarding ? 'NOW BOARDING' : 'UP NEXT' ?></p>
            <div class="tq-now__unit">
                <span class="tq-now__num">#<?= $nowItem['position'] ?? '--' ?></span>
                <div class="tq-now__detail">
                    <h2 class="tq-now__plate"><?= esc($nowItem['plate_number'] ?? 'Awaiting next unit') ?></h2>
                    <p class="tq-now__route">
                        <?php if ($nowItem): ?>
                            <?= esc($nowItem['origin']) ?> <span class="arrow">&rarr;</span> <strong><?= esc($nowItem['destination']) ?></strong>
                        <?php else: ?>
                            No vehicle is currently boarding
                        <?php endif; ?>
                    </p>
                    <?php if ($nowItem): ?>
                        <div class="tq-now__sub">
                            <span class="tq-now__eta"><i class="fas fa-clock"></i>
                                <?= !empty($nowItem['estimated_departure']) ? date('h:i A', strtotime($nowItem['estimated_departure'])) : 'Waiting' ?>
                            </span>
                            <?= vehicle_type_badge($nowItem['vehicle_type'] ?? '') ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ($nowItem): ?>
                    <div class="tq-now__cap" style="--pct:<?= $nowPct ?>%">
                        <span><?= $nowItem['current_passengers'] ?>/<?= $nowItem['capacity'] ?></span>
                        <small>Onboard</small>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Stats Summary -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon-wrapper si-blue"><i class="fas fa-car-side"></i></div>
                <div class="stat-info">
                    <span class="value" id="count-queued"><?= count($active_queue) ?></span>
                    <span class="label">Actively Queued</span>
                </div>
            </div>
            <a href="<?= base_url('schedules') ?>" class="stat-card stat-card-link" aria-label="View schedules">
                <div class="stat-icon-wrapper si-gold"><i class="fas fa-route"></i></div>
                <div class="stat-info">
                    <span class="value">8</span>
                    <span class="label">Operating Routes</span>
                </div>
            </a>
            <div class="stat-card">
                <div class="stat-icon-wrapper si-green"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info">
                    <span class="value" id="count-departures"><?= $total_departures_today ?></span>
                    <span class="label">Recent Departures</span>
                </div>
            </div>
            <button type="button" class="stat-card stat-card-button" id="routeAverageCard" aria-haspopup="dialog" aria-controls="routeAverageModal">
                <div class="stat-icon-wrapper si-purple"><i class="fas fa-clock"></i></div>
                <div class="stat-info">
                    <span class="value">View</span>
                    <span class="label">Avg. Departure Interval</span>
                </div>
            </button>
        </div>

        <div class="route-average-modal" id="routeAverageModal" aria-hidden="true">
            <div class="route-average-dialog" role="dialog" aria-modal="true" aria-labelledby="routeAverageTitle">
                <div class="route-average-header">
                    <div>
                        <h3 class="route-average-title" id="routeAverageTitle">Average Departure Interval by Route</h3>
                        <p class="route-average-subtitle">Based on today's completed departures from Palompon Terminal.</p>
                    </div>
                    <button type="button" class="route-average-close" id="routeAverageClose" aria-label="Close average departure time modal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="route-average-body">
                    <div class="route-average-list" id="routeAverageList">
                        <?php $routeAverageDepartures = $route_average_departures ?? []; ?>
                        <?php if (!empty($routeAverageDepartures)): ?>
                            <?php foreach ($routeAverageDepartures as $avg): ?>
                                <div class="route-average-item">
                                    <div>
                                        <div class="route-average-route"><?= esc($avg['origin']) ?> &rarr; <?= esc($avg['destination']) ?></div>
                                        <div class="route-average-count"><?= (int) $avg['departure_count'] ?> departure<?= (int) $avg['departure_count'] === 1 ? '' : 's' ?> today</div>
                                    </div>
                                    <div class="route-average-time"><?= esc($avg['interval_label']) ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="route-average-empty">No completed departures yet today.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
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
                            <i class="fas fa-map-marker-alt" style="font-size:11px;"></i> <?= esc($dest) ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div id="queueNoResults" class="no-results-message" style="display:none;">
                    <i class="fas fa-search" style="font-size:28px;margin-bottom:10px;opacity:0.4;display:block;"></i>
                    No vehicles match the selected filter.
                </div>

                <div class="tq-queue-grid" id="queueList">
                    <?php if (!empty($active_queue)): ?>
                        <?php foreach ($active_queue as $item): ?>
                            <?php
                            $vType  = strtolower($item['vehicle_type'] ?? '');
                            $fkey   = strtolower(trim($item['origin'])) . '|' . strtolower(trim($item['destination'])) . '|' . $vType;
                            $cardFare = $fareMap[$fkey] ?? null;
                            $pct    = min(100, ($item['current_passengers'] / max(1, $item['capacity'])) * 100);
                            $fill   = $pct >= 100 ? 'full' : ($pct >= 70 ? 'mid' : 'low');
                            $isFull = (int) $item['current_passengers'] >= (int) $item['capacity'];
                            $st     = $item['status'] ?? 'waiting';
                            $pillClass = $st === 'boarding' ? 'go' : ($st === 'departed' ? 'done' : 'wait');
                            $pillText  = $st === 'boarding' ? 'Boarding' : ($st === 'departed' ? 'Departed' : 'Waiting');
                            if ($st === 'departed' && !empty($item['departure_time'])) {
                                $etaStr = date('h:i A', strtotime($item['departure_time']));
                            } elseif (!empty($item['estimated_departure'])) {
                                $etaStr = date('h:i A', strtotime($item['estimated_departure']));
                            } else {
                                $etaStr = 'Waiting';
                            }
                            ?>
                            <article class="tq-card status-<?= esc($st) ?>"
                                     data-vehicle-type="<?= esc($vType) ?>"
                                     data-destination="<?= esc(strtolower($item['destination'])) ?>">
                                <header class="tq-card__head">
                                    <span class="tq-pos <?= vehicle_type_class($vType) ?>">#<?= $item['position'] ?></span>
                                    <span class="tq-plate"><?= esc($item['plate_number']) ?></span>
                                    <?= vehicle_type_badge($vType) ?>
                                    <span class="tq-pill tq-pill--<?= $pillClass ?>"><span class="tq-pill__dot"></span> <?= $pillText ?></span>
                                </header>
                                <div class="tq-card__meta">
                                    <div><i class="fas fa-user"></i> <?= esc($item['driver_name'] ?? 'N/A') ?></div>
                                    <div><i class="fas fa-route"></i> <?= esc($item['origin']) ?> <span class="arrow">&rarr;</span> <strong><?= esc($item['destination']) ?></strong></div>
                                    <div class="tq-eta"><i class="fas fa-plane-departure"></i> Est. <strong><?= $etaStr ?></strong></div>
                                    <div><i class="fas fa-users"></i> <?= $item['current_passengers'] ?>/<?= $item['capacity'] ?> onboard</div>
                                </div>
                                <?php if ($cardFare !== null): ?>
                                    <div class="tq-fare"><i class="fas fa-tag"></i> &#8369;<?= number_format($cardFare, 0) ?> Fare</div>
                                <?php endif; ?>
                                <div class="tq-cap" data-fill="<?= $fill ?>" style="--pct:<?= $pct ?>%">
                                    <div class="tq-cap__head"><i class="fas fa-users"></i> Capacity
                                        <span class="tq-cap__count <?= $isFull ? 'is-full' : '' ?>"><?= $item['current_passengers'] ?> / <?= $item['capacity'] ?></span>
                                        <?php if ($isFull): ?><span class="tq-pill tq-pill--full">Full</span><?php endif; ?>
                                    </div>
                                    <div class="tq-cap__bar"><span></span></div>
                                </div>
                                <?php if ($st === 'boarding' && !empty($item['estimated_departure'])): ?>
                                    <div class="countdown-timer" data-departure="<?= date('c', strtotime($item['estimated_departure'])) ?>"></div>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="tq-empty"><i class="fas fa-inbox"></i> No vehicles currently in queue.</div>
                    <?php endif; ?>
                </div>
            </section>

        </div>
    </div>

    <?= $this->include('templates/guestfooter') ?>

    <!-- Public board is poll-only (ws-client.js intentionally NOT loaded):
         passengers refresh every 3s via the cached /status endpoint, so they
         don't consume WebSocket-server connections. Staff/admin pages keep the
         instant WebSocket path. -->
    <script src="<?= base_url('js/queue-sync.js') ?>"></script>
    <script>
        var _fetchPending = false;
        var baseUrl = '<?= base_url() ?>';
        var routeAverageDepartures = <?= json_encode($route_average_departures ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        var fareMap = <?= json_encode($fareMap ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

        // --- Active filter state ---
        var activeFilterType = 'all';
        var activeFilterValue = 'all';

        function applyQueueFilter() {
            var cards = document.querySelectorAll('#queueList .tq-card');
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
            return '<div class="tq-fare"><i class="fas fa-tag"></i> &#8369;' + Number(fare).toLocaleString('en-PH', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + ' Fare</div>';
        }

        // Build the dispatcher/public queue card markup — MUST mirror the PHP template
        // (article.tq-card) so poll-rewrites are visually identical. Presentational only.
        function buildQueueCard(item) {
            var vType = (item.vehicle_type || '').toLowerCase();
            var cur = Number(item.current_passengers), cap = Number(item.capacity);
            var pct = Math.min(100, (cur / Math.max(1, cap)) * 100);
            var fill = pct >= 100 ? 'full' : (pct >= 70 ? 'mid' : 'low');
            var isFull = cur >= cap;
            var st = item.status || 'waiting';
            var pillClass = st === 'boarding' ? 'go' : (st === 'departed' ? 'done' : 'wait');
            var pillText = st === 'boarding' ? 'Boarding' : (st === 'departed' ? 'Departed' : 'Waiting');
            var vLabel = vType ? (vType.charAt(0).toUpperCase() + vType.slice(1)) : '';
            var etaStr = item.estimated_departure_formatted || 'Waiting';
            var estDepIso = item.estimated_departure ? item.estimated_departure.replace(' ', 'T') : null;
            var fareBadge = getFareBadgeHtml(item.origin || '', item.destination || '', vType);
            var countdown = (st === 'boarding' && estDepIso)
                ? '<div class="countdown-timer" data-departure="' + estDepIso + '"></div>' : '';
            return '<article class="tq-card status-' + st + '" data-vehicle-type="' + vType + '" data-destination="' + (item.destination || '').toLowerCase() + '">'
                + '<header class="tq-card__head">'
                + '<span class="tq-pos vehicle-type-' + vType + '">#' + item.position + '</span>'
                + '<span class="tq-plate">' + (item.plate_number || '') + '</span>'
                + '<span class="vehicle-type-chip vehicle-type-' + vType + '">' + vLabel + '</span>'
                + '<span class="tq-pill tq-pill--' + pillClass + '"><span class="tq-pill__dot"></span> ' + pillText + '</span>'
                + '</header>'
                + '<div class="tq-card__meta">'
                + '<div><i class="fas fa-user"></i> ' + (item.driver_name || 'N/A') + '</div>'
                + '<div><i class="fas fa-route"></i> ' + item.origin + ' <span class="arrow">&rarr;</span> <strong>' + item.destination + '</strong></div>'
                + '<div class="tq-eta"><i class="fas fa-plane-departure"></i> Est. <strong>' + etaStr + '</strong></div>'
                + '<div><i class="fas fa-users"></i> ' + cur + '/' + cap + ' onboard</div>'
                + '</div>'
                + fareBadge
                + '<div class="tq-cap" data-fill="' + fill + '" style="--pct:' + pct + '%">'
                + '<div class="tq-cap__head"><i class="fas fa-users"></i> Capacity '
                + '<span class="tq-cap__count' + (isFull ? ' is-full' : '') + '">' + cur + ' / ' + cap + '</span>'
                + (isFull ? '<span class="tq-pill tq-pill--full">Full</span>' : '')
                + '</div><div class="tq-cap__bar"><span></span></div>'
                + '</div>'
                + countdown
                + '</article>';
        }

        // "Now Boarding" hero — mirrors the PHP hero; blinks when the boarding plate changes.
        function updateNowServing(queue) {
            var el = document.getElementById('tqNowServing');
            if (!el) return;
            var boarding = null, nextW = null;
            queue.forEach(function (q) {
                if (q.status === 'boarding' && !boarding) boarding = q;
                if (q.status === 'waiting' && !nextW) nextW = q;
            });
            var item = boarding || nextW;
            var isBoarding = !!boarding;
            var prevPlate = el.getAttribute('data-plate') || '';
            var newPlate = item ? (item.plate_number || '') : '';
            var label = '<span class="tq-live__dot"></span> ' + (isBoarding ? 'NOW BOARDING' : 'UP NEXT');

            var inner = '<p class="tq-now__label">' + label + '</p><div class="tq-now__unit">';
            if (item) {
                var vType = (item.vehicle_type || '').toLowerCase();
                var vLabel = vType ? (vType.charAt(0).toUpperCase() + vType.slice(1)) : '';
                var pct = Math.min(100, (Number(item.current_passengers) / Math.max(1, Number(item.capacity))) * 100);
                inner += '<span class="tq-now__num">#' + item.position + '</span>'
                    + '<div class="tq-now__detail">'
                    + '<h2 class="tq-now__plate">' + (item.plate_number || '') + '</h2>'
                    + '<p class="tq-now__route">' + item.origin + ' <span class="arrow">&rarr;</span> <strong>' + item.destination + '</strong></p>'
                    + '<div class="tq-now__sub"><span class="tq-now__eta"><i class="fas fa-clock"></i> ' + (item.estimated_departure_formatted || 'Waiting') + '</span>'
                    + '<span class="vehicle-type-chip vehicle-type-' + vType + '">' + vLabel + '</span></div>'
                    + '</div>'
                    + '<div class="tq-now__cap" style="--pct:' + pct + '%"><span>' + item.current_passengers + '/' + item.capacity + '</span><small>Onboard</small></div>';
            } else {
                inner += '<span class="tq-now__num">#--</span><div class="tq-now__detail">'
                    + '<h2 class="tq-now__plate">Awaiting next unit</h2>'
                    + '<p class="tq-now__route">No vehicle is currently boarding</p></div>';
            }
            inner += '</div>';
            el.innerHTML = inner;
            el.classList.toggle('tq-now--idle', !isBoarding);
            el.setAttribute('data-plate', newPlate);
            if (newPlate && newPlate !== prevPlate) {
                el.classList.remove('is-changed');
                void el.offsetWidth;
                el.classList.add('is-changed');
            }
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
            document.querySelectorAll('.stat-card').forEach(function (el) {
                if (!el.dataset.observed) {
                    el.dataset.observed = "true";
                    el.style.opacity = "0.7";
                    el.style.transform = "translateY(20px)";
                    el.style.transition = "all 0.6s ease-out";
                    observer.observe(el);
                }
            });
        }

        // Fingerprint cache to avoid flickering DOM rewrites when data hasn't changed
        var _lastQueueFingerprint = '';
        var _lastRouteAverageFingerprint = makeFingerprint(routeAverageDepartures);

        function makeFingerprint(arr) {
            return JSON.stringify(arr);
        }

        function renderRouteAverageDepartures(rows) {
            var list = document.getElementById('routeAverageList');
            if (!list) return;

            list.innerHTML = '';
            if (!rows || rows.length === 0) {
                var empty = document.createElement('div');
                empty.className = 'route-average-empty';
                empty.textContent = 'No completed departures yet today.';
                list.appendChild(empty);
                return;
            }

            rows.forEach(function (row) {
                var item = document.createElement('div');
                item.className = 'route-average-item';

                var routeWrap = document.createElement('div');
                var route = document.createElement('div');
                route.className = 'route-average-route';
                route.textContent = (row.origin || 'Unknown Origin') + ' \u2192 ' + (row.destination || 'Unknown Destination');

                var count = document.createElement('div');
                var departureCount = Number(row.departure_count || 0);
                count.className = 'route-average-count';
                count.textContent = departureCount + ' departure' + (departureCount === 1 ? '' : 's') + ' today';

                var time = document.createElement('div');
                time.className = 'route-average-time';
                time.textContent = row.interval_label || '--';

                routeWrap.appendChild(route);
                routeWrap.appendChild(count);
                item.appendChild(routeWrap);
                item.appendChild(time);
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

        // Fetch status for real-time sync
        function fetchStatus() {
            if (_fetchPending) return;
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
                    if (countQueued) countQueued.innerText = data.active_queue.length;
                    if (countDepartures) countDepartures.innerText = data.total_departures_today;

                    if (data.route_average_departures) {
                        var routeAverageFP = makeFingerprint(data.route_average_departures);
                        if (routeAverageFP !== _lastRouteAverageFingerprint) {
                            _lastRouteAverageFingerprint = routeAverageFP;
                            routeAverageDepartures = data.route_average_departures;
                            renderRouteAverageDepartures(routeAverageDepartures);
                        }
                    }

                    // Only rewrite Queue DOM if data actually changed
                    var queueFP = makeFingerprint(data.active_queue);
                    if (queueFP !== _lastQueueFingerprint) {
                        _lastQueueFingerprint = queueFP;
                        var queueList = document.getElementById('queueList');
                        if (data.active_queue.length > 0) {
                            var queueHtml = '';
                            data.active_queue.forEach(function (item) {
                                queueHtml += buildQueueCard(item);
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
                                    html += '<button class="filter-chip' + (isActive ? ' active' : '') + '" data-filter="' + d.toLowerCase() + '" data-type="destination"><i class="fas fa-map-marker-alt" style="font-size:11px;"></i> ' + d + '</button>';
                                });
                                destGroup.innerHTML = html;
                                initFilterChips();
                            }
                        } else {
                            queueList.innerHTML = '<div class="tq-empty"><i class="fas fa-inbox"></i> No vehicles currently in queue.</div>';
                        }
                        applyQueueFilter();
                        observeItems();
                        // Keep the "Now Boarding" hero in sync (presentational; blinks on change)
                        updateNowServing(data.active_queue);
                    }
                })
                .catch(function (error) {
                    console.error('Error fetching status:', error);
                })
                .finally(function () {
                    _fetchPending = false;
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

        // Initialize real-time sync (poll-only on the public board).
        // Polls the cached /status endpoint every 3s; no WebSocket connection is
        // opened here (ws-client.js is intentionally not loaded), keeping the
        // single-process WS server free for staff/admin.
        QueueSync.init({
            pollInterval: 3000,
            customRefresh: fetchStatus
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
