<?= view('templates/header', ['title' => $title]) ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">

<div class="page-header-modern fade-in">
    <h1 class="page-title-modern">
        <i class="bi bi-clock-history"></i>
        Departure Time Rules
    </h1>
    <?php if (session()->get('role') !== 'staff'): ?>
    <div>
        <a href="<?= base_url($prefix . '/departure-rules/create') ?>" class="btn-modern btn-modern-primary">
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

<div class="modern-card shadow-modern fade-in">
    <div class="modern-card-header">
        <span class="modern-card-title">
            <i class="bi bi-list" style="color: var(--primary-red);"></i>
            Rule List
        </span>
    </div>
    <div class="modern-card-body">
        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th>Terminal</th>
                        <th>Destination</th>
                        <th>Time From</th>
                        <th>Time To</th>
                        <th>Wait Time</th>
                        <th>Label</th>
                        <?php if (session()->get('role') !== 'staff'): ?>
                        <th>Actions</th>
                        <?php else: ?>
                        <th>Status</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rules) && is_array($rules)): ?>
                        <?php foreach ($rules as $rule): ?>
                            <tr>
                                <td data-label="Terminal"><?= esc($rule['terminal_name'] ?? '-') ?></td>
                                <td data-label="Destination"><?= esc($rule['route_destination'] ?? '-') ?></td>
                                <td data-label="Time From"><span class="badge-modern badge-modern-primary"><?= date('H:i', strtotime($rule['time_from'])) ?></span></td>
                                <td data-label="Time To"><span class="badge-modern badge-modern-info"><?= date('H:i', strtotime($rule['time_to'])) ?></span></td>
                                <td data-label="Wait Time">
                                    <?php
                                        $mins = (int)$rule['wait_minutes'];
                                        $h = floor($mins / 60);
                                        $m = $mins % 60;
                                    ?>
                                    <strong><?= sprintf('%02d:%02d', $h, $m) ?></strong>
                                </td>
                                <td data-label="Label"><?= esc($rule['label'] ?? '-') ?></td>
                                <?php if (session()->get('role') !== 'staff'): ?>
                                <td data-label="Actions">
                                    <div class="d-flex gap-2">
                                        <a href="<?= base_url($prefix . '/departure-rules/edit/'.$rule['id']) ?>" class="btn-modern btn-modern-outline btn-modern-sm" title="Edit">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form action="<?= base_url($prefix . '/departure-rules/delete/'.$rule['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this rule?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn-modern btn-modern-sm btn-action-delete" title="Delete">
                                                <i class="bi bi-trash"></i> Delete
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
                        <tr>
                            <td colspan="<?= session()->get('role') !== 'staff' ? '7' : '7' ?>" style="text-align: center; padding: 40px; color: var(--slate-500);">No departure rules configured. Default of 30 minutes will be used.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= view('templates/footer') ?>
