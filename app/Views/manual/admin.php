<?= $this->include('templates/header') ?>

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
        background: linear-gradient(135deg, #B71C1C 0%, #7F0000 100%);
        color: #ffffff !important;
        border-radius: 18px;
        padding: 32px 28px;
        margin-bottom: 24px;
        box-shadow: 0 10px 25px -5px rgba(183, 28, 28, 0.28);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        width: 100%;
        box-sizing: border-box;
    }

    /* High Specificity Text Overrides for Admin Theme Header */
    html body.admin-theme .manual-header h1,
    html body.admin-theme .manual-header .manual-title,
    html body.staff-theme .manual-header h1,
    html body.staff-theme .manual-header .manual-title,
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

    html body.admin-theme .manual-header p,
    html body.admin-theme .manual-header .manual-subtitle,
    html body.staff-theme .manual-header p,
    html body.staff-theme .manual-header .manual-subtitle,
    html body .manual-header p,
    html body .manual-header .manual-subtitle {
        color: #fee2e2 !important;
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
        background: rgba(255, 255, 255, 0.16);
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

    .manual-header .btn-print {
        background: #ffffff;
        color: #B71C1C !important;
        border: none;
        padding: 9px 16px;
        border-radius: 9px;
        font-size: 13.5px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }

    .manual-header .btn-print:hover {
        background: #fef2f2;
        color: #7F0000 !important;
        transform: translateY(-1px);
    }

    .manual-header .btn-print-outline {
        background: rgba(255, 255, 255, 0.16);
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

    /* Quick Topic Selector on Mobile (< 520px) */
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
        border-color: #D62828;
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
        background: #D62828;
        border-color: #D62828;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(214, 40, 40, 0.25);
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
        animation: fadeInAdmin 0.25s ease;
    }

    @keyframes fadeInAdmin {
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
        background: #fee2e2;
        color: #b91c1c;
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
        border-left: 4px solid #3b82f6;
        padding: 14px 16px;
        border-radius: 0 10px 10px 0;
        margin: 16px 0;
        font-size: 13.5px;
        color: #1e293b;
        line-height: 1.6;
        box-sizing: border-box;
        width: 100%;
    }

    .error-callout {
        background: #fef2f2;
        border-left: 4px solid #ef4444;
        padding: 14px 16px;
        border-radius: 0 10px 10px 0;
        margin: 16px 0;
        font-size: 13.5px;
        color: #991b1b;
        line-height: 1.6;
        box-sizing: border-box;
        width: 100%;
    }

    .success-callout {
        background: #f0fdf4;
        border-left: 4px solid #16a34a;
        padding: 14px 16px;
        border-radius: 0 10px 10px 0;
        margin: 16px 0;
        font-size: 13.5px;
        color: #166534;
        line-height: 1.6;
        box-sizing: border-box;
        width: 100%;
    }

    /* Role Hierarchy: Desktop Table & Mobile Cards */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin: 16px 0;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        box-sizing: border-box;
    }

    .table-spec {
        width: 100%;
        min-width: 580px;
        border-collapse: collapse;
        margin: 0;
        font-size: 13.5px;
    }

    .table-spec th {
        background: #f8fafc;
        padding: 12px 14px;
        text-align: left;
        font-weight: 700;
        color: #475569;
        border-bottom: 2px solid #e2e8f0;
    }

    .table-spec td {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }

    /* Mobile Role Cards (< 520px) */
    .role-cards-mobile {
        display: none;
        flex-direction: column;
        gap: 12px;
        margin: 16px 0;
    }

    .role-card-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px;
        box-sizing: border-box;
    }

    .role-card-item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        flex-wrap: wrap;
        gap: 6px;
    }

    .role-badge {
        display: inline-flex;
        align-items: center;
        padding: 3px 8px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 11.5px;
        gap: 4px;
    }

    .role-badge.super { background: #fee2e2; color: #991b1b; }
    .role-badge.admin { background: #dbeafe; color: #1e40af; }
    .role-badge.staff { background: #dcfce7; color: #166534; }
    .role-badge.guest { background: #f1f5f9; color: #475569; }

    .role-card-item p {
        font-size: 13px;
        color: #475569;
        margin: 0;
        line-height: 1.5;
    }

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
        html body.admin-theme .manual-header h1,
        html body.admin-theme .manual-header .manual-title,
        html body .manual-header h1,
        html body .manual-header .manual-title {
            font-size: 18px;
            gap: 8px;
        }
        html body.admin-theme .manual-header p,
        html body.admin-theme .manual-header .manual-subtitle,
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
        .manual-header .btn-print {
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
        .rule-callout, .error-callout, .success-callout {
            padding: 12px;
            font-size: 12.5px;
            border-radius: 0 8px 8px 0;
        }

        /* Show mobile role cards, hide wide table on phone */
        .table-responsive.role-table-desktop {
            display: none;
        }
        .role-cards-mobile {
            display: flex;
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
        .role-cards-mobile,
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
            border-bottom: 2.5pt solid #b71c1c !important;
            padding: 0 0 12pt 0 !important;
            margin: 0 0 16pt 0 !important;
            display: block !important;
        }

        html body.admin-theme .manual-header h1,
        html body.admin-theme .manual-header .manual-title,
        html body .manual-header h1,
        html body .manual-header .manual-title {
            color: #b71c1c !important;
            font-size: 19pt !important;
            font-weight: 800 !important;
            margin: 0 0 4pt 0 !important;
            display: flex !important;
            align-items: center !important;
            gap: 8pt !important;
        }

        .manual-header h1 i,
        .manual-header .manual-title i {
            color: #b71c1c !important;
        }

        html body.admin-theme .manual-header p,
        html body.admin-theme .manual-header .manual-subtitle,
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

        /* Tables */
        .table-responsive.role-table-desktop,
        .table-responsive {
            display: block !important;
            overflow: visible !important;
            width: 100% !important;
        }

        table.table-spec,
        .manual-card table {
            width: 100% !important;
            border-collapse: collapse !important;
            margin-top: 8pt !important;
            margin-bottom: 8pt !important;
        }

        table.table-spec th,
        .manual-card table th {
            background: #f1f5f9 !important;
            color: #0f172a !important;
            border: 1pt solid #cbd5e1 !important;
            padding: 6pt 8pt !important;
            font-size: 9.5pt !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        table.table-spec td,
        .manual-card table td {
            border: 1pt solid #cbd5e1 !important;
            padding: 6pt 8pt !important;
            font-size: 9.5pt !important;
            color: #1e293b !important;
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
                <i class="fas fa-book-open" style="color: #ffffff;"></i>
                Admin & Superadmin User Manual
            </h1>
            <p class="manual-subtitle">
                Official guide for fleet administration, route & fare matrix configuration, departure headway rules, staff permissions, and error recovery.
            </p>
        </div>
        <div class="manual-header-actions">
            <a href="<?= base_url('admin/dashboard') ?>" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <button onclick="printManual('all')" class="btn-print" title="Print the complete official manual">
                <i class="fas fa-print"></i> Print Manual
            </button>
            <button onclick="printManual('current')" class="btn-print-outline" title="Print only the currently selected topic">
                <i class="fas fa-file-lines"></i> Print This Topic
            </button>
        </div>
    </div>

    <!-- Quick Jump Select on Mobile (< 520px) -->
    <div class="manual-mobile-select-wrap">
        <label for="adminSectionSelect" class="manual-mobile-select-label">
            <i class="fas fa-list-check"></i> Jump to Topic:
        </label>
        <select id="adminSectionSelect" class="manual-mobile-select" onchange="switchManualTab(this.value, null)">
            <option value="tab-overview">1. User Roles & Hierarchy</option>
            <option value="tab-users">2. User & Staff Management</option>
            <option value="tab-terminals">3. Terminal & Bay Management</option>
            <option value="tab-fleet">4. Fleet & Vehicle Register</option>
            <option value="tab-routes">5. Routes & Fare Matrices (20% Discounts)</option>
            <option value="tab-rules">6. Departure Headway Rules</option>
            <option value="tab-announcements">7. Announcements & Advisories</option>
            <option value="tab-logs">8. Audit Trails & Logs</option>
            <option value="tab-history">9. Departure History Reports</option>
            <option value="tab-error-recovery">10. Error Recognition, Diagnosis & Recovery</option>
            <option value="tab-system">11. Daemons & System Health</option>
        </select>
    </div>

    <!-- Tab Navigation Pills -->
    <div class="manual-nav-pills">
        <button class="manual-pill-btn active" data-tab="tab-overview" onclick="switchManualTab('tab-overview', this)">
            <i class="fas fa-shield-alt"></i> Roles & Overview
        </button>
        <button class="manual-pill-btn" data-tab="tab-users" onclick="switchManualTab('tab-users', this)">
            <i class="fas fa-users-cog"></i> Users & Staff
        </button>
        <button class="manual-pill-btn" data-tab="tab-terminals" onclick="switchManualTab('tab-terminals', this)">
            <i class="fas fa-building"></i> Terminals & Bays
        </button>
        <button class="manual-pill-btn" data-tab="tab-fleet" onclick="switchManualTab('tab-fleet', this)">
            <i class="fas fa-bus"></i> Fleet Registry
        </button>
        <button class="manual-pill-btn" data-tab="tab-routes" onclick="switchManualTab('tab-routes', this)">
            <i class="fas fa-route"></i> Routes & Fares
        </button>
        <button class="manual-pill-btn" data-tab="tab-rules" onclick="switchManualTab('tab-rules', this)">
            <i class="fas fa-clock"></i> Departure Rules
        </button>
        <button class="manual-pill-btn" data-tab="tab-announcements" onclick="switchManualTab('tab-announcements', this)">
            <i class="fas fa-bullhorn"></i> Announcements
        </button>
        <button class="manual-pill-btn" data-tab="tab-logs" onclick="switchManualTab('tab-logs', this)">
            <i class="fas fa-clipboard-list"></i> Audit Logs
        </button>
        <button class="manual-pill-btn" data-tab="tab-history" onclick="switchManualTab('tab-history', this)">
            <i class="fas fa-history"></i> Departure History
        </button>
        <button class="manual-pill-btn" data-tab="tab-error-recovery" onclick="switchManualTab('tab-error-recovery', this)">
            <i class="fas fa-triangle-exclamation" style="color:#ef4444;"></i> Error Recovery
        </button>
        <button class="manual-pill-btn" data-tab="tab-system" onclick="switchManualTab('tab-system', this)">
            <i class="fas fa-server"></i> System Health
        </button>
    </div>

    <!-- Tab 1: Roles & Overview -->
    <div id="tab-overview" class="tab-content active">
        <div class="manual-card">
            <h3><i class="fas fa-users-cog" style="color:#3b82f6;"></i> User Roles & Access Hierarchy</h3>
            <p style="color:#475569; font-size:13.5px;">The PTTM System divides authority into clear, non-overlapping operational roles:</p>
            
            <!-- Desktop Table View -->
            <div class="table-responsive role-table-desktop">
                <table class="table-spec">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Access Scope</th>
                            <th>Key Capabilities</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Super Admin</strong></td>
                            <td>Full System</td>
                            <td>Create Admins and Staff, configure all terminal settings, purge historical logs, configure vehicle types.</td>
                        </tr>
                        <tr>
                            <td><strong>Admin</strong></td>
                            <td>Fleet & Routes</td>
                            <td>Register vehicles, create routes and fare matrices, configure departure rules, print official departure history.</td>
                        </tr>
                        <tr>
                            <td><strong>Dispatcher (Staff)</strong></td>
                            <td>Assigned Routes</td>
                            <td>Check vehicles into queues, transition Waiting &rarr; Boarding &rarr; Departed, live passenger counter, driver updates, 1-click Undo Cancel.</td>
                        </tr>
                        <tr>
                            <td><strong>Commuter (Public)</strong></td>
                            <td>Read-Only Public</td>
                            <td>Live queue monitor, estimated departure countdowns, seat progress, fare lookup with 20% discount calculator, departure search.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View (< 520px) — No Horizontal Table Overflow! -->
            <div class="role-cards-mobile">
                <div class="role-card-item" style="border-left: 4px solid #ef4444;">
                    <div class="role-card-item-header">
                        <strong>Super Admin</strong>
                        <span class="role-badge super">Full System Scope</span>
                    </div>
                    <p>Create Admins and Staff, configure all terminal settings, purge historical logs, configure vehicle types, manage daemons.</p>
                </div>

                <div class="role-card-item" style="border-left: 4px solid #3b82f6;">
                    <div class="role-card-item-header">
                        <strong>Admin</strong>
                        <span class="role-badge admin">Fleet & Routes Scope</span>
                    </div>
                    <p>Register vehicles, create routes and fare matrices, configure departure headway rules, print official departure history reports.</p>
                </div>

                <div class="role-card-item" style="border-left: 4px solid #10b981;">
                    <div class="role-card-item-header">
                        <strong>Dispatcher (Staff)</strong>
                        <span class="role-badge staff">Assigned Routes Scope</span>
                    </div>
                    <p>Check vehicles into queues, transition Waiting &rarr; Boarding &rarr; Departed, live passenger counter, driver updates, 1-click Undo Cancel.</p>
                </div>

                <div class="role-card-item" style="border-left: 4px solid #64748b;">
                    <div class="role-card-item-header">
                        <strong>Commuter (Public)</strong>
                        <span class="role-badge guest">Read-Only Public</span>
                    </div>
                    <p>Live queue monitor, estimated departure countdowns, seat progress, fare lookup with 20% discount calculator, departure search.</p>
                </div>
            </div>

            <div class="rule-callout">
                <i class="fas fa-info-circle" style="color:#3b82f6; margin-right:6px;"></i>
                <strong>Route Assignment Rule:</strong> When creating a staff account under <em>Management &rarr; Users</em>, ensure you check the specific route checkboxes they supervise. Unassigned dispatchers cannot check in vehicles on unauthorized routes.
            </div>
        </div>
    </div>

    <!-- Tab 2: User & Staff Management -->
    <div id="tab-users" class="tab-content">
        <div class="manual-card">
            <h3><i class="fas fa-users-cog" style="color:#6366f1;"></i> User & Staff Management (`/admin/users`)</h3>
            <p style="color:#475569; font-size:13.5px;">Administrators manage staff accounts, role assignments, and route boundaries:</p>
            
            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-text">
                    <h5>Creating a Staff Account</h5>
                    <p>Navigate to <strong>Management &rarr; Users</strong> and click <strong>+ Add New User</strong>. Enter username, full name, email, role (Admin or Staff), and initial password.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-text">
                    <h5>Assigning Authorized Routes (Staff Only)</h5>
                    <p>Check the destination checkboxes (e.g. Ormoc, Tacloban) that the dispatcher is authorized to operate. This prevents dispatchers from accidentally altering queues for other routes.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-text">
                    <h5>Password Resets & Account Deactivation</h5>
                    <p>Click <strong>Reset Password</strong> beside any user to issue a reset. To suspend access without corrupting audit history, toggle status to <code>Inactive</code>.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 3: Terminal & Bay Management -->
    <div id="tab-terminals" class="tab-content">
        <div class="manual-card">
            <h3><i class="fas fa-building" style="color:#0ea5e9;"></i> Terminal & Bay Management (`/admin/terminals`)</h3>
            <p style="color:#475569; font-size:13.5px;">Terminals represent physical dispatch stations and boarding bays:</p>
            
            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-text">
                    <h5>Add or Edit Terminal</h5>
                    <p>Go to <strong>Management &rarr; Terminals</strong> and click <strong>+ Add Terminal</strong>. Enter terminal name (e.g., <code>Central Terminal Bay 1</code>), address, and bay notes.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-text">
                    <h5>Active Status</h5>
                    <p>Setting terminal status to <code>Active</code> links it as an origin for routes and dispatch queues.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 4: Fleet & Vehicle Register -->
    <div id="tab-fleet" class="tab-content">
        <div class="manual-card">
            <h3><i class="fas fa-bus" style="color:#10b981;"></i> Fleet & Vehicle Registry (`/admin/vehicles`)</h3>
            
            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-text">
                    <h5>Navigate to Vehicle Register</h5>
                    <p>Go to <strong>Management &rarr; Vehicle Register</strong> and click <strong>+ Add Vehicle</strong>.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-text">
                    <h5>Plate Verification & Duplicate Collision Guard</h5>
                    <p>Enter the LTO Plate Number (e.g. <code>ABC-1234</code>). The system performs real-time validation via AJAX (<code>admin/vehicles/check-plate</code>) to prevent duplicate plate entries.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-text">
                    <h5>Classification & Seating Capacity</h5>
                    <p>Select the vehicle classification (PUJ Jeepney, UV Express Van, Modern Minibus). Certified seating capacity will pre-populate automatically (14 for vans, 18 for PUJs).</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">4</div>
                <div class="step-text">
                    <h5>Status: Active vs. Maintenance Guard</h5>
                    <p>Vehicles in <code>Active</code> status appear in the dispatcher's queue pool. Setting a vehicle to <code>Maintenance</code> immediately hides it from the dispatcher queue to prevent dispatching unroadworthy units.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 5: Route & Fare Matrix Management -->
    <div id="tab-routes" class="tab-content">
        <div class="manual-card">
            <h3><i class="fas fa-route" style="color:#8b5cf6;"></i> Route & Fare Matrix Configuration (`/admin/routes`)</h3>
            <p style="color:#475569; font-size:13.5px;">Configure official fare matrices adhering to LTFRB regulations:</p>
            
            <div class="rule-callout">
                <strong>Standard Formula:</strong> <code>Total Fare = Base Fare + (Distance in km - 4 km) × Rate per km</code><br>
                <strong>20% Statutory Discount:</strong> Automatically applied for verified Students, Senior Citizens, and PWDs across all routes.
            </div>

            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-text">
                    <h5>Add or Edit Route</h5>
                    <p>Go to <strong>Management &rarr; Routes</strong>. Enter destination (e.g., Ormoc City), highway distance in kilometers, base fare, and incremental per-km rate.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-text">
                    <h5>Instant Public Synchronization</h5>
                    <p>Once saved, the public Fare Matrix at <code>/fares</code> is updated dynamically. Commuters and conductors immediately see the authoritative rates.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 6: Departure Rules & Headway Scheduling -->
    <div id="tab-rules" class="tab-content">
        <div class="manual-card">
            <h3><i class="fas fa-clock" style="color:#f59e0b;"></i> Headway & Departure Interval Rules (`/admin/departure-rules`)</h3>
            <p style="color:#475569; font-size:13.5px;">Departure rules govern when a vehicle is expected to depart, balancing peak passenger demand against regular off-peak travel:</p>
            
            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-text">
                    <h5>Configure Time Windows</h5>
                    <p>Set a rule for specific hours (e.g., Morning Peak from 06:00:00 to 09:00:00). Enter a waiting interval (e.g. 20 minutes) and rule label.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-text">
                    <h5>Dynamic ETD Calculation</h5>
                    <p>When a dispatcher moves a trip to <strong>Boarding</strong>, the system evaluates active rules for that hour and assigns the target Estimated Departure Time (ETD) automatically.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-text">
                    <h5>Conflict Prevention</h5>
                    <p>The system prevents overlapping time windows for the same route to prevent ambiguous ETD assignments.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 7: Announcements & Advisories -->
    <div id="tab-announcements" class="tab-content">
        <div class="manual-card">
            <h3><i class="fas fa-bullhorn" style="color:#ea580c;"></i> Announcements & Emergency Advisories (`/admin/announcements`)</h3>
            <p style="color:#475569; font-size:13.5px;">Broadcast live advisories to all commuter and dispatcher screens:</p>
            
            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-text">
                    <h5>Publish Bulletin</h5>
                    <p>Go to <strong>Announcements</strong> and click <strong>+ New Announcement</strong>. Enter message text, set priority (Normal or High), and set status to Active.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-text">
                    <h5>Marquee & Modal Display</h5>
                    <p>High-priority announcements immediately display on the rolling top advisory marquee and within the bullhorn modal across all connected client devices.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 8: Audit Trails & Security Logs -->
    <div id="tab-logs" class="tab-content">
        <div class="manual-card">
            <h3><i class="fas fa-clipboard-list" style="color:#0ea5e9;"></i> Audit Trails & Security Logs (`/admin/logs`)</h3>
            <p style="color:#475569; font-size:13.5px;">The system maintains a tamper-resistant security ledger of every operational mutation:</p>
            
            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-text">
                    <h5>Recorded Metadata</h5>
                    <p>Every log captures: User identity & role, action type (Queue, Board, Depart, Cancel, Undo Cancel, Driver Swap), details (old vs. new values), timestamp, and client IP address.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-text">
                    <h5>Official Print Log Feature</h5>
                    <p>Click <strong>Print Logs</strong> to generate a clean, formatted audit ledger suitable for municipal administrative reporting and compliance audits.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 9: Departure History Reports -->
    <div id="tab-history" class="tab-content">
        <div class="manual-card">
            <h3><i class="fas fa-history" style="color:#2563eb;"></i> Departure History & Official Ledgers (`/admin/history`)</h3>
            <p style="color:#475569; font-size:13.5px;">Track and export historical departed trips:</p>
            
            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-text">
                    <h5>Filter Ledgers</h5>
                    <p>Filter departed vehicles by date range, franchised route, vehicle classification (PUJ, Van, Minibus), or license plate number.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-text">
                    <h5>Official Print Report</h5>
                    <p>Generate an official terminal departure report formatted for LGU records, displaying vehicle totals, passenger counts, and departure timestamps.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 10: Error Recognition, Diagnosis & Recovery -->
    <div id="tab-error-recovery" class="tab-content">
        <div class="manual-card">
            <h3><i class="fas fa-life-ring" style="color:#ef4444;"></i> Error Recognition, Diagnosis & Recovery</h3>
            <p style="color:#475569; font-size:13.5px; margin-bottom: 20px;">
                How PTTM helps administrators and dispatchers recognize, diagnose, and instantly recover from errors:
            </p>

            <div class="error-callout">
                <h5 style="margin:0 0 6px; font-weight:800;"><i class="fas fa-exclamation-triangle"></i> Scenario 1: Accidental Trip Cancellation</h5>
                <p style="margin:0;"><strong>Problem:</strong> Dispatcher clicks "Cancel" on a vehicle by mistake.</p>
                <p style="margin:4px 0 0;"><strong>Diagnosis:</strong> The system marks the trip as canceled, but does NOT purge the record.</p>
                <p style="margin:4px 0 0;"><strong>Recovery:</strong> A 1-click <code>Undo Cancel</code> button appears immediately. Clicking it restores the vehicle to the queue at its exact prior position with atomic database safety.</p>
            </div>

            <div class="rule-callout">
                <h5 style="margin:0 0 6px; font-weight:800;"><i class="fas fa-stopwatch"></i> Scenario 2: Post-Departure Cooldown Rule</h5>
                <p style="margin:0;"><strong>Problem:</strong> Driver departs, turns around immediately, and demands to re-enter the queue before the cooldown period passes (default: 30 minutes, customizable in System Settings &gt; Retention & Queue Rules).</p>
                <p style="margin:4px 0 0;"><strong>Diagnosis:</strong> Plain-language alert informs the dispatcher: <em>"Vehicle ABC-1234 departed recently. Please wait about X more minute(s) before adding it back."</em></p>
                <p style="margin:4px 0 0;"><strong>Recovery:</strong> The system automatically tracks the remaining minutes and re-enables the vehicle in the Available pool the instant the cooldown expires.</p>
            </div>

            <div class="success-callout">
                <h5 style="margin:0 0 6px; font-weight:800;"><i class="fas fa-shield-alt"></i> Scenario 3: The "No-Change Guard" (`no-change-guard.js`)</h5>
                <p style="margin:0;"><strong>Problem:</strong> Administrator opens an edit form or modal, makes no changes, and hits "Save".</p>
                <p style="margin:4px 0 0;"><strong>Diagnosis:</strong> Submitting unchanged forms generates duplicate audit logs and redundant WebSocket broadcasts.</p>
                <p style="margin:4px 0 0;"><strong>Recovery:</strong> The No-Change Guard detects identical form state and displays a friendly notice: <em>"No changes detected — nothing was updated."</em> It cancels the request cleanly without reloading.</p>
            </div>
        </div>
    </div>

    <!-- Tab 11: Daemons & System Health -->
    <div id="tab-system" class="tab-content">
        <div class="manual-card">
            <h3><i class="fas fa-server" style="color:#0ea5e9;"></i> Real-Time Daemon & Server Diagnostics</h3>
            
            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-text">
                    <h5>WebSocket Server Daemon</h5>
                    <p>PTTM uses a high-performance WebSocket daemon for sub-second terminal queue broadcasts. Start or monitor via terminal:</p>
                    <pre style="background:#0f172a; color:#38bdf8; padding:12px; border-radius:8px; font-size:13px; margin-top:6px; overflow-x:auto;">php spark ws:serve</pre>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-text">
                    <h5>Automatic Polling Failover</h5>
                    <p>If the WebSocket service is interrupted, client browsers automatically drop back to 20-second HTTP polling without throwing an error or logging the user out.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-text">
                    <h5>Log Inspection</h5>
                    <p>Application errors are written to <code>writable/logs/log-YYYY-MM-DD.log</code> and WebSocket events to <code>writable/logs/ws.log</code>.</p>
                </div>
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
    var select = document.getElementById('adminSectionSelect');
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
