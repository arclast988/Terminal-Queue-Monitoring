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
            <div class="stat-card-icon" style="background: #fdecea; color: #c62828;">
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
                <div class="position-relative">
                    <input type="text" class="input-modern pe-4" id="admin-history-q" name="q" placeholder="Plate, Driver, or Destination..." value="<?= esc($search ?? '') ?>" oninput="toggleAdminHistoryClear(this.value)">
                    <button type="button" class="btn-clear-search" id="clear-admin-history-search" onclick="clearAdminHistorySearch()" style="<?= !empty($search) ? 'display: inline-flex !important;' : 'display: none !important;' ?> right: 10px;" title="Clear search">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>
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
                                <?= strtoupper(esc($d['destination'])) ?>
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
                            <?php 
                                $vtSlug = $vt['slug'] ?? $vt['name'];
                                $vtIcon = !empty($vt['icon']) ? $vt['icon'] : vehicle_type_icon($vtSlug);
                                $vtColor = !empty($vt['color']) ? $vt['color'] : vehicle_type_color($vtSlug);
                            ?>
                            <option value="<?= esc($vtSlug) ?>"
                                    data-icon="<?= esc($vtIcon) ?>"
                                    data-color="<?= esc($vtColor) ?>"
                                    <?= ($vehicle_type ?? '') === $vtSlug ? 'selected' : '' ?>>
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

        <div class="quick-chips-row" id="historyQuickChipsRow">
            <span class="text-muted" style="font-size: 12px; font-weight: 700; text-transform: uppercase;">
                <i class="bi bi-clock-history me-1"></i> Quick Ranges:
            </span>
            <button type="button" class="quick-chip quick-chip-btn <?= empty($from_date) && empty($to_date) ? 'active' : '' ?>" data-from="" data-to="">All (60d)</button>
            <button type="button" class="quick-chip quick-chip-btn <?= ($from_date === date('Y-m-d') && $to_date === date('Y-m-d')) ? 'active' : '' ?>" data-from="<?= date('Y-m-d') ?>" data-to="<?= date('Y-m-d') ?>">Today</button>
            <button type="button" class="quick-chip quick-chip-btn <?= ($from_date === date('Y-m-d', strtotime('-7 days')) && $to_date === date('Y-m-d')) ? 'active' : '' ?>" data-from="<?= date('Y-m-d', strtotime('-7 days')) ?>" data-to="<?= date('Y-m-d') ?>">Last 7 Days</button>
            <button type="button" class="quick-chip quick-chip-btn <?= ($from_date === date('Y-m-d', strtotime('-30 days')) && $to_date === date('Y-m-d')) ? 'active' : '' ?>" data-from="<?= date('Y-m-d', strtotime('-30 days')) ?>" data-to="<?= date('Y-m-d') ?>">Last 30 Days</button>
            <span id="clearFilterContainer" class="ms-auto">
                <?php if (!empty($search) || !empty($from_date) || !empty($to_date) || !empty($destination) || !empty($vehicle_type)): ?>
                    <a href="<?= base_url('admin/history') ?>" id="clearFiltersBtn" class="btn-modern btn-modern-sm btn-modern-outline" title="Clear all filters">
                        <i class="bi bi-x-circle me-1"></i> Clear Filters
                    </a>
                <?php endif; ?>
            </span>
        </div>
    </div>
</div>

<!-- Departures Table -->
<div class="modern-card shadow-modern fade-in mb-4" id="historyTableCard">
    <div class="modern-card-header d-flex align-items-center justify-content-between">
        <span class="modern-card-title" id="historyTableTitle" style="font-size: 16px;">
            <i class="bi bi-list-ul" style="color: var(--primary-red, #b71c1c);"></i>
            Departure Records
            <?php if (!empty($departures)): ?>
                <span class="badge-modern badge-modern-secondary ms-2" style="font-size: 13px;"><?= count($departures) ?> on this page</span>
            <?php endif; ?>
        </span>
    </div>
    <div class="modern-card-body p-0">
        <div class="table-responsive">
            <table class="table-modern" id="historyTable">
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
                                        $photoUrl = vehicle_type_photo($vType);
                                    ?>
                                    <div class="vehicle-type-cell">
                                        <span class="vehicle-type-icon <?= vehicle_type_class($vType) ?>">
                                            <?php if (!empty($photoUrl)): ?>
                                                <img src="<?= esc($photoUrl) ?>" alt="<?= vehicle_type_label($vType) ?>" style="height:32px; width:auto;" title="<?= vehicle_type_label($vType) ?>" data-vt-photo="<?= esc(vehicle_type_key($vType)) ?>">
                                            <?php else: ?>
                                                <i class="fas <?= esc(vehicle_type_icon($vType)) ?>" style="color: <?= esc(vehicle_type_color($vType)) ?>; font-size: 16px;"></i>
                                            <?php endif; ?>
                                        </span>
                                        <?= vehicle_type_badge($vType) ?>
                                    </div>
                                </td>
                                <td data-label="Route">
                                    <div class="route-info">
                                        <i class="bi bi-geo-alt" style="color: var(--primary, #1565c0);"></i>
                                        <span><?= strtoupper(esc($item['origin'] ?? '')) ?> → <?= strtoupper(esc($item['destination'] ?? '')) ?></span>
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
                            <td colspan="6" class="text-center py-5 text-muted empty-state-table">
                                <i class="bi bi-clock-history fs-1 d-block mb-3 opacity-50"></i>
                                <div class="fw-bold fs-6 empty-state-title">No departure records found</div>
                                <small class="empty-state-subtitle">Try adjusting your search criteria or date filters.</small>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div id="historyPaginationWrap">
        <?php if ($pager && $pager->getPageCount() > 1): ?>
            <div class="modern-card-footer bg-white py-3 d-flex justify-content-center">
                <?= $pager->links() ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= view('admin/modals/report_filter', ['destinations' => $destinations, 'vehicleTypes' => $vehicleTypes]) ?>
<?= view('templates/footer') ?>

<script src="<?= base_url('js/queue-sync.js?v=20260907') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('historyFilterForm');
    var fromInput = document.getElementById('fromDate');
    var toInput = document.getElementById('toDate');
    var tableCard = document.getElementById('historyTableCard');

    function updateActiveChips(from, to) {
        var quickButtons = document.querySelectorAll('#historyQuickChipsRow .quick-chip-btn');
        quickButtons.forEach(function(btn) {
            var btnFrom = btn.getAttribute('data-from') || '';
            var btnTo = btn.getAttribute('data-to') || '';
            if (btnFrom === from && btnTo === to) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
    }

    var isFetching = false;
    function fetchFilteredHistory(url, pushState) {
        if (typeof pushState === 'undefined') pushState = true;
        if (isFetching) return;
        isFetching = true;

        if (tableCard) {
            tableCard.style.opacity = '0.5';
            tableCard.style.pointerEvents = 'none';
            tableCard.style.transition = 'opacity 0.15s ease';
        }

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(res) {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.text();
        })
        .then(function(html) {
            var parser = new DOMParser();
            var doc = parser.parseFromString(html, 'text/html');

            // 1. Update Table Body
            var newTbody = doc.querySelector('#historyTable tbody');
            var curTbody = document.querySelector('#historyTable tbody');
            if (newTbody && curTbody) {
                curTbody.innerHTML = newTbody.innerHTML;
            }

            // 2. Update Header / Count Badge
            var newTitle = doc.getElementById('historyTableTitle');
            var curTitle = document.getElementById('historyTableTitle');
            if (newTitle && curTitle) {
                curTitle.innerHTML = newTitle.innerHTML;
            }

            // 3. Update Pagination
            var newPagerWrap = doc.getElementById('historyPaginationWrap');
            var curPagerWrap = document.getElementById('historyPaginationWrap');
            if (newPagerWrap && curPagerWrap) {
                curPagerWrap.innerHTML = newPagerWrap.innerHTML;
            }

            // 4. Update Clear Filter Button
            var newClearWrap = doc.getElementById('clearFilterContainer');
            var curClearWrap = document.getElementById('clearFilterContainer');
            if (newClearWrap && curClearWrap) {
                curClearWrap.innerHTML = newClearWrap.innerHTML;
            }

            // 5. Update Stat Cards
            var newCards = doc.querySelectorAll('.stat-card-value');
            var curCards = document.querySelectorAll('.stat-card-value');
            newCards.forEach(function(card, i) {
                if (curCards[i]) curCards[i].textContent = card.textContent;
            });

            // 6. Sync inputs with URL params
            try {
                var urlObj = new URL(url, window.location.origin);
                var params = urlObj.searchParams;
                var qInput = form ? form.querySelector('[name="q"]') : null;
                var destInput = form ? form.querySelector('[name="destination"]') : null;
                var vTypeInput = form ? form.querySelector('[name="vehicle_type"]') : null;

                if (qInput) qInput.value = params.get('q') || '';
                if (fromInput) fromInput.value = params.get('from_date') || '';
                if (toInput) toInput.value = params.get('to_date') || '';
                if (destInput) destInput.value = params.get('destination') || '';
                if (vTypeInput) vTypeInput.value = params.get('vehicle_type') || '';

                updateActiveChips(params.get('from_date') || '', params.get('to_date') || '');
            } catch(e) {}

            // 7. Update URL in browser
            if (pushState) {
                window.history.pushState({ url: url }, '', url);
            }
        })
        .catch(function(err) {
            console.error('AJAX history filter error:', err);
            window.location.href = url;
        })
        .finally(function() {
            isFetching = false;
            if (tableCard) {
                tableCard.style.opacity = '1';
                tableCard.style.pointerEvents = 'auto';
            }
        });
    }

    // Intercept clicks on Quick Chips, Clear Filters, and Pagination links
    document.addEventListener('click', function(e) {
        // Quick Range Chip click
        var chipBtn = e.target.closest('#historyQuickChipsRow .quick-chip-btn');
        if (chipBtn) {
            e.preventDefault();
            var from = chipBtn.getAttribute('data-from') || '';
            var to = chipBtn.getAttribute('data-to') || '';

            if (fromInput) fromInput.value = from;
            if (toInput) toInput.value = to;

            updateActiveChips(from, to);

            var formData = new FormData(form);
            var params = new URLSearchParams();
            for (var pair of formData.entries()) {
                if (pair[1] !== '') {
                    params.append(pair[0], pair[1]);
                }
            }
            var targetUrl = form.action + (params.toString() ? '?' + params.toString() : '');
            fetchFilteredHistory(targetUrl, true);
            return;
        }

        // Clear Filters click
        var clearBtn = e.target.closest('#clearFiltersBtn');
        if (clearBtn) {
            e.preventDefault();
            if (form) form.reset();
            if (fromInput) fromInput.value = '';
            if (toInput) toInput.value = '';
            updateActiveChips('', '');
            var targetUrl = clearBtn.getAttribute('href') || form.action;
            fetchFilteredHistory(targetUrl, true);
            return;
        }

        // Pagination links click
        var pageLink = e.target.closest('#historyPaginationWrap a');
        if (pageLink) {
            e.preventDefault();
            var targetUrl = pageLink.getAttribute('href');
            if (targetUrl) {
                fetchFilteredHistory(targetUrl, true);
                if (tableCard) {
                    tableCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
            return;
        }
    });

    // Intercept Form Submit (Filter button or Enter key in search)
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(form);
            var params = new URLSearchParams();
            for (var pair of formData.entries()) {
                if (pair[1] !== '') {
                    params.append(pair[0], pair[1]);
                }
            }
            var targetUrl = form.action + (params.toString() ? '?' + params.toString() : '');
            updateActiveChips(fromInput ? fromInput.value : '', toInput ? toInput.value : '');
            fetchFilteredHistory(targetUrl, true);
        });
    }

    function toggleAdminHistoryClear(val) {
        var btn = document.getElementById('clear-admin-history-search');
        if (btn) {
            if (val && val.trim().length > 0) {
                btn.style.setProperty('display', 'inline-flex', 'important');
            } else {
                btn.style.setProperty('display', 'none', 'important');
            }
        }
    }
    window.toggleAdminHistoryClear = toggleAdminHistoryClear;

    window.clearAdminHistorySearch = function() {
        var input = document.getElementById('admin-history-q');
        if (input) {
            input.value = '';
            toggleAdminHistoryClear('');
            input.focus();
            var form = document.getElementById('historyFilterForm');
            if (form) {
                var submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) submitBtn.click();
                else form.submit();
            }
        }
    };

    var histSearchInput = document.getElementById('admin-history-q');
    if (histSearchInput) toggleAdminHistoryClear(histSearchInput.value);

    // Handle browser back and forward navigation
    window.addEventListener('popstate', function(e) {
        fetchFilteredHistory(window.location.href, false);
    });

    // Real-time sync (polling + WebSocket)
    if (typeof QueueSync !== 'undefined') {
        QueueSync.init({
            pollInterval:  15000,
            refreshUrl:    window.location.href,
            tableSelector: '#historyTable tbody',
            extraRefresh:  function(newDoc) {
                var newCards = newDoc.querySelectorAll('.stat-card-value');
                var curCards = document.querySelectorAll('.stat-card-value');
                newCards.forEach(function(card, i) { if (curCards[i]) curCards[i].textContent = card.textContent; });

                var newPagerWrap = newDoc.getElementById('historyPaginationWrap');
                var curPagerWrap = document.getElementById('historyPaginationWrap');
                if (newPagerWrap && curPagerWrap) curPagerWrap.innerHTML = newPagerWrap.innerHTML;
            }
        });

        // Immediately refresh history on actual trip departures or cancellations
        document.addEventListener('pttm:ws-queue_update', function(e) {
            var detail = e.detail || {};
            var data = detail.data || detail;
            if (data && (data.action === 'status_change' || data.action === 'cancel_trip' || data.status === 'departed')) {
                if (window.QueueSync && window.QueueSync.refresh) {
                    window.QueueSync.refresh();
                }
            }
        });
    }
});
</script>
