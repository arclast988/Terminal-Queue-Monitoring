<?= $this->include('templates/header') ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css?v=20260928_3') ?>">

<style>
/* Button sizing (always applies) */
.card-modern a.btn-modern-outline,
.page-header-modern a.btn-modern-outline {
    padding: 10px 20px !important;
    font-size: 14px !important;
}

</style>

<div class="page-header-modern fade-in">
    <h1 class="page-title-modern">
        <i class="bi bi-pencil-square"></i> Edit User
    </h1>
    <a href="<?= base_url('admin/users') ?>" class="btn-modern btn-modern-outline">
        <i class="bi bi-arrow-left"></i> Back to Users
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="card-modern fade-in">
            <div class="card-header-modern">
                <span class="card-title-modern"><i class="bi bi-person-gear me-2"></i> User Details</span>
                <span class="badge-modern badge-modern-primary">Editing Account</span>
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

                <?= $this->include('partials/flash_notices') ?>

                <form class="user-account-form" action="<?= base_url('admin/users/update/' . $user['id']) ?>" method="post" data-no-change-guard>
                    <?= csrf_field() ?>

                    <?php
                    $currentUserId = (int) session()->get('id');
                    $currentUserRole = session()->get('role');
                    $isTargetSuperAdmin = ($user['role'] === 'super_admin');
                    $canManageThisAvatar = false;
                    if ($isTargetSuperAdmin) {
                        $canManageThisAvatar = ($currentUserId === (int) $user['id']);
                    } elseif ($user['role'] === 'admin') {
                        $canManageThisAvatar = ($currentUserRole === 'super_admin' || $currentUserId === (int) $user['id']);
                    } else {
                        $canManageThisAvatar = ($currentUserRole === 'super_admin' || $currentUserRole === 'admin');
                    }

                    $defaultAvatarSvg = <<<SVG
<svg class="profile-identicon-svg" viewBox="0 0 64 64" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <circle cx="32" cy="32" r="31" fill="#F1F5F9" stroke="#CBD5E1" stroke-width="2" />
    <path d="M32 32.5a9.5 9.5 0 1 0 0-19 9.5 9.5 0 0 0 0 19zm0 5c-9.2 0-17 5.8-17 13.5 0 1 .8 1.8 1.8 1.8h30.4c1 0 1.8-.8 1.8-1.8 0-7.7-7.8-13.5-17-13.5z" fill="#475569" />
</svg>
SVG;
                    $avatarBg = $user['role'] === 'super_admin' ? '#B71C1C' : ($user['role'] === 'admin' ? '#dc2626' : '#15803d');
                    ?>

                    <!-- Profile Photo Section -->
                    <div class="user-avatar-panel">
                        <div id="editUserAvatarPreview" class="user-form-avatar-preview" style="border-color: <?= $avatarBg ?>;">
                            <?php if (!empty($user['profile_image'])): ?>
                                <img src="<?= base_url(esc($user['profile_image'])) ?>" alt="<?= esc($user['full_name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <?= $defaultAvatarSvg ?>
                            <?php endif; ?>
                        </div>
                        <div class="user-avatar-copy">
                            <div class="user-avatar-title">Profile Picture</div>
                            <?php if ($isTargetSuperAdmin && !$canManageThisAvatar): ?>
                                <div class="user-avatar-hint"><i class="bi bi-shield-lock-fill text-danger me-1"></i> Only the Super Admin can change their own profile picture.</div>
                            <?php else: ?>
                                <div class="user-avatar-hint">JPG, PNG, WEBP, or GIF image (max 4MB).</div>
                                <div class="user-avatar-actions">
                                    <input type="file" id="editAvatarFileInput" accept="image/jpeg,image/png,image/webp,image/gif" style="display: none;">
                                    <button type="button" class="btn-modern btn-modern-outline btn-modern-sm" onclick="document.getElementById('editAvatarFileInput').click();" id="btnEditUploadPhoto">
                                        <i class="bi bi-camera-fill"></i> Upload New Photo
                                    </button>
                                    <button type="button" class="btn-modern btn-modern-outline btn-modern-sm text-danger <?= empty($user['profile_image']) ? 'd-none' : '' ?>" id="btnEditRemovePhoto" onclick="removeEditUserAvatar();" style="<?= empty($user['profile_image']) ? 'display: none !important;' : '' ?>">
                                        <i class="bi bi-trash"></i> Remove Photo
                                    </button>
                                </div>
                                <span id="editAvatarFeedback" class="user-avatar-feedback"></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-lg-6 mb-3">
                            <label for="username" class="form-label-modern">Username <span class="text-muted small">(Gmail account)</span></label>
                            <div class="input-group-modern">
                                <span class="input-group-text-modern"><i class="bi bi-person-fill"></i></span>
                                <input type="email" class="input-modern" id="username" name="username" value="<?= old('username', $user['username']) ?>" required placeholder="e.g. jdoe@gmail.com">
                            </div>
                            <div class="form-text-modern">Used for signing in and password reset. Must be a valid Gmail account.</div>
                        </div>

                        <div class="col-12 col-lg-6 mb-3">
                            <label for="password" class="form-label-modern">Password</label>
                            <div class="input-group-modern">
                                <span class="input-group-text-modern"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" class="input-modern" id="password" name="password" minlength="8" placeholder="New password">
                                <button type="button" class="btn-modern btn-modern-outline password-toggle" id="togglePassword" title="Show or hide password" aria-label="Show or hide password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-text-modern">Leave blank to keep the current password, or enter at least 8 characters.</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-lg-6 mb-3">
                            <label for="full_name" class="form-label-modern">Full Name</label>
                            <div class="input-group-modern">
                                <span class="input-group-text-modern"><i class="bi bi-card-text"></i></span>
                                <input type="text" class="input-modern" id="full_name" name="full_name" value="<?= old('full_name', $user['full_name']) ?>" required placeholder="First Last">
                            </div>
                            <div class="form-text-modern">Display name shown in admin and staff records.</div>
                        </div>

                        <div class="col-12 col-lg-6 mb-3">
                            <label for="role" class="form-label-modern">Role</label>
                            <div class="input-group-modern">
                                <span class="input-group-text-modern"><i class="bi bi-shield-lock-fill"></i></span>
                                <?php if ($user['role'] === 'super_admin'): ?>
                                    <input type="text" class="input-modern" value="Super Admin" disabled>
                                    <input type="hidden" name="role" value="super_admin">
                                <?php elseif ((int)$user['id'] === (int)session()->get('id')): ?>
                                    <input type="text" class="input-modern" value="<?= $user['role'] === 'super_admin' ? 'Super Admin' : ($user['role'] === 'admin' ? 'Admin' : 'Dispatcher') ?>" disabled>
                                    <input type="hidden" name="role" value="<?= esc($user['role']) ?>">
                                <?php else: ?>
                                    <?php $selectedRole = old('role', in_array($user['role'], ['staff', 'operator'], true) ? 'staff' : $user['role']); ?>
                                    <select class="select-modern" id="role" name="role" required style="flex: 1 1 0%; width: 100%; min-width: 0;">
                                        <option value="staff" <?= $selectedRole === 'staff' ? 'selected' : '' ?>>Dispatcher</option>
                                        <?php if (session()->get('role') === 'super_admin'): ?>
                                            <option value="admin" <?= $selectedRole === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        <?php endif; ?>
                                    </select>
                                <?php endif; ?>
                            </div>
                            <div class="form-text-modern">Route access applies to dispatchers only; administrators do not perform queue operations.</div>
                        </div>
                    </div>

                    <div class="mb-4" id="routeAssignmentSection">
                        <div class="route-section-header">
                            <div>
                                <label class="form-label-modern fw-semibold mb-1">Assigned Routes <span class="text-danger">*</span></label>
                                <p class="form-text-modern mt-0 mb-0">Select the routes where this dispatcher can perform queue operations.</p>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mb-2 flex-column flex-sm-row route-filter">
                            <div class="input-group-modern input-group-sm flex-grow-1 position-relative">
                                <span class="input-group-text-modern"><i class="bi bi-search"></i></span>
                                <input type="text" id="routeSearch" class="input-modern pe-4" placeholder="Search routes...">
                                <button type="button" class="btn-clear-search" id="clearRouteSearch" onclick="clearRouteSearchPicker()" style="display: none !important; right: 8px;" title="Clear search">
                                    <i class="bi bi-x-circle-fill"></i>
                                </button>
                            </div>
                            <label class="route-select-all" for="selectAllRoutes">
                                <input class="form-check-input" type="checkbox" id="selectAllRoutes">
                                <span>Select all visible</span>
                            </label>
                        </div>

                        <div class="route-picker border rounded p-3 bg-white">
                            <?php
                                $oldRoutesInput = old('route_ids') ?? ($assignedRouteIds ?? []);
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
                                            <span class="route-name"><?= strtoupper(esc($group['origin'])) ?> &rarr; <?= strtoupper(esc($group['destination'])) ?></span>
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
                            <i class="bi bi-check-lg"></i> Update User
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
    .route-section-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 0.75rem; }
    .route-select-all { display: inline-flex; align-items: center; gap: 9px; flex: 0 0 auto; min-height: 44px; margin: 0; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 10px; background: #fff; color: #334155; font-size: 14px; font-weight: 600; line-height: 1.3; white-space: nowrap; cursor: pointer; }
    .route-select-all .form-check-input { float: none !important; width: 22px; height: 22px; min-width: 22px; margin: 0 !important; flex: 0 0 22px; border: 2px solid #94a3b8; border-radius: 5px; cursor: pointer; }
    .route-select-all .form-check-input:checked,
    .route-select-all .form-check-input:indeterminate { background-color: #2563eb; border-color: #2563eb; }
    .route-select-all:focus-within { outline: 2px solid #2563eb; outline-offset: 2px; }
    .route-picker { max-height: 280px; overflow-y: auto; overscroll-behavior: contain; }
    .route-item { border-radius: 6px; margin-bottom: 0.25rem; padding: 0.55rem 0.65rem 0.55rem 2rem; }
    .route-item:last-child { margin-bottom: 0; }
    .route-item .form-check-input { width: 20px; height: 20px; margin-left: -1.35rem; border: 2px solid #94a3b8; }
    .route-name { font-weight: 600; color: var(--text-main, #1e293b); }
    .user-form-actions .btn-modern { display: inline-flex; align-items: center; justify-content: center; gap: 0.35rem; }
    @media (max-width: 767.98px) {
        .route-section-header { flex-direction: column; }
        .route-select-all { align-self: flex-start; max-width: 100%; white-space: normal; }
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
        if (!routeSection) return;
        var currentRole = roleSelect ? roleSelect.value : (document.querySelector('input[name="role"]') || {}).value || '';
        var isDispatcher = currentRole === 'staff' || currentRole === 'operator';
        routeSection.style.display = isDispatcher ? 'block' : 'none';
        routeCheckboxes.forEach(function(checkbox) { checkbox.disabled = !isDispatcher; });
        if (selectAll) selectAll.disabled = !isDispatcher;
        if (routeSearch) routeSearch.disabled = !isDispatcher;
    }

    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            var type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('bi-eye');
            this.querySelector('i').classList.toggle('bi-eye-slash');
        });
    }

    function toggleRouteSearchClear(val) {
        var btn = document.getElementById('clearRouteSearch');
        if (btn) {
            if (val && val.trim().length > 0) {
                btn.style.setProperty('display', 'inline-flex', 'important');
            } else {
                btn.style.setProperty('display', 'none', 'important');
            }
        }
    }
    window.clearRouteSearchPicker = function() {
        if (routeSearch) {
            routeSearch.value = '';
            toggleRouteSearchClear('');
            routeSearch.dispatchEvent(new Event('input'));
            routeSearch.focus();
        }
    };

    if (routeSearch) {
        toggleRouteSearchClear(routeSearch.value);
        routeSearch.addEventListener('input', function() {
            var query = this.value.trim().toLowerCase();
            toggleRouteSearchClear(this.value);
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

    // Profile Photo Upload & Remove Handlers
    function getEditUserCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function updateEditUserCsrfToken(data) {
        if (!data || !data.csrf_hash) return;
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) meta.setAttribute('content', data.csrf_hash);
    }

    var editAvatarInput = document.getElementById('editAvatarFileInput');
    if (editAvatarInput) {
        editAvatarInput.addEventListener('change', function() {
            var file = this.files[0];
            if (!file) return;
            if (file.size > 4 * 1024 * 1024) {
                if (typeof window.showSystemAlert === 'function') {
                    window.showSystemAlert({
                        title: 'File Too Large',
                        message: 'The selected avatar exceeds the 4MB maximum allowed size. Please select a smaller image.',
                        variant: 'warning'
                    });
                }
                this.value = '';
                return;
            }

            var formData = new FormData();
            formData.append('avatar', file);
            var csrfToken = getEditUserCsrfToken();
            if (csrfToken) formData.append('csrf_test_name', csrfToken);

            var feedback = document.getElementById('editAvatarFeedback');
            var uploadBtn = document.getElementById('btnEditUploadPhoto');
            if (feedback) {
                feedback.className = 'small text-primary ms-2';
                feedback.textContent = 'Uploading...';
            }
            if (uploadBtn) uploadBtn.disabled = true;

            fetch('<?= base_url('admin/users/upload-avatar/' . $user['id']) ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                updateEditUserCsrfToken(data);
                if (uploadBtn) uploadBtn.disabled = false;
                if (data.success) {
                    var preview = document.getElementById('editUserAvatarPreview');
                    if (preview) {
                        preview.innerHTML = '<img src="' + data.image_url + '" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">';
                    }
                    if (parseInt(<?= (int)$user['id'] ?>, 10) === <?= (int)(session()->get('id') ?? 0) ?>) {
                        if (typeof window.updateAvatarDom === 'function') {
                            window.updateAvatarDom(data.image_url);
                        } else {
                            var imgHtml = '<img src="' + data.image_url + '" alt="Avatar" class="profile-avatar-img user-avatar-preview">';
                            var tAvatar = document.getElementById('navProfileTriggerAvatar');
                            if (tAvatar) tAvatar.innerHTML = imgHtml;
                            var hAvatar = document.getElementById('navProfileHeaderAvatar');
                            if (hAvatar) hAvatar.innerHTML = imgHtml;
                            var dAvatar = document.querySelector('.drawer-user-avatar');
                            if (dAvatar) dAvatar.innerHTML = imgHtml;
                        }
                    }
                    if (feedback) {
                        feedback.className = 'small text-success ms-2';
                        feedback.textContent = 'Photo updated successfully!';
                        setTimeout(function() { feedback.textContent = ''; }, 3000);
                    }
                    if (typeof window.notifyAvatarSync === 'function') {
                        window.notifyAvatarSync(<?= (int)$user['id'] ?>, data.image_url);
                    }
                    var removeBtn = document.getElementById('btnEditRemovePhoto');
                    if (removeBtn) {
                        removeBtn.classList.remove('d-none');
                        removeBtn.style.setProperty('display', 'inline-flex', 'important');
                    }
                } else {
                    if (feedback) {
                        feedback.className = 'small text-danger ms-2';
                        feedback.textContent = data.message || 'Upload failed.';
                    }
                }
            })
            .catch(function() {
                if (uploadBtn) uploadBtn.disabled = false;
                if (feedback) {
                    feedback.className = 'small text-danger ms-2';
                    feedback.textContent = 'Upload error.';
                }
            });
        });
    }

    var DEFAULT_AVATAR_SVG = '<?= str_replace(["\r", "\n"], '', $defaultAvatarSvg) ?>';

    window.removeEditUserAvatar = function() {
        function executeEditAvatarRemoval() {
            var feedback = document.getElementById('editAvatarFeedback');
            var removeBtn = document.getElementById('btnEditRemovePhoto');
            if (feedback) {
                feedback.className = 'small text-primary ms-2';
                feedback.textContent = 'Removing...';
            }
            if (removeBtn) removeBtn.disabled = true;
            var csrfToken = getEditUserCsrfToken();
            var formData = new FormData();
            if (csrfToken) formData.append('csrf_test_name', csrfToken);

            fetch('<?= base_url('admin/users/remove-avatar/' . $user['id']) ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                updateEditUserCsrfToken(data);
                if (removeBtn) removeBtn.disabled = false;
                if (data.success) {
                    var preview = document.getElementById('editUserAvatarPreview');
                    if (preview) {
                        preview.innerHTML = DEFAULT_AVATAR_SVG;
                    }
                    if (parseInt(<?= (int)$user['id'] ?>, 10) === <?= (int)(session()->get('id') ?? 0) ?>) {
                        if (typeof window.updateAvatarDom === 'function') {
                            window.updateAvatarDom(null);
                        } else {
                            var tAvatar = document.getElementById('navProfileTriggerAvatar');
                            if (tAvatar) tAvatar.innerHTML = DEFAULT_AVATAR_SVG;
                            var hAvatar = document.getElementById('navProfileHeaderAvatar');
                            if (hAvatar) hAvatar.innerHTML = DEFAULT_AVATAR_SVG;
                            var dAvatar = document.querySelector('.drawer-user-avatar');
                            if (dAvatar) dAvatar.innerHTML = DEFAULT_AVATAR_SVG;
                        }
                    }
                    if (removeBtn) {
                        removeBtn.classList.add('d-none');
                        removeBtn.style.setProperty('display', 'none', 'important');
                    }
                    if (typeof window.notifyAvatarSync === 'function') {
                        window.notifyAvatarSync(<?= (int)$user['id'] ?>, null);
                    }
                    if (feedback) {
                        feedback.className = 'small text-success ms-2';
                        feedback.textContent = 'Photo removed.';
                        setTimeout(function() { feedback.textContent = ''; }, 3000);
                    }
                } else {
                    if (feedback) {
                        feedback.className = 'small text-danger ms-2';
                        feedback.textContent = data.message || 'Removal failed.';
                    }
                }
            })
            .catch(function() {
                if (removeBtn) removeBtn.disabled = false;
                if (feedback) {
                    feedback.className = 'small text-danger ms-2';
                    feedback.textContent = 'Error removing photo.';
                }
            });
        }

        if (typeof window.confirmAction === 'function') {
            window.confirmAction({
                title: 'Remove Profile Photo?',
                message: 'Are you sure you want to remove this profile picture and revert to the default avatar?',
                confirmText: 'Remove Photo',
                onConfirm: executeEditAvatarRemoval
            });
        } else if (confirm('Are you sure you want to remove this profile picture?')) {
            executeEditAvatarRemoval();
        }
    };

    if (roleSelect) roleSelect.addEventListener('change', toggleRouteSection);
    toggleRouteSection();
    updateSelectAllState();
});
</script>

<?= $this->include('templates/footer') ?>
