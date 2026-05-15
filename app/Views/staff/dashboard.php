<?= $this->include('templates/header') ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dispatcher Dashboard</h1>
</div>

<div class="row">
    <div class="col-12 col-md-6 col-xl-4 mb-4">
        <div class="card bg-primary text-white shadow">
            <div class="card-body">
                <h6 class="text-uppercase small">Active in Queue</h6>
                <h2 class="mb-0"><?= $active_queue_count ?> Vehicles</h2>
            </div>
            <div class="card-footer border-0">
                <a href="<?= base_url('staff/queue') ?>" class="text-white small text-decoration-none">
                    Manage Queue <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>
    </div>
    
    <?php foreach ($terminals as $terminal): ?>
        <div class="col-12 col-md-6 col-xl-4 mb-4">
            <div class="card border-info shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted small text-uppercase"><?= esc($terminal['name']) ?></h6>
                    <div class="d-flex justify-content-between align-items-end">
                        <h3 class="mb-0"><?= esc($terminal['capacity']) ?> <small class="fs-6">pax capacity</small></h3>
                        <i class="bi bi-building fs-2 text-info opacity-25"></i>
                    </div>
                    <p class="mb-0 small text-muted"><?= esc($terminal['location']) ?></p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<h4 class="mt-4 mb-3">Recent Departures</h4>
<div class="card shadow-sm">
    <div class="table-responsive">
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
                            <td>
                                <?php 
                                    $imgMap = ['van' => 'van.png', 'jeepney' => 'jeep.png', 'minibus' => 'minibus.png'];
                                    $imgFile = $imgMap[$dept['vehicle_type']] ?? 'van.png';
                                ?>
                                <img src="<?= base_url('images/' . $imgFile) ?>" alt="<?= ucfirst($dept['vehicle_type']) ?>" style="height:36px; width:auto;" title="<?= ucfirst($dept['vehicle_type']) ?>">
                            </td>
                            <td class="fw-bold"><?= esc($dept['plate_number']) ?></td>
                            <td><?= date('h:i A', strtotime($dept['departure_time'])) ?></td>
                            <td><span class="badge bg-success">Departed</span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted py-3">No recent departures today.</td>
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
            // Update Active in Queue count
            var newCount = newDoc.querySelector('.card.bg-primary .card-body h2');
            var curCount = document.querySelector('.card.bg-primary .card-body h2');
            if (newCount && curCount) curCount.textContent = newCount.textContent;

            // Update stat cards (terminal capacity, etc.)
            var newCards = newDoc.querySelectorAll('.card.border-info .card-body');
            var curCards = document.querySelectorAll('.card.border-info .card-body');
            newCards.forEach(function(card, i) { if (curCards[i]) curCards[i].innerHTML = card.innerHTML; });
        }
    });
</script>
