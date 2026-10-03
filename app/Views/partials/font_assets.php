<?php
// Match the exact CSS font URLs so preloads are reused by the browser.
$fontFiles = [
    'Outfit' => 'outfit-latin-6c18d579fd87.woff2',
    'Inter' => 'inter-latin-3100e775e861.woff2',
];
$fontFile = $fontFiles[$fontFamily ?? 'Outfit'] ?? $fontFiles['Outfit'];
?>
<link rel="preload" href="<?= esc(base_url('assets/vendor/fonts/' . $fontFile)) ?>" as="font" type="font/woff2" crossorigin>
<?php if (!empty($fontIcons)): ?>
<link rel="preload" href="<?= esc(base_url('assets/vendor/fontawesome/webfonts/fa-solid-900.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<?php endif; ?>
<link rel="stylesheet" href="<?= app_asset_url('assets/vendor/fonts/fonts.css') ?>">
