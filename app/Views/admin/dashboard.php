<?= $this->include('templates/header') ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">

<style>
    /* Admin-specific responsive enhancements */
    @media (max-width: 768px) {
        .page-header-modern {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
            padding: 8px 0 6px 0;
            margin-bottom: 8px;
        }
        
        .page-title-modern {
            font-size: 22px;
        }
        
        .stat-card-modern {
            padding: 18px;
        }
        
        .stat-card-icon {
            width: 48px;
            height: 48px;
            font-size: 20px;
        }
        
        .stat-card-value {
            font-size: 26px;
        }
        
        .stat-card-label {
            font-size: 13px;
            font-weight: 600;
        }
        
        .table-modern thead {
            display: none;
        }
        
        .table-modern tbody tr {
            display: block;
            margin-bottom: 12px;
            border-radius: 10px;
            border: 1px solid var(--slate-200);
            padding: 14px;
        }
        
        .table-modern tbody td {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid var(--slate-100);
            font-size: 13px;
        }
        
        .table-modern tbody td:last-child {
            border-bottom: none;
        }
        
        .table-modern tbody td::before {
            content: attr(data-label);
            font-weight: 700;
            color: var(--slate-500);
            text-transform: uppercase;
            font-size: 12px;
        }
    }
    
    @media (max-width: 480px) {
        .stat-card-modern {
            padding: 16px;
        }
        
        .stat-card-value {
            font-size: 22px;
        }
        
        .btn-modern {
            padding: 10px 18px;
            font-size: 13px;
        }
    }
</style>

<div class="page-header-modern fade-in">
    <h1 class="page-title-modern">
        <i class="bi bi-speedometer2"></i>
        Admin Dashboard
    </h1>
    <div class="d-flex gap-2 flex-wrap flex-column flex-sm-row">
        <button type="button" class="btn-modern btn-modern-primary" data-bs-toggle="modal" data-bs-target="#reportFilterModal" style="display:inline-flex; align-items:center; justify-content:center; gap:6px;">
            <i class="bi bi-file-earmark-text"></i> Generate Report
        </button>
    </div>
</div>

<?= view('admin/modals/report_filter', ['destinations' => $destinations, 'vehicleTypes' => $vehicleTypes]) ?>

<?php if (!empty($unassignedVehicles)): ?>
    <div class="alert-modern alert-modern-warning fade-in">
        <i class="bi bi-exclamation-triangle-fill alert-modern-icon"></i>
        <div>
            <strong><?= $unassignedVehicles ?> vehicle<?= $unassignedVehicles > 1 ? 's have' : ' has' ?> no assigned route.</strong>
            Please assign routes before queueing them.
            <a href="<?= base_url('admin/vehicles') ?>" style="color: inherit; font-weight: 600; text-decoration: underline;">Go to Vehicle Register →</a>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($unassignedStaff)): ?>
    <div class="alert-modern alert-modern-warning fade-in">
        <i class="bi bi-exclamation-triangle-fill alert-modern-icon"></i>
        <div>
            <strong><?= $unassignedStaff ?> dispatcher<?= $unassignedStaff > 1 ? 's have' : ' has' ?> no routes assigned.</strong>
            They cannot manage any queue until routes are assigned.
            <a href="<?= base_url('admin/users') ?>" style="color: inherit; font-weight: 600; text-decoration: underline;">Go to User Management →</a>
        </div>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-12 col-md-6 col-xl-3 mb-4">
        <div class="stat-card-modern blue-accent fade-in">
            <div class="stat-card-icon" style="background: #DBEAFE; color: #1565c0;">
                <i class="bi bi-truck"></i>
            </div>
            <div class="stat-card-value"><?= $stats['vehicles'] ?></div>
            <div class="stat-card-label">Total Vehicles</div>
            <a href="<?= base_url('admin/vehicles') ?>" class="stat-card-link">
                View Details <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3 mb-4">
        <div class="stat-card-modern success-accent fade-in">
            <div class="stat-card-icon" style="background: #D1FAE5; color: #059669;">
                <i class="bi bi-map"></i>
            </div>
            <div class="stat-card-value"><?= $stats['routes'] ?></div>
            <div class="stat-card-label">Active Routes</div>
            <a href="<?= base_url('admin/routes') ?>" class="stat-card-link">
                View Details <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3 mb-4">
        <div class="stat-card-modern gold-accent fade-in">
            <div class="stat-card-icon" style="background: #FEF3C7; color: #d97706;">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-card-value"><?= $stats['users'] ?></div>
            <div class="stat-card-label">Total Users</div>
            <a href="<?= base_url('admin/users') ?>" class="stat-card-link">
                View Details <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3 mb-4">
        <div class="stat-card-modern fade-in">
            <div class="stat-card-icon" style="background: var(--primary-soft, #fdecea); color: var(--primary, #c62828);">
                <i class="bi bi-journal-text"></i>
            </div>
            <div class="stat-card-value"><?= $stats['logs'] ?></div>
            <div class="stat-card-label">System Logs</div>
            <a href="<?= base_url('admin/logs') ?>" class="stat-card-link">
                View Details <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-5 mb-3 fade-in">
    <h2 class="mb-0" style="font-size: 20px; font-weight: 700; color: var(--text-main, #1e293b);">
        <i class="bi bi-clock-history me-1 text-primary"></i> Recent Activity
    </h2>
    <a href="<?= base_url('admin/logs') ?>" class="btn-modern btn-modern-outline btn-modern-sm">
        View All Logs <i class="bi bi-arrow-right ms-1"></i>
    </a>
</div>
<div class="modern-card shadow-modern fade-in">
    <div class="table-responsive">
        <table class="table-modern" id="adminRecentActivityTable">
            <thead>
                <tr>
                    <th style="width: 175px;">Timestamp</th>
                    <th style="width: 220px;">User</th>
                    <th style="width: 210px;">Action</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recent_logs)): ?>
                    <?php foreach ($recent_logs as $log): ?>
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
                            <td data-label="Timestamp">
                                <div style="white-space: nowrap; font-size: 13.5px; font-weight: 700; color: var(--text-main, #0f172a);">
                                    <i class="bi bi-calendar3 text-muted me-1" style="font-size: 11px;"></i><?= !empty($log['timestamp']) ? date('M d, Y', strtotime($log['timestamp'])) : 'N/A' ?>
                                </div>
                                <div class="text-muted" style="font-size: 12px; font-weight: 600; font-family: monospace; margin-top: 2px;">
                                    <i class="bi bi-clock text-muted me-1" style="font-size: 11px;"></i><?= !empty($log['timestamp']) ? date('H:i:s', strtotime($log['timestamp'])) : '' ?>
                                </div>
                            </td>
                            <td data-label="User">
                                <?php if (!empty($log['username'])): ?>
                                    <div style="font-weight: 700; font-size: 13.5px; color: var(--text-main, #0f172a);">
                                        <?= esc($log['email'] ?: $log['username']) ?>
                                    </div>
                                    <small class="text-muted d-block" style="font-size: 12px;">
                                        <?= esc($log['full_name'] ?? $log['username']) ?>
                                    </small>
                                <?php else: ?>
                                    <span class="text-muted fst-italic" style="font-size: 13px;">System / Automatic</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Action">
                                <span class="action-badge-pill <?= $badgeClass ?>">
                                    <?= esc($log['action']) ?>
                                </span>
                            </td>
                            <td data-label="Details" class="log-details-cell" style="font-size: 13px;">
                                <?= esc($log['details'] ?? '—') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted empty-state-table">
                            <i class="bi bi-activity fs-1 d-block mb-3 opacity-50"></i>
                            <div class="fw-bold fs-6 empty-state-title">No recent activity recorded</div>
                            <small class="empty-state-subtitle">System actions and user events will appear here in real-time.</small>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->include('templates/footer') ?>

<script src="<?= base_url('js/queue-sync.js?v=20260920_3') ?>"></script>
<script>
    QueueSync.init({
        pollInterval: 8000,
        refreshUrl:   '<?= base_url('admin/dashboard') ?>',
        tableSelector: '#adminRecentActivityTable tbody',
        extraRefresh: function(newDoc) {
            // Update all stat cards dynamically
            var newCards = newDoc.querySelectorAll('.stat-card-value');
            var curCards = document.querySelectorAll('.stat-card-value');
            newCards.forEach(function(card, i) { 
                if (curCards[i]) curCards[i].textContent = card.textContent; 
            });
        }
    });

    // Real-time WebSocket listeners for immediate admin activity and stats refresh
    document.addEventListener('pttm:ws-queue_update', function(e) {
        var detail = (e && e.detail) ? e.detail : {};
        var data = detail.data || detail;
        if (data && (data.action === 'passenger_change' || data.type === 'passenger_change')) {
            if (window.QueueSync && window.QueueSync.updatePassengerUI) {
                var id = data.id;
                var newCount = parseInt(data.new_count, 10);
                var capacity = parseInt(data.capacity, 10);
                if (id && !isNaN(newCount) && !isNaN(capacity)) {
                    window.QueueSync.updatePassengerUI(id, newCount, capacity);
                }
            }
            return;
        }
        if (window.QueueSync && window.QueueSync.refresh) {
            window.QueueSync.refresh();
        }
    });
    document.addEventListener('pttm:ws-fare_update', function(e) {
        if (window.QueueSync && window.QueueSync.refresh) {
            window.QueueSync.refresh();
        }
    });
    document.addEventListener('pttm:ws-vehicle_type_update', function() {
        if (window.QueueSync && window.QueueSync.refresh) {
            window.QueueSync.refresh();
        }
    });
    document.addEventListener('pttm:ws-operational_settings_updated', function() {
        if (window.QueueSync && window.QueueSync.refresh) {
            window.QueueSync.refresh();
        }
    });
    document.addEventListener('pttm:ws-announcement_update', function() {
        if (window.QueueSync && window.QueueSync.refresh) {
            window.QueueSync.refresh();
        }
    });
    document.addEventListener('pttm:ws-branding_updated', function() {
        if (window.QueueSync && window.QueueSync.refresh) {
            window.QueueSync.refresh();
        }
    });
</script>



