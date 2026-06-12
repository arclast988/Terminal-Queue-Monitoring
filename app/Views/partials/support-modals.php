<?php
/**
 * Shared support modals. Include once before </body> (done in templates/footer.php).
 *
 * Usage from any page:
 *   confirmAction({
 *     title:   'Delete vehicle?',
 *     message: 'This action cannot be undone.',
 *     confirmText: 'Delete',
 *     formId:  'delete-form-12'        // form to submit on confirm
 *     // or onConfirm: function () {}  // custom callback instead of a form
 *   });
 */
?>
<div class="modal fade" id="confirmActionModal" tabindex="-1" aria-hidden="true" aria-labelledby="confirmActionTitle">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border: 0; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg);">
            <div class="modal-body text-center p-4">
                <div class="card__icon mx-auto mb-3" style="background: var(--danger-soft); color: var(--danger);">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
                <h5 id="confirmActionTitle" class="mb-1" style="font-family: var(--font-display);">Are you sure?</h5>
                <p id="confirmActionMessage" class="mb-0" style="color: var(--text-muted); font-size: .9rem;">
                    This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center">
                <button type="button" class="btn btn--secondary btn--sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn--danger btn--sm" id="confirmActionConfirmBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var pending = null;

        window.confirmAction = function (options) {
            options = options || {};
            var modalEl = document.getElementById('confirmActionModal');
            if (!modalEl || !window.bootstrap) {
                // Fallback if Bootstrap is unavailable
                if (window.confirm(options.message || 'Are you sure?')) {
                    runPending(options);
                }
                return;
            }

            document.getElementById('confirmActionTitle').textContent =
                options.title || 'Are you sure?';
            document.getElementById('confirmActionMessage').textContent =
                options.message || 'This action cannot be undone.';
            document.getElementById('confirmActionConfirmBtn').textContent =
                options.confirmText || 'Confirm';

            pending = options;
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        };

        function runPending(options) {
            if (!options) return;
            if (typeof options.onConfirm === 'function') {
                options.onConfirm();
            } else if (options.formId) {
                var form = document.getElementById(options.formId);
                if (form) form.submit();
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            var btn = document.getElementById('confirmActionConfirmBtn');
            if (!btn) return;
            btn.addEventListener('click', function () {
                var modalEl = document.getElementById('confirmActionModal');
                if (modalEl && window.bootstrap) {
                    window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                }
                runPending(pending);
                pending = null;
            });
        });
    })();
</script>
