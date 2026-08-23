<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 30px 35px;
            background: #ffffff;
            line-height: 1.5;
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
        }

        .btn-toolbar-primary {
            background: #b71c1c;
            color: #ffffff;
        }

        .btn-toolbar-primary:hover {
            background: #991b1b;
        }

        .btn-toolbar-secondary {
            background: #334155;
            color: #ffffff;
        }

        .btn-toolbar-secondary:hover {
            background: #475569;
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

        @media print {
            body {
                padding: 10mm 12mm;
            }
            .print-toolbar {
                display: none !important;
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
            <i class="fas fa-file-alt"></i> Departure History Report Preview
        </div>
        <div class="print-toolbar-actions">
            <button onclick="window.print()" class="btn-toolbar btn-toolbar-primary">
                <i class="fas fa-print"></i> Print Report
            </button>
            <button onclick="window.close()" class="btn-toolbar btn-toolbar-secondary">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
    </div>

    <!-- Header Section -->
    <div class="header">
        <div class="terminal-info">
            <h1>Palompon Transit Terminal</h1>
            <p><i class="fas fa-map-marker-alt text-danger"></i> Palompon, Leyte, Philippines &bull; <i class="fas fa-phone text-primary"></i> Terminal Operations Office</p>
            <p>Integrated Terminal Management System &bull; Official Departure History Report</p>
        </div>
        <div class="report-meta">
            <h2>Departure History Report</h2>
            <p><strong>Generated On:</strong> <?= date('F d, Y H:i:s') ?></p>
            <p><strong>Generated By:</strong> <?= esc(session()->get('full_name') ?? session()->get('username') ?? 'Administrator') ?> (<?= esc(ucwords(str_replace('_', ' ', session()->get('role') ?? 'Admin'))) ?>)</p>
            <p><strong>Retention Policy:</strong> 60 Days Automatic Cleanup</p>
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
                <?php if (!empty($from_date) && !empty($to_date)): ?>
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
                            <div style="font-weight: 700; color: #0f172a;"><?= date('M d, Y', strtotime($row['departure_time'])) ?></div>
                            <div style="color: #64748b; font-size: 11.5px; font-weight: 500; font-family: monospace; margin-top: 2px;">
                                <i class="far fa-clock"></i> <?= date('H:i', strtotime($row['departure_time'])) ?>
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
                            <div style="color: #64748b; font-size: 11px; font-weight: 600;"><?= esc($row['origin'] ?? 'Palompon') ?></div>
                            <div style="color: #0f172a; font-weight: 700; font-size: 13px; display: inline-flex; align-items: center; gap: 4px;">
                                <i class="fas fa-arrow-right text-primary" style="font-size: 10px;"></i> <?= esc($row['destination'] ?? '—') ?>
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
        <div>Palompon Transit Terminal Monitoring System &bull; Official Operations Report</div>
        <div>Page generated on <?= date('Y-m-d H:i:s') ?></div>
    </div>

    <script>
        // ESC to close preview window
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') window.close();
        });
    </script>
</body>
</html>
