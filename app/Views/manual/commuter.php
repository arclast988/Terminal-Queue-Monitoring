<!DOCTYPE html>
<html lang="en">
<head>
    <?= view('partials/maze_snippet') ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="only light">
    <title><?= esc($title ?? 'Commuter User Guide & Error Help - ' . app_name()) ?></title>
    <?= view('partials/app_install') ?>

    <!-- Modern Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome & Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/design-system.css') ?>?v=20260927a">
    <link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>?v=20260926b">
    <link rel="stylesheet" href="<?= base_url('assets/css/responsive.css') ?>?v=20260926b">
    <?= app_theme_css() ?>

    <style>
        :root {
            --accent: #FFA726;
            --text-main: #0f172a;
            --text-muted: #475569;
            --bg-body: #f8fafc;
        }

        *, *::before, *::after {
            -webkit-tap-highlight-color: transparent !important;
        }

        button, .btn, [role="button"], a {
            -webkit-tap-highlight-color: transparent !important;
        }

        button:focus:not(:focus-visible),
        .btn:focus:not(:focus-visible),
        [role="button"]:focus:not(:focus-visible),
        a:focus:not(:focus-visible) {
            outline: none !important;
            box-shadow: none !important;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg-body);
            color: var(--text-main);
            line-height: 1.6;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
        }

        .guide-container {
            width: 92%;
            max-width: 1200px;
            margin: 24px auto 40px !important;
            padding: 0 10px;
            box-sizing: border-box;
        }

        /* Hero Banner */
        .guide-hero {
            background: linear-gradient(135deg, var(--primary, #B71C1C) 0%, var(--primary-dark, #7F0000) 100%);
            color: #ffffff !important;
            border-radius: 18px;
            padding: 32px 28px;
            margin-bottom: 24px;
            box-shadow: 0 10px 25px -5px var(--primary-soft, rgba(183, 28, 28, 0.28));
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            width: 100%;
            box-sizing: border-box;
        }

        html body .guide-hero h1,
        html body .guide-hero .hero-title {
            color: #ffffff !important;
            font-size: clamp(20px, 4vw, 28px);
            font-weight: 800;
            margin: 0 0 10px;
            display: flex;
            align-items: center;
            gap: 12px;
            line-height: 1.3;
        }

        html body .guide-hero p,
        html body .guide-hero .hero-subtitle {
            font-size: 14.5px;
            color: #fee2e2 !important;
            margin: 0;
            max-width: 720px;
            line-height: 1.6;
        }

        .guide-hero-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .btn-hero-action {
            background: #ffffff;
            color: var(--primary, #B71C1C) !important;
            font-weight: 700;
            border: none;
            padding: 10px 18px;
            border-radius: 10px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 13.5px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }

        .btn-hero-action:hover {
            background: #fef2f2;
            color: var(--primary-dark, #7F0000) !important;
            transform: translateY(-1px);
        }

        .btn-hero-outline {
            background: rgba(255, 255, 255, 0.16) !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.35) !important;
            box-shadow: none !important;
        }

        .btn-hero-outline:hover {
            background: rgba(255, 255, 255, 0.28) !important;
            color: #ffffff !important;
        }

        /* Mobile Topic Dropdown Selector (< 520px) */
        .manual-mobile-select-wrap {
            display: none;
            margin-bottom: 20px;
            background: white;
            padding: 12px 14px;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            box-shadow: 0 2px 5px rgba(0,0,0,0.04);
        }

        .manual-mobile-select-label {
            display: block;
            font-size: 12px;
            font-weight: 800;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .manual-mobile-select {
            width: 100%;
            padding: 10px 14px;
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            background-color: #f8fafc;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            font-family: inherit;
            cursor: pointer;
            outline: none;
        }

        .manual-mobile-select:focus {
            border-color: #B71C1C;
            background: white;
        }

        /* Nav Pills */
        .guide-nav-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 10px;
            align-items: center;
            padding-bottom: 12px;
            margin-bottom: 24px;
            border-bottom: 2px solid #e2e8f0;
            width: 100%;
            box-sizing: border-box;
        }

        .guide-pill-btn {
            background: white;
            border: 1px solid #cbd5e1;
            color: #334155;
            padding: 9px 16px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            white-space: nowrap;
            user-select: none;
        }

        .guide-pill-btn:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .guide-pill-btn.active {
            background: var(--primary, #B71C1C);
            border-color: var(--primary, #B71C1C);
            color: var(--on-primary, #ffffff);
            box-shadow: 0 4px 12px var(--primary-soft, rgba(183, 28, 28, 0.25));
        }

        @media (max-width: 640px) {
            .guide-nav-pills {
                flex-wrap: nowrap;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                padding-bottom: 10px;
                scrollbar-width: thin;
                scrollbar-color: #cbd5e1 transparent;
            }
            .guide-nav-pills::-webkit-scrollbar {
                display: block;
                height: 4px;
            }
            .guide-nav-pills::-webkit-scrollbar-track {
                background: #f1f5f9;
                border-radius: 4px;
            }
            .guide-nav-pills::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 4px;
            }
            .guide-pill-btn {
                flex-shrink: 0;
            }
        }

        /* Content Cards */
        .tab-content {
            display: none;
            width: 100%;
            box-sizing: border-box;
        }

        .tab-content.active {
            display: block;
            width: 100%;
            animation: fadeInGuide 0.25s ease;
        }

        @keyframes fadeInGuide {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .guide-card {
            background: white;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            padding: 28px 24px;
            margin-bottom: 24px;
            width: 100%;
            box-sizing: border-box;
            overflow-wrap: break-word;
            word-break: break-word;
        }

        .guide-card h3 {
            font-size: clamp(17px, 3vw, 20px);
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 2px solid #f8fafc;
            padding-bottom: 12px;
            line-height: 1.35;
        }

        .guide-card h4 {
            font-size: 15.5px;
            font-weight: 700;
            color: #1e293b;
            margin: 20px 0 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Step Items */
        .step-item {
            display: flex;
            gap: 14px;
            margin-bottom: 18px;
            align-items: flex-start;
        }

        .step-number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #fee2e2;
            color: #B71C1C;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 13.5px;
        }

        .step-text {
            flex: 1;
            min-width: 0;
        }

        .step-text h5 {
            font-size: 15px;
            font-weight: 700;
            margin: 0 0 4px;
            color: #0f172a;
        }

        .step-text p {
            font-size: 13.5px;
            color: #475569;
            margin: 0;
            line-height: 1.55;
        }

        /* Badges Demo */
        .badge-demo {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 12px;
            gap: 6px;
            white-space: nowrap;
        }
        .badge-demo.boarding { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge-demo.waiting { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-demo.next { background: #ffedd5; color: #9a3412; border: 1px solid #fed7aa; }
        .badge-demo.full { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-demo.departed { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

        /* Responsive Mobile Badge Grid Cards */
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 12px;
            margin: 16px 0;
        }

        .status-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px;
            box-sizing: border-box;
        }

        .status-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
            flex-wrap: wrap;
            gap: 6px;
        }

        .status-card-title {
            font-size: 13.5px;
            font-weight: 700;
            color: #0f172a;
        }

        .status-card p {
            font-size: 13px;
            color: #475569;
            margin: 0;
            line-height: 1.5;
        }

        .status-action-tag {
            display: inline-block;
            margin-top: 8px;
            font-size: 11.5px;
            font-weight: 700;
            color: #047857;
            background: #ecfdf5;
            padding: 3px 8px;
            border-radius: 6px;
        }

        /* Callouts */
        .callout-box {
            background: #f8fafc;
            border-left: 4px solid #B71C1C;
            padding: 14px 16px;
            border-radius: 0 10px 10px 0;
            margin: 16px 0;
            font-size: 13.5px;
            color: #1e293b;
            line-height: 1.6;
            box-sizing: border-box;
            width: 100%;
        }

        .callout-box.info {
            background: #eff6ff;
            border-left-color: #2563eb;
            color: #1e40af;
        }

        .callout-box.warning {
            background: #fffbeb;
            border-left-color: #d97706;
            color: #92400e;
        }

        .callout-box.error {
            background: #fef2f2;
            border-left-color: #ef4444;
            color: #991b1b;
        }

        .callout-box.success {
            background: #f0fdf4;
            border-left-color: #16a34a;
            color: #166534;
        }

        /* Visual seat demo */
        .seat-bar-demo {
            background: #e2e8f0;
            border-radius: 999px;
            height: 14px;
            overflow: hidden;
            margin: 8px 0;
            display: flex;
        }
        .seat-bar-fill {
            background: #16a34a;
            height: 100%;
            border-radius: 999px;
            transition: width 0.3s;
        }

        /* Mobile Screens Under 400px */
        @media (max-width: 520px) {
            .guide-container {
                width: 100% !important;
                max-width: 100% !important;
                margin: 12px 0 30px !important;
                padding: 0 8px !important;
            }
            .guide-hero {
                padding: 20px 16px;
                border-radius: 14px;
                margin-bottom: 16px;
            }
            html body .guide-hero h1,
            html body .guide-hero .hero-title {
                font-size: 18px;
                gap: 8px;
            }
            html body .guide-hero p,
            html body .guide-hero .hero-subtitle {
                font-size: 12.5px;
                line-height: 1.45;
            }
            .guide-hero-actions {
                width: 100%;
                margin-top: 6px;
            }
            .btn-hero-action {
                width: 100%;
                font-size: 13px;
                padding: 9px 14px;
            }
            .manual-mobile-select-wrap {
                display: block;
            }
            .guide-nav-pills {
                padding-bottom: 6px;
                margin-bottom: 16px;
                gap: 6px;
            }
            .guide-pill-btn {
                padding: 8px 12px;
                font-size: 12.5px;
                border-radius: 8px;
            }
            .guide-card {
                padding: 16px 12px;
                border-radius: 12px;
                margin-bottom: 16px;
            }
            .guide-card h3 {
                font-size: 16px;
                padding-bottom: 8px;
                margin-bottom: 12px;
                gap: 8px;
            }
            .step-item {
                gap: 10px;
                margin-bottom: 14px;
            }
            .step-number {
                width: 28px;
                height: 28px;
                font-size: 12px;
            }
            .step-text h5 {
                font-size: 14px;
            }
            .step-text p {
                font-size: 12.5px;
            }
            .callout-box {
                padding: 12px;
                font-size: 12.5px;
                border-radius: 0 8px 8px 0;
            }
            .status-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 360px) {
            .guide-hero {
                padding: 16px 12px;
            }
            html body .guide-hero h1,
            html body .guide-hero .hero-title {
                font-size: 17px;
            }
            .guide-card {
                padding: 14px 10px;
            }
        }

    /* =================================================================
       OFFICIAL PRINT HANDBOOK STYLES
       ================================================================= */
    @media print {
        @page {
            size: A4 portrait;
            margin: 12mm 15mm 12mm 15mm;
        }

        html, html.layout-lock, body, body.layout-lock {
            height: auto !important;
            min-height: auto !important;
            overflow: visible !important;
            overflow-x: visible !important;
            overflow-y: visible !important;
            display: block !important;
            position: static !important;
            background: #ffffff !important;
            background-image: none !important;
            color: #0f172a !important;
            margin: 0 !important;
            padding: 0 !important;
            font-family: 'Outfit', sans-serif !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Completely remove background photos, slideshows, watermarks and overlays */
        body::before,
        body::after,
        html::before,
        html::after,
        body.admin-theme::before,
        body.admin-theme::after,
        body.staff-theme::before,
        body.staff-theme::after,
        .global-progress-bar {
            display: none !important;
            content: none !important;
            background: none !important;
            background-image: none !important;
            animation: none !important;
        }

        /* Hide site chrome, interactive buttons, tabs, mobile selectors, announcements, breadcrumbs */
        header#site-header,
        header,
        .guest-header,
        .sticky-top-wrapper,
        .advisory-bar,
        .ann-modal-overlay,
        .breadcrumb-section,
        nav,
        .navbar,
        .mobile-toggle,
        .mobile-nav-overlay,
        footer,
        .footer,
        .footer-container,
        .support-modal-backdrop,
        .guide-hero-actions,
        .guide-nav-pills,
        .manual-mobile-select-wrap,
        .btn,
        .btn-back,
        .btn-print,
        .btn-print-outline {
            display: none !important;
        }

        .main-content,
        .guide-container {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            position: static !important;
            float: none !important;
        }

        /* Clean Official Header Banner */
        .guide-hero {
            background: none !important;
            color: #0f172a !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            border-bottom: 2.5pt solid #b71c1c !important;
            padding: 0 0 12pt 0 !important;
            margin: 0 0 16pt 0 !important;
            display: block !important;
        }

        html body .guide-hero h1,
        html body .guide-hero .hero-title {
            color: #b71c1c !important;
            font-size: 19pt !important;
            font-weight: 800 !important;
            margin: 0 0 4pt 0 !important;
            display: flex !important;
            align-items: center !important;
            gap: 8pt !important;
        }

        .guide-hero h1 i,
        .guide-hero .hero-title i {
            color: #b71c1c !important;
        }

        html body .guide-hero p,
        html body .guide-hero .hero-subtitle {
            color: #475569 !important;
            font-size: 10pt !important;
            line-height: 1.4 !important;
            max-width: 100% !important;
            margin: 0 !important;
        }

        /* Print ALL Chapters by default as a complete manual */
        .tab-content {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
            margin-bottom: 18pt !important;
            page-break-inside: auto !important;
        }

        /* If user chose "Print This Topic", only print active section */
        body.print-current-only .tab-content:not(.active) {
            display: none !important;
        }
        body.print-current-only .tab-content.active {
            display: block !important;
        }

        /* Section Cards */
        .guide-card {
            background: #ffffff !important;
            border: 1pt solid #cbd5e1 !important;
            border-radius: 6pt !important;
            box-shadow: none !important;
            padding: 12pt 14pt !important;
            margin-bottom: 14pt !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .guide-card h3 {
            color: #0f172a !important;
            font-size: 13pt !important;
            font-weight: 800 !important;
            border-bottom: 1pt solid #e2e8f0 !important;
            padding-bottom: 6pt !important;
            margin-top: 0 !important;
            margin-bottom: 10pt !important;
        }

        .guide-card h3 i {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Callouts, Steps, Tables */
        .callout-box,
        .step-item,
        .guide-card table {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            border-radius: 4pt !important;
            margin-bottom: 8pt !important;
            padding: 8pt 10pt !important;
            font-size: 9.5pt !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .step-number {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        pre, code {
            background: #f8fafc !important;
            color: #0f172a !important;
            border: 1pt solid #e2e8f0 !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            white-space: pre-wrap !important;
        }
    }
    </style>
</head>
<body>

    <?= view('templates/guest_header', [
        'announcements'      => $announcements ?? [],
        'breadcrumb_current' => 'Commuter User Guide',
    ]) ?>

    <div class="guide-container">
        <!-- Hero Section -->
        <div class="guide-hero">
            <div>
                <h1 class="hero-title">
                    <i class="fas fa-compass" style="color: #ffffff;"></i>
                    Commuter & Passenger User Guide
                </h1>
                <p class="hero-subtitle">
                    Complete official manual for tracking live queues, understanding boarding badges, looking up certified route fares & 20% discounts, daily trip timetables, and resolving travel issues.
                </p>
            </div>
            <div class="guide-hero-actions">
                <a href="<?= base_url('guest') ?>" class="btn-hero-action">
                    <i class="fas fa-tv"></i> Live Terminal Monitor
                </a>
                <button onclick="printGuide('all')" class="btn-hero-action" style="cursor:pointer;" title="Print complete commuter guide">
                    <i class="fas fa-print"></i> Print Guide
                </button>
                <button onclick="printGuide('current')" class="btn-hero-action btn-hero-outline" style="cursor:pointer;" title="Print only the currently selected topic">
                    <i class="fas fa-file-lines"></i> Print This Topic
                </button>
            </div>
        </div>

        <!-- Quick Jump Select on Mobile (< 520px) -->
        <div class="manual-mobile-select-wrap">
            <label for="commuterSectionSelect" class="manual-mobile-select-label">
                <i class="fas fa-list-check"></i> Jump to Topic:
            </label>
            <select id="commuterSectionSelect" class="manual-mobile-select" onchange="switchGuideTab(this.value, null)">
                <option value="tab-live-queue">1. Live Queue & Status Badges</option>
                <option value="tab-seats-etd">2. Seat Availability & ETD Rules</option>
                <option value="tab-fares">3. Fares & Statutory 20% Discounts</option>
                <option value="tab-schedules">4. Timetables & Operating Hours</option>
                <option value="tab-search">5. Instant Trip Search</option>
                <option value="tab-advisories">6. Advisories & Emergency Marquee</option>
                <option value="tab-recovery">7. Troubleshooting & Issue Recovery</option>
                <option value="tab-faq">8. FAQ & Terminal Contact</option>
            </select>
        </div>

        <!-- Tab Navigation Pills -->
        <div class="guide-nav-pills">
            <button class="guide-pill-btn active" data-tab="tab-live-queue" onclick="switchGuideTab('tab-live-queue', this)">
                <i class="fas fa-bus"></i> Live Queue & Badges
            </button>
            <button class="guide-pill-btn" data-tab="tab-seats-etd" onclick="switchGuideTab('tab-seats-etd', this)">
                <i class="fas fa-chair"></i> Seats & ETD
            </button>
            <button class="guide-pill-btn" data-tab="tab-fares" onclick="switchGuideTab('tab-fares', this)">
                <i class="fas fa-tags"></i> Fares & 20% Discount
            </button>
            <button class="guide-pill-btn" data-tab="tab-schedules" onclick="switchGuideTab('tab-schedules', this)">
                <i class="fas fa-calendar-alt"></i> Schedules
            </button>
            <button class="guide-pill-btn" data-tab="tab-search" onclick="switchGuideTab('tab-search', this)">
                <i class="fas fa-search"></i> Trip Search
            </button>
            <button class="guide-pill-btn" data-tab="tab-advisories" onclick="switchGuideTab('tab-advisories', this)">
                <i class="fas fa-bullhorn"></i> Advisories
            </button>
            <button class="guide-pill-btn" data-tab="tab-recovery" onclick="switchGuideTab('tab-recovery', this)">
                <i class="fas fa-life-ring" style="color:#ef4444;"></i> Error Help
            </button>
            <button class="guide-pill-btn" data-tab="tab-faq" onclick="switchGuideTab('tab-faq', this)">
                <i class="fas fa-question-circle"></i> FAQ & Info
            </button>
        </div>

        <!-- Tab 1: Live Queue & Status Badges -->
        <div id="tab-live-queue" class="tab-content active">
            <div class="guide-card">
                <h3><i class="fas fa-satellite-dish" style="color:#B71C1C;"></i> Reading the Live Terminal Queue</h3>
                <p style="color:#475569; font-size:13.5px; margin-bottom: 16px;">
                    The <?= esc(app_system_title()) ?> (<code>/</code> or <code>/guest</code>) delivers sub-second live updates without requiring manual browser refreshing. Here is what every detail on your screen means:
                </p>

                <div class="status-grid">
                    <div class="status-card" style="border-left: 4px solid #16a34a;">
                        <div class="status-card-header">
                            <span class="badge-demo boarding"><i class="fas fa-door-open"></i> BOARDING</span>
                            <span class="status-card-title">Active Bay Vehicle</span>
                        </div>
                        <p>The vehicle is physically parked at the passenger boarding bay. Passengers are paying fares and taking seats.</p>
                        <span class="status-action-tag"><i class="fas fa-walking"></i> What to do: Proceed directly to boarding bay.</span>
                    </div>

                    <div class="status-card" style="border-left: 4px solid #ea580c;">
                        <div class="status-card-header">
                            <span class="badge-demo next"><i class="fas fa-arrow-right"></i> NEXT IN LINE</span>
                            <span class="status-card-title">Upcoming Trip (#1 Waiting)</span>
                        </div>
                        <p>Scheduled to move into the boarding bay as soon as the current vehicle departs.</p>
                        <span class="status-action-tag" style="background:#fff7ed; color:#c2410c;"><i class="fas fa-suitcase"></i> What to do: Gather luggage; prepare cash.</span>
                    </div>

                    <div class="status-card" style="border-left: 4px solid #d97706;">
                        <div class="status-card-header">
                            <span class="badge-demo waiting"><i class="fas fa-clock"></i> WAITING</span>
                            <span class="status-card-title">Staging Area (#2+ in Queue)</span>
                        </div>
                        <p>Vehicle has arrived and checked in. It is parked in the staging lane in strict FIFO (First-In, First-Out) sequence.</p>
                        <span class="status-action-tag" style="background:#fefce8; color:#a16207;"><i class="fas fa-couch"></i> What to do: Relax in waiting lounge.</span>
                    </div>

                    <div class="status-card" style="border-left: 4px solid #dc2626;">
                        <div class="status-card-header">
                            <span class="badge-demo full"><i class="fas fa-users"></i> FULL</span>
                            <span class="status-card-title">Capacity Reached</span>
                        </div>
                        <p>All certified passenger seats are occupied. The trip is closed and about to depart.</p>
                        <span class="status-action-tag" style="background:#fef2f2; color:#b91c1c;"><i class="fas fa-hourglass-half"></i> What to do: Queue for next vehicle.</span>
                    </div>

                    <div class="status-card" style="border-left: 4px solid #64748b;">
                        <div class="status-card-header">
                            <span class="badge-demo departed"><i class="fas fa-check"></i> DEPARTED</span>
                            <span class="status-card-title">Trip Completed</span>
                        </div>
                        <p>Vehicle has dispatched from the terminal grounds and is on route to its destination.</p>
                        <span class="status-action-tag" style="background:#f1f5f9; color:#475569;"><i class="fas fa-road"></i> Trip is en route on the highway.</span>
                    </div>
                </div>

                <h4><i class="fas fa-van-shuttle" style="color:#2563eb;"></i> Vehicle Classifications</h4>
                <div class="step-item">
                    <div class="step-number"><i class="fas fa-truck-pickup"></i></div>
                    <div class="step-text">
                        <h5>PUJ (Public Utility Jeepney)</h5>
                        <p>Traditional and modern jeepneys, typically accommodating 16 to 22 passengers. Frequent departures on regional trunk lines.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-number"><i class="fas fa-shuttle-van"></i></div>
                    <div class="step-text">
                        <h5>UV Express Vans</h5>
                        <p>Air-conditioned passenger vans (Toyota HiAce, Nissan Urvan) configured for 14 to 16 passengers. Express direct service to Ormoc and Tacloban.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-number"><i class="fas fa-bus"></i></div>
                    <div class="step-text">
                        <h5>Modern Minibuses</h5>
                        <p>Modernized low-floor transit buses with 22–26 passenger seating capacity and automated fare systems.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: Seat Availability & ETD Rules -->
        <div id="tab-seats-etd" class="tab-content">
            <div class="guide-card">
                <h3><i class="fas fa-chair" style="color:#059669;"></i> Live Seating Progress & Departure Timers</h3>
                <p style="color:#475569; font-size:13.5px;">
                    PTTM gives passengers real-time visibility into seat availability before you walk up to the boarding platform:
                </p>

                <div class="step-item">
                    <div class="step-number">1</div>
                    <div class="step-text">
                        <h5>Live Passenger Progress Bar</h5>
                        <p>Every active boarding vehicle shows an instant count: <code>11 / 14 Seats (3 remaining)</code>. As passengers board, the dispatcher taps the counter and the progress bar adjusts immediately on all screens.</p>
                        <div class="seat-bar-demo">
                            <div class="seat-bar-fill" style="width: 78%;"></div>
                        </div>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number">2</div>
                    <div class="step-text">
                        <h5>Estimated Departure Time (ETD) & Countdown</h5>
                        <p>The ETD badge displays the scheduled target departure (e.g., <code>08:30 AM</code> or <code>12 mins remaining</code>). This is calculated by municipal headway rules for each route.</p>
                    </div>
                </div>

                <div class="callout-box success">
                    <i class="fas fa-bolt" style="margin-right:6px;"></i>
                    <strong>Dynamic Early Departure Rule:</strong> If a vehicle fills to capacity (14/14 or 18/18) before the scheduled ETD clock expires, terminal dispatchers immediately clear the trip to depart. This ensures you never sit waiting in a fully loaded vehicle!
                </div>
            </div>
        </div>

        <!-- Tab 3: Fares & Statutory 20% Discounts -->
        <div id="tab-fares" class="tab-content">
            <div class="guide-card">
                <h3><i class="fas fa-tags" style="color:#10b981;"></i> Official LTFRB Fares & Statutory 20% Concessions</h3>
                <p style="color:#475569; font-size:13.5px;">
                    Under LTFRB regulations and local transport resolutions, all public transit fares are strictly regulated by route distance. Check rates at <code>/fares</code>.
                </p>

                <div class="callout-box info">
                    <strong>Standard Rate Formula:</strong><br>
                    <code>Regular Fare = Base Fare (first 4 km) + (Remaining km × Rate per km)</code><br>
                    <strong>Discounted Rate:</strong> <code>Discounted Fare = Regular Fare × 0.80 (20% OFF)</code>
                </div>

                <h4><i class="fas fa-id-card" style="color:#B71C1C;"></i> Who Qualifies for the Mandatory 20% Discount?</h4>
                <div class="step-item">
                    <div class="step-number"><i class="fas fa-graduation-cap"></i></div>
                    <div class="step-text">
                        <h5>Students (Elementary, High School, College & Vocational)</h5>
                        <p>Entitled to 20% discount across all routes every day of the week, including weekends and school holidays. <strong>Requirement:</strong> Must present a valid, unexpired school ID or enrollment certificate.</p>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number"><i class="fas fa-blind"></i></div>
                    <div class="step-text">
                        <h5>Senior Citizens (Aged 60 and Above)</h5>
                        <p>Mandatory 20% discount under Republic Act No. 9994. <strong>Requirement:</strong> Must present an official OSCA Senior Citizen Identification Card.</p>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number"><i class="fas fa-wheelchair"></i></div>
                    <div class="step-text">
                        <h5>Persons with Disability (PWD)</h5>
                        <p>Mandatory 20% discount under Republic Act No. 10754. <strong>Requirement:</strong> Must present an official National PWD ID issued by the municipal or city Social Welfare office.</p>
                    </div>
                </div>

                <div class="callout-box warning">
                    <i class="fas fa-calculator" style="margin-right:6px;"></i>
                    <strong>Interactive Discount Calculator:</strong> Visit <a href="<?= base_url('fares') ?>" style="font-weight:700; color:#92400e;">Fares Directory</a> to select your destination and vehicle type to see both the regular and exact discounted fare down to the centavo!
                </div>
            </div>
        </div>

        <!-- Tab 4: Timetables & Operating Hours -->
        <div id="tab-schedules" class="tab-content">
            <div class="guide-card">
                <h3><i class="fas fa-calendar-alt" style="color:#2563eb;"></i> Daily Operating Timetables</h3>
                <p style="color:#475569; font-size:13.5px;">
                    Plan your regional journeys in advance by reviewing daily departure schedules at <code>/schedules</code>.
                </p>

                <div class="step-item">
                    <div class="step-number"><i class="fas fa-clock"></i></div>
                    <div class="step-text">
                        <h5>Terminal Operating Hours</h5>
                        <p>The Central Transit Terminal operates daily from <strong>4:00 AM to 8:00 PM</strong>. Earliest trips to Ormoc City depart at approximately 4:30 AM.</p>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number"><i class="fas fa-route"></i></div>
                    <div class="step-text">
                        <h5>Primary Regional Destinations</h5>
                        <p>Direct routes connect the terminal to: <strong>Ormoc City</strong> (52 km), <strong>Tacloban City</strong> (138 km), <strong>Isabel / PASAR</strong> (18 km), <strong>Naval, Biliran</strong>, and <strong>Kananga</strong>.</p>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number"><i class="fas fa-hourglass-start"></i></div>
                    <div class="step-text">
                        <h5>Peak vs. Off-Peak Frequencies</h5>
                        <p>During morning peak (06:00 AM–09:00 AM) and late afternoon peak (04:00 PM–07:00 PM), vans and jeepneys depart roughly every <strong>15–20 minutes</strong>. During mid-day off-peak hours, departures average every <strong>30–45 minutes</strong>.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 5: Instant Trip Search -->
        <div id="tab-search" class="tab-content">
            <div class="guide-card">
                <h3><i class="fas fa-search" style="color:#8b5cf6;"></i> Real-Time Autocomplete Search</h3>
                <p style="color:#475569; font-size:13.5px;">
                    Looking for a specific trip, driver, or destination? Use the live search bar at the top of the screen:
                </p>

                <div class="step-item">
                    <div class="step-number">1</div>
                    <div class="step-text">
                        <h5>Search by Destination</h5>
                        <p>Type <em>"Ormoc"</em>, <em>"Tacloban"</em>, or <em>"Isabel"</em> to immediately filter all active and queued trips heading in that direction.</p>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number">2</div>
                    <div class="step-text">
                        <h5>Search by Plate Number or Driver</h5>
                        <p>Enter any partial plate number (e.g. <code>ABC</code> or <code>123</code>) or driver's name to locate a specific vehicle in seconds.</p>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number">3</div>
                    <div class="step-text">
                        <h5>One-Tap Result Navigation</h5>
                        <p>Clicking any suggested autocomplete entry scrolls directly to that vehicle's card and highlights it in amber.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 6: Advisories & Emergency Marquee -->
        <div id="tab-advisories" class="tab-content">
            <div class="guide-card">
                <h3><i class="fas fa-bullhorn" style="color:#ea580c;"></i> Live Advisories & Emergency Weather Bulletins</h3>
                <p style="color:#475569; font-size:13.5px;">
                    During severe weather, typhoon signals, sea travel suspensions, or road blockages, terminal management broadcasts urgent advisories:
                </p>

                <div class="callout-box error">
                    <h5 style="margin:0 0 6px; font-weight:800;"><i class="fas fa-exclamation-triangle"></i> Rolling Advisory Marquee:</h5>
                    <p style="margin:0;">Look at the red banner at the top of every page. Critical updates on typhoon storm signals, landslide detours, or roll-on/roll-off (RoRo) ferry connections roll continuously in real time.</p>
                </div>

                <div class="step-item">
                    <div class="step-number"><i class="fas fa-bell"></i></div>
                    <div class="step-text">
                        <h5>Full Bulletins via Bullhorn Icon</h5>
                        <p>Click the <strong>Bullhorn Icon</strong> located on the left of the top marquee to open the complete bulletin dialog and read all active notices.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 7: Troubleshooting & Issue Recovery -->
        <div id="tab-recovery" class="tab-content">
            <div class="guide-card">
                <h3><i class="fas fa-life-ring" style="color:#ef4444;"></i> Help Recognizing & Recovering from Common Travel Issues</h3>
                <p style="color:#475569; font-size:13.5px; margin-bottom: 20px;">
                    How PTTM helps commuters diagnose and resolve common travel situations with built-in error recovery:
                </p>

                <div class="callout-box error">
                    <h5 style="margin:0 0 6px; font-weight:800;"><i class="fas fa-search"></i> 1. Search Query Shows "No Trips Found"</h5>
                    <p style="margin:0;"><strong>What happened:</strong> A misspelled destination (e.g. <em>"Ormok"</em>) or an inactive vehicle plate was typed.</p>
                    <p style="margin:4px 0 0;"><strong>How to Recover:</strong> Check the spelling or click one of the quick destination filter buttons (<em>Ormoc, Tacloban, Isabel, Naval</em>) to view all active departures for that route.</p>
                </div>

                <div class="callout-box warning">
                    <h5 style="margin:0 0 6px; font-weight:800;"><i class="fas fa-wifi"></i> 2. "Reconnecting..." or Cellular Signal Loss</h5>
                    <p style="margin:0;"><strong>What happened:</strong> Mobile signal momentarily dropped while traveling through cellular dead spots in mountainous road sections.</p>
                    <p style="margin:4px 0 0;"><strong>How to Recover:</strong> <strong>Do not reload your page!</strong> The system automatically retries connection using exponential backoff and transparently falls back to 20-second background polling without throwing errors.</p>
                </div>

                <div class="callout-box info">
                    <h5 style="margin:0 0 6px; font-weight:800;"><i class="fas fa-hand-holding-dollar"></i> 3. Suspected Overcharging or Fare Dispute</h5>
                    <p style="margin:0;"><strong>What happened:</strong> A conductor requests an amount higher than the certified LTFRB matrix or refuses the 20% student/senior/PWD discount.</p>
                    <p style="margin:4px 0 0;"><strong>How to Recover:</strong> Open the <a href="<?= base_url('fares') ?>" style="font-weight:700;">Official Fares page</a> on your phone and show the official rate to the driver. If unresolved, note the vehicle's plate number and submit an incident report via the <strong>Report Issue</strong> link in the footer.</p>
                </div>
            </div>
        </div>

        <!-- Tab 8: FAQ & Terminal Contact -->
        <div id="tab-faq" class="tab-content">
            <div class="guide-card">
                <h3><i class="fas fa-question-circle" style="color:#eab308;"></i> Frequently Asked Questions</h3>
                
                <div class="step-item">
                    <div class="step-number">?</div>
                    <div class="step-text">
                        <h5>Can I reserve seats online through this website?</h5>
                        <p>No. PTTM is an operational passenger monitoring service. In accordance with LGU transport rules, all seating is strictly on a first-come, first-served basis at the terminal boarding bays.</p>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number">?</div>
                    <div class="step-text">
                        <h5>Can I add the terminal monitor to my phone's home screen?</h5>
                        <p>Yes, through your browser. On Android Chrome, open the three-dot menu &rarr; <strong>Install and create shortcut</strong>, then choose <strong>Create shortcut</strong> or <strong>Install</strong> if offered. On iPhone Safari, open Share &rarr; <strong>Add to Home Screen</strong>. These options open the same website, not a separate store app; live information still needs an internet connection.</p>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number">?</div>
                    <div class="step-text">
                        <h5>How often does data refresh?</h5>
                        <p>In real-time! Whenever a dispatcher adds a vehicle, clicks a boarding passenger, or dispatches a trip, changes broadcast across WebSockets in under 1 second.</p>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number"><i class="fas fa-building"></i></div>
                    <div class="step-text">
                        <h5>Terminal Office & Inquiries</h5>
                        <p>
                            <strong>Address:</strong> <?= esc(app_contact_address()) ?>.<br>
                            <strong>Hotline:</strong> (053) 555-8376 | (053) 338-2022<br>
                            <strong>Official Email:</strong> arclast988@gmail.com
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?= $this->include('templates/guestfooter') ?>

    <script>
    function switchGuideTab(tabId, btn) {
        document.querySelectorAll('.tab-content').forEach(function(el) {
            el.classList.remove('active');
        });
        document.querySelectorAll('.guide-pill-btn').forEach(function(el) {
            el.classList.remove('active');
        });

        var target = document.getElementById(tabId);
        if (target) {
            target.classList.add('active');
        }

        if (btn) {
            btn.classList.add('active');
        } else {
            var matchingBtn = document.querySelector('.guide-pill-btn[data-tab="' + tabId + '"]');
            if (matchingBtn) matchingBtn.classList.add('active');
        }

        // Sync mobile dropdown
        var select = document.getElementById('commuterSectionSelect');
        if (select && select.value !== tabId) {
            select.value = tabId;
        }

        // Scroll into view on small screens if tab clicked from select
        if (!btn && window.innerWidth <= 520 && target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function printGuide(mode) {
        if (mode === 'current') {
            document.body.classList.add('print-current-only');
        } else {
            document.body.classList.remove('print-current-only');
        }
        window.print();
    }

    window.addEventListener('afterprint', function() {
        document.body.classList.remove('print-current-only');
    });
    </script>
</body>
</html>
