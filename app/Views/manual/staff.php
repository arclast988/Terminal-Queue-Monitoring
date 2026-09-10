<?= view('templates/header', ['title' => $title]) ?>

<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">
<style>
    .manual-container {
        width: 92%;
        max-width: 1200px;
        margin: 24px auto 40px;
        padding: 0 10px;
        box-sizing: border-box;
    }

    /* Header Banner */
    .manual-header {
        background: linear-gradient(135deg, #065f46 0%, #047857 100%);
        color: #ffffff !important;
        border-radius: 18px;
        padding: 32px 28px;
        margin-bottom: 24px;
        box-shadow: 0 10px 25px -5px rgba(4, 120, 87, 0.28);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        width: 100%;
        box-sizing: border-box;
    }

    /* High Specificity Text Overrides for Header */
    html body.staff-theme .manual-header h1,
    html body.staff-theme .manual-header .manual-title,
    html body.admin-theme .manual-header h1,
    html body.admin-theme .manual-header .manual-title,
    html body .manual-header h1,
    html body .manual-header .manual-title {
        color: #ffffff !important;
        font-size: clamp(20px, 3.8vw, 26px);
        font-weight: 800;
        margin: 0 0 8px;
        display: flex;
        align-items: center;
        gap: 12px;
        line-height: 1.3;
    }

    html body.staff-theme .manual-header p,
    html body.staff-theme .manual-header .manual-subtitle,
    html body.admin-theme .manual-header p,
    html body.admin-theme .manual-header .manual-subtitle,
    html body .manual-header p,
    html body .manual-header .manual-subtitle {
        color: #d1fae5 !important;
        font-size: 14.5px;
        margin: 0;
        max-width: 720px;
        line-height: 1.6;
    }

    .manual-header-actions {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .manual-header .btn-back {
        background: rgba(255, 255, 255, 0.18);
        color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.35);
        padding: 9px 16px;
        border-radius: 9px;
        font-size: 13.5px;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
    }

    .manual-header .btn-back:hover {
        background: rgba(255, 255, 255, 0.28);
        color: #ffffff !important;
        transform: translateY(-1px);
    }

    .manual-header .btn-action-primary {
        background: #ffffff;
        color: #065f46 !important;
        border: none;
        padding: 9px 16px;
        border-radius: 9px;
        font-size: 13.5px;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        box-shadow: 0 4px 10px rgba(0,0,0,0.12);
    }

    .manual-header .btn-action-primary:hover {
        background: #f0fdf4;
        color: #047857 !important;
        transform: translateY(-1px);
    }

    .manual-header .btn-print-outline {
        background: rgba(255, 255, 255, 0.18);
        color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.35);
        padding: 9px 14px;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
    }

    .manual-header .btn-print-outline:hover {
        background: rgba(255, 255, 255, 0.28);
        color: #ffffff !important;
        transform: translateY(-1px);
    }

    /* Mobile Quick Topic Selector (< 520px) */
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
        border-color: #059669;
        background: white;
    }

    /* Nav Pills */
    .manual-nav-pills {
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

    .manual-pill-btn {
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

    .manual-pill-btn:hover {
        background: #f1f5f9;
        color: #0f172a;
    }

    .manual-pill-btn.active {
        background: #059669;
        border-color: #059669;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
    }

    @media (max-width: 640px) {
        .manual-nav-pills {
            flex-wrap: nowrap;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 10px;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }
        .manual-nav-pills::-webkit-scrollbar {
            display: block;
            height: 4px;
        }
        .manual-nav-pills::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        .manual-nav-pills::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .manual-pill-btn {
            flex-shrink: 0;
        }
    }

    /* Tab Content & Cards */
    .tab-content {
        display: none;
        width: 100%;
        box-sizing: border-box;
    }

    .tab-content.active {
        display: block;
        width: 100%;
        animation: fadeInStaff 0.25s ease;
    }

    @keyframes fadeInStaff {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .manual-card {
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

    .manual-card h3 {
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

    .manual-card h4 {
        font-size: 15.5px;
        font-weight: 700;
        color: #1e293b;
        margin: 20px 0 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Steps */
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
        background: #d1fae5;
        color: #065f46;
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

    /* Callouts */
    .rule-callout {
        background: #f8fafc;
        border-left: 4px solid #059669;
        padding: 14px 16px;
        border-radius: 0 10px 10px 0;
        margin: 16px 0;
        font-size: 13.5px;
        color: #1e293b;
        line-height: 1.6;
        box-sizing: border-box;
        width: 100%;
    }

    .rule-callout.warning {
        background: #fffbeb;
        border-left-color: #d97706;
        color: #92400e;
    }

    .rule-callout.error {
        background: #fef2f2;
        border-left-color: #ef4444;
        color: #991b1b;
    }

    .rule-callout.info {
        background: #eff6ff;
        border-left-color: #2563eb;
        color: #1e40af;
    }

    /* Badges in Dispatcher Guide */
    .badge-demo {
        display: inline-flex;
        align-items: center;
        padding: 3px 8px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 12px;
        gap: 5px;
    }
    .badge-demo.boarding { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .badge-demo.waiting { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .badge-demo.full { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    /* Mobile Screens Under 400px */
    @media (max-width: 520px) {
        .manual-container {
            width: 100% !important;
            max-width: 100% !important;
            margin: 12px 0 30px !important;
            padding: 0 8px !important;
        }
        .manual-header {
            padding: 20px 16px;
            border-radius: 14px;
            margin-bottom: 16px;
        }
        html body.staff-theme .manual-header h1,
        html body.staff-theme .manual-header .manual-title,
        html body .manual-header h1,
        html body .manual-header .manual-title {
            font-size: 18px;
            gap: 8px;
        }
        html body.staff-theme .manual-header p,
        html body.staff-theme .manual-header .manual-subtitle,
        html body .manual-header p,
        html body .manual-header .manual-subtitle {
            font-size: 12.5px;
            line-height: 1.45;
        }
        .manual-header-actions {
            width: 100%;
            flex-direction: column;
            gap: 8px;
            margin-top: 6px;
        }
        .manual-header .btn-back,
        .manual-header .btn-action-primary {
            width: 100%;
            justify-content: center;
            padding: 9px 14px;
            font-size: 13px;
        }
        .manual-mobile-select-wrap {
            display: block;
        }
        .manual-nav-pills {
            padding-bottom: 6px;
            margin-bottom: 16px;
            gap: 6px;
        }
        .manual-pill-btn {
            padding: 8px 12px;
            font-size: 12.5px;
            border-radius: 8px;
        }
        .manual-card {
            padding: 16px 12px;
            border-radius: 12px;
            margin-bottom: 16px;
        }
        .manual-card h3 {
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
        .rule-callout {
            padding: 12px;
            font-size: 12.5px;
            border-radius: 0 8px 8px 0;
        }
    }

    @media (max-width: 360px) {
        .manual-header {
            padding: 16px 12px;
        }
        html body .manual-header h1,
        html body .manual-header .manual-title {
            font-size: 17px;
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

        /* Hide site chrome, interactive buttons, tabs, mobile selectors */
        header#site-header,
        header,
        nav,
        .navbar,
        .mobile-toggle,
        .mobile-nav-overlay,
        footer,
        .footer,
        .manual-header-actions,
        .manual-nav-pills,
        .manual-mobile-select-wrap,
        .btn,
        .btn-back,
        .btn-print,
        .btn-print-outline {
            display: none !important;
        }

        .main-content,
        .manual-container {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            position: static !important;
            float: none !important;
        }

        /* Clean Official Header Banner */
        .manual-header {
            background: none !important;
            color: #0f172a !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            border-bottom: 2.5pt solid #059669 !important;
            padding: 0 0 12pt 0 !important;
            margin: 0 0 16pt 0 !important;
            display: block !important;
        }

        html body.staff-theme .manual-header h1,
        html body.staff-theme .manual-header .manual-title,
        html body .manual-header h1,
        html body .manual-header .manual-title {
            color: #059669 !important;
            font-size: 19pt !important;
            font-weight: 800 !important;
            margin: 0 0 4pt 0 !important;
            display: flex !important;
            align-items: center !important;
            gap: 8pt !important;
        }

        .manual-header h1 i,
        .manual-header .manual-title i {
            color: #059669 !important;
        }

        html body.staff-theme .manual-header p,
        html body.staff-theme .manual-header .manual-subtitle,
        html body .manual-header p,
        html body .manual-header .manual-subtitle {
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
        .manual-card {
            background: #ffffff !important;
            border: 1pt solid #cbd5e1 !important;
            border-radius: 6pt !important;
            box-shadow: none !important;
            padding: 12pt 14pt !important;
            margin-bottom: 14pt !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .manual-card h3 {
            color: #0f172a !important;
            font-size: 13pt !important;
            font-weight: 800 !important;
            border-bottom: 1pt solid #e2e8f0 !important;
            padding-bottom: 6pt !important;
            margin-top: 0 !important;
            margin-bottom: 10pt !important;
        }

        .manual-card h3 i {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Callouts, Steps, Code */
        .callout-box,
        .rule-callout,
        .error-callout,
        .success-callout,
        .step-item {
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

<div class="manual-container">
    <!-- Header Banner -->
    <div class="manual-header">
        <div>
            <h1 class="manual-title">
                <i class="fas fa-clipboard-list" style="color: #6ee7b7;"></i>
                Dispatcher & Staff User Manual
            </h1>
            <p class="manual-subtitle">
                Official operating manual for terminal dispatchers: queue management, boarding flow, live passenger counters, 30-min cooldown rules, and 1-click Undo Cancel recovery.
            </p>
        </div>
        <div class="manual-header-actions">
            <a href="<?= base_url('staff/dashboard') ?>" class="btn-back">
                <i class="fas fa-arrow-left"></i> Staff Dashboard
            </a>
            <a href="<?= base_url('staff/queue') ?>" class="btn-back">
                <i class="fas fa-list-ol"></i> Go to Queue
            </a>
            <button onclick="printManual('all')" class="btn-action-primary" title="Print the complete official manual">
                <i class="fas fa-print"></i> Print Manual
            </button>
            <button onclick="printManual('current')" class="btn-print-outline" title="Print only the currently selected topic">
                <i class="fas fa-file-lines"></i> Print This Topic
            </button>
        </div>
    </div>

    <!-- Quick Jump Select on Mobile (< 520px) -->
    <div class="manual-mobile-select-wrap">
        <label for="staffSectionSelect" class="manual-mobile-select-label">
            <i class="fas fa-list-check"></i> Jump to Topic:
        </label>
        <select id="staffSectionSelect" class="manual-mobile-select" onchange="switchManualTab(this.value, null)">
            <option value="tab-shift">1. Shift Setup & Route Jurisdiction</option>
            <option value="tab-queue-zones">2. Queue Board vs. Ready Pool</option>
            <option value="tab-checkin">3. Checking In Arriving Vehicles</option>
            <option value="tab-cooldown">4. 30-Minute Cooldown Rule</option>
            <option value="tab-boarding">5. Waiting &rarr; Boarding &rarr; Departed</option>
            <option value="tab-passengers">6. Live Passenger Counter & Clamping</option>
            <option value="tab-driver-swap">7. Updating Drivers on Duty</option>
            <option value="tab-undo-cancel">8. 1-Click Undo Cancel Recovery</option>
            <option value="tab-announcements">9. Advisories & Emergency Alerts</option>
            <option value="tab-history">10. Shift Logs & History</option>
            <option value="tab-troubleshooting">11. Dispatcher Troubleshooting</option>
        </select>
    </div>

    <!-- Tab Navigation Pills -->
    <div class="manual-nav-pills">
        <button class="manual-pill-btn active" data-tab="tab-shift" onclick="switchManualTab('tab-shift', this)">
            <i class="fas fa-user-clock"></i> Shift Setup
        </button>
        <button class="manual-pill-btn" data-tab="tab-queue-zones" onclick="switchManualTab('tab-queue-zones', this)">
            <i class="fas fa-columns"></i> Queue Zones
        </button>
        <button class="manual-pill-btn" data-tab="tab-checkin" onclick="switchManualTab('tab-checkin', this)">
            <i class="fas fa-plus-circle"></i> Check-In
        </button>
        <button class="manual-pill-btn" data-tab="tab-cooldown" onclick="switchManualTab('tab-cooldown', this)">
            <i class="fas fa-stopwatch"></i> 30-Min Cooldown
        </button>
        <button class="manual-pill-btn" data-tab="tab-boarding" onclick="switchManualTab('tab-boarding', this)">
            <i class="fas fa-tasks"></i> Boarding Flow
        </button>
        <button class="manual-pill-btn" data-tab="tab-passengers" onclick="switchManualTab('tab-passengers', this)">
            <i class="fas fa-users"></i> Passenger Counter
        </button>
        <button class="manual-pill-btn" data-tab="tab-driver-swap" onclick="switchManualTab('tab-driver-swap', this)">
            <i class="fas fa-id-badge"></i> Driver Swap
        </button>
        <button class="manual-pill-btn" data-tab="tab-undo-cancel" onclick="switchManualTab('tab-undo-cancel', this)">
            <i class="fas fa-rotate-left" style="color:#ef4444;"></i> Undo Cancel
        </button>
        <button class="manual-pill-btn" data-tab="tab-announcements" onclick="switchManualTab('tab-announcements', this)">
            <i class="fas fa-bullhorn"></i> Advisories
        </button>
        <button class="manual-pill-btn" data-tab="tab-history" onclick="switchManualTab('tab-history', this)">
            <i class="fas fa-history"></i> Shift Logs
        </button>
        <button class="manual-pill-btn" data-tab="tab-troubleshooting" onclick="switchManualTab('tab-troubleshooting', this)">
            <i class="fas fa-life-ring" style="color:#0ea5e9;"></i> Troubleshooting
        </button>
    </div>

    <!-- Tab 1: Shift Setup & Assigned Routes -->
    <div id="tab-shift" class="tab-content active">
        <div class="manual-card">
            <h3><i class="fas fa-sign-in-alt" style="color:#059669;"></i> Shift Setup & Route Jurisdiction</h3>
            <p style="color:#475569; font-size:13.5px;">
                As a terminal dispatcher, your permissions are bound to specific routes authorized by the terminal administrator.
            </p>

            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-text">
                    <h5>Sign In to Dispatcher Portal</h5>
                    <p>Go to <code>/login</code> and authenticate with your staff credentials. You will land on your <strong>Staff Dashboard</strong> (<code>/staff/dashboard</code>).</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-text">
                    <h5>Verify Authorized Destinations</h5>
                    <p>Your dashboard displays the routes assigned to your shift (e.g. <em>Palompon &rarr; Ormoc</em> and <em>Palompon &rarr; Tacloban</em>). You can only check in and dispatch vehicles assigned to these franchised destinations.</p>
                </div>
            </div>

            <div class="rule-callout warning">
                <i class="fas fa-shield-alt"></i> <strong>Route Access Rule:</strong> If a vehicle arrives for a route that does not appear in your queue controls, do not force an edit. An administrator must check the route checkbox for your account under <em>Management &rarr; Users</em>.
            </div>
        </div>
    </div>

    <!-- Tab 2: Queue Interface Architecture -->
    <div id="tab-queue-zones" class="tab-content">
        <div class="manual-card">
            <h3><i class="fas fa-columns" style="color:#2563eb;"></i> Understanding the Queue Interface (`/staff/queue`)</h3>
            <p style="color:#475569; font-size:13.5px;">
                The queue screen is organized into two primary operational zones:
            </p>

            <div class="step-item">
                <div class="step-number">A</div>
                <div class="step-text">
                    <h5>Ready Vehicles Panel (Available Staging Pool)</h5>
                    <p>Located on the left (or top on mobile). Lists all certified fleet vehicles for your assigned routes that are physically present at the terminal but not yet added to the departure line.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">B</div>
                <div class="step-text">
                    <h5>Active Queue Board</h5>
                    <p>Displays vehicles currently queued for departure. Arranged in strict First-In, First-Out (FIFO) chronological sequence: Position #1 is the active Boarding vehicle, followed by #2, #3, etc.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 3: Checking In Arriving Vehicles -->
    <div id="tab-checkin" class="tab-content">
        <div class="manual-card">
            <h3><i class="fas fa-plus-circle" style="color:#059669;"></i> Checking In Arriving Vehicles</h3>
            <p style="color:#475569; font-size:13.5px;">
                When an operator or driver arrives at the terminal gate:
            </p>

            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-text">
                    <h5>Locate Vehicle in Available Pool</h5>
                    <p>Find the vehicle by plate number (e.g. <code>HAA-1234</code>) in the Available Pool.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-text">
                    <h5>Click "Add to Queue"</h5>
                    <p>Click the <strong>+ Add to Queue</strong> button (or select checkboxes for multiple vehicles and click bulk add). The system checks plate uniqueness and automatically appends the vehicle to the end of the queue as <span class="badge-demo waiting">WAITING</span>.</p>
                </div>
            </div>

            <div class="rule-callout info">
                <i class="fas fa-info-circle"></i> <strong>Automatic ETD Assignment:</strong> The instant a vehicle is queued, the system inspects active municipal departure interval rules for that hour and assigns its target departure time automatically.
            </div>
        </div>
    </div>

    <!-- Tab 4: 30-Minute Departure Cooldown Rule -->
    <div id="tab-cooldown" class="tab-content">
        <div class="manual-card">
            <h3><i class="fas fa-stopwatch" style="color:#f59e0b;"></i> The 30-Minute Departure Cooldown Rule</h3>
            <p style="color:#475569; font-size:13.5px;">
                To prevent unfair queue hogging and ensure equitable rotation among all transport operators:
            </p>

            <div class="rule-callout warning">
                <h5 style="margin:0 0 6px; font-weight:800;"><i class="fas fa-ban"></i> Cooldown Protection:</h5>
                <p style="margin:0;">Once a vehicle departs (<code>status = departed</code>), it cannot immediately re-enter the queue.</p>
                <p style="margin:6px 0 0;">If a driver returns early and asks to be queued before 30 minutes pass, the system intercepts the request and informs you: <em>"Vehicle ABC-1234 departed recently. Please wait about 14 more minute(s) before adding it back."</em></p>
                <p style="margin:6px 0 0;">The system automatically unlocks the vehicle the instant the 30-minute timer expires—no manual override needed!</p>
            </div>
        </div>
    </div>

    <!-- Tab 5: Waiting -> Boarding -> Departed Workflow -->
    <div id="tab-boarding" class="tab-content">
        <div class="manual-card">
            <h3><i class="fas fa-tasks" style="color:#059669;"></i> Complete Status Lifecycle</h3>
            <p style="color:#475569; font-size:13.5px;">
                Every trip moves through three definitive operational states:
            </p>

            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-text">
                    <h5>Waiting &rarr; Start Boarding</h5>
                    <p>When the active bay clears, locate the #1 Waiting vehicle for that route and click <strong>Start Boarding</strong>. The status changes to <span class="badge-demo boarding">BOARDING</span>. The departure interval clock resets and starts counting down from this moment.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-text">
                    <h5>Boarding &rarr; Depart Vehicle</h5>
                    <p>When all passenger seats are filled OR the scheduled departure timer expires, click <strong>Depart Vehicle</strong>.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-text">
                    <h5>Automatic Queue Advancement</h5>
                    <p>The departed vehicle is archived to the departure ledger, and the next waiting vehicle is automatically promoted to Position #1.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 6: Live Passenger Counter & Clamping -->
    <div id="tab-passengers" class="tab-content">
        <div class="manual-card">
            <h3><i class="fas fa-users" style="color:#2563eb;"></i> Passenger Loading Counter & Capacity Clamping</h3>
            <p style="color:#475569; font-size:13.5px;">
                Keep commuter displays accurate as passengers board at the bay:
            </p>

            <div class="step-item">
                <div class="step-number">+</div>
                <div class="step-text">
                    <h5>Debounced Live Counter</h5>
                    <p>Use the <strong>+</strong> and <strong>-</strong> buttons (or type directly) to record boarding passengers. The counter uses debounced AJAX so rapid clicks never create race conditions.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number"><i class="fas fa-lock"></i></div>
                <div class="step-text">
                    <h5>Automatic Capacity Clamping</h5>
                    <p>The system strictly enforces certified seating limits: <code>count = max(0, min(input, capacity))</code>. For example, a 14-seater van can never be set to 15 passengers.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number"><i class="fas fa-bell"></i></div>
                <div class="step-text">
                    <h5>Full Vehicle Alert</h5>
                    <p>When max capacity is reached, the passenger badge turns red with <span class="badge-demo full">FULL</span>. Commuters immediately know to wait for the next vehicle.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 7: Updating Drivers on Duty -->
    <div id="tab-driver-swap" class="tab-content">
        <div class="manual-card">
            <h3><i class="fas fa-id-badge" style="color:#8b5cf6;"></i> Real-Time Driver Swaps on Duty</h3>
            <p style="color:#475569; font-size:13.5px;">
                When a relief driver takes over a scheduled trip:
            </p>

            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-text">
                    <h5>Click the Driver Name</h5>
                    <p>On the vehicle row in the queue table, click the <strong>Driver Name</strong> or the edit pencil icon.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-text">
                    <h5>Type New Driver & Save</h5>
                    <p>Enter the substitute driver's full name and press Enter (or click Save). The update is recorded in the permanent audit trail and instantly reflected on the public terminal monitor.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 8: 1-Click Undo Cancel Recovery -->
    <div id="tab-undo-cancel" class="tab-content">
        <div class="manual-card">
            <h3><i class="fas fa-rotate-left" style="color:#ef4444;"></i> Accidental Cancellation? 1-Click Undo Cancel!</h3>
            <p style="color:#475569; font-size:13.5px;">
                Terminals are noisy and high-pressure. If you accidentally hit <strong>Cancel Trip</strong> instead of another button:
            </p>

            <div class="rule-callout error">
                <h5 style="margin:0 0 6px; font-weight:800;"><i class="fas fa-magic"></i> Instant Recovery with Undo Cancel:</h5>
                <p style="margin:0;">1. Immediately upon cancellation, a high-visibility amber toast notification appears on your screen.</p>
                <p style="margin:4px 0 0;">2. Click the <strong>[Undo Cancel]</strong> button on the toast notice.</p>
                <p style="margin:4px 0 0;">3. The vehicle is immediately restored to its exact prior position in the queue with atomic database safety.</p>
                <p style="margin:4px 0 0;">4. All commuter screens and queue boards restore the vehicle in under 1 second!</p>
            </div>
        </div>
    </div>

    <!-- Tab 9: Advisories & Emergency Alerts -->
    <div id="tab-announcements" class="tab-content">
        <div class="manual-card">
            <h3><i class="fas fa-bullhorn" style="color:#ea580c;"></i> Publishing Terminal Advisories</h3>
            <p style="color:#475569; font-size:13.5px;">
                Keep passengers informed during weather delays, road repairs, or temporary gate changes:
            </p>

            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-text">
                    <h5>Open Announcements</h5>
                    <p>Navigate to <strong>Announcements</strong> (<code>/admin/announcements</code>) and click <strong>+ New Announcement</strong>.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-text">
                    <h5>Set Priority & Publish</h5>
                    <p>Select <code>High</code> priority for severe weather or emergency delays. Once published, it appears on the rolling top marquee within milliseconds.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 10: Shift Logs & History -->
    <div id="tab-history" class="tab-content">
        <div class="manual-card">
            <h3><i class="fas fa-history" style="color:#2563eb;"></i> Reviewing Shift Departure Logs</h3>
            <p style="color:#475569; font-size:13.5px;">
                At the end of your shift or during handover to the next dispatcher:
            </p>

            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-text">
                    <h5>View Shift History</h5>
                    <p>Review the list of dispatches completed during your shift. Confirm total departures, total passenger counts, and on-time performance.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-text">
                    <h5>Handover Accuracy</h5>
                    <p>Ensure all physically waiting vehicles in the staging lanes are accounted for on the Active Queue Board before signing out.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 11: Dispatcher Troubleshooting -->
    <div id="tab-troubleshooting" class="tab-content">
        <div class="manual-card">
            <h3><i class="fas fa-life-ring" style="color:#0ea5e9;"></i> Dispatcher Troubleshooting Guide</h3>
            
            <div class="rule-callout info">
                <h5 style="margin:0 0 4px; font-weight:800;"><i class="fas fa-wifi"></i> WebSocket Reconnecting Notice</h5>
                <p style="margin:0;">If terminal Wi-Fi blinks, client browsers automatically drop back to 20-second HTTP polling without throwing an error or logging you out.</p>
            </div>

            <div class="rule-callout error">
                <h5 style="margin:0 0 4px; font-weight:800;"><i class="fas fa-ban"></i> "Vehicle already in queue" Warning</h5>
                <p style="margin:0;">The system blocks duplicate queue insertions for the same plate number to prevent corrupting queue ledgers.</p>
            </div>
        </div>
    </div>
</div>

<script>
function switchManualTab(tabId, btn) {
    document.querySelectorAll('.tab-content').forEach(function(el) {
        el.classList.remove('active');
    });
    document.querySelectorAll('.manual-pill-btn').forEach(function(el) {
        el.classList.remove('active');
    });

    var target = document.getElementById(tabId);
    if (target) {
        target.classList.add('active');
    }

    if (btn) {
        btn.classList.add('active');
    } else {
        var matchingBtn = document.querySelector('.manual-pill-btn[data-tab="' + tabId + '"]');
        if (matchingBtn) matchingBtn.classList.add('active');
    }

    // Sync mobile select
    var select = document.getElementById('staffSectionSelect');
    if (select && select.value !== tabId) {
        select.value = tabId;
    }

    // Scroll into view on mobile if clicked from select
    if (!btn && window.innerWidth <= 520 && target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function printManual(mode) {
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

<?= $this->include('templates/footer') ?>
