<?php
/**
 * Shared site header. Picks the correct nav partial from the session role.
 * Styles: public/assets/css/navigation.css (loaded by templates/navbar.php).
 */
$role    = session()->get('isLoggedIn') ? session()->get('role') : null;
$isAdmin = in_array($role, ['super_admin', 'admin'], true);
$homeUrl = $isAdmin ? 'admin/dashboard' : ($role === 'staff' ? 'staff/dashboard' : '/');
?>
<header id="site-header">
    <a href="<?= base_url($homeUrl) ?>" class="logo-section">
        <img src="<?= base_url('images/9HFScgVg_400x400.png') ?>" alt="Palompon Transit Logo" class="logo">
        <div class="logo-text">
            <h1>Palompon Transit</h1>
            <p>Terminal Monitor</p>
        </div>
    </a>

    <nav class="nav-menu" aria-label="Primary navigation">
        <?php if ($isAdmin): ?>
            <?= view('partials/nav-admin') ?>
        <?php elseif ($role === 'staff'): ?>
            <?= view('partials/nav-dispatcher') ?>
        <?php else: ?>
            <?= view('partials/nav-guest') ?>
        <?php endif; ?>
    </nav>

    <button class="mobile-toggle" id="mobileMenuToggle" type="button" aria-label="Toggle navigation">
        <i class="fas fa-bars" id="mobileMenuIcon"></i>
    </button>
</header>

<div class="mobile-nav-overlay" id="mobileNavOverlay"></div>

<?php if (session()->get('isLoggedIn')): ?>
    <?= view('partials/logout-modal') ?>
<?php endif; ?>

<script>
    function toggleAdminMobileMenu() {
        var menu    = document.querySelector('header .nav-menu');
        var overlay = document.getElementById('mobileNavOverlay');
        var icon    = document.getElementById('mobileMenuIcon');
        var isOpen  = menu.classList.contains('mobile-open');

        if (isOpen) {
            menu.classList.remove('mobile-open');
            overlay.classList.remove('active');
            icon.classList.replace('fa-times', 'fa-bars');
            document.body.style.overflow = '';
        } else {
            menu.classList.add('mobile-open');
            overlay.classList.add('active');
            icon.classList.replace('fa-bars', 'fa-times');
            document.body.style.overflow = 'hidden';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var toggle  = document.getElementById('mobileMenuToggle');
        var overlay = document.getElementById('mobileNavOverlay');
        if (toggle)  toggle.addEventListener('click', toggleAdminMobileMenu);
        if (overlay) overlay.addEventListener('click', toggleAdminMobileMenu);

        // Mobile: dropdowns open on tap instead of hover
        document.querySelectorAll('.nav-menu .dropdown .dropbtn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                if (window.innerWidth <= 1280) {
                    e.preventDefault();
                    e.stopPropagation();
                    var dropdown = this.closest('.dropdown');
                    if (dropdown) dropdown.classList.toggle('mobile-open');
                }
            });
        });
    });

    // Reset drawer state when resized back to desktop
    window.addEventListener('resize', function () {
        if (window.innerWidth > 1280) {
            var menu    = document.querySelector('header .nav-menu');
            var overlay = document.getElementById('mobileNavOverlay');
            var icon    = document.getElementById('mobileMenuIcon');
            if (menu)    menu.classList.remove('mobile-open');
            if (overlay) overlay.classList.remove('active');
            if (icon)    icon.classList.replace('fa-times', 'fa-bars');
            document.body.style.overflow = '';
            document.querySelectorAll('.nav-menu .dropdown.mobile-open').forEach(function (d) {
                d.classList.remove('mobile-open');
            });
        }
    });
</script>
