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

<div class="row">
    <div class="col-md-3 mb-4">
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
    <div class="col-md-3 mb-4">
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
    <div class="col-md-3 mb-4">
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
    <div class="col-md-3 mb-4">
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

<h2 class="mt-4"><i class="bi bi-list-ol"></i> Live Terminal Queue </h2>
<div class="card shadow mb-4">
    <div class="card-body p-0">
        <div class="table-responsive-lg">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center">Pos</th>
                        <th>Vehicle</th>
                        <th>Route (Origin - Dest)</th>
                        <th>Est. Departure</th>
                        <th class="text-center">Passengers Onboard</th>
                        <th>Status</th>
                        <th>Quick Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($queue)): ?>
                        <?php foreach ($queue as $item): ?>
                            <tr>
                                <td class="fw-bold text-center">#<?= $item['position'] ?></td>
                                <td>
                                    <span class="fw-bold"><?= esc($item['plate_number']) ?></span>
                                    <br><small class="text-muted">Max: <?= $item['capacity'] ?></small>
                                </td>
                                <td><?= esc($item['origin']) ?> - <?= esc($item['destination']) ?></td>
                                <td>
                                    <span class="fw-bold text-primary"><?= !empty($item['estimated_departure']) ? date('h:i A', strtotime($item['estimated_departure'])) : 'N/A' ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-2"
                                        id="passenger-controls-<?= $item['id'] ?>">
                                        <button onclick="updatePassengers(<?= $item['id'] ?>, 'decrement')"
                                            class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-dash-lg"></i>
                                        </button>
                                        <span
                                            class="fw-bold fs-5 passenger-count <?= $item['current_passengers'] >= $item['capacity'] ? 'text-danger' : '' ?>">
                                            <?= $item['current_passengers'] ?> / <?= $item['capacity'] ?>
                                        </span>
                                        <button onclick="updatePassengers(<?= $item['id'] ?>, 'increment')"
                                            class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    </div>
                                    <span class="badge bg-danger full-badge"
                                        style="<?= (int)$item['current_passengers'] >= (int)$item['capacity'] ? '' : 'display:none;' ?>">FULL
                                        CAPACITY</span>
                                </td>
                                <td>
                                    <span
                                        class="badge <?= $item['status'] == 'boarding' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                        <?= strtoupper($item['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-dark dropdown-toggle" type="button"
                                            data-bs-toggle="dropdown" data-bs-boundary="viewport">
                                            Manage Status
                                        </button>
                                        <ul class="dropdown-menu">
                                            <?php if ($item['status'] == 'waiting'): ?>
                                                <li>
                                                    <form action="<?= base_url('admin/queue/update/' . $item['id'] . '/boarding') ?>?ref=dashboard" method="post">
                                                        <?= csrf_field() ?>
                                                        <button type="submit" class="dropdown-item">Start Boarding</button>
                                                    </form>
                                                </li>
                                            <?php elseif ($item['status'] == 'boarding'): ?>
                                                <li>
                                                    <form action="<?= base_url('admin/queue/update/' . $item['id'] . '/departed') ?>?ref=dashboard" method="post">
                                                        <?= csrf_field() ?>
                                                        <button type="submit" class="dropdown-item">Depart Vehicle</button>
                                                    </form>
                                                </li>
                                            <?php endif; ?>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <form action="<?= base_url('admin/queue/update/' . $item['id'] . '/canceled') ?>?ref=dashboard" method="post" onsubmit="return confirm('Cancel trip?');">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="dropdown-item text-danger">Cancel Trip</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No vehicles currently in queue.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-light text-end">
        <a href="<?= base_url('admin/queue') ?>" class="btn btn-sm btn-primary">Full Queue Operations <i
                class="bi bi-arrow-right"></i></a>
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
<script src="<?= base_url('js/debounce-passengers.js') ?>"></script>
<script src="<?= base_url('js/queue-sync.js') ?>"></script>
<script>
    // Initialize debounced passenger controls
    PassengerDebounce.init({
        setUrl: '<?= base_url('admin/queue/setPassengers') ?>/{id}'
    });

    function updatePassengers(id, action) {
        var container = document.getElementById('passenger-controls-' + id);
        var countSpan = container ? container.querySelector('.passenger-count') : null;
        var capacity = 0;
        if (countSpan) {
            var parts = countSpan.textContent.trim().split('/');
            capacity = parseInt(parts[1], 10) || 0;
        }
        PassengerDebounce.adjust(id, action, capacity);
    }

    // Initialize real-time sync (polling + WebSocket)
    QueueSync.init({
        apiUrl:        '<?= base_url('api/queue-status') ?>',
        pollInterval:  3000,
        refreshUrl:    '<?= base_url('admin/dashboard') ?>',
        tableSelector: 'table tbody',
        extraRefresh:  function(newDoc) {
            // Update stat cards
            var newCards = newDoc.querySelectorAll('.card.text-white h2');
            var curCards = document.querySelectorAll('.card.text-white h2');
            newCards.forEach(function(card, i) { if (curCards[i]) curCards[i].textContent = card.textContent; });

            // Update activity log
            var newLogs = newDoc.querySelectorAll('.table-striped tbody');
            var curLogs = document.querySelectorAll('.table-striped tbody');
            if (newLogs[0] && curLogs[0]) curLogs[0].innerHTML = newLogs[0].innerHTML;
        }
    });
</script>





