<?= $this->include('templates/header') ?>


<style>
    /* Premium Hover & Transitions for Table Rows */
    .table-modern tbody tr {
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }
    /* Touch browsers can keep :hover after a tap; reserve this effect for a mouse. */
    @media (hover: hover) and (pointer: fine) {
        .table-modern tbody tr:hover {
            background-color: var(--primary-soft, #f1f5f9) !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
            z-index: 2;
        }
        .table-modern tbody tr:hover td:first-child {
            border-left: 3px solid var(--primary, #1565c0) !important;
        }
        .table-modern tbody tr:hover .badge-modern {
            transform: scale(1.05);
        }
        .table-modern tbody tr:hover .vehicle-type-icon img {
            transform: scale(1.1) rotate(2deg);
        }
    }
    .vehicle-type-icon img, .badge-modern {
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .sched-dep-cell {
        display: inline-flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
        min-width: 112px;
    }
    .sched-passengers-text {
        display: inline-flex;
        align-items: baseline;
        margin-top: 0;
        line-height: 1.25;
        white-space: nowrap;
    }
    .plate-number {
        font-weight: 700;
        font-family: 'Courier New', monospace;
        font-size: 14px;
        background: #f1f5f9;
        color: #1e293b;
        padding: 4px 10px;
        border-radius: 6px;
        display: inline-block;
        letter-spacing: 0.5px;
    }

    /* Route Filter Capsule Bar (matching screenshot) */
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
        border-color: var(--primary, #1565C0);
        color: var(--primary, #1565C0);
        background: var(--primary-soft, rgba(21, 101, 192, 0.06));
        transform: translateY(-1px);
    }
    .route-chip.active {
        background: var(--primary, #1565C0) !important;
        border-color: var(--primary, #1565C0) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.18);
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
        color: #ffffff;
    }

    body.admin-theme .route-chip:hover {
        border-color: var(--primary-red, #C62828);
        color: var(--primary-red, #C62828);
        background: rgba(198, 40, 40, 0.06);
    }
    body.admin-theme .route-chip.active {
        background: var(--primary-red, #C62828) !important;
        border-color: var(--primary-red, #C62828) !important;
        box-shadow: 0 4px 12px rgba(198, 40, 40, 0.25);
    }
    body.staff-theme .route-chip:hover {
        border-color: var(--primary, #15803d);
        color: var(--primary, #15803d);
        background: var(--primary-soft, rgba(21, 128, 61, 0.06));
    }
    body.staff-theme .route-chip.active {
        background: var(--primary, #15803d) !important;
        border-color: var(--primary, #15803d) !important;
        box-shadow: 0 4px 12px rgba(21, 128, 61, 0.25);
    }

    @media (max-width: 480px) {
        .route-filter-wrapper {
            padding: 12px 10px !important;
            border-radius: 12px !important;
            margin-bottom: 14px !important;
        }
        .route-filter-label {
            font-size: 11px !important;
            margin-bottom: 6px !important;
        }
        .route-filter-bar {
            gap: 6px !important;
            padding: 2px 2px 6px 2px !important;
            flex-wrap: nowrap !important;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
        }
        .route-chip {
            padding: 6px 12px !important;
            font-size: 11.5px !important;
            border-radius: 20px !important;
            gap: 5px !important;
            flex-shrink: 0 !important;
        }
        .chip-count {
            font-size: 10px !important;
            min-width: 18px !important;
            height: 18px !important;
            padding: 0 4px !important;
        }
    }
    @keyframes countPulse {
        0% { transform: scale(1); color: inherit; }
        50% { transform: scale(1.35); color: #2563eb; font-weight: 800; }
        100% { transform: scale(1); color: inherit; }
    }
    .passenger-count-num.pulse-update {
        display: inline-block;
        animation: countPulse 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
</style>

<div class="page-header-modern fade-in">
    <h1 class="page-title-modern">
        <i class="bi bi-calendar3"></i>
        Vehicle Schedules
    </h1>
</div>

<!-- Modern Stat Cards block -->
<div class="row mb-4">
    <div class="col-12 col-md-6 mb-4">
        <div class="stat-card-modern fade-in">
            <div class="stat-card-icon" style="background: var(--primary-soft, rgba(21, 101, 192, 0.08)); color: var(--primary, #1565c0);">
                <i class="bi bi-bus-front"></i>
            </div>
            <div class="stat-card-value" id="statTodayDepartures"><?= count($schedules) ?></div>
            <div class="stat-card-label">Today's Departures</div>
        </div>
    </div>
    <div class="col-12 col-md-6 mb-4">
        <div class="stat-card-modern fade-in">
            <div class="stat-card-icon" style="background: var(--primary-soft, rgba(21, 101, 192, 0.08)); color: var(--primary, #1565c0);">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="stat-card-value"><?= strtoupper(date('D, M j, Y')) ?></div>
            <div class="stat-card-label">Schedule Date</div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="modern-card shadow-modern fade-in mb-4">
    <div class="modern-card-body">
        <form method="get" action="<?= base_url('schedules') ?>" class="row g-2 g-md-3 align-items-end" id="sharedFilterForm">
            <div class="col-12 col-md-4">
                <label class="form-label-modern" style="color: #1e293b; font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Vehicle Type</label>
                <select name="type" id="sharedTypeSelect" class="form-select-modern">
                    <option value="">All Types</option>
                    <?php foreach (($vehicleTypes ?? []) as $vehicleType): ?>
                        <option value="<?= esc($vehicleType['slug']) ?>"
                                data-icon="<?= esc(!empty($vehicleType['icon']) ? $vehicleType['icon'] : vehicle_type_icon($vehicleType['slug'])) ?>"
                                data-color="<?= esc(!empty($vehicleType['color']) ? $vehicleType['color'] : vehicle_type_color($vehicleType['slug'])) ?>"
                                <?= $vehicle_type === $vehicleType['slug'] ? 'selected' : '' ?>><?= esc($vehicleType['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-5">
                <label class="form-label-modern" style="color: #1e293b; font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Destination</label>
                <select name="destination" id="sharedDestSelect" class="form-select-modern">
                    <option value="">All Destinations</option>
                    <?php foreach ($all_destinations as $dest): ?>
                        <option value="<?= esc($dest) ?>" <?= $destination == $dest ? 'selected' : '' ?>><?= esc($dest) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <button type="submit" class="btn-modern btn-modern-primary w-100" style="min-height: 42px;">
                    <i class="bi bi-funnel-fill"></i> Apply Filters
                </button>
            </div>
        </form>
        <?php if ($vehicle_type || $destination): ?>
            <div class="mt-3 pt-3 border-top d-flex gap-2 align-items-center flex-wrap">
                <span style="font-size:13px; font-weight:600; color:#64748b; text-transform:uppercase;">Active Filters:</span>
                <?php if ($vehicle_type): ?>
                    <?= vehicle_type_badge($vehicle_type) ?>
                <?php endif; ?>
                <?php if ($destination): ?>
                    <span class="badge-modern badge-modern-info"><?= strtoupper(esc($destination)) ?></span>
                <?php endif; ?>
                <a href="<?= base_url('schedules') ?>" class="btn-modern btn-modern-sm btn-modern-outline ms-2">
                    <i class="bi bi-x-circle"></i> Clear Filters
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Route Filter Bar (Pill Chips) placed UNDER the filter inputs -->
<div class="route-filter-wrapper fade-in">
    <div class="route-filter-label">
        <i class="bi bi-geo-alt-fill" style="color: var(--primary, #1565C0);"></i> Route Destinations:
    </div>
    <div class="route-filter-bar" id="sharedRouteFilterBar">
        <button type="button" class="route-chip <?= empty($destination) ? 'active' : '' ?>" data-dest="all" onclick="filterSharedSchedules('all', this)">
            All Routes
            <span class="chip-count" id="count-all-dest"><?= (int)($total_active_count ?? count($schedules)) ?></span>
        </button>
        <?php foreach (($active_dest_counts ?? []) as $dest => $cnt): ?>
            <button type="button" class="route-chip <?= (strcasecmp($destination ?? '', $dest) === 0) ? 'active' : '' ?>" data-dest="<?= esc(strtolower($dest)) ?>" onclick="filterSharedSchedules('<?= esc(strtolower($dest)) ?>', this)">
                <?= strtoupper(esc($dest)) ?>
                <span class="chip-count"><?= (int)$cnt ?></span>
            </button>
        <?php endforeach; ?>
    </div>
</div>

<!-- Schedules Table -->
<div class="modern-card shadow-modern fade-in">
    <div class="modern-card-header">
        <span class="modern-card-title">
            <i class="bi bi-list-task" style="color: var(--primary-red);"></i>
            Today's Schedules
        </span>
        <span class="badge-modern badge-modern-primary" id="scheduleVehicleBadge"><?= count($schedules) ?> vehicles</span>
    </div>
    <div class="modern-card-body p-0">
        <div class="table-responsive">
            <table class="table-modern" id="sharedScheduleTable">
                <thead>
                    <tr>
                        <th>Queue #</th>
                        <th>Plate Number</th>
                        <th>Operator</th>
                        <th>Driver</th>
                        <th>Type</th>
                        <th>Route</th>
                        <th>Est. Departure (HH:MM)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="sharedScheduleTableBody">
                    <?php if (!empty($schedules)): ?>
                        <?php foreach ($schedules as $index => $s): ?>
                            <?php $qid = (int)($s['queue_id'] ?? $s['id'] ?? 0); ?>
                            <tr data-queue-id="<?= $qid ?>" data-plate="<?= esc($s['plate_number']) ?>" data-destination="<?= esc(strtolower($s['destination'] ?? '')) ?>" data-type="<?= esc(strtolower($s['vehicle_type'] ?? '')) ?>">
                                <td data-label="Queue #" class="cell-queue-num">
                                    <?php 
                                        $queueNum = !empty($s['position']) && (int)$s['position'] > 0 ? (int)$s['position'] : ($index + 1);
                                    ?>
                                    <span class="badge-modern badge-modern-primary">
                                        #<?= esc($queueNum) ?>
                                    </span>
                                </td>
                                <td data-label="Plate Number">
                                    <span class="plate-number"><?= esc($s['plate_number']) ?></span>
                                </td>
                                <td data-label="Operator">
                                    <span class="fw-bold" style="font-size: 14px; color: #0f172a;">
                                        <i class="bi bi-building text-muted me-1"></i>
                                        <?= esc(!empty($s['operator_name']) ? $s['operator_name'] : ($s['driver_name'] ?? '—')) ?>
                                    </span>
                                </td>
                                <td data-label="Driver">
                                    <span class="fw-semibold" style="font-size: 13.5px; color: #334155;">
                                        <i class="bi bi-person-badge text-primary me-1"></i>
                                        <?= esc($s['driver_name'] ?? '—') ?>
                                    </span>
                                </td>
                                <td data-label="Type">
                                    <?php
                                        $vType = $s['vehicle_type'] ?? '';
                                        $photoUrl = vehicle_resolved_photo($s, $vType);
                                        $hasCustomPhoto = !empty($s['vehicle_photo'] ?? $s['photo'] ?? null);
                                    ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="vehicle-type-icon <?= vehicle_type_class($vType) ?>">
                                            <?php if (!empty($photoUrl)): ?>
                                                <img src="<?= esc($photoUrl) ?>" alt="<?= vehicle_type_label($vType) ?>" style="height:36px; width:auto;" title="<?= vehicle_type_label($vType) ?>" class="<?= $hasCustomPhoto ? 'vehicle-custom-photo' : '' ?>" <?= $hasCustomPhoto ? 'data-vehicle-custom-photo="true"' : ('data-vt-photo="' . esc(vehicle_type_key($vType)) . '"') ?> onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='inline-block';">
                                                <i class="fas <?= esc(vehicle_type_icon($vType)) ?>" style="color: <?= esc(vehicle_type_color($vType)) ?>; font-size: 18px; display: none;"></i>
                                            <?php else: ?>
                                                <i class="fas <?= esc(vehicle_type_icon($vType)) ?>" style="color: <?= esc(vehicle_type_color($vType)) ?>; font-size: 18px;"></i>
                                            <?php endif; ?>
                                        </span>
                                        <?= vehicle_type_badge($vType) ?>
                                    </div>
                                </td>
                                <td data-label="Route" class="schedule-route-cell">
                                    <div class="schedule-route-display" style="word-break: break-word;">
                                        <strong><?= strtoupper(esc($s['origin'])) ?></strong>
                                        <i class="bi bi-arrow-right text-muted" style="font-size: 11px;"></i>
                                        <strong><?= strtoupper(esc($s['destination'])) ?></strong>
                                    </div>
                                </td>
                                <td data-label="Est. Departure (HH:MM)">
                                    <?php if ($s['status'] === 'departed' && $s['departure_time']): ?>
                                        <span class="badge-modern badge-modern-info">
                                            <?= date('H:i', strtotime($s['departure_time'])) ?>
                                        </span>
                                        <small class="text-muted d-block mt-1">Departed</small>
                                    <?php else: ?>
                                        <div class="sched-dep-cell" id="shared-dep-cell-<?= $qid ?>">
                                            <span class="badge-modern badge-modern-primary" id="shared-dep-time-<?= $qid ?>">
                                                <?= !empty($s['estimated_departure']) ? date('H:i', strtotime($s['estimated_departure'])) : 'TBA' ?>
                                            </span>
                                            <small class="sched-passengers-text text-muted" id="shared-passengers-text-<?= $qid ?>">
                                                <span id="passenger-count-<?= $qid ?>" class="passenger-count-num <?= passenger_color_class((int)$s['current_passengers'], (int)$s['capacity']) ?>"><?= $s['current_passengers'] ?></span>/<?= $s['capacity'] ?> passengers
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Status">
                                    <?php
                                    $statusClass = match ($s['status']) {
                                        'boarding' => 'badge-modern-success',
                                        'waiting' => 'badge-modern-warning',
                                        'departed' => 'badge-modern-primary',
                                        'canceled' => 'badge-modern-danger',
                                        default => 'badge-modern-info'
                                    };
                                    $statusIcon = match ($s['status']) {
                                        'boarding' => 'bi-play-circle-fill',
                                        'waiting' => 'bi-hourglass-split',
                                        'departed' => 'bi-check-circle-fill',
                                        'canceled' => 'bi-x-circle-fill',
                                        default => 'bi-info-circle-fill'
                                    };
                                    ?>
                                    <span class="badge-modern <?= $statusClass ?>">
                                        <i class="bi <?= $statusIcon ?> me-1"></i><?= strtoupper($s['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr class="no-schedules-row">
                            <td colspan="8" class="text-center py-5 text-muted empty-state-table">
                                <i class="bi bi-calendar-x fs-1 d-block mb-3 opacity-50"></i>
                                <div class="fw-bold fs-6 empty-state-title">No scheduled departures found</div>
                                <small class="empty-state-subtitle">No vehicle departures have been scheduled for today yet.</small>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr id="shared-no-filter-match" class="d-none" style="display:none !important;">
                        <td colspan="8" class="text-center py-5 text-muted empty-state-table">
                            <i class="bi bi-search fs-1 d-block mb-3 opacity-50"></i>
                            <div class="fw-bold fs-6 empty-state-title">No schedules match the selected filter</div>
                            <small class="empty-state-subtitle">Try choosing a different destination route or vehicle type.</small>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->include('templates/footer') ?>

<script src="<?= base_url('js/ws-client.js?v=20261002app1') ?>"></script>
<script src="<?= base_url('js/queue-sync.js?v=20261001a') ?>"></script>
<script>
    var currentSharedDestFilter = '<?= esc(strtolower($destination ?? 'all')) ?>';

    function filterSharedSchedules(dest, btn) {
        currentSharedDestFilter = dest;

        // Update active chip
        document.querySelectorAll('#sharedRouteFilterBar .route-chip').forEach(function(c) {
            c.classList.remove('active');
        });
        if (btn) {
            btn.classList.add('active');
        }

        // Sync with Destination dropdown
        var destSelect = document.getElementById('sharedDestSelect');
        if (destSelect) {
            for (var i = 0; i < destSelect.options.length; i++) {
                if (dest === 'all' && destSelect.options[i].value === '') {
                    destSelect.selectedIndex = i;
                    break;
                } else if (destSelect.options[i].value.toLowerCase() === dest.toLowerCase()) {
                    destSelect.selectedIndex = i;
                    break;
                }
            }
        }

        applySharedFilter();
    }

    function applySharedFilter() {
        var rows = document.querySelectorAll('#sharedScheduleTableBody tr:not(#shared-no-filter-match):not(.no-schedules-row)');
        var visibleCount = 0;

        rows.forEach(function(row) {
            var rowDest = (row.getAttribute('data-destination') || '').toLowerCase();
            var matches = (currentSharedDestFilter === 'all' || !currentSharedDestFilter || rowDest === currentSharedDestFilter);

            if (matches) {
                row.style.removeProperty('display');
                row.classList.remove('d-none');
                visibleCount++;
            } else {
                row.style.setProperty('display', 'none', 'important');
                row.classList.add('d-none');
            }
        });

        // Handle no-match row
        var noMatch = document.getElementById('shared-no-filter-match');
        if (noMatch) {
            if (visibleCount === 0 && rows.length > 0) {
                noMatch.style.removeProperty('display');
                noMatch.classList.remove('d-none');
            } else {
                noMatch.style.setProperty('display', 'none', 'important');
                noMatch.classList.add('d-none');
            }
        }

        // Update badge
        var badge = document.getElementById('scheduleVehicleBadge');
        if (badge) {
            badge.textContent = visibleCount + ' vehicles';
        }
    }

    function updateSharedScheduleRowPassengers(queueId, newCount, capacity) {
        if (!queueId) return;
        var countSpan = document.getElementById('passenger-count-' + queueId);

        newCount = parseInt(newCount, 10);
        capacity = parseInt(capacity, 10);
        if (isNaN(newCount)) return;
        var isFull = (!isNaN(capacity) && capacity > 0 && newCount >= capacity);

        if (countSpan) {
            countSpan.textContent = newCount;
            if (typeof applyPassengerColor === 'function') {
                applyPassengerColor(countSpan, newCount, capacity);
            }
            countSpan.classList.remove('pulse-update');
            void countSpan.offsetWidth;
            countSpan.classList.add('pulse-update');
        }
    }

    function handleSharedLiveQueueUpdate(message) {
        var data = (message && message.data) ? message.data : message;
        if (!data) return;
        if (data.action === 'passenger_change') {
            updateSharedScheduleRowPassengers(data.id, data.new_count, data.capacity);
        } else if (window.QueueSync && window.QueueSync.refresh) {
            window.QueueSync.refresh(true);
        }
    }

    // Initialize real-time sync (adaptive polling + WebSocket)
    QueueSync.init({
        apiUrl:        '<?= base_url('schedules/status') ?>',
        pollInterval:  4000,
        refreshUrl:    window.location.href,
        tableSelector: '#sharedScheduleTableBody',
        customWSHandler: handleSharedLiveQueueUpdate,
        extraRefresh:  function(newDoc) {
            // Update stats
            var newStats = newDoc.querySelector('#statTodayDepartures');
            var curStats = document.querySelector('#statTodayDepartures');
            if (newStats && curStats) curStats.textContent = newStats.textContent;

            // Re-apply route filter
            applySharedFilter();
        }
    });

</script>
