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

<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center" style="padding:10px 16px;">
        <span style="font-weight:600; font-size:14px; color:#1e293b;"><i class="bi bi-journal-text me-1"></i> Log Entries</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm">
                <thead class="table-light">
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
                                <td><?= $log['id'] ?></td>
                                <td><?= $log['timestamp'] ?></td>
                                <td>
                                    <?php if ($log['username']): ?>
                                        <span class="fw-bold"><?= esc($log['username']) ?></span>
                                        <small class="text-muted d-block"><?= esc($log['full_name']) ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">System/Guest</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($log['action']) ?></td>
                                <td><?= esc($log['details']) ?></td>
                                <td>
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

<?= view('templates/footer') ?>