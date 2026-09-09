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
// Compute summary counts
$totalUsers = count($users);
$countAdmin = 0;
$countDispatcher = 0;
foreach ($users as $u) {
    if ($u['role'] === 'super_admin' || $u['role'] === 'admin') {
        $countAdmin++;
    } else {
        $countDispatcher++;
    }
}
?>

<!-- User Search & Filter Bar -->
<div class="modern-card shadow-modern fade-in mb-4">
    <div class="modern-card-body">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <div class="user-search-group me-3">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" class="user-search-input" id="user-search" placeholder="Search name or username..." onkeyup="filterUsers(currentFilter)">
            </div>
            
            <span style="font-size:13px; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:0.5px; margin-right:4px;">
                <i class="bi bi-funnel me-1"></i>Filter:
            </span>
            
            <button type="button" class="vf-btn active" id="filter-btn-all" onclick="filterUsers('all')">
                <i class="bi bi-grid-3x3-gap-fill"></i> All
                <span class="vf-count"><?= $totalUsers ?></span>
            </button>
            
            <span class="vf-divider" style="width:1px; height:24px; background:#dee2e6; margin:0 4px;"></span>
            
            <button type="button" class="vf-btn vf-van" id="filter-btn-admin" onclick="filterUsers('admin')">
                <i class="bi bi-shield-lock"></i> Admins
                <span class="vf-count"><?= $countAdmin ?></span>
            </button>
            
            <button type="button" class="vf-btn vf-jeepney" id="filter-btn-dispatcher" onclick="filterUsers('dispatcher')">
                <i class="bi bi-person-fill-gear"></i> Dispatchers
                <span class="vf-count"><?= $countDispatcher ?></span>
            </button>
        </div>
        
        <div id="filter-label" style="font-size:13px; color:#64748b;">Showing all <strong><?= $totalUsers ?></strong> users</div>
    </div>
</div>

<div class="modern-card shadow-modern fade-in">
    <div class="modern-card-header">
        <span class="modern-card-title">
            <i class="bi bi-people" style="color: var(--primary-red);"></i>
            User List
        </span>
    </div>
    <div class="modern-card-body">
        <div class="table-responsive">
            <table class="table-modern" id="users-table">
                <thead>
                    <tr>
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
                            <tr data-role="<?= esc($user['role']) ?>">
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
                                    ?>
                                    <div class="d-flex align-items-center user-cell-content">
                                        <div class="avatar-circle me-3 <?= $user['role'] === 'super_admin' || $user['role'] === 'admin' ? 'avatar-admin' : 'avatar-dispatcher' ?>">
                                            <?= esc($initials) ?>
                                        </div>
                                        <div class="user-info-text" style="min-width: 0; flex: 1 1 auto;">
                                            <div class="fw-bold" style="font-size:14.5px; color: inherit;"><?= esc($user['full_name']) ?></div>
                                            <div>
                                                <small style="font-size:12.5px; color: #64748b;">@<?= esc($user['username']) ?></small>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Role">
                                    <?php if ($user['role'] === 'super_admin'): ?>
                                        <span class="badge-modern badge-modern-super-admin">Super Admin</span>
                                    <?php else: ?>
                                        <span class="badge-modern badge-modern-<?= $user['role'] === 'admin' ? 'danger' : 'info' ?>">
                                            <?= $user['role'] === 'staff' ? 'Dispatcher' : 'Admin' ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Assigned Routes">
                                    <?php if ($user['role'] === 'super_admin' || $user['role'] === 'admin'): ?>
                                        <span class="badge-modern badge-modern-success">All Routes</span>
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

                                    $canEdit = ($targetUserId === $currentUserId) || 
                                               ($currentUserRole === 'super_admin') || 
                                               ($currentUserRole === 'admin' && $targetUserRole === 'staff');

                                    $canDelete = ($targetUserId !== $currentUserId) && 
                                                 ($targetUserRole !== 'super_admin') && 
                                                 (($currentUserRole === 'super_admin') || 
                                                  ($currentUserRole === 'admin' && $targetUserRole === 'staff'));
                                    ?>
                                    <div class="btn-group-modern" role="group">
                                        <?php if ($canEdit): ?>
                                            <a href="<?= base_url('admin/users/edit/' . $user['id']) ?>"
                                                class="btn-modern btn-action-edit btn-modern-sm" title="Edit">
                                                <i class="bi bi-pencil"></i> <span class="user-action-label">Edit</span>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($canDelete): ?>
                                            <form id="delete-user-form-<?= $user['id'] ?>" action="<?= base_url('admin/users/delete/' . $user['id']) ?>" method="post"
                                                class="d-inline">
                                                <?= csrf_field() ?>
                                                <button type="button" class="btn-modern btn-action-delete btn-modern-sm" title="Delete"
                                                    onclick="showDeleteUserModal({
                                                        formId: 'delete-user-form-<?= $user['id'] ?>',
                                                        userId: '<?= $user['id'] ?>',
                                                        name: '<?= esc(addslashes($user['full_name'] ?? $user['username'])) ?>',
                                                        username: '<?= esc(addslashes($user['username'] ?? '')) ?>',
                                                        role: '<?= esc(addslashes($user['role'] === 'super_admin' ? 'Super Admin' : ($user['role'] === 'admin' ? 'Admin' : 'Dispatcher'))) ?>'
                                                    })">
                                                    <i class="bi bi-trash"></i> <span class="user-action-label">Delete</span>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted empty-state-table">
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
        height: 38px !important;
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
        font-size: 14px !important;
        transition: border-color 0.2s ease;
    }

    .user-search-group .user-search-input {
        height: 38px !important;
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

    /* Avatar Circles styling */
    .avatar-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 13px;
        color: #fff;
        flex-shrink: 0;
        letter-spacing: 0.5px;
    }
    .avatar-admin {
        background: #dc2626;
        border: 2px solid rgba(220, 38, 38, 0.15);
    }
    .avatar-dispatcher {
        background: #0ea5e9;
        border: 2px solid rgba(14, 165, 233, 0.15);
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

    @media (max-width: 991.98px) {
        #users-table {
            font-size: 13px;
        }

        #users-table thead th,
        #users-table tbody td {
            padding: 0.65rem 0.5rem !important;
        }

        #users-table th:first-child,
        #users-table td.row-number {
            width: 38px;
            text-align: center;
        }

        .btn-group-modern {
            display: flex !important;
            gap: 0.35rem;
        }

        .btn-group-modern .btn-modern {
            width: 34px !important;
            height: 34px;
            padding: 0 !important;
            border-radius: 6px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .user-action-label {
            display: none;
        }
    }

    @media (max-width: 575.98px) {
        .vf-divider {
            display: none !important;
        }

        .vf-btn {
            flex: 1 1 calc(50% - 0.5rem);
            justify-content: center;
        }
    }

    /* Filter capsule buttons — matches Vehicles Register page */
    .vf-btn {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        padding: 6px 14px !important;
        border-radius: 20px !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        font-family: 'Outfit', sans-serif !important;
        cursor: pointer !important;
        border: 2px solid #dee2e6 !important;
        background: #fff !important;
        color: #475569 !important;
        white-space: nowrap !important;
        line-height: 1.4 !important;
        transition: border-color .15s ease, background .15s ease, color .15s ease;
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
    .vf-btn.vf-jeepney.active {
        background: #1565c0 !important;
        border-color: #1565c0 !important;
        color: #fff !important;
    }
</style>

<script>
    let currentFilter = 'all';

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
            'all': 'all',
            'admin': 'Admin',
            'dispatcher': 'Dispatcher'
        };

        const searchQuery = document.getElementById('user-search') ? document.getElementById('user-search').value.toLowerCase() : '';

        let visibleCount = 0;
        rows.forEach(row => {
            let show = false;
            const role = row.getAttribute('data-role');
            if (filter === 'all') {
                show = true;
            } else if (filter === 'admin') {
                show = role === 'super_admin' || role === 'admin';
            } else if (filter === 'dispatcher') {
                show = role === 'staff';
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

        // Update filter label text
        if (filter === 'all') {
            filterLabel.innerHTML = 'Showing all <strong>' + visibleCount + '</strong> users';
        } else {
            filterLabel.innerHTML = 'Showing <strong>' + visibleCount + '</strong> ' + labelMap[filter] + ' user' + (visibleCount !== 1 ? 's' : '');
        }

        // Handle empty state
        let emptyRow = document.querySelector('#users-table .no-filter-results');
        if (visibleCount === 0 && rows.length > 0) {
            if (!emptyRow) {
                emptyRow = document.createElement('tr');
                emptyRow.classList.add('no-filter-results');
                emptyRow.innerHTML = '<td colspan="5" class="text-center py-5 text-muted empty-state-table"><i class="bi bi-search fs-1 d-block mb-3 opacity-50"></i><div class="fw-bold fs-6 empty-state-title">No users match the selected filter</div><small class="empty-state-subtitle">Try selecting a different role or clearing your search.</small></td>';
                document.querySelector('#users-table tbody').appendChild(emptyRow);
            }
            emptyRow.style.removeProperty('display');
            emptyRow.classList.remove('d-none');
        } else if (emptyRow) {
            emptyRow.remove();
        }
    }
</script>

<?php if (!empty($users)): ?>
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

                <h4 class="delete-user-modal-title" id="deleteUserConfirmLabel">Delete User?</h4>
                <p class="delete-user-modal-desc" id="deleteUserConfirmMessage">
                    Are you sure you want to delete this user account? This action cannot be undone.
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
                    <i class="fas fa-trash-alt me-1"></i> Yes, Delete
                </button>
            </div>
        </div>
    </div>
</div>

<style>
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
body.modal-open #deleteUserConfirmModal,
#deleteUserConfirmModal {
    z-index: 100050 !important;
}

@media (max-width: 480px) {
    .delete-user-modal-footer {
        flex-direction: column-reverse !important;
        gap: 8px !important;
        padding: 0 16px 20px 16px !important;
    }
}
</style>

<script>
(function() {
    var _deleteUserFormId = null;

    window.showDeleteUserModal = function(opts) {
        opts = opts || {};
        var modalEl = document.getElementById('deleteUserConfirmModal');
        if (!modalEl || !window.bootstrap) {
            if (window.confirm('Delete user ' + (opts.username || '') + '?')) {
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
            confirmBtn.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Yes, Delete';
        }

        _deleteUserFormId = opts.formId || null;
        window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
    };

    document.addEventListener('DOMContentLoaded', function() {
        var confirmBtn = document.getElementById('deleteUserConfirmBtn');
        if (!confirmBtn) return;

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
                confirmBtn.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Yes, Delete';
            }
        });
    });
})();
</script>
<?php endif; ?>

<?= $this->include('templates/footer') ?>