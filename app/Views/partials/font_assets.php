<?php
// Let the stylesheet request the fonts actually used by this page. Preloading
// a fixed family/subset wastes a request when theme or Unicode coverage differs.
?>
<?php if (!empty($fontIcons)): ?>
<link rel="preload" href="<?= esc(base_url('assets/vendor/fontawesome/webfonts/fa-solid-900.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<?php endif; ?>
<link rel="stylesheet" href="<?= app_asset_url('assets/vendor/fonts/fonts.css') ?>">
