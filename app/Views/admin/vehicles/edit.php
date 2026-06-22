<?= $this->include('templates/header') ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">

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

<div class="card-modern fade-in">
    <div class="card-header-modern">
        <span><i class="bi bi-pencil-fill"></i> Update Vehicle Details</span>
    </div>
    <div class="card-body-modern">
        <form action="<?= base_url('admin/vehicles/update/' . $vehicle['id']) ?>" method="post">
            <?= csrf_field() ?>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="plate_number" class="form-label-modern">Plate Number <span class="text-danger">*</span></label>
                    <input type="text" class="input-modern" id="plate_number" name="plate_number" placeholder="e.g. ABC-1234" value="<?= old('plate_number') ?? esc($vehicle['plate_number']) ?>" required>
                    <div class="form-text-modern">Unique identifier (required)</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="driver_name" class="form-label-modern">Driver Name <span class="text-danger">*</span></label>
                    <input type="text" class="input-modern" id="driver_name" name="driver_name" placeholder="e.g. Juan Dela Cruz" value="<?= old('driver_name') ?? esc($vehicle['driver_name']) ?>" required>
                    <div class="form-text-modern">Driver's full name</div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="type" class="form-label-modern">Vehicle Type <span class="text-danger">*</span></label>
                    <select class="select-modern" id="type" name="type" required>
                        <option value="">-- Select Type --</option>
                        <option value="jeepney" <?= (old('type') ?? $vehicle['type']) == 'jeepney' ? 'selected' : '' ?>>Jeepney</option>
                        <option value="van" <?= (old('type') ?? $vehicle['type']) == 'van' ? 'selected' : '' ?>>Van</option>
                        <option value="minibus" <?= (old('type') ?? $vehicle['type']) == 'minibus' ? 'selected' : '' ?>>Minibus</option>
                    </select>
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
                    <label for="route_id" class="form-label-modern">Assigned Route <span class="text-danger">*</span></label>
                    <select class="select-modern" id="route_id" name="route_id" required>
                        <option value="">-- Select Route --</option>
                        <?php if (!empty($routes)): ?>
                            <?php foreach ($routes as $r): ?>
                                <option value="<?= $r['id'] ?>" data-type="<?= esc($r['vehicle_type']) ?>"
                                    <?= (old('route_id') ?? $vehicle['route_id']) == $r['id'] ? 'selected' : '' ?>>
                                    <?= strtoupper(esc($r['origin'])) ?> &rarr; <?= strtoupper(esc($r['destination'])) ?> (<?= ucfirst(esc($r['vehicle_type'])) ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <div class="form-text-modern">Route must match vehicle type</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label-modern">Status <span class="text-danger">*</span></label>
                    <select class="select-modern" id="status" name="status" required>
                        <option value="active" <?= (old('status') ?? $vehicle['status']) == 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="maintenance" <?= (old('status') ?? $vehicle['status']) == 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                    </select>
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
            <strong>Registered:</strong> <?= strtoupper(date('M d, Y H:i', strtotime($vehicle['created_at']))) ?>
        </p>
    </div>
</div>

<script>
// Filter route dropdown based on selected vehicle type
document.addEventListener('DOMContentLoaded', function() {
    var typeSelect = document.getElementById('type');
    var routeSelect = document.getElementById('route_id');
    var currentRouteId = '<?= old('route_id') ?? $vehicle['route_id'] ?>';

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
});
</script>

<?= $this->include('templates/footer') ?>
