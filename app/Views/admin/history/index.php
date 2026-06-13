<?= view('templates/header', ['title' => $title]) ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">

<style>
    .table-modern .plate-number {
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
    .table-modern .driver-cell {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
        color: #2c3e50;
    }
    .table-modern .driver-cell i {
        color: #66788a;
        font-size: 12px;
    }
    .table-modern .route-info {
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
</style>

<div class="page-header-modern fade-in">
    <h1 class="page-title-modern">
        <i class="bi bi-clock-history"></i>
        Departure History
    </h1>
    <div class="d-flex gap-2">
        <button type="button" class="btn-modern btn-modern-primary" data-bs-toggle="modal" data-bs-target="#reportFilterModal">
            <i class="bi bi-file-earmark-text"></i> Generate Report
        </button>
        <?php if ($stats['total'] > 0): ?>
                <form action="<?= base_url('admin/history/delete-all') ?>" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete ALL departure records? This action cannot be undone?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-modern btn-modern-sm btn-action-delete" title="Delete All">
                        <i class="bi bi-trash-fill"></i> Delete All
                    </button>
                </form>
        <?php endif; ?>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert-modern alert-modern-success fade-in">
        <i class="bi bi-check-circle-fill alert-modern-icon"></i>
        <div><?= esc(session()->getFlashdata('success')) ?></div>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert-modern alert-modern-danger fade-in">
        <i class="bi bi-exclamation-triangle-fill alert-modern-icon"></i>
        <div><?= esc(session()->getFlashdata('error')) ?></div>
    </div>
<?php endif; ?>

<!-- Stat Cards -->
<div class="row mb-4">
    <div class="col-12 col-md-6 col-xl-3 mb-4">
        <div class="stat-card-modern blue-accent fade-in">
            <div class="stat-card-icon" style="background: #DBEAFE; color: #1565c0;">
                <i class="bi bi-truck"></i>
            </div>
            <div class="stat-card-value"><?= $stats['total'] ?></div>
            <div class="stat-card-label">Total Departed</div>
            <a href="<?= base_url('admin/history') ?>" class="stat-card-link">View Details <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3 mb-4">
        <div class="stat-card-modern success-accent fade-in">
            <div class="stat-card-icon" style="background: #D1FAE5; color: #059669;">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="stat-card-value"><?= $stats['today'] ?></div>
            <div class="stat-card-label">Today</div>
            <a href="<?= base_url('admin/history') . '?from_date=' . date('Y-m-d') . '&to_date=' . date('Y-m-d') ?>" class="stat-card-link">View Details <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3 mb-4">
        <div class="stat-card-modern info-accent fade-in">
            <div class="stat-card-icon" style="background: #DBEAFE; color: #1976D2;">
                <i class="bi bi-calendar-month"></i>
            </div>
            <div class="stat-card-value"><?= $stats['month'] ?></div>
            <div class="stat-card-label">This Month</div>
            <a href="<?= base_url('admin/history') . '?from_date=' . date('Y-m-01') . '&to_date=' . date('Y-m-t') ?>" class="stat-card-link">View Details <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3 mb-4">
        <div class="stat-card-modern fade-in">
            <div class="stat-card-icon" style="background: #F1F5F9; color: #475569;">
                <i class="bi bi-calendar-range"></i>
            </div>
            <div class="stat-card-value"><?= $stats['year'] ?></div>
            <div class="stat-card-label">This Year</div>
            <a href="<?= base_url('admin/history') . '?from_date=' . date('Y-01-01') . '&to_date=' . date('Y-12-31') ?>" class="stat-card-link">View Details <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</div>

<!-- Filters & Search -->
<div class="modern-card shadow-modern fade-in mb-4">
    <div class="modern-card-body">
        <form method="get" action="<?= base_url('admin/history') ?>" class="row g-3">
            <div class="col-12 col-md-4">
                <label class="form-label-modern">Search Records</label>
                <div class="input-group-modern">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" class="input-modern border-start-0" name="q" placeholder="Plate, Driver, or Destination..." value="<?= esc($search ?? '') ?>">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label-modern">From Date</label>
                <input type="date" class="input-modern" name="from_date" value="<?= esc($from_date ?? '') ?>">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label-modern">To Date</label>
                <input type="date" class="input-modern" name="to_date" value="<?= esc($to_date ?? '') ?>">
            </div>
            <div class="col-12 col-md-2 d-flex align-items-end">
                <button type="submit" class="btn-modern btn-modern-primary w-100">
                    <i class="bi bi-funnel-fill"></i> Filter
                </button>
            </div>
        </form>
        
        <?php if (!empty($search) || !empty($from_date) || !empty($to_date)): ?>
            <div class="mt-3 d-flex align-items-center gap-2 flex-wrap">
                <span style="font-size:13px; font-weight:600; color:#64748b; text-transform:uppercase;">Active Filters:</span>
                <?php if ($search): ?>
                    <span class="badge-modern badge-modern-info">"<?= esc($search) ?>"</span>
                <?php endif; ?>
                <?php if ($from_date): ?>
                    <span class="badge-modern badge-modern-primary">From: <?= esc($from_date) ?></span>
                <?php endif; ?>
                <?php if ($to_date): ?>
                    <span class="badge-modern badge-modern-primary">To: <?= esc($to_date) ?></span>
                <?php endif; ?>
                <a href="<?= base_url('admin/history') ?>" class="btn-modern btn-modern-sm btn-modern-outline ms-2">
                    <i class="bi bi-x-circle"></i> Clear All
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Departures Table -->
<div class="modern-card shadow-modern fade-in mb-4">
    <div class="modern-card-header">
        <span class="modern-card-title">
            <i class="bi bi-list-ul" style="color: var(--primary-red);"></i>
            Departure Records
        </span>
    </div>
    <div class="modern-card-body">
        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th>Vehicle</th>
                        <th>Plate Number</th>
                        <th>Driver</th>
                        <th>Route</th>
                        <th>Departure Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($departures)): ?>
                        <?php foreach ($departures as $item): ?>
                            <tr>
                                <td data-label="Vehicle">
                                    <?php
                                        $imgMap = ['van' => 'van.png', 'jeepney' => 'jeep.png', 'minibus' => 'minibus.png'];
                                        $vType = strtolower($item['vehicle_type'] ?? '');
                                        $imgFile = $imgMap[$vType] ?? 'van.png';
                                    ?>
                                    <span class="vehicle-type-icon <?= vehicle_type_class($vType) ?>">
                                        <img src="<?= base_url('images/' . $imgFile) ?>" alt="<?= vehicle_type_label($vType) ?>" style="height:32px; width:auto;" title="<?= vehicle_type_label($vType) ?>">
                                    </span>
                                    <div class="mt-1"><?= vehicle_type_badge($vType) ?></div>
                                </td>
                                <td data-label="Plate Number">
                                    <span class="plate-number"><?= esc($item['plate_number']) ?></span>
                                </td>
                                <td data-label="Driver">
                                    <div class="driver-cell">
                                        <i class="bi bi-person-badge"></i>
                                        <?= esc($item['driver_name'] ?? '—') ?>
                                    </div>
                                </td>
                                <td data-label="Route">
                                    <div class="route-info">
                                        <i class="bi bi-geo-alt" style="color: var(--primary-red);"></i>
                                        <span><?= esc($item['origin'] ?? '') ?> → <?= esc($item['destination'] ?? '') ?></span>
                                    </div>
                                </td>
                                <td data-label="Departure Time">
                                    <span class="badge-modern badge-modern-primary"><?= date('M d, Y h:i A', strtotime($item['departure_time'])) ?></span>
                                </td>
                                <td data-label="Status">
                                    <span class="status-badge status-departed">
                                        <i class="bi bi-check-circle-fill"></i> Departed
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                No departure records found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($pager): ?>
        <div class="modern-card-footer bg-white py-3 d-flex justify-content-center">
            <?= $pager->links() ?>
        </div>
    <?php endif; ?>
</div>

<?= view('admin/modals/report_filter', ['destinations' => $destinations, 'vehicleTypes' => $vehicleTypes]) ?>

<?= view('templates/footer') ?>

<script src="<?= base_url('js/ws-client.js') ?>"></script>
<script src="<?= base_url('js/queue-sync.js') ?>"></script>
<script>
    // Initialize real-time sync (polling + WebSocket)
    QueueSync.init({
        onlyWS:        true,
        pollInterval:  30000,
        refreshUrl:    window.location.href,
        tableSelector: '.table-hover tbody',
        extraRefresh:  function(newDoc) {
            // Update stat cards
            var newCards = newDoc.querySelectorAll('.card.text-white h2');
            var curCards = document.querySelectorAll('.card.text-white h2');
            newCards.forEach(function(card, i) { if (curCards[i]) curCards[i].textContent = card.textContent; });

            // Update pagination
            var newPager = newDoc.querySelector('.card-footer');
            var curPager = document.querySelector('.card-footer');
            if (newPager && curPager) curPager.innerHTML = newPager.innerHTML;
        }
    });
</script>
