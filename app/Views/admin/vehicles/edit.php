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

/* Ensure dropdowns have a visible chevron arrow */
select.select-modern,
select.form-select-modern,
select.form-select {
    appearance: none !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    background-image: none !important;
    padding-right: 40px !important;
    cursor: pointer !important;
}

.dropdown-chevron-icon {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: #475569;
    font-size: 13px;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: center;
}

</style>

<div class="page-header-modern fade-in">
    <h1 class="page-title-modern">
        <i class="bi bi-pencil-square"></i> Edit Vehicle
    </h1>
    <a href="<?= base_url('admin/vehicles') ?>" class="btn-modern btn-modern-outline">
        <i class="bi bi-arrow-left"></i> Back to Vehicles
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert-modern alert-modern-success">
        <i class="bi bi-check-circle-fill alert-modern-icon"></i>
        <div><?= session()->getFlashdata('success') ?></div>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert-modern alert-modern-danger">
        <i class="bi bi-exclamation-triangle-fill alert-modern-icon"></i>
        <div><?= session()->getFlashdata('error') ?></div>
    </div>
<?php endif; ?>

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

<?= $this->include('partials/flash_notices') ?>

<div class="card-modern fade-in">
    <div class="card-header-modern">
        <span class="card-title-modern"><i class="bi bi-pencil-fill me-2"></i> Update Vehicle Details</span>
    </div>
    <div class="card-body-modern">
        <form action="<?= base_url('admin/vehicles/update/' . $vehicle['id']) ?>" method="post" enctype="multipart/form-data" data-no-change-guard id="editVehicleForm">
            <?= csrf_field() ?>
            
            <!-- Vehicle Photo / Emblem Section -->
            <?php
            $vehPhoto = $vehicle['photo'] ?? null;
            $vehPhotoUrl = !empty($vehPhoto) ? media_url($vehPhoto) : '';
            $hasInitialPhoto = !empty($vehPhotoUrl);
            ?>
            <div class="mb-4 p-3 rounded-3" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label-modern m-0">
                        <i class="bi bi-camera me-1"></i> Vehicle Photo / Icon <small class="text-muted fw-normal">(Optional)</small>
                    </label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="checkbox" name="remove_photo" id="remove_photo" value="1" style="display: none;">
                        <button type="button" id="edit_veh_photo_clear" class="veh-photo-clear-btn <?= $hasInitialPhoto ? 'has-photo' : '' ?>" onclick="handleEditVehPhotoClear()">
                            <i class="fas fa-times me-1"></i> Clear Photo
                        </button>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 border flex-shrink-0 shadow-sm position-relative" style="width: 72px; height: 72px; overflow: hidden; border-color: #cbd5e1 !important; background: #ffffff;">
                        <img id="edit_veh_photo_preview" src="<?= esc($vehPhotoUrl) ?>" alt="Vehicle Photo" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; z-index: 2; <?= empty($vehPhotoUrl) ? 'display: none !important;' : 'display: block !important;' ?>">
                        <div id="edit_veh_type_fallback" class="veh-fallback-box" style="position: absolute; inset: 0; width: 100%; height: 100%; display: <?= empty($vehPhotoUrl) ? 'flex' : 'none' ?> !important; align-items: center; justify-content: center; background: #f1f5f9; z-index: 1;">
                            <i id="edit_veh_type_icon" class="fas fa-bus-simple text-muted" style="font-size: 28px;"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <input type="file" id="edit_veh_photo_input" name="photo" accept="image/png,image/jpeg,image/jpg,image/webp" class="form-control form-control-sm" style="height: 38px; border-radius: 8px; font-size: 13px;" onchange="previewEditVehPhoto(this)">
                        <div class="form-text mt-1 text-muted" style="font-size: 11.5px;">
                            Supports JPG, PNG, WEBP &bull; Max 5MB &bull; Falls back to vehicle type emblem if empty.
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3 plate-col-wrapper">
                    <label for="plate_number" class="form-label-modern">Plate Number <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <input type="text" class="input-modern text-uppercase" id="plate_number" name="plate_number" placeholder="E.G. ABC-1234" value="<?= strtoupper((string)(old('plate_number') ?? esc($vehicle['plate_number']))) ?>" style="text-transform: uppercase; padding-right: 58px;" oninput="this.value = this.value.toUpperCase(); validatePlateRealtime(this.value); toggleFieldClearBtn(this, 'plate_number_clear');" required>
                        <button type="button" class="input-clear-btn" id="plate_number_clear" onclick="clearVehicleInput('plate_number')" title="Clear" aria-label="Clear plate number">
                            <i class="fas fa-times"></i>
                        </button>
                        <span id="plate-validation-icon" class="plate-validation-icon" style="display: none;"></span>
                    </div>
                    <div id="plate-validation-feedback" class="plate-validation-feedback" style="display: none;"></div>
                    <div class="form-text-modern">Unique identifier (required)</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="operator_name" class="form-label-modern">Operator Name <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <input type="text" class="input-modern text-uppercase" id="operator_name" name="operator_name" placeholder="E.G. LETRANSCO" value="<?= strtoupper((string)(old('operator_name') ?? esc($vehicle['operator_name'] ?? ''))) ?>" style="text-transform: uppercase; padding-right: 36px;" oninput="this.value = this.value.toUpperCase(); toggleFieldClearBtn(this, 'operator_name_clear');" required>
                        <button type="button" class="input-clear-btn" id="operator_name_clear" onclick="clearVehicleInput('operator_name')" title="Clear" aria-label="Clear operator name">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="form-text-modern">Operator's full name</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="driver_name" class="form-label-modern">Driver Name <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <input type="text" class="input-modern" id="driver_name" name="driver_name" placeholder="e.g. Pedro Santos" value="<?= old('driver_name') ?? esc($vehicle['driver_name']) ?>" style="padding-right: 36px;" oninput="toggleFieldClearBtn(this, 'driver_name_clear');" required>
                        <button type="button" class="input-clear-btn" id="driver_name_clear" onclick="clearVehicleInput('driver_name')" title="Clear" aria-label="Clear driver name">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="form-text-modern">Driver's full name</div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="type" class="form-label-modern">Vehicle Type <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <select class="form-select select-modern pe-5" id="type" name="type" required onchange="updateEditTypeFallback(this)">
                            <option value="">-- Select Type --</option>
                            <?php foreach (($vehicleTypes ?? []) as $vehicleType): ?>
                                <?php 
                                    $optCol = !empty($vehicleType['color']) ? $vehicleType['color'] : vehicle_type_color($vehicleType['slug']); 
                                    $optPhoto = !empty($vehicleType['photo']) ? media_url($vehicleType['photo']) : '';
                                    $optIcon = !empty($vehicleType['icon']) ? $vehicleType['icon'] : vehicle_type_icon($vehicleType['slug']);
                                ?>
                                <option value="<?= esc($vehicleType['slug']) ?>" data-color="<?= esc($optCol) ?>" data-photo="<?= esc($optPhoto) ?>" data-icon="<?= esc($optIcon) ?>" <?= (old('type') ?? $vehicle['type']) === $vehicleType['slug'] ? 'selected' : '' ?>><?= esc($vehicleType['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-text-modern">Accessible to Admin and Super Admin</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="capacity" class="form-label-modern">Passenger Capacity <span class="text-danger">*</span></label>
                    <input type="number" class="input-modern" id="capacity" name="capacity" placeholder="e.g. 16" value="<?= old('capacity') ?? esc($vehicle['capacity']) ?>" min="1" required>
                    <div class="form-text-modern">Maximum passengers</div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="route_id" class="form-label-modern">Destination <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <select class="form-select select-modern" id="route_id" name="route_id" required>
                            <option value="">-- Select Destination --</option>
                            <?php if (!empty($routes)): ?>
                                <?php foreach ($routes as $r): ?>
                                    <option value="<?= $r['id'] ?>" data-type="<?= esc($r['vehicle_type']) ?>"
                                        <?= (old('route_id') ?? $vehicle['default_route_id'] ?? $vehicle['route_id'] ?? '') == $r['id'] ? 'selected' : '' ?>>
                                        <?= strtoupper(esc($r['origin'])) ?> &rarr; <?= strtoupper(esc($r['destination'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-text-modern">Filtered by selected vehicle type</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label-modern">Status <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <select class="form-select select-modern" id="status" name="status" required>
                            <option value="active" <?= (old('status') ?? $vehicle['status']) == 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="maintenance" <?= (old('status') ?? $vehicle['status']) == 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                        </select>
                    </div>
                    <div class="form-text-modern">Accessible to Admin and Super Admin</div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn-modern btn-modern-primary">
                    <i class="bi bi-check-lg"></i> Update Vehicle
                </button>
                <a href="<?= base_url('admin/vehicles') ?>" class="btn-modern btn-modern-outline">
                    <i class="bi bi-x-lg"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card-modern fade-in mt-3">
    <div class="card-body-modern">
        <p class="text-muted mb-0">
            <strong>Vehicle ID:</strong> <?= esc($vehicle['id']) ?><br>
            <strong>Registered:</strong> <?= !empty($vehicle['created_at']) ? strtoupper(date('M d, Y H:i', strtotime($vehicle['created_at']))) : 'N/A' ?>
        </p>
    </div>
</div>

<script>
// Filter route dropdown based on selected vehicle type
document.addEventListener('DOMContentLoaded', function() {
    var typeSelect = document.getElementById('type');
    var routeSelect = document.getElementById('route_id');
    var currentRouteId = '<?= old('route_id') ?? $vehicle['default_route_id'] ?? $vehicle['route_id'] ?? '' ?>';

    function filterRoutes() {
        var selectedType = typeSelect.value;
        var options = routeSelect.querySelectorAll('option[data-type]');
        var hasSelected = false;

        options.forEach(function(opt) {
            if (opt.getAttribute('data-type') === selectedType) {
                opt.style.display = '';
                opt.disabled = false;
                if (opt.value === currentRouteId) {
                    opt.selected = true;
                    hasSelected = true;
                }
            } else {
                opt.style.display = 'none';
                opt.disabled = true;
                if (opt.selected) opt.selected = false;
            }
        });

        if (!hasSelected) {
            routeSelect.value = '';
        }
    }

    filterRoutes();
    typeSelect.addEventListener('change', function() {
        currentRouteId = '';
        filterRoutes();
    });

    // Initialize fallback emblem if vehicle has no photo
    <?php if (empty($vehPhotoUrl)): ?>
    if (typeSelect) {
        updateEditTypeFallback(typeSelect);
    }
    <?php endif; ?>
});

function previewEditVehPhoto(input) {
    var preview = document.getElementById('edit_veh_photo_preview');
    var fallback = document.getElementById('edit_veh_type_fallback');
    var clearBtn = document.getElementById('edit_veh_photo_clear');
    var removeCheckbox = document.getElementById('remove_photo');
    if (removeCheckbox) removeCheckbox.checked = false;

    if (input.files && input.files[0]) {
        var file = input.files[0];
        if (file.size > 5 * 1024 * 1024) {
            input.value = '';
            handleEditVehPhotoClear();
            if (typeof window.showSystemAlert === 'function') {
                window.showSystemAlert({
                    title: 'File Too Large',
                    message: 'The selected vehicle photo is ' + (file.size / (1024 * 1024)).toFixed(1) + 'MB. The maximum allowed size is 5MB. Please choose a smaller image.',
                    variant: 'danger'
                });
            }
            return;
        }

        var reader = new FileReader();
        reader.onload = function(e) {
            if (preview) {
                preview.src = e.target.result;
                preview.style.setProperty('display', 'block', 'important');
            }
            if (fallback) fallback.style.setProperty('display', 'none', 'important');
            if (clearBtn) {
                clearBtn.classList.add('has-photo');
            }
        };
        reader.readAsDataURL(file);
    }
}

function handleEditVehPhotoClear() {
    var input = document.getElementById('edit_veh_photo_input');
    var preview = document.getElementById('edit_veh_photo_preview');
    var fallback = document.getElementById('edit_veh_type_fallback');
    var clearBtn = document.getElementById('edit_veh_photo_clear');
    var removeCheckbox = document.getElementById('remove_photo');

    if (input) input.value = '';
    if (preview) {
        preview.src = '';
        preview.style.setProperty('display', 'none', 'important');
    }
    if (fallback) fallback.style.setProperty('display', 'flex', 'important');
    if (removeCheckbox) removeCheckbox.checked = true;
    if (clearBtn) {
        clearBtn.classList.remove('has-photo');
    }
    var typeSelect = document.getElementById('type');
    if (typeSelect) updateEditTypeFallback(typeSelect);
}

function clearEditVehPhoto() {
    handleEditVehPhotoClear();
}

function toggleRemoveEditVehPhoto(isChecked) {
    if (isChecked) {
        handleEditVehPhotoClear();
    } else {
        var originalUrl = '<?= !empty($vehPhotoUrl) ? esc($vehPhotoUrl) : '' ?>';
        var preview = document.getElementById('edit_veh_photo_preview');
        var fallback = document.getElementById('edit_veh_type_fallback');
        var clearBtn = document.getElementById('edit_veh_photo_clear');
        var removeCheckbox = document.getElementById('remove_photo');
        if (removeCheckbox) removeCheckbox.checked = false;
        if (originalUrl && preview) {
            preview.src = originalUrl;
            preview.style.setProperty('display', 'block', 'important');
            if (fallback) fallback.style.setProperty('display', 'none', 'important');
            if (clearBtn) clearBtn.classList.add('has-photo');
        }
    }
}

function updateEditTypeFallback(select) {
    var preview = document.getElementById('edit_veh_photo_preview');
    if (preview && preview.style.display === 'block' && preview.src && preview.src.length > 0) return;

    var fallback = document.getElementById('edit_veh_type_fallback');
    if (!fallback) return;
    var opt = select.options[select.selectedIndex];
    if (!opt || !opt.value) {
        fallback.innerHTML = '<i class="fas fa-bus-simple text-muted" style="font-size: 28px;"></i>';
        return;
    }

    var photo = opt.getAttribute('data-photo');
    var icon = opt.getAttribute('data-icon') || 'fa-bus-simple';
    var color = opt.getAttribute('data-color') || '#475569';

    if (photo) {
        fallback.innerHTML = '<img src="' + photo + '" alt="Emblem" style="width:100%;height:100%;object-fit:cover;">';
    } else {
        fallback.innerHTML = '<i class="fas ' + icon + '" style="font-size:28px;color:' + color + ';"></i>';
    }
}
</script>

<!-- Real-time Plate Number Validation -->
<style>
    .card-modern:has(.plate-col-wrapper) {
        position: relative !important;
        z-index: 10 !important;
        overflow: visible !important;
    }
    body.modal-open .card-modern:has(.plate-col-wrapper) {
        z-index: auto !important;
    }
    .plate-validation-icon {
        position: absolute;
        right: 38px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 16px;
        pointer-events: none;
        transition: opacity 0.2s ease;
    }
    .plate-col-wrapper {
        position: relative;
    }
    .plate-validation-feedback {
        position: relative !important;
        top: auto !important;
        left: auto !important;
        right: auto !important;
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin-top: 6px !important;
        margin-bottom: 4px !important;
        box-sizing: border-box !important;
        font-size: 11.5px;
        padding: 6px 12px;
        line-height: 1.35;
        transition: opacity 0.2s ease, transform 0.2s ease;
        font-weight: 500;
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-radius: 8px;
        border: 1px solid rgba(0, 0, 0, 0.08);
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.10);
        pointer-events: auto;
    }
    .plate-validation-feedback.feedback-error {
        background: rgba(255, 245, 245, 0.94);
        border-color: rgba(220, 53, 69, 0.25);
        color: #dc3545;
        box-shadow: 0 4px 14px rgba(220, 53, 69, 0.12);
    }
    .plate-validation-feedback.feedback-success {
        background: rgba(240, 255, 244, 0.94);
        border-color: rgba(25, 135, 84, 0.25);
        color: #198754;
        box-shadow: 0 4px 14px rgba(25, 135, 84, 0.12);
    }
    .plate-validation-feedback.feedback-checking {
        background: rgba(248, 249, 250, 0.94);
        border-color: rgba(108, 117, 125, 0.2);
        color: #6c757d;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
    }
    @media (max-width: 1199.98px) {
        .plate-validation-feedback {
            position: relative !important;
            top: auto !important;
            left: auto !important;
            right: auto !important;
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            margin-top: 6px !important;
            margin-bottom: 2px !important;
            box-sizing: border-box !important;
            pointer-events: auto !important;
        }
    }
    .input-modern.plate-valid {
        border-color: #198754 !important;
        box-shadow: 0 0 0 2px rgba(25, 135, 84, 0.15) !important;
    }
    .input-modern.plate-invalid {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.15) !important;
    }
    @keyframes spin-plate {
        to { transform: translateY(-50%) rotate(360deg); }
    }
    .plate-validation-icon.spinning {
        animation: spin-plate 0.8s linear infinite;
    }
</style>

<script>
(function() {
    var plateCheckTimer = null;
    var plateCheckXhr = null;
    var plateIsValid = true;
    var baseUrl = '<?= base_url('admin/vehicles/check-plate') ?>';
    var excludeId = <?= (int)$vehicle['id'] ?>;
    var originalPlate = '<?= strtoupper(esc($vehicle['plate_number'])) ?>';

    window.validatePlateRealtime = function(value) {
        var plate = value.trim().toUpperCase();
        var input = document.getElementById('plate_number');
        var icon = document.getElementById('plate-validation-icon');
        var feedback = document.getElementById('plate-validation-feedback');

        if (plateCheckTimer) clearTimeout(plateCheckTimer);
        if (plateCheckXhr) { plateCheckXhr.abort(); plateCheckXhr = null; }

        input.classList.remove('plate-valid', 'plate-invalid');
        icon.style.display = 'none';
        icon.className = 'plate-validation-icon';

        if (plate === '') {
            feedback.style.display = 'none';
            plateIsValid = true;
            return;
        }

        // If the plate hasn't changed from original, it's valid
        if (plate === originalPlate) {
            feedback.style.display = 'none';
            input.classList.remove('plate-invalid');
            plateIsValid = true;
            return;
        }

        if (plate.length < 5) {
            feedback.style.display = 'block';
            feedback.className = 'plate-validation-feedback feedback-error';
            feedback.innerHTML = '<i class="bi bi-info-circle me-1"></i>Must be at least 5 characters';
            input.classList.add('plate-invalid');
            icon.style.display = 'inline-block';
            icon.innerHTML = '<i class="bi bi-exclamation-circle" style="color: #dc3545;"></i>';
            plateIsValid = false;
            return;
        }

        if (plate.length > 20) {
            feedback.style.display = 'block';
            feedback.className = 'plate-validation-feedback feedback-error';
            feedback.innerHTML = '<i class="bi bi-info-circle me-1"></i>Cannot exceed 20 characters';
            input.classList.add('plate-invalid');
            icon.style.display = 'inline-block';
            icon.innerHTML = '<i class="bi bi-exclamation-circle" style="color: #dc3545;"></i>';
            plateIsValid = false;
            return;
        }

        feedback.style.display = 'block';
        feedback.className = 'plate-validation-feedback feedback-checking';
        feedback.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Checking availability...';
        icon.style.display = 'inline-block';
        icon.innerHTML = '<i class="bi bi-arrow-repeat" style="color: #6c757d;"></i>';
        icon.classList.add('spinning');

        plateCheckTimer = setTimeout(function() {
            var url = baseUrl + '?plate=' + encodeURIComponent(plate) + '&exclude_id=' + excludeId;

            plateCheckXhr = new XMLHttpRequest();
            plateCheckXhr.open('GET', url, true);
            plateCheckXhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            plateCheckXhr.onreadystatechange = function() {
                if (plateCheckXhr.readyState !== 4) return;
                icon.classList.remove('spinning');

                if (plateCheckXhr.status === 200) {
                    try {
                        var data = JSON.parse(plateCheckXhr.responseText);
                        if (data.available) {
                            feedback.className = 'plate-validation-feedback feedback-success';
                            feedback.innerHTML = '<i class="bi bi-check-circle me-1"></i>Plate number is available';
                            input.classList.remove('plate-invalid');
                            input.classList.add('plate-valid');
                            icon.innerHTML = '<i class="bi bi-check-circle-fill" style="color: #198754;"></i>';
                            plateIsValid = true;
                        } else {
                            feedback.className = 'plate-validation-feedback feedback-error';
                            feedback.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>' + (data.message || 'Plate number is not available');
                            input.classList.remove('plate-valid');
                            input.classList.add('plate-invalid');
                            icon.innerHTML = '<i class="bi bi-exclamation-circle-fill" style="color: #dc3545;"></i>';
                            plateIsValid = false;
                        }
                    } catch(e) {
                        feedback.style.display = 'none';
                        plateIsValid = true;
                    }
                } else {
                    feedback.style.display = 'none';
                    input.classList.remove('plate-valid', 'plate-invalid');
                    icon.style.display = 'none';
                    plateIsValid = true;
                }
            };
            plateCheckXhr.send();
        }, 400);
    };

    document.addEventListener('DOMContentLoaded', function() {
        var form = document.getElementById('editVehicleForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!plateIsValid) {
                    e.preventDefault();
                    var feedback = document.getElementById('plate-validation-feedback');
                    var input = document.getElementById('plate_number');
                    if (feedback) {
                        feedback.style.display = 'block';
                        feedback.className = 'plate-validation-feedback feedback-error';
                        feedback.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>Please fix the plate number before submitting';
                    }
                    if (input) {
                        input.focus();
                        input.classList.add('plate-invalid');
                    }
                    return false;
                }
            });
        }

        if (typeof window.initVehicleInputClearBtns === 'function') {
            window.initVehicleInputClearBtns(document);
        }
    });

    window.toggleFieldClearBtn = function(input, btnId) {
        var btn = document.getElementById(btnId);
        if (!btn) return;
        if (input && input.value && input.value.trim().length > 0) {
            btn.style.setProperty('display', 'inline-flex', 'important');
        } else {
            btn.style.setProperty('display', 'none', 'important');
        }
    };

    window.clearVehicleInput = function(inputId) {
        var input = document.getElementById(inputId);
        if (!input) return;
        input.value = '';
        var clearBtn = document.getElementById(inputId + '_clear');
        if (clearBtn) {
            clearBtn.style.setProperty('display', 'none', 'important');
        }
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
        if (inputId === 'plate_number' && typeof validatePlateRealtime === 'function') {
            validatePlateRealtime('');
        }
        input.focus();
    };

    window.initVehicleInputClearBtns = function(container) {
        var root = container || document;
        var fields = ['plate_number', 'operator_name', 'driver_name'];
        fields.forEach(function(id) {
            var input = root.querySelector ? root.querySelector('#' + id) : document.getElementById(id);
            if (input) {
                window.toggleFieldClearBtn(input, id + '_clear');
                if (!input.dataset.clearBtnBound) {
                    input.dataset.clearBtnBound = '1';
                    input.addEventListener('input', function() {
                        window.toggleFieldClearBtn(this, id + '_clear');
                    });
                    input.addEventListener('change', function() {
                        window.toggleFieldClearBtn(this, id + '_clear');
                    });
                }
            }
        });
    };
})();
</script>

<?= $this->include('templates/footer') ?>
