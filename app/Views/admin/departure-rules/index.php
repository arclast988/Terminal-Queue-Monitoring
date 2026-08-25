<?= view('templates/header', ['title' => $title]) ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">

<style>
.rule-filter-container {
    background: #ffffff;
    border-radius: 16px;
    padding: 16px 20px;
    margin-bottom: 24px;
    box-shadow: 0 4px 16px -2px rgba(15, 23, 42, 0.05);
    border: 1px solid #e2e8f0;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
}

.rule-filter-left {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    width: 100%;
}

.rule-filter-label {
    font-size: 12.5px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 6px;
    margin-right: 4px;
}

.rule-filter-group {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.rule-filter-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 18px;
    border-radius: 20px;
    border: 1.5px solid #cbd5e1;
    background: #ffffff;
    color: #334155;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.18s ease;
    user-select: none;
    font-family: inherit;
    white-space: nowrap;
}

.rule-filter-btn i {
    color: var(--primary, #1565C0);
    transition: color 0.18s ease;
}

.rule-filter-btn:hover {
    border-color: var(--primary, #1565C0);
    color: var(--primary, #1565C0);
    background: var(--primary-soft, rgba(21, 101, 192, 0.06));
    transform: translateY(-1px);
}

.rule-filter-btn.active {
    background: var(--primary, #1565C0) !important;
    border-color: var(--primary, #1565C0) !important;
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.rule-filter-btn.active i {
    color: #ffffff !important;
}

.rule-filter-btn .badge-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 2px 7px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 800;
    background: rgba(0, 0, 0, 0.08);
    color: inherit;
}

.rule-filter-btn.active .badge-count {
    background: rgba(255, 255, 255, 0.25);
    color: #ffffff;
}

.route-tag-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12.5px;
    font-weight: 800;
    background: #f1f5f9;
    color: #1e293b;
    border: 1px solid #cbd5e1;
    white-space: nowrap;
}

#departure-rules-table {
    table-layout: fixed;
    width: 100%;
}

#departure-rules-table th,
#departure-rules-table td {
    vertical-align: middle;
}

#departure-rules-table th:nth-child(1) { width: 15%; }
#departure-rules-table th:nth-child(2) { width: 17%; }
#departure-rules-table th:nth-child(3) { width: 12%; }
#departure-rules-table th:nth-child(4) { width: 12%; }
#departure-rules-table th:nth-child(5) { width: 13%; }
#departure-rules-table th:nth-child(6) { width: 17%; }
#departure-rules-table th:nth-child(7) { width: 14%; }

@media (max-width: 768px) {
    #departure-rules-table {
        table-layout: auto !important;
    }
    
    #departure-rules-table th {
        width: auto !important;
    }

    .table-modern tbody tr[style*="display: none"],
    .table-modern tbody tr[style*="display:none"],
    .table-modern tbody tr.d-none {
        display: none !important;
    }
    
    .table-modern tbody tr td[colspan] {
        display: block !important;
        text-align: center !important;
        justify-content: center !important;
        padding: 24px 16px !important;
    }
    .table-modern tbody tr td[colspan]::before {
        display: none !important;
    }
}
</style>

<div class="page-header-modern fade-in">
    <h1 class="page-title-modern">
        <i class="bi bi-clock-history"></i>
        Departure Time Rules
    </h1>
    <?php if (session()->get('role') !== 'staff'): ?>
    <div>
        <a href="<?= base_url($prefix . '/departure-rules/create') ?>" id="btn-add-rule" class="btn-modern btn-modern-primary">
            <i class="bi bi-plus-circle"></i> Add New Rule
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

<!-- Route Filter Bar -->
<div class="rule-filter-container fade-in">
    <div class="rule-filter-left">
        <span class="rule-filter-label">
            <i class="bi bi-funnel-fill"></i> Filter Route:
        </span>
        <div class="rule-filter-group" id="ruleFilterGroup">
            <button class="rule-filter-btn active" data-route-filter="all" data-route-id="">
                <i class="bi bi-grid-fill"></i> All Routes
                <span class="badge-count"><?= count($rules) ?></span>
            </button>
            <?php foreach (($destinationsMap ?? []) as $destName => $rId): ?>
                <?php 
                    $count = 0;
                    foreach ($rules as $r) {
                        if (strcasecmp($r['route_destination'] ?? '', $destName) === 0) {
                            $count++;
                        }
                    }
                ?>
                <button class="rule-filter-btn" data-route-filter="<?= esc(strtolower($destName)) ?>" data-route-id="<?= esc($rId ?? '') ?>">
                    <i class="bi bi-geo-alt-fill"></i> <?= esc($destName) ?>
                    <span class="badge-count"><?= $count ?></span>
                </button>
            <?php endforeach; ?>

            <?php 
                $generalCount = 0;
                foreach ($rules as $r) {
                    if (empty($r['route_destination']) || $r['route_destination'] === '-') {
                        $generalCount++;
                    }
                }
                if ($generalCount > 0):
            ?>
                <button class="rule-filter-btn" data-route-filter="general" data-route-id="">
                    <i class="bi bi-sliders"></i> Terminal Default
                    <span class="badge-count"><?= $generalCount ?></span>
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modern-card shadow-modern fade-in">
    <div class="modern-card-header d-flex align-items-center justify-content-between">
        <span class="modern-card-title">
            <i class="bi bi-list" style="color: var(--primary, #1565C0);"></i>
            Rule List <span id="active-filter-title" class="badge-modern badge-modern-primary ms-2" style="font-size: 12px; font-weight: 700;">(All Destinations)</span>
        </span>
    </div>
    <div class="modern-card-body">
        <div class="table-responsive">
            <table class="table-modern" id="departure-rules-table">
                <thead>
                    <tr>
                        <th>Terminal</th>
                        <th>Destination</th>
                        <th>Time From</th>
                        <th>Time To</th>
                        <th>Wait Time</th>
                        <th>Label</th>
                        <?php if (session()->get('role') !== 'staff'): ?>
                        <th>Actions</th>
                        <?php else: ?>
                        <th>Status</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rules) && is_array($rules)): ?>
                        <?php foreach ($rules as $rule): ?>
                            <?php $destSlug = !empty($rule['route_destination']) && $rule['route_destination'] !== '-' ? strtolower($rule['route_destination']) : 'general'; ?>
                            <tr class="rule-row" data-destination="<?= esc($destSlug) ?>" data-route-id="<?= esc($rule['route_id'] ?? '') ?>">
                                <td data-label="Terminal">
                                    <span class="fw-bold" style="color: var(--text-main, #0f172a); font-size: 13.5px;">
                                        <i class="bi bi-building me-1 text-muted"></i><?= esc($rule['terminal_name'] ?? '-') ?>
                                    </span>
                                </td>
                                <td data-label="Destination">
                                    <?php if (!empty($rule['route_destination']) && $rule['route_destination'] !== '-'): ?>
                                        <span class="route-tag-pill"><i class="bi bi-geo-alt-fill text-danger"></i> <?= esc($rule['route_destination']) ?></span>
                                    <?php else: ?>
                                        <span class="badge-modern badge-modern-secondary"><i class="bi bi-sliders"></i> Terminal Default</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Time From"><span style="white-space: nowrap; font-weight: 600; font-size: 13px; color: var(--text-main);"><i class="bi bi-clock me-1 text-muted"></i><?= date('H:i', strtotime($rule['time_from'])) ?></span></td>
                                <td data-label="Time To"><span style="white-space: nowrap; font-weight: 600; font-size: 13px; color: var(--text-main);"><i class="bi bi-clock me-1 text-muted"></i><?= date('H:i', strtotime($rule['time_to'])) ?></span></td>
                                <td data-label="Wait Time">
                                    <?php
                                        $mins = (int)$rule['wait_minutes'];
                                        $h = floor($mins / 60);
                                        $m = $mins % 60;
                                    ?>
                                    <span class="badge-modern badge-modern-info" style="font-weight: 700; font-size: 13px;">
                                        <i class="bi bi-hourglass-split me-1"></i><?= sprintf('%02d:%02d', $h, $m) ?>
                                    </span>
                                </td>
                                <td data-label="Label">
                                    <span style="color: #475569; font-weight: 500; font-size: 13.5px;"><?= esc(!empty($rule['label']) && $rule['label'] !== '-' ? $rule['label'] : '—') ?></span>
                                </td>
                                <?php if (session()->get('role') !== 'staff'): ?>
                                <td data-label="Actions">
                                    <div class="d-flex gap-2">
                                        <a href="<?= base_url($prefix . '/departure-rules/edit/'.$rule['id']) ?>" class="btn-modern btn-action-edit btn-modern-sm" title="Edit">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form action="<?= base_url($prefix . '/departure-rules/delete/'.$rule['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this rule?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn-modern btn-modern-sm btn-action-delete" title="Delete">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                <?php else: ?>
                                <td data-label="Status">
                                    <span class="badge-modern badge-modern-info"><i class="bi bi-eye-fill"></i> View Only</span>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr id="empty-all-rules-row">
                            <td colspan="7" class="text-center py-5 text-muted empty-state-table">
                                <i class="bi bi-clock-history fs-1 d-block mb-3 opacity-50"></i>
                                <div class="fw-bold fs-6 empty-state-title">No departure rules configured</div>
                                <small class="empty-state-subtitle">The terminal default interval (30 minutes) will be used.</small>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr id="empty-filter-row" class="d-none" style="display: none !important;">
                        <td colspan="7" class="text-center py-5 text-muted empty-state-table">
                            <i class="bi bi-search fs-1 d-block mb-3 opacity-50"></i>
                            <div class="fw-bold fs-6 empty-state-title" id="empty-filter-text">No departure rules configured for this route</div>
                            <small class="empty-state-subtitle">No custom dispatch interval has been configured for this destination.</small>
                            <?php if (session()->get('role') !== 'staff'): ?>
                            <div class="mt-3">
                                <a href="<?= base_url($prefix . '/departure-rules/create') ?>" id="btn-add-for-route" class="btn-modern btn-modern-sm btn-modern-primary d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-plus-circle"></i> Add Rule For This Route
                                </a>
                            </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('.rule-filter-btn');
    const rows = document.querySelectorAll('.rule-row');
    const emptyFilterRow = document.getElementById('empty-filter-row');
    const emptyFilterText = document.getElementById('empty-filter-text');
    const emptyAllRow = document.getElementById('empty-all-rules-row');
    const activeFilterTitle = document.getElementById('active-filter-title');
    const btnAddRule = document.getElementById('btn-add-rule');
    const btnAddForRoute = document.getElementById('btn-add-for-route');
    const baseAddUrl = "<?= base_url($prefix . '/departure-rules/create') ?>";

    function applyFilter(filterName, routeId) {
        let visibleCount = 0;

        filterBtns.forEach(btn => {
            if (btn.getAttribute('data-route-filter') === filterName) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        rows.forEach(row => {
            const dest = row.getAttribute('data-destination');
            if (filterName === 'all') {
                row.style.removeProperty('display');
                row.classList.remove('d-none');
                visibleCount++;
            } else if (filterName === 'general') {
                if (dest === 'general' || dest === '' || dest === '-') {
                    row.style.removeProperty('display');
                    row.classList.remove('d-none');
                    visibleCount++;
                } else {
                    row.style.setProperty('display', 'none', 'important');
                    row.classList.add('d-none');
                }
            } else {
                if (dest === filterName) {
                    row.style.removeProperty('display');
                    row.classList.remove('d-none');
                    visibleCount++;
                } else {
                    row.style.setProperty('display', 'none', 'important');
                    row.classList.add('d-none');
                }
            }
        });

        if (emptyAllRow) {
            if (rows.length === 0) {
                emptyAllRow.style.removeProperty('display');
                emptyAllRow.classList.remove('d-none');
            } else {
                emptyAllRow.style.setProperty('display', 'none', 'important');
                emptyAllRow.classList.add('d-none');
            }
        }

        if (rows.length > 0 && visibleCount === 0) {
            emptyFilterRow.style.removeProperty('display');
            emptyFilterRow.classList.remove('d-none');
            const routeDisplayName = filterName.toUpperCase();
            if (emptyFilterText) {
                emptyFilterText.textContent = `No departure rules configured for ${routeDisplayName}. Default wait time of 30 minutes will be used.`;
            }
            if (btnAddForRoute) {
                btnAddForRoute.href = routeId ? `${baseAddUrl}?route_id=${routeId}` : baseAddUrl;
            }
        } else if (emptyFilterRow) {
            emptyFilterRow.style.setProperty('display', 'none', 'important');
            emptyFilterRow.classList.add('d-none');
        }

        if (activeFilterTitle) {
            if (filterName === 'all') {
                activeFilterTitle.textContent = '(All Destinations)';
            } else if (filterName === 'general') {
                activeFilterTitle.textContent = '(Terminal Default)';
            } else {
                activeFilterTitle.textContent = `(${filterName.toUpperCase()})`;
            }
        }

        if (btnAddRule) {
            if (routeId) {
                btnAddRule.href = `${baseAddUrl}?route_id=${routeId}`;
            } else {
                btnAddRule.href = baseAddUrl;
            }
        }

        // Update URL query parameter without page reload
        const url = new URL(window.location.href);
        if (filterName === 'all') {
            url.searchParams.delete('route');
        } else {
            url.searchParams.set('route', filterName);
        }
        window.history.replaceState({}, '', url);
    }

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const filterName = this.getAttribute('data-route-filter');
            const routeId = this.getAttribute('data-route-id');
            applyFilter(filterName, routeId);
        });
    });

    // Check if initial filter is set in URL query param
    const initialUrl = new URL(window.location.href);
    const initialRoute = initialUrl.searchParams.get('route');
    if (initialRoute) {
        const matchingBtn = document.querySelector(`.rule-filter-btn[data-route-filter="${initialRoute.toLowerCase()}"]`);
        if (matchingBtn) {
            matchingBtn.click();
        }
    }
});
</script>

<?= view('templates/footer') ?>
