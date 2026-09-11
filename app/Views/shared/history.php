<?= $this->include('templates/header') ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">

<style>
    /* Route Filter Capsule Bar (matching screenshot) */
    .route-filter-wrapper {
        margin-bottom: 20px;
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
        text-decoration: none !important;
    }
    .route-chip:hover {
        border-color: var(--primary, #1565c0);
        color: var(--primary, #1565c0);
        background: rgba(21, 101, 192, 0.04);
        transform: translateY(-1px);
    }
    .route-chip.active {
        background: var(--primary, #1565c0) !important;
        border-color: var(--primary, #1565c0) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(21, 101, 192, 0.25);
        transform: translateY(0);
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

    .driver-cell {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
        color: #1e293b;
        font-size: 13.5px;
    }
</style>

<div class="page-header-modern fade-in">
    <h1 class="page-title-modern">
        <i class="bi bi-clock-history"></i>
        Departure History
    </h1>
</div>

<!-- Search Bar & Filters -->
<div class="modern-card shadow-modern fade-in mb-4">
    <div class="modern-card-body py-3">
        <form method="get" action="<?= base_url('history') ?>" class="d-flex flex-wrap align-items-center gap-3">
            <?php if (!empty($destination) && $destination !== 'all'): ?>
                <input type="hidden" name="destination" value="<?= esc($destination) ?>">
            <?php endif; ?>
            <div style="display: flex; align-items: center; max-width: 440px; width: 100%; position: relative;">
                <span class="input-group-text" style="height: 40px; background: #f8fafc; border: 1.5px solid #cbd5e1; border-right: none; border-radius: 20px 0 0 20px; color: #64748b; padding: 0 14px;">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control" id="history-search-input" name="q" placeholder="Search plate number, destination, driver, or operator..." value="<?= esc($search ?? '') ?>" style="height: 40px; border: 1.5px solid #cbd5e1; border-left: none; border-radius: 0 20px 20px 0; outline: none; font-size: 13.5px; box-shadow: none; padding-right: 36px;" oninput="toggleHistorySearchClear(this.value)">
                <button type="button" class="btn-clear-search" id="clear-history-search" onclick="clearHistorySearch()" style="<?= !empty($search) ? 'display: inline-flex !important;' : 'display: none !important;' ?> right: 12px;" title="Clear search">
                    <i class="bi bi-x-circle-fill"></i>
                </button>
            </div>
            <button type="submit" class="btn-modern btn-modern-primary" style="height: 40px;">
                <i class="bi bi-search"></i> Search
            </button>
            <?php if (!empty($search) || (!empty($destination) && $destination !== 'all')): ?>
                <a href="<?= base_url('history') ?>" class="btn-modern btn-modern-outline btn-modern-sm" title="Clear all filters">
                    <i class="bi bi-x-circle"></i> Clear Filters
                </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php if (!empty($destinations)): ?>
<!-- Route Destination Filter Bar (Pill Chips) -->
<div class="route-filter-wrapper fade-in">
    <div class="route-filter-label">
        <i class="bi bi-geo-alt-fill" style="color: var(--primary, #1565c0);"></i> Route Filter:
    </div>
    <div class="route-filter-bar">
        <a href="<?= base_url('history' . (!empty($search) ? '?q=' . urlencode($search) : '')) ?>" class="route-chip <?= empty($destination) || $destination === 'all' ? 'active' : '' ?>">
            All Routes
        </a>
        <?php foreach ($destinations as $d): ?>
            <?php
                $dName = $d['destination'] ?? '';
                if (empty($dName)) continue;
                $params = [];
                $params['destination'] = $dName;
                if (!empty($search)) $params['q'] = $search;
                $chipUrl = base_url('history?' . http_build_query($params));
                $isChipActive = (strcasecmp($destination ?? '', $dName) === 0);
            ?>
            <a href="<?= $chipUrl ?>" class="route-chip <?= $isChipActive ? 'active' : '' ?>">
                <?= strtoupper(esc($dName)) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="modern-card shadow-modern fade-in mb-4">
    <div class="modern-card-header d-flex align-items-center justify-content-between">
        <span class="modern-card-title" style="font-size: 16px;">
            <i class="bi bi-list-ul" style="color: var(--primary, #1565c0);"></i>
            Past Departures
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
                        <th style="width: 150px;">Plate Number</th>
                        <th>Operator</th>
                        <th>Driver</th>
                        <th>Vehicle</th>
                        <th>Route</th>
                        <th style="width: 170px;">Departure Time</th>
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
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="vehicle-type-icon <?= vehicle_type_class($vType) ?>">
                                            <img src="<?= base_url('images/' . $imgFile) ?>" alt="<?= esc($vType) ?>" style="height:32px;width:auto;" title="<?= vehicle_type_label($vType) ?>">
                                        </span>
                                        <?= vehicle_type_badge($vType) ?>
                                    </div>
                                </td>
                                <td data-label="Route">
                                    <div class="d-flex align-items-center gap-2">
                                        <small class="text-muted"><?= strtoupper(esc($item['origin'])) ?></small>
                                        <i class="bi bi-arrow-right text-primary"></i>
                                        <strong><?= strtoupper(esc($item['destination'])) ?></strong>
                                    </div>
                                </td>
                                <td data-label="Departure Time">
                                    <div style="white-space: nowrap;">
                                        <span style="font-weight: 700; color: var(--text-main); font-size: 13px;">
                                            <i class="bi bi-clock me-1 text-muted"></i><?= date('H:i', strtotime($item['departure_time'])) ?>
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
                                <div class="fw-bold fs-6 empty-state-title">No departure history found</div>
                                <small class="empty-state-subtitle">Past departure logs will appear here once trips are completed.</small>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($pager && $pager->getPageCount() > 1): ?>
        <div class="pager-wrap">
            <?= $pager->links() ?>
        </div>
    <?php endif; ?>
</div>

<script>
function toggleHistorySearchClear(val) {
    var btn = document.getElementById('clear-history-search');
    if (btn) {
        if (val && val.trim().length > 0) {
            btn.style.setProperty('display', 'inline-flex', 'important');
        } else {
            btn.style.setProperty('display', 'none', 'important');
        }
    }
}
function clearHistorySearch() {
    var input = document.getElementById('history-search-input');
    if (input) {
        input.value = '';
        toggleHistorySearchClear('');
        input.focus();
        if (window.location.search.includes('q=')) {
            var url = new URL(window.location.href);
            url.searchParams.delete('q');
            window.location.href = url.toString();
        }
    }
}
document.addEventListener('DOMContentLoaded', function() {
    var input = document.getElementById('history-search-input');
    if (input) toggleHistorySearchClear(input.value);
});
</script>

<?= $this->include('templates/footer') ?>
