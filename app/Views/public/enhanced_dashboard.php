<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminal Status - Palompon Transit</title>
    <!-- Font Awesome for Icons (using CDN as fallback, assuming FontAwesome is preferred for "classy" UI) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 30px;
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
        @media (max-width: 1100px) {
            .main-layout {
                grid-template-columns: 1fr;
            }

            .sidebar-section {
                position: relative;
                top: 0;
            }
        }

        @media (max-width: 992px) {
            .header-info {
                display: none !important;
            }
        }

        @media (max-width: 768px) {
            .advisory-bar {
                padding: 8px 5%;
                font-size: 11px;
            }

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
</head>

<body>

    <!-- Mobile Overlay -->
    <div class="nav-overlay" id="navOverlay" onclick="toggleMenu()"></div>

    <!-- Mobile Close Button - outside menu panel so position:fixed works at any zoom -->
    <button class="mobile-menu-close" id="mobileMenuClose" onclick="toggleMenu()" aria-label="Close menu">
        <i class="fas fa-times"></i>
    </button>

    <!-- Advisory Bar (announcements from admin) -->
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
    <header class="guest-header">
        <div style="display: flex; align-items: center; gap: 40px;">
            <a href="<?= base_url('guest') ?>" class="logo-section">
                <img src="<?= base_url('images/9HFScgVg_400x400.png') ?>" alt="Logo" class="logo">
                <div class="logo-text">
                    <h1>Palompon Transit </h1>
                    <p>Terminal Monitor</p>
                </div>
            </a>

            <div class="header-info"
                style="display: flex; flex-direction: column; gap: 8px; font-size: 13px; color: var(--text-muted); border-left: 1px solid #eee; padding-left: 20px;">
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
            <a href="<?= base_url('guest') ?>" class="<?= current_url() == base_url('guest') ? 'active' : '' ?>"><i
                    class="fas fa-home"></i> Home</a>
            <a href="<?= base_url('schedules') ?>"
                class="<?= (strpos(uri_string(), 'schedules') !== false) ? 'active' : '' ?>"><i
                    class="fas fa-calendar-alt"></i> Schedules</a>
            <a href="<?= base_url('fares') ?>"
                class="<?= (strpos(uri_string(), 'fares') !== false) ? 'active' : '' ?>"><i class="fas fa-tags"></i>
                Fares</a>

            <div class="lang-switcher"
                style="display: flex; align-items: center; gap: 6px; font-size: 14px; color: var(--text-muted);">
                <i class="fas fa-globe"></i>
                <span>EN</span>
                <i class="fas fa-chevron-down" style="font-size: 11px;"></i>
            </div>

            <?php if (session()->get('isLoggedIn')): ?>
                <?php
                $dashboardUrl = '/';
                if (session()->get('role') == 'admin')
                    $dashboardUrl = '/admin/dashboard';
                elseif (session()->get('role') == 'staff')
                    $dashboardUrl = '/staff/dashboard';
                ?>
                <a href="<?= base_url($dashboardUrl) ?>" class="login-btn btn-success" style="margin-left: 10px;">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
                <a href="<?= base_url('logout') ?>"
                    style="color: #e53e3e; padding: 8px 15px; border-radius: 6px; transition: var(--transition); display: flex; align-items: center;">
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
        <a href="<?= base_url('guest') ?>">Home</a> <i class="fas fa-chevron-right"></i> <span>Dashboard</span>
    </div>

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

        <div class="main-layout">
            <!-- Left: Terminal Queue -->
            <section class="queue-section">
                <div class="section-header">
                    <h3 class="section-title"><i class="fas fa-stream"></i> Terminal Queue</h3>
                    <div class="live-indicator">
                        <div class="dot-pulse"></div>
                        Live Monitor
                    </div>
                </div>

                <div class="queue-container" id="queueList">
                    <?php if (!empty($active_queue)): ?>
                        <?php foreach ($active_queue as $item): ?>
                            <div class="queue-card">
                                <?php
                                $imgMap = [
                                    'van' => 'van.png',
                                    'jeepney' => 'jeep.png',
                                    'minibus' => 'minibus.png'
                                ];
                                $vType = strtolower($item['vehicle_type'] ?? '');
                                $imgFile = $imgMap[$vType] ?? 'van.png';
                                ?>
                                <div class="vehicle-type-icon <?= vehicle_type_class($vType) ?>"
                                    style="position: relative; width: clamp(50px, 11vw, 75px); flex-shrink: 0; display: flex; justify-content: center; align-items: center;">
                                    <img src="<?= base_url('images/' . $imgFile) ?>" alt="<?= esc($vType) ?>"
                                        style="width: 100%; height: auto; object-fit: contain; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));">
                                    <?php
                                        $posColors = ['van' => '#c62828', 'jeepney' => '#1565c0', 'minibus' => '#2e7d32'];
                                        $posColor = $posColors[$vType] ?? '#1e293b';
                                    ?>
                                    <span
                                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill border border-white border-2 queue-pos-badge"
                                        style="background:<?= $posColor ?>; color:#fff; font-size:13px; font-weight:800; padding:4px 9px; min-width:28px; text-align:center; box-shadow:0 2px 6px rgba(0,0,0,0.25); z-index:2;">
                                        #<?= $item['position'] ?>
                                    </span>
                                </div>
                                <div class="queue-details">
                                    <div class="q-info">
                                        <h4><?= esc($item['plate_number']) ?></h4>
                                        <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                                            <p><i class="fas fa-user"></i> <?= esc($item['driver_name'] ?? 'N/A') ?></p>
                                            <p><i class="fas fa-map-marker-alt"></i> Route: <strong><?= esc($item['origin']) ?>
                                                    - <?= esc($item['destination']) ?></strong></p>
                                        </div>
                                        <div class="capacity-info" style="margin-top: 5px;">
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <i class="fas fa-users" style="color: var(--primary);"></i>
                                                <span style="font-size: 13px; font-weight: 600; color: var(--text-main);">
                                                    <?= $item['current_passengers'] ?> / <?= $item['capacity'] ?> Onboard
                                                </span>
                                                <?php if ((int) $item['current_passengers'] >= (int) $item['capacity']): ?>
                                                    <span class="badge bg-danger"
                                                        style="font-size: 10px; padding: 2px 8px; border-radius: 10px;">FULL</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="progress"
                                                style="height: 6px; background: #eee; border-radius: 10px; margin-top: 5px; overflow: hidden;">
                                                <?php $percent = min(100, ($item['current_passengers'] / max(1, $item['capacity'])) * 100); ?>
                                                <div class="progress-bar"
                                                    style="width: <?= $percent ?>%; height: 100%; background: <?= $percent >= 100 ? '#e53e3e' : 'var(--primary)' ?>; transition: width 0.3s ease;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="q-meta" style="text-align: center;">
                                        <small class="text-muted"
                                            style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 8px;">Est.
                                            Departure</small>
                                        <div
                                            style="background: #4a5c7a; color: white; padding: 12px 16px; border-radius: 12px; font-size: 18px; font-weight: 700; margin-bottom: 8px;">
                                            <?php
                                            if ($item['status'] == 'departed' && $item['departure_time']):
                                                echo date('h:i A', strtotime($item['departure_time']));
                                            elseif ($item['estimated_departure']):
                                                echo date('h:i A', strtotime($item['estimated_departure']));
                                            else:
                                                echo '--:-- --';
                                            endif;
                                            ?>
                                        </div>
                                        <?php if ($item['status'] === 'boarding' && !empty($item['estimated_departure'])): ?>
                                            <div class="countdown-timer"
                                                data-departure="<?= date('c', strtotime($item['estimated_departure'])) ?>"></div>
                                        <?php endif; ?>
                                        <div style="font-size: 13px; color: #1565c0; font-weight: 600; margin-top: 4px;">
                                            <?php if ($item['status'] == 'departed'): ?>
                                                Departed
                                            <?php elseif ($item['status'] == 'boarding'): ?>
                                                <?= $item['current_passengers'] ?>/<?= $item['capacity'] ?> passengers
                                            <?php else: ?>
                                                Waiting
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="card p-4 text-center">
                            <p class="text-muted">No vehicles currently in queue.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Right: Route Fares -->
            <aside class="fares-section" id="fares-section">
                <div class="sidebar-section">
                    <h3 class="sidebar-title"><i class="fas fa-paper-plane"></i> Recent Departures</h3>

                    <div class="fare-search">
                        <input type="text" id="deptSearch" placeholder="Filter departures...">
                    </div>

                    <div class="fare-list" id="deptList">
                        <?php if (!empty($recent_departures)): ?>
                            <?php foreach ($recent_departures as $dept): ?>
                                <a href="<?= base_url('history?q=' . urlencode($dept['destination'])) ?>"
                                    class="fare-item" style="text-decoration: none;">
                                    <div>
                                        <div class="f-dest"><?= esc($dept['origin']) ?> - <?= esc($dept['destination']) ?></div>
                                        <div class="f-type"><?= esc($dept['plate_number']) ?></div>
                                    </div>
                                    <div class="f-price" style="font-size: 14px;">
                                        <?= date('h:i A', strtotime($dept['departure_time'])) ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center py-3">No recent departures.</p>
                        <?php endif; ?>
                    </div>
                    <div class="text-center mt-3">
                        <a href="<?= base_url('history') ?>" class="btn btn-sm btn-outline-primary"
                            style="font-size: 12px; font-weight: 600; text-decoration: none;">View All History</a>
                    </div>
                </div>

                <div class="sidebar-section mt-4" id="route-fares">
                    <h3 class="sidebar-title"><i class="fas fa-tags"></i> Route Fares</h3>
                    <div class="fare-list" id="routeFaresList">
                        <?php if (!empty($routes)): ?>
                            <?php foreach ($routes as $route): ?>
                                <div class="fare-item">
                                    <div>
                                        <div class="f-dest"><?= esc($route['origin']) ?> - <?= esc($route['destination']) ?>
                                        </div>
                                        <div class="f-type"><?= vehicle_type_badge($route['vehicle_type']) ?></div>
                                    </div>
                                    <div class="f-price">₱<?= number_format($route['fare'], 0) ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center py-3">No active routes.</p>
                        <?php endif; ?>
                    </div>

                </div>
            </aside>
        </div>
    </div>

    <?= $this->include('templates/guestfooter') ?>

    <script src="<?= base_url('js/ws-client.js') ?>"></script>
    <script src="<?= base_url('js/queue-sync.js') ?>"></script>
    <script>
        var _fetchPending = false;
        var baseUrl = '<?= base_url() ?>';
        var routeAverageDepartures = <?= json_encode($route_average_departures ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

        // Language Toggle
        function toggleLanguage(event) {
            event.preventDefault();
            alert("Language switching functionality coming soon! Currently defaulting to English (EN).");
        }

        // Real-time Clock
        function updateClock() {
            var clockEl = document.getElementById('headerClock');
            if (clockEl) {
                var now = new Date();
                clockEl.textContent = now.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                });
            }
        }
        setInterval(updateClock, 1000);
        updateClock();

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
                    el.style.opacity = "0.7";
                    el.style.transform = "translateY(20px)";
                    el.style.transition = "all 0.6s ease-out";
                    observer.observe(el);
                }
            });
        }

        // Fingerprint cache to avoid flickering DOM rewrites when data hasn't changed
        var _lastQueueFingerprint = '';
        var _lastDeptFingerprint = '';
        var _lastRoutesFingerprint = '';
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
                                var percent = Math.min(100, (Number(item.current_passengers) / Math.max(1, Number(item.capacity))) * 100);
                                var progressColor = percent >= 100 ? '#e53e3e' : 'var(--primary)';
                                var fullBadge = Number(item.current_passengers) >= Number(item.capacity)
                                    ? '<span class="badge bg-danger" style="font-size:10px;padding:2px 8px;border-radius:10px;">FULL</span>' : '';
                                var statusText = 'Waiting';
                                if (item.status === 'departed') statusText = 'Departed';
                                else if (item.status === 'boarding') statusText = item.current_passengers + '/' + item.capacity + ' passengers';
                                var imgMap = { 'van': 'van.png', 'jeepney': 'jeep.png', 'minibus': 'minibus.png' };
                                var vType = (item.vehicle_type || '').toLowerCase();
                                var imgFile = imgMap[vType] || 'van.png';
                                var posColors = { van: '#c62828', jeepney: '#1565c0', minibus: '#2e7d32' };
                                var posColor = posColors[vType] || '#1e293b';
                                var posBadge = '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill border border-white border-2 queue-pos-badge" style="background:' + posColor + ';color:#fff;font-size:13px;font-weight:800;padding:4px 9px;min-width:28px;text-align:center;box-shadow:0 2px 6px rgba(0,0,0,0.25);z-index:2;">#' + item.position + '</span>';
                                queueHtml += '<div class="queue-card">'
                                    + '<div class="vehicle-type-icon vehicle-type-' + vType + '" style="position:relative;width:clamp(50px,11vw,75px);flex-shrink:0;display:flex;justify-content:center;align-items:center;">'
                                    + '<img src="<?= base_url("images/") ?>' + imgFile + '" alt="' + vType + '" style="width:100%;height:auto;object-fit:contain;filter:drop-shadow(0 4px 6px rgba(0,0,0,0.1));">'
                                    + posBadge
                                    + '</div>'
                                    + '<div class="queue-details">'
                                    + '<div class="q-info">'
                                    + '<h4>' + item.plate_number + '</h4>'
                                    + '<div style="display:flex;align-items:center;gap:15px;flex-wrap:wrap;">'
                                    + '<p><i class="fas fa-user"></i> ' + (item.driver_name || 'N/A') + '</p>'
                                    + '<p><i class="fas fa-map-marker-alt"></i> Route: <strong>' + item.origin + ' - ' + item.destination + '</strong></p>'
                                    + '</div>'
                                    + '<div class="capacity-info" style="margin-top:5px;">'
                                    + '<div style="display:flex;align-items:center;gap:10px;">'
                                    + '<i class="fas fa-users" style="color:var(--primary);"></i>'
                                    + '<span style="font-size:13px;font-weight:600;color:var(--text-main);">' + item.current_passengers + ' / ' + item.capacity + ' Onboard</span>'
                                    + fullBadge
                                    + '</div>'
                                    + '<div class="progress" style="height:6px;background:#eee;border-radius:10px;margin-top:5px;overflow:hidden;">'
                                    + '<div class="progress-bar" style="width:' + percent + '%;height:100%;background:' + progressColor + ';transition:width 0.3s ease;"></div>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>'
                                    + '<div class="q-meta" style="text-align:center;">'
                                    + '<small class="text-muted" style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;display:block;margin-bottom:8px;">Est. Departure</small>'
                                    + '<div style="background:#4a5c7a;color:white;padding:12px 16px;border-radius:12px;font-size:18px;font-weight:700;margin-bottom:8px;">'
                                    + (item.estimated_departure_formatted || '--:-- --')
                                    + '</div>'
                                    + (item.status === 'boarding' && item.estimated_departure ? '<div class="countdown-timer" data-departure="' + item.estimated_departure + '"></div>' : '')
                                    + '<div style="font-size:13px;color:#1565c0;font-weight:600;margin-top:4px;">' + statusText + '</div>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>';
                            });
                            queueList.innerHTML = queueHtml;
                        } else {
                            queueList.innerHTML = '<div class="card p-4 text-center"><p class="text-muted">No vehicles currently in queue.</p></div>';
                        }
                        observeItems();
                    }

                    // Only rewrite Departures DOM if data changed
                    var deptFP = makeFingerprint(data.recent_departures);
                    if (deptFP !== _lastDeptFingerprint) {
                        _lastDeptFingerprint = deptFP;
                        var deptList = document.getElementById('deptList');
                        if (data.recent_departures.length > 0) {
                            var deptHtml = '';
                            data.recent_departures.forEach(function (dept) {
                                var url = '<?= base_url("history?q=") ?>' + encodeURIComponent(dept.destination);
                                deptHtml += '<a href="' + url + '" class="fare-item" style="text-decoration:none;">'
                                    + '<div>'
                                    + '<div class="f-dest">' + dept.origin + ' - ' + dept.destination + '</div>'
                                    + '<div class="f-type">' + dept.plate_number + '</div>'
                                    + '</div>'
                                    + '<div class="f-price" style="font-size:14px;">' + dept.departure_time_formatted + '</div>'
                                    + '</a>';
                            });
                            deptList.innerHTML = deptHtml;
                        } else {
                            deptList.innerHTML = '<p class="text-muted text-center py-3">No recent departures.</p>';
                        }
                    }

                    // Update Route Fares sidebar if data changed
                    if (data.routes) {
                        var routesFP = makeFingerprint(data.routes);
                        if (routesFP !== _lastRoutesFingerprint) {
                            _lastRoutesFingerprint = routesFP;
                            var faresList = document.getElementById('routeFaresList');
                            if (faresList) {
                                if (data.routes.length > 0) {
                                    var faresHtml = '';
                                    data.routes.forEach(function (route) {
                                        faresHtml += '<div class="fare-item">'
                                            + '<div>'
                                            + '<div class="f-dest">' + route.origin + ' - ' + route.destination + '</div>'
                                            + '<div class="f-type"><span class="vehicle-type-chip vehicle-type-' + route.vehicle_type + '">' + route.vehicle_type + '</span></div>'
                                            + '</div>'
                                            + '<div class="f-price">₱' + Number(route.fare).toLocaleString('en-PH', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + '</div>'
                                            + '</div>';
                                    });
                                    faresList.innerHTML = faresHtml;
                                } else {
                                    faresList.innerHTML = '<p class="text-muted text-center py-3">No active routes.</p>';
                                }
                            }
                        }
                    }
                })
                .catch(function (error) {
                    console.error('Error fetching status:', error);
                })
                .finally(function () {
                    _fetchPending = false;
                });
        }

        function toggleMenu() {
            var menu = document.getElementById('navMenu');
            var overlay = document.getElementById('navOverlay');
            var toggleIcon = document.querySelector('.mobile-toggle i');
            var toggleBtn = document.querySelector('.mobile-toggle');
            var closeBtn = document.getElementById('mobileMenuClose');

            if (!menu) return;

            menu.classList.toggle('open');
            if (overlay) overlay.classList.toggle('active');

            if (menu.classList.contains('open')) {
                if (toggleIcon) toggleIcon.classList.replace('fa-bars', 'fa-times');
                if (toggleBtn) toggleBtn.style.visibility = 'hidden';
                if (closeBtn) closeBtn.classList.add('active');
                document.body.style.overflow = 'hidden';
            } else {
                if (toggleIcon) toggleIcon.classList.replace('fa-times', 'fa-bars');
                if (toggleBtn) toggleBtn.style.visibility = 'visible';
                if (closeBtn) closeBtn.classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        // Local filtering for departure list
        var deptSearch = document.getElementById('deptSearch');
        if (deptSearch) {
            deptSearch.addEventListener('input', function () {
                var query = this.value.toLowerCase();
                var depts = document.querySelectorAll('#deptList .fare-item');
                depts.forEach(function (dept) {
                    var text = dept.innerText.toLowerCase();
                    dept.style.display = text.includes(query) ? 'flex' : 'none';
                });
            });
        }

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

        // Initialize real-time sync (polling + WebSocket)
        // Polls every 5s as fallback, WebSocket provides instant updates
        QueueSync.init({
            pollInterval: 3000,
            customRefresh: fetchStatus,
            customWSHandler: function () { fetchStatus(); }
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
