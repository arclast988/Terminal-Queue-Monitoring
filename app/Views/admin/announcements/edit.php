<?= $this->include('templates/header') ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">

<style>
/* Button sizing (always applies) */
.card-modern a.btn-modern-outline,
.page-header-modern a.btn-modern-outline {
    padding: 10px 20px !important;
    font-size: 14px !important;
}

</style>

<div class="page-header-modern fade-in">
    <div>
        <h1 class="page-title-modern">
            <i class="bi bi-pencil-square"></i> Edit Announcement
        </h1>
        <p class="text-muted mb-0">Update announcement details</p>
    </div>
    <div class="mt-3 mt-md-0">
        <a href="<?= base_url('admin/announcements') ?>" class="btn btn-modern btn-modern-outline">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>
</div>

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

            <?= $this->include('partials/flash_notices') ?>

            <div class="card-modern">
                <div class="card-header-modern">
                    <span class="card-title-modern"><i class="bi bi-chat-left-text me-2"></i> Announcement Details</span>
                </div>
                <div class="card-body-modern">
                    <form action="<?= base_url('admin/announcements/update/'.$announcement['id']) ?>" method="post" data-no-change-guard>
                        <?= csrf_field() ?>

                        <div class="mb-4">
                            <label for="message" class="form-label-modern">Message <span class="text-danger">*</span></label>
                            <textarea class="input-modern" id="message" name="message" rows="4" required><?= old('message', $announcement['message']) ?></textarea>
                            <div class="form-text-modern">This message will be displayed on the guest dashboard.</div>
                        </div>

                        <?php $onlyTerminal = (is_array($terminals) && count($terminals) === 1); ?>
                        <div class="mb-4">
                            <label for="terminal_id" class="form-label-modern">Terminal <span class="text-danger">*</span></label>
                            <?php if ($onlyTerminal): ?>
                                <?php $singleTerminal = reset($terminals); ?>
                                <input type="text" class="input-modern" value="<?= esc($singleTerminal['name']) ?>" readonly style="background-color: var(--surface-sunken, #f8fafc); cursor: not-allowed;">
                                <input type="hidden" name="terminal_id" id="terminal_id" value="<?= $singleTerminal['id'] ?>">
                            <?php else: ?>
                                <select class="select-modern" id="terminal_id" name="terminal_id" required>
                                    <option value="">— Select Terminal —</option>
                                    <?php foreach ($terminals as $t): ?>
                                        <option value="<?= $t['id'] ?>" <?= (old('terminal_id', $announcement['terminal_id'] ?? '') == $t['id']) ? 'selected' : '' ?>>
                                            <?= esc($t['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>

                        <div class="mb-4">
                            <label class="form-label-modern d-block">Status</label>
                            <div class="form-check-modern">
                                <input class="form-check-input-modern" type="checkbox" name="is_active" id="is_active" value="1" <?= !empty($announcement['is_active']) ? 'checked' : '' ?>>
                                <label class="form-check-label-modern" for="is_active">
                                    <span class="fw-semibold">Active</span>
                                    <small class="d-block text-muted">Show this announcement on the guest dashboard</small>
                                </label>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-5">
                            <button type="submit" class="btn btn-modern btn-modern-primary">
                                <i class="bi bi-save"></i> Update Announcement
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

<?= $this->include('templates/footer') ?>
