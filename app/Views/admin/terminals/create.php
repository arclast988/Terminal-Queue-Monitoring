<?= $this->include('templates/header') ?>

<div class="page-header-modern">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="page-title-modern">
                    <i class="bi bi-plus-circle"></i> Add New Terminal
                </h1>
                <p class="text-muted mb-0">Create a new terminal in the system</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="<?= base_url('admin/terminals') ?>" class="btn btn-modern btn-modern-outline">
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
                    <i class="bi bi-geo-alt me-2"></i> Terminal Information
                </div>
                <div class="card-body-modern">
                    <form action="<?= base_url('admin/terminals/store') ?>" method="post">
                        <?= csrf_field() ?>
                        
                        <div class="mb-4">
                            <label for="name" class="form-label-modern">Terminal Name <span class="text-danger">*</span></label>
                            <input type="text" class="input-modern" id="name" name="name" value="<?= old('name') ?>" placeholder="e.g. Cebu South Terminal" required>
                            <div class="form-text-modern">Enter the official name of the terminal.</div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="location" class="form-label-modern">Location <span class="text-danger">*</span></label>
                            <input type="text" class="input-modern" id="location" name="location" value="<?= old('location') ?>" placeholder="e.g. Cebu City, Cebu" required>
                            <div class="form-text-modern">Enter the city/town and province.</div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="capacity" class="form-label-modern">Capacity (Max Vehicles) <span class="text-danger">*</span></label>
                            <input type="number" class="input-modern" id="capacity" name="capacity" value="<?= old('capacity', 0) ?>" min="0" required>
                            <div class="form-text-modern">Maximum number of vehicles that can be stationed at this terminal.</div>
                        </div>
                        
                        <div class="d-flex gap-2 mt-5">
                            <button type="submit" class="btn btn-modern btn-modern-primary">
                                <i class="bi bi-save"></i> Save Terminal
                            </button>
                            <a href="<?= base_url('admin/terminals') ?>" class="btn btn-modern btn-modern-outline">
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
