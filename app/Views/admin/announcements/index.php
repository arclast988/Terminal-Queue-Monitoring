<?= view('templates/header', ['title' => $title]) ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">

<div class="page-header-modern fade-in">
    <h1 class="page-title-modern">
        <i class="bi bi-megaphone"></i>
        Announcements
    </h1>
    <?php if (in_array(session()->get('role'), ['super_admin', 'admin', 'staff'], true)): ?>
    <div>
        <a href="<?= base_url('admin/announcements/create') ?>" class="btn-modern btn-modern-primary">
            <i class="bi bi-plus-circle"></i> Add Announcement
        </a>
    </div>
    <?php endif; ?>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert-modern alert-modern-success fade-in">
        <i class="bi bi-check-circle-fill alert-modern-icon"></i>
        <div><?= esc(session()->getFlashdata('success')) ?></div>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert-modern alert-modern-danger fade-in">
        <i class="bi bi-exclamation-triangle-fill alert-modern-icon"></i>
        <div><?= esc(session()->getFlashdata('error')) ?></div>
    </div>
<?php endif; ?>

<div class="modern-card shadow-modern fade-in">
    <div class="modern-card-header">
        <span class="modern-card-title">
            <i class="bi bi-list" style="color: var(--primary-red);"></i>
            Announcement List
        </span>
    </div>
    <div class="modern-card-body">
        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Terminal</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Created</th>
                        <?php if (in_array(session()->get('role'), ['super_admin', 'admin', 'staff'], true)): ?>
                        <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($announcements) && is_array($announcements)): ?>
                        <?php foreach ($announcements as $a): ?>
                            <tr>
                                <td data-label="ID"><strong>#<?= $a['id'] ?></strong></td>
                                <td data-label="Terminal"><span class="badge-modern badge-modern-primary"><?= esc($a['terminal_name'] ?? '—') ?></span></td>
                                <td data-label="Message"><?= esc(strlen($a['message']) > 80 ? substr($a['message'], 0, 80) . '…' : $a['message']) ?></td>
                                <td data-label="Status">
                                    <?php if ($a['is_active']): ?>
                                        <span class="badge-modern badge-modern-success"><i class="bi bi-check-circle-fill"></i> Active</span>
                                    <?php else: ?>
                                        <span class="badge-modern badge-modern-info"><i class="bi bi-pause-circle-fill"></i> Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Created">
                                    <?php if (!empty($a['created_at'])): ?>
                                        <div style="white-space: nowrap; font-size: 13px; font-weight: 600; color: var(--text-main);">
                                            <i class="bi bi-calendar3 text-muted me-1" style="font-size: 12px;"></i><?= date('M d, Y', strtotime($a['created_at'])) ?>
                                        </div>
                                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                                            <?= date('H:i', strtotime($a['created_at'])) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <?php if (in_array(session()->get('role'), ['super_admin', 'admin', 'staff'], true)): ?>
                                <td data-label="Actions">
                                    <div class="d-flex gap-2">
                                        <a href="<?= base_url('admin/announcements/edit/' . $a['id']) ?>" class="btn-modern btn-modern-outline btn-modern-sm" title="Edit">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form action="<?= base_url('admin/announcements/delete/' . $a['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Delete this announcement?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn-modern btn-modern-sm btn-action-delete" title="Delete">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= in_array(session()->get('role'), ['super_admin', 'admin', 'staff'], true) ? '6' : '5' ?>" style="text-align: center; padding: 40px; color: var(--slate-500);">No announcements yet. Add one to show on the guest dashboard.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= view('templates/footer') ?>
