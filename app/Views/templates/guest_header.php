<?php
/**
 * Reusable guest header partial.
 *
 * Params:
 *   $announcements        array  optional — active AnnouncementModel rows; rendered into the marquee.
 *   $breadcrumb_current   string optional — label for the current page (e.g. "Departure History").
 *                                If omitted, breadcrumb shows: Home > Dashboard
 *                                If provided,                   Home > Dashboard > <current>
 *   $skip_breadcrumb      bool   optional — when true, the breadcrumb section is not rendered (the
 *                                page is expected to render its own). Defaults to false.
 *
 * Self-contained: brings its own <style> and <script>. The page using it must already
 * have <head> with Font Awesome + Outfit font + the CSS variables (--primary, --primary-dark,
 * --text-muted, --shadow-sm, --transition) defined.
 */
?>
<style>
    /* --- Advisory Bar --- */
    .advisory-bar {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        padding: 10px 5%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
        font-size: 14px;
        text-align: center;
        position: relative;
        z-index: 1;
    }
    .advisory-icon {
        width: 24px;
        height: 24px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
        font-weight: 800;
        flex-shrink: 0;
    }
    .advisory-text {
        overflow: hidden;
        white-space: nowrap;
    }
    .marquee {
        display: inline-block;
        padding-left: 100%;
        animation: gh-marquee 15s linear infinite;
    }
    @keyframes gh-marquee {
        0%   { transform: translate(0, 0); }
        100% { transform: translate(-100%, 0); }
    }

    /* --- Fixed wrapper that locks advisory bar + nav together --- */
    .sticky-top-wrapper {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1010;
    }

    /* --- Guest Header (logo + nav + clock + language) --- */
    .guest-header {
        background: #ffffff;
        padding: 15px 5%;
        box-shadow: var(--shadow-sm);
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        z-index: 1;
    }
    .guest-header .logo-section {
        display: flex;
        align-items: center;
        gap: 15px;
        text-decoration: none;
    }
    .guest-header .logo {
        width: 45px;
        height: 45px;
        object-fit: contain;
        border-radius: 10px;
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .guest-header .logo-section:hover .logo {
        transform: scale(1.05);
    }
    .guest-header .logo-text h1 {
        font-size: 18px;
        color: var(--primary-dark);
        font-weight: 800;
        margin: 0;
        letter-spacing: -0.5px;
    }
    .guest-header .logo-text p {
        font-size: 10px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 0;
    }
    .guest-header .nav-menu {
        display: flex;
        gap: 20px;
        align-items: center;
    }
    .guest-header .nav-menu a {
        text-decoration: none;
        color: var(--text-muted);
        font-size: 14px;
        font-weight: 600;
        transition: var(--transition);
        position: relative;
        padding: 8px 15px;
        display: flex;
        align-items: center;
        gap: 8px;
        border-radius: 6px;
    }
    .guest-header .nav-menu a i {
        font-size: 16px;
    }
    .guest-header .nav-menu a::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 2px;
        background: var(--primary);
        transition: var(--transition);
    }
    .guest-header .nav-menu a:hover,
    .guest-header .nav-menu a.active {
        color: var(--primary);
        background: rgba(21, 101, 192, 0.05);
    }
    .guest-header .nav-menu a:hover::after,
    .guest-header .nav-menu a.active::after {
        width: 100%;
    }
    .guest-header .nav-menu a:focus,
    .guest-header .nav-menu a:focus-visible {
        outline: none !important;
        box-shadow: none !important;
    }
    .guest-header .nav-menu a.login-btn {
        background: #1e3a8a;
        color: white !important;
        padding: 10px 25px;
        border-radius: 25px;
        font-size: 14px;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .guest-header .nav-menu a.login-btn:hover {
        transform: translateY(-1px);
        background: #172554 !important;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
    }
    .guest-header .nav-menu a.login-btn.btn-success {
        background: #059669;
    }
    .guest-header .nav-menu a.login-btn.btn-success:hover {
        background: #047857 !important;
    }
    /* --- Mobile Toggle --- */
    .guest-header .mobile-toggle {
        display: none;
        font-size: 24px;
        color: var(--primary);
        cursor: pointer;
    }

    /* --- Breadcrumb --- */
    .breadcrumb-section {
        background: white;
        padding: 12px 5%;
        font-size: 13px;
        border-bottom: 1px solid #edf2f7;
    }
    .breadcrumb-section i {
        margin: 0 8px;
        font-size: 10px;
        color: #cbd5e0;
    }
    .breadcrumb-section a {
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
    }

    /* --- Responsive --- */
    @media (max-width: 1200px) {
        .guest-header .header-info {
            display: none !important;
        }
    }

    @media (max-width: 768px) {
        .advisory-bar {
            padding: 8px 5%;
            font-size: 11px;
        }
        .guest-header {
            padding: 10px 5%;
            min-height: 65px;
            border-bottom: 1px solid #eee;
        }
        .guest-header .logo {
            width: 40px;
            height: 40px;
            border-radius: 8px;
        }
        .guest-header .logo-text h1 {
            font-size: 15px;
            letter-spacing: -0.2px;
        }
        .guest-header .logo-text p {
            font-size: 8px;
        }
    }

    @media (max-width: 1200px) {
        .guest-header .nav-menu {
            position: fixed;
            top: 0;
            right: -100%;
            width: 260px;
            height: 100vh;
            background: white;
            flex-direction: column;
            justify-content: flex-start;
            padding: 70px 25px 30px;
            box-shadow: -5px 0 25px rgba(0, 0, 0, 0.08);
            transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1003;
            gap: 12px;
        }
        .guest-header .nav-menu.open {
            right: 0;
        }
        .guest-header .nav-menu a {
            width: 100%;
            padding: 12px 18px;
            border-radius: 12px;
            background: #f8fafc;
            font-size: 15px;
            font-weight: 600;
        }
        .guest-header .nav-menu a.active {
            background: #e3f2fd;
            color: var(--primary);
        }
        .guest-header .nav-menu a.login-btn {
            background: #1e3a8a;
            color: white !important;
            text-align: center;
            justify-content: center;
            margin-top: 10px;
            margin-left: 0 !important;
        }
        .guest-header .mobile-toggle {
            display: block;
        }
    }
</style>

<div class="sticky-top-wrapper">
<!-- Advisory Bar -->
<div class="advisory-bar">
    <div class="advisory-icon"><i class="fas fa-bullhorn"></i></div>
    <div class="advisory-text">
        <div class="marquee">
            <?php if (!empty($announcements) && is_array($announcements)): ?>
                <?= esc(implode(' | ', array_column($announcements, 'message'))) ?>
            <?php else: ?>
                Welcome to Palompon Transit Terminal. Check schedules and fares for your trip.
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Header & Navigation -->
<header class="guest-header">
    <div style="display: flex; align-items: center; gap: 40px;">
        <a href="<?= base_url('guest') ?>" class="logo-section">
            <img src="<?= base_url('images/9HFScgVg_400x400.png') ?>" alt="Logo" class="logo">
            <div class="logo-text">
                <h1>Palompon Transit </h1>
                <p>Terminal Monitor</p>
            </div>
        </a>

        <div class="header-info"
            style="display: flex; flex-direction: column; gap: 8px; font-size: 13px; color: var(--text-muted); border-left: 1px solid #eee; padding-left: 20px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-map-marker-alt" style="color: #FF9800; font-size: 16px;"></i>
                <span style="font-weight: 500;">Central Terminal, Palompon, Leyte</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-clock" style="color: var(--primary); font-size: 16px;"></i>
                <span id="headerClock" style="font-weight: 500;"><?= date('H:i:s') ?></span>
            </div>
        </div>
    </div>

    <div class="nav-menu" id="navMenu">
        <a href="<?= base_url('guest') ?>" class="<?= current_url() == base_url('guest') ? 'active' : '' ?>"><i
                class="fas fa-home"></i> Home</a>
        <a href="<?= base_url('schedules') ?>"
            class="<?= (strpos(uri_string(), 'schedules') !== false) ? 'active' : '' ?>"><i
                class="fas fa-calendar-alt"></i> Schedules</a>
        <a href="<?= base_url('fares') ?>"
            class="<?= (strpos(uri_string(), 'fares') !== false) ? 'active' : '' ?>"><i class="fas fa-tags"></i>
            Fares</a>
        <a href="<?= base_url('history') ?>"
            class="<?= (strpos(uri_string(), 'history') !== false) ? 'active' : '' ?>"><i class="fas fa-history"></i>
            Departures</a>

        <?php if (session()->get('isLoggedIn')): ?>
            <?php
            $dashboardUrl = '/';
            if (in_array(session()->get('role'), ['super_admin', 'admin'], true))
                $dashboardUrl = '/admin/dashboard';
            elseif (session()->get('role') == 'staff')
                $dashboardUrl = '/staff/dashboard';
            ?>
            <a href="<?= base_url($dashboardUrl) ?>" class="login-btn btn-success" style="margin-left: 10px;">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
            <a href="<?= base_url('logout') ?>"
                style="color: #e53e3e; padding: 8px 15px; border-radius: 6px; transition: var(--transition); display: flex; align-items: center;">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        <?php else: ?>
            <a href="<?= base_url('login') ?>" class="login-btn" style="margin-left: 10px;">
                <i class="fas fa-sign-in-alt"></i> LogIn
            </a>
        <?php endif; ?>
    </div>

    <div class="mobile-toggle" onclick="toggleMenu()">
        <i class="fas fa-bars" id="mobileMenuIcon"></i>
    </div>

    <!-- Mobile overlay backdrop -->
    <div class="mobile-nav-overlay" id="mobileNavOverlay" onclick="toggleMenu()"></div>
</header>
</div><!-- /.sticky-top-wrapper -->

<?php if (empty($skip_breadcrumb)): ?>
<!-- Breadcrumbs -->
<div class="breadcrumb-section">
    <a href="<?= base_url('guest') ?>">Home</a>
    <i class="fas fa-chevron-right"></i>
    <?php if (!empty($breadcrumb_current)): ?>
        <a href="<?= base_url('guest') ?>">Dashboard</a>
        <i class="fas fa-chevron-right"></i>
        <span><?= esc($breadcrumb_current) ?></span>
    <?php else: ?>
        <span>Dashboard</span>
    <?php endif; ?>
</div>
<?php endif; ?>

<style>
    /* Mobile overlay backdrop */
    .mobile-nav-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.4);
        z-index: 1002;
        cursor: pointer;
    }
    .mobile-nav-overlay.active {
        display: block;
    }
</style>

<script>
    (function () {
        var el = document.getElementById('headerClock');
        if (el) {
            setInterval(function () {
                el.innerText = new Date().toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false
                });
            }, 1000);
        }
    })();

    // Live-refresh the announcement marquee so admin changes show without a page
    // reload. Guest pages are poll-only; this rides its own lightweight 3s poll
    // of the cached /api/announcements endpoint and only rewrites the text when
    // it actually changes. textContent keeps it XSS-safe.
    (function () {
        var bar = document.querySelector('.advisory-bar .marquee');
        if (!bar) return;
        var FALLBACK = 'Welcome to Palompon Transit Terminal. Check schedules and fares for your trip.';
        var lastText = bar.textContent.trim();
        function refreshAnnouncements() {
            fetch('<?= base_url('api/announcements') ?>?_=' + Date.now(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (d) {
                    if (!d || !d.success || !Array.isArray(d.announcements)) return;
                    var msgs = d.announcements.map(function (a) { return a.message; }).filter(Boolean);
                    var text = msgs.length ? msgs.join(' | ') : FALLBACK;
                    if (text !== lastText) {
                        lastText = text;
                        bar.textContent = text;
                    }
                })
                .catch(function () { /* keep current text on error */ });
        }
        setInterval(refreshAnnouncements, 3000);
    })();

    function toggleMenu() {
        var m = document.getElementById('navMenu');
        var overlay = document.getElementById('mobileNavOverlay');
        var icon = document.getElementById('mobileMenuIcon');
        if (!m) return;
        m.classList.toggle('open');
        var isOpen = m.classList.contains('open');
        if (overlay) {
            if (isOpen) overlay.classList.add('active');
            else overlay.classList.remove('active');
        }
        // Morph the hamburger to an X while open — it is the single close control.
        if (icon) {
            icon.className = isOpen ? 'fas fa-times' : 'fas fa-bars';
        }
        document.body.style.overflow = isOpen ? 'hidden' : '';
    }

    // Keep body padding-top in sync with the fixed header height so content
    // never hides under it. Runs once on load and again on every resize.
    (function () {
        var bar = document.querySelector('.sticky-top-wrapper');
        if (!bar) return;
        function syncPadding() {
            document.body.style.paddingTop = bar.offsetHeight + 'px';
        }
        syncPadding();
        if (window.ResizeObserver) {
            new ResizeObserver(syncPadding).observe(bar);
        }
        window.addEventListener('resize', syncPadding, { passive: true });
    })();
</script>

