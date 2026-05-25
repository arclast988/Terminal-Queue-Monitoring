<?= view('templates/header', ['title' => $title]) ?>

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-journal-text text-primary me-2"></i> Vehicle Register</h1>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- Add Vehicle Form -->
<div class="card shadow mb-4 vehicle-register-form">
    <div class="card-header">
        <i class="bi bi-plus-circle"></i> Register New Vehicle
    </div>
    <div class="card-body">
        <form action="<?= base_url('admin/vehicles/store') ?>" method="post">
            <?= csrf_field() ?>
            <div class="row g-3 align-items-end">
                <div class="col-12 col-sm-6 col-xl-2">
                    <label for="driver_name" class="form-label">Driver Name</label>
                    <input type="text" class="form-control" id="driver_name" name="driver_name"
                        placeholder="e.g. Juan Dela Cruz" value="<?= old('driver_name') ?>" required>
                </div>
                <div class="col-12 col-sm-6 col-xl-2">
                    <label for="plate_number" class="form-label">Plate Number</label>
                    <input type="text" class="form-control" id="plate_number" name="plate_number"
                        placeholder="e.g. ABC-1234" value="<?= old('plate_number') ?>" required>
                </div>
                <div class="col-12 col-sm-6 col-xl-2">
                    <label for="type" class="form-label">Type</label>
                    <select class="form-select" id="type" name="type" required>
                        <option value="jeepney" <?= old('type') == 'jeepney' ? 'selected' : '' ?>>Jeepney</option>
                        <option value="van" <?= old('type') == 'van' ? 'selected' : '' ?>>Van</option>
                        <option value="minibus" <?= old('type') == 'minibus' ? 'selected' : '' ?>>Minibus</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <label for="route_id" class="form-label">Assigned Route</label>
                    <select class="form-select" id="route_id" name="route_id" required>
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
                <div class="col-12 col-sm-6 col-xl-1">
                    <label for="capacity" class="form-label">Capacity</label>
                    <input type="number" class="form-control" id="capacity" name="capacity" placeholder="16"
                        value="<?= old('capacity') ?>" min="1" required>
                </div>
                <div class="col-12 col-sm-6 col-xl-1">
                    <button type="submit" class="btn btn-primary w-100 vehicle-add-btn">
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
$countJeepney = 0;
$countVan = 0;
$countMinibus = 0;
$countActive = 0;
$countMaintenance = 0;
if (!empty($vehicles) && is_array($vehicles)) {
    foreach ($vehicles as $v) {
        if ($v['type'] === 'jeepney')
            $countJeepney++;
        elseif ($v['type'] === 'van')
            $countVan++;
        elseif ($v['type'] === 'minibus')
            $countMinibus++;
        if ($v['status'] === 'active')
            $countActive++;
        else
            $countMaintenance++;
    }
}
?>

<!-- Vehicle Filter & Summary Bar -->
<div class="mb-4">
    <!-- Filter Buttons Row -->
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <div class="input-group input-group-sm me-3" style="max-width: 250px;">
            <span class="input-group-text bg-white border-end-0" style="border-radius: 20px 0 0 20px;"><i class="bi bi-search text-muted"></i></span>
            <input type="text" class="form-control border-start-0 ps-0" id="vehicle-search" placeholder="Search plate or driver..." onkeyup="filterVehicles(currentFilter)" style="border-radius: 0 20px 20px 0;">
        </div>
        <span
            style="font-size:13px; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:0.5px; margin-right:4px;">
            <i class="bi bi-funnel me-1"></i>Filter:
        </span>
        <button type="button" class="vf-btn active" id="filter-btn-all" onclick="filterVehicles('all')">
            <i class="bi bi-grid-3x3-gap-fill"></i> All
            <span class="vf-count"><?= $totalVehicles ?></span>
        </button>
        <span class="vf-divider" style="width:1px; height:24px; background:#dee2e6; margin:0 4px;"></span>
        <button type="button" class="vf-btn vf-jeepney" id="filter-btn-jeepney" onclick="filterVehicles('jeepney')">
            <img src="<?= base_url('images/jeep.png') ?>" alt="" style="height:18px; width:auto;"> Jeepney
            <span class="vf-count"><?= $countJeepney ?></span>
        </button>
        <button type="button" class="vf-btn vf-van" id="filter-btn-van" onclick="filterVehicles('van')">
            <img src="<?= base_url('images/van.png') ?>" alt="" style="height:18px; width:auto;"> Van
            <span class="vf-count"><?= $countVan ?></span>
        </button>
        <button type="button" class="vf-btn vf-minibus" id="filter-btn-minibus" onclick="filterVehicles('minibus')">
            <img src="<?= base_url('images/minibus.png') ?>" alt="" style="height:18px; width:auto;"> Minibus
            <span class="vf-count"><?= $countMinibus ?></span>
        </button>
        <span class="vf-divider" style="width:1px; height:24px; background:#dee2e6; margin:0 4px;"></span>
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
</div>

<!-- Vehicles Table -->
<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center" style="padding:10px 16px;">
        <span style="font-weight:600; font-size:14px; color:#1e293b;"><i class="bi bi-list-ul me-1"></i> Vehicle List</span>
        <span id="filter-label" style="font-size:12px; color:#64748b;">Showing all <strong><?= $totalVehicles ?></strong> vehicles</span>
    </div>
    <div class="card-body">
        <div class="table-responsive table-responsive-card">
            <table class="table table-striped table-hover" id="vehicles-table">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Plate Number</th>
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
                                <td class="row-number" data-label="#"><?= $i + 1 ?></td>
                                <td data-label="Plate Number">
                                    <span class="fw-bold"><?= esc($vehicle['plate_number']) ?></span>
                                </td>
                                <td class="fw-bold" data-label="Driver Name"><?= esc($vehicle['driver_name']) ?></td>
                                <td data-label="Vehicle Type">
                                    <?php
                                    $imgMap = ['van' => 'van.png', 'jeepney' => 'jeep.png', 'minibus' => 'minibus.png'];
                                    $imgFile = $imgMap[$vehicle['type']] ?? 'van.png';
                                    ?>
                                    <img src="<?= base_url('images/' . $imgFile) ?>" alt="<?= ucfirst($vehicle['type']) ?>"
                                        style="height:32px; width:auto;" title="<?= ucfirst($vehicle['type']) ?>">
                                    <small class="d-block text-muted"
                                        style="font-size:10px;"><?= ucfirst($vehicle['type']) ?></small>
                                </td>
                                <td style="max-width: 250px;" data-label="Assigned Route">
                                    <?php if (!empty($vehicle['route_destination'])):
                                        $routeLabel = strtoupper(esc($vehicle['route_origin'])) . ' → ' . strtoupper(esc($vehicle['route_destination']));
                                    ?>
                                        <div class="badge-scroll-wrap" title="<?= $routeLabel ?>">
                                            <span class="badge bg-primary" style="font-size:10px; font-weight:500;">
                                                <?= $routeLabel ?>
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Not Assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Capacity"><?= $vehicle['capacity'] ?></td>
                                <td data-label="Status">
                                    <?php if ($vehicle['status'] == 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Maintenance</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Registered">
                                    <small class="text-muted"><?= date('M d, Y', strtotime($vehicle['created_at'])) ?></small>
                                </td>
                                <td data-label="Action">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="<?= base_url('admin/vehicles/edit/' . $vehicle['id']) ?>"
                                            class="btn btn-outline-primary" title="Edit vehicle">
                                            <i class="bi bi-pencil"></i> <span class="vehicle-action-label">Edit</span>
                                        </a>
                                        <form action="<?= base_url('admin/vehicles/delete/' . $vehicle['id']) ?>" method="post"
                                            class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to delete this vehicle?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline-danger" title="Delete vehicle">
                                                <i class="bi bi-trash"></i> <span class="vehicle-action-label">Delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr class="no-vehicles-row">
                            <td colspan="9" class="text-center py-4 text-muted">No vehicles registered yet.</td>
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
    }

    .vf-btn:hover {
        border-color: #94a3b8 !important;
        background: #f8fafc !important;
        color: #1e293b !important;
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
        background: #d97706 !important;
        border-color: #d97706 !important;
        color: #fff !important;
    }

    .vf-btn.vf-van.active {
        background: #059669 !important;
        border-color: #059669 !important;
        color: #fff !important;
    }

    .vf-btn.vf-minibus.active {
        background: #0891b2 !important;
        border-color: #0891b2 !important;
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

        #vehicles-table {
            min-width: 680px;
            font-size: 13px;
        }

        #vehicles-table thead th,
        #vehicles-table tbody td {
            padding: 0.65rem 0.5rem !important;
        }

        #vehicles-table th:first-child,
        #vehicles-table td.row-number {
            width: 38px;
            text-align: center;
        }

        #vehicles-table th:nth-child(4),
        #vehicles-table td:nth-child(4) {
            width: 86px;
            text-align: center;
        }

        #vehicles-table th:nth-child(5),
        #vehicles-table td:nth-child(5),
        #vehicles-table th:nth-child(6),
        #vehicles-table td:nth-child(6) {
            width: 84px;
            text-align: center;
        }

        #vehicles-table th:nth-child(8),
        #vehicles-table td:nth-child(8) {
            width: 88px;
            text-align: center;
        }

        #vehicles-table .btn-group {
            flex-direction: row !important;
            gap: 0.35rem;
        }

        #vehicles-table .btn-group .btn {
            width: 34px !important;
            height: 34px;
            padding: 0 !important;
            border-radius: 6px !important;
        }

        #vehicles-table .btn-group form {
            display: inline-flex !important;
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

<script>
    let currentFilter = 'all';

    function filterVehicles(filter) {
        currentFilter = filter;
        const rows = document.querySelectorAll('#vehicles-table tbody tr[data-type]');
        const filterLabel = document.getElementById('filter-label');
        const typeFilters = ['jeepney', 'van', 'minibus'];
        const statusFilters = ['active', 'maintenance'];

        // Update active button styling
        document.querySelectorAll('.vf-btn').forEach(btn => btn.classList.remove('active'));
        const activeBtn = document.getElementById('filter-btn-' + filter);
        if (activeBtn) activeBtn.classList.add('active');

        // Filter label map
        const labelMap = {
            'all': 'all',
            'jeepney': 'Jeepney',
            'van': 'Van',
            'minibus': 'Minibus',
            'active': 'Active',
            'maintenance': 'Maintenance'
        };

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
            var selectedType = typeSelect.value;
            routeSelect.value = '';
            var options = routeSelect.querySelectorAll('option[data-type]');
            options.forEach(function (opt) {
                if (opt.getAttribute('data-type') === selectedType) {
                    opt.style.display = '';
                    opt.disabled = false;
                } else {
                    opt.style.display = 'none';
                    opt.disabled = true;
                }
            });
        }

        filterRoutes();
        typeSelect.addEventListener('change', filterRoutes);
    });
</script>

<?= view('templates/footer') ?>