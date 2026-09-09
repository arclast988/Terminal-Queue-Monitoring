<?= view('templates/header', ['title' => $title]) ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">

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
            <?php if (!empty($announcements) && is_array($announcements)): ?>
            <button type="button" class="btn-modern btn-modern-sm btn-action-delete" id="deleteAllAnnouncementsBtn" data-bs-toggle="modal" data-bs-target="#deleteAllAnnouncementsModal" onclick="confirmDeleteAll(event);" title="Delete All Announcements">
                <i class="bi bi-trash"></i> <span>Delete All</span>
            </button>
            <?php else: ?>
            <button type="button" class="btn-modern btn-modern-sm btn-action-delete" id="deleteAllAnnouncementsBtn" title="No announcements to delete" disabled style="opacity: 0.5; cursor: not-allowed;">
                <i class="bi bi-trash"></i> <span>Delete All</span>
            </button>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <div class="modern-card-body">
        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Terminal</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Created</th>
                        <?php if (in_array(session()->get('role'), ['super_admin', 'admin', 'staff'], true)): ?>
                        <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($announcements) && is_array($announcements)): ?>
                        <?php foreach ($announcements as $a): ?>
                            <tr>
                                <td data-label="ID"><strong>#<?= $a['id'] ?></strong></td>
                                <td data-label="Terminal"><span class="badge-modern badge-modern-primary"><?= strtoupper(esc($a['terminal_name'] ?? '—')) ?></span></td>
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
                                <td data-label="Actions">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="<?= base_url('admin/announcements/edit/' . $a['id']) ?>" class="btn-modern btn-modern-outline btn-modern-sm" title="Edit">
                                            <i class="bi bi-pencil"></i> <span class="action-label">Edit</span>
                                        </a>
                                        <form action="<?= base_url('admin/announcements/delete/' . $a['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Delete this announcement?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn-modern btn-modern-sm btn-action-delete" title="Delete">
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
                            <td colspan="<?= in_array(session()->get('role'), ['super_admin', 'admin', 'staff'], true) ? '6' : '5' ?>" class="text-center py-5 text-muted empty-state-table">
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

<?= view('templates/footer') ?>
