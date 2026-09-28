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

/* Severity Selector Styles */
.severity-options-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}
@media (max-width: 768px) {
    .severity-options-grid {
        grid-template-columns: 1fr;
    }
}
.severity-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    border: 2px solid var(--border-light, #e2e8f0);
    border-radius: 12px;
    background: #ffffff;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    user-select: none;
}
.severity-card:hover {
    border-color: #cbd5e1;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.severity-card input.severity-radio {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}
.severity-card-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
    transition: transform 0.2s ease;
}
.severity-card:hover .severity-card-icon {
    transform: scale(1.06);
}
.severity-info { background: #e0f2fe; color: #0284c7; }
.severity-warning { background: #fef3c7; color: #d97706; }
.severity-danger { background: #fee2e2; color: #dc2626; }

.severity-card.active[data-severity="info"] {
    border-color: #0284c7;
    background: #f0f9ff;
    box-shadow: 0 0 0 1px #0284c7, 0 4px 12px rgba(2, 132, 199, 0.12);
}
.severity-card.active[data-severity="warning"] {
    border-color: #d97706;
    background: #fffbeb;
    box-shadow: 0 0 0 1px #d97706, 0 4px 12px rgba(217, 119, 6, 0.12);
}
.severity-card.active[data-severity="danger"] {
    border-color: #dc2626;
    background: #fef2f2;
    box-shadow: 0 0 0 1px #dc2626, 0 4px 12px rgba(220, 38, 38, 0.12);
}
.severity-card-content {
    flex: 1;
    min-width: 0;
}
.severity-card-title {
    font-weight: 700;
    font-size: 13.5px;
    color: #0f172a;
    line-height: 1.2;
}
.severity-card-desc {
    font-size: 11.5px;
    color: #64748b;
    margin-top: 3px;
    line-height: 1.3;
}
.severity-check-indicator {
    display: none;
    font-size: 16px;
    margin-left: auto;
    flex-shrink: 0;
}
.severity-card.active[data-severity="info"] .severity-check-indicator {
    display: block;
    color: #0284c7;
}
.severity-card.active[data-severity="warning"] .severity-check-indicator {
    display: block;
    color: #d97706;
}
.severity-card.active[data-severity="danger"] .severity-check-indicator {
    display: block;
    color: #dc2626;
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

            <div class="card-modern fade-in">
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

                        <?php $selectedSeverity = old('severity', $announcement['severity'] ?? 'info'); ?>
                        <div class="mb-4">
                            <label class="form-label-modern d-block">Severity Level <span class="text-danger">*</span></label>
                            <div class="severity-options-grid">
                                <label class="severity-card <?= $selectedSeverity === 'info' ? 'active' : '' ?>" data-severity="info">
                                    <input type="radio" name="severity" value="info" <?= $selectedSeverity === 'info' ? 'checked' : '' ?> class="severity-radio">
                                    <div class="severity-card-icon severity-info"><i class="bi bi-info-circle-fill"></i></div>
                                    <div class="severity-card-content">
                                        <div class="severity-card-title">Info / Normal</div>
                                        <div class="severity-card-desc">General notices & routine updates</div>
                                    </div>
                                    <div class="severity-check-indicator"><i class="bi bi-check-circle-fill"></i></div>
                                </label>
                                <label class="severity-card <?= $selectedSeverity === 'warning' ? 'active' : '' ?>" data-severity="warning">
                                    <input type="radio" name="severity" value="warning" <?= $selectedSeverity === 'warning' ? 'checked' : '' ?> class="severity-radio">
                                    <div class="severity-card-icon severity-warning"><i class="bi bi-exclamation-triangle-fill"></i></div>
                                    <div class="severity-card-content">
                                        <div class="severity-card-title">Warning</div>
                                        <div class="severity-card-desc">Delays, detours & cautions</div>
                                    </div>
                                    <div class="severity-check-indicator"><i class="bi bi-check-circle-fill"></i></div>
                                </label>
                                <label class="severity-card <?= $selectedSeverity === 'danger' ? 'active' : '' ?>" data-severity="danger">
                                    <input type="radio" name="severity" value="danger" <?= $selectedSeverity === 'danger' ? 'checked' : '' ?> class="severity-radio">
                                    <div class="severity-card-icon severity-danger"><i class="bi bi-exclamation-octagon-fill"></i></div>
                                    <div class="severity-card-content">
                                        <div class="severity-card-title">Urgent / Alert</div>
                                        <div class="severity-card-desc">Cancellations, emergencies & suspensions</div>
                                    </div>
                                    <div class="severity-check-indicator"><i class="bi bi-check-circle-fill"></i></div>
                                </label>
                            </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    var cards = document.querySelectorAll('.severity-card');
    cards.forEach(function(card) {
        card.addEventListener('click', function() {
            var radio = card.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
                cards.forEach(function(c) { c.classList.remove('active'); });
                card.classList.add('active');
            }
        });
    });
});
</script>

<?= $this->include('templates/footer') ?>
