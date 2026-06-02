<?= $this->include('templates/header') ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-people-fill text-primary me-2"></i> Manage Users</h1>
        <p class="text-muted mb-0" style="font-size: 14px;">Manage administrator and dispatcher system accounts and route permissions.</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 align-self-start">
        <a href="<?= base_url('admin/users/create') ?>" class="btn btn-primary d-inline-flex align-items-center gap-2">
            <i class="bi bi-plus-circle"></i> Add New User
        </a>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<?php
// Compute summary counts
$totalUsers = count($users);
$countAdmin = 0;
$countDispatcher = 0;
foreach ($users as $u) {
    if ($u['role'] === 'admin') {
        $countAdmin++;
    } else {
        $countDispatcher++;
    }
}
?>

<!-- User Search & Filter Bar -->
<div class="mb-4">
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <div class="input-group input-group-sm me-3" style="max-width: 280px;">
            <span class="input-group-text bg-white border-end-0" style="border-radius: 20px 0 0 20px;">
                <i class="bi bi-search text-muted"></i>
            </span>
            <input type="text" class="form-control border-start-0 ps-0" id="user-search" placeholder="Search name or username..." onkeyup="filterUsers(currentFilter)" style="border-radius: 0 20px 20px 0;">
        </div>
        
        <span style="font-size:13px; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:0.5px; margin-right:4px;">
            <i class="bi bi-funnel me-1"></i>Filter:
        </span>
        
        <button type="button" class="vf-btn active" id="filter-btn-all" onclick="filterUsers('all')">
            <i class="bi bi-grid-3x3-gap-fill"></i> All
            <span class="vf-count"><?= $totalUsers ?></span>
        </button>
        
        <span class="vf-divider" style="width:1px; height:24px; background:#dee2e6; margin:0 4px;"></span>
        
        <button type="button" class="vf-btn vf-admin" id="filter-btn-admin" onclick="filterUsers('admin')">
            <i class="bi bi-shield-lock"></i> Admins
            <span class="vf-count"><?= $countAdmin ?></span>
        </button>
        
        <button type="button" class="vf-btn vf-dispatcher" id="filter-btn-dispatcher" onclick="filterUsers('dispatcher')">
            <i class="bi bi-person-fill-gear"></i> Dispatchers
            <span class="vf-count"><?= $countDispatcher ?></span>
        </button>
    </div>
</div>

<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center" style="padding:12px 16px;">
        <span style="font-weight:600; font-size:14px; color:#1e293b;"><i class="bi bi-people me-1"></i> User List</span>
        <span id="filter-label" style="font-size:12px; color:#64748b;">Showing all <strong><?= $totalUsers ?></strong> users</span>
    </div>
    <div class="card-body">
        <div class="table-responsive table-responsive-card">
            <table class="table table-striped table-hover" id="users-table">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Assigned Routes</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
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
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-3 <?= $user['role'] === 'admin' ? 'avatar-admin' : 'avatar-dispatcher' ?>">
                                        <?= esc($initials) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size:14px;"><?= esc($user['full_name']) ?></div>
                                        <div>
                                            <small class="text-muted" style="font-size:11px;">@<?= esc($user['username']) ?></small>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Role">
                                <span class="badge bg-<?= $user['role'] == 'admin' ? 'danger' : 'info' ?>">
                                    <?= $user['role'] == 'staff' ? 'Dispatcher' : ucfirst($user['role']) ?>
                                </span>
                            </td>
                            <td style="max-width: 350px;" data-label="Assigned Routes">
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="badge bg-success">All Routes</span>
                                <?php elseif (!empty($user['assigned_routes_label']) && $user['assigned_routes_label'] !== 'None'): ?>
                                    <div class="badge-scroll-wrap" style="display: flex; gap: 4px; padding-bottom: 4px;" title="<?= esc($user['assigned_routes_label']) ?>">
                                        <?php
                                        $routeParts = explode(', ', $user['assigned_routes_label']);
                                        foreach ($routeParts as $rp):
                                            ?>
                                            <span class="badge bg-primary"
                                                style="font-size:10px; font-weight:500; white-space: nowrap;"><?= esc($rp) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">No Routes</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Action">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?= base_url('admin/users/edit/' . $user['id']) ?>"
                                        class="btn btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i> <span class="user-action-label">Edit</span>
                                    </a>
                                    <?php if ((int) $user['id'] !== (int) session()->get('id')): ?>
                                        <form action="<?= base_url('admin/users/delete/' . $user['id']) ?>" method="post"
                                            class="d-inline"
                                            onsubmit="return confirm('Delete user <?= esc($user['username']) ?>?')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i> <span class="user-action-label">Delete</span>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
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

    #users-table .btn-group .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
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

    /* User Filter Buttons styling */
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
    }

    .vf-btn:hover {
        border-color: #94a3b8 !important;
        background: #f8fafc !important;
        color: #1e293b !important;
    }

    .vf-count {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 22px !important;
        height: 22px !important;
        padding: 0 6px !important;
        border-radius: 12px !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        background: #e2e8f0 !important;
        color: #475569 !important;
    }

    /* Active states styling */
    .vf-btn.active {
        background: #C62828 !important;
        border-color: #C62828 !important;
        color: #fff !important;
    }

    .vf-btn.active .vf-count {
        background: rgba(255, 255, 255, 0.25) !important;
        color: #fff !important;
    }

    .vf-btn.vf-admin.active {
        background: #dc2626 !important;
        border-color: #dc2626 !important;
        color: #fff !important;
    }

    .vf-btn.vf-dispatcher.active {
        background: #0ea5e9 !important;
        border-color: #0ea5e9 !important;
        color: #fff !important;
    }

    .vf-btn.vf-admin.active .vf-count,
    .vf-btn.vf-dispatcher.active .vf-count {
        background: rgba(255, 255, 255, 0.25) !important;
        color: #fff !important;
    }

    .user-row-hidden {
        display: none !important;
    }

    @media (max-width: 991.98px) {
        #users-table {
            min-width: 680px;
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

        #users-table .btn-group {
            flex-direction: row !important;
            gap: 0.35rem;
        }

        #users-table .btn-group .btn {
            width: 34px !important;
            height: 34px;
            padding: 0 !important;
            border-radius: 6px !important;
        }

        #users-table .btn-group form {
            display: inline-flex !important;
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
            padding-left: 10px !important;
            padding-right: 10px !important;
        }
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
                show = role === 'admin';
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
                emptyRow.innerHTML = '<td colspan="5" class="text-center py-4 text-muted"><i class="bi bi-inbox me-2"></i>No users match the selected filter.</td>';
                document.querySelector('#users-table tbody').appendChild(emptyRow);
            }
            emptyRow.style.display = '';
        } else if (emptyRow) {
            emptyRow.style.display = 'none';
        }
    }
</script>

<?= $this->include('templates/footer') ?>