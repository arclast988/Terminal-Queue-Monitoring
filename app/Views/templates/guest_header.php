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
<link rel="stylesheet" href="<?= base_url('assets/css/guest-shell.css') ?>?v=20261003m1">
<?php if (empty($interaction_assets_loaded)): ?>
<link rel="stylesheet" href="<?= base_url('assets/css/interaction-motion.css?v=20261002m3') ?>">
<?php endif; ?>

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
    <button type="button" class="advisory-icon" onclick="openAnnouncementModal()" title="View announcements" aria-label="View announcements"><i class="fas fa-bullhorn"></i></button>
    <div class="advisory-text" onclick="openAnnouncementModal()" style="cursor: pointer;" title="View announcements" role="button" tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openAnnouncementModal();}">
        <div class="marquee" id="guestMarquee">
            <span class="marquee-copy"><?= esc($rawMarqueeText) ?></span>
            <span class="marquee-copy" aria-hidden="true"><?= esc($rawMarqueeText) ?></span>
        </div>
        <script>
            (function () {
                var track = document.getElementById('guestMarquee');
                var first = track && track.querySelector('.marquee-copy');
                if (!track || !first) return;

                var resizeTimer;

                function rebuild() {
                    while (track.children.length > 2) track.removeChild(track.lastElementChild);
                    if (track.children.length < 2) {
                        var copy = first.cloneNode(true);
                        copy.setAttribute('aria-hidden', 'true');
                        track.appendChild(copy);
                    } else if (track.children[1]) {
                        track.children[1].textContent = first.textContent;
                    }

                    var bounds = first.getBoundingClientRect();
                    var segmentWidth = bounds.width || first.offsetWidth || 350;
                    var durationSec = Math.max(12, Math.round(segmentWidth / 55));
                    var durStr = durationSec + 's';
                    if (track.style.animationDuration !== durStr) {
                        track.style.setProperty('animation-duration', durStr, 'important');
                        track.style.setProperty('-webkit-animation-duration', durStr, 'important');
                    }
                }

                window.refreshGuestMarquee = rebuild;
                rebuild();
                window.addEventListener('load', rebuild);
                window.addEventListener('resize', function () {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(rebuild, 150);
                });
                if (document.fonts && document.fonts.ready) {
                    document.fonts.ready.then(rebuild);
                }
                window.addEventListener('pageshow', function (event) {
                    if (event.persisted) rebuild();
                });
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
            var formatter = new Intl.DateTimeFormat('en-US', {
                timeZone: 'Asia/Manila', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
            });
            function updateClock() {
                if (document.hidden) return;
                var value = formatter.format(new Date());
                if (el.textContent !== value) el.textContent = value;
            }
            updateClock();
            setInterval(updateClock, 1000);
            document.addEventListener('visibilitychange', updateClock);
        }
    })();

    // Live-refresh the announcement marquee without a page reload. WebSocket is
    // the fast path; a visibility-aware timer is only a recovery/safety path.
    (function () {
        var bar = document.querySelector('.advisory-bar .marquee');
        if (!bar) return;
        var FALLBACK = <?= json_encode('Welcome to ' . app_name() . ' Terminal. Check schedules and fares for your trip.') ?>;
        var firstCopy = bar.querySelector('.marquee-copy');
        var lastText = firstCopy ? firstCopy.textContent.trim() : '';
        var lastModalSignature = null;
        function appendAnnouncementText(container, message) {
            var text = String(message || '');
            var urlPattern = /https?:\/\/[^\s<>]+/gi;
            var cursor = 0;
            var match;
            while ((match = urlPattern.exec(text)) !== null) {
                container.appendChild(document.createTextNode(text.slice(cursor, match.index)));
                var url = match[0].replace(/[.,;!?:)]+$/, '');
                var trailing = match[0].slice(url.length);
                try {
                    var parsed = new URL(url);
                    if (parsed.protocol !== 'http:' && parsed.protocol !== 'https:') throw new Error('Invalid link');
                    var link = document.createElement('a');
                    link.href = parsed.href;
                    link.target = '_blank';
                    link.rel = 'noopener noreferrer';
                    link.textContent = url;
                    container.appendChild(link);
                } catch (e) {
                    container.appendChild(document.createTextNode(url));
                }
                container.appendChild(document.createTextNode(trailing));
                cursor = match.index + match[0].length;
            }
            container.appendChild(document.createTextNode(text.slice(cursor)));
        }
        function renderAnnouncementList(announcements, modalList, countBadge) {
            var signature = JSON.stringify(announcements.map(function(a) { return [a.id, a.message, a.severity]; }));
            if (signature === lastModalSignature) return;
            lastModalSignature = signature;
            var fragment = document.createDocumentFragment();
            announcements.forEach(function(a) {
                var message = String(a.message || '').trim();
                if (!message) return;
                var severity = a.severity === 'danger' || a.severity === 'warning' ? a.severity : 'info';
                var icon = severity === 'danger' ? 'fa-circle-exclamation' : severity === 'warning' ? 'fa-triangle-exclamation' : 'fa-info-circle';
                var label = severity === 'danger' ? 'Urgent' : severity === 'warning' ? 'Warning' : 'Notice';
                var item = document.createElement('li');
                item.className = 'ann-item-' + severity;
                var bullet = document.createElement('div');
                bullet.className = 'ann-bullet-wrap ann-bullet-' + severity;
                var iconEl = document.createElement('i');
                iconEl.className = 'fas ' + icon;
                bullet.appendChild(iconEl);
                var body = document.createElement('div');
                body.className = 'ann-modal-text';
                var tag = document.createElement('div');
                tag.className = 'ann-severity-tag ann-tag-' + severity;
                tag.textContent = label;
                var content = document.createElement('div');
                appendAnnouncementText(content, message);
                body.appendChild(tag);
                body.appendChild(content);
                item.appendChild(bullet);
                item.appendChild(body);
                fragment.appendChild(item);
            });
            if (!fragment.childNodes.length) {
                var empty = document.createElement('li');
                empty.className = 'ann-modal-empty';
                empty.textContent = 'No active announcements at this time.';
                fragment.appendChild(empty);
            }
            if (countBadge) countBadge.textContent = announcements.length ? announcements.length + ' Active' : 'Live';
            while (modalList.firstChild) modalList.removeChild(modalList.firstChild);
            modalList.appendChild(fragment);
        }
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
                    if (text !== lastText && firstCopy) {
                        lastText = text;
                        firstCopy.textContent = text;
                        if (window.refreshGuestMarquee) window.refreshGuestMarquee(true);
                    }
                    // Also update modal list
                    var modalList = document.getElementById('annModalList');
                    var countBadge = document.getElementById('annCountBadge');
                    if (modalList) renderAnnouncementList(d.announcements, modalList, countBadge);
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
            overlay.classList.toggle('active', isOpen);
        }
        if (toggle) {
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            toggle.setAttribute('aria-label', isOpen ? 'Close navigation menu' : 'Open navigation menu');
        }

        if (isOpen) {
            if (icon) icon.className = 'fas fa-bars';
            if (toggle) toggle.classList.remove('is-closing');
            document.body.style.overflow = 'hidden';
            return;
        }

        var finishClose = function(event) {
            if (event && event.target !== m) return;
            if (closeSequence !== guestMenuCloseSequence || m.classList.contains('open')) return;
            if (icon) icon.className = 'fas fa-bars';
            if (toggle) {
                toggle.classList.remove('is-closing');
                toggle.blur();
            }
            document.body.style.overflow = '';
        };
        m.addEventListener('transitionend', finishClose, { once: true });
        finishClose();
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
        var lastPadding = -1;
        function syncPadding() {
            var h = bar.offsetHeight;
            if (h !== lastPadding) {
                lastPadding = h;
                document.body.style.paddingTop = h + 'px';
            }
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
        var advisory = document.querySelector('.advisory-bar');
        if (advisory) advisory.classList.add('modal-ann-open');
    }
    function closeAnnouncementModal(e) {
        if (e && e.target !== e.currentTarget) return;
        document.getElementById('annModalOverlay').classList.remove('open');
        var advisory = document.querySelector('.advisory-bar');
        if (advisory) advisory.classList.remove('modal-ann-open');
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.left = '';
        document.body.style.right = '';
        // Return to the exact spot instead of the top.
        window.scrollTo(0, _annScrollY);
    }

</script>

