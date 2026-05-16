<?= $this->include('templates/header') ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Users</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= base_url('admin/users/create') ?>" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-circle"></i> Add New User
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
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr
                    style="background:#f8fafc; font-size:12px; text-transform:uppercase; letter-spacing:0.5px; color:#64748b;">
                    <th style="width:40px; text-align:center; padding:10px 8px;">#</th>
                    <th style="padding:10px 8px;">User</th>
                    <th style="padding:10px 8px;">Role</th>
                    <th style="padding:10px 8px;">Assigned Routes</th>
                    <th style="width:90px; text-align:center; padding:10px 8px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $i => $user): ?>
                    <tr>
                        <td class="text-center text-muted" style="padding:10px 8px; font-size:13px;"><?= $i + 1 ?></td>
                        <td style="padding:10px 8px;">
                            <div>
                                <span class="fw-bold" style="font-size:14px;"><?= esc($user['full_name']) ?></span>
                                <div>
                                    <small class="text-muted" style="font-size:11px;">@<?= esc($user['username']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td style="padding:10px 8px;">
                            <span
                                class="badge bg-<?= $user['role'] == 'admin' ? 'danger' : 'info' ?>"><?= $user['role'] == 'staff' ? 'Dispatcher' : ucfirst($user['role']) ?></span>
                        </td>
                        <td style="padding:10px 8px; max-width:280px;">
                            <?php if ($user['role'] === 'admin'): ?>
                                <span class="badge bg-success">All Routes</span>
                            <?php elseif (!empty($user['assigned_routes_label']) && $user['assigned_routes_label'] !== 'None'): ?>
                                <div style="display: flex; gap: 4px; overflow-x: auto; padding-bottom: 4px;">
                                    <?php
                                    $routeParts = explode(', ', $user['assigned_routes_label']);
                                    foreach ($routeParts as $rp):
                                        ?>
                                        <span class="badge bg-primary"
                                            style="font-size:10px; font-weight:500; white-space: nowrap;"><?= esc($rp) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">No Routes</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center" style="padding:10px 8px;">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="<?= base_url('admin/users/edit/' . $user['id']) ?>"
                                    class="btn btn-sm btn-outline-primary" title="Edit" style="padding:3px 7px;">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if ((int) $user['id'] !== (int) session()->get('id')): ?>
                                    <form action="<?= base_url('admin/users/delete/' . $user['id']) ?>" method="post"
                                        class="d-inline"
                                        onsubmit="return confirm('Delete user <?= esc($user['username']) ?>?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"
                                            style="padding:3px 7px;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->include('templates/footer') ?>