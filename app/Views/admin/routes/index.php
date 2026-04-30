<?= view('templates/header', ['title' => $title]) ?>

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Routes Management</h1>
    <div>
        <a href="<?= base_url('admin/routes/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New Route
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
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Origin</th>
                        <th>Destination</th>
                        <th>Type</th>
                        <th>Fare (PHP)</th>
                        <th>Terminal</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($routes) && is_array($routes)): ?>
                        <?php foreach ($routes as $route): ?>
                            <tr>
                                <td><?= $route['id'] ?></td>
                                <td><?= esc($route['origin']) ?></td>
                                <td><?= esc($route['destination']) ?></td>
                                <td>
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
                                <td><?= number_format($route['fare'], 2) ?></td>
                                <td><?= esc($route['terminal_name']) ?></td>
                                <td>
                                    <a href="<?= base_url('admin/routes/edit/'.$route['id']) ?>" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="<?= base_url('admin/routes/delete/'.$route['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this route?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No routes found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= view('templates/footer') ?>
