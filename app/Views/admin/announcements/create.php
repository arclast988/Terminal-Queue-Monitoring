<?= $this->include('templates/header') ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">

<style>
.card-modern a.btn-modern-outline,
.page-header-modern a.btn-modern-outline,
.card-modern a.btn-modern-outline .bi,
.card-modern a.btn-modern-outline i,
.page-header-modern a.btn-modern-outline .bi,
.page-header-modern a.btn-modern-outline i {
    background: #334155 !important;
    border-color: #475569 !important;
    color: #f1f5f9 !important;
    -webkit-text-fill-color: #f1f5f9 !important;
    padding: 10px 20px !important;
    font-size: 14px !important;
}
.card-modern a.btn-modern-outline:hover,
.page-header-modern a.btn-modern-outline:hover,
.card-modern a.btn-modern-outline:hover .bi,
.card-modern a.btn-modern-outline:hover i,
.page-header-modern a.btn-modern-outline:hover .bi,
.page-header-modern a.btn-modern-outline:hover i {
    background: #475569 !important;
    border-color: #64748b !important;
    color: #fff !important;
    -webkit-text-fill-color: #fff !important;
}
</style>

<div class="page-header-modern">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="page-title-modern">
                    <i class="bi bi-megaphone"></i> Add Announcement
                </h1>
                <p class="text-muted mb-0">Create a new public announcement</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="<?= base_url('admin/announcements') ?>" class="btn btn-modern btn-modern-outline">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert-modern alert-modern-danger mb-4">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Validation Errors:</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card-modern">
                <div class="card-header-modern">
                    <i class="bi bi-chat-left-text me-2"></i> Announcement Details
                </div>
                <div class="card-body-modern">
                    <form action="<?= base_url('admin/announcements/store') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="mb-4">
                            <label for="message" class="form-label-modern">Message <span class="text-danger">*</span></label>
                            <textarea class="input-modern" id="message" name="message" rows="4" required placeholder="e.g. IMPORTANT ADVISORY: Due to heavy rains, there may be delays on the Ormoc route."><?= old('message') ?></textarea>
                            <div class="form-text-modern">Shown in the advisory bar on the guest dashboard. Keep it short for best display.</div>
                        </div>

                        <?php $onlyTerminal = (is_array($terminals) && count($terminals) === 1); ?>
                        <div class="mb-4">
                            <label for="terminal_id" class="form-label-modern">Terminal <span class="text-danger">*</span></label>
                            <select class="select-modern" id="terminal_id" name="terminal_id" required>
                                <?php if (!$onlyTerminal): ?>
                                    <option value="">— Select Terminal —</option>
                                <?php endif; ?>
                                <?php foreach ($terminals as $t): ?>
                                    <option value="<?= $t['id'] ?>" <?= ($onlyTerminal || old('terminal_id') == $t['id']) ? 'selected' : '' ?>>
                                        <?= esc($t['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label-modern d-block">Status</label>
                            <div class="form-check-modern">
                                <input class="form-check-input-modern" type="checkbox" name="is_active" id="is_active" value="1" <?= old('is_active', '1') ? 'checked' : '' ?>>
                                <label class="form-check-label-modern" for="is_active">
                                    <span class="fw-semibold">Active</span>
                                    <small class="d-block text-muted">Show this announcement on the guest dashboard</small>
                                </label>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-5">
                            <button type="submit" class="btn btn-modern btn-modern-primary">
                                <i class="bi bi-save"></i> Save Announcement
                            </button>
                            <a href="<?= base_url('admin/announcements') ?>" class="btn btn-modern btn-modern-outline">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->include('templates/footer') ?>
