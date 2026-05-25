<?= view('templates/header', ['title' => $title]) ?>

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Terminals Management</h1>
    <div>
        <a href="<?= base_url('admin/terminals/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New Terminal
        </a>
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
        <span style="font-weight:600; font-size:14px; color:#1e293b;"><i class="bi bi-geo-alt me-1"></i> Terminal List</span>
    </div>
    <div class="card-body">
        <div class="table-responsive table-responsive-card">
            <table class="table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Capacity</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($terminals) && is_array($terminals)): ?>
                        <?php foreach ($terminals as $terminal): ?>
                            <tr>
                                <td data-label="ID"><?= $terminal['id'] ?></td>
                                <td data-label="Name"><?= esc($terminal['name']) ?></td>
                                <td data-label="Location"><?= esc($terminal['location']) ?></td>
                                <td data-label="Capacity"><?= $terminal['capacity'] ?></td>
                                <td data-label="Created At"><?= $terminal['created_at'] ?></td>
                                <td data-label="Actions">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="<?= base_url('admin/terminals/edit/'.$terminal['id']) ?>" class="btn btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i> <span class="action-label">Edit</span>
                                        </a>
                                        <form action="<?= base_url('admin/terminals/delete/'.$terminal['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this terminal?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i> <span class="action-label">Delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No terminals found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= view('templates/footer') ?>
