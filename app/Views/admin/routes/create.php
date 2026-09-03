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
            <i class="bi bi-plus-circle"></i> Add New Route / Fare
        </h1>
        <p class="text-muted mb-0">Create a new route and set initial fare details</p>
    </div>
    <div class="mt-3 mt-md-0">
        <a href="<?= base_url('admin/routes') ?>" class="btn-modern btn-modern-outline">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
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
                                <?php foreach (($vehicleTypes ?? []) as $vehicleType): ?>
                                    <?php
                                        $vtSlug = $vehicleType['slug'];
                                        $vtCol  = !empty($vehicleType['color']) ? $vehicleType['color'] : vehicle_type_color($vtSlug);
                                        $vtIco  = !empty($vehicleType['icon']) ? $vehicleType['icon'] : vehicle_type_icon($vtSlug);
                                        $val    = old('fares.' . $vtSlug, ($vtSlug === 'van' ? old('fare') : ''));
                                    ?>
                                    <div class="col-12 col-sm-6 col-md-4">
                                        <div class="p-3 border rounded-3 h-100" style="background: var(--surface-sunken, #f8fafc); border-color: var(--border, #e2e8f0) !important; border-top: 3.5px solid <?= esc($vtCol) ?> !important;">
                                            <div class="fw-bold mb-2 d-flex align-items-center gap-2" style="color: <?= esc($vtCol) ?>; font-size: 15px;">
                                                <i class="fas <?= esc($vtIco) ?>"></i>
                                                <span style="color: var(--text-main);"><?= esc($vehicleType['name']) ?></span>
                                            </div>
                                            <div class="input-group-modern">
                                                <span class="input-group-text-modern">₱</span>
                                                <input type="number" step="0.01" min="1" class="input-modern" name="fares[<?= esc($vtSlug) ?>]"
                                                       value="<?= esc($val) ?>" placeholder="0.00">
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-sm-row gap-2 mt-4 mt-md-5 form-actions-modern">
                            <button type="submit" class="btn-modern btn-modern-primary">
                                <i class="bi bi-save"></i> Save Route
                            </button>
                            <a href="<?= base_url('admin/routes') ?>" class="btn-modern btn-modern-outline">
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
