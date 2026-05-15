<?= view('templates/header', ['title' => $title]) ?>

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-history text-primary me-2"></i> Departure History</h1>
    <div class="btn-toolbar mb-2 mb-md-0 d-flex gap-2">
        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#reportFilterModal">
            <i class="fas fa-file-invoice me-1"></i> Generate Report
        </button>
    </div>
</div>

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
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-secondary text-white py-3">
        <h5 class="mb-0"><i class="bi bi-list-ul"></i> Departure Records</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4">Vehicle</th>
                        <th>Plate Number</th>
                        <th>Driver / Owner</th>
                        <th>Route (Origin → Dest)</th>
                        <th>Departure Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($departures)): ?>
                        <?php foreach ($departures as $item): ?>
                            <tr>
                                <td class="px-4">
                                    <?php
                                        $imgMap = ['van' => 'van.png', 'jeepney' => 'jeep.png', 'minibus' => 'minibus.png'];
                                        $vType = strtolower($item['vehicle_type'] ?? '');
                                        $imgFile = $imgMap[$vType] ?? 'van.png';
                                    ?>
                                    <img src="<?= base_url('images/' . $imgFile) ?>" alt="<?= ucfirst($vType) ?>" style="height:32px; width:auto;" title="<?= ucfirst($vType) ?>">
                                    <small class="d-block text-muted" style="font-size:10px;"><?= ucfirst($vType) ?></small>
                                </td>
                                <td class="fw-bold"><?= esc($item['plate_number']) ?></td>
                                <td><?= esc($item['driver_name'] ?? $item['owner_name'] ?? '—') ?></td>
                                <td>
                                    <small class="text-muted"><?= esc($item['origin']) ?></small>
                                    <i class="bi bi-arrow-right text-primary mx-1"></i>
                                    <strong><?= esc($item['destination']) ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-secondary fs-6">
                                        <?= date('M d, Y h:i A', strtotime($item['departure_time'])) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
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



