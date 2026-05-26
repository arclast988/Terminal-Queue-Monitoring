<?= view('templates/header', ['title' => $title]) ?>

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">System Activity Logs</h1>
    <div>
        <form action="<?= base_url('admin/logs/clear') ?>" method="post" class="d-inline"
            onsubmit="return confirm('Are you sure you want to clear all logs? This action cannot be undone.')">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash"></i> Clear All Logs
            </button>
        </form>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i> <?= esc(session()->getFlashdata('success')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center" style="padding:10px 16px;">
        <span style="font-weight:600; font-size:14px; color:#1e293b;"><i class="bi bi-journal-text me-1"></i> Log Entries</span>
    </div>
    <div class="card-body">
        <div class="table-responsive table-responsive-card">
            <table class="table table-striped table-hover table-sm" id="logs-table">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Action</th>
                        <th class="log-details-col">Details</th>
                        <th class="log-manage-col">Manage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs) && is_array($logs)): ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td data-label="ID"><?= $log['id'] ?></td>
                                <td data-label="Timestamp"><?= $log['timestamp'] ?></td>
                                <td data-label="User">
                                    <?php if ($log['username']): ?>
                                        <span class="fw-bold"><?= esc($log['username']) ?></span>
                                        <small class="text-muted d-block"><?= esc($log['full_name']) ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">System/Guest</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Action"><?= esc($log['action']) ?></td>
                                <td data-label="Details" class="log-details-cell"><?= esc($log['details']) ?></td>
                                <td data-label="Manage" class="log-manage-cell">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <form action="<?= base_url('admin/logs/delete/' . $log['id']) ?>" method="post" class="d-inline"
                                            onsubmit="return confirm('Delete this log entry?')">
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
                            <td colspan="6" class="text-center">No logs found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    #logs-table {
        width: 100%;
        min-width: 980px;
    }

    #logs-table th,
    #logs-table td {
        vertical-align: middle;
    }

    #logs-table .log-details-col,
    #logs-table .log-details-cell {
        min-width: 280px;
        white-space: normal !important;
        overflow-wrap: anywhere;
    }

    #logs-table .log-manage-col,
    #logs-table .log-manage-cell {
        width: 120px;
        text-align: center;
        vertical-align: middle;
    }

    #logs-table .btn-outline-danger {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        border: 1px solid #e53935 !important;
        color: #c62828;
    }

    #logs-table .btn-outline-danger:hover {
        background-color: #c62828;
        color: #fff;
    }

    @media (max-width: 768px) {
        #logs-table {
            min-width: 100% !important;
        }

        #logs-table .log-manage-col,
        #logs-table .log-manage-cell {
            width: auto;
        }
    }
</style>

<?= view('templates/footer') ?>
