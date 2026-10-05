<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <?= view('partials/font_assets', ['fontFamily' => 'Outfit', 'fontIcons' => true]) ?>
    <link rel="stylesheet" href="<?= app_asset_url('assets/vendor/fontawesome/css/all.min.css') ?>">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent !important;
        }

        button, .btn-toolbar, a {
            -webkit-tap-highlight-color: transparent !important;
            user-select: none;
            -webkit-user-select: none;
        }

        button:focus,
        button:active,
        .btn-toolbar:focus,
        .btn-toolbar:active,
        a:focus,
        a:active {
            outline: none !important;
            box-shadow: none !important;
            -webkit-tap-highlight-color: transparent !important;
        }

        body {
            font-family: 'Outfit', sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 30px 35px;
            background: #ffffff;
            line-height: 1.5;
            -webkit-tap-highlight-color: transparent !important;
        }

        /* Screen-only action toolbar */
        .print-toolbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #0f172a;
            color: #ffffff;
            padding: 12px 24px;
            margin: -30px -35px 25px -35px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            -webkit-tap-highlight-color: transparent !important;
        }

        .print-toolbar-title {
            font-weight: 700;
            font-size: 14.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .print-toolbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-toolbar {
            padding: 8px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.15s ease;
            -webkit-tap-highlight-color: transparent !important;
            user-select: none;
            -webkit-user-select: none;
            outline: none !important;
        }

        .btn-toolbar:focus,
        .btn-toolbar:active {
            outline: none !important;
            box-shadow: none !important;
        }

        .btn-toolbar-primary {
            background: #b71c1c;
            color: #ffffff;
        }

        .btn-toolbar-primary:hover,
        .btn-toolbar-primary:focus,
        .btn-toolbar-primary:active {
            background: #991b1b !important;
            outline: none !important;
            box-shadow: none !important;
        }

        .btn-toolbar-secondary {
            background: #334155;
            color: #ffffff;
        }

        .btn-toolbar-secondary:hover,
        .btn-toolbar-secondary:focus,
        .btn-toolbar-secondary:active {
            background: #475569 !important;
            outline: none !important;
            box-shadow: none !important;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2.5px solid #b71c1c;
            padding-bottom: 16px;
            margin-bottom: 20px;
            gap: 20px;
        }

        .terminal-brand-group {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .report-logo {
            width: 68px;
            height: 68px;
            object-fit: contain;
            flex-shrink: 0;
            border-radius: 10px;
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            padding: 4px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        }

        .terminal-info h1 {
            color: #b71c1c;
            margin: 0 0 4px 0;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
            text-transform: uppercase;
        }

        .terminal-info p {
            margin: 2px 0;
            color: #64748b;
            font-size: 12.5px;
            font-weight: 500;
        }

        .report-meta {
            text-align: right;
            min-width: 260px;
            flex-shrink: 0;
        }

        .report-meta h2 {
            margin: 0 0 4px 0;
            font-size: 18px;
            color: #0f172a;
            font-weight: 800;
        }

        .report-meta p {
            margin: 2px 0;
            color: #64748b;
            font-size: 12px;
            line-height: 1.4;
        }

        .filter-summary {
            background: #f8fafc;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 12.5px;
            border: 1px solid #e2e8f0;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 16px;
        }

        .filter-item {
            display: inline-flex;
            flex-direction: column;
            gap: 2px;
        }

        .filter-label {
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            font-size: 10.5px;
            letter-spacing: 0.5px;
        }

        .filter-value {
            font-weight: 700;
            color: #0f172a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        th {
            background: #f1f5f9;
            color: #334155;
            text-transform: uppercase;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.6px;
            text-align: left;
            padding: 10px 12px;
            border-bottom: 2px solid #cbd5e1;
        }

        td {
            padding: 11px 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 12.5px;
            vertical-align: middle;
        }

        tr:nth-child(even) td {
            background: #fafbfc;
        }

        .plate-number {
            font-family: 'Courier New', monospace;
            font-size: 13.5px;
            font-weight: 800;
            color: #0f172a;
            background: #f1f5f9;
            padding: 3px 8px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            display: inline-block;
        }

        .vehicle-type-chip {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            line-height: 1.15;
            text-transform: uppercase;
            letter-spacing: 0.35px;
            white-space: nowrap;
            border: 1px solid transparent;
        }

        .vehicle-type-jeepney {
            background: #e3f2fd;
            border-color: #1565c0;
            color: #1565c0;
        }

        .vehicle-type-van {
            background: #ffebee;
            border-color: #c62828;
            color: #c62828;
        }

        .vehicle-type-minibus {
            background: #e8f5e9;
            border-color: #2e7d32;
            color: #2e7d32;
        }

        .passenger-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 28px;
            padding: 3px 8px;
            border-radius: 12px;
            background: #f1f5f9;
            color: #1e293b;
            font-weight: 700;
            font-size: 12px;
            border: 1px solid #cbd5e1;
        }

        .status-badge {
            background-color: #059669;
            color: #ffffff;
            font-weight: 700;
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            letter-spacing: 0.3px;
        }

        .footer {
            margin-top: 30px;
            padding-top: 14px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11.5px;
            color: #64748b;
        }

        .signatory-section {
            margin-top: 35px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }

        .signatory-box {
            width: 220px;
            text-align: center;
        }

        .signatory-line {
            border-top: 1.5px solid #334155;
            margin-top: 40px;
            padding-top: 4px;
            font-weight: 700;
            font-size: 12px;
            color: #0f172a;
        }

        .signatory-title {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
        }

        /* Responsive Styles for Mobile & Tablets */
        .report-table-wrapper {
            width: 100%;
            margin-bottom: 20px;
        }

        .mobile-scroll-hint {
            display: none;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
            font-size: 11px;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 6px;
            margin-bottom: 8px;
        }

        @media screen and (max-width: 860px) {
            body {
                padding: 18px 16px 24px;
            }

            .print-toolbar {
                margin: -18px -16px 18px -16px;
                padding: 10px 16px;
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }

            .print-toolbar-title {
                justify-content: center;
                text-align: center;
                font-size: 13.5px;
            }

            .print-toolbar-actions {
                display: flex;
                width: 100%;
                gap: 8px;
            }

            .btn-toolbar {
                flex: 1;
                justify-content: center;
                padding: 9px 14px;
                font-size: 12.5px;
            }

            .header {
                flex-direction: column;
                align-items: stretch;
                gap: 14px;
                padding-bottom: 14px;
                margin-bottom: 16px;
            }

            .terminal-brand-group {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .report-logo {
                width: 56px;
                height: 56px;
            }

            .terminal-info h1 {
                font-size: 21px;
            }

            .report-meta {
                text-align: left;
                min-width: 0;
                width: 100%;
                background: #f8fafc;
                padding: 12px 14px;
                border-radius: 8px;
                border: 1px solid #e2e8f0;
            }

            .report-meta h2 {
                font-size: 16px;
            }

            .filter-summary {
                padding: 12px 14px;
                gap: 12px 16px;
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
                margin-bottom: 16px;
            }

            .report-table-wrapper {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                border: 1px solid #e2e8f0;
                border-radius: 10px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.05);
                background: #ffffff;
                scrollbar-width: thin;
                scrollbar-color: #cbd5e1 #f1f5f9;
            }

            .report-table-wrapper::-webkit-scrollbar {
                height: 6px;
            }
            .report-table-wrapper::-webkit-scrollbar-track {
                background: #f1f5f9;
                border-radius: 4px;
            }
            .report-table-wrapper::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 4px;
            }

            .mobile-scroll-hint {
                display: flex;
            }

            table {
                min-width: 720px;
            }

            th, td {
                padding: 9px 10px;
                font-size: 12px;
            }

            .signatory-section {
                margin-top: 24px;
                flex-direction: column;
                align-items: stretch;
                gap: 20px;
            }

            .signatory-box {
                width: 100%;
                max-width: 280px;
                margin: 0 auto;
            }

            .signatory-line {
                margin-top: 26px;
            }

            .footer {
                margin-top: 20px;
                padding-top: 12px;
                flex-direction: column;
                text-align: center;
                gap: 6px;
                font-size: 11px;
            }
        }

        @media screen and (max-width: 480px) {
            body {
                padding: 14px 12px 20px;
            }

            .print-toolbar {
                margin: -14px -12px 14px -12px;
                padding: 9px 12px;
            }

            .terminal-info h1 {
                font-size: 18px;
            }

            .terminal-info p {
                font-size: 11.5px;
            }

            .filter-summary {
                grid-template-columns: 1fr;
                gap: 10px;
            }
        }

        @media print {
            body {
                padding: 10mm 12mm;
            }
            .print-toolbar,
            .mobile-scroll-hint {
                display: none !important;
            }
            .report-table-wrapper {
                overflow: visible !important;
                border: none !important;
                box-shadow: none !important;
                margin-bottom: 0 !important;
            }
            table {
                min-width: 0 !important;
                width: 100% !important;
            }
            .header {
                display: flex !important;
                flex-direction: row !important;
                justify-content: space-between !important;
            }
            .terminal-brand-group {
                display: flex !important;
                flex-direction: row !important;
                align-items: center !important;
                gap: 14px !important;
            }
            .report-logo {
                width: 58px !important;
                height: 58px !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .report-meta {
                text-align: right !important;
                background: none !important;
                border: none !important;
                padding: 0 !important;
                width: auto !important;
            }
            .filter-summary {
                display: flex !important;
                flex-direction: row !important;
            }
            .signatory-section {
                display: flex !important;
                flex-direction: row !important;
                justify-content: space-between !important;
            }
            .signatory-box {
                width: 220px !important;
                max-width: 220px !important;
                margin: 0 !important;
            }
            .footer {
                display: flex !important;
                flex-direction: row !important;
                justify-content: space-between !important;
            }
            .vehicle-type-chip,
            .status-badge,
            .passenger-badge,
            .plate-number {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            tr {
                page-break-inside: avoid;
            }
            @page {
                margin: 10mm 10mm;
                size: portrait;
            }
        }
    </style>
</head>
<body>

    <!-- On-screen Toolbar -->
    <div class="print-toolbar">
        <div class="print-toolbar-title">
            <i class="fas fa-file-alt"></i> <?= esc($report_heading ?? 'Departure History Report') ?> Preview
        </div>
        <div class="print-toolbar-actions">
            <button onclick="window.print()" class="btn-toolbar btn-toolbar-primary">
                <i class="fas fa-print"></i> Print Report
            </button>
            <button type="button" onclick="closeReportPreview()" class="btn-toolbar btn-toolbar-secondary">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
    </div>

    <!-- Header Section -->
    <div class="header">
        <div class="terminal-brand-group">
            <img src="<?= esc(app_logo()) ?>" alt="Official Seal" class="report-logo" onerror="this.style.display='none'">
            <div class="terminal-info">
                <h1><?= esc(app_name()) ?><?= str_ends_with(strtolower(trim(app_name())), 'terminal') ? '' : ' Terminal' ?></h1>
                <?php if (!empty(app_contact_address()) || !empty(app_contact_phone())): ?>
                <p>
                    <?php if (!empty(app_contact_address())): ?>
                        <i class="fas fa-map-marker-alt text-danger"></i> <?= esc(app_contact_address()) ?>
                    <?php endif; ?>
                    <?php if (!empty(app_contact_address()) && !empty(app_contact_phone())): ?>
                        &bull;
                    <?php endif; ?>
                    <?php if (!empty(app_contact_phone())): ?>
                        <i class="fas fa-phone text-primary"></i> <?= esc(app_contact_phone()) ?>
                    <?php endif; ?>
                </p>
                <?php endif; ?>
                <p><?= esc(app_system_title()) ?> &bull; Official Departure History Report</p>
            </div>
        </div>
        <div class="report-meta">
            <h2><?= esc($report_heading ?? 'Departure History Report') ?></h2>
            <p><strong>Generated On:</strong> <?= date('F d, Y H:i:s') ?></p>
            <p><strong>Generated By:</strong> <?= esc(session()->get('full_name') ?? session()->get('username') ?? 'Administrator') ?> (<?= esc(ucwords(str_replace('_', ' ', session()->get('role') ?? 'Admin'))) ?>)</p>
            <?php if (!empty($today_only_report)): ?>
            <p><strong>Report Scope:</strong> Today's departures on your assigned routes</p>
            <?php else: ?>
            <p><strong>Retention Policy:</strong> 60 Days Automatic Cleanup</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filters Summary -->
    <div class="filter-summary">
        <div class="filter-item">
            <span class="filter-label">Total Records</span>
            <span class="filter-value"><?= count($results ?? []) ?> Departures</span>
        </div>
        <div class="filter-item">
            <span class="filter-label">Date Covered</span>
            <span class="filter-value">
                <?php if (!empty($today_only_report)): ?>
                    <?= esc(date('M d, Y', strtotime($from_date))) ?> (Today only)
                <?php elseif (!empty($from_date) && !empty($to_date)): ?>
                    <?= date('M d, Y', strtotime($from_date)) ?> &mdash; <?= date('M d, Y', strtotime($to_date)) ?>
                <?php elseif (!empty($from_date)): ?>
                    Starting <?= date('M d, Y', strtotime($from_date)) ?>
                <?php elseif (!empty($to_date)): ?>
                    Until <?= date('M d, Y', strtotime($to_date)) ?>
                <?php else: ?>
                    All Active Records (Last 60 Days)
                <?php endif; ?>
            </span>
        </div>
        <?php if (!empty($vehicle_type)): ?>
        <div class="filter-item">
            <span class="filter-label">Vehicle Type</span>
            <span class="filter-value"><?= vehicle_type_badge($vehicle_type) ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($destination)): ?>
        <div class="filter-item">
            <span class="filter-label">Destination</span>
            <span class="filter-value"><?= esc($destination) ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($search)): ?>
        <div class="filter-item">
            <span class="filter-label">Search Query</span>
            <span class="filter-value">"<?= esc($search) ?>"</span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Table Section -->
    <div class="report-table-wrapper">
        <div class="mobile-scroll-hint">
            <i class="fas fa-arrows-left-right"></i> Swipe horizontally to view full departure ledger
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width: 17%;">Date & Time</th>
                    <th style="width: 13%;">Plate Number</th>
                    <th style="width: 18%;">Operator</th>
                    <th style="width: 16%;">Driver</th>
                    <th style="width: 11%;">Type</th>
                    <th style="width: 15%;">Route</th>
                    <th style="width: 10%; text-align: center;">Passengers</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($results)): ?>
                    <?php foreach ($results as $row): ?>
                        <tr>
                            <td style="white-space: nowrap;">
                                <div style="font-weight: 700; color: #0f172a;"><?= history_departure_time($row['departure_time'], 'M d, Y') ?></div>
                                <div style="color: #64748b; font-size: 11.5px; font-weight: 500; font-family: monospace; margin-top: 2px;">
                                    <i class="far fa-clock"></i> <?= !empty($today_only_report) ? operations_recorded_departure($row['departure_time']) : history_departure_time($row['departure_time']) ?>
                                </div>
                            </td>
                            <td>
                                <span class="plate-number"><?= esc($row['plate_number']) ?></span>
                            </td>
                            <td>
                                <?php
                                    $opName = !empty($row['operator_name']) ? $row['operator_name'] : (!empty($row['owner_name']) ? $row['owner_name'] : '');
                                ?>
                                <div style="font-weight: 700; color: #0f172a; display: inline-flex; align-items: center; gap: 5px;">
                                    <i class="fas fa-building text-muted" style="font-size: 11px;"></i>
                                    <?= esc(!empty($opName) ? $opName : '—') ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #334155; display: inline-flex; align-items: center; gap: 5px;">
                                    <i class="fas fa-id-badge text-primary" style="font-size: 11px;"></i>
                                    <?= esc($row['driver_name'] ?? '—') ?>
                                </div>
                            </td>
                            <td><?= vehicle_type_badge($row['vehicle_type']) ?></td>
                            <td>
                                <div style="color: #64748b; font-size: 11px; font-weight: 600;"><?= strtoupper(esc($row['origin'] ?? 'Terminal')) ?></div>
                                <div style="color: #0f172a; font-weight: 700; font-size: 13px; display: inline-flex; align-items: center; gap: 4px;">
                                    <i class="fas fa-arrow-right text-primary" style="font-size: 10px;"></i> <?= strtoupper(esc($row['destination'] ?? '—')) ?>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <span class="passenger-badge"><?= (int) ($row['current_passengers'] ?? 0) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: #94a3b8; font-weight: 600;">
                            No departure records match the specified filters.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Signatory Section -->
    <div class="signatory-section">
        <div class="signatory-box">
            <div class="signatory-line"><?= esc(session()->get('full_name') ?? session()->get('username') ?? 'Administrator') ?></div>
            <div class="signatory-title">Generated By</div>
        </div>
        <div class="signatory-box">
            <div class="signatory-line">Terminal Operations Manager</div>
            <div class="signatory-title">Verified / Noted By</div>
        </div>
    </div>

    <!-- Footer Section -->
    <div class="footer">
        <div><?= esc(app_system_title()) ?> &bull; Official Operations Report</div>
        <div>Page generated on <?= date('Y-m-d H:i:s') ?></div>
    </div>

    <script>
        function closeReportPreview() {
            if (window.opener && !window.opener.closed) {
                window.close();
                window.setTimeout(() => {
                    if (!window.closed) {
                        window.location.replace(<?= json_encode($return_url ?? base_url('admin/history')) ?>);
                    }
                }, 150);
                return;
            }

            window.location.replace(<?= json_encode($return_url ?? base_url('admin/history')) ?>);
        }

        // ESC closes the preview or returns to departure history on mobile.
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeReportPreview();
        });
    </script>
    <?php if (session()->get('isLoggedIn')): ?>
    <script src="<?= app_asset_url('assets/js/session-guard.js') ?>" data-status-url="<?= base_url('auth/session-status') ?>" data-login-url="<?= base_url('login') ?>"></script>
    <?php endif; ?>
</body>
</html>
