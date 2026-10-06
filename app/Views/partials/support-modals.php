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

<!-- System Warning / Alert Modal (Replaces browser alert() so "localhost says" never appears) -->
<div class="modal fade" id="systemAlertModal" tabindex="-1" aria-hidden="true" aria-labelledby="systemAlertTitle" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px; margin: 1.75rem auto;">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.18); overflow: hidden; background: #ffffff;">
            <div class="modal-body text-center" style="padding: 32px 24px 22px;">
                <!-- Icon badge: soft pink/peach rounded square with warning triangle (Picture 1 style) -->
                <div style="width: 60px; height: 60px; border-radius: 16px; background-color: #fee2e2; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px;">
                    <i class="fas fa-triangle-exclamation" style="font-size: 26px; color: #1e293b;"></i>
                </div>
                <h4 id="systemAlertTitle" style="font-weight: 700; font-size: 1.3rem; color: #1e293b; margin-bottom: 12px; letter-spacing: -0.01em;">Notice</h4>
                <p id="systemAlertMessage" style="font-size: 0.95rem; color: #334155; line-height: 1.5; margin: 0 auto; max-width: 290px;"></p>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #f1f5f9; padding: 16px 24px 20px; display: flex; justify-content: center; background: #ffffff;">
                <button type="button" class="btn btn-primary" id="systemAlertOkBtn" data-bs-dismiss="modal" style="min-width: 130px; height: 42px; font-weight: 600; font-size: 14.5px; border-radius: 8px; transition: all 0.15s ease;">
                    OK
                </button>
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

        window.showSystemAlert = function (options) {
            if (typeof options === 'string') {
                options = { message: options };
            }
            options = options || {};
            var modalEl = document.getElementById('systemAlertModal');
            if (!modalEl || !window.bootstrap) {
                if (typeof window.originalAlert === 'function') {
                    window.originalAlert(options.message || '');
                }
                return;
            }

            var titleEl = document.getElementById('systemAlertTitle');
            var msgEl = document.getElementById('systemAlertMessage');
            var okBtn = document.getElementById('systemAlertOkBtn');
            if (titleEl) titleEl.textContent = options.title || 'Notice';
            if (msgEl) msgEl.textContent = options.message || '';
            if (okBtn && options.confirmText) okBtn.textContent = options.confirmText;

            var modal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();

            setTimeout(function() {
                if (okBtn) okBtn.focus();
            }, 120);
        };

        // Intercept native alert() so "localhost says" never appears in the application
        if (typeof window.originalAlert === 'undefined') {
            window.originalAlert = window.alert;
            window.alert = function (message) {
                var customWarning = document.getElementById('systemWarningModal');
                if (customWarning && typeof window.showSystemWarning === 'function') {
                    window.showSystemWarning(String(message), 'Notice');
                    return;
                }
                var modalEl = document.getElementById('systemAlertModal');
                if (modalEl && window.bootstrap) {
                    window.showSystemAlert({ message: String(message) });
                } else if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', function() {
                        window.showSystemAlert({ message: String(message) });
                    }, { once: true });
                } else {
                    console.warn('[System Notice]', message);
                }
            };
        }

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
