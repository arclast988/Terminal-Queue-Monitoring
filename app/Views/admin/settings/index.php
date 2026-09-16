<?= $this->include('templates/header') ?>

<?php
$s = $settings ?? [];
$logoUrl        = app_logo();
$bgUrl          = app_bg_image();
$bgMode         = app_bg_mode();
$slideshowUrls  = app_bg_slideshow();

$hasCustomLogo  = !empty($s['app_logo']);
$hasCustomBg    = !empty($s['app_background_image']);

// Slot metadata definitions
$slotsMeta = [
    1 => [
        'name'        => 'Town Hall / Municipal Landmark',
        'desc'        => 'Palompon Municipal Hall and municipal landmark',
        'defaultFile' => 'bg1_townhall.png',
        'isCustom'    => !empty($s['app_bg_slideshow_1']),
    ],
    2 => [
        'name'        => 'Aerial Port & Sea Gateway',
        'desc'        => 'Palompon Port, ocean terminal and coastline',
        'defaultFile' => 'bg2_aerial_port.png',
        'isCustom'    => !empty($s['app_bg_slideshow_2']),
    ],
    3 => [
        'name'        => 'Aerial Townscape',
        'desc'        => 'Bird\'s eye view of Palompon municipality and streets',
        'defaultFile' => 'bg3_aerial_town.png',
        'isCustom'    => !empty($s['app_bg_slideshow_3']),
    ],
    4 => [
        'name'        => 'Terminal Exterior & Facade',
        'desc'        => 'Terminal building entrance, parking & drop-off zone',
        'defaultFile' => 'bg4_terminal_exterior.png',
        'isCustom'    => !empty($s['app_bg_slideshow_4']),
    ],
    5 => [
        'name'        => 'Terminal Bay & Vehicle Queue',
        'desc'        => 'Active transit passenger bays and vehicle fleet',
        'defaultFile' => 'bg5_terminal_bay.png',
        'isCustom'    => !empty($s['app_bg_slideshow_5']),
    ],
];
?>

<style>
/* ==========================================================================
   Superadmin Branding & Customization Modern Styles
   ========================================================================== */

:root {
    --sb-primary: #B71C1C;
    --sb-primary-hover: #991b1b;
    --sb-primary-light: rgba(183, 28, 28, 0.08);
    --sb-surface: #ffffff;
    --sb-surface-subtle: #f8fafc;
    --sb-border: #e2e8f0;
    --sb-border-strong: #cbd5e1;
    --sb-text-main: #0f172a;
    --sb-text-muted: #64748b;
    --sb-radius-sm: 8px;
    --sb-radius-md: 12px;
    --sb-radius-lg: 16px;
    --sb-shadow-sm: 0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.02);
    --sb-shadow-md: 0 4px 14px -2px rgba(0,0,0,0.06), 0 2px 6px -1px rgba(0,0,0,0.03);
    --sb-shadow-lg: 0 10px 25px -3px rgba(0,0,0,0.08), 0 4px 10px -2px rgba(0,0,0,0.04);
}

.settings-page {
    max-width: 1240px;
    margin: 0 auto;
    padding: 10px 16px 40px;
}

/* Page Header */
.settings-header-card {
    background: #ffffff !important;
    border-radius: var(--sb-radius-lg);
    padding: 24px 28px;
    margin-bottom: 24px;
    color: var(--sb-text-main, #0f172a);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
    border-left: 5px solid var(--sb-primary, #B71C1C);
    position: relative;
    overflow: hidden;
}

.settings-header-card::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 280px;
    height: 280px;
    background: radial-gradient(circle, rgba(183, 28, 28, 0.05) 0%, transparent 70%);
    pointer-events: none;
}

.settings-header-left {
    display: flex;
    align-items: center;
    gap: 16px;
    position: relative;
    z-index: 1;
}

.settings-header-icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    background: linear-gradient(135deg, #B71C1C 0%, #ef4444 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(183, 28, 28, 0.35);
}

.settings-header-card h1 {
    font-size: 22px;
    font-weight: 700;
    margin: 0;
    color: #0f172a !important;
    -webkit-text-fill-color: #0f172a !important;
    letter-spacing: -0.3px;
}

.settings-header-card p {
    font-size: 13.5px;
    color: #64748b !important;
    margin: 4px 0 0;
    max-width: 600px;
    line-height: 1.45;
}

.settings-header-badges {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    position: relative;
    z-index: 1;
}

.sa-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
    font-size: 11.5px;
    font-weight: 700;
    padding: 6px 14px;
    border-radius: 20px;
    letter-spacing: 0.4px;
    text-transform: uppercase;
}

.sa-link-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f8fafc;
    color: #475569;
    border: 1px solid #cbd5e1;
    font-size: 12px;
    font-weight: 600;
    padding: 6px 14px;
    border-radius: 20px;
    text-decoration: none;
    transition: all 0.2s ease;
}

.sa-link-btn:hover {
    background: #f1f5f9;
    color: #0f172a;
    transform: translateY(-1px);
}

/* Tabs Navigation */
.settings-tabs-wrapper {
    position: relative;
    margin-bottom: 24px;
    background: #ffffff;
    padding: 8px 10px;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
}

.settings-tabs {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
    padding: 2px;
    border-bottom: none;
}

.settings-tabs::-webkit-scrollbar {
    display: none;
}

.settings-tab {
    padding: 10px 18px;
    font-size: 13.5px;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
    border: 1.5px solid #e2e8f0;
    background: #f8fafc;
    border-radius: 10px;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}

.settings-tab i {
    font-size: 15px;
    color: #64748b;
    transition: transform 0.2s ease, color 0.2s ease;
}

.settings-tab:hover {
    color: var(--sb-primary, #B71C1C);
    background: #fef2f2;
    border-color: #fca5a5;
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(183, 28, 28, 0.12);
}

.settings-tab:hover i {
    color: var(--sb-primary, #B71C1C);
    transform: scale(1.1);
}

.settings-tab.active {
    color: #ffffff !important;
    background: var(--sb-primary, #B71C1C) !important;
    border-color: var(--sb-primary, #B71C1C) !important;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(183, 28, 28, 0.32);
}

.settings-tab.active i {
    color: #ffffff !important;
}

/* Tab Panels */
.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
    animation: sbFadeIn 0.25s ease-out;
}

@keyframes sbFadeIn {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Cards */
.settings-card {
    background: var(--sb-surface);
    border: 1px solid var(--sb-border);
    border-radius: var(--sb-radius-lg);
    padding: 26px;
    margin-bottom: 24px;
    box-shadow: var(--sb-shadow-sm);
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.settings-card:hover {
    box-shadow: var(--sb-shadow-md);
}

.settings-card-header {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    margin-bottom: 22px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--sb-border);
}

.settings-card-header .card-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: var(--sb-primary-light);
    color: var(--sb-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}

.settings-card-header h3 {
    font-size: 17px;
    font-weight: 700;
    color: var(--sb-text-main);
    margin: 0 0 4px;
}

.settings-card-header p {
    font-size: 13px;
    color: var(--sb-text-muted);
    margin: 0;
    line-height: 1.45;
}

/* Form Styles */
.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 18px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--sb-text-main);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.label-hint {
    font-weight: 400;
    color: var(--sb-text-muted);
    font-size: 11.5px;
}

.form-input {
    padding: 10px 14px;
    border: 1.5px solid var(--sb-border);
    border-radius: var(--sb-radius-sm);
    font-size: 14px;
    font-family: inherit;
    color: var(--sb-text-main);
    background: var(--sb-surface);
    transition: all 0.2s ease;
    width: 100%;
}

.form-input:focus {
    outline: none;
    border-color: var(--sb-primary);
    box-shadow: 0 0 0 3px rgba(183, 28, 28, 0.1);
    background: #ffffff;
}

.form-textarea {
    min-height: 95px;
    resize: vertical;
    line-height: 1.5;
}

/* Variable Chips Bar */
.tag-chips-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 6px;
    font-size: 12px;
}

.tag-chips-bar span.chip-label {
    color: var(--sb-text-muted);
    font-weight: 500;
}

.tag-chip {
    display: inline-flex;
    align-items: center;
    background: #f1f5f9;
    border: 1px solid var(--sb-border);
    padding: 3px 8px;
    border-radius: 6px;
    font-family: monospace;
    font-size: 11.5px;
    color: #0f172a;
    cursor: pointer;
    transition: all 0.15s ease;
}

.tag-chip:hover {
    background: #e2e8f0;
    border-color: var(--sb-primary);
    color: var(--sb-primary);
}

/* Save Buttons */
.btn-save {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 26px;
    background: #C62828;
    color: #ffffff !important;
    border: none;
    border-radius: var(--sb-radius-sm, 8px);
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(198, 40, 40, 0.3);
}

.btn-save:hover {
    background: #B71C1C;
    color: #ffffff !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(183, 28, 28, 0.45);
}

.btn-save:active {
    transform: translateY(0);
}

.btn-save i {
    font-size: 14px;
    color: #ffffff !important;
}

.form-action-row {
    margin-top: 22px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 12px;
    padding-top: 16px;
    border-top: 1px solid var(--sb-border);
}

/* ==========================================================================
   Live Preview Modules
   ========================================================================== */
.live-preview-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.preview-card {
    border: 1px solid var(--sb-border);
    border-radius: var(--sb-radius-md);
    overflow: hidden;
    background: #ffffff;
    box-shadow: var(--sb-shadow-sm);
}

.preview-card-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: #64748b;
    padding: 8px 14px;
    background: #f8fafc;
    border-bottom: 1px solid var(--sb-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.preview-header-bar {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 18px;
    transition: background-color 0.2s ease, color 0.2s ease;
}

.preview-header-bar img {
    width: 38px;
    height: 38px;
    object-fit: contain;
    border-radius: 8px;
    background: rgba(255,255,255,0.1);
    flex-shrink: 0;
}

.preview-header-bar .preview-brand h4 {
    font-size: 14.5px;
    font-weight: 700;
    margin: 0;
    line-height: 1.25;
}

.preview-header-bar .preview-brand p {
    font-size: 10.5px;
    margin: 1px 0 0;
    opacity: 0.82;
    font-weight: 600;
    letter-spacing: 0.3px;
}

/* Public Live Footer Preview (Matching User Screenshot) */
.footer-preview-container {
    background: #1e293b;
    color: #f1f5f9;
    border-radius: var(--sb-radius-md);
    padding: 24px 26px 18px;
    position: relative;
    overflow: hidden;
}

.footer-preview-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #f97316 0%, #ef4444 50%, #f97316 100%);
}

.footer-preview-about h4 {
    color: #f97316;
    font-size: 17px;
    font-weight: 700;
    margin: 0 0 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.footer-preview-about p {
    font-size: 13.5px;
    line-height: 1.6;
    color: #cbd5e1;
    margin: 0;
    max-width: 800px;
}

.footer-preview-divider {
    height: 1px;
    background: rgba(255, 255, 255, 0.12);
    margin: 20px 0 14px;
}

.footer-preview-copyright {
    font-size: 12px;
    color: #94a3b8;
    text-align: center;
    line-height: 1.5;
}

/* ==========================================================================
   Media & Background Management
   ========================================================================== */

/* Mode Switcher Radio Card */
.bg-mode-selector {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 24px;
}

.bg-mode-card {
    border: 2px solid var(--sb-border);
    border-radius: var(--sb-radius-md);
    padding: 16px 20px;
    cursor: pointer;
    background: var(--sb-surface);
    transition: all 0.2s ease;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    position: relative;
}

.bg-mode-card:hover {
    border-color: #94a3b8;
    background: #f8fafc;
}

.bg-mode-card.active {
    border-color: var(--sb-primary);
    background: rgba(183, 28, 28, 0.02);
    box-shadow: 0 0 0 1px var(--sb-primary);
}

.bg-mode-card input[type="radio"] {
    margin-top: 3px;
    accent-color: var(--sb-primary);
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.bg-mode-info h4 {
    font-size: 14.5px;
    font-weight: 700;
    margin: 0 0 3px;
    color: var(--sb-text-main);
}

.bg-mode-info p {
    font-size: 12px;
    color: var(--sb-text-muted);
    margin: 0;
    line-height: 1.4;
}

/* Upload Zone */
.upload-zone {
    border: 2px dashed var(--sb-border-strong);
    border-radius: var(--sb-radius-md);
    padding: 26px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    background: var(--sb-surface-subtle);
    position: relative;
}

.upload-zone:hover, .upload-zone.drag-over {
    border-color: var(--sb-primary);
    background: rgba(183, 28, 28, 0.03);
    transform: translateY(-1px);
}

.upload-zone .upload-icon {
    font-size: 32px;
    color: #94a3b8;
    margin-bottom: 8px;
    transition: transform 0.2s ease;
}

.upload-zone:hover .upload-icon {
    transform: scale(1.1);
    color: var(--sb-primary);
}

.upload-zone .upload-text {
    font-size: 13.5px;
    color: var(--sb-text-muted);
}

.upload-zone .upload-text strong {
    color: var(--sb-primary);
}

.upload-zone .upload-hint {
    font-size: 11.5px;
    color: #94a3b8;
    margin-top: 4px;
}

/* Media Preview Cards */
.media-preview-card {
    display: flex;
    align-items: center;
    gap: 18px;
    padding: 16px;
    background: #f8fafc;
    border: 1px solid var(--sb-border);
    border-radius: var(--sb-radius-md);
    margin-top: 14px;
}

.media-preview-card img {
    width: 72px;
    height: 72px;
    object-fit: contain;
    border-radius: 10px;
    border: 1px solid var(--sb-border);
    background: #ffffff;
    padding: 4px;
    box-shadow: var(--sb-shadow-sm);
}

.media-preview-card.bg-type img {
    width: 140px;
    height: 80px;
    object-fit: cover;
    padding: 0;
}

.media-preview-info {
    flex: 1;
    min-width: 0;
}

.media-preview-info .filename {
    font-size: 13.5px;
    font-weight: 600;
    color: var(--sb-text-main);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.media-preview-info .filemeta {
    font-size: 12px;
    color: var(--sb-text-muted);
    margin-top: 3px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.status-chip {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.status-chip.custom {
    background: #dcfce7;
    color: #15803d;
}

.status-chip.default {
    background: #f1f5f9;
    color: #475569;
}

.btn-reset-media {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    background: #ffffff;
    color: #dc2626;
    border: 1.5px solid #fca5a5;
    border-radius: 8px;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
    white-space: nowrap;
}

.btn-reset-media:hover {
    background: #fef2f2;
    border-color: #dc2626;
    transform: translateY(-1px);
}

/* ==========================================================================
   Multi-Slot Slideshow Grid
   ========================================================================== */
.slideshow-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 16px;
    margin-top: 16px;
}

.slideshow-slot-card {
    background: #ffffff;
    border: 1.5px solid var(--sb-border);
    border-radius: var(--sb-radius-md);
    overflow: hidden;
    box-shadow: var(--sb-shadow-sm);
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
}

.slideshow-slot-card:hover {
    border-color: #94a3b8;
    box-shadow: var(--sb-shadow-md);
    transform: translateY(-2px);
}

.slot-card-image-wrap {
    width: 100%;
    aspect-ratio: 16 / 9;
    position: relative;
    background: #0f172a;
    overflow: hidden;
}

.slot-card-image-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.slideshow-slot-card:hover .slot-card-image-wrap img {
    transform: scale(1.05);
}

.slot-number-badge {
    position: absolute;
    top: 8px;
    left: 8px;
    background: rgba(15, 23, 42, 0.85);
    color: #ffffff;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 6px;
    backdrop-filter: blur(4px);
    border: 1px solid rgba(255,255,255,0.15);
}

.slot-status-badge {
    position: absolute;
    bottom: 8px;
    right: 8px;
    font-size: 10px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 10px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.slot-status-badge.custom {
    background: #16a34a;
    color: #ffffff;
}

.slot-status-badge.default {
    background: rgba(15, 23, 42, 0.75);
    color: #e2e8f0;
    border: 1px solid rgba(255,255,255,0.2);
}

.slot-card-body {
    padding: 12px 14px;
    display: flex;
    flex-direction: column;
    flex: 1;
}

.slot-card-body h5 {
    font-size: 13px;
    font-weight: 700;
    margin: 0 0 3px;
    color: var(--sb-text-main);
    line-height: 1.3;
}

.slot-card-body p {
    font-size: 11px;
    color: var(--sb-text-muted);
    margin: 0 0 10px;
    line-height: 1.35;
    flex: 1;
}

.slot-card-actions {
    display: flex;
    gap: 6px;
    align-items: center;
    margin-top: auto;
}

.btn-slot-upload {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 7px 10px;
    background: #f1f5f9;
    color: #0f172a;
    border: 1px solid var(--sb-border);
    border-radius: 6px;
    font-size: 11.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
}

.btn-slot-upload:hover {
    background: var(--sb-primary);
    border-color: var(--sb-primary);
    color: #ffffff;
}

.btn-slot-reset {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 7px 10px;
    background: #fff;
    color: #dc2626;
    border: 1px solid #fca5a5;
    border-radius: 6px;
    font-size: 11.5px;
    cursor: pointer;
    transition: all 0.15s ease;
}

.btn-slot-reset:hover {
    background: #fef2f2;
    border-color: #dc2626;
}

/* ==========================================================================
   Theme Palettes & Preset Themes
   ========================================================================== */
.theme-presets-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 22px;
    padding: 14px 18px;
    background: #f8fafc;
    border: 1px solid var(--sb-border);
    border-radius: var(--sb-radius-md);
}

.theme-presets-bar span.preset-title {
    font-size: 12.5px;
    font-weight: 700;
    color: var(--sb-text-main);
}

.btn-preset {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border-radius: 20px;
    font-size: 12.5px;
    font-weight: 600;
    background: #ffffff;
    border: 1.5px solid var(--sb-border, #e2e8f0);
    color: #334155;
    cursor: pointer;
    transition: all 0.15s ease;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}

.btn-preset:hover {
    border-color: var(--sb-primary, #B71C1C);
    color: var(--sb-primary, #B71C1C);
    background: #fff5f5;
    transform: translateY(-1px);
}

.btn-preset .palette-dots {
    display: inline-flex;
    gap: 3px;
}

.btn-preset .p-dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
}

.theme-section {
    background: #f8fafc;
    border: 1px solid var(--sb-border);
    border-radius: var(--sb-radius-md);
    padding: 18px 20px;
    margin-bottom: 16px;
}

.theme-section-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 14px;
}

.theme-section-header .theme-dot {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 2px solid #ffffff;
    box-shadow: 0 0 0 1px #cbd5e1;
}

.theme-section-header h4 {
    font-size: 15px;
    font-weight: 700;
    color: var(--sb-text-main);
    margin: 0;
}

.color-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
}

.color-field {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.color-field label {
    font-size: 12px;
    font-weight: 600;
    color: var(--sb-text-muted);
}

.color-picker-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
}

.color-picker-wrap input[type="color"] {
    width: 42px;
    height: 40px;
    border: 1.5px solid var(--sb-border);
    border-radius: 8px;
    cursor: pointer;
    padding: 2px;
    background: #ffffff;
}

.color-picker-wrap input[type="text"] {
    flex: 1;
    padding: 9px 12px;
    border: 1.5px solid var(--sb-border);
    border-radius: 8px;
    font-family: monospace;
    font-size: 13px;
    color: var(--sb-text-main);
    background: #ffffff;
    text-transform: uppercase;
}

/* Toast Notifications */
.sb-toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    padding: 12px 20px;
    background: #0f172a;
    color: #ffffff;
    border-radius: 10px;
    font-size: 13.5px;
    font-weight: 600;
    box-shadow: 0 10px 25px rgba(0,0,0,0.25);
    display: flex;
    align-items: center;
    gap: 10px;
    z-index: 99999;
    transform: translateY(100px);
    opacity: 0;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    border-left: 4px solid var(--sb-primary);
}

.sb-toast.show {
    transform: translateY(0);
    opacity: 1;
}

.sb-toast.success {
    border-left-color: #22c55e;
}

.sb-toast.error {
    border-left-color: #ef4444;
}

/* ==========================================================================
   Mobile Responsiveness Media Queries
   ========================================================================= */
@media (max-width: 900px) {
    .form-grid { grid-template-columns: 1fr; }
    .live-preview-grid { grid-template-columns: 1fr; }
    .color-grid { grid-template-columns: 1fr; }
    .bg-mode-selector { grid-template-columns: 1fr; }
}

@media (max-width: 640px) {
    .settings-page { padding: 8px 10px 30px; }
    .settings-header-card { padding: 18px 16px; border-radius: 12px; }
    .settings-header-icon { width: 42px; height: 42px; font-size: 18px; }
    .settings-header-card h1 { font-size: 18px; }
    .settings-card { padding: 18px 14px; border-radius: 12px; }
    .settings-tab { padding: 10px 14px; font-size: 12.5px; }
    .btn-save { width: 100%; justify-content: center; }
    .form-action-row { justify-content: stretch; }
    .media-preview-card { flex-direction: column; text-align: center; }
    .media-preview-card.bg-type img { width: 100%; height: 120px; }
    .slideshow-grid { grid-template-columns: 1fr; }
}
</style>

<div class="settings-page">

    <!-- Top Executive Header -->
    <div class="settings-header-card">
        <div class="settings-header-left">
            <div class="settings-header-icon">
                <i class="fas fa-palette"></i>
            </div>
            <div>
                <h1>System Branding & Themes</h1>
                <p>Configure public identity, visual role themes, high-resolution media assets, and footer attribution for the Palompon Transit network.</p>
            </div>
        </div>
        <div class="settings-header-badges">
            <span class="sa-badge"><i class="fas fa-shield-alt"></i> Super Admin Only</span>
        </div>
    </div>

    <!-- Alert Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert-modern alert-modern-success fade-in" style="margin-bottom: 20px;">
            <i class="bi bi-check-circle-fill alert-modern-icon"></i>
            <div><?= esc(session()->getFlashdata('success')) ?></div>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert-modern alert-modern-danger fade-in" style="margin-bottom: 20px;">
            <i class="bi bi-exclamation-circle-fill alert-modern-icon"></i>
            <div><?= esc(session()->getFlashdata('error')) ?></div>
        </div>
    <?php endif; ?>

    <!-- Segmented Navigation Tabs -->
    <div class="settings-tabs-wrapper">
        <div class="settings-tabs" role="tablist">
            <button class="settings-tab active" data-tab="identity" role="tab" id="tabBtn-identity">
                <i class="fas fa-id-badge"></i> System Identity
            </button>
            <button class="settings-tab" data-tab="media" role="tab" id="tabBtn-media">
                <i class="fas fa-images"></i> Logo & Background Pictures
            </button>
            <button class="settings-tab" data-tab="themes" role="tab" id="tabBtn-themes">
                <i class="fas fa-swatchbook"></i> Role Themes
            </button>
            <button class="settings-tab" data-tab="footer" role="tab" id="tabBtn-footer">
                <i class="fas fa-layer-group"></i> Footer & Public Attribution
            </button>
        </div>
    </div>

    <!-- ====================================================================
         TAB 1: SYSTEM IDENTITY
         ==================================================================== -->
    <div class="tab-content active" id="tab-identity">
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="card-icon"><i class="fas fa-building"></i></div>
                <div>
                    <h3>Transit Network Identity</h3>
                    <p>Customize the official agency name, subtitle, acronym, and primary terminal contact details displayed across all public screens, navigation headers, and passenger schedules.</p>
                </div>
            </div>

            <!-- Real-time Live Header Preview Cluster -->
            <div class="live-preview-grid">
                <div class="preview-card">
                    <div class="preview-card-label">
                        <span><i class="fas fa-users"></i> Guest / Public Header</span>
                        <small>Desktop & Mobile Navbar</small>
                    </div>
                    <div class="preview-header-bar" id="previewGuest" style="background: <?= esc($s['theme_guest_nav_bg'] ?? '#ffffff') ?>; color: <?= esc($s['theme_guest_nav_text'] ?? '#1c2430') ?>;">
                        <div style="display:flex; align-items:center; gap:10px; min-width:0;">
                            <img src="<?= esc($logoUrl) ?>" alt="Logo" class="previewLogoImg">
                            <div class="preview-brand">
                                <h4 class="previewNameEl"><?= esc($s['app_name'] ?? 'Palompon Transit') ?></h4>
                                <p class="previewSubEl"><?= esc($s['app_subtitle'] ?? 'Terminal Monitor') ?></p>
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px; margin-left:auto; flex-shrink:0;">
                            <span class="preview-nav-link" style="font-size:11.5px; font-weight:600; opacity:0.85;">Schedules</span>
                            <span class="preview-btn-guest" style="background: <?= esc($s['theme_guest_primary'] ?? '#C62828') ?>; color: #ffffff; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                Login <i class="fas fa-arrow-right" style="font-size:9px;"></i>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="preview-card">
                    <div class="preview-card-label">
                        <span><i class="fas fa-shield-alt"></i> Admin / Super Admin Header</span>
                        <small>Operations Dashboard</small>
                    </div>
                    <div class="preview-header-bar" id="previewAdmin" style="background: <?= esc($s['theme_admin_nav_bg'] ?? '#B71C1C') ?>; color: <?= esc($s['theme_admin_nav_text'] ?? '#ffffff') ?>;">
                        <div style="display:flex; align-items:center; gap:10px; min-width:0;">
                            <img src="<?= esc($logoUrl) ?>" alt="Logo" class="previewLogoImg">
                            <div class="preview-brand">
                                <h4 class="previewNameEl"><?= esc($s['app_name'] ?? 'Palompon Transit') ?></h4>
                                <p class="previewSubEl"><?= esc($s['app_subtitle'] ?? 'Terminal Monitor') ?></p>
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px; margin-left:auto; flex-shrink:0;">
                            <span class="preview-btn-admin" style="background: <?= esc($s['theme_admin_primary'] ?? '#B71C1C') ?>; border: 1px solid rgba(255,255,255,0.3); color: #ffffff; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700;">
                                Admin
                            </span>
                        </div>
                    </div>
                </div>

                <div class="preview-card">
                    <div class="preview-card-label">
                        <span><i class="fas fa-headset"></i> Dispatcher / Staff Header</span>
                        <small>Terminal Dispatch Portal</small>
                    </div>
                    <div class="preview-header-bar" id="previewStaff" style="background: <?= esc($s['theme_staff_nav_bg'] ?? '#15803d') ?>; color: <?= esc($s['theme_staff_nav_text'] ?? '#ffffff') ?>;">
                        <div style="display:flex; align-items:center; gap:10px; min-width:0;">
                            <img src="<?= esc($logoUrl) ?>" alt="Logo" class="previewLogoImg">
                            <div class="preview-brand">
                                <h4 class="previewNameEl"><?= esc($s['app_name'] ?? 'Palompon Transit') ?></h4>
                                <p class="previewSubEl"><?= esc($s['app_subtitle'] ?? 'Terminal Monitor') ?></p>
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px; margin-left:auto; flex-shrink:0;">
                            <span class="preview-btn-staff" style="background: <?= esc($s['theme_staff_primary'] ?? '#15803d') ?>; border: 1px solid rgba(255,255,255,0.3); color: #ffffff; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700;">
                                Staff
                            </span>
                        </div>
                    </div>
                </div>

                <div class="preview-card">
                    <div class="preview-card-label">
                        <span><i class="fas fa-file-signature"></i> Official Title & Short Code</span>
                        <small>Reports & Documents</small>
                    </div>
                    <div style="padding: 14px 18px; background: #f8fafc; font-size: 13px;">
                        <div style="font-weight: 700; color: var(--sb-text-main);" id="previewSysTitleCard"><?= esc($s['system_title'] ?? 'Palompon Transit Terminal Management System') ?></div>
                        <div style="color: var(--sb-text-muted); font-size: 11.5px; margin-top: 3px;">Short Acronym: <span style="font-weight: 700; color: var(--sb-primary);" id="previewAcronymCard"><?= esc($s['acronym'] ?? 'PTTM') ?></span></div>
                    </div>
                </div>
            </div>

            <!-- Identity Form -->
            <form method="post" action="<?= base_url('admin/settings/update-branding') ?>" autocomplete="off" id="formIdentity">
                <?= csrf_field() ?>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Transit / System Name <span class="label-hint">(Shown in Navbar)</span></label>
                        <input type="text" name="app_name" class="form-input" id="inputAppName" value="<?= esc($s['app_name'] ?? 'Palompon Transit') ?>" required maxlength="80" placeholder="e.g. Palompon Transit">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subtitle / Tagline <span class="label-hint">(Below name)</span></label>
                        <input type="text" name="app_subtitle" class="form-input" id="inputAppSubtitle" value="<?= esc($s['app_subtitle'] ?? 'Terminal Monitor') ?>" maxlength="60" placeholder="e.g. Terminal Monitor">
                    </div>
                    <div class="form-group">
                        <label class="form-label">System Acronym <span class="label-hint">(Short code)</span></label>
                        <input type="text" name="acronym" class="form-input" id="inputAcronym" value="<?= esc($s['acronym'] ?? 'PTTM') ?>" maxlength="20" placeholder="e.g. PTTM">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Full Official Title <span class="label-hint">(Printed Reports & Documentation)</span></label>
                        <input type="text" name="system_title" class="form-input" id="inputSystemTitle" value="<?= esc($s['system_title'] ?? 'Palompon Transit Terminal Management System') ?>" maxlength="150" placeholder="e.g. Palompon Transit Terminal Management System">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Terminal Contact Phone</label>
                        <input type="text" name="contact_phone" class="form-input" value="<?= esc($s['contact_phone'] ?? '') ?>" maxlength="80" placeholder="e.g. (053) 555-8376 / 338-2022">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Terminal Contact Email</label>
                        <input type="email" name="contact_email" class="form-input" value="<?= esc($s['contact_email'] ?? '') ?>" maxlength="120" placeholder="e.g. terminal@palompon.gov.ph">
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">Terminal Physical Address</label>
                        <input type="text" name="contact_address" class="form-input" value="<?= esc($s['contact_address'] ?? '') ?>" maxlength="200" placeholder="e.g. Palompon Transit Terminal, Rizal St., Palompon, Leyte 6538">
                    </div>
                </div>
                <div class="form-action-row">
                    <button type="submit" class="btn-save"><i class="fas fa-save"></i> Save System Identity</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ====================================================================
         TAB 2: MEDIA (LOGO & BACKGROUND PICTURES)
         ==================================================================== -->
    <div class="tab-content" id="tab-media">
        <!-- 1. System Logo Card -->
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="card-icon"><i class="fas fa-stamp"></i></div>
                <div>
                    <h3>Official System Logo</h3>
                    <p>Upload a custom high-resolution emblem or municipal seal to replace the default seal everywhere across navigation bars, logins, and print manifests.</p>
                </div>
            </div>

            <div class="upload-zone" id="logoUploadZone">
                <input type="file" id="logoFileInput" accept="image/png,image/jpeg,image/jpg,image/webp,image/gif,image/svg+xml" style="display:none;">
                <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                <div class="upload-text"><strong>Click to upload</strong> or drag and drop official seal</div>
                <div class="upload-hint">PNG, JPG, WEBP, GIF, or SVG &bull; Maximum 5 MB &bull; Transparent PNG recommended</div>
            </div>

            <div class="media-preview-card" id="logoPreviewCard">
                <img src="<?= esc($logoUrl) ?>" alt="Current Logo" id="logoPreviewImg">
                <div class="media-preview-info">
                    <div class="filename" id="logoFilename"><?= $hasCustomLogo ? basename($s['app_logo']) : 'Default Seal (9HFScgVg_400x400.png)' ?></div>
                    <div class="filemeta">
                        <span class="status-chip <?= $hasCustomLogo ? 'custom' : 'default' ?>" id="logoStatusChip">
                            <i class="fas <?= $hasCustomLogo ? 'fa-check-circle' : 'fa-circle-info' ?>"></i>
                            <?= $hasCustomLogo ? 'Custom Upload Active' : 'System Default Seal' ?>
                        </span>
                        <span>Dimensions: Auto-fit (40x40 preview)</span>
                    </div>
                </div>
                <button type="button" class="btn-reset-media" id="btnResetLogo" style="<?= $hasCustomLogo ? '' : 'display:none;' ?>">
                    <i class="fas fa-undo"></i> Reset to Default Seal
                </button>
            </div>
        </div>

        <!-- 2. Background Pictures Manager -->
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="card-icon"><i class="fas fa-panorama"></i></div>
                <div>
                    <h3>Authentication & Login Background Pictures</h3>
                    <p>Choose between a dynamic 5-photo rotating crossfade slideshow or a single static hero image for all staff and dispatcher sign-in portals.</p>
                </div>
            </div>

            <!-- Background Mode Selector -->
            <div class="bg-mode-selector">
                <label class="bg-mode-card <?= $bgMode === 'slideshow' ? 'active' : '' ?>" id="modeCardSlideshow">
                    <input type="radio" name="bg_display_mode" value="slideshow" <?= $bgMode === 'slideshow' ? 'checked' : '' ?>>
                    <div class="bg-mode-info">
                        <h4><i class="fas fa-images" style="color:#f59e0b; margin-right:6px;"></i> Dynamic Slideshow Mode (5 Rotating Photos)</h4>
                        <p>Rotates through 5 landmark photos of Palompon terminal and town with smooth 30-second crossfade transitions on login screens. (Recommended)</p>
                    </div>
                </label>
                <label class="bg-mode-card <?= $bgMode === 'single' ? 'active' : '' ?>" id="modeCardSingle">
                    <input type="radio" name="bg_display_mode" value="single" <?= $bgMode === 'single' ? 'checked' : '' ?>>
                    <div class="bg-mode-info">
                        <h4><i class="fas fa-image" style="color:#3b82f6; margin-right:6px;"></i> Single Hero Background Mode</h4>
                        <p>Shows one static high-resolution artwork or custom terminal hero photo behind sign-in screens.</p>
                    </div>
                </label>
            </div>

            <!-- SLIDESHOW MANAGER SECTION -->
            <div id="sectionSlideshowManager" style="<?= $bgMode === 'slideshow' ? '' : 'display:none;' ?>">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; flex-wrap:wrap; gap:10px;">
                    <div>
                        <h4 style="font-size:15px; font-weight:700; margin:0; color:var(--sb-text-main);"><i class="fas fa-film" style="color:var(--sb-primary);"></i> Dynamic Slideshow Gallery (5+ Rotating Photos)</h4>
                        <span style="font-size:12px; color:var(--sb-text-muted);">Click "Change Photo" on any slot, "+ Add Slideshow Photo" to add more than 5 photos, or reset to defaults.</span>
                    </div>
                    <button type="button" class="btn-reset-media" id="btnResetAllSlideshow">
                        <i class="fas fa-rotate-left"></i> Reset All 5 to System Defaults
                    </button>
                </div>

                <div class="slideshow-grid" id="slideshowGrid">
                    <?php
                    $allSlotKeys = array_unique(array_merge([1, 2, 3, 4, 5], array_keys($slideshowUrls)));
                    sort($allSlotKeys, SORT_NUMERIC);
                    ?>
                    <?php foreach ($allSlotKeys as $i): ?>
                        <?php
                        $meta     = $slotsMeta[$i] ?? [
                            'name'        => 'Custom Slot ' . $i,
                            'desc'        => 'Additional rotating background photo for sign-in screens',
                            'defaultFile' => '',
                            'isCustom'    => true,
                        ];
                        $slotUrl  = $slideshowUrls[$i] ?? (!empty($meta['defaultFile']) ? base_url('images/bg/' . $meta['defaultFile']) : '');
                        $isCustom = ($i > 5) || !empty($meta['isCustom']);
                        ?>
                        <div class="slideshow-slot-card" id="slotCard-<?= $i ?>" data-slot="<?= $i ?>">
                            <div class="slot-card-image-wrap">
                                <img src="<?= esc($slotUrl) ?>" alt="Slot <?= $i ?>" id="slotImg-<?= $i ?>">
                                <span class="slot-number-badge">Slot <?= $i ?></span>
                                <span class="slot-status-badge <?= $isCustom ? 'custom' : 'default' ?>" id="slotBadge-<?= $i ?>">
                                    <?= $isCustom ? 'Custom' : 'Default' ?>
                                </span>
                            </div>
                            <div class="slot-card-body">
                                <h5><?= esc($meta['name']) ?></h5>
                                <p><?= esc($meta['desc']) ?></p>
                                <div class="slot-card-actions">
                                    <input type="file" id="slotFileInput-<?= $i ?>" accept="image/png,image/jpeg,image/jpg,image/webp" style="display:none;" data-slot="<?= $i ?>">
                                    <button type="button" class="btn-slot-upload" onclick="document.getElementById('slotFileInput-<?= $i ?>').click();" title="Upload new photo for Slot <?= $i ?>">
                                        <i class="fas fa-upload"></i> Change Photo
                                    </button>
                                    <?php if ($i <= 5): ?>
                                    <button type="button" class="btn-slot-reset" id="btnResetSlot-<?= $i ?>" onclick="resetSlotPhoto(<?= $i ?>);" title="Reset to default artwork" style="<?= $isCustom ? '' : 'display:none;' ?>">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                    <?php else: ?>
                                    <button type="button" class="btn-slot-reset text-danger" id="btnDeleteSlot-<?= $i ?>" onclick="deleteSlotPhoto(<?= $i ?>);" title="Delete custom slot <?= $i ?>" style="border-color:#fca5a5; color:#dc2626; background:#fef2f2;">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Dedicated Card to Add Slot 6+ Photos -->
                    <div class="slideshow-slot-card add-slot-card" id="addSlotCard" onclick="document.getElementById('newSlotFileInput').click();" style="border: 2px dashed #cbd5e1; display:flex; flex-direction:column; align-items:center; justify-content:center; cursor:pointer; min-height:240px; background:#f8fafc; border-radius:12px; transition:all 0.2s ease;" onmouseover="this.style.borderColor='#b71c1c'; this.style.background='#fff5f5';" onmouseout="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';">
                        <input type="file" id="newSlotFileInput" accept="image/png,image/jpeg,image/jpg,image/webp" style="display:none;" onchange="handleAddNewSlot(this);">
                        <div style="text-align:center; padding:20px;">
                            <div style="width:52px; height:52px; border-radius:50%; background:#fee2e2; color:#b71c1c; display:flex; align-items:center; justify-content:center; margin:0 auto 12px; font-size:22px; box-shadow:0 2px 8px rgba(183,28,28,0.15);">
                                <i class="fas fa-plus"></i>
                            </div>
                            <h5 style="margin:0 0 6px 0; font-size:15px; font-weight:700; color:#0f172a;">Add Slideshow Photo</h5>
                            <p style="margin:0; font-size:12px; color:#64748b; line-height:1.4;">Upload a 6th+ photo to rotate in login slideshow</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SINGLE HERO BACKGROUND SECTION -->
            <div id="sectionSingleBgManager" style="<?= $bgMode === 'single' ? '' : 'display:none;' ?>">
                <div class="upload-zone" id="bgUploadZone">
                    <input type="file" id="bgFileInput" accept="image/png,image/jpeg,image/jpg,image/webp" style="display:none;">
                    <div class="upload-icon"><i class="fas fa-mountain-sun"></i></div>
                    <div class="upload-text"><strong>Click to upload</strong> or drag and drop single hero background</div>
                    <div class="upload-hint">PNG, JPG, or WEBP &bull; Max 8 MB &bull; High resolution landscape recommended</div>
                </div>

                <div class="media-preview-card bg-type" id="singleBgPreviewCard">
                    <img src="<?= esc($bgUrl) ?>" alt="Single Background" id="bgPreviewImg">
                    <div class="media-preview-info">
                        <div class="filename" id="bgFilename"><?= $hasCustomBg ? basename($s['app_background_image']) : 'Default Artwork (system bg image.png)' ?></div>
                        <div class="filemeta">
                            <span class="status-chip <?= $hasCustomBg ? 'custom' : 'default' ?>" id="bgStatusChip">
                                <i class="fas <?= $hasCustomBg ? 'fa-check-circle' : 'fa-circle-info' ?>"></i>
                                <?= $hasCustomBg ? 'Custom Artwork Active' : 'System Default Artwork' ?>
                            </span>
                            <span>Aspect: 16:9 Full Screen Cover</span>
                        </div>
                    </div>
                    <button type="button" class="btn-reset-media" id="btnResetBg" style="<?= $hasCustomBg ? '' : 'display:none;' ?>">
                        <i class="fas fa-undo"></i> Reset to Default
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ====================================================================
         TAB 3: ROLE THEME TEMPLATES
         ==================================================================== -->
    <div class="tab-content" id="tab-themes">
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="card-icon"><i class="fas fa-swatchbook"></i></div>
                <div>
                    <h3>Role Themes & Navigation Palettes</h3>
                    <p>Adjust primary brand colors and top navigation bar backgrounds for each role. Changes update the live preview headers below in real time.</p>
                </div>
            </div>

            <!-- Preset Palettes Bar -->
            <div class="theme-presets-bar">
                <span class="preset-title"><i class="fas fa-wand-magic-sparkles" style="color:var(--sb-primary); margin-right:4px;"></i> Quick Preset Themes:</span>
                <button type="button" class="btn-preset" onclick="applyThemePreset('#C62828','#ffffff','#1c2430','#15803d','#15803d','#ffffff','#B71C1C','#B71C1C','#ffffff')">
                    <span class="palette-dots"><span class="p-dot" style="background:#C62828"></span><span class="p-dot" style="background:#15803d"></span><span class="p-dot" style="background:#B71C1C"></span></span>
                    Palompon Standard
                </button>
                <button type="button" class="btn-preset" onclick="applyThemePreset('#0284c7','#0f172a','#f8fafc','#059669','#0f172a','#f8fafc','#dc2626','#0f172a','#f8fafc')">
                    <span class="palette-dots"><span class="p-dot" style="background:#0284c7"></span><span class="p-dot" style="background:#059669"></span><span class="p-dot" style="background:#dc2626"></span></span>
                    Executive Dark Nav
                </button>
                <button type="button" class="btn-preset" onclick="applyThemePreset('#2563eb','#ffffff','#1e293b','#0d9488','#ffffff','#1e293b','#e11d48','#ffffff','#1e293b')">
                    <span class="palette-dots"><span class="p-dot" style="background:#2563eb"></span><span class="p-dot" style="background:#0d9488"></span><span class="p-dot" style="background:#e11d48"></span></span>
                    Clean Modern White
                </button>
            </div>

            <!-- Synchronized Live Preview on Tab 3 -->
            <div class="live-preview-grid" style="margin-bottom:24px;">
                <div class="preview-card">
                    <div class="preview-card-label">Guest Portal Header Preview</div>
                    <div class="preview-header-bar" id="themePreviewGuest" style="background: <?= esc($s['theme_guest_nav_bg'] ?? '#ffffff') ?>; color: <?= esc($s['theme_guest_nav_text'] ?? '#1c2430') ?>;">
                        <div style="display:flex; align-items:center; gap:10px; min-width:0;">
                            <img src="<?= esc($logoUrl) ?>" alt="Logo" class="previewLogoImg">
                            <div class="preview-brand">
                                <h4 class="previewNameEl"><?= esc($s['app_name'] ?? 'Palompon Transit') ?></h4>
                                <p class="previewSubEl"><?= esc($s['app_subtitle'] ?? 'Terminal Monitor') ?></p>
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px; margin-left:auto; flex-shrink:0;">
                            <span class="preview-nav-link" style="font-size:11.5px; font-weight:600; opacity:0.85;">Schedules</span>
                            <span class="preview-btn-guest" style="background: <?= esc($s['theme_guest_primary'] ?? '#C62828') ?>; color: #ffffff; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                Login <i class="fas fa-arrow-right" style="font-size:9px;"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="preview-card">
                    <div class="preview-card-label">Admin Header Preview</div>
                    <div class="preview-header-bar" id="themePreviewAdmin" style="background: <?= esc($s['theme_admin_nav_bg'] ?? '#B71C1C') ?>; color: <?= esc($s['theme_admin_nav_text'] ?? '#ffffff') ?>;">
                        <div style="display:flex; align-items:center; gap:10px; min-width:0;">
                            <img src="<?= esc($logoUrl) ?>" alt="Logo" class="previewLogoImg">
                            <div class="preview-brand">
                                <h4 class="previewNameEl"><?= esc($s['app_name'] ?? 'Palompon Transit') ?></h4>
                                <p class="previewSubEl"><?= esc($s['app_subtitle'] ?? 'Terminal Monitor') ?></p>
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px; margin-left:auto; flex-shrink:0;">
                            <span class="preview-btn-admin" style="background: <?= esc($s['theme_admin_primary'] ?? '#B71C1C') ?>; border: 1px solid rgba(255,255,255,0.3); color: #ffffff; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700;">
                                Admin
                            </span>
                        </div>
                    </div>
                </div>
                <div class="preview-card" style="grid-column: 1 / -1;">
                    <div class="preview-card-label">Dispatcher / Staff Header Preview</div>
                    <div class="preview-header-bar" id="themePreviewStaff" style="background: <?= esc($s['theme_staff_nav_bg'] ?? '#15803d') ?>; color: <?= esc($s['theme_staff_nav_text'] ?? '#ffffff') ?>;">
                        <div style="display:flex; align-items:center; gap:10px; min-width:0;">
                            <img src="<?= esc($logoUrl) ?>" alt="Logo" class="previewLogoImg">
                            <div class="preview-brand">
                                <h4 class="previewNameEl"><?= esc($s['app_name'] ?? 'Palompon Transit') ?></h4>
                                <p class="previewSubEl"><?= esc($s['app_subtitle'] ?? 'Terminal Monitor') ?></p>
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px; margin-left:auto; flex-shrink:0;">
                            <span class="preview-btn-staff" style="background: <?= esc($s['theme_staff_primary'] ?? '#15803d') ?>; border: 1px solid rgba(255,255,255,0.3); color: #ffffff; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700;">
                                Staff
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <form method="post" action="<?= base_url('admin/settings/update-themes') ?>" autocomplete="off" id="formThemes">
                <?= csrf_field() ?>

                <!-- Guest Portal Theme -->
                <div class="theme-section">
                    <div class="theme-section-header">
                        <span class="theme-dot" id="dotGuest" style="background: <?= esc($s['theme_guest_primary'] ?? '#C62828') ?>;"></span>
                        <h4>Guest / Commuter Public Portal</h4>
                    </div>
                    <div class="color-grid">
                        <div class="color-field">
                            <label>Primary Brand Accent</label>
                            <div class="color-picker-wrap">
                                <input type="color" name="theme_guest_primary" id="picker_guest_primary" value="<?= esc($s['theme_guest_primary'] ?? '#C62828') ?>" data-sync="text_guest_primary" data-preview="Guest" data-prop="primary">
                                <input type="text" id="text_guest_primary" value="<?= esc($s['theme_guest_primary'] ?? '#C62828') ?>" data-sync-color="picker_guest_primary" maxlength="7">
                            </div>
                        </div>
                        <div class="color-field">
                            <label>Navbar Background</label>
                            <div class="color-picker-wrap">
                                <input type="color" name="theme_guest_nav_bg" id="picker_guest_nav_bg" value="<?= esc($s['theme_guest_nav_bg'] ?? '#ffffff') ?>" data-sync="text_guest_nav_bg" data-preview="Guest" data-prop="bg">
                                <input type="text" id="text_guest_nav_bg" value="<?= esc($s['theme_guest_nav_bg'] ?? '#ffffff') ?>" data-sync-color="picker_guest_nav_bg" maxlength="7">
                            </div>
                        </div>
                        <div class="color-field">
                            <label>Navbar Text & Links</label>
                            <div class="color-picker-wrap">
                                <input type="color" name="theme_guest_nav_text" id="picker_guest_nav_text" value="<?= esc($s['theme_guest_nav_text'] ?? '#1c2430') ?>" data-sync="text_guest_nav_text" data-preview="Guest" data-prop="text">
                                <input type="text" id="text_guest_nav_text" value="<?= esc($s['theme_guest_nav_text'] ?? '#1c2430') ?>" data-sync-color="picker_guest_nav_text" maxlength="7">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin Theme -->
                <div class="theme-section">
                    <div class="theme-section-header">
                        <span class="theme-dot" id="dotAdmin" style="background: <?= esc($s['theme_admin_primary'] ?? '#B71C1C') ?>;"></span>
                        <h4>Admin & Super Admin Portal</h4>
                    </div>
                    <div class="color-grid">
                        <div class="color-field">
                            <label>Primary Brand Accent</label>
                            <div class="color-picker-wrap">
                                <input type="color" name="theme_admin_primary" id="picker_admin_primary" value="<?= esc($s['theme_admin_primary'] ?? '#B71C1C') ?>" data-sync="text_admin_primary" data-preview="Admin" data-prop="primary">
                                <input type="text" id="text_admin_primary" value="<?= esc($s['theme_admin_primary'] ?? '#B71C1C') ?>" data-sync-color="picker_admin_primary" maxlength="7">
                            </div>
                        </div>
                        <div class="color-field">
                            <label>Navbar Background</label>
                            <div class="color-picker-wrap">
                                <input type="color" name="theme_admin_nav_bg" id="picker_admin_nav_bg" value="<?= esc($s['theme_admin_nav_bg'] ?? '#B71C1C') ?>" data-sync="text_admin_nav_bg" data-preview="Admin" data-prop="bg">
                                <input type="text" id="text_admin_nav_bg" value="<?= esc($s['theme_admin_nav_bg'] ?? '#B71C1C') ?>" data-sync-color="picker_admin_nav_bg" maxlength="7">
                            </div>
                        </div>
                        <div class="color-field">
                            <label>Navbar Text & Links</label>
                            <div class="color-picker-wrap">
                                <input type="color" name="theme_admin_nav_text" id="picker_admin_nav_text" value="<?= esc($s['theme_admin_nav_text'] ?? '#ffffff') ?>" data-sync="text_admin_nav_text" data-preview="Admin" data-prop="text">
                                <input type="text" id="text_admin_nav_text" value="<?= esc($s['theme_admin_nav_text'] ?? '#ffffff') ?>" data-sync-color="picker_admin_nav_text" maxlength="7">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Staff Theme -->
                <div class="theme-section">
                    <div class="theme-section-header">
                        <span class="theme-dot" id="dotStaff" style="background: <?= esc($s['theme_staff_primary'] ?? '#15803d') ?>;"></span>
                        <h4>Dispatcher & Staff Operations Portal</h4>
                    </div>
                    <div class="color-grid">
                        <div class="color-field">
                            <label>Primary Brand Accent</label>
                            <div class="color-picker-wrap">
                                <input type="color" name="theme_staff_primary" id="picker_staff_primary" value="<?= esc($s['theme_staff_primary'] ?? '#15803d') ?>" data-sync="text_staff_primary" data-preview="Staff" data-prop="primary">
                                <input type="text" id="text_staff_primary" value="<?= esc($s['theme_staff_primary'] ?? '#15803d') ?>" data-sync-color="picker_staff_primary" maxlength="7">
                            </div>
                        </div>
                        <div class="color-field">
                            <label>Navbar Background</label>
                            <div class="color-picker-wrap">
                                <input type="color" name="theme_staff_nav_bg" id="picker_staff_nav_bg" value="<?= esc($s['theme_staff_nav_bg'] ?? '#15803d') ?>" data-sync="text_staff_nav_bg" data-preview="Staff" data-prop="bg">
                                <input type="text" id="text_staff_nav_bg" value="<?= esc($s['theme_staff_nav_bg'] ?? '#15803d') ?>" data-sync-color="picker_staff_nav_bg" maxlength="7">
                            </div>
                        </div>
                        <div class="color-field">
                            <label>Navbar Text & Links</label>
                            <div class="color-picker-wrap">
                                <input type="color" name="theme_staff_nav_text" id="picker_staff_nav_text" value="<?= esc($s['theme_staff_nav_text'] ?? '#ffffff') ?>" data-sync="text_staff_nav_text" data-preview="Staff" data-prop="text">
                                <input type="text" id="text_staff_nav_text" value="<?= esc($s['theme_staff_nav_text'] ?? '#ffffff') ?>" data-sync-color="picker_staff_nav_text" maxlength="7">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-action-row">
                    <button type="submit" class="btn-save"><i class="fas fa-save"></i> Save Theme Colours</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ====================================================================
         TAB 4: FOOTER & PUBLIC ATTRIBUTION (MATCHING USER SCREENSHOTS)
         ==================================================================== -->
    <div class="tab-content" id="tab-footer">
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="card-icon"><i class="fas fa-layer-group"></i></div>
                <div>
                    <h3>Footer Information & Legal Attribution</h3>
                    <p>Customize the footer headline, description text, managing municipality credit, and official copyright line as shown across all public portal footers.</p>
                </div>
            </div>

            <!-- Public Footer Exact Live Preview (Matches Screenshot 1 & Screenshot 2) -->
            <div style="margin-bottom: 24px;">
                <div style="font-size:12px; font-weight:700; color:var(--sb-text-muted); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">
                    <i class="fas fa-eye" style="color:#f97316;"></i> Public Portal Footer Live Preview (Real-time View)
                </div>
                <div class="footer-preview-container">
                    <div class="footer-preview-about">
                        <h4 id="previewFooterHeading">
                            <span id="previewFooterHeadingText"><?= esc($s['footer_about_title'] ?? 'PTTM System') ?></span>
                        </h4>
                        <p id="previewFooterBodyText"><?= esc($s['footer_about_text'] ?? 'Palompon Transit Terminal Management System provides real-time tracking of vehicle queues and departure schedules to ensure efficient travel for every passenger.') ?></p>
                    </div>
                    <div class="footer-preview-divider"></div>
                    <div class="footer-preview-copyright" id="previewFooterCopyrightText">
                        <?= app_footer_copyright() ?>
                    </div>
                </div>
            </div>

            <!-- Footer Form -->
            <form method="post" action="<?= base_url('admin/settings/update-footer') ?>" autocomplete="off" id="formFooter">
                <?= csrf_field() ?>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">
                            <span>Footer Headline Title <strong style="color:#f97316;">(Picture 1 Heading)</strong></span>
                            <span class="label-hint">e.g. PTTM System</span>
                        </label>
                        <input type="text" name="footer_about_title" class="form-input" id="inputFooterTitle" value="<?= esc($s['footer_about_title'] ?? 'PTTM System') ?>" maxlength="60" placeholder="e.g. PTTM System" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <span>Municipality / Agency Credit <span class="label-hint">(Picture 2 Credit)</span></span>
                        </label>
                        <input type="text" name="footer_credit" class="form-input" id="inputFooterCredit" value="<?= esc($s['footer_credit'] ?? 'Municipality of Palompon, Leyte') ?>" maxlength="120" placeholder="e.g. Municipality of Palompon, Leyte" required>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">
                            <span>Footer Description Text <strong style="color:var(--sb-primary);">(Picture 1 Description)</strong></span>
                            <span class="label-hint">Summarizes terminal service to the public</span>
                        </label>
                        <textarea name="footer_about_text" class="form-input form-textarea" id="inputFooterText" maxlength="500" placeholder="Describe your transit system for the public footer." required><?= esc($s['footer_about_text'] ?? 'Palompon Transit Terminal Management System provides real-time tracking of vehicle queues and departure schedules to ensure efficient travel for every passenger.') ?></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">
                            <span>Official Copyright Notice <strong style="color:var(--sb-primary);">(Picture 2 Copyright Bar)</strong></span>
                            <span class="label-hint">Full customizable format</span>
                        </label>
                        <input type="text" name="footer_copyright_text" class="form-input" id="inputFooterCopyright" value="<?= esc($s['footer_copyright_text'] ?? '© {year} {title} ({acronym}). All rights reserved. | {credit}') ?>" maxlength="250" placeholder="e.g. © {year} {title} ({acronym}). All rights reserved. | {credit}">
                        <div class="tag-chips-bar">
                            <span class="chip-label"><i class="fas fa-magic"></i> Click tags to insert:</span>
                            <button type="button" class="tag-chip" onclick="insertTag('{year}')">{year}</button>
                            <button type="button" class="tag-chip" onclick="insertTag('{title}')">{title}</button>
                            <button type="button" class="tag-chip" onclick="insertTag('{acronym}')">{acronym}</button>
                            <button type="button" class="tag-chip" onclick="insertTag('{credit}')">{credit}</button>
                        </div>
                    </div>
                </div>

                <div class="form-action-row">
                    <button type="submit" class="btn-save"><i class="fas fa-save"></i> Save Footer & Attribution</button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- Floating Toast Container -->
<div id="sbToast" class="sb-toast">
    <i class="fas fa-check-circle" id="sbToastIcon"></i>
    <span id="sbToastMsg">Settings updated.</span>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // =========================================================================
    // 1. Tab Switching & Persistent URL Hash / LocalStorage
    // =========================================================================
    var tabs = document.querySelectorAll('.settings-tab');
    var contents = document.querySelectorAll('.tab-content');

    function switchTab(tabId, pushHash) {
        if (!tabId) tabId = 'identity';
        var activeBtn = document.getElementById('tabBtn-' + tabId);
        var activeContent = document.getElementById('tab-' + tabId);
        if (!activeBtn || !activeContent) return;

        tabs.forEach(function(t) { t.classList.remove('active'); });
        contents.forEach(function(c) { c.classList.remove('active'); });

        activeBtn.classList.add('active');
        activeContent.classList.add('active');

        localStorage.setItem('sb_active_tab', tabId);
        if (pushHash) {
            history.replaceState(null, null, '#' + tabId);
        }
    }

    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            switchTab(tab.dataset.tab, true);
        });
    });

    // Check hash on page load
    var initialHash = (window.location.hash || '').replace('#', '');
    var savedTab = localStorage.getItem('sb_active_tab');
    if (initialHash && document.getElementById('tab-' + initialHash)) {
        switchTab(initialHash, false);
    } else if (savedTab && document.getElementById('tab-' + savedTab)) {
        switchTab(savedTab, true);
    }

    // =========================================================================
    // 2. CSRF Token Manager (Robust AJAX Sync)
    // =========================================================================
    var currentCsrfToken = '<?= csrf_token() ?>';
    var currentCsrfHash  = '<?= csrf_hash() ?>';

    function updateCsrf(newToken, newHash) {
        if (!newHash) return;
        currentCsrfHash = newHash;
        if (newToken) currentCsrfToken = newToken;

        // Update all CSRF inputs in all forms
        document.querySelectorAll('input[name="' + currentCsrfToken + '"]').forEach(function(el) {
            el.value = newHash;
        });

        // Update meta tag if exists
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) meta.content = newHash;
    }

    function showToast(msg, isSuccess) {
        var toast = document.getElementById('sbToast');
        var toastMsg = document.getElementById('sbToastMsg');
        var toastIcon = document.getElementById('sbToastIcon');
        if (!toast) return;

        toast.className = 'sb-toast ' + (isSuccess ? 'success' : 'error') + ' show';
        if (toastMsg) toastMsg.textContent = msg;
        if (toastIcon) toastIcon.className = isSuccess ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';

        clearTimeout(toast._timeout);
        toast._timeout = setTimeout(function() {
            toast.classList.remove('show');
        }, 3800);
    }

    // =========================================================================
    // 3. Live Preview Synchronizers (Identity & Footer)
    // =========================================================================
    var inputName    = document.getElementById('inputAppName');
    var inputSub     = document.getElementById('inputAppSubtitle');
    var inputAcro    = document.getElementById('inputAcronym');
    var inputTitle   = document.getElementById('inputSystemTitle');
    var inputFTile   = document.getElementById('inputFooterTitle');
    var inputFText   = document.getElementById('inputFooterText');
    var inputFCredit = document.getElementById('inputFooterCredit');
    var inputFCopy   = document.getElementById('inputFooterCopyright');

    function syncIdentityPreviews() {
        var name = inputName ? inputName.value.trim() : 'Palompon Transit';
        var sub  = inputSub ? inputSub.value.trim() : 'Terminal Monitor';
        var acro = inputAcro ? inputAcro.value.trim() : 'PTTM';
        var titl = inputTitle ? inputTitle.value.trim() : 'Palompon Transit Terminal Management System';

        document.querySelectorAll('.previewNameEl').forEach(function(el) { el.textContent = name; });
        document.querySelectorAll('.previewSubEl').forEach(function(el) { el.textContent = sub; });

        // Update active page header and drawer in real-time
        document.querySelectorAll('#site-header .logo-text h1, .site-header .logo-text h1, .drawer-brand-title, .app-brand-name').forEach(function(el) {
            el.textContent = name;
        });
        document.querySelectorAll('#site-header .logo-text p, .site-header .logo-text p, .drawer-brand-subtitle, .app-system-title').forEach(function(el) {
            el.textContent = sub;
        });

        var cardTitle = document.getElementById('previewSysTitleCard');
        var cardAcro  = document.getElementById('previewAcronymCard');
        if (cardTitle) cardTitle.textContent = titl;
        if (cardAcro) cardAcro.textContent = acro;

        syncFooterPreview();
    }

    function syncFooterPreview() {
        var fTitle  = inputFTile ? inputFTile.value : 'PTTM System';
        var fText   = inputFText ? inputFText.value : '';
        var fCredit = inputFCredit ? inputFCredit.value : 'Municipality of Palompon, Leyte';
        var fCopy   = inputFCopy ? inputFCopy.value : '© {year} {title} ({acronym}). All rights reserved. | {credit}';
        var titl    = inputTitle ? inputTitle.value.trim() : 'Palompon Transit Terminal Management System';
        var acro    = inputAcro ? inputAcro.value.trim() : 'PTTM';
        var year    = (new Date()).getFullYear();

        var headingEl = document.getElementById('previewFooterHeadingText');
        var bodyEl    = document.getElementById('previewFooterBodyText');
        var copyEl    = document.getElementById('previewFooterCopyrightText');

        if (headingEl) headingEl.textContent = fTitle;
        if (bodyEl) bodyEl.textContent = fText;

        if (copyEl) {
            var renderedCopy = fCopy
                .replace(/{year}/g, year)
                .replace(/{title}/g, titl)
                .replace(/{acronym}/g, acro)
                .replace(/{credit}/g, fCredit);
            copyEl.textContent = renderedCopy;
        }
    }

    [inputName, inputSub, inputAcro, inputTitle].forEach(function(el) {
        if (el) el.addEventListener('input', syncIdentityPreviews);
    });

    [inputFTile, inputFText, inputFCredit, inputFCopy].forEach(function(el) {
        if (el) el.addEventListener('input', syncFooterPreview);
    });

    // Tag chip helper for copyright input
    window.insertTag = function(tag) {
        if (!inputFCopy) return;
        var start = inputFCopy.selectionStart;
        var end = inputFCopy.selectionEnd;
        var val = inputFCopy.value;
        inputFCopy.value = val.substring(0, start) + tag + val.substring(end);
        inputFCopy.focus();
        inputFCopy.selectionStart = inputFCopy.selectionEnd = start + tag.length;
        syncFooterPreview();
    };

    // =========================================================================
    // 4. Role Theme Palettes Sync & Live Color Pickers
    // =========================================================================
    document.querySelectorAll('input[type="color"][data-sync]').forEach(function(picker) {
        var textEl = document.getElementById(picker.dataset.sync);
        if (!textEl) return;

        function updateColor(hex) {
            textEl.value = hex.toUpperCase();
            // Check if preview element needs update
            var previewRole = picker.dataset.preview;
            var previewProp = picker.dataset.prop;
            if (previewRole) {
                var previewEls = [
                    document.getElementById('preview' + previewRole),
                    document.getElementById('themePreview' + previewRole)
                ];
                previewEls.forEach(function(el) {
                    if (!el) return;
                    if (previewProp === 'bg') el.style.backgroundColor = hex;
                    if (previewProp === 'text') {
                        el.style.color = hex;
                        el.querySelectorAll('.preview-brand h4, .preview-brand p, .preview-nav-link').forEach(function(child) {
                            child.style.color = hex;
                        });
                    }
                });

                if (previewProp === 'primary') {
                    var dot = document.getElementById('dot' + previewRole);
                    if (dot) dot.style.backgroundColor = hex;
                    document.querySelectorAll('.preview-btn-' + previewRole.toLowerCase()).forEach(function(btn) {
                        btn.style.backgroundColor = hex;
                    });
                    if (previewRole === 'Admin') {
                        document.documentElement.style.setProperty('--primary-color', hex);
                        document.documentElement.style.setProperty('--admin-primary', hex);
                        document.documentElement.style.setProperty('--primary', hex);
                    }
                }
            }
        }

        picker.addEventListener('input', function() { updateColor(picker.value); });
        textEl.addEventListener('input', function() {
            var val = textEl.value.trim();
            if (/^#[0-9a-fA-F]{6}$/.test(val)) {
                picker.value = val;
                updateColor(val);
            }
        });
    });

    // Preset helper function
    window.applyThemePreset = function(gp, gnb, gnt, sp, snb, snt, ap, anb, ant) {
        function setField(pickerId, textId, val) {
            var p = document.getElementById(pickerId);
            var t = document.getElementById(textId);
            if (p) p.value = val;
            if (t) t.value = val.toUpperCase();
            if (p) p.dispatchEvent(new Event('input'));
        }
        setField('picker_guest_primary', 'text_guest_primary', gp);
        setField('picker_guest_nav_bg', 'text_guest_nav_bg', gnb);
        setField('picker_guest_nav_text', 'text_guest_nav_text', gnt);
        setField('picker_staff_primary', 'text_staff_primary', sp);
        setField('picker_staff_nav_bg', 'text_staff_nav_bg', snb);
        setField('picker_staff_nav_text', 'text_staff_nav_text', snt);
        setField('picker_admin_primary', 'text_admin_primary', ap);
        setField('picker_admin_nav_bg', 'text_admin_nav_bg', anb);
        setField('picker_admin_nav_text', 'text_admin_nav_text', ant);
        showToast('Preset applied! Click "Save Theme Colours" to persist.', true);
    };

    // =========================================================================
    // 5. Background Mode Switcher (Slideshow vs Single)
    // =========================================================================
    var modeRadios = document.querySelectorAll('input[name="bg_display_mode"]');
    var secSlideshow = document.getElementById('sectionSlideshowManager');
    var secSingle    = document.getElementById('sectionSingleBgManager');
    var cardSlideshow = document.getElementById('modeCardSlideshow');
    var cardSingle    = document.getElementById('modeCardSingle');

    modeRadios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            var mode = radio.value;
            if (mode === 'slideshow') {
                secSlideshow.style.display = 'block';
                secSingle.style.display    = 'none';
                cardSlideshow.classList.add('active');
                cardSingle.classList.remove('active');
            } else {
                secSlideshow.style.display = 'none';
                secSingle.style.display    = 'block';
                cardSingle.classList.add('active');
                cardSlideshow.classList.remove('active');
            }

            // Send AJAX update to save mode instantly
            var fd = new FormData();
            fd.append('mode', mode);
            fd.append(currentCsrfToken, currentCsrfHash);

            fetch('<?= base_url("admin/settings/update-bg-mode") ?>', { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.csrf_hash) updateCsrf(data.csrf_token, data.csrf_hash);
                    if (data.success) {
                        showToast(data.message || 'Mode updated', true);
                    }
                })
                .catch(function() {
                    showToast('Failed to switch mode.', false);
                });
        });
    });

    // =========================================================================
    // 6. Generic AJAX File Uploader Helper
    // =========================================================================
    function uploadMedia(url, fieldName, file, extraData, onSuccess, onError) {
        var fd = new FormData();
        fd.append(fieldName, file);
        fd.append(currentCsrfToken, currentCsrfHash);
        if (extraData) {
            for (var k in extraData) {
                fd.append(k, extraData[k]);
            }
        }

        fetch(url, { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.csrf_hash) updateCsrf(data.csrf_token, data.csrf_hash);
                if (data.success) {
                    onSuccess(data);
                } else {
                    onError(data.message || 'Upload failed.');
                }
            })
            .catch(function(err) {
                onError('Network communication error.');
            });
    }

    // =========================================================================
    // 7. System Logo Upload & Reset
    // =========================================================================
    var logoZone  = document.getElementById('logoUploadZone');
    var logoInput = document.getElementById('logoFileInput');
    var logoPreviewImg = document.getElementById('logoPreviewImg');
    var logoFilename   = document.getElementById('logoFilename');
    var logoStatusChip = document.getElementById('logoStatusChip');
    var btnResetLogo   = document.getElementById('btnResetLogo');

    if (logoZone && logoInput) {
        logoZone.addEventListener('click', function() { logoInput.click(); });
        logoZone.addEventListener('dragover', function(e) { e.preventDefault(); logoZone.classList.add('drag-over'); });
        logoZone.addEventListener('dragleave', function() { logoZone.classList.remove('drag-over'); });
        logoZone.addEventListener('drop', function(e) {
            e.preventDefault();
            logoZone.classList.remove('drag-over');
            if (e.dataTransfer.files.length > 0) handleLogoFile(e.dataTransfer.files[0]);
        });
        logoInput.addEventListener('change', function() {
            if (logoInput.files.length > 0) handleLogoFile(logoInput.files[0]);
        });
    }

    function handleLogoFile(file) {
        showToast('Uploading system logo...', true);
        uploadMedia('<?= base_url("admin/settings/upload-logo") ?>', 'logo', file, {}, function(data) {
            showToast(data.message || 'Logo updated!', true);
            var newUrl = data.image_url + (data.image_url.indexOf('?') === -1 ? '?t=' : '&t=') + Date.now();
            if (logoPreviewImg) logoPreviewImg.src = newUrl;
            document.querySelectorAll('#site-header img.logo, .site-header img.logo, .drawer-logo, img.previewLogoImg').forEach(function(el) { el.src = newUrl; });
            if (logoFilename) logoFilename.textContent = file.name;
            if (logoStatusChip) {
                logoStatusChip.className = 'status-chip custom';
                logoStatusChip.innerHTML = '<i class="fas fa-check-circle"></i> Custom Upload Active';
            }
            if (btnResetLogo) btnResetLogo.style.display = 'inline-flex';
        }, function(err) {
            showToast(err, false);
        });
    }

    if (btnResetLogo) {
        btnResetLogo.addEventListener('click', function() {
            if (!confirm('Reset logo back to default municipal seal?')) return;
            var fd = new FormData();
            fd.append(currentCsrfToken, currentCsrfHash);
            fetch('<?= base_url("admin/settings/reset-logo") ?>', { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.csrf_hash) updateCsrf(data.csrf_token, data.csrf_hash);
                    if (data.success) {
                        showToast(data.message, true);
                        var newUrl = data.image_url + (data.image_url.indexOf('?') === -1 ? '?t=' : '&t=') + Date.now();
                        if (logoPreviewImg) logoPreviewImg.src = newUrl;
                        document.querySelectorAll('#site-header img.logo, .site-header img.logo, .drawer-logo, img.previewLogoImg').forEach(function(el) { el.src = newUrl; });
                        if (logoFilename) logoFilename.textContent = 'Default Seal (9HFScgVg_400x400.png)';
                        if (logoStatusChip) {
                            logoStatusChip.className = 'status-chip default';
                            logoStatusChip.innerHTML = '<i class="fas fa-circle-info"></i> System Default Seal';
                        }
                        btnResetLogo.style.display = 'none';
                    }
                })
                .catch(function() { showToast('Reset failed.', false); });
        });
    }

    // =========================================================================
    // 8. Single Hero Background Upload & Reset
    // =========================================================================
    var bgZone  = document.getElementById('bgUploadZone');
    var bgInput = document.getElementById('bgFileInput');
    var bgPreviewImg = document.getElementById('bgPreviewImg');
    var bgFilename   = document.getElementById('bgFilename');
    var bgStatusChip = document.getElementById('bgStatusChip');
    var btnResetBg   = document.getElementById('btnResetBg');

    if (bgZone && bgInput) {
        bgZone.addEventListener('click', function() { bgInput.click(); });
        bgZone.addEventListener('dragover', function(e) { e.preventDefault(); bgZone.classList.add('drag-over'); });
        bgZone.addEventListener('dragleave', function() { bgZone.classList.remove('drag-over'); });
        bgZone.addEventListener('drop', function(e) {
            e.preventDefault();
            bgZone.classList.remove('drag-over');
            if (e.dataTransfer.files.length > 0) handleBgFile(e.dataTransfer.files[0]);
        });
        bgInput.addEventListener('change', function() {
            if (bgInput.files.length > 0) handleBgFile(bgInput.files[0]);
        });
    }

    function handleBgFile(file) {
        showToast('Uploading hero background...', true);
        uploadMedia('<?= base_url("admin/settings/upload-background") ?>', 'background', file, {}, function(data) {
            showToast(data.message || 'Background updated!', true);
            if (bgPreviewImg) bgPreviewImg.src = data.image_url;
            if (bgFilename) bgFilename.textContent = file.name;
            if (bgStatusChip) {
                bgStatusChip.className = 'status-chip custom';
                bgStatusChip.innerHTML = '<i class="fas fa-check-circle"></i> Custom Artwork Active';
            }
            if (btnResetBg) btnResetBg.style.display = 'inline-flex';
        }, function(err) {
            showToast(err, false);
        });
    }

    if (btnResetBg) {
        btnResetBg.addEventListener('click', function() {
            if (!confirm('Reset background back to system default artwork?')) return;
            var fd = new FormData();
            fd.append(currentCsrfToken, currentCsrfHash);
            fetch('<?= base_url("admin/settings/reset-background") ?>', { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.csrf_hash) updateCsrf(data.csrf_token, data.csrf_hash);
                    if (data.success) {
                        showToast(data.message, true);
                        if (bgPreviewImg) bgPreviewImg.src = data.image_url;
                        if (bgFilename) bgFilename.textContent = 'Default Artwork (system bg image.png)';
                        if (bgStatusChip) {
                            bgStatusChip.className = 'status-chip default';
                            bgStatusChip.innerHTML = '<i class="fas fa-circle-info"></i> System Default Artwork';
                        }
                        btnResetBg.style.display = 'none';
                    }
                })
                .catch(function() { showToast('Reset failed.', false); });
        });
    }

    // =========================================================================
    // 9. Dynamic Slideshow Slots Upload, Reset, Add & Delete (5+ Photos)
    // =========================================================================
    document.addEventListener('change', function(e) {
        if (e.target && e.target.matches('input[id^="slotFileInput-"]')) {
            var input = e.target;
            var slot = parseInt(input.dataset.slot, 10);
            if (!slot || input.files.length === 0) return;
            var file = input.files[0];
            showToast('Uploading photo for Slot ' + slot + '...', true);

            uploadMedia('<?= base_url("admin/settings/upload-slideshow-slot") ?>', 'image', file, { slot: slot }, function(data) {
                showToast(data.message, true);
                var img = document.getElementById('slotImg-' + slot);
                if (img) img.src = data.image_url;
                var badge = document.getElementById('slotBadge-' + slot);
                if (badge) {
                    badge.className = 'slot-status-badge custom';
                    badge.textContent = 'Custom';
                }
                var resetBtn = document.getElementById('btnResetSlot-' + slot);
                if (resetBtn) resetBtn.style.display = 'inline-flex';
            }, function(err) {
                showToast(err, false);
            });
        }
    });

    window.resetSlotPhoto = function(slot) {
        if (!confirm('Restore Slot ' + slot + ' back to default system photo?')) return;
        var fd = new FormData();
        fd.append('slot', slot);
        fd.append(currentCsrfToken, currentCsrfHash);

        fetch('<?= base_url("admin/settings/reset-slideshow-slot") ?>', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.csrf_hash) updateCsrf(data.csrf_token, data.csrf_hash);
                if (data.success) {
                    showToast(data.message, true);
                    var img = document.getElementById('slotImg-' + slot);
                    if (img) img.src = data.image_url;
                    var badge = document.getElementById('slotBadge-' + slot);
                    if (badge) {
                        badge.className = 'slot-status-badge default';
                        badge.textContent = 'Default';
                    }
                    var resetBtn = document.getElementById('btnResetSlot-' + slot);
                    if (resetBtn) resetBtn.style.display = 'none';
                } else {
                    showToast(data.message || 'Reset failed', false);
                }
            })
            .catch(function() { showToast('Reset failed.', false); });
    };

    window.handleAddNewSlot = function(input) {
        if (!input || !input.files || input.files.length === 0) return;
        var file = input.files[0];
        showToast('Adding new slideshow slot photo...', true);

        uploadMedia('<?= base_url("admin/settings/add-slideshow-slot") ?>', 'image', file, {}, function(data) {
            showToast(data.message || 'New slideshow photo added!', true);
            input.value = '';
            var slot = data.slot;
            var imageUrl = data.image_url;

            // Build DOM card for new slot and insert before addSlotCard
            var addCard = document.getElementById('addSlotCard');
            var newCard = document.createElement('div');
            newCard.className = 'slideshow-slot-card';
            newCard.id = 'slotCard-' + slot;
            newCard.dataset.slot = slot;
            newCard.innerHTML = '<div class="slot-card-image-wrap">' +
                '<img src="' + imageUrl + '" alt="Slot ' + slot + '" id="slotImg-' + slot + '">' +
                '<span class="slot-number-badge">Slot ' + slot + '</span>' +
                '<span class="slot-status-badge custom" id="slotBadge-' + slot + '">Custom</span>' +
                '</div>' +
                '<div class="slot-card-body">' +
                '<h5>Custom Slot ' + slot + '</h5>' +
                '<p>Additional rotating background photo for sign-in screens</p>' +
                '<div class="slot-card-actions">' +
                '<input type="file" id="slotFileInput-' + slot + '" accept="image/png,image/jpeg,image/jpg,image/webp" style="display:none;" data-slot="' + slot + '">' +
                '<button type="button" class="btn-slot-upload" onclick="document.getElementById(\'slotFileInput-' + slot + '\').click();" title="Upload new photo for Slot ' + slot + '">' +
                '<i class="fas fa-upload"></i> Change Photo' +
                '</button>' +
                '<button type="button" class="btn-slot-reset text-danger" id="btnDeleteSlot-' + slot + '" onclick="deleteSlotPhoto(' + slot + ');" title="Delete custom slot ' + slot + '" style="border-color:#fca5a5; color:#dc2626; background:#fef2f2;">' +
                '<i class="fas fa-trash-alt"></i>' +
                '</button>' +
                '</div>' +
                '</div>';
            if (addCard && addCard.parentNode) {
                addCard.parentNode.insertBefore(newCard, addCard);
            }
        }, function(err) {
            showToast(err, false);
            input.value = '';
        });
    };

    window.deleteSlotPhoto = function(slot) {
        if (!confirm('Are you sure you want to remove Slot ' + slot + ' from the slideshow?')) return;
        var fd = new FormData();
        fd.append('slot', slot);
        fd.append(currentCsrfToken, currentCsrfHash);

        fetch('<?= base_url("admin/settings/delete-slideshow-slot") ?>', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.csrf_hash) updateCsrf(data.csrf_token, data.csrf_hash);
                if (data.success) {
                    showToast(data.message || 'Slot deleted', true);
                    var card = document.getElementById('slotCard-' + slot);
                    if (card) {
                        card.style.transition = 'all 0.3s ease';
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.8)';
                        setTimeout(function() {
                            if (card.parentNode) card.parentNode.removeChild(card);
                        }, 300);
                    }
                } else {
                    showToast(data.message || 'Failed to delete slot.', false);
                }
            })
            .catch(function() { showToast('Delete failed.', false); });
    };

    var btnResetAll = document.getElementById('btnResetAllSlideshow');
    if (btnResetAll) {
        btnResetAll.addEventListener('click', function() {
            if (!confirm('Are you sure you want to reset ALL 5 slideshow pictures back to system defaults?')) return;
            var fd = new FormData();
            fd.append(currentCsrfToken, currentCsrfHash);

            fetch('<?= base_url("admin/settings/reset-all-slideshow") ?>', { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.csrf_hash) updateCsrf(data.csrf_token, data.csrf_hash);
                    if (data.success) {
                        showToast(data.message, true);
                        if (data.defaults) {
                            for (var s = 1; s <= 5; s++) {
                                var img = document.getElementById('slotImg-' + s);
                                if (img && data.defaults[s]) img.src = data.defaults[s];
                                var badge = document.getElementById('slotBadge-' + s);
                                if (badge) {
                                    badge.className = 'slot-status-badge default';
                                    badge.textContent = 'Default';
                                }
                                var resetBtn = document.getElementById('btnResetSlot-' + s);
                                if (resetBtn) resetBtn.style.display = 'none';
                            }
                        }
                    }
                })
                .catch(function() { showToast('Reset failed.', false); });
        });
    }

    // =========================================================================
    // 10. AJAX Form Submissions with Instant Real-Time Sync & Toasts
    // =========================================================================
    function bindAjaxForm(formId, defaultSuccessMsg) {
        var form = document.getElementById(formId);
        if (!form) return;
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var submitBtn = form.querySelector('button[type="submit"]');
            var originalText = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            }

            var fd = new FormData(form);
            if (!fd.has(currentCsrfToken)) {
                fd.append(currentCsrfToken, currentCsrfHash);
            }

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: fd
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
                if (data.csrf_hash) updateCsrf(data.csrf_token, data.csrf_hash);
                if (data.success) {
                    showToast(data.message || defaultSuccessMsg, true);
                    if (data.data && typeof window.applyLiveBranding === 'function') {
                        window.applyLiveBranding(data.data);
                    }
                } else {
                    showToast(data.message || 'Failed to save changes.', false);
                }
            })
            .catch(function() {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
                form.submit(); // fallback to standard post
            });
        });
    }

    bindAjaxForm('formIdentity', 'Branding identity updated successfully.');
    bindAjaxForm('formThemes', 'Theme palettes updated successfully.');
    bindAjaxForm('formFooter', 'Footer settings updated successfully.');
});
</script>

<?= $this->include('templates/footer') ?>
