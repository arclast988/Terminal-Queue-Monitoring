<?php
/** Guest navigation links. Rendered inside .nav-menu by partials/header.php */
$isActive = static fn (string $path): string => url_is($path) ? 'active' : '';
?>
<a href="<?= base_url('/') ?>" class="<?= url_is('/') || url_is('guest') ? 'active' : '' ?>">
    <i class="fas fa-home"></i> Home
</a>
<a href="<?= base_url('schedules') ?>" class="<?= $isActive('schedules*') ?>">
    <i class="fas fa-calendar-alt"></i> Schedules
</a>
<a href="<?= base_url('fares') ?>" class="<?= $isActive('fares*') ?>">
    <i class="fas fa-tags"></i> Fares
</a>
<a href="<?= base_url('login') ?>" class="login-btn">
    <i class="fas fa-sign-in-alt"></i> Login
</a>
