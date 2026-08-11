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

<div class="page-header-modern">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="page-title-modern">
                    <i class="bi bi-plus-circle"></i> Add New Route / Fare
                </h1>
                <p class="text-muted mb-0">Create a new route and set initial fare details</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="<?= base_url('admin/routes') ?>" class="btn btn-modern btn-modern-outline">
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
                    <i class="bi bi-map me-2"></i> Route Information
                </div>
                <div class="card-body-modern">
                    <form action="<?= base_url('admin/routes/store') ?>" method="post">
                        <?= csrf_field() ?>

                        <?php $onlyTerminal = (is_array($terminals) && count($terminals) === 1); ?>
                        <div class="mb-4">
                            <label for="terminal_id" class="form-label-modern">Terminal <span class="text-danger">*</span></label>
                            <?php if ($onlyTerminal): ?>
                                <?php $singleTerminal = reset($terminals); ?>
                                <input type="text" class="input-modern" value="<?= esc($singleTerminal['name']) ?> (<?= esc($singleTerminal['location']) ?>)" readonly style="background-color: var(--surface-sunken, #f8fafc); cursor: not-allowed;">
                                <input type="hidden" name="terminal_id" id="terminal_id" value="<?= $singleTerminal['id'] ?>">
                            <?php else: ?>
                                <select class="select-modern" id="terminal_id" name="terminal_id" required>
                                    <option value="">Select Terminal</option>
                                    <?php foreach ($terminals as $terminal): ?>
                                        <option value="<?= $terminal['id'] ?>"
                                                data-name="<?= esc($terminal['name']) ?>"
                                                <?= (old('terminal_id') == $terminal['id']) ? 'selected' : '' ?>>
                                            <?= esc($terminal['name']) ?> (<?= esc($terminal['location']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                            <div class="form-text-modern">
                                <i class="bi bi-info-circle me-1"></i>This terminal is used as the route source.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="destination" class="form-label-modern">Destination <span class="text-danger">*</span></label>
                            <input type="text" class="input-modern autocomplete-location" id="destination" name="destination"
                                   value="<?= old('destination') ?>" placeholder="Search or type destination (e.g. ORMOC, TACLOBAN)..."
                                   required minlength="2" maxlength="100" autocomplete="off"
                                   data-suggestions="<?= esc(json_encode($all_locations ?? [])) ?>">
                            <div class="form-text-modern"><i class="bi bi-search me-1"></i>Type to search existing locations or enter a new destination name.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label-modern d-block">Vehicle Types & Fares (PHP)</label>
                            <div class="form-text-modern mb-3"><i class="bi bi-info-circle me-1"></i>Enter fares for all vehicle types serving this route. Leave blank for types that do not serve this route:</div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="p-3 border rounded-3" style="background: var(--surface-sunken, #f8fafc); border-color: var(--border, #e2e8f0) !important;">
                                        <div class="fw-bold mb-2" style="color: var(--text-main); font-size: 15px;">🚐 Van</div>
                                        <div class="input-group-modern">
                                            <span class="input-group-text-modern">₱</span>
                                            <input type="number" step="0.01" min="1" class="input-modern" name="fares[van]"
                                                   value="<?= old('fares.van', old('fare')) ?>" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 border rounded-3" style="background: var(--surface-sunken, #f8fafc); border-color: var(--border, #e2e8f0) !important;">
                                        <div class="fw-bold mb-2" style="color: var(--text-main); font-size: 15px;">🚌 Jeepney</div>
                                        <div class="input-group-modern">
                                            <span class="input-group-text-modern">₱</span>
                                            <input type="number" step="0.01" min="1" class="input-modern" name="fares[jeepney]"
                                                   value="<?= old('fares.jeepney') ?>" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 border rounded-3" style="background: var(--surface-sunken, #f8fafc); border-color: var(--border, #e2e8f0) !important;">
                                        <div class="fw-bold mb-2" style="color: var(--text-main); font-size: 15px;">🚍 Mini Bus</div>
                                        <div class="input-group-modern">
                                            <span class="input-group-text-modern">₱</span>
                                            <input type="number" step="0.01" min="1" class="input-modern" name="fares[minibus]"
                                                   value="<?= old('fares.minibus') ?>" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                                <?php foreach (($vehicleTypes ?? []) as $vehicleType): ?>
                                    <?php if (in_array($vehicleType['slug'], ['van', 'jeepney', 'minibus'], true)) continue; ?>
                                    <div class="col-md-4">
                                        <div class="p-3 border rounded-3" style="background: var(--surface-sunken, #f8fafc); border-color: var(--border, #e2e8f0) !important;">
                                            <div class="fw-bold mb-2" style="color: var(--text-main); font-size: 15px;"><i class="bi bi-truck me-1"></i><?= esc($vehicleType['name']) ?></div>
                                            <div class="input-group-modern">
                                                <span class="input-group-text-modern">₱</span>
                                                <input type="number" step="0.01" min="1" class="input-modern" name="fares[<?= esc($vehicleType['slug']) ?>]"
                                                       value="<?= old('fares.' . $vehicleType['slug']) ?>" placeholder="0.00">
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-5">
                            <button type="submit" class="btn btn-modern btn-modern-primary">
                                <i class="bi bi-save"></i> Save Route
                            </button>
                            <a href="<?= base_url('admin/routes') ?>" class="btn btn-modern btn-modern-outline">
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
