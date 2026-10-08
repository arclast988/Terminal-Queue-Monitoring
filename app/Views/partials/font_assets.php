<?php
// Fetch the page's body font before text layout, avoiding late button/badge wraps.
$bodyFontFile = ($fontFamily ?? 'Outfit') === 'Inter'
    ? 'inter-latin-3100e775e861.woff2'
    : 'outfit-latin-6c18d579fd87.woff2';
?>
<!-- Filenames already contain content hashes; match the CSS URL exactly for reuse. -->
<?php if ($preloadFont ?? true): ?>
<link rel="preload" href="<?= base_url('assets/vendor/fonts/' . $bodyFontFile) ?>" as="font" type="font/woff2" crossorigin>
<?php endif; ?>
<link rel="stylesheet" href="<?= app_asset_url('assets/vendor/fonts/fonts.css') ?>">
