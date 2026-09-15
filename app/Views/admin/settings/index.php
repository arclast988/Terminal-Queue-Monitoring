<?= $this->include('templates/header') ?>

<style>
/* ============================== Settings Page Styles ============================== */
.settings-page { max-width: 1200px; margin: 0 auto; }
.settings-hero { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 28px; flex-wrap: wrap; }
.settings-hero h1 { font-size: 26px; font-weight: 700; color: var(--text-main, #1e293b); display: flex; align-items: center; gap: 12px; }
.settings-hero h1 i { color: var(--primary, #B71C1C); font-size: 22px; }
.sa-badge { display: inline-flex; align-items: center; gap: 6px; background: #B71C1C; color: #fff; font-size: 11px; font-weight: 700; padding: 5px 14px; border-radius: 20px; letter-spacing: 0.5px; }

/* Tabs */
.settings-tabs { display: flex; gap: 4px; border-bottom: 2px solid var(--border, #e2e8f0); margin-bottom: 24px; flex-wrap: wrap; }
.settings-tab { padding: 12px 22px; font-size: 14px; font-weight: 600; color: var(--text-muted, #64748b); cursor: pointer; border: none; background: none; border-bottom: 3px solid transparent; transition: all 0.2s; border-radius: 8px 8px 0 0; display: flex; align-items: center; gap: 8px; }
.settings-tab:hover { color: var(--primary, #B71C1C); background: rgba(183,28,28,0.04); }
.settings-tab.active { color: var(--primary, #B71C1C); border-bottom-color: var(--primary, #B71C1C); background: rgba(183,28,28,0.06); }
.tab-content { display: none; animation: fadeIn 0.3s ease; }
.tab-content.active { display: block; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

/* Cards */
.settings-card { background: var(--surface, #fff); border: 1px solid var(--border, #e2e8f0); border-radius: 16px; padding: 28px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.settings-card-header { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid var(--border, #e2e8f0); }
.settings-card-header i { font-size: 20px; color: var(--primary, #B71C1C); width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: rgba(183,28,28,0.08); border-radius: 10px; }
.settings-card-header h3 { font-size: 18px; font-weight: 700; color: var(--text-main, #1e293b); margin: 0; }
.settings-card-header p { font-size: 13px; color: var(--text-muted, #64748b); margin: 2px 0 0; }

/* Form Elements */
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group.full-width { grid-column: 1 / -1; }
.form-label { font-size: 13px; font-weight: 600; color: var(--text-main, #1e293b); letter-spacing: 0.3px; }
.form-label .label-hint { font-weight: 400; color: var(--text-muted, #94a3b8); font-size: 12px; }
.form-input { padding: 10px 14px; border: 1.5px solid var(--border, #e2e8f0); border-radius: 10px; font-size: 14px; font-family: inherit; color: var(--text-main, #1e293b); background: var(--surface, #fff); transition: border-color 0.2s, box-shadow 0.2s; }
.form-input:focus { outline: none; border-color: var(--primary, #B71C1C); box-shadow: 0 0 0 3px rgba(183,28,28,0.08); }
.form-textarea { min-height: 90px; resize: vertical; }
.btn-save { display: inline-flex; align-items: center; gap: 8px; padding: 10px 24px; background: var(--primary, #B71C1C); color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.btn-save:hover { filter: brightness(1.1); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(183,28,28,0.25); }

/* Upload Zone */
.upload-zone { border: 2px dashed var(--border-strong, #cbd5e1); border-radius: 14px; padding: 32px 24px; text-align: center; cursor: pointer; transition: all 0.2s; background: var(--surface-sunken, #f8fafc); position: relative; }
.upload-zone:hover, .upload-zone.drag-over { border-color: var(--primary, #B71C1C); background: rgba(183,28,28,0.03); }
.upload-zone .upload-icon { font-size: 36px; color: var(--text-faint, #94a3b8); margin-bottom: 12px; }
.upload-zone .upload-text { font-size: 14px; color: var(--text-muted, #64748b); }
.upload-zone .upload-text strong { color: var(--primary, #B71C1C); }
.upload-zone .upload-hint { font-size: 12px; color: var(--text-faint, #94a3b8); margin-top: 6px; }

/* Media Preview */
.media-preview { display: flex; align-items: center; gap: 20px; padding: 16px; background: var(--surface-sunken, #f8fafc); border-radius: 12px; margin-top: 16px; }
.media-preview img { width: 80px; height: 80px; object-fit: contain; border-radius: 10px; border: 1px solid var(--border, #e2e8f0); background: #fff; }
.media-preview.bg-preview img { width: 160px; height: 90px; object-fit: cover; border-radius: 10px; }
.media-preview-info { flex: 1; }
.media-preview-info .filename { font-size: 13px; font-weight: 600; color: var(--text-main); }
.media-preview-info .filemeta { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
.btn-reset { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: #fff; color: #dc2626; border: 1.5px solid #fca5a5; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.btn-reset:hover { background: #fef2f2; border-color: #dc2626; }

/* Theme Color Picker */
.theme-section { margin-bottom: 24px; }
.theme-section-header { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
.theme-section-header .theme-dot { width: 14px; height: 14px; border-radius: 50%; border: 2px solid #e2e8f0; }
.theme-section-header h4 { font-size: 15px; font-weight: 600; color: var(--text-main); margin: 0; }
.color-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
.color-field { display: flex; flex-direction: column; gap: 6px; }
.color-field label { font-size: 12px; font-weight: 600; color: var(--text-muted); }
.color-picker-wrap { display: flex; align-items: center; gap: 8px; }
.color-picker-wrap input[type="color"] { width: 40px; height: 40px; border: 2px solid var(--border); border-radius: 10px; cursor: pointer; padding: 2px; background: #fff; }
.color-picker-wrap input[type="text"] { flex: 1; padding: 8px 12px; border: 1.5px solid var(--border); border-radius: 8px; font-family: 'Outfit', monospace; font-size: 13px; }

/* Live Preview */
.live-preview-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px; }
.preview-card { border: 1px solid var(--border); border-radius: 14px; overflow: hidden; background: #fff; }
.preview-card-label { font-size: 11px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; color: var(--text-faint); padding: 8px 14px; background: var(--surface-sunken); border-bottom: 1px solid var(--border); }
.preview-header-bar { display: flex; align-items: center; gap: 12px; padding: 10px 16px; }
.preview-header-bar img { width: 36px; height: 36px; object-fit: contain; border-radius: 8px; }
.preview-header-bar .preview-brand h4 { font-size: 14px; font-weight: 700; margin: 0; line-height: 1.2; }
.preview-header-bar .preview-brand p { font-size: 10px; margin: 0; opacity: 0.8; font-weight: 600; letter-spacing: 0.5px; }

/* Footer preview */
.preview-footer-bar { padding: 14px 16px; font-size: 12px; }
.preview-footer-bar h5 { font-size: 13px; font-weight: 700; margin: 0 0 4px; }
.preview-footer-bar .footer-desc { opacity: 0.85; line-height: 1.5; font-size: 11px; }
.preview-footer-bar .footer-copy { margin-top: 8px; opacity: 0.7; font-size: 10px; border-top: 1px solid rgba(255,255,255,0.15); padding-top: 8px; }

/* Status messages */
.upload-status { padding: 10px 16px; border-radius: 8px; font-size: 13px; font-weight: 500; margin-top: 12px; display: none; }
.upload-status.success { display: block; background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.upload-status.error { display: block; background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; }

@media (max-width: 768px) {
    .form-grid { grid-template-columns: 1fr; }
    .live-preview-grid { grid-template-columns: 1fr; }
    .color-grid { grid-template-columns: 1fr; }
    .settings-hero { flex-direction: column; align-items: flex-start; }
}
</style>

<?php
$s = $settings ?? [];
$logoUrl   = app_logo();
$bgUrl     = app_bg_image();
$hasCustomLogo = !empty($s['app_logo']);
$hasCustomBg   = !empty($s['app_background_image']);
?>

<div class="settings-page">

    <!-- Page Header -->
    <div class="settings-hero">
        <h1><i class="fas fa-palette"></i> System Branding & Themes</h1>
        <span class="sa-badge"><i class="fas fa-shield-alt"></i> Super Admin Only</span>
    </div>

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

    <!-- Tabs -->
    <div class="settings-tabs" role="tablist">
        <button class="settings-tab active" data-tab="identity" role="tab"><i class="fas fa-id-badge"></i> Identity</button>
        <button class="settings-tab" data-tab="media" role="tab"><i class="fas fa-image"></i> Logo & Background</button>
        <button class="settings-tab" data-tab="themes" role="tab"><i class="fas fa-swatchbook"></i> Theme Templates</button>
        <button class="settings-tab" data-tab="footer" role="tab"><i class="fas fa-layer-group"></i> Footer & Credits</button>
    </div>

    <!-- ===================== TAB 1: IDENTITY ===================== -->
    <div class="tab-content active" id="tab-identity">
        <div class="settings-card">
            <div class="settings-card-header">
                <i class="fas fa-building"></i>
                <div>
                    <h3>System Identity</h3>
                    <p>Set the transit name, subtitle, acronym, and contact information displayed everywhere in the system.</p>
                </div>
            </div>

            <!-- Live Preview -->
            <div class="live-preview-grid" style="margin-bottom: 24px;">
                <div class="preview-card">
                    <div class="preview-card-label">Guest / Public Header Preview</div>
                    <div class="preview-header-bar" id="previewGuest" style="background: <?= esc($s['theme_guest_nav_bg'] ?? '#ffffff') ?>; color: <?= esc($s['theme_guest_nav_text'] ?? '#1c2430') ?>;">
                        <img src="<?= esc($logoUrl) ?>" alt="Logo" id="previewLogoGuest">
                        <div class="preview-brand">
                            <h4 id="previewNameGuest"><?= esc($s['app_name'] ?? 'Palompon Transit') ?></h4>
                            <p id="previewSubGuest"><?= esc($s['app_subtitle'] ?? 'Terminal Monitor') ?></p>
                        </div>
                    </div>
                </div>
                <div class="preview-card">
                    <div class="preview-card-label">Admin / Superadmin Header Preview</div>
                    <div class="preview-header-bar" id="previewAdmin" style="background: <?= esc($s['theme_admin_nav_bg'] ?? '#B71C1C') ?>; color: <?= esc($s['theme_admin_nav_text'] ?? '#ffffff') ?>;">
                        <img src="<?= esc($logoUrl) ?>" alt="Logo" id="previewLogoAdmin">
                        <div class="preview-brand">
                            <h4 id="previewNameAdmin"><?= esc($s['app_name'] ?? 'Palompon Transit') ?></h4>
                            <p id="previewSubAdmin"><?= esc($s['app_subtitle'] ?? 'Terminal Monitor') ?></p>
                        </div>
                    </div>
                </div>
                <div class="preview-card">
                    <div class="preview-card-label">Dispatcher / Staff Header Preview</div>
                    <div class="preview-header-bar" id="previewStaff" style="background: <?= esc($s['theme_staff_nav_bg'] ?? '#15803d') ?>; color: <?= esc($s['theme_staff_nav_text'] ?? '#ffffff') ?>;">
                        <img src="<?= esc($logoUrl) ?>" alt="Logo" id="previewLogoStaff">
                        <div class="preview-brand">
                            <h4 id="previewNameStaff"><?= esc($s['app_name'] ?? 'Palompon Transit') ?></h4>
                            <p id="previewSubStaff"><?= esc($s['app_subtitle'] ?? 'Terminal Monitor') ?></p>
                        </div>
                    </div>
                </div>
                <div class="preview-card">
                    <div class="preview-card-label">Footer Preview</div>
                    <div class="preview-footer-bar" style="background: #1e293b; color: #e2e8f0;">
                        <h5 id="previewFooterTitle" style="color: #f97316;"><?= esc($s['footer_about_title'] ?? 'PTTM System') ?></h5>
                        <div class="footer-desc" id="previewFooterDesc"><?= esc($s['footer_about_text'] ?? '') ?></div>
                        <div class="footer-copy" id="previewFooterCopy">&copy; <?= date('Y') ?> <span id="previewCopyTitle"><?= esc($s['system_title'] ?? 'Palompon Transit Terminal Management System') ?></span> (<span id="previewCopyAcro"><?= esc($s['acronym'] ?? 'PTTM') ?></span>). All rights reserved. | <span id="previewCopyCredit"><?= esc($s['footer_credit'] ?? 'Municipality of Palompon, Leyte') ?></span></div>
                    </div>
                </div>
            </div>

            <form method="post" action="<?= base_url('admin/settings/update-branding') ?>" autocomplete="off">
                <?= csrf_field() ?>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Transit / System Name <span class="label-hint">(header title)</span></label>
                        <input type="text" name="app_name" class="form-input" id="inputAppName" value="<?= esc($s['app_name'] ?? 'Palompon Transit') ?>" required maxlength="80" placeholder="e.g. Palompon Transit">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subtitle / Tagline <span class="label-hint">(below title)</span></label>
                        <input type="text" name="app_subtitle" class="form-input" id="inputAppSubtitle" value="<?= esc($s['app_subtitle'] ?? 'Terminal Monitor') ?>" maxlength="60" placeholder="e.g. Terminal Monitor">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Acronym <span class="label-hint">(short code)</span></label>
                        <input type="text" name="acronym" class="form-input" id="inputAcronym" value="<?= esc($s['acronym'] ?? 'PTTM') ?>" maxlength="10" placeholder="e.g. PTTM">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Full System Title <span class="label-hint">(footer / reports)</span></label>
                        <input type="text" name="system_title" class="form-input" id="inputSystemTitle" value="<?= esc($s['system_title'] ?? '') ?>" maxlength="150" placeholder="e.g. Palompon Transit Terminal Management System">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Phone</label>
                        <input type="text" name="contact_phone" class="form-input" value="<?= esc($s['contact_phone'] ?? '') ?>" maxlength="80" placeholder="e.g. (053) 555-8376">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Email</label>
                        <input type="email" name="contact_email" class="form-input" value="<?= esc($s['contact_email'] ?? '') ?>" maxlength="120" placeholder="e.g. admin@palompon.gov.ph">
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">Contact Address</label>
                        <input type="text" name="contact_address" class="form-input" value="<?= esc($s['contact_address'] ?? '') ?>" maxlength="200" placeholder="Terminal street address">
                    </div>
                </div>
                <div style="margin-top: 20px; text-align: right;">
                    <button type="submit" class="btn-save"><i class="fas fa-save"></i> Save Identity</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================== TAB 2: MEDIA (LOGO & BACKGROUND) ===================== -->
    <div class="tab-content" id="tab-media">
        <!-- Logo -->
        <div class="settings-card">
            <div class="settings-card-header">
                <i class="fas fa-stamp"></i>
                <div>
                    <h3>System Logo</h3>
                    <p>Upload a custom logo to replace the default seal across the entire system. Max 5 MB (PNG, JPG, WEBP, GIF, SVG).</p>
                </div>
            </div>
            <div class="upload-zone" id="logoUploadZone">
                <input type="file" id="logoFileInput" accept="image/png,image/jpeg,image/jpg,image/webp,image/gif,image/svg+xml" style="display:none;">
                <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                <div class="upload-text"><strong>Click to upload</strong> or drag and drop</div>
                <div class="upload-hint">PNG, JPG, WEBP, GIF, or SVG — Max 5 MB</div>
            </div>
            <div class="upload-status" id="logoUploadStatus"></div>
            <div class="media-preview" id="logoPreview">
                <img src="<?= esc($logoUrl) ?>" alt="Current Logo" id="logoPreviewImg">
                <div class="media-preview-info">
                    <div class="filename"><?= $hasCustomLogo ? basename($s['app_logo']) : 'Default Seal (9HFScgVg_400x400.png)' ?></div>
                    <div class="filemeta"><?= $hasCustomLogo ? 'Custom Upload' : 'System Default' ?></div>
                </div>
                <?php if ($hasCustomLogo): ?>
                    <button type="button" class="btn-reset" id="btnResetLogo"><i class="fas fa-undo"></i> Reset to Default</button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Background Picture -->
        <div class="settings-card">
            <div class="settings-card-header">
                <i class="fas fa-panorama"></i>
                <div>
                    <h3>Background / Hero Picture</h3>
                    <p>Upload a custom background image for login and auth pages. Max 8 MB (PNG, JPG, WEBP).</p>
                </div>
            </div>
            <div class="upload-zone" id="bgUploadZone">
                <input type="file" id="bgFileInput" accept="image/png,image/jpeg,image/jpg,image/webp" style="display:none;">
                <div class="upload-icon"><i class="fas fa-mountain-sun"></i></div>
                <div class="upload-text"><strong>Click to upload</strong> or drag and drop</div>
                <div class="upload-hint">PNG, JPG, or WEBP — Max 8 MB</div>
            </div>
            <div class="upload-status" id="bgUploadStatus"></div>
            <div class="media-preview bg-preview" id="bgPreview">
                <img src="<?= esc($bgUrl) ?>" alt="Current Background" id="bgPreviewImg">
                <div class="media-preview-info">
                    <div class="filename"><?= $hasCustomBg ? basename($s['app_background_image']) : 'Default Artwork (system bg image.png)' ?></div>
                    <div class="filemeta"><?= $hasCustomBg ? 'Custom Upload' : 'System Default' ?></div>
                </div>
                <?php if ($hasCustomBg): ?>
                    <button type="button" class="btn-reset" id="btnResetBg"><i class="fas fa-undo"></i> Reset to Default</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ===================== TAB 3: THEME TEMPLATES ===================== -->
    <div class="tab-content" id="tab-themes">
        <div class="settings-card">
            <div class="settings-card-header">
                <i class="fas fa-swatchbook"></i>
                <div>
                    <h3>Role Theme Colours</h3>
                    <p>Customize the primary colour and navigation bar appearance for each role.</p>
                </div>
            </div>

            <form method="post" action="<?= base_url('admin/settings/update-themes') ?>" autocomplete="off">
                <?= csrf_field() ?>

                <!-- Guest Theme -->
                <div class="theme-section">
                    <div class="theme-section-header">
                        <span class="theme-dot" style="background: <?= esc($s['theme_guest_primary'] ?? '#1E40AF') ?>;"></span>
                        <h4>Guest / Public Portal</h4>
                    </div>
                    <div class="color-grid">
                        <div class="color-field">
                            <label>Primary Colour</label>
                            <div class="color-picker-wrap">
                                <input type="color" name="theme_guest_primary" value="<?= esc($s['theme_guest_primary'] ?? '#1E40AF') ?>" data-sync="theme_guest_primary_text">
                                <input type="text" id="theme_guest_primary_text" value="<?= esc($s['theme_guest_primary'] ?? '#1E40AF') ?>" data-sync-color="theme_guest_primary" maxlength="7" placeholder="#1E40AF">
                            </div>
                        </div>
                        <div class="color-field">
                            <label>Nav Background</label>
                            <div class="color-picker-wrap">
                                <input type="color" name="theme_guest_nav_bg" value="<?= esc($s['theme_guest_nav_bg'] ?? '#ffffff') ?>" data-sync="theme_guest_nav_bg_text">
                                <input type="text" id="theme_guest_nav_bg_text" value="<?= esc($s['theme_guest_nav_bg'] ?? '#ffffff') ?>" data-sync-color="theme_guest_nav_bg" maxlength="7">
                            </div>
                        </div>
                        <div class="color-field">
                            <label>Nav Text</label>
                            <div class="color-picker-wrap">
                                <input type="color" name="theme_guest_nav_text" value="<?= esc($s['theme_guest_nav_text'] ?? '#1c2430') ?>" data-sync="theme_guest_nav_text_text">
                                <input type="text" id="theme_guest_nav_text_text" value="<?= esc($s['theme_guest_nav_text'] ?? '#1c2430') ?>" data-sync-color="theme_guest_nav_text" maxlength="7">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin Theme -->
                <div class="theme-section">
                    <div class="theme-section-header">
                        <span class="theme-dot" style="background: <?= esc($s['theme_admin_primary'] ?? '#B71C1C') ?>;"></span>
                        <h4>Admin / Super Admin</h4>
                    </div>
                    <div class="color-grid">
                        <div class="color-field">
                            <label>Primary Colour</label>
                            <div class="color-picker-wrap">
                                <input type="color" name="theme_admin_primary" value="<?= esc($s['theme_admin_primary'] ?? '#B71C1C') ?>" data-sync="theme_admin_primary_text">
                                <input type="text" id="theme_admin_primary_text" value="<?= esc($s['theme_admin_primary'] ?? '#B71C1C') ?>" data-sync-color="theme_admin_primary" maxlength="7">
                            </div>
                        </div>
                        <div class="color-field">
                            <label>Nav Background</label>
                            <div class="color-picker-wrap">
                                <input type="color" name="theme_admin_nav_bg" value="<?= esc($s['theme_admin_nav_bg'] ?? '#B71C1C') ?>" data-sync="theme_admin_nav_bg_text">
                                <input type="text" id="theme_admin_nav_bg_text" value="<?= esc($s['theme_admin_nav_bg'] ?? '#B71C1C') ?>" data-sync-color="theme_admin_nav_bg" maxlength="7">
                            </div>
                        </div>
                        <div class="color-field">
                            <label>Nav Text</label>
                            <div class="color-picker-wrap">
                                <input type="color" name="theme_admin_nav_text" value="<?= esc($s['theme_admin_nav_text'] ?? '#ffffff') ?>" data-sync="theme_admin_nav_text_text">
                                <input type="text" id="theme_admin_nav_text_text" value="<?= esc($s['theme_admin_nav_text'] ?? '#ffffff') ?>" data-sync-color="theme_admin_nav_text" maxlength="7">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Staff Theme -->
                <div class="theme-section">
                    <div class="theme-section-header">
                        <span class="theme-dot" style="background: <?= esc($s['theme_staff_primary'] ?? '#15803d') ?>;"></span>
                        <h4>Dispatcher / Staff</h4>
                    </div>
                    <div class="color-grid">
                        <div class="color-field">
                            <label>Primary Colour</label>
                            <div class="color-picker-wrap">
                                <input type="color" name="theme_staff_primary" value="<?= esc($s['theme_staff_primary'] ?? '#15803d') ?>" data-sync="theme_staff_primary_text">
                                <input type="text" id="theme_staff_primary_text" value="<?= esc($s['theme_staff_primary'] ?? '#15803d') ?>" data-sync-color="theme_staff_primary" maxlength="7">
                            </div>
                        </div>
                        <div class="color-field">
                            <label>Nav Background</label>
                            <div class="color-picker-wrap">
                                <input type="color" name="theme_staff_nav_bg" value="<?= esc($s['theme_staff_nav_bg'] ?? '#15803d') ?>" data-sync="theme_staff_nav_bg_text">
                                <input type="text" id="theme_staff_nav_bg_text" value="<?= esc($s['theme_staff_nav_bg'] ?? '#15803d') ?>" data-sync-color="theme_staff_nav_bg" maxlength="7">
                            </div>
                        </div>
                        <div class="color-field">
                            <label>Nav Text</label>
                            <div class="color-picker-wrap">
                                <input type="color" name="theme_staff_nav_text" value="<?= esc($s['theme_staff_nav_text'] ?? '#ffffff') ?>" data-sync="theme_staff_nav_text_text">
                                <input type="text" id="theme_staff_nav_text_text" value="<?= esc($s['theme_staff_nav_text'] ?? '#ffffff') ?>" data-sync-color="theme_staff_nav_text" maxlength="7">
                            </div>
                        </div>
                    </div>
                </div>

                <div style="text-align: right; margin-top: 16px;">
                    <button type="submit" class="btn-save"><i class="fas fa-save"></i> Save Theme Colours</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================== TAB 4: FOOTER & CREDITS ===================== -->
    <div class="tab-content" id="tab-footer">
        <div class="settings-card">
            <div class="settings-card-header">
                <i class="fas fa-layer-group"></i>
                <div>
                    <h3>Footer & Credits</h3>
                    <p>Customize the footer heading, description text, and municipal/agency credit displayed on public pages.</p>
                </div>
            </div>

            <form method="post" action="<?= base_url('admin/settings/update-footer') ?>" autocomplete="off">
                <?= csrf_field() ?>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Footer About Title <span class="label-hint">(heading)</span></label>
                        <input type="text" name="footer_about_title" class="form-input" id="inputFooterTitle" value="<?= esc($s['footer_about_title'] ?? 'PTTM System') ?>" maxlength="60" placeholder="e.g. PTTM System">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Municipality / Agency Credit</label>
                        <input type="text" name="footer_credit" class="form-input" id="inputFooterCredit" value="<?= esc($s['footer_credit'] ?? 'Municipality of Palompon, Leyte') ?>" maxlength="120" placeholder="e.g. Municipality of Palompon, Leyte">
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">Footer Description Text</label>
                        <textarea name="footer_about_text" class="form-input form-textarea" id="inputFooterText" maxlength="500" placeholder="Describe your transit system for the public footer."><?= esc($s['footer_about_text'] ?? '') ?></textarea>
                    </div>
                </div>
                <div style="margin-top: 20px; text-align: right;">
                    <button type="submit" class="btn-save"><i class="fas fa-save"></i> Save Footer Info</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ---- Tab switching ----
    document.querySelectorAll('.settings-tab').forEach(function(tab) {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.settings-tab').forEach(function(t) { t.classList.remove('active'); });
            document.querySelectorAll('.tab-content').forEach(function(c) { c.classList.remove('active'); });
            tab.classList.add('active');
            var target = document.getElementById('tab-' + tab.dataset.tab);
            if (target) target.classList.add('active');
        });
    });

    // ---- Live preview: Identity fields ----
    var nameInput = document.getElementById('inputAppName');
    var subInput  = document.getElementById('inputAppSubtitle');
    var acroInput = document.getElementById('inputAcronym');
    var titleInput = document.getElementById('inputSystemTitle');
    var footerTitleInput = document.getElementById('inputFooterTitle');
    var footerTextInput  = document.getElementById('inputFooterText');
    var footerCreditInput = document.getElementById('inputFooterCredit');

    function updatePreviews() {
        var name = nameInput ? nameInput.value : '';
        var sub  = subInput ? subInput.value : '';
        ['Guest','Admin','Staff'].forEach(function(role) {
            var n = document.getElementById('previewName' + role);
            var s = document.getElementById('previewSub' + role);
            if (n) n.textContent = name;
            if (s) s.textContent = sub;
        });
        var ct = document.getElementById('previewCopyTitle');
        var ca = document.getElementById('previewCopyAcro');
        if (ct && titleInput) ct.textContent = titleInput.value;
        if (ca && acroInput) ca.textContent = acroInput.value;
        var ft = document.getElementById('previewFooterTitle');
        var fd = document.getElementById('previewFooterDesc');
        var fc = document.getElementById('previewCopyCredit');
        if (ft && footerTitleInput) ft.textContent = footerTitleInput.value;
        if (fd && footerTextInput) fd.textContent = footerTextInput.value;
        if (fc && footerCreditInput) fc.textContent = footerCreditInput.value;
    }
    [nameInput, subInput, acroInput, titleInput, footerTitleInput, footerTextInput, footerCreditInput].forEach(function(el) {
        if (el) el.addEventListener('input', updatePreviews);
    });

    // ---- Colour picker sync ----
    document.querySelectorAll('input[type="color"][data-sync]').forEach(function(picker) {
        var textId = picker.dataset.sync;
        var textEl = document.getElementById(textId);
        if (textEl) {
            picker.addEventListener('input', function() { textEl.value = picker.value; });
            textEl.addEventListener('input', function() {
                if (/^#[0-9a-fA-F]{6}$/.test(textEl.value)) picker.value = textEl.value;
            });
        }
    });

    // ---- File uploads ----
    var csrfToken = document.querySelector('meta[name="csrf-token"]');
    var csrfHeader = document.querySelector('meta[name="csrf-header"]');

    function setupUpload(zoneId, inputId, statusId, previewImgId, endpoint, fieldName) {
        var zone = document.getElementById(zoneId);
        var input = document.getElementById(inputId);
        var status = document.getElementById(statusId);
        var previewImg = document.getElementById(previewImgId);
        if (!zone || !input) return;

        zone.addEventListener('click', function() { input.click(); });
        zone.addEventListener('dragover', function(e) { e.preventDefault(); zone.classList.add('drag-over'); });
        zone.addEventListener('dragleave', function() { zone.classList.remove('drag-over'); });
        zone.addEventListener('drop', function(e) {
            e.preventDefault(); zone.classList.remove('drag-over');
            if (e.dataTransfer.files.length > 0) { input.files = e.dataTransfer.files; doUpload(input.files[0]); }
        });
        input.addEventListener('change', function() { if (input.files.length > 0) doUpload(input.files[0]); });

        function doUpload(file) {
            var fd = new FormData();
            fd.append(fieldName, file);
            fd.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            if (status) { status.className = 'upload-status'; status.style.display = 'block'; status.textContent = 'Uploading...'; status.style.background = '#e0f2fe'; status.style.color = '#0369a1'; status.style.border = '1px solid #7dd3fc'; }
            fetch(endpoint, { method: 'POST', body: fd, headers: csrfHeader ? { [csrfHeader.content]: csrfToken ? csrfToken.content : '' } : {} })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.csrf_hash) { if (csrfToken) csrfToken.content = data.csrf_hash; document.querySelectorAll('input[name="<?= csrf_token() ?>"]').forEach(function(el) { el.value = data.csrf_hash; }); }
                    if (data.success) {
                        status.className = 'upload-status success'; status.textContent = data.message || 'Uploaded successfully.';
                        if (previewImg && data.image_url) previewImg.src = data.image_url;
                        if (previewImgId === 'logoPreviewImg') { ['previewLogoGuest','previewLogoAdmin','previewLogoStaff'].forEach(function(id) { var el = document.getElementById(id); if (el) el.src = data.image_url; }); }
                        setTimeout(function() { location.reload(); }, 1200);
                    } else {
                        status.className = 'upload-status error'; status.textContent = data.message || 'Upload failed.';
                    }
                })
                .catch(function(err) { if (status) { status.className = 'upload-status error'; status.textContent = 'Network error. Please try again.'; } });
        }
    }

    setupUpload('logoUploadZone', 'logoFileInput', 'logoUploadStatus', 'logoPreviewImg', '<?= base_url("admin/settings/upload-logo") ?>', 'logo');
    setupUpload('bgUploadZone', 'bgFileInput', 'bgUploadStatus', 'bgPreviewImg', '<?= base_url("admin/settings/upload-background") ?>', 'background');

    // ---- Reset buttons ----
    var resetLogoBtn = document.getElementById('btnResetLogo');
    if (resetLogoBtn) {
        resetLogoBtn.addEventListener('click', function() {
            if (!confirm('Reset logo to default seal?')) return;
            var fd = new FormData(); fd.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            fetch('<?= base_url("admin/settings/reset-logo") ?>', { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(data) { if (data.success) location.reload(); })
                .catch(function() {});
        });
    }
    var resetBgBtn = document.getElementById('btnResetBg');
    if (resetBgBtn) {
        resetBgBtn.addEventListener('click', function() {
            if (!confirm('Reset background to default artwork?')) return;
            var fd = new FormData(); fd.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            fetch('<?= base_url("admin/settings/reset-background") ?>', { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(data) { if (data.success) location.reload(); })
                .catch(function() {});
        });
    }
});
</script>

<?= $this->include('templates/footer') ?>
