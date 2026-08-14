<?= view('templates/header', ['title' => $title]) ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">

<div class="page-header-modern fade-in">
    <h1 class="page-title-modern">
        <i class="bi bi-truck"></i>
        Vehicle Register
    </h1>
    <div class="d-flex gap-2">
        <button type="button" class="btn-modern btn-modern-outline" data-bs-toggle="modal" data-bs-target="#manageVehicleTypesModal">
            <i class="bi bi-gear-fill me-1"></i> Manage Types
        </button>
        <button type="button" class="btn-modern btn-modern-outline" data-bs-toggle="modal" data-bs-target="#addVehicleTypeModal">
            <i class="bi bi-tags me-1"></i> Add Vehicle Type
        </button>
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

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert-modern alert-modern-danger fade-in">
        <i class="bi bi-exclamation-circle-fill alert-modern-icon"></i>
        <div>
            <ul class="mb-0" style="padding-left: 20px;">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php endif; ?>

<!-- Add Vehicle Form -->
<div class="modern-card shadow-modern fade-in">
    <div class="modern-card-header">
        <span class="modern-card-title">
            <i class="bi bi-plus-circle" style="color: var(--primary-red);"></i>
            Register New Vehicle
        </span>
    </div>
    <div class="modern-card-body">
        <form action="<?= base_url('admin/vehicles/store') ?>" method="post">
            <?= csrf_field() ?>
            <div class="row g-3 align-items-end">
                <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                    <label for="operator_name" class="form-label-modern">Operator Name</label>
                    <input type="text" class="form-control-modern" id="operator_name" name="operator_name"
                        placeholder="E.G. LETRANSCO" value="<?= old('operator_name') ?>" required>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                    <label for="driver_name" class="form-label-modern">Driver Name</label>
                    <input type="text" class="form-control-modern" id="driver_name" name="driver_name"
                        placeholder="e.g. Pedro Santos" value="<?= old('driver_name') ?>" required>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                    <label for="plate_number" class="form-label-modern">Plate Number</label>
                    <input type="text" class="form-control-modern" id="plate_number" name="plate_number"
                        placeholder="e.g. ABC-1234" value="<?= old('plate_number') ?>" required>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                    <label for="type" class="form-label-modern">Type</label>
                    <select class="form-select-modern" id="type" name="type" required>
                        <option value="">-- Select Type --</option>
                        <?php foreach (($vehicleTypes ?? []) as $vehicleType): ?>
                            <option value="<?= esc($vehicleType['slug']) ?>" <?= old('type') === $vehicleType['slug'] ? 'selected' : '' ?>><?= esc($vehicleType['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                    <label for="route_id" class="form-label-modern">Assigned Route</label>
                    <select class="form-select-modern" id="route_id" name="route_id" required>
                        <option value="">-- Select Route --</option>
                        <?php if (!empty($routes)): ?>
                            <?php foreach ($routes as $r): ?>
                                <option value="<?= $r['id'] ?>" data-type="<?= esc($r['vehicle_type']) ?>"
                                    <?= old('route_id') == $r['id'] ? 'selected' : '' ?>>
                                    <?= strtoupper(esc($r['origin'])) ?> → <?= strtoupper(esc($r['destination'])) ?> (<?= ucfirst(esc($r['vehicle_type'])) ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-2 col-xl-1">
                    <label for="capacity" class="form-label-modern">Capacity</label>
                    <input type="number" class="form-control-modern" id="capacity" name="capacity" placeholder="16"
                        value="<?= old('capacity') ?>" min="1" required>
                </div>
                <div class="col-12 col-sm-6 col-md-2 col-xl-1">
                    <button type="submit" class="btn-modern btn-modern-primary w-100">
                        <i class="bi bi-plus-lg"></i> Add
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
// Count vehicles by type and status
$totalVehicles = is_array($vehicles) ? count($vehicles) : 0;
$typeCounts = array_fill_keys(array_column($vehicleTypes ?? [], 'slug'), 0);
$countActive = 0;
$countMaintenance = 0;
if (!empty($vehicles) && is_array($vehicles)) {
    foreach ($vehicles as $v) {
        if (array_key_exists($v['type'], $typeCounts)) {
            $typeCounts[$v['type']]++;
        }
        if ($v['status'] === 'active')
            $countActive++;
        else
            $countMaintenance++;
    }
}
?>

<!-- Vehicle Filter & Summary Bar -->
<div class="modern-card shadow-modern fade-in mb-4">
    <div class="modern-card-body">
        <!-- Filter Buttons Row -->
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <div class="vehicle-search-group me-3">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" class="vehicle-search-input" id="vehicle-search" placeholder="Search plate, operator, or driver..." onkeyup="filterVehicles(currentFilter)">
            </div>
            
            <span class="filter-label-text">
                <i class="bi bi-funnel me-1"></i>Filter:
            </span>
            
            <button type="button" class="vf-btn active" id="filter-btn-all" onclick="filterVehicles('all')">
                <i class="bi bi-grid-3x3-gap-fill"></i> All
                <span class="vf-count"><?= $totalVehicles ?></span>
            </button>
            
            <span class="vf-divider"></span>
            
            <?php foreach (($vehicleTypes ?? []) as $vehicleType): ?>
            <button type="button" class="vf-btn" id="filter-btn-<?= esc($vehicleType['slug']) ?>" onclick="filterVehicles('<?= esc($vehicleType['slug']) ?>')">
                <img src="<?= base_url('images/' . vehicle_type_image($vehicleType['slug'])) ?>" alt="" style="height:18px; width:auto;"> <?= esc($vehicleType['name']) ?>
                <span class="vf-count"><?= $typeCounts[$vehicleType['slug']] ?? 0 ?></span>
            </button>
            <?php endforeach; ?>
            
            <span class="vf-divider"></span>
            
            <button type="button" class="vf-btn vf-active-status" id="filter-btn-active" onclick="filterVehicles('active')">
                <i class="bi bi-check-circle-fill"></i> Active
                <span class="vf-count"><?= $countActive ?></span>
            </button>
            
            <button type="button" class="vf-btn vf-maintenance-status" id="filter-btn-maintenance"
                onclick="filterVehicles('maintenance')">
                <i class="bi bi-wrench-adjustable"></i> Maintenance
                <span class="vf-count"><?= $countMaintenance ?></span>
            </button>
        </div>
        <div id="filter-label" style="font-size:13px; color:var(--slate-500);">Showing all <strong><?= $totalVehicles ?></strong> vehicles</div>
    </div>
</div>

<!-- Vehicles Table -->
<div class="modern-card shadow-modern fade-in">
    <div class="modern-card-header">
        <span class="modern-card-title">
            <i class="bi bi-list-ul" style="color: var(--primary-red);"></i>
            Vehicle List
        </span>
    </div>
    <div class="modern-card-body">
        <div class="table-responsive">
            <table class="table-modern" id="vehicles-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Plate Number</th>
                        <th>Operator Name</th>
                        <th>Driver Name</th>
                        <th>Vehicle Type</th>
                        <th>Assigned Route</th>
                        <th>Capacity</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($vehicles) && is_array($vehicles)): ?>
                        <?php foreach ($vehicles as $i => $vehicle): ?>
                            <tr data-type="<?= esc($vehicle['type']) ?>" data-status="<?= esc($vehicle['status']) ?>">
                                <td data-label="#" class="row-number"><strong><?= $i + 1 ?></strong></td>
                                <td data-label="Plate Number">
                                    <span class="plate-number"><?= esc($vehicle['plate_number']) ?></span>
                                </td>
                                <td data-label="Operator Name">
                                    <div class="driver-cell">
                                        <i class="bi bi-person-fill"></i>
                                        <?= esc($vehicle['operator_name'] ?? '—') ?>
                                    </div>
                                </td>
                                <td data-label="Driver Name">
                                    <div class="driver-cell">
                                        <i class="bi bi-person-badge"></i>
                                        <?= esc($vehicle['driver_name'] ?? '—') ?>
                                    </div>
                                </td>
                                <td data-label="Vehicle Type">
                                    <?php $imgFile = vehicle_type_image($vehicle['type']); ?>
                                    <div class="vehicle-type-cell">
                                        <span class="vehicle-type-icon <?= vehicle_type_class($vehicle['type']) ?>">
                                            <img src="<?= base_url('images/' . $imgFile) ?>" alt="<?= vehicle_type_label($vehicle['type']) ?>"
                                                style="height:28px; width:auto;" title="<?= vehicle_type_label($vehicle['type']) ?>">
                                        </span>
                                        <?= vehicle_type_badge($vehicle['type']) ?>
                                    </div>
                                </td>
                                <td data-label="Assigned Route">
                                    <?php if (!empty($vehicle['route_destination'])):
                                        $routeLabel = strtoupper(esc($vehicle['route_origin'])) . ' → ' . strtoupper(esc($vehicle['route_destination']));
                                    ?>
                                        <div class="route-info">
                                            <i class="bi bi-geo-alt" style="color: var(--primary-red);"></i>
                                            <span><?= $routeLabel ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge-modern badge-modern-warning">Not Assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Capacity"><strong><?= $vehicle['capacity'] ?></strong></td>
                                <td data-label="Status">
                                    <?php if ($vehicle['status'] == 'active'): ?>
                                        <span class="badge-modern badge-modern-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge-modern badge-modern-danger">Maintenance</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Registered">
                                    <span class="badge-modern badge-modern-info"><?= strtoupper(date('M d, Y', strtotime($vehicle['created_at']))) ?></span>
                                </td>
                                <td data-label="Action">
                                    <div class="d-flex gap-2">
                                        <a href="<?= base_url('admin/vehicles/edit/' . $vehicle['id']) ?>"
                                            class="btn-modern btn-action-edit btn-modern-sm" title="Edit vehicle">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form action="<?= base_url('admin/vehicles/delete/' . $vehicle['id']) ?>" method="post"
                                            class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to delete this vehicle?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn-modern btn-modern-sm btn-action-delete" title="Delete vehicle">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr class="no-vehicles-row">
                            <td colspan="10" style="text-align:center; padding:40px; color:var(--slate-500);">
                                <i class="bi bi-inbox" style="font-size:32px; opacity:0.3; margin-bottom:8px;"></i>
                                <div>No vehicles registered yet.</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .vehicle-register-form .vehicle-add-btn {
        min-height: 42px;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    #vehicles-table {
        width: 100%;
    }

    #vehicles-table th,
    #vehicles-table td {
        vertical-align: middle;
    }

    #vehicles-table .btn-group .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
    }

    /* Premium Hover & Transitions for Table Rows */
    #vehicles-table tbody tr {
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }
    #vehicles-table tbody tr:hover {
        background-color: var(--primary-soft, #f1f5f9) !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
        z-index: 2;
    }
    /* Left border accent on hover */
    #vehicles-table tbody tr td:first-child {
        position: relative;
        transition: border-left-color 0.2s ease;
    }
    #vehicles-table tbody tr:hover td:first-child {
        border-left: 3px solid var(--primary, #c62828) !important;
    }
    /* Scale inner badges and icons smoothly on hover */
    #vehicles-table tbody tr:hover .badge-modern {
        transform: scale(1.05);
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    #vehicles-table tbody tr:hover .vehicle-type-icon img {
        transform: scale(1.1) rotate(2deg);
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    #vehicles-table tbody tr:hover .btn-modern {
        transform: scale(1.02);
    }
    .vehicle-type-icon img, .badge-modern, .btn-modern {
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* ── Search Input Capsule and Alignments ── */
    .vehicle-search-group {
        display: flex !important;
        align-items: center !important;
        max-width: 250px;
        width: 100%;
        position: relative;
    }

    .vehicle-search-group .input-group-text {
        height: 34px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 6px 0 6px 14px !important;
        border: 2px solid #dee2e6 !important;
        border-right: none !important;
        background: #fff !important;
        color: #64748b !important;
        border-radius: 20px 0 0 20px !important;
        transition: border-color 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.2s ease !important;
    }

    .vehicle-search-input {
        height: 34px !important;
        width: 100%;
        padding: 6px 14px 6px 8px !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        font-family: 'Outfit', sans-serif !important;
        border: 2px solid #dee2e6 !important;
        border-left: none !important;
        background: #fff !important;
        color: #1e293b !important;
        border-radius: 0 20px 20px 0 !important;
        transition: border-color 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.2s ease !important;
    }

    /* Focus effects across the search capsule */
    .vehicle-search-group:focus-within .input-group-text {
        border-color: var(--primary, #c62828) !important;
        box-shadow: 0 4px 12px rgba(198, 40, 40, 0.08) !important;
    }
    
    .vehicle-search-group:focus-within .vehicle-search-input {
        border-color: var(--primary, #c62828) !important;
        box-shadow: 0 4px 12px rgba(198, 40, 40, 0.08) !important;
        outline: none !important;
    }

    .filter-label-text {
        font-size: 13px !important;
        font-weight: 600 !important;
        color: #64748b !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        margin-right: 4px !important;
        display: inline-flex !important;
        align-items: center !important;
        height: 34px !important;
    }

    .vf-divider {
        display: inline-block !important;
        width: 1px !important;
        height: 24px !important;
        background: #dee2e6 !important;
        margin: 0 4px !important;
        align-self: center !important;
    }

    /* ── Vehicle Filter Buttons ── */
    .vf-btn {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        padding: 6px 14px !important;
        border-radius: 20px !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        font-family: 'Outfit', sans-serif !important;
        cursor: pointer !important;
        border: 2px solid #dee2e6 !important;
        background: #fff !important;
        color: #475569 !important;
        white-space: nowrap !important;
        line-height: 1.4 !important;
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.2s ease, background-color 0.2s ease, color 0.2s ease !important;
    }

    .vf-btn:hover {
        border-color: #cbd5e1 !important;
        background: #f8fafc !important;
        color: #1e293b !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06) !important;
    }

    .vf-btn.active:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(198, 40, 40, 0.25) !important;
    }
    .vf-btn.vf-jeepney.active:hover {
        box-shadow: 0 4px 12px rgba(21, 101, 192, 0.25) !important;
    }
    .vf-btn.vf-van.active:hover {
        box-shadow: 0 4px 12px rgba(198, 40, 40, 0.25) !important;
    }
    .vf-btn.vf-minibus.active:hover {
        box-shadow: 0 4px 12px rgba(46, 125, 50, 0.25) !important;
    }
    .vf-btn.vf-active-status.active:hover {
        box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25) !important;
    }
    .vf-btn.vf-maintenance-status.active:hover {
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25) !important;
    }

    .vf-count {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 22px !important;
        height: 22px !important;
        padding: 0 6px !important;
        border-radius: 12px !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        background: #e2e8f0 !important;
        color: #475569 !important;
    }

    /* Active state for "All" */
    .vf-btn.active {
        background: #C62828 !important;
        border-color: #C62828 !important;
        color: #fff !important;
    }

    .vf-btn.active .vf-count {
        background: rgba(255, 255, 255, 0.25) !important;
        color: #fff !important;
    }

    /* Active states per type */
    .vf-btn.vf-jeepney.active {
        background: var(--vehicle-jeepney, #1565c0) !important;
        border-color: var(--vehicle-jeepney, #1565c0) !important;
        color: #fff !important;
    }

    .vf-btn.vf-van.active {
        background: var(--vehicle-van, #c62828) !important;
        border-color: var(--vehicle-van, #c62828) !important;
        color: #fff !important;
    }

    .vf-btn.vf-minibus.active {
        background: var(--vehicle-minibus, #2e7d32) !important;
        border-color: var(--vehicle-minibus, #2e7d32) !important;
        color: #fff !important;
    }

    .vf-btn.vf-active-status.active {
        background: #16a34a !important;
        border-color: #16a34a !important;
        color: #fff !important;
    }

    .vf-btn.vf-maintenance-status.active {
        background: #dc2626 !important;
        border-color: #dc2626 !important;
        color: #fff !important;
    }

    .vf-btn.vf-jeepney.active .vf-count,
    .vf-btn.vf-van.active .vf-count,
    .vf-btn.vf-minibus.active .vf-count,
    .vf-btn.vf-active-status.active .vf-count,
    .vf-btn.vf-maintenance-status.active .vf-count {
        background: rgba(255, 255, 255, 0.25) !important;
        color: #fff !important;
    }

    .vehicle-row-hidden {
        display: none !important;
    }

    @media (max-width: 991.98px) {
        .vehicle-register-form .card-body {
            padding: 1rem !important;
        }

        /* Let the master mobile card CSS in responsive.css handle the table layout.
           Do NOT set min-width here — it blocks the card conversion. */
        #vehicles-table {
            min-width: 0 !important;
            font-size: 13px;
        }

        .vehicle-action-label {
            display: none;
        }
    }

    @media (max-width: 575.98px) {
        .vf-divider {
            display: none !important;
        }

        .vf-btn {
            flex: 1 1 calc(50% - 0.5rem);
            justify-content: center;
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        .vehicle-register-form .vehicle-add-btn {
            width: 100%;
        }
    }
</style>

<?php $vehicleTypeLabels = array_column($vehicleTypes ?? [], 'name', 'slug'); ?>
<script>
    let currentFilter = 'all';
    const vehicleTypeLabels = <?= json_encode($vehicleTypeLabels, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    function filterVehicles(filter) {
        currentFilter = filter;
        const rows = document.querySelectorAll('#vehicles-table tbody tr[data-type]');
        const filterLabel = document.getElementById('filter-label');
        const typeFilters = Object.keys(vehicleTypeLabels);
        const statusFilters = ['active', 'maintenance'];

        // Update active button styling
        document.querySelectorAll('.vf-btn').forEach(btn => btn.classList.remove('active'));
        const activeBtn = document.getElementById('filter-btn-' + filter);
        if (activeBtn) activeBtn.classList.add('active');

        // Filter label map
        const labelMap = Object.assign({
            'all': 'all',
            'active': 'Active',
            'maintenance': 'Maintenance'
        }, vehicleTypeLabels);

        const searchQuery = document.getElementById('vehicle-search') ? document.getElementById('vehicle-search').value.toLowerCase() : '';

        let visibleCount = 0;
        rows.forEach(row => {
            let show = false;
            if (filter === 'all') {
                show = true;
            } else if (typeFilters.includes(filter)) {
                show = row.getAttribute('data-type') === filter;
            } else if (statusFilters.includes(filter)) {
                show = row.getAttribute('data-status') === filter;
            }

            if (show && searchQuery) {
                const textContent = row.textContent.toLowerCase();
                if (!textContent.includes(searchQuery)) {
                    show = false;
                }
            }

            if (show) {
                row.classList.remove('vehicle-row-hidden');
                visibleCount++;
            } else {
                row.classList.add('vehicle-row-hidden');
            }
        });

        // Re-number visible rows
        let num = 1;
        rows.forEach(row => {
            if (!row.classList.contains('vehicle-row-hidden')) {
                row.querySelector('.row-number').textContent = num++;
            }
        });

        // Update filter label text
        if (filter === 'all') {
            filterLabel.innerHTML = 'Showing all <strong>' + visibleCount + '</strong> vehicles';
        } else {
            filterLabel.innerHTML = 'Showing <strong>' + visibleCount + '</strong> ' + labelMap[filter] + ' vehicle' + (visibleCount !== 1 ? 's' : '');
        }

        // Handle empty state
        let emptyRow = document.querySelector('#vehicles-table .no-filter-results');
        if (visibleCount === 0 && rows.length > 0) {
            if (!emptyRow) {
                emptyRow = document.createElement('tr');
                emptyRow.classList.add('no-filter-results');
                emptyRow.innerHTML = '<td colspan="9" class="text-center py-4 text-muted"><i class="bi bi-inbox me-2"></i>No vehicles match the selected filter.</td>';
                document.querySelector('#vehicles-table tbody').appendChild(emptyRow);
            }
            emptyRow.style.display = '';
        } else if (emptyRow) {
            emptyRow.style.display = 'none';
        }
    }
</script>


<script>
    // Filter route dropdown based on selected vehicle type
    document.addEventListener('DOMContentLoaded', function () {
        var typeSelect = document.getElementById('type');
        var routeSelect = document.getElementById('route_id');

        function filterRoutes() {
            if (!typeSelect || !routeSelect) return;
            var selectedType = typeSelect.value;
            var options = routeSelect.querySelectorAll('option[data-type]');
            options.forEach(function (opt) {
                if (!selectedType || opt.getAttribute('data-type') === selectedType) {
                    opt.style.display = '';
                    opt.disabled = false;
                } else {
                    opt.style.display = 'none';
                    opt.disabled = true;
                }
            });
            routeSelect.dispatchEvent(new Event('change'));
        }

        filterRoutes();
        if (typeSelect) {
            typeSelect.addEventListener('change', function() {
                if (routeSelect) routeSelect.value = '';
                filterRoutes();
            });
        }
    });

    function openAddVehicleTypeModal() {
        var manageEl = document.getElementById('manageVehicleTypesModal');
        var addEl = document.getElementById('addVehicleTypeModal');
        if (!addEl) return;
        addEl.dataset.fromManage = "true";

        function showAdd() {
            // Remove any leftover backdrops just in case
            document.querySelectorAll('.modal-backdrop').forEach(function(b) { b.remove(); });
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
            var addModal = bootstrap.Modal.getOrCreateInstance(addEl);
            addModal.show();
        }

        if (manageEl) {
            var manageModal = bootstrap.Modal.getInstance(manageEl);
            if (manageModal) {
                // Wait for the manage modal to fully hide before showing add modal
                manageEl.addEventListener('hidden.bs.modal', function onHidden() {
                    manageEl.removeEventListener('hidden.bs.modal', onHidden);
                    showAdd();
                });
                manageModal.hide();
            } else {
                showAdd();
            }
        } else {
            showAdd();
        }
    }

    function openEditVehicleTypeModal(id) {
        var manageEl = document.getElementById('manageVehicleTypesModal');
        var editEl = document.getElementById('editVehicleTypeModal' + id);
        if (!editEl) return;

        function showEdit() {
            // Remove any leftover backdrops just in case
            document.querySelectorAll('.modal-backdrop').forEach(function(b) { b.remove(); });
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
            var editModal = bootstrap.Modal.getOrCreateInstance(editEl);
            editModal.show();
        }

        if (manageEl) {
            var manageModal = bootstrap.Modal.getInstance(manageEl);
            if (manageModal) {
                manageEl.addEventListener('hidden.bs.modal', function onHidden() {
                    manageEl.removeEventListener('hidden.bs.modal', onHidden);
                    showEdit();
                });
                manageModal.hide();
            } else {
                showEdit();
            }
        } else {
            showEdit();
        }
    }

    function backToManageVehicleTypesModal(currentModalId) {
        var currentEl = document.getElementById(currentModalId);
        var manageEl = document.getElementById('manageVehicleTypesModal');
        if (!currentEl || !manageEl) return;

        function showManage() {
            document.querySelectorAll('.modal-backdrop').forEach(function(b) { b.remove(); });
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
            var manageModal = bootstrap.Modal.getOrCreateInstance(manageEl);
            manageModal.show();
        }

        var currentModal = bootstrap.Modal.getInstance(currentEl);
        if (currentModal) {
            currentEl.addEventListener('hidden.bs.modal', function onHidden() {
                currentEl.removeEventListener('hidden.bs.modal', onHidden);
                showManage();
            });
            currentModal.hide();
        } else {
            showManage();
        }
    }

    function closeAddVehicleTypeModal() {
        var addEl = document.getElementById('addVehicleTypeModal');
        if (addEl && addEl.dataset.fromManage === "true") {
            delete addEl.dataset.fromManage;
            backToManageVehicleTypesModal('addVehicleTypeModal');
        } else if (addEl) {
            var addModal = bootstrap.Modal.getInstance(addEl);
            if (addModal) {
                addModal.hide();
            }
        }
    }
</script>

<div class="modal fade" id="manageVehicleTypesModal" tabindex="-1" aria-labelledby="manageVehicleTypesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="manageVehicleTypesModalLabel"><i class="bi bi-gear-fill me-2"></i>Manage Vehicle Types</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <?php if (!empty($vehicleTypes)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($vehicleTypes as $vt): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center py-3 px-4">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?= base_url('images/' . vehicle_type_image($vt['slug'])) ?>" alt="" style="height:24px; width:auto;">
                                    <div>
                                        <div class="fw-bold text-dark"><?= esc($vt['name']) ?></div>
                                        <small class="text-muted">Slug: <code><?= esc($vt['slug']) ?></code> &bull; <?= $typeCounts[$vt['slug']] ?? 0 ?> vehicle(s)</small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="openEditVehicleTypeModal(<?= $vt['id'] ?>)" title="Edit Vehicle Type">
                                        <i class="bi bi-pencil me-1"></i> Edit
                                    </button>
                                    <form action="<?= base_url('admin/vehicle-types/delete/' . $vt['id']) ?>" method="post" class="d-inline"
                                          onsubmit="return confirm('WARNING: Are you sure you want to delete vehicle type &quot;<?= esc($vt['name']) ?>&quot;?\n\nThis will ALSO DELETE all cards in Route Fares, connected routes, and vehicles of this type!');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Vehicle Type">
                                            <i class="bi bi-trash me-1"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="p-4 text-center text-muted">No vehicle types configured.</div>
                <?php endif; ?>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="openAddVehicleTypeModal()">
                    <i class="bi bi-plus-lg me-1"></i> Add New Type
                </button>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($vehicleTypes)): ?>
    <?php foreach ($vehicleTypes as $vt): ?>
        <!-- Edit Vehicle Type Modal for <?= esc($vt['name']) ?> -->
        <div class="modal fade" id="editVehicleTypeModal<?= $vt['id'] ?>" tabindex="-1" aria-labelledby="editVehicleTypeModalLabel<?= $vt['id'] ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="editVehicleTypeModalLabel<?= $vt['id'] ?>"><i class="bi bi-pencil me-2"></i>Edit Vehicle Type</h5>
                        <button type="button" class="btn-close" onclick="backToManageVehicleTypesModal('editVehicleTypeModal<?= $vt['id'] ?>')" aria-label="Close"></button>
                    </div>
                    <form action="<?= base_url('admin/vehicle-types/update/' . $vt['id']) ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="modal-body">
                            <label for="edit_vehicle_type_name_<?= $vt['id'] ?>" class="form-label fw-semibold">Vehicle Type Name</label>
                            <input id="edit_vehicle_type_name_<?= $vt['id'] ?>" name="name" type="text" class="form-control" value="<?= esc($vt['name']) ?>" maxlength="80" required>
                            <div class="form-text mt-2">Updating the name will update the display name and slug across vehicles and routes.</div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" onclick="backToManageVehicleTypesModal('editVehicleTypeModal<?= $vt['id'] ?>')">Cancel</button>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<div class="modal fade" id="addVehicleTypeModal" tabindex="-1" aria-labelledby="addVehicleTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="addVehicleTypeModalLabel"><i class="bi bi-tags me-2"></i>Add Vehicle Type</h5>
                <button type="button" class="btn-close" onclick="closeAddVehicleTypeModal()" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('admin/vehicle-types/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <label for="vehicle_type_name" class="form-label fw-semibold">Vehicle Type Name</label>
                    <input id="vehicle_type_name" name="name" type="text" class="form-control" placeholder="e.g. Bus" maxlength="80" required>
                    <div class="form-text">It will be available in Vehicle Register and as a new Route Fares card.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" onclick="closeAddVehicleTypeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Type</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= view('templates/footer') ?>


