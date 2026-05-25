<?= $this->include('templates/header') ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Add New User</h1>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-md-10">
        <div class="row g-4">
            <div class="col-12 col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <?php if (session()->has('errors')): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                <?php foreach (session('errors') as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach ?>
                                </ul>
                            </div>
                        <?php endif ?>

                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
                        <?php endif ?>

                        <form action="<?= base_url('admin/users/store') ?>" method="post" novalidate>
                            <?= csrf_field() ?>

                            <div class="mb-3">
                                <label for="username" class="form-label">Username <span class="text-muted small">(unique)</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" value="<?= old('username') ?>" required placeholder="e.g. jdoe">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" required placeholder="Choose a strong password">
                                    <button type="button" class="btn btn-outline-secondary" id="togglePassword" title="Show / hide password"><i class="bi bi-eye"></i></button>
                                </div>
                                <div class="form-text">Minimum 8 characters recommended.</div>
                            </div>

                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" value="<?= old('full_name') ?>" required placeholder="First Last">
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

                                <div class="d-flex gap-2 mb-2 flex-column flex-sm-row route-filter">
                                    <input type="text" id="routeSearch" class="form-control form-control-sm flex-grow-1" placeholder="Search routes...">
                                    <div class="form-check align-items-center ms-0 ms-sm-2 mt-2 mt-sm-0">
                                        <input class="form-check-input" type="checkbox" id="selectAllRoutes">
                                        <label class="form-check-label small" for="selectAllRoutes">Select all</label>
                                    </div>
                                </div>

                                <div class="border rounded p-3 bg-white" style="max-height: 280px; overflow-y: auto;">
                                    <?php
                                        $oldRoutes = old('route_ids') ?? [];
                                    ?>
                                    <?php if (!empty($routes)): ?>
                                        <?php foreach ($routes as $r): ?>
                                            <?php $labelText = strtoupper(esc($r['origin'])) . ' → ' . strtoupper(esc($r['destination'])) . ' (' . ucfirst(esc($r['vehicle_type'])) . ')'; ?>
                                            <div class="form-check mb-1 route-item py-2" data-label="<?= strtolower($labelText) ?>">
                                                <input class="form-check-input route-checkbox" type="checkbox" name="route_ids[]"
                                                       value="<?= $r['id'] ?>" id="route_<?= $r['id'] ?>"
                                                       <?= in_array($r['id'], $oldRoutes) ? 'checked' : '' ?>>
                                                <label class="form-check-label ms-2" for="route_<?= $r['id'] ?>">
                                                    <?= $labelText ?>
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

            <div class="col-12 col-lg-6">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="profile-avatar rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width:72px;height:72px;">
                                <i class="bi bi-person-fill text-white" style="font-size:28px"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">User Summary</h5>
                                <div class="text-muted small">Preview of selected options</div>
                            </div>
                        </div>

                        <ul class="list-group list-group-flush mb-3">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="small text-muted">Username</div>
                                    <div id="summaryUsername">-</div>
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="small text-muted">Full name</div>
                                    <div id="summaryFullname">-</div>
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="small text-muted">Role</div>
                                    <div id="summaryRole">-</div>
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="small text-muted">Assigned routes</div>
                                    <div id="summaryRoutes">0 selected</div>
                                </div>
                            </li>
                        </ul>

                        <div class="mt-auto">
                            <div class="small text-muted">Tips:</div>
                            <ul class="small text-muted mb-0">
                                <li>Use a strong password and keep it secure.</li>
                                <li>Select only routes the dispatcher should manage.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .profile-avatar { flex: 0 0 72px; }
    .route-item[style*="display: none"] { display: none !important; }

    /* Mobile adjustments */
    @media (max-width: 767.98px) {
        .profile-avatar { width:56px; height:56px; }
        .profile-avatar i { font-size:22px; }
        .route-filter { flex-direction: column !important; }
        .route-filter .form-check { margin-left: 0 !important; margin-top: .4rem; }
        .route-item { padding: .45rem; }
        .input-group .btn { min-width: 44px; }
        .card .card-body { padding: 1rem; }
    }

    @media (max-width: 575.98px) {
        .input-group .form-control { font-size: 0.98rem; }
        #routeSearch { font-size: 0.95rem; }
        .list-group-item .small { font-size: .85rem; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var roleSelect = document.getElementById('role');
    var routeSection = document.getElementById('routeAssignmentSection');
    var routeSearch = document.getElementById('routeSearch');
    var selectAll = document.getElementById('selectAllRoutes');
    var routeItems = document.querySelectorAll('.route-item') || [];
    var routeCheckboxes = document.querySelectorAll('.route-checkbox') || [];

    function toggleRouteSection() {
        if (!roleSelect || !routeSection) return;
        routeSection.style.display = roleSelect.value === 'staff' ? 'block' : 'none';
    }

    function updateSummary() {
        var usernameEl = document.getElementById('username');
        var fullnameEl = document.getElementById('full_name');
        document.getElementById('summaryUsername').textContent = (usernameEl && usernameEl.value) ? usernameEl.value : '-';
        document.getElementById('summaryFullname').textContent = (fullnameEl && fullnameEl.value) ? fullnameEl.value : '-';
        var roleText = roleSelect ? roleSelect.options[roleSelect.selectedIndex].text : '-';
        document.getElementById('summaryRole').textContent = roleText || '-';
        var selected = document.querySelectorAll('.route-checkbox:checked').length;
        document.getElementById('summaryRoutes').textContent = selected + ' selected';
    }

    // Password toggle
    var togglePassword = document.getElementById('togglePassword');
    var passwordInput = document.getElementById('password');
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            var type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('bi-eye');
            this.querySelector('i').classList.toggle('bi-eye-slash');
        });
    }

    // Route filter
    if (routeSearch) {
        routeSearch.addEventListener('input', function() {
            var q = this.value.trim().toLowerCase();
            routeItems.forEach(function(item) {
                var label = item.getAttribute('data-label') || '';
                if (label.indexOf(q) === -1) {
                    item.style.display = 'none';
                } else {
                    item.style.display = 'block';
                }
            });
        });
    }

    // Select all visible
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            var checked = this.checked;
            routeItems.forEach(function(item) {
                if (item.style.display !== 'none') {
                    var cb = item.querySelector('.route-checkbox');
                    if (cb) cb.checked = checked;
                }
            });
            updateSummary();
        });
    }

    // Update summary on input/change
    var usernameEl = document.getElementById('username');
    var fullnameEl = document.getElementById('full_name');
    if (usernameEl) usernameEl.addEventListener('input', updateSummary);
    if (fullnameEl) fullnameEl.addEventListener('input', updateSummary);
    if (roleSelect) roleSelect.addEventListener('change', function() { updateSummary(); toggleRouteSection(); });
    routeCheckboxes.forEach(function(cb) { cb.addEventListener('change', updateSummary); });

    // Initialize
    toggleRouteSection();
    updateSummary();
});
</script>

<?= $this->include('templates/footer') ?>
