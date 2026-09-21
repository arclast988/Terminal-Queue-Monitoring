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
 * Self-contained: loads the shared guest shell stylesheet and its own script. The page using it must already
 * have <head> with Font Awesome + Outfit font + the CSS variables (--primary, --primary-dark,
 * --text-muted, --shadow-sm, --transition) defined.
 */
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url('assets/css/guest-shell.css') ?>?v=20260921h">

<div class="sticky-top-wrapper">
<!-- Advisory Bar -->
<?php
$marqueeItems = [];
if (!empty($announcements) && is_array($announcements)) {
    foreach ($announcements as $ann) {
        $m = trim($ann['message'] ?? '');
        if ($m === '') continue;
        $s = $ann['severity'] ?? 'info';
        if ($s === 'danger') {
            $marqueeItems[] = '[URGENT] ' . $m;
        } elseif ($s === 'warning') {
            $marqueeItems[] = '[WARNING] ' . $m;
        } else {
            $marqueeItems[] = $m;
        }
    }
}
$annSeparator = str_repeat("\u{00A0}", 6) . '|' . str_repeat("\u{00A0}", 6);
$rawMarqueeText = !empty($marqueeItems) ? implode($annSeparator, $marqueeItems) : ('Welcome to ' . app_name() . ' Terminal. Check schedules and fares for your trip.');
?>
<div class="advisory-bar">
    <div class="advisory-icon" onclick="openAnnouncementModal()" title="View Announcements"><i class="fas fa-bullhorn"></i></div>
    <div class="advisory-text">
        <div class="marquee" id="guestMarquee"><?= esc($rawMarqueeText) ?></div>
        <script>
            // Synchronously compute announcement marquee animation phase before paint
            // so the announcement continues seamlessly when clicking between Home, Schedules, and Fares.
            (function () {
                try {
                    var KEY_BASE = 'pt_ann_base_time';
                    var KEY_LAST = 'pt_ann_last_seen';
                    var KEY_TEXT = 'pt_ann_text';
                    var KEY_DUR  = 'pt_ann_duration';

                    var now = Date.now();
                    var rawText = <?= json_encode(trim($rawMarqueeText)) ?>;

                    // Content-aware duration: maintains a steady, comfortable ~60px/sec readable speed
                    var totalDist = (window.innerWidth || 1200) + Math.max(600, rawText.length * 9.5);
                    var DURATION = Math.max(35, Math.round(totalDist / 60));
                    document.documentElement.style.setProperty('--marquee-duration', DURATION + 's');
                    sessionStorage.setItem(KEY_DUR, DURATION.toString());
                    localStorage.setItem(KEY_DUR, DURATION.toString());

                    var storedText = sessionStorage.getItem(KEY_TEXT) || localStorage.getItem(KEY_TEXT);
                    var lastSeen = parseFloat(sessionStorage.getItem(KEY_LAST) || localStorage.getItem(KEY_LAST));
                    var baseTime = parseFloat(sessionStorage.getItem(KEY_BASE) || localStorage.getItem(KEY_BASE));

                    // If announcement text changed or inactive for more than 15 mins or fresh session: initialize base time
                    if (!baseTime || isNaN(baseTime) || storedText !== rawText || !lastSeen || (now - lastSeen > 15 * 60 * 1000)) {
                        baseTime = now;
                        sessionStorage.setItem(KEY_BASE, baseTime.toString());
                        localStorage.setItem(KEY_BASE, baseTime.toString());
                        sessionStorage.setItem(KEY_TEXT, rawText);
                        localStorage.setItem(KEY_TEXT, rawText);
                    }

                    sessionStorage.setItem(KEY_LAST, now.toString());
                    localStorage.setItem(KEY_LAST, now.toString());

                    var elapsed = ((now - baseTime) / 1000) % DURATION;
                    if (elapsed < 0) elapsed = 0;
                    var delayStr = '-' + elapsed.toFixed(3) + 's';

                    var el = document.getElementById('guestMarquee');
                    if (el) {
                        el.style.animationDelay = delayStr;
                    }
                    document.documentElement.style.setProperty('--marquee-delay', delayStr);
                } catch (e) {}
            })();
        </script>
    </div>
</div>

<!-- Announcement Modal -->
<div class="ann-modal-overlay" id="annModalOverlay" onclick="closeAnnouncementModal(event)">
    <div class="ann-modal" onclick="event.stopPropagation()">
        <div class="ann-modal-header">
            <div class="ann-modal-header-left">
                <div class="ann-modal-header-icon">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <div>
                    <h3>Announcements <span class="ann-modal-header-badge" id="annCountBadge"><?= !empty($announcements) && is_array($announcements) ? count($announcements) . ' Active' : 'Live' ?></span></h3>
                </div>
            </div>
            <button type="button" class="ann-modal-close" onclick="closeAnnouncementModal()" aria-label="Close modal">&times;</button>
        </div>
        <div class="ann-modal-body">
            <ul class="ann-modal-list" id="annModalList">
                <?php if (!empty($announcements) && is_array($announcements)): ?>
                    <?php foreach ($announcements as $ann):
                        $sev = $ann['severity'] ?? 'info';
                        $iconClass = ($sev === 'danger') ? 'fa-circle-exclamation' : (($sev === 'warning') ? 'fa-triangle-exclamation' : 'fa-info-circle');
                        $tagLabel = ($sev === 'danger') ? 'Urgent' : (($sev === 'warning') ? 'Warning' : 'Notice');
                    ?>
                        <li class="ann-item-<?= esc($sev) ?>">
                            <div class="ann-bullet-wrap ann-bullet-<?= esc($sev) ?>">
                                <i class="fas <?= $iconClass ?>"></i>
                            </div>
                            <div class="ann-modal-text">
                                <div class="ann-severity-tag ann-tag-<?= esc($sev) ?>"><?= $tagLabel ?></div>
                                <div><?php
                                    $text = esc($ann['message'] ?? '');
                                    $text = preg_replace('~(\bhttps?://[^\s<]+?)\)([A-Za-z0-9])~i', '$1) $2', $text);
                                    echo preg_replace_callback(
                                        '~https?://[^\s<]+~i',
                                        static function ($matches) {
                                            $url = $matches[0];
                                            $trailing = '';
                                            while ($url !== '' && preg_match('/[.,;!?:)]$/', $url)) {
                                                $trailing = substr($url, -1) . $trailing;
                                                $url = substr($url, 0, -1);
                                            }
                                            return '<a href="' . $url . '" target="_blank" rel="noopener noreferrer">' . $url . '</a>' . $trailing;
                                        },
                                        $text
                                    );
                                ?></div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="ann-modal-empty">
                        <i class="fas fa-bell-slash"></i>
                        No active announcements at this time.
                    </li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="ann-modal-footer">
            <span class="ann-modal-footer-status"><i class="fas fa-circle"></i> Live terminal advisory sync</span>
            <button type="button" class="ann-modal-btn-close" onclick="closeAnnouncementModal()">Close</button>
        </div>
    </div>
</div>

<!-- Header & Navigation -->
<header class="guest-header">
    <div class="logo-container">
        <a href="<?= base_url('guest') ?>" class="logo-section" title="<?= esc(app_name()) ?>">
            <img src="<?= esc(app_logo()) ?>" alt="<?= esc(app_name()) ?> Logo" class="logo">
            <div class="logo-text">
                <h1><?= esc(app_name()) ?></h1>
                <p><?= esc(app_subtitle()) ?></p>
            </div>
        </a>

        <div class="header-info">
            <div class="header-clock-pill">
                <i class="fas fa-clock"></i>
                <span id="headerClock"><?= date('h:i:s A') ?></span>
            </div>
        </div>
    </div>

    <div class="nav-menu" id="navMenu">
        <button type="button" class="guest-nav-close-btn" onclick="closeMenu()" aria-label="Close navigation menu">
            <i class="fas fa-times" aria-hidden="true"></i>
        </button>
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
                data-bs-toggle="modal" data-bs-target="#logoutModal" onclick="return confirmLogout(event);" role="button" aria-haspopup="dialog"
                style="color: #e53e3e; padding: 8px 15px; border-radius: 6px; transition: var(--transition); display: flex; align-items: center;" title="Log Out">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        <?php else: ?>
            <a href="<?= base_url('login') ?>" class="login-btn" style="margin-left: 10px;">
                <i class="fas fa-sign-in-alt"></i> LogIn
            </a>
        <?php endif; ?>
    </div>

    <button type="button" class="mobile-toggle" onclick="toggleMenu()" aria-label="Open navigation menu" aria-controls="navMenu" aria-expanded="false">
        <i class="fas fa-bars" id="mobileMenuIcon" aria-hidden="true"></i>
    </button>

    <!-- Mobile overlay backdrop -->
    <div class="mobile-nav-overlay" id="mobileNavOverlay" onclick="closeMenu()"></div>
</header>
</div><!-- /.sticky-top-wrapper -->

<?php if (session()->get('isLoggedIn')): ?>
    <?= view('partials/logout-modal') ?>
<?php endif; ?>

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

    // Live-refresh the announcement marquee without a page reload. WebSocket is
    // the fast path; a visibility-aware timer is only a recovery/safety path.
    (function () {
        var bar = document.querySelector('.advisory-bar .marquee');
        if (!bar) return;
        var FALLBACK = <?= json_encode('Welcome to ' . app_name() . ' Terminal. Check schedules and fares for your trip.') ?>;
        var lastText = bar.textContent.trim();
        function refreshAnnouncements() {
            fetch('<?= base_url('api/announcements') ?>?_=' + Date.now(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (d) {
                    if (!d || !d.success || !Array.isArray(d.announcements)) return;
                    var msgs = d.announcements.map(function (a) {
                        var m = String(a.message || '').replace(/\s+/g, ' ').trim();
                        if (!m) return '';
                        var s = a.severity || 'info';
                        if (s === 'danger') return '[URGENT] ' + m;
                        if (s === 'warning') return '[WARNING] ' + m;
                        return m;
                    }).filter(Boolean);
                    var annSeparator = '\u00A0\u00A0\u00A0\u00A0\u00A0\u00A0|\u00A0\u00A0\u00A0\u00A0\u00A0\u00A0';
                    var text = msgs.length ? msgs.join(annSeparator) : FALLBACK;
                    if (text !== lastText) {
                        lastText = text;
                        bar.textContent = text;
                        try {
                            var nowReset = Date.now();
                            var newDist = (window.innerWidth || 1200) + Math.max(600, text.length * 9.5);
                            var newDur = Math.max(35, Math.round(newDist / 60));
                            sessionStorage.setItem('pt_ann_text', text);
                            localStorage.setItem('pt_ann_text', text);
                            sessionStorage.setItem('pt_ann_base_time', nowReset.toString());
                            localStorage.setItem('pt_ann_base_time', nowReset.toString());
                            sessionStorage.setItem('pt_ann_duration', newDur.toString());
                            localStorage.setItem('pt_ann_duration', newDur.toString());
                            document.documentElement.style.setProperty('--marquee-duration', newDur + 's');
                            bar.style.animation = 'none';
                            bar.offsetHeight;
                            bar.style.animation = '';
                            bar.style.animationDelay = '0s';
                            document.documentElement.style.setProperty('--marquee-delay', '0s');
                        } catch (e) {}
                    }
                    // Also update modal list
                    var modalList = document.getElementById('annModalList');
                    var countBadge = document.getElementById('annCountBadge');
                    if (modalList) {
                        if (d.announcements.length) {
                            if (countBadge) countBadge.textContent = d.announcements.length + ' Active';
                            modalList.innerHTML = d.announcements.map(function(a) {
                                var m = String(a.message || '').trim();
                                if (!m) return '';
                                var s = a.severity || 'info';
                                var iconClass = (s === 'danger') ? 'fa-circle-exclamation' : ((s === 'warning') ? 'fa-triangle-exclamation' : 'fa-info-circle');
                                var tagLabel = (s === 'danger') ? 'Urgent' : ((s === 'warning') ? 'Warning' : 'Notice');
                                var safe = m.replace(/</g, '&lt;').replace(/>/g, '&gt;');
                                safe = safe.replace(/(\bhttps?:\/\/[^\s<]+?)\)([A-Za-z0-9])/gi, '$1) $2');
                                var withLinks = safe.replace(/https?:\/\/[^\s<]+/gi, function (fullMatch) {
                                    var url = fullMatch;
                                    var trailing = '';
                                    while (url.length && /[.,;!?:)]$/.test(url)) {
                                        trailing = url.slice(-1) + trailing;
                                        url = url.slice(0, -1);
                                    }
                                    return '<a href="' + url + '" target="_blank" rel="noopener noreferrer">' + url + '</a>' + trailing;
                                });
                                return '<li class="ann-item-' + s + '"><div class="ann-bullet-wrap ann-bullet-' + s + '"><i class="fas ' + iconClass + '"></i></div><div class="ann-modal-text"><div class="ann-severity-tag ann-tag-' + s + '">' + tagLabel + '</div><div>' + withLinks + '</div></div></li>';
                            }).filter(Boolean).join('');
                        } else {
                            if (countBadge) countBadge.textContent = 'Live';
                            modalList.innerHTML = '<li class="ann-modal-empty"><i class="fas fa-bell-slash"></i>No active announcements at this time.</li>';
                        }
                    }
                })
                .catch(function () { /* keep current text on error */ });
        }
        // Listen for real-time WebSocket announcement broadcasts (<100ms update)
        document.addEventListener('pttm:ws-announcement_update', refreshAnnouncements);
        document.addEventListener('announcement-updated', refreshAnnouncements);

        var announcementPollTimer = null;
        function scheduleAnnouncementPoll() {
            if (announcementPollTimer) clearTimeout(announcementPollTimer);
            var wsConnected = window.QueueWS && window.QueueWS.isConnected && window.QueueWS.isConnected();
            var delay = wsConnected ? 120000 : 30000;
            announcementPollTimer = setTimeout(function () {
                if (!document.hidden) refreshAnnouncements();
                scheduleAnnouncementPoll();
            }, delay);
        }
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                refreshAnnouncements();
                scheduleAnnouncementPoll();
            }
        });
        scheduleAnnouncementPoll();
    })();

    var guestMenuCloseSequence = 0;

    function setMenuOpen(shouldOpen) {
        var m = document.getElementById('navMenu');
        var overlay = document.getElementById('mobileNavOverlay');
        var icon = document.getElementById('mobileMenuIcon');
        var toggle = document.querySelector('.guest-header .mobile-toggle');
        if (!m) return;

        var isOpen = !!shouldOpen;
        var closeSequence = ++guestMenuCloseSequence;
        m.classList.toggle('open', isOpen);
        if (overlay) {
            if (isOpen) overlay.classList.add('active');
            else overlay.classList.remove('active');
        }
        if (toggle) {
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            toggle.setAttribute('aria-label', 'Open navigation menu');
        }

        if (icon) icon.className = 'fas fa-bars';

        if (isOpen) {
            if (toggle) toggle.classList.remove('is-closing');
            document.body.style.overflow = 'hidden';
            return;
        }

        // Do not turn the X back into a hamburger while the panel is still
        // sliding away. That was the blue hamburger seen inside the closing
        // white drawer on slower phones and Messenger's embedded browser.
        if (toggle) toggle.classList.add('is-closing');
        var finishClose = function(event) {
            if (event && event.target !== m) return;
            if (event && event.propertyName !== 'transform') return;
            if (closeSequence !== guestMenuCloseSequence || m.classList.contains('open')) return;
            if (toggle) {
                toggle.classList.remove('is-closing');
                toggle.blur();
            }
            document.body.style.overflow = '';
        };
        m.addEventListener('transitionend', finishClose, { once: true });
        window.setTimeout(finishClose, 240);
    }

    function toggleMenu() {
        var menu = document.getElementById('navMenu');
        setMenuOpen(!(menu && menu.classList.contains('open')));
    }

    function closeMenu() {
        setMenuOpen(false);
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeMenu();
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 900) closeMenu();
    });

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
    // Announcement pause-on-hover synchronization
    (function () {
        var bar = document.getElementById('guestMarquee') || document.querySelector('.advisory-bar .marquee');
        if (!bar) return;

        var KEY_BASE = 'pt_ann_base_time';
        var hoverStart = 0;

        function applyPausedDuration(duration) {
            if (duration <= 0) return;
            try {
                var baseTime = parseFloat(sessionStorage.getItem(KEY_BASE) || localStorage.getItem(KEY_BASE));
                if (baseTime && !isNaN(baseTime)) {
                    baseTime += duration;
                    sessionStorage.setItem(KEY_BASE, baseTime.toString());
                    localStorage.setItem(KEY_BASE, baseTime.toString());
                }
            } catch (e) {}
        }

        bar.addEventListener('mouseenter', function () {
            hoverStart = Date.now();
        });

        bar.addEventListener('mouseleave', function () {
            if (hoverStart) {
                applyPausedDuration(Date.now() - hoverStart);
                hoverStart = 0;
            }
        });

        document.addEventListener('click', function (e) {
            if (hoverStart) {
                applyPausedDuration(Date.now() - hoverStart);
                hoverStart = 0;
            }
        }, true);

        // Handle browser bfcache restore
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) {
                try {
                    var DURATION = parseFloat(sessionStorage.getItem('pt_ann_duration') || localStorage.getItem('pt_ann_duration')) || 35;
                    document.documentElement.style.setProperty('--marquee-duration', DURATION + 's');
                    var baseTime = parseFloat(sessionStorage.getItem(KEY_BASE) || localStorage.getItem(KEY_BASE));
                    if (baseTime && !isNaN(baseTime)) {
                        var elapsed = ((Date.now() - baseTime) / 1000) % DURATION;
                        if (elapsed < 0) elapsed = 0;
                        var delayStr = '-' + elapsed.toFixed(3) + 's';
                        bar.style.animationDelay = delayStr;
                        document.documentElement.style.setProperty('--marquee-delay', delayStr);
                    }
                } catch (err) {}
            }
        });
    })();
</script>

