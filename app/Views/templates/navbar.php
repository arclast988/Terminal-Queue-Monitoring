<?php
/**
 * Site navigation — thin wrapper kept for backward compatibility.
 * Markup lives in app/Views/partials/header.php and the role nav partials
 * (nav-admin / nav-dispatcher / nav-guest). Styles moved from the old inline
 * <style> block to public/assets/css/navigation.css (token-driven, themed
 * automatically by body.guest-theme / .admin-theme / .staff-theme).
 */
?>
<link rel="stylesheet" href="<?= base_url('assets/css/navigation.css?v=20260921_4') ?>">
<?= view('partials/header') ?>
