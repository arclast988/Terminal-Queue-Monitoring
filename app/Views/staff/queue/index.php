<?= view('templates/header', ['title' => $title]) ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">

<style>
    /* Targeted: keep button/card hover transitions, only kill modal and debug animations */
    .fade,
    .modal,
    .modal-dialog,
    .modal-content {
        animation: none !important;
        transition: none !important;
    }

    /* Targeted transitions for interactive elements */
    .btn, a, .card, .q-card {
        transition: opacity 0.15s ease, background-color 0.15s ease, transform 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
    }

    /* Disable Debug Toolbar Hot Reload Animation */
    #debug-bar .rotate {
        animation: none !important;
    }

    /* CRITICAL: Make depart modal visible and centered */
    [id^="confirmDepartModal"] {
        display: none !important;
    }

    [id^="confirmDepartModal"].fade {
        display: none !important;
        opacity: 0 !important;
    }

    [id^="confirmDepartModal"].fade.show {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        opacity: 1 !important;
        visibility: visible !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        z-index: 1060 !important;
        width: 100% !important;
        height: 100% !important;
        min-height: 100vh !important;
        background: transparent !important;
        backdrop-filter: none !important;
        pointer-events: none !important;
    }

    /* Modal dialog centering */
    [id^="confirmDepartModal"] .modal-dialog {
        position: relative !important;
        margin: auto !important;
        transform: none !important;
        max-height: 90vh !important;
        pointer-events: auto !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
    }

    [id^="confirmDepartModal"] .modal-content {
        background-color: var(--surface, #ffffff) !important;
        border: 1px solid var(--border, #e2e8f0) !important;
        border-radius: 8px !important;
        pointer-events: auto !important;
    }

    [id^="confirmDepartModal"] .modal-body {
        background-color: var(--surface, #ffffff) !important;
        color: var(--text-main, #1e293b) !important;
        pointer-events: auto !important;
    }

    /* Ensure buttons in modal are clickable */
    [id^="confirmDepartModal"] button,
    [id^="confirmDepartModal"] a {
        pointer-events: auto !important;
        cursor: pointer !important;
    }

    /* Add Vehicle to Queue modal */
    #addToQueueModal.fade.show {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        opacity: 1 !important;
        visibility: visible !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        z-index: 1060 !important;
        width: 100% !important;
        height: 100% !important;
        min-height: 100vh !important;
        background: transparent !important;
        backdrop-filter: none !important;
        pointer-events: none !important;
    }

    #addToQueueModal .modal-dialog {
        position: relative !important;
        margin: auto !important;
        transform: none !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        pointer-events: auto !important;
    }

    #addToQueueModal .modal-title {
        color: var(--text-main, #1e293b);
        font-weight: 700;
     }
 
     #addToQueueModal .form-label {
        color: var(--text-main, #1e293b) !important;
        font-weight: 600;
     }
 
     #addToQueueModal .modal-header {
        border-bottom-color: var(--border, #e2e8f0);
     }
 
     #addToQueueModal .modal-footer {
        border-top-color: var(--border, #e2e8f0);
     }
 
     #addToQueueModal .btn-close {
        opacity: 0.8;
     }

    /* Departure Warning Modal Centering */
    #departureWarningModal.fade.show {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        opacity: 1 !important;
        visibility: visible !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        z-index: 1080 !important;
        width: 100% !important;
        height: 100% !important;
        background: rgba(0, 0, 0, 0.1) !important;
        pointer-events: none !important;
    }

    #departureWarningModal .modal-dialog {
        position: relative !important;
        margin: auto !important;
        transform: none !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        pointer-events: auto !important;
    }

    /* Change Driver Modal */
    #changeDriverModal.fade.show {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        opacity: 1 !important;
        visibility: visible !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        z-index: 1060 !important;
        width: 100% !important;
        height: 100% !important;
        min-height: 100vh !important;
        background: rgba(0, 0, 0, 0.4) !important;
        pointer-events: none !important;
    }

    #changeDriverModal .modal-dialog {
        position: relative !important;
        margin: auto !important;
        transform: none !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        pointer-events: auto !important;
    }

    #changeDriverModal .modal-content {
        background-color: var(--surface, #ffffff) !important;
        border: 1px solid var(--border, #e2e8f0) !important;
        border-radius: 8px !important;
        pointer-events: auto !important;
    }

    #changeDriverModal .modal-body {
        background-color: var(--surface, #ffffff) !important;
        color: var(--text-main, #1e293b) !important;
        pointer-events: auto !important;
    }

    #changeDriverModal .modal-title {
        color: var(--text-main, #1e293b);
        font-weight: 700;
    }

    #changeDriverModal .form-label {
        color: var(--text-main, #1e293b) !important;
        font-weight: 600;
    }

    #changeDriverModal .modal-header {
        border-bottom-color: var(--border, #e2e8f0);
    }

    #changeDriverModal .modal-footer {
        border-top-color: var(--border, #e2e8f0);
    }

    #changeDriverModal .btn-close {
        opacity: 0.8;
    }

    /* Modern Queue Card Hover Effects & Transitions */
    .q-card {
        background: var(--surface, #ffffff);
        border-radius: var(--radius-lg, 12px) !important;
        border: 1px solid var(--border, #e2e8f0) !important;
        box-shadow: var(--shadow-sm, 0 1px 3px rgba(16, 24, 40, 0.08)) !important;
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.25s ease;
        overflow: hidden;
        position: relative;
    }
    .q-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--primary, #1565c0);
        z-index: 1;
        transition: opacity 0.25s ease;
    }
    .q-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg, 0 16px 40px -12px rgba(16, 24, 40, 0.2)) !important;
        border-color: var(--primary, #1565c0) !important;
    }
    /* Header hover accent matching theme */
    .q-card-header {
        background-color: var(--surface-sunken, #f1f3f6);
        transition: background-color 0.2s ease;
        position: relative;
    }
    .q-card:hover .q-card-header {
        background-color: var(--primary-soft, #e7f0fb) !important;
    }
    /* Better text contrast inside cards */
    .q-card .text-muted {
        color: var(--text-muted, #475569) !important;
    }
    .q-card .fw-semibold {
        color: var(--text-main, #1e293b);
    }
    .q-card .q-card-header .fw-semibold {
        color: var(--text-main, #1e293b);
        font-weight: 700;
    }
    .q-card .bg-light {
        background-color: var(--surface-sunken, #f1f3f6) !important;
    }
    .q-card .text-primary {
        color: var(--primary, #1565c0) !important;
    }
    /* Rotate / scale type image slightly on card hover */
    .q-card:hover .vehicle-type-icon img {
        transform: scale(1.1) rotate(2deg);
        transition: transform 0.2s ease;
    }
    .vehicle-type-icon img {
        transition: transform 0.2s ease;
    }
    /* Badge size transitions */
    .q-card .badge-modern {
        transition: transform 0.2s ease;
    }
    .q-card:hover .badge-modern {
        transform: scale(1.05);
    }

    /* Passenger Counter Controls */
    .btn-counter {
        width: 36px;
        height: 36px;
        border-radius: 50% !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 18px;
        padding: 0;
        border: none;
        transition: transform 0.2s, background-color 0.2s, box-shadow 0.2s;
    }
    .btn-counter-minus {
        background-color: var(--danger-light, #FEE2E2);
        color: var(--danger-dark, #dc2626);
    }
    .btn-counter-minus:hover {
        background-color: var(--danger-dark, #dc2626);
        color: white;
        transform: scale(1.1);
    }
    .btn-counter-plus {
        background-color: var(--success-light, #D1FAE5);
        color: var(--success-dark, #059669);
    }
    .btn-counter-plus:hover {
        background-color: var(--success-dark, #059669);
        color: white;
        transform: scale(1.1);
    }
    .btn-counter-max {
        border-radius: var(--radius-sm, 6px) !important;
        font-size: 12px;
        font-weight: 800;
        padding: 6px 12px;
        background-color: var(--slate-100, #f1f5f9);
        color: var(--slate-700, #475569);
        border: 1px solid var(--slate-200, #e2e8f0);
        transition: all 0.2s;
    }
    .btn-counter-max:hover {
        background-color: var(--slate-800, #1e293b);
        color: white;
        transform: translateY(-1px);
    }

    /* Custom success button for Boarding Actions */
    .btn-modern-success {
        background: linear-gradient(135deg, var(--success, #10b981) 0%, var(--success-dark, #059669) 100%);
        color: white !important;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
    }
    .btn-modern-success:hover {
        background: linear-gradient(135deg, #34d399 0%, var(--success, #10b981) 100%);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.45);
        transform: translateY(-2px);
    }

    .btn-modern-danger {
        background: var(--danger-light, #FEE2E2);
        color: var(--danger-dark, #dc2626) !important;
        border: 2px solid transparent;
    }
    .btn-modern-danger:hover {
        background: linear-gradient(135deg, var(--danger-dark, #dc2626) 0%, var(--danger, #ef4444) 100%);
        color: white !important;
        transform: translateY(-2px);
    }
    /* Cancel button: red bg with white text */
    .btn-modern-outline.btn-modern-danger {
        background: var(--danger, #dc2626) !important;
        color: #fff !important;
        border: 2px solid var(--danger, #dc2626) !important;
    }
    .btn-modern-outline.btn-modern-danger:hover {
        background: var(--danger-dark, #b91c1c) !important;
        border-color: var(--danger-dark, #b91c1c) !important;
        color: #fff !important;
        transform: translateY(-2px);
    }

    /* Mobile responsive: queue passenger counters and action buttons */
    @media (max-width: 768px) {
        .q-card .btn-counter-minus,
        .q-card .btn-counter-plus {
            width: 44px !important;
            height: 44px !important;
            font-size: 20px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .q-card .row .col-6 {
            text-align: center;
        }

        .q-card .d-flex.gap-2 {
            flex-wrap: wrap;
        }

        .q-card .btn {
            min-height: 44px !important;
        }
    }
</style>

<div class="page-header-modern fade-in">
    <h1 class="page-title-modern">
        <i class="bi bi-clock-history"></i>
        Queue Operations
    </h1>
    <div class="d-flex gap-2">
        <?php if (empty($noRoutesAssigned)): ?>
        <button class="btn-modern btn-modern-primary" data-bs-toggle="modal" data-bs-target="#addToQueueModal">
            <i class="bi bi-plus-lg"></i> Add Vehicle to Queue
        </button>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($noRoutesAssigned)): ?>
    <div class="alert-modern alert-modern-warning fade-in">
        <i class="bi bi-exclamation-triangle-fill alert-modern-icon"></i>
        <div>
            <strong>No Routes Assigned.</strong> You have no routes assigned to your account. Please contact the administrator to assign routes before you can manage the queue.
        </div>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert-modern alert-modern-success fade-in">
        <i class="bi bi-check-circle-fill alert-modern-icon"></i>
        <div><?= esc(session()->getFlashdata('success')) ?></div>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('warning')): ?>
    <div class="alert-modern alert-modern-warning fade-in">
        <i class="bi bi-exclamation-triangle-fill alert-modern-icon"></i>
        <div><strong>Already Queued!</strong> <?= esc(session()->getFlashdata('warning')) ?></div>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert-modern alert-modern-danger fade-in">
        <i class="bi bi-x-circle-fill alert-modern-icon"></i>
        <div><?= esc(session()->getFlashdata('error')) ?></div>
    </div>
<?php endif; ?>

<div class="queue-list" id="queue-list">
    <?php if (!empty($queue) && is_array($queue)): ?>
        <?php foreach ($queue as $item): ?>
            <?php
                $isFull = (int)$item['current_passengers'] >= (int)$item['capacity'];
                $isBoarding = $item['status'] === 'boarding';
                $isWaiting  = $item['status'] === 'waiting';
            ?>
            <div class="q-card mb-3 fade-in" id="card-<?= $item['id'] ?>">

                <!-- Header row: position badge + plate + status -->
                <div class="q-card-header d-flex align-items-center gap-2 px-3 py-2 border-bottom">
                    <span class="badge-modern badge-modern-primary">#<?= $item['position'] ?></span>
                    <?php
                        $vType = $item['vehicle_type'] ?? '';
                        $imgFile = vehicle_type_image($vType);
                    ?>
                    <span class="vehicle-type-icon <?= vehicle_type_class($vType) ?>" style="padding:0.2rem;border-radius:8px;">
                        <img src="<?= base_url('images/' . $imgFile) ?>" alt="<?= vehicle_type_label($vType) ?>" style="height:32px;width:auto;">
                    </span>
                    <span class="fw-semibold flex-grow-1"><?= esc($item['plate_number']) ?>
                        <?= vehicle_type_badge($vType) ?>
                    </span>
                    <?php if ($isWaiting): ?>
                        <span class="badge-modern badge-modern-warning">
                            <i class="bi bi-hourglass-split me-1"></i>Waiting
                        </span>
                    <?php else: ?>
                        <span class="badge-modern badge-modern-success">
                            <i class="bi bi-play-circle-fill me-1"></i>Boarding
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Info grid -->
                <div class="row g-0 px-3 pt-2 pb-1">
                    <div class="col-6 py-1">
                        <div class="text-muted small">Route</div>
                        <div class="fw-semibold small"><?= esc($item['origin']) ?> &rarr; <?= esc($item['destination']) ?></div>
                    </div>
                    <div class="col-6 py-1">
                        <div class="text-muted small">Arrived</div>
                        <div class="fw-semibold small"><?= date('H:i', strtotime($item['arrival_time'])) ?></div>
                    </div>
                    <div class="col-6 py-1">
                        <div class="text-muted small">Est. departure</div>
                        <div class="fw-semibold small text-primary">
                            <?= !empty($item['estimated_departure']) ? date('g:i A', strtotime($item['estimated_departure'])) : 'Waiting' ?>
                        </div>
                    </div>
                    <div class="col-6 py-1">
                        <div class="text-muted small">Capacity</div>
                        <div class="fw-semibold small"><?= $item['capacity'] ?> seats</div>
                    </div>
                </div>

                <!-- Driver row -->
                <div class="mx-3 mb-2 p-2 rounded-2 bg-light d-flex align-items-center gap-2">
                    <span class="text-muted small flex-shrink-0">Driver</span>
                    <span id="driver-name-<?= $item['id'] ?>" class="fw-semibold small flex-grow-1 text-truncate">
                        <i class="bi bi-person-badge me-1"></i><?= esc($item['driver_name'] ?? '—') ?>
                    </span>
                    <button type="button"
                        class="btn-modern btn-modern-outline btn-modern-sm flex-shrink-0"
                        data-action="change-driver"
                        data-id="<?= $item['id'] ?>"
                        data-plate="<?= esc($item['plate_number'], 'attr') ?>"
                        data-driver="<?= esc($item['driver_name'] ?? '', 'attr') ?>"
                        title="Change Driver">
                        <i class="bi bi-person-gear me-1"></i> Change Driver
                    </button>
                </div>

                <!-- Passenger counter -->
                <div class="mx-3 mb-2 p-2 rounded-2 bg-light d-flex align-items-center gap-2">
                    <span class="text-muted small flex-shrink-0">Passengers</span>
                    <div class="d-flex align-items-center gap-2 ms-auto">
                        <button onclick="updatePassengers(<?= $item['id'] ?>, 'decrement')"
                            class="btn-counter btn-counter-minus">
                            &minus;
                        </button>
                        <span id="passenger-count-<?= $item['id'] ?>"
                            class="fw-bold <?= $isFull ? 'text-danger' : '' ?>" style="min-width:64px;text-align:center;font-size:15px;">
                            <?= $item['current_passengers'] ?> / <?= $item['capacity'] ?>
                            <?php if ($isFull): ?><br><span class="badge-modern badge-modern-danger" style="font-size:12px; font-weight:800; padding:2px 8px;">FULL</span><?php endif; ?>
                        </span>
                        <button onclick="updatePassengers(<?= $item['id'] ?>, 'increment')"
                            class="btn-counter btn-counter-plus">
                            &#43;
                        </button>
                        <button onclick="updatePassengers(<?= $item['id'] ?>, 'max')"
                            class="btn-counter-max">MAX</button>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="d-flex gap-2 px-3 pb-3">
                    <?php if ($isWaiting): ?>
                        <button onclick="updateStatus(<?= $item['id'] ?>, 'boarding', null, this)"
                            class="btn-modern btn-modern-primary flex-fill" style="white-space:nowrap;">
                            Start Boarding
                        </button>
                    <?php elseif ($isBoarding): ?>
                        <button type="button" class="btn-modern btn-modern-success flex-fill" style="white-space:nowrap;"
                            data-bs-toggle="modal" data-bs-target="#confirmDepartModal<?= $item['id'] ?>">
                            Depart
                        </button>
                    <?php endif; ?>
                    <button onclick="if(confirm('Cancel this trip?')) updateStatus(<?= $item['id'] ?>, 'canceled', null, this)"
                        class="btn-modern btn-modern-outline btn-modern-danger flex-fill" style="white-space:nowrap;">
                        Cancel
                    </button>
                </div>

            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            Queue is currently empty.
        </div>
    <?php endif; ?>
</div>

<!-- Depart Confirmation Modals - Placed at page level for proper positioning -->
<?php if (!empty($queue) && is_array($queue)): ?>
    <?php foreach ($queue as $item): ?>
        <?php if ($item['status'] == 'boarding'): ?>
            <!-- Depart Confirmation Modal for <?= esc($item['plate_number']) ?> -->
            <div class="modal fade" id="confirmDepartModal<?= $item['id'] ?>" tabindex="-1" aria-labelledby="confirmDepartModalLabel<?= $item['id'] ?>" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content">
                        <div class="modal-body p-4 text-center">
                            <h5 class="fw-bold mb-3" id="confirmDepartModalLabel<?= $item['id'] ?>" style="color: #dc3545;">
                                Confirm Departure
                            </h5>
                            <p class="text-muted mb-3">
                                <strong><?= esc($item['plate_number']) ?></strong>
                            </p>
                            <p class="text-muted small mb-3">
                                This action cannot be undone.
                            </p>
                            <div class="d-flex gap-2 justify-content-center mt-3">
                                <button type="button" class="btn-modern btn-modern-outline btn-modern-sm" data-bs-dismiss="modal">
                                    Cancel
                                </button>
                                <button onclick="updateStatus(<?= $item['id'] ?>, 'departed', 'confirmDepartModal<?= $item['id'] ?>', this)" 
                                   id="confirmDepartBtn<?= $item['id'] ?>" 
                                   data-action="update-status"
                                   data-id="<?= $item['id'] ?>"
                                   data-status="departed"
                                   data-modal-id="confirmDepartModal<?= $item['id'] ?>"
                                   class="btn-modern btn-modern-sm btn-modern-danger">
                                    Depart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

<style>
    .vehicle-select-item {
        cursor: pointer;
        user-select: none;
        border-left: 4px solid transparent !important;
        transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .vehicle-select-item:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        border-color: var(--bs-border-color-translucent, #cbd5e1);
    }
    .vehicle-select-item.is-selected {
        background-color: var(--bs-primary-bg-subtle, #eff6ff) !important;
        border-color: #93c5fd !important;
        border-left-color: #2563eb !important;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.12) !important;
    }
    .vehicle-select-list::-webkit-scrollbar {
        width: 6px;
    }
    .vehicle-select-list::-webkit-scrollbar-thumb {
        background-color: rgba(0, 0, 0, 0.15);
        border-radius: 4px;
    }
</style>

<div class="modal fade" id="addToQueueModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="<?= base_url('staff/queue/add') ?>" method="post" id="addToQueueForm">
            <?= csrf_field() ?>
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header px-4 py-3 bg-body border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center text-white shadow-sm" 
                             style="width: 44px; height: 44px; background: linear-gradient(135deg, #2563eb, #0284c7);">
                            <i class="bi bi-truck-front-fill fs-4"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-body mb-0" style="letter-spacing: -0.01em;">Add Vehicles to Queue</h5>
                            <span class="text-muted small">Select one or more available vehicles to dispatch</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <?php if (empty($vehicles)): ?>
                        <div class="text-center py-5 text-muted">
                            <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex p-3 mb-3 text-success">
                                <i class="bi bi-check-circle-fill fs-1"></i>
                            </div>
                            <h6 class="fw-bold fs-5 text-body">All vehicles are currently queued!</h6>
                            <p class="small text-muted mb-0">There are no unqueued active vehicles assigned to your routes right now.</p>
                        </div>
                    <?php else: ?>
                        <!-- Quick Actions & Search Filter Bar -->
                        <div class="p-2.5 p-sm-3 bg-light rounded-3 mb-3 border">
                            <div class="row g-2 align-items-center">
                                <div class="col-12 col-sm">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-body border-end-0 text-muted ps-3">
                                            <i class="bi bi-search"></i>
                                        </span>
                                        <input type="text" id="vehicleModalSearch" class="form-control bg-body border-start-0 ps-0" placeholder="Search plate number, type, destination..." autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-12 col-sm-auto d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary fw-semibold px-3" id="selectAllVehiclesBtn" style="border-radius: 8px;">
                                        <i class="bi bi-check-all me-1"></i>Select All
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary fw-semibold px-3" id="deselectAllVehiclesBtn" style="border-radius: 8px;">
                                        <i class="bi bi-x-circle me-1"></i>Clear
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Scrollable Vehicle Card List -->
                        <div class="vehicle-select-list pe-1 mb-3" style="max-height: 310px; overflow-y: auto;" id="vehicleListContainer">
                            <?php foreach ($vehicles as $v): ?>
                                <?php
                                    $vType = $v['type'] ?? '';
                                    $imgFile = vehicle_type_image($vType);
                                    $isDeparted = !empty($v['is_departed']);
                                    $departedTime = $v['departed_time'] ?? '';
                                ?>
                                <div class="vehicle-select-item p-3 mb-2 rounded-3 border d-flex align-items-center justify-content-between gap-3 bg-body"
                                     data-search="<?= strtolower(esc($v['plate_number']) . ' ' . esc($v['type']) . ' ' . esc($v['route_destination'] ?? '')) ?>">
                                    <div class="d-flex align-items-center gap-3 flex-grow-1 min-w-0">
                                        <div class="form-check mb-0 flex-shrink-0">
                                            <input class="form-check-input vehicle-checkbox" type="checkbox" name="vehicle_ids[]" value="<?= $v['id'] ?>" id="veh_check_<?= $v['id'] ?>" style="cursor: pointer; width: 1.3em; height: 1.3em;">
                                        </div>
                                        <div class="vehicle-type-icon <?= vehicle_type_class($vType) ?> flex-shrink-0 p-2 rounded-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                            <img src="<?= base_url('images/' . $imgFile) ?>" alt="<?= vehicle_type_label($vType) ?>" style="height: 26px; width: auto;">
                                        </div>
                                        <div class="min-w-0 flex-grow-1">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <span class="fw-bold fs-6 text-body me-1" style="font-family: monospace, sans-serif; letter-spacing: 0.5px;"><?= esc($v['plate_number']) ?></span>
                                                <?= vehicle_type_badge($vType) ?>
                                            </div>
                                            <div class="text-muted small d-flex align-items-center gap-1 text-truncate">
                                                <i class="bi bi-geo-alt-fill text-primary small"></i>
                                                <span class="fw-medium"><?= !empty($v['route_origin']) ? strtoupper(esc($v['route_origin'])) . ' &rarr; ' . strtoupper(esc($v['route_destination'])) : 'No Route' ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0 text-end">
                                        <?php if ($isDeparted): ?>
                                            <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2.5 py-1.5" style="font-size: 11px; font-weight: 600;" title="Currently Departed">
                                                <i class="bi bi-clock-history me-1"></i>DEPARTED <?= esc($departedTime) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1.5" style="font-size: 11px; font-weight: 600;">
                                                <i class="bi bi-check-circle-fill me-1"></i>Ready
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Selection Summary & Info Banner -->
                        <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3 mb-0 border">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge rounded-pill bg-primary px-3 py-2 fs-6 fw-bold shadow-sm" id="selectedBadge">
                                    <i class="bi bi-check2-square me-1"></i> <span id="selectedCountNum">0</span> Selected
                                </span>
                                <span class="text-muted small" id="selectionHintText">Click any card to select</span>
                            </div>
                            <span class="text-muted small d-none d-sm-inline">
                                <i class="bi bi-clock me-1"></i>ETAs calculated automatically
                            </span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="modal-footer px-4 py-3 bg-body border-top">
                    <button type="button" class="btn btn-outline-secondary px-4 fw-semibold" data-bs-dismiss="modal" style="border-radius: 10px;">Cancel</button>
                    <?php if (!empty($vehicles)): ?>
                        <button type="submit" class="btn btn-primary px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-2" id="submitToQueueBtn" style="border-radius: 10px;" disabled>
                            <i class="bi bi-plus-lg"></i> Add Selected Vehicles to Queue
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Change Driver Modal -->
<div class="modal fade" id="changeDriverModal" tabindex="-1" aria-labelledby="changeDriverModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form id="changeDriverForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="changeDriverModalLabel"><i class="bi bi-person-badge me-2"></i>Change Driver</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label-modern">Vehicle</label>
                        <div class="form-control bg-light" id="changeDriverVehicle" style="pointer-events:none;border-radius:var(--radius-md);"></div>
                    </div>
                    <div class="mb-0">
                        <label for="changeDriverInput" class="form-label-modern">Driver Name</label>
                        <input type="text" class="form-control-modern" id="changeDriverInput" name="driver_name"
                            minlength="2" maxlength="100" autocomplete="off" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modern btn-modern-outline btn-modern-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-modern btn-modern-primary btn-modern-sm" id="changeDriverSubmitBtn">
                        <i class="bi bi-check-lg me-1"></i> Save Driver
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Departure Warning Modal -->
<div class="modal fade" id="departureWarningModal" tabindex="-1" aria-hidden="true" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-warning shadow">
            <div class="modal-body p-4 text-center">
                <i class="bi bi-exclamation-triangle text-warning mb-3" style="font-size: 3rem;"></i>
                <h5 class="fw-bold mb-2">Wait a moment!</h5>
                <p class="text-muted small mb-3" id="departureWarningMessage">
                    This vehicle just departed recently.
                </p>
                <button type="button" class="btn btn-warning w-100 fw-semibold" data-bs-dismiss="modal">
                    Understood
                </button>
            </div>
        </div>
    </div>
</div>

<?= view('templates/footer') ?>

<script src="<?= base_url('js/ws-client.js') ?>"></script>
<script src="<?= base_url('js/debounce-passengers.js') ?>"></script>
<script src="<?= base_url('js/queue-sync.js') ?>"></script>
<script>
    // Initialize debounced passenger controls
    PassengerDebounce.init({
        setUrl: '<?= base_url('staff/queue/setPassengers') ?>/{id}'
    });

    function updatePassengers(id, action) {
        var countSpan = document.getElementById('passenger-count-' + id);
        var capacity = 0;
        if (countSpan) {
            var parts = countSpan.textContent.trim().split('/');
            capacity = parseInt(parts[1], 10) || 0;
        }
        PassengerDebounce.adjust(id, action, capacity);
    }

    function updateStatus(id, status, modalId, btn) {
        if (btn) {
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
            btn.classList.add('disabled');
        }

        if (modalId) {
            var modalEl = document.getElementById(modalId);
            if (modalEl) {
                var modal = (typeof bootstrap !== 'undefined') ? (bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl)) : null;
                if (modal) modal.hide();
            }
        }

        var updateUrl = '<?= base_url('staff/queue/update') ?>/' + id + '/' + status;

        var csrfToken = '';
        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (csrfMeta) csrfToken = csrfMeta.getAttribute('content');

        var headers = { 'X-Requested-With': 'XMLHttpRequest' };
        if (csrfToken) headers['X-CSRF-TOKEN'] = csrfToken;

        fetch(updateUrl, { method: 'POST', headers: headers })
        .then(function() {
            window.location.reload();
        })
        .catch(function() {
            window.location.reload();
        });
    }

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('[data-action="update-status"]');
        if (btn) {
            var id = btn.getAttribute('data-id');
            var status = btn.getAttribute('data-status');
            var modalId = btn.getAttribute('data-modal-id');
            if (id && status) {
                updateStatus(id, status, modalId, btn);
            }
        }
    });

    var changeDriverModalEl = document.getElementById('changeDriverModal');
    var changeDriverForm = document.getElementById('changeDriverForm');
    var changeDriverInput = document.getElementById('changeDriverInput');
    var changeDriverVehicle = document.getElementById('changeDriverVehicle');
    var changeDriverQueueId = null;

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('[data-action="change-driver"]');
        if (!btn) return;
        changeDriverQueueId = btn.getAttribute('data-id');
        if (changeDriverVehicle) changeDriverVehicle.textContent = btn.getAttribute('data-plate') || '';
        if (changeDriverInput) {
            changeDriverInput.value = btn.getAttribute('data-driver') || '';
            changeDriverInput.classList.remove('is-invalid');
        }
        if (changeDriverModalEl && typeof bootstrap !== 'undefined') {
            var modal = bootstrap.Modal.getInstance(changeDriverModalEl) || bootstrap.Modal.getOrCreateInstance(changeDriverModalEl);
            if (modal) modal.show();
        }
    });

    if (changeDriverForm) {
        changeDriverForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var driverName = changeDriverInput.value.trim();
            if (driverName.length < 2 || driverName.length > 100) {
                changeDriverInput.classList.add('is-invalid');
                changeDriverInput.focus();
                return;
            }
            changeDriverInput.classList.remove('is-invalid');

            var submitBtn = document.getElementById('changeDriverSubmitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';

            var csrfToken = '';
            var csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (csrfMeta) csrfToken = csrfMeta.getAttribute('content');

            var headers = { 'X-Requested-With': 'XMLHttpRequest' };
            if (csrfToken) headers['X-CSRF-TOKEN'] = csrfToken;

            var body = new URLSearchParams();
            body.append('driver_name', driverName);

            fetch('<?= base_url('staff/queue/updateDriver') ?>/' + changeDriverQueueId, {
                method: 'POST',
                headers: headers,
                body: body
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    if (changeDriverModalEl && typeof bootstrap !== 'undefined') {
                        var modal = bootstrap.Modal.getInstance(changeDriverModalEl);
                        if (modal) modal.hide();
                    }
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to update driver.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Save Driver';
                }
            })
            .catch(function() {
                alert('Network error. Please try again.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Save Driver';
            });
        });
    }

    // Initialize real-time sync (polling + WebSocket)
    QueueSync.init({
        apiUrl:        '<?= base_url('api/queue-status') ?>',
        pollInterval:  3000,
        refreshUrl:    '<?= base_url('staff/queue') ?>',
        tableSelector: 'table tbody',
        modalSelector: '[id^="confirmDepartModal"]'
    });

    // Handle Add to Queue Form — multi-vehicle selection, card click interactivity & availability check
    document.addEventListener('DOMContentLoaded', function() {
        var addForm = document.getElementById('addToQueueForm');
        var addModalEl = document.getElementById('addToQueueModal');
        var warningModalEl = document.getElementById('departureWarningModal');
        var warningMsg = document.getElementById('departureWarningMessage');
        var submitBtn = document.getElementById('submitToQueueBtn');

        var modalSearchInput = document.getElementById('vehicleModalSearch');
        var vehicleItems = document.querySelectorAll('#vehicleListContainer .vehicle-select-item');
        var checkboxes = document.querySelectorAll('.vehicle-checkbox');
        var selectAllBtn = document.getElementById('selectAllVehiclesBtn');
        var deselectAllBtn = document.getElementById('deselectAllVehiclesBtn');
        var selectedCountNum = document.getElementById('selectedCountNum');
        var selectedBadge = document.getElementById('selectedBadge');

        function cleanUpAllModals() {
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
            document.querySelectorAll('.modal-backdrop').forEach(function(b) {
                b.remove();
            });
        }

        function updateCardStyle(card) {
            var cb = card.querySelector('.vehicle-checkbox');
            if (cb && cb.checked) {
                card.classList.add('is-selected');
            } else {
                card.classList.remove('is-selected');
            }
        }

        function updateSelectionCount() {
            var checkedCount = document.querySelectorAll('.vehicle-checkbox:checked').length;
            if (selectedCountNum) selectedCountNum.textContent = checkedCount;
            if (submitBtn) {
                submitBtn.disabled = (checkedCount === 0);
            }
            if (selectedBadge) {
                if (checkedCount > 0) {
                    selectedBadge.className = 'badge rounded-pill bg-primary px-3 py-2 fs-6 fw-bold shadow-sm';
                } else {
                    selectedBadge.className = 'badge rounded-pill bg-secondary bg-opacity-75 px-3 py-2 fs-6 fw-bold';
                }
            }
        }

        // Entire Card Click Handler
        vehicleItems.forEach(function(card) {
            card.addEventListener('click', function(e) {
                var cb = card.querySelector('.vehicle-checkbox');
                if (!cb) return;

                // If click was directly on checkbox input, let default behavior run then sync style
                if (e.target.classList.contains('vehicle-checkbox')) {
                    updateCardStyle(card);
                    updateSelectionCount();
                    return;
                }

                // Toggle checkbox when clicking anywhere on card
                cb.checked = !cb.checked;
                updateCardStyle(card);
                updateSelectionCount();
            });
        });

        checkboxes.forEach(function(cb) {
            cb.addEventListener('change', function() {
                var card = cb.closest('.vehicle-select-item');
                if (card) updateCardStyle(card);
                updateSelectionCount();
            });
        });

        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', function() {
                vehicleItems.forEach(function(item) {
                    if (item.style.display !== 'none') {
                        var cb = item.querySelector('.vehicle-checkbox');
                        if (cb) {
                            cb.checked = true;
                            updateCardStyle(item);
                        }
                    }
                });
                updateSelectionCount();
            });
        }

        if (deselectAllBtn) {
            deselectAllBtn.addEventListener('click', function() {
                checkboxes.forEach(function(cb) {
                    cb.checked = false;
                    var card = cb.closest('.vehicle-select-item');
                    if (card) updateCardStyle(card);
                });
                updateSelectionCount();
            });
        }

        if (modalSearchInput) {
            modalSearchInput.addEventListener('input', function() {
                var query = modalSearchInput.value.trim().toLowerCase();
                vehicleItems.forEach(function(item) {
                    var searchData = item.getAttribute('data-search') || '';
                    if (!query || searchData.indexOf(query) !== -1) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }

        if (warningModalEl) {
            warningModalEl.addEventListener('hidden.bs.modal', function () {
                cleanUpAllModals();
            });

            var understoodBtn = warningModalEl.querySelector('[data-bs-dismiss="modal"]');
            if (understoodBtn) {
                understoodBtn.addEventListener('click', function() {
                    var warningModal = bootstrap.Modal.getInstance(warningModalEl);
                    if (warningModal) warningModal.hide();
                    setTimeout(cleanUpAllModals, 350);
                });
            }
        }

        if (addForm) {
            addForm.addEventListener('submit', function(e) {
                var checkedCbs = Array.from(document.querySelectorAll('.vehicle-checkbox:checked'));
                if (checkedCbs.length === 0) {
                    e.preventDefault();
                    return;
                }

                e.preventDefault();
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Checking availability...';

                var checkedIds = checkedCbs.map(function(cb) { return cb.value; });
                var checkPromises = checkedIds.map(function(id) {
                    return fetch('<?= base_url('api/check-vehicle-availability') ?>/' + id).then(function(r) { return r.json(); });
                });

                Promise.all(checkPromises)
                    .then(function(results) {
                        var unavailable = results.find(function(data) { return data.success && !data.available; });
                        if (unavailable) {
                            if (addModalEl && typeof bootstrap !== 'undefined') {
                                var addModal = bootstrap.Modal.getInstance(addModalEl) || bootstrap.Modal.getOrCreateInstance(addModalEl);
                                if (addModal) addModal.hide();
                            }
                            cleanUpAllModals();

                            setTimeout(function() {
                                warningMsg.textContent = unavailable.message;
                                if (warningModalEl && typeof bootstrap !== 'undefined') {
                                    var warningModal = bootstrap.Modal.getOrCreateInstance(warningModalEl);
                                    warningModal.show();
                                }
                            }, 100);

                            submitBtn.disabled = false;
                            submitBtn.innerHTML = '<i class="bi bi-plus-lg me-1"></i> Add Selected Vehicles to Queue';
                        } else {
                            addForm.submit();
                        }
                    })
                    .catch(function() {
                        addForm.submit();
                    });
            });
        }
    });
</script>
