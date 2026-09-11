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

/* ── Vehicle Type Checkbox Cards ── */
.vt-check-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    border: 1.5px solid var(--border, #e2e8f0);
    border-radius: 12px;
    background: var(--surface-sunken, #f8fafc);
    cursor: pointer;
    transition: border-color 0.2s ease, background-color 0.2s ease, box-shadow 0.2s ease;
    min-height: 52px;
}
.vt-check-card:hover {
    background: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.vt-check-card.checked {
    background: #fff;
    border-color: var(--vt-color, #1565c0);
    box-shadow: 0 0 0 2px color-mix(in srgb, var(--vt-color, #1565c0) 15%, transparent);
}
.vt-check-card .form-check-input {
    width: 20px;
    height: 20px;
    margin: 0;
    flex-shrink: 0;
    cursor: pointer;
}
.vt-check-card .form-check-input:checked {
    background-color: var(--vt-color, #1565c0);
    border-color: var(--vt-color, #1565c0);
}
.vt-check-card .vt-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    font-size: 14px;
    flex-shrink: 0;
}
.vt-check-card .vt-label {
    font-size: 14.5px;
    font-weight: 600;
    color: var(--text-main, #1e293b);
}
@media (max-width: 575.98px) {
    .vt-check-card {
        padding: 12px 14px;
        gap: 10px;
        min-height: 48px;
    }
    .vt-check-card .vt-icon {
        width: 28px;
        height: 28px;
        font-size: 13px;
    }
    .vt-check-card .vt-label {
        font-size: 14px;
    }
}
</style>

<div class="page-header-modern fade-in">
    <div>
        <h1 class="page-title-modern">
            <i class="bi bi-plus-circle"></i> Add New Route
        </h1>
        <p class="text-muted mb-0">Define a new route and assign vehicle types</p>
    </div>
    <div class="mt-3 mt-md-0">
        <a href="<?= base_url('admin/routes') ?>" class="btn-modern btn-modern-outline">
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

            <div class="card-modern">
                <div class="card-header-modern">
                    <span class="card-title-modern"><i class="bi bi-map me-2"></i> Route Information</span>
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
                            <label class="form-label-modern d-block">Vehicle Types <span class="text-danger">*</span></label>
                            <div class="form-text-modern mb-3"><i class="bi bi-info-circle me-1"></i>Select which vehicle types operate on this route. Fares can be assigned later in <strong>Fare Management</strong>.</div>
                            <div class="row g-3">
                                <?php foreach (($vehicleTypes ?? []) as $vehicleType): ?>
                                    <?php
                                        $vtSlug = $vehicleType['slug'];
                                        $vtCol  = !empty($vehicleType['color']) ? $vehicleType['color'] : vehicle_type_color($vtSlug);
                                        $vtIco  = !empty($vehicleType['icon']) ? $vehicleType['icon'] : vehicle_type_icon($vtSlug);
                                        $isChecked = (is_array(old('vehicle_types')) && in_array($vtSlug, old('vehicle_types')));
                                    ?>
                                    <div class="col-12 col-sm-6 col-md-4">
                                        <label class="vt-check-card <?= $isChecked ? 'checked' : '' ?>" style="--vt-color: <?= esc($vtCol) ?>; border-top: 3px solid <?= esc($vtCol) ?>;">
                                            <input type="checkbox" name="vehicle_types[]" value="<?= esc($vtSlug) ?>"
                                                   class="form-check-input mt-0 vt-checkbox" <?= $isChecked ? 'checked' : '' ?>>
                                            <span class="vt-icon" style="background: <?= esc($vtCol) ?>18; color: <?= esc($vtCol) ?>;">
                                                <i class="fas <?= esc($vtIco) ?>"></i>
                                            </span>
                                            <span class="vt-label"><?= esc($vehicleType['name']) ?></span>
                                        </label>
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
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        document.querySelectorAll('.vt-checkbox').forEach(function(cb) {
                            cb.addEventListener('change', function() {
                                var card = this.closest('.vt-check-card');
                                if (card) {
                                    card.classList.toggle('checked', this.checked);
                                }
                            });
                        });
                    });
                    </script>
                </div>
            </div>
        </div>
    </div>

<?= $this->include('templates/footer') ?>

