<?= view('templates/header', ['title' => $title]) ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">

<?php
$isAdmin = in_array(session()->get('role'), ['super_admin', 'admin'], true);
?>

<div class="page-header-modern fade-in">
    <h1 class="page-title-modern">
        <i class="bi bi-signpost-split"></i>
        Routes Management
    </h1>
    <?php if ($isAdmin): ?>
    <div>
        <a href="<?= base_url('admin/routes/create') ?>" class="btn-modern btn-modern-primary">
            <i class="bi bi-plus-circle"></i> Add New Route
        </a>
    </div>
    <?php endif; ?>
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

<!-- Search Bar & Lifecycle Filter Tabs -->
<div class="modern-card shadow-modern fade-in mb-4">
    <div class="modern-card-body py-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <!-- Search Input Capsule -->
            <div class="route-search-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" class="route-search-input" id="route-search" placeholder="Search destination, origin, vehicle type, or fare..." onkeyup="filterRoutes()" oninput="toggleRouteClearBtn(this.value)" autocomplete="off">
                <button type="button" class="btn-clear-search" id="clear-route-search" onclick="clearRouteSearch()" style="display: none !important;" title="Clear search">
                    <i class="bi bi-x-circle-fill"></i>
                </button>
            </div>

            <!-- Route Status Tabs & Select All -->
            <div class="d-flex flex-wrap align-items-center gap-2">
                <button type="button" class="vf-btn active" id="tab-active-routes" onclick="setRouteTab('active', this)">
                    <i class="bi bi-check-circle-fill" style="color: var(--primary-red, #b71c1c);"></i> Active Routes
                    <span class="vf-count" id="count-active-tab"><?= $countActiveGroups ?? 0 ?></span>
                </button>
                <button type="button" class="vf-btn vf-archive" id="tab-archived-routes" onclick="setRouteTab('archived', this)">
                    <i class="bi bi-archive-fill text-secondary"></i> Archived Routes
                    <span class="vf-count" id="count-archived-tab"><?= $countArchivedGroups ?? 0 ?></span>
                </button>
                <?php if ($isAdmin): ?>
                <button type="button" class="btn-modern btn-modern-sm btn-modern-outline" id="btn-toggle-select-routes" onclick="toggleRouteSelectMode()" title="Toggle selection mode for batch actions">
                    <i class="bi bi-check2-square me-1"></i> <span id="btn-select-routes-text">Select</span>
                </button>
                <?php endif; ?>
            </div>

            <div id="route-count-display" style="font-size: 13.5px; font-weight: 600; color: var(--text-muted, #64748b);">
                Showing <strong style="color: var(--text-main, #0f172a);" id="visible-routes-count"><?= $countActiveGroups ?? 0 ?></strong> route<?= (($countActiveGroups ?? 0) !== 1) ? 's' : '' ?>
            </div>
        </div>
    </div>
</div>

<style>
/* Scoped Selection & Bulk Action Rules - Matches App Action Buttons */
.route-group-checkbox,
:not(.selection-mode-active) .route-group-checkbox {
    display: none !important;
    width: 20px !important;
    height: 20px !important;
    min-width: 20px !important;
    min-height: 20px !important;
    max-width: 20px !important;
    max-height: 20px !important;
    aspect-ratio: 1 / 1 !important;
    flex: 0 0 20px !important;
    flex-shrink: 0 !important;
    cursor: pointer !important;
    border-radius: 5px !important;
    border: 1.5px solid #94a3b8 !important;
    background-color: #ffffff !important;
    -webkit-appearance: none !important;
    appearance: none !important;
}
.selection-mode-active .route-group-checkbox {
    display: inline-block !important;
}
.route-group-checkbox:checked {
    background-color: #2563eb !important;
    border-color: #2563eb !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='m6 10 3 3 6-6'/%3e%3c/svg%3e") !important;
    background-size: 13px 13px !important;
    background-position: center !important;
    background-repeat: no-repeat !important;
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

<?php if (!empty($groupedRoutes)): ?>
    <div class="d-flex flex-column gap-4 fade-in" id="routes-container">
        <?php if ($isAdmin): ?>
        <!-- Integrated Top Bulk Action Bar -->
        <div id="route-bulk-toolbar" class="bulk-action-top-bar" style="display: none;">
            <div class="bulk-bar-info">
                <label class="bulk-select-all-wrap mb-0">
                    <input type="checkbox" id="select-all-routes" class="form-check-input select-all-checkbox m-0" style="width: 18px; height: 18px; cursor: pointer;">
                    <span class="fw-semibold text-dark" style="font-size: 13.5px;">Select All</span>
                </label>
                <span class="bulk-bar-divider"></span>
                <span class="bulk-count-badge">
                    <span id="route-selected-count">0</span> <span id="route-selected-text">selected</span>
                </span>
            </div>
            <div class="bulk-bar-actions">
                <button type="button" class="btn-modern btn-modern-sm btn-action-deactivate btn-bulk-deactivate" id="btn-bulk-deactivate-routes" onclick="openRouteBulkModal('deactivate')" disabled>
                    <i class="bi bi-pause-circle"></i> <span>Deactivate Selected</span>
                </button>
                <button type="button" class="btn-modern btn-modern-sm btn-action-activate btn-bulk-activate" id="btn-bulk-activate-routes" onclick="openRouteBulkModal('activate')" style="display: none;" disabled>
                    <i class="bi bi-check-circle"></i> <span>Activate Selected</span>
                </button>
                <button type="button" class="btn-modern btn-modern-sm btn-action-delete btn-bulk-delete" id="btn-bulk-delete-routes" onclick="openRouteBulkModal('delete')" style="display: none;" disabled>
                    <i class="bi bi-trash"></i> <span>Delete Selected</span>
                </button>
                <button type="button" class="btn-modern btn-modern-sm btn-modern-outline btn-bulk-cancel" onclick="toggleRouteSelectMode(false)">
                    <i class="bi bi-x"></i> <span>Cancel</span>
                </button>
            </div>
        </div>
        <?php endif; ?>
        <?php foreach ($groupedRoutes as $routeKey => $group): ?>
            <?php $grpStatus = $group['status'] ?? 'active'; ?>
            <div class="modern-card shadow-modern mb-0 route-card-item <?= ($grpStatus === 'archived') ? 'route-card-archived' : '' ?>" data-status="<?= esc($grpStatus) ?>" data-destination="<?= esc(strtolower($group['destination'])) ?>" data-terminal="<?= esc(strtolower($group['terminal_name'])) ?>">
                <div class="modern-card-header d-flex justify-content-between align-items-center flex-wrap gap-2 py-3 px-4" style="background: var(--surface-sunken, #f8fafc); border-bottom: 1px solid var(--border, #e2e8f0);">
                    <div class="d-flex align-items-center gap-2">
                        <?php if ($isAdmin): ?>
                            <input type="checkbox" class="form-check-input route-group-checkbox me-1" value="<?= $group['items'][0]['id'] ?>" data-status="<?= esc($grpStatus) ?>" data-origin="<?= esc(strtoupper($group['terminal_name'])) ?>" data-destination="<?= esc(strtoupper($group['destination'])) ?>" title="Select Route" style="display: none; width: 18px; height: 18px; cursor: pointer;">
                        <?php endif; ?>
                        <i class="bi bi-geo-alt-fill text-danger fs-5"></i>
                        <span class="fw-bold fs-5" style="color: var(--text-main);"><?= strtoupper(esc($group['terminal_name'])) ?> <i class="bi bi-arrow-right text-muted mx-1"></i> <?= strtoupper(esc($group['destination'])) ?></span>
                        <?php if ($grpStatus === 'archived'): ?>
                            <span class="badge-modern badge-modern-secondary ms-2" style="background:#f1f5f9; color:#64748b; border:1px solid #cbd5e1; font-size:11px; padding:3px 8px;">
                                <i class="bi bi-archive me-1"></i>Archived
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <?php if ($isAdmin): ?>
                        <div class="d-inline-flex gap-2">
                            <?php if ($grpStatus === 'archived'): ?>
                                <button type="button" class="btn-modern btn-action-activate btn-modern-sm" style="padding: 6px 12px;" title="Activate Route Group"
                                    onclick="showActivateRouteModal({
                                        routeId: '<?= $group['items'][0]['id'] ?>',
                                        origin: '<?= esc(addslashes(strtoupper($group['terminal_name']))) ?>',
                                        destination: '<?= esc(addslashes(strtoupper($group['destination']))) ?>',
                                        typesCount: '<?= count($group['items']) ?>'
                                    })">
                                    <i class="bi bi-check-circle"></i> Activate Route
                                </button>
                                <a href="<?= base_url('admin/routes/edit/'.$group['items'][0]['id']) ?>" class="btn-modern btn-action-edit btn-modern-sm" style="padding: 6px 12px;" title="Edit Route Group">
                                    <i class="bi bi-pencil"></i> Edit Route
                                </a>
                                <button type="button" class="btn-modern btn-modern-sm btn-action-delete" style="padding: 6px 12px;" title="Permanently Delete Route Group"
                                    onclick="showDeleteRouteModal({
                                        formId: 'delete-route-group-form-<?= $group['items'][0]['id'] ?>',
                                        routeId: '<?= $group['items'][0]['id'] ?>',
                                        origin: '<?= esc(addslashes(strtoupper($group['terminal_name']))) ?>',
                                        destination: '<?= esc(addslashes(strtoupper($group['destination']))) ?>',
                                        typesCount: '<?= count($group['items']) ?>'
                                    })">
                                    <i class="bi bi-trash"></i> Delete Route
                                </button>
                                <form id="delete-route-group-form-<?= $group['items'][0]['id'] ?>" action="<?= base_url('admin/routes/delete_group/'.$group['items'][0]['id']) ?>" method="post" class="d-none">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="redirect_tab" value="archived">
                                </form>
                            <?php else: ?>
                                <a href="<?= base_url('admin/routes/edit/'.$group['items'][0]['id']) ?>" class="btn-modern btn-action-edit btn-modern-sm" style="padding: 6px 12px;" title="Edit Route Group">
                                    <i class="bi bi-pencil"></i> Edit Route
                                </a>
                                <button type="button" class="btn-modern btn-action-deactivate btn-modern-sm" style="padding: 6px 12px;" title="Deactivate Route Group"
                                    onclick="showDeactivateRouteModal({
                                        routeId: '<?= $group['items'][0]['id'] ?>',
                                        origin: '<?= esc(addslashes(strtoupper($group['terminal_name']))) ?>',
                                        destination: '<?= esc(addslashes(strtoupper($group['destination']))) ?>',
                                        typesCount: '<?= count($group['items']) ?>'
                                    })">
                                    <i class="bi bi-pause-circle"></i> Deactivate Route
                                </button>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <span class="badge-modern badge-modern-primary px-3 py-1 fs-6">
                            <i class="bi bi-bus-front me-1"></i> <?= count($group['items']) ?> Vehicle Type<?= count($group['items']) > 1 ? 's' : '' ?>
                        </span>
                    </div>
                </div>
                <div class="modern-card-body p-0">
                    <div class="table-responsive">
                        <table class="table-modern mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 50%;">Vehicle Type</th>
                                    <th style="width: 50%;">Fare (PHP)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($group['items'] as $route): ?>
                                    <tr>
                                        <td data-label="Vehicle Type">
                                            <div class="d-flex align-items-center gap-2">
                                                <?= vehicle_type_badge($route['vehicle_type']) ?>
                                            </div>
                                        </td>
                                        <td data-label="Fare (PHP)">
                                            <?php if (isset($route['fare']) && (float)$route['fare'] > 0): ?>
                                                <strong style="font-size: 16px; color: var(--text-main);">₱<?= number_format($route['fare'], 2) ?></strong>
                                            <?php elseif ($grpStatus === 'archived' || ($route['status'] ?? '') === 'archived'): ?>
                                                <span class="text-muted" style="color: var(--text-muted, #94a3b8); font-size: 16px; font-weight: 500;">&mdash;</span>
                                            <?php else: ?>
                                                <a href="<?= base_url('fares?action=add&terminal_id=' . $route['terminal_id'] . '&destination=' . urlencode($route['destination']) . '&vehicle_type=' . urlencode($route['vehicle_type'])) ?>" 
                                                   class="btn-modern btn-modern-sm btn-action-edit d-inline-flex align-items-center gap-1 text-decoration-none" 
                                                   style="padding: 4px 10px; font-size: 12px; border-radius: 6px; font-weight: 600;" 
                                                   title="Set fare for this route in Fare Management">
                                                    <i class="bi bi-tag-fill me-1 text-primary"></i>Set Fare
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <div id="no-routes-match" class="modern-card shadow-modern fade-in text-center py-5 empty-state d-none" style="display: none !important;">
            <div class="py-4 text-muted">
                <i class="bi bi-search fs-1 d-block mb-3 opacity-50"></i>
                <div class="fw-bold fs-6 empty-state-title">No routes match your search query</div>
                <small class="empty-state-subtitle">Try searching with a different origin or destination name.</small>
            </div>
        </div>
    </div>



<!-- Route Bulk Confirmation Modal -->
<div class="modal fade" id="routeBulkConfirmModal" tabindex="-1" aria-labelledby="routeBulkConfirmLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px; margin: 1.75rem auto;">
        <div class="modal-content delete-route-modal-content">
            <div id="routeBulkStripe" style="height: 4px; width: 100%; background: #f59e0b;"></div>
            <form id="routeBulkForm" method="post" action="<?= base_url('admin/routes/bulk-action') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" id="routeBulkActionInput" value="">
                <input type="hidden" name="redirect_tab" id="routeBulkRedirectTab" value="">
                <div id="routeBulkIdsContainer"></div>

                <div class="modal-body text-center p-4">
                    <div id="routeBulkIconBadge" style="width: 68px; height: 68px; margin: 4px auto 18px auto; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; background: #fef3c7; color: #d97706;">
                        <i id="routeBulkIcon" class="bi bi-pause-circle-fill"></i>
                    </div>

                    <h4 class="delete-route-modal-title" id="routeBulkConfirmTitle">Deactivate Selected Routes?</h4>
                    <p class="delete-route-modal-desc" id="routeBulkConfirmDesc">
                        Are you sure you want to deactivate the selected routes?
                    </p>

                    <div id="routeBulkSelectedList" class="p-2 mb-2 text-start" style="max-height: 140px; overflow-y: auto; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px;">
                    </div>
                </div>

                <div class="modal-footer delete-route-modal-footer">
                    <button type="button" class="btn delete-route-btn-cancel" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn" id="routeBulkSubmitBtn" style="flex: 1; font-weight: 600; padding: 10px 18px; border-radius: 10px; border: none; background: #f59e0b; color: #fff;">
                        Confirm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php else: ?>
    <div class="modern-card shadow-modern fade-in text-center py-5 empty-state">
        <div class="py-4 text-muted">
            <i class="bi bi-signpost-split fs-1 d-block mb-3 opacity-50"></i>
            <div class="fw-bold fs-6 empty-state-title">No routes found</div>
            <small class="empty-state-subtitle">Click <strong>Add New Route</strong> above to create your first route.</small>
        </div>
    </div>
<?php endif; ?>

<style>
    .route-search-group {
        display: flex;
        align-items: center;
        position: relative;
        max-width: 480px;
        width: 100%;
    }
    .route-search-group .input-group-text {
        height: 40px;
        background: #f8fafc;
        border: 1.5px solid #cbd5e1;
        border-right: none;
        border-radius: 20px 0 0 20px;
        color: #64748b;
        padding: 0 14px;
        font-size: 14px;
    }
    .route-search-input {
        height: 40px;
        width: 100%;
        padding: 6px 36px 6px 10px;
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
    .route-search-group:focus-within .input-group-text,
    .route-search-group:focus-within .route-search-input {
        border-color: var(--primary, #b71c1c);
        box-shadow: 0 0 0 3px rgba(183, 28, 28, 0.12);
    }
    .btn-clear-search {
        position: absolute;
        right: 12px;
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        transition: color 0.15s ease;
    }
    .btn-clear-search:hover {
        color: #475569;
    }
    .btn-clear-search[style*="display: none"],
    .btn-clear-search[style*="display:none"],
    .btn-clear-search.d-none {
        display: none !important;
    }
    @media (max-width: 768px) {
        .route-search-group {
            max-width: 100% !important;
            width: 100% !important;
        }
    }

    /* Route Filter Tabs */
    .vf-btn {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        padding: 6px 14px !important;
        border-radius: 20px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        font-family: 'Outfit', sans-serif !important;
        cursor: pointer !important;
        border: 2px solid #dee2e6 !important;
        background: #fff !important;
        color: #475569 !important;
        white-space: nowrap !important;
        line-height: 1.4 !important;
        transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease !important;
    }
    .vf-btn:hover {
        border-color: #cbd5e1 !important;
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
        font-size: 12px !important;
        font-weight: 700 !important;
        background: #e2e8f0 !important;
        color: #475569 !important;
    }
    #tab-active-routes.active {
        background: #b71c1c !important;
        border-color: #b71c1c !important;
        color: #fff !important;
        box-shadow: 0 4px 12px rgba(183, 28, 28, 0.25) !important;
    }
    #tab-active-routes.active i {
        color: #fff !important;
    }
    #tab-active-routes.active .vf-count {
        background: rgba(255, 255, 255, 0.25) !important;
        color: #fff !important;
    }
    #tab-archived-routes.active {
        background: #475569 !important;
        border-color: #475569 !important;
        color: #fff !important;
        box-shadow: 0 4px 12px rgba(71, 85, 105, 0.25) !important;
    }
    #tab-archived-routes.active i {
        color: #fff !important;
    }
    #tab-archived-routes.active .vf-count {
        background: rgba(255, 255, 255, 0.25) !important;
        color: #fff !important;
    }
</style>

<script>
    let currentRouteTab = 'active';
    let routeTabInitialized = false;

    function setRouteTab(tab, btn) {
        if (routeTabInitialized && tab === currentRouteTab) return;
        routeTabInitialized = true;
        currentRouteTab = tab;
        document.querySelectorAll('#tab-active-routes, #tab-archived-routes').forEach(b => b.classList.remove('active'));
        if (btn) {
            btn.classList.add('active');
        } else {
            const target = tab === 'archived' ? document.getElementById('tab-archived-routes') : document.getElementById('tab-active-routes');
            if (target) target.classList.add('active');
        }

        // URL tab persistence
        const newUrl = new URL(window.location);
        if (tab === 'active') {
            newUrl.searchParams.delete('tab');
        } else {
            newUrl.searchParams.set('tab', tab);
        }
        window.history.replaceState({}, '', newUrl);

        // Update single modal redirect_tab
        const deactRedir = document.getElementById('deactivateRouteRedirectTab');
        if (deactRedir) deactRedir.value = tab;

        if (isRouteSelectMode) toggleRouteSelectMode(false);
        filterRoutes();
    }

    function toggleRouteClearBtn(val) {
        const btn = document.getElementById('clear-route-search');
        if (btn) {
            if (val && val.trim().length > 0) {
                btn.style.setProperty('display', 'inline-flex', 'important');
            } else {
                btn.style.setProperty('display', 'none', 'important');
            }
        }
    }

    function clearRouteSearch() {
        const input = document.getElementById('route-search');
        if (input) {
            input.value = '';
            toggleRouteClearBtn('');
            input.focus();
        }
        filterRoutes();
    }

    function filterRoutes() {
        const input = document.getElementById('route-search');
        const query = input ? input.value.toLowerCase().trim() : '';
        toggleRouteClearBtn(query);

        const cards = document.querySelectorAll('.route-card-item');
        const noMatch = document.getElementById('no-routes-match');
        const countDisplay = document.getElementById('route-count-display');
        let visibleCount = 0;
        let totalInTab = 0;

        cards.forEach(card => {
            const cardStatus = card.getAttribute('data-status') || 'active';
            const matchesTab = (cardStatus === currentRouteTab);
            if (matchesTab) totalInTab++;

            const matchesQuery = !query || card.textContent.toLowerCase().includes(query);

            if (matchesTab && matchesQuery) {
                if (card.classList.contains('d-none') || card.style.display === 'none') {
                    card.style.removeProperty('display');
                    card.classList.remove('d-none');
                }
                visibleCount++;
            } else if (!card.classList.contains('d-none') || card.style.display !== 'none') {
                card.style.setProperty('display', 'none', 'important');
                card.classList.add('d-none');
            }
        });

        if (noMatch) {
            if (visibleCount === 0) {
                noMatch.style.removeProperty('display');
                noMatch.classList.remove('d-none');
                const titleEl = noMatch.querySelector('.empty-state-title');
                const subEl = noMatch.querySelector('.empty-state-subtitle');
                if (query) {
                    if (titleEl) titleEl.textContent = 'No routes match your search query';
                    if (subEl) subEl.textContent = 'Try searching with a different origin or destination name.';
                } else {
                    if (titleEl) titleEl.textContent = currentRouteTab === 'archived' ? 'No archived routes' : 'No active routes found';
                    if (subEl) subEl.textContent = currentRouteTab === 'archived' ? 'Deactivated routes will appear here.' : 'Click Add New Route above to create a route.';
                }
            } else {
                noMatch.style.setProperty('display', 'none', 'important');
                noMatch.classList.add('d-none');
            }
        }

        if (countDisplay) {
            if (query) {
                countDisplay.innerHTML = `Showing <strong style="color: var(--text-main, #0f172a);">${visibleCount}</strong> of ${totalInTab} ${currentRouteTab} route${totalInTab !== 1 ? 's' : ''}`;
            } else {
                countDisplay.innerHTML = `Total: <strong style="color: var(--text-main, #0f172a);">${totalInTab}</strong> ${currentRouteTab} route${totalInTab !== 1 ? 's' : ''}`;
            }
        }
    }

    // Selection mode state & toggler for routes
    let isRouteSelectMode = false;
    function toggleRouteSelectMode(forceState) {
        if (typeof forceState === 'boolean') {
            isRouteSelectMode = forceState;
        } else {
            isRouteSelectMode = !isRouteSelectMode;
        }
        const container = document.getElementById('routes-container');
        const toolbar = document.getElementById('route-bulk-toolbar');
        const btnText = document.getElementById('btn-select-routes-text');
        const btn = document.getElementById('btn-toggle-select-routes');

        if (isRouteSelectMode) {
            if (container) container.classList.add('selection-mode-active');
            if (toolbar) {
                toolbar.classList.add('is-visible');
                toolbar.style.setProperty('display', 'flex', 'important');
            }
            if (btnText) btnText.textContent = 'Exit Select';
            if (btn) btn.classList.add('active');
            updateRouteBulkToolbar();
        } else {
            if (container) container.classList.remove('selection-mode-active');
            if (toolbar) {
                toolbar.classList.remove('is-visible');
                toolbar.style.setProperty('display', 'none', 'important');
            }
            if (btnText) btnText.textContent = 'Select';
            if (btn) btn.classList.remove('active');
            clearRouteSelection();
        }
    }

    // Bulk selection handlers for routes
    function updateRouteBulkToolbar() {
        const checkedBoxes = Array.from(document.querySelectorAll('.route-group-checkbox:checked'));
        const toolbar = document.getElementById('route-bulk-toolbar');
        const countEl = document.getElementById('route-selected-count');
        const textEl = document.getElementById('route-selected-text');
        const btnDeact = document.getElementById('btn-bulk-deactivate-routes');
        const btnAct = document.getElementById('btn-bulk-activate-routes');
        const btnDel = document.getElementById('btn-bulk-delete-routes');

        if (!toolbar) return;

        const count = checkedBoxes.length;
        if (countEl) countEl.textContent = count;
        if (textEl) textEl.textContent = (count === 1 ? 'route selected' : 'routes selected');

        const hasSelection = count > 0;

        if (currentRouteTab === 'archived') {
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

        const selectAll = document.getElementById('select-all-routes');
        if (selectAll) {
            const visibleCheckboxes = Array.from(document.querySelectorAll('.route-card-item'))
                .filter(c => !c.classList.contains('d-none'))
                .map(c => c.querySelector('.route-group-checkbox'))
                .filter(cb => cb);
            const allChecked = visibleCheckboxes.length > 0 && visibleCheckboxes.every(cb => cb.checked);
            const someChecked = visibleCheckboxes.some(cb => cb.checked) && !allChecked;
            selectAll.checked = allChecked;
            selectAll.indeterminate = someChecked;
        }
    }

    function clearRouteSelection() {
        document.querySelectorAll('.route-group-checkbox').forEach(cb => cb.checked = false);
        const selectAll = document.getElementById('select-all-routes');
        if (selectAll) { selectAll.checked = false; selectAll.indeterminate = false; }
        updateRouteBulkToolbar();
    }

    function openRouteBulkModal(action) {
        const checkedBoxes = Array.from(document.querySelectorAll('.route-group-checkbox:checked'));
        if (checkedBoxes.length === 0) return;

        const modalEl = document.getElementById('routeBulkConfirmModal');
        const actionInput = document.getElementById('routeBulkActionInput');
        const redirectInput = document.getElementById('routeBulkRedirectTab');
        const idsContainer = document.getElementById('routeBulkIdsContainer');
        const titleEl = document.getElementById('routeBulkConfirmTitle');
        const descEl = document.getElementById('routeBulkConfirmDesc');
        const listEl = document.getElementById('routeBulkSelectedList');
        const stripeEl = document.getElementById('routeBulkStripe');
        const iconBadge = document.getElementById('routeBulkIconBadge');
        const iconEl = document.getElementById('routeBulkIcon');
        const submitBtn = document.getElementById('routeBulkSubmitBtn');

        if (actionInput) actionInput.value = action;
        if (redirectInput) redirectInput.value = currentRouteTab;

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
                const orig = cb.getAttribute('data-origin') || '';
                const dest = cb.getAttribute('data-destination') || '';
                return `<span class="badge bg-light text-dark border me-1 mb-1" style="font-size: 13px; font-weight: 600;"><i class="bi bi-geo-alt-fill text-danger me-1"></i>${orig} → ${dest}</span>`;
            }).join(' ');
        }

        const count = checkedBoxes.length;

        if (action === 'deactivate') {
            if (titleEl) titleEl.textContent = `Deactivate ${count} Selected Route Group${count > 1 ? 's' : ''}?`;
            if (descEl) descEl.textContent = `Are you sure you want to deactivate these ${count} route groups? They will be moved to the archive and excluded from active assignments.`;
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
            if (titleEl) titleEl.textContent = `Activate ${count} Selected Route Group${count > 1 ? 's' : ''}?`;
            if (descEl) descEl.textContent = `Are you sure you want to reactivate these ${count} route groups? They will be restored from archive.`;
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
            if (titleEl) titleEl.textContent = `Permanently Delete ${count} Route Group${count > 1 ? 's' : ''}?`;
            if (descEl) descEl.textContent = `Are you sure you want to permanently delete these ${count} route groups and all their associated fares from the system? This action CANNOT be undone.`;
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

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const initialTab = urlParams.get('tab') || 'active';
        const targetBtn = initialTab === 'archived' ? document.getElementById('tab-archived-routes') : document.getElementById('tab-active-routes');
        setRouteTab(initialTab, targetBtn);

        // Select All listener
        const selectAll = document.getElementById('select-all-routes');
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                const isChecked = this.checked;
                const cards = document.querySelectorAll('.route-card-item');
                cards.forEach(card => {
                    if (!card.classList.contains('d-none')) {
                        const cb = card.querySelector('.route-group-checkbox');
                        if (cb) cb.checked = isChecked;
                    }
                });
                updateRouteBulkToolbar();
            });
        }

        // Delegate route checkbox listener
        const container = document.getElementById('routes-container');
        if (container) {
            container.addEventListener('change', function(e) {
                if (e.target && e.target.classList.contains('route-group-checkbox')) {
                    updateRouteBulkToolbar();
                }
            });
        }
    });
</script>

<?php if ($isAdmin && !empty($groupedRoutes)): ?>
<!-- ══════════════════════════════════════════════════ -->
<!--  Deactivate Route Confirmation Modal               -->
<!-- ══════════════════════════════════════════════════ -->
<div class="modal fade" id="deactivateRouteConfirmModal" tabindex="-1" aria-labelledby="deactivateRouteConfirmLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px; margin: 1.75rem auto;">
        <div class="modal-content delete-route-modal-content">
            <!-- Amber accent stripe -->
            <div style="height: 4px; width: 100%; background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);"></div>

            <form id="deactivateRouteForm" method="post" action="">
                <?= csrf_field() ?>
                <input type="hidden" name="redirect_tab" id="deactivateRouteRedirectTab" value="">
                <div class="modal-body text-center p-4">
                    <!-- Icon badge -->
                    <div style="width: 68px; height: 68px; margin: 4px auto 18px auto; border-radius: 50%; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 0 0 8px #fffbeb;">
                        <i class="bi bi-pause-circle-fill"></i>
                    </div>

                    <h4 class="delete-route-modal-title" id="deactivateRouteConfirmLabel">Deactivate Route?</h4>
                    <p class="delete-route-modal-desc">
                        Are you sure you want to deactivate this route group? It will be moved to the archive and excluded from active vehicle assignments.
                    </p>

                    <!-- Route preview chip -->
                    <div class="delete-route-item-chip">
                        <i class="bi bi-geo-alt-fill text-danger"></i>
                        <span id="deactivateRouteLabel" class="fw-bold">ORIGIN → DESTINATION</span>
                        <span class="delete-route-types-tag" id="deactivateRouteTypesTag" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; display:none;"></span>
                    </div>
                </div>

                <div class="modal-footer delete-route-modal-footer">
                    <button type="button" class="btn delete-route-btn-cancel" data-bs-dismiss="modal">
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
<!--  Activate Route Confirmation Modal                 -->
<!-- ══════════════════════════════════════════════════ -->
<div class="modal fade" id="activateRouteConfirmModal" tabindex="-1" aria-labelledby="activateRouteConfirmLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px; margin: 1.75rem auto;">
        <div class="modal-content delete-route-modal-content">
            <!-- Green accent stripe -->
            <div style="height: 4px; width: 100%; background: linear-gradient(90deg, #10b981 0%, #059669 100%);"></div>

            <form id="activateRouteForm" method="post" action="">
                <?= csrf_field() ?>
                <input type="hidden" name="redirect_tab" id="activateRouteRedirectTab" value="archived">
                <div class="modal-body text-center p-4">
                    <!-- Icon badge -->
                    <div style="width: 68px; height: 68px; margin: 4px auto 18px auto; border-radius: 50%; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 0 0 8px #f0fdf4;">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>

                    <h4 class="delete-route-modal-title" id="activateRouteConfirmLabel">Activate Route?</h4>
                    <p class="delete-route-modal-desc">
                        Are you sure you want to reactivate this route group? It will be restored from the archive and become eligible for terminal vehicle assignments.
                    </p>

                    <!-- Route preview chip -->
                    <div class="delete-route-item-chip">
                        <i class="bi bi-geo-alt-fill text-success"></i>
                        <span id="activateRouteLabel" class="fw-bold">ORIGIN → DESTINATION</span>
                        <span class="delete-route-types-tag" id="activateRouteTypesTag" style="background: #d1fae5; color: #047857; display:none;"></span>
                    </div>
                </div>

                <div class="modal-footer delete-route-modal-footer">
                    <button type="button" class="btn delete-route-btn-cancel" data-bs-dismiss="modal">
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
<!--  Delete Route Confirmation Modal (Archive Only)    -->
<!-- ══════════════════════════════════════════════════ -->
<div class="modal fade" id="deleteRouteConfirmModal" tabindex="-1" aria-labelledby="deleteRouteConfirmLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px; margin: 1.75rem auto;">
        <div class="modal-content delete-route-modal-content">
            <!-- Accent stripe -->
            <div class="delete-route-stripe"></div>

            <div class="modal-body text-center p-4">
                <!-- Icon badge -->
                <div class="delete-route-icon-wrapper">
                    <i class="fas fa-trash-can"></i>
                </div>

                <h4 class="delete-route-modal-title" id="deleteRouteConfirmLabel">Permanently Delete Route?</h4>
                <p class="delete-route-modal-desc" id="deleteRouteConfirmMessage">
                    Are you sure you want to permanently delete this route group and all its fares from the archive? This action cannot be undone.
                </p>

                <!-- Route preview chip -->
                <div class="delete-route-item-chip" id="deleteRouteItemChip">
                    <i class="bi bi-geo-alt-fill text-danger"></i>
                    <span id="deleteRouteLabel" class="fw-bold">ORIGIN → DESTINATION</span>
                    <span class="delete-route-types-tag" id="deleteRouteTypesTag" style="display:none;"></span>
                </div>
            </div>

            <div class="modal-footer delete-route-modal-footer">
                <button type="button" class="btn delete-route-btn-cancel" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn delete-route-btn-confirm" id="deleteRouteConfirmBtn">
                    <i class="fas fa-trash-alt me-1"></i> Yes, Delete
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* ── Delete Route Confirmation Modal Styles ── */
.delete-route-modal-content {
    border: 0 !important;
    border-radius: 18px !important;
    box-shadow: 0 20px 35px -5px rgba(15, 23, 42, 0.25), 0 10px 15px -5px rgba(15, 23, 42, 0.1) !important;
    background: #ffffff !important;
    overflow: hidden !important;
    position: relative !important;
    font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif !important;
}

.delete-route-stripe {
    height: 4px;
    width: 100%;
    background: linear-gradient(90deg, #dc2626 0%, #ef4444 50%, #f97316 100%);
}

.delete-route-icon-wrapper {
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

.delete-route-modal-content:hover .delete-route-icon-wrapper {
    transform: scale(1.04);
}

.delete-route-modal-title {
    font-size: 1.35rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 8px;
    letter-spacing: -0.01em;
}

.delete-route-modal-desc {
    color: #64748b;
    font-size: 0.925rem;
    line-height: 1.5;
    margin-bottom: 16px;
    padding: 0 10px;
}

.delete-route-item-chip {
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

.delete-route-item-chip i {
    font-size: 0.95rem;
    flex-shrink: 0;
}

.delete-route-types-tag {
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

.delete-route-modal-footer {
    border: 0 !important;
    padding: 0 24px 24px 24px !important;
    display: flex !important;
    gap: 12px !important;
    justify-content: stretch !important;
    background: transparent !important;
}

.delete-route-btn-cancel {
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

.delete-route-btn-cancel:hover,
.delete-route-btn-cancel:focus {
    background: #e2e8f0 !important;
    color: #0f172a !important;
    border-color: #cbd5e1 !important;
}

.delete-route-btn-confirm {
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

.delete-route-btn-confirm:hover,
.delete-route-btn-confirm:focus {
    background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%) !important;
    box-shadow: 0 6px 16px rgba(220, 38, 38, 0.38) !important;
    transform: translateY(-1px);
}

.delete-route-btn-confirm.is-loading {
    pointer-events: none !important;
    opacity: 0.75 !important;
}

/* Ensure modal paints above fixed navbar */
body.modal-open #deleteRouteConfirmModal,
#deleteRouteConfirmModal {
    z-index: 100050 !important;
}


</style>

<script>
(function() {
    var _deleteRouteFormId = null;

    window.showDeactivateRouteModal = function(opts) {
        opts = opts || {};
        var modalEl = document.getElementById('deactivateRouteConfirmModal');
        var form = document.getElementById('deactivateRouteForm');
        if (form) {
            form.action = '<?= base_url('admin/routes/deactivate_group') ?>/' + opts.routeId;
        }
        var labelEl = document.getElementById('deactivateRouteLabel');
        var typesTagEl = document.getElementById('deactivateRouteTypesTag');
        if (labelEl) {
            labelEl.textContent = (opts.origin || 'ORIGIN') + ' → ' + (opts.destination || 'DESTINATION');
        }
        if (typesTagEl) {
            if (opts.typesCount) {
                typesTagEl.textContent = opts.typesCount + ' Vehicle Type' + (opts.typesCount != 1 ? 's' : '');
                typesTagEl.style.display = '';
            } else {
                typesTagEl.style.display = 'none';
            }
        }
        if (modalEl && window.bootstrap) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    };

    window.showActivateRouteModal = function(opts) {
        opts = opts || {};
        var modalEl = document.getElementById('activateRouteConfirmModal');
        var form = document.getElementById('activateRouteForm');
        if (form) {
            form.action = '<?= base_url('admin/routes/activate_group') ?>/' + opts.routeId;
        }
        var labelEl = document.getElementById('activateRouteLabel');
        var typesTagEl = document.getElementById('activateRouteTypesTag');
        if (labelEl) {
            labelEl.textContent = (opts.origin || 'ORIGIN') + ' → ' + (opts.destination || 'DESTINATION');
        }
        if (typesTagEl) {
            if (opts.typesCount) {
                typesTagEl.textContent = opts.typesCount + ' Vehicle Type' + (opts.typesCount != 1 ? 's' : '');
                typesTagEl.style.display = '';
            } else {
                typesTagEl.style.display = 'none';
            }
        }
        if (modalEl && window.bootstrap) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    };

    window.showDeleteRouteModal = function(opts) {
        opts = opts || {};
        var modalEl = document.getElementById('deleteRouteConfirmModal');
        if (!modalEl || !window.bootstrap) {
            if (window.confirm('Are you sure you want to delete this entire route group and all its fares?')) {
                var form = document.getElementById(opts.formId);
                if (form) form.submit();
            }
            return;
        }

        var labelEl = document.getElementById('deleteRouteLabel');
        var typesTagEl = document.getElementById('deleteRouteTypesTag');

        if (labelEl) {
            var labelText = (opts.origin || 'ORIGIN') + ' → ' + (opts.destination || 'DESTINATION');
            labelEl.textContent = labelText;
        }

        if (typesTagEl) {
            if (opts.typesCount) {
                typesTagEl.textContent = opts.typesCount + ' Vehicle Type' + (opts.typesCount != 1 ? 's' : '');
                typesTagEl.style.display = '';
            } else {
                typesTagEl.style.display = 'none';
            }
        }

        var confirmBtn = document.getElementById('deleteRouteConfirmBtn');
        if (confirmBtn) {
            confirmBtn.classList.remove('is-loading');
            confirmBtn.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Yes, Delete';
        }

        _deleteRouteFormId = opts.formId || null;
        window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
    };

    document.addEventListener('DOMContentLoaded', function() {
        var confirmBtn = document.getElementById('deleteRouteConfirmBtn');
        if (!confirmBtn) return;

        confirmBtn.addEventListener('click', function() {
            if (!_deleteRouteFormId) return;

            confirmBtn.classList.add('is-loading');
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Deleting...';

            var form = document.getElementById(_deleteRouteFormId);
            if (form) {
                form.submit();
            } else {
                var modalEl = document.getElementById('deleteRouteConfirmModal');
                if (modalEl && window.bootstrap) {
                    window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                }
                confirmBtn.classList.remove('is-loading');
                confirmBtn.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Yes, Delete';
            }
        });
    });
})();
</script>
<?php endif; ?>

<?= view('templates/footer') ?>
