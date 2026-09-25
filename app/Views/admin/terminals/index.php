<?= view('templates/header', ['title' => $title]) ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">

<style>
/* Scoped Selection & Bulk Action Rules - Matches App Action Buttons */
.bulk-col,
.bulk-select-cell,
#terminals-table:not(.selection-mode-active) .bulk-col,
#terminals-table:not(.selection-mode-active) .bulk-select-cell,
table:not(.selection-mode-active) .bulk-col,
table:not(.selection-mode-active) .bulk-select-cell {
    display: none !important;
}
@media (min-width: 769px) {
    .bulk-col,
    .bulk-select-cell {
        width: 44px !important;
        min-width: 44px !important;
        max-width: 44px !important;
        text-align: center !important;
        vertical-align: middle !important;
        padding: 8px 6px !important;
    }
    .table-modern.selection-mode-active .bulk-col,
    .table-modern.selection-mode-active .bulk-select-cell,
    .selection-mode-active .bulk-col,
    .selection-mode-active .bulk-select-cell {
        display: table-cell !important;
    }
}
</style>

<?php
$isAdmin = in_array(session()->get('role'), ['super_admin', 'admin'], true);
?>

<div class="page-header-modern fade-in">
    <h1 class="page-title-modern">
        <i class="bi bi-geo-alt"></i>
        Terminals Management
    </h1>
    <?php if ($isAdmin): ?>
    <div>
        <a href="<?= base_url('admin/terminals/create') ?>" class="btn-modern btn-modern-primary">
            <i class="bi bi-plus-circle"></i> Add New Terminal
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

<div class="modern-card shadow-modern fade-in">
    <div class="modern-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="modern-card-title">
            <i class="bi bi-list" style="color: var(--primary-red);"></i>
            Terminal List
        </span>
        <?php if ($isAdmin): ?>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn-modern btn-modern-sm btn-modern-outline" id="btn-toggle-select-terminals" onclick="toggleTerminalSelectMode()" title="Toggle selection mode for batch actions" <?= empty($terminals) ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '' ?>>
                <i class="bi bi-check2-square me-1"></i> <span id="btn-select-terminals-text">Select</span>
            </button>
        </div>
        <?php endif; ?>
    </div>
    <div class="modern-card-body">
        <!-- Integrated Top Bulk Action Bar -->
        <div id="terminal-bulk-toolbar" class="bulk-action-top-bar" style="display: none;">
            <div class="bulk-bar-info">
                <label class="bulk-select-all-wrap mb-0">
                    <input type="checkbox" id="select-all-terminals" class="form-check-input select-all-checkbox m-0" style="width: 17px; height: 17px; cursor: pointer;">
                    <span class="fw-semibold text-dark" style="font-size: 13.5px;">Select All</span>
                </label>
                <span class="bulk-bar-divider"></span>
                <span class="bulk-count-badge">
                    <span id="terminal-selected-count">0</span> <span id="terminal-selected-text">selected</span>
                </span>
            </div>
            <div class="bulk-bar-actions">
                <button type="button" class="btn-modern btn-modern-sm btn-action-delete btn-bulk-delete" id="btn-bulk-delete-terminals" onclick="openTerminalBulkModal()" disabled>
                    <i class="bi bi-trash"></i> <span>Delete Selected</span>
                </button>
                <button type="button" class="btn-modern btn-modern-sm btn-modern-outline btn-bulk-cancel" onclick="toggleTerminalSelectMode(false)">
                    <i class="bi bi-x"></i> <span>Cancel</span>
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table-modern" id="terminals-table">
                <thead>
                    <tr>
                        <?php if ($isAdmin): ?>
                        <th class="bulk-col" style="display: none; width: 44px; text-align: center;">
                            <input type="checkbox" id="table-head-select-all-terminals" class="form-check-input select-all-checkbox m-0" style="width: 17px; height: 17px; cursor: pointer;" title="Select All">
                        </th>
                        <?php endif; ?>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Capacity</th>
                        <th>Created At</th>
                        <?php if ($isAdmin): ?>
                        <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($terminals) && is_array($terminals)): ?>
                        <?php foreach ($terminals as $terminal): ?>
                            <tr data-terminal-id="<?= $terminal['id'] ?>">
                                <?php if ($isAdmin): ?>
                                <td data-label="Select" class="bulk-col bulk-select-cell" style="display: none; text-align: center;">
                                    <input type="checkbox" class="form-check-input terminal-row-checkbox" value="<?= $terminal['id'] ?>" data-id="<?= $terminal['id'] ?>" data-name="<?= esc(addslashes($terminal['name'])) ?>" data-location="<?= esc(addslashes($terminal['location'] ?? '')) ?>" title="Select terminal <?= esc($terminal['name']) ?>">
                                </td>
                                <?php endif; ?>
                                <td data-label="ID"><strong>#<?= $terminal['id'] ?></strong></td>
                                <td data-label="Name"><?= esc($terminal['name']) ?></td>
                                <td data-label="Location"><?= esc($terminal['location']) ?></td>
                                <td data-label="Capacity"><span class="badge-modern badge-modern-primary"><?= (int) $terminal['capacity'] ?> vehicles</span></td>
                                <td data-label="Created At">
                                    <div style="white-space: nowrap; font-size: 13px; font-weight: 600; color: var(--text-main);">
                                        <i class="bi bi-calendar3 text-muted me-1" style="font-size: 12px;"></i><?= !empty($terminal['created_at']) ? date('M d, Y', strtotime($terminal['created_at'])) : 'N/A' ?>
                                    </div>
                                </td>
                                <?php if ($isAdmin): ?>
                                <td data-label="Actions">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="<?= base_url('admin/terminals/edit/'.$terminal['id']) ?>" class="btn-modern btn-modern-outline btn-modern-sm" title="Edit">
                                            <i class="bi bi-pencil"></i> <span class="action-label">Edit</span>
                                        </a>
                                        <form id="delete-terminal-form-<?= $terminal['id'] ?>" action="<?= base_url('admin/terminals/delete/'.$terminal['id']) ?>" method="post" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="button" class="btn-modern btn-modern-sm btn-action-delete" title="Delete"
                                                onclick="showDeleteTerminalModal({
                                                    formId: 'delete-terminal-form-<?= $terminal['id'] ?>',
                                                    terminalId: '<?= $terminal['id'] ?>',
                                                    name: '<?= esc(addslashes($terminal['name'])) ?>',
                                                    location: '<?= esc(addslashes($terminal['location'] ?? '')) ?>',
                                                    capacity: '<?= esc(addslashes((string)($terminal['capacity'] ?? ''))) ?>'
                                                })">
                                                <i class="bi bi-trash"></i> <span class="action-label">Delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= $isAdmin ? '7' : '5' ?>" class="text-center py-5 text-muted empty-state-table">
                                <i class="bi bi-building fs-1 d-block mb-3 opacity-50"></i>
                                <div class="fw-bold fs-6 empty-state-title">No terminals found</div>
                                <small class="empty-state-subtitle"><?= $isAdmin ? 'Click Add New Terminal above to create one.' : 'No active terminals are currently registered.' ?></small>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($isAdmin && !empty($terminals)): ?>
<!-- ══════════════════════════════════════════════════ -->
<!--  Delete Terminal Confirmation Modal               -->
<!-- ══════════════════════════════════════════════════ -->
<div class="modal fade" id="deleteTerminalConfirmModal" tabindex="-1" aria-labelledby="deleteTerminalConfirmLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px; margin: 1.75rem auto;">
        <div class="modal-content delete-terminal-modal-content">
            <!-- Accent stripe -->
            <div class="delete-terminal-stripe"></div>

            <div class="modal-body text-center p-4">
                <!-- Icon badge -->
                <div class="delete-terminal-icon-wrapper">
                    <i class="fas fa-trash-can"></i>
                </div>

                <h4 class="delete-terminal-modal-title" id="deleteTerminalConfirmLabel">Delete Terminal?</h4>
                <p class="delete-terminal-modal-desc" id="deleteTerminalConfirmMessage">
                    Are you sure you want to delete this terminal? This action cannot be undone.
                </p>

                <!-- Terminal preview chip -->
                <div class="delete-terminal-item-chip" id="deleteTerminalItemChip">
                    <i class="bi bi-geo-alt-fill text-danger"></i>
                    <span id="deleteTerminalItemLabel" class="fw-semibold">Terminal</span>
                    <span class="delete-terminal-item-extra" id="deleteTerminalItemExtra" style="display:none;"></span>
                </div>
            </div>

            <div class="modal-footer delete-terminal-modal-footer">
                <button type="button" class="btn delete-terminal-btn-cancel" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn delete-terminal-btn-confirm" id="deleteTerminalConfirmBtn">
                    <i class="fas fa-trash-alt me-1"></i> Yes, Delete
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* ── Delete Terminal Confirmation Modal Styles ── */
.delete-terminal-modal-content {
    border: 0 !important;
    border-radius: 18px !important;
    box-shadow: 0 20px 35px -5px rgba(15, 23, 42, 0.25), 0 10px 15px -5px rgba(15, 23, 42, 0.1) !important;
    background: #ffffff !important;
    overflow: hidden !important;
    position: relative !important;
    font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif !important;
}

.delete-terminal-stripe {
    height: 4px;
    width: 100%;
    background: linear-gradient(90deg, #dc2626 0%, #ef4444 50%, #f97316 100%);
}

.delete-terminal-icon-wrapper {
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

.delete-terminal-modal-content:hover .delete-terminal-icon-wrapper {
    transform: scale(1.04);
}

.delete-terminal-modal-title {
    font-size: 1.35rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 8px;
    letter-spacing: -0.01em;
}

.delete-terminal-modal-desc {
    color: #64748b;
    font-size: 0.925rem;
    line-height: 1.5;
    margin-bottom: 16px;
    padding: 0 10px;
}

.delete-terminal-item-chip {
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

.delete-terminal-item-chip i {
    font-size: 0.95rem;
    flex-shrink: 0;
}

.delete-terminal-item-extra {
    background: #fee2e2;
    color: #b91c1c;
    font-size: 0.725rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 6px;
    letter-spacing: 0.3px;
    flex-shrink: 0;
}

.delete-terminal-modal-footer {
    border: 0 !important;
    padding: 0 24px 24px 24px !important;
    display: flex !important;
    gap: 12px !important;
    justify-content: stretch !important;
    background: transparent !important;
}

.delete-terminal-btn-cancel {
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

.delete-terminal-btn-cancel:hover,
.delete-terminal-btn-cancel:focus {
    background: #e2e8f0 !important;
    color: #0f172a !important;
    border-color: #cbd5e1 !important;
}

.delete-terminal-btn-confirm {
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

.delete-terminal-btn-confirm:hover,
.delete-terminal-btn-confirm:focus {
    background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%) !important;
    box-shadow: 0 6px 16px rgba(220, 38, 38, 0.38) !important;
    transform: translateY(-1px);
}

.delete-terminal-btn-confirm.is-loading {
    pointer-events: none !important;
    opacity: 0.75 !important;
}

/* Ensure modal paints above fixed navbar */
body.modal-open #deleteTerminalConfirmModal,
#deleteTerminalConfirmModal {
    z-index: 100050 !important;
}


</style>

<script>
(function() {
    var _deleteTerminalFormId = null;

    window.showDeleteTerminalModal = function(opts) {
        opts = opts || {};
        var modalEl = document.getElementById('deleteTerminalConfirmModal');
        if (!modalEl || !window.bootstrap) {
            if (window.confirm('Are you sure you want to delete this terminal?')) {
                var form = document.getElementById(opts.formId);
                if (form) form.submit();
            }
            return;
        }

        var labelEl = document.getElementById('deleteTerminalItemLabel');
        var extraEl = document.getElementById('deleteTerminalItemExtra');

        var labelText = '#' + (opts.terminalId || '?') + ' · ' + (opts.name || 'Terminal');
        if (opts.location) {
            labelText += ' (' + opts.location + ')';
        }
        if (labelEl) {
            labelEl.textContent = labelText;
        }

        if (extraEl) {
            if (opts.capacity) {
                extraEl.textContent = opts.capacity + ' vehicles';
                extraEl.style.display = '';
            } else {
                extraEl.style.display = 'none';
            }
        }

        var confirmBtn = document.getElementById('deleteTerminalConfirmBtn');
        if (confirmBtn) {
            confirmBtn.classList.remove('is-loading');
            confirmBtn.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Yes, Delete';
        }

        _deleteTerminalFormId = opts.formId || null;
        window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
    };

    document.addEventListener('DOMContentLoaded', function() {
        var confirmBtn = document.getElementById('deleteTerminalConfirmBtn');
        if (!confirmBtn) return;

        confirmBtn.addEventListener('click', function() {
            if (!_deleteTerminalFormId) return;

            confirmBtn.classList.add('is-loading');
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Deleting...';

            var form = document.getElementById(_deleteTerminalFormId);
            if (form) {
                form.submit();
            } else {
                var modalEl = document.getElementById('deleteTerminalConfirmModal');
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

<!-- ══════════════════════════════════════════════════ -->
<!--  Bulk Delete Terminals Confirmation Modal          -->
<!-- ══════════════════════════════════════════════════ -->
<div class="modal fade" id="terminalBulkConfirmModal" tabindex="-1" aria-labelledby="terminalBulkConfirmLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px; margin: 1.75rem auto;">
        <div class="modal-content delete-terminal-modal-content">
            <!-- Accent stripe -->
            <div class="delete-terminal-stripe"></div>
            <form id="terminalBulkForm" method="post" action="<?= base_url('admin/terminals/bulk-action') ?>">
                <?= csrf_field() ?>
                <div id="terminalBulkIdsContainer"></div>

                <div class="modal-body text-center p-4">
                    <!-- Icon badge -->
                    <div class="delete-terminal-icon-wrapper">
                        <i class="fas fa-trash-can"></i>
                    </div>

                    <h4 class="delete-terminal-modal-title" id="terminalBulkConfirmTitle">Delete Selected Terminals?</h4>
                    <p class="delete-terminal-modal-desc" id="terminalBulkConfirmDesc">
                        Are you sure you want to delete the selected terminals? Any linked routes, fares, or active records may prevent deletion. This action cannot be undone.
                    </p>

                    <div id="terminalBulkSelectedList" class="p-2 mb-2 text-start" style="max-height: 140px; overflow-y: auto; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px;">
                    </div>
                </div>

                <div class="modal-footer delete-terminal-modal-footer">
                    <button type="button" class="btn delete-terminal-btn-cancel" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn delete-terminal-btn-confirm" id="terminalBulkSubmitBtn" style="flex: 1;">
                        <i class="fas fa-trash-alt me-1"></i> Yes, Delete Selected
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    window.isTerminalSelectMode = false;

    window.toggleTerminalSelectMode = function(forceState) {
        if (typeof forceState === 'boolean') {
            window.isTerminalSelectMode = forceState;
        } else {
            window.isTerminalSelectMode = !window.isTerminalSelectMode;
        }

        const table = document.getElementById('terminals-table');
        const toolbar = document.getElementById('terminal-bulk-toolbar');
        const btnText = document.getElementById('btn-select-terminals-text');
        const btn = document.getElementById('btn-toggle-select-terminals');
        const bulkCells = document.querySelectorAll('#terminals-table .bulk-col');

        if (window.isTerminalSelectMode) {
            if (table) table.classList.add('selection-mode-active');
            bulkCells.forEach(function(c) {
                c.style.removeProperty('display');
            });
            if (toolbar) {
                toolbar.classList.add('is-visible');
                toolbar.style.setProperty('display', 'flex', 'important');
            }
            if (btnText) btnText.textContent = 'Exit Select';
            if (btn) btn.classList.add('active');
            updateTerminalBulkToolbar();
        } else {
            if (table) table.classList.remove('selection-mode-active');
            bulkCells.forEach(function(c) {
                c.style.removeProperty('display');
            });
            if (toolbar) {
                toolbar.classList.remove('is-visible');
                toolbar.style.setProperty('display', 'none', 'important');
            }
            if (btnText) btnText.textContent = 'Select';
            if (btn) btn.classList.remove('active');
            clearTerminalSelection();
        }
    };

    function updateTerminalBulkToolbar() {
        const checkedBoxes = Array.from(document.querySelectorAll('.terminal-row-checkbox:checked'));
        const toolbar = document.getElementById('terminal-bulk-toolbar');
        const countEl = document.getElementById('terminal-selected-count');
        const textEl = document.getElementById('terminal-selected-text');
        const btnDel = document.getElementById('btn-bulk-delete-terminals');

        if (!toolbar) return;

        const count = checkedBoxes.length;
        if (countEl) countEl.textContent = count;
        if (textEl) textEl.textContent = (count === 1 ? 'terminal selected' : 'terminals selected');

        if (btnDel) {
            btnDel.disabled = (count === 0);
        }

        const selectAll = document.getElementById('select-all-terminals');
        const tableHeadSelectAll = document.getElementById('table-head-select-all-terminals');
        const visibleCheckboxes = Array.from(document.querySelectorAll('.terminal-row-checkbox'));

        const allChecked = visibleCheckboxes.length > 0 && visibleCheckboxes.every(cb => cb.checked);
        const someChecked = visibleCheckboxes.some(cb => cb.checked) && !allChecked;

        if (selectAll) {
            selectAll.checked = allChecked;
            selectAll.indeterminate = someChecked;
        }
        if (tableHeadSelectAll) {
            tableHeadSelectAll.checked = allChecked;
            tableHeadSelectAll.indeterminate = someChecked;
        }
    }

    function clearTerminalSelection() {
        document.querySelectorAll('.terminal-row-checkbox').forEach(cb => cb.checked = false);
        const selectAll = document.getElementById('select-all-terminals');
        const tableHeadSelectAll = document.getElementById('table-head-select-all-terminals');
        if (selectAll) { selectAll.checked = false; selectAll.indeterminate = false; }
        if (tableHeadSelectAll) { tableHeadSelectAll.checked = false; tableHeadSelectAll.indeterminate = false; }
        updateTerminalBulkToolbar();
    }

    window.openTerminalBulkModal = function() {
        const checkedBoxes = Array.from(document.querySelectorAll('.terminal-row-checkbox:checked'));
        if (checkedBoxes.length === 0) return;

        const modalEl = document.getElementById('terminalBulkConfirmModal');
        const idsContainer = document.getElementById('terminalBulkIdsContainer');
        const titleEl = document.getElementById('terminalBulkConfirmTitle');
        const descEl = document.getElementById('terminalBulkConfirmDesc');
        const listEl = document.getElementById('terminalBulkSelectedList');
        const count = checkedBoxes.length;

        if (idsContainer) {
            idsContainer.innerHTML = '';
            checkedBoxes.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = cb.value;
                idsContainer.appendChild(input);
            });
        }

        if (titleEl) {
            titleEl.textContent = `Delete ${count} Selected Terminal${count > 1 ? 's' : ''}?`;
        }
        if (descEl) {
            descEl.textContent = `Are you sure you want to delete the selected terminals? Any linked routes, fares, or active records may prevent deletion. This action cannot be undone.`;
        }

        if (listEl) {
            listEl.innerHTML = checkedBoxes.map(cb => {
                const id = cb.getAttribute('data-id') || cb.value;
                const name = cb.getAttribute('data-name') || 'Terminal';
                const loc = cb.getAttribute('data-location') || '';
                return `<div class="d-flex align-items-center gap-2 mb-1 p-1 bg-white rounded border">
                    <span class="badge bg-danger text-white">#${id}</span>
                    <span class="fw-semibold text-dark">${name}</span>
                    ${loc ? `<span class="text-muted small">(${loc})</span>` : ''}
                </div>`;
            }).join('');
        }

        if (modalEl && window.bootstrap) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        function handleSelectAll(isChecked) {
            const checkboxes = document.querySelectorAll('.terminal-row-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = isChecked;
            });
            updateTerminalBulkToolbar();
        }

        const selectAll = document.getElementById('select-all-terminals');
        if (selectAll) {
            selectAll.addEventListener('change', function() { handleSelectAll(this.checked); });
        }
        const tableHeadSelectAll = document.getElementById('table-head-select-all-terminals');
        if (tableHeadSelectAll) {
            tableHeadSelectAll.addEventListener('change', function() { handleSelectAll(this.checked); });
        }

        const tableBody = document.querySelector('#terminals-table tbody');
        if (tableBody) {
            tableBody.addEventListener('change', function(e) {
                if (e.target && e.target.classList.contains('terminal-row-checkbox')) {
                    updateTerminalBulkToolbar();
                }
            });
            tableBody.addEventListener('click', function(e) {
                const cell = e.target.closest('.bulk-select-cell');
                if (cell && e.target.tagName !== 'INPUT') {
                    const cb = cell.querySelector('.terminal-row-checkbox');
                    if (cb && !cb.disabled) {
                        cb.checked = !cb.checked;
                        cb.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
            });
        }

        var submitBtn = document.getElementById('terminalBulkSubmitBtn');
        var bulkForm = document.getElementById('terminalBulkForm');
        if (submitBtn && bulkForm) {
            bulkForm.addEventListener('submit', function() {
                submitBtn.classList.add('is-loading');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Deleting...';
            });
        }
    });
})();
</script>
<?php endif; ?>

<?= view('templates/footer') ?>

