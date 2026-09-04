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
    <div class="alert-modern alert-modern-warning fade-in" data-auto-dismiss="5000">
        <i class="bi bi-exclamation-triangle-fill alert-modern-icon"></i>
        <div><?= esc(session()->getFlashdata('warning')) ?></div>
        <button type="button" class="alert-close-btn" data-dismiss-notice aria-label="Dismiss" style="background:none;border:none;color:inherit;opacity:.6;font-size:18px;cursor:pointer;margin-left:auto;padding:0 6px;line-height:1;flex-shrink:0;">&times;</button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('info')): ?>
    <div class="alert-modern alert-modern-info fade-in" data-auto-dismiss="5000">
        <i class="bi bi-info-circle-fill alert-modern-icon"></i>
        <div><?= esc(session()->getFlashdata('info')) ?></div>
        <button type="button" class="alert-close-btn" data-dismiss-notice aria-label="Dismiss" style="background:none;border:none;color:inherit;opacity:.6;font-size:18px;cursor:pointer;margin-left:auto;padding:0 6px;line-height:1;flex-shrink:0;">&times;</button>
    </div>
<?php endif; ?>
<script>
(function () {
    // Self-contained auto-dismiss for flash notices: fades out after a few
    // seconds and supports the × button — independent of footer scripts.
    function fadeOut(el) {
        if (!el || el.dataset.ncDone) return;
        el.dataset.ncDone = 'true';
        el.dataset.dismissInit = 'true';
        el.style.transition = 'opacity .4s ease, transform .4s ease';
        el.style.opacity = '0';
        el.style.transform = 'translateY(-8px)';
        el.style.pointerEvents = 'none';
        setTimeout(function () { if (el.parentNode) el.parentNode.removeChild(el); }, 450);
    }
    document.querySelectorAll('.alert-modern[data-auto-dismiss]:not([data-nc-wired])').forEach(function (el) {
        el.dataset.ncWired = 'true';
        el.dataset.dismissInit = 'true';
        var ms = parseInt(el.getAttribute('data-auto-dismiss'), 10) || 5000;
        var timer = setTimeout(function () { fadeOut(el); }, ms);
        el.addEventListener('mouseenter', function () { clearTimeout(timer); });
        el.addEventListener('mouseleave', function () {
            timer = setTimeout(function () { fadeOut(el); }, 1500);
        });
        var btn = el.querySelector('[data-dismiss-notice]');
        if (btn) btn.addEventListener('click', function (e) {
            e.preventDefault();
            clearTimeout(timer);
            fadeOut(el);
        });
    });
})();
</script>
