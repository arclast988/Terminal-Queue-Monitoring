<?= view('templates/header', ['title' => $title]) ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">

<style>
    .retention-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f0fdf4;
        color: #15803d;
        border: 1px solid #bbf7d0;
        border-radius: 20px;
        padding: 5px 14px;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.3px;
    }

    .table-modern .plate-number {
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
    .table-modern .driver-cell {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
        color: #2c3e50;
    }
    .table-modern .driver-cell i {
        color: #66788a;
        font-size: 12px;
    }
    .table-modern .route-info {
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .quick-chips-row {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 16px;
        padding-top: 14px;
        border-top: 1px solid #f1f5f9;
    }

    /* Red theme quick chips */
    html body .quick-chip {
        padding: 6px 16px;
        border-radius: 20px;
        border: 1.5px solid #cbd5e1 !important;
        background: #f8fafc !important;
        color: #475569 !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        text-decoration: none !important;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
        outline: none;
    }

    html body .quick-chip:hover {
        border-color: #b71c1c !important;
        color: #b71c1c !important;
        background: #fef2f2 !important;
    }

    html body .quick-chip.active {
        background: #b71c1c !important;
        border-color: #b71c1c !important;
        color: #ffffff !important;
    }


</style>

<div class="page-header-modern fade-in">
    <div>
        <h1 class="page-title-modern mb-1">
            <i class="bi bi-clock-history"></i> System Departure History
        </h1>
        <div class="retention-pill">
            <i class="bi bi-shield-check"></i> Auto-Retention: Departure records are automatically kept for 60 days
        </div>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <button type="button" class="btn-modern btn-modern-primary" data-bs-toggle="modal" data-bs-target="#reportFilterModal" title="Generate Departure Report">
            <i class="bi bi-file-earmark-text"></i> Generate Report
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

<!-- Summary Stat Cards -->
<div class="row mb-4">
    <div class="col-12 col-md-6 col-xl-3 mb-4">
        <div class="stat-card-modern blue-accent fade-in">
            <div class="stat-card-icon" style="background: #DBEAFE; color: #1565c0;">
                <i class="bi bi-truck"></i>
            </div>
            <div class="stat-card-value"><?= number_format($stats['total'] ?? 0) ?></div>
            <div class="stat-card-label">Total Departed</div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3 mb-4">
        <div class="stat-card-modern success-accent fade-in">
            <div class="stat-card-icon" style="background: #D1FAE5; color: #059669;">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="stat-card-value"><?= number_format($stats['today'] ?? 0) ?></div>
            <div class="stat-card-label">Departed Today</div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3 mb-4">
        <div class="stat-card-modern gold-accent fade-in">
            <div class="stat-card-icon" style="background: #FEF3C7; color: #d97706;">
                <i class="bi bi-calendar-month"></i>
            </div>
            <div class="stat-card-value"><?= number_format($stats['month'] ?? 0) ?></div>
            <div class="stat-card-label">This Month</div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3 mb-4">
        <div class="stat-card-modern fade-in">
            <div class="stat-card-icon" style="background: #F1F5F9; color: #475569;">
                <i class="bi bi-calendar-range"></i>
            </div>
            <div class="stat-card-value"><?= number_format($stats['year'] ?? 0) ?></div>
            <div class="stat-card-label">This Year</div>
        </div>
    </div>
</div>

<!-- Search & Filters -->
<div class="modern-card shadow-modern fade-in mb-4">
    <div class="modern-card-body">
        <form method="get" action="<?= base_url('admin/history') ?>" id="historyFilterForm" class="row g-3 align-items-end">
            <div class="col-12 col-lg-3 col-md-6">
                <label class="form-label-modern"><i class="bi bi-search me-1"></i> Keyword Search</label>
                <input type="text" class="input-modern" name="q" placeholder="Plate, Driver, or Destination..." value="<?= esc($search ?? '') ?>">
            </div>
            <div class="col-12 col-lg-2 col-md-3">
                <label class="form-label-modern"><i class="bi bi-calendar-event me-1"></i> From Date</label>
                <input type="date" class="input-modern" name="from_date" id="fromDate" value="<?= esc($from_date ?? '') ?>">
            </div>
            <div class="col-12 col-lg-2 col-md-3">
                <label class="form-label-modern"><i class="bi bi-calendar-event me-1"></i> To Date</label>
                <input type="date" class="input-modern" name="to_date" id="toDate" value="<?= esc($to_date ?? '') ?>">
            </div>
            <div class="col-12 col-lg-2 col-md-4">
                <label class="form-label-modern"><i class="bi bi-geo-alt me-1"></i> Destination</label>
                <select name="destination" class="input-modern">
                    <option value="">All Destinations</option>
                    <?php if (!empty($destinations)): ?>
                        <?php foreach ($destinations as $d): ?>
                            <option value="<?= esc($d['destination']) ?>" <?= ($destination ?? '') === $d['destination'] ? 'selected' : '' ?>>
                                <?= esc($d['destination']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-12 col-lg-2 col-md-4">
                <label class="form-label-modern"><i class="bi bi-truck me-1"></i> Vehicle Type</label>
                <select name="vehicle_type" class="input-modern">
                    <option value="">All Types</option>
                    <?php if (!empty($vehicleTypes)): ?>
                        <?php foreach ($vehicleTypes as $vt): ?>
                            <option value="<?= esc($vt['slug'] ?? $vt['name']) ?>" <?= ($vehicle_type ?? '') === ($vt['slug'] ?? $vt['name']) ? 'selected' : '' ?>>
                                <?= esc($vt['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-12 col-lg-1 col-md-4">
                <button type="submit" class="btn-modern btn-modern-primary w-100" style="height: 42px;">
                    <i class="bi bi-funnel-fill"></i> Filter
                </button>
            </div>
        </form>

        <div class="quick-chips-row">
            <span class="text-muted" style="font-size: 12px; font-weight: 700; text-transform: uppercase;">
                <i class="bi bi-clock-history me-1"></i> Quick Ranges:
            </span>
            <button type="button" class="quick-chip quick-chip-btn <?= empty($from_date) && empty($to_date) ? 'active' : '' ?>" data-from="" data-to="">All (60d)</button>
            <button type="button" class="quick-chip quick-chip-btn <?= ($from_date === date('Y-m-d') && $to_date === date('Y-m-d')) ? 'active' : '' ?>" data-from="<?= date('Y-m-d') ?>" data-to="<?= date('Y-m-d') ?>">Today</button>
            <button type="button" class="quick-chip quick-chip-btn <?= ($from_date === date('Y-m-d', strtotime('-7 days')) && $to_date === date('Y-m-d')) ? 'active' : '' ?>" data-from="<?= date('Y-m-d', strtotime('-7 days')) ?>" data-to="<?= date('Y-m-d') ?>">Last 7 Days</button>
            <button type="button" class="quick-chip quick-chip-btn <?= ($from_date === date('Y-m-d', strtotime('-30 days')) && $to_date === date('Y-m-d')) ? 'active' : '' ?>" data-from="<?= date('Y-m-d', strtotime('-30 days')) ?>" data-to="<?= date('Y-m-d') ?>">Last 30 Days</button>
            <?php if (!empty($search) || !empty($from_date) || !empty($to_date) || !empty($destination) || !empty($vehicle_type)): ?>
                <a href="<?= base_url('admin/history') ?>" class="btn-modern btn-modern-sm btn-modern-outline ms-auto" title="Clear all filters">
                    <i class="bi bi-x-circle me-1"></i> Clear Filters
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Departures Table -->
<div class="modern-card shadow-modern fade-in mb-4">
    <div class="modern-card-header d-flex align-items-center justify-content-between">
        <span class="modern-card-title" style="font-size: 16px;">
            <i class="bi bi-list-ul" style="color: var(--primary-red, #b71c1c);"></i>
            Departure Records
            <?php if (!empty($departures)): ?>
                <span class="badge-modern badge-modern-secondary ms-2" style="font-size: 13px;"><?= count($departures) ?> on this page</span>
            <?php endif; ?>
        </span>
    </div>
    <div class="modern-card-body p-0">
        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th style="width: 140px;">Plate Number</th>
                        <th>Operator</th>
                        <th>Driver</th>
                        <th>Vehicle</th>
                        <th>Route</th>
                        <th style="width: 160px;">Departure Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($departures)): ?>
                        <?php foreach ($departures as $item): ?>
                            <tr>
                                <td data-label="Plate Number">
                                    <span class="plate-number"><?= esc($item['plate_number']) ?></span>
                                </td>
                                <td data-label="Operator">
                                    <?php
                                        $opName = $item['operator_name'] ?: ($item['owner_name'] ?? '');
                                        $drName = $item['driver_name'] ?? '';
                                    ?>
                                    <?php if (!empty($opName) && strtolower(trim($opName)) !== strtolower(trim($drName))): ?>
                                        <div class="driver-cell">
                                            <i class="bi bi-building text-muted"></i>
                                            <span><?= esc($opName) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Driver">
                                    <div class="driver-cell">
                                        <i class="bi bi-person-badge text-primary"></i>
                                        <span><?= esc($item['driver_name'] ?? '—') ?></span>
                                    </div>
                                </td>
                                <td data-label="Vehicle">
                                    <?php
                                        $vType = $item['vehicle_type'] ?? '';
                                        $imgFile = vehicle_type_image($vType);
                                    ?>
                                    <div class="vehicle-type-cell">
                                        <span class="vehicle-type-icon <?= vehicle_type_class($vType) ?>">
                                            <img src="<?= base_url('images/' . $imgFile) ?>" alt="<?= vehicle_type_label($vType) ?>" style="height:32px; width:auto;" title="<?= vehicle_type_label($vType) ?>">
                                        </span>
                                        <?= vehicle_type_badge($vType) ?>
                                    </div>
                                </td>
                                <td data-label="Route">
                                    <div class="route-info">
                                        <i class="bi bi-geo-alt" style="color: var(--primary, #1565c0);"></i>
                                        <span><?= esc($item['origin'] ?? '') ?> → <?= esc($item['destination'] ?? '') ?></span>
                                    </div>
                                </td>
                                <td data-label="Departure Time">
                                    <div style="white-space: nowrap;">
                                        <span style="font-weight: 700; color: var(--primary-dark); font-size: 13px;">
                                            <?= date('H:i', strtotime($item['departure_time'])) ?>
                                        </span>
                                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                                            <i class="bi bi-calendar3 me-1"></i><?= date('M d, Y', strtotime($item['departure_time'])) ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                <div class="fw-bold fs-6">No departure records found</div>
                                <small>Try adjusting your search criteria or date filters.</small>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($pager && $pager->getPageCount() > 1): ?>
        <div class="modern-card-footer bg-white py-3 d-flex justify-content-center">
            <?= $pager->links() ?>
        </div>
    <?php endif; ?>
</div>

<?= view('admin/modals/report_filter', ['destinations' => $destinations, 'vehicleTypes' => $vehicleTypes]) ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var quickButtons = document.querySelectorAll('.quick-chip-btn');
    var fromInput = document.getElementById('fromDate');
    var toInput = document.getElementById('toDate');
    var form = document.getElementById('historyFilterForm');

    quickButtons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var from = this.getAttribute('data-from') || '';
            var to = this.getAttribute('data-to') || '';
            
            if (fromInput) fromInput.value = from;
            if (toInput) toInput.value = to;

            quickButtons.forEach(function(b) {
                b.classList.remove('active');
            });
            this.classList.add('active');

            if (form) {
                form.submit();
            }
        });
    });
});
</script>

<script src="<?= base_url('js/ws-client.js') ?>"></script>
<script src="<?= base_url('js/queue-sync.js') ?>"></script>
<script>
    // Initialize real-time sync (polling + WebSocket)
    QueueSync.init({
        onlyWS:        true,
        pollInterval:  30000,
        refreshUrl:    window.location.href,
        tableSelector: '.table-modern tbody',
        extraRefresh:  function(newDoc) {
            // Update stat cards
            var newCards = newDoc.querySelectorAll('.stat-card-value');
            var curCards = document.querySelectorAll('.stat-card-value');
            newCards.forEach(function(card, i) { if (curCards[i]) curCards[i].textContent = card.textContent; });

            // Update pagination
            var newPager = newDoc.querySelector('.modern-card-footer');
            var curPager = document.querySelector('.modern-card-footer');
            if (newPager && curPager) curPager.innerHTML = newPager.innerHTML;
        }
    });
</script>

<?= view('templates/footer') ?>
