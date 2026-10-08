<?php if (($fontFamily ?? 'Outfit') === 'Outfit'): ?>
<!-- Load the visible body font before layout; the filename matches the CSS URL. -->
<link rel="preload" href="<?= base_url('assets/vendor/fonts/outfit-latin-6c18d579fd87.woff2') ?>" as="font" type="font/woff2" crossorigin>
<?php endif; ?>
<!-- Authentication uses Inter on demand, without unused font preload requests. -->
<link rel="stylesheet" href="<?= app_asset_url('assets/vendor/fonts/fonts.css') ?>">
