<?= view('templates/header', ['title' => $title]) ?>

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

    /* Mobile responsive: queue passenger counters and action buttons */
    @media (max-width: 768px) {
        .q-card .btn-danger.rounded-circle,
        .q-card .btn-success.rounded-circle {
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

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Queue Operations </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <?php if (empty($noRoutesAssigned)): ?>
        <button class="tq-btn tq-btn--primary" data-bs-toggle="modal" data-bs-target="#addToQueueModal">
            <i class="bi bi-plus-lg"></i> Add Vehicle to Queue
        </button>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($noRoutesAssigned)): ?>
    <div class="alert alert-warning" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>No Routes Assigned.</strong> You have no routes assigned to your account. Please contact the administrator to assign routes before you can manage the queue.
    </div>
<?php endif; ?>

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

<?php
    // Presentational bucketing of the SAME $queue by its existing status (no new stages).
    $waitingQueue  = [];
    $boardingQueue = [];
    if (!empty($queue) && is_array($queue)) {
        foreach ($queue as $q) {
            if (($q['status'] ?? '') === 'boarding') {
                $boardingQueue[] = $q;
            } else {
                $waitingQueue[] = $q;
            }
        }
    }
?>
<div class="tq-board" id="queue-list">

    <!-- Stage: In Queue (waiting) -->
    <section class="tq-col tq-col--wait">
        <div class="tq-col__head">
            <h2 class="tq-col__title"><span class="tq-dot"></span> In Queue</h2>
            <span class="tq-col__count"><?= count($waitingQueue) ?></span>
        </div>
        <div class="tq-col__body">
            <?php if (!empty($waitingQueue)): ?>
                <?php foreach ($waitingQueue as $item): ?>
                    <?= view('staff/queue/_queue_card', ['item' => $item]) ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="tq-empty"><i class="bi bi-inbox"></i> No vehicles waiting in queue.</div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Stage: Boarding -->
    <section class="tq-col tq-col--go">
        <div class="tq-col__head">
            <h2 class="tq-col__title"><span class="tq-dot"></span> Boarding</h2>
            <span class="tq-col__count"><?= count($boardingQueue) ?></span>
        </div>
        <div class="tq-col__body">
            <?php if (!empty($boardingQueue)): ?>
                <?php foreach ($boardingQueue as $item): ?>
                    <?= view('staff/queue/_queue_card', ['item' => $item]) ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="tq-empty"><i class="bi bi-hourglass-split"></i> No vehicle is boarding yet.</div>
            <?php endif; ?>
        </div>
    </section>

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
                        <select name="vehicle_id" id="vehicleSelect" class="form-select" required>
                            <option value="" data-route="">-- Choose Vehicle --</option>
                            <?php foreach ($vehicles as $v): ?>
                                <option value="<?= $v['id'] ?>"
                                    data-route="<?= !empty($v['route_origin']) ? strtoupper(esc($v['route_origin'])) . ' → ' . strtoupper(esc($v['route_destination'])) : '' ?>"
                                    data-type="<?= esc($v['type']) ?>">
                                    <?= esc($v['plate_number']) ?> (<?= ucfirst(esc($v['type'])) ?>)<?= !empty($v['route_destination']) ? ' — ' . strtoupper(esc($v['route_destination'])) : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3" id="routeInfoBox" style="display:none;">
                        <label class="form-label">Assigned Route</label>
                        <div class="form-control bg-light" id="routeInfoText" style="pointer-events:none;"></div>
                    </div>
                    <div class="mb-0">
                        <div class="form-text text-muted">
                            <i class="bi bi-clock me-1"></i>Estimated departure will be set automatically based on departure time rules.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger" id="submitToQueueBtn">Add to Queue</button>
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
                var modal = bootstrap.Modal.getInstance(modalEl);
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

    // Initialize real-time sync (polling + WebSocket)
    QueueSync.init({
        apiUrl:        '<?= base_url('api/queue-status') ?>',
        pollInterval:  3000,
        refreshUrl:    '<?= base_url('staff/queue') ?>',
        tableSelector: 'table tbody',
        modalSelector: '[id^="confirmDepartModal"]'
    });

    // Presentational only: mirror the capacity bar to the existing count span.
    // Watches passenger-count text (changed by debounce, poll, or WebSocket) and
    // animates the bar/pop — no data flow change.
    (function () {
        function syncCapBar(span) {
            var cap = span.closest('.tq-cap');
            if (!cap) return;
            var parts = span.textContent.trim().split('/');
            var cur = parseInt(parts[0], 10) || 0;
            var max = parseInt(parts[1], 10) || 1;
            var pct = Math.min(100, (cur / Math.max(1, max)) * 100);
            cap.style.setProperty('--pct', pct + '%');
            cap.setAttribute('data-fill', pct >= 100 ? 'full' : (pct >= 70 ? 'mid' : 'low'));
        }
        document.querySelectorAll('.tq-cap__count').forEach(function (span) {
            syncCapBar(span);
            var obs = new MutationObserver(function () {
                syncCapBar(span);
                span.classList.remove('tq-count-pop');
                void span.offsetWidth;            // restart animation
                span.classList.add('tq-count-pop');
            });
            obs.observe(span, { childList: true, characterData: true, subtree: true });
        });
    })();

    // Handle Add to Queue Form — show route info on vehicle selection
    document.addEventListener('DOMContentLoaded', function() {
        var addForm = document.querySelector('#addToQueueModal form');
        var warningModal = new bootstrap.Modal(document.getElementById('departureWarningModal'));
        var warningMsg = document.getElementById('departureWarningMessage');
        var submitBtn = document.getElementById('submitToQueueBtn');

        var vehicleSelect = document.getElementById('vehicleSelect');
        var routeInfoBox = document.getElementById('routeInfoBox');
        var routeInfoText = document.getElementById('routeInfoText');

        if (vehicleSelect) {
            vehicleSelect.addEventListener('change', function() {
                var selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
                var routeLabel = selectedOption.getAttribute('data-route') || '';
                if (routeLabel) {
                    routeInfoText.textContent = routeLabel;
                    routeInfoBox.style.display = 'block';
                } else {
                    routeInfoBox.style.display = 'none';
                }
            });
        }

        if (addForm) {
            addForm.addEventListener('submit', function(e) {
                var vehicleId = addForm.querySelector('select[name="vehicle_id"]').value;
                if (!vehicleId) return;

                e.preventDefault();
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Checking...';

                fetch('<?= base_url('api/check-vehicle-availability') ?>/' + vehicleId)
                    .then(r => r.json())
                    .then(data => {
                        if (data.success && !data.available) {
                            warningMsg.textContent = data.message;
                            warningModal.show();
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = 'Add to Queue';
                        } else {
                            addForm.submit();
                        }
                    })
                    .catch(() => {
                        addForm.submit();
                    });
            });
        }
    });
</script>
