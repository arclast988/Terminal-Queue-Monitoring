<script type="application/json" id="backgroundSyncConfig"><?= json_encode([
    'url' => base_url('api/system-branding'),
    'background' => [
        'app_bg_mode' => app_bg_mode(),
        'app_background_image' => app_bg_image(),
        'app_bg_slideshow' => array_values(app_bg_slideshow()),
    ],
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES) ?></script>
<script src="<?= app_asset_url('assets/js/background-sync.js') ?>"></script>
