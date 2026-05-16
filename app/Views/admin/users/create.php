<?= $this->include('templates/header') ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Add New User</h1>
</div>

<div class="row">
    <div class="col-md-8 col-lg-6">
        <div class="card">
            <div class="card-body">
                <?php if (session()->has('errors')): ?>
                    <div class="alert alert-danger">
                        <ul>
                        <?php foreach (session('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach ?>
                        </ul>
                    </div>
                <?php endif ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
                <?php endif ?>

                <form action="<?= base_url('admin/users/store') ?>" method="post">
                    <?= csrf_field() ?>
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= old('username') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?= old('full_name') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="staff" <?= old('role', 'staff') === 'staff' ? 'selected' : '' ?>>Dispatcher</option>
                            <option value="admin" <?= old('role') === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>

                    <!-- Route Assignment (only for Dispatcher) -->
                    <div class="mb-3" id="routeAssignmentSection">
                        <label class="form-label fw-semibold">Assigned Routes <span class="text-danger">*</span></label>
                        <p class="form-text text-muted mt-0 mb-2">Select the routes this dispatcher can manage.</p>
                        <div class="border rounded p-3" style="max-height: 250px; overflow-y: auto;">
                            <?php
                                $oldRoutes = old('route_ids') ?? [];
                            ?>
                            <?php if (!empty($routes)): ?>
                                <?php foreach ($routes as $r): ?>
                                    <div class="form-check mb-1">
                                        <input class="form-check-input" type="checkbox" name="route_ids[]"
                                               value="<?= $r['id'] ?>" id="route_<?= $r['id'] ?>"
                                               <?= in_array($r['id'], $oldRoutes) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="route_<?= $r['id'] ?>">
                                            <?= strtoupper(esc($r['origin'])) ?> → <?= strtoupper(esc($r['destination'])) ?>
                                            <small class="text-muted">(<?= ucfirst(esc($r['vehicle_type'])) ?>)</small>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted mb-0">No routes available. Create routes first.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Create User</button>
                        <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var roleSelect = document.getElementById('role');
    var routeSection = document.getElementById('routeAssignmentSection');

    function toggleRouteSection() {
        routeSection.style.display = roleSelect.value === 'staff' ? 'block' : 'none';
    }

    toggleRouteSection();
    roleSelect.addEventListener('change', toggleRouteSection);
});
</script>

<?= $this->include('templates/footer') ?>
