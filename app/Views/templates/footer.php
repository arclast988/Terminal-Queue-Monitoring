    </div>
    <?= view('partials/support-modals') ?>
    <?= view('partials/footer') ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('assets/js/autocomplete-search.js') ?>"></script>
    <script src="<?= base_url('assets/js/auto-dismiss-alerts.js') ?>"></script>

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
