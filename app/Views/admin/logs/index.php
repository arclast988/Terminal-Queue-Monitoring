<?= view('templates/header', ['title' => $title]) ?>


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

    /* Enhanced table typography & sizing */
    .table-modern th {
        font-size: 13px !important;
        font-weight: 800 !important;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #475569 !important;
        padding: 14px 16px !important;
    }

    .table-modern td {
        font-size: 14px !important;
        padding: 15px 16px !important;
        vertical-align: middle;
    }

    .log-id-badge {
        font-family: 'Courier New', monospace;
        font-weight: 800;
        font-size: 14.5px;
        background: #f1f5f9;
        color: #0f172a;
        padding: 4px 10px;
        border-radius: 6px;
        display: inline-block;
        border: 1px solid #e2e8f0;
    }

    .log-user-email {
        font-weight: 700;
        color: var(--text-main, #0f172a);
        font-size: 14.5px;
        word-break: break-all;
    }

    .log-user-sub {
        font-size: 13px;
        color: #64748b;
        margin-top: 3px;
    }

    .badge-role {
        font-size: 12px;
        font-weight: 700;
        text-transform: capitalize;
        color: #64748b;
    }

    .action-badge-pill {
        display: inline-flex;
        align-items: center;
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 13.5px;
        font-weight: 700;
        line-height: 1.35;
        border: 1px solid transparent;
        white-space: normal;
        word-break: break-word;
        max-width: 220px;
    }

    .action-badge-default { background: #f1f5f9; color: #334155; border-color: #cbd5e1; }
    .action-badge-success { background: #ecfdf5; color: #065f46; border-color: #a7f3d0; }
    .action-badge-info    { background: #eff6ff; color: #1e40af; border-color: #bfdbfe; }
    .action-badge-warning { background: #fefce8; color: #854d0e; border-color: #fef08a; }
    .action-badge-danger  { background: #fef2f2; color: #991b1b; border-color: #fecaca; }

    .table-modern .log-details-cell {
        max-width: 450px;
        white-space: normal !important;
        overflow-wrap: anywhere;
        font-size: 14px;
        color: #1e293b;
        line-height: 1.55;
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
            <i class="bi bi-journal-text"></i> System Activity Logs
        </h1>
        <div class="retention-pill">
            <i class="bi bi-shield-check"></i> Auto-Retention: Logs are automatically kept for 60 days
        </div>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <button type="button" class="btn-modern btn-modern-primary" data-bs-toggle="modal" data-bs-target="#logReportFilterModal" title="Generate Activity Log Report">
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

<!-- Summary Stat Cards -->
<div class="row mb-4">
    <div class="col-12 col-md-6 col-xl-3 mb-4">
        <div class="stat-card-modern blue-accent fade-in">
            <div class="stat-card-icon" style="background: #DBEAFE; color: #1565c0;">
                <i class="bi bi-activity"></i>
            </div>
            <div class="stat-card-value"><?= number_format($stats['total'] ?? 0) ?></div>
            <div class="stat-card-label">Active Log Entries</div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3 mb-4">
        <div class="stat-card-modern success-accent fade-in">
            <div class="stat-card-icon" style="background: #D1FAE5; color: #059669;">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="stat-card-value"><?= number_format($stats['today'] ?? 0) ?></div>
            <div class="stat-card-label">Logged Today</div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3 mb-4">
        <div class="stat-card-modern gold-accent fade-in">
            <div class="stat-card-icon" style="background: #FEF3C7; color: #d97706;">
                <i class="bi bi-calendar-week"></i>
            </div>
            <div class="stat-card-value"><?= number_format($stats['this_week'] ?? 0) ?></div>
            <div class="stat-card-label">Past 7 Days</div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3 mb-4">
        <div class="stat-card-modern fade-in">
            <div class="stat-card-icon" style="background: #fdecea; color: #c62828;">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-card-value"><?= number_format($stats['active_users'] ?? 0) ?></div>
            <div class="stat-card-label">Active Users Tracked</div>
        </div>
    </div>
</div>

<!-- Search & Filters -->
<div class="modern-card shadow-modern fade-in mb-4">
    <div class="modern-card-body">
        <form method="get" action="<?= base_url('admin/logs') ?>" id="logsFilterForm" class="row g-3 align-items-end">
            <div class="col-12 col-lg-3 col-md-6">
                <label class="form-label-modern"><i class="bi bi-search me-1"></i> Keyword Search</label>
                <div class="position-relative">
                    <input type="text" class="input-modern pe-4" id="admin-logs-q" name="q" placeholder="Actions, details, user..." value="<?= esc($search ?? '') ?>" oninput="toggleAdminLogsClear(this.value)">
                    <button type="button" class="btn-clear-search" id="clear-admin-logs-search" onclick="clearAdminLogsSearch()" style="<?= !empty($search) ? 'display: inline-flex !important;' : 'display: none !important;' ?> right: 10px;" title="Clear search">
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
                <label class="form-label-modern"><i class="bi bi-lightning-charge me-1"></i> Action</label>
                <select name="action_type" class="input-modern">
                    <option value="">All Actions</option>
                    <?php if (!empty($actions)): ?>
                        <?php foreach ($actions as $act): ?>
                            <option value="<?= esc($act['action']) ?>" <?= ($action_type ?? '') === $act['action'] ? 'selected' : '' ?>>
                                <?= esc($act['action']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-12 col-lg-2 col-md-4">
                <label class="form-label-modern"><i class="bi bi-person me-1"></i> User</label>
                <select name="user_id" class="input-modern">
                    <option value="">All Users</option>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= esc($u['id']) ?>" <?= ($user_id ?? '') == $u['id'] ? 'selected' : '' ?>>
                                <?= esc($u['full_name'] ?? $u['username']) ?>
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

        <div class="quick-chips-row" id="logsQuickChipsRow">
            <span class="text-muted" style="font-size: 12px; font-weight: 700; text-transform: uppercase;">
                <i class="bi bi-clock-history me-1"></i> Quick Ranges:
            </span>
            <button type="button" class="quick-chip quick-chip-btn <?= empty($from_date) && empty($to_date) ? 'active' : '' ?>" data-from="" data-to="">All (60d)</button>
            <button type="button" class="quick-chip quick-chip-btn <?= ($from_date === date('Y-m-d') && $to_date === date('Y-m-d')) ? 'active' : '' ?>" data-from="<?= date('Y-m-d') ?>" data-to="<?= date('Y-m-d') ?>">Today</button>
            <button type="button" class="quick-chip quick-chip-btn <?= ($from_date === date('Y-m-d', strtotime('-7 days')) && $to_date === date('Y-m-d')) ? 'active' : '' ?>" data-from="<?= date('Y-m-d', strtotime('-7 days')) ?>" data-to="<?= date('Y-m-d') ?>">Last 7 Days</button>
            <button type="button" class="quick-chip quick-chip-btn <?= ($from_date === date('Y-m-d', strtotime('-30 days')) && $to_date === date('Y-m-d')) ? 'active' : '' ?>" data-from="<?= date('Y-m-d', strtotime('-30 days')) ?>" data-to="<?= date('Y-m-d') ?>">Last 30 Days</button>
            <span id="clearFilterContainer" class="ms-auto">
                <?php if (!empty($search) || !empty($from_date) || !empty($to_date) || !empty($action_type) || !empty($user_id)): ?>
                    <a href="<?= base_url('admin/logs') ?>" id="clearFiltersBtn" class="btn-modern btn-modern-sm btn-modern-outline" title="Clear all filters">
                        <i class="bi bi-x-circle me-1"></i> Clear Filters
                    </a>
                <?php endif; ?>
            </span>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="modern-card shadow-modern fade-in mb-4" id="logsTableCard">
    <div class="modern-card-header d-flex align-items-center justify-content-between">
        <span class="modern-card-title" id="logsTableTitle" style="font-size: 16px;">
            <i class="bi bi-list-ul" style="color: var(--primary-red, #b71c1c);"></i>
            Log Entries
            <?php if (!empty($logs)): ?>
                <span class="badge-modern badge-modern-secondary ms-2" id="logsCountBadge" style="font-size: 13px;"><?= count($logs) ?> on this page</span>
            <?php endif; ?>
        </span>
    </div>
    <div class="modern-card-body p-0">
        <div class="table-responsive">
            <table class="table-modern" id="logsTable">
                <thead>
                    <tr>
                        <th style="width: 85px;">ID</th>
                        <th style="width: 175px;">Timestamp</th>
                        <th style="width: 230px;">User</th>
                        <th style="width: 210px;">Action</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs) && is_array($logs)): ?>
                        <?php foreach ($logs as $log): ?>
                            <?php
                                $actLower = strtolower($log['action'] ?? '');
                                $badgeClass = 'action-badge-default';
                                if (strpos($actLower, 'login') !== false || strpos($actLower, 'auth') !== false) {
                                    $badgeClass = 'action-badge-success';
                                } elseif (strpos($actLower, 'add') !== false || strpos($actLower, 'create') !== false || strpos($actLower, 'register') !== false) {
                                    $badgeClass = 'action-badge-info';
                                } elseif (strpos($actLower, 'update') !== false || strpos($actLower, 'edit') !== false || strpos($actLower, 'reassign') !== false) {
                                    $badgeClass = 'action-badge-warning';
                                } elseif (strpos($actLower, 'delete') !== false || strpos($actLower, 'remove') !== false) {
                                    $badgeClass = 'action-badge-danger';
                                }
                            ?>
                            <tr>
                                <td data-label="ID">
                                    <span class="log-id-badge">#<?= $log['id'] ?></span>
                                </td>
                                <td data-label="Timestamp">
                                    <div style="white-space: nowrap; font-size: 14.5px; font-weight: 700; color: var(--text-main, #0f172a);">
                                        <i class="bi bi-calendar3 text-muted me-1" style="font-size: 12px;"></i><?= !empty($log['timestamp']) ? date('M d, Y', strtotime($log['timestamp'])) : 'N/A' ?>
                                    </div>
                                    <div class="text-muted" style="font-size: 13px; font-weight: 600; font-family: monospace; margin-top: 2px;">
                                        <i class="bi bi-clock text-muted me-1" style="font-size: 12px;"></i><?= !empty($log['timestamp']) ? date('H:i:s', strtotime($log['timestamp'])) : '' ?>
                                    </div>
                                </td>
                                <td data-label="User">
                                    <?php if (!empty($log['username'])): ?>
                                        <div class="log-user-email">
                                            <?= esc($log['email'] ?: $log['username']) ?>
                                        </div>
                                        <div class="log-user-sub">
                                            <?= esc($log['full_name'] ?? $log['username']) ?>
                                            <?php if (!empty($log['role'])): ?>
                                                <?php 
                                                    $displayRole = $log['role'];
                                                    if ($displayRole === 'staff') $displayRole = 'Dispatcher';
                                                    else $displayRole = ucwords(str_replace('_', ' ', $displayRole));
                                                ?>
                                                &bull; <span class="badge-role"><?= esc($displayRole) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic" style="font-size: 13.5px;">System / Automatic</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Action">
                                    <span class="action-badge-pill <?= $badgeClass ?>">
                                        <?= esc($log['action']) ?>
                                    </span>
                                </td>
                                <td data-label="Details" class="log-details-cell">
                                    <?= esc($log['details'] ?? '—') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted empty-state-table">
                                <i class="bi bi-activity fs-1 d-block mb-3 opacity-50"></i>
                                <div class="fw-bold fs-6 empty-state-title">No activity logs found</div>
                                <small class="empty-state-subtitle">Try adjusting your search criteria or date filters.</small>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div id="logsPaginationWrap">
        <?php if ($pager && $pager->getPageCount() > 1): ?>
            <div class="modern-card-footer bg-white py-3 d-flex justify-content-center">
                <?= $pager->links() ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= view('admin/modals/log_report_filter', ['actions' => $actions, 'users' => $users]) ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('logsFilterForm');
    var fromInput = document.getElementById('fromDate');
    var toInput = document.getElementById('toDate');
    var tableCard = document.getElementById('logsTableCard');

    function updateActiveChips(from, to) {
        var quickButtons = document.querySelectorAll('#logsQuickChipsRow .quick-chip-btn');
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
    function fetchFilteredLogs(url, pushState) {
        if (typeof pushState === 'undefined') pushState = true;
        if (isFetching) return;
        isFetching = true;

        if (tableCard) {
            tableCard.style.opacity = '0.5';
            tableCard.style.pointerEvents = 'none';
            tableCard.style.transition = 'opacity 0.15s ease';
        }

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-Silent': 'true' }
        })
        .then(function(res) {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.text();
        })
        .then(function(html) {
            var parser = new DOMParser();
            var doc = parser.parseFromString(html, 'text/html');

            // 1. Update Table Body
            var newTbody = doc.querySelector('#logsTable tbody');
            var curTbody = document.querySelector('#logsTable tbody');
            if (newTbody && curTbody) {
                curTbody.innerHTML = newTbody.innerHTML;
            }

            // 2. Update Header / Count Badge
            var newTitle = doc.getElementById('logsTableTitle');
            var curTitle = document.getElementById('logsTableTitle');
            if (newTitle && curTitle) {
                curTitle.innerHTML = newTitle.innerHTML;
            }

            // 3. Update Pagination
            var newPagerWrap = doc.getElementById('logsPaginationWrap');
            var curPagerWrap = document.getElementById('logsPaginationWrap');
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
                var actionInput = form ? form.querySelector('[name="action_type"]') : null;
                var userInput = form ? form.querySelector('[name="user_id"]') : null;

                if (qInput) qInput.value = params.get('q') || '';
                if (fromInput) fromInput.value = params.get('from_date') || '';
                if (toInput) toInput.value = params.get('to_date') || '';
                if (actionInput) actionInput.value = params.get('action_type') || '';
                if (userInput) userInput.value = params.get('user_id') || '';

                updateActiveChips(params.get('from_date') || '', params.get('to_date') || '');
            } catch(e) {}

            // 7. Update URL in browser
            if (pushState) {
                window.history.pushState({ url: url }, '', url);
            }
        })
        .catch(function(err) {
            console.error('AJAX logs filter error:', err);
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
        var chipBtn = e.target.closest('#logsQuickChipsRow .quick-chip-btn');
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
            fetchFilteredLogs(targetUrl, true);
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
            fetchFilteredLogs(targetUrl, true);
            return;
        }

        // Pagination links click
        var pageLink = e.target.closest('#logsPaginationWrap a');
        if (pageLink) {
            e.preventDefault();
            var targetUrl = pageLink.getAttribute('href');
            if (targetUrl) {
                fetchFilteredLogs(targetUrl, true);
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
            fetchFilteredLogs(targetUrl, true);
        });
    }

    function toggleAdminLogsClear(val) {
        var btn = document.getElementById('clear-admin-logs-search');
        if (btn) {
            if (val && val.trim().length > 0) {
                btn.style.setProperty('display', 'inline-flex', 'important');
            } else {
                btn.style.setProperty('display', 'none', 'important');
            }
        }
    }
    window.toggleAdminLogsClear = toggleAdminLogsClear;

    window.clearAdminLogsSearch = function() {
        var input = document.getElementById('admin-logs-q');
        if (input) {
            input.value = '';
            toggleAdminLogsClear('');
            input.focus();
            if (form) {
                var submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) submitBtn.click();
                else form.submit();
            }
        }
    };

    var logsSearchInput = document.getElementById('admin-logs-q');
    if (logsSearchInput) toggleAdminLogsClear(logsSearchInput.value);

    // Handle browser back and forward navigation
    window.addEventListener('popstate', function(e) {
        fetchFilteredLogs(window.location.href, false);
    });
});
</script>

<?= view('templates/footer') ?>
