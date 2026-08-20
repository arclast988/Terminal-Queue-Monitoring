<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
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
            margin: 0;
            color: #64748b;
            font-size: 13px;
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
            table-layout: fixed;
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
            vertical-align: top;
        }

        tr:nth-child(even) td {
            background: #fafbfc;
        }

        .log-id {
            font-family: 'Courier New', monospace;
            font-weight: 800;
            color: #0f172a;
            white-space: nowrap;
            font-size: 13px;
        }

        .log-timestamp {
            white-space: nowrap;
            font-weight: 700;
            color: #0f172a;
            font-size: 12.5px;
        }

        .log-user {
            font-weight: 700;
            color: #0f172a;
            font-size: 12.5px;
            word-break: break-word;
        }

        .log-role {
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
            word-break: break-all;
        }

        .action-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 700;
            line-height: 1.35;
            word-break: break-word;
            max-width: 100%;
        }

        .details-text {
            color: #1e293b;
            word-break: normal;
            overflow-wrap: break-word;
            line-height: 1.5;
            font-size: 12.5px;
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
            <i class="fas fa-file-alt"></i> System Activity Logs Report Preview
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
            <p>Integrated Terminal Management System &bull; System Activity Audit Log</p>
        </div>
        <div class="report-meta">
            <h2>Activity Logs Report</h2>
            <p><strong>Generated On:</strong> <?= date('F d, Y H:i:s', strtotime($generated_at ?? 'now')) ?></p>
            <p><strong>Generated By:</strong> <?= esc(session()->get('full_name') ?? session()->get('username') ?? 'Administrator') ?> (<?= esc(ucwords(str_replace('_', ' ', session()->get('role') ?? 'Admin'))) ?>)</p>
            <p><strong>Retention Policy:</strong> 60 Days Automatic Cleanup</p>
        </div>
    </div>

    <!-- Filters Summary -->
    <div class="filter-summary">
        <div class="filter-item">
            <span class="filter-label">Total Records</span>
            <span class="filter-value"><?= count($results ?? []) ?> Entries</span>
        </div>
        <div class="filter-item">
            <span class="filter-label">Date Range</span>
            <span class="filter-value">
                <?php if (!empty($from_date) && !empty($to_date)): ?>
                    <?= date('M d, Y', strtotime($from_date)) ?> &mdash; <?= date('M d, Y', strtotime($to_date)) ?>
                <?php elseif (!empty($from_date)): ?>
                    From <?= date('M d, Y', strtotime($from_date)) ?>
                <?php elseif (!empty($to_date)): ?>
                    Until <?= date('M d, Y', strtotime($to_date)) ?>
                <?php else: ?>
                    All Active Records (Last 60 Days)
                <?php endif; ?>
            </span>
        </div>
        <?php if (!empty($action_type)): ?>
        <div class="filter-item">
            <span class="filter-label">Action Filter</span>
            <span class="filter-value"><?= esc($action_type) ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($search)): ?>
        <div class="filter-item">
            <span class="filter-label">Keyword Query</span>
            <span class="filter-value">"<?= esc($search) ?>"</span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Table Section -->
    <table>
        <thead>
            <tr>
                <th style="width: 8%;">Log ID</th>
                <th style="width: 17%;">Timestamp</th>
                <th style="width: 23%;">User</th>
                <th style="width: 18%;">Action</th>
                <th style="width: 34%;">Details</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($results) && is_array($results)): ?>
                <?php foreach ($results as $row): ?>
                    <?php
                        $actLower = strtolower($row['action'] ?? '');
                        $badgeStyle = 'background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;';
                        if (strpos($actLower, 'delete') !== false || strpos($actLower, 'remove') !== false) {
                            $badgeStyle = 'background: #fef2f2; color: #991b1b; border: 1px solid #fecaca;';
                        } elseif (strpos($actLower, 'update') !== false || strpos($actLower, 'edit') !== false || strpos($actLower, 'reassign') !== false) {
                            $badgeStyle = 'background: #fefce8; color: #854d0e; border: 1px solid #fef08a;';
                        } elseif (strpos($actLower, 'add') !== false || strpos($actLower, 'create') !== false) {
                            $badgeStyle = 'background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;';
                        }
                    ?>
                    <tr>
                        <td class="log-id">#<?= $row['id'] ?></td>
                        <td class="log-timestamp">
                            <div><?= date('M d, Y', strtotime($row['timestamp'])) ?></div>
                            <div style="color: #64748b; font-size: 11.5px; font-weight: 500; font-family: monospace; margin-top: 2px;">
                                <i class="far fa-clock"></i> <?= date('H:i:s', strtotime($row['timestamp'])) ?>
                            </div>
                        </td>
                        <td>
                            <div class="log-user"><?= esc($row['full_name'] ?? $row['username'] ?? 'System/Guest') ?></div>
                            <div class="log-role">
                                <?= esc($row['email'] ?: ($row['username'] ? '@' . $row['username'] : 'Automatic')) ?> &bull; <?= esc(ucwords(str_replace('_', ' ', $row['role'] ?? 'System'))) ?>
                            </div>
                        </td>
                        <td>
                            <span class="action-badge" style="<?= $badgeStyle ?>"><?= esc($row['action']) ?></span>
                        </td>
                        <td class="details-text">
                            <?= esc($row['details'] ?? '—') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 35px; color: #94a3b8; font-weight: 600;">
                        No activity log entries match the specified filters.
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
        <div>Palompon Transit Terminal Monitoring System &bull; Confidential Audit Report</div>
        <div>Page generated on <?= date('Y-m-d H:i:s') ?></div>
    </div>

</body>
</html>
