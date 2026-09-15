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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
    /* --- Advisory Bar --- */
    .advisory-bar {
        background: linear-gradient(135deg, #B71C1C 0%, #7F0000 100%);
        color: #ffffff;
        padding: 0 20px;
        min-height: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        position: relative;
        z-index: 10;
        box-sizing: border-box;
    }
    .advisory-icon {
        width: 28px;
        height: 28px;
        min-width: 28px;
        background: #ffffff;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #B71C1C;
        font-size: 13px;
        font-weight: 900;
        flex-shrink: 0;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0,0,0,0.25);
    }
    .advisory-icon:hover {
        box-shadow: 0 0 0 3px rgba(255,255,255,0.35);
    }
    .advisory-text {
        overflow: hidden;
        white-space: nowrap;
        display: flex;
        align-items: center;
        flex: 1 1 auto;
        min-width: 0;
        height: 100%;
        margin: 0;
        padding: 0;
    }
    .marquee {
        display: inline-flex;
        align-items: center;
        height: 100%;
        line-height: 42px !important;
        padding-left: 100%;
        white-space: nowrap !important;
        animation: gh-marquee var(--marquee-duration, 35s) linear infinite;
        animation-delay: var(--marquee-delay, 0s);
        font-family: 'Outfit', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        font-weight: 800 !important;
        font-size: 16px !important;
        letter-spacing: 0.35px;
        color: #ffffff !important;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        margin: 0;
        padding-top: 0;
        padding-bottom: 0;
    }
    .marquee:hover {
        animation-play-state: paused;
    }

    /* --- Announcement Modal --- */
    .ann-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        z-index: 99999;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .ann-modal-overlay.open {
        display: flex;
    }
    .ann-modal {
        background: #ffffff;
        border-radius: 20px;
        max-width: 600px;
        width: 100%;
        max-height: 82vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 25px 60px -15px rgba(0,0,0,0.35), 0 0 0 1px rgba(0,0,0,0.06);
        animation: annSlideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .ann-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 24px;
        background: linear-gradient(135deg, #B71C1C 0%, #7F0000 100%);
        border-radius: 20px 20px 0 0;
        color: white;
        flex-shrink: 0;
        border-bottom: 1px solid rgba(255,255,255,0.12);
    }
    .ann-modal-header-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .ann-modal-header-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        color: #ffffff;
        flex-shrink: 0;
    }
    .ann-modal-header h3 {
        margin: 0;
        font-size: 17px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
        color: #ffffff;
        letter-spacing: -0.2px;
    }
    .ann-modal-header-badge {
        font-size: 11px;
        font-weight: 700;
        background: rgba(255,255,255,0.22);
        color: #ffffff;
        padding: 3px 8px;
        border-radius: 9999px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        flex-shrink: 0;
    }
    .ann-modal-close {
        background: rgba(255,255,255,0.18);
        border: none;
        color: white;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 18px;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.15s ease, transform 0.15s ease;
    }
    .ann-modal-close:hover {
        background: rgba(255,255,255,0.35);
    }
    .ann-modal-body {
        padding: 20px 24px;
        overflow-y: auto;
        overflow-x: hidden;
        flex: 1 1 auto;
        background: #f8fafc;
    }
    .ann-modal-body::-webkit-scrollbar {
        width: 6px;
    }
    .ann-modal-body::-webkit-scrollbar-track {
        background: transparent;
    }
    .ann-modal-body::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 9999px;
    }
    .ann-modal-body::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    .ann-modal-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .ann-modal-list li {
        padding: 16px 18px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        display: flex;
        align-items: flex-start;
        gap: 14px;
        font-size: 14.5px;
        color: #1e293b;
        line-height: 1.65;
        word-break: break-word;
        overflow-wrap: anywhere;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .ann-modal-list li:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 14px rgba(0,0,0,0.06);
    }
    .ann-bullet-wrap {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #fee2e2;
        color: #B71C1C;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 12px;
        margin-top: 1px;
    }
    .ann-item-danger {
        border-left: 4px solid #dc2626 !important;
    }
    .ann-item-warning {
        border-left: 4px solid #d97706 !important;
    }
    .ann-item-info {
        border-left: 4px solid #0284c7 !important;
    }
    .ann-bullet-danger {
        background: #fee2e2 !important;
        color: #dc2626 !important;
    }
    .ann-bullet-warning {
        background: #fef3c7 !important;
        color: #d97706 !important;
    }
    .ann-bullet-info {
        background: #e0f2fe !important;
        color: #0284c7 !important;
    }
    .ann-severity-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 2px 8px;
        border-radius: 6px;
        margin-bottom: 5px;
        line-height: 1.4;
    }
    .ann-tag-danger { background: #fee2e2; color: #991b1b; }
    .ann-tag-warning { background: #fef3c7; color: #92400e; }
    .ann-tag-info { background: #e0f2fe; color: #075985; }
    .ann-modal-text {
        flex: 1 1 auto;
        min-width: 0;
        word-break: break-word;
        overflow-wrap: anywhere;
        white-space: pre-line;
    }
    .ann-modal-text a {
        color: #B71C1C;
        font-weight: 600;
        text-decoration: underline;
        text-underline-offset: 2px;
        word-break: break-all;
    }
    .ann-modal-text a:hover {
        color: #7F0000;
    }
    .ann-modal-empty {
        text-align: center;
        color: #64748b;
        padding: 40px 20px;
        font-size: 14.5px;
        background: #ffffff;
        border: 1px dashed #cbd5e1;
        border-radius: 14px;
    }
    .ann-modal-empty i {
        font-size: 34px;
        margin-bottom: 12px;
        display: block;
        color: #cbd5e1;
    }
    .ann-modal-footer {
        padding: 12px 24px;
        background: #ffffff;
        border-top: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    }
    .ann-modal-footer-status {
        font-size: 12px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .ann-modal-footer-status i {
        color: #10b981;
        font-size: 8px;
    }
    .ann-modal-btn-close {
        padding: 6px 18px;
        font-size: 13px;
        font-weight: 600;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #334155;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .ann-modal-btn-close:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
        color: #0f172a;
    }
    @media (max-width: 640px) {
        .ann-modal {
            max-height: 86vh;
            border-radius: 16px;
            margin: 8px;
            width: calc(100% - 16px);
        }
        .ann-modal-header {
            padding: 12px 16px;
            border-radius: 16px 16px 0 0;
            gap: 8px;
        }
        .ann-modal-header-left {
            gap: 10px;
            min-width: 0;
            flex: 1 1 auto;
        }
        .ann-modal-header-icon {
            width: 32px;
            height: 32px;
            font-size: 14px;
            border-radius: 8px;
        }
        .ann-modal-header h3 {
            font-size: 15px;
            gap: 6px;
            white-space: nowrap;
        }
        .ann-modal-header-badge {
            font-size: 10px;
            padding: 2px 7px;
            letter-spacing: 0.3px;
        }
        .ann-modal-body {
            padding: 12px 10px;
            -webkit-overflow-scrolling: touch;
        }
        .ann-modal-list {
            gap: 10px;
        }
        .ann-modal-list li {
            padding: 12px 12px;
            gap: 10px;
            font-size: 13.5px;
            border-radius: 12px;
        }
        .ann-bullet-wrap {
            width: 24px;
            height: 24px;
            font-size: 10.5px;
            margin-top: 1px;
        }
        .ann-modal-footer {
            padding: 10px 14px;
        }
        .ann-modal-footer-status {
            font-size: 11px;
        }
        .ann-modal-btn-close {
            padding: 5px 14px;
            font-size: 12px;
        }
    }
    @keyframes annSlideUp {
        from { transform: translateY(20px); opacity: 0; }
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
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
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
    @media (max-width: 1200px) and (min-width: 769px) {
        .guest-header .header-info {
            padding-left: 14px;
            display: flex !important;
            height: 24px;
        }
        .header-clock-pill {
            font-size: 13.5px;
            gap: 5px;
        }
        .header-clock-pill i {
            font-size: 13px;
        }
    }

    @media (max-width: 768px) {
        .advisory-bar {
            padding: 0 14px;
            min-height: 38px;
            height: 38px;
            gap: 10px;
        }
        .advisory-icon {
            width: 24px;
            height: 24px;
            min-width: 24px;
            font-size: 11px;
        }
        .marquee {
            font-size: 14px !important;
            line-height: 38px !important;
            font-weight: 800 !important;
            white-space: nowrap !important;
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
            padding: 0 10px;
            min-height: 36px;
            height: 36px;
            gap: 8px;
        }
        .advisory-icon {
            width: 22px;
            height: 22px;
            min-width: 22px;
            font-size: 10px;
        }
        .marquee {
            font-size: 13px !important;
            line-height: 36px !important;
            font-weight: 800 !important;
            white-space: nowrap !important;
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

    /* Print Styles */
    @media print {
        .sticky-top-wrapper,
        .advisory-bar,
        .ann-modal-overlay,
        .guest-header,
        .breadcrumb-section,
        .mobile-nav-overlay {
            display: none !important;
        }
    }
</style>

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
    <div class="logo-container" style="display: flex; align-items: center; gap: 24px;">
        <a href="<?= base_url('guest') ?>" class="logo-section">
            <img src="<?= esc(app_logo()) ?>" alt="<?= esc(app_name()) ?> Logo" class="logo">
            <div class="logo-text">
                <h1><?= esc(app_name()) ?> </h1>
                <p><?= esc(app_subtitle()) ?></p>
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

    <div class="mobile-toggle" onclick="toggleMenu()">
        <i class="fas fa-bars" id="mobileMenuIcon"></i>
    </div>

    <!-- Mobile overlay backdrop -->
    <div class="mobile-nav-overlay" id="mobileNavOverlay" onclick="toggleMenu()"></div>
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

        // Periodic background fallback poll
        setInterval(refreshAnnouncements, 20000);
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

