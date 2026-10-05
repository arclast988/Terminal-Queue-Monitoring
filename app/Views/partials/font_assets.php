<?php
// Prioritize only the Latin fonts used by this page, with the same stable URLs
// as fonts.css. Other Unicode subsets remain on demand.
$criticalFonts = ($fontFamily ?? '') === 'Inter'
    ? ['inter-latin-3100e775e861.woff2']
    : (($fontFamily ?? '') === 'Outfit'
        ? ['outfit-latin-6c18d579fd87.woff2']
        : ['outfit-latin-6c18d579fd87.woff2', 'bricolage-grotesque-latin-a79fdb52d4a5.woff2']);
?>
<?php foreach ($criticalFonts as $criticalFont): ?>
<link rel="preload" href="<?= esc(base_url('assets/vendor/fonts/' . $criticalFont)) ?>" as="font" type="font/woff2" crossorigin>
<?php endforeach; ?>
<?php if (!empty($fontIcons)): ?>
<link rel="preload" href="<?= esc(base_url('assets/vendor/fontawesome/webfonts/fa-solid-900.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<?php endif; ?>
<link rel="stylesheet" href="<?= app_asset_url('assets/vendor/fonts/fonts.css') ?>">
