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
                        <span class="fw-bold fs-5" style="color: var(--text-main);"><?= esc($group['terminal_name']) ?> <i class="bi bi-arrow-right text-muted mx-1"></i> <?= esc($group['destination']) ?></span>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <?php if ($isAdmin): ?>
                        <div class="d-inline-flex gap-2">
                            <a href="<?= base_url('admin/routes/edit/'.$group['items'][0]['id']) ?>" class="btn-modern btn-action-edit btn-modern-sm" style="padding: 6px 12px;" title="Edit Route Group">
                                <i class="bi bi-pencil"></i> Edit Route
                            </a>
                            <form action="<?= base_url('admin/routes/delete_group/'.$group['items'][0]['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this entire route group and all its fares?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn-modern btn-modern-sm btn-action-delete" style="padding: 6px 12px;" title="Delete Route Group">
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
        
        <div id="no-routes-match" class="modern-card shadow-modern fade-in text-center py-5 empty-state" style="display: none;">
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
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (noMatch) {
            noMatch.style.display = (visibleCount === 0 && totalCards > 0) ? '' : 'none';
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

<?= view('templates/footer') ?>
