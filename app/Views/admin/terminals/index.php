<?= view('templates/header', ['title' => $title]) ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">

<div class="page-header-modern fade-in">
    <h1 class="page-title-modern">
        <i class="bi bi-geo-alt"></i>
        Terminals Management
    </h1>
    <?php if (session()->get('role') === 'admin'): ?>
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
                        <?php if (session()->get('role') === 'admin'): ?>
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
                                <td data-label="Capacity"><span class="badge-modern badge-modern-info"><?= $terminal['capacity'] ?> pax</span></td>
                                <td data-label="Created At"><?= strtoupper(date('M d, Y', strtotime($terminal['created_at']))) ?></td>
                                <?php if (session()->get('role') === 'admin'): ?>
                                <td data-label="Actions">
                                    <div class="d-flex gap-2">
                                        <a href="<?= base_url('admin/terminals/edit/'.$terminal['id']) ?>" class="btn-modern btn-modern-outline btn-modern-sm" title="Edit">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form action="<?= base_url('admin/terminals/delete/'.$terminal['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this terminal?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn-modern btn-modern-sm btn-action-delete" title="Delete">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= session()->get('role') === 'admin' ? '6' : '5' ?>" style="text-align: center; padding: 40px; color: var(--slate-500);">No terminals found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= view('templates/footer') ?>
