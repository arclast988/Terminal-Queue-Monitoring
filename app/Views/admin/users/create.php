<?= $this->include('templates/header') ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css?v=20260928_1') ?>">

<div class="form-entry-page">
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
                <span class="card-title-modern"><i class="bi bi-person-gear me-2"></i> User Details</span>
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

<?php
$defaultAvatarSvg = <<<SVG
<svg class="profile-identicon-svg" viewBox="0 0 64 64" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <circle cx="32" cy="32" r="31" fill="#F1F5F9" stroke="#CBD5E1" stroke-width="2" />
    <path d="M32 32.5a9.5 9.5 0 1 0 0-19 9.5 9.5 0 0 0 0 19zm0 5c-9.2 0-17 5.8-17 13.5 0 1 .8 1.8 1.8 1.8h30.4c1 0 1.8-.8 1.8-1.8 0-7.7-7.8-13.5-17-13.5z" fill="#475569" />
</svg>
SVG;
?>

                <form action="<?= base_url('admin/users/store') ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <!-- Profile Photo Section -->
                    <div class="p-3 mb-4 rounded-3 d-flex flex-wrap align-items-center gap-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                        <div id="createUserAvatarPreview" style="width: 60px; height: 60px; border-radius: 50%; background: #F1F5F9; border: 2px solid #15803d; display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            <?= $defaultAvatarSvg ?>
                        </div>
                        <div style="flex: 1 1 auto; min-width: 200px;">
                            <div class="fw-bold" style="font-size: 14px; color: #1e293b;">Profile Picture <span class="text-muted fw-normal small">(Optional)</span></div>
                            <div class="small text-muted mb-2">JPG, PNG, WEBP, or GIF image (max 4MB).</div>
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <input type="file" id="createAvatarFileInput" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif" style="display: none;">
                                <button type="button" class="btn-modern btn-modern-outline btn-modern-sm" onclick="document.getElementById('createAvatarFileInput').click();" id="btnCreateUploadPhoto">
                                    <i class="bi bi-camera-fill"></i> Upload Photo
                                </button>
                                <button type="button" class="btn-modern btn-modern-outline btn-modern-sm text-danger d-none" id="btnCreateRemovePhoto" onclick="removeCreateUserAvatar();" style="display: none !important;">
                                    <i class="bi bi-trash"></i> Remove Photo
                                </button>
                                <span id="createAvatarFeedback" class="small fw-semibold ms-2"></span>
                            </div>
                        </div>
                    </div>

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
                                <input type="password" class="input-modern" id="password" name="password" required minlength="8" placeholder="Choose a strong password">
                                <button type="button" class="btn-modern btn-modern-outline password-toggle" id="togglePassword" title="Show or hide password" aria-label="Show or hide password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-text-modern">Minimum 8 characters required.</div>
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
                                <select class="select-modern" id="role" name="role" required style="flex: 1 1 0%; width: 100%; min-width: 0;">
                                    <option value="staff" <?= old('role', 'staff') === 'staff' ? 'selected' : '' ?>>Dispatcher</option>
                                    <?php if (session()->get('role') === 'super_admin'): ?>
                                        <option value="admin" <?= old('role') === 'admin' ? 'selected' : '' ?>>Admin</option>
                                    <?php endif; ?>
                                </select>
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
                                            <span class="route-name"><?= strtoupper(esc($group['origin'])) ?> &rarr; <?= strtoupper(esc($group['destination'])) ?></span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted mb-0">No routes available. Create routes first.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-column flex-sm-row user-form-actions form-entry-actions">
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
    .route-select-all { display: flex; align-items: center; min-height: 44px; margin: 0; padding-left: 1.75rem; white-space: nowrap; }
    .route-picker { max-height: 280px; overflow-y: auto; overscroll-behavior: contain; }
    .route-item { border-radius: 6px; margin-bottom: 0.25rem; min-height: 44px; padding: 0.55rem 0.65rem 0.55rem 2rem; }
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

    var DEFAULT_AVATAR_SVG = '<?= str_replace(["\r", "\n"], '', $defaultAvatarSvg) ?>';
    var avatarInput = document.getElementById('createAvatarFileInput');
    var avatarPreview = document.getElementById('createUserAvatarPreview');
    var removeAvatarBtn = document.getElementById('btnCreateRemovePhoto');
    var feedbackEl = document.getElementById('createAvatarFeedback');

    function updatePreviewBorder() {
        if (!avatarPreview || !roleSelect) return;
        avatarPreview.style.border = (roleSelect.value === 'admin') ? '2px solid #dc2626' : '2px solid #15803d';
    }

    if (avatarInput) {
        avatarInput.addEventListener('change', function() {
            var file = this.files && this.files[0];
            if (!file) return;

            if (file.size > 4 * 1024 * 1024) {
                if (feedbackEl) {
                    feedbackEl.className = 'small text-danger ms-2';
                    feedbackEl.textContent = 'Image exceeds 4MB maximum allowed size.';
                }
                this.value = '';
                return;
            }

            var allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (allowedTypes.indexOf(file.type) === -1) {
                if (feedbackEl) {
                    feedbackEl.className = 'small text-danger ms-2';
                    feedbackEl.textContent = 'Please select a valid image (JPG, PNG, WEBP, GIF).';
                }
                this.value = '';
                return;
            }

            var reader = new FileReader();
            reader.onload = function(e) {
                if (avatarPreview) {
                    avatarPreview.innerHTML = '<img src="' + e.target.result + '" alt="Avatar Preview" style="width: 100%; height: 100%; object-fit: cover;">';
                }
                if (removeAvatarBtn) {
                    removeAvatarBtn.classList.remove('d-none');
                    removeAvatarBtn.style.setProperty('display', 'inline-flex', 'important');
                }
                if (feedbackEl) {
                    feedbackEl.className = 'small text-success ms-2';
                    feedbackEl.textContent = 'Photo selected';
                    setTimeout(function() {
                        if (feedbackEl.textContent === 'Photo selected') feedbackEl.textContent = '';
                    }, 3000);
                }
            };
            reader.readAsDataURL(file);
        });
    }

    window.removeCreateUserAvatar = function() {
        if (avatarInput) avatarInput.value = '';
        if (avatarPreview) avatarPreview.innerHTML = DEFAULT_AVATAR_SVG;
        if (removeAvatarBtn) {
            removeAvatarBtn.classList.add('d-none');
            removeAvatarBtn.style.setProperty('display', 'none', 'important');
        }
        if (feedbackEl) {
            feedbackEl.className = 'small text-muted ms-2';
            feedbackEl.textContent = 'Photo removed';
            setTimeout(function() {
                if (feedbackEl.textContent === 'Photo removed') feedbackEl.textContent = '';
            }, 2000);
        }
    };

    if (roleSelect) {
        roleSelect.addEventListener('change', function() {
            toggleRouteSection();
            updatePreviewBorder();
        });
    }

    toggleRouteSection();
    updateSelectAllState();
    updatePreviewBorder();
});
</script>

<?= $this->include('templates/footer') ?>
