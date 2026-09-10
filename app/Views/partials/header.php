<?php
/**
 * Shared site header. Picks the correct nav partial from the session role.
 * Styles: public/assets/css/navigation.css (loaded by templates/navbar.php).
 */
$role    = session()->get('isLoggedIn') ? session()->get('role') : null;
$isAdmin = in_array($role, ['super_admin', 'admin'], true);
$isStaff = ($role === 'staff');
$isAuth  = ($isAdmin || $isStaff);
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

    <!-- Header Actions (Right Side) -->
    <div class="header-right-actions">
        <?php if ($isAuth): ?>
            <div class="header-user-badge">
                <span class="user-badge-name"><?= esc(session()->get('full_name') ?? session()->get('username')) ?></span>
                <span class="user-badge-role"><?= $isAdmin ? (session()->get('role') === 'super_admin' ? 'Super Admin' : 'Admin') : 'Dispatcher' ?></span>
            </div>
            <button class="nav-options-btn" id="mobileMenuToggle" type="button" aria-label="Toggle navigation options" title="Open navigation">
                <i class="fas fa-bars" id="mobileMenuIcon"></i>
            </button>
        <?php else: ?>
            <button class="mobile-toggle" id="mobileMenuToggle" type="button" aria-label="Toggle navigation">
                <i class="fas fa-bars" id="mobileMenuIcon"></i>
            </button>
        <?php endif; ?>
    </div>

    <nav class="nav-menu" aria-label="Primary navigation">
        <?php if ($isAuth): ?>
            <div class="drawer-header">
                <div class="drawer-header-brand">
                    <i class="fas fa-bars-staggered"></i>
                    <span>Menu Options</span>
                </div>
                <button type="button" class="drawer-close-btn" id="drawerCloseBtn" aria-label="Close navigation options">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        <?php endif; ?>

        <?php if ($isAdmin): ?>
            <?= view('partials/nav-admin') ?>
        <?php elseif ($role === 'staff'): ?>
            <?= view('partials/nav-dispatcher') ?>
        <?php else: ?>
            <?= view('partials/nav-guest') ?>
        <?php endif; ?>
    </nav>
</header>

<div class="mobile-nav-overlay" id="mobileNavOverlay"></div>

<?php if (session()->get('isLoggedIn')): ?>
    <?= view('partials/logout-modal') ?>
<?php endif; ?>

<script>
    function openNavDrawer() {
        var menu    = document.querySelector('header .nav-menu');
        var overlay = document.getElementById('mobileNavOverlay');
        var icon    = document.getElementById('mobileMenuIcon');
        if (!menu) return;

        menu.classList.add('mobile-open');
        if (overlay) overlay.classList.add('active');
        if (icon) {
            icon.classList.remove('fa-bars');
            icon.classList.add('fa-times');
        }
        document.body.style.overflow = 'hidden';
    }

    function closeNavDrawer() {
        var menu    = document.querySelector('header .nav-menu');
        var overlay = document.getElementById('mobileNavOverlay');
        var icon    = document.getElementById('mobileMenuIcon');
        if (!menu) return;

        menu.classList.remove('mobile-open');
        if (overlay) overlay.classList.remove('active');
        if (icon) {
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        }
        document.body.style.overflow = '';
    }

    function toggleAdminMobileMenu() {
        var menu = document.querySelector('header .nav-menu');
        if (!menu) return;
        if (menu.classList.contains('mobile-open')) {
            closeNavDrawer();
        } else {
            openNavDrawer();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var toggle   = document.getElementById('mobileMenuToggle');
        var overlay  = document.getElementById('mobileNavOverlay');
        var closeBtn = document.getElementById('drawerCloseBtn');

        if (toggle) {
            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                toggleAdminMobileMenu();
            });
        }

        if (overlay) {
            overlay.addEventListener('click', function (e) {
                e.preventDefault();
                closeNavDrawer();
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', function (e) {
                e.preventDefault();
                closeNavDrawer();
            });
        }

        // Escape key closes menu drawer
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeNavDrawer();
            }
        });

        // Dropdown accordion inside menu drawer
        document.querySelectorAll('.nav-menu .dropdown .dropbtn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var dropdown = this.closest('.dropdown');
                if (dropdown) dropdown.classList.toggle('mobile-open');
            });
        });
    });

    // Reset drawer state when guest resized back to desktop
    window.addEventListener('resize', function () {
        var isGuest = !document.body.classList.contains('admin-theme') && !document.body.classList.contains('staff-theme');
        if (isGuest && window.innerWidth > 1280) {
            closeNavDrawer();
            document.querySelectorAll('.nav-menu .dropdown.mobile-open').forEach(function (d) {
                d.classList.remove('mobile-open');
            });
        }
    });
</script>

