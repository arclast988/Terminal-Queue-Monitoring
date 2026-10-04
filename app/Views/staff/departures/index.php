<?= view('templates/header', ['title' => $title]) ?>

<div class="page-header-modern fade-in flex-wrap gap-3">
    <div>
        <h1 class="page-title-modern mb-1"><i class="bi bi-clock-history"></i> Today Departures</h1>
        <p class="text-muted small mb-0"><?= esc(date('F j, Y', strtotime($today))) ?> &bull; Your assigned routes</p>
    </div>
    <a class="btn-modern btn-modern-primary" href="<?= esc(base_url('staff/departures/print') . '?' . http_build_query(array_filter($filters, static fn($value) => $value !== ''))) ?>" target="_blank" rel="noopener"><i class="bi bi-file-earmark-text"></i> Generate Report</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-6"><div class="stat-card-modern success-accent"><div class="stat-card-value"><?= (int) ($stats['departures'] ?? 0) ?></div><div class="stat-card-label">Departures today</div></div></div>
    <div class="col-6"><div class="stat-card-modern success-accent"><div class="stat-card-value"><?= (int) ($stats['passengers'] ?? 0) ?></div><div class="stat-card-label">Passengers today</div></div></div>
</div>

<form action="<?= base_url('staff/departures') ?>" method="get" class="modern-card shadow-modern mb-3">
    <div class="modern-card-body">
        <div class="row g-3 align-items-end">
            <div class="col-lg-4 col-md-6">
                <label class="form-label-modern" for="todayDepartureSearch">Search departures</label>
                <input type="search" class="form-control" id="todayDepartureSearch" name="q" value="<?= esc($filters['q'], 'attr') ?>" placeholder="Plate, operator, driver or route…" autocomplete="off">
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label-modern" for="todayDepartureDestination">Filter Route</label>
                <select class="form-select" id="todayDepartureDestination" name="destination" data-autocomplete-placeholder="Search route…">
                    <option value="">All routes</option>
                    <?php foreach ($destinations as $route): ?>
                    <option value="<?= esc($route['destination'], 'attr') ?>" <?= $filters['destination'] === $route['destination'] ? 'selected' : '' ?>><?= esc(strtoupper($route['destination'])) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label-modern" for="todayDepartureType">Vehicle type</label>
                <select class="form-select" id="todayDepartureType" name="vehicle_type">
                    <option value="">All types</option>
                    <?php foreach ($vehicleTypes as $type): ?>
                    <option value="<?= esc($type['slug'], 'attr') ?>" <?= $filters['vehicle_type'] === $type['slug'] ? 'selected' : '' ?>><?= esc(vehicle_type_label($type['slug'])) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-2 col-md-6 d-flex gap-2">
                <button type="submit" class="btn-modern btn-modern-primary flex-grow-1">Filter</button>
                <a href="<?= base_url('staff/departures') ?>" class="btn-modern btn-modern-outline">Reset</a>
            </div>
        </div>
    </div>
</form>

<div class="modern-card shadow-modern">
    <div class="table-responsive">
        <table class="table-modern" id="today-departures-table">
            <thead><tr><th>Plate Number</th><th>Operator</th><th>Driver</th><th>Type</th><th>Route</th><th>Departure Time</th><th>Passengers</th></tr></thead>
            <tbody>
            <?php foreach ($departures as $departure): ?>
                <tr>
                    <td data-label="Plate Number"><span class="plate-number"><?= esc($departure['plate_number']) ?></span></td>
                    <td data-label="Operator"><?= esc($departure['operator_name'] ?: '—') ?></td>
                    <td data-label="Driver"><?= esc($departure['driver_name'] ?: '—') ?></td>
                    <td data-label="Type"><?= vehicle_type_badge($departure['vehicle_type'] ?? '') ?></td>
                    <td data-label="Route"><?= esc(strtoupper($departure['origin'] ?? 'Terminal')) ?> &rarr; <?= esc(strtoupper($departure['destination'] ?? '—')) ?></td>
                    <td data-label="Departure Time"><span class="badge-modern badge-modern-info"><i class="bi bi-clock me-1"></i><?= operations_recorded_departure($departure['departure_time']) ?></span></td>
                    <td data-label="Passengers"><?= (int) ($departure['current_passengers'] ?? 0) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($departures)): ?>
                <tr><td colspan="7" class="text-center py-5 text-muted"><i class="bi bi-clock-history fs-1 d-block mb-3"></i>No departures match today’s selection.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php if ($pager): ?><div class="mt-3"><?= $pager->links() ?></div><?php endif; ?>

<?= view('templates/footer') ?>
