<?php
/** Admin navigation links. Rendered inside .nav-menu by partials/header.php */
$isActive = static fn (string $path): string => url_is($path) ? 'active' : '';
?>
<a href="<?= base_url('admin/dashboard') ?>" class="<?= $isActive('admin/dashboard') ?>">
    <i class="fas fa-gauge-high"></i> Dashboard
</a>

<div class="dropdown">
    <button class="dropbtn" type="button">
        <i class="fas fa-sliders"></i> Management <i class="fas fa-caret-down"></i>
    </button>
    <div class="dropdown-content">
        <a href="<?= base_url('admin/terminals') ?>"><i class="fas fa-building"></i> Terminals</a>
        <a href="<?= base_url('admin/vehicles') ?>"><i class="fas fa-bus"></i> Vehicle Register</a>
        <a href="<?= base_url('admin/routes') ?>"><i class="fas fa-route"></i> Routes</a>
        <a href="<?= base_url('admin/users') ?>"><i class="fas fa-users"></i> Users</a>
        <a href="<?= base_url('admin/departure-rules') ?>"><i class="fas fa-clock"></i> Departure Rules</a>
    </div>
</div>

<a href="<?= base_url('admin/announcements') ?>" class="<?= $isActive('admin/announcements*') ?>">
    <i class="fas fa-bullhorn"></i> Announcements
</a>
<a href="<?= base_url('admin/logs') ?>" class="<?= $isActive('admin/logs*') ?>">
    <i class="fas fa-clipboard-list"></i> Logs
</a>
<a href="<?= base_url('admin/history') ?>" class="<?= $isActive('admin/history*') ?>">
    <i class="fas fa-history"></i> History
</a>
<a href="<?= base_url('schedules') ?>" class="<?= $isActive('schedules*') ?>">
    <i class="fas fa-calendar-alt"></i> Schedules
</a>
<a href="<?= base_url('fares') ?>" class="<?= $isActive('fares*') ?>">
    <i class="fas fa-tags"></i> Fares
</a>

<div class="admin-profile">
    <div class="profile-info">
        <span class="profile-name"><?= esc(session()->get('full_name') ?? session()->get('username')) ?></span>
        <span class="profile-role">Admin</span>
    </div>
    <a href="<?= base_url('logout') ?>" class="logout-btn-custom">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>
