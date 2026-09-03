<?= $this->include('templates/header') ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">

<div class="page-header-modern fade-in">
    <h1 class="page-title-modern">
        <i class="bi bi-person-plus-fill"></i> Add New User
    </h1>
    <a href="<?= base_url('admin/users') ?>" class="btn-modern btn-modern-outline">
        <i class="bi bi-arrow-left"></i> Back to Users
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="card-modern fade-in">
            <div class="card-header-modern">
                <span><i class="bi bi-person-gear me-2"></i> User Details</span>
                <span class="badge-modern badge-modern-primary">New Account</span>
            </div>
            <div class="card-body-modern">
                <?php if (session()->has('errors')): ?>
                    <div class="alert-modern alert-modern-danger">
                        <i class="bi bi-exclamation-triangle-fill alert-modern-icon"></i>
                        <ul class="mb-0">
                            <?php foreach (session('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                <?php endif ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert-modern alert-modern-danger">
                        <i class="bi bi-exclamation-triangle-fill alert-modern-icon"></i>
                        <div><?= esc(session()->getFlashdata('error')) ?></div>
                    </div>
                <?php endif ?>

                <form action="<?= base_url('admin/users/store') ?>" method="post" novalidate>
                    <?= csrf_field() ?>

                    <div class="row">
                        <div class="col-12 col-lg-6 mb-3">
                            <label for="username" class="form-label-modern">Username <span class="text-muted small">(Gmail account)</span></label>
                            <div class="input-group-modern">
                                <span class="input-group-text-modern"><i class="bi bi-person-fill"></i></span>
                                <input type="email" class="input-modern" id="username" name="username" value="<?= old('username') ?>" required placeholder="e.g. jdoe@gmail.com">
                            </div>
                            <div class="form-text-modern">Used for signing in and password reset. Must be a valid Gmail account.</div>
                        </div>

                        <div class="col-12 col-lg-6 mb-3">
                            <label for="password" class="form-label-modern">Password</label>
                            <div class="input-group-modern">
                                <span class="input-group-text-modern"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" class="input-modern" id="password" name="password" required placeholder="Choose a strong password">
                                <button type="button" class="btn-modern btn-modern-outline password-toggle" id="togglePassword" title="Show or hide password" aria-label="Show or hide password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-text-modern">Minimum 6 characters required; 8 or more is recommended.</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-lg-6 mb-3">
                            <label for="full_name" class="form-label-modern">Full Name</label>
                            <div class="input-group-modern">
                                <span class="input-group-text-modern"><i class="bi bi-card-text"></i></span>
                                <input type="text" class="input-modern" id="full_name" name="full_name" value="<?= old('full_name') ?>" required placeholder="First Last">
                            </div>
                            <div class="form-text-modern">Display name shown in admin and staff records.</div>
                        </div>

                        <div class="col-12 col-lg-6 mb-3">
                            <label for="role" class="form-label-modern">Role</label>
                            <div class="input-group-modern">
                                <span class="input-group-text-modern"><i class="bi bi-shield-lock-fill"></i></span>
                                <select class="select-modern" id="role" name="role" required style="flex: 1 1 auto; width: 100%; min-width: 0;">
                                    <option value="staff" <?= old('role', 'staff') === 'staff' ? 'selected' : '' ?>>Dispatcher</option>
                                    <?php if (session()->get('role') === 'super_admin'): ?>
                                        <option value="admin" <?= old('role') === 'admin' ? 'selected' : '' ?>>Admin</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="form-text-modern">Admins can access all routes; dispatchers need assigned routes.</div>
                        </div>
                    </div>

                    <div class="mb-4" id="routeAssignmentSection">
                        <div class="route-section-header">
                            <div>
                                <label class="form-label-modern fw-semibold mb-1">Assigned Routes <span class="text-danger">*</span></label>
                                <p class="form-text-modern mt-0 mb-0">Select the routes this dispatcher can manage.</p>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mb-2 flex-column flex-sm-row route-filter">
                            <div class="input-group-modern input-group-sm flex-grow-1">
                                <span class="input-group-text-modern"><i class="bi bi-search"></i></span>
                                <input type="text" id="routeSearch" class="input-modern" placeholder="Search routes...">
                            </div>
                            <div class="form-check route-select-all">
                                <input class="form-check-input" type="checkbox" id="selectAllRoutes">
                                <label class="form-check-label small" for="selectAllRoutes">Select all visible</label>
                            </div>
                        </div>

                        <div class="route-picker border rounded p-3 bg-white">
                            <?php
                                $oldRoutesInput = old('route_ids') ?? [];
                                $selectedRouteIds = [];
                                foreach ((array) $oldRoutesInput as $routeValue) {
                                    foreach (explode(',', (string) $routeValue) as $routeId) {
                                        if ($routeId !== '') {
                                            $selectedRouteIds[] = (int) $routeId;
                                        }
                                    }
                                }
                                $selectedRouteIds = array_unique($selectedRouteIds);

                                $grouped = [];
                                foreach ($routes as $r) {
                                    $origin = strtoupper($r['origin']);
                                    $destination = strtoupper($r['destination']);
                                    $key = $origin . '|' . $destination;
                                    if (!isset($grouped[$key])) {
                                        $grouped[$key] = [
                                            'origin' => $origin,
                                            'destination' => $destination,
                                            'ids' => [],
                                        ];
                                    }
                                    $grouped[$key]['ids'][] = (int) $r['id'];
                                }
                            ?>
                            <?php if (!empty($grouped)): ?>
                                <?php foreach ($grouped as $key => $group): ?>
                                    <?php
                                        $val = implode(',', $group['ids']);
                                        $isChecked = !empty(array_intersect($group['ids'], $selectedRouteIds));
                                        $routeLabel = $group['origin'] . ' ' . $group['destination'];
                                        $routeIdSelector = preg_replace('/[^a-zA-Z0-9]/', '_', $key);
                                    ?>
                                    <div class="form-check route-item" data-label="<?= esc(strtolower($routeLabel), 'attr') ?>">
                                        <input class="form-check-input route-checkbox" type="checkbox" name="route_ids[]"
                                               value="<?= esc($val) ?>" id="route_<?= $routeIdSelector ?>"
                                               <?= $isChecked ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="route_<?= $routeIdSelector ?>">
                                            <span class="route-name"><?= esc($group['origin']) ?> &rarr; <?= esc($group['destination']) ?></span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted mb-0">No routes available. Create routes first.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-column flex-sm-row user-form-actions">
                        <button type="submit" class="btn-modern btn-modern-primary">
                            <i class="bi bi-check-lg"></i> Create User
                        </button>
                        <a href="<?= base_url('admin/users') ?>" class="btn-modern btn-modern-outline">
                            <i class="bi bi-x-lg"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    /* Button sizing (always applies) */
    .card-modern a.btn-modern-outline,
    .page-header-modern a.btn-modern-outline {
        padding: 10px 20px !important;
        font-size: 14px !important;
    }

    .user-form-heading .text-muted { font-size: 14px; }
    .user-form-card .card-header { padding: 1rem 1.25rem !important; }
    .user-form-card .badge { white-space: nowrap; }
    .user-form-card .input-group-text { min-width: 42px; justify-content: center; }
    .password-toggle { min-width: 44px; border-left: 1px solid #e2e8f0 !important; }
    .route-section-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 0.75rem; }
    .route-select-all { display: flex; align-items: center; min-height: 34px; margin: 0; padding-left: 1.75rem; white-space: nowrap; }
    .route-picker { max-height: 280px; overflow-y: auto; }
    .route-item { border-radius: 6px; margin-bottom: 0.25rem; padding: 0.55rem 0.65rem 0.55rem 2rem; }
    .route-item:last-child { margin-bottom: 0; }
    .route-item .form-check-input { margin-left: -1.35rem; }
    .route-name { font-weight: 600; color: var(--text-main, #1e293b); }
    .user-form-actions .btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.35rem; }
    @media (max-width: 767.98px) {
        .user-form-heading { align-items: flex-start !important; gap: 0.75rem; }
        .route-section-header { flex-direction: column; }
        .route-select-all { padding-left: 1.5rem; white-space: normal; }
        .route-item { padding-top: 0.5rem; padding-bottom: 0.5rem; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var roleSelect = document.getElementById('role');
    var routeSection = document.getElementById('routeAssignmentSection');
    var routeSearch = document.getElementById('routeSearch');
    var selectAll = document.getElementById('selectAllRoutes');
    var routeItems = Array.prototype.slice.call(document.querySelectorAll('.route-item'));
    var routeCheckboxes = Array.prototype.slice.call(document.querySelectorAll('.route-checkbox'));
    var togglePassword = document.getElementById('togglePassword');
    var passwordInput = document.getElementById('password');

    function visibleRouteItems() {
        return routeItems.filter(function(item) {
            return !item.classList.contains('d-none');
        });
    }

    function updateSelectAllState() {
        if (!selectAll) return;
        var visibleCheckboxes = visibleRouteItems().map(function(item) {
            return item.querySelector('.route-checkbox');
        }).filter(Boolean);
        var checkedCount = visibleCheckboxes.filter(function(cb) {
            return cb.checked;
        }).length;

        selectAll.checked = visibleCheckboxes.length > 0 && checkedCount === visibleCheckboxes.length;
        selectAll.indeterminate = checkedCount > 0 && checkedCount < visibleCheckboxes.length;
    }

    function toggleRouteSection() {
        if (!roleSelect || !routeSection) return;
        routeSection.style.display = roleSelect.value === 'staff' ? 'block' : 'none';
    }

    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            var type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('bi-eye');
            this.querySelector('i').classList.toggle('bi-eye-slash');
        });
    }

    if (routeSearch) {
        routeSearch.addEventListener('input', function() {
            var query = this.value.trim().toLowerCase();
            routeItems.forEach(function(item) {
                var label = item.getAttribute('data-label') || '';
                item.classList.toggle('d-none', label.indexOf(query) === -1);
            });
            updateSelectAllState();
        });
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            var checked = this.checked;
            visibleRouteItems().forEach(function(item) {
                var checkbox = item.querySelector('.route-checkbox');
                if (checkbox) checkbox.checked = checked;
            });
            updateSelectAllState();
        });
    }

    routeCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', updateSelectAllState);
    });

    if (roleSelect) roleSelect.addEventListener('change', toggleRouteSection);

    toggleRouteSection();
    updateSelectAllState();
});
</script>

<?= $this->include('templates/footer') ?>
