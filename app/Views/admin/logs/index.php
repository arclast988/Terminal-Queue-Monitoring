<?= view('templates/header', ['title' => $title]) ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">

<style>
    .retention-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f0fdf4;
        color: #15803d;
        border: 1px solid #bbf7d0;
        border-radius: 20px;
        padding: 5px 14px;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.3px;
    }

    /* Enhanced table typography & sizing */
    .table-modern th {
        font-size: 13px !important;
        font-weight: 800 !important;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #475569 !important;
        padding: 14px 16px !important;
    }

    .table-modern td {
        font-size: 14px !important;
        padding: 15px 16px !important;
        vertical-align: middle;
    }

    .log-id-badge {
        font-family: 'Courier New', monospace;
        font-weight: 800;
        font-size: 14.5px;
        background: #f1f5f9;
        color: #0f172a;
        padding: 4px 10px;
        border-radius: 6px;
        display: inline-block;
        border: 1px solid #e2e8f0;
    }

    .log-user-email {
        font-weight: 700;
        color: var(--text-main, #0f172a);
        font-size: 14.5px;
        word-break: break-all;
    }

    .log-user-sub {
        font-size: 13px;
        color: #64748b;
        margin-top: 3px;
    }

    .badge-role {
        font-size: 12px;
        font-weight: 700;
        text-transform: capitalize;
        color: #64748b;
    }

    .action-badge-pill {
        display: inline-flex;
        align-items: center;
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 13.5px;
        font-weight: 700;
        line-height: 1.35;
        border: 1px solid transparent;
        white-space: normal;
        word-break: break-word;
        max-width: 220px;
    }

    .action-badge-default { background: #f1f5f9; color: #334155; border-color: #cbd5e1; }
    .action-badge-success { background: #ecfdf5; color: #065f46; border-color: #a7f3d0; }
    .action-badge-info    { background: #eff6ff; color: #1e40af; border-color: #bfdbfe; }
    .action-badge-warning { background: #fefce8; color: #854d0e; border-color: #fef08a; }
    .action-badge-danger  { background: #fef2f2; color: #991b1b; border-color: #fecaca; }

    .table-modern .log-details-cell {
        max-width: 450px;
        white-space: normal !important;
        overflow-wrap: anywhere;
        font-size: 14px;
        color: #1e293b;
        line-height: 1.55;
    }

    .quick-chips-row {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 16px;
        padding-top: 14px;
        border-top: 1px solid #f1f5f9;
    }

    /* Red theme quick chips */
    html body .quick-chip {
        padding: 6px 16px;
        border-radius: 20px;
        border: 1.5px solid #cbd5e1 !important;
        background: #f8fafc !important;
        color: #475569 !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        text-decoration: none !important;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
        outline: none;
    }

    html body .quick-chip:hover {
        border-color: #b71c1c !important;
        color: #b71c1c !important;
        background: #fef2f2 !important;
    }

    html body .quick-chip.active {
        background: #b71c1c !important;
        border-color: #b71c1c !important;
        color: #ffffff !important;
    }

    @media (prefers-color-scheme: dark) {
        .log-id-badge {
            background: #334155;
            color: #f8fafc;
            border-color: #475569;
        }
        .table-modern .log-details-cell {
            color: #cbd5e1;
        }
        html body .quick-chip {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #cbd5e1 !important;
        }
        html body .quick-chip.active {
            background: #b71c1c !important;
            border-color: #b71c1c !important;
            color: #ffffff !important;
        }
    }
</style>

<div class="page-header-modern fade-in">
    <div>
        <h1 class="page-title-modern mb-1">
            <i class="bi bi-journal-text"></i> System Activity Logs
        </h1>
        <div class="retention-pill">
            <i class="bi bi-shield-check"></i> Auto-Retention: Logs are automatically kept for 60 days
        </div>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <?php
            $queryUrl = !empty($_GET) ? '?' . http_build_query($_GET) : '';
        ?>
        <a href="<?= base_url('admin/logs/print' . $queryUrl) ?>" target="_blank" class="btn-modern btn-modern-primary" title="Print or preview log report">
            <i class="bi bi-file-earmark-text"></i> Generate Report
        </a>

    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert-modern alert-modern-success fade-in">
        <i class="bi bi-check-circle-fill alert-modern-icon"></i>
        <div><?= esc(session()->getFlashdata('success')) ?></div>
    </div>
<?php endif; ?>

<!-- Summary Stat Cards -->
<div class="row mb-4">
    <div class="col-12 col-md-6 col-xl-3 mb-4">
        <div class="stat-card-modern blue-accent fade-in">
            <div class="stat-card-icon" style="background: #DBEAFE; color: #1565c0;">
                <i class="bi bi-activity"></i>
            </div>
            <div class="stat-card-value"><?= number_format($stats['total'] ?? 0) ?></div>
            <div class="stat-card-label">Active Log Entries</div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3 mb-4">
        <div class="stat-card-modern success-accent fade-in">
            <div class="stat-card-icon" style="background: #D1FAE5; color: #059669;">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="stat-card-value"><?= number_format($stats['today'] ?? 0) ?></div>
            <div class="stat-card-label">Logged Today</div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3 mb-4">
        <div class="stat-card-modern gold-accent fade-in">
            <div class="stat-card-icon" style="background: #FEF3C7; color: #d97706;">
                <i class="bi bi-calendar-week"></i>
            </div>
            <div class="stat-card-value"><?= number_format($stats['this_week'] ?? 0) ?></div>
            <div class="stat-card-label">Past 7 Days</div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3 mb-4">
        <div class="stat-card-modern fade-in">
            <div class="stat-card-icon" style="background: #F1F5F9; color: #475569;">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-card-value"><?= number_format($stats['active_users'] ?? 0) ?></div>
            <div class="stat-card-label">Active Users Tracked</div>
        </div>
    </div>
</div>

<!-- Search & Filters -->
<div class="modern-card shadow-modern fade-in mb-4">
    <div class="modern-card-body">
        <form method="get" action="<?= base_url('admin/logs') ?>" id="logsFilterForm" class="row g-3 align-items-end">
            <div class="col-12 col-lg-3 col-md-6">
                <label class="form-label-modern"><i class="bi bi-search me-1"></i> Keyword Search</label>
                <input type="text" class="input-modern" name="q" placeholder="Actions, details, user..." value="<?= esc($search ?? '') ?>">
            </div>
            <div class="col-12 col-lg-2 col-md-3">
                <label class="form-label-modern"><i class="bi bi-calendar-event me-1"></i> From Date</label>
                <input type="date" class="input-modern" name="from_date" id="fromDate" value="<?= esc($from_date ?? '') ?>">
            </div>
            <div class="col-12 col-lg-2 col-md-3">
                <label class="form-label-modern"><i class="bi bi-calendar-event me-1"></i> To Date</label>
                <input type="date" class="input-modern" name="to_date" id="toDate" value="<?= esc($to_date ?? '') ?>">
            </div>
            <div class="col-12 col-lg-2 col-md-4">
                <label class="form-label-modern"><i class="bi bi-lightning-charge me-1"></i> Action</label>
                <select name="action_type" class="input-modern">
                    <option value="">All Actions</option>
                    <?php if (!empty($actions)): ?>
                        <?php foreach ($actions as $act): ?>
                            <option value="<?= esc($act['action']) ?>" <?= ($action_type ?? '') === $act['action'] ? 'selected' : '' ?>>
                                <?= esc($act['action']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-12 col-lg-2 col-md-4">
                <label class="form-label-modern"><i class="bi bi-person me-1"></i> User</label>
                <select name="user_id" class="input-modern">
                    <option value="">All Users</option>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= esc($u['id']) ?>" <?= ($user_id ?? '') == $u['id'] ? 'selected' : '' ?>>
                                <?= esc($u['full_name'] ?? $u['username']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-12 col-lg-1 col-md-4">
                <button type="submit" class="btn-modern btn-modern-primary w-100" style="height: 42px;">
                    <i class="bi bi-funnel-fill"></i> Filter
                </button>
            </div>
        </form>

        <div class="quick-chips-row">
            <span class="text-muted" style="font-size: 12px; font-weight: 700; text-transform: uppercase;">
                <i class="bi bi-clock-history me-1"></i> Quick Ranges:
            </span>
            <button type="button" class="quick-chip quick-chip-btn <?= empty($from_date) && empty($to_date) ? 'active' : '' ?>" data-from="" data-to="">All (60d)</button>
            <button type="button" class="quick-chip quick-chip-btn <?= ($from_date === date('Y-m-d') && $to_date === date('Y-m-d')) ? 'active' : '' ?>" data-from="<?= date('Y-m-d') ?>" data-to="<?= date('Y-m-d') ?>">Today</button>
            <button type="button" class="quick-chip quick-chip-btn <?= ($from_date === date('Y-m-d', strtotime('-7 days')) && $to_date === date('Y-m-d')) ? 'active' : '' ?>" data-from="<?= date('Y-m-d', strtotime('-7 days')) ?>" data-to="<?= date('Y-m-d') ?>">Last 7 Days</button>
            <button type="button" class="quick-chip quick-chip-btn <?= ($from_date === date('Y-m-d', strtotime('-30 days')) && $to_date === date('Y-m-d')) ? 'active' : '' ?>" data-from="<?= date('Y-m-d', strtotime('-30 days')) ?>" data-to="<?= date('Y-m-d') ?>">Last 30 Days</button>
            <?php if (!empty($search) || !empty($from_date) || !empty($to_date) || !empty($action_type) || !empty($user_id)): ?>
                <a href="<?= base_url('admin/logs') ?>" class="btn-modern btn-modern-sm btn-modern-outline ms-auto" title="Clear all filters">
                    <i class="bi bi-x-circle me-1"></i> Clear Filters
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="modern-card shadow-modern fade-in mb-4">
    <div class="modern-card-header d-flex align-items-center justify-content-between">
        <span class="modern-card-title" style="font-size: 16px;">
            <i class="bi bi-list-ul" style="color: var(--primary-red, #b71c1c);"></i>
            Log Entries
            <?php if (!empty($logs)): ?>
                <span class="badge-modern badge-modern-secondary ms-2" style="font-size: 13px;"><?= count($logs) ?> on this page</span>
            <?php endif; ?>
        </span>
    </div>
    <div class="modern-card-body p-0">
        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th style="width: 85px;">ID</th>
                        <th style="width: 175px;">Timestamp</th>
                        <th style="width: 230px;">User</th>
                        <th style="width: 210px;">Action</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs) && is_array($logs)): ?>
                        <?php foreach ($logs as $log): ?>
                            <?php
                                $actLower = strtolower($log['action'] ?? '');
                                $badgeClass = 'action-badge-default';
                                if (strpos($actLower, 'login') !== false || strpos($actLower, 'auth') !== false) {
                                    $badgeClass = 'action-badge-success';
                                } elseif (strpos($actLower, 'add') !== false || strpos($actLower, 'create') !== false || strpos($actLower, 'register') !== false) {
                                    $badgeClass = 'action-badge-info';
                                } elseif (strpos($actLower, 'update') !== false || strpos($actLower, 'edit') !== false || strpos($actLower, 'reassign') !== false) {
                                    $badgeClass = 'action-badge-warning';
                                } elseif (strpos($actLower, 'delete') !== false || strpos($actLower, 'remove') !== false) {
                                    $badgeClass = 'action-badge-danger';
                                }
                            ?>
                            <tr>
                                <td data-label="ID">
                                    <span class="log-id-badge">#<?= $log['id'] ?></span>
                                </td>
                                <td data-label="Timestamp">
                                    <div style="white-space: nowrap; font-size: 14.5px; font-weight: 700; color: var(--text-main, #0f172a);">
                                        <i class="bi bi-calendar3 text-muted me-1" style="font-size: 12px;"></i><?= date('M d, Y', strtotime($log['timestamp'])) ?>
                                    </div>
                                    <div class="text-muted" style="font-size: 13px; font-weight: 600; font-family: monospace; margin-top: 2px;">
                                        <i class="bi bi-clock text-muted me-1" style="font-size: 12px;"></i><?= date('H:i:s', strtotime($log['timestamp'])) ?>
                                    </div>
                                </td>
                                <td data-label="User">
                                    <?php if (!empty($log['username'])): ?>
                                        <div class="log-user-email">
                                            <?= esc($log['email'] ?: $log['username']) ?>
                                        </div>
                                        <div class="log-user-sub">
                                            <?= esc($log['full_name'] ?? $log['username']) ?>
                                            <?php if (!empty($log['role'])): ?>
                                                &bull; <span class="badge-role"><?= esc(ucwords(str_replace('_', ' ', $log['role']))) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic" style="font-size: 13.5px;">System / Automatic</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Action">
                                    <span class="action-badge-pill <?= $badgeClass ?>">
                                        <?= esc($log['action']) ?>
                                    </span>
                                </td>
                                <td data-label="Details" class="log-details-cell">
                                    <?= esc($log['details'] ?? '—') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                <div class="fw-bold fs-6">No activity logs found</div>
                                <small>Try adjusting your search criteria or date filters.</small>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($pager && $pager->getPageCount() > 1): ?>
        <div class="modern-card-footer bg-white py-3 d-flex justify-content-center">
            <?= $pager->links() ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var quickButtons = document.querySelectorAll('.quick-chip-btn');
    var fromInput = document.getElementById('fromDate');
    var toInput = document.getElementById('toDate');
    var form = document.getElementById('logsFilterForm');

    quickButtons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var from = this.getAttribute('data-from') || '';
            var to = this.getAttribute('data-to') || '';
            
            if (fromInput) fromInput.value = from;
            if (toInput) toInput.value = to;

            quickButtons.forEach(function(b) {
                b.classList.remove('active');
            });
            this.classList.add('active');

            if (form) {
                form.submit();
            }
        });
    });
});
</script>

<?= view('templates/footer') ?>
