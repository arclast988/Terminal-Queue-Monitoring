<?= view('templates/header', ['title' => $title]) ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">

<div class="page-header-modern fade-in">
    <h1 class="page-title-modern">
        <i class="bi bi-journal-text"></i>
        System Activity Logs
    </h1>
    <form action="<?= base_url('admin/logs/clear') ?>" method="post" class="d-inline"
        onsubmit="return confirm('Are you sure you want to clear all logs? This action cannot be undone.')">
        <?= csrf_field() ?>
        <button type="submit" class="btn-modern btn-modern-primary">
            <i class="bi bi-trash-fill"></i> Clear All Logs
        </button>
    </form>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert-modern alert-modern-success fade-in">
        <i class="bi bi-check-circle-fill alert-modern-icon"></i>
        <div><?= esc(session()->getFlashdata('success')) ?></div>
    </div>
<?php endif; ?>

<div class="modern-card shadow-modern fade-in">
    <div class="modern-card-header">
        <span class="modern-card-title">
            <i class="bi bi-list-ul" style="color: var(--primary-red);"></i>
            Log Entries
        </span>
    </div>
    <div class="modern-card-body">
        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>Manage</th>
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
                                        <div class="fw-bold"><?= esc($log['username']) ?></div>
                                        <small class="text-muted d-block"><?= esc($log['full_name']) ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">System/Guest</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Action"><?= esc($log['action']) ?></td>
                                <td data-label="Details" class="log-details-cell"><?= esc($log['details']) ?></td>
                                <td data-label="Manage" class="text-center">
                                    <form action="<?= base_url('admin/logs/delete/' . $log['id']) ?>" method="post" class="d-inline"
                                        onsubmit="return confirm('Delete this log entry?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn-modern btn-modern-sm btn-modern-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i> <span class="action-label">Delete</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                No logs found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<style>
    .table-modern .log-details-cell {
        max-width: 280px;
        white-space: normal !important;
        overflow-wrap: anywhere;
        font-size: 13px;
        color: #64748b;
    }
    
    @media (max-width: 768px) {
        .table-modern .log-details-cell {
            max-width: none;
        }
        
        .action-label {
            display: none;
        }
        
        .btn-modern.btn-modern-sm {
            padding: 6px 10px;
        }
    }
</style>

<?= view('templates/footer') ?>
