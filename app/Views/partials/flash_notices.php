<?php
/**
 * Shared flash notices for update forms.
 *
 * Renders `warning` and `info` flashdata (e.g. "No changes detected").
 * Success / error / validation-errors banners stay in their own views,
 * so including this partial never duplicates existing messages.
 */
?>
<?php if (session()->getFlashdata('warning')): ?>
    <div class="alert-modern alert-modern-warning fade-in">
        <i class="bi bi-exclamation-triangle-fill alert-modern-icon"></i>
        <div><?= esc(session()->getFlashdata('warning')) ?></div>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('info')): ?>
    <div class="alert-modern alert-modern-info fade-in">
        <i class="bi bi-info-circle-fill alert-modern-icon"></i>
        <div><?= esc(session()->getFlashdata('info')) ?></div>
    </div>
<?php endif; ?>
