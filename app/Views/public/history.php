<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Departure History') ?> – Palompon Transit</title>
    <meta name="description" content="View all completed vehicle departures and search by plate number, destination, or driver.">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/design-system.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/responsive.css') ?>">
    <style>
        /* ===== DESIGN TOKENS ===== */
        :root {
            --primary:       #1E40AF;
            --primary-dark:  #1E3A8A;
            --accent:        #FFA726;
            --accent-dark:   #F57C00;
            --success:       #43a047;
            --text-main:     #2c3e50;
            --text-muted:    #66788a;
            --bg-body:       #f5f7fa;
            --white:         #ffffff;
            --shadow-sm:  0 2px 10px rgba(0,0,0,0.05);
            --shadow-md:  0 4px 15px rgba(0,0,0,0.08);
            --shadow-lg:  0 8px 30px rgba(0,0,0,0.12);
            --transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
            --radius-card: 20px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg-body);
            color: var(--text-main);
            overflow-x: clip;
            line-height: 1.6;
        }

        /* ===== HERO ===== */
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
        .hero > * { position: relative; z-index: 2; }

        .hero h2 {
            font-size: 38px;
            font-weight: 800;
            color: #000000;
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

        /* ===== SEARCH BAR ===== */
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

        /* Active filter badge */
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

        /* ===== MAIN CONTAINER ===== */
        .hist-content {
            width: 90%;
            max-width: 1300px;
            margin: -30px auto 40px !important;
            position: relative;
            z-index: 10;
        }

        /* Back button row */
        .back-row {
            margin-bottom: 24px;
        }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: white;
            color: #1565c0;
            border: 2px solid rgba(21,101,192,0.2);
            padding: 10px 22px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .btn-back i {
            transition: transform var(--transition);
        }
        .btn-back:hover {
            background: #1565c0;
            color: white;
            border-color: #1565c0;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        .btn-back:hover i {
            transform: translateX(-4px);
        }
        .btn-back:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(21, 101, 192, 0.25), var(--shadow-sm);
        }

        /* ===== TABLE CARD ===== */
        .table-card {
            background: white;
            border-radius: var(--radius-card);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }

        .table-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 22px 28px;
            border-bottom: 1px solid #edf2f7;
        }
        .table-card-header h2 {
            font-size: 20px;
            font-weight: 800;
            color: #000000;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .table-card-header h2 i { color: var(--success); }

        .record-count {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            background: #f1f5f9;
            padding: 5px 14px;
            border-radius: 30px;
        }

        /* Table itself */
        .table-wrap { overflow-x: hidden; }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead th {
            background: #f8fafc;
            padding: 14px 20px;
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            white-space: nowrap;
            border-bottom: 1px solid #edf2f7;
        }
        thead th:first-child { padding-left: 28px; }

        tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.18s ease;
        }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: #fafbff; }

        tbody td {
            padding: 16px 20px;
            vertical-align: middle;
        }
        tbody td:first-child { padding-left: 28px; }

        /* ===== SCHEDULE-STYLE TABLE (shared visual language with /schedules) ===== */
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
            vertical-align: middle;
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

        .table-card {
            animation: fadeInUp 0.5s ease-out both;
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

        .route-info {
            display: flex;
            align-items: center;
            gap: 10px;
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
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        .status-departed {
            background: #f1f5f9;
            color: #64748b;
        }
        .status-departed i { color: #FF9800; }

        /* Empty state */
        .empty-state {
            padding: 80px 20px;
            text-align: center;
        }
        .empty-icon {
            width: 80px; height: 80px;
            background: #f1f5f9;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 32px; color: var(--text-muted);
            margin: 0 auto 20px;
        }
        .empty-state h3 { font-size: 20px; font-weight: 700; color: var(--text-main); margin-bottom: 8px; }
        .empty-state p  { color: var(--text-muted); font-size: 14px; }

        /* ===== PAGINATION ===== */
        .pager-wrap {
            padding: 20px 28px;
            border-top: 1px solid #edf2f7;
            display: flex;
            justify-content: center;
        }
        /* CI4 default pager outputs: nav > ul.pagination > li > a */
        .pager-wrap nav { display: flex; justify-content: center; }
        .pager-wrap .pagination { margin: 0; gap: 6px; display: flex; flex-wrap: wrap; list-style: none; padding: 0; }
        .pager-wrap .pagination li a {
            display: inline-block;
            border-radius: 10px;
            font-weight: 600;
            font-family: 'Outfit', sans-serif;
            color: var(--primary);
            border: 1px solid #e2e8f0;
            padding: 8px 14px;
            text-decoration: none;
            transition: var(--transition);
        }
        .pager-wrap .pagination li.active a {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            color: white;
        }
        .pager-wrap .pagination li a:hover {
            background: #e3f2fd;
            border-color: var(--primary);
        }

        /* ===== MOBILE CARD VIEW (≤ 768px) ===== */
        .mobile-cards { display: none; }

        /* Hide pager when only 1 page (single active link, no prev/next) */
        .pager-wrap--single-page { display: none; }

        @media (max-width: 768px) {
            .hero { padding: 50px 5% 80px; }

            .search-bar { flex-direction: column; border-radius: 18px; padding: 10px; gap: 8px; }
            .search-bar input { padding: 12px 16px; text-align: center; }
            .search-bar button { padding: 12px; border-radius: 12px; width: 100%; }

            .hist-content { width: 94%; margin-top: -30px !important; }

            .table-wrap { overflow-x: auto; }

            .schedule-table,
            .schedule-table tbody,
            .schedule-table tr,
            .schedule-table td {
                display: block;
                width: 100%;
            }

            .schedule-table thead {
                display: none;
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
                border-bottom: 1px solid #f1f5f9;
            }

            .schedule-table td:last-child {
                border-bottom: none;
            }

            .schedule-table td::before {
                content: attr(data-label);
                font-weight: 700;
                color: var(--text-muted);
                font-size: 12px;
                text-transform: uppercase;
                text-align: left;
            }

            .table-card-header { flex-direction: column; gap: 10px; align-items: flex-start; }
        }

        @media (max-width: 420px) {
            .btn-back span { display: none; }
        }
    </style>
</head>
<body>

<?= view('templates/guest_header', [
    'announcements' => $announcements ?? [],
    'breadcrumb_current' => 'Departure History',
]) ?>

<!-- ===== HERO ===== -->
<section class="hero">
    <h2>Departure History</h2>
    <p>Search or browse all completed vehicle departures from our terminal.</p>

    <div class="search-container">
        <form method="get" action="<?= base_url('history') ?>" class="search-bar">
            <input
                type="text"
                name="q"
                placeholder="Search by Plate Number, Destination, or Driver..."
                value="<?= esc($search ?? '') ?>"
                autocomplete="off"
            >
            <button type="submit">SEARCH</button>
        </form>
    </div>
</section>

<!-- ===== MAIN CONTENT ===== -->
<div class="hist-content">
    <?php if (!empty($search)): ?>
        <div style="margin-bottom: 20px;">
            <span class="filter-badge">
                <i class="fas fa-filter"></i>
                Results for: <strong><?= esc($search) ?></strong>
                <a href="<?= base_url('history') ?>" title="Clear filter">×</a>
            </span>
        </div>
    <?php endif; ?>


    <!-- Table Card -->
    <div class="table-card">
        <div class="table-card-header">
            <h2>
                All Past Departures
            </h2>
            <?php if (!empty($departures)): ?>
                <span class="record-count">
                    <?= count($departures) ?> record<?= count($departures) !== 1 ? 's' : '' ?> found
                </span>
            <?php endif; ?>
        </div>

        <!-- Table content -->
        <div class="table-wrap">
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th>Plate Number</th>
                        <th>Driver / Operator</th>
                        <th>Type</th>
                        <th>Route</th>
                        <th>Departure Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($departures)): ?>
                        <?php foreach ($departures as $item): ?>
                            <tr>
                                <td data-label="Plate Number">
                                    <span class="plate-number"><?= esc($item['plate_number']) ?></span>
                                </td>
                                <td data-label="Driver / Operator">
                                    <div class="driver-cell">
                                        <i class="fas fa-user-tie"></i>
                                        <span><?= esc($item['driver_name'] ?? '—') ?></span>
                                    </div>
                                    <?php
                                        $opName = $item['operator_name'] ?: ($item['owner_name'] ?? '');
                                        $drName = $item['driver_name'] ?? '';
                                        if (!empty($opName) && strtolower(trim($opName)) !== strtolower(trim($drName))):
                                    ?>
                                        <div class="text-muted" style="font-size: 11.5px; margin-top: 2px; color: #64748b;">
                                            <i class="fas fa-building me-1" style="font-size: 10px;"></i><?= esc($opName) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Type">
                                    <?php
                                        $vType = $item['vehicle_type'] ?? '';
                                        $imgFile = vehicle_type_image($vType);
                                    ?>
                                    <div class="vehicle-type-cell">
                                        <span class="vehicle-type-icon <?= vehicle_type_class($vType) ?>">
                                            <img src="<?= base_url('images/' . $imgFile) ?>" alt="<?= esc($vType) ?>" style="height:36px;width:auto;" title="<?= vehicle_type_label($vType) ?>">
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
                                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                                        <?= date('M d, Y', strtotime($item['departure_time'])) ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <i class="fas fa-history"></i>
                                    </div>
                                    <h3>No Departures Found</h3>
                                    <p><?= !empty($search) ? 'Try a different search term.' : 'No departure history has been recorded yet.' ?></p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>



        <!-- Pagination (hidden when only 1 page) -->
        <?php if ($pager && $pager->getPageCount() > 1): ?>
            <div class="pager-wrap">
                <?= $pager->links() ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<?= $this->include('templates/guestfooter') ?>
<style>#confirmActionModal { display: none !important; }</style>

</body>
</html>
