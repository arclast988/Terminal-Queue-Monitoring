<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Departure History') ?> – Palompon Transit</title>
    <meta name="description" content="View all completed vehicle departures and search by plate number, destination, or driver.">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/responsive.css') ?>">
    <style>
        /* ===== DESIGN TOKENS ===== */
        :root {
            --primary:       #1E40AF;
            --primary-dark:  #1E3A8A;
            --accent:        #FFB74D;
            --accent-dark:   #FFA726;
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
        .hist-hero {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            padding: 70px 5% 100px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .hist-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(circle at 15% 50%, rgba(255,255,255,0.22) 0%, transparent 40%),
                radial-gradient(circle at 85% 20%, rgba(255,255,255,0.18) 0%, transparent 40%);
        }
        .hist-hero > * { position: relative; z-index: 1; }

        .hist-hero h1 {
            font-size: clamp(28px, 5vw, 50px);
            font-weight: 800;
            color: #0d47a1;
            margin-bottom: 12px;
            letter-spacing: -1px;
        }
        .hist-hero h1 i { margin-right: 12px; }

        .hist-hero p {
            font-size: clamp(14px, 2vw, 18px);
            color: #0d47a1;
            opacity: 0.8;
            max-width: 640px;
            margin: 0 auto 36px;
        }

        /* ===== SEARCH BAR ===== */
        .search-container { max-width: 820px; margin: 0 auto; }

        .search-bar {
            background: white;
            padding: 8px;
            border-radius: 50px;
            display: flex;
            box-shadow: var(--shadow-lg);
            gap: 0;
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
            margin: -50px auto 60px;
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
        .btn-back:hover {
            background: #1565c0;
            color: white;
            border-color: #1565c0;
            transform: translateX(-3px);
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
            color: #0d47a1;
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
        .table-wrap { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead th {
            background: #f8fafc;
            padding: 14px 20px;
            text-align: left;
            font-size: 11px;
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
        .schedule-table tr:hover { background: #fafbfc; }

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

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 11px;
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
        /* Override CI4 Bootstrap pager links */
        .pager-wrap .pagination { margin: 0; gap: 6px; display: flex; flex-wrap: wrap; list-style: none; padding: 0; }
        .pager-wrap .pagination .page-item .page-link {
            border-radius: 10px !important;
            font-weight: 600;
            font-family: 'Outfit', sans-serif;
            color: var(--primary);
            border: 1px solid #e2e8f0;
            padding: 8px 14px;
        }
        .pager-wrap .pagination .page-item.active .page-link {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            color: white;
        }
        .pager-wrap .pagination .page-item .page-link:hover {
            background: #e3f2fd;
            border-color: var(--primary);
        }

        /* ===== MOBILE CARD VIEW (≤ 640px) ===== */
        .mobile-cards { display: none; }

        @media (max-width: 640px) {
            .hist-hero { padding: 50px 5% 80px; }

            .search-bar { flex-direction: column; border-radius: 18px; padding: 10px; gap: 8px; }
            .search-bar input { padding: 12px 16px; text-align: center; }
            .search-bar button { padding: 12px; border-radius: 12px; width: 100%; }

            .hist-content { width: 94%; margin-top: -40px; }

            /* Hide desktop table, show mobile cards */
            .desktop-table { display: none; }
            .mobile-cards  { display: flex; flex-direction: column; gap: 0; }

            .mob-row {
                padding: 16px 20px;
                border-bottom: 1px solid #f1f5f9;
                transition: background 0.15s;
            }
            .mob-row:last-child { border-bottom: none; }
            .mob-row:hover { background: #fafbff; }

            .mob-top {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 10px;
            }
            .mob-bottom {
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 8px;
            }
            .mob-driver {
                font-size: 13px;
                color: var(--text-muted);
                display: flex;
                align-items: center;
                gap: 5px;
                margin-bottom: 4px;
                font-weight: 600;
            }
            .mob-driver i { font-size: 12px; }

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
<section class="hist-hero">
    <h1><i class="fas fa-history"></i> Departure History</h1>
    <p>Search or browse all completed vehicle departures from our terminal.</p>

    <div class="search-container">
        <form method="get" action="<?= base_url('history') ?>">
            <div class="search-bar">
                <input
                    type="text"
                    name="q"
                    placeholder="Search by Plate Number, Destination, or Driver..."
                    value="<?= esc($search ?? '') ?>"
                    autocomplete="off"
                >
                <button type="submit">
                    <i class="fas fa-search" style="margin-right:7px;"></i>SEARCH
                </button>
            </div>
        </form>

        <?php if (!empty($search)): ?>
            <div>
                <span class="filter-badge">
                    <i class="fas fa-filter"></i>
                    Results for: <strong><?= esc($search) ?></strong>
                    <a href="<?= base_url('history') ?>" title="Clear filter">×</a>
                </span>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ===== MAIN CONTENT ===== -->
<div class="hist-content">

    <!-- Back button -->
    <div class="back-row">
        <a href="<?= base_url('guest') ?>" class="btn-back">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Live Monitor</span>
        </a>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-card-header">
            <h2>
                <i class="fas fa-check-circle"></i>
                All Past Departures
            </h2>
            <?php if (!empty($departures)): ?>
                <span class="record-count">
                    <?= count($departures) ?> record<?= count($departures) !== 1 ? 's' : '' ?> found
                </span>
            <?php endif; ?>
        </div>

        <!-- ===== DESKTOP TABLE ===== -->
        <div class="table-wrap desktop-table">
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th>Plate Number</th>
                        <th>Driver</th>
                        <th>Type</th>
                        <th>Route</th>
                        <th>Departure Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($departures)): ?>
                        <?php foreach ($departures as $item): ?>
                            <tr>
                                <td data-label="Plate">
                                    <span class="plate-number"><?= esc($item['plate_number']) ?></span>
                                </td>
                                <td data-label="Driver">
                                    <div class="driver-cell">
                                        <i class="fas fa-user-tie"></i>
                                        <?= esc($item['driver_name'] ?? '—') ?>
                                    </div>
                                </td>
                                <td data-label="Type">
                                    <?php
                                        $imgMap = ['van' => 'van.png', 'jeepney' => 'jeep.png', 'minibus' => 'minibus.png'];
                                        $vType = strtolower($item['vehicle_type'] ?? '');
                                        $imgFile = $imgMap[$vType] ?? 'van.png';
                                    ?>
                                    <span class="vehicle-type-icon <?= vehicle_type_class($vType) ?>">
                                        <img src="<?= base_url('images/' . $imgFile) ?>" alt="<?= esc($vType) ?>" style="height:36px;width:auto;" title="<?= vehicle_type_label($vType) ?>">
                                    </span>
                                    <div class="mt-1"><?= vehicle_type_badge($vType) ?></div>
                                </td>
                                <td data-label="Route">
                                    <div class="route-info">
                                        <span style="color: var(--text-muted); font-size: 13px;"><?= esc($item['origin']) ?></span>
                                        <i class="fas fa-arrow-right" style="color: var(--primary); font-size: 12px;"></i>
                                        <span style="font-weight: 700; color: var(--primary-dark);"><?= esc($item['destination']) ?></span>
                                    </div>
                                </td>
                                <td data-label="Departure Time">
                                    <span class="status-badge status-departed">
                                        <i class="fas fa-clock"></i>
                                        <?= date('M d, Y h:i A', strtotime($item['departure_time'])) ?>
                                    </span>
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

        <!-- ===== MOBILE CARD LIST ===== -->
        <div class="mobile-cards">
            <?php if (!empty($departures)): ?>
                <?php foreach ($departures as $item): ?>
                    <?php
                        $imgMap = ['van' => 'van.png', 'jeepney' => 'jeep.png', 'minibus' => 'minibus.png'];
                        $vType = strtolower($item['vehicle_type'] ?? '');
                        $imgFile = $imgMap[$vType] ?? 'van.png';
                    ?>
                    <div class="mob-row">
                        <div class="mob-top">
                            <div>
                                <div class="mob-driver">
                                    <i class="fas fa-user-tie"></i>
                                    <?= esc($item['driver_name'] ?? '—') ?>
                                </div>
                                <span class="plate-number"><?= esc($item['plate_number']) ?></span>
                            </div>
                            <span class="status-badge status-departed" style="font-size:11px;">
                                <i class="fas fa-clock"></i>
                                <?= date('h:i A', strtotime($item['departure_time'])) ?>
                            </span>
                        </div>
                        <div class="mob-bottom">
                            <div class="route-info">
                                <span class="vehicle-type-icon <?= vehicle_type_class($vType) ?>" style="padding:0.2rem;border-radius:8px;">
                                    <img src="<?= base_url('images/' . $imgFile) ?>" alt="<?= esc($vType) ?>" style="height:22px;width:auto;">
                                </span>
                                <span style="color: var(--text-muted); font-size: 12px;"><?= esc($item['origin']) ?></span>
                                <i class="fas fa-arrow-right" style="color: var(--primary); font-size: 11px;"></i>
                                <span style="font-weight: 700; color: var(--primary-dark); font-size: 13px;"><?= esc($item['destination']) ?></span>
                            </div>
                            <span style="font-size:11px;color:var(--text-muted);">
                                <?= date('M d, Y', strtotime($item['departure_time'])) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-history"></i></div>
                    <h3>No Departures Found</h3>
                    <p><?= !empty($search) ? 'Try a different search term.' : 'No departure history has been recorded yet.' ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pager): ?>
            <div class="pager-wrap">
                <?= $pager->links() ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<?= view('templates/footer') ?>

</body>
</html>
