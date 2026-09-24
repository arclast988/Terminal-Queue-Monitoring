<?= $this->include('templates/header') ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">

<div class="page-header-modern fade-in">
    <h1 class="page-title-modern">
        <i class="bi bi-people-fill"></i>
        Manage Users
    </h1>
    <a href="<?= base_url('admin/users/create') ?>" class="btn-modern btn-modern-primary">
        <i class="bi bi-plus-circle"></i> Add New User
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert-modern alert-modern-success fade-in">
        <i class="bi bi-check-circle-fill alert-modern-icon"></i>
        <div><?= esc(session()->getFlashdata('success')) ?></div>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert-modern alert-modern-danger fade-in">
        <i class="bi bi-exclamation-triangle-fill alert-modern-icon"></i>
        <div><?= esc(session()->getFlashdata('error')) ?></div>
    </div>
<?php endif; ?>

<?php
$defaultAvatarSvg = <<<SVG
<svg class="profile-identicon-svg" viewBox="0 0 64 64" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <circle cx="32" cy="32" r="31" fill="#F1F5F9" stroke="#CBD5E1" stroke-width="2" />
    <path d="M32 32.5a9.5 9.5 0 1 0 0-19 9.5 9.5 0 0 0 0 19zm0 5c-9.2 0-17 5.8-17 13.5 0 1 .8 1.8 1.8 1.8h30.4c1 0 1.8-.8 1.8-1.8 0-7.7-7.8-13.5-17-13.5z" fill="#475569" />
</svg>
SVG;

// Compute summary counts or fallback
$totalActiveUsers = $countActive ?? 0;
$countAdmin = $countAdmin ?? 0;
$countDispatcher = $countDispatcher ?? 0;
$countArchived = $countArchived ?? 0;
if (!isset($countActive)) {
    $totalActiveUsers = 0;
    $countAdmin = 0;
    $countDispatcher = 0;
    $countArchived = 0;
    foreach ($users as $u) {
        if (($u['status'] ?? 'active') === 'archived') {
            $countArchived++;
        } else {
            $totalActiveUsers++;
            if ($u['role'] === 'super_admin' || $u['role'] === 'admin') {
                $countAdmin++;
            } else {
                $countDispatcher++;
            }
        }
    }
}
?>

<!-- User Search & Filter Bar -->
<div class="modern-card shadow-modern fade-in mb-4">
    <div class="modern-card-body p-3 p-md-4">
        <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-3">
            <div class="user-search-group position-relative">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" class="user-search-input" id="user-search" placeholder="Search name or username..." onkeyup="filterUsers(currentFilter)" oninput="toggleUserClearBtn(this.value)" autocomplete="off">
                <button type="button" class="btn-clear-search" id="clear-user-search" onclick="clearUserSearch()" style="display: none !important;" title="Clear search">
                    <i class="bi bi-x-circle-fill"></i>
                </button>
            </div>
            
            <div class="user-filter-bar d-flex flex-wrap align-items-center gap-2">
                <span class="user-filter-heading d-none d-sm-inline-flex align-items-center" style="font-size:13px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.5px; margin-right:4px;">
                    <i class="bi bi-funnel me-1" style="font-size: 14px;"></i>Filter:
                </span>
                
                <button type="button" class="vf-btn active" id="filter-btn-all" onclick="filterUsers('all')">
                    <i class="bi bi-grid-3x3-gap-fill"></i> All
                    <span class="vf-count"><?= $totalActiveUsers ?></span>
                </button>
                
                <span class="vf-divider d-none d-md-inline-block" style="width:1px; height:24px; background:#dee2e6; margin:0 4px;"></span>
                
                <button type="button" class="vf-btn vf-van" id="filter-btn-admin" onclick="filterUsers('admin')">
                    <i class="bi bi-shield-lock"></i> Admins
                    <span class="vf-count"><?= $countAdmin ?></span>
                </button>
                
                <button type="button" class="vf-btn vf-dispatcher" id="filter-btn-dispatcher" onclick="filterUsers('dispatcher')">
                    <i class="bi bi-person-fill-gear"></i> Dispatchers
                    <span class="vf-count"><?= $countDispatcher ?></span>
                </button>

                <span class="vf-divider d-none d-md-inline-block" style="width:1px; height:24px; background:#dee2e6; margin:0 4px;"></span>

                <button type="button" class="vf-btn vf-archive" id="filter-btn-archived" onclick="filterUsers('archived')">
                    <i class="bi bi-archive-fill"></i> Archived
                    <span class="vf-count"><?= $countArchived ?></span>
                </button>
            </div>
        </div>
        
        <div id="filter-label" class="mt-2 pt-1" style="font-size:13px; color:#64748b;">Showing all <strong><?= $totalActiveUsers ?></strong> active users</div>
    </div>
</div>

<div class="modern-card shadow-modern fade-in">
    <div class="modern-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="modern-card-title">
            <i class="bi bi-people" style="color: var(--primary-red);"></i>
            User List
<style>
/* Scoped Selection & Bulk Action Rules - Matches App Action Buttons */
.bulk-col {
    display: none !important;
    width: 44px !important;
    min-width: 44px !important;
    max-width: 44px !important;
    text-align: center !important;
    vertical-align: middle !important;
    padding: 8px 6px !important;
}
.table-modern.selection-mode-active .bulk-col,
.selection-mode-active .bulk-col {
    display: table-cell !important;
}
.bulk-action-top-bar {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 8px 14px;
    margin-bottom: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    display: none;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
}
.bulk-action-top-bar.is-visible {
    display: flex !important;
}
.bulk-bar-info {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    color: #1e293b;
}
.bulk-select-all-wrap {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    cursor: pointer;
    user-select: none;
    margin-bottom: 0;
}
.bulk-bar-divider {
    display: inline-block;
    width: 1px;
    height: 16px;
    background: #e2e8f0;
}
.bulk-count-badge {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    padding: 2px 7px;
    font-size: 12px;
    font-weight: 600;
}
.bulk-count-text {
    font-size: 13px;
    color: #64748b;
}
.bulk-bar-actions {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.btn-bulk-deactivate {
    background: #fef3c7 !important;
    color: #d97706 !important;
    border: 1px solid #fde68a !important;
    font-weight: 600 !important;
    border-radius: 6px !important;
    padding: 5px 12px !important;
    font-size: 13px !important;
    align-items: center;
    gap: 6px;
    box-shadow: none !important;
    transition: all 0.15s ease !important;
    cursor: pointer !important;
}
.btn-bulk-deactivate:hover:not([disabled]) {
    background: #f59e0b !important;
    color: #ffffff !important;
    border-color: #f59e0b !important;
}
.btn-bulk-deactivate:hover:not([disabled]) i,
.btn-bulk-deactivate:hover:not([disabled]) span {
    color: #ffffff !important;
}

.btn-bulk-activate {
    background: #dcfce7 !important;
    color: #15803d !important;
    border: 1px solid #bbf7d0 !important;
    font-weight: 600 !important;
    border-radius: 6px !important;
    padding: 5px 12px !important;
    font-size: 13px !important;
    align-items: center;
    gap: 6px;
    box-shadow: none !important;
    transition: all 0.15s ease !important;
    cursor: pointer !important;
}
.btn-bulk-activate:hover:not([disabled]) {
    background: #16a34a !important;
    color: #ffffff !important;
    border-color: #16a34a !important;
}
.btn-bulk-activate:hover:not([disabled]) i,
.btn-bulk-activate:hover:not([disabled]) span {
    color: #ffffff !important;
}

.btn-bulk-delete {
    background: #fee2e2 !important;
    color: #dc2626 !important;
    border: 1px solid #fecaca !important;
    font-weight: 600 !important;
    border-radius: 6px !important;
    padding: 5px 12px !important;
    font-size: 13px !important;
    align-items: center;
    gap: 6px;
    box-shadow: none !important;
    transition: all 0.15s ease !important;
    cursor: pointer !important;
}
.btn-bulk-delete:hover:not([disabled]) {
    background: #dc2626 !important;
    color: #ffffff !important;
    border-color: #dc2626 !important;
}
.btn-bulk-delete:hover:not([disabled]) i,
.btn-bulk-delete:hover:not([disabled]) span {
    color: #ffffff !important;
}

.btn-bulk-deactivate[disabled],
.btn-bulk-activate[disabled],
.btn-bulk-delete[disabled],
.btn-bulk-deactivate:disabled,
.btn-bulk-activate:disabled,
.btn-bulk-delete:disabled {
    opacity: 0.45 !important;
    cursor: not-allowed !important;
    pointer-events: none !important;
    box-shadow: none !important;
    transform: none !important;
}

.btn-bulk-cancel {
    background: #ffffff !important;
    border: 1.5px solid #cbd5e1 !important;
    color: #475569 !important;
    border-radius: 6px !important;
    padding: 5px 12px !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    transition: all 0.15s ease !important;
    cursor: pointer !important;
    align-items: center;
    gap: 4px;
}
.btn-bulk-cancel:hover {
    background: #f1f5f9 !important;
    color: #0f172a !important;
    border-color: #94a3b8 !important;
}
</style>

<div class="modern-card shadow-modern fade-in">
    <div class="modern-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="modern-card-title">
            <i class="bi bi-people-fill" style="color: var(--primary-red);"></i>
            User Accounts
        </span>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn-modern btn-modern-sm btn-modern-outline" id="btn-toggle-select-users" onclick="toggleUserSelectMode()" title="Toggle selection mode for batch actions">
                <i class="bi bi-check2-square me-1"></i> <span id="btn-select-users-text">Select</span>
            </button>
        </div>
    </div>
    <div class="modern-card-body">
        <!-- Integrated Top Bulk Action Bar -->
        <div id="user-bulk-toolbar" class="bulk-action-top-bar" style="display: none;">
            <div class="bulk-bar-info">
                <label class="bulk-select-all-wrap mb-0">
                    <input type="checkbox" id="select-all-users" class="form-check-input select-all-checkbox m-0" style="width: 17px; height: 17px; cursor: pointer;">
                    <span class="fw-semibold text-dark" style="font-size: 13.5px;">Select All</span>
                </label>
                <span class="bulk-bar-divider"></span>
                <span class="bulk-count-badge">
                    <span id="user-selected-count">0</span> <span id="user-selected-text">selected</span>
                </span>
            </div>
            <div class="bulk-bar-actions">
                <button type="button" class="btn-modern btn-modern-sm btn-action-deactivate btn-bulk-deactivate" id="btn-bulk-deactivate-users" onclick="openUserBulkModal('deactivate')" disabled>
                    <i class="bi bi-person-slash"></i> <span>Deactivate Selected</span>
                </button>
                <button type="button" class="btn-modern btn-modern-sm btn-action-activate btn-bulk-activate" id="btn-bulk-activate-users" onclick="openUserBulkModal('activate')" style="display: none;" disabled>
                    <i class="bi bi-person-check-fill"></i> <span>Activate Selected</span>
                </button>
                <button type="button" class="btn-modern btn-modern-sm btn-action-delete btn-bulk-delete" id="btn-bulk-delete-users" onclick="openUserBulkModal('delete')" style="display: none;" disabled>
                    <i class="bi bi-trash"></i> <span>Delete Selected</span>
                </button>
                <button type="button" class="btn-modern btn-modern-sm btn-modern-outline btn-bulk-cancel" onclick="toggleUserSelectMode(false)">
                    <i class="bi bi-x"></i> <span>Cancel</span>
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table-modern" id="users-table">
                <thead>
                    <tr>
                        <th class="bulk-col" style="display: none; width: 44px; text-align: center;"></th>
                        <th>#</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Assigned Routes</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $i => $user): ?>
                            <?php
                            $cUserId = (int) session()->get('id');
                            $cUserRole = session()->get('role');
                            $tUserId = (int) $user['id'];
                            $tUserRole = $user['role'];
                            $isUArchived = ($user['status'] ?? 'active') === 'archived';

                            $canUDeact = !$isUArchived && 
                                         ($tUserId !== $cUserId) && 
                                         ($tUserRole !== 'super_admin') && 
                                         (($cUserRole === 'super_admin') || 
                                          ($cUserRole === 'admin' && $tUserRole === 'staff'));

                            $canUAct = $isUArchived && 
                                       (($cUserRole === 'super_admin') || 
                                        ($cUserRole === 'admin' && $tUserRole === 'staff'));

                            $canUDel = $isUArchived && 
                                       ($tUserId !== $cUserId) && 
                                       ($tUserRole !== 'super_admin') && 
                                       (($cUserRole === 'super_admin') || 
                                        ($cUserRole === 'admin' && $tUserRole === 'staff'));

                            $canUSelect = $isUArchived ? ($canUAct || $canUDel) : $canUDeact;
                            ?>
                            <tr data-role="<?= esc($user['role']) ?>" data-status="<?= esc($user['status'] ?? 'active') ?>" class="<?= ($user['status'] ?? 'active') === 'archived' ? 'user-row-archived' : '' ?>" data-user-id="<?= $user['id'] ?>">
                                <td data-label="Select" class="bulk-col bulk-select-cell" style="display: none; text-align: center;">
                                    <?php if ($canUSelect): ?>
                                        <input type="checkbox" class="form-check-input user-row-checkbox" value="<?= $user['id'] ?>" data-status="<?= esc($user['status'] ?? 'active') ?>" data-name="<?= esc($user['full_name'] ?? $user['username']) ?>" data-role="<?= esc($user['role']) ?>" title="Select <?= esc($user['full_name'] ?? $user['username']) ?>">
                                    <?php else: ?>
                                        <span class="text-muted" title="<?= $tUserRole === 'super_admin' ? 'Superadmin cannot be modified' : ($tUserId === $cUserId ? 'Cannot modify your own account' : 'Protected Account') ?>" style="opacity: 0.45; cursor: not-allowed; display: inline-flex; align-items: center; justify-content: center; width: 18px; height: 18px;">
                                            <i class="bi bi-lock-fill" style="font-size: 13px;"></i>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="row-number" data-label="#"><?= $i + 1 ?></td>
                                <td data-label="User">
                                    <?php
                                    $nameParts = explode(' ', trim($user['full_name']));
                                    $initials = '';
                                    if (!empty($nameParts[0])) {
                                        $initials .= strtoupper(substr($nameParts[0], 0, 1));
                                    }
                                    if (count($nameParts) >= 2 && !empty($nameParts[count($nameParts) - 1])) {
                                        $initials .= strtoupper(substr($nameParts[count($nameParts) - 1], 0, 1));
                                    }
                                    if (empty($initials)) {
                                        $initials = strtoupper(substr($user['username'], 0, 2));
                                    }
                                    $currentUserId = (int) session()->get('id');
                                    $currentUserRole = session()->get('role');
                                    $canChangeAvatar = false;
                                    if ($user['role'] === 'super_admin') {
                                        $canChangeAvatar = ($currentUserId === (int) $user['id']);
                                    } elseif ($user['role'] === 'admin') {
                                        $canChangeAvatar = ($currentUserRole === 'super_admin' || $currentUserId === (int) $user['id']);
                                    } else {
                                        $canChangeAvatar = ($currentUserRole === 'super_admin' || $currentUserRole === 'admin');
                                    }
                                    ?>
                                    <div class="d-flex align-items-center user-cell-content">
                                        <div class="avatar-wrapper me-3 <?= $canChangeAvatar ? 'avatar-editable' : '' ?>" id="avatar-wrapper-<?= $user['id'] ?>"
                                            data-user-id="<?= $user['id'] ?>"
                                            data-img-url="<?= !empty($user['profile_image']) ? base_url(esc($user['profile_image'])) : '' ?>"
                                            data-user-name="<?= esc(addslashes($user['full_name'] ?: $user['username']), 'js') ?>"
                                            data-initials="<?= esc($initials, 'js') ?>"
                                            data-role="<?= esc($user['role'], 'js') ?>"
                                            <?php if ($canChangeAvatar): ?>
                                                role="button"
                                                tabindex="0"
                                                title="Click to change profile picture"
                                                onclick="openAvatarModal(<?= $user['id'] ?>, '<?= esc(addslashes($user['full_name'] ?: $user['username']), 'js') ?>', '<?= !empty($user['profile_image']) ? base_url(esc($user['profile_image'])) : '' ?>', '<?= esc($initials, 'js') ?>', '<?= esc($user['role'], 'js') ?>')"
                                                onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();this.click();}"
                                            <?php endif; ?>>
                                            <div class="avatar-circle <?= $user['role'] === 'super_admin' ? 'avatar-super-admin' : ($user['role'] === 'admin' ? 'avatar-admin' : 'avatar-dispatcher') ?>" id="avatar-circle-<?= $user['id'] ?>">
                                                <?php if (!empty($user['profile_image'])): ?>
                                                    <img src="<?= base_url(esc($user['profile_image'])) ?>" alt="<?= esc($user['full_name']) ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                                <?php else: ?>
                                                    <?= $defaultAvatarSvg ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="user-info-text" style="min-width: 0; flex: 1 1 auto;">
                                            <div class="fw-bold d-flex align-items-center flex-wrap gap-1" style="font-size:14.5px; color: inherit;">
                                                <span><?= esc($user['full_name']) ?></span>
                                                <?php if ((int) $user['id'] === (int) session()->get('id')): ?>
                                                    <span class="badge" style="background: #e2e8f0; color: #334155; font-size: 11px; font-weight: 600; padding: 2px 6px; border-radius: 4px; vertical-align: middle;">You</span>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <small style="font-size:12.5px; color: #64748b;">
                                                    <i class="bi bi-envelope me-1"></i><?= esc($user['username']) ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Role">
                                    <div class="d-flex align-items-center flex-wrap gap-1">
                                        <?php if ($user['role'] === 'super_admin'): ?>
                                            <span class="profile-role-pill pill-super_admin">
                                                <i class="fas fa-shield-alt"></i> Super Admin
                                            </span>
                                        <?php elseif ($user['role'] === 'admin'): ?>
                                            <span class="profile-role-pill pill-admin">
                                                <i class="fas fa-shield-alt"></i> Admin
                                            </span>
                                        <?php else: ?>
                                            <span class="profile-role-pill pill-staff">
                                                <i class="fas fa-user-gear"></i> Dispatcher
                                            </span>
                                        <?php endif; ?>
                                        <?php if (($user['status'] ?? 'active') === 'archived'): ?>
                                            <span class="badge-status-archived" title="This account is deactivated">
                                                <i class="bi bi-archive-fill me-1"></i>Archived
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td data-label="Assigned Routes">
                                    <?php if ($user['role'] === 'super_admin' || $user['role'] === 'admin'): ?>
                                        <span class="text-muted" style="color: #94a3b8; font-size: 16px; font-weight: 500;">&mdash;</span>
                                    <?php elseif (!empty($user['assigned_routes_label']) && $user['assigned_routes_label'] !== 'None'): ?>
                                        <div class="badge-scroll-wrap" style="display: flex; gap: 4px; padding-bottom: 4px;" title="<?= esc($user['assigned_routes_label']) ?>">
                                            <?php
                                            $routeParts = explode(', ', $user['assigned_routes_label']);
                                            foreach ($routeParts as $rp):
                                                ?>
                                                <span class="badge-modern badge-modern-primary"
                                                    style="font-size:12.5px; font-weight:600; padding: 3px 8px;"><?= esc($rp) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge-modern badge-modern-warning">No Routes</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Action">
                                    <?php
                                    $currentUserId = (int) session()->get('id');
                                    $currentUserRole = session()->get('role');
                                    $targetUserId = (int) $user['id'];
                                    $targetUserRole = $user['role'];
                                    $isArchived = ($user['status'] ?? 'active') === 'archived';

                                    $canEdit = ($targetUserId === $currentUserId) || 
                                               ($currentUserRole === 'super_admin') || 
                                               ($currentUserRole === 'admin' && $targetUserRole === 'staff');

                                    // Deactivate: only if currently active, not self, not super_admin
                                    $canDeactivate = !$isArchived && 
                                                     ($targetUserId !== $currentUserId) && 
                                                     ($targetUserRole !== 'super_admin') && 
                                                     (($currentUserRole === 'super_admin') || 
                                                      ($currentUserRole === 'admin' && $targetUserRole === 'staff'));

                                    // Activate: only if currently archived
                                    $canActivate = $isArchived && 
                                                   (($currentUserRole === 'super_admin') || 
                                                    ($currentUserRole === 'admin' && $targetUserRole === 'staff'));

                                    // Delete: permanent delete only allowed for archived users, not self, not super_admin
                                    $canDelete = $isArchived && 
                                                 ($targetUserId !== $currentUserId) && 
                                                 ($targetUserRole !== 'super_admin') && 
                                                 (($currentUserRole === 'super_admin') || 
                                                  ($currentUserRole === 'admin' && $targetUserRole === 'staff'));
                                    ?>
                                    <div class="btn-group-modern" role="group">
                                        <?php if ($isArchived): ?>
                                            <?php if ($canActivate): ?>
                                                <form id="activate-user-form-<?= $user['id'] ?>" action="<?= base_url('admin/users/activate/' . $user['id']) ?>" method="post" class="d-inline">
                                                    <input type="hidden" name="redirect_tab" value="archived">
                                                    <?= csrf_field() ?>
                                                    <button type="button" class="btn-modern btn-action-activate btn-modern-sm" title="Activate"
                                                        onclick="showActivateUserModal({
                                                            formId: 'activate-user-form-<?= $user['id'] ?>',
                                                            userId: '<?= $user['id'] ?>',
                                                            name: '<?= esc(addslashes($user['full_name'] ?? $user['username'])) ?>',
                                                            username: '<?= esc(addslashes($user['username'] ?? '')) ?>',
                                                            role: '<?= esc(addslashes($user['role'] === 'super_admin' ? 'Super Admin' : ($user['role'] === 'admin' ? 'Admin' : 'Dispatcher'))) ?>',
                                                            rawRole: '<?= esc($user['role']) ?>'
                                                        })">
                                                        <i class="bi bi-person-check-fill"></i> <span class="user-action-label">Activate</span>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($canEdit): ?>
                                                <a href="<?= base_url('admin/users/edit/' . $user['id']) ?>"
                                                    class="btn-modern btn-action-edit btn-modern-sm" title="Edit">
                                                    <i class="bi bi-pencil"></i> <span class="user-action-label">Edit</span>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($canDelete): ?>
                                                <form id="delete-user-form-<?= $user['id'] ?>" action="<?= base_url('admin/users/delete/' . $user['id']) ?>" method="post" class="d-inline">
                                                    <input type="hidden" name="redirect_tab" value="archived">
                                                    <?= csrf_field() ?>
                                                    <button type="button" class="btn-modern btn-action-delete btn-modern-sm" title="Delete Permanently"
                                                        onclick="showDeleteUserModal({
                                                            formId: 'delete-user-form-<?= $user['id'] ?>',
                                                            userId: '<?= $user['id'] ?>',
                                                            name: '<?= esc(addslashes($user['full_name'] ?? $user['username'])) ?>',
                                                            username: '<?= esc(addslashes($user['username'] ?? '')) ?>',
                                                            role: '<?= esc(addslashes($user['role'] === 'super_admin' ? 'Super Admin' : ($user['role'] === 'admin' ? 'Admin' : 'Dispatcher'))) ?>',
                                                            rawRole: '<?= esc($user['role']) ?>'
                                                        })">
                                                        <i class="bi bi-trash"></i> <span class="user-action-label">Delete</span>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php if ($canEdit): ?>
                                                <a href="<?= base_url('admin/users/edit/' . $user['id']) ?>"
                                                    class="btn-modern btn-action-edit btn-modern-sm" title="Edit">
                                                    <i class="bi bi-pencil"></i> <span class="user-action-label">Edit</span>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($canDeactivate): ?>
                                                <form id="deactivate-user-form-<?= $user['id'] ?>" action="<?= base_url('admin/users/deactivate/' . $user['id']) ?>" method="post" class="d-inline">
                                                    <input type="hidden" name="redirect_tab" class="user-deact-redirect-tab" value="">
                                                    <?= csrf_field() ?>
                                                    <button type="button" class="btn-modern btn-action-deactivate btn-modern-sm" title="Deactivate"
                                                        onclick="showDeactivateUserModal({
                                                            formId: 'deactivate-user-form-<?= $user['id'] ?>',
                                                            userId: '<?= $user['id'] ?>',
                                                            name: '<?= esc(addslashes($user['full_name'] ?? $user['username'])) ?>',
                                                            username: '<?= esc(addslashes($user['username'] ?? '')) ?>',
                                                            role: '<?= esc(addslashes($user['role'] === 'super_admin' ? 'Super Admin' : ($user['role'] === 'admin' ? 'Admin' : 'Dispatcher'))) ?>',
                                                            rawRole: '<?= esc($user['role']) ?>'
                                                        })">
                                                        <i class="bi bi-person-slash"></i> <span class="user-action-label">Deactivate</span>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted empty-state-table">
                                <i class="bi bi-people fs-1 d-block mb-3 opacity-50"></i>
                                <div class="fw-bold fs-6 empty-state-title">No users found</div>
                                <small class="empty-state-subtitle">No user accounts exist in the system.</small>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>



<!-- User Bulk Confirmation Modal -->
<div class="modal fade" id="userBulkConfirmModal" tabindex="-1" aria-labelledby="userBulkConfirmLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px; margin: 1.75rem auto;">
        <div class="modal-content delete-user-modal-content" style="border-radius: 18px; overflow: hidden; border: none; box-shadow: 0 20px 35px -5px rgba(15, 23, 42, 0.25);">
            <div id="userBulkStripe" style="height: 4px; width: 100%; background: #f59e0b;"></div>
            <form id="userBulkForm" method="post" action="<?= base_url('admin/users/bulk-action') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" id="userBulkActionInput" value="">
                <input type="hidden" name="redirect_tab" id="userBulkRedirectTab" value="">
                <div id="userBulkIdsContainer"></div>

                <div class="modal-body text-center p-4">
                    <div id="userBulkIconBadge" style="width: 68px; height: 68px; margin: 4px auto 18px auto; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; background: #fef3c7; color: #d97706;">
                        <i id="userBulkIcon" class="bi bi-person-slash"></i>
                    </div>

                    <h4 class="delete-user-modal-title" id="userBulkConfirmTitle" style="font-size: 1.25rem; font-weight: 700; color: #0f172a;">Deactivate Selected Users?</h4>
                    <p class="delete-user-modal-desc" id="userBulkConfirmDesc" style="font-size: 0.9rem; color: #64748b;">
                        Are you sure you want to deactivate the selected users?
                    </p>

                    <div id="userBulkSelectedList" class="p-2 mb-2 text-start" style="max-height: 140px; overflow-y: auto; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px;">
                    </div>
                </div>

                <div class="modal-footer delete-user-modal-footer" style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; gap: 8px;">
                    <button type="button" class="btn" data-bs-dismiss="modal" style="border: 1px solid #cbd5e1; background: #fff; color: #475569; font-weight: 600; padding: 10px 18px; border-radius: 10px;">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn" id="userBulkSubmitBtn" style="flex: 1; font-weight: 600; padding: 10px 18px; border-radius: 10px; border: none; background: #f59e0b; color: #fff;">
                        Confirm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<style>
    #users-table {
        width: 100%;
    }

    #users-table th,
    #users-table td {
        vertical-align: middle;
    }

    /* Search Input Capsule and Alignments */
    .user-search-group {
        display: flex !important;
        align-items: center !important;
        max-width: 280px;
        width: 100%;
        position: relative;
    }

    .user-search-group .input-group-text {
        height: 40px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 6px 0 6px 14px !important;
        border: 2px solid #cbd5e1 !important;
        border-right: none !important;
        border-top-left-radius: 9999px !important;
        border-bottom-left-radius: 9999px !important;
        background-color: var(--card-bg, #fff) !important;
        color: #64748b !important;
        font-size: 15px !important;
        transition: border-color 0.2s ease;
    }

    .user-search-group .user-search-input {
        height: 40px !important;
        border: 2px solid #cbd5e1 !important;
        border-left: none !important;
        border-top-right-radius: 9999px !important;
        border-bottom-right-radius: 9999px !important;
        padding: 6px 14px 6px 6px !important;
        font-size: 14px !important;
        outline: none !important;
        background-color: var(--card-bg, #fff) !important;
        color: var(--text-main, #1e293b) !important;
        width: 100% !important;
        transition: border-color 0.2s ease;
    }

    .user-search-group:focus-within .input-group-text,
    .user-search-group:focus-within .user-search-input {
        border-color: var(--primary-red, #dc2626) !important;
    }

    @media (max-width: 768px) {
        .user-search-group {
            max-width: 100% !important;
            width: 100% !important;
            margin-right: 0 !important;
            margin-bottom: 8px !important;
        }
    }

    /* Avatar Circles & Interactive Upload */
    .avatar-wrapper {
        position: relative;
        display: inline-block;
        flex-shrink: 0;
        line-height: 1;
    }
    .avatar-circle {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 15px;
        color: #fff;
        flex-shrink: 0;
        letter-spacing: 0.5px;
        overflow: hidden;
        position: relative;
        background: #F1F5F9;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .avatar-super-admin {
        border: 2.5px solid rgba(183, 28, 28, 0.45);
    }
    .avatar-admin {
        border: 2.5px solid rgba(220, 38, 38, 0.4);
    }
    .avatar-dispatcher {
        border: 2.5px solid rgba(21, 128, 61, 0.4);
    }

    .avatar-editable {
        cursor: pointer;
    }
    .avatar-editable:hover .avatar-circle {
        transform: scale(1.06);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.18);
    }

    .vf-count {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 22px !important;
        height: 22px !important;
        padding: 0 6px !important;
        border-radius: 12px !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        background: #e2e8f0 !important;
        color: #475569 !important;
    }
    .vf-btn.active .vf-count {
        background: rgba(255, 255, 255, 0.25) !important;
        color: #fff !important;
    }

    .user-row-hidden {
        display: none !important;
    }

    .page-title-modern i {
        font-size: 26px !important;
        vertical-align: -2px;
    }

    .modern-card-title i {
        font-size: 20px !important;
        vertical-align: -2px;
    }

    .btn-group-modern .btn-modern i,
    .btn-group-modern .btn-modern .bi {
        font-size: 14.5px !important;
        vertical-align: -1px;
    }

    .profile-role-pill i {
        font-size: 13.5px !important;
        vertical-align: -1px;
    }

    @media (max-width: 991.98px) {
        #users-table {
            font-size: 13px;
        }

        #users-table thead th,
        #users-table tbody td {
            padding: 0.7rem 0.55rem !important;
        }

        #users-table th:first-child,
        #users-table td.row-number {
            width: 38px;
            text-align: center;
        }

        .btn-group-modern {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 5px !important;
            align-items: center !important;
        }

        .btn-group-modern .btn-modern {
            width: auto !important;
            height: auto !important;
            padding: 5px 10px !important;
            border-radius: 6px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 5px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            white-space: nowrap !important;
            min-height: 34px !important;
        }

        .user-action-label {
            display: inline !important;
        }
    }

    @media (max-width: 575.98px) {
        .avatar-circle {
            width: 42px !important;
            height: 42px !important;
            font-size: 15px !important;
        }

        .user-filter-bar {
            width: 100% !important;
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 8px !important;
        }

        .vf-btn {
            flex: 1 1 calc(50% - 5px);
            justify-content: center;
            padding: 8px 10px !important;
            font-size: 12.5px !important;
            min-height: 38px !important;
        }

        .btn-group-modern .btn-modern {
            padding: 6px 10px !important;
            font-size: 11.5px !important;
            gap: 4px !important;
            min-height: 34px !important;
        }

        .user-search-group .input-group-text {
            height: 42px !important;
            font-size: 15px !important;
        }
        .user-search-group .user-search-input {
            height: 42px !important;
        }

        .vf-divider {
            display: none !important;
        }

        .profile-role-pill {
            font-size: 11px !important;
        }
        .profile-role-pill i {
            font-size: 12px !important;
        }
    }

    @media (max-width: 380px) {
        .vf-btn {
            flex: 1 1 100%;
            min-height: 36px !important;
        }

        .btn-group-modern .btn-modern {
            padding: 5px 8px !important;
            font-size: 11px !important;
            gap: 3px !important;
            min-height: 34px !important;
        }
    }

    /* Filter capsule buttons — matches Vehicles Register page */
    .vf-btn {
        display: inline-flex !important;
        align-items: center !important;
        gap: 7px !important;
        padding: 8px 16px !important;
        border-radius: 20px !important;
        font-size: 13.5px !important;
        font-weight: 500 !important;
        font-family: 'Outfit', sans-serif !important;
        cursor: pointer !important;
        border: 2px solid #dee2e6 !important;
        background: #fff !important;
        color: #475569 !important;
        white-space: nowrap !important;
        line-height: 1.4 !important;
        min-height: 38px !important;
        transition: border-color .15s ease, background .15s ease, color .15s ease;
    }
    .vf-btn i,
    .vf-btn .bi {
        font-size: 15px !important;
    }
    .vf-btn:hover {
        border-color: #94a3b8 !important;
        background: #f8fafc !important;
        color: #1e293b !important;
    }
    .vf-btn.active {
        background: var(--primary, #C62828) !important;
        border-color: var(--primary, #C62828) !important;
        color: #fff !important;
    }
    .vf-btn.vf-van.active {
        background: #c62828 !important;
        border-color: #c62828 !important;
        color: #fff !important;
    }
    .vf-btn.vf-dispatcher.active {
        background: #15803d !important;
        border-color: #15803d !important;
        color: #fff !important;
    }
    .vf-btn.vf-dispatcher:hover:not(.active) {
        border-color: #86efac !important;
        background: #f0fdf4 !important;
        color: #15803d !important;
    }
    .vf-btn.vf-archive.active {
        background: #475569 !important;
        border-color: #475569 !important;
        color: #fff !important;
    }

    .badge-status-archived {
        display: inline-flex;
        align-items: center;
        background: #f1f5f9;
        color: #64748b;
        border: 1px solid #cbd5e1;
        font-size: 11px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .user-row-archived {
        background-color: rgba(248, 250, 252, 0.6);
    }
</style>

<script>
    let currentFilter = 'all';

    function toggleUserClearBtn(val) {
        const btn = document.getElementById('clear-user-search');
        if (btn) {
            if (val && val.trim().length > 0) {
                btn.style.setProperty('display', 'inline-flex', 'important');
            } else {
                btn.style.setProperty('display', 'none', 'important');
            }
        }
    }

    function clearUserSearch() {
        const input = document.getElementById('user-search');
        if (input) {
            input.value = '';
            toggleUserClearBtn('');
            input.focus();
            filterUsers(currentFilter);
        }
    }

    function filterUsers(filter) {
        currentFilter = filter;
        const rows = document.querySelectorAll('#users-table tbody tr[data-role]');
        const filterLabel = document.getElementById('filter-label');

        // Update active button styling
        document.querySelectorAll('.vf-btn').forEach(btn => btn.classList.remove('active'));
        const activeBtn = document.getElementById('filter-btn-' + filter);
        if (activeBtn) activeBtn.classList.add('active');

        // Filter label map
        const labelMap = {
            'all': 'active',
            'admin': 'Admin',
            'dispatcher': 'Dispatcher',
            'archived': 'Archived'
        };

        const searchInput = document.getElementById('user-search');
        const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
        toggleUserClearBtn(searchQuery);

        let visibleCount = 0;
        rows.forEach(row => {
            let show = false;
            const role = row.getAttribute('data-role');
            const status = row.getAttribute('data-status') || 'active';

            if (filter === 'all') {
                show = (status !== 'archived');
            } else if (filter === 'admin') {
                show = (status !== 'archived') && (role === 'super_admin' || role === 'admin');
            } else if (filter === 'dispatcher') {
                show = (status !== 'archived') && (role === 'staff');
            } else if (filter === 'archived') {
                show = (status === 'archived');
            }

            if (show && searchQuery) {
                const textContent = row.textContent.toLowerCase();
                if (!textContent.includes(searchQuery)) {
                    show = false;
                }
            }

            if (show) {
                row.classList.remove('user-row-hidden');
                visibleCount++;
            } else {
                row.classList.add('user-row-hidden');
            }
        });

        // Re-number visible rows
        let num = 1;
        rows.forEach(row => {
            if (!row.classList.contains('user-row-hidden')) {
                const rowNumCell = row.querySelector('.row-number');
                if (rowNumCell) rowNumCell.textContent = num++;
            }
        });

        // Update URL tab parameter for persistence
        const newUrl = new URL(window.location);
        if (filter === 'all') {
            newUrl.searchParams.delete('tab');
        } else {
            newUrl.searchParams.set('tab', filter);
        }
        window.history.replaceState({}, '', newUrl);

        // Update single deactivate forms redirect_tab
        document.querySelectorAll('.user-deact-redirect-tab').forEach(el => el.value = filter);

        // Reset selection & exit select mode whenever filter changes
        toggleUserSelectMode(false);

        // Update filter label text
        if (filter === 'all') {
            filterLabel.innerHTML = 'Showing all <strong>' + visibleCount + '</strong> active users';
        } else if (filter === 'archived') {
            filterLabel.innerHTML = 'Showing <strong>' + visibleCount + '</strong> archived user' + (visibleCount !== 1 ? 's' : '');
        } else {
            filterLabel.innerHTML = 'Showing <strong>' + visibleCount + '</strong> ' + labelMap[filter] + ' user' + (visibleCount !== 1 ? 's' : '');
        }

        // Handle empty state
        let emptyRow = document.querySelector('#users-table .no-filter-results');
        if (visibleCount === 0 && rows.length > 0) {
            if (!emptyRow) {
                emptyRow = document.createElement('tr');
                emptyRow.classList.add('no-filter-results');
                emptyRow.innerHTML = '<td colspan="6" class="text-center py-5 text-muted empty-state-table"><i class="bi bi-search fs-1 d-block mb-3 opacity-50"></i><div class="fw-bold fs-6 empty-state-title">No users match the selected filter</div><small class="empty-state-subtitle">Try selecting a different role or clearing your search.</small></td>';
                document.querySelector('#users-table tbody').appendChild(emptyRow);
            }
            emptyRow.style.removeProperty('display');
            emptyRow.classList.remove('d-none');
        } else if (emptyRow) {
            emptyRow.remove();
        }
    }

    // Selection mode state & toggler for users
    let isUserSelectMode = false;
    function toggleUserSelectMode(forceState) {
        if (typeof forceState === 'boolean') {
            isUserSelectMode = forceState;
        } else {
            isUserSelectMode = !isUserSelectMode;
        }
        const table = document.getElementById('users-table');
        const toolbar = document.getElementById('user-bulk-toolbar');
        const btnText = document.getElementById('btn-select-users-text');
        const btn = document.getElementById('btn-toggle-select-users');
        const bulkCells = document.querySelectorAll('#users-table .bulk-col');

        if (isUserSelectMode) {
            if (table) table.classList.add('selection-mode-active');
            bulkCells.forEach(function(c) {
                c.style.setProperty('display', 'table-cell', 'important');
            });
            if (toolbar) {
                toolbar.classList.add('is-visible');
                toolbar.style.setProperty('display', 'flex', 'important');
            }
            if (btnText) btnText.textContent = 'Exit Select';
            if (btn) btn.classList.add('active');
            updateUserBulkToolbar();
        } else {
            if (table) table.classList.remove('selection-mode-active');
            bulkCells.forEach(function(c) {
                c.style.setProperty('display', 'none', 'important');
            });
            if (toolbar) {
                toolbar.classList.remove('is-visible');
                toolbar.style.setProperty('display', 'none', 'important');
            }
            if (btnText) btnText.textContent = 'Select';
            if (btn) btn.classList.remove('active');
            clearUserSelection();
        }
    }

    // Bulk selection handlers for users
    function updateUserBulkToolbar() {
        const checkedBoxes = Array.from(document.querySelectorAll('.user-row-checkbox:checked'));
        const toolbar = document.getElementById('user-bulk-toolbar');
        const countEl = document.getElementById('user-selected-count');
        const textEl = document.getElementById('user-selected-text');
        const btnDeact = document.getElementById('btn-bulk-deactivate-users');
        const btnAct = document.getElementById('btn-bulk-activate-users');
        const btnDel = document.getElementById('btn-bulk-delete-users');

        if (!toolbar) return;

        const count = checkedBoxes.length;
        if (countEl) countEl.textContent = count;
        if (textEl) textEl.textContent = (count === 1 ? 'user selected' : 'users selected');

        const hasSelection = count > 0;

        if (currentFilter === 'archived') {
            if (btnDeact) btnDeact.style.setProperty('display', 'none', 'important');
            if (btnAct) {
                btnAct.style.setProperty('display', 'inline-flex', 'important');
                btnAct.disabled = !hasSelection;
            }
            if (btnDel) {
                btnDel.style.setProperty('display', 'inline-flex', 'important');
                btnDel.disabled = !hasSelection;
            }
        } else {
            if (btnDeact) {
                btnDeact.style.setProperty('display', 'inline-flex', 'important');
                btnDeact.disabled = !hasSelection;
            }
            if (btnAct) btnAct.style.setProperty('display', 'none', 'important');
            if (btnDel) btnDel.style.setProperty('display', 'none', 'important');
        }

        const selectAll = document.getElementById('select-all-users');
        const tableHeadSelectAll = document.getElementById('table-head-select-all-users');
        const visibleCheckboxes = Array.from(document.querySelectorAll('#users-table tbody tr[data-role]'))
            .filter(tr => !tr.classList.contains('user-row-hidden'))
            .map(tr => tr.querySelector('.user-row-checkbox'))
            .filter(cb => cb && !cb.disabled);

        const allChecked = visibleCheckboxes.length > 0 && visibleCheckboxes.every(cb => cb.checked);
        const someChecked = visibleCheckboxes.some(cb => cb.checked) && !allChecked;

        if (selectAll) {
            selectAll.checked = allChecked;
            selectAll.indeterminate = someChecked;
        }
        if (tableHeadSelectAll) {
            tableHeadSelectAll.checked = allChecked;
            tableHeadSelectAll.indeterminate = someChecked;
        }
    }

    function clearUserSelection() {
        document.querySelectorAll('.user-row-checkbox').forEach(cb => cb.checked = false);
        const selectAll = document.getElementById('select-all-users');
        if (selectAll) { selectAll.checked = false; selectAll.indeterminate = false; }
        updateUserBulkToolbar();
    }

    function openUserBulkModal(action) {
        const checkedBoxes = Array.from(document.querySelectorAll('.user-row-checkbox:checked'));
        if (checkedBoxes.length === 0) return;

        const modalEl = document.getElementById('userBulkConfirmModal');
        const actionInput = document.getElementById('userBulkActionInput');
        const redirectInput = document.getElementById('userBulkRedirectTab');
        const idsContainer = document.getElementById('userBulkIdsContainer');
        const titleEl = document.getElementById('userBulkConfirmTitle');
        const descEl = document.getElementById('userBulkConfirmDesc');
        const listEl = document.getElementById('userBulkSelectedList');
        const stripeEl = document.getElementById('userBulkStripe');
        const iconBadge = document.getElementById('userBulkIconBadge');
        const iconEl = document.getElementById('userBulkIcon');
        const submitBtn = document.getElementById('userBulkSubmitBtn');

        if (actionInput) actionInput.value = action;
        if (redirectInput) redirectInput.value = currentFilter;

        if (idsContainer) {
            idsContainer.innerHTML = '';
            checkedBoxes.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = cb.value;
                idsContainer.appendChild(input);
            });
        }

        if (listEl) {
            listEl.innerHTML = checkedBoxes.map(cb => {
                const name = cb.getAttribute('data-name') || 'User';
                const role = cb.getAttribute('data-role') || '';
                return `<span class="badge bg-light text-dark border me-1 mb-1" style="font-size: 13px; font-weight: 600;"><i class="bi bi-person me-1"></i>${name} (${role})</span>`;
            }).join(' ');
        }

        const count = checkedBoxes.length;

        if (action === 'deactivate') {
            if (titleEl) titleEl.textContent = `Deactivate ${count} Selected User${count > 1 ? 's' : ''}?`;
            if (descEl) descEl.textContent = `Are you sure you want to deactivate these ${count} users? They will be unable to log in and moved to archive.`;
            if (stripeEl) stripeEl.style.background = 'linear-gradient(90deg, #f59e0b 0%, #d97706 100%)';
            if (iconBadge) {
                iconBadge.style.background = '#fef3c7';
                iconBadge.style.color = '#d97706';
            }
            if (iconEl) iconEl.className = 'bi bi-person-slash';
            if (submitBtn) {
                submitBtn.style.background = 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)';
                submitBtn.textContent = 'Yes, Deactivate All';
            }
        } else if (action === 'activate') {
            if (titleEl) titleEl.textContent = `Activate ${count} Selected User${count > 1 ? 's' : ''}?`;
            if (descEl) descEl.textContent = `Are you sure you want to reactivate these ${count} users? They will be restored from archive and regain system access.`;
            if (stripeEl) stripeEl.style.background = 'linear-gradient(90deg, #10b981 0%, #059669 100%)';
            if (iconBadge) {
                iconBadge.style.background = '#ecfdf5';
                iconBadge.style.color = '#059669';
            }
            if (iconEl) iconEl.className = 'bi bi-person-check-fill';
            if (submitBtn) {
                submitBtn.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
                submitBtn.textContent = 'Yes, Activate All';
            }
        } else if (action === 'delete') {
            if (titleEl) titleEl.textContent = `Permanently Delete ${count} User${count > 1 ? 's' : ''}?`;
            if (descEl) descEl.textContent = `Are you sure you want to permanently delete these ${count} users from the system? All associated user records will be deleted. This action CANNOT be undone.`;
            if (stripeEl) stripeEl.style.background = 'linear-gradient(90deg, #ef4444 0%, #dc2626 100%)';
            if (iconBadge) {
                iconBadge.style.background = '#fee2e2';
                iconBadge.style.color = '#dc2626';
            }
            if (iconEl) iconEl.className = 'bi bi-trash-fill';
            if (submitBtn) {
                submitBtn.style.background = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
                submitBtn.textContent = 'Yes, Permanently Delete All';
            }
        }

        if (modalEl && window.bootstrap) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    // Initialize filter on DOMContentLoaded or restore from ?tab=
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const initialTab = urlParams.get('tab') || 'all';
        filterUsers(initialTab);

        // Select All listeners (both in top bar and table header)
        function handleUserSelectAll(isChecked) {
            const rows = document.querySelectorAll('#users-table tbody tr[data-role]');
            rows.forEach(tr => {
                if (!tr.classList.contains('user-row-hidden')) {
                    const cb = tr.querySelector('.user-row-checkbox');
                    if (cb && !cb.disabled) cb.checked = isChecked;
                }
            });
            updateUserBulkToolbar();
        }

        const selectAll = document.getElementById('select-all-users');
        if (selectAll) {
            selectAll.addEventListener('change', function() { handleUserSelectAll(this.checked); });
        }
        const tableHeadSelectAll = document.getElementById('table-head-select-all-users');
        if (tableHeadSelectAll) {
            tableHeadSelectAll.addEventListener('change', function() { handleUserSelectAll(this.checked); });
        }

        // Delegate row checkbox change listener
        const tableBody = document.querySelector('#users-table tbody');
        if (tableBody) {
            tableBody.addEventListener('change', function(e) {
                if (e.target && e.target.classList.contains('user-row-checkbox')) {
                    updateUserBulkToolbar();
                }
            });
        }
    });
</script>

<?php if (!empty($users)): ?>
<!-- ══════════════════════════════════════════════════ -->
<!--  User Avatar Management Modal                      -->
<!-- ══════════════════════════════════════════════════ -->
<div class="modal fade" id="userAvatarModal" tabindex="-1" aria-labelledby="userAvatarModalLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px; margin: 1.75rem auto;">
        <div class="modal-content" style="border-radius: 18px; overflow: hidden; border: none; box-shadow: 0 20px 35px -5px rgba(15, 23, 42, 0.25);">
            <div style="height: 4px; width: 100%; background: linear-gradient(90deg, #b71c1c 0%, #dc2626 50%, #ef4444 100%);"></div>
            <div class="modal-header border-0 pb-0 px-4 pt-4" style="border-bottom: none !important; align-items: flex-start;">
                <h5 class="modal-title fw-bold" id="userAvatarModalLabel" style="font-size: 1.2rem; color: #0f172a;">
                    <i class="bi bi-camera me-2 text-danger"></i>Change Profile Photo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="margin-top: -8px; position: relative; z-index: 15;"></button>
            </div>
            <div class="modal-body text-center px-4 py-3">
                <div class="d-flex flex-column align-items-center mb-3">
                    <div id="modalAvatarPreviewCircle" style="width: 84px; height: 84px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 700; color: #fff; background: #F1F5F9; border: 2.5px solid #dc2626; box-shadow: 0 4px 14px rgba(0,0,0,0.12); overflow: hidden; margin-bottom: 12px; position: relative;">
                    </div>
                    <h6 class="fw-bold mb-0" id="modalAvatarUserName" style="font-size: 15px; color: #1e293b;">User Name</h6>
                    <span class="badge mt-1" id="modalAvatarUserRole" style="font-size: 11px; padding: 3px 8px;">Role</span>
                </div>

                <div id="modalAvatarAlert" class="alert py-2 px-3 d-none mb-3" style="font-size: 13px;"></div>

                <form id="userAvatarForm" enctype="multipart/form-data">
                    <input type="hidden" id="modalAvatarUserId" value="">
                    
                    <div class="mb-3 text-start">
                        <label for="modalAvatarInput" class="form-label fw-semibold" style="font-size: 13px; color: #475569;">
                            Select New Image <small class="text-muted">(JPG, PNG, WEBP, GIF, Max 4MB)</small>
                        </label>
                        <input class="form-control form-control-sm" type="file" id="modalAvatarInput" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif" style="border-radius: 8px;">
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 pt-0 d-flex gap-2">
                <button type="button" class="btn btn-outline-danger btn-sm d-none" id="modalRemoveAvatarBtn" style="border-radius: 8px; font-weight: 600; padding: 7px 14px; display: none !important;">
                    <i class="bi bi-trash me-1"></i> Remove Photo
                </button>
                <div class="ms-auto d-flex gap-2">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 600; padding: 7px 14px;">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" id="modalSaveAvatarBtn" style="border-radius: 8px; font-weight: 600; padding: 7px 16px; background: linear-gradient(135deg, #b71c1c 0%, #dc2626 100%); border: none;">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Save Photo
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════ -->
<!--  Remove Avatar Confirmation Modal (Warning Style)  -->
<!-- ══════════════════════════════════════════════════ -->
<div class="modal fade" id="removeAvatarConfirmModal" tabindex="-1" aria-labelledby="removeAvatarConfirmLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px; margin: 1.75rem auto;">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.18); overflow: hidden; background: #ffffff;">
            <div class="modal-body text-center" style="padding: 32px 24px 22px;">
                <!-- Icon badge: soft pink/peach rounded square with warning triangle -->
                <div style="width: 60px; height: 60px; border-radius: 16px; background-color: #fee2e2; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px;">
                    <i class="fas fa-triangle-exclamation" style="font-size: 26px; color: #1e293b;"></i>
                </div>

                <h4 id="removeAvatarConfirmLabel" style="font-weight: 700; font-size: 1.35rem; color: #1e293b; margin-bottom: 12px; letter-spacing: -0.01em;">
                    Remove Profile Photo?
                </h4>
                <p id="removeAvatarConfirmMessage" style="font-size: 0.95rem; color: #334155; line-height: 1.5; margin: 0 auto; max-width: 290px;">
                    Are you sure you want to revert your profile picture to the default avatar?
                </p>
            </div>

            <div class="modal-footer" style="border-top: 1px solid #f1f5f9; padding: 16px 24px 20px; display: flex; gap: 12px; justify-content: center; background: #ffffff;">
                <button type="button" class="btn" id="removeAvatarCancelBtn" data-bs-dismiss="modal" style="flex: 1; height: 42px; background: #ffffff; border: 1px solid #e2e8f0; color: #334155; font-weight: 600; font-size: 14.5px; border-radius: 8px; transition: all 0.15s ease;">
                    Cancel
                </button>
                <button type="button" class="btn" id="removeAvatarConfirmBtn" style="flex: 1; height: 42px; background: #e03131; border: none; color: #ffffff; font-weight: 600; font-size: 14.5px; border-radius: 8px; transition: all 0.15s ease; box-shadow: 0 2px 6px rgba(224, 49, 49, 0.25);">
                    Remove Photo
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════ -->
<!--  Deactivate User Confirmation Modal                -->
<!-- ══════════════════════════════════════════════════ -->
<div class="modal fade" id="deactivateUserConfirmModal" tabindex="-1" aria-labelledby="deactivateUserConfirmLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px; margin: 1.75rem auto;">
        <div class="modal-content deactivate-user-modal-content">
            <div class="deactivate-user-stripe"></div>

            <div class="modal-body text-center p-4">
                <div class="deactivate-user-icon-wrapper">
                    <i class="fas fa-user-slash"></i>
                </div>

                <h4 class="deactivate-user-modal-title" id="deactivateUserConfirmLabel">Deactivate User?</h4>
                <p class="deactivate-user-modal-desc" id="deactivateUserConfirmMessage">
                    Are you sure you want to deactivate this user account? Deactivated users will be moved to the archive and will no longer be able to log in.
                </p>

                <div class="deactivate-user-item-chip" id="deactivateUserItemChip">
                    <i class="bi bi-person-fill text-warning"></i>
                    <span id="deactivateUserNameLabel" class="fw-bold">User Name</span>
                    <span class="deactivate-user-role-tag" id="deactivateUserRoleTag">ROLE</span>
                </div>

                <div id="deactivateUserUsernameSubtitle" class="mt-2 text-muted" style="font-size: 0.825rem; word-break: break-all;"></div>
            </div>

            <div class="modal-footer deactivate-user-modal-footer">
                <button type="button" class="btn deactivate-user-btn-cancel" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn deactivate-user-btn-confirm" id="deactivateUserConfirmBtn">
                    <i class="fas fa-user-slash me-1"></i> Yes, Deactivate
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════ -->
<!--  Activate User Confirmation Modal                  -->
<!-- ══════════════════════════════════════════════════ -->
<div class="modal fade" id="activateUserConfirmModal" tabindex="-1" aria-labelledby="activateUserConfirmLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px; margin: 1.75rem auto;">
        <div class="modal-content activate-user-modal-content">
            <div class="activate-user-stripe"></div>

            <div class="modal-body text-center p-4">
                <div class="activate-user-icon-wrapper">
                    <i class="fas fa-user-check"></i>
                </div>

                <h4 class="activate-user-modal-title" id="activateUserConfirmLabel">Reactivate User?</h4>
                <p class="activate-user-modal-desc" id="activateUserConfirmMessage">
                    Are you sure you want to reactivate this user account? The user will be restored to active status and will regain full login access.
                </p>

                <div class="activate-user-item-chip" id="activateUserItemChip">
                    <i class="bi bi-person-check-fill text-success"></i>
                    <span id="activateUserNameLabel" class="fw-bold">User Name</span>
                    <span class="activate-user-role-tag" id="activateUserRoleTag">ROLE</span>
                </div>

                <div id="activateUserUsernameSubtitle" class="mt-2 text-muted" style="font-size: 0.825rem; word-break: break-all;"></div>
            </div>

            <div class="modal-footer activate-user-modal-footer">
                <button type="button" class="btn activate-user-btn-cancel" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn activate-user-btn-confirm" id="activateUserConfirmBtn">
                    <i class="fas fa-user-check me-1"></i> Yes, Reactivate
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════ -->
<!--  Delete User Confirmation Modal                    -->
<!-- ══════════════════════════════════════════════════ -->
<div class="modal fade" id="deleteUserConfirmModal" tabindex="-1" aria-labelledby="deleteUserConfirmLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px; margin: 1.75rem auto;">
        <div class="modal-content delete-user-modal-content">
            <!-- Accent stripe -->
            <div class="delete-user-stripe"></div>

            <div class="modal-body text-center p-4">
                <!-- Icon badge -->
                <div class="delete-user-icon-wrapper">
                    <i class="fas fa-trash-can"></i>
                </div>

                <h4 class="delete-user-modal-title" id="deleteUserConfirmLabel">Permanently Delete User?</h4>
                <p class="delete-user-modal-desc" id="deleteUserConfirmMessage">
                    Are you sure you want to permanently delete this user account from the system? This record and associated assignments will be irreversibly removed.
                </p>

                <!-- User preview chip -->
                <div class="delete-user-item-chip" id="deleteUserItemChip">
                    <i class="bi bi-person-fill text-danger"></i>
                    <span id="deleteUserNameLabel" class="fw-bold">User Name</span>
                    <span class="delete-user-role-tag" id="deleteUserRoleTag">ROLE</span>
                </div>

                <!-- Username/Email subtitle -->
                <div id="deleteUserUsernameSubtitle" class="mt-2 text-muted" style="font-size: 0.825rem; word-break: break-all;"></div>
            </div>

            <div class="modal-footer delete-user-modal-footer">
                <button type="button" class="btn delete-user-btn-cancel" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn delete-user-btn-confirm" id="deleteUserConfirmBtn">
                    <i class="fas fa-trash-alt me-1"></i> Yes, Permanently Delete
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* ── Deactivate User Confirmation Modal Styles ── */
.deactivate-user-modal-content {
    border: 0 !important;
    border-radius: 18px !important;
    box-shadow: 0 20px 35px -5px rgba(15, 23, 42, 0.25), 0 10px 15px -5px rgba(15, 23, 42, 0.1) !important;
    background: #ffffff !important;
    overflow: hidden !important;
    position: relative !important;
    font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif !important;
}
.deactivate-user-stripe {
    height: 4px;
    width: 100%;
    background: linear-gradient(90deg, #f59e0b 0%, #d97706 50%, #b45309 100%);
}
.deactivate-user-icon-wrapper {
    width: 68px;
    height: 68px;
    margin: 4px auto 18px auto;
    border-radius: 50%;
    background: #fffbeb;
    color: #d97706;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    box-shadow: 0 0 0 8px #fef3c7;
    transition: transform 0.3s ease;
}
.deactivate-user-modal-content:hover .deactivate-user-icon-wrapper {
    transform: scale(1.04);
}
.deactivate-user-modal-title {
    font-size: 1.35rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 8px;
    letter-spacing: -0.01em;
}
.deactivate-user-modal-desc {
    color: #64748b;
    font-size: 0.925rem;
    line-height: 1.5;
    margin-bottom: 16px;
    padding: 0 10px;
}
.deactivate-user-item-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: #fffbeb;
    border: 1px solid #fde68a;
    padding: 7px 14px;
    border-radius: 9999px;
    font-size: 0.825rem;
    color: #92400e;
    max-width: 100%;
    box-sizing: border-box;
    flex-wrap: wrap;
}
.deactivate-user-role-tag {
    background: #fef3c7;
    color: #b45309;
    font-size: 0.725rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 6px;
    letter-spacing: 0.3px;
    text-transform: uppercase;
    flex-shrink: 0;
}
.deactivate-user-modal-footer {
    border: 0 !important;
    padding: 0 24px 24px 24px !important;
    display: flex !important;
    gap: 12px !important;
    justify-content: stretch !important;
    background: transparent !important;
}
.deactivate-user-btn-cancel {
    flex: 1 !important;
    background: #f1f5f9 !important;
    color: #475569 !important;
    border: 1px solid #e2e8f0 !important;
    padding: 10px 18px !important;
    border-radius: 10px !important;
    font-weight: 600 !important;
    font-size: 0.9rem !important;
    transition: all 0.2s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
}
.deactivate-user-btn-cancel:hover {
    background: #e2e8f0 !important;
    color: #0f172a !important;
    border-color: #cbd5e1 !important;
}
.deactivate-user-btn-confirm {
    flex: 1 !important;
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
    color: #ffffff !important;
    border: 0 !important;
    padding: 10px 18px !important;
    border-radius: 10px !important;
    font-weight: 600 !important;
    font-size: 0.9rem !important;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.28) !important;
    transition: all 0.2s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
}
.deactivate-user-btn-confirm:hover {
    background: linear-gradient(135deg, #d97706 0%, #b45309 100%) !important;
    box-shadow: 0 6px 16px rgba(245, 158, 11, 0.38) !important;
    transform: translateY(-1px);
}
.deactivate-user-btn-confirm.is-loading {
    pointer-events: none !important;
    opacity: 0.75 !important;
}

/* ── Activate User Confirmation Modal Styles ── */
.activate-user-modal-content {
    border: 0 !important;
    border-radius: 18px !important;
    box-shadow: 0 20px 35px -5px rgba(15, 23, 42, 0.25), 0 10px 15px -5px rgba(15, 23, 42, 0.1) !important;
    background: #ffffff !important;
    overflow: hidden !important;
    position: relative !important;
    font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif !important;
}
.activate-user-stripe {
    height: 4px;
    width: 100%;
    background: linear-gradient(90deg, #10b981 0%, #059669 50%, #047857 100%);
}
.activate-user-icon-wrapper {
    width: 68px;
    height: 68px;
    margin: 4px auto 18px auto;
    border-radius: 50%;
    background: #ecfdf5;
    color: #059669;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    box-shadow: 0 0 0 8px #d1fae5;
    transition: transform 0.3s ease;
}
.activate-user-modal-content:hover .activate-user-icon-wrapper {
    transform: scale(1.04);
}
.activate-user-modal-title {
    font-size: 1.35rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 8px;
    letter-spacing: -0.01em;
}
.activate-user-modal-desc {
    color: #64748b;
    font-size: 0.925rem;
    line-height: 1.5;
    margin-bottom: 16px;
    padding: 0 10px;
}
.activate-user-item-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    padding: 7px 14px;
    border-radius: 9999px;
    font-size: 0.825rem;
    color: #065f46;
    max-width: 100%;
    box-sizing: border-box;
    flex-wrap: wrap;
}
.activate-user-role-tag {
    background: #d1fae5;
    color: #047857;
    font-size: 0.725rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 6px;
    letter-spacing: 0.3px;
    text-transform: uppercase;
    flex-shrink: 0;
}
.activate-user-modal-footer {
    border: 0 !important;
    padding: 0 24px 24px 24px !important;
    display: flex !important;
    gap: 12px !important;
    justify-content: stretch !important;
    background: transparent !important;
}
.activate-user-btn-cancel {
    flex: 1 !important;
    background: #f1f5f9 !important;
    color: #475569 !important;
    border: 1px solid #e2e8f0 !important;
    padding: 10px 18px !important;
    border-radius: 10px !important;
    font-weight: 600 !important;
    font-size: 0.9rem !important;
    transition: all 0.2s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
}
.activate-user-btn-cancel:hover {
    background: #e2e8f0 !important;
    color: #0f172a !important;
    border-color: #cbd5e1 !important;
}
.activate-user-btn-confirm {
    flex: 1 !important;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
    color: #ffffff !important;
    border: 0 !important;
    padding: 10px 18px !important;
    border-radius: 10px !important;
    font-weight: 600 !important;
    font-size: 0.9rem !important;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.28) !important;
    transition: all 0.2s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
}
.activate-user-btn-confirm:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.38) !important;
    transform: translateY(-1px);
}
.activate-user-btn-confirm.is-loading {
    pointer-events: none !important;
    opacity: 0.75 !important;
}

/* ── Delete User Confirmation Modal Styles ── */
.delete-user-modal-content {
    border: 0 !important;
    border-radius: 18px !important;
    box-shadow: 0 20px 35px -5px rgba(15, 23, 42, 0.25), 0 10px 15px -5px rgba(15, 23, 42, 0.1) !important;
    background: #ffffff !important;
    overflow: hidden !important;
    position: relative !important;
    font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif !important;
}

.delete-user-stripe {
    height: 4px;
    width: 100%;
    background: linear-gradient(90deg, #dc2626 0%, #ef4444 50%, #f97316 100%);
}

.delete-user-icon-wrapper {
    width: 68px;
    height: 68px;
    margin: 4px auto 18px auto;
    border-radius: 50%;
    background: #fef2f2;
    color: #dc2626;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    box-shadow: 0 0 0 8px #fff1f2;
    transition: transform 0.3s ease;
}

.delete-user-modal-content:hover .delete-user-icon-wrapper {
    transform: scale(1.04);
}

.delete-user-modal-title {
    font-size: 1.35rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 8px;
    letter-spacing: -0.01em;
}

.delete-user-modal-desc {
    color: #64748b;
    font-size: 0.925rem;
    line-height: 1.5;
    margin-bottom: 16px;
    padding: 0 10px;
}

.delete-user-item-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 7px 14px;
    border-radius: 9999px;
    font-size: 0.825rem;
    color: #334155;
    max-width: 100%;
    box-sizing: border-box;
    flex-wrap: wrap;
}

.delete-user-item-chip i {
    font-size: 0.95rem;
    flex-shrink: 0;
}

.delete-user-role-tag {
    background: #fee2e2;
    color: #b91c1c;
    font-size: 0.725rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 6px;
    letter-spacing: 0.3px;
    text-transform: uppercase;
    flex-shrink: 0;
}

.delete-user-modal-footer {
    border: 0 !important;
    padding: 0 24px 24px 24px !important;
    display: flex !important;
    gap: 12px !important;
    justify-content: stretch !important;
    background: transparent !important;
}

.delete-user-btn-cancel {
    flex: 1 !important;
    background: #f1f5f9 !important;
    color: #475569 !important;
    border: 1px solid #e2e8f0 !important;
    padding: 10px 18px !important;
    border-radius: 10px !important;
    font-weight: 600 !important;
    font-size: 0.9rem !important;
    transition: all 0.2s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.delete-user-btn-cancel:hover,
.delete-user-btn-cancel:focus {
    background: #e2e8f0 !important;
    color: #0f172a !important;
    border-color: #cbd5e1 !important;
}

.delete-user-btn-confirm {
    flex: 1 !important;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
    color: #ffffff !important;
    border: 0 !important;
    padding: 10px 18px !important;
    border-radius: 10px !important;
    font-weight: 600 !important;
    font-size: 0.9rem !important;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.28) !important;
    transition: all 0.2s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.delete-user-btn-confirm:hover,
.delete-user-btn-confirm:focus {
    background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%) !important;
    box-shadow: 0 6px 16px rgba(220, 38, 38, 0.38) !important;
    transform: translateY(-1px);
}

.delete-user-btn-confirm.is-loading {
    pointer-events: none !important;
    opacity: 0.75 !important;
}

/* Ensure modal paints above fixed navbar */
body.modal-open #userAvatarModal,
#userAvatarModal,
body.modal-open #deactivateUserConfirmModal,
#deactivateUserConfirmModal,
body.modal-open #activateUserConfirmModal,
#activateUserConfirmModal,
body.modal-open #deleteUserConfirmModal,
#deleteUserConfirmModal {
    z-index: 100050 !important;
}

/* User Avatar modal header and close button adjustment */
#userAvatarModal .modal-header {
    border-bottom: none !important;
    padding-bottom: 0 !important;
    align-items: flex-start !important;
}
#userAvatarModal .modal-header .btn-close {
    margin-top: -8px !important;
    margin-right: -4px !important;
    position: relative !important;
    z-index: 15 !important;
}

/* Remove avatar confirmation layered directly over userAvatarModal */
#removeAvatarConfirmModal {
    z-index: 100065 !important;
}
#removeAvatarConfirmModal .modal-dialog {
    z-index: 100070 !important;
}
</style>

<script>
(function() {
    var _deleteUserFormId = null;
    var _deactivateUserFormId = null;
    var _activateUserFormId = null;
    var _avatarModalUser = null;
    var _reopenAvatarModalOnCancel = false;
    var DEFAULT_AVATAR_SVG = '<?= str_replace(["\r", "\n"], '', $defaultAvatarSvg) ?>';

    // ── Avatar Management Modal ──
    window.openAvatarModal = function(userId, userName, currentImgUrl, initials, role) {
        var wrapper = document.getElementById('avatar-wrapper-' + userId);
        if (wrapper) {
            if (wrapper.getAttribute('data-img-url') !== null && wrapper.getAttribute('data-img-url') !== '') {
                currentImgUrl = wrapper.getAttribute('data-img-url');
            } else if (wrapper.getAttribute('data-img-url') === '') {
                currentImgUrl = '';
            }
            if (!userName && wrapper.getAttribute('data-user-name')) {
                userName = wrapper.getAttribute('data-user-name');
            }
            if (!role && wrapper.getAttribute('data-role')) {
                role = wrapper.getAttribute('data-role');
            }
            if (!initials && wrapper.getAttribute('data-initials')) {
                initials = wrapper.getAttribute('data-initials');
            }
        }

        var currentUserId = <?= (int)session()->get('id') ?>;
        if (parseInt(userId, 10) === currentUserId) {
            var tImg = document.querySelector('#navProfileTriggerAvatar img');
            if (tImg && tImg.src) {
                currentImgUrl = tImg.src;
            } else if (!tImg && wrapper && wrapper.getAttribute('data-img-url') === '') {
                currentImgUrl = '';
            }
        }

        _avatarModalUser = {
            id: userId,
            name: userName,
            imgUrl: currentImgUrl,
            initials: initials,
            role: role
        };
        window._avatarModalUser = _avatarModalUser;

        var modalEl = document.getElementById('userAvatarModal');
        if (!modalEl || !window.bootstrap) return;

        var userIdInput = document.getElementById('modalAvatarUserId');
        if (userIdInput) userIdInput.value = userId;

        var nameEl = document.getElementById('modalAvatarUserName');
        if (nameEl) nameEl.textContent = userName;

        var roleBadge = document.getElementById('modalAvatarUserRole');
        if (roleBadge) {
            if (role === 'super_admin') {
                roleBadge.textContent = 'Super Admin';
                roleBadge.className = 'badge profile-role-pill pill-super_admin';
            } else if (role === 'admin') {
                roleBadge.textContent = 'Admin';
                roleBadge.className = 'badge profile-role-pill pill-admin';
            } else {
                roleBadge.textContent = 'Dispatcher';
                roleBadge.className = 'badge profile-role-pill pill-staff';
            }
        }

        updateModalAvatarPreview(currentImgUrl, initials, role);

        var fileInput = document.getElementById('modalAvatarInput');
        if (fileInput) fileInput.value = '';

        var alertEl = document.getElementById('modalAvatarAlert');
        if (alertEl) {
            alertEl.className = 'alert py-2 px-3 d-none mb-3';
            alertEl.textContent = '';
        }

        var removeBtn = document.getElementById('modalRemoveAvatarBtn');
        if (removeBtn) {
            if (currentImgUrl && currentImgUrl.length > 0) {
                removeBtn.classList.remove('d-none');
                removeBtn.style.setProperty('display', 'inline-flex', 'important');
            } else {
                removeBtn.classList.add('d-none');
                removeBtn.style.setProperty('display', 'none', 'important');
            }
        }

        var saveBtn = document.getElementById('modalSaveAvatarBtn');
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="bi bi-cloud-arrow-up-fill me-1"></i> Save Photo';
        }

        window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
    };

    window.updateModalAvatarPreview = function(imgUrl, initials, role) {
        var previewEl = document.getElementById('modalAvatarPreviewCircle');
        if (!previewEl) return;

        var borderColor = role === 'super_admin' ? '#B71C1C' : (role === 'admin' ? '#dc2626' : '#15803d');
        previewEl.style.border = '2.5px solid ' + borderColor;
        previewEl.style.background = '#F1F5F9';

        if (imgUrl && imgUrl.length > 0) {
            previewEl.innerHTML = '<img src="' + imgUrl + '" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">';
        } else {
            previewEl.innerHTML = DEFAULT_AVATAR_SVG;
        }
    };
    function updateModalAvatarPreview(imgUrl, initials, role) {
        window.updateModalAvatarPreview(imgUrl, initials, role);
    }

    function showAvatarAlert(msg, type) {
        var alertEl = document.getElementById('modalAvatarAlert');
        if (!alertEl) return;
        alertEl.className = 'alert alert-' + type + ' py-2 px-3 mb-3';
        alertEl.textContent = msg;
    }

    // ── Deactivate User Modal ──
    window.showDeactivateUserModal = function(opts) {
        opts = opts || {};
        var modalEl = document.getElementById('deactivateUserConfirmModal');
        if (!modalEl || !window.bootstrap) {
            if (window.confirm('Deactivate user ' + (opts.username || '') + '?')) {
                var form = document.getElementById(opts.formId);
                if (form) form.submit();
            }
            return;
        }

        var nameEl = document.getElementById('deactivateUserNameLabel');
        var roleTagEl = document.getElementById('deactivateUserRoleTag');
        var subEl = document.getElementById('deactivateUserUsernameSubtitle');
        var chipEl = document.getElementById('deactivateUserItemChip');
        var iconEl = chipEl ? chipEl.querySelector('i') : null;

        if (nameEl) nameEl.textContent = opts.name || opts.username || 'User';
        if (roleTagEl) {
            if (opts.role) {
                roleTagEl.textContent = opts.role;
                roleTagEl.style.display = '';
            } else {
                roleTagEl.style.display = 'none';
            }
        }
        if (subEl) {
            if (opts.username) {
                subEl.textContent = '@' + opts.username;
                subEl.style.display = '';
            } else {
                subEl.style.display = 'none';
            }
        }

        // Dynamic Role-colored chip styling
        var rawRole = opts.rawRole || '';
        if (rawRole === 'staff') {
            if (chipEl) {
                chipEl.style.background = '#f0fdf4';
                chipEl.style.borderColor = '#bbf7d0';
                chipEl.style.color = '#166534';
            }
            if (roleTagEl) {
                roleTagEl.style.background = '#dcfce7';
                roleTagEl.style.color = '#15803d';
            }
            if (iconEl) {
                iconEl.className = 'bi bi-person-fill';
                iconEl.style.color = '#15803d';
            }
        } else if (rawRole === 'super_admin' || rawRole === 'admin') {
            var rColor = (rawRole === 'super_admin') ? '#b71c1c' : '#dc2626';
            if (chipEl) {
                chipEl.style.background = '#fef2f2';
                chipEl.style.borderColor = '#fecaca';
                chipEl.style.color = '#991b1b';
            }
            if (roleTagEl) {
                roleTagEl.style.background = '#fee2e2';
                roleTagEl.style.color = rColor;
            }
            if (iconEl) {
                iconEl.className = 'bi bi-person-fill';
                iconEl.style.color = rColor;
            }
        } else {
            if (chipEl) {
                chipEl.style.background = '#fffbeb';
                chipEl.style.borderColor = '#fde68a';
                chipEl.style.color = '#92400e';
            }
            if (roleTagEl) {
                roleTagEl.style.background = '#fef3c7';
                roleTagEl.style.color = '#b45309';
            }
            if (iconEl) {
                iconEl.className = 'bi bi-person-fill text-warning';
                iconEl.style.color = '';
            }
        }

        var confirmBtn = document.getElementById('deactivateUserConfirmBtn');
        if (confirmBtn) {
            confirmBtn.classList.remove('is-loading');
            confirmBtn.innerHTML = '<i class="fas fa-user-slash me-1"></i> Yes, Deactivate';
        }

        _deactivateUserFormId = opts.formId || null;
        window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
    };

    // ── Activate User Modal ──
    window.showActivateUserModal = function(opts) {
        opts = opts || {};
        var modalEl = document.getElementById('activateUserConfirmModal');
        if (!modalEl || !window.bootstrap) {
            if (window.confirm('Reactivate user ' + (opts.username || '') + '?')) {
                var form = document.getElementById(opts.formId);
                if (form) form.submit();
            }
            return;
        }

        var nameEl = document.getElementById('activateUserNameLabel');
        var roleTagEl = document.getElementById('activateUserRoleTag');
        var subEl = document.getElementById('activateUserUsernameSubtitle');
        var chipEl = document.getElementById('activateUserItemChip');
        var iconEl = chipEl ? chipEl.querySelector('i') : null;

        if (nameEl) nameEl.textContent = opts.name || opts.username || 'User';
        if (roleTagEl) {
            if (opts.role) {
                roleTagEl.textContent = opts.role;
                roleTagEl.style.display = '';
            } else {
                roleTagEl.style.display = 'none';
            }
        }
        if (subEl) {
            if (opts.username) {
                subEl.textContent = '@' + opts.username;
                subEl.style.display = '';
            } else {
                subEl.style.display = 'none';
            }
        }

        var rawRole = opts.rawRole || '';
        if (rawRole === 'staff') {
            if (chipEl) {
                chipEl.style.background = '#f0fdf4';
                chipEl.style.borderColor = '#bbf7d0';
                chipEl.style.color = '#166534';
            }
            if (roleTagEl) {
                roleTagEl.style.background = '#dcfce7';
                roleTagEl.style.color = '#15803d';
            }
            if (iconEl) {
                iconEl.className = 'bi bi-person-check-fill';
                iconEl.style.color = '#15803d';
            }
        } else if (rawRole === 'super_admin' || rawRole === 'admin') {
            var rColor = (rawRole === 'super_admin') ? '#b71c1c' : '#dc2626';
            if (chipEl) {
                chipEl.style.background = '#fef2f2';
                chipEl.style.borderColor = '#fecaca';
                chipEl.style.color = '#991b1b';
            }
            if (roleTagEl) {
                roleTagEl.style.background = '#fee2e2';
                roleTagEl.style.color = rColor;
            }
            if (iconEl) {
                iconEl.className = 'bi bi-person-check-fill';
                iconEl.style.color = rColor;
            }
        }

        var confirmBtn = document.getElementById('activateUserConfirmBtn');
        if (confirmBtn) {
            confirmBtn.classList.remove('is-loading');
            confirmBtn.innerHTML = '<i class="fas fa-user-check me-1"></i> Yes, Reactivate';
        }

        _activateUserFormId = opts.formId || null;
        window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
    };

    // ── Delete User Modal ──
    window.showDeleteUserModal = function(opts) {
        opts = opts || {};
        var modalEl = document.getElementById('deleteUserConfirmModal');
        if (!modalEl || !window.bootstrap) {
            if (window.confirm('Permanently delete user ' + (opts.username || '') + '?')) {
                var form = document.getElementById(opts.formId);
                if (form) form.submit();
            }
            return;
        }

        var nameEl = document.getElementById('deleteUserNameLabel');
        var roleTagEl = document.getElementById('deleteUserRoleTag');
        var subEl = document.getElementById('deleteUserUsernameSubtitle');

        if (nameEl) {
            nameEl.textContent = opts.name || opts.username || 'User';
        }

        if (roleTagEl) {
            if (opts.role) {
                roleTagEl.textContent = opts.role;
                roleTagEl.style.display = '';
            } else {
                roleTagEl.style.display = 'none';
            }
        }

        if (subEl) {
            if (opts.username) {
                subEl.textContent = '@' + opts.username;
                subEl.style.display = '';
            } else {
                subEl.style.display = 'none';
            }
        }

        var confirmBtn = document.getElementById('deleteUserConfirmBtn');
        if (confirmBtn) {
            confirmBtn.classList.remove('is-loading');
            confirmBtn.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Yes, Permanently Delete';
        }

        _deleteUserFormId = opts.formId || null;
        window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
    };

    document.addEventListener('DOMContentLoaded', function() {
        function getAdminUsersCsrfToken() {
            var meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.getAttribute('content') : '';
        }

        function updateAdminUsersCsrfToken(data) {
            if (!data || !data.csrf_hash) return;
            var meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) meta.setAttribute('content', data.csrf_hash);
        }

        // Avatar file change preview
        var fileInput = document.getElementById('modalAvatarInput');
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                var file = this.files[0];
                if (file) {
                    if (file.size > 4 * 1024 * 1024) {
                        showAvatarAlert('File exceeds 4MB maximum allowed size.', 'danger');
                        this.value = '';
                        return;
                    }
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        updateModalAvatarPreview(e.target.result, _avatarModalUser ? _avatarModalUser.initials : '', _avatarModalUser ? _avatarModalUser.role : '');
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // Avatar Save button
        var saveAvatarBtn = document.getElementById('modalSaveAvatarBtn');
        if (saveAvatarBtn) {
            saveAvatarBtn.addEventListener('click', function() {
                var fInput = document.getElementById('modalAvatarInput');
                if (!fInput || !fInput.files || fInput.files.length === 0) {
                    showAvatarAlert('Please choose an image file first.', 'warning');
                    return;
                }

                var userId = document.getElementById('modalAvatarUserId').value;
                var formData = new FormData();
                formData.append('avatar', fInput.files[0]);
                var csrfToken = getAdminUsersCsrfToken();
                if (csrfToken) formData.append('csrf_test_name', csrfToken);

                saveAvatarBtn.disabled = true;
                saveAvatarBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

                fetch('<?= base_url('admin/users/upload-avatar') ?>/' + userId, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    updateAdminUsersCsrfToken(data);
                    saveAvatarBtn.disabled = false;
                    saveAvatarBtn.innerHTML = '<i class="bi bi-cloud-arrow-up-fill me-1"></i> Save Photo';
                    if (data.success) {
                        var newImgUrl = data.image_url;
                        var rowCircle = document.getElementById('avatar-circle-' + userId);
                        if (rowCircle) {
                            rowCircle.innerHTML = '<img src="' + newImgUrl + '" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">';
                        }
                        var wrapper = document.getElementById('avatar-wrapper-' + userId);
                        if (wrapper) {
                            wrapper.setAttribute('data-img-url', newImgUrl);
                        }
                        if (_avatarModalUser) _avatarModalUser.imgUrl = newImgUrl;
                        updateModalAvatarPreview(newImgUrl, _avatarModalUser ? _avatarModalUser.initials : '', _avatarModalUser ? _avatarModalUser.role : '');

                        var rBtn = document.getElementById('modalRemoveAvatarBtn');
                        if (rBtn) {
                            rBtn.classList.remove('d-none');
                            rBtn.style.setProperty('display', 'inline-flex', 'important');
                        }

                        var fInputClear = document.getElementById('modalAvatarInput');
                        if (fInputClear) fInputClear.value = '';

                        if (parseInt(userId, 10) === <?= (int)session()->get('id') ?>) {
                            if (typeof window.updateAvatarDom === 'function') {
                                window.updateAvatarDom(newImgUrl);
                            } else {
                                var imgHtml = '<img src="' + newImgUrl + '" alt="Avatar" class="profile-avatar-img user-avatar-preview">';
                                var tAvatar = document.getElementById('navProfileTriggerAvatar');
                                if (tAvatar) tAvatar.innerHTML = imgHtml;
                                var hAvatar = document.getElementById('navProfileHeaderAvatar');
                                if (hAvatar) hAvatar.innerHTML = imgHtml;
                                var dAvatar = document.querySelector('.drawer-user-avatar');
                                if (dAvatar) dAvatar.innerHTML = imgHtml;
                            }
                        }

                        if (typeof window.notifyAvatarSync === 'function') {
                            window.notifyAvatarSync(userId, newImgUrl);
                        }

                        // Close modal immediately without delay
                        var modalEl = document.getElementById('userAvatarModal');
                        if (modalEl && window.bootstrap) {
                            window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                        }
                    } else {
                        showAvatarAlert(data.message || 'Failed to update avatar.', 'danger');
                    }
                })
                .catch(function() {
                    saveAvatarBtn.disabled = false;
                    saveAvatarBtn.innerHTML = '<i class="bi bi-cloud-arrow-up-fill me-1"></i> Save Photo';
                    showAvatarAlert('An error occurred during upload. Please try again.', 'danger');
                });
            });
        }

        // Avatar Remove button -> opens in-system confirmation modal
        var removeAvatarBtn = document.getElementById('modalRemoveAvatarBtn');
        var removeConfirmModalEl = document.getElementById('removeAvatarConfirmModal');

        if (removeAvatarBtn) {
            removeAvatarBtn.addEventListener('click', function() {
                if (!removeConfirmModalEl || !window.bootstrap) {
                    if (window.confirm('Are you sure you want to revert your profile picture to the default avatar?')) {
                        executeAvatarRemoval();
                    }
                    return;
                }

                var confirmBtn = document.getElementById('removeAvatarConfirmBtn');
                if (confirmBtn) {
                    confirmBtn.classList.remove('is-loading');
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = 'Remove Photo';
                }

                // Show confirmation modal on top of userAvatarModal (DO NOT hide userAvatarModal)
                window.bootstrap.Modal.getOrCreateInstance(removeConfirmModalEl).show();
            });
        }

        // Multi-modal backdrop layering and body scroll retention
        if (removeConfirmModalEl) {
            removeConfirmModalEl.addEventListener('show.bs.modal', function() {
                setTimeout(function() {
                    var backdrops = document.querySelectorAll('.modal-backdrop');
                    if (backdrops.length > 1) {
                        backdrops[backdrops.length - 1].style.zIndex = '100060';
                    }
                }, 10);
            });

            removeConfirmModalEl.addEventListener('hidden.bs.modal', function() {
                var avatarModalEl = document.getElementById('userAvatarModal');
                if (avatarModalEl && avatarModalEl.classList.contains('show')) {
                    document.body.classList.add('modal-open');
                    document.body.style.overflow = 'hidden';
                }
            });
        }

        function executeAvatarRemoval() {
            var userId = document.getElementById('modalAvatarUserId').value;
            var csrfToken = getAdminUsersCsrfToken();
            var formData = new FormData();
            if (csrfToken) formData.append('csrf_test_name', csrfToken);

            var confirmBtn = document.getElementById('removeAvatarConfirmBtn');
            if (confirmBtn) {
                confirmBtn.classList.add('is-loading');
                confirmBtn.disabled = true;
                confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Removing...';
            }

            fetch('<?= base_url('admin/users/remove-avatar') ?>/' + userId, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                updateAdminUsersCsrfToken(data);
                if (confirmBtn) {
                    confirmBtn.classList.remove('is-loading');
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = 'Remove Photo';
                }

                // Close confirmation modal
                if (removeConfirmModalEl && window.bootstrap) {
                    window.bootstrap.Modal.getOrCreateInstance(removeConfirmModalEl).hide();
                }

                if (data.success) {
                    // Close the main modal as well
                    var avatarModalEl = document.getElementById('userAvatarModal');
                    if (avatarModalEl && window.bootstrap) {
                        window.bootstrap.Modal.getOrCreateInstance(avatarModalEl).hide();
                    }

                    var rowCircle = document.getElementById('avatar-circle-' + userId);
                    if (rowCircle) {
                        rowCircle.innerHTML = DEFAULT_AVATAR_SVG;
                    }
                    var wrapper = document.getElementById('avatar-wrapper-' + userId);
                    if (wrapper) {
                        wrapper.setAttribute('data-img-url', '');
                    }
                    if (_avatarModalUser) _avatarModalUser.imgUrl = '';
                    updateModalAvatarPreview('', '', _avatarModalUser ? _avatarModalUser.role : '');

                    var rBtn = document.getElementById('modalRemoveAvatarBtn');
                    if (rBtn) {
                        rBtn.classList.add('d-none');
                        rBtn.style.setProperty('display', 'none', 'important');
                    }

                    var fInputClear = document.getElementById('modalAvatarInput');
                    if (fInputClear) fInputClear.value = '';

                    if (parseInt(userId, 10) === <?= (int)session()->get('id') ?>) {
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

                    if (typeof window.notifyAvatarSync === 'function') {
                        window.notifyAvatarSync(userId, null);
                    }
                } else {
                    showAvatarAlert(data.message || 'Failed to remove photo.', 'danger');
                }
            })
            .catch(function() {
                if (confirmBtn) {
                    confirmBtn.classList.remove('is-loading');
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = 'Remove Photo';
                }
                if (removeConfirmModalEl && window.bootstrap) {
                    window.bootstrap.Modal.getOrCreateInstance(removeConfirmModalEl).hide();
                }
                showAvatarAlert('An error occurred. Please try again.', 'danger');
            });
        }

        var removeConfirmBtn = document.getElementById('removeAvatarConfirmBtn');
        if (removeConfirmBtn) {
            removeConfirmBtn.addEventListener('click', function() {
                executeAvatarRemoval();
            });
        }

        // Deactivate submit handler
        var deactConfirmBtn = document.getElementById('deactivateUserConfirmBtn');
        if (deactConfirmBtn) {
            deactConfirmBtn.addEventListener('click', function() {
                if (!_deactivateUserFormId) return;
                deactConfirmBtn.classList.add('is-loading');
                deactConfirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Deactivating...';
                var form = document.getElementById(_deactivateUserFormId);
                if (form) {
                    form.submit();
                } else {
                    var modalEl = document.getElementById('deactivateUserConfirmModal');
                    if (modalEl && window.bootstrap) {
                        window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                    }
                    deactConfirmBtn.classList.remove('is-loading');
                }
            });
        }

        // Activate submit handler
        var actConfirmBtn = document.getElementById('activateUserConfirmBtn');
        if (actConfirmBtn) {
            actConfirmBtn.addEventListener('click', function() {
                if (!_activateUserFormId) return;
                actConfirmBtn.classList.add('is-loading');
                actConfirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Reactivating...';
                var form = document.getElementById(_activateUserFormId);
                if (form) {
                    form.submit();
                } else {
                    var modalEl = document.getElementById('activateUserConfirmModal');
                    if (modalEl && window.bootstrap) {
                        window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                    }
                    actConfirmBtn.classList.remove('is-loading');
                }
            });
        }

        // Delete submit handler
        var confirmBtn = document.getElementById('deleteUserConfirmBtn');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', function() {
                if (!_deleteUserFormId) return;

                confirmBtn.classList.add('is-loading');
                confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Deleting...';

                var form = document.getElementById(_deleteUserFormId);
                if (form) {
                    form.submit();
                } else {
                    var modalEl = document.getElementById('deleteUserConfirmModal');
                    if (modalEl && window.bootstrap) {
                        window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                    }
                    confirmBtn.classList.remove('is-loading');
                    confirmBtn.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Yes, Permanently Delete';
                }
            });
        }
    });
})();
</script>
<?php endif; ?>

<?= $this->include('templates/footer') ?>