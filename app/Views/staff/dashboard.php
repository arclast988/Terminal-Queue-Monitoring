<?= $this->include('templates/header') ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">

<style>
    /* Staff/Dispatcher-specific responsive enhancements */
    @media (max-width: 768px) {
        .page-header-modern {
            flex-direction: column;
            align-items: flex-start;
            gap: 16px;
            padding: 20px 0;
        }
        
        .page-title-modern {
            font-size: 22px;
        }
        
        .stat-card-modern {
            padding: 18px;
        }
        
        .stat-card-icon {
            width: 48px;
            height: 48px;
            font-size: 20px;
        }
        
        .stat-card-value {
            font-size: 26px;
        }
        
        .stat-card-label {
            font-size: 13px;
            font-weight: 600;
        }
        
        .table-modern thead {
            display: none;
        }
        
        .table-modern tbody tr {
            display: block;
            margin-bottom: 12px;
            border-radius: 10px;
            border: 1px solid var(--slate-200);
            padding: 14px;
        }
        
        .table-modern tbody td {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid var(--slate-100);
            font-size: 13px;
        }
        
        .table-modern tbody td:last-child {
            border-bottom: none;
        }
        
        .table-modern tbody td::before {
            content: attr(data-label);
            font-weight: 700;
            color: var(--slate-500);
            text-transform: uppercase;
            font-size: 12px;
        }
    }
    
    @media (max-width: 480px) {
        .stat-card-modern {
            padding: 16px;
        }
        
        .stat-card-value {
            font-size: 22px;
        }
    }
</style>

<div class="page-header-modern fade-in">
    <h1 class="page-title-modern">
        <i class="bi bi-person-workspace"></i>
        Dispatcher Dashboard
    </h1>
</div>

<div class="row">
    <div class="col-12 col-md-6 col-xl-4 mb-4">
        <div class="stat-card-modern blue-accent fade-in">
            <div class="stat-card-icon" style="background: #DBEAFE; color: #1565c0;">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="stat-card-value"><?= $active_queue_count ?></div>
            <div class="stat-card-label">Vehicles in Queue</div>
            <a href="<?= base_url('staff/queue') ?>" class="stat-card-link">
                Manage Queue <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <?php foreach ($terminals as $terminal): ?>
        <div class="col-12 col-md-6 col-xl-4 mb-4">
            <div class="modern-card shadow-modern fade-in">
                <div class="modern-card-body">
                    <div style="font-size: 12.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: var(--text-muted, #475569); margin-bottom: 8px;">
                        <?= esc($terminal['name']) ?>
                    </div>
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <div style="font-size: 28px; font-weight: 800; color: var(--text-main, #1e293b); line-height: 1.1;">
                                <?= esc($terminal['capacity']) ?>
                                <span style="font-size: 12px; font-weight: 600; color: var(--text-muted, #475569);">pax capacity</span>
                            </div>
                        </div>
                        <i class="bi bi-building fs-2" style="color: var(--primary, #60a5fa); opacity: 0.7;"></i>
                    </div>
                    <p style="margin: 8px 0 0; font-size: 13px; color: var(--text-muted, #475569);"><?= esc($terminal['location']) ?></p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php
// Calculate vehicle type counts for recent departures
$totalDepartures = count($recent_departures ?? []);
$typeCounts = [];
if (!empty($vehicleTypes)) {
    foreach ($vehicleTypes as $vt) {
        $typeCounts[strtolower($vt['slug'])] = 0;
    }
}
if (!empty($recent_departures)) {
    foreach ($recent_departures as $dept) {
        $vtSlug = strtolower($dept['vehicle_type'] ?? '');
        if (isset($typeCounts[$vtSlug])) {
            $typeCounts[$vtSlug]++;
        } else {
            $typeCounts[$vtSlug] = 1;
        }
    }
}
?>

<h4 class="mt-5 mb-3 fade-in" style="font-size: 20px; font-weight: 700; color: var(--text-main, #1e293b);">Recent Departures</h4>

<!-- Search Bar and Vehicle Type Filters -->
<div class="modern-card shadow-modern fade-in mb-4">
    <div class="modern-card-body py-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <!-- Search Input Capsule -->
            <div class="departure-search-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" class="departure-search-input" id="departure-search" placeholder="Search plate, operator, driver..." onkeyup="filterDepartures()">
            </div>

            <!-- Vehicle Type Filter Chips -->
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="filter-label-text"><i class="bi bi-funnel me-1"></i>FILTER:</span>
                <button type="button" class="dep-filter-btn active" data-type="all" onclick="setDepartureTypeFilter('all', this)">
                    <i class="bi bi-grid-fill me-1"></i> All <span class="dep-chip-count" id="count-all"><?= $totalDepartures ?></span>
                </button>
                <?php if (!empty($vehicleTypes)): ?>
                    <?php foreach ($vehicleTypes as $vt): ?>
                        <?php 
                            $slug = strtolower($vt['slug']); 
                            $count = $typeCounts[$slug] ?? 0;
                            $img = vehicle_type_image($slug);
                        ?>
                        <button type="button" class="dep-filter-btn" data-type="<?= esc($slug) ?>" onclick="setDepartureTypeFilter('<?= esc($slug) ?>', this)">
                            <img src="<?= base_url('images/' . $img) ?>" alt="<?= esc($vt['name']) ?>">
                            <?= esc($vt['name']) ?> <span class="dep-chip-count"><?= $count ?></span>
                        </button>
                    <?php endforeach; ?>
                <?php else: ?>
                    <button type="button" class="dep-filter-btn" data-type="jeepney" onclick="setDepartureTypeFilter('jeepney', this)">
                        <img src="<?= base_url('images/' . vehicle_type_image('jeepney')) ?>" alt="Jeepney">
                        Jeepney <span class="dep-chip-count"><?= $typeCounts['jeepney'] ?? 0 ?></span>
                    </button>
                    <button type="button" class="dep-filter-btn" data-type="minibus" onclick="setDepartureTypeFilter('minibus', this)">
                        <img src="<?= base_url('images/' . vehicle_type_image('minibus')) ?>" alt="Minibus">
                        Minibus <span class="dep-chip-count"><?= $typeCounts['minibus'] ?? 0 ?></span>
                    </button>
                    <button type="button" class="dep-filter-btn" data-type="van" onclick="setDepartureTypeFilter('van', this)">
                        <img src="<?= base_url('images/' . vehicle_type_image('van')) ?>" alt="Van">
                        Van <span class="dep-chip-count"><?= $typeCounts['van'] ?? 0 ?></span>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modern-card shadow-modern fade-in">
    <div class="table-responsive">
        <table class="table-modern">
            <thead>
                <tr>
                    <th>Queue #</th>
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
                        <tr data-vehicle-type="<?= esc(strtolower($dept['vehicle_type'] ?? '')) ?>">
                            <td data-label="Queue #">
                                <span class="badge-modern badge-modern-primary" style="font-weight:700; font-size:13px;">
                                    #<?= esc($dept['position'] ?? '0') ?>
                                </span>
                            </td>
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
                                    <strong style="color: #334155; font-size: 13.5px;"><?= esc($dept['origin'] ?? 'Palompon') ?></strong>
                                    <i class="bi bi-arrow-right text-primary"></i>
                                    <strong style="color: #0f172a; font-size: 13.5px;"><?= esc($dept['destination'] ?? '—') ?></strong>
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
                        <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted, #475569);">No recent departures today.</td>
                    </tr>
                <?php endif; ?>
                <tr id="no-departures-match" style="display:none;">
                    <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted, #475569);">
                        <i class="bi bi-search fs-3 d-block mb-2 text-muted opacity-50"></i>
                        No departures match the selected search or filter.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<style>
    /* Search capsule */
    .departure-search-group {
        display: flex;
        align-items: center;
        max-width: 380px;
        min-width: 240px;
    }
    .departure-search-group .input-group-text {
        height: 36px;
        background: #f8fafc;
        border: 1.5px solid #cbd5e1;
        border-right: none;
        border-radius: 20px 0 0 20px;
        color: #64748b;
        padding: 0 12px;
        font-size: 14px;
    }
    .departure-search-input {
        height: 36px;
        width: 100%;
        padding: 6px 14px 6px 8px;
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
        border-color: var(--primary, #1565c0);
        box-shadow: 0 0 0 3px rgba(21, 101, 192, 0.12);
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
        background: #C62828 !important;
        border-color: #C62828 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 10px rgba(198, 40, 40, 0.25);
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
        color: #ffffff;
    }
    .dep-filter-btn img {
        height: 18px;
        width: auto;
        object-fit: contain;
    }
</style>

<?= $this->include('templates/footer') ?>

<script src="<?= base_url('js/ws-client.js') ?>"></script>
<script src="<?= base_url('js/queue-sync.js') ?>"></script>
<script>
    let currentVehicleTypeFilter = 'all';

    function setDepartureTypeFilter(type, btn) {
        currentVehicleTypeFilter = type;
        document.querySelectorAll('.dep-filter-btn').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        filterDepartures();
    }

    function filterDepartures() {
        const searchInput = document.getElementById('departure-search');
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const rows = document.querySelectorAll('#departures-table-body tr:not(#no-departures-match)');
        let visibleCount = 0;

        rows.forEach(row => {
            const vType = (row.getAttribute('data-vehicle-type') || '').toLowerCase();
            const text = row.innerText.toLowerCase();

            const matchesType = (currentVehicleTypeFilter === 'all' || vType === currentVehicleTypeFilter);
            const matchesQuery = (!query || text.includes(query));

            if (matchesType && matchesQuery) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const noMatchRow = document.getElementById('no-departures-match');
        if (noMatchRow) {
            noMatchRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
        }
    }

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
