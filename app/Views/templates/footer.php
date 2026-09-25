    </div>
    <?= view('partials/support-modals') ?>
    <?= view('partials/footer') ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('assets/js/global-loader.js?v=20260920_2') ?>"></script>
    <script src="<?= base_url('assets/js/autocomplete-search.js?v=20260920_2') ?>"></script>
    <script src="<?= base_url('assets/js/auto-dismiss-alerts.js') ?>"></script>
    <script src="<?= base_url('assets/js/no-change-guard.js?v=20260909') ?>"></script>
    <script src="<?= base_url('js/ws-client.js?v=20260920_2') ?>"></script>
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
