<?= view('templates/header', ['title' => $title]) ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">

<div class="page-header-modern fade-in">
    <h1 class="page-title-modern">
        <i class="bi bi-truck"></i>
        Vehicle Register
    </h1>
    <div class="page-header-actions vehicle-header-actions d-flex gap-2">
        <button type="button" class="btn-modern btn-modern-outline" data-bs-toggle="modal" data-bs-target="#manageVehicleTypesModal">
            <i class="bi bi-gear-fill me-1"></i> Manage Types
        </button>
        <button type="button" class="btn-modern btn-modern-primary" data-bs-toggle="modal" data-bs-target="#registerVehicleModal">
            <i class="bi bi-plus-circle me-1"></i> Register Vehicle
        </button>
    </div>
</div>

<?php 
$isManageVehicleTypesOpen = (bool) (session()->getFlashdata('manage_vehicle_types_open') || (isset($_GET['manage_types']) && $_GET['manage_types'] == '1'));
?>

<?php if (!$isManageVehicleTypesOpen): ?>
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
            <div style="flex: 1; min-width: 0;">
                <ul class="mb-0" style="padding-left: 20px;">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <?= view('partials/flash_notices') ?>
<?php endif; ?>


<?php
// Count vehicles by type and status
$totalVehicles = is_array($vehicles) ? count($vehicles) : 0;
$typeCounts = array_fill_keys(array_column($vehicleTypes ?? [], 'slug'), 0);
$countActive = 0;
$countMaintenance = 0;
$countArchived = 0;
if (!empty($vehicles) && is_array($vehicles)) {
    foreach ($vehicles as $v) {
        if ($v['status'] === 'archived') {
            $countArchived++;
            continue;
        }
        if (array_key_exists($v['type'], $typeCounts)) {
            $typeCounts[$v['type']]++;
        }
        if ($v['status'] === 'active') {
            $countActive++;
        } else {
            $countMaintenance++;
        }
    }
}
$countInService = $countActive + $countMaintenance;
?>

<!-- Vehicle Filter & Summary Bar -->
<div class="modern-card shadow-modern fade-in mb-4">
    <div class="modern-card-body">
        <!-- Filter Buttons Row -->
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <div class="vehicle-search-group me-3 position-relative">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" class="vehicle-search-input" id="vehicle-search" placeholder="Search plate, operator, or driver..." onkeyup="filterVehicles(currentFilter)" oninput="toggleVehicleClearBtn(this.value)" autocomplete="off">
                <button type="button" class="btn-clear-search" id="clear-vehicle-search" onclick="clearVehicleSearch()" style="display: none !important;" title="Clear search">
                    <i class="bi bi-x-circle-fill"></i>
                </button>
            </div>
            
            <span class="filter-label-text">
                <i class="bi bi-funnel me-1"></i>Filter:
            </span>
            
            <button type="button" class="vf-btn active" id="filter-btn-all" onclick="filterVehicles('all')">
                <i class="bi bi-grid-3x3-gap-fill"></i> All
                <span class="vf-count"><?= $countInService ?></span>
            </button>
            
            <span class="vf-divider"></span>
            
            <?php foreach (($vehicleTypes ?? []) as $vehicleType): ?>
            <?php 
                $tabImg = vehicle_type_photo($vehicleType['slug']);
                $tabCol = !empty($vehicleType['color']) ? $vehicleType['color'] : vehicle_type_color($vehicleType['slug']);
                $tabIco = !empty($vehicleType['icon']) ? $vehicleType['icon'] : vehicle_type_icon($vehicleType['slug']);
            ?>
            <button type="button" class="vf-btn vf-<?= esc($vehicleType['slug']) ?>" id="filter-btn-<?= esc($vehicleType['slug']) ?>" onclick="filterVehicles('<?= esc($vehicleType['slug']) ?>')">
                <?php if (!empty($tabImg)): ?>
                    <img src="<?= esc($tabImg) ?>" alt="" style="height:18px; width:auto; max-width:24px; object-fit:contain;">
                <?php else: ?>
                    <i class="fas <?= esc($tabIco) ?>" style="color: <?= esc($tabCol) ?>; font-size: 13px;"></i>
                <?php endif; ?>
                <?= esc($vehicleType['name']) ?>
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

            <span class="vf-divider"></span>

            <button type="button" class="vf-btn vf-archive" id="filter-btn-archived" onclick="filterVehicles('archived')">
                <i class="bi bi-archive-fill"></i> Archived
                <span class="vf-count"><?= $countArchived ?></span>
            </button>
        </div>
        <div id="filter-label" style="font-size:13px; color:var(--slate-500);">Showing all <strong><?= $countInService ?></strong> in-service vehicles</div>
    </div>
</div>

<style>
/* Scoped Selection & Bulk Action Rules - Matches App Action Buttons */
.bulk-col,
.bulk-select-cell,
#vehicles-table:not(.selection-mode-active) .bulk-col,
#vehicles-table:not(.selection-mode-active) .bulk-select-cell,
table:not(.selection-mode-active) .bulk-col,
table:not(.selection-mode-active) .bulk-select-cell {
    display: none !important;
}
@media (min-width: 769px) {
    .bulk-col,
    .bulk-select-cell {
        width: 44px !important;
        min-width: 44px !important;
        max-width: 44px !important;
        text-align: center !important;
        vertical-align: middle !important;
        padding: 8px 6px !important;
    }
    .table-modern.selection-mode-active .bulk-col,
    .table-modern.selection-mode-active .bulk-select-cell,
    .selection-mode-active .bulk-col,
    .selection-mode-active .bulk-select-cell {
        display: table-cell !important;
    }
}
.bulk-action-top-bar {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 8px 14px;
    margin-bottom: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    display: none;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
}
.bulk-action-top-bar.is-visible {
    display: flex !important;
}
.bulk-bar-info {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    color: #1e293b;
}
.bulk-select-all-wrap {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    cursor: pointer;
    user-select: none;
    margin-bottom: 0;
}
.bulk-bar-divider {
    display: inline-block;
    width: 1px;
    height: 16px;
    background: #e2e8f0;
}
.bulk-count-badge {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    padding: 2px 7px;
    font-size: 12px;
    font-weight: 600;
}
.bulk-count-text {
    font-size: 13px;
    color: #64748b;
}
.bulk-bar-actions {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.btn-bulk-deactivate {
    background: #fef3c7 !important;
    color: #d97706 !important;
    border: 1px solid #fde68a !important;
    font-weight: 600 !important;
    border-radius: 6px !important;
    padding: 5px 12px !important;
    font-size: 13px !important;
    align-items: center;
    gap: 6px;
    box-shadow: none !important;
    transition: all 0.15s ease !important;
    cursor: pointer !important;
}
.btn-bulk-deactivate:hover:not([disabled]) {
    background: #f59e0b !important;
    color: #ffffff !important;
    border-color: #f59e0b !important;
}
.btn-bulk-deactivate:hover:not([disabled]) i,
.btn-bulk-deactivate:hover:not([disabled]) span {
    color: #ffffff !important;
}

.btn-bulk-activate {
    background: #dcfce7 !important;
    color: #15803d !important;
    border: 1px solid #bbf7d0 !important;
    font-weight: 600 !important;
    border-radius: 6px !important;
    padding: 5px 12px !important;
    font-size: 13px !important;
    align-items: center;
    gap: 6px;
    box-shadow: none !important;
    transition: all 0.15s ease !important;
    cursor: pointer !important;
}
.btn-bulk-activate:hover:not([disabled]) {
    background: #16a34a !important;
    color: #ffffff !important;
    border-color: #16a34a !important;
}
.btn-bulk-activate:hover:not([disabled]) i,
.btn-bulk-activate:hover:not([disabled]) span {
    color: #ffffff !important;
}

.btn-bulk-delete {
    background: #fee2e2 !important;
    color: #dc2626 !important;
    border: 1px solid #fecaca !important;
    font-weight: 600 !important;
    border-radius: 6px !important;
    padding: 5px 12px !important;
    font-size: 13px !important;
    align-items: center;
    gap: 6px;
    box-shadow: none !important;
    transition: all 0.15s ease !important;
    cursor: pointer !important;
}
.btn-bulk-delete:hover:not([disabled]) {
    background: #dc2626 !important;
    color: #ffffff !important;
    border-color: #dc2626 !important;
}
.btn-bulk-delete:hover:not([disabled]) i,
.btn-bulk-delete:hover:not([disabled]) span {
    color: #ffffff !important;
}

.btn-bulk-deactivate[disabled],
.btn-bulk-activate[disabled],
.btn-bulk-delete[disabled],
.btn-bulk-deactivate:disabled,
.btn-bulk-activate:disabled,
.btn-bulk-delete:disabled {
    opacity: 0.45 !important;
    cursor: not-allowed !important;
    pointer-events: none !important;
    box-shadow: none !important;
    transform: none !important;
}

.btn-bulk-cancel {
    background: #ffffff !important;
    border: 1.5px solid #cbd5e1 !important;
    color: #475569 !important;
    border-radius: 6px !important;
    padding: 5px 12px !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    transition: all 0.15s ease !important;
    cursor: pointer !important;
    align-items: center;
    gap: 4px;
}
.btn-bulk-cancel:hover {
    background: #f1f5f9 !important;
    color: #0f172a !important;
    border-color: #94a3b8 !important;
}
</style>

<!-- Vehicles Table -->
<div class="modern-card shadow-modern fade-in">
    <div class="modern-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="modern-card-title">
            <i class="bi bi-list-ul" style="color: var(--primary-red);"></i>
            Vehicle List
        </span>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn-modern btn-modern-sm btn-modern-outline" id="btn-toggle-select-vehicles" onclick="toggleVehicleSelectMode()" title="Toggle selection mode for batch actions">
                <i class="bi bi-check2-square me-1"></i> <span id="btn-select-vehicles-text">Select</span>
            </button>
        </div>
    </div>
    <div class="modern-card-body">
        <!-- Integrated Top Bulk Action Bar -->
        <div id="vehicle-bulk-toolbar" class="bulk-action-top-bar" style="display: none;">
            <div class="bulk-bar-info">
                <label class="bulk-select-all-wrap mb-0">
                    <input type="checkbox" id="select-all-vehicles" class="form-check-input select-all-checkbox m-0" style="width: 18px; height: 18px; cursor: pointer;">
                    <span class="fw-semibold text-dark" style="font-size: 13.5px;">Select All</span>
                </label>
                <span class="bulk-bar-divider"></span>
                <span class="bulk-count-badge">
                    <span id="vehicle-selected-count">0</span> <span id="vehicle-selected-text">selected</span>
                </span>
            </div>
            <div class="bulk-bar-actions">
                <button type="button" class="btn-modern btn-modern-sm btn-action-deactivate btn-bulk-deactivate" id="btn-bulk-deactivate-vehicles" onclick="openVehicleBulkModal('deactivate')" disabled>
                    <i class="bi bi-pause-circle"></i> <span>Deactivate Selected</span>
                </button>
                <button type="button" class="btn-modern btn-modern-sm btn-action-activate btn-bulk-activate" id="btn-bulk-activate-vehicles" onclick="openVehicleBulkModal('activate')" style="display: none;" disabled>
                    <i class="bi bi-check-circle"></i> <span>Activate Selected</span>
                </button>
                <button type="button" class="btn-modern btn-modern-sm btn-action-delete btn-bulk-delete" id="btn-bulk-delete-vehicles" onclick="openVehicleBulkModal('delete')" style="display: none;" disabled>
                    <i class="bi bi-trash"></i> <span>Delete Selected</span>
                </button>
                <button type="button" class="btn-modern btn-modern-sm btn-modern-outline btn-bulk-cancel" onclick="toggleVehicleSelectMode(false)">
                    <i class="bi bi-x"></i> <span>Cancel</span>
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table-modern" id="vehicles-table">
                <thead>
                    <tr>
                        <th class="bulk-col" style="display: none; width: 44px; text-align: center;"></th>
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
                            <tr data-type="<?= esc($vehicle['type']) ?>" data-status="<?= esc($vehicle['status']) ?>" data-vehicle-id="<?= $vehicle['id'] ?>">
                                <td data-label="Select" class="bulk-col bulk-select-cell" style="display: none; text-align: center;">
                                    <input type="checkbox" class="form-check-input vehicle-row-checkbox" value="<?= $vehicle['id'] ?>" data-status="<?= esc($vehicle['status']) ?>" data-plate="<?= esc($vehicle['plate_number']) ?>" title="Select vehicle <?= esc($vehicle['plate_number']) ?>">
                                </td>
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
                                    <?php 
                                        $hasVehPhoto = !empty($vehicle['photo']);
                                        $vehPhotoUrl = $hasVehPhoto ? base_url($vehicle['photo']) : null;
                                        $typePhotoUrl = vehicle_type_photo($vehicle['type']);
                                        $displayPhoto = $vehPhotoUrl ?: $typePhotoUrl;
                                    ?>
                                    <div class="vehicle-type-cell">
                                        <?php if (!empty($displayPhoto)): ?>
                                            <span class="vehicle-type-icon <?= vehicle_type_class($vehicle['type']) ?>" style="<?= $hasVehPhoto ? 'border-radius: 8px; overflow: hidden; width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; border: 1.5px solid #cbd5e1; background: #ffffff;' : '' ?>">
                                                <img src="<?= esc($displayPhoto) ?>" alt="<?= vehicle_type_label($vehicle['type']) ?>"
                                                    style="<?= $hasVehPhoto ? 'width: 100%; height: 100%; object-fit: cover;' : 'height:28px; width:auto; max-width:32px; object-fit:contain;' ?>" 
                                                    title="<?= $hasVehPhoto ? ('Vehicle ' . esc($vehicle['plate_number']) . ' (' . vehicle_type_label($vehicle['type']) . ')') : vehicle_type_label($vehicle['type']) ?>">
                                            </span>
                                        <?php else: ?>
                                            <?php 
                                                $cellCol = vehicle_type_color($vehicle['type']);
                                                $cellIco = vehicle_type_icon($vehicle['type']);
                                            ?>
                                            <span class="vehicle-type-icon <?= vehicle_type_class($vehicle['type']) ?>" style="display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:8px; background:<?= esc($cellCol) ?>18; border:1px solid <?= esc($cellCol) ?>40; color:<?= esc($cellCol) ?>; font-size:16px;">
                                                <i class="fas <?= esc($cellIco) ?>" title="<?= vehicle_type_label($vehicle['type']) ?>"></i>
                                            </span>
                                        <?php endif; ?>
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
                                    <?php if ($vehicle['status'] === 'active'): ?>
                                        <span class="badge-modern badge-modern-success">Active</span>
                                    <?php elseif ($vehicle['status'] === 'maintenance'): ?>
                                        <span class="badge-modern badge-modern-danger">Maintenance</span>
                                    <?php else: ?>
                                        <span class="badge-modern badge-modern-secondary" style="background:#f1f5f9; color:#64748b; border:1px solid #cbd5e1;"><i class="bi bi-archive me-1"></i>Archived</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Registered">
                                    <div style="white-space: nowrap; font-size: 13px; font-weight: 600; color: var(--text-main);">
                                        <i class="bi bi-calendar3 text-muted me-1" style="font-size: 12px;"></i><?= !empty($vehicle['created_at']) ? date('M d, Y', strtotime($vehicle['created_at'])) : 'N/A' ?>
                                    </div>
                                </td>
                                <td data-label="Action" style="white-space: nowrap;">
                                    <div class="d-flex gap-2 justify-content-end flex-nowrap">
                                        <?php if (($vehicle['status'] ?? 'active') === 'archived'): ?>
                                            <button type="button" class="btn-modern btn-modern-sm btn-action-activate" title="Activate vehicle"
                                                onclick="showActivateVehicleModal({
                                                    vehicleId: '<?= $vehicle['id'] ?>',
                                                    plateNumber: '<?= esc(addslashes($vehicle['plate_number'])) ?>',
                                                    typeLabel: '<?= esc(addslashes(vehicle_type_label($vehicle['type']))) ?>',
                                                    typeColor: '<?= esc(addslashes(vehicle_type_color($vehicle['type']))) ?>',
                                                    driverName: '<?= esc(addslashes($vehicle['driver_name'] ?? '')) ?>',
                                                    operatorName: '<?= esc(addslashes($vehicle['operator_name'] ?? '')) ?>',
                                                    route: '<?= esc(addslashes(!empty($vehicle['route_destination']) ? strtoupper($vehicle['route_origin']) . ' → ' . strtoupper($vehicle['route_destination']) : '')) ?>'
                                                })">
                                                <i class="bi bi-check-circle"></i> <span class="action-label">Activate</span>
                                            </button>
                                            <a href="<?= base_url('admin/vehicles/edit/' . $vehicle['id']) ?>"
                                                class="btn-modern btn-action-edit btn-modern-sm" title="Edit vehicle">
                                                <i class="bi bi-pencil"></i> <span class="action-label">Edit</span>
                                            </a>
                                            <form id="delete-vehicle-form-<?= $vehicle['id'] ?>" action="<?= base_url('admin/vehicles/delete/' . $vehicle['id']) ?>" method="post" class="d-inline">
                                                <input type="hidden" name="redirect_tab" value="archived">
                                                <?= csrf_field() ?>
                                                <button type="button" class="btn-modern btn-modern-sm btn-action-delete" title="Permanently delete vehicle"
                                                    onclick="showDeleteVehicleModal({
                                                        formId: 'delete-vehicle-form-<?= $vehicle['id'] ?>',
                                                        vehicleId: '<?= $vehicle['id'] ?>',
                                                        plateNumber: '<?= esc(addslashes($vehicle['plate_number'])) ?>',
                                                        typeLabel: '<?= esc(addslashes(vehicle_type_label($vehicle['type']))) ?>',
                                                        typeColor: '<?= esc(addslashes(vehicle_type_color($vehicle['type']))) ?>',
                                                        driverName: '<?= esc(addslashes($vehicle['driver_name'] ?? '')) ?>',
                                                        operatorName: '<?= esc(addslashes($vehicle['operator_name'] ?? '')) ?>',
                                                        route: '<?= esc(addslashes(!empty($vehicle['route_destination']) ? strtoupper($vehicle['route_origin']) . ' → ' . strtoupper($vehicle['route_destination']) : '')) ?>'
                                                    })">
                                                    <i class="bi bi-trash"></i> <span class="action-label">Delete</span>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <a href="<?= base_url('admin/vehicles/edit/' . $vehicle['id']) ?>"
                                                class="btn-modern btn-action-edit btn-modern-sm" title="Edit vehicle">
                                                <i class="bi bi-pencil"></i> <span class="action-label">Edit</span>
                                            </a>
                                            <button type="button" class="btn-modern btn-modern-sm btn-action-deactivate" title="Deactivate vehicle"
                                                onclick="showDeactivateVehicleModal({
                                                    vehicleId: '<?= $vehicle['id'] ?>',
                                                    plateNumber: '<?= esc(addslashes($vehicle['plate_number'])) ?>',
                                                    typeLabel: '<?= esc(addslashes(vehicle_type_label($vehicle['type']))) ?>',
                                                    typeColor: '<?= esc(addslashes(vehicle_type_color($vehicle['type']))) ?>',
                                                    driverName: '<?= esc(addslashes($vehicle['driver_name'] ?? '')) ?>',
                                                    operatorName: '<?= esc(addslashes($vehicle['operator_name'] ?? '')) ?>',
                                                    route: '<?= esc(addslashes(!empty($vehicle['route_destination']) ? strtoupper($vehicle['route_origin']) . ' → ' . strtoupper($vehicle['route_destination']) : '')) ?>'
                                                })">
                                                <i class="bi bi-pause-circle"></i> <span class="action-label">Deactivate</span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr class="no-vehicles-row">
                            <td colspan="11" class="text-center py-5 text-muted empty-state-table">
                                <i class="bi bi-truck fs-1 d-block mb-3 opacity-50"></i>
                                <div class="fw-bold fs-6 empty-state-title">No vehicles registered yet</div>
                                <small class="empty-state-subtitle">Use the form above to register your first terminal vehicle.</small>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>



<!-- Vehicle Bulk Confirmation Modal -->
<div class="modal fade" id="vehicleBulkConfirmModal" tabindex="-1" aria-labelledby="vehicleBulkConfirmLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px; margin: 1.75rem auto;">
        <div class="modal-content delete-vehicle-modal-content">
            <div id="vehicleBulkStripe" style="height: 4px; width: 100%; background: #f59e0b;"></div>
            <form id="vehicleBulkForm" method="post" action="<?= base_url('admin/vehicles/bulk-action') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" id="vehicleBulkActionInput" value="">
                <input type="hidden" name="redirect_tab" id="vehicleBulkRedirectTab" value="">
                <div id="vehicleBulkIdsContainer"></div>

                <div class="modal-body text-center p-4">
                    <div id="vehicleBulkIconBadge" style="width: 68px; height: 68px; margin: 4px auto 18px auto; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; background: #fef3c7; color: #d97706;">
                        <i id="vehicleBulkIcon" class="bi bi-pause-circle-fill"></i>
                    </div>

                    <h4 class="delete-vehicle-modal-title" id="vehicleBulkConfirmTitle">Deactivate Selected Vehicles?</h4>
                    <p class="delete-vehicle-modal-desc" id="vehicleBulkConfirmDesc">
                        Are you sure you want to deactivate the selected vehicles?
                    </p>

                    <div id="vehicleBulkSelectedList" class="p-2 mb-2 text-start" style="max-height: 140px; overflow-y: auto; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px;">
                    </div>
                </div>

                <div class="modal-footer delete-vehicle-modal-footer">
                    <button type="button" class="btn delete-vehicle-btn-cancel" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn" id="vehicleBulkSubmitBtn" style="flex: 1; font-weight: 600; padding: 10px 18px; border-radius: 10px; border: none; background: #f59e0b; color: #fff;">
                        Confirm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<style>
    .dropdown-chevron-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        color: #475569;
        font-size: 12px;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: center;
    }

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
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15) !important;
    }
    .vf-btn#filter-btn-all.active:hover {
        box-shadow: 0 4px 12px rgba(198, 40, 40, 0.25) !important;
    }
    .vf-btn.vf-active-status.active:hover {
        box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25) !important;
    }
    .vf-btn.vf-maintenance-status.active:hover {
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25) !important;
    }
    .vf-btn.vf-archive.active:hover {
        box-shadow: 0 4px 12px rgba(71, 85, 105, 0.25) !important;
    }

    .vf-count {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 22px !important;
        height: 22px !important;
        padding: 0 6px !important;
        border-radius: 12px !important;
        font-size: 12px !important;
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

    /* Active states for vehicle type filters (strictly derived from vehicle theme colors) */
    <?php 
    $typesToRender = $vehicleTypes ?? [];
    $renderedSlugs = array_column($typesToRender, 'slug');
    foreach (['jeepney', 'van', 'minibus', 'bus'] as $fallbackSlug) {
        if (!in_array($fallbackSlug, $renderedSlugs, true)) {
            $typesToRender[] = ['slug' => $fallbackSlug, 'color' => vehicle_type_color($fallbackSlug)];
        }
    }
    ?>
    <?php foreach ($typesToRender as $vt): ?>
    <?php
        $slug = esc($vt['slug']);
        $col = !empty($vt['color']) ? $vt['color'] : vehicle_type_color($slug);
    ?>
    .vf-btn.vf-<?= $slug ?>.active {
        background: var(--vehicle-<?= $slug ?>-soft, <?= $col ?>18) !important;
        border-color: var(--vehicle-<?= $slug ?>, <?= $col ?>) !important;
        color: var(--vehicle-<?= $slug ?>, <?= $col ?>) !important;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08) !important;
    }
    .vf-btn.vf-<?= $slug ?>.active .vf-count {
        background: var(--vehicle-<?= $slug ?>-soft, <?= $col ?>25) !important;
        color: var(--vehicle-<?= $slug ?>, <?= $col ?>) !important;
    }
    .vf-btn.vf-<?= $slug ?>.active:hover {
        background: var(--vehicle-<?= $slug ?>-soft, <?= $col ?>25) !important;
        border-color: var(--vehicle-<?= $slug ?>, <?= $col ?>) !important;
        color: var(--vehicle-<?= $slug ?>, <?= $col ?>) !important;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.12) !important;
        filter: brightness(0.96);
    }
    <?php endforeach; ?>

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

    .vf-btn.vf-archive.active {
        background: #475569 !important;
        border-color: #475569 !important;
        color: #fff !important;
    }

    .vf-btn.vf-archive.active:hover {
        background: #334155 !important;
        border-color: #334155 !important;
        box-shadow: 0 4px 12px rgba(71, 85, 105, 0.3) !important;
    }

    .vf-btn.vf-archive:hover:not(.active) {
        border-color: #94a3b8 !important;
        background: #f1f5f9 !important;
        color: #334155 !important;
    }

    .vf-btn.vf-active-status.active .vf-count,
    .vf-btn.vf-maintenance-status.active .vf-count,
    .vf-btn.vf-archive.active .vf-count {
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

        .vehicle-action-label,
        .action-label {
            display: inline !important;
        }

        .btn-group-modern {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 4px !important;
            align-items: center !important;
        }

        .btn-group-modern .btn-modern {
            padding: 4px 8px !important;
            font-size: 11.5px !important;
            border-radius: 6px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 4px !important;
            white-space: nowrap !important;
        }

    @media (max-width: 768px) {
        .vehicle-search-group {
            max-width: 100% !important;
            width: 100% !important;
            margin-right: 0 !important;
            margin-bottom: 8px !important;
        }
        .filter-label-text {
            width: 100%;
            margin-bottom: 4px;
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

    function toggleVehicleClearBtn(val) {
        const btn = document.getElementById('clear-vehicle-search');
        if (btn) {
            if (val && val.trim().length > 0) {
                btn.style.setProperty('display', 'inline-flex', 'important');
            } else {
                btn.style.setProperty('display', 'none', 'important');
            }
        }
    }

    function clearVehicleSearch() {
        const input = document.getElementById('vehicle-search');
        if (input) {
            input.value = '';
            toggleVehicleClearBtn('');
            input.focus();
            filterVehicles(currentFilter);
        }
    }

    function filterVehicles(filter) {
        currentFilter = filter;
        const rows = document.querySelectorAll('#vehicles-table tbody tr[data-type]');
        const filterLabel = document.getElementById('filter-label');
        const typeFilters = Object.keys(vehicleTypeLabels);
        const statusFilters = ['active', 'maintenance', 'archived'];

        // Update active button styling
        document.querySelectorAll('.vf-btn').forEach(btn => btn.classList.remove('active'));
        const activeBtn = document.getElementById('filter-btn-' + filter);
        if (activeBtn) activeBtn.classList.add('active');

        // Filter label map
        const labelMap = Object.assign({
            'all': 'in-service',
            'active': 'Active',
            'maintenance': 'Maintenance',
            'archived': 'Archived'
        }, vehicleTypeLabels);

        const searchInput = document.getElementById('vehicle-search');
        const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
        toggleVehicleClearBtn(searchQuery);

        let visibleCount = 0;
        rows.forEach(row => {
            let show = false;
            const status = row.getAttribute('data-status') || 'active';
            const isArchived = (status === 'archived');

            if (filter === 'all') {
                show = !isArchived;
            } else if (filter === 'archived') {
                show = isArchived;
            } else if (filter === 'active' || filter === 'maintenance') {
                show = (status === filter);
            } else if (typeFilters.includes(filter)) {
                show = !isArchived && (row.getAttribute('data-type') === filter);
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
                const numEl = row.querySelector('.row-number strong') || row.querySelector('.row-number');
                if (numEl) numEl.textContent = num++;
            }
        });

        // Update URL tab parameter for persistence
        const newUrl = new URL(window.location);
        if (filter === 'all') {
            newUrl.searchParams.delete('tab');
        } else {
            newUrl.searchParams.set('tab', filter);
        }
        window.history.replaceState({}, '', newUrl);

        // Update single deactivate modal redirect_tab
        const deactRedirectEl = document.getElementById('deactivateVehicleRedirectTab');
        if (deactRedirectEl) deactRedirectEl.value = filter;

        // Reset selection & exit select mode whenever filter changes
        toggleVehicleSelectMode(false);

        // Update filter label text
        if (filter === 'all') {
            filterLabel.innerHTML = 'Showing all <strong>' + visibleCount + '</strong> in-service vehicles';
        } else if (filter === 'archived') {
            filterLabel.innerHTML = 'Showing <strong>' + visibleCount + '</strong> archived vehicle' + (visibleCount !== 1 ? 's' : '');
        } else {
            filterLabel.innerHTML = 'Showing <strong>' + visibleCount + '</strong> ' + (labelMap[filter] || filter) + ' vehicle' + (visibleCount !== 1 ? 's' : '');
        }

        // Handle empty state
        let emptyRow = document.querySelector('#vehicles-table .no-filter-results');
        if (visibleCount === 0 && rows.length > 0) {
            if (!emptyRow) {
                emptyRow = document.createElement('tr');
                emptyRow.classList.add('no-filter-results');
                emptyRow.innerHTML = '<td colspan="11" class="text-center py-5 text-muted empty-state-table"><i class="bi bi-search fs-1 d-block mb-3 opacity-50"></i><div class="fw-bold fs-6 empty-state-title">No vehicles match the selected filter</div><small class="empty-state-subtitle">Try selecting a different filter or clearing your search.</small></td>';
                document.querySelector('#vehicles-table tbody').appendChild(emptyRow);
            }
            emptyRow.style.removeProperty('display');
            emptyRow.classList.remove('d-none');
        } else if (emptyRow) {
            emptyRow.remove();
        }
    }

    // Selection mode state & toggler
    let isVehicleSelectMode = false;
    function toggleVehicleSelectMode(forceState) {
        if (typeof forceState === 'boolean') {
            isVehicleSelectMode = forceState;
        } else {
            isVehicleSelectMode = !isVehicleSelectMode;
        }
        const table = document.getElementById('vehicles-table');
        const toolbar = document.getElementById('vehicle-bulk-toolbar');
        const btnText = document.getElementById('btn-select-vehicles-text');
        const btn = document.getElementById('btn-toggle-select-vehicles');
        const bulkCells = document.querySelectorAll('#vehicles-table .bulk-col');

        if (isVehicleSelectMode) {
            if (table) table.classList.add('selection-mode-active');
            bulkCells.forEach(function(c) {
                c.style.removeProperty('display');
            });
            if (toolbar) {
                toolbar.classList.add('is-visible');
                toolbar.style.setProperty('display', 'flex', 'important');
            }
            if (btnText) btnText.textContent = 'Exit Select';
            if (btn) btn.classList.add('active');
            updateVehicleBulkToolbar();
        } else {
            if (table) table.classList.remove('selection-mode-active');
            bulkCells.forEach(function(c) {
                c.style.removeProperty('display');
            });
            if (toolbar) {
                toolbar.classList.remove('is-visible');
                toolbar.style.setProperty('display', 'none', 'important');
            }
            if (btnText) btnText.textContent = 'Select';
            if (btn) btn.classList.remove('active');
            clearVehicleSelection();
        }
    }

    // Bulk selection handlers
    function updateVehicleBulkToolbar() {
        const checkedBoxes = Array.from(document.querySelectorAll('.vehicle-row-checkbox:checked'));
        const toolbar = document.getElementById('vehicle-bulk-toolbar');
        const countEl = document.getElementById('vehicle-selected-count');
        const textEl = document.getElementById('vehicle-selected-text');
        const btnDeact = document.getElementById('btn-bulk-deactivate-vehicles');
        const btnAct = document.getElementById('btn-bulk-activate-vehicles');
        const btnDel = document.getElementById('btn-bulk-delete-vehicles');

        if (!toolbar) return;

        const count = checkedBoxes.length;
        if (countEl) countEl.textContent = count;
        if (textEl) textEl.textContent = (count === 1 ? 'vehicle selected' : 'vehicles selected');

        const hasSelection = count > 0;

        // Toggle which buttons show based on current active tab
        if (currentFilter === 'archived') {
            if (btnDeact) btnDeact.style.setProperty('display', 'none', 'important');
            if (btnAct) {
                btnAct.style.setProperty('display', 'inline-flex', 'important');
                btnAct.disabled = !hasSelection;
            }
            if (btnDel) {
                btnDel.style.setProperty('display', 'inline-flex', 'important');
                btnDel.disabled = !hasSelection;
            }
        } else {
            if (btnDeact) {
                btnDeact.style.setProperty('display', 'inline-flex', 'important');
                btnDeact.disabled = !hasSelection;
            }
            if (btnAct) btnAct.style.setProperty('display', 'none', 'important');
            if (btnDel) btnDel.style.setProperty('display', 'none', 'important');
        }

        const selectAll = document.getElementById('select-all-vehicles');
        const tableHeadSelectAll = document.getElementById('table-head-select-all-vehicles');
        const visibleCheckboxes = Array.from(document.querySelectorAll('#vehicles-table tbody tr[data-type]'))
            .filter(tr => !tr.classList.contains('vehicle-row-hidden'))
            .map(tr => tr.querySelector('.vehicle-row-checkbox'))
            .filter(cb => cb);

        const allChecked = visibleCheckboxes.length > 0 && visibleCheckboxes.every(cb => cb.checked);
        const someChecked = visibleCheckboxes.some(cb => cb.checked) && !allChecked;

        if (selectAll) {
            selectAll.checked = allChecked;
            selectAll.indeterminate = someChecked;
        }
        if (tableHeadSelectAll) {
            tableHeadSelectAll.checked = allChecked;
            tableHeadSelectAll.indeterminate = someChecked;
        }
    }

    function clearVehicleSelection() {
        document.querySelectorAll('.vehicle-row-checkbox').forEach(cb => cb.checked = false);
        const selectAll = document.getElementById('select-all-vehicles');
        if (selectAll) { selectAll.checked = false; selectAll.indeterminate = false; }
        updateVehicleBulkToolbar();
    }

    function openVehicleBulkModal(action) {
        const checkedBoxes = Array.from(document.querySelectorAll('.vehicle-row-checkbox:checked'));
        if (checkedBoxes.length === 0) return;

        const modalEl = document.getElementById('vehicleBulkConfirmModal');
        const actionInput = document.getElementById('vehicleBulkActionInput');
        const redirectInput = document.getElementById('vehicleBulkRedirectTab');
        const idsContainer = document.getElementById('vehicleBulkIdsContainer');
        const titleEl = document.getElementById('vehicleBulkConfirmTitle');
        const descEl = document.getElementById('vehicleBulkConfirmDesc');
        const listEl = document.getElementById('vehicleBulkSelectedList');
        const stripeEl = document.getElementById('vehicleBulkStripe');
        const iconBadge = document.getElementById('vehicleBulkIconBadge');
        const iconEl = document.getElementById('vehicleBulkIcon');
        const submitBtn = document.getElementById('vehicleBulkSubmitBtn');

        if (actionInput) actionInput.value = action;
        if (redirectInput) redirectInput.value = currentFilter;

        if (idsContainer) {
            idsContainer.innerHTML = '';
            checkedBoxes.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = cb.value;
                idsContainer.appendChild(input);
            });
        }

        if (listEl) {
            listEl.innerHTML = checkedBoxes.map(cb => {
                const plate = cb.getAttribute('data-plate') || 'Vehicle';
                return `<span class="badge bg-light text-dark border me-1 mb-1" style="font-family: monospace; font-size: 13px; font-weight: 600;">${plate}</span>`;
            }).join(' ');
        }

        const count = checkedBoxes.length;

        if (action === 'deactivate') {
            if (titleEl) titleEl.textContent = `Deactivate ${count} Selected Vehicle${count > 1 ? 's' : ''}?`;
            if (descEl) descEl.textContent = `Are you sure you want to deactivate these ${count} vehicles? They will be removed from active queue operations and moved to archive.`;
            if (stripeEl) stripeEl.style.background = 'linear-gradient(90deg, #f59e0b 0%, #d97706 100%)';
            if (iconBadge) {
                iconBadge.style.background = '#fef3c7';
                iconBadge.style.color = '#d97706';
            }
            if (iconEl) iconEl.className = 'bi bi-pause-circle-fill';
            if (submitBtn) {
                submitBtn.style.background = 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)';
                submitBtn.textContent = 'Yes, Deactivate All';
            }
        } else if (action === 'activate') {
            if (titleEl) titleEl.textContent = `Activate ${count} Selected Vehicle${count > 1 ? 's' : ''}?`;
            if (descEl) descEl.textContent = `Are you sure you want to reactivate these ${count} vehicles? They will be restored from archive and become eligible for queue assignments.`;
            if (stripeEl) stripeEl.style.background = 'linear-gradient(90deg, #10b981 0%, #059669 100%)';
            if (iconBadge) {
                iconBadge.style.background = '#ecfdf5';
                iconBadge.style.color = '#059669';
            }
            if (iconEl) iconEl.className = 'bi bi-check-circle-fill';
            if (submitBtn) {
                submitBtn.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
                submitBtn.textContent = 'Yes, Activate All';
            }
        } else if (action === 'delete') {
            if (titleEl) titleEl.textContent = `Permanently Delete ${count} Vehicle${count > 1 ? 's' : ''}?`;
            if (descEl) descEl.textContent = `Are you sure you want to permanently delete these ${count} vehicles from the system? This action CANNOT be undone.`;
            if (stripeEl) stripeEl.style.background = 'linear-gradient(90deg, #ef4444 0%, #dc2626 100%)';
            if (iconBadge) {
                iconBadge.style.background = '#fee2e2';
                iconBadge.style.color = '#dc2626';
            }
            if (iconEl) iconEl.className = 'bi bi-trash-fill';
            if (submitBtn) {
                submitBtn.style.background = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
                submitBtn.textContent = 'Yes, Permanently Delete All';
            }
        }

        if (modalEl && window.bootstrap) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    // Hide archived rows on initial page load or restore from ?tab=
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const initialTab = urlParams.get('tab') || 'all';
        filterVehicles(initialTab);

        // Select All listeners (both in top bar and table header)
        function handleSelectAll(isChecked) {
            const rows = document.querySelectorAll('#vehicles-table tbody tr[data-type]');
            rows.forEach(tr => {
                if (!tr.classList.contains('vehicle-row-hidden')) {
                    const cb = tr.querySelector('.vehicle-row-checkbox');
                    if (cb) cb.checked = isChecked;
                }
            });
            updateVehicleBulkToolbar();
        }

        const selectAll = document.getElementById('select-all-vehicles');
        if (selectAll) {
            selectAll.addEventListener('change', function() { handleSelectAll(this.checked); });
        }
        const tableHeadSelectAll = document.getElementById('table-head-select-all-vehicles');
        if (tableHeadSelectAll) {
            tableHeadSelectAll.addEventListener('change', function() { handleSelectAll(this.checked); });
        }

        // Delegate row checkbox change listener & card select bar tap
        const tableBody = document.querySelector('#vehicles-table tbody');
        if (tableBody) {
            tableBody.addEventListener('change', function(e) {
                if (e.target && e.target.classList.contains('vehicle-row-checkbox')) {
                    updateVehicleBulkToolbar();
                }
            });
            tableBody.addEventListener('click', function(e) {
                const cell = e.target.closest('.bulk-select-cell');
                if (cell && e.target.tagName !== 'INPUT') {
                    const cb = cell.querySelector('.vehicle-row-checkbox');
                    if (cb && !cb.disabled) {
                        cb.checked = !cb.checked;
                        cb.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
            });
        }
    });
</script>

<!-- Real-time Plate Number Validation -->
<style>
    .register-vehicle-card,
    .modern-card:has(.plate-col-wrapper) {
        position: relative !important;
        z-index: 10 !important;
        overflow: visible !important;
        margin-bottom: 2rem !important;
    }
    body.modal-open .register-vehicle-card,
    body.modal-open .modern-card:has(.plate-col-wrapper) {
        z-index: auto !important;
    }
    .plate-validation-icon {
        position: absolute;
        right: 38px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 16px;
        pointer-events: none;
        transition: opacity 0.2s ease;
    }
    .plate-col-wrapper {
        position: relative;
    }
    .plate-validation-feedback {
        position: relative !important;
        top: auto !important;
        left: auto !important;
        right: auto !important;
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin-top: 6px !important;
        margin-bottom: 4px !important;
        box-sizing: border-box !important;
        font-size: 11.5px;
        padding: 6px 12px;
        line-height: 1.35;
        transition: opacity 0.2s ease, transform 0.2s ease;
        font-weight: 500;
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-radius: 8px;
        border: 1px solid rgba(0, 0, 0, 0.08);
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.10);
        pointer-events: auto;
    }
    .plate-validation-feedback.feedback-error {
        background: rgba(255, 245, 245, 0.94);
        border-color: rgba(220, 53, 69, 0.25);
        color: #dc3545;
        box-shadow: 0 4px 14px rgba(220, 53, 69, 0.12);
    }
    .plate-validation-feedback.feedback-success {
        background: rgba(240, 255, 244, 0.94);
        border-color: rgba(25, 135, 84, 0.25);
        color: #198754;
        box-shadow: 0 4px 14px rgba(25, 135, 84, 0.12);
    }
    .plate-validation-feedback.feedback-checking {
        background: rgba(248, 249, 250, 0.94);
        border-color: rgba(108, 117, 125, 0.2);
        color: #6c757d;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
    }
    @media (max-width: 1199.98px) {
        .plate-validation-feedback {
            position: relative !important;
            top: auto !important;
            left: auto !important;
            right: auto !important;
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            margin-top: 6px !important;
            margin-bottom: 2px !important;
            box-sizing: border-box !important;
            pointer-events: auto !important;
        }
        .register-vehicle-card .row.align-items-end {
            align-items: flex-start !important;
        }
    }
    .form-control-modern.plate-valid {
        border-color: #198754 !important;
        box-shadow: 0 0 0 2px rgba(25, 135, 84, 0.15) !important;
    }
    .form-control-modern.plate-invalid {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.15) !important;
    }
    .input-modern.plate-valid {
        border-color: #198754 !important;
        box-shadow: 0 0 0 2px rgba(25, 135, 84, 0.15) !important;
    }
    .input-modern.plate-invalid {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.15) !important;
    }
    @keyframes spin-plate {
        to { transform: translateY(-50%) rotate(360deg); }
    }
    .plate-validation-icon.spinning {
        animation: spin-plate 0.8s linear infinite;
    }
</style>

<script>
(function() {
    var plateCheckTimer = null;
    var plateCheckXhr = null;
    var plateIsValid = true; // assume valid until proven otherwise
    var baseUrl = '<?= base_url('admin/vehicles/check-plate') ?>';
    var excludeId = 0; // no exclude for new vehicles

    window.validatePlateRealtime = function(value) {
        var plate = value.trim().toUpperCase();
        var input = document.getElementById('plate_number');
        var icon = document.getElementById('plate-validation-icon');
        var feedback = document.getElementById('plate-validation-feedback');

        // Clear previous timer
        if (plateCheckTimer) clearTimeout(plateCheckTimer);
        if (plateCheckXhr) { plateCheckXhr.abort(); plateCheckXhr = null; }

        // Reset states
        input.classList.remove('plate-valid', 'plate-invalid');
        icon.style.display = 'none';
        icon.className = 'plate-validation-icon';

        if (plate === '') {
            feedback.style.display = 'none';
            plateIsValid = true;
            return;
        }

        // Immediate client-side length check
        if (plate.length < 5) {
            feedback.style.display = 'block';
            feedback.className = 'plate-validation-feedback feedback-error';
            feedback.innerHTML = '<i class="bi bi-info-circle me-1"></i>Must be at least 5 characters';
            input.classList.add('plate-invalid');
            icon.style.display = 'inline-block';
            icon.innerHTML = '<i class="bi bi-exclamation-circle" style="color: #dc3545;"></i>';
            plateIsValid = false;
            return;
        }

        if (plate.length > 20) {
            feedback.style.display = 'block';
            feedback.className = 'plate-validation-feedback feedback-error';
            feedback.innerHTML = '<i class="bi bi-info-circle me-1"></i>Cannot exceed 20 characters';
            input.classList.add('plate-invalid');
            icon.style.display = 'inline-block';
            icon.innerHTML = '<i class="bi bi-exclamation-circle" style="color: #dc3545;"></i>';
            plateIsValid = false;
            return;
        }

        // Show checking state
        feedback.style.display = 'block';
        feedback.className = 'plate-validation-feedback feedback-checking';
        feedback.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Checking availability...';
        icon.style.display = 'inline-block';
        icon.innerHTML = '<i class="bi bi-arrow-repeat" style="color: #6c757d;"></i>';
        icon.classList.add('spinning');

        // Debounce the AJAX call (400ms)
        plateCheckTimer = setTimeout(function() {
            var url = baseUrl + '?plate=' + encodeURIComponent(plate);
            if (excludeId > 0) url += '&exclude_id=' + excludeId;

            plateCheckXhr = new XMLHttpRequest();
            plateCheckXhr.open('GET', url, true);
            plateCheckXhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            plateCheckXhr.onreadystatechange = function() {
                if (plateCheckXhr.readyState !== 4) return;
                icon.classList.remove('spinning');

                if (plateCheckXhr.status === 200) {
                    try {
                        var data = JSON.parse(plateCheckXhr.responseText);
                        if (data.available) {
                            // Available
                            feedback.className = 'plate-validation-feedback feedback-success';
                            feedback.innerHTML = '<i class="bi bi-check-circle me-1"></i>Plate number is available';
                            input.classList.remove('plate-invalid');
                            input.classList.add('plate-valid');
                            icon.innerHTML = '<i class="bi bi-check-circle-fill" style="color: #198754;"></i>';
                            plateIsValid = true;
                        } else {
                            // Duplicate or invalid
                            feedback.className = 'plate-validation-feedback feedback-error';
                            feedback.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>' + (data.message || 'Plate number is not available');
                            input.classList.remove('plate-valid');
                            input.classList.add('plate-invalid');
                            icon.innerHTML = '<i class="bi bi-exclamation-circle-fill" style="color: #dc3545;"></i>';
                            plateIsValid = false;
                        }
                    } catch(e) {
                        feedback.style.display = 'none';
                        plateIsValid = true; // fallback to server-side
                    }
                } else {
                    // Network error - fall back to server-side validation
                    feedback.style.display = 'none';
                    input.classList.remove('plate-valid', 'plate-invalid');
                    icon.style.display = 'none';
                    plateIsValid = true;
                }
            };
            plateCheckXhr.send();
        }, 400);
    };

    // Prevent form submission if plate is invalid
    document.addEventListener('DOMContentLoaded', function() {
        var form = document.getElementById('registerVehicleForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!plateIsValid) {
                    e.preventDefault();
                    var feedback = document.getElementById('plate-validation-feedback');
                    var input = document.getElementById('plate_number');
                    if (feedback) {
                        feedback.style.display = 'block';
                        feedback.className = 'plate-validation-feedback feedback-error';
                        if (input && input.value.trim().length < 5) {
                            feedback.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>Plate number must be at least 5 characters';
                        } else {
                            feedback.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>Please fix the plate number before submitting';
                        }
                    }
                    if (input) {
                        input.focus();
                        input.classList.add('plate-invalid');
                    }
                    return false;
                }
            });
        }

        // Trigger validation if there's already a value (e.g. from old() after failed submission)
        var plateInput = document.getElementById('plate_number');
        if (plateInput && plateInput.value.trim() !== '') {
            validatePlateRealtime(plateInput.value);
        }

        if (typeof window.initVehicleInputClearBtns === 'function') {
            window.initVehicleInputClearBtns(document);
        }
        var regModal = document.getElementById('registerVehicleModal');
        if (regModal) {
            regModal.addEventListener('shown.bs.modal', function() {
                if (typeof window.initVehicleInputClearBtns === 'function') {
                    window.initVehicleInputClearBtns(regModal);
                }
            });
        }
    });

    window.toggleFieldClearBtn = function(input, btnId) {
        var btn = document.getElementById(btnId);
        if (!btn) return;
        if (input && input.value && input.value.trim().length > 0) {
            btn.style.setProperty('display', 'inline-flex', 'important');
        } else {
            btn.style.setProperty('display', 'none', 'important');
        }
    };

    window.clearVehicleInput = function(inputId) {
        var input = document.getElementById(inputId);
        if (!input) return;
        input.value = '';
        var clearBtn = document.getElementById(inputId + '_clear');
        if (clearBtn) {
            clearBtn.style.setProperty('display', 'none', 'important');
        }
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
        if (inputId === 'plate_number' && typeof validatePlateRealtime === 'function') {
            validatePlateRealtime('');
        }
        input.focus();
    };

    window.initVehicleInputClearBtns = function(container) {
        var root = container || document;
        var fields = ['plate_number', 'operator_name', 'driver_name'];
        fields.forEach(function(id) {
            var input = root.querySelector ? root.querySelector('#' + id) : document.getElementById(id);
            if (input) {
                window.toggleFieldClearBtn(input, id + '_clear');
                if (!input.dataset.clearBtnBound) {
                    input.dataset.clearBtnBound = '1';
                    input.addEventListener('input', function() {
                        window.toggleFieldClearBtn(this, id + '_clear');
                    });
                    input.addEventListener('change', function() {
                        window.toggleFieldClearBtn(this, id + '_clear');
                    });
                }
            }
        });
    };
})();
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
                if (selectedType && opt.getAttribute('data-type') === selectedType) {
                    opt.style.display = '';
                    opt.disabled = false;
                } else {
                    opt.style.display = 'none';
                    opt.disabled = true;
                    if (opt.selected) opt.selected = false;
                }
            });
            // Update placeholder text based on whether a type is selected
            var placeholder = routeSelect.querySelector('option[value=""]');
            if (placeholder) {
                placeholder.textContent = selectedType ? '-- Select Destination --' : '-- Select Type First --';
            }
            routeSelect.dispatchEvent(new Event('change'));
        }

        filterRoutes();
        if (typeSelect) {
            typeSelect.addEventListener('change', function() {
                if (routeSelect) routeSelect.value = '';
                filterRoutes();
            });
        }

        // Initialize type fallback on load if a type was already selected (e.g. old() input)
        if (typeSelect && typeSelect.value) {
            updateRegTypeFallback(typeSelect);
        }

        <?php if ($isManageVehicleTypesOpen): ?>
        var mTypesEl = document.getElementById('manageVehicleTypesModal');
        if (mTypesEl) {
            var mTypesModal = bootstrap.Modal.getOrCreateInstance(mTypesEl);
            mTypesModal.show();
        }
        <?php elseif (session()->getFlashdata('errors') || session()->getFlashdata('error')): ?>
        var regModalEl = document.getElementById('registerVehicleModal');
        if (regModalEl) {
            var regModal = bootstrap.Modal.getOrCreateInstance(regModalEl);
            regModal.show();
        }
        <?php endif; ?>

        var _isSubmittingVehicleTypeForm = false;

        // When submitting forms in Add/Edit modals, prevent auto-reopen of Manage modal before page reload
        document.querySelectorAll('#addVehicleTypeModal form, [id^="editVehicleTypeModal"] form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                setTimeout(function() {
                    if (!e.defaultPrevented) {
                        _isSubmittingVehicleTypeForm = true;
                    }
                }, 0);
            });
        });

        // When Add or Edit modal is closed (via Cancel, X, backdrop, or ESC), transition back to Manage modal
        document.querySelectorAll('#addVehicleTypeModal, [id^="editVehicleTypeModal"]').forEach(function(subModal) {
            subModal.addEventListener('hidden.bs.modal', function() {
                if (_isSubmittingVehicleTypeForm) return;
                var manageEl = document.getElementById('manageVehicleTypesModal');
                if (manageEl) {
                    var manageModal = bootstrap.Modal.getOrCreateInstance(manageEl);
                    manageModal.show();
                }
            });
        });
    });

    function previewRegVehiclePhoto(input) {
        var preview = document.getElementById('reg_veh_photo_preview');
        var fallback = document.getElementById('reg_veh_type_fallback');
        var clearBtn = document.getElementById('reg_veh_photo_clear');
        if (input.files && input.files[0]) {
            var file = input.files[0];
            if (file.size > 5 * 1024 * 1024) {
                input.value = '';
                clearRegVehiclePhoto();
                if (typeof window.showSystemAlert === 'function') {
                    window.showSystemAlert({
                        title: 'File Too Large',
                        message: 'The selected vehicle photo is ' + (file.size / (1024 * 1024)).toFixed(1) + 'MB. The maximum allowed size is 5MB. Please choose a smaller image.',
                        variant: 'danger'
                    });
                }
                return;
            }
            var reader = new FileReader();
            reader.onload = function(e) {
                if (preview) {
                    preview.src = e.target.result;
                    preview.style.setProperty('display', 'block', 'important');
                }
                if (fallback) fallback.style.setProperty('display', 'none', 'important');
                if (clearBtn) clearBtn.classList.add('has-photo');
            };
            reader.readAsDataURL(file);
        }
    }

    function clearRegVehiclePhoto() {
        var input = document.getElementById('reg_veh_photo_input');
        var preview = document.getElementById('reg_veh_photo_preview');
        var fallback = document.getElementById('reg_veh_type_fallback');
        var clearBtn = document.getElementById('reg_veh_photo_clear');
        if (input) input.value = '';
        if (preview) {
            preview.src = '';
            preview.style.setProperty('display', 'none', 'important');
        }
        if (fallback) fallback.style.setProperty('display', 'flex', 'important');
        if (clearBtn) clearBtn.classList.remove('has-photo');
        var typeSelect = document.getElementById('type');
        if (typeSelect) updateRegTypeFallback(typeSelect);
    }

    function updateRegTypeFallback(select) {
        var preview = document.getElementById('reg_veh_photo_preview');
        if (preview && preview.style.display === 'block' && preview.src && preview.src.length > 0) return;

        var fallback = document.getElementById('reg_veh_type_fallback');
        if (!fallback) return;
        var opt = select.options[select.selectedIndex];
        if (!opt || !opt.value) {
            fallback.innerHTML = '<i class="fas fa-bus-simple text-muted" style="font-size: 26px;"></i>';
            return;
        }

        var photo = opt.getAttribute('data-photo');
        var icon = opt.getAttribute('data-icon') || 'fa-bus-simple';
        var color = opt.getAttribute('data-color') || '#475569';

        if (photo) {
            fallback.innerHTML = '<img src="' + photo + '" alt="Emblem" style="width:100%;height:100%;object-fit:cover;">';
        } else {
            fallback.innerHTML = '<i class="fas ' + icon + '" style="font-size:26px;color:' + color + ';"></i>';
        }
    }

    function openAddVehicleTypeModal() {
        var manageEl = document.getElementById('manageVehicleTypesModal');
        var addEl = document.getElementById('addVehicleTypeModal');
        if (!addEl) return;

        function showAdd() {
            var addModal = bootstrap.Modal.getOrCreateInstance(addEl);
            addModal.show();
        }

        if (manageEl && manageEl.classList.contains('show')) {
            var manageModal = bootstrap.Modal.getInstance(manageEl);
            if (manageModal) {
                manageEl.addEventListener('hidden.bs.modal', function onManageHidden() {
                    manageEl.removeEventListener('hidden.bs.modal', onManageHidden);
                    showAdd();
                }, { once: true });
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
            var editModal = bootstrap.Modal.getOrCreateInstance(editEl);
            editModal.show();
        }

        if (manageEl && manageEl.classList.contains('show')) {
            var manageModal = bootstrap.Modal.getInstance(manageEl);
            if (manageModal) {
                manageEl.addEventListener('hidden.bs.modal', function onManageHidden() {
                    manageEl.removeEventListener('hidden.bs.modal', onManageHidden);
                    showEdit();
                }, { once: true });
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
        if (currentEl) {
            var currentModal = bootstrap.Modal.getInstance(currentEl);
            if (currentModal) {
                currentModal.hide();
            }
        }
    }

    function closeAddVehicleTypeModal() {
        backToManageVehicleTypesModal('addVehicleTypeModal');
    }

    function getContrastTextColor(hexColor) {
        if (!hexColor) return '#ffffff';
        var hex = hexColor.replace('#', '').trim();
        if (hex.length === 3) {
            hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
        }
        if (hex.length < 6) return '#ffffff';
        var r = parseInt(hex.substr(0, 2), 16) || 0;
        var g = parseInt(hex.substr(2, 2), 16) || 0;
        var b = parseInt(hex.substr(4, 2), 16) || 0;
        var brightness = (r * 299 + g * 587 + b * 114) / 1000;
        return (brightness > 155) ? '#0f172a' : '#ffffff';
    }

    function applyIconButtonColor(btn, color) {
        if (!btn) return;
        var contrastColor = getContrastTextColor(color);
        btn.classList.add('active');
        btn.classList.remove('bg-primary', 'text-white', 'border-primary');
        btn.style.setProperty('background-color', color, 'important');
        btn.style.setProperty('border-color', color, 'important');
        btn.style.setProperty('color', contrastColor, 'important');
        btn.style.setProperty('box-shadow', '0 2px 6px ' + color + '55', 'important');
        var iconEl = btn.querySelector('i');
        if (iconEl) {
            iconEl.style.setProperty('color', contrastColor, 'important');
        }
        var textEl = btn.querySelector('span');
        if (textEl) {
            textEl.style.setProperty('color', contrastColor, 'important');
        }
    }

    function resetIconButton(btn) {
        if (!btn) return;
        btn.classList.remove('active', 'bg-primary', 'text-white', 'border-primary');
        btn.style.removeProperty('background-color');
        btn.style.removeProperty('border-color');
        btn.style.removeProperty('color');
        btn.style.removeProperty('box-shadow');
        var iconEl = btn.querySelector('i');
        if (iconEl) iconEl.style.removeProperty('color');
        var textEl = btn.querySelector('span');
        if (textEl) textEl.style.removeProperty('color');
    }

    function selectAddVtColor(color, btn) {
        document.getElementById('add_vt_color').value = color;
        var colorInput = document.getElementById('add_vt_color_input');
        if (colorInput && colorInput.value !== color) {
            colorInput.value = color;
        }
        var contrastColor = getContrastTextColor(color);

        var badge = document.getElementById('add_vt_color_badge');
        if (badge) {
            badge.textContent = color;
            badge.style.setProperty('background-color', color, 'important');
            badge.style.setProperty('color', contrastColor, 'important');
            badge.style.border = (contrastColor === '#0f172a') ? '1px solid rgba(0,0,0,0.2)' : '1px solid rgba(255,255,255,0.25)';
        }

        document.querySelectorAll('.add-color-swatch').forEach(function(el) {
            el.classList.remove('active');
            var ic = el.querySelector('i');
            if (ic) ic.classList.add('d-none');
        });
        var customBadge = document.getElementById('add_vt_custom_label');
        if (btn) {
            btn.classList.add('active');
            var ic = btn.querySelector('i');
            if (ic) {
                ic.classList.remove('d-none');
                ic.style.color = contrastColor;
            }
            if (customBadge) customBadge.classList.remove('active');
        } else {
            if (customBadge) customBadge.classList.add('active');
        }

        // Selected icon button follows the newly assigned color
        var activeIconBtn = document.querySelector('.add-icon-btn.active');
        if (activeIconBtn) {
            applyIconButtonColor(activeIconBtn, color);
        }

        updateAddVtPreview();
    }

    function selectAddVtIcon(icon, btn) {
        document.getElementById('add_vt_icon').value = icon;
        var currentColor = (document.getElementById('add_vt_color') && document.getElementById('add_vt_color').value) ? document.getElementById('add_vt_color').value : '#1565c0';

        document.querySelectorAll('.add-icon-btn').forEach(function(el) {
            resetIconButton(el);
        });
        if (btn) {
            applyIconButtonColor(btn, currentColor);
        }
        updateAddVtPreview();
    }

    function updateAddVtPreview() {
        var nameInput = document.getElementById('vehicle_type_name');
        var name = (nameInput && nameInput.value.trim()) ? nameInput.value.trim() : 'New Vehicle';
        var color = (document.getElementById('add_vt_color') && document.getElementById('add_vt_color').value) ? document.getElementById('add_vt_color').value : '#1565c0';
        var icon = (document.getElementById('add_vt_icon') && document.getElementById('add_vt_icon').value) ? document.getElementById('add_vt_icon').value : 'fa-bus-simple';
        var contrastColor = getContrastTextColor(color);
        var isLightColor = (contrastColor === '#0f172a');

        var card = document.getElementById('add_vt_preview_card');
        var title = document.getElementById('add_vt_preview_title');
        var iconEl = document.getElementById('add_vt_preview_icon');
        var badge = document.getElementById('add_vt_preview_badge');

        var cardTopBorder = isLightColor ? '#94a3b8' : color;
        var titleColor = isLightColor ? '#0f172a' : color;

        if (card) card.style.borderTop = '3.5px solid ' + cardTopBorder;
        if (title) { title.style.color = titleColor; title.textContent = name + ' Routes'; }
        if (iconEl) {
            iconEl.style.setProperty('background-color', color, 'important');
            iconEl.style.setProperty('color', contrastColor, 'important');
            iconEl.style.border = isLightColor ? '1.5px solid #cbd5e1' : 'none';
            var iconI = document.getElementById('add_vt_preview_icon_i');
            if (iconI) {
                var cleanIcon = icon.trim();
                if (!cleanIcon.startsWith('fa-') && !cleanIcon.startsWith('fas ') && !cleanIcon.startsWith('bi-')) {
                    cleanIcon = 'fa-' + cleanIcon;
                }
                iconI.className = cleanIcon.indexOf(' ') !== -1 ? cleanIcon : ('fas ' + cleanIcon);
                iconI.style.setProperty('color', contrastColor, 'important');
            }
        }
        if (badge) {
            badge.style.backgroundColor = isLightColor ? '#f1f5f9' : (color + '18');
            badge.style.color = isLightColor ? '#0f172a' : color;
            badge.style.borderColor = isLightColor ? '#cbd5e1' : (color + '40');
            badge.textContent = name;
        }
    }

    function selectEditVtColor(id, color, btn) {
        document.getElementById('edit_vt_color_' + id).value = color;
        var colorInput = document.getElementById('edit_vt_color_input_' + id);
        if (colorInput && colorInput.value !== color) {
            colorInput.value = color;
        }
        var contrastColor = getContrastTextColor(color);

        var badge = document.getElementById('edit_vt_color_badge_' + id);
        if (badge) {
            badge.textContent = color;
            badge.style.setProperty('background-color', color, 'important');
            badge.style.setProperty('color', contrastColor, 'important');
            badge.style.border = (contrastColor === '#0f172a') ? '1px solid rgba(0,0,0,0.2)' : '1px solid rgba(255,255,255,0.25)';
        }

        document.querySelectorAll('.edit-color-swatch-' + id).forEach(function(el) {
            el.classList.remove('active');
            var ic = el.querySelector('i');
            if (ic) ic.classList.add('d-none');
        });
        var customBadge = document.getElementById('edit_vt_custom_label_' + id);
        if (btn) {
            btn.classList.add('active');
            var ic = btn.querySelector('i');
            if (ic) {
                ic.classList.remove('d-none');
                ic.style.color = contrastColor;
            }
            if (customBadge) customBadge.classList.remove('active');
        } else {
            if (customBadge) customBadge.classList.add('active');
        }

        // Selected icon button follows the newly assigned color
        var activeIconBtn = document.querySelector('.edit-icon-btn-' + id + '.active');
        if (activeIconBtn) {
            applyIconButtonColor(activeIconBtn, color);
        }

        updateEditVtPreview(id);
    }

    function selectEditVtIcon(id, icon, btn) {
        document.getElementById('edit_vt_icon_' + id).value = icon;
        var currentColor = (document.getElementById('edit_vt_color_' + id) && document.getElementById('edit_vt_color_' + id).value) ? document.getElementById('edit_vt_color_' + id).value : '#1565c0';

        document.querySelectorAll('.edit-icon-btn-' + id).forEach(function(el) {
            resetIconButton(el);
        });
        if (btn) {
            applyIconButtonColor(btn, currentColor);
        }
        updateEditVtPreview(id);
    }

    function updateEditVtPreview(id) {
        var nameInput = document.getElementById('edit_vehicle_type_name_' + id);
        var name = (nameInput && nameInput.value.trim()) ? nameInput.value.trim() : 'Vehicle';
        var color = document.getElementById('edit_vt_color_' + id).value;
        var icon = document.getElementById('edit_vt_icon_' + id).value;
        var contrastColor = getContrastTextColor(color);
        var isLightColor = (contrastColor === '#0f172a');

        var card = document.getElementById('edit_vt_preview_card_' + id);
        var title = document.getElementById('edit_vt_preview_title_' + id);
        var iconEl = document.getElementById('edit_vt_preview_icon_' + id);
        var badge = document.getElementById('edit_vt_preview_badge_' + id);

        var cardTopBorder = isLightColor ? '#94a3b8' : color;
        var titleColor = isLightColor ? '#0f172a' : color;

        if (card) card.style.borderTop = '3.5px solid ' + cardTopBorder;
        if (title) { title.style.color = titleColor; title.textContent = name + ' Routes'; }
        if (iconEl) {
            iconEl.style.setProperty('background-color', color, 'important');
            iconEl.style.setProperty('color', contrastColor, 'important');
            iconEl.style.border = isLightColor ? '1.5px solid #cbd5e1' : 'none';
            var iconI = document.getElementById('edit_vt_preview_icon_i_' + id);
            if (iconI) {
                var cleanIcon = icon.trim();
                if (!cleanIcon.startsWith('fa-') && !cleanIcon.startsWith('fas ') && !cleanIcon.startsWith('bi-')) {
                    cleanIcon = 'fa-' + cleanIcon;
                }
                iconI.className = cleanIcon.indexOf(' ') !== -1 ? cleanIcon : ('fas ' + cleanIcon);
                iconI.style.setProperty('color', contrastColor, 'important');
            }
        }
        if (badge) {
            badge.style.backgroundColor = isLightColor ? '#f1f5f9' : (color + '18');
            badge.style.color = isLightColor ? '#0f172a' : color;
            badge.style.borderColor = isLightColor ? '#cbd5e1' : (color + '40');
            badge.textContent = name;
        }
    }

    function initAddVehicleTypeModal() {
        var color = (document.getElementById('add_vt_color') && document.getElementById('add_vt_color').value) ? document.getElementById('add_vt_color').value : '#1565c0';
        var activeBtn = document.querySelector('.add-icon-btn.active');
        if (activeBtn) {
            applyIconButtonColor(activeBtn, color);
        }
        var badge = document.getElementById('add_vt_color_badge');
        if (badge) {
            var contrastColor = getContrastTextColor(color);
            badge.style.setProperty('background-color', color, 'important');
            badge.style.setProperty('color', contrastColor, 'important');
            badge.style.border = (contrastColor === '#0f172a') ? '1px solid rgba(0,0,0,0.2)' : '1px solid rgba(255,255,255,0.25)';
        }
        clearAddVtPhoto();
        updateAddVtPreview();
    }

    function initEditVehicleTypeModal(id) {
        var colorInput = document.getElementById('edit_vt_color_' + id);
        var color = colorInput ? colorInput.value : '#1565c0';
        var activeBtn = document.querySelector('.edit-icon-btn-' + id + '.active');
        if (activeBtn) {
            applyIconButtonColor(activeBtn, color);
        }
        var badge = document.getElementById('edit_vt_color_badge_' + id);
        if (badge) {
            var contrastColor = getContrastTextColor(color);
            badge.style.setProperty('background-color', color, 'important');
            badge.style.setProperty('color', contrastColor, 'important');
            badge.style.border = (contrastColor === '#0f172a') ? '1px solid rgba(0,0,0,0.2)' : '1px solid rgba(255,255,255,0.25)';
        }
        updateEditVtPreview(id);
        var preview = document.getElementById('edit_vt_photo_preview_' + id);
        var clearBtn = document.getElementById('edit_vt_photo_clear_' + id);
        if (clearBtn) {
            if (preview && preview.getAttribute('src') && preview.getAttribute('src').trim() !== '') {
                clearBtn.classList.add('has-photo');
            } else {
                clearBtn.classList.remove('has-photo');
            }
        }
    }

    // Photo preview helper functions
    window.previewAddVtPhoto = function(input) {
        var preview = document.getElementById('add_vt_photo_preview');
        var placeholder = document.getElementById('add_vt_photo_placeholder');
        var clearBtn = document.getElementById('add_vt_photo_clear');
        var previewPhoto = document.getElementById('add_vt_preview_photo');
        var previewIcon = document.getElementById('add_vt_preview_icon_i');

        if (input.files && input.files[0]) {
            var file = input.files[0];
            if (file.size > 2 * 1024 * 1024) {
                input.value = '';
                clearAddVtPhoto();
                if (typeof window.showSystemAlert === 'function') {
                    window.showSystemAlert({
                        title: 'File Too Large',
                        message: 'The selected photo is ' + (file.size / (1024 * 1024)).toFixed(1) + 'MB. The maximum allowed size for a vehicle type emblem is 2MB. Please choose a smaller image.',
                        variant: 'danger'
                    });
                }
                return;
            }

            var reader = new FileReader();
            reader.onload = function(e) {
                if (preview) { preview.src = e.target.result; preview.style.display = 'block'; }
                if (placeholder) placeholder.style.display = 'none';
                if (clearBtn) clearBtn.classList.add('has-photo');
                if (previewPhoto) { previewPhoto.src = e.target.result; previewPhoto.style.display = 'block'; }
                if (previewIcon) previewIcon.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
    };

    window.clearAddVtPhoto = function() {
        var input = document.getElementById('add_vt_photo_input');
        var preview = document.getElementById('add_vt_photo_preview');
        var placeholder = document.getElementById('add_vt_photo_placeholder');
        var clearBtn = document.getElementById('add_vt_photo_clear');
        var previewPhoto = document.getElementById('add_vt_preview_photo');
        var previewIcon = document.getElementById('add_vt_preview_icon_i');

        if (input) input.value = '';
        if (preview) { preview.src = ''; preview.style.display = 'none'; }
        if (placeholder) placeholder.style.display = 'block';
        if (clearBtn) clearBtn.classList.remove('has-photo');
        if (previewPhoto) { previewPhoto.src = ''; previewPhoto.style.display = 'none'; }
        if (previewIcon) previewIcon.style.display = 'inline-block';
    };

    window.previewEditVtPhoto = function(id, input) {
        var preview = document.getElementById('edit_vt_photo_preview_' + id);
        var placeholder = document.getElementById('edit_vt_photo_placeholder_' + id);
        var previewPhoto = document.getElementById('edit_vt_preview_photo_' + id);
        var previewIcon = document.getElementById('edit_vt_preview_icon_i_' + id);
        var removeCheck = document.getElementById('remove_photo_' + id);
        var clearBtn = document.getElementById('edit_vt_photo_clear_' + id);

        if (removeCheck) removeCheck.checked = false;

        if (input.files && input.files[0]) {
            var file = input.files[0];
            if (file.size > 2 * 1024 * 1024) {
                input.value = '';
                if (typeof window.showSystemAlert === 'function') {
                    window.showSystemAlert({
                        title: 'File Too Large',
                        message: 'The selected photo is ' + (file.size / (1024 * 1024)).toFixed(1) + 'MB. The maximum allowed size for a vehicle type emblem is 2MB. Please choose a smaller image.',
                        variant: 'danger'
                    });
                }
                return;
            }

            var reader = new FileReader();
            reader.onload = function(e) {
                if (preview) { preview.src = e.target.result; preview.style.display = 'block'; }
                if (placeholder) placeholder.style.display = 'none';
                if (previewPhoto) { previewPhoto.src = e.target.result; previewPhoto.style.display = 'block'; }
                if (previewIcon) previewIcon.style.display = 'none';
                if (clearBtn) clearBtn.classList.add('has-photo');
            };
            reader.readAsDataURL(file);
        }
    };

    window.clearEditVtPhoto = function(id) {
        var input = document.getElementById('edit_vt_photo_input_' + id);
        var preview = document.getElementById('edit_vt_photo_preview_' + id);
        var placeholder = document.getElementById('edit_vt_photo_placeholder_' + id);
        var previewPhoto = document.getElementById('edit_vt_preview_photo_' + id);
        var previewIcon = document.getElementById('edit_vt_preview_icon_i_' + id);
        var removeCheck = document.getElementById('remove_photo_' + id);
        var clearBtn = document.getElementById('edit_vt_photo_clear_' + id);

        if (input) input.value = '';
        if (preview) { preview.src = ''; preview.style.display = 'none'; }
        if (placeholder) placeholder.style.display = 'block';
        if (previewPhoto) { previewPhoto.src = ''; previewPhoto.style.display = 'none'; }
        if (previewIcon) previewIcon.style.display = 'inline-block';
        if (removeCheck) removeCheck.checked = true;
        if (clearBtn) clearBtn.classList.remove('has-photo');
    };

    window.toggleRemoveEditPhoto = function(id, isChecked) {
        var input = document.getElementById('edit_vt_photo_input_' + id);
        var preview = document.getElementById('edit_vt_photo_preview_' + id);
        var placeholder = document.getElementById('edit_vt_photo_placeholder_' + id);
        var previewPhoto = document.getElementById('edit_vt_preview_photo_' + id);
        var previewIcon = document.getElementById('edit_vt_preview_icon_i_' + id);
        var clearBtn = document.getElementById('edit_vt_photo_clear_' + id);

        if (isChecked) {
            if (input) input.value = '';
            if (preview) preview.style.display = 'none';
            if (placeholder) placeholder.style.display = 'block';
            if (previewPhoto) previewPhoto.style.display = 'none';
            if (previewIcon) previewIcon.style.display = 'inline-block';
            if (clearBtn) clearBtn.classList.remove('has-photo');
        } else {
            if (preview && preview.getAttribute('src')) {
                preview.style.display = 'block';
                if (placeholder) placeholder.style.display = 'none';
                if (previewPhoto) previewPhoto.style.display = 'block';
                if (previewIcon) previewIcon.style.display = 'none';
                if (clearBtn) clearBtn.classList.add('has-photo');
            }
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        var addModalEl = document.getElementById('addVehicleTypeModal');
        if (addModalEl) {
            addModalEl.addEventListener('show.bs.modal', function() {
                initAddVehicleTypeModal();
            });
        }
        document.querySelectorAll('[id^="editVehicleTypeModal"]').forEach(function(modalEl) {
            modalEl.addEventListener('show.bs.modal', function() {
                var id = this.id.replace('editVehicleTypeModal', '');
                if (id) {
                    initEditVehicleTypeModal(id);
                }
            });
        });
    });
</script>

<?php
$transitIcons = [
    ['icon' => 'fa-van-shuttle', 'label' => 'Van / Shuttle'],
    ['icon' => 'fa-truck-front', 'label' => 'Jeepney'],
    ['icon' => 'fa-bus', 'label' => 'Minibus'],
    ['icon' => 'fa-bus-simple', 'label' => 'Bus / Coach'],
    ['icon' => 'fa-motorcycle', 'label' => 'Tricycle'],
    ['icon' => 'fa-motorcycle', 'label' => 'Motorcycle'],
    ['icon' => 'fa-car', 'label' => 'Car / Sedan'],
    ['icon' => 'fa-taxi', 'label' => 'Taxi / Cab'],
];

$colorPalette = [
    '#c62828', // Red
    '#1565c0', // Blue
    '#2e7d32', // Green
    '#ea580c', // Orange
    '#7c3aed', // Purple
    '#0891b2', // Cyan
    '#e11d48', // Rose
    '#ca8a04', // Gold
    '#059669', // Emerald
    '#4f46e5', // Indigo
    '#475569', // Slate
    '#db2777', // Magenta
];

$usedColors = array_filter(array_column($vehicleTypes ?? [], 'color'));
$availableColors = array_values(array_diff($colorPalette, $usedColors));
$suggestedColor = !empty($availableColors) ? $availableColors[0] : '#ea580c';
?>

<style>
    /* Keep vehicle registration modal dropdowns fully visible and unclipped */
    #registerVehicleModal.modal {
        overflow-x: hidden !important;
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch;
    }

    #registerVehicleModal .modal-dialog {
        max-height: none !important;
        height: auto !important;
        min-height: auto !important;
        display: flex !important;
        align-items: center !important;
        overflow: visible !important;
    }

    #registerVehicleModal .modal-content {
        border-radius: 16px;
        max-height: none !important;
        height: auto !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: visible !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.18) !important;
    }

    #registerVehicleModal form {
        display: flex !important;
        flex-direction: column !important;
        height: auto !important;
        min-height: auto !important;
        max-height: none !important;
        overflow: visible !important;
        width: 100% !important;
        flex: 1 1 auto !important;
    }

    #registerVehicleModal .modal-body,
    #registerVehicleModal .row,
    #registerVehicleModal [class*="col-"] {
        overflow: visible !important;
    }

    #registerVehicleModal .modal-body {
        height: auto !important;
        max-height: none !important;
        flex: none !important;
    }

    #registerVehicleModal .modal-header {
        border-top-left-radius: 16px;
        border-top-right-radius: 16px;
    }

    #registerVehicleModal .modal-footer {
        position: relative;
        z-index: 1 !important;
        border-bottom-left-radius: 16px;
        border-bottom-right-radius: 16px;
        margin-top: auto !important;
        flex-shrink: 0 !important;
    }

    #registerVehicleModal .autocomplete-wrapper.is-open {
        position: relative !important;
        z-index: 1065 !important;
    }

    #registerVehicleModal .autocomplete-dropdown {
        z-index: 1070 !important;
    }

    /* Mobile responsiveness enhancements for vehicle modals */
    @media (max-width: 576px) {
        #registerVehicleModal .modal-dialog,
        #manageVehicleTypesModal .modal-dialog,
        #addVehicleTypeModal .modal-dialog,
        [id^="editVehicleTypeModal"] .modal-dialog {
            margin: 0.5rem auto !important;
            width: calc(100% - 1rem) !important;
            max-width: calc(100% - 1rem) !important;
        }

        #registerVehicleModal .modal-dialog {
            max-height: none !important;
            height: auto !important;
            min-height: auto !important;
        }

        #registerVehicleModal .modal-content {
            max-height: none !important;
            height: auto !important;
            overflow: visible !important;
        }

        #registerVehicleModal form {
            height: auto !important;
            max-height: none !important;
            overflow: visible !important;
        }

        #registerVehicleModal .modal-body {
            padding: 0.875rem !important;
            height: auto !important;
            max-height: none !important;
            overflow: visible !important;
        }

        #registerVehicleModal .modal-footer {
            padding: 0.75rem 0.875rem !important;
            margin-top: 0.5rem !important;
        }

        #manageVehicleTypesModal .list-group-item {
            padding: 0.625rem 0.625rem !important;
        }

        #manageVehicleTypesModal .modal-header,
        #manageVehicleTypesModal .modal-footer,
        #addVehicleTypeModal .modal-header,
        #addVehicleTypeModal .modal-footer,
        [id^="editVehicleTypeModal"] .modal-header,
        [id^="editVehicleTypeModal"] .modal-footer {
            padding: 0.625rem 0.75rem !important;
        }

        .btn-action-edit,
        .btn-action-delete {
            min-width: 32px !important;
            height: 30px !important;
            padding: 0 8px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
    }
</style>

<!-- Register New Vehicle Modal -->
<div class="modal fade" id="registerVehicleModal" tabindex="-1" aria-labelledby="registerVehicleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mx-auto my-2" style="max-width: 680px; width: calc(100% - 1.5rem);">
        <div class="modal-content border-0 shadow" style="border-radius: 16px;">
            <div class="modal-header py-2 px-3 bg-white flex-shrink-0" style="border-bottom: 1px solid var(--border, #e2e8f0); border-top-left-radius: 16px; border-top-right-radius: 16px;">
                <h5 class="modal-title fw-bold fs-6 mb-0 text-dark" id="registerVehicleModalLabel">
                    <i class="bi bi-plus-circle me-2" style="color: var(--primary-red);"></i>Register New Vehicle
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('admin/vehicles/store') ?>" method="post" enctype="multipart/form-data" id="registerVehicleForm">
                <?= csrf_field() ?>
                <div class="modal-body py-3 px-3 px-sm-4">
                    
                    <!-- Vehicle Photo Upload Section -->
                    <div class="mb-3 p-3 rounded-3" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
                        <label class="form-label-modern d-flex justify-content-between align-items-center mb-2">
                            <span><i class="bi bi-camera me-1"></i> Vehicle Photo / Icon <small class="text-muted fw-normal">(Optional)</small></span>
                            <button type="button" id="reg_veh_photo_clear" class="veh-photo-clear-btn" onclick="clearRegVehiclePhoto()">
                                <i class="fas fa-times me-1"></i> Clear Photo
                            </button>
                        </label>
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 border flex-shrink-0 shadow-sm position-relative" style="width: 68px; height: 68px; overflow: hidden; border-color: #cbd5e1 !important; background: #ffffff;">
                                <img id="reg_veh_photo_preview" src="" alt="Vehicle Photo" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; z-index: 2; display: none !important;">
                                <div id="reg_veh_type_fallback" class="veh-fallback-box" style="position: absolute; inset: 0; width: 100%; height: 100%; display: flex !important; align-items: center; justify-content: center; background: #f1f5f9; z-index: 1;">
                                    <i id="reg_veh_type_icon" class="fas fa-bus-simple text-muted" style="font-size: 26px;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <input type="file" id="reg_veh_photo_input" name="photo" accept="image/png,image/jpeg,image/jpg,image/webp" class="form-control form-control-sm" style="height: 38px; border-radius: 8px; font-size: 13px;" onchange="previewRegVehiclePhoto(this)">
                                <div class="form-text mt-1 text-muted" style="font-size: 11.5px;">
                                    Supports JPG, PNG, WEBP &bull; Max 5MB &bull; Falls back to vehicle type emblem if empty.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 1: Plate Number, Operator Name, Driver Name -->
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-4 plate-col-wrapper">
                            <label for="plate_number" class="form-label-modern">Plate Number <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" class="form-control-modern text-uppercase" id="plate_number" name="plate_number"
                                    placeholder="E.G. ABC-1234" value="<?= old('plate_number') ?>" style="text-transform: uppercase; padding-right: 58px;"
                                    oninput="this.value = this.value.toUpperCase(); validatePlateRealtime(this.value); toggleFieldClearBtn(this, 'plate_number_clear');" required>
                                <button type="button" class="input-clear-btn" id="plate_number_clear" onclick="clearVehicleInput('plate_number')" title="Clear" aria-label="Clear plate number">
                                    <i class="fas fa-times"></i>
                                </button>
                                <span id="plate-validation-icon" class="plate-validation-icon" style="display: none;"></span>
                            </div>
                            <div id="plate-validation-feedback" class="plate-validation-feedback" style="display: none;"></div>
                            <div class="form-text-modern" style="font-size:11px;">Unique identifier</div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="operator_name" class="form-label-modern">Operator Name <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" class="form-control-modern text-uppercase" id="operator_name" name="operator_name"
                                    placeholder="E.G. LETRANSCO" value="<?= old('operator_name') ?>" style="text-transform: uppercase; padding-right: 36px;"
                                    oninput="this.value = this.value.toUpperCase(); toggleFieldClearBtn(this, 'operator_name_clear');" required>
                                <button type="button" class="input-clear-btn" id="operator_name_clear" onclick="clearVehicleInput('operator_name')" title="Clear" aria-label="Clear operator name">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="form-text-modern" style="font-size:11px;">Operator's full name</div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="driver_name" class="form-label-modern">Driver Name <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" class="form-control-modern" id="driver_name" name="driver_name"
                                    placeholder="e.g. Pedro Santos" value="<?= old('driver_name') ?>" style="padding-right: 36px;"
                                    oninput="toggleFieldClearBtn(this, 'driver_name_clear');" required>
                                <button type="button" class="input-clear-btn" id="driver_name_clear" onclick="clearVehicleInput('driver_name')" title="Clear" aria-label="Clear driver name">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="form-text-modern" style="font-size:11px;">Assigned driver</div>
                        </div>
                    </div>

                    <!-- Row 2: Type, Destination, Capacity -->
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label for="type" class="form-label-modern">Vehicle Type <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <select class="form-select-modern pe-5" id="type" name="type" required onchange="updateRegTypeFallback(this)">
                                    <option value="">-- Select Type --</option>
                                    <?php foreach (($vehicleTypes ?? []) as $vehicleType): ?>
                                        <?php 
                                            $optCol = !empty($vehicleType['color']) ? $vehicleType['color'] : vehicle_type_color($vehicleType['slug']); 
                                            $optPhoto = !empty($vehicleType['photo']) ? base_url($vehicleType['photo']) : '';
                                            $optIcon = !empty($vehicleType['icon']) ? $vehicleType['icon'] : vehicle_type_icon($vehicleType['slug']);
                                        ?>
                                        <option value="<?= esc($vehicleType['slug']) ?>" 
                                                data-color="<?= esc($optCol) ?>" 
                                                data-photo="<?= esc($optPhoto) ?>" 
                                                data-icon="<?= esc($optIcon) ?>"
                                                <?= old('type') === $vehicleType['slug'] ? 'selected' : '' ?>><?= esc($vehicleType['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-text-modern" style="font-size:11px;">Classification</div>
                        </div>
                        <div class="col-12 col-md-5">
                            <label for="route_id" class="form-label-modern">Destination <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <select class="form-select-modern pe-5" id="route_id" name="route_id" required>
                                    <option value="">-- Select Type First --</option>
                                    <?php if (!empty($routes)): ?>
                                        <?php foreach ($routes as $r): ?>
                                            <option value="<?= $r['id'] ?>" data-type="<?= esc($r['vehicle_type']) ?>"
                                                <?= old('route_id') == $r['id'] ? 'selected' : '' ?>>
                                                <?= strtoupper(esc($r['origin'])) ?> → <?= strtoupper(esc($r['destination'])) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="form-text-modern" style="font-size:11px;">Filtered by vehicle type</div>
                        </div>
                        <div class="col-12 col-md-3">
                            <label for="capacity" class="form-label-modern">Capacity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control-modern" id="capacity" name="capacity" placeholder="16"
                                value="<?= old('capacity') ?>" min="1" required>
                            <div class="form-text-modern" style="font-size:11px;">Max passengers</div>
                        </div>
                    </div>

                    <input type="hidden" name="status" value="active">
                </div>
                <div class="modal-footer py-2 px-3 bg-white d-flex align-items-center justify-content-between flex-nowrap w-100" style="border-top: 1px solid var(--border, #e2e8f0); border-bottom-left-radius: 16px; border-bottom-right-radius: 16px; position: relative; z-index: 1;">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-modern btn-modern-primary px-3" style="min-height: 38px;">
                        <i class="bi bi-plus-lg me-1"></i> Register Vehicle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="manageVehicleTypesModal" tabindex="-1" aria-labelledby="manageVehicleTypesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable mx-auto my-2 my-sm-4" style="max-width: 550px; width: calc(100% - 1.5rem); max-height: calc(100vh - 2rem);">
        <div class="modal-content border-0 shadow" style="max-height: calc(100vh - 2rem); display: flex; flex-direction: column; overflow: hidden; border-radius: 16px;">
            <div class="modal-header py-2 px-3 bg-white flex-shrink-0" style="border-bottom: 1px solid var(--border, #e2e8f0); z-index: 10;">
                <h5 class="modal-title fw-bold fs-6 mb-0" id="manageVehicleTypesModalLabel"><i class="bi bi-gear-fill me-2"></i>Manage Vehicle Types</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <?php if ($isManageVehicleTypesOpen): ?>
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success m-2 py-2 px-3 d-flex align-items-center gap-2" style="font-size: 13px; border-radius: 8px;">
                        <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                        <div><?= esc(session()->getFlashdata('success')) ?></div>
                    </div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger m-2 py-2 px-3 d-flex align-items-center gap-2" style="font-size: 13px; border-radius: 8px;">
                        <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                        <div><?= esc(session()->getFlashdata('error')) ?></div>
                    </div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="alert alert-danger m-2 py-2 px-3" style="font-size: 13px; border-radius: 8px;">
                        <ul class="mb-0 ps-3">
                            <?php foreach (session()->getFlashdata('errors') as $err): ?>
                                <li><?= esc($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('info')): ?>
                    <div class="alert alert-info m-2 py-2 px-3 d-flex align-items-center gap-2" style="font-size: 13px; border-radius: 8px;">
                        <i class="bi bi-info-circle-fill flex-shrink-0"></i>
                        <div><?= esc(session()->getFlashdata('info')) ?></div>
                    </div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('warning')): ?>
                    <div class="alert alert-warning m-2 py-2 px-3 d-flex align-items-center gap-2" style="font-size: 13px; border-radius: 8px;">
                        <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                        <div><?= esc(session()->getFlashdata('warning')) ?></div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            <div class="modal-body p-0 flex-grow-1" style="overflow-y: auto !important; -webkit-overflow-scrolling: touch; min-height: 0; flex: 1 1 auto;">
                <?php if (!empty($vehicleTypes)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($vehicleTypes as $vt): ?>
                            <?php 
                                $vtColor = !empty($vt['color']) ? $vt['color'] : vehicle_type_color($vt['slug']);
                                $vtIcon = !empty($vt['icon']) ? $vt['icon'] : vehicle_type_icon($vt['slug']);
                                $vtContrast = contrast_text_color($vtColor);
                                $vtIsLight = ($vtContrast === '#0f172a');
                            ?>
                            <div class="list-group-item d-flex align-items-center justify-content-between py-2 px-2 px-sm-3 no-stack flex-nowrap gap-2">
                                <div class="d-flex align-items-center gap-2 gap-sm-3 flex-grow-1" style="min-width: 0;">
                                    <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 36px; height: 36px; background-color: <?= esc($vtColor) ?>; color: <?= esc($vtContrast) ?>; <?= $vtIsLight ? 'border: 1.5px solid #cbd5e1;' : '' ?> overflow: hidden;">
                                        <?php if (!empty($vt['photo'])): ?>
                                            <img src="<?= base_url(esc($vt['photo'])) ?>" alt="<?= esc($vt['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                        <?php else: ?>
                                            <i class="fas <?= esc($vtIcon) ?>" style="font-size: 15px; color: <?= esc($vtContrast) ?> !important;"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div style="min-width: 0; flex: 1;">
                                        <div class="fw-bold text-dark d-flex align-items-center gap-1 gap-sm-2" style="font-size: 13.5px; line-height: 1.3;">
                                            <span class="text-truncate" style="max-width: 170px;"><?= esc($vt['name']) ?></span>
                                            <span class="badge rounded-pill flex-shrink-0 d-none d-sm-inline-block" style="background-color: <?= $vtIsLight ? '#f1f5f9' : (esc($vtColor) . '18') ?>; color: <?= $vtIsLight ? '#0f172a' : esc($vtColor) ?>; border: 1px solid <?= $vtIsLight ? '#cbd5e1' : (esc($vtColor) . '40') ?>; font-size: 11px;">
                                                <?= esc($vtColor) ?>
                                            </span>
                                            <span class="rounded-circle d-inline-block d-sm-none flex-shrink-0" style="width: 8px; height: 8px; background-color: <?= esc($vtColor) ?>; border: 1px solid rgba(0,0,0,0.15);" title="<?= esc($vtColor) ?>"></span>
                                        </div>
                                        <small class="text-muted d-block text-truncate" style="font-size: 11px;">Slug: <code><?= esc($vt['slug']) ?></code> &bull; <?= $typeCounts[$vt['slug']] ?? 0 ?> <span class="d-none d-sm-inline">vehicle(s)</span><span class="d-sm-none">veh</span></small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-1 gap-sm-2 flex-shrink-0 ms-auto">
                                    <button type="button" class="btn btn-sm btn-action-edit d-inline-flex align-items-center justify-content-center" style="padding: 4px 8px; font-size: 12px; border-radius: 6px; white-space: nowrap;" onclick="openEditVehicleTypeModal(<?= $vt['id'] ?>)" title="Edit Vehicle Type">
                                        <i class="bi bi-pencil"></i><span class="d-none d-sm-inline ms-1">Edit</span>
                                    </button>
                                    <form id="delete-vehicle-type-form-<?= $vt['id'] ?>" action="<?= base_url('admin/vehicle-types/delete/' . $vt['id']) ?>" method="post" class="d-inline m-0 p-0">
                                        <?= csrf_field() ?>
                                        <button type="button" class="btn btn-sm btn-action-delete d-inline-flex align-items-center justify-content-center" style="padding: 4px 8px; font-size: 12px; border-radius: 6px; white-space: nowrap;" title="Delete Vehicle Type"
                                            onclick="showDeleteVehicleTypeModal({
                                                formId: 'delete-vehicle-type-form-<?= $vt['id'] ?>',
                                                id: '<?= $vt['id'] ?>',
                                                name: '<?= esc(addslashes($vt['name'])) ?>',
                                                slug: '<?= esc(addslashes($vt['slug'])) ?>',
                                                color: '<?= esc(addslashes($vtColor)) ?>',
                                                icon: '<?= esc(addslashes($vtIcon)) ?>',
                                                vehicleCount: <?= (int)($typeCounts[$vt['slug']] ?? 0) ?>
                                            })">
                                            <i class="bi bi-trash"></i><span class="d-none d-sm-inline ms-1">Delete</span>
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
            <div class="modal-footer py-2 px-3 bg-white d-flex align-items-center justify-content-between flex-nowrap w-100 flex-shrink-0" style="border-top: 1px solid var(--border, #e2e8f0); z-index: 10;">
                <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" onclick="openAddVehicleTypeModal()">
                    <i class="bi bi-plus-lg"></i> Add New Type
                </button>
                <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($vehicleTypes)): ?>
    <?php foreach ($vehicleTypes as $vt): ?>
        <?php 
            $curColor = !empty($vt['color']) ? $vt['color'] : vehicle_type_color($vt['slug']);
            $curIcon = !empty($vt['icon']) ? $vt['icon'] : vehicle_type_icon($vt['slug']);
        ?>
        <!-- Edit Vehicle Type Modal for <?= esc($vt['name']) ?> -->
        <div class="modal fade" id="editVehicleTypeModal<?= $vt['id'] ?>" tabindex="-1" aria-labelledby="editVehicleTypeModalLabel<?= $vt['id'] ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable mx-auto my-2 my-sm-4" style="max-width: 500px; width: calc(100% - 1.5rem); max-height: calc(100vh - 2rem);">
                <div class="modal-content border-0 shadow" style="max-height: calc(100vh - 2rem); display: flex; flex-direction: column; overflow: hidden; border-radius: 16px;">
                    <div class="modal-header py-2 px-3 bg-white flex-shrink-0" style="border-bottom: 1px solid var(--border, #e2e8f0); z-index: 10;">
                        <h5 class="modal-title fw-bold fs-6 mb-0" id="editVehicleTypeModalLabel<?= $vt['id'] ?>"><i class="bi bi-pencil me-2"></i>Edit Vehicle Type</h5>
                        <button type="button" class="btn-close" onclick="backToManageVehicleTypesModal('editVehicleTypeModal<?= $vt['id'] ?>')" aria-label="Close"></button>
                    </div>
                    <form action="<?= base_url('admin/vehicle-types/update/' . $vt['id']) ?>" method="post" enctype="multipart/form-data" class="d-flex flex-column flex-grow-1" style="min-height: 0; flex: 1 1 auto; overflow: hidden;" data-no-change-guard>
                        <?= csrf_field() ?>
                        <div class="modal-body py-3 px-3 flex-grow-1" style="overflow-y: auto !important; -webkit-overflow-scrolling: touch; min-height: 0; flex: 1 1 auto;">
                            <!-- Name -->
                            <div class="mb-2">
                                <label for="edit_vehicle_type_name_<?= $vt['id'] ?>" class="form-label fw-semibold mb-1" style="font-size: 13px;">Vehicle Type Name</label>
                                <input id="edit_vehicle_type_name_<?= $vt['id'] ?>" name="name" type="text" class="form-control form-control-sm" value="<?= esc($vt['name']) ?>" maxlength="80" required oninput="updateEditVtPreview(<?= $vt['id'] ?>)">
                                <div class="form-text mt-0" style="font-size: 11px;">Updates display name and slug across vehicles and routes.</div>
                            </div>

                            <!-- Vehicle Photo / Emblem -->
                            <?php
                            $vtPhoto = $vt['photo'] ?? null;
                            $vtPhotoUrl = !empty($vtPhoto) ? base_url($vtPhoto) : '';
                            ?>
                            <div class="mb-2">
                                <label class="form-label fw-semibold mb-1 d-flex justify-content-between align-items-center" style="font-size: 13px;">
                                    <span>Vehicle Photo / Emblem <small class="text-muted fw-normal">(Optional image)</small></span>
                                    <button type="button" id="edit_vt_photo_clear_<?= $vt['id'] ?>" class="veh-photo-clear-btn <?= !empty($vtPhotoUrl) ? 'has-photo' : '' ?>" onclick="clearEditVtPhoto(<?= $vt['id'] ?>)">
                                        <i class="fas fa-times me-1"></i> Clear Photo
                                    </button>
                                    <input type="checkbox" name="remove_photo" id="remove_photo_<?= $vt['id'] ?>" value="1" style="display: none;">
                                </label>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-2 border d-flex align-items-center justify-content-center bg-light flex-shrink-0" style="width: 38px; height: 38px; overflow: hidden; border-color: #cbd5e1 !important;">
                                        <img id="edit_vt_photo_preview_<?= $vt['id'] ?>" src="<?= esc($vtPhotoUrl) ?>" alt="Photo" style="width: 100%; height: 100%; object-fit: cover; <?= empty($vtPhotoUrl) ? 'display: none;' : '' ?>">
                                        <i id="edit_vt_photo_placeholder_<?= $vt['id'] ?>" class="fas fa-image text-muted" style="font-size: 16px; <?= !empty($vtPhotoUrl) ? 'display: none;' : '' ?>"></i>
                                    </div>
                                    <input type="file" id="edit_vt_photo_input_<?= $vt['id'] ?>" name="photo" accept="image/png,image/jpeg,image/jpg,image/webp" class="form-control form-control-sm flex-grow-1" style="height: 38px; border-radius: 8px; font-size: 12.5px;" onchange="previewEditVtPhoto(<?= $vt['id'] ?>, this)">
                                </div>
                                <div class="form-text mt-1" style="font-size: 11px;">PNG, JPG, WEBP &bull; Max 2MB</div>
                            </div>

                            <!-- Color Assignment -->
                            <?php $curContrast = contrast_text_color($curColor); ?>
                            <div class="mb-2">
                                <label class="form-label fw-semibold mb-1 d-flex justify-content-between align-items-center" style="font-size: 13px;">
                                    <span>Assign Color</span>
                                    <span id="edit_vt_color_badge_<?= $vt['id'] ?>" class="badge rounded-pill px-2 py-0" style="background-color: <?= esc($curColor) ?>; color: <?= esc($curContrast) ?>; font-size: 11px; <?= ($curContrast === '#0f172a') ? 'border: 1px solid rgba(0,0,0,0.2);' : '' ?>">
                                        <?= esc($curColor) ?>
                                    </span>
                                </label>
                                <div class="vt-palette-wrap">
                                    <?php foreach ($colorPalette as $c): ?>
                                        <?php $cContrast = contrast_text_color($c); ?>
                                        <button type="button" 
                                                class="rounded-circle border-0 position-relative edit-color-swatch-<?= $vt['id'] ?> vt-color-swatch <?= (strtolower($c) === strtolower($curColor)) ? 'active' : '' ?>" 
                                                style="background-color: <?= $c ?>;" 
                                                data-color="<?= $c ?>"
                                                onclick="selectEditVtColor(<?= $vt['id'] ?>, '<?= $c ?>', this)">
                                            <i class="bi bi-check fw-bold <?= (strtolower($c) === strtolower($curColor)) ? '' : 'd-none' ?>" style="color: <?= $cContrast ?>;"></i>
                                        </button>
                                    <?php endforeach; ?>
                                    <?php $isCurColorInPalette = in_array(strtolower($curColor), array_map('strtolower', $colorPalette), true); ?>
                                    <label for="edit_vt_color_input_<?= $vt['id'] ?>" id="edit_vt_custom_label_<?= $vt['id'] ?>" class="vt-custom-color-badge <?= !$isCurColorInPalette ? 'active' : '' ?>" title="Custom Color Picker">
                                        <span class="vt-custom-color-preview">
                                            <input type="color" id="edit_vt_color_input_<?= $vt['id'] ?>" class="vt-custom-color-input" value="<?= esc($curColor) ?>" oninput="selectEditVtColor(<?= $vt['id'] ?>, this.value, null)" onchange="selectEditVtColor(<?= $vt['id'] ?>, this.value, null)">
                                        </span>
                                        <span class="vt-custom-color-text">Custom</span>
                                    </label>
                                </div>
                                <input type="hidden" id="edit_vt_color_<?= $vt['id'] ?>" name="color" value="<?= esc($curColor) ?>">
                            </div>

                            <!-- Icon Selection -->
                            <div class="mb-2">
                                <label class="form-label fw-semibold mb-1" style="font-size: 13px;">Assign Icon <small class="text-muted fw-normal">(Reflects vehicle type)</small></label>
                                <div class="d-flex flex-wrap gap-1">
                                    <?php foreach ($transitIcons as $ti): ?>
                                        <?php 
                                            $isSelected = ($ti['icon'] === $curIcon); 
                                            $editBtnStyle = $isSelected 
                                                ? "border-radius: 6px; font-size: 11.5px; padding: 3px 8px; background-color: " . esc($curColor) . " !important; border-color: " . esc($curColor) . " !important; color: " . esc($curContrast) . " !important; box-shadow: 0 2px 6px " . esc($curColor) . "55 !important;"
                                                : "border-radius: 6px; font-size: 11.5px; padding: 3px 8px;";
                                        ?>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 edit-icon-btn-<?= $vt['id'] ?> <?= $isSelected ? 'active' : '' ?>" 
                                                data-icon="<?= $ti['icon'] ?>"
                                                onclick="selectEditVtIcon(<?= $vt['id'] ?>, '<?= $ti['icon'] ?>', this)"
                                                style="<?= $editBtnStyle ?>">
                                            <i class="fas <?= $ti['icon'] ?>" <?= $isSelected ? 'style="color: ' . esc($curContrast) . ' !important;"' : '' ?>></i>
                                            <span <?= $isSelected ? 'style="color: ' . esc($curContrast) . ' !important;"' : '' ?>><?= esc($ti['label']) ?></span>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                                <input type="hidden" id="edit_vt_icon_<?= $vt['id'] ?>" name="icon" value="<?= esc($curIcon) ?>">
                            </div>

                            <!-- Live Preview -->
                            <div class="p-2 rounded-3" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
                                <small class="text-uppercase fw-bold text-muted d-block mb-1" style="font-size: 10px; letter-spacing: 0.5px;">Live Preview (Route Fares Card)</small>
                                <div class="rounded-3 shadow-sm bg-white p-2" id="edit_vt_preview_card_<?= $vt['id'] ?>" style="border-top: 3.5px solid <?= esc($curColor) ?>;">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <span id="edit_vt_preview_icon_<?= $vt['id'] ?>" class="d-inline-flex align-items-center justify-content-center rounded-2 flex-shrink-0" style="width: 26px; height: 26px; background-color: <?= esc($curColor) ?>; color: <?= esc($curContrast) ?>; font-size: 12px; overflow: hidden;">
                                                <img id="edit_vt_preview_photo_<?= $vt['id'] ?>" src="<?= esc($vtPhotoUrl) ?>" alt="Photo" style="width: 100%; height: 100%; object-fit: cover; <?= empty($vtPhotoUrl) ? 'display: none;' : '' ?>">
                                                <i class="fas <?= esc($curIcon) ?>" id="edit_vt_preview_icon_i_<?= $vt['id'] ?>" style="color: <?= esc($curContrast) ?> !important; <?= !empty($vtPhotoUrl) ? 'display: none;' : '' ?>"></i>
                                            </span>
                                            <strong id="edit_vt_preview_title_<?= $vt['id'] ?>" style="color: <?= esc($curColor) ?>; font-size: 13px;"><?= esc($vt['name']) ?> Routes</strong>
                                        </div>
                                        <span id="edit_vt_preview_badge_<?= $vt['id'] ?>" class="badge rounded-pill" style="background-color: <?= esc($curColor) ?>18; color: <?= esc($curColor) ?>; border: 1px solid <?= esc($curColor) ?>40; font-size: 11px;">
                                            <?= esc($vt['name']) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer py-2 px-3 bg-white justify-content-between flex-shrink-0" style="border-top: 1px solid var(--border, #e2e8f0); z-index: 10;">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="backToManageVehicleTypesModal('editVehicleTypeModal<?= $vt['id'] ?>')">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-primary px-3"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<div class="modal fade" id="addVehicleTypeModal" tabindex="-1" aria-labelledby="addVehicleTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable mx-auto my-2 my-sm-4" style="max-width: 500px; width: calc(100% - 1.5rem); max-height: calc(100vh - 2rem);">
        <div class="modal-content border-0 shadow" style="max-height: calc(100vh - 2rem); display: flex; flex-direction: column; overflow: hidden; border-radius: 16px;">
            <div class="modal-header py-2 px-3 bg-white flex-shrink-0" style="border-bottom: 1px solid var(--border, #e2e8f0); z-index: 10;">
                <h5 class="modal-title fw-bold fs-6 mb-0" id="addVehicleTypeModalLabel"><i class="bi bi-tags me-2"></i>Add Vehicle Type</h5>
                <button type="button" class="btn-close" onclick="closeAddVehicleTypeModal()" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('admin/vehicle-types/store') ?>" method="post" enctype="multipart/form-data" class="d-flex flex-column flex-grow-1" style="min-height: 0; flex: 1 1 auto; overflow: hidden;">
                <?= csrf_field() ?>
                <div class="modal-body py-3 px-3 flex-grow-1" style="overflow-y: auto !important; -webkit-overflow-scrolling: touch; min-height: 0; flex: 1 1 auto;">
                    <!-- Vehicle Type Name -->
                    <div class="mb-2">
                        <label for="vehicle_type_name" class="form-label fw-semibold mb-1" style="font-size: 13px;">Vehicle Type Name</label>
                        <input id="vehicle_type_name" name="name" type="text" class="form-control form-control-sm" placeholder="e.g. Bus, Tricycle, Taxi" maxlength="80" required oninput="updateAddVtPreview()">
                        <div class="form-text mt-0" style="font-size: 11px;">It will be available in Vehicle Register and as a new Route Fares card.</div>
                    </div>

                    <!-- Vehicle Photo / Emblem -->
                    <div class="mb-2">
                        <label class="form-label fw-semibold mb-1 d-flex justify-content-between align-items-center" style="font-size: 13px;">
                            <span>Vehicle Photo / Emblem <small class="text-muted fw-normal">(Optional image)</small></span>
                            <button type="button" id="add_vt_photo_clear" class="veh-photo-clear-btn" onclick="clearAddVtPhoto()">
                                <i class="fas fa-times me-1"></i> Clear Photo
                            </button>
                        </label>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-2 border d-flex align-items-center justify-content-center bg-light flex-shrink-0" style="width: 38px; height: 38px; overflow: hidden; border-color: #cbd5e1 !important;">
                                <img id="add_vt_photo_preview" src="" alt="Preview" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                                <i id="add_vt_photo_placeholder" class="fas fa-image text-muted" style="font-size: 16px;"></i>
                            </div>
                            <input type="file" id="add_vt_photo_input" name="photo" accept="image/png,image/jpeg,image/jpg,image/webp" class="form-control form-control-sm flex-grow-1" style="height: 38px; border-radius: 8px; font-size: 12.5px;" onchange="previewAddVtPhoto(this)">
                        </div>
                        <div class="form-text mt-1" style="font-size: 11px;">PNG, JPG, WEBP &bull; Max 2MB</div>
                    </div>

                    <!-- Assign Color -->
                    <?php $sugContrast = contrast_text_color($suggestedColor); ?>
                    <div class="mb-2">
                        <label class="form-label fw-semibold mb-1 d-flex justify-content-between align-items-center" style="font-size: 13px;">
                            <span>Assign Color <small class="text-muted fw-normal">(Distinct from other vehicles)</small></span>
                            <span id="add_vt_color_badge" class="badge rounded-pill px-2 py-0" style="background-color: <?= esc($suggestedColor) ?>; color: <?= esc($sugContrast) ?>; font-size: 11px; <?= ($sugContrast === '#0f172a') ? 'border: 1px solid rgba(0,0,0,0.2);' : '' ?>">
                                <?= esc($suggestedColor) ?>
                            </span>
                        </label>
                        <div class="vt-palette-wrap" id="add_vt_palette">
                            <?php foreach ($colorPalette as $c): ?>
                                <?php 
                                    $isUsed = in_array(strtolower($c), array_map('strtolower', $usedColors), true); 
                                    $cContrast = contrast_text_color($c);
                                ?>
                                <button type="button" 
                                        class="rounded-circle border-0 position-relative add-color-swatch vt-color-swatch <?= ($c === $suggestedColor) ? 'active' : '' ?>" 
                                        style="background-color: <?= $c ?>;" 
                                        data-color="<?= $c ?>"
                                        onclick="selectAddVtColor('<?= $c ?>', this)"
                                        title="<?= $isUsed ? 'Already in use by another type' : 'Available distinct color' ?>">
                                    <i class="bi bi-check fw-bold <?= ($c === $suggestedColor) ? '' : 'd-none' ?>" style="color: <?= $cContrast ?>;"></i>
                                </button>
                            <?php endforeach; ?>
                            <label for="add_vt_color_input" id="add_vt_custom_label" class="vt-custom-color-badge" title="Custom Color Picker">
                                <span class="vt-custom-color-preview">
                                    <input type="color" id="add_vt_color_input" class="vt-custom-color-input" value="<?= esc($suggestedColor) ?>" oninput="selectAddVtColor(this.value, null)" onchange="selectAddVtColor(this.value, null)">
                                </span>
                                <span class="vt-custom-color-text">Custom</span>
                            </label>
                        </div>
                        <input type="hidden" id="add_vt_color" name="color" value="<?= esc($suggestedColor) ?>">
                    </div>

                    <!-- Assign Icon -->
                    <div class="mb-2">
                        <label class="form-label fw-semibold mb-1" style="font-size: 13px;">Assign Icon <small class="text-muted fw-normal">(Reflects vehicle type)</small></label>
                        <div class="d-flex flex-wrap gap-1" id="add_vt_icons">
                            <?php foreach ($transitIcons as $idx => $ti): ?>
                                <?php 
                                    $isInitActive = ($ti['icon'] === 'fa-bus-simple');
                                    $addBtnStyle = $isInitActive 
                                        ? "border-radius: 6px; font-size: 11.5px; padding: 3px 8px; background-color: " . esc($suggestedColor) . " !important; border-color: " . esc($suggestedColor) . " !important; color: " . esc($sugContrast) . " !important; box-shadow: 0 2px 6px " . esc($suggestedColor) . "55 !important;"
                                        : "border-radius: 6px; font-size: 11.5px; padding: 3px 8px;";
                                ?>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 add-icon-btn <?= $isInitActive ? 'active' : '' ?>" 
                                        data-icon="<?= $ti['icon'] ?>"
                                        onclick="selectAddVtIcon('<?= $ti['icon'] ?>', this)"
                                        style="<?= $addBtnStyle ?>">
                                    <i class="fas <?= $ti['icon'] ?>" <?= $isInitActive ? 'style="color: ' . esc($sugContrast) . ' !important;"' : '' ?>></i>
                                    <span <?= $isInitActive ? 'style="color: ' . esc($sugContrast) . ' !important;"' : '' ?>><?= esc($ti['label']) ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" id="add_vt_icon" name="icon" value="fa-bus-simple">
                    </div>

                    <!-- Live Preview -->
                    <div class="p-2 rounded-3" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
                        <small class="text-uppercase fw-bold text-muted d-block mb-1" style="font-size: 10px; letter-spacing: 0.5px;">Live Preview (Route Fares Card)</small>
                        <div class="rounded-3 shadow-sm bg-white p-2" id="add_vt_preview_card" style="border-top: 3.5px solid <?= esc($suggestedColor) ?>;">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <span id="add_vt_preview_icon" class="d-inline-flex align-items-center justify-content-center rounded-2 flex-shrink-0" style="width: 26px; height: 26px; background-color: <?= esc($suggestedColor) ?>; color: <?= esc($sugContrast) ?>; font-size: 12px; overflow: hidden;">
                                        <img id="add_vt_preview_photo" src="" alt="Preview" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                                        <i class="fas fa-bus-simple" id="add_vt_preview_icon_i" style="color: <?= esc($sugContrast) ?> !important;"></i>
                                    </span>
                                    <strong id="add_vt_preview_title" style="color: <?= esc($suggestedColor) ?>; font-size: 13px;">New Vehicle Routes</strong>
                                </div>
                                <span id="add_vt_preview_badge" class="badge rounded-pill" style="background-color: <?= esc($suggestedColor) ?>18; color: <?= esc($suggestedColor) ?>; border: 1px solid <?= esc($suggestedColor) ?>40; font-size: 11px;">
                                    New Vehicle
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-white justify-content-between flex-shrink-0" style="border-top: 1px solid var(--border, #e2e8f0); z-index: 10;">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="closeAddVehicleTypeModal()">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary px-3"><i class="bi bi-plus-lg me-1"></i>Add Vehicle Type</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════ -->
<!--  Deactivate Vehicle Confirmation Modal             -->
<!-- ══════════════════════════════════════════════════ -->
<div class="modal fade" id="deactivateVehicleConfirmModal" tabindex="-1" aria-labelledby="deactivateVehicleConfirmLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px; margin: 1.75rem auto;">
        <div class="modal-content delete-vehicle-modal-content">
            <!-- Amber accent stripe -->
            <div style="height: 4px; width: 100%; background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);"></div>

            <form id="deactivateVehicleForm" method="post" action="">
                <?= csrf_field() ?>
                <input type="hidden" name="redirect_tab" id="deactivateVehicleRedirectTab" value="">
                <div class="modal-body text-center p-4">
                    <!-- Icon badge -->
                    <div style="width: 68px; height: 68px; margin: 4px auto 18px auto; border-radius: 50%; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 0 0 8px #fffbeb;">
                        <i class="bi bi-pause-circle-fill"></i>
                    </div>

                    <h4 class="delete-vehicle-modal-title" id="deactivateVehicleConfirmLabel">Deactivate Vehicle?</h4>
                    <p class="delete-vehicle-modal-desc">
                        Are you sure you want to deactivate this vehicle? It will be removed from active queue operations and moved to the archive.
                    </p>

                    <!-- Vehicle preview chip -->
                    <div class="delete-vehicle-item-chip">
                        <i class="bi bi-truck" id="deactivateVehicleIcon"></i>
                        <span id="deactivateVehiclePlateLabel" class="fw-bold" style="font-family: monospace; letter-spacing: 0.5px; font-size: 0.95rem;">PLATE</span>
                        <span class="delete-vehicle-type-tag" id="deactivateVehicleTypeTag">TYPE</span>
                    </div>

                    <div id="deactivateVehicleSubInfo" class="mt-2 text-muted" style="font-size: 0.825rem;"></div>
                </div>

                <div class="modal-footer delete-vehicle-modal-footer">
                    <button type="button" class="btn delete-vehicle-btn-cancel" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn" style="flex: 1; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #fff; font-weight: 600; padding: 10px 18px; border-radius: 10px; border: none; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);">
                        <i class="bi bi-pause-circle me-1"></i> Yes, Deactivate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════ -->
<!--  Activate Vehicle Confirmation Modal               -->
<!-- ══════════════════════════════════════════════════ -->
<div class="modal fade" id="activateVehicleConfirmModal" tabindex="-1" aria-labelledby="activateVehicleConfirmLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px; margin: 1.75rem auto;">
        <div class="modal-content delete-vehicle-modal-content">
            <!-- Green accent stripe -->
            <div style="height: 4px; width: 100%; background: linear-gradient(90deg, #10b981 0%, #059669 100%);"></div>

            <form id="activateVehicleForm" method="post" action="">
                <?= csrf_field() ?>
                <input type="hidden" name="redirect_tab" id="activateVehicleRedirectTab" value="archived">
                <div class="modal-body text-center p-4">
                    <!-- Icon badge -->
                    <div style="width: 68px; height: 68px; margin: 4px auto 18px auto; border-radius: 50%; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 0 0 8px #f0fdf4;">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>

                    <h4 class="delete-vehicle-modal-title" id="activateVehicleConfirmLabel">Activate Vehicle?</h4>
                    <p class="delete-vehicle-modal-desc">
                        Are you sure you want to reactivate this vehicle? It will be restored from the archive and become eligible for terminal queue assignments.
                    </p>

                    <!-- Vehicle preview chip -->
                    <div class="delete-vehicle-item-chip">
                        <i class="bi bi-truck" id="activateVehicleIcon"></i>
                        <span id="activateVehiclePlateLabel" class="fw-bold" style="font-family: monospace; letter-spacing: 0.5px; font-size: 0.95rem;">PLATE</span>
                        <span class="delete-vehicle-type-tag" id="activateVehicleTypeTag">TYPE</span>
                    </div>

                    <div id="activateVehicleSubInfo" class="mt-2 text-muted" style="font-size: 0.825rem;"></div>
                </div>

                <div class="modal-footer delete-vehicle-modal-footer">
                    <button type="button" class="btn delete-vehicle-btn-cancel" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn" style="flex: 1; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #fff; font-weight: 600; padding: 10px 18px; border-radius: 10px; border: none; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
                        <i class="bi bi-check-circle me-1"></i> Yes, Activate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════ -->
<!--  Delete Vehicle Confirmation Modal (Archive Only)   -->
<!-- ══════════════════════════════════════════════════ -->
<div class="modal fade" id="deleteVehicleConfirmModal" tabindex="-1" aria-labelledby="deleteVehicleConfirmLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px; margin: 1.75rem auto;">
        <div class="modal-content delete-vehicle-modal-content">
            <!-- Accent stripe -->
            <div class="delete-vehicle-stripe"></div>

            <div class="modal-body text-center p-4">
                <!-- Icon badge -->
                <div class="delete-vehicle-icon-wrapper">
                    <i class="fas fa-trash-can"></i>
                </div>

                <h4 class="delete-vehicle-modal-title" id="deleteVehicleConfirmLabel">Permanently Delete Vehicle?</h4>
                <p class="delete-vehicle-modal-desc" id="deleteVehicleConfirmMessage">
                    Are you sure you want to permanently delete this vehicle from the archive? This action cannot be undone.
                </p>

                <!-- Vehicle preview chip -->
                <div class="delete-vehicle-item-chip" id="deleteVehicleItemChip">
                    <i class="bi bi-truck" id="deleteVehicleIcon"></i>
                    <span id="deleteVehiclePlateLabel" class="fw-bold" style="font-family: monospace; letter-spacing: 0.5px; font-size: 0.95rem;">PLATE</span>
                    <span class="delete-vehicle-type-tag" id="deleteVehicleTypeTag">TYPE</span>
                </div>

                <!-- Subtitle for Driver & Route info -->
                <div id="deleteVehicleSubInfo" class="mt-2 text-muted" style="font-size: 0.825rem;"></div>
            </div>

            <div class="modal-footer delete-vehicle-modal-footer">
                <button type="button" class="btn delete-vehicle-btn-cancel" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn delete-vehicle-btn-confirm" id="deleteVehicleConfirmBtn">
                    <i class="fas fa-trash-alt me-1"></i> Yes, Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════ -->
<!--  Delete Vehicle Type Confirmation Modal            -->
<!-- ══════════════════════════════════════════════════ -->
<div class="modal fade" id="deleteVehicleTypeConfirmModal" tabindex="-1" aria-labelledby="deleteVehicleTypeConfirmLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px; margin: 1.75rem auto;">
        <div class="modal-content delete-vehicle-modal-content">
            <!-- Accent stripe -->
            <div class="delete-vehicle-stripe"></div>

            <div class="modal-body text-center p-4">
                <!-- Icon badge -->
                <div class="delete-vehicle-icon-wrapper">
                    <i class="fas fa-trash-can"></i>
                </div>

                <h4 class="delete-vehicle-modal-title" id="deleteVehicleTypeConfirmLabel">Delete Vehicle Type?</h4>
                <p class="delete-vehicle-modal-desc" id="deleteVehicleTypeConfirmMessage">
                    Are you sure you want to delete this vehicle type? All connected fares, routes, and departure rules will also be removed. This action cannot be undone.
                </p>

                <!-- Vehicle type preview chip -->
                <div class="delete-vehicle-item-chip" id="deleteVehicleTypeItemChip">
                    <div id="deleteVehicleTypeIconBox" class="rounded-2 d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 26px; height: 26px; background-color: #475569; color: #ffffff;">
                        <i id="deleteVehicleTypeIcon" class="fas fa-bus-simple" style="font-size: 13px;"></i>
                    </div>
                    <span id="deleteVehicleTypeName" class="fw-bold" style="font-size: 0.95rem;">Type</span>
                    <span class="badge rounded-pill flex-shrink-0" id="deleteVehicleTypeBadge" style="font-size: 11px;">#475569</span>
                    <span class="text-muted small flex-shrink-0" id="deleteVehicleTypeCount">• 0 vehicle(s)</span>
                </div>

                <!-- Caution note if vehicles exist under this type -->
                <div id="deleteVehicleTypeWarningNote" class="mt-2 text-danger small fw-semibold" style="font-size: 0.825rem; display: none;">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <span>This vehicle type has registered vehicles.</span>
                </div>
            </div>

            <div class="modal-footer delete-vehicle-modal-footer">
                <button type="button" class="btn delete-vehicle-btn-cancel" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn delete-vehicle-btn-confirm" id="deleteVehicleTypeConfirmBtn">
                    <i class="fas fa-trash-alt me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* ── Delete Vehicle & Vehicle Type Confirmation Modal Styles ── */
.delete-vehicle-modal-content {
    border: 0 !important;
    border-radius: 18px !important;
    box-shadow: 0 20px 35px -5px rgba(15, 23, 42, 0.25), 0 10px 15px -5px rgba(15, 23, 42, 0.1) !important;
    background: #ffffff !important;
    overflow: hidden !important;
    position: relative !important;
    font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif !important;
}

.delete-vehicle-stripe {
    height: 4px;
    width: 100%;
    background: linear-gradient(90deg, #dc2626 0%, #ef4444 50%, #f97316 100%);
}

.delete-vehicle-icon-wrapper {
    width: 68px;
    height: 68px;
    margin: 4px auto 18px auto;
    border-radius: 50%;
    background: #fef2f2;
    color: #dc2626;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    box-shadow: 0 0 0 8px #fff1f2;
    transition: transform 0.3s ease;
}

.delete-vehicle-modal-content:hover .delete-vehicle-icon-wrapper {
    transform: scale(1.04);
}

.delete-vehicle-modal-title {
    font-size: 1.35rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 8px;
    letter-spacing: -0.01em;
}

.delete-vehicle-modal-desc {
    color: #64748b;
    font-size: 0.925rem;
    line-height: 1.5;
    margin-bottom: 16px;
    padding: 0 10px;
}

.delete-vehicle-item-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 7px 14px;
    border-radius: 9999px;
    font-size: 0.825rem;
    color: #334155;
    max-width: 100%;
    box-sizing: border-box;
    flex-wrap: wrap;
}

.delete-vehicle-item-chip i {
    font-size: 0.95rem;
    flex-shrink: 0;
}

.delete-vehicle-type-tag {
    background: #fee2e2;
    color: #b91c1c;
    font-size: 0.725rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 6px;
    letter-spacing: 0.3px;
    text-transform: uppercase;
    flex-shrink: 0;
}

.delete-vehicle-modal-footer {
    border: 0 !important;
    padding: 0 24px 24px 24px !important;
    display: flex !important;
    gap: 12px !important;
    justify-content: stretch !important;
    background: transparent !important;
}

.delete-vehicle-btn-cancel {
    flex: 1 !important;
    background: #f1f5f9 !important;
    color: #475569 !important;
    border: 1px solid #e2e8f0 !important;
    padding: 10px 18px !important;
    border-radius: 10px !important;
    font-weight: 600 !important;
    font-size: 0.9rem !important;
    transition: all 0.2s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.delete-vehicle-btn-cancel:hover,
.delete-vehicle-btn-cancel:focus {
    background: #e2e8f0 !important;
    color: #0f172a !important;
    border-color: #cbd5e1 !important;
}

.delete-vehicle-btn-confirm {
    flex: 1 !important;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
    color: #ffffff !important;
    border: 0 !important;
    padding: 10px 18px !important;
    border-radius: 10px !important;
    font-weight: 600 !important;
    font-size: 0.9rem !important;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.28) !important;
    transition: all 0.2s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.delete-vehicle-btn-confirm:hover,
.delete-vehicle-btn-confirm:focus {
    background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%) !important;
    box-shadow: 0 6px 16px rgba(220, 38, 38, 0.38) !important;
    transform: translateY(-1px);
}

.delete-vehicle-btn-confirm.is-loading {
    pointer-events: none !important;
    opacity: 0.75 !important;
}

/* Ensure modal paints above fixed navbar */
body.modal-open #deleteVehicleConfirmModal,
#deleteVehicleConfirmModal,
body.modal-open #manageVehicleTypesModal,
#manageVehicleTypesModal {
    z-index: 100050 !important;
}

/* Ensure Sub-Modals (Add, Edit, Delete Vehicle Type) float cleanly above Manage Vehicle Types Modal */
body.modal-open #deleteVehicleTypeConfirmModal,
#deleteVehicleTypeConfirmModal,
body.modal-open #addVehicleTypeModal,
#addVehicleTypeModal,
body.modal-open [id^="editVehicleTypeModal"],
[id^="editVehicleTypeModal"] {
    z-index: 100070 !important;
}

/* Vehicle Type Modals: Full Scrollability, Clean Layout & No Cutoff */
#manageVehicleTypesModal .modal-dialog,
[id^="editVehicleTypeModal"] .modal-dialog,
#addVehicleTypeModal .modal-dialog {
    max-height: calc(100vh - 2rem) !important;
}

#manageVehicleTypesModal .modal-content,
[id^="editVehicleTypeModal"] .modal-content,
#addVehicleTypeModal .modal-content {
    max-height: calc(100vh - 2rem) !important;
    display: flex !important;
    flex-direction: column !important;
    overflow: hidden !important;
    border-radius: 16px !important;
}

[id^="editVehicleTypeModal"] form,
#addVehicleTypeModal form {
    display: flex !important;
    flex-direction: column !important;
    flex: 1 1 auto !important;
    min-height: 0 !important;
    overflow: hidden !important;
}

#manageVehicleTypesModal .modal-body,
[id^="editVehicleTypeModal"] .modal-body,
#addVehicleTypeModal .modal-body {
    overflow-y: auto !important;
    -webkit-overflow-scrolling: touch !important;
    flex: 1 1 auto !important;
    min-height: 0 !important;
    overscroll-behavior: contain !important;
    padding-bottom: 1.25rem !important;
}

#manageVehicleTypesModal .modal-header,
#manageVehicleTypesModal .modal-footer,
[id^="editVehicleTypeModal"] .modal-header,
[id^="editVehicleTypeModal"] .modal-footer,
#addVehicleTypeModal .modal-header,
#addVehicleTypeModal .modal-footer {
    flex-shrink: 0 !important;
}

/* ── Vehicle Type Color Swatches & Custom Color Picker ── */
.vt-palette-wrap {
    display: flex !important;
    flex-wrap: wrap !important;
    flex-direction: row !important;
    align-items: center !important;
    gap: 8px !important;
    padding: 3px 0 !important;
    width: 100% !important;
}

.vt-color-swatch,
.add-color-swatch,
[class*="edit-color-swatch-"] {
    width: 28px !important;
    height: 28px !important;
    min-width: 28px !important;
    max-width: 28px !important;
    min-height: 28px !important;
    max-height: 28px !important;
    border-radius: 50% !important;
    border: none !important;
    padding: 0 !important;
    margin: 0 !important;
    flex: 0 0 28px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    cursor: pointer !important;
    box-sizing: border-box !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.22) !important;
    transition: transform 0.15s ease, box-shadow 0.15s ease !important;
    outline: none !important;
    -webkit-appearance: none !important;
    appearance: none !important;
    position: relative !important;
}

.vt-color-swatch:hover,
.add-color-swatch:hover,
[class*="edit-color-swatch-"]:hover {
    transform: scale(1.15) !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3) !important;
    z-index: 2 !important;
}

.vt-color-swatch.active,
.add-color-swatch.active,
[class*="edit-color-swatch-"].active {
    box-shadow: 0 0 0 2px #ffffff, 0 0 0 4px #0f172a !important;
    transform: scale(1.1) !important;
    z-index: 3 !important;
}

.vt-color-swatch i,
.add-color-swatch i,
[class*="edit-color-swatch-"] i {
    pointer-events: none !important;
    font-size: 16px !important;
    line-height: 28px !important;
}

/* Custom Color Badge Pill */
.vt-custom-color-badge {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    margin-left: 2px !important;
    padding: 2px 8px 2px 2px !important;
    border-radius: 9999px !important;
    background: #f8fafc !important;
    border: 1.5px solid #e2e8f0 !important;
    cursor: pointer !important;
    transition: all 0.15s ease !important;
    user-select: none !important;
    -webkit-tap-highlight-color: transparent !important;
    box-sizing: border-box !important;
    height: 32px !important;
    flex-shrink: 0 !important;
}

.vt-custom-color-badge:hover {
    background: #f1f5f9 !important;
    border-color: #cbd5e1 !important;
}

.vt-custom-color-badge.active {
    border-color: #0f172a !important;
    background: #e2e8f0 !important;
    box-shadow: 0 0 0 2px rgba(15, 23, 42, 0.12) !important;
}

.vt-custom-color-preview {
    position: relative !important;
    width: 26px !important;
    height: 26px !important;
    min-width: 26px !important;
    max-width: 26px !important;
    min-height: 26px !important;
    max-height: 26px !important;
    border-radius: 50% !important;
    overflow: hidden !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2) !important;
    border: 1px solid rgba(0, 0, 0, 0.15) !important;
    flex-shrink: 0 !important;
    box-sizing: border-box !important;
    pointer-events: auto !important;
}

.vt-custom-color-badge.active .vt-custom-color-preview {
    box-shadow: 0 0 0 2px #ffffff, 0 0 0 3.5px #0f172a !important;
}

.vt-custom-color-input {
    position: absolute !important;
    top: -50% !important;
    left: -50% !important;
    width: 200% !important;
    height: 200% !important;
    padding: 0 !important;
    margin: 0 !important;
    border: none !important;
    cursor: pointer !important;
    background: none !important;
    opacity: 1 !important;
    box-sizing: border-box !important;
}

.vt-custom-color-input::-webkit-color-swatch-wrapper {
    padding: 0 !important;
    border: none !important;
}

.vt-custom-color-input::-webkit-color-swatch {
    border: none !important;
    border-radius: 0 !important;
}

.vt-custom-color-input::-moz-color-swatch {
    border: none !important;
    border-radius: 0 !important;
}

.vt-custom-color-text {
    font-size: 11.5px !important;
    font-weight: 600 !important;
    color: #475569 !important;
    line-height: 1 !important;
    letter-spacing: 0.01em !important;
}

.vt-custom-color-badge.active .vt-custom-color-text {
    color: #0f172a !important;
    font-weight: 700 !important;
}
</style>

<script>
(function() {
    var _deleteVehicleFormId = null;

    window.showDeactivateVehicleModal = function(opts) {
        opts = opts || {};
        var modalEl = document.getElementById('deactivateVehicleConfirmModal');
        var form = document.getElementById('deactivateVehicleForm');
        if (form) {
            form.action = '<?= base_url('admin/vehicles/deactivate') ?>/' + opts.vehicleId;
        }
        var plateEl = document.getElementById('deactivateVehiclePlateLabel');
        var typeTagEl = document.getElementById('deactivateVehicleTypeTag');
        var iconEl = document.getElementById('deactivateVehicleIcon');
        var subInfoEl = document.getElementById('deactivateVehicleSubInfo');
        var color = opts.typeColor || '#f59e0b';

        if (plateEl) plateEl.textContent = opts.plateNumber || 'Vehicle';
        if (iconEl) iconEl.style.color = color;
        if (typeTagEl) {
            if (opts.typeLabel) {
                typeTagEl.textContent = opts.typeLabel;
                typeTagEl.style.backgroundColor = color + '20';
                typeTagEl.style.color = color;
                typeTagEl.style.border = '1px solid ' + color + '45';
                typeTagEl.style.display = '';
            } else {
                typeTagEl.style.display = 'none';
            }
        }
        if (subInfoEl) {
            var parts = [];
            if (opts.driverName && opts.driverName !== '—') parts.push('Driver: ' + opts.driverName);
            if (opts.route) parts.push(opts.route);
            subInfoEl.textContent = parts.join(' · ');
            subInfoEl.style.display = parts.length > 0 ? '' : 'none';
        }
        if (modalEl && window.bootstrap) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    };

    window.showActivateVehicleModal = function(opts) {
        opts = opts || {};
        var modalEl = document.getElementById('activateVehicleConfirmModal');
        var form = document.getElementById('activateVehicleForm');
        if (form) {
            form.action = '<?= base_url('admin/vehicles/activate') ?>/' + opts.vehicleId;
        }
        var plateEl = document.getElementById('activateVehiclePlateLabel');
        var typeTagEl = document.getElementById('activateVehicleTypeTag');
        var iconEl = document.getElementById('activateVehicleIcon');
        var subInfoEl = document.getElementById('activateVehicleSubInfo');
        var color = opts.typeColor || '#10b981';

        if (plateEl) plateEl.textContent = opts.plateNumber || 'Vehicle';
        if (iconEl) iconEl.style.color = color;
        if (typeTagEl) {
            if (opts.typeLabel) {
                typeTagEl.textContent = opts.typeLabel;
                typeTagEl.style.backgroundColor = color + '20';
                typeTagEl.style.color = color;
                typeTagEl.style.border = '1px solid ' + color + '45';
                typeTagEl.style.display = '';
            } else {
                typeTagEl.style.display = 'none';
            }
        }
        if (subInfoEl) {
            var parts = [];
            if (opts.driverName && opts.driverName !== '—') parts.push('Driver: ' + opts.driverName);
            if (opts.route) parts.push(opts.route);
            subInfoEl.textContent = parts.join(' · ');
            subInfoEl.style.display = parts.length > 0 ? '' : 'none';
        }
        if (modalEl && window.bootstrap) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    };

    window.showDeleteVehicleModal = function(opts) {
        opts = opts || {};
        var modalEl = document.getElementById('deleteVehicleConfirmModal');
        if (!modalEl || !window.bootstrap) {
            if (window.confirm('Are you sure you want to delete this vehicle?')) {
                var form = document.getElementById(opts.formId);
                if (form) form.submit();
            }
            return;
        }

        var plateEl = document.getElementById('deleteVehiclePlateLabel');
        var typeTagEl = document.getElementById('deleteVehicleTypeTag');
        var iconEl = document.getElementById('deleteVehicleIcon');
        var subInfoEl = document.getElementById('deleteVehicleSubInfo');
        var color = opts.typeColor || '#dc2626';

        if (plateEl) {
            plateEl.textContent = opts.plateNumber || 'Vehicle';
        }
        if (iconEl) {
            iconEl.style.color = color;
        }
        if (typeTagEl) {
            if (opts.typeLabel) {
                typeTagEl.textContent = opts.typeLabel;
                typeTagEl.style.backgroundColor = color + '20';
                typeTagEl.style.color = color;
                typeTagEl.style.border = '1px solid ' + color + '45';
                typeTagEl.style.display = '';
            } else {
                typeTagEl.style.display = 'none';
            }
        }

        if (subInfoEl) {
            var parts = [];
            if (opts.driverName && opts.driverName !== '—') {
                parts.push('Driver: ' + opts.driverName);
            }
            if (opts.route) {
                parts.push(opts.route);
            }
            subInfoEl.textContent = parts.join(' · ');
            subInfoEl.style.display = parts.length > 0 ? '' : 'none';
        }

        var confirmBtn = document.getElementById('deleteVehicleConfirmBtn');
        if (confirmBtn) {
            confirmBtn.classList.remove('is-loading');
            confirmBtn.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Yes, Delete';
        }

        _deleteVehicleFormId = opts.formId || null;
        window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
    };

    document.addEventListener('DOMContentLoaded', function() {
        var confirmBtn = document.getElementById('deleteVehicleConfirmBtn');
        if (!confirmBtn) return;

        confirmBtn.addEventListener('click', function() {
            if (!_deleteVehicleFormId) return;

            confirmBtn.classList.add('is-loading');
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Deleting...';

            var form = document.getElementById(_deleteVehicleFormId);
            if (form) {
                form.submit();
            } else {
                var modalEl = document.getElementById('deleteVehicleConfirmModal');
                if (modalEl && window.bootstrap) {
                    window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                }
                confirmBtn.classList.remove('is-loading');
                confirmBtn.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Yes, Delete';
            }
        });
    });
})();

(function() {
    var _deleteVehicleTypeFormId = null;

    function getLocalContrastColor(hexColor) {
        if (!hexColor) return '#ffffff';
        var hex = hexColor.replace('#', '').trim();
        if (hex.length === 3) {
            hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
        }
        if (hex.length < 6) return '#ffffff';
        var r = parseInt(hex.substr(0, 2), 16) || 0;
        var g = parseInt(hex.substr(2, 2), 16) || 0;
        var b = parseInt(hex.substr(4, 2), 16) || 0;
        var brightness = (r * 299 + g * 587 + b * 114) / 1000;
        return (brightness > 155) ? '#0f172a' : '#ffffff';
    }

    window.showDeleteVehicleTypeModal = function(opts) {
        opts = opts || {};
        var modalEl = document.getElementById('deleteVehicleTypeConfirmModal');
        if (!modalEl || !window.bootstrap) {
            if (window.confirm('Are you sure you want to delete vehicle type "' + (opts.name || '') + '"?\n\nAll connected fares, routes, and departure rules will also be removed. Note: Any registered vehicles using this type must be reassigned or removed first.')) {
                var form = document.getElementById(opts.formId);
                if (form) form.submit();
            }
            return;
        }

        var nameEl = document.getElementById('deleteVehicleTypeName');
        var badgeEl = document.getElementById('deleteVehicleTypeBadge');
        var iconBoxEl = document.getElementById('deleteVehicleTypeIconBox');
        var iconEl = document.getElementById('deleteVehicleTypeIcon');
        var countEl = document.getElementById('deleteVehicleTypeCount');
        var warnEl = document.getElementById('deleteVehicleTypeWarningNote');

        if (nameEl) nameEl.textContent = opts.name || 'Vehicle Type';

        var color = opts.color || '#475569';
        var contrast = (typeof getContrastTextColor === 'function') ? getContrastTextColor(color) : getLocalContrastColor(color);
        var isLight = (contrast === '#0f172a');

        if (badgeEl) {
            badgeEl.textContent = color;
            badgeEl.style.backgroundColor = isLight ? '#f1f5f9' : (color + '22');
            badgeEl.style.color = isLight ? '#0f172a' : color;
            badgeEl.style.border = '1px solid ' + (isLight ? '#cbd5e1' : (color + '55'));
        }

        if (iconBoxEl) {
            iconBoxEl.style.backgroundColor = color;
            iconBoxEl.style.color = contrast;
            iconBoxEl.style.border = isLight ? '1.5px solid #cbd5e1' : 'none';
        }

        if (iconEl) {
            var rawIcon = opts.icon || 'fa-bus-simple';
            var iconClass = rawIcon.trim();
            if (!iconClass.startsWith('fa-') && !iconClass.startsWith('fas ') && !iconClass.startsWith('bi-')) {
                iconClass = 'fa-' + iconClass;
            }
            if (!iconClass.startsWith('fa') && !iconClass.startsWith('bi')) {
                iconClass = 'fas ' + iconClass;
            }
            iconEl.className = iconClass.indexOf(' ') !== -1 ? iconClass : ('fas ' + iconClass);
            iconEl.style.setProperty('color', contrast, 'important');
        }

        var vCount = parseInt(opts.vehicleCount, 10) || 0;
        if (countEl) {
            countEl.textContent = '• ' + vCount + ' vehicle(s)';
        }

        if (warnEl) {
            if (vCount > 0) {
                warnEl.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> This vehicle type has ' + vCount + ' registered vehicle(s). Reassign or remove them first before deleting.';
                warnEl.style.display = '';
            } else {
                warnEl.style.display = 'none';
            }
        }

        var confirmBtn = document.getElementById('deleteVehicleTypeConfirmBtn');
        if (confirmBtn) {
            confirmBtn.classList.remove('is-loading');
            confirmBtn.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Delete';
            confirmBtn.disabled = (vCount > 0);
            if (vCount > 0) {
                confirmBtn.setAttribute('title', 'Cannot delete while registered vehicles are assigned to this type');
            } else {
                confirmBtn.removeAttribute('title');
            }
        }

        _deleteVehicleTypeFormId = opts.formId || null;
        window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
    };

    document.addEventListener('DOMContentLoaded', function() {
        var modalEl = document.getElementById('deleteVehicleTypeConfirmModal');
        if (modalEl) {
            modalEl.addEventListener('show.bs.modal', function() {
                setTimeout(function() {
                    var backdrops = document.querySelectorAll('.modal-backdrop');
                    if (backdrops.length > 1) {
                        backdrops[backdrops.length - 1].style.zIndex = '100065';
                    }
                }, 10);
            });

            modalEl.addEventListener('hidden.bs.modal', function() {
                var manageModal = document.getElementById('manageVehicleTypesModal');
                if (manageModal && manageModal.classList.contains('show')) {
                    document.body.classList.add('modal-open');
                    document.body.style.overflow = 'hidden';
                }
            });
        }

        var confirmBtn = document.getElementById('deleteVehicleTypeConfirmBtn');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', function() {
                if (!_deleteVehicleTypeFormId) return;

                confirmBtn.classList.add('is-loading');
                confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Deleting...';

                var form = document.getElementById(_deleteVehicleTypeFormId);
                if (form) {
                    form.submit();
                } else {
                    if (modalEl && window.bootstrap) {
                        window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                    }
                    confirmBtn.classList.remove('is-loading');
                    confirmBtn.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Delete';
                }
            });
        }
    });
})();
</script>

<?= view('templates/footer') ?>


