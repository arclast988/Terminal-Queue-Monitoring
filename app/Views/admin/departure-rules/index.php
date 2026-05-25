<?= view('templates/header', ['title' => $title]) ?>

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">Departure Time Rules</h1>
        <p class="text-muted mb-0">Configure how long vehicles wait before departing, based on time of day.</p>
        <?php if (session()->get('role') === 'staff'): ?>
            <span class="badge bg-info">View Only</span>
        <?php endif; ?>
    </div>
    <div>
        <?php if (session()->get('role') !== 'staff'): ?>
            <a href="<?= base_url($prefix . '/departure-rules/create') ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Rule
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center" style="padding:10px 16px;">
        <span style="font-weight:600; font-size:14px; color:#1e293b;"><i class="bi bi-clock-history me-1"></i> Rule List</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Terminal</th>
                        <th>Time From</th>
                        <th>Time To</th>
                        <th>Wait (Minutes)</th>
                        <th>Label</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rules) && is_array($rules)): ?>
                        <?php foreach ($rules as $rule): ?>
                            <tr>
                                <td><?= esc($rule['terminal_name'] ?? '-') ?></td>
                                <td><span class="badge bg-primary"><?= date('g:i A', strtotime($rule['time_from'])) ?></span></td>
                                <td><span class="badge bg-info"><?= date('g:i A', strtotime($rule['time_to'])) ?></span></td>
                                <td><strong><?= $rule['wait_minutes'] ?> min</strong></td>
                                <td><?= esc($rule['label'] ?? '-') ?></td>
                                <td>
                                    <?php if (session()->get('role') !== 'staff'): ?>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= base_url($prefix . '/departure-rules/edit/'.$rule['id']) ?>" class="btn btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i> <span class="action-label">Edit</span>
                                            </a>
                                            <form action="<?= base_url($prefix . '/departure-rules/delete/'.$rule['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this rule?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                    <i class="bi bi-trash"></i> <span class="action-label">Delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">View Only</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No departure rules configured. Default of 30 minutes will be used.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= view('templates/footer') ?>
