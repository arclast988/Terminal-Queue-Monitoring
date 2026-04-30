<?= view('templates/header', ['title' => $title]) ?>
    <style>
        
        
        
    </style>

<style>
    /* DISABLE ALL ANIMATIONS AND TRANSITIONS GLOBALLY */
    * {
        animation: none !important;
        transition: none !important;
    }

    /* Disable Bootstrap fade animation */
    .fade {
        animation: none !important;
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
        pointer-events: none !important; /* Allow clicking through the transparent backdrop if needed, but dialog will override */
    }

    /* Modal dialog centering */
    [id^="confirmDepartModal"] .modal-dialog {
        position: relative !important;
        margin: auto !important; /* Let flex centering work with auto margin */
        transform: none !important;
        max-height: 90vh !important;
        pointer-events: auto !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
    }

    [id^="confirmDepartModal"] .modal-content {
        background-color: white !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 8px !important;
        pointer-events: auto !important;
    }

    [id^="confirmDepartModal"] .modal-body {
        background-color: white !important;
        color: #1e293b !important;
        pointer-events: auto !important;
    }

    /* Ensure buttons in modal are clickable */
    [id^="confirmDepartModal"] button,
    [id^="confirmDepartModal"] a {
        pointer-events: auto !important;
        cursor: pointer !important;
    }

    /* Add Vehicle to Queue modal: ensure labels and title are readable (dark on white) */
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
        background: transparent !important; /* Make backdrop transparent */
        backdrop-filter: none !important;
        pointer-events: none !important;
    }

    #addToQueueModal .modal-dialog {
        position: relative !important;
        margin: auto !important; /* Center using auto margin + flex */
        transform: none !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        pointer-events: auto !important;
    }

    #addToQueueModal .modal-title {
        color: #1e293b;
        font-weight: 700;
    }

    #addToQueueModal .form-label {
        color: #1e293b !important;
        font-weight: 600;
    }

    #addToQueueModal .modal-header {
        border-bottom-color: #e2e8f0;
    }

    #addToQueueModal .modal-footer {
        border-top-color: #e2e8f0;
    }

    #addToQueueModal .btn-close {
        filter: none;
        opacity: 0.8;
    }


</style>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Queue Operations </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#addToQueueModal">
            <i class="bi bi-plus-lg me-1"></i> Add Vehicle to Queue
        </button>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <?= esc(session()->getFlashdata('success')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('warning')): ?>
    <div class="alert alert-warning alert-dismissible" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Already Queued!</strong> <?= esc(session()->getFlashdata('warning')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible" role="alert">
        <i class="bi bi-x-circle-fill me-2"></i>
        <?= esc(session()->getFlashdata('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card border-0">
    <div class="card-body p-3">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Pos</th>
                        <th>Vehicle</th>
                        <th>Route (Origin - Dest)</th>
                        <th>Passengers</th>
                        <th>Arrival / Est. Departure</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($queue) && is_array($queue)): ?>
                        <?php foreach ($queue as $item): ?>
                            <tr>
                                <td class="fw-bold text-center">#<?= $item['position'] ?></td>
                                <td class="fw-bold">
                                    <div class="d-flex align-items-center gap-2">
                                        <?php 
                                            $imgMap = ['van' => 'van.png', 'jeepney' => 'jeep.png', 'minibus' => 'minibus.png'];
                                            $imgFile = $imgMap[$item['vehicle_type']] ?? 'van.png';
                                        ?>
                                        <img src="<?= base_url('images/' . $imgFile) ?>" alt="<?= ucfirst($item['vehicle_type']) ?>" style="height:45px; width:auto;">
                                        <div>
                                            <?= esc($item['plate_number']) ?>
                                            <br><small class="text-muted">Cap: <?= $item['capacity'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?= esc($item['origin']) ?> - <?= esc($item['destination']) ?></td>
                                <td class="text-center text-nowrap"> <!-- Prevent wrapping -->
                                    <div class="d-flex align-items-center justify-content-center gap-1"> <!-- Tighter gap -->
                                        <button onclick="updatePassengers(<?= $item['id'] ?>, 'decrement')"
                                            class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-dash-lg"></i>
                                        </button>
                                        <span id="passenger-count-<?= $item['id'] ?>"
                                            class="fw-bold fs-5 <?= (int)$item['current_passengers'] >= (int)$item['capacity'] ? 'text-danger' : '' ?>" style="min-width: 55px;">
                                            <?= $item['current_passengers'] ?> / <?= $item['capacity'] ?>
                                        </span>
                                        <button onclick="updatePassengers(<?= $item['id'] ?>, 'increment')"
                                            class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                        <button onclick="updatePassengers(<?= $item['id'] ?>, 'max')"
                                            class="btn btn-sm btn-primary" title="Set to maximum capacity">
                                            MAX
                                        </button>
                                    </div>
                                    <span id="full-badge-<?= $item['id'] ?>" class="badge bg-danger mt-1" style="<?= (int)$item['current_passengers'] >= (int)$item['capacity'] ? '' : 'display:none;' ?>">FULL</span>
                                </td>
                                <td>
                                    <small class="text-muted d-block">Arr: <?= date('h:i A', strtotime($item['arrival_time'])) ?></small>
                                    <span class="fw-bold text-primary">Est: <?= !empty($item['estimated_departure']) ? date('h:i A', strtotime($item['estimated_departure'])) : 'N/A' ?></span>
                                </td>
                                <td>
                                    <?php if ($item['status'] == 'waiting'): ?>
                                        <span class="badge bg-warning text-dark">Waiting</span>
                                    <?php elseif ($item['status'] == 'boarding'): ?>
                                        <span class="badge bg-info text-dark">Boarding</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-nowrap"> <!-- Prevent button stacking -->
                                    <?php if ($item['status'] == 'waiting'): ?>
                                        <button onclick="updateStatus(<?= $item['id'] ?>, 'boarding', null, this)"
                                            class="btn btn-sm btn-primary px-3">
                                            Start Boarding
                                        </button>
                                    <?php elseif ($item['status'] == 'boarding'): ?>
                                        <button type="button" class="btn btn-sm btn-success px-3" data-bs-toggle="modal" data-bs-target="#confirmDepartModal<?= $item['id'] ?>">
                                            Depart
                                        </button>
                                    <?php endif; ?>

                                    <button onclick="if(confirm('Cancel this trip?')) updateStatus(<?= $item['id'] ?>, 'canceled', null, this)"
                                        class="btn btn-sm btn-danger px-2 ms-1">
                                        Cancel
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Queue is currently empty.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
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
                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                                    Cancel
                                </button>
                                <button onclick="updateStatus(<?= $item['id'] ?>, 'departed', 'confirmDepartModal<?= $item['id'] ?>')" 
                                   id="confirmDepartBtn<?= $item['id'] ?>" 
                                   class="btn btn-sm btn-danger">
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

<!-- Add to Queue Modal -->
<div class="modal fade" id="addToQueueModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= base_url('staff/queue/add') ?>" method="post">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Vehicle to Queue</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Vehicle</label>
                        <select name="vehicle_id" class="form-select" required>
                            <option value="">-- Choose Vehicle --</option>
                            <?php foreach ($vehicles as $v): ?>
                                <option value="<?= $v['id'] ?>"><?= esc($v['plate_number']) ?> (<?= ucfirst(esc($v['type'])) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Route</label>
                        <select name="route_id" class="form-select" required>
                            <option value="">-- Choose Route --</option>
                            <?php foreach ($routes as $r): ?>
                                <option value="<?= $r['id'] ?>"><?= esc($r['destination']) ?> (<?= esc($r['origin']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Departure In (Wait Time)</label>
                        <select name="wait_minutes" class="form-select" required>
                            <option value="30">30 minutes</option>
                            <option value="40">40 minutes</option>
                            <option value="60">60 minutes</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Add to Queue</button>
                </div>
            </div>
        </form>
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
                var modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }
        }

        var updateUrl = '<?= base_url('staff/queue/update') ?>/' + id + '/' + status;

        // Read CSRF token from meta tag
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

    // Initialize real-time sync (polling + WebSocket)
    QueueSync.init({
        apiUrl:        '<?= base_url('api/queue-status') ?>',
        pollInterval:  3000,
        refreshUrl:    '<?= base_url('staff/queue') ?>',
        tableSelector: 'table tbody',
        modalSelector: '[id^="confirmDepartModal"]'
    });
</script>











