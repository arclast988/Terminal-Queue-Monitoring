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
    <div class="card-header d-flex justify-content-between align-items-center" style="padding:10px 16px;">
        <span style="font-weight:600; font-size:14px; color:#1e293b;"><i class="bi bi-people me-1"></i> User List</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="users-table">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Assigned Routes</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $i => $user): ?>
                        <tr>
                            <td class="row-number"><?= $i + 1 ?></td>
                            <td>
                                <div>
                                    <span class="fw-bold" style="font-size:14px;"><?= esc($user['full_name']) ?></span>
                                    <div>
                                        <small class="text-muted" style="font-size:11px;">@<?= esc($user['username']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge bg-<?= $user['role'] == 'admin' ? 'danger' : 'info' ?>"><?= $user['role'] == 'staff' ? 'Dispatcher' : ucfirst($user['role']) ?></span>
                            </td>
                            <td style="max-width: 350px;">
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
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?= base_url('admin/users/edit/' . $user['id']) ?>"
                                        class="btn btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i> <span class="user-action-label">Edit</span>
                                    </a>
                                    <?php if ((int) $user['id'] !== (int) session()->get('id')): ?>
                                        <form action="<?= base_url('admin/users/delete/' . $user['id']) ?>" method="post"
                                            class="d-inline"
                                            onsubmit="return confirm('Delete user <?= esc($user['username']) ?>?')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i> <span class="user-action-label">Delete</span>
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
</div>

<style>
    #users-table {
        width: 100%;
    }

    #users-table th,
    #users-table td {
        vertical-align: middle;
    }

    #users-table .btn-group .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
    }

    @media (max-width: 991.98px) {
        #users-table {
            min-width: 680px;
            font-size: 13px;
        }

        #users-table thead th,
        #users-table tbody td {
            padding: 0.65rem 0.5rem !important;
        }

        #users-table th:first-child,
        #users-table td.row-number {
            width: 38px;
            text-align: center;
        }

        #users-table .btn-group {
            flex-direction: row !important;
            gap: 0.35rem;
        }

        #users-table .btn-group .btn {
            width: 34px !important;
            height: 34px;
            padding: 0 !important;
            border-radius: 6px !important;
        }

        #users-table .btn-group form {
            display: inline-flex !important;
        }

        .user-action-label {
            display: none;
        }
    }
</style>

<?= $this->include('templates/footer') ?>