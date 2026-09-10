<?= view('templates/header', ['title' => $title]) ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">

<?php
$isAdmin = in_array(session()->get('role'), ['super_admin', 'admin'], true);
?>

<div class="page-header-modern fade-in">
    <h1 class="page-title-modern">
        <i class="bi bi-signpost-split"></i>
        Routes Management
    </h1>
    <?php if ($isAdmin): ?>
    <div>
        <a href="<?= base_url('admin/routes/create') ?>" class="btn-modern btn-modern-primary">
            <i class="bi bi-plus-circle"></i> Add New Route
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

<!-- Search Bar -->
<div class="modern-card shadow-modern fade-in mb-4">
    <div class="modern-card-body py-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <!-- Search Input Capsule -->
            <div class="route-search-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" class="route-search-input" id="route-search" placeholder="Search destination, origin, vehicle type, or fare..." onkeyup="filterRoutes()" autocomplete="off">
                <button type="button" class="btn-clear-search" id="clear-route-search" onclick="clearRouteSearch()" style="display:none;" title="Clear search">
                    <i class="bi bi-x-circle-fill"></i>
                </button>
            </div>
            <div id="route-count-display" style="font-size: 13.5px; font-weight: 600; color: var(--text-muted, #64748b);">
                Total: <strong style="color: var(--text-main, #0f172a);"><?= !empty($groupedRoutes) ? count($groupedRoutes) : 0 ?></strong> route<?= (!empty($groupedRoutes) && count($groupedRoutes) !== 1) ? 's' : '' ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($groupedRoutes)): ?>
    <div class="d-flex flex-column gap-4 fade-in" id="routes-container">
        <?php foreach ($groupedRoutes as $routeKey => $group): ?>
            <div class="modern-card shadow-modern mb-0 route-card-item" data-destination="<?= esc(strtolower($group['destination'])) ?>" data-terminal="<?= esc(strtolower($group['terminal_name'])) ?>">
                <div class="modern-card-header d-flex justify-content-between align-items-center flex-wrap gap-2 py-3 px-4" style="background: var(--surface-sunken, #f8fafc); border-bottom: 1px solid var(--border, #e2e8f0);">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-geo-alt-fill text-danger fs-5"></i>
                        <span class="fw-bold fs-5" style="color: var(--text-main);"><?= strtoupper(esc($group['terminal_name'])) ?> <i class="bi bi-arrow-right text-muted mx-1"></i> <?= strtoupper(esc($group['destination'])) ?></span>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <?php if ($isAdmin): ?>
                        <div class="d-inline-flex gap-2">
                            <a href="<?= base_url('admin/routes/edit/'.$group['items'][0]['id']) ?>" class="btn-modern btn-action-edit btn-modern-sm" style="padding: 6px 12px;" title="Edit Route Group">
                                <i class="bi bi-pencil"></i> Edit Route
                            </a>
                            <form id="delete-route-group-form-<?= $group['items'][0]['id'] ?>" action="<?= base_url('admin/routes/delete_group/'.$group['items'][0]['id']) ?>" method="post" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="button" class="btn-modern btn-modern-sm btn-action-delete" style="padding: 6px 12px;" title="Delete Route Group"
                                    onclick="showDeleteRouteModal({
                                        formId: 'delete-route-group-form-<?= $group['items'][0]['id'] ?>',
                                        routeId: '<?= $group['items'][0]['id'] ?>',
                                        origin: '<?= esc(addslashes(strtoupper($group['terminal_name']))) ?>',
                                        destination: '<?= esc(addslashes(strtoupper($group['destination']))) ?>',
                                        typesCount: '<?= count($group['items']) ?>'
                                    })">
                                    <i class="bi bi-trash"></i> Delete Route
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>
                        <span class="badge-modern badge-modern-primary px-3 py-1 fs-6">
                            <i class="bi bi-bus-front me-1"></i> <?= count($group['items']) ?> Vehicle Type<?= count($group['items']) > 1 ? 's' : '' ?>
                        </span>
                    </div>
                </div>
                <div class="modern-card-body p-0">
                    <div class="table-responsive">
                        <table class="table-modern mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 50%;">Vehicle Type</th>
                                    <th style="width: 50%;">Fare (PHP)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($group['items'] as $route): ?>
                                    <tr>
                                        <td data-label="Vehicle Type">
                                            <div class="d-flex align-items-center gap-2">
                                                <?= vehicle_type_badge($route['vehicle_type']) ?>
                                            </div>
                                        </td>
                                        <td data-label="Fare (PHP)">
                                            <strong style="font-size: 16px; color: var(--text-main);">₱<?= number_format($route['fare'], 2) ?></strong>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <div id="no-routes-match" class="modern-card shadow-modern fade-in text-center py-5 empty-state d-none" style="display: none !important;">
            <div class="py-4 text-muted">
                <i class="bi bi-search fs-1 d-block mb-3 opacity-50"></i>
                <div class="fw-bold fs-6 empty-state-title">No routes match your search query</div>
                <small class="empty-state-subtitle">Try searching with a different origin or destination name.</small>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="modern-card shadow-modern fade-in text-center py-5 empty-state">
        <div class="py-4 text-muted">
            <i class="bi bi-signpost-split fs-1 d-block mb-3 opacity-50"></i>
            <div class="fw-bold fs-6 empty-state-title">No routes found</div>
            <small class="empty-state-subtitle">Click <strong>Add New Route</strong> above to create your first route.</small>
        </div>
    </div>
<?php endif; ?>

<style>
    .route-search-group {
        display: flex;
        align-items: center;
        position: relative;
        max-width: 480px;
        width: 100%;
    }
    .route-search-group .input-group-text {
        height: 40px;
        background: #f8fafc;
        border: 1.5px solid #cbd5e1;
        border-right: none;
        border-radius: 20px 0 0 20px;
        color: #64748b;
        padding: 0 14px;
        font-size: 14px;
    }
    .route-search-input {
        height: 40px;
        width: 100%;
        padding: 6px 36px 6px 10px;
        font-size: 13.5px;
        font-family: inherit;
        border: 1.5px solid #cbd5e1;
        border-left: none;
        background: #ffffff;
        color: #1e293b;
        border-radius: 0 20px 20px 0;
        outline: none;
        transition: all 0.2s ease;
    }
    .route-search-group:focus-within .input-group-text,
    .route-search-group:focus-within .route-search-input {
        border-color: var(--primary, #b71c1c);
        box-shadow: 0 0 0 3px rgba(183, 28, 28, 0.12);
    }
    .btn-clear-search {
        position: absolute;
        right: 12px;
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        transition: color 0.15s ease;
    }
    .btn-clear-search:hover {
        color: #475569;
    }
    @media (max-width: 768px) {
        .route-search-group {
            max-width: 100% !important;
            width: 100% !important;
        }
    }
</style>

<script>
    function filterRoutes() {
        const input = document.getElementById('route-search');
        const clearBtn = document.getElementById('clear-route-search');
        const query = input ? input.value.toLowerCase().trim() : '';
        const cards = document.querySelectorAll('.route-card-item');
        const noMatch = document.getElementById('no-routes-match');
        const countDisplay = document.getElementById('route-count-display');
        const totalCards = cards.length;
        let visibleCount = 0;

        if (clearBtn) {
            clearBtn.style.display = query.length > 0 ? 'flex' : 'none';
        }

        cards.forEach(card => {
            const text = card.innerText.toLowerCase();
            if (!query || text.includes(query)) {
                card.style.removeProperty('display');
                card.classList.remove('d-none');
                visibleCount++;
            } else {
                card.style.setProperty('display', 'none', 'important');
                card.classList.add('d-none');
            }
        });

        if (noMatch) {
            const showEmpty = (visibleCount === 0 && totalCards > 0);
            if (showEmpty) {
                noMatch.style.removeProperty('display');
                noMatch.classList.remove('d-none');
            } else {
                noMatch.style.setProperty('display', 'none', 'important');
                noMatch.classList.add('d-none');
            }
        }

        if (countDisplay) {
            if (query) {
                countDisplay.innerHTML = `Showing <strong style="color: var(--text-main, #0f172a);">${visibleCount}</strong> of ${totalCards} route${totalCards !== 1 ? 's' : ''}`;
            } else {
                countDisplay.innerHTML = `Total: <strong style="color: var(--text-main, #0f172a);">${totalCards}</strong> route${totalCards !== 1 ? 's' : ''}`;
            }
        }
    }

    function clearRouteSearch() {
        const input = document.getElementById('route-search');
        if (input) {
            input.value = '';
            input.focus();
        }
        filterRoutes();
    }
</script>

<?php if ($isAdmin && !empty($groupedRoutes)): ?>
<!-- ══════════════════════════════════════════════════ -->
<!--  Delete Route Confirmation Modal                   -->
<!-- ══════════════════════════════════════════════════ -->
<div class="modal fade" id="deleteRouteConfirmModal" tabindex="-1" aria-labelledby="deleteRouteConfirmLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px; margin: 1.75rem auto;">
        <div class="modal-content delete-route-modal-content">
            <!-- Accent stripe -->
            <div class="delete-route-stripe"></div>

            <div class="modal-body text-center p-4">
                <!-- Icon badge -->
                <div class="delete-route-icon-wrapper">
                    <i class="fas fa-trash-can"></i>
                </div>

                <h4 class="delete-route-modal-title" id="deleteRouteConfirmLabel">Delete Route?</h4>
                <p class="delete-route-modal-desc" id="deleteRouteConfirmMessage">
                    Are you sure you want to delete this entire route group and all its fares? This action cannot be undone.
                </p>

                <!-- Route preview chip -->
                <div class="delete-route-item-chip" id="deleteRouteItemChip">
                    <i class="bi bi-geo-alt-fill text-danger"></i>
                    <span id="deleteRouteLabel" class="fw-bold">ORIGIN → DESTINATION</span>
                    <span class="delete-route-types-tag" id="deleteRouteTypesTag" style="display:none;"></span>
                </div>
            </div>

            <div class="modal-footer delete-route-modal-footer">
                <button type="button" class="btn delete-route-btn-cancel" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn delete-route-btn-confirm" id="deleteRouteConfirmBtn">
                    <i class="fas fa-trash-alt me-1"></i> Yes, Delete
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* ── Delete Route Confirmation Modal Styles ── */
.delete-route-modal-content {
    border: 0 !important;
    border-radius: 18px !important;
    box-shadow: 0 20px 35px -5px rgba(15, 23, 42, 0.25), 0 10px 15px -5px rgba(15, 23, 42, 0.1) !important;
    background: #ffffff !important;
    overflow: hidden !important;
    position: relative !important;
    font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif !important;
}

.delete-route-stripe {
    height: 4px;
    width: 100%;
    background: linear-gradient(90deg, #dc2626 0%, #ef4444 50%, #f97316 100%);
}

.delete-route-icon-wrapper {
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

.delete-route-modal-content:hover .delete-route-icon-wrapper {
    transform: scale(1.04);
}

.delete-route-modal-title {
    font-size: 1.35rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 8px;
    letter-spacing: -0.01em;
}

.delete-route-modal-desc {
    color: #64748b;
    font-size: 0.925rem;
    line-height: 1.5;
    margin-bottom: 16px;
    padding: 0 10px;
}

.delete-route-item-chip {
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

.delete-route-item-chip i {
    font-size: 0.95rem;
    flex-shrink: 0;
}

.delete-route-types-tag {
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

.delete-route-modal-footer {
    border: 0 !important;
    padding: 0 24px 24px 24px !important;
    display: flex !important;
    gap: 12px !important;
    justify-content: stretch !important;
    background: transparent !important;
}

.delete-route-btn-cancel {
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

.delete-route-btn-cancel:hover,
.delete-route-btn-cancel:focus {
    background: #e2e8f0 !important;
    color: #0f172a !important;
    border-color: #cbd5e1 !important;
}

.delete-route-btn-confirm {
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

.delete-route-btn-confirm:hover,
.delete-route-btn-confirm:focus {
    background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%) !important;
    box-shadow: 0 6px 16px rgba(220, 38, 38, 0.38) !important;
    transform: translateY(-1px);
}

.delete-route-btn-confirm.is-loading {
    pointer-events: none !important;
    opacity: 0.75 !important;
}

/* Ensure modal paints above fixed navbar */
body.modal-open #deleteRouteConfirmModal,
#deleteRouteConfirmModal {
    z-index: 100050 !important;
}


</style>

<script>
(function() {
    var _deleteRouteFormId = null;

    window.showDeleteRouteModal = function(opts) {
        opts = opts || {};
        var modalEl = document.getElementById('deleteRouteConfirmModal');
        if (!modalEl || !window.bootstrap) {
            if (window.confirm('Are you sure you want to delete this entire route group and all its fares?')) {
                var form = document.getElementById(opts.formId);
                if (form) form.submit();
            }
            return;
        }

        var labelEl = document.getElementById('deleteRouteLabel');
        var typesTagEl = document.getElementById('deleteRouteTypesTag');

        if (labelEl) {
            var labelText = (opts.origin || 'ORIGIN') + ' → ' + (opts.destination || 'DESTINATION');
            labelEl.textContent = labelText;
        }

        if (typesTagEl) {
            if (opts.typesCount) {
                typesTagEl.textContent = opts.typesCount + ' Vehicle Type' + (opts.typesCount != 1 ? 's' : '');
                typesTagEl.style.display = '';
            } else {
                typesTagEl.style.display = 'none';
            }
        }

        var confirmBtn = document.getElementById('deleteRouteConfirmBtn');
        if (confirmBtn) {
            confirmBtn.classList.remove('is-loading');
            confirmBtn.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Yes, Delete';
        }

        _deleteRouteFormId = opts.formId || null;
        window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
    };

    document.addEventListener('DOMContentLoaded', function() {
        var confirmBtn = document.getElementById('deleteRouteConfirmBtn');
        if (!confirmBtn) return;

        confirmBtn.addEventListener('click', function() {
            if (!_deleteRouteFormId) return;

            confirmBtn.classList.add('is-loading');
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Deleting...';

            var form = document.getElementById(_deleteRouteFormId);
            if (form) {
                form.submit();
            } else {
                var modalEl = document.getElementById('deleteRouteConfirmModal');
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
