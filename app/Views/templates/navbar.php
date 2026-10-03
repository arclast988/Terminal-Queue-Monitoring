<?php
/**
 * Site navigation — thin wrapper kept for backward compatibility.
 * Markup lives in app/Views/partials/header.php and the role nav partials
 * (nav-admin / nav-dispatcher / nav-guest). Styles moved from the old inline
 * <style> block to public/assets/css/navigation.css (token-driven, themed
 * automatically by body.guest-theme / .admin-theme / .staff-theme).
 */
?>
<?php if (empty($navigation_assets_loaded)): ?>
<link rel="stylesheet" href="<?= app_asset_url('assets/css/navigation.css') ?>">
<?php endif; ?>
<?php if (empty($interaction_assets_loaded)): ?>
<link rel="stylesheet" href="<?= app_asset_url('assets/css/interaction-motion.css') ?>">
<?php endif; ?>
<?= view('partials/header') ?>
