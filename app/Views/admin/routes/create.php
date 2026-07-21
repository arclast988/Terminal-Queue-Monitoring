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
/* Dark mode only */
@media (prefers-color-scheme: dark) {
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
}
</style>

<div class="page-header-modern fade-in">
    <h1 class="page-title-modern">
        <i class="bi bi-plus-circle"></i> Add New Route / Fare
    </h1>
    <a href="<?= base_url('admin/routes') ?>" class="btn-modern btn-modern-outline">
        <i class="bi bi-arrow-left"></i> Back to List
    </a>
</div>

<div class="card-modern fade-in">
    <div class="card-body-modern">
        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert-modern alert-modern-danger">
                <i class="bi bi-exclamation-triangle-fill alert-modern-icon"></i>
                <ul class="mb-0">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('admin/routes/store') ?>" method="post">
            <?= csrf_field() ?>

            <?php $onlyTerminal = (is_array($terminals) && count($terminals) === 1); ?>
            <div class="mb-3">
                <label for="terminal_id" class="form-label-modern">Terminal</label>
                <select class="select-modern" id="terminal_id" name="terminal_id" required>
                    <?php if (!$onlyTerminal): ?>
                        <option value="">Select Terminal</option>
                    <?php endif; ?>
                    <?php foreach ($terminals as $terminal): ?>
                        <option value="<?= $terminal['id'] ?>"
                                data-name="<?= esc($terminal['name']) ?>"
                                <?= ($onlyTerminal || old('terminal_id') == $terminal['id']) ? 'selected' : '' ?>>
                            <?= esc($terminal['name']) ?> (<?= esc($terminal['location']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text-modern">
                    <i class="bi bi-info-circle me-1"></i>This terminal is used as the route source.
                </div>
            </div>

            <div class="mb-4">
                <label for="destination" class="form-label-modern">Destination</label>
                <input type="text" class="input-modern" id="destination" name="destination"
                       value="<?= old('destination') ?>" placeholder="e.g. ORMOC, TACLOBAN" required minlength="2" maxlength="100">
                <div class="form-text-modern">Type the destination city/town name.</div>
            </div>

            <div class="mb-4">
                <label class="form-label-modern d-block">Vehicle Types & Fares (PHP)</label>
                <div class="form-text-modern mb-3"><i class="bi bi-info-circle me-1"></i>Enter fares for all vehicle types serving this route. Leave blank for types that do not serve this route:</div>
                <div class="d-flex flex-column gap-3">
                    <div class="p-3 border rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-3" style="background: var(--surface-sunken, #f8fafc); border-color: var(--border, #e2e8f0) !important;">
                        <div class="fw-bold" style="color: var(--text-main); font-size: 15px;">🚐 Van</div>
                        <div class="input-group-modern" style="max-width: 250px;">
                            <span class="input-group-text-modern">₱</span>
                            <input type="number" step="0.01" min="1" class="input-modern" name="fares[van]"
                                   value="<?= old('fares.van', old('fare')) ?>" placeholder="Fare amount (e.g. 150.00)">
                        </div>
                    </div>
                    <div class="p-3 border rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-3" style="background: var(--surface-sunken, #f8fafc); border-color: var(--border, #e2e8f0) !important;">
                        <div class="fw-bold" style="color: var(--text-main); font-size: 15px;">🚌 Jeepney</div>
                        <div class="input-group-modern" style="max-width: 250px;">
                            <span class="input-group-text-modern">₱</span>
                            <input type="number" step="0.01" min="1" class="input-modern" name="fares[jeepney]"
                                   value="<?= old('fares.jeepney') ?>" placeholder="Fare amount (e.g. 100.00)">
                        </div>
                    </div>
                    <div class="p-3 border rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-3" style="background: var(--surface-sunken, #f8fafc); border-color: var(--border, #e2e8f0) !important;">
                        <div class="fw-bold" style="color: var(--text-main); font-size: 15px;">🚍 Mini Bus</div>
                        <div class="input-group-modern" style="max-width: 250px;">
                            <span class="input-group-text-modern">₱</span>
                            <input type="number" step="0.01" min="1" class="input-modern" name="fares[minibus]"
                                   value="<?= old('fares.minibus') ?>" placeholder="Fare amount (e.g. 120.00)">
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-modern btn-modern-primary">
                <i class="bi bi-save"></i> Save Route
            </button>
        </form>
    </div>
</div>

<?= $this->include('templates/footer') ?>
