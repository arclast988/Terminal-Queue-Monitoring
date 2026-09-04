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
        background: linear-gradient(135deg, #B71C1C 0%, #7F0000 100%);
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
        width: 28px;
        height: 28px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #B71C1C;
        font-weight: 800;
        flex-shrink: 0;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .advisory-icon:hover {
        transform: scale(1.15);
        box-shadow: 0 0 0 3px rgba(255,255,255,0.35);
    }
    .advisory-text {
        overflow: hidden;
        white-space: nowrap;
    }
    .marquee {
        display: inline-block;
        padding-left: 100%;
        animation: gh-marquee 35s linear infinite;
        font-weight: 800;
        font-size: 16px;
        letter-spacing: 0.5px;
        text-shadow: 0 1px 3px rgba(0,0,0,0.3);
    }
    .marquee:hover {
        animation-play-state: paused;
    }

    /* --- Announcement Modal --- */
    .ann-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .ann-modal-overlay.open {
        display: flex;
    }
    .ann-modal {
        background: white;
        border-radius: 16px;
        max-width: 520px;
        width: 100%;
        max-height: 70vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0,0,0,0.25);
        animation: annSlideUp 0.3s ease;
    }
    .ann-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 22px;
        border-bottom: 1px solid #eee;
        background: linear-gradient(135deg, #B71C1C 0%, #7F0000 100%);
        border-radius: 16px 16px 0 0;
        color: white;
    }
    .ann-modal-header h3 {
        margin: 0;
        font-size: 17px;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #ffffff;
    }
    .ann-modal-header h3 i {
        color: #ffffff;
    }
    .ann-modal-close {
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }
    .ann-modal-close:hover {
        background: rgba(255,255,255,0.35);
    }
    .ann-modal-body {
        padding: 16px 22px 22px;
    }
    .ann-modal-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .ann-modal-list li {
        padding: 14px 16px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        font-size: 15.5px;
        color: #1e293b;
        line-height: 1.65;
    }
    .ann-modal-list li:last-child {
        border-bottom: none;
    }
    .ann-bullet {
        width: 8px;
        height: 8px;
        background: #B71C1C;
        border-radius: 50%;
        flex-shrink: 0;
        margin-top: 7px;
    }
    .ann-modal-empty {
        text-align: center;
        color: #94a3b8;
        padding: 30px 0;
        font-size: 14px;
    }
    .ann-modal-empty i {
        font-size: 32px;
        margin-bottom: 10px;
        display: block;
        color: #cbd5e1;
    }
    @keyframes annSlideUp {
        from { transform: translateY(30px); opacity: 0; }
        to   { transform: translateY(0); opacity: 1; }
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
        padding: 8px 5%;
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
        width: 64px;
        height: 64px;
        object-fit: contain;
        border-radius: 10px;
        image-rendering: auto;
        -ms-interpolation-mode: bicubic;
        filter: drop-shadow(0 1px 3px rgba(0, 0, 0, 0.15));
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .guest-header .logo-section:hover .logo {
        transform: scale(1.05);
    }
    .guest-header .logo-text h1 {
        font-size: 20px;
        color: #000000;
        font-weight: 800;
        margin: 0;
        letter-spacing: -0.5px;
    }
    .guest-header .logo-text p {
        font-size: 12px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.8px;
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
        font-size: 14.5px;
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
        background: #D62828;
        transition: var(--transition);
    }
    .guest-header .nav-menu a:hover,
    .guest-header .nav-menu a.active {
        color: #D62828;
        background: rgba(214, 40, 40, 0.06);
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
        background: #B71C1C;
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
        background: #8B0000 !important;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
    }
    /* --- Mobile Toggle --- */
    .guest-header .mobile-toggle {
        display: none;
        font-size: 24px;
        color: #000000;
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
        font-size: 11px;
        color: #cbd5e0;
    }
    .breadcrumb-section a {
        color: #D62828;
        text-decoration: none;
        font-weight: 500;
    }

    .guest-header .header-info {
        display: flex;
        align-items: center;
        border-left: 1.5px solid #e2e8f0;
        padding-left: 18px;
        height: 28px;
        margin-top: -6px;
    }
    .header-clock-pill {
        display: flex;
        align-items: center;
        gap: 7px;
        font-weight: 700;
        color: #0f172a;
        font-size: 15px;
        letter-spacing: 0.5px;
        line-height: 1;
    }
    .header-clock-pill i {
        color: #D62828;
        font-size: 15px;
    }

    /* --- Responsive --- */
    @media (max-width: 768px) {
        .advisory-bar {
            padding: 8px 5%;
            font-size: 12.5px;
        }
        .guest-header {
            padding: 10px 4%;
            min-height: 60px;
            border-bottom: 1px solid #eee;
        }
        .guest-header .logo-container {
            gap: 12px !important;
        }
        .guest-header .logo {
            width: 38px;
            height: 38px;
            border-radius: 8px;
        }
        .guest-header .logo-text h1 {
            font-size: 14px;
            letter-spacing: -0.2px;
        }
        .guest-header .logo-text p {
            font-size: 9px;
        }
        .guest-header .header-info {
            padding-left: 10px !important;
            border-left: 1.5px solid #e2e8f0 !important;
            display: flex !important;
            height: 22px !important;
            margin-top: -4px !important;
        }
        .header-clock-pill {
            font-size: 12.5px !important;
            gap: 4px !important;
        }
        .header-clock-pill i {
            font-size: 12px !important;
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
            background: rgba(214, 40, 40, 0.08);
            color: #D62828;
        }
        .guest-header .nav-menu a.login-btn {
            background: #B71C1C;
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

    @media (max-width: 420px) {
        .advisory-bar {
            padding: 6px 3%;
            font-size: 11px;
        }
        .guest-header {
            padding: 8px 3% !important;
            min-height: 54px !important;
        }
        .guest-header .logo-container {
            gap: 8px !important;
        }
        .guest-header .logo {
            width: 32px !important;
            height: 32px !important;
            border-radius: 6px !important;
        }
        .guest-header .logo-text h1 {
            font-size: 13px !important;
        }
        .guest-header .logo-text p {
            font-size: 7.5px !important;
        }
        .guest-header .header-info {
            padding-left: 8px !important;
        }
        .header-clock-pill {
            font-size: 11px !important;
            gap: 3px !important;
        }
        .header-clock-pill i {
            font-size: 11px !important;
        }
        .breadcrumb-section {
            padding: 8px 3% !important;
            font-size: 11px !important;
        }
    }
</style>

<div class="sticky-top-wrapper">
<!-- Advisory Bar -->
<div class="advisory-bar">
    <div class="advisory-icon" onclick="openAnnouncementModal()" title="View Announcements"><i class="fas fa-bullhorn"></i></div>
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

<!-- Announcement Modal -->
<div class="ann-modal-overlay" id="annModalOverlay" onclick="closeAnnouncementModal(event)">
    <div class="ann-modal" onclick="event.stopPropagation()">
        <div class="ann-modal-header">
            <h3><i class="fas fa-bullhorn"></i> Announcements</h3>
            <button class="ann-modal-close" onclick="closeAnnouncementModal()">&times;</button>
        </div>
        <div class="ann-modal-body">
            <ul class="ann-modal-list" id="annModalList">
                <?php if (!empty($announcements) && is_array($announcements)): ?>
                    <?php foreach ($announcements as $ann): ?>
                        <li>
                            <span class="ann-bullet"></span>
                            <span><?= esc($ann['message']) ?></span>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="ann-modal-empty">
                        <i class="fas fa-info-circle"></i>
                        No announcements at this time.
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<!-- Header & Navigation -->
<header class="guest-header">
    <div class="logo-container" style="display: flex; align-items: center; gap: 24px;">
        <a href="<?= base_url('guest') ?>" class="logo-section">
            <img src="<?= base_url('images/9HFScgVg_400x400.png') ?>" alt="Logo" class="logo">
            <div class="logo-text">
                <h1>Palompon Transit </h1>
                <p>Terminal Monitor</p>
            </div>
        </a>

        <div class="header-info"
            style="display: flex; align-items: center; border-left: 1px solid #eee; padding-left: 18px;">
            <div class="header-clock-pill" style="display: flex; align-items: center; gap: 6px; font-weight: 700; color: #0f172a; font-size: 14px; letter-spacing: 0.5px;">
                <i class="fas fa-clock" style="color: #D62828; font-size: 14px;"></i>
                <span id="headerClock"><?= date('h:i:s A') ?></span>
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

        <?php if (session()->get('isLoggedIn')): ?>
            <?php
            $dashboardUrl = '/';
            if (in_array(session()->get('role'), ['super_admin', 'admin'], true))
                $dashboardUrl = '/admin/dashboard';
            elseif (session()->get('role') == 'staff')
                $dashboardUrl = '/staff/dashboard';
            ?>
            <a href="<?= base_url($dashboardUrl) ?>" class="login-btn" style="margin-left: 10px;">
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
                el.innerText = new Date().toLocaleTimeString('en-US', {
                    timeZone: 'Asia/Manila',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
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
                    // Also update modal list
                    var modalList = document.getElementById('annModalList');
                    if (modalList) {
                        if (msgs.length) {
                            modalList.innerHTML = msgs.map(function(m) {
                                var safe = m.replace(/</g, '&lt;').replace(/>/g, '&gt;');
                                return '<li><span class="ann-bullet"></span><span>' + safe + '</span></li>';
                            }).join('');
                        } else {
                            modalList.innerHTML = '<li class="ann-modal-empty"><i class="fas fa-info-circle"></i>No announcements at this time.</li>';
                        }
                    }
                })
                .catch(function () { /* keep current text on error */ });
        }
        // Check for announcement changes every 30 seconds
        setInterval(refreshAnnouncements, 30000);
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
    var _annScrollY = 0;
    function openAnnouncementModal() {
        // Freeze the page exactly where the user is (toggling body overflow
        // alone makes the browser jump to the top).
        _annScrollY = window.scrollY || document.documentElement.scrollTop || 0;
        document.body.style.position = 'fixed';
        document.body.style.top = (-_annScrollY) + 'px';
        document.body.style.left = '0';
        document.body.style.right = '0';
        document.getElementById('annModalOverlay').classList.add('open');
    }
    function closeAnnouncementModal(e) {
        if (e && e.target !== e.currentTarget) return;
        document.getElementById('annModalOverlay').classList.remove('open');
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.left = '';
        document.body.style.right = '';
        // Return to the exact spot instead of the top.
        window.scrollTo(0, _annScrollY);
    }
</script>

