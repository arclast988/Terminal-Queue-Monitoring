<?php
/** Admin navigation links. Rendered inside .nav-menu by partials/header.php */
$isActive = static fn (string $path): string => url_is($path) ? 'active' : '';
$isManagementActive = $isActive('admin/terminals*') || $isActive('admin/vehicles*') || $isActive('admin/routes*') || $isActive('admin/users*') || $isActive('admin/departure-rules*');
$isRecordsActive    = $isActive('admin/history*') || $isActive('admin/logs*');
?>
<a href="<?= base_url('admin/dashboard') ?>" class="<?= $isActive('admin/dashboard') ?>">
    <i class="fas fa-gauge-high"></i> Dashboard
</a>

<div class="dropdown">
    <button class="dropbtn <?= $isManagementActive ? 'active' : '' ?>" type="button" aria-haspopup="true" aria-expanded="false" aria-controls="managementDropdownMenu">
        <i class="fas fa-sliders"></i> Management <i class="fas fa-caret-down"></i>
    </button>
    <div class="dropdown-content" id="managementDropdownMenu" role="menu">
        <a href="<?= base_url('admin/terminals') ?>" class="<?= $isActive('admin/terminals*') ?>"><i class="fas fa-building"></i> Terminals</a>
        <a href="<?= base_url('admin/vehicles') ?>" class="<?= $isActive('admin/vehicles*') ?>"><i class="fas fa-bus"></i> Vehicle Register</a>
        <a href="<?= base_url('admin/routes') ?>" class="<?= $isActive('admin/routes*') ?>"><i class="fas fa-route"></i> Routes</a>
        <a href="<?= base_url('admin/users') ?>" class="<?= $isActive('admin/users*') ?>"><i class="fas fa-users"></i> Users</a>
        <a href="<?= base_url('admin/departure-rules') ?>" class="<?= $isActive('admin/departure-rules*') ?>"><i class="fas fa-clock"></i> Departure Rules</a>
    </div>
</div>

<a href="<?= base_url('admin/announcements') ?>" class="<?= $isActive('admin/announcements*') ?>">
    <i class="fas fa-bullhorn"></i> Announcements
</a>
<a href="<?= base_url('schedules') ?>" class="<?= $isActive('schedules*') ?>">
    <i class="fas fa-calendar-alt"></i> Schedules
</a>
<a href="<?= base_url('fares') ?>" class="<?= $isActive('fares*') ?>">
    <i class="fas fa-tags"></i> Fares
</a>

<div class="dropdown">
    <button class="dropbtn <?= $isRecordsActive ? 'active' : '' ?>" type="button" aria-haspopup="true" aria-expanded="false" aria-controls="recordsDropdownMenu">
        <i class="fas fa-folder-open"></i> Records <i class="fas fa-caret-down"></i>
    </button>
    <div class="dropdown-content" id="recordsDropdownMenu" role="menu">
        <a href="<?= base_url('admin/history') ?>" class="<?= $isActive('admin/history*') ?>">
            <i class="fas fa-history"></i> Departure History
            <span class="nav-badge-chip chip-purple">Records</span>
        </a>
        <a href="<?= base_url('admin/logs') ?>" class="<?= $isActive('admin/logs*') ?>">
            <i class="fas fa-clipboard-list"></i> Activity Logs
            <span class="nav-badge-chip chip-amber">Audit</span>
        </a>
    </div>
</div>

<?= view('partials/nav-profile') ?>


