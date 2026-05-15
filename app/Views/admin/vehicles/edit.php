<?= view('templates/header', ['title' => $title]) ?>

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-pencil-square text-primary me-2"></i> Edit Vehicle</h1>
    <a href="<?= base_url('admin/vehicles') ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Vehicles
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- Edit Vehicle Form -->
<div class="card shadow">
    <div class="card-header">
        <i class="bi bi-pencil-fill"></i> Update Vehicle Details
    </div>
    <div class="card-body">
        <form action="<?= base_url('admin/vehicles/update/' . $vehicle['id']) ?>" method="post">
            <?= csrf_field() ?>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="plate_number" class="form-label">Plate Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="plate_number" name="plate_number" placeholder="e.g. ABC-1234" value="<?= old('plate_number') ?? esc($vehicle['plate_number']) ?>" required>
                    <small class="form-text text-muted">Unique identifier (required)</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="driver_name" class="form-label">Driver Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="driver_name" name="driver_name" placeholder="e.g. Juan Dela Cruz" value="<?= old('driver_name') ?? esc($vehicle['driver_name']) ?>" required>
                    <small class="form-text text-muted">Driver's full name</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="type" class="form-label">Vehicle Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="type" name="type" required>
                        <option value="">-- Select Type --</option>
                        <option value="jeepney" <?= (old('type') ?? $vehicle['type']) == 'jeepney' ? 'selected' : '' ?>>Jeepney</option>
                        <option value="van" <?= (old('type') ?? $vehicle['type']) == 'van' ? 'selected' : '' ?>>Van</option>
                        <option value="minibus" <?= (old('type') ?? $vehicle['type']) == 'minibus' ? 'selected' : '' ?>>Minibus</option>
                    </select>
                    <small class="form-text text-muted">Accessible to Admin and Staff</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="capacity" class="form-label">Passenger Capacity <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="capacity" name="capacity" placeholder="e.g. 16" value="<?= old('capacity') ?? esc($vehicle['capacity']) ?>" min="1" required>
                    <small class="form-text text-muted">Maximum passengers</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="active" <?= (old('status') ?? $vehicle['status']) == 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="maintenance" <?= (old('status') ?? $vehicle['status']) == 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                    </select>
                    <small class="form-text text-muted">Accessible to Admin and Staff</small>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Update Vehicle
                    </button>
                    <a href="<?= base_url('admin/vehicles') ?>" class="btn btn-secondary">
                        <i class="bi bi-x-lg"></i> Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card shadow mt-3">
    <div class="card-body">
        <p class="text-muted">
            <strong>Vehicle ID:</strong> <?= esc($vehicle['id']) ?><br>
            <strong>Registered:</strong> <?= date('M d, Y h:i A', strtotime($vehicle['created_at'])) ?>
        </p>
    </div>
</div>

<?= view('templates/footer') ?>
