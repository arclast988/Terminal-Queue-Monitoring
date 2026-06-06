<?= $this->include('templates/header') ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dispatcher Dashboard</h1>
</div>

<div class="tq-stat-grid mb-4">
    <a href="<?= base_url('staff/queue') ?>" class="tq-stat">
        <span class="tq-stat__icon is-mint"><i class="bi bi-people-fill"></i></span>
        <span class="tq-stat__body">
            <span class="tq-stat__value"><?= $active_queue_count ?></span>
            <span class="tq-stat__label">Active in Queue &middot; Manage &rarr;</span>
        </span>
    </a>

    <?php foreach ($terminals as $terminal): ?>
        <div class="tq-stat">
            <span class="tq-stat__icon is-cyan"><i class="bi bi-building"></i></span>
            <span class="tq-stat__body">
                <span class="tq-stat__value"><?= esc($terminal['capacity']) ?></span>
                <span class="tq-stat__label"><?= esc($terminal['name']) ?> &middot; <?= esc($terminal['location']) ?></span>
            </span>
        </div>
    <?php endforeach; ?>
</div>

<div class="tq-board-head">
    <h2 class="tq-section-title"><i class="fas fa-plane-departure"></i> Recent Departures</h2>
    <span class="tq-live"><span class="tq-live__dot"></span> Live</span>
</div>
<div class="tq-panel">
    <div class="table-responsive table-responsive-card">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Type</th>
                    <th>Plate Number</th>
                    <th>Departure Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recent_departures)): ?>
                    <?php foreach ($recent_departures as $dept): ?>
                        <tr>
                            <td data-label="Type">
                                <?php 
                                    $imgMap = ['van' => 'van.png', 'jeepney' => 'jeep.png', 'minibus' => 'minibus.png'];
                                    $vType = strtolower($dept['vehicle_type'] ?? '');
                                    $imgFile = $imgMap[$vType] ?? 'van.png';
                                ?>
                                <span class="vehicle-type-icon <?= vehicle_type_class($vType) ?>">
                                    <img src="<?= base_url('images/' . $imgFile) ?>" alt="<?= vehicle_type_label($vType) ?>" style="height:36px; width:auto;" title="<?= vehicle_type_label($vType) ?>">
                                </span>
                            </td>
                            <td class="fw-bold" data-label="Plate Number"><?= esc($dept['plate_number']) ?></td>
                            <td data-label="Departure Time"><?= date('h:i A', strtotime($dept['departure_time'])) ?></td>
                            <td data-label="Status"><span class="badge bg-success">Departed</span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-3">No recent departures today.</td>
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
        pollInterval: 3000,
        refreshUrl:   '<?= base_url('staff/dashboard') ?>',
        tableSelector: '.table-hover tbody',
        extraRefresh: function(newDoc) {
            // Update stat tiles (active-in-queue + terminal capacities) — same data, new markup
            var newVals = newDoc.querySelectorAll('.tq-stat__value');
            var curVals = document.querySelectorAll('.tq-stat__value');
            newVals.forEach(function(v, i) { if (curVals[i]) curVals[i].textContent = v.textContent; });
        }
    });
</script>
