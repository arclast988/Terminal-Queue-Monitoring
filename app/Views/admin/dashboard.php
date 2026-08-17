<?= $this->include('templates/header') ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">

<style>
    /* Admin-specific responsive enhancements */
    @media (max-width: 768px) {
        .page-header-modern {
            flex-direction: column;
            align-items: flex-start;
            gap: 16px;
            padding: 20px 0;
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
    <button type="button" class="btn-modern btn-modern-primary" data-bs-toggle="modal" data-bs-target="#reportFilterModal">
        <i class="bi bi-file-earmark-text"></i> Generate Report
    </button>
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
            <div class="stat-card-icon" style="background: #F1F5F9; color: #475569;">
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

<h2 class="mt-5 mb-3 fade-in" style="font-size: 20px; font-weight: 700; color: var(--text-main, #1e293b);">Recent Activity</h2>
<div class="modern-card shadow-modern fade-in">
    <div class="table-responsive">
        <table class="table-modern">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>User</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recent_logs)): ?>
                    <?php foreach ($recent_logs as $log): ?>
                        <tr>
                            <td data-label="Time"><?= date('Y-m-d H:i', strtotime($log['timestamp'])) ?></td>
                            <td data-label="User"><?= esc($log['username'] ?? 'System') ?></td>
                            <td data-label="Action"><?= esc($log['action']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 40px; color: var(--slate-500);">No recent activity</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->include('templates/footer') ?>

<script src="<?= base_url('js/ws-client.js') ?>"></script>
<script src="<?= base_url('js/queue-sync.js') ?>"></script>
<script>
    QueueSync.init({
        pollInterval: 5000,
        refreshUrl:   '<?= base_url('admin/dashboard') ?>',
        tableSelector: '.table-striped tbody',
        extraRefresh: function(newDoc) {
            // Update all stat cards
            var newCards = newDoc.querySelectorAll('.card.text-white .card-body h2');
            var curCards = document.querySelectorAll('.card.text-white .card-body h2');
            newCards.forEach(function(card, i) { if (curCards[i]) curCards[i].textContent = card.textContent; });
        }
    });
</script>




