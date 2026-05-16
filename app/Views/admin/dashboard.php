<?= $this->include('templates/header') ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Admin Dashboard </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#reportFilterModal">
            <i class="fas fa-file-invoice me-1 text-white"></i> Generate Report
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

<div class="row">
    <div class="col-12 col-md-6 col-xl-3 mb-4">
        <div class="card text-white bg-primary shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase mb-1">Vehicles</h6>
                        <h2 class="mb-0"><?= $stats['vehicles'] ?> </h2>
                    </div>
                    <i class="bi bi-truck fs-1 opacity-50"></i>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between small">
                <a class="text-white stretched-link" href="<?= base_url('admin/vehicles') ?>">View Details</a>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3 mb-4">
        <div class="card text-white bg-success shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase mb-1">Routes</h6>
                        <h2 class="mb-0"><?= $stats['routes'] ?> </h2>
                    </div>
                    <i class="bi bi-map fs-1 opacity-50"></i>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between small">
                <a class="text-white stretched-link" href="<?= base_url('admin/routes') ?>">View Details</a>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3 mb-4">
        <div class="card text-white bg-info shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase mb-1">Users</h6>
                        <h2 class="mb-0"><?= $stats['users'] ?> </h2>
                    </div>
                    <i class="bi bi-people fs-1 opacity-50"></i>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between small">
                <a class="text-white stretched-link" href="<?= base_url('admin/users') ?>">View Details</a>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3 mb-4">
        <div class="card text-white bg-secondary shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase mb-1">Logs</h6>
                        <h2 class="mb-0"><?= $stats['logs'] ?> </h2>
                    </div>
                    <i class="bi bi-journal-text fs-1 opacity-50"></i>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between small">
                <a class="text-white stretched-link" href="<?= base_url('admin/logs') ?>">View Details</a>
            </div>
        </div>
    </div>
</div>



<h2 class="mt-4">Recent Activity </h2>
<div class="card shadow-sm">
    <div class="table-responsive">
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
                            <td><?= date('Y-m-d H:i', strtotime($log['timestamp'])) ?></td>
                            <td><?= esc($log['username'] ?? 'System') ?></td>
                            <td><?= esc($log['action']) ?></td>
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
            // Update all stat cards
            var newCards = newDoc.querySelectorAll('.card.text-white .card-body h2');
            var curCards = document.querySelectorAll('.card.text-white .card-body h2');
            newCards.forEach(function(card, i) { if (curCards[i]) curCards[i].textContent = card.textContent; });
        }
    });
</script>




