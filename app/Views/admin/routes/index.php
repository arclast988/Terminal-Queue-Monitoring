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

<?php if (!empty($groupedRoutes)): ?>
    <div class="d-flex flex-column gap-4 fade-in">
        <?php foreach ($groupedRoutes as $routeKey => $group): ?>
            <div class="modern-card shadow-modern mb-0">
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
    </div>
<?php else: ?>
    <div class="modern-card shadow-modern fade-in text-center py-5">
        <div class="py-4 text-muted">
            <i class="bi bi-signpost-split fs-1 d-block mb-3"></i>
            No routes found. Click <strong>Add New Route</strong> to create one.
        </div>
    </div>
<?php endif; ?>

<?= view('templates/footer') ?>
