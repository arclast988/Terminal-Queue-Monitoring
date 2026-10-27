<?php
// Installation icons are separate from the display logo so their dimensions stay square.
$browserThemeRole = $browserThemeRole ?? session()->get('role') ?? 'guest';
$browserThemeColor = app_browser_theme_color($browserThemeRole);
?>
<meta name="theme-color" content="<?= esc($browserThemeColor, 'attr') ?>">
<link rel="manifest" data-app-manifest href="<?= esc(base_url('app-manifest.webmanifest').'?'.http_build_query(['role'=>$browserThemeRole,'theme'=>ltrim($browserThemeColor,'#')])) ?>">
<link rel="icon" type="image/png" sizes="32x32" href="<?= esc(base_url('icons/favicon-32.png')) ?>" data-app-icon>
<link rel="icon" type="image/png" sizes="192x192" href="<?= esc(base_url('icons/app-icon-192.png')) ?>" data-app-icon>
<link rel="shortcut icon" type="image/png" href="<?= esc(base_url('icons/favicon-32.png')) ?>" data-app-icon>
<link rel="apple-touch-icon" sizes="180x180" href="<?= esc(base_url('icons/apple-touch-icon-180.png')) ?>" data-app-icon>
