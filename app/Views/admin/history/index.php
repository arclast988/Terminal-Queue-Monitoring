<?= view('templates/header', ['title' => $title]) ?>

<style>
    .table .plate-number {
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
    .table .driver-cell {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
        color: #2c3e50;
    }
    .table .driver-cell i {
        color: #66788a;
        font-size: 12px;
    }
    .table .route-info {
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    .table .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }
    .table .status-departed {
        background: #f1f5f9;
        color: #64748b;
    }
    .table .status-departed i { color: #FF9800; }
</style>

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-history text-primary me-2"></i> Departure History</h1>
    <div class="btn-toolbar mb-2 mb-md-0 d-flex gap-2 align-items-center">
        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#reportFilterModal">
            <i class="fas fa-file-invoice me-1"></i> Generate Report
        </button>
        <?php if ($stats['total'] > 0): ?>
            <form action="<?= base_url('admin/history/delete-all') ?>" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete ALL departure records? This action cannot be undone.')">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-danger shadow-sm">
                    <i class="bi bi-trash-fill me-1"></i> Delete All
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<!-- Stat Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-primary shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase mb-1">Total Departed</h6>
                        <h2 class="mb-0"><?= $stats['total'] ?></h2>
                    </div>
                    <i class="bi bi-truck fs-1 opacity-50"></i>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between small">
                <span class="text-white">All Time</span>
                <a class="text-white stretched-link text-decoration-none" href="<?= base_url('admin/history') ?>">View Details <i class="bi bi-chevron-right"></i></a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-success shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase mb-1">Today</h6>
                        <h2 class="mb-0"><?= $stats['today'] ?></h2>
                    </div>
                    <i class="bi bi-calendar-check fs-1 opacity-50"></i>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between small">
                <span class="text-white"><?= date('M d, Y') ?></span>
                <a class="text-white stretched-link text-decoration-none" href="<?= base_url('admin/history') . '?from_date=' . date('Y-m-d') . '&to_date=' . date('Y-m-d') ?>">View Details <i class="bi bi-chevron-right"></i></a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-info shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase mb-1">This Month</h6>
                        <h2 class="mb-0"><?= $stats['month'] ?></h2>
                    </div>
                    <i class="bi bi-calendar-month fs-1 opacity-50"></i>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between small">
                <span class="text-white"><?= date('F Y') ?></span>
                <a class="text-white stretched-link text-decoration-none" href="<?= base_url('admin/history') . '?from_date=' . date('Y-m-01') . '&to_date=' . date('Y-m-t') ?>">View Details <i class="bi bi-chevron-right"></i></a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-secondary shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase mb-1">This Year</h6>
                        <h2 class="mb-0"><?= $stats['year'] ?></h2>
                    </div>
                    <i class="bi bi-calendar-range fs-1 opacity-50"></i>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between small">
                <span class="text-white"><?= date('Y') ?></span>
                <a class="text-white stretched-link text-decoration-none" href="<?= base_url('admin/history') . '?from_date=' . date('Y-01-01') . '&to_date=' . date('Y-12-31') ?>">View Details <i class="bi bi-chevron-right"></i></a>
            </div>
        </div>
    </div>
</div>

<!-- Filters & Search -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-4">
        <form method="get" action="<?= base_url('admin/history') ?>" class="row g-3">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted text-uppercase">Search Records</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control border-start-0" name="q" placeholder="Plate, Driver, or Destination..." value="<?= esc($search ?? '') ?>">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted text-uppercase">From Date</label>
                <input type="date" class="form-control" name="from_date" value="<?= esc($from_date ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted text-uppercase">To Date</label>
                <input type="date" class="form-control" name="to_date" value="<?= esc($to_date ?? '') ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100 fw-bold">
                    <i class="bi bi-funnel-fill me-1"></i> Filter
                </button>
            </div>
        </form>
        
        <?php if (!empty($search) || !empty($from_date) || !empty($to_date)): ?>
            <div class="mt-3 d-flex align-items-center gap-2">
                <span class="text-muted small">Active Filters:</span>
                <?php if ($search): ?>
                    <span class="badge bg-info text-dark">"<?= esc($search) ?>"</span>
                <?php endif; ?>
                <?php if ($from_date): ?>
                    <span class="badge bg-secondary">From: <?= esc($from_date) ?></span>
                <?php endif; ?>
                <?php if ($to_date): ?>
                    <span class="badge bg-secondary">To: <?= esc($to_date) ?></span>
                <?php endif; ?>
                <a href="<?= base_url('admin/history') ?>" class="btn btn-link btn-sm text-decoration-none p-0 ms-2">
                    <i class="bi bi-x-circle text-danger"></i> Clear All
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Departures Table -->
<div class="card shadow mb-4">
    <div class="card-header d-flex justify-content-between align-items-center" style="padding:10px 16px;">
        <span style="font-weight:600; font-size:14px; color:#1e293b;"><i class="bi bi-list-ul me-1"></i> Departure Records</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive table-responsive-card">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4">Vehicle</th>
                        <th>Plate Number</th>
                        <th>Driver</th>
                        <th>Route</th>
                        <th>Departure Time</th>
                        <th class="text-end px-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($departures)): ?>
                        <?php foreach ($departures as $item): ?>
                            <tr>
                                <td class="px-4" data-label="Vehicle">
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
                                        <i class="fas fa-user-tie"></i>
                                        <?= esc($item['driver_name'] ?? '—') ?>
                                    </div>
                                </td>
                                <td data-label="Route">
                                    <div class="route-info">
                                        <span style="color: #66788a; font-size: 13px;"><?= esc($item['origin']) ?></span>
                                        <i class="bi bi-arrow-right" style="color: #1565c0; font-size: 12px;"></i>
                                        <span style="font-weight: 700; color: #0d47a1;"><?= esc($item['destination']) ?></span>
                                    </div>
                                </td>
                                <td data-label="Departure Time">
                                    <span class="status-badge status-departed">
                                        <i class="fas fa-clock"></i>
                                        <?= date('M d, Y h:i A', strtotime($item['departure_time'])) ?>
                                    </span>
                                </td>
                                <td class="text-end px-4" data-label="Action">
                                    <form action="<?= base_url('admin/history/delete/' . $item['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Delete departure record for vehicle <?= esc($item['plate_number']) ?>?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
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
        <div class="card-footer bg-white py-3 d-flex justify-content-center">
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


