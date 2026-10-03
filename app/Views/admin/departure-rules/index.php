<?= view('templates/header', ['title' => $title]) ?>


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
    align-items: baseline;
    gap: 12px;
    flex-wrap: nowrap;
    width: 100%;
    min-width: 0;
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
    flex: 1 1 auto;
    min-width: 0;
    overflow-x: auto;
    overflow-y: hidden;
    flex-wrap: nowrap;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    padding: 9px 3px 12px;
}

.rule-filter-btn {
    display: inline-flex;
    flex: 0 0 auto;
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
    transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
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
@media (hover: none), (pointer: coarse) {
    .rule-filter-btn:hover { transform: none; }
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

@media (max-width: 768px) {
    .rule-filter-left {
        flex-direction: column;
        align-items: stretch;
        gap: 8px;
    }
    .rule-filter-group {
        width: 100%;
        max-width: 100%;
        flex: 0 0 auto;
    }
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
    table-layout: auto;
    width: 100%;
}

#departure-rules-table th,
#departure-rules-table td {
    vertical-align: middle;
}

#departure-rules-table th:last-child,
#departure-rules-table td:last-child {
    white-space: nowrap;
    width: 170px;
}

.btn-action-edit {
    background: #ffffff !important;
    border: 1.5px solid var(--primary-blue, #1565c0) !important;
    color: var(--primary-blue, #1565c0) !important;
    -webkit-text-fill-color: var(--primary-blue, #1565c0) !important;
    font-weight: 700 !important;
    border-radius: 8px !important;
    padding: 6px 14px !important;
    font-size: 13.5px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    text-decoration: none !important;
    box-shadow: none !important;
    transition: all 0.2s ease !important;
    white-space: nowrap !important;
}
.btn-action-edit i {
    color: var(--primary-blue, #1565c0) !important;
    font-size: 13px !important;
}
.btn-action-edit:hover {
    background: var(--primary-blue, #1565c0) !important;
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    border-color: var(--primary-blue, #1565c0) !important;
}
.btn-action-edit:hover i {
    color: #ffffff !important;
}

.btn-action-delete {
    background: var(--danger-light, #fee2e2) !important;
    border: 1.5px solid transparent !important;
    color: var(--danger-dark, #dc2626) !important;
    -webkit-text-fill-color: var(--danger-dark, #dc2626) !important;
    font-weight: 700 !important;
    border-radius: 8px !important;
    padding: 6px 14px !important;
    font-size: 13.5px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    box-shadow: none !important;
    transition: all 0.2s ease !important;
    white-space: nowrap !important;
}
.btn-action-delete i {
    color: var(--danger-dark, #dc2626) !important;
    font-size: 13px !important;
}
.btn-action-delete:hover {
    background: var(--danger-dark, #dc2626) !important;
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    border-color: var(--danger-dark, #dc2626) !important;
}
.btn-action-delete:hover i {
    color: #ffffff !important;
}

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
    
    .table-modern tbody tr td[colspan],
    .table-modern tbody tr td.empty-state-table {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
        text-align: center !important;
        padding: 28px 14px !important;
        box-sizing: border-box !important;
    }
    .table-modern tbody tr td[colspan] > *,
    .table-modern tbody tr td[colspan] *,
    .table-modern tbody tr td.empty-state-table > *,
    .table-modern tbody tr td.empty-state-table * {
        text-align: center !important;
        margin-left: auto !important;
        margin-right: auto !important;
        white-space: normal !important;
        word-break: break-word !important;
        overflow-wrap: anywhere !important;
        box-sizing: border-box !important;
        max-width: 100% !important;
    }
    #empty-filter-text,
    .empty-state-title {
        white-space: normal !important;
        word-break: break-word !important;
        overflow-wrap: anywhere !important;
        max-width: 100% !important;
        line-height: 1.45 !important;
        padding: 0 4px !important;
        font-size: 14.5px !important;
    }
    .empty-state-subtitle {
        white-space: normal !important;
        word-break: break-word !important;
        overflow-wrap: anywhere !important;
        max-width: 100% !important;
        line-height: 1.4 !important;
        padding: 0 4px !important;
        display: block !important;
    }
    .table-modern tbody tr td[colspan]::before {
        display: none !important;
        content: none !important;
    }
}
</style>

<div class="page-header-modern fade-in">
    <h1 class="page-title-modern">
        <i class="bi bi-clock-history"></i>
        Departure Time Rules
    </h1>
    <?php if ($prefix !== 'staff' || !empty($routes)): ?>
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
                        <th>Day / Round</th>
                        <th>Time From (HH:MM)</th>
                        <th>Time To (HH:MM)</th>
                        <th>Wait Time (HH:MM)</th>
                        <th>Label</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rules) && is_array($rules)): ?>
                        <?php foreach ($rules as $rule): ?>
                            <?php $destSlug = !empty($rule['route_destination']) && $rule['route_destination'] !== '-' ? strtolower($rule['route_destination']) : 'general'; ?>
                            <tr class="rule-row" data-destination="<?= esc($destSlug) ?>" data-route-id="<?= esc($rule['route_id'] ?? '') ?>">
                                <td data-label="Terminal">
                                    <span class="fw-bold" style="color: var(--text-main, #0f172a); font-size: 13.5px;">
                                        <i class="bi bi-building me-1 text-muted"></i><?= strtoupper(esc($rule['terminal_name'] ?? '-')) ?>
                                    </span>
                                </td>
                                <td data-label="Destination">
                                    <?php if (!empty($rule['route_destination']) && $rule['route_destination'] !== '-'): ?>
                                        <span class="route-tag-pill"><i class="bi bi-geo-alt-fill text-danger"></i> <?= strtoupper(esc($rule['route_destination'])) ?></span>
                                    <?php else: ?>
                                        <span class="badge-modern badge-modern-secondary"><i class="bi bi-sliders"></i> Terminal Default</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Day / Round"><span class="badge-modern badge-modern-primary"><?= esc([1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',7=>'Sunday'][(int) ($rule['day_of_week'] ?? 0)] ?? 'Every day') ?></span><br><span class="small"><?= !empty($rule['round_number']) ? 'Round ' . (int) $rule['round_number'] : 'All rounds' ?></span></td>
                                <td data-label="Time From (HH:MM)"><span style="white-space: nowrap; font-weight: 600; font-size: 13px; color: var(--text-main);"><i class="bi bi-clock me-1 text-muted"></i><?= date('H:i', strtotime($rule['time_from'])) ?></span></td>
                                <td data-label="Time To (HH:MM)"><span style="white-space: nowrap; font-weight: 600; font-size: 13px; color: var(--text-main);"><i class="bi bi-clock me-1 text-muted"></i><?= date('H:i', strtotime($rule['time_to'])) ?></span></td>
                                <td data-label="Wait Time (HH:MM)">
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
                                <?php if (!empty($rule['can_manage']) || session()->get('role') !== 'staff'): ?>
                                <td data-label="Action">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="<?= base_url($prefix . '/departure-rules/edit/'.$rule['id']) ?>" class="btn-modern btn-action-edit btn-modern-sm rule-edit-link" title="Edit">
                                            <i class="bi bi-pencil"></i> <span class="action-label">Edit</span>
                                        </a>
                                        <form id="delete-rule-form-<?= $rule['id'] ?>" action="<?= base_url($prefix . '/departure-rules/delete/'.$rule['id']) ?>" method="post" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="return_route" class="rule-return-route" value="all">
                                            <button type="button" class="btn-modern btn-modern-sm btn-action-delete" title="Delete"
                                                onclick="showDeleteRuleModal({
                                                    formId: 'delete-rule-form-<?= $rule['id'] ?>',
                                                    ruleId: '<?= $rule['id'] ?>',
                                                    destination: '<?= esc(addslashes(!empty($rule['route_destination']) && $rule['route_destination'] !== '-' ? strtoupper($rule['route_destination']) : 'TERMINAL DEFAULT')) ?>',
                                                    timeWindow: '<?= esc(addslashes(date('H:i', strtotime($rule['time_from'])) . ' - ' . date('H:i', strtotime($rule['time_to'])))) ?>',
                                                    label: '<?= esc(addslashes(!empty($rule['label']) && $rule['label'] !== '-' ? $rule['label'] : '')) ?>',
                                                    waitMinutes: '<?= (int)($rule['wait_minutes'] ?? 0) ?>'
                                                })">
                                                <i class="bi bi-trash"></i> <span class="action-label">Delete</span>
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
                            <td colspan="8" class="text-center py-5 text-muted empty-state-table">
                                <i class="bi bi-clock-history fs-1 d-block mb-3 opacity-50"></i>
                                <div class="fw-bold fs-6 empty-state-title">No departure rules configured</div>
                                <small class="empty-state-subtitle">The terminal default interval (30 minutes) will be used.</small>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr id="empty-filter-row" class="d-none" style="display: none !important;">
                        <td colspan="8" class="text-center py-5 text-muted empty-state-table">
                            <i class="bi bi-search fs-1 d-block mb-3 opacity-50"></i>
                            <div class="fw-bold fs-6 empty-state-title" id="empty-filter-text" style="white-space: normal !important; overflow-wrap: anywhere !important; word-break: break-word !important; max-width: 100% !important;">No departure rules configured for this route</div>
                            <small class="empty-state-subtitle">No custom dispatch interval has been configured for this destination.</small>
                            <?php if ($prefix !== 'staff' || !empty($routes)): ?>
                            <div class="mt-3">
                                <a href="<?= base_url($prefix . '/departure-rules/create') ?>" id="btn-add-for-route" class="btn-modern btn-modern-sm btn-modern-primary" style="font-size: 13.5px; font-weight: 700; padding: 7px 16px; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px;">
                                    <i class="bi bi-plus-circle" style="font-size: 14px !important; display: inline-block !important; margin: 0 !important; color: #ffffff !important; opacity: 1 !important;"></i> Add Rule For This Route
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
    let appliedRouteFilter = null;

    function addUrlForFilter(routeId, filterName) {
        const url = new URL(baseAddUrl, window.location.href);
        if (routeId) url.searchParams.set('route_id', routeId);
        url.searchParams.set('return_route', filterName);
        return url.toString();
    }

    function applyFilter(filterName, routeId) {
        if (appliedRouteFilter === filterName) return;
        appliedRouteFilter = filterName;
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
            const show = filterName === 'all' ||
                (filterName === 'general' ? (dest === 'general' || dest === '' || dest === '-') : dest === filterName);
            if (show) {
                if (row.classList.contains('d-none') || row.style.display === 'none') {
                    row.style.removeProperty('display');
                    row.classList.remove('d-none');
                }
                visibleCount++;
            } else if (!row.classList.contains('d-none') || row.style.display !== 'none') {
                row.style.setProperty('display', 'none', 'important');
                row.classList.add('d-none');
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

        if (btnAddRule) btnAddRule.href = addUrlForFilter(routeId, filterName);
        if (btnAddForRoute) btnAddForRoute.href = addUrlForFilter(routeId, filterName);
        document.querySelectorAll('.rule-edit-link').forEach(link => {
            const editUrl = new URL(link.href, window.location.href);
            editUrl.searchParams.set('return_route', filterName);
            link.href = editUrl.toString();
        });
        document.querySelectorAll('.rule-return-route').forEach(input => {
            input.value = filterName;
        });

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
        const matchingBtn = Array.from(filterBtns).find(btn => btn.getAttribute('data-route-filter') === initialRoute.toLowerCase());
        if (matchingBtn) {
            matchingBtn.click();
        }
    }
});
</script>

<?php if (!empty($rules)): ?>
<!-- ══════════════════════════════════════════════════ -->
<!--  Delete Departure Rule Confirmation Modal          -->
<!-- ══════════════════════════════════════════════════ -->
<div class="modal fade" id="deleteRuleConfirmModal" tabindex="-1" aria-labelledby="deleteRuleConfirmLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px; margin: 1.75rem auto;">
        <div class="modal-content delete-rule-modal-content">
            <!-- Accent stripe -->
            <div class="delete-rule-stripe"></div>

            <div class="modal-body text-center p-4">
                <!-- Icon badge -->
                <div class="delete-rule-icon-wrapper">
                    <i class="fas fa-trash-can"></i>
                </div>

                <h4 class="delete-rule-modal-title" id="deleteRuleConfirmLabel">Delete Departure Rule?</h4>
                <p class="delete-rule-modal-desc" id="deleteRuleConfirmMessage">
                    Are you sure you want to delete this departure rule? This action cannot be undone.
                </p>

                <!-- Rule preview chip -->
                <div class="delete-rule-item-chip" id="deleteRuleItemChip">
                    <i class="bi bi-clock-history text-danger"></i>
                    <span id="deleteRuleTimeLabel" class="fw-bold">05:00 - 07:00</span>
                    <span class="delete-rule-dest-tag" id="deleteRuleDestTag">ROUTE</span>
                </div>

                <!-- Subtitle for label and wait time -->
                <div id="deleteRuleSubtitle" class="mt-2 text-muted" style="font-size: 0.825rem;"></div>
            </div>

            <div class="modal-footer delete-rule-modal-footer">
                <button type="button" class="btn delete-rule-btn-cancel" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn delete-rule-btn-confirm" id="deleteRuleConfirmBtn">
                    <i class="fas fa-trash-alt me-1"></i> Yes, Delete
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* ── Delete Departure Rule Confirmation Modal Styles ── */
.delete-rule-modal-content {
    border: 0 !important;
    border-radius: 18px !important;
    box-shadow: 0 20px 35px -5px rgba(15, 23, 42, 0.25), 0 10px 15px -5px rgba(15, 23, 42, 0.1) !important;
    background: #ffffff !important;
    overflow: hidden !important;
    position: relative !important;
    font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif !important;
}

.delete-rule-stripe {
    height: 4px;
    width: 100%;
    background: linear-gradient(90deg, #dc2626 0%, #ef4444 50%, #f97316 100%);
}

.delete-rule-icon-wrapper {
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

.delete-rule-modal-content:hover .delete-rule-icon-wrapper {
    transform: scale(1.04);
}

.delete-rule-modal-title {
    font-size: 1.35rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 8px;
    letter-spacing: -0.01em;
}

.delete-rule-modal-desc {
    color: #64748b;
    font-size: 0.925rem;
    line-height: 1.5;
    margin-bottom: 16px;
    padding: 0 10px;
}

.delete-rule-item-chip {
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

.delete-rule-item-chip i {
    font-size: 0.95rem;
    flex-shrink: 0;
}

.delete-rule-dest-tag {
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

.delete-rule-modal-footer {
    border: 0 !important;
    padding: 0 24px 24px 24px !important;
    display: flex !important;
    gap: 12px !important;
    justify-content: stretch !important;
    background: transparent !important;
}

.delete-rule-btn-cancel {
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

.delete-rule-btn-cancel:hover,
.delete-rule-btn-cancel:focus {
    background: #e2e8f0 !important;
    color: #0f172a !important;
    border-color: #cbd5e1 !important;
}

.delete-rule-btn-confirm {
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

.delete-rule-btn-confirm:hover,
.delete-rule-btn-confirm:focus {
    background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%) !important;
    box-shadow: 0 6px 16px rgba(220, 38, 38, 0.38) !important;
    transform: translateY(-1px);
}

.delete-rule-btn-confirm.is-loading {
    pointer-events: none !important;
    opacity: 0.75 !important;
}

/* Ensure modal paints above fixed navbar */
body.modal-open #deleteRuleConfirmModal,
#deleteRuleConfirmModal {
    z-index: 100050 !important;
}


</style>

<script>
(function() {
    var _deleteRuleFormId = null;

    window.showDeleteRuleModal = function(opts) {
        opts = opts || {};
        var modalEl = document.getElementById('deleteRuleConfirmModal');
        if (!modalEl || !window.bootstrap) {
            if (window.confirm('Are you sure you want to delete this rule?')) {
                var form = document.getElementById(opts.formId);
                if (form) form.submit();
            }
            return;
        }

        var timeEl = document.getElementById('deleteRuleTimeLabel');
        var destTagEl = document.getElementById('deleteRuleDestTag');
        var subEl = document.getElementById('deleteRuleSubtitle');

        if (timeEl) {
            timeEl.textContent = opts.timeWindow || 'Rule Time';
        }

        if (destTagEl) {
            if (opts.destination) {
                destTagEl.textContent = opts.destination;
                destTagEl.style.display = '';
            } else {
                destTagEl.style.display = 'none';
            }
        }

        if (subEl) {
            var parts = [];
            if (opts.label) {
                parts.push(opts.label);
            }
            if (opts.waitMinutes) {
                var m = parseInt(opts.waitMinutes, 10);
                var hrs = Math.floor(m / 60);
                var rem = m % 60;
                var waitStr = 'Wait Time: ' + (hrs > 0 ? hrs + 'h ' : '') + rem + ' min';
                parts.push(waitStr);
            }
            subEl.textContent = parts.join(' · ');
            subEl.style.display = parts.length > 0 ? '' : 'none';
        }

        var confirmBtn = document.getElementById('deleteRuleConfirmBtn');
        if (confirmBtn) {
            confirmBtn.classList.remove('is-loading');
            confirmBtn.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Yes, Delete';
        }

        _deleteRuleFormId = opts.formId || null;
        window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
    };

    document.addEventListener('DOMContentLoaded', function() {
        var confirmBtn = document.getElementById('deleteRuleConfirmBtn');
        if (!confirmBtn) return;

        confirmBtn.addEventListener('click', function() {
            if (!_deleteRuleFormId) return;

            confirmBtn.classList.add('is-loading');
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Deleting...';

            var form = document.getElementById(_deleteRuleFormId);
            if (form) {
                form.submit();
            } else {
                var modalEl = document.getElementById('deleteRuleConfirmModal');
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
