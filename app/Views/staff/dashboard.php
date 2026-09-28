<?= view('templates/header', ['title' => $title]) ?>

<div class="page-header-modern fade-in">
    <h1 class="page-title-modern mb-0">
        <i class="bi bi-speedometer2"></i>
        <?= esc($title) ?>
    </h1>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert-modern alert-modern-success fade-in">
        <i class="bi bi-check-circle-fill alert-modern-icon"></i>
        <div><?= esc(session()->getFlashdata('success')) ?></div>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert-modern alert-modern-danger fade-in">
        <i class="bi bi-x-circle-fill alert-modern-icon"></i>
        <div><?= esc(session()->getFlashdata('error')) ?></div>
    </div>
<?php endif; ?>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="stat-card-modern success-accent fade-in">
            <div class="stat-card-icon" style="background: var(--primary-soft, #dcfce7); color: var(--primary, #15803d);">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-card-value"><?= esc($active_queue_count) ?></div>
            <div class="stat-card-label">Active in Queue</div>
            <a href="<?= base_url('staff/queue') ?>" class="stat-card-link" style="color: var(--primary, #15803d);">
                Manage Queue <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-card-modern success-accent fade-in">
            <div class="stat-card-icon" style="background: var(--primary-soft, #dcfce7); color: var(--primary, #15803d);">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="stat-card-value"><?= strtoupper(date('D, M j')) ?></div>
            <div class="stat-card-label">Today's Date</div>
            <div class="stat-card-link" style="color: var(--primary, #15803d);">
                Active Duty
            </div>
        </div>
    </div>
</div>

<!-- Per-Route Queue Breakdown Cards -->
<?php if (!empty($routeBreakdowns)): ?>
<div class="row g-3 mb-4">
    <div class="col-12">
        <h6 class="text-uppercase text-muted fw-bold mb-2" style="font-size: 12px; letter-spacing: 0.8px;">
            <i class="bi bi-diagram-3 me-1"></i> Queue by Route
        </h6>
    </div>
    <?php foreach ($routeBreakdowns as $rb): ?>
    <div class="col-sm-6 col-lg-3">
        <div class="modern-card shadow-modern fade-in h-100" style="border-left: 3px solid var(--primary, #15803d);">
            <div class="modern-card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-bold text-dark text-truncate" style="font-size: 14px;" title="<?= strtoupper(esc($rb['destination'])) ?>">
                        <?= strtoupper(esc($rb['destination'])) ?>
                    </span>
                    <span class="badge-modern badge-modern-success" style="font-size: 11px;">
                        <?= (int)$rb['queue_count'] ?> vehicles
                    </span>
                </div>
                <div class="d-flex justify-content-between text-muted" style="font-size: 12px;">
                    <span>Wait: ~<?= (int)$rb['est_wait_minutes'] ?> mins</span>
                    <span>Cap: <?= (int)$rb['total_capacity'] ?> pax</span>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Quick Actions -->
<div class="modern-card shadow-modern fade-in mb-4">
    <div class="modern-card-header">
        <span class="modern-card-title">
            <i class="bi bi-lightning-charge" style="color: var(--primary, #15803d);"></i>
            Quick Actions
        </span>
    </div>
    <div class="modern-card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <a href="<?= base_url('staff/queue') ?>" class="btn-modern btn-modern-primary w-100" style="padding: 14px; text-decoration: none;">
                    <i class="bi bi-clock-history"></i>
                    Queue Operations
                </a>
            </div>
            <div class="col-md-6">
                <a href="<?= base_url('schedules') ?>" class="btn-modern btn-modern-outline w-100" style="padding: 14px; text-decoration: none;">
                    <i class="bi bi-calendar3"></i>
                    View Public Schedules
                </a>
            </div>
        </div>
    </div>
</div>

<?php
// Compute vehicle type counts and route destinations
$typeCounts = [];
$totalDepartures = !empty($recent_departures) ? count($recent_departures) : 0;
$uniqueDestinations = [];

if (!empty($recent_departures)) {
    foreach ($recent_departures as $dept) {
        $vt = strtolower($dept['vehicle_type'] ?? 'other');
        $typeCounts[$vt] = ($typeCounts[$vt] ?? 0) + 1;

        if ($vt === 'modern jeepney' || $vt === 'traditional jeepney') {
            $typeCounts['jeepney'] = ($typeCounts['jeepney'] ?? 0) + 1;
        }

        if (!empty($dept['destination']) && !in_array($dept['destination'], $uniqueDestinations)) {
            $uniqueDestinations[] = $dept['destination'];
        }
    }
}
?>

<h4 class="mt-5 mb-3 fade-in" style="font-size: 20px; font-weight: 700; color: var(--text-main, #1e293b);">Recent Departures</h4>

<!-- Search Bar and Vehicle Type Filters -->
<div class="modern-card shadow-modern fade-in mb-3">
    <div class="modern-card-body py-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <!-- Search Input Capsule -->
            <div class="departure-search-group position-relative">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" class="departure-search-input" id="departure-search" placeholder="Search plate, operator, driver..." onkeyup="filterDepartures()" oninput="toggleDepartureClearBtn(this.value)" autocomplete="off">
                <button type="button" class="btn-clear-search" id="clear-departure-search" onclick="clearDepartureSearch()" style="display: none !important;" title="Clear search">
                    <i class="bi bi-x-circle-fill"></i>
                </button>
            </div>

            <!-- Vehicle Type Filter Chips -->
            <div class="departure-type-filter">
                <span class="filter-label-text"><i class="bi bi-funnel me-1"></i>TYPE:</span>
                <div class="departure-type-options" role="group" aria-label="Filter departures by vehicle type">
                <button type="button" class="dep-filter-btn active" data-type="all" onclick="setDepartureTypeFilter('all', this)">
                    <i class="bi bi-grid-fill me-1"></i> All <span class="dep-chip-count" id="count-all"><?= $totalDepartures ?></span>
                </button>
                <?php if (!empty($vehicleTypes)): ?>
                    <?php foreach ($vehicleTypes as $vt): ?>
                        <?php 
                            $slug = strtolower($vt['slug']); 
                            $count = $typeCounts[$slug] ?? 0;
                            $photo = vehicle_type_photo($slug);
                            $vIcon = vehicle_type_icon($slug, $vt['icon'] ?? null);
                            $vColor = vehicle_type_color($slug, $vt['color'] ?? null);
                        ?>
                        <button type="button" class="dep-filter-btn" data-type="<?= esc($slug) ?>" onclick="setDepartureTypeFilter('<?= esc($slug) ?>', this)">
                            <?php if ($photo): ?>
                                <img src="<?= esc($photo) ?>" alt="<?= esc($vt['name']) ?>" class="dep-filter-icon" data-vt-photo="<?= esc($slug) ?>">
                            <?php else: ?>
                                <i class="fas <?= esc($vIcon) ?> me-1" style="color: <?= esc($vColor) ?>;" data-vt-icon="<?= esc($slug) ?>"></i>
                            <?php endif; ?>
                            <?= esc($vt['name']) ?> <span class="dep-chip-count" id="count-<?= esc($slug) ?>"><?= $count ?></span>
                        </button>
                    <?php endforeach; ?>
                <?php else: ?>
                    <button type="button" class="dep-filter-btn" data-type="jeepney" onclick="setDepartureTypeFilter('jeepney', this)">
                        <?php $jp = vehicle_type_photo('jeepney'); if ($jp): ?><img src="<?= esc($jp) ?>" alt="Jeepney" class="dep-filter-icon" data-vt-photo="jeepney"><?php else: ?><i class="fas <?= esc(vehicle_type_icon('jeepney')) ?> me-1" style="color: <?= esc(vehicle_type_color('jeepney')) ?>;" data-vt-icon="jeepney"></i><?php endif; ?>
                        Jeepney <span class="dep-chip-count"><?= $typeCounts['jeepney'] ?? 0 ?></span>
                    </button>
                    <button type="button" class="dep-filter-btn" data-type="minibus" onclick="setDepartureTypeFilter('minibus', this)">
                        <?php $mb = vehicle_type_photo('minibus'); if ($mb): ?><img src="<?= esc($mb) ?>" alt="Minibus" class="dep-filter-icon" data-vt-photo="minibus"><?php else: ?><i class="fas <?= esc(vehicle_type_icon('minibus')) ?> me-1" style="color: <?= esc(vehicle_type_color('minibus')) ?>;" data-vt-icon="minibus"></i><?php endif; ?>
                        Minibus <span class="dep-chip-count"><?= $typeCounts['minibus'] ?? 0 ?></span>
                    </button>
                    <button type="button" class="dep-filter-btn" data-type="van" onclick="setDepartureTypeFilter('van', this)">
                        <?php $vn = vehicle_type_photo('van'); if ($vn): ?><img src="<?= esc($vn) ?>" alt="Van" class="dep-filter-icon" data-vt-photo="van"><?php else: ?><i class="fas <?= esc(vehicle_type_icon('van')) ?> me-1" style="color: <?= esc(vehicle_type_color('van')) ?>;" data-vt-icon="van"></i><?php endif; ?>
                        Van <span class="dep-chip-count"><?= $typeCounts['van'] ?? 0 ?></span>
                    </button>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($uniqueDestinations)): ?>
<!-- Quick Route Filter Bar (Pill Chips) placed UNDER search and type filters -->
<div class="route-filter-wrapper fade-in mb-4">
    <div class="route-filter-label">
        <i class="bi bi-geo-alt-fill" style="color: var(--primary, #15803d);"></i> Route Destinations:
    </div>
    <div class="route-filter-bar" id="staffDepRouteFilterBar">
        <button type="button" class="route-chip active" data-dest="all" onclick="setDepartureRouteFilter('all', this)">
            All Routes
            <span class="chip-count"><?= $totalDepartures ?></span>
        </button>
        <?php foreach ($uniqueDestinations as $dest): ?>
            <?php
                $cnt = 0;
                foreach ($recent_departures as $d) {
                    if (strcasecmp($d['destination'] ?? '', $dest) === 0) $cnt++;
                }
            ?>
            <button type="button" class="route-chip" data-dest="<?= esc(strtolower($dest)) ?>" onclick="setDepartureRouteFilter('<?= esc(strtolower($dest)) ?>', this)">
                <?= strtoupper(esc($dest)) ?>
                <span class="chip-count"><?= $cnt ?></span>
            </button>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="modern-card shadow-modern fade-in">
    <div class="table-responsive">
        <table class="table-modern">
            <thead>
                <tr>
                    <th>Plate Number</th>
                    <th>Operator</th>
                    <th>Driver</th>
                    <th>Type</th>
                    <th>Route</th>
                    <th>Departure Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="departures-table-body">
                <?php if (!empty($recent_departures)): ?>
                    <?php foreach ($recent_departures as $dept): ?>
                        <tr data-vehicle-type="<?= esc(strtolower($dept['vehicle_type'] ?? '')) ?>" data-destination="<?= esc(strtolower($dept['destination'] ?? '')) ?>">
                            <td data-label="Plate Number">
                                <span class="plate-number"><?= esc($dept['plate_number']) ?></span>
                            </td>
                            <td data-label="Operator">
                                <span class="fw-bold" style="font-size: 14px; color: #0f172a; display: inline-flex; align-items: center; gap: 6px;">
                                    <i class="bi bi-building text-muted"></i>
                                    <?= esc(!empty($dept['operator_name']) ? $dept['operator_name'] : ($dept['driver_name'] ?? '—')) ?>
                                </span>
                            </td>
                            <td data-label="Driver">
                                <span class="fw-semibold" style="font-size: 13.5px; color: #334155; display: inline-flex; align-items: center; gap: 6px;">
                                    <i class="bi bi-person-badge text-primary"></i>
                                    <?= esc($dept['driver_name'] ?? '—') ?>
                                </span>
                            </td>
                            <td data-label="Type">
                                <?php 
                                    $vType = $dept['vehicle_type'] ?? '';
                                    $photoUrl = vehicle_resolved_photo($dept, $vType);
                                    $hasCustomPhoto = !empty($dept['vehicle_photo'] ?? $dept['photo'] ?? null);
                                ?>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="vehicle-type-icon <?= vehicle_type_class($vType) ?>">
                                        <?php if (!empty($photoUrl)): ?>
                                            <img src="<?= esc($photoUrl) ?>" alt="<?= vehicle_type_label($vType) ?>" style="height:36px; width:auto;" title="<?= vehicle_type_label($vType) ?>" class="<?= $hasCustomPhoto ? 'vehicle-custom-photo' : '' ?>" <?= $hasCustomPhoto ? 'data-vehicle-custom-photo="true"' : ('data-vt-photo="' . esc(vehicle_type_key($vType)) . '"') ?>>
                                        <?php else: ?>
                                            <i class="fas <?= esc(vehicle_type_icon($vType)) ?>" style="color: <?= esc(vehicle_type_color($vType)) ?>; font-size: 18px;"></i>
                                        <?php endif; ?>
                                    </span>
                                    <?= vehicle_type_badge($vType) ?>
                                </div>
                            </td>
                            <td data-label="Route" class="schedule-route-cell">
                                <div class="schedule-route-display">
                                    <strong><?= strtoupper(esc($dept['origin'] ?? 'Terminal')) ?></strong>
                                    <i class="bi bi-arrow-right text-primary"></i>
                                    <strong><?= strtoupper(esc($dept['destination'] ?? '—')) ?></strong>
                                </div>
                            </td>
                            <td data-label="Departure Time">
                                <span class="badge-modern badge-modern-info">
                                    <i class="bi bi-clock me-1"></i>
                                    <?= date('H:i', strtotime($dept['departure_time'])) ?>
                                </span>
                            </td>
                            <td data-label="Status">
                                <span style="background-color: #059669; color: #fff; font-weight: 700; font-size: 12px; padding: 5px 14px; border-radius: 50px; display: inline-flex; align-items: center; gap: 5px; letter-spacing: 0.3px; box-shadow: 0 1px 4px rgba(5,150,105,0.25);">
                                    <i class="bi bi-check-circle-fill"></i> DEPARTED
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted empty-state-table">
                            <i class="bi bi-clock-history fs-1 d-block mb-3 opacity-50"></i>
                            <div class="fw-bold fs-6 empty-state-title">No recent departures today</div>
                            <small class="empty-state-subtitle">Vehicles that depart today will appear here in real-time.</small>
                        </td>
                    </tr>
                <?php endif; ?>
                <tr id="no-departures-match" class="d-none" style="display:none !important;">
                    <td colspan="7" class="text-center py-5 text-muted empty-state-table">
                        <i class="bi bi-search fs-1 d-block mb-3 opacity-50"></i>
                        <div class="fw-bold fs-6 empty-state-title">No departures match your filter</div>
                        <small class="empty-state-subtitle">Try selecting a different vehicle type, route, or clear your search.</small>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<style>
    /* Route Filter Capsule Bar */
    .route-filter-wrapper {
        margin-bottom: 24px;
        background: #ffffff;
        padding: 16px 20px;
        border-radius: 16px;
        box-shadow: var(--shadow-sm, 0 2px 8px rgba(0, 0, 0, 0.06));
        border: 1px solid #e2e8f0;
    }
    .route-filter-label {
        font-size: 12.5px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .route-filter-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        overflow-x: auto;
        padding: 4px 2px 8px 2px;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    .route-filter-bar::-webkit-scrollbar {
        display: none;
    }
    .route-chip {
        padding: 8px 20px;
        border-radius: 25px;
        border: 1.5px solid #cbd5e1;
        background: #f8fafc;
        color: #334155;
        font-size: 13.5px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        font-family: inherit;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        user-select: none;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }
    .route-chip:hover {
        border-color: var(--primary, #15803d);
        color: var(--primary, #15803d);
        background: var(--primary-soft, rgba(21, 128, 61, 0.06));
        transform: translateY(-1px);
    }
    .route-chip.active {
        background: var(--primary, #15803d) !important;
        border-color: var(--primary, #15803d) !important;
        color: var(--on-primary, #ffffff) !important;
        box-shadow: 0 4px 12px var(--primary-soft, rgba(21, 128, 61, 0.28));
        transform: translateY(0);
    }
    .route-chip .chip-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        border-radius: 10px;
        font-size: 11.5px;
        font-weight: 700;
        background: #e2e8f0;
        color: #475569;
        margin-left: 2px;
    }
    .route-chip.active .chip-count {
        background: rgba(255, 255, 255, 0.25);
        color: var(--on-primary, #ffffff);
    }

    /* Search capsule */
    .departure-search-group {
        display: flex;
        align-items: center;
        max-width: 420px;
        min-width: 240px;
        width: 100%;
    }
    .departure-search-group .input-group-text {
        height: 36px;
        padding: 6px 12px;
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-right: none;
        border-radius: 20px 0 0 20px;
        color: #64748b;
        font-size: 14px;
    }
    .departure-search-input {
        height: 36px;
        width: 100%;
        padding: 6px 36px 6px 8px;
        font-size: 13.5px;
        font-family: inherit;
        border: 1.5px solid #cbd5e1;
        border-left: none;
        background: #ffffff;
        color: #1e293b;
        border-radius: 0 20px 20px 0;
        outline: none;
        transition: all 0.2s ease;
    }
    .departure-search-group:focus-within .input-group-text,
    .departure-search-group:focus-within .departure-search-input {
        border-color: var(--primary, #15803d);
        box-shadow: 0 0 0 3px var(--primary-soft, rgba(21, 128, 61, 0.14));
    }
    
    .filter-label-text {
        font-size: 13px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-right: 4px;
        display: inline-flex;
        align-items: center;
    }

    .departure-type-filter {
        display: flex;
        align-items: baseline;
        gap: 8px;
        flex: 1 1 420px;
        min-width: 0;
    }
    .departure-type-options {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 1 1 auto;
        min-width: 0;
        overflow-x: auto;
        overflow-y: hidden;
        flex-wrap: nowrap;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        padding-bottom: 4px;
    }
    .departure-type-options .dep-filter-btn {
        flex: 0 0 auto;
        white-space: nowrap;
    }

    /* Filter chips */
    .dep-filter-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border: 1.5px solid #cbd5e1;
        background: #ffffff;
        color: #475569;
        white-space: nowrap;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .dep-filter-btn:hover {
        border-color: #94a3b8;
        background: #f8fafc;
        color: #0f172a;
        transform: translateY(-1px);
    }
    .dep-filter-btn.active {
        background: var(--primary, #15803d) !important;
        border-color: var(--primary, #15803d) !important;
        color: var(--on-primary, #ffffff) !important;
        box-shadow: 0 4px 10px var(--primary-soft, rgba(21, 128, 61, 0.28));
    }
    .dep-filter-btn .dep-chip-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        border-radius: 10px;
        font-size: 11.5px;
        font-weight: 700;
        background: #e2e8f0;
        color: #475569;
    }
    .dep-filter-btn.active .dep-chip-count {
        background: rgba(255, 255, 255, 0.28);
        color: var(--on-primary, #ffffff);
    }

    /* Active states for vehicle type filter buttons (strictly derived from vehicle theme colors) */
    <?php 
    $staffTypesToRender = $vehicleTypes ?? [];
    $staffRenderedSlugs = array_column($staffTypesToRender, 'slug');
    foreach (['jeepney', 'van', 'minibus', 'bus'] as $fallbackSlug) {
        if (!in_array($fallbackSlug, $staffRenderedSlugs, true)) {
            $staffTypesToRender[] = ['slug' => $fallbackSlug, 'color' => vehicle_type_color($fallbackSlug)];
        }
    }
    ?>
    <?php foreach ($staffTypesToRender as $vt): ?>
        <?php
            $slug = strtolower(esc($vt['slug']));
            $col = !empty($vt['color']) ? $vt['color'] : vehicle_type_color($slug);
        ?>
        .dep-filter-btn[data-type="<?= $slug ?>"].active {
            background: var(--vehicle-<?= $slug ?>-soft, <?= $col ?>18) !important;
            border-color: var(--vehicle-<?= $slug ?>, <?= $col ?>) !important;
            color: var(--vehicle-<?= $slug ?>, <?= $col ?>) !important;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08) !important;
        }
        .dep-filter-btn[data-type="<?= $slug ?>"].active .dep-chip-count {
            background: var(--vehicle-<?= $slug ?>-soft, <?= $col ?>25) !important;
            color: var(--vehicle-<?= $slug ?>, <?= $col ?>) !important;
        }
        .dep-filter-btn[data-type="<?= $slug ?>"].active:hover {
            background: var(--vehicle-<?= $slug ?>-soft, <?= $col ?>25) !important;
            border-color: var(--vehicle-<?= $slug ?>, <?= $col ?>) !important;
            color: var(--vehicle-<?= $slug ?>, <?= $col ?>) !important;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.12) !important;
            filter: brightness(0.96);
        }
    <?php endforeach; ?>

    .dep-filter-btn img {
        height: 18px;
        width: auto;
        object-fit: contain;
    }

    @media (max-width: 768px) {
        .departure-type-filter {
            flex-basis: 100%;
            width: 100%;
            flex-direction: column;
            align-items: stretch;
        }
        .departure-type-options {
            width: 100%;
            flex: 0 0 auto;
        }
        .departure-search-group {
            max-width: 100% !important;
            min-width: 0 !important;
            width: 100% !important;
            margin-bottom: 8px;
        }
        .filter-label-text {
            margin-bottom: 0;
        }
    }
</style>

<?= $this->include('templates/footer') ?>

<script src="<?= base_url('js/queue-sync.js?v=20260928_1') ?>"></script>
<script>
    let currentVehicleTypeFilter = 'all';
    let currentRouteFilter = 'all';

    function setDepartureTypeFilter(type, btn) {
        currentVehicleTypeFilter = type;
        document.querySelectorAll('.dep-filter-btn').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        filterDepartures();
    }

    function setDepartureRouteFilter(dest, btn) {
        currentRouteFilter = dest;
        document.querySelectorAll('#staffDepRouteFilterBar .route-chip').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        filterDepartures();
    }

    function toggleDepartureClearBtn(val) {
        const btn = document.getElementById('clear-departure-search');
        if (btn) {
            if (val && val.trim().length > 0) {
                btn.style.setProperty('display', 'inline-flex', 'important');
            } else {
                btn.style.setProperty('display', 'none', 'important');
            }
        }
    }

    function clearDepartureSearch() {
        const input = document.getElementById('departure-search');
        if (input) {
            input.value = '';
            toggleDepartureClearBtn('');
            input.focus();
            filterDepartures();
        }
    }

    function filterDepartures() {
        const searchInput = document.getElementById('departure-search');
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        toggleDepartureClearBtn(query);
        const rows = document.querySelectorAll('#departures-table-body tr:not(#no-departures-match)');
        let visibleCount = 0;

        rows.forEach(row => {
            const vType = (row.getAttribute('data-vehicle-type') || '').toLowerCase();
            const dest = (row.getAttribute('data-destination') || '').toLowerCase();
            const text = row.innerText.toLowerCase();

            const matchesType = (currentVehicleTypeFilter === 'all' || vType === currentVehicleTypeFilter);
            const matchesRoute = (currentRouteFilter === 'all' || dest === currentRouteFilter);
            const matchesQuery = (!query || text.includes(query));

            if (matchesType && matchesRoute && matchesQuery) {
                row.style.removeProperty('display');
                row.classList.remove('d-none');
                visibleCount++;
            } else {
                row.style.setProperty('display', 'none', 'important');
                row.classList.add('d-none');
            }
        });

        const noMatchRow = document.getElementById('no-departures-match');
        if (noMatchRow) {
            if (visibleCount === 0 && rows.length > 0) {
                noMatchRow.style.removeProperty('display');
                noMatchRow.classList.remove('d-none');
            } else {
                noMatchRow.style.setProperty('display', 'none', 'important');
                noMatchRow.classList.add('d-none');
            }
        }
    }

    QueueSync.init({
        pollInterval: 3000,
        refreshUrl:   '<?= base_url('staff/dashboard') ?>',
        tableSelector: '#departures-table-body',
        extraRefresh: function(newDoc) {
            // Update Active in Queue count
            var newCount = newDoc.querySelector('.stat-card-value');
            var curCount = document.querySelector('.stat-card-value');
            if (newCount && curCount) curCount.textContent = newCount.textContent;

            // Re-apply filter if needed
            filterDepartures();
        }
    });

    // Real-time WebSocket listener for immediate dashboard departures refresh
    document.addEventListener('pttm:ws-queue_update', function(e) {
        var detail = (e && e.detail) ? e.detail : {};
        var data = detail.data || detail;
        if (data && (data.action === 'passenger_change' || data.type === 'passenger_change')) {
            if (window.QueueSync && window.QueueSync.updatePassengerUI) {
                var id = data.id;
                var newCount = parseInt(data.new_count, 10);
                var capacity = parseInt(data.capacity, 10);
                if (id && !isNaN(newCount) && !isNaN(capacity)) {
                    window.QueueSync.updatePassengerUI(id, newCount, capacity);
                }
            }
            return;
        }
        if (window.QueueSync && window.QueueSync.refresh) {
            window.QueueSync.refresh();
        }
    });
    document.addEventListener('pttm:ws-vehicle_type_update', function() {
        if (window.QueueSync && window.QueueSync.refresh) {
            window.QueueSync.refresh();
        }
    });
    document.addEventListener('pttm:ws-fare_update', function() {
        if (window.QueueSync && window.QueueSync.refresh) {
            window.QueueSync.refresh();
        }
    });
    document.addEventListener('pttm:ws-operational_settings_updated', function() {
        if (window.QueueSync && window.QueueSync.refresh) {
            window.QueueSync.refresh();
        }
    });
    document.addEventListener('pttm:ws-branding_updated', function() {
        if (window.QueueSync && window.QueueSync.refresh) {
            window.QueueSync.refresh();
        }
    });
</script>
