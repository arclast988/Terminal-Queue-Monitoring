<?= view('templates/header', ['title' => $title]) ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">

<style>
.table-modern th.actions-col,
.table-modern td.actions-col {
    text-align: right !important;
    white-space: nowrap;
}
.announcement-actions-wrap {
    display: inline-flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
    vertical-align: middle;
}
.announcement-actions-wrap form {
    margin: 0 !important;
    padding: 0 !important;
    display: inline-flex !important;
    vertical-align: middle !important;
}
.announcement-actions-wrap .btn-modern {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 5px !important;
    vertical-align: middle !important;
    height: 32px !important;
    padding: 4px 12px !important;
    font-size: 13px !important;
    font-weight: 600 !important;
}
@media (max-width: 768px) {
    .table-modern td.actions-col {
        justify-content: space-between !important;
    }

    .announcement-actions-wrap {
        width: auto !important;
        margin-left: auto !important;
        flex: 0 0 auto !important;
        justify-content: flex-end !important;
    }
}

/* Scoped Selection & Bulk Action Rules - Matches App Action Buttons */
.bulk-col,
.bulk-select-cell,
#announcements-table:not(.selection-mode-active) .bulk-col,
#announcements-table:not(.selection-mode-active) .bulk-select-cell,
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

<div class="page-header-modern fade-in">
    <h1 class="page-title-modern">
        <i class="bi bi-megaphone"></i>
        Announcements
    </h1>
    <?php if (in_array(session()->get('role'), ['super_admin', 'admin', 'staff'], true)): ?>
    <div>
        <a href="<?= base_url('admin/announcements/create') ?>" class="btn-modern btn-modern-primary">
            <i class="bi bi-plus-circle"></i> Add Announcement
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
    <div class="modern-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <span class="modern-card-title">
            <i class="bi bi-list" style="color: var(--primary-red);"></i>
            Announcement List
        </span>
        <?php if (in_array(session()->get('role'), ['super_admin', 'admin', 'staff'], true)): ?>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn-modern btn-modern-sm btn-modern-outline" id="btn-toggle-select-announcements" onclick="toggleAnnouncementSelectMode()" title="Toggle selection mode for batch actions" <?= empty($announcements) ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '' ?>>
                    <i class="bi bi-check2-square me-1"></i> <span id="btn-select-announcements-text">Select</span>
                </button>
            </div>
        <?php endif; ?>
    </div>
    <div class="modern-card-body">
        <!-- Integrated Top Bulk Action Bar -->
        <div id="announcement-bulk-toolbar" class="bulk-action-top-bar" style="display: none;">
            <div class="bulk-bar-info">
                <label class="bulk-select-all-wrap mb-0">
                    <input type="checkbox" id="select-all-announcements" class="form-check-input select-all-checkbox m-0" style="width: 17px; height: 17px; cursor: pointer;">
                    <span class="fw-semibold text-dark" style="font-size: 13.5px;">Select All</span>
                </label>
                <span class="bulk-bar-divider"></span>
                <span class="bulk-count-badge">
                    <span id="announcement-selected-count">0</span> <span id="announcement-selected-text">selected</span>
                </span>
            </div>
            <div class="bulk-bar-actions">
                <button type="button" class="btn-modern btn-modern-sm btn-action-delete btn-bulk-delete" id="btn-bulk-delete-announcements" onclick="openAnnouncementBulkModal()" disabled>
                    <i class="bi bi-trash"></i> <span>Delete Selected</span>
                </button>
                <button type="button" class="btn-modern btn-modern-sm btn-modern-outline btn-bulk-cancel" onclick="toggleAnnouncementSelectMode(false)">
                    <i class="bi bi-x"></i> <span>Cancel</span>
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table-modern" id="announcements-table">
                <thead>
                    <tr>
                        <?php if (in_array(session()->get('role'), ['super_admin', 'admin', 'staff'], true)): ?>
                        <th class="bulk-col" style="display: none; width: 44px; text-align: center;">
                            <input type="checkbox" id="table-head-select-all-announcements" class="form-check-input select-all-checkbox m-0" style="width: 17px; height: 17px; cursor: pointer;" title="Select All">
                        </th>
                        <?php endif; ?>
                        <th>ID</th>
                        <th>Terminal</th>
                        <th>Severity</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Created</th>
                        <?php if (in_array(session()->get('role'), ['super_admin', 'admin', 'staff'], true)): ?>
                        <th class="actions-col" style="width: 170px;">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($announcements) && is_array($announcements)): ?>
                        <?php foreach ($announcements as $a): ?>
                            <tr data-announcement-id="<?= $a['id'] ?>">
                                <?php if (in_array(session()->get('role'), ['super_admin', 'admin', 'staff'], true)): ?>
                                <td data-label="Select" class="bulk-col bulk-select-cell" style="display: none; text-align: center;">
                                    <input type="checkbox" class="form-check-input announcement-row-checkbox" value="<?= $a['id'] ?>" data-id="<?= $a['id'] ?>" data-message="<?= esc(strlen($a['message']) > 60 ? substr($a['message'], 0, 60) . '…' : $a['message']) ?>" data-terminal="<?= esc($a['terminal_name'] ?? '—') ?>" title="Select announcement #<?= $a['id'] ?>">
                                </td>
                                <?php endif; ?>
                                <td data-label="ID"><strong>#<?= $a['id'] ?></strong></td>
                                <td data-label="Terminal"><span class="badge-modern badge-modern-primary"><?= strtoupper(esc($a['terminal_name'] ?? '—')) ?></span></td>
                                <td data-label="Severity">
                                    <?php
                                    $sev = $a['severity'] ?? 'info';
                                    if ($sev === 'danger'): ?>
                                        <span class="badge-modern badge-modern-danger"><i class="bi bi-exclamation-octagon-fill"></i> Urgent</span>
                                    <?php elseif ($sev === 'warning'): ?>
                                        <span class="badge-modern badge-modern-warning"><i class="bi bi-exclamation-triangle-fill"></i> Warning</span>
                                    <?php else: ?>
                                        <span class="badge-modern badge-modern-info"><i class="bi bi-info-circle-fill"></i> Info</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Message"><?= esc(strlen($a['message']) > 80 ? substr($a['message'], 0, 80) . '…' : $a['message']) ?></td>
                                <td data-label="Status">
                                    <?php if ($a['is_active']): ?>
                                        <span class="badge-modern badge-modern-success"><i class="bi bi-check-circle-fill"></i> Active</span>
                                    <?php else: ?>
                                        <span class="badge-modern badge-modern-info"><i class="bi bi-pause-circle-fill"></i> Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Created">
                                    <?php if (!empty($a['created_at'])): ?>
                                        <div style="white-space: nowrap; font-size: 13px; font-weight: 600; color: var(--text-main);">
                                            <i class="bi bi-calendar3 text-muted me-1" style="font-size: 12px;"></i><?= date('M d, Y', strtotime($a['created_at'])) ?>
                                        </div>
                                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                                            <?= date('H:i', strtotime($a['created_at'])) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <?php if (in_array(session()->get('role'), ['super_admin', 'admin', 'staff'], true)): ?>
                                <td data-label="Actions" class="actions-col">
                                    <div class="announcement-actions-wrap">
                                        <a href="<?= base_url('admin/announcements/edit/' . $a['id']) ?>" class="btn-modern btn-modern-outline btn-modern-sm" title="Edit">
                                            <i class="bi bi-pencil"></i> <span class="action-label">Edit</span>
                                        </a>
                                        <form id="delete-announcement-form-<?= $a['id'] ?>" action="<?= base_url('admin/announcements/delete/' . $a['id']) ?>" method="post" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="button" class="btn-modern btn-modern-sm btn-action-delete" title="Delete"
                                                onclick="showDeleteAnnouncementModal({formId:'delete-announcement-form-<?= $a['id'] ?>',announcementId:'<?= $a['id'] ?>',message:'<?= esc(addslashes(strlen($a['message']) > 60 ? substr($a['message'], 0, 60) . '…' : $a['message'])) ?>',terminal:'<?= esc(addslashes($a['terminal_name'] ?? '—')) ?>',severity:'<?= esc(addslashes($a['severity'] ?? 'info')) ?>'})">
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
                            <td colspan="<?= in_array(session()->get('role'), ['super_admin', 'admin', 'staff'], true) ? '8' : '6' ?>" class="text-center py-5 text-muted empty-state-table">
                                <i class="bi bi-megaphone fs-1 d-block mb-3 opacity-50"></i>
                                <div class="fw-bold fs-6 empty-state-title">No announcements yet</div>
                                <small class="empty-state-subtitle">Add an announcement to broadcast real-time notices to passengers.</small>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modern Delete All Announcements Confirmation Modal -->
<?php if (in_array(session()->get('role'), ['super_admin', 'admin', 'staff'], true) && !empty($announcements)): ?>
<div class="modal fade" id="deleteAllAnnouncementsModal" tabindex="-1" aria-labelledby="deleteAllModalLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px; margin: 1.75rem auto;">
        <div class="modal-content delete-all-modal-content">
            <!-- Accent stripe -->
            <div class="delete-all-stripe"></div>
            
            <div class="modal-body text-center p-4">
                <!-- Icon badge -->
                <div class="delete-all-icon-wrapper">
                    <i class="fas fa-trash-can"></i>
                </div>
                
                <h4 class="delete-all-modal-title" id="deleteAllModalLabel">Delete All Announcements?</h4>
                <p class="delete-all-modal-desc">
                    Are you sure you want to delete all announcements? This will permanently remove all announcement messages from the board and cannot be undone.
                </p>

                <!-- Count Badge -->
                <div class="delete-all-count-chip">
                    <i class="fas fa-bullhorn"></i>
                    <span><strong><?= count($announcements) ?></strong> announcement<?= count($announcements) === 1 ? '' : 's' ?> will be permanently deleted</span>
                </div>
            </div>

            <div class="modal-footer delete-all-modal-footer">
                <button type="button" class="btn delete-all-btn-cancel" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <form action="<?= base_url('admin/announcements/delete-all') ?>" method="post" class="d-inline m-0 flex-grow-1" id="deleteAllAnnouncementsForm">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn delete-all-btn-confirm w-100" id="btnConfirmDeleteAll">
                        <i class="fas fa-trash-alt me-1"></i> Yes, Delete All
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
/* Scoped Delete All Modal Styles */
.delete-all-modal-content {
    border: 0 !important;
    border-radius: 18px !important;
    box-shadow: 0 20px 35px -5px rgba(15, 23, 42, 0.25), 0 10px 15px -5px rgba(15, 23, 42, 0.1) !important;
    background: #ffffff !important;
    overflow: hidden !important;
    position: relative !important;
    font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif !important;
}

.delete-all-stripe {
    height: 4px;
    width: 100%;
    background: linear-gradient(90deg, #dc2626 0%, #ef4444 50%, #f97316 100%);
}

.delete-all-icon-wrapper {
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

.delete-all-modal-content:hover .delete-all-icon-wrapper {
    transform: scale(1.04);
}

.delete-all-modal-title {
    font-size: 1.35rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 8px;
    letter-spacing: -0.01em;
}

.delete-all-modal-desc {
    color: #64748b;
    font-size: 0.925rem;
    line-height: 1.5;
    margin-bottom: 16px;
    padding: 0 10px;
}

.delete-all-count-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    padding: 7px 14px;
    border-radius: 9999px;
    font-size: 0.825rem;
    color: #991b1b;
    max-width: 100%;
    box-sizing: border-box;
}

.delete-all-count-chip i {
    color: #dc2626;
    font-size: 0.95rem;
    flex-shrink: 0;
}

.delete-all-modal-footer {
    border: 0 !important;
    padding: 0 24px 24px 24px !important;
    display: flex !important;
    gap: 12px !important;
    background: transparent !important;
}

.delete-all-btn-cancel {
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

.delete-all-btn-cancel:hover,
.delete-all-btn-cancel:focus {
    background: #e2e8f0 !important;
    color: #0f172a !important;
    border-color: #cbd5e1 !important;
}

.delete-all-btn-confirm {
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
    text-decoration: none !important;
}

.delete-all-btn-confirm:hover,
.delete-all-btn-confirm:focus {
    background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%) !important;
    color: #ffffff !important;
    box-shadow: 0 6px 16px rgba(220, 38, 38, 0.38) !important;
    transform: translateY(-1px);
}

/* Ensure modal paints above everything including fixed header */
body.modal-open #deleteAllAnnouncementsModal,
#deleteAllAnnouncementsModal {
    z-index: 100050 !important;
}

@media (max-width: 480px) {
    .delete-all-modal-footer {
        flex-direction: column-reverse !important;
        gap: 8px !important;
        padding: 0 16px 20px 16px !important;
    }
    .delete-all-modal-footer .btn,
    .delete-all-modal-footer form {
        width: 100% !important;
    }
    .delete-all-count-chip {
        font-size: 0.775rem;
    }
}

/* --- Dispatcher (staff) green-themed overrides --- */
body.staff-theme .delete-all-stripe {
    background: linear-gradient(90deg, #15803d 0%, #16a34a 50%, #10b981 100%);
}

body.staff-theme .delete-all-icon-wrapper {
    background: #dcfce7;
    color: #15803d;
    box-shadow: 0 0 0 8px #f0fdf4;
}

body.staff-theme .delete-all-count-chip {
    background: #f0fdf4;
    border-color: #bbf7d0;
    color: #166534;
}

body.staff-theme .delete-all-count-chip i {
    color: #15803d;
}

body.staff-theme .delete-all-btn-confirm {
    background: linear-gradient(135deg, #15803d 0%, #166534 100%) !important;
    box-shadow: 0 4px 12px rgba(21, 128, 61, 0.28) !important;
}

body.staff-theme .delete-all-btn-confirm:hover,
body.staff-theme .delete-all-btn-confirm:focus {
    background: linear-gradient(135deg, #166534 0%, #14532d 100%) !important;
    box-shadow: 0 6px 16px rgba(21, 128, 61, 0.38) !important;
}
</style>

<script>
function confirmDeleteAll(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    var modalEl = document.getElementById('deleteAllAnnouncementsModal');
    if (modalEl && window.bootstrap && typeof window.bootstrap.Modal === 'function') {
        try {
            var modalInstance = window.bootstrap.Modal.getOrCreateInstance(modalEl);
            modalInstance.show();
            return false;
        } catch (err) {
            console.warn('Bootstrap modal trigger error:', err);
        }
    }
    if (window.confirm("Are you sure you want to delete ALL announcements? This action cannot be undone.")) {
        var form = document.getElementById('deleteAllAnnouncementsForm');
        if (form) form.submit();
    }
    return false;
}
</script>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════ -->
<!--  Delete Single Announcement Confirmation Modal    -->
<!-- ══════════════════════════════════════════════════ -->
<?php if (in_array(session()->get('role'), ['super_admin', 'admin', 'staff'], true) && !empty($announcements)): ?>
<div class="modal fade" id="deleteAnnouncementModal" tabindex="-1" aria-labelledby="deleteAnnouncementLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px; margin: 1.75rem auto;">
        <div class="modal-content delete-all-modal-content">
            <!-- Accent stripe -->
            <div class="delete-all-stripe"></div>

            <div class="modal-body text-center p-4">
                <!-- Icon badge -->
                <div class="delete-all-icon-wrapper">
                    <i class="fas fa-trash-can"></i>
                </div>

                <h4 class="delete-all-modal-title" id="deleteAnnouncementLabel">Delete Announcement?</h4>
                <p class="delete-all-modal-desc" id="deleteAnnouncementDesc">
                    Are you sure you want to delete this announcement? This action cannot be undone.
                </p>

                <!-- Announcement preview chip -->
                <div class="delete-all-count-chip" id="deleteAnnouncementChip">
                    <i class="fas fa-bullhorn"></i>
                    <span id="deleteAnnouncementPreview">Announcement</span>
                </div>
            </div>

            <div class="modal-footer delete-all-modal-footer">
                <button type="button" class="btn delete-all-btn-cancel" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn delete-all-btn-confirm" id="deleteAnnouncementConfirmBtn" style="flex:1;">
                    <i class="fas fa-trash-alt me-1"></i> Yes, Delete
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Ensure modal paints above everything */
body.modal-open #deleteAnnouncementModal,
#deleteAnnouncementModal {
    z-index: 100050 !important;
}
.del-ann-btn-loading {
    pointer-events: none !important;
    opacity: 0.75 !important;
}
</style>

<script>
(function() {
    var _pendingFormId = null;

    window.showDeleteAnnouncementModal = function(opts) {
        opts = opts || {};
        var modalEl = document.getElementById('deleteAnnouncementModal');
        if (!modalEl || !window.bootstrap) {
            if (window.confirm('Delete this announcement?')) {
                var form = document.getElementById(opts.formId);
                if (form) form.submit();
            }
            return;
        }

        // Populate modal content
        var desc = document.getElementById('deleteAnnouncementDesc');
        desc.textContent = 'Are you sure you want to delete this announcement? This action cannot be undone.';

        var preview = document.getElementById('deleteAnnouncementPreview');
        var label = '#' + (opts.announcementId || '?');
        if (opts.severity && opts.severity !== 'info') label += ' [' + opts.severity.toUpperCase() + ']';
        if (opts.terminal) label += ' · ' + opts.terminal.toUpperCase();
        if (opts.message) label += ' — "' + opts.message + '"';
        preview.textContent = label;

        // Reset confirm button
        var confirmBtn = document.getElementById('deleteAnnouncementConfirmBtn');
        confirmBtn.classList.remove('del-ann-btn-loading');
        confirmBtn.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Yes, Delete';

        _pendingFormId = opts.formId || null;
        window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
    };

    document.addEventListener('DOMContentLoaded', function() {
        var confirmBtn = document.getElementById('deleteAnnouncementConfirmBtn');
        if (!confirmBtn) return;

        confirmBtn.addEventListener('click', function() {
            if (!_pendingFormId) return;

            confirmBtn.classList.add('del-ann-btn-loading');
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Deleting...';

            var form = document.getElementById(_pendingFormId);
            if (form) {
                form.submit();
            } else {
                var modalEl = document.getElementById('deleteAnnouncementModal');
                if (modalEl && window.bootstrap) {
                    window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                }
                confirmBtn.classList.remove('del-ann-btn-loading');
                confirmBtn.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Yes, Delete';
            }
        });

        // Real-time table synchronization on announcement broadcasts
        function refreshAnnouncementsTable() {
            if (window.isAnnouncementSelectMode || document.querySelector('.modal.show')) return;
            fetch(window.location.href, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-Silent': 'true' }
            })
            .then(function(r) { return r.ok ? r.text() : null; })
            .then(function(html) {
                if (!html || window.isAnnouncementSelectMode || document.querySelector('.modal.show')) return;
                var parser = new DOMParser();
                var newDoc = parser.parseFromString(html, 'text/html');
                var curTable = document.getElementById('announcements-table');
                var newTable = newDoc.getElementById('announcements-table');
                if (curTable && newTable) {
                    curTable.innerHTML = newTable.innerHTML;
                }
                var curHeader = document.querySelector('.modern-card-header');
                var newHeader = newDoc.querySelector('.modern-card-header');
                if (curHeader && newHeader) {
                    curHeader.innerHTML = newHeader.innerHTML;
                }
            })
            .catch(function() {});
        }
        document.addEventListener('pttm:ws-announcement_update', refreshAnnouncementsTable);
    });
})();
</script>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════ -->
<!--  Bulk Delete Announcements Confirmation Modal      -->
<!-- ══════════════════════════════════════════════════ -->
<?php if (in_array(session()->get('role'), ['super_admin', 'admin', 'staff'], true)): ?>
<div class="modal fade" id="announcementBulkConfirmModal" tabindex="-1" aria-labelledby="announcementBulkConfirmLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px; margin: 1.75rem auto;">
        <div class="modal-content delete-all-modal-content">
            <!-- Accent stripe -->
            <div class="delete-all-stripe"></div>
            <form id="announcementBulkForm" method="post" action="<?= base_url('admin/announcements/bulk-action') ?>">
                <?= csrf_field() ?>
                <div id="announcementBulkIdsContainer"></div>

                <div class="modal-body text-center p-4">
                    <!-- Icon badge -->
                    <div class="delete-all-icon-wrapper">
                        <i class="fas fa-trash-can"></i>
                    </div>

                    <h4 class="delete-all-modal-title" id="announcementBulkConfirmTitle">Delete Selected Announcements?</h4>
                    <p class="delete-all-modal-desc" id="announcementBulkConfirmDesc">
                        Are you sure you want to delete the selected announcements? This action cannot be undone.
                    </p>

                    <div id="announcementBulkSelectedList" class="p-2 mb-2 text-start" style="max-height: 140px; overflow-y: auto; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px;">
                    </div>
                </div>

                <div class="modal-footer delete-all-modal-footer">
                    <button type="button" class="btn delete-all-btn-cancel" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn delete-all-btn-confirm" id="announcementBulkSubmitBtn" style="flex: 1;">
                        <i class="fas fa-trash-alt me-1"></i> Yes, Delete Selected
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    window.isAnnouncementSelectMode = false;

    window.toggleAnnouncementSelectMode = function(forceState) {
        if (typeof forceState === 'boolean') {
            window.isAnnouncementSelectMode = forceState;
        } else {
            window.isAnnouncementSelectMode = !window.isAnnouncementSelectMode;
        }

        const table = document.getElementById('announcements-table');
        const toolbar = document.getElementById('announcement-bulk-toolbar');
        const btnText = document.getElementById('btn-select-announcements-text');
        const btn = document.getElementById('btn-toggle-select-announcements');
        const bulkCells = document.querySelectorAll('#announcements-table .bulk-col');

        if (window.isAnnouncementSelectMode) {
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
            updateAnnouncementBulkToolbar();
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
            clearAnnouncementSelection();
        }
    };

    function updateAnnouncementBulkToolbar() {
        const checkedBoxes = Array.from(document.querySelectorAll('.announcement-row-checkbox:checked'));
        const toolbar = document.getElementById('announcement-bulk-toolbar');
        const countEl = document.getElementById('announcement-selected-count');
        const textEl = document.getElementById('announcement-selected-text');
        const btnDel = document.getElementById('btn-bulk-delete-announcements');

        if (!toolbar) return;

        const count = checkedBoxes.length;
        if (countEl) countEl.textContent = count;
        if (textEl) textEl.textContent = (count === 1 ? 'announcement selected' : 'announcements selected');

        if (btnDel) {
            btnDel.disabled = (count === 0);
        }

        const selectAll = document.getElementById('select-all-announcements');
        const tableHeadSelectAll = document.getElementById('table-head-select-all-announcements');
        const visibleCheckboxes = Array.from(document.querySelectorAll('.announcement-row-checkbox'));

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

    function clearAnnouncementSelection() {
        document.querySelectorAll('.announcement-row-checkbox').forEach(cb => cb.checked = false);
        const selectAll = document.getElementById('select-all-announcements');
        const tableHeadSelectAll = document.getElementById('table-head-select-all-announcements');
        if (selectAll) { selectAll.checked = false; selectAll.indeterminate = false; }
        if (tableHeadSelectAll) { tableHeadSelectAll.checked = false; tableHeadSelectAll.indeterminate = false; }
        updateAnnouncementBulkToolbar();
    }

    window.openAnnouncementBulkModal = function() {
        const checkedBoxes = Array.from(document.querySelectorAll('.announcement-row-checkbox:checked'));
        if (checkedBoxes.length === 0) return;

        const modalEl = document.getElementById('announcementBulkConfirmModal');
        const idsContainer = document.getElementById('announcementBulkIdsContainer');
        const titleEl = document.getElementById('announcementBulkConfirmTitle');
        const descEl = document.getElementById('announcementBulkConfirmDesc');
        const listEl = document.getElementById('announcementBulkSelectedList');
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
            titleEl.textContent = `Delete ${count} Selected Announcement${count > 1 ? 's' : ''}?`;
        }
        if (descEl) {
            descEl.textContent = `Are you sure you want to permanently delete these ${count} announcements? This action cannot be undone.`;
        }

        if (listEl) {
            listEl.innerHTML = checkedBoxes.map(cb => {
                const id = cb.getAttribute('data-id') || cb.value;
                const terminal = cb.getAttribute('data-terminal') || '';
                const msg = cb.getAttribute('data-message') || '';
                return `<div class="d-flex align-items-center gap-2 mb-1 p-1 bg-white rounded border">
                    <span class="badge bg-danger text-white">#${id}</span>
                    <span class="badge bg-light text-dark border">${terminal}</span>
                    <span class="text-truncate" style="max-width: 280px;" title="${msg}">${msg}</span>
                </div>`;
            }).join('');
        }

        if (modalEl && window.bootstrap) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        function handleSelectAll(isChecked) {
            const checkboxes = document.querySelectorAll('.announcement-row-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = isChecked;
            });
            updateAnnouncementBulkToolbar();
        }

        const selectAll = document.getElementById('select-all-announcements');
        if (selectAll) {
            selectAll.addEventListener('change', function() { handleSelectAll(this.checked); });
        }
        const tableHeadSelectAll = document.getElementById('table-head-select-all-announcements');
        if (tableHeadSelectAll) {
            tableHeadSelectAll.addEventListener('change', function() { handleSelectAll(this.checked); });
        }

        const tableBody = document.querySelector('#announcements-table tbody');
        if (tableBody) {
            tableBody.addEventListener('change', function(e) {
                if (e.target && e.target.classList.contains('announcement-row-checkbox')) {
                    updateAnnouncementBulkToolbar();
                }
            });
            tableBody.addEventListener('click', function(e) {
                const cell = e.target.closest('.bulk-select-cell');
                if (cell && e.target.tagName !== 'INPUT') {
                    const cb = cell.querySelector('.announcement-row-checkbox');
                    if (cb && !cb.disabled) {
                        cb.checked = !cb.checked;
                        cb.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
            });
        }

        var submitBtn = document.getElementById('announcementBulkSubmitBtn');
        var bulkForm = document.getElementById('announcementBulkForm');
        if (submitBtn && bulkForm) {
            bulkForm.addEventListener('submit', function() {
                submitBtn.classList.add('del-ann-btn-loading');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Deleting...';
            });
        }
    });
})();
</script>
<?php endif; ?>

<?= view('templates/footer') ?>
