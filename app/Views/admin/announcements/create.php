<?= view('templates/header', ['title' => $title]) ?>

<div class="row mb-3">
    <div class="col-md-6">
        <h2>Add Announcement</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="<?= base_url('admin/announcements') ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<div class="card shadow">
    <div class="card-body">
        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('admin/announcements/store') ?>" method="post">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea class="form-control" id="message" name="message" rows="3" required placeholder="e.g. IMPORTANT ADVISORY: Due to heavy rains, there may be delays on the Ormoc route."><?= old('message') ?></textarea>
                <small class="text-muted">Shown in the advisory bar on the guest dashboard. Keep it short for best display.</small>
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" <?= old('is_active', '1') ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_active">Active (show on guest dashboard)</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Announcement</button>
        </form>
    </div>
</div>

<?= view('templates/footer') ?>
