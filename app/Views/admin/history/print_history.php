<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 40px;
            background: white;
            line-height: 1.5;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #C62828;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .terminal-info h1 {
            color: #C62828;
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .terminal-info p {
            margin: 5px 0;
            color: #64748b;
            font-size: 14px;
        }

        .report-meta {
            text-align: right;
        }

        .report-meta h2 {
            margin: 0;
            font-size: 18px;
            color: #1e293b;
        }

        .report-meta p {
            margin: 5px 0;
            color: #64748b;
            font-size: 12px;
        }

        .filter-summary {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 13px;
            border: 1px solid #e2e8f0;
        }

        .filter-item {
            display: inline-block;
            margin-right: 20px;
        }

        .filter-label {
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            font-size: 11px;
            display: block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background: #f1f5f9;
            color: #475569;
            text-transform: uppercase;
            font-size: 11px;
            font-weight: 700;
            text-align: left;
            padding: 12px 10px;
            border-bottom: 2px solid #e2e8f0;
        }

        td {
            padding: 12px 10px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
        }

        tr:nth-child(even) {
            background-color: #fafafa;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            background: #f1f5f9;
            font-weight: 600;
            font-size: 12px;
        }

        .v-type {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            display: block;
        }

        .footer {
            padding-top: 30px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            margin: 0;
            /* Keep lines together and with the table if possible */
            break-inside: avoid;
            page-break-inside: avoid;
            break-before: avoid;
            page-break-before: avoid;
        }

        .footer p {
            margin: 2px 0;
            display: block;
        }

        @media print {
            /* Force the browser to omit URL, Date, and Title headers/footers */
            @page { 
                margin: 0; 
                size: auto;
            }
            body { 
                padding: 2cm; 
                margin: 0;
            }
            .no-print { 
                display: none !important; 
            }
            /* Hide intentional header/footer partials if they exist */
            header, footer { display: none !important; }
        }

        .print-controls {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: rgba(255, 255, 255, 0.9);
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(4px);
            z-index: 1000;
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .btn-print {
            background: #C62828;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-print:hover {
            background: #B71C1C;
        }
    </style>
</head>
<body>

    <div class="print-controls no-print">
        <button onclick="window.print()" class="btn-print">
            <i class="fas fa-print"></i> Save as PDF / Print
        </button>
        <p style="margin: 8px 0 0; font-size: 11px; text-align: center; color: #64748b;">
            Press ESC to close
        </p>
    </div>

    <div class="header">
        <div class="terminal-info">
            <h1>Palompon Transit Terminal</h1>
            <p><i class="fas fa-map-marker-alt"></i> Palompon, Leyte, Philippines</p>
            <p><i class="fas fa-phone"></i> Terminal Operations Office</p>
        </div>
        <div class="report-meta">
            <h2>Departure History Report</h2>
            <p>Generated on: <?= date('M d, Y h:i A') ?></p>
        </div>
    </div>

    <div class="filter-summary">
        <div class="filter-item">
            <span class="filter-label">Date Range</span>
            <strong>
                <?php if ($from_date && $to_date): ?>
                    <?= date('M d, Y', strtotime($from_date)) ?> — <?= date('M d, Y', strtotime($to_date)) ?>
                <?php elseif ($from_date): ?>
                    Starting <?= date('M d, Y', strtotime($from_date)) ?>
                <?php elseif ($to_date): ?>
                    Until <?= date('M d, Y', strtotime($to_date)) ?>
                <?php else: ?>
                    All Time Records
                <?php endif; ?>
            </strong>
        </div>
        <?php if ($search): ?>
        <div class="filter-item">
            <span class="filter-label">Search Query</span>
            <strong>"<?= esc($search) ?>"</strong>
        </div>
        <?php endif; ?>

        <?php if ($destination): ?>
        <div class="filter-item">
            <span class="filter-label">Route Destination</span>
            <strong><?= esc($destination) ?></strong>
        </div>
        <?php endif; ?>

        <?php if ($vehicle_type): ?>
        <div class="filter-item">
            <span class="filter-label">Vehicle Type</span>
            <strong><?= ucfirst(esc($vehicle_type)) ?></strong>
        </div>
        <?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date & Time</th>
                <th>Plate Number</th>
                <th>Vehicle Type</th>
                <th>Driver / Owner</th>
                <th>Route</th>
                <th>Pax</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($results)): ?>
                <?php foreach ($results as $row): ?>
                    <tr>
                        <td style="white-space: nowrap; line-height: 1.4;">
                            <strong style="display: block; font-size: 14px;"><?= date('M d, Y', strtotime($row['departure_time'])) ?></strong>
                            <span style="color: #64748b; font-size: 12px; font-weight: 500; font-family: monospace;">
                                <i class="far fa-clock" style="font-size: 10px;"></i> <?= date('h:i A', strtotime($row['departure_time'])) ?>
                            </span>
                        </td>
                        <td style="font-family: monospace; font-size: 14px; font-weight: 600;"><?= esc($row['plate_number']) ?></td>
                        <td><?= ucfirst($row['vehicle_type']) ?></td>
                        <td>
                            <?= esc($row['driver_name'] ?? $row['owner_name'] ?? '—') ?>
                            <br><small style="color: #94a3b8; font-size: 10px;"><?= $row['driver_name'] ? 'Driver' : 'Owner' ?></small>
                        </td>
                        <td>
                            <small style="color: #94a3b8;"><?= esc($row['origin']) ?></small>
                            <br><strong><?= esc($row['destination']) ?></strong>
                        </td>
                        <td><span class="badge"><?= $row['current_passengers'] ?></span></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: #94a3b8;">
                        No departure records match the current filters.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>This is a system-generated report from the Palompon Transit Terminal Monitoring System.</p>
        <p>&copy; <?= date('Y') ?> All Rights Reserved.</p>
    </div>

    <script>
        // Optional: Auto open print dialog
        window.onload = function() {
            // setTimeout(() => window.print(), 500);
        }
        
        // ESC to go back
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') window.close();
        });
    </script>
</body>
</html>
