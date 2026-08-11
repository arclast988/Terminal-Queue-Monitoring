<?php
/** Dispatcher (staff) navigation links. Rendered inside .nav-menu by partials/header.php */
$isActive = static fn (string $path): string => url_is($path) ? 'active' : '';
?>
<a href="<?= base_url('staff/dashboard') ?>" class="<?= $isActive('staff/dashboard') ?>">
    <i class="fas fa-gauge-high"></i> Dashboard
</a>
<a href="<?= base_url('admin/announcements') ?>" class="<?= $isActive('admin/announcements*') ?>">
    <i class="fas fa-bullhorn"></i> Announcements
</a>
<a href="<?= base_url('staff/queue') ?>" class="<?= $isActive('staff/queue*') ?>">
    <i class="fas fa-list-ol"></i> Queue Management
</a>
<a href="<?= base_url('staff/departure-rules') ?>" class="<?= $isActive('staff/departure-rules*') ?>">
    <i class="fas fa-clock"></i> Departure Rules
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
        <span class="profile-role">Dispatcher</span>
    </div>
    <a href="<?= base_url('logout') ?>" class="logout-btn-custom">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>
