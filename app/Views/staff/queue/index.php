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

    /* Add Vehicle to Queue modal - Viewport Centered & Height Constrained */
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
        background: rgba(0, 0, 0, 0.4) !important;
        pointer-events: none !important;
    }

    #addToQueueModal .modal-dialog {
        position: relative !important;
        margin: 1rem auto !important;
        transform: none !important;
        display: flex !important;
        flex-direction: column !important;
        max-height: calc(100vh - 2rem) !important;
        pointer-events: auto !important;
        width: 95% !important;
        max-width: 720px !important;
    }

    #addToQueueModal .modal-content {
        max-height: calc(100vh - 2rem) !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
    }

    #addToQueueModal .modal-body {
        overflow-y: auto !important;
        flex: 1 1 auto !important;
        max-height: calc(100vh - 180px) !important;
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
        flex-shrink: 0 !important;
     }
 
     #addToQueueModal .modal-footer {
        border-top-color: var(--border, #e2e8f0);
        flex-shrink: 0 !important;
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

    /* Cancel Trip Modal */
    #cancelTripModal.fade.show {
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

    #cancelTripModal .modal-dialog {
        position: relative !important;
        margin: auto !important;
        transform: none !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        pointer-events: auto !important;
        max-width: 420px !important;
        width: 90% !important;
    }

    #cancelTripModal .modal-content {
        background-color: var(--surface, #ffffff) !important;
        border: 1px solid var(--border, #e2e8f0) !important;
        border-radius: 12px !important;
        pointer-events: auto !important;
    }

    #cancelTripModal .modal-body {
        background-color: var(--surface, #ffffff) !important;
        color: var(--text-main, #1e293b) !important;
        pointer-events: auto !important;
    }

    /* 10-Second Undo Banner */
    .undo-banner {
        position: relative;
        background: #fffbeb !important;
        border: 1.5px solid #fcd34d !important;
        color: #92400e !important;
        border-radius: 12px !important;
        box-shadow: 0 4px 14px rgba(245, 158, 11, 0.12) !important;
        padding: 0.85rem 1.15rem !important;
    }
    .dark .undo-banner,
    [data-bs-theme="dark"] .undo-banner {
        background: rgba(120, 53, 15, 0.25) !important;
        border-color: #b45309 !important;
        color: #fde68a !important;
    }
    .undo-banner .btn-undo-action {
        background: #f59e0b !important;
        border: none !important;
        color: #1c1917 !important;
        font-size: 0.875rem;
        font-weight: 700;
        transition: all 0.2s ease;
        box-shadow: 0 2px 6px rgba(245, 158, 11, 0.35);
        cursor: pointer;
    }
    .undo-banner .btn-undo-action:hover:not(:disabled) {
        background: #d97706 !important;
        color: #ffffff !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(217, 119, 6, 0.4);
    }
    .undo-banner .btn-undo-action:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }
    .undo-progress-bar {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 3.5px;
        width: 100%;
        background: linear-gradient(90deg, #f59e0b, #b45309);
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 12px;
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
        background: var(--primary, #15803d);
        z-index: 1;
        transition: opacity 0.25s ease;
    }
    .q-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg, 0 16px 40px -12px rgba(16, 24, 40, 0.2)) !important;
        border-color: var(--primary, #15803d) !important;
    }
    /* Header hover accent matching theme */
    .q-card-header {
        background-color: var(--surface-sunken, #f1f3f6);
        transition: background-color 0.2s ease;
        position: relative;
    }
    .q-card:hover .q-card-header {
        background-color: var(--primary-soft, #dcfce7) !important;
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
        color: var(--primary, #15803d) !important;
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

    /* Countdown Timer Styles */
    .countdown-timer {
        font-size: 11.5px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 6px;
        display: inline-block;
        letter-spacing: 0.3px;
        margin-top: 2px;
    }
    .countdown-timer.cd-plenty,
    .countdown-timer.cd-green { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
    .countdown-timer.cd-yellow { background: #fef9c3; color: #a16207; border: 1px solid #fef08a; }
    .countdown-timer.cd-soon,
    .countdown-timer.cd-orange { background: #fff3e0; color: #e65100; border: 1px solid #ffe0b2; }
    .countdown-timer.cd-imminent,
    .countdown-timer.cd-red { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; animation: cdPulse 1s infinite; }
    .countdown-timer.cd-passed,
    .countdown-timer.cd-overdue { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; animation: cdPulse 1.2s infinite; }

    @keyframes cdPulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
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

<div id="undoBannerContainer" class="mb-3" style="display:none;"></div>

<div class="queue-list" id="queue-list">
    <?php if (!empty($queue) && is_array($queue)): ?>
        <?php foreach ($queue as $item): ?>
            <?php
                $isFull = (int)$item['current_passengers'] >= (int)$item['capacity'];
                $isBoarding = $item['status'] === 'boarding';
                $isWaiting  = $item['status'] === 'waiting';
            ?>
            <div class="q-card mb-3 fade-in" id="card-<?= $item['id'] ?>" data-destination="<?= esc(strtolower($item['destination'] ?? '')) ?>" data-vehicle-type="<?= esc(strtolower($item['vehicle_type'] ?? '')) ?>">

                <!-- Header row: position badge + plate + status -->
                <div class="q-card-header d-flex align-items-center gap-2 px-3 py-2 border-bottom">
                    <span class="badge-modern badge-modern-primary">#<?= $item['position'] ?></span>
                    <?php
                        $vType = $item['vehicle_type'] ?? '';
                        $imgFile = vehicle_type_image($vType);
                        $itemCol = vehicle_type_color($vType);
                        $itemIco = vehicle_type_icon($vType);
                    ?>
                    <span class="vehicle-type-icon <?= vehicle_type_class($vType) ?>" style="padding:0.2rem;border-radius:8px; background: <?= esc($itemCol) ?>18 !important; border: 1px solid <?= esc($itemCol) ?>44 !important; color: <?= esc($itemCol) ?> !important;">
                        <?php if (!empty($imgFile)): ?>
                            <img src="<?= base_url('images/' . $imgFile) ?>" alt="<?= vehicle_type_label($vType) ?>" style="height:32px;width:auto;">
                        <?php else: ?>
                            <i class="fas <?= esc($itemIco) ?>" style="color: <?= esc($itemCol) ?>; font-size: 16px;"></i>
                        <?php endif; ?>
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
                        <div class="text-muted small text-uppercase">Route</div>
                        <div class="fw-semibold small text-uppercase"><?= strtoupper(esc($item['origin'])) ?> &rarr; <?= strtoupper(esc($item['destination'])) ?></div>
                    </div>
                    <div class="col-6 py-1">
                        <div class="text-muted small">Arrived</div>
                        <div class="fw-semibold small"><?= date('H:i', strtotime($item['arrival_time'])) ?></div>
                    </div>
                    <div class="col-6 py-1">
                        <div class="text-muted small">Est. departure (HH:MM)</div>
                        <div class="fw-semibold small text-primary">
                            <?= !empty($item['estimated_departure']) ? date('H:i', strtotime($item['estimated_departure'])) : 'Waiting' ?>
                        </div>
                        <?php if (!empty($item['estimated_departure']) && $item['status'] === 'boarding'): ?>
                            <div class="countdown-timer" data-departure="<?= date('c', strtotime($item['estimated_departure'])) ?>"></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-6 py-1">
                        <div class="text-muted small">Capacity</div>
                        <div class="fw-semibold small"><?= $item['capacity'] ?> seats</div>
                    </div>
                </div>

                <!-- Operator & Driver row -->
                <div class="mx-3 mb-2 p-2 rounded-2 bg-light d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <?php
                            $opName = $item['operator_name'] ?: ($item['owner_name'] ?? '');
                            $drName = $item['driver_name'] ?? '';
                        ?>
                        <?php if (!empty($opName) && strtolower(trim($opName)) !== strtolower(trim($drName))): ?>
                            <span class="small text-truncate" title="Operator: <?= esc($opName) ?>">
                                <span class="text-muted"><i class="bi bi-building me-1"></i>Operator:</span>
                                <strong><?= esc($opName) ?></strong>
                            </span>
                        <?php endif; ?>
                        <span id="driver-name-<?= $item['id'] ?>" class="small text-truncate" title="Driver: <?= esc($drName) ?>">
                            <span class="text-muted"><i class="bi bi-person-badge me-1"></i>Driver:</span>
                            <strong class="text-dark"><?= esc($drName ?: '—') ?></strong>
                        </span>
                    </div>
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
                            data-capacity="<?= (int)$item['capacity'] ?>"
                            class="fw-bold passenger-count-display" style="min-width:64px;text-align:center;font-size:15px;">
                            <strong class="passenger-count-num <?= passenger_color_class((int)$item['current_passengers'], (int)$item['capacity']) ?>"><?= $item['current_passengers'] ?></strong> / <?= $item['capacity'] ?>
                            <span id="full-badge-<?= $item['id'] ?>" class="full-badge-wrap" style="<?= $isFull ? 'display:block;' : 'display:none;' ?>"><span class="badge-modern badge-modern-danger full-badge" style="font-size:12px; font-weight:800; padding:2px 8px;">FULL</span></span>
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
                    <button type="button"
                        data-action="open-cancel-modal"
                        data-id="<?= $item['id'] ?>"
                        data-plate="<?= esc($item['plate_number'], 'attr') ?>"
                        data-type="<?= esc(vehicle_type_label($item['vehicle_type'] ?? ''), 'attr') ?>"
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

<!-- Cancel Trip Confirmation Modal -->
<div class="modal fade" id="cancelTripModal" tabindex="-1" aria-labelledby="cancelTripModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow-lg">
            <div class="modal-body p-4 text-center">
                <div class="mb-2" style="font-size: 2.5rem; line-height: 1;">🛑</div>
                <h5 class="fw-bold mb-2 text-dark" id="cancelTripModalLabel">
                    🛑 Cancel Trip for <span id="cancelTripVehicleLabel"></span>?
                </h5>
                <p class="text-muted small mb-4" style="line-height: 1.5;">
                    This will remove the vehicle from the active queue and notify waiting passengers.
                </p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn-modern btn-modern-outline btn-modern-sm flex-fill" data-bs-dismiss="modal" id="keepTripBtn">
                        Keep Trip
                    </button>
                    <button type="button" class="btn-modern btn-modern-sm btn-modern-danger flex-fill" id="confirmCancelTripBtn">
                        Yes, Cancel Trip
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Add to Queue Modal Visual Improvements */
    #addToQueueModal .modal-content {
        border-radius: 20px !important;
        box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.25) !important;
        overflow: hidden;
        border: 1px solid var(--border, #cbd5e1) !important;
        max-height: min(92vh, 840px) !important;
        display: flex !important;
        flex-direction: column !important;
    }

    /* Single-scrollbar modal: header/footer stay fixed, ONLY the vehicle
       list scrolls. The body itself must never show a second scrollbar. */
    #addToQueueModal .modal-header,
    #addToQueueModal .modal-footer {
        flex-shrink: 0 !important;
    }

    #addToQueueModal .modal-body {
        overflow-y: hidden !important;
        overflow-x: hidden !important;
        min-height: 0 !important;
        display: flex !important;
        flex-direction: column !important;
    }

    #addToQueueModal .modal-body > div:not(.vehicle-select-list) {
        flex-shrink: 0 !important;
    }

    #addToQueueModal .vehicle-select-list {
        flex: 1 1 auto !important;
        min-height: 90px !important;
    }

    #addToQueueModal .modal-header {
        background: var(--surface, #ffffff);
        border-bottom: 1px solid var(--border, #e2e8f0) !important;
        padding: 1.25rem 1.5rem !important;
    }

    .modal-icon-badge {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #15803d 0%, #16a34a 100%);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(21, 128, 61, 0.3);
        flex-shrink: 0;
    }

    .queue-toolbar-card {
        background: var(--surface-sunken, #f8fafc);
        border: 1px solid var(--border, #e2e8f0);
        border-radius: 12px;
        padding: 0.75rem 1rem;
    }

    .search-input-modern {
        background-color: var(--surface, #ffffff) !important;
        border: 1px solid var(--border-strong, #cbd5e1) !important;
        border-radius: 10px !important;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }
    .input-group > .search-input-modern {
        border-left: 0 !important;
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }
    .input-group > .input-group-text {
        border-right: 0 !important;
        border-color: var(--border-strong, #cbd5e1) !important;
        background-color: var(--surface, #ffffff) !important;
        border-top-left-radius: 10px !important;
        border-bottom-left-radius: 10px !important;
    }
    .search-input-modern:focus {
        border-color: #15803d !important;
        box-shadow: 0 0 0 3px rgba(21, 128, 61, 0.15) !important;
    }

    /* Vehicle item card styling (Custom Flex - No Bootstrap d-flex to allow display toggling) */
    .vehicle-select-item {
        cursor: pointer;
        user-select: none;
        border-radius: 12px !important;
        border: 1.5px solid var(--border, #e2e8f0) !important;
        border-left: 5px solid transparent !important;
        background: var(--surface, #ffffff);
        transition: all 0.18s ease-in-out;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.85rem 1rem;
        margin-bottom: 0.6rem;
    }
    .vehicle-select-item:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
        border-color: var(--border-strong, #cbd5e1) !important;
    }
    .vehicle-select-item.is-selected {
        background: #f0fdf4 !important;
        border-color: #16a34a !important;
        border-left: 5px solid #15803d !important;
        box-shadow: 0 2px 8px rgba(21, 128, 61, 0.12) !important;
    }
    .dark .vehicle-select-item.is-selected,
    [data-bs-theme="dark"] .vehicle-select-item.is-selected {
        background: rgba(20, 83, 45, 0.3) !important;
        border-color: #22c55e !important;
        border-left-color: #4ade80 !important;
    }

    .plate-number-box {
        font-family: monospace, sans-serif;
        font-size: 1.1rem;
        font-weight: 800;
        letter-spacing: 0.5px;
        color: var(--text-main, #0f172a);
    }

    .badge-departed-status {
        background: #e2e8f0;
        color: #334155;
        border: 1px solid #94a3b8;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        white-space: nowrap;
    }
    
    .badge-ready-status {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        white-space: nowrap;
    }

    .vehicle-select-list {
        padding-right: 4px;
    }
    .vehicle-select-list::-webkit-scrollbar {
        width: 6px;
    }
    .vehicle-select-list::-webkit-scrollbar-thumb {
        background-color: rgba(148, 163, 184, 0.5);
        border-radius: 999px;
    }

    .btn-submit-queue {
        background: #15803d;
        border: 1px solid #15803d;
        color: #ffffff;
        font-weight: 700;
        border-radius: 10px;
        padding: 0.6rem 1.3rem;
        box-shadow: 0 2px 6px rgba(21, 128, 61, 0.25);
        transition: all 0.15s ease;
    }
    .btn-submit-queue:hover:not(:disabled) {
        background: #166534;
        border-color: #166534;
        box-shadow: 0 4px 12px rgba(21, 128, 61, 0.35);
        color: #ffffff;
    }
    .btn-submit-queue:disabled {
        background: #cbd5e1;
        border-color: #cbd5e1;
        color: #64748b;
        box-shadow: none;
        cursor: not-allowed;
    }

    @media (max-width: 768px) {
        #addToQueueModal.fade.show {
            padding: 8px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        #addToQueueModal .modal-dialog {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            max-height: 94vh !important;
        }
        #addToQueueModal .modal-content {
            max-height: 94vh !important;
            border-radius: 14px !important;
        }
        #addToQueueModal .modal-header {
            padding: 12px 14px !important;
            gap: 10px !important;
        }
        #addToQueueModal .modal-header > .d-flex {
            min-width: 0 !important;
            flex: 1 1 auto !important;
            gap: 10px !important;
        }
        #addToQueueModal .modal-icon-badge {
            width: 36px !important;
            height: 36px !important;
            border-radius: 10px !important;
        }
        #addToQueueModal .modal-title {
            font-size: 1rem !important;
            line-height: 1.25 !important;
        }
        #addToQueueModal .modal-header .text-muted.small {
            font-size: 0.75rem !important;
            line-height: 1.35 !important;
            display: block !important;
        }
        #addToQueueModal .modal-header .btn-close {
            align-self: flex-start !important;
            flex-shrink: 0 !important;
        }
        #addToQueueModal .modal-body {
            padding: 12px 12px !important;
            max-height: calc(94vh - 130px) !important;
        }
        #addToQueueModal .modal-footer {
            padding: 10px 14px !important;
        }
        /* Toolbar: search on its own row, Select All + Clear as equal halves. */
        #addToQueueModal .queue-toolbar-card .row > [class*="col-"] {
            flex: 0 0 100% !important;
            max-width: 100% !important;
        }
        #addToQueueModal #selectAllVehiclesBtn,
        #addToQueueModal #deselectAllVehiclesBtn {
            flex: 1 1 0 !important;
            justify-content: center !important;
            white-space: nowrap !important;
            padding-left: 8px !important;
            padding-right: 8px !important;
            min-height: 38px !important;
        }
        #addToQueueModal #selectAllVehiclesBtn {
            background: #ecfdf5 !important;
            border: 1px solid #86efac !important;
            color: #15803d !important;
        }
        #addToQueueModal #deselectAllVehiclesBtn {
            background: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
            color: #64748b !important;
        }
        .vehicle-select-list {
            max-height: 48vh !important;
        }
        .vehicle-select-item {
            padding: 10px 10px !important;
        }
        .vehicle-select-item .plate-number-box {
            font-size: 0.95rem !important;
        }
        .vehicle-select-item .badge-departed-status,
        .vehicle-select-item .badge-ready-status {
            font-size: 0.7rem !important;
            padding: 0.2rem 0.5rem !important;
        }
        /* Plate + type badge may wrap; status pill stays pinned right. */
        .vehicle-select-item .d-flex.align-items-center.justify-content-between {
            flex-wrap: wrap !important;
            row-gap: 6px !important;
        }
        .vehicle-select-item .badge-departed-status,
        .vehicle-select-item .badge-ready-status {
            white-space: nowrap !important;
            flex-shrink: 0 !important;
        }
        /* Summary banner: tidy stacked rows. */
        #addToQueueModal .p-3.rounded-3.border.bg-body-tertiary > .d-flex {
            flex-direction: column !important;
            align-items: stretch !important;
        }
        /* Footer actions: equal full-width stacked buttons, primary action first. */
        #addToQueueModal .modal-footer {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 8px !important;
        }
        #addToQueueModal .modal-footer .btn {
            width: 100% !important;
            margin: 0 !important;
            justify-content: center !important;
        }
        #addToQueueModal .modal-footer #submitToQueueBtn {
            order: 1 !important;
        }
        #addToQueueModal .modal-footer [data-bs-dismiss="modal"] {
            order: 2 !important;
        }
    }
</style>

<div class="modal fade" id="addToQueueModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="<?= base_url('staff/queue/add') ?>" method="post" id="addToQueueForm" class="modal-content">
            <?= csrf_field() ?>
            <!-- Header -->
            <div class="modal-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="modal-icon-badge">
                            <i class="bi bi-truck-front-fill fs-5"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="modal-title fw-bold mb-0">Add Vehicles to Queue</h5>
                                <span class="badge rounded-pill fw-bold" style="font-size: 0.7rem; background: #dcfce7; color: #15803d;">BATCH DISPATCH</span>
                            </div>
                            <span class="text-muted small">Select one or more available vehicles to dispatch into the active queue</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-3 p-md-4">
                    <?php if (empty($vehicles)): ?>
                        <div class="text-center py-5">
                            <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex p-3 mb-3 text-success">
                                <i class="bi bi-check-circle-fill fs-1"></i>
                            </div>
                            <h6 class="fw-bold fs-5 text-body">All vehicles are currently queued!</h6>
                            <p class="small text-muted mb-0">There are no unqueued active vehicles assigned to your routes right now.</p>
                        </div>
                    <?php else: ?>
                        <!-- Quick Actions & Search Filter Bar -->
                        <div class="queue-toolbar-card mb-3">
                            <div class="row g-2 align-items-center">
                                <div class="col-12 col-sm">
                                    <div class="input-group input-group-sm position-relative">
                                        <span class="input-group-text bg-body border-end-0 text-muted ps-3">
                                            <i class="bi bi-search"></i>
                                        </span>
                                        <input type="text" id="vehicleModalSearch" class="form-control search-input-modern border-start-0 ps-0 pe-4" placeholder="Search plate number, type, route, driver..." autocomplete="off">
                                        <button type="button" class="btn-clear-search" id="clearVehicleModalSearch" onclick="clearVehicleModalSearch()" style="display: none !important; right: 8px;" title="Clear search">
                                            <i class="bi bi-x-circle-fill"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-auto d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-success fw-bold px-3 d-flex align-items-center gap-1" id="selectAllVehiclesBtn" style="border-radius: 8px;">
                                        <i class="bi bi-check-all fs-6"></i> Select All
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary fw-semibold px-3 d-flex align-items-center gap-1" id="deselectAllVehiclesBtn" style="border-radius: 8px;">
                                        <i class="bi bi-x-circle"></i> Clear
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Scrollable Vehicle Card List -->
                        <div class="vehicle-select-list mb-3" style="max-height: 260px; overflow-y: auto;" id="vehicleListContainer">
                            <?php foreach ($vehicles as $v): ?>
                                <?php
                                    $vType = $v['type'] ?? '';
                                    $imgFile = vehicle_type_image($vType);
                                    $isDeparted = !empty($v['is_departed']);
                                    $departedTime = $v['departed_time'] ?? '';

                                    $searchableText = strtolower(esc(implode(' ', array_filter([
                                        $v['plate_number'] ?? '',
                                        $v['type'] ?? '',
                                        $v['route_origin'] ?? '',
                                        $v['route_destination'] ?? '',
                                        $v['driver_name'] ?? '',
                                        $v['owner_name'] ?? '',
                                        $v['operator_name'] ?? ''
                                    ]))));
                                ?>
                                <div class="vehicle-select-item" data-search="<?= $searchableText ?>">
                                    <div class="d-flex align-items-center gap-3 flex-grow-1 min-w-0">
                                        <div class="form-check mb-0 flex-shrink-0">
                                            <input class="form-check-input vehicle-checkbox" type="checkbox" name="vehicle_ids[]" value="<?= $v['id'] ?>" id="veh_check_<?= $v['id'] ?>" style="cursor: pointer; width: 1.3em; height: 1.3em;">
                                        </div>
                                        <?php
                                            $vCol = vehicle_type_color($vType);
                                            $vIco = vehicle_type_icon($vType);
                                        ?>
                                        <div class="vehicle-type-icon <?= vehicle_type_class($vType) ?> flex-shrink-0 p-2 rounded-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: <?= esc($vCol) ?>18; border: 1px solid <?= esc($vCol) ?>44;">
                                            <?php if (!empty($imgFile)): ?>
                                                <img src="<?= base_url('images/' . $imgFile) ?>" alt="<?= vehicle_type_label($vType) ?>" style="height: 24px; width: auto;">
                                            <?php else: ?>
                                                <i class="fas <?= esc($vIco) ?>" style="color: <?= esc($vCol) ?>; font-size: 16px;"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="min-w-0 flex-grow-1">
                                            <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="plate-number-box"><?= esc($v['plate_number']) ?></span>
                                                    <?= vehicle_type_badge($vType) ?>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <?php if ($isDeparted): ?>
                                                        <span class="badge-departed-status" title="Recently Departed">
                                                            <i class="bi bi-clock-history"></i>
                                                            <span>DEPARTED <?= esc($departedTime) ?></span>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge-ready-status">
                                                            <i class="bi bi-check-circle-fill"></i>
                                                            <span>READY</span>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="text-muted small d-flex align-items-center gap-1 text-truncate">
                                                <i class="bi bi-geo-alt-fill text-primary small"></i>
                                                <span class="fw-semibold text-secondary"><?= !empty($v['route_origin']) ? strtoupper(esc($v['route_origin'])) . ' &rarr; ' . strtoupper(esc($v['route_destination'])) : 'No Route Assigned' ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                    <?php endif; ?>
                </div>

                <div class="modal-footer px-4 py-3 bg-body border-top">
                    <button type="button" class="btn btn-outline-secondary px-4 fw-semibold" data-bs-dismiss="modal" style="border-radius: 10px;">Cancel</button>
                    <?php if (!empty($vehicles)): ?>
                        <button type="submit" class="btn btn-submit-queue d-flex align-items-center gap-2" id="submitToQueueBtn" disabled>
                            <i class="bi bi-plus-circle-fill fs-6"></i> Add Selected Vehicles to Queue
                        </button>
                    <?php endif; ?>
                </div>
        </form>
    </div>
</div>

<!-- Change Driver Modal -->
<div class="modal fade" id="changeDriverModal" tabindex="-1" aria-labelledby="changeDriverModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form id="changeDriverForm" class="modal-content">
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
                <button type="button" class="btn-modern btn-modern-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-modern btn-modern-primary" id="changeDriverSubmitBtn">
                    <i class="bi bi-check-lg me-1"></i>Save Driver
                </button>
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

<script src="<?= base_url('js/ws-client.js?v=20260906') ?>"></script>
<script src="<?= base_url('js/debounce-passengers.js') ?>"></script>
<script src="<?= base_url('js/queue-sync.js?v=20260906') ?>"></script>
<script>
    // Initialize debounced passenger controls
    PassengerDebounce.init({
        setUrl: '<?= base_url('staff/queue/setPassengers') ?>/{id}'
    });

    function updatePassengers(id, action) {
        var countSpan = document.getElementById('passenger-count-' + id);
        var capacity = 0;
        if (countSpan) {
            capacity = parseInt(countSpan.getAttribute('data-capacity'), 10);
            if (!capacity) {
                var parts = countSpan.textContent.trim().split('/');
                capacity = parseInt(parts[1], 10) || 0;
            }
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
        .then(function(response) {
            if (!response.ok) {
                throw new Error('Status update failed');
            }
            return response.json();
        })
        .then(function(data) {
            if (!data.success) {
                throw new Error(data.message || 'Status update failed');
            }
            if (window.QueueSync) {
                window.QueueSync.refresh();
            } else {
                window.location.reload();
            }
        })
        .catch(function(error) {
            console.error('[Queue] Status update error:', error);
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

    function escHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/[&<>"']/g, function(c){
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
        });
    }

    // Cancel Trip Modal & 10-Second Undo Banner Logic
    var cancelTripModalEl = document.getElementById('cancelTripModal');
    var currentCancelQueueId = null;
    var currentCancelPlate = '';
    var currentCancelType = '';

    function openCancelModal(id, plate, type) {
        currentCancelQueueId = id;
        currentCancelPlate = plate;
        currentCancelType = type;

        var fullLabel = (type ? type + ' ' : '') + plate;
        var labelSpan = document.getElementById('cancelTripVehicleLabel');
        if (labelSpan) labelSpan.textContent = fullLabel;

        var titleEl = document.getElementById('cancelTripModalLabel');
        if (titleEl) {
            titleEl.innerHTML = '🛑 Cancel Trip for ' + escHtml(fullLabel) + '?';
        }

        var confirmBtn = document.getElementById('confirmCancelTripBtn');
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = 'Yes, Cancel Trip';
        }

        if (cancelTripModalEl && typeof bootstrap !== 'undefined') {
            var modal = bootstrap.Modal.getInstance(cancelTripModalEl) || bootstrap.Modal.getOrCreateInstance(cancelTripModalEl);
            modal.show();
        }
    }

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('[data-action="open-cancel-modal"]');
        if (btn) {
            var id = btn.getAttribute('data-id');
            var plate = btn.getAttribute('data-plate') || '';
            var type = btn.getAttribute('data-type') || '';
            openCancelModal(id, plate, type);
        }
    });

    var confirmCancelBtn = document.getElementById('confirmCancelTripBtn');
    if (confirmCancelBtn) {
        confirmCancelBtn.addEventListener('click', function() {
            if (!currentCancelQueueId) return;

            confirmCancelBtn.disabled = true;
            confirmCancelBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Canceling...';

            var queueId = currentCancelQueueId;
            var plateNumber = currentCancelPlate;
            var vehicleType = currentCancelType;

            var modal = (typeof bootstrap !== 'undefined' && cancelTripModalEl)
                ? (bootstrap.Modal.getInstance(cancelTripModalEl) || bootstrap.Modal.getOrCreateInstance(cancelTripModalEl))
                : null;
            if (modal) modal.hide();

            var updateUrl = '<?= base_url('staff/queue/update') ?>/' + queueId + '/canceled';

            var csrfToken = '';
            var csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (csrfMeta) csrfToken = csrfMeta.getAttribute('content');

            var headers = { 'X-Requested-With': 'XMLHttpRequest' };
            if (csrfToken) headers['X-CSRF-TOKEN'] = csrfToken;

            fetch(updateUrl, { method: 'POST', headers: headers })
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('Trip cancellation failed');
                }
                return response.json();
            })
            .then(function(data) {
                if (!data.success) {
                    throw new Error(data.message || 'Trip cancellation failed');
                }
                if (window.QueueSync) {
                    window.QueueSync.refresh();
                }
                showUndoBanner(queueId, plateNumber, vehicleType, 10000);
            })
            .catch(function(err) {
                console.error('[Queue] Cancel error:', err);
                alert(err.message || 'Failed to cancel trip.');
                if (window.QueueSync) {
                    window.QueueSync.refresh();
                } else {
                    window.location.reload();
                }
            });
        });
    }

    var _undoTimer = null;
    var _undoInterval = null;

    function hideUndoBanner() {
        if (_undoTimer) { clearTimeout(_undoTimer); _undoTimer = null; }
        if (_undoInterval) { clearInterval(_undoInterval); _undoInterval = null; }
        try { sessionStorage.removeItem('pendingUndoTrip'); } catch(e) {}

        var alertEl = document.getElementById('undoBannerAlert');
        var bannerContainer = document.getElementById('undoBannerContainer');
        if (alertEl) {
            alertEl.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
            alertEl.style.opacity = '0';
            alertEl.style.transform = 'translateY(-8px)';
            setTimeout(function() {
                if (bannerContainer) bannerContainer.style.display = 'none';
            }, 250);
        } else if (bannerContainer) {
            bannerContainer.style.display = 'none';
        }
    }

    function showUndoBanner(queueId, plateNumber, vehicleType, durationMs) {
        durationMs = durationMs || 10000;
        var expiresAt = Date.now() + durationMs;

        try {
            sessionStorage.setItem('pendingUndoTrip', JSON.stringify({
                queueId: queueId,
                plateNumber: plateNumber,
                vehicleType: vehicleType,
                expiresAt: expiresAt
            }));
        } catch(e) {}

        if (_undoTimer) { clearTimeout(_undoTimer); _undoTimer = null; }
        if (_undoInterval) { clearInterval(_undoInterval); _undoInterval = null; }

        var bannerContainer = document.getElementById('undoBannerContainer');
        if (!bannerContainer) return;

        bannerContainer.innerHTML =
            '<div class="undo-banner alert-modern alert-modern-warning fade-in d-flex align-items-center justify-content-between position-relative overflow-hidden" id="undoBannerAlert">' +
                '<div class="d-flex align-items-center gap-2 flex-wrap min-w-0 py-1">' +
                    '<span class="fs-5" style="line-height:1;">⚠️</span>' +
                    '<span class="fw-semibold text-dark">' +
                        'Trip for <strong>' + escHtml(plateNumber) + '</strong> was canceled.' +
                    '</span>' +
                    '<button type="button" class="btn btn-sm btn-warning text-dark fw-bold px-3 py-1 rounded-pill ms-sm-2 shadow-sm btn-undo-action" id="undoTripBtn" data-id="' + queueId + '">' +
                        '<i class="bi bi-arrow-counterclockwise me-1"></i>Click to Undo / Restore <span id="undoSecondsBadge" class="badge bg-dark bg-opacity-25 ms-1">10s</span>' +
                    '</button>' +
                '</div>' +
                '<button type="button" class="btn-close btn-sm ms-2 flex-shrink-0" id="closeUndoBannerBtn" aria-label="Dismiss"></button>' +
                '<div class="undo-progress-bar" id="undoProgressBar"></div>' +
            '</div>';

        bannerContainer.style.display = 'block';

        var progressBar = document.getElementById('undoProgressBar');
        var secondsBadge = document.getElementById('undoSecondsBadge');
        var undoBtn = document.getElementById('undoTripBtn');
        var closeBtn = document.getElementById('closeUndoBannerBtn');

        var initialSec = Math.max(1, Math.ceil(durationMs / 1000));
        if (secondsBadge) secondsBadge.textContent = initialSec + 's';

        if (progressBar) {
            progressBar.style.width = '100%';
            requestAnimationFrame(function() {
                progressBar.style.transition = 'width ' + durationMs + 'ms linear';
                progressBar.style.width = '0%';
            });
        }

        _undoInterval = setInterval(function() {
            var leftMs = expiresAt - Date.now();
            var leftSec = Math.max(0, Math.ceil(leftMs / 1000));
            if (secondsBadge) {
                secondsBadge.textContent = leftSec + 's';
            }
            if (leftSec <= 0) {
                clearInterval(_undoInterval);
                _undoInterval = null;
            }
        }, 300);

        _undoTimer = setTimeout(function() {
            hideUndoBanner();
        }, durationMs);

        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                hideUndoBanner();
            });
        }

        if (undoBtn) {
            undoBtn.addEventListener('click', function() {
                executeUndo(queueId, plateNumber);
            });
        }
    }

    function executeUndo(queueId, plateNumber) {
        if (_undoTimer) { clearTimeout(_undoTimer); _undoTimer = null; }
        if (_undoInterval) { clearInterval(_undoInterval); _undoInterval = null; }

        var undoBtn = document.getElementById('undoTripBtn');
        if (undoBtn) {
            undoBtn.disabled = true;
            undoBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Restoring...';
        }

        var csrfToken = '';
        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (csrfMeta) csrfToken = csrfMeta.getAttribute('content');

        var headers = { 'X-Requested-With': 'XMLHttpRequest' };
        if (csrfToken) headers['X-CSRF-TOKEN'] = csrfToken;

        fetch('<?= base_url('staff/queue/undoCancel') ?>/' + queueId, {
            method: 'POST',
            headers: headers
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            try { sessionStorage.removeItem('pendingUndoTrip'); } catch(e) {}

            if (data.success) {
                var bannerContainer = document.getElementById('undoBannerContainer');
                if (bannerContainer) {
                    bannerContainer.innerHTML =
                        '<div class="alert-modern alert-modern-success fade-in d-flex align-items-center justify-content-between p-3" id="undoSuccessAlert">' +
                            '<div class="d-flex align-items-center gap-2">' +
                                '<i class="bi bi-check-circle-fill fs-5 text-success"></i>' +
                                '<span>Trip for <strong>' + escHtml(plateNumber) + '</strong> was restored to the queue!</span>' +
                            '</div>' +
                            '<button type="button" class="btn-close btn-sm" onclick="this.closest(\'.alert-modern\').remove()"></button>' +
                        '</div>';

                    setTimeout(function() {
                        var alertEl = document.getElementById('undoSuccessAlert');
                        if (alertEl) {
                            alertEl.style.transition = 'opacity 0.35s ease';
                            alertEl.style.opacity = '0';
                            setTimeout(function() {
                                if (bannerContainer) bannerContainer.style.display = 'none';
                            }, 350);
                        }
                    }, 3000);
                }

                if (window.QueueSync) {
                    window.QueueSync.refresh();
                } else {
                    window.location.reload();
                }
            } else {
                alert(data.message || 'Failed to restore trip.');
                hideUndoBanner();
            }
        })
        .catch(function(err) {
            console.error('[Queue] Undo error:', err);
            alert('Network error while restoring trip.');
            hideUndoBanner();
        });
    }

    try {
        var savedUndo = sessionStorage.getItem('pendingUndoTrip');
        if (savedUndo) {
            var parsed = JSON.parse(savedUndo);
            var remaining = parsed.expiresAt - Date.now();
            if (remaining > 500) {
                showUndoBanner(parsed.queueId, parsed.plateNumber, parsed.vehicleType, remaining);
            } else {
                sessionStorage.removeItem('pendingUndoTrip');
            }
        }
    } catch(e) {
        try { sessionStorage.removeItem('pendingUndoTrip'); } catch(err) {}
    }

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
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Save Driver';
                if (data.success) {
                    if (changeDriverModalEl && typeof bootstrap !== 'undefined') {
                        var modal = bootstrap.Modal.getInstance(changeDriverModalEl);
                        if (modal) modal.hide();
                    }
                    var btn = document.querySelector('[data-action="change-driver"][data-id="' + changeDriverQueueId + '"]');
                    if (btn) {
                        btn.setAttribute('data-driver', driverName);
                    }
                    var driverSpan = document.getElementById('driver-name-' + changeDriverQueueId);
                    if (driverSpan) {
                        driverSpan.setAttribute('title', 'Driver: ' + driverName);
                        var strongEl = driverSpan.querySelector('strong');
                        if (strongEl) {
                            strongEl.textContent = driverName;
                        }
                    }
                } else {
                    alert(data.message || 'Failed to update driver.');
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
    // NOTE: this page uses card divs (#queue-list), NOT a <table> — the sync
    // module now refreshes #queue-list + #vehicleListContainer automatically so
    // admin vehicle edits appear instantly without a manual page reload.
    QueueSync.init({
        apiUrl:        '<?= base_url('api/queue-status') ?>',
        pollInterval:  3000,
        refreshUrl:    '<?= base_url('staff/queue') ?>',
        tableSelector: '#queue-list',
        modalSelector: '[id^="confirmDepartModal"]',
        extraRefresh: function() {
            // New queue cards carry fresh countdown targets — repaint immediately.
            updateCountdowns();
        }
    });

    // Re-check the 30-minute departed-vehicle cooldown without requiring a
    // manual page refresh. The server recalculates vehicle availability on
    // each staff queue HTML request.
    setInterval(function() {
        if (!document.hidden && window.QueueSync) {
            window.QueueSync.refresh();
        }
    }, 30000);

    // Countdown timer updater
    function updateCountdowns() {
        var timers = document.querySelectorAll('.countdown-timer[data-departure]');
        var now = new Date();
        timers.forEach(function(el) {
            var dep = new Date(el.dataset.departure);
            var diff = dep - now;
            if (isNaN(dep.getTime())) { el.textContent = ''; return; }

            el.classList.remove('cd-plenty', 'cd-green', 'cd-yellow', 'cd-soon', 'cd-orange', 'cd-imminent', 'cd-red', 'cd-passed', 'cd-overdue');

            if (diff <= 0) {
                var overMin = Math.floor(Math.abs(diff) / 60000);
                if (overMin < 5) {
                    el.textContent = '\u23F1 Departing soon!';
                    el.classList.add('cd-imminent', 'cd-red');
                } else {
                    el.textContent = '\u23F1 ' + overMin + 'm overdue';
                    el.classList.add('cd-passed', 'cd-overdue');
                }
                return;
            }

            var totalSec = Math.floor(diff / 1000);
            var hrs = Math.floor(totalSec / 3600);
            var mins = Math.floor((totalSec % 3600) / 60);
            var secs = totalSec % 60;

            var label = '';
            if (hrs > 0) {
                label = hrs + 'h ' + mins + 'm';
            } else if (mins > 0) {
                label = mins + 'm ' + secs + 's';
            } else {
                label = secs + 's';
            }
            el.textContent = '\u23F1 ' + label;

            if (totalSec <= 120) {
                el.classList.add('cd-imminent', 'cd-red');
            } else if (totalSec <= 300) {
                el.classList.add('cd-soon', 'cd-orange');
            } else if (totalSec <= 600) {
                el.classList.add('cd-yellow');
            } else {
                el.classList.add('cd-plenty', 'cd-green');
            }
        });
    }
    setInterval(updateCountdowns, 1000);
    updateCountdowns();

    // Handle Add to Queue Form — multi-vehicle selection, card click interactivity & availability check
    document.addEventListener('DOMContentLoaded', function() {
        var addForm = document.getElementById('addToQueueForm');
        var addModalEl = document.getElementById('addToQueueModal');
        var warningModalEl = document.getElementById('departureWarningModal');
        var warningMsg = document.getElementById('departureWarningMessage');
        var submitBtn = document.getElementById('submitToQueueBtn');

        var selectAllBtn = document.getElementById('selectAllVehiclesBtn');
        var deselectAllBtn = document.getElementById('deselectAllVehiclesBtn');
        var selectedCountNum = document.getElementById('selectedCountNum');
        var selectedBadge = document.getElementById('selectedBadge');

        // Live helpers — always query fresh DOM so realtime list refreshes
        // (QueueSync replaces #vehicleListContainer innerHTML) keep working.
        function getVehicleItems() {
            return document.querySelectorAll('#vehicleListContainer .vehicle-select-item');
        }
        function getCheckboxes() {
            return document.querySelectorAll('#vehicleListContainer .vehicle-checkbox');
        }
        function getModalSearchInput() {
            return document.getElementById('vehicleModalSearch');
        }
        function getSubmitBtn() {
            return document.getElementById('submitToQueueBtn');
        }

        function cleanUpAllModals() {
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
            document.querySelectorAll('.modal-backdrop').forEach(function(b) {
                b.remove();
            });
        }

        var selectedOrder = [];

        function updateCardStyle(card) {
            var cb = card.querySelector('.vehicle-checkbox');
            if (cb && cb.checked) {
                card.classList.add('is-selected');
            } else {
                card.classList.remove('is-selected');
            }
        }

        function updateSelectionCount() {
            // Keep selectedOrder in sync with checked checkboxes (fresh DOM —
            // realtime refresh may have removed vehicles that are now queued).
            selectedOrder = selectedOrder.filter(function(id) {
                var cb = document.querySelector('#vehicleListContainer .vehicle-checkbox[value="' + id + '"]');
                return cb && cb.checked;
            });

            var checkedCount = selectedOrder.length;
            var curSubmit = getSubmitBtn() || submitBtn;
            if (selectedCountNum) selectedCountNum.textContent = checkedCount;
            if (curSubmit) {
                curSubmit.disabled = (checkedCount === 0);
                if (checkedCount > 0) {
                    curSubmit.innerHTML = '<i class="bi bi-plus-circle-fill fs-6 me-1"></i> Add ' + checkedCount + ' Vehicle' + (checkedCount > 1 ? 's' : '') + ' to Queue';
                } else {
                    curSubmit.innerHTML = '<i class="bi bi-plus-circle-fill fs-6 me-1"></i> Add Selected Vehicles to Queue';
                }
            }
            if (selectedBadge) {
                if (checkedCount > 0) {
                    selectedBadge.className = 'badge rounded-pill bg-primary px-3 py-2 fs-6 fw-bold shadow-sm';
                } else {
                    selectedBadge.className = 'badge rounded-pill bg-secondary bg-opacity-75 px-3 py-2 fs-6 fw-bold';
                }
            }
        }

        // Delegated handlers — survive realtime list replacement (QueueSync
        // swaps #vehicleListContainer innerHTML when admin edits vehicles).
        // Card click toggles its checkbox; checkbox change keeps order in sync.
        function toggleVehicleCard(card, checkboxClicked) {
            var container = document.getElementById('vehicleListContainer');
            if (!container || !card || !container.contains(card)) return;
            var cb = card.querySelector('.vehicle-checkbox');
            if (!cb) return;
            var vId = cb.value;
            if (!checkboxClicked) {
                cb.checked = !cb.checked;
            }
            if (cb.checked) {
                if (selectedOrder.indexOf(vId) === -1) selectedOrder.push(vId);
            } else {
                selectedOrder = selectedOrder.filter(function(id) { return id !== vId; });
            }
            updateCardStyle(card);
            updateSelectionCount();
        }

        document.addEventListener('click', function(e) {
            // Select All / Clear live inside the modal toolbar (may be
            // re-rendered on realtime refresh) — handle via delegation.
            var selAll = e.target.closest('#selectAllVehiclesBtn');
            if (selAll) {
                selectedOrder = [];
                getVehicleItems().forEach(function(item) {
                    if (!item.classList.contains('d-none') && item.style.display !== 'none') {
                        var cb = item.querySelector('.vehicle-checkbox');
                        if (cb) {
                            cb.checked = true;
                            selectedOrder.push(cb.value);
                            updateCardStyle(item);
                        }
                    }
                });
                updateSelectionCount();
                return;
            }
            var clearBtn = e.target.closest('#deselectAllVehiclesBtn');
            if (clearBtn) {
                selectedOrder = [];
                var searchInput = getModalSearchInput();
                if (searchInput) searchInput.value = '';
                getVehicleItems().forEach(function(item) {
                    item.classList.remove('d-none');
                    item.style.setProperty('display', 'flex', 'important');
                });
                getCheckboxes().forEach(function(cb) {
                    cb.checked = false;
                    var card = cb.closest('.vehicle-select-item');
                    if (card) updateCardStyle(card);
                });
                updateSelectionCount();
                return;
            }

            var card = e.target.closest ? e.target.closest('#vehicleListContainer .vehicle-select-item') : null;
            if (!card) return;
            var isCheckbox = e.target.classList && e.target.classList.contains('vehicle-checkbox');
            toggleVehicleCard(card, isCheckbox);
        });

        document.addEventListener('change', function(e) {
            if (e.target && e.target.classList && e.target.classList.contains('vehicle-checkbox')) {
                var container = document.getElementById('vehicleListContainer');
                if (!container || !container.contains(e.target)) return;
                var vId = e.target.value;
                if (e.target.checked) {
                    if (selectedOrder.indexOf(vId) === -1) selectedOrder.push(vId);
                } else {
                    selectedOrder = selectedOrder.filter(function(id) { return id !== vId; });
                }
                var card = e.target.closest('.vehicle-select-item');
                if (card) updateCardStyle(card);
                updateSelectionCount();
            }
        });

        function toggleVehicleModalClear(val) {
            var btn = document.getElementById('clearVehicleModalSearch');
            if (btn) {
                if (val && val.trim().length > 0) {
                    btn.style.setProperty('display', 'inline-flex', 'important');
                } else {
                    btn.style.setProperty('display', 'none', 'important');
                }
            }
        }
        window.clearVehicleModalSearch = function() {
            var input = document.getElementById('vehicleModalSearch');
            if (input) {
                input.value = '';
                toggleVehicleModalClear('');
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.focus();
            }
        };

        document.addEventListener('input', function(e) {
            if (e.target && e.target.id === 'vehicleModalSearch') {
                var query = (e.target.value || '').trim().toLowerCase();
                toggleVehicleModalClear(e.target.value);
                getVehicleItems().forEach(function(item) {
                    var searchData = (item.getAttribute('data-search') || '').toLowerCase();
                    if (!query || searchData.indexOf(query) !== -1) {
                        item.classList.remove('d-none');
                        item.style.setProperty('display', 'flex', 'important');
                    } else {
                        item.classList.add('d-none');
                        item.style.setProperty('display', 'none', 'important');
                    }
                });
            }
        });

        // After a realtime vehicle-list refresh: drop selections for vehicles
        // that disappeared (queued elsewhere / deactivated by admin) and
        // repaint the submit-button count. QueueSync already restored checked
        // boxes + search filter before firing this event.
        document.addEventListener('vehicle-list-refreshed', function() {
            submitBtn = getSubmitBtn() || submitBtn;
            updateSelectionCount();
        });

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
                if (selectedOrder.length === 0) {
                    e.preventDefault();
                    return;
                }

                e.preventDefault();
                var curSubmitBtn = getSubmitBtn() || submitBtn;
                if (curSubmitBtn) {
                    curSubmitBtn.disabled = true;
                    curSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Checking availability...';
                }

                var checkPromises = selectedOrder.map(function(id) {
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

                            submitBtn = getSubmitBtn() || submitBtn;
                            if (submitBtn) submitBtn.disabled = false;
                            updateSelectionCount();
                        } else {
                            // Append vehicle_ids in the EXACT selected order
                            addForm.querySelectorAll('input[name="vehicle_ids[]"]').forEach(function(el) {
                                el.remove();
                            });
                            selectedOrder.forEach(function(id) {
                                var hiddenInput = document.createElement('input');
                                hiddenInput.type = 'hidden';
                                hiddenInput.name = 'vehicle_ids[]';
                                hiddenInput.value = id;
                                addForm.appendChild(hiddenInput);
                            });
                            addForm.submit();
                        }
                    })
                    .catch(function() {
                        addForm.querySelectorAll('input[name="vehicle_ids[]"]').forEach(function(el) {
                            el.remove();
                        });
                        selectedOrder.forEach(function(id) {
                            var hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = 'vehicle_ids[]';
                            hiddenInput.value = id;
                            addForm.appendChild(hiddenInput);
                        });
                        addForm.submit();
                    });
            });
        }
    });
</script>
