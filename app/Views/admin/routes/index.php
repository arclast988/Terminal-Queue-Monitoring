<?= view('templates/header', ['title' => $title]) ?>

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Routes Management</h1>
    <?php if (session()->get('role') === 'admin'): ?>
    <div>
        <a href="<?= base_url('admin/routes/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New Route
        </a>
    </div>
    <?php endif; ?>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center" style="padding:10px 16px;">
        <span style="font-weight:600; font-size:14px; color:#1e293b;"><i class="bi bi-signpost-split me-1"></i> Route List</span>
    </div>
    <div class="card-body">
        <div class="table-responsive table-responsive-card">
            <table class="table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Terminal</th>
                        <th>Destination</th>
                        <th>Type</th>
                        <th>Fare (PHP)</th>
                        <?php if (session()->get('role') === 'admin'): ?>
                        <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($routes) && is_array($routes)): ?>
                        <?php foreach ($routes as $route): ?>
                            <tr>
                                <td data-label="ID"><?= $route['id'] ?></td>
                                <td data-label="Terminal"><?= esc($route['terminal_name']) ?></td>
                                <td data-label="Destination"><?= esc($route['destination']) ?></td>
                                <td data-label="Type">
                                    <?php
                                        $badgeClass = 'bg-secondary';
                                        if ($route['vehicle_type'] == 'van') {
                                            $badgeClass = 'bg-info';
                                        } elseif ($route['vehicle_type'] == 'jeepney') {
                                            $badgeClass = 'bg-warning text-dark';
                                        } elseif ($route['vehicle_type'] == 'minibus') {
                                            $badgeClass = 'bg-purple text-white';
                                        }
                                    ?>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= strtoupper($route['vehicle_type']) ?>
                                    </span>
                                </td>
                                <td data-label="Fare (PHP)"><?= number_format($route['fare'], 2) ?></td>
                                <?php if (session()->get('role') === 'admin'): ?>
                                <td data-label="Actions">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="<?= base_url('admin/routes/edit/'.$route['id']) ?>" class="btn btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i> <span class="action-label">Edit</span>
                                        </a>
                                        <form action="<?= base_url('admin/routes/delete/'.$route['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this route?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
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
                            <td colspan="<?= session()->get('role') === 'admin' ? '6' : '5' ?>" class="text-center">No routes found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= view('templates/footer') ?>
