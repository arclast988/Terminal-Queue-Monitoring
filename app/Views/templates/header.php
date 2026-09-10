<!DOCTYPE html>
<html lang="en" class="layout-lock">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Terminal Monitoring System' ?></title>
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="csrf-header" content="<?= csrf_header() ?>">
    <meta name="color-scheme" content="light">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= base_url('images/9HFScgVg_400x400.png') ?>">
    <link rel="shortcut icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('apple-touch-icon.png') ?>">

    <!-- Modern Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap (Keep for layout if needed by other pages, but navbar uses custom CSS now) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Design system: shared tokens/components, role themes, then the legacy-class bridge -->
    <link rel="stylesheet" href="<?= base_url('assets/css/design-system.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/themes.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/legacy-bridge.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">

    <!-- Modern Admin Styling (for logged-in users) - Remove this line to rollback -->
    <?php if (session()->get('isLoggedIn')): ?>
        <link rel="stylesheet" href="<?= base_url('assets/css/admin-modern.css') ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= base_url('assets/css/responsive.css?v=20260910_1') ?>">
    <?= vehicle_type_colors_css() ?>


    <style>
        /* Targeted lightweight transitions for interactive elements */
        .btn, a, .card, .vf-btn {
            transition: opacity 0.15s ease, background-color 0.15s ease, transform 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
        }



        /* Disable tooltip/popover animations */
        .tooltip-inner,
        .popover {
            animation: none !important;
        }

        /* Ensure proper background colors */
        html, body {
            background-color: #f8fafc !important;
        }

        /* Theme tokens are defined in public/assets/css/design-system.css and
           public/assets/css/themes.css. Removing duplicate variable declarations
           here reduces cascade conflicts and keeps the source of truth in the
           stylesheet files. Keep only safe fallbacks for background and text. */

        /* Admin theme: keep color application but prefer tokens with fallbacks */
        body.admin-theme {
            background-color: var(--bg-main) !important;
            color: var(--text-main) !important;
        }

        body.admin-theme .main-content {
            background-color: transparent !important;
        }

        body.admin-theme .border-bottom {
            border-color: var(--border) !important;
        }

        body.admin-theme .text-muted {
            color: var(--text-muted) !important;
        }
        
        body.admin-theme .card .text-muted,
        body.admin-theme .table .text-muted,
        body.admin-theme .list-group-item .text-muted {
            color: var(--text-muted) !important; 
        }

        body.admin-theme .h2,
        body.admin-theme .h1,
        body.admin-theme h1:not(.logo-text h1),
        body.admin-theme h2 {
            color: var(--text-main) !important;
        }

        body.admin-theme #site-header .logo-text h1,
        body.admin-theme .logo-text h1,
        body.staff-theme #site-header .logo-text h1,
        body.staff-theme .logo-text h1 {
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
        }



        body.admin-theme .card:not(.text-white) {
            background-color: var(--surface) !important;
            border-color: var(--border) !important;
            border: 1px solid var(--border) !important;
            color: var(--text-main) !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
            backdrop-filter: blur(12px) saturate(150%);
            -webkit-backdrop-filter: blur(12px) saturate(150%);
        }

        body.admin-theme .card.text-white {
            border: 1px solid rgba(0, 0, 0, 0.1) !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
        }

        body.admin-theme .card.text-white h2,
        body.admin-theme .card.text-white h6,
        body.admin-theme .card.text-white .card-title,
        body.admin-theme .card.text-white .card-body,
        body.admin-theme .card.text-white .card-footer a {
            color: white !important;
        }

        body.admin-theme .table {
            color: var(--text-main);
        }

        body.admin-theme .table-light {
            background-color: var(--surface-sunken, #f8fafc);
        }

        body.admin-theme .table-dark {
            background-color: var(--primary);
        }

        body.admin-theme .bg-primary {
            background-color: var(--primary) !important;
            border-color: var(--primary) !important;
        }

        body.admin-theme .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        body.admin-theme .btn-outline-secondary {
            border-color: var(--border-strong, #cbd5e1);
            color: var(--text-muted, #475569);
        }

        body.admin-theme .btn-outline-secondary:hover {
            background-color: var(--surface-sunken, #f1f5f9);
            border-color: var(--border-strong, #94a3b8);
            color: var(--text-main, #1e293b);
        }

        /* Quick Actions: readable button and natural dropdown on admin dashboard */
        body.admin-theme .table .btn-outline-dark {
            border-color: var(--border);
            color: var(--text-main);
            background: var(--surface);
        }

        body.admin-theme .table .btn-outline-dark:hover {
            background-color: var(--surface-sunken);
            border-color: var(--border-strong);
            color: var(--text-main);
        }

        /* Staff theme: keep color application but prefer tokens with fallbacks */
        body.staff-theme {
            background-color: var(--bg-main) !important;
            color: var(--text-main) !important;
        }

        body.staff-theme .main-content {
            background-color: transparent !important;
        }

        body.staff-theme .border-bottom {
            border-color: var(--border) !important;
        }

        body.staff-theme .text-muted {
            color: var(--text-muted) !important;
        }
        
        body.staff-theme .card .text-muted,
        body.staff-theme .table .text-muted,
        body.staff-theme .list-group-item .text-muted {
            color: var(--text-muted) !important; 
        }

        body.staff-theme .h2,
        body.staff-theme .h1,
        body.staff-theme h1:not(.logo-text h1),
        body.staff-theme h2 {
            color: var(--text-main) !important;
        }

        body.staff-theme .card:not(.text-white) {
            background-color: var(--surface) !important;
            border-color: var(--border) !important;
            border: 1px solid var(--border) !important;
            color: var(--text-main) !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
            backdrop-filter: blur(12px) saturate(150%);
            -webkit-backdrop-filter: blur(12px) saturate(150%);
        }

        body.staff-theme .card.text-white {
            border: 1px solid rgba(0, 0, 0, 0.1) !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
        }

        body.staff-theme .card.text-white h2,
        body.staff-theme .card.text-white h6,
        body.staff-theme .card.text-white .card-title,
        body.staff-theme .card.text-white .card-body,
        body.staff-theme .card.text-white .card-footer a {
            color: white !important;
        }

        body.staff-theme .table {
            color: var(--text-main);
        }

        body.staff-theme .table-light {
            background-color: var(--surface-sunken, #f8fafc);
        }

        body.staff-theme .table-dark {
            background-color: var(--primary);
        }

        body.staff-theme .bg-primary {
            background-color: var(--primary) !important;
            border-color: var(--primary) !important;
        }

        body.staff-theme .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        body.staff-theme .btn-outline-secondary {
            border-color: var(--border-strong, #cbd5e1);
            color: var(--text-muted, #64748b);
        }

        body.staff-theme .btn-outline-secondary:hover {
            background-color: var(--surface-sunken, #f1f5f9);
            border-color: var(--border-strong, #94a3b8);
            color: var(--text-main, #1e293b);
        }

        /* Quick Actions: readable button and natural dropdown on staff dashboard */
        body.staff-theme .table .btn-outline-dark {
            border-color: var(--border);
            color: var(--text-main);
            background: var(--surface);
        }

        body.staff-theme .table .btn-outline-dark:hover {
            background-color: var(--surface-sunken);
            border-color: var(--border-strong);
            color: var(--text-main);
        }

        /* Lock layout: no horizontal shift, scrollbar space always reserved */
        html {
            overflow-y: auto;
            overflow-x: hidden;
            max-width: 100%;
            box-sizing: border-box;
        }

        html *,
        html *::before,
        html *::after {
            box-sizing: inherit;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: 'Outfit', sans-serif;
            background-color: #f8fafc;
            color: var(--text-main);
            margin: 0;
            overflow-x: hidden;
            max-width: 100%;
        }

        :root {
            --site-header-height: 80px;
        }

        /* Reserve space for fixed header with comfortable, elegant breathing room */
        .main-content {
            flex: 1 0 auto !important;
            width: 100% !important;
            max-width: 100% !important;
            padding-top: calc(var(--site-header-height, 80px) + 10px) !important;
            padding-right: 24px !important;
            padding-bottom: 30px !important;
            padding-left: 24px !important;
            background-color: transparent !important;
            box-sizing: border-box !important;
        }

        footer,
        .footer,
        footer.footer,
        body.admin-theme footer,
        body.admin-theme .footer,
        body.staff-theme footer,
        body.staff-theme .footer {
            flex-shrink: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            margin: auto 0 0 0 !important;
            padding: 16px 24px !important;
            background: rgba(255, 255, 255, 0.75) !important;
            backdrop-filter: blur(12px) saturate(160%) !important;
            -webkit-backdrop-filter: blur(12px) saturate(160%) !important;
            border-top: 1px solid rgba(226, 232, 240, 0.8) !important;
            border-radius: 0 !important;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.02) !important;
            text-align: center !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: #64748b !important;
            font-weight: 500 !important;
            font-size: 13.5px !important;
            position: relative !important;
            z-index: 10 !important;
            box-sizing: border-box !important;
        }

        footer p,
        .footer p,
        body.admin-theme footer p,
        body.admin-theme .footer p,
        body.staff-theme footer p,
        body.staff-theme .footer p {
            margin: 0 !important;
            padding: 0 !important;
            color: inherit !important;
            font-size: inherit !important;
            font-weight: inherit !important;
            text-align: center !important;
            width: 100% !important;
        }

        /* Schedules/Fares: same header as dashboard, but content area full-width (no padding) */
        body.public-page .main-content {
            padding: 0 !important;
            padding-top: calc(var(--site-header-height, 80px) + 10px) !important;
        }

        /* Force header to stay fixed - cannot be overridden by page styles */
        html,
        html.layout-lock {
            height: 100% !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        body,
        body.layout-lock {
            min-height: 100vh !important;
            display: flex !important;
            flex-direction: column !important;
            overflow-x: hidden !important;
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        body.layout-lock header#site-header {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            padding-left: 24px !important;
            padding-right: 24px !important;
        }

        /* Mobile responsive main content padding */
        @media (max-width: 768px) {
            :root {
                --site-header-height: 72px;
            }

            body.layout-lock header#site-header {
                padding-left: 16px !important;
                padding-right: 16px !important;
            }

            .main-content {
                padding-top: calc(var(--site-header-height, 72px) + 12px) !important;
                padding-right: 16px !important;
                padding-bottom: 24px !important;
                padding-left: 16px !important;
            }

            footer,
            .footer,
            body.admin-theme footer,
            body.admin-theme .footer,
            body.staff-theme footer,
            body.staff-theme .footer {
                padding: 14px 16px !important;
                font-size: 12.5px !important;
            }

            .row {
                margin-left: -7.5px;
                margin-right: -7.5px;
            }

            .row > * {
                padding-left: 7.5px;
                padding-right: 7.5px;
            }
        }

        @media (max-width: 480px) {
            :root {
                --site-header-height: 52px;
            }

            body.layout-lock header#site-header {
                padding-left: 10px !important;
                padding-right: 10px !important;
            }

            .main-content {
                padding-top: calc(var(--site-header-height, 52px) + 12px) !important;
                padding-right: 10px !important;
                padding-bottom: 20px !important;
                padding-left: 10px !important;
            }

            .row {
                margin-left: -5px !important;
                margin-right: -5px !important;
            }

            .row > * {
                padding-left: 5px !important;
                padding-right: 5px !important;
            }
        }

        /* Custom badge colors */
        .bg-purple {
            background-color: #6a1b9a !important;
        }


        /*
         * Bootstrap appends .modal-backdrop directly to <body>, while page
         * modals are rendered inside .main-content. The watermark layer
         * (body > *) gives body children position:relative; z-index:1,
         * creating stacking contexts that trap nested modals below the backdrop.
         *
         * Solution: When a modal is active (body.modal-open), remove position:relative
         * and z-index from .main-content so it no longer creates an isolated
         * stacking context. This lets nested modals (z-index: 1055) paint above
         * .modal-backdrop (z-index: 1040) while keeping .main-content covered.
         */

        /* Backdrop sits above header (1030) and page content */
        body > .modal-backdrop {
            position: fixed !important;
            z-index: 1040 !important;
            opacity: 0.5 !important;
        }

        /* Remove stacking context on .main-content when modal is open */
        body.modal-open > .main-content {
            position: static !important;
            z-index: auto !important;
            transform: none !important;
            filter: none !important;
        }

        /* All modals (whether in body or nested in .main-content) sit above backdrop */
        body.modal-open .modal {
            position: fixed !important;
            z-index: 1055 !important;
        }

        /* Navigation header sits below backdrop */
        body.modal-open header#site-header {
            z-index: 1030 !important;
        }

        /* Watermark slideshow stays at base background layer */
        body.modal-open::after {
            z-index: 0 !important;
        }
    </style>
</head>

<body
    class="layout-lock <?= (in_array(session()->get('role'), ['super_admin', 'admin'], true) ? 'admin-theme ' : (session()->get('role') === 'staff' ? 'staff-theme ' : '')) ?><?= esc($body_class ?? '') ?>">
    <?php include __DIR__ . '/navbar.php'; ?>
    <script>
        (function() {
            function syncHeaderHeight() {
                var hdr = document.getElementById('site-header');
                if (hdr && hdr.offsetHeight > 0) {
                    document.documentElement.style.setProperty('--site-header-height', hdr.offsetHeight + 'px');
                }
            }
            syncHeaderHeight();
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', syncHeaderHeight);
            }
            window.addEventListener('load', syncHeaderHeight);
            if (window.ResizeObserver) {
                var el = document.getElementById('site-header');
                if (el) new ResizeObserver(syncHeaderHeight).observe(el);
            }
            window.addEventListener('resize', syncHeaderHeight, { passive: true });
        })();
    </script>
    <div class="main-content container-fluid">
