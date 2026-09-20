<?= view('templates/header', ['title' => $title]) ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">

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
    <div class="modern-card-header">
        <span class="modern-card-title">
            <i class="bi bi-list" style="color: var(--primary-red);"></i>
            Terminal List
        </span>
    </div>
    <div class="modern-card-body">
        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
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
                            <tr>
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
                            <td colspan="<?= $isAdmin ? '6' : '5' ?>" class="text-center py-5 text-muted empty-state-table">
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
<?php endif; ?>

<?= view('templates/footer') ?>
