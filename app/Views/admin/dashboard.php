<?= $this->include('templates/header') ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Admin Dashboard </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="tq-btn tq-btn--primary" data-bs-toggle="modal" data-bs-target="#reportFilterModal">
            <i class="fas fa-file-invoice"></i> Generate Report
        </button>
    </div>
</div>

<?= view('admin/modals/report_filter', ['destinations' => $destinations, 'vehicleTypes' => $vehicleTypes]) ?>

<?php if (!empty($unassignedVehicles)): ?>
    <div class="alert alert-warning d-flex align-items-center mb-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
        <div>
            <strong><?= $unassignedVehicles ?> vehicle<?= $unassignedVehicles > 1 ? 's have' : ' has' ?> no assigned route.</strong>
            Please assign routes before queueing them.
            <a href="<?= base_url('admin/vehicles') ?>" class="alert-link ms-1">Go to Vehicle Register →</a>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($unassignedStaff)): ?>
    <div class="alert alert-warning d-flex align-items-center mb-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
        <div>
            <strong><?= $unassignedStaff ?> dispatcher<?= $unassignedStaff > 1 ? 's have' : ' has' ?> no routes assigned.</strong>
            They cannot manage any queue until routes are assigned.
            <a href="<?= base_url('admin/users') ?>" class="alert-link ms-1">Go to User Management →</a>
        </div>
    </div>
<?php endif; ?>

<div class="tq-stat-grid mb-4">
    <a href="<?= base_url('admin/vehicles') ?>" class="tq-stat">
        <span class="tq-stat__icon is-cyan"><i class="bi bi-truck"></i></span>
        <span class="tq-stat__body">
            <span class="tq-stat__value"><?= $stats['vehicles'] ?></span>
            <span class="tq-stat__label">Vehicles</span>
        </span>
    </a>
    <a href="<?= base_url('admin/routes') ?>" class="tq-stat">
        <span class="tq-stat__icon is-mint"><i class="bi bi-map"></i></span>
        <span class="tq-stat__body">
            <span class="tq-stat__value"><?= $stats['routes'] ?></span>
            <span class="tq-stat__label">Routes</span>
        </span>
    </a>
    <a href="<?= base_url('admin/users') ?>" class="tq-stat">
        <span class="tq-stat__icon is-amber"><i class="bi bi-people"></i></span>
        <span class="tq-stat__body">
            <span class="tq-stat__value"><?= $stats['users'] ?></span>
            <span class="tq-stat__label">Users</span>
        </span>
    </a>
    <a href="<?= base_url('admin/logs') ?>" class="tq-stat">
        <span class="tq-stat__icon is-rose"><i class="bi bi-journal-text"></i></span>
        <span class="tq-stat__body">
            <span class="tq-stat__value"><?= $stats['logs'] ?></span>
            <span class="tq-stat__label">Logs</span>
        </span>
    </a>
</div>

<div class="tq-board-head">
    <h2 class="tq-section-title"><i class="fas fa-wave-square"></i> Recent Activity</h2>
    <span class="tq-live"><span class="tq-live__dot"></span> Live</span>
</div>
<div class="tq-panel">
    <div class="table-responsive table-responsive-card">
        <table class="table table-striped table-hover mb-0">
            <thead class="table-light">
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
                        <td colspan="3" class="text-center text-muted py-3">No recent activity</td>
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
            // Update all stat tiles (same data, new markup)
            var newCards = newDoc.querySelectorAll('.tq-stat__value');
            var curCards = document.querySelectorAll('.tq-stat__value');
            newCards.forEach(function(card, i) { if (curCards[i]) curCards[i].textContent = card.textContent; });
        }
    });
</script>




