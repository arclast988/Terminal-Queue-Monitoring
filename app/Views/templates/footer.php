    </div>
    <span id="tq-main-content-ready" hidden></span>
    <?= view('partials/support-modals') ?>
    <?= view('partials/footer') ?>
    <script src="<?= app_asset_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/global-loader.js?v=20261003motion2') ?>"></script>
    <script src="<?= base_url('assets/js/autocomplete-search.js?v=' . (defined('FCPATH') && file_exists(FCPATH . 'assets/js/autocomplete-search.js') ? filemtime(FCPATH . 'assets/js/autocomplete-search.js') : '20260927_1')) ?>"></script>
    <script src="<?= base_url('assets/js/auto-dismiss-alerts.js') ?>"></script>
    <script src="<?= base_url('assets/js/no-change-guard.js?v=20260909') ?>"></script>
    <?php if (session()->get('isLoggedIn')): ?>
    <script src="<?= app_asset_url('assets/js/session-guard.js') ?>" data-status-url="<?= base_url('auth/session-status') ?>" data-login-url="<?= base_url('login') ?>"></script>
    <?php endif; ?>
    <script src="<?= app_asset_url('js/ws-client.js') ?>"></script>
    <script src="<?= base_url('js/vehicle-type-live.js?v=20260905') ?>"></script>

    <script>
        // Disable Bootstrap transitions/animations completely
        if (window.bootstrap) {
            const Tooltip = window.bootstrap.Tooltip;
            const Popover = window.bootstrap.Popover;
            if (Tooltip) {
                Tooltip.Default.animation = false;
            }
            if (Popover) {
                Popover.Default.animation = false;
            }
        }

        // Prevent Chrome aria-hidden accessibility warning on modal close
        document.addEventListener('hide.bs.modal', function(e) {
            if (document.activeElement && e.target && e.target.contains(document.activeElement)) {
                document.activeElement.blur();
            }
        });

        // Disable hot reload
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const hotReloadBtn = document.querySelector('[id*="hot-reload"]');
                if (hotReloadBtn && hotReloadBtn.classList.contains('active')) {
                    hotReloadBtn.click();
                }
            }, 100);
        });
    </script>
</body>
</html>
