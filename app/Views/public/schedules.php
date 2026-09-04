<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Schedules - Palompon Transit</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= base_url('images/9HFScgVg_400x400.png') ?>">
    <link rel="shortcut icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('apple-touch-icon.png') ?>">

    <!-- Modern Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap Icons (Fallback) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/design-system.css') ?>?v=3.2">
    <link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>?v=3.2">
    <link rel="stylesheet" href="<?= base_url('assets/css/responsive.css') ?>?v=3.2">
    <?= vehicle_type_colors_css() ?>

    <style>
        :root {
            --primary: #B71C1C;
            --primary-dark: #8B0000;
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

        /* --- Main Content --- */
        .container {
            width: 90%;
            max-width: 1400px;
            margin: 25px auto 40px !important;
            position: relative;
            z-index: 20;
        }

        /* --- Filter Box --- */
        .filter-box {
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            z-index: 100;
            overflow: visible !important;
        }

        .filter-form {
            display: grid;
            grid-template-columns: 1fr 1.5fr auto;
            gap: 20px;
            align-items: end;
            position: relative;
            z-index: 100;
            overflow: visible !important;
        }

        .form-group {
            position: relative;
            z-index: 100;
            overflow: visible !important;
        }

        .form-group label {
            display: block;
            font-weight: 700;
            font-size: 13px;
            margin-bottom: 8px;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group select,
        .form-group input,
        .form-group .autocomplete-wrapper input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            font-size: 15px;
            color: #0f172a;
            font-weight: 500;
            background: #ffffff;
            cursor: pointer;
            transition: var(--transition);
            outline: none;
        }

        .form-group input::placeholder,
        .form-group .autocomplete-wrapper input::placeholder {
            color: #64748b !important;
            opacity: 1 !important;
            font-weight: 500;
        }

        .form-group select:focus,
        .form-group input:focus,
        .form-group .autocomplete-wrapper input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.15);
            background: white;
        }

        .filter-btn {
            background: #B71C1C;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
        }

        .filter-btn:hover {
            transform: translateY(-2px) scale(1.02);
            background: #8B0000;
            box-shadow: var(--shadow-md);
        }

        .active-filters {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .filter-badge {
            background: #e3f2fd;
            color: var(--primary);
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
        }

        .clear-btn {
            background: none;
            border: 1px solid #e2e8f0;
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 13px;
            color: var(--text-muted);
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: var(--transition);
        }

        .clear-btn:hover {
            background: #f1f5f9;
            color: var(--text-main);
            border-color: var(--text-muted);
            transform: translateY(-1px);
        }

        /* --- Schedule Card/Table --- */
        .schedule-card {
            background: white;
            border-radius: 20px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            box-shadow: var(--shadow-md);
            border: 1px solid #edf2f7;
        }

        .card-header {
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            border-bottom: 1px solid #eee;
        }

        .card-header h3 {
            font-size: 20px;
            font-weight: 800;
            color: #000000;
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 0;
        }

        .count-badge {
            background: #e3f2fd;
            color: var(--primary);
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
        }

        .schedule-table {
            width: 100%;
            border-collapse: collapse;
        }

        .schedule-table thead {
            background: #f8fafc;
        }

        .schedule-table th {
            padding: 15px 25px;
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .schedule-table td {
            padding: 20px 25px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
            color: var(--text-main);
        }

        .schedule-table tbody tr {
            transition: var(--transition);
        }

        .schedule-table tbody tr:hover {
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

        .filter-box {
            animation: fadeInUp 0.5s ease-out both;
        }

        .schedule-card {
            animation: fadeInUp 0.5s ease-out both;
            animation-delay: 0.15s;
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

        .plate-number {
            font-weight: 700;
            color: var(--text-main);
            font-family: monospace;
            font-size: 15px;
            background: #f1f5f9;
            padding: 4px 8px;
            border-radius: 6px;
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
            color: var(--text-muted);
            font-size: 12px;
        }

        .type-badge {
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .van-badge {
            background: #e3f2fd;
            color: #1565c0;
        }

        .jeepney-badge {
            background: #fff8e1;
            color: #e65100;
        }

        .minibus-badge {
            background: #ede9fe;
            color: #6d28d9;
        }

        .route-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-badge {
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-scheduled {
            background: #e3f2fd;
            color: #1565c0;
        }

        .status-waiting {
            background: #fff8e1;
            color: #ef6c00;
        }

        .status-boarding {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-departed {
            background: #f1f5f9;
            color: #64748b;
        }

        .status-canceled {
            background: #ffebee;
            color: #c62828;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 60px;
            color: #cbd5e0;
            margin-bottom: 20px;
        }

        .empty-state h4 {
            font-size: 20px;
            color: var(--text-main);
            margin-bottom: 10px;
            font-weight: 700;
        }

        .empty-state p {
            color: var(--text-muted);
        }



        /* --- Responsive Queries --- */
        @media (max-width: 768px) {
            header { padding: 10px 5%; min-height: 65px; border-bottom: 1px solid #eee; }
            .logo { width: 40px; height: 40px; border-radius: 8px; }
            .logo-text h1 { font-size: 15px; letter-spacing: -0.2px; }
            .logo-text p { font-size: 8px; }

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

            .filter-box {
                padding: 15px;
                margin-top: 0;
                margin-bottom: 20px;
            }

            .filter-form {
                grid-template-columns: 1fr;
                gap: 15px;
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

            .schedule-table thead {
                display: none;
            }

            .schedule-table,
            .schedule-table tbody,
            .schedule-table tr,
            .schedule-table td {
                display: block;
                width: 100%;
            }

            .schedule-table tr {
                padding: 15px;
                border: 1px solid #edf2f7;
                border-radius: 15px;
                margin-bottom: 15px;
                background: white;
                transition: var(--transition);
            }

            .schedule-table tr:hover {
                transform: translateY(-3px);
                box-shadow: var(--shadow-md);
                border-color: var(--primary);
            }

            .schedule-table td {
                padding: 10px 0;
                border: none;
                display: flex;
                justify-content: space-between;
                align-items: center;
                text-align: right;
            }

            .schedule-table td::before {
                content: attr(data-label);
                font-weight: 700;
                color: var(--text-muted);
                font-size: 12px;
                text-transform: uppercase;
                text-align: left;
            }

        }

        /* --- Route Filter Capsule Bar (matching screenshot) --- */
        .route-filter-wrapper {
            margin-bottom: 24px;
            background: white;
            padding: 16px 20px;
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            border: 1px solid #edf2f7;
            animation: fadeInUp 0.5s ease-out both;
        }

        .route-filter-label {
            font-size: 12.5px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .route-filter-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            overflow-x: auto;
            padding: 4px 2px 8px 2px;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }

        .route-filter-bar::-webkit-scrollbar {
            display: none;
        }

        .route-chip {
            padding: 6px 14px;
            border-radius: 20px;
            border: 1.5px solid #e2e8f0;
            background: #ffffff;
            color: #334155;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            font-family: inherit;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            user-select: none;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        }

        .route-chip:hover {
            border-color: #D62828;
            color: #D62828;
            background: rgba(214, 40, 40, 0.06);
            transform: translateY(-1px);
        }

        .route-chip.active {
            background: #000000 !important;
            border-color: #000000 !important;
            color: #ffffff !important;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.25);
            transform: translateY(0);
        }

        .chip-count {
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
        }

        .route-chip.active .chip-count {
            background: rgba(255, 255, 255, 0.25);
            color: #ffffff;
        }

        /* --- Compact Mobile (≤ 420px) --- */
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
                margin: 14px auto 24px !important;
                padding: 0 !important;
            }
            .search-container {
                max-width: 100% !important;
                padding: 0 2px !important;
            }
            .search-bar {
                padding: 4px 6px !important;
                border-radius: 30px !important;
            }
            .search-bar input {
                padding: 8px 10px !important;
                font-size: 12px !important;
            }
            .search-bar button {
                padding: 0 12px !important;
                height: 36px !important;
                font-size: 11px !important;
            }
            .filter-box {
                padding: 10px !important;
                border-radius: 12px !important;
            }
            .route-filter-wrapper {
                padding: 10px !important;
                border-radius: 12px !important;
                margin-bottom: 12px !important;
            }
            .route-filter-label {
                font-size: 11px !important;
                margin-bottom: 6px !important;
            }
            .route-filter-bar {
                gap: 6px !important;
            }
            .route-chip {
                padding: 5px 12px !important;
                font-size: 11px !important;
                border-radius: 18px !important;
                gap: 5px !important;
            }
            .chip-count {
                font-size: 10px !important;
                min-width: 18px !important;
                height: 18px !important;
                padding: 0 4px !important;
            }
            .card-header {
                padding: 12px 10px !important;
                gap: 8px !important;
            }
            .card-header h3 {
                font-size: 14px !important;
            }
            .schedule-table tr {
                padding: 8px 10px !important;
                border-radius: 10px !important;
                margin-bottom: 10px !important;
            }
            .schedule-table td {
                padding: 5px 0 !important;
                font-size: 12px !important;
            }
            .schedule-table td::before {
                font-size: 10px !important;
            }
        }
    </style>
</head>

<body>

    <?= view('templates/guest_header', [
        'announcements'      => $announcements ?? [],
        'breadcrumb_current' => 'Schedules',
    ]) ?>

    <!-- Hero Section -->
    <section class="hero">
        <h2>Vehicle Schedules</h2>
        <p>View departure times, vehicle status, and route information for all available vehicle types.</p>

        <div class="search-container">
            <form action="<?= base_url('schedules') ?>" method="get" class="search-bar" id="scheduleSearchForm">
                <?php if (!empty($vehicle_type)): ?><input type="hidden" name="type" value="<?= esc($vehicle_type) ?>"><?php endif; ?>
                <?php if (!empty($destination)): ?><input type="hidden" name="destination" value="<?= esc($destination) ?>"><?php endif; ?>
                <input type="text" name="q" id="scheduleSearchInput" placeholder="Search by Route, Destination, or Plate Number..." value="<?= esc($search ?? '') ?>" autocomplete="off">
                <button type="submit">SEARCH</button>
            </form>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container">
        <!-- Filter Box -->
        <div class="filter-box">
            <form method="get" action="<?= base_url('schedules') ?>" class="filter-form">
                <div class="form-group">
                    <label>Vehicle Type</label>
                    <select name="type" id="vehicleTypeSelect">
                        <option value="">All Types</option>
                        <?php foreach (($vehicleTypes ?? []) as $vehicleType): ?>
                            <option value="<?= esc($vehicleType['slug']) ?>" <?= $vehicle_type === $vehicleType['slug'] ? 'selected' : '' ?>><?= esc($vehicleType['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Destination</label>
                    <select name="destination" id="destinationSelect">
                        <option value="">All Destinations</option>
                        <?php foreach ($all_destinations as $dest): ?>
                            <option value="<?= esc($dest) ?>" <?= $destination == $dest ? 'selected' : '' ?>><?= esc($dest) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="filter-btn"><i class="fas fa-filter"></i> Apply Filters</button>
            </form>
            <?php if ($vehicle_type || $destination): ?>
                <div class="active-filters">
                    <?php if ($vehicle_type): ?>
                        <?= vehicle_type_badge($vehicle_type) ?>
                    <?php else: ?>
                        <span class="filter-badge">All Types</span>
                    <?php endif; ?>
                    <?php if ($destination): ?><span class="filter-badge">Destination:
                            <?= esc($destination) ?></span><?php endif; ?>
                    <a href="<?= base_url('schedules') ?>" class="clear-btn"><i class="fas fa-times"></i> Clear Filters</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Quick Route Filter Bar (Pills) placed UNDER the filter box -->
        <div class="route-filter-wrapper">
            <div class="route-filter-label">
                <i class="fas fa-route" style="color: var(--primary);"></i> Route Destinations:
            </div>
            <div class="route-filter-bar" id="routeFilterBar">
                <button type="button" class="route-chip <?= empty($destination) ? 'active' : '' ?>" data-dest="all" onclick="selectRouteFilter('all', this)">
                    All Routes <span class="chip-count"><?= (int)($total_active_count ?? count($schedules)) ?></span>
                </button>
                <?php foreach (($active_dest_counts ?? []) as $dest => $cnt): ?>
                    <button type="button" class="route-chip <?= (strcasecmp($destination ?? '', $dest) === 0) ? 'active' : '' ?>" data-dest="<?= esc(strtolower($dest)) ?>" onclick="selectRouteFilter('<?= esc(strtolower($dest)) ?>', this)">
                        <?= strtoupper(esc($dest)) ?> <span class="chip-count"><?= (int)$cnt ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Schedule List -->
        <div class="schedule-card">
            <div class="card-header">
                <h3><i class="fas fa-list-alt"></i> Schedule Board</h3>
                <span class="count-badge" id="scheduleCount"><?= count($schedules) ?> Found</span>
            </div>
            <?php if (!empty($schedules)): ?>
                <table class="schedule-table">
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
                    <tbody id="scheduleTableBody">
                        <?php foreach ($schedules as $index => $schedule): ?>
                            <tr data-destination="<?= esc(strtolower($schedule['destination'] ?? '')) ?>" data-type="<?= esc(strtolower($schedule['vehicle_type'] ?? '')) ?>">
                                <td data-label="Queue #">
                                    <?php 
                                        $queueNum = !empty($schedule['position']) && (int)$schedule['position'] > 0 ? (int)$schedule['position'] : ($index + 1);
                                    ?>
                                    <span class="time-display">#<?= esc($queueNum) ?></span>
                                </td>
                                <td data-label="Plate"><span class="plate-number"><?= esc($schedule['plate_number']) ?></span>
                                </td>
                                <td data-label="Operator">
                                    <div class="operator-cell">
                                        <i class="fas fa-building text-muted"></i>
                                        <?= esc(!empty($schedule['operator_name']) ? $schedule['operator_name'] : ($schedule['driver_name'] ?? '—')) ?>
                                    </div>
                                </td>
                                <td data-label="Driver">
                                    <div class="driver-cell">
                                        <i class="fas fa-user-tie"></i>
                                        <?= esc($schedule['driver_name'] ?? '—') ?>
                                    </div>
                                </td>
                                <td data-label="Type">
                                    <?php
                                        $vType = $schedule['vehicle_type'] ?? '';
                                        $imgFile = vehicle_type_image($vType);
                                    ?>
                                    <div class="vehicle-type-cell">
                                        <span class="vehicle-type-icon <?= vehicle_type_class($vType) ?>">
                                            <img src="<?= base_url('images/' . $imgFile) ?>" alt="<?= vehicle_type_label($vType) ?>" style="height:36px; width:auto;" title="<?= vehicle_type_label($vType) ?>">
                                        </span>
                                        <?= vehicle_type_badge($vType) ?>
                                    </div>
                                </td>
                                <td data-label="Route">
                                    <div class="route-info">
                                        <span
                                            style="color: var(--text-muted); font-size: 13px;"><?= esc($schedule['origin']) ?></span>
                                        <i class="fas fa-arrow-right" style="color: var(--primary); font-size: 12px;"></i>
                                        <span
                                            style="font-weight: 700; color: var(--primary-dark);"><?= esc($schedule['destination']) ?></span>
                                    </div>
                                </td>
                                <td data-label="Est. Departure">
                                    <?php if ($schedule['status'] === 'departed' && $schedule['departure_time']): ?>
                                        <span class="time-display departed">
                                            <?= date('g:i A', strtotime($schedule['departure_time'])) ?>
                                        </span>
                                        <div style="font-size: 12.5px; color: var(--text-muted);">Departed</div>
                                    <?php elseif ($schedule['is_full']): ?>
                                        <span class="time-display full">
                                            FULL — Ready
                                        </span>
                                    <?php else: ?>
                                        <span class="time-display">
                                            <?= !empty($schedule['estimated_departure']) ? date('g:i A', strtotime($schedule['estimated_departure'])) : 'Waiting' ?>
                                        </span>
                                        <div style="font-size: 12.5px; color: var(--text-muted);">
                                            <?= $schedule['current_passengers'] ?>/<?= $schedule['capacity'] ?> passengers
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Status">
                                    <?php
                                    $statusClass = ['scheduled' => 'status-scheduled', 'waiting' => 'status-waiting', 'boarding' => 'status-boarding', 'departed' => 'status-departed', 'canceled' => 'status-canceled'];
                                    ?>
                                    <span class="status-badge <?= $statusClass[$schedule['status']] ?? 'status-departed' ?>">
                                        <?= strtoupper($schedule['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="far fa-calendar-times"></i>
                    <h4>No schedules available</h4>
                    <p><?= $vehicle_type || $destination ? 'Try adjusting your filters.' : 'No vehicles have been scheduled yet.' ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <?= $this->include('templates/guestfooter') ?>

        <!-- WebSocket is the fast path; polling remains the fallback. -->
        <script src="<?= base_url('js/ws-client.js?v=20260905') ?>"></script>
        <script src="<?= base_url('js/queue-sync.js?v=20260906') ?>"></script>
        <script>
        var currentType = '<?= esc($vehicle_type) ?>';
        var currentDest = '<?= esc($destination) ?>';
        var currentSearch = '<?= esc($search ?? '') ?>';
        var _fetchPending = false;

        function selectRouteFilter(dest, btn) {
            currentDest = (dest === 'all') ? '' : dest;

            // Update chip active classes
            document.querySelectorAll('#routeFilterBar .route-chip').forEach(function(c) {
                c.classList.remove('active');
            });
            if (btn) {
                btn.classList.add('active');
            }

            // Sync select dropdown
            var destSelect = document.getElementById('destinationSelect');
            if (destSelect) {
                var targetVal = (dest === 'all') ? '' : dest;
                for (var i = 0; i < destSelect.options.length; i++) {
                    if (targetVal === '' && destSelect.options[i].value === '') {
                        destSelect.selectedIndex = i;
                        break;
                    } else if (destSelect.options[i].value.toLowerCase() === targetVal.toLowerCase()) {
                        destSelect.selectedIndex = i;
                        break;
                    }
                }
            }

            // Instant client-side filter
            filterTableClientSide();

            // Fetch live update with new filter
            fetchSchedulesStatus();
        }

        function filterTableClientSide() {
            var rows = document.querySelectorAll('#scheduleTableBody tr');
            var visibleCount = 0;
            var q = (currentSearch || '').toLowerCase();
            rows.forEach(function(row) {
                var rowDest = (row.getAttribute('data-destination') || '').toLowerCase();
                var rowType = (row.getAttribute('data-type') || '').toLowerCase();
                var rowText = (row.innerText || '').toLowerCase();

                var matchesDest = (!currentDest || currentDest === 'all' || rowDest === currentDest.toLowerCase());
                var matchesType = (!currentType || currentType === 'all' || rowType === currentType.toLowerCase());
                var matchesSearch = (!q || rowText.indexOf(q) !== -1);

                if (matchesDest && matchesType && matchesSearch) {
                    row.style.removeProperty('display');
                    row.classList.remove('d-none');
                    visibleCount++;
                } else {
                    row.style.setProperty('display', 'none', 'important');
                    row.classList.add('d-none');
                }
            });

            var badge = document.getElementById('scheduleCount');
            if (badge) {
                badge.innerText = visibleCount + ' Found';
            }
        }

        function fetchSchedulesStatus() {
            if (_fetchPending) return;
            _fetchPending = true;

            var params = [];
            if (currentType) params.push('type=' + encodeURIComponent(currentType));
            if (currentDest && currentDest !== 'all') params.push('destination=' + encodeURIComponent(currentDest));
            if (currentSearch) params.push('q=' + encodeURIComponent(currentSearch));
            params.push('_=' + Date.now());
            var fetchUrl = '<?= base_url('schedules/status') ?>?' + params.join('&');

            fetch(fetchUrl)
            .then(function(response) {
                if (!response.ok) throw new Error('Network error');
                return response.json();
            })
            .then(function(data) {
                renderSchedules(data.schedules, data.vehicle_types || []);
                var badge = document.getElementById('scheduleCount');
                if (badge) badge.innerText = (data.count !== undefined ? data.count : data.schedules.length) + ' Found';
                syncDestinationOptions(data.destinations, data.active_dest_counts, data.total_active_count);
            })
            .catch(function(e) {
                console.error('Fetch error:', e);
            })
            .finally(function() {
                _fetchPending = false;
            });
        }

        function renderSchedules(schedules, vehicleTypes) {
            var tbody = document.getElementById('scheduleTableBody');
            var card = document.querySelector('.schedule-card');
            if (!card) return;

            if (schedules.length === 0) {
                card.innerHTML = '<div class="card-header"><h3><i class="fas fa-list-alt"></i> Schedule Board</h3><span class="count-badge" id="scheduleCount">0 Found</span></div>'
                    + '<div class="empty-state"><i class="far fa-calendar-times"></i><h4>No schedules available</h4><p>No active vehicles waiting or boarding right now.</p></div>';
                return;
            }

            if (!tbody) {
                card.innerHTML = '<div class="card-header"><h3><i class="fas fa-list-alt"></i> Schedule Board</h3><span class="count-badge" id="scheduleCount">' + schedules.length + ' Found</span></div>'
                    + '<table class="schedule-table"><thead><tr>'
                    + '<th>Queue #</th><th>Plate Number</th><th>Operator</th><th>Driver</th><th>Type</th><th>Route</th><th>Est. Departure</th><th>Status</th>'
                    + '</tr></thead><tbody id="scheduleTableBody"></tbody></table>';
                tbody = document.getElementById('scheduleTableBody');
            }

            var vehicleTypeMeta = {};
            (vehicleTypes || []).forEach(function(type) {
                vehicleTypeMeta[type.slug] = type;
                // Keep CSS-var icon boxes/chips in sync without reload.
                if (type.color && /^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/.test(type.color)) {
                    try {
                        document.documentElement.style.setProperty('--vehicle-' + type.slug, type.color);
                        document.documentElement.style.setProperty('--vehicle-' + type.slug + '-soft', type.color + '18');
                    } catch (e) { /* ignore */ }
                }
            });
            var statusClassMap = { 'scheduled': 'status-scheduled', 'waiting': 'status-waiting', 'boarding': 'status-boarding', 'departed': 'status-departed', 'canceled': 'status-canceled' };
            var html = '';
            schedules.forEach(function(s) {
                var typeMeta = vehicleTypeMeta[s.vehicle_type] || {};
                var imgFile = typeMeta.image || 'minibus.png';
                var typeLabel = typeMeta.name || s.vehicle_type.replace(/[_-]+/g, ' ').replace(/\b\w/g, function(char) { return char.toUpperCase(); });
                var statusClass = statusClassMap[s.status] || 'status-waiting';
                var dep = '';
                if (s.is_full) {
                    dep = '<span class="time-display full">FULL</span>';
                } else {
                    dep = '<span class="time-display">' + (s.estimated_departure_formatted || 'Waiting') + '</span><div style="font-size:12.5px;color:var(--text-muted)">' + s.current_passengers + '/' + s.capacity + ' passengers</div>';
                }
                function escHtml(v) {
                    if (v === null || v === undefined) return '';
                    return String(v).replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; });
                }
                var opDisplay = s.operator_name ? escHtml(s.operator_name) : (s.driver_name ? escHtml(s.driver_name) : '—');
                var typeSlug = String(s.vehicle_type || '').toLowerCase();
                var typeColor = typeMeta.color || '#1565c0';
                html += '<tr data-destination="' + escHtml((s.destination || '').toLowerCase()) + '" data-type="' + escHtml(typeSlug) + '">' +
                    '<td data-label="Queue #"><span class="time-display">#' + s.position + '</span></td>' +
                    '<td data-label="Plate"><span class="plate-number">' + escHtml(s.plate_number) + '</span></td>' +
                    '<td data-label="Operator"><div class="operator-cell"><i class="fas fa-building text-muted"></i>' + opDisplay + '</div></td>' +
                    '<td data-label="Driver"><div class="driver-cell"><i class="fas fa-user-tie"></i>' + (s.driver_name ? escHtml(s.driver_name) : '—') + '</div></td>' +
                    '<td data-label="Type"><div class="vehicle-type-cell"><span class="vehicle-type-icon vehicle-type-' + escHtml(typeSlug) + '"><img src="<?= base_url('images/') ?>' + imgFile + '" style="height:36px;width:auto"></span><span class="vehicle-type-chip vehicle-type-' + escHtml(typeSlug) + '" data-vtype="' + escHtml(typeSlug) + '" style="background: var(--vehicle-' + escHtml(typeSlug) + '-soft, ' + typeColor + '18) !important; color: var(--vehicle-' + escHtml(typeSlug) + ', ' + typeColor + ') !important; border: 1.5px solid var(--vehicle-' + escHtml(typeSlug) + ', ' + typeColor + ') !important;">' + escHtml(typeLabel) + '</span></div></td>' +
                    '<td data-label="Route"><div class="route-info"><span style="color:var(--text-muted);font-size:13px">' + s.origin + '</span><i class="fas fa-arrow-right" style="color:var(--primary);font-size:12px"></i><span style="font-weight:700;color:var(--primary-dark)">' + s.destination + '</span></div></td>' +
                    '<td data-label="Est. Departure">' + dep + '</td>' +
                    '<td data-label="Status"><span class="status-badge ' + statusClass + '">' + s.status.toUpperCase() + '</span></td>' +
                '</tr>';
            });
            tbody.innerHTML = html;
        }

        // Rebuild Destination filter options and route chips when route list changes
        function syncDestinationOptions(destinations, activeCounts, totalCount) {
            var sel = document.getElementById('destinationSelect');
            if (sel && Array.isArray(destinations) && document.activeElement !== sel) {
                var desired = [''].concat(destinations);
                var existing = Array.prototype.map.call(sel.options, function (o) { return o.value; });
                var same = existing.length === desired.length && existing.every(function (v, i) { return v === desired[i]; });
                if (!same) {
                    var current = sel.value;
                    sel.innerHTML = '';
                    var all = document.createElement('option');
                    all.value = '';
                    all.textContent = 'All Destinations';
                    sel.appendChild(all);
                    destinations.forEach(function (d) {
                        var o = document.createElement('option');
                        o.value = d;
                        o.textContent = d;
                        sel.appendChild(o);
                    });
                    sel.value = current;
                }
            }

            // Sync Route Filter Bar chips — only show active routes with vehicles & include count badges
            var bar = document.getElementById('routeFilterBar');
            if (bar && activeCounts && typeof activeCounts === 'object') {
                var activeDestNames = Object.keys(activeCounts).sort();
                var activeDest = currentDest ? currentDest.toLowerCase() : 'all';
                var totCount = (totalCount !== undefined) ? totalCount : 0;

                var chipHtml = '<button type="button" class="route-chip' + (activeDest === 'all' ? ' active' : '') + '" data-dest="all" onclick="selectRouteFilter(\'all\', this)">'
                    + 'All Routes <span class="chip-count">' + totCount + '</span></button>';

                activeDestNames.forEach(function(d) {
                    var isAct = (activeDest === d.toLowerCase());
                    var cnt = activeCounts[d] || 0;
                    chipHtml += '<button type="button" class="route-chip' + (isAct ? ' active' : '') + '" data-dest="' + d.toLowerCase() + '" onclick="selectRouteFilter(\'' + d.toLowerCase() + '\', this)">'
                        + d.toUpperCase() + ' <span class="chip-count">' + cnt + '</span></button>';
                });
                bar.innerHTML = chipHtml;
            }
        }

        // Initialize real-time sync & search input listeners
        document.addEventListener('DOMContentLoaded', function() {
            var searchInput = document.getElementById('scheduleSearchInput');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    currentSearch = this.value.trim();
                    filterTableClientSide();
                });
            }

            QueueSync.init({
                pollInterval:  3000,
                customRefresh: fetchSchedulesStatus,
                customWSHandler: fetchSchedulesStatus
            });
            fetchSchedulesStatus();
        });
    </script>
</body>
</html>
