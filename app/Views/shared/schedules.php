<?= $this->include('templates/header') ?>

<style>
    /* Modern Hero Section - Role-Aware */
    .page-hero {
        padding: 40px 30px;
        margin: -20px -24px 30px -24px;
        border-radius: 0 0 24px 24px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    }

    /* Admin hero: deep navy to red */
    .page-hero.hero-admin {
        background: linear-gradient(135deg, rgba(30, 58, 95, 0.95), rgba(198, 40, 40, 0.85)) !important;
    }

    /* Staff hero: deep blue to teal */
    .page-hero.hero-staff {
        background: linear-gradient(135deg, rgba(15, 32, 65, 0.95), rgba(21, 101, 192, 0.85)) !important;
    }

    .page-hero h1 {
        color: white !important;
        font-size: 32px;
        font-weight: 800;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .page-hero .subtitle {
        color: rgba(255, 255, 255, 0.8);
        font-size: 15px;
        margin-bottom: 20px;
    }

    .page-hero .stats-row {
        display: flex;
        gap: 25px;
        flex-wrap: wrap;
    }

    .page-hero .stat-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: rgba(255, 255, 255, 0.9);
        font-size: 14px;
    }

    .page-hero .stat-item i {
        width: 32px;
        height: 32px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .page-hero .stat-value {
        font-weight: 700;
        font-size: 18px;
        color: white;
    }
</style>

<?php $heroClass = (session()->get('role') === 'staff') ? 'hero-staff' : 'hero-admin'; ?>

<!-- Modern Page Hero -->
<div class="page-hero <?= $heroClass ?>">
    <h1><i class="fas fa-calendar-alt"></i> Vehicle Schedules</h1>
    <p class="subtitle">View departure times, routes, and real-time vehicle status</p>
    <div class="stats-row">
        <div class="stat-item">
            <i class="fas fa-bus"></i>
            <div>
                <div class="stat-value"><?= count($schedules) ?></div>
                <small>Today's Departures</small>
            </div>
        </div>
        <div class="stat-item">
            <i class="fas fa-clock"></i>
            <div>
                <div class="stat-value"><?= date('D, M j') ?></div>
                <small>Schedule Date</small>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="get" action="<?= base_url('schedules') ?>" class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-bold">Vehicle Type</label>
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="van" <?= $vehicle_type == 'van' ? 'selected' : '' ?>>Van</option>
                    <option value="jeepney" <?= $vehicle_type == 'jeepney' ? 'selected' : '' ?>>Jeepney</option>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label fw-bold">Destination</label>
                <select name="destination" class="form-select">
                    <option value="">All Destinations</option>
                    <optgroup label="Van Destinations">
                        <?php foreach ($van_destinations as $dest): ?>
                            <option value="<?= $dest ?>" <?= $destination == $dest ? 'selected' : '' ?>><?= $dest ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                    <optgroup label="Jeepney Destinations">
                        <?php foreach ($jeepney_destinations as $dest): ?>
                            <option value="<?= $dest ?>" <?= $destination == $dest ? 'selected' : '' ?>><?= $dest ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i> Apply Filters
                </button>
            </div>
        </form>
        <?php if ($vehicle_type || $destination): ?>
            <div class="mt-3 pt-3 border-top d-flex gap-2 align-items-center">
                <span class="badge bg-primary"><?= $vehicle_type ? ucfirst($vehicle_type) : 'All Types' ?></span>
                <?php if ($destination): ?><span
                        class="badge bg-info text-dark"><?= esc($destination) ?></span><?php endif; ?>
                <a href="<?= base_url('schedules') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-times"></i> Clear
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Schedules Table -->
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="fas fa-list me-2"></i>Today's Schedules</h5>
        <span class="badge bg-primary"><?= count($schedules) ?> vehicles</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Queue #</th>
                        <th>Plate Number</th>
                        <th>Type</th>
                        <th>Route</th>
                        <th>Est. Departure</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($schedules)): ?>
                        <?php foreach ($schedules as $s): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-primary fs-6">
                                        #<?= esc($s['position']) ?>
                                    </span>
                                </td>
                                <td><code class="fw-bold"><?= esc($s['plate_number']) ?></code></td>
                                <td>
                                    <?php
                                        $imgMap = ['van' => 'van.png', 'jeepney' => 'jeep.png', 'minibus' => 'minibus.png'];
                                        $imgFile = $imgMap[$s['vehicle_type']] ?? 'van.png';
                                    ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?= base_url('images/' . $imgFile) ?>" alt="<?= ucfirst($s['vehicle_type']) ?>" style="height:45px; width:auto;" title="<?= ucfirst($s['vehicle_type']) ?>">
                                        <small class="text-muted fw-bold"><?= ucfirst($s['vehicle_type']) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <strong><?= esc($s['origin']) ?></strong>
                                    <i class="fas fa-arrow-right mx-1 text-muted small"></i>
                                    <strong><?= esc($s['destination']) ?></strong>
                                </td>
                                <td>
                                    <?php if ($s['status'] === 'departed' && $s['departure_time']): ?>
                                        <span class="badge bg-secondary fs-6">
                                            <?= date('g:i A', strtotime($s['departure_time'])) ?>
                                        </span>
                                        <small class="text-muted d-block">Departed</small>
                                    <?php elseif ($s['is_full']): ?>
                                        <span class="badge bg-success fs-6">FULL — Ready</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary fs-6">
                                            <?= date('g:i A', strtotime($s['estimated_departure'])) ?>
                                        </span>
                                        <small class="text-muted d-block"><?= $s['current_passengers'] ?>/<?= $s['capacity'] ?>
                                            passengers</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = match ($s['status']) {
                                        'boarding' => 'bg-success',
                                        'waiting' => 'bg-warning text-dark',
                                        'departed' => 'bg-secondary',
                                        'canceled' => 'bg-danger',
                                        default => 'bg-info'
                                    };
                                    ?>
                                    <span class="badge <?= $statusClass ?>"><?= strtoupper($s['status']) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-calendar-times fa-2x mb-3 d-block"></i>
                                <p class="mb-0">No scheduled departures found.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->include('templates/footer') ?>

<script src="<?= base_url('js/ws-client.js') ?>"></script>
<script src="<?= base_url('js/queue-sync.js') ?>"></script>
<script>
    // Initialize real-time sync (polling + WebSocket)
    QueueSync.init({
        onlyWS:        true,
        pollInterval:  3000,
        refreshUrl:    window.location.href,
        tableSelector: 'table tbody',
        extraRefresh:  function(newDoc) {
            // Update stats
            var newStats = newDoc.querySelectorAll('.stat-value');
            var curStats = document.querySelectorAll('.stat-value');
            newStats.forEach(function(s, i) { if (curStats[i]) curStats[i].textContent = s.textContent; });

            // Update vehicle count badge
            var newBadge = newDoc.querySelector('.badge.bg-primary');
            var curBadge = document.querySelector('.badge.bg-primary');
            if (newBadge && curBadge) curBadge.textContent = newBadge.textContent;
        }
    });
</script>