<?= $this->include('templates/header') ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">

<style>
    /* Premium Hover & Transitions for Table Rows */
    .table-modern tbody tr {
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }
    .table-modern tbody tr:hover {
        background-color: var(--primary-soft, #f1f5f9) !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
        z-index: 2;
    }
    /* Accent hover indicator */
    .table-modern tbody tr td:first-child {
        position: relative;
        transition: border-left-color 0.2s ease;
    }
    .table-modern tbody tr:hover td:first-child {
        border-left: 3px solid var(--primary, #1565c0) !important;
    }
    /* Scale inner badges and icons smoothly on hover */
    .table-modern tbody tr:hover .badge-modern {
        transform: scale(1.05);
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .table-modern tbody tr:hover .vehicle-type-icon img {
        transform: scale(1.1) rotate(2deg);
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .vehicle-type-icon img, .badge-modern {
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .plate-number {
        font-weight: 700;
        font-family: 'Courier New', monospace;
        font-size: 14px;
        background: #f1f5f9;
        color: #1e293b;
        padding: 4px 10px;
        border-radius: 6px;
        display: inline-block;
        letter-spacing: 0.5px;
    }
</style>

<div class="page-header-modern fade-in">
    <h1 class="page-title-modern">
        <i class="bi bi-calendar3"></i>
        Vehicle Schedules
    </h1>
</div>

<!-- Modern Stat Cards block -->
<div class="row mb-4">
    <div class="col-12 col-md-6 mb-4">
        <div class="stat-card-modern blue-accent fade-in">
            <div class="stat-card-icon" style="background: #DBEAFE; color: #1565c0;">
                <i class="bi bi-bus-front"></i>
            </div>
            <div class="stat-card-value"><?= count($schedules) ?></div>
            <div class="stat-card-label">Today's Departures</div>
        </div>
    </div>
    <div class="col-12 col-md-6 mb-4">
        <div class="stat-card-modern fade-in">
            <div class="stat-card-icon" style="background: #F1F5F9; color: #475569;">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="stat-card-value"><?= strtoupper(date('D, M j, Y')) ?></div>
            <div class="stat-card-label">Schedule Date</div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="modern-card shadow-modern fade-in mb-4">
    <div class="modern-card-body">
        <form method="get" action="<?= base_url('schedules') ?>" class="row g-3">
            <div class="col-md-4">
                <label class="form-label-modern">Vehicle Type</label>
                <select name="type" class="form-select-modern">
                    <option value="">All Types</option>
                    <option value="van" <?= $vehicle_type == 'van' ? 'selected' : '' ?>>Van</option>
                    <option value="jeepney" <?= $vehicle_type == 'jeepney' ? 'selected' : '' ?>>Jeepney</option>
                    <option value="minibus" <?= $vehicle_type == 'minibus' ? 'selected' : '' ?>>Minibus</option>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label-modern">Destination</label>
                <select name="destination" class="form-select-modern">
                    <option value="">All Destinations</option>
                    <?php foreach ($all_destinations as $dest): ?>
                        <option value="<?= esc($dest) ?>" <?= $destination == $dest ? 'selected' : '' ?>><?= esc($dest) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn-modern btn-modern-primary w-100">
                    <i class="bi bi-funnel-fill"></i> Apply Filters
                </button>
            </div>
        </form>
        <?php if ($vehicle_type || $destination): ?>
            <div class="mt-3 pt-3 border-top d-flex gap-2 align-items-center flex-wrap">
                <span style="font-size:13px; font-weight:600; color:#64748b; text-transform:uppercase;">Active Filters:</span>
                <?php if ($vehicle_type): ?>
                    <?= vehicle_type_badge($vehicle_type) ?>
                <?php endif; ?>
                <?php if ($destination): ?>
                    <span class="badge-modern badge-modern-info"><?= esc($destination) ?></span>
                <?php endif; ?>
                <a href="<?= base_url('schedules') ?>" class="btn-modern btn-modern-sm btn-modern-outline ms-2">
                    <i class="bi bi-x-circle"></i> Clear Filters
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Schedules Table -->
<div class="modern-card shadow-modern fade-in">
    <div class="modern-card-header">
        <span class="modern-card-title">
            <i class="bi bi-list-task" style="color: var(--primary-red);"></i>
            Today's Schedules
        </span>
        <span class="badge-modern badge-modern-primary"><?= count($schedules) ?> vehicles</span>
    </div>
    <div class="modern-card-body p-0">
        <div class="table-responsive">
            <table class="table-modern">
                <thead>
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
                                <td data-label="Queue #">
                                    <span class="badge-modern badge-modern-primary">
                                        #<?= esc($s['position']) ?>
                                    </span>
                                </td>
                                <td data-label="Plate Number">
                                    <span class="plate-number"><?= esc($s['plate_number']) ?></span>
                                </td>
                                <td data-label="Type">
                                    <?php
                                        $vType = $s['vehicle_type'] ?? '';
                                        $imgFile = vehicle_type_image($vType);
                                    ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="vehicle-type-icon <?= vehicle_type_class($vType) ?>">
                                            <img src="<?= base_url('images/' . $imgFile) ?>" alt="<?= vehicle_type_label($vType) ?>" style="height:36px; width:auto;" title="<?= vehicle_type_label($vType) ?>">
                                        </span>
                                        <?= vehicle_type_badge($vType) ?>
                                    </div>
                                </td>
                                <td data-label="Route">
                                    <div class="d-flex align-items-center gap-2">
                                        <strong><?= esc($s['origin']) ?></strong>
                                        <i class="bi bi-arrow-right text-muted"></i>
                                        <strong><?= esc($s['destination']) ?></strong>
                                    </div>
                                </td>
                                <td data-label="Est. Departure">
                                    <?php if ($s['status'] === 'departed' && $s['departure_time']): ?>
                                        <span class="badge-modern badge-modern-info">
                                            <?= date('H:i', strtotime($s['departure_time'])) ?>
                                        </span>
                                        <small class="text-muted d-block mt-1">Departed</small>
                                    <?php elseif ($s['is_full']): ?>
                                        <span class="badge-modern badge-modern-success">FULL — Ready</span>
                                    <?php else: ?>
                                        <span class="badge-modern badge-modern-primary">
                                            <?= !empty($s['estimated_departure']) ? date('H:i', strtotime($s['estimated_departure'])) : 'Waiting' ?>
                                        </span>
                                        <small class="text-muted d-block mt-1"><?= $s['current_passengers'] ?>/<?= $s['capacity'] ?> passengers</small>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Status">
                                    <?php
                                    $statusClass = match ($s['status']) {
                                        'boarding' => 'badge-modern-success',
                                        'waiting' => 'badge-modern-warning',
                                        'departed' => 'badge-modern-primary',
                                        'canceled' => 'badge-modern-danger',
                                        default => 'badge-modern-info'
                                    };
                                    $statusIcon = match ($s['status']) {
                                        'boarding' => 'bi-play-circle-fill',
                                        'waiting' => 'bi-hourglass-split',
                                        'departed' => 'bi-check-circle-fill',
                                        'canceled' => 'bi-x-circle-fill',
                                        default => 'bi-info-circle-fill'
                                    };
                                    ?>
                                    <span class="badge-modern <?= $statusClass ?>">
                                        <i class="bi <?= $statusIcon ?> me-1"></i><?= strtoupper($s['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x fs-1 mb-3 d-block"></i>
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
            var newBadge = newDoc.querySelector('.badge-modern-primary');
            var curBadge = document.querySelector('.badge-modern-primary');
            if (newBadge && curBadge) curBadge.textContent = newBadge.textContent;
        }
    });
</script>
