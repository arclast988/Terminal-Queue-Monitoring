<!DOCTYPE html>
<html lang="en" class="layout-lock">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Terminal Monitoring System' ?></title>
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="csrf-header" content="<?= csrf_header() ?>">

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

    <!-- Modern Admin Styling (for logged-in users) - Remove this line to rollback -->
    <?php if (session()->get('isLoggedIn')): ?>
        <link rel="stylesheet" href="<?= base_url('assets/css/admin-modern.css') ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= base_url('assets/css/responsive.css') ?>">


    <style>
        /* Targeted lightweight transitions for interactive elements */
        .btn, a, .card, .vf-btn {
            transition: opacity 0.15s ease, background-color 0.15s ease, transform 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
        }

        /* Disable Bootstrap fade animation but allow transitionend to fire */
        .fade {
            opacity: 1 !important;
            transition: opacity 0.001s !important;
            animation: none !important;
        }

        .show {
            opacity: 1 !important;
        }

        /* Disable all Bootstrap animations */
        .modal.fade .modal-dialog {
            animation: none !important;
            transition: none !important;
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

        :root {
            --primary: #C62828;
            --primary-dark: #B71C1C;
            --secondary: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --bg-main: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #475569;
        }

        /* Admin theme: modern clean light layout with deep red accent */
        body.admin-theme {
            --primary: #C62828;
            --primary-dark: #B71C1C;
            --bg-main: #f1f5f9;
            --text-main: #1e293b;
            --text-muted: #475569;
            background-color: #f1f5f9 !important;
            color: #1e293b !important;
        }

        body.admin-theme .main-content {
            background-color: #f1f5f9 !important;
        }

        body.admin-theme .border-bottom {
            border-color: #e2e8f0 !important;
        }

        body.admin-theme .text-muted {
            color: #475569 !important;
        }
        
        body.admin-theme .card .text-muted,
        body.admin-theme .table .text-muted,
        body.admin-theme .list-group-item .text-muted {
            color: #475569 !important; 
        }

        body.admin-theme .h2,
        body.admin-theme .h1,
        body.admin-theme h1,
        body.admin-theme h2 {
            color: #1e293b !important;
        }

        body.admin-theme .card:not(.text-white) {
            background-color: white !important;
            border-color: #e2e8f0 !important;
            border: 1px solid #e2e8f0 !important;
            color: #1e293b !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
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
            color: #1e293b;
        }

        body.admin-theme .table-light {
            background-color: #f8fafc;
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
            border-color: #cbd5e1;
            color: #475569;
        }

        body.admin-theme .btn-outline-secondary:hover {
            background-color: #f1f5f9;
            border-color: #94a3b8;
            color: #1e293b;
        }

        /* Quick Actions: readable button and natural dropdown on admin dashboard */
        body.admin-theme .table .btn-outline-dark {
            border-color: #e2e8f0;
            color: #1e293b;
            background: #fff;
        }

        body.admin-theme .table .btn-outline-dark:hover {
            background-color: #f8fafc;
            border-color: #cbd5e1;
            color: #1e293b;
        }

        /* Staff theme: modern clean light layout with blue accent */
        body.staff-theme {
            --primary: #1565c0;
            --primary-dark: #0d47a1;
            --bg-main: #f1f5f9;
            --text-main: #1e293b;
            --text-muted: #475569;
            background-color: #f1f5f9 !important;
            color: #1e293b !important;
        }

        body.staff-theme .main-content {
            background-color: #f1f5f9 !important;
        }

        body.staff-theme .border-bottom {
            border-color: #e2e8f0 !important;
        }

        body.staff-theme .text-muted {
            color: #475569 !important;
        }
        
        body.staff-theme .card .text-muted,
        body.staff-theme .table .text-muted,
        body.staff-theme .list-group-item .text-muted {
            color: #475569 !important; 
        }

        body.staff-theme .h2,
        body.staff-theme .h1,
        body.staff-theme h1,
        body.staff-theme h2 {
            color: #1e293b !important;
        }

        body.staff-theme .card:not(.text-white) {
            background-color: white !important;
            border-color: #e2e8f0 !important;
            border: 1px solid #e2e8f0 !important;
            color: #1e293b !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
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
            color: #1e293b;
        }

        body.staff-theme .table-light {
            background-color: #f8fafc;
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
            border-color: #cbd5e1;
            color: #64748b;
        }

        body.staff-theme .btn-outline-secondary:hover {
            background-color: #f1f5f9;
            border-color: #94a3b8;
            color: #1e293b;
        }

        /* Quick Actions: readable button and natural dropdown on staff dashboard */
        body.staff-theme .table .btn-outline-dark {
            border-color: #e2e8f0;
            color: #1e293b;
            background: #fff;
        }

        body.staff-theme .table .btn-outline-dark:hover {
            background-color: #f8fafc;
            border-color: #cbd5e1;
            color: #1e293b;
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

        /* Reserve space for fixed header so content is not hidden underneath */
        .main-content {
            flex: 1;
            width: 100%;
            max-width: 100%;
            padding: 20px;
            padding-top: 72px;
            background-color: #f8fafc;
        }

        .footer {
            background-color: #f8f9fa;
            padding: 10px 0;
            text-align: center;
            margin-top: auto;
        }

        /* Schedules/Fares: same header as dashboard, but content area full-width (no padding) */
        body.public-page .main-content {
            padding: 0;
            padding-top: 72px;
        }

        /* Force header to stay fixed - cannot be overridden by page styles */
        html.layout-lock {
            overflow-y: auto !important;
            overflow-x: hidden !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        body.layout-lock {
            overflow-x: hidden !important;
            width: 100% !important;
            max-width: 100% !important;
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
            body.layout-lock header#site-header {
                padding-left: 16px !important;
                padding-right: 16px !important;
            }

            .main-content {
                padding: 15px;
                padding-top: 72px;
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

        /* Custom badge colors */
        .bg-purple {
            background-color: #6a1b9a !important;
        }

        /* =================================================================
           DARK MODE — respects system preference, overrides !important rules
           ================================================================= */
        @media (prefers-color-scheme: dark) {
            html, body {
                background-color: #0f172a !important;
            }
            body {
                background-color: #0f172a !important;
                color: #f1f5f9 !important;
            }
            body.admin-theme,
            body.admin-theme .main-content {
                background-color: #0f172a !important;
                color: #f1f5f9 !important;
            }
            body.staff-theme,
            body.staff-theme .main-content {
                background-color: #0f172a !important;
                color: #f1f5f9 !important;
            }
            body.admin-theme .card:not(.text-white),
            body.staff-theme .card:not(.text-white) {
                background-color: #1e293b !important;
                border-color: #334155 !important;
                color: #f1f5f9 !important;
            }
            body.admin-theme .card.text-white,
            body.staff-theme .card.text-white {
                border-color: #334155 !important;
            }
            body.admin-theme .text-muted,
            body.staff-theme .text-muted {
                color: #94a3b8 !important;
            }
            body.admin-theme .card-header:not(.bg-dark):not(.bg-primary):not(.bg-info):not(.bg-secondary):not(.bg-success),
            body.staff-theme .card-header:not(.bg-dark):not(.bg-primary):not(.bg-info):not(.bg-secondary):not(.bg-success) {
                background-color: #1e293b !important;
                border-bottom-color: #334155 !important;
                color: #f1f5f9 !important;
            }
            body.admin-theme .table,
            body.admin-theme .table td,
            body.admin-theme .table th,
            body.staff-theme .table,
            body.staff-theme .table td,
            body.staff-theme .table th {
                color: #e2e8f0 !important;
            }
            body.admin-theme .modern-card,
            body.staff-theme .modern-card {
                background: #1e293b !important;
                border-color: #334155 !important;
            }
            body.admin-theme .modern-card-header,
            body.staff-theme .modern-card-header {
                background: #1e293b !important;
                border-bottom-color: #334155 !important;
            }
            body.admin-theme .stat-card-modern,
            body.staff-theme .stat-card-modern {
                background: #1e293b !important;
                border-color: #334155 !important;
            }
            body.admin-theme .modal-content,
            body.staff-theme .modal-content {
                background-color: #1e293b !important;
                border-color: #334155 !important;
                color: #f1f5f9 !important;
            }
            body.admin-theme .modal-header,
            body.staff-theme .modal-header,
            body.admin-theme .modal-body,
            body.staff-theme .modal-body,
            body.admin-theme .modal-footer,
            body.staff-theme .modal-footer {
                background-color: #1e293b !important;
                border-color: #334155 !important;
                color: #f1f5f9 !important;
            }
            body.admin-theme .modal-title,
            body.staff-theme .modal-title,
            body.admin-theme .modal-body p,
            body.staff-theme .modal-body p,
            body.admin-theme .modal-body small,
            body.staff-theme .modal-body small,
            body.admin-theme .modal-header h5,
            body.staff-theme .modal-header h5 {
                color: #f1f5f9 !important;
            }
            body.admin-theme .table thead th,
            body.staff-theme .table thead th {
                background: #334155 !important;
                border-bottom-color: #475569 !important;
                color: #94a3b8 !important;
            }
            body.admin-theme .table tbody tr,
            body.staff-theme .table tbody tr {
                background: #1e293b;
            }
            body.admin-theme .table tbody td,
            body.staff-theme .table tbody td {
                border-bottom-color: #334155 !important;
            }
            body.admin-theme .table-striped tbody tr:nth-of-type(odd),
            body.staff-theme .table-striped tbody tr:nth-of-type(odd) {
                background-color: #1e293b !important;
            }
            body.admin-theme .table tbody tr:hover,
            body.staff-theme .table tbody tr:hover {
                background-color: #334155 !important;
            }
            body.admin-theme .table-striped tbody tr:nth-of-type(odd):hover,
            body.staff-theme .table-striped tbody tr:nth-of-type(odd):hover {
                background-color: #334155 !important;
            }
            body.admin-theme .form-control,
            body.admin-theme .form-select,
            body.staff-theme .form-control,
            body.staff-theme .form-select {
                background-color: #1e293b !important;
                border-color: #475569 !important;
                color: #f1f5f9 !important;
            }
            body.admin-theme .form-label,
            body.staff-theme .form-label {
                color: #94a3b8 !important;
            }
            body.admin-theme .page-title-modern,
            body.staff-theme .page-title-modern,
            body.admin-theme .stat-card-value,
            body.staff-theme .stat-card-value,
            body.admin-theme .modern-card-title,
            body.staff-theme .modern-card-title {
                color: #f1f5f9 !important;
            }
            body.admin-theme .stat-card-label,
            body.staff-theme .stat-card-label {
                color: #94a3b8 !important;
            }
            body.admin-theme .input-modern,
            body.admin-theme .form-control-modern,
            body.admin-theme .form-select-modern,
            body.staff-theme .input-modern,
            body.staff-theme .form-control-modern,
            body.staff-theme .form-select-modern {
                background: #1e293b !important;
                border-color: #475569 !important;
                color: #f1f5f9 !important;
            }
            body.admin-theme .form-label-modern,
            body.staff-theme .form-label-modern {
                color: #94a3b8 !important;
            }
            body.admin-theme .breadcrumb,
            body.staff-theme .breadcrumb {
                background: #1e293b !important;
                border-color: #334155 !important;
            }
            body.admin-theme .alert,
            body.staff-theme .alert {
                border-color: #334155 !important;
            }
            body.admin-theme .list-group-item,
            body.staff-theme .list-group-item {
                background: #1e293b !important;
                border-bottom-color: #334155 !important;
            }
            body.admin-theme .page-header-modern,
            body.staff-theme .page-header-modern,
            body.admin-theme .border-bottom,
            body.staff-theme .border-bottom {
                border-bottom-color: #334155 !important;
            }
            body.admin-theme .pagination .page-link,
            body.staff-theme .pagination .page-link {
                background: #1e293b !important;
                color: #94a3b8 !important;
            }
            body.admin-theme .pagination .page-item.active .page-link,
            body.staff-theme .pagination .page-item.active .page-link {
                background: var(--primary) !important;
                color: #fff !important;
            }
            body.admin-theme .dropdown-content,
            body.staff-theme .dropdown-content {
                background: #1e293b !important;
                border-color: #334155 !important;
            }
            body.admin-theme .dropdown-content a,
            body.staff-theme .dropdown-content a {
                color: #e2e8f0 !important;
            }
            body.admin-theme .dropdown-content a:hover,
            body.staff-theme .dropdown-content a:hover {
                background: #334155 !important;
            }
            body.admin-theme .input-group-text,
            body.staff-theme .input-group-text {
                background: #334155 !important;
                border-color: #475569 !important;
                color: #94a3b8 !important;
            }
            body.admin-theme .table-modern thead th,
            body.staff-theme .table-modern thead th {
                background: linear-gradient(to bottom, #334155, #1e293b) !important;
                border-bottom-color: #475569 !important;
                color: #94a3b8 !important;
            }
            body.admin-theme .table-modern tbody tr,
            body.staff-theme .table-modern tbody tr {
                background: #1e293b !important;
            }
            body.admin-theme .table-modern tbody td,
            body.staff-theme .table-modern tbody td {
                border-bottom-color: #334155 !important;
                color: #e2e8f0 !important;
            }
            body.admin-theme .table-modern tbody tr:hover,
            body.staff-theme .table-modern tbody tr:hover {
                background: #334155 !important;
            }
            body.admin-theme .btn-modern-outline,
            body.staff-theme .btn-modern-outline {
                background: transparent !important;
                border-color: #475569 !important;
                color: #94a3b8 !important;
            }
            body.admin-theme .btn-modern-outline:hover,
            body.staff-theme .btn-modern-outline:hover {
                border-color: #64748b !important;
                background: #1e293b !important;
                color: #e2e8f0 !important;
            }
            body.admin-theme .btn-outline-secondary,
            body.staff-theme .btn-outline-secondary {
                border-color: #475569 !important;
                background: transparent !important;
                color: #94a3b8 !important;
            }
            body.admin-theme .btn-outline-secondary:hover,
            body.staff-theme .btn-outline-secondary:hover {
                background: #1e293b !important;
                border-color: #64748b !important;
                color: #e2e8f0 !important;
            }
            body.admin-theme .stat-card-link,
            body.staff-theme .stat-card-link {
                color: var(--primary) !important;
            }
        }
    </style>
</head>

<body
    class="layout-lock <?= (session()->get('role') === 'admin' ? 'admin-theme ' : (session()->get('role') === 'staff' ? 'staff-theme ' : '')) ?><?= esc($body_class ?? '') ?>">
    <?php include __DIR__ . '/navbar.php'; ?>
    <div class="main-content container-fluid">