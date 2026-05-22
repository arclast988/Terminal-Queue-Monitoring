<?= view('templates/header', ['title' => $title]) ?>

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Announcements</h1>
    <div>
        <a href="<?= base_url('admin/announcements/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Announcement
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
                        <th>Terminal</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($announcements) && is_array($announcements)): ?>
                        <?php foreach ($announcements as $a): ?>
                            <tr>
                                <td><?= $a['id'] ?></td>
                                <td><span class="badge bg-dark"><?= esc($a['terminal_name'] ?? '—') ?></span></td>
                                <td><?= esc(strlen($a['message']) > 80 ? substr($a['message'], 0, 80) . '…' : $a['message']) ?>
                                </td>
                                <td>
                                    <?php if ($a['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $a['created_at'] ? date('M d, Y H:i', strtotime($a['created_at'])) : '-' ?></td>
                                <td>
                                    <a href="<?= base_url('admin/announcements/edit/' . $a['id']) ?>"
                                        class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="<?= base_url('admin/announcements/delete/' . $a['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Delete this announcement?');">
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
                            <td colspan="6" class="text-center text-muted py-4">No announcements yet. Add one to show on the
                                guest dashboard.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= view('templates/footer') ?>