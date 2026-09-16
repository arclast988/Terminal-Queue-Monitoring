/**
 * No-Change Guard
 * ─────────────────────────────────────────────────────────────
 * Prevents "empty updates" on edit forms: if the user opens an edit
 * form and submits without changing anything, a popup explains that
 * nothing was updated and the submit is blocked (no wasted write,
 * audit log, or realtime broadcast).
 *
 * Usage: add `data-no-change-guard` to any edit/update <form>.
 *   <form action=".../update/1" method="post" data-no-change-guard>
 *
 * - Snapshot is taken on load AND every time a wrapping Bootstrap
 *   modal is shown (covers JS-populated modals like fare/discount edit).
 * - Fields with `data-no-change-ignore` and file inputs are skipped.
 * - Multi-value fields (checkbox groups) are compared order-insensitively.
 */
(function () {
    'use strict';

    var POPUP_ID = 'noChangeGuardModal';

    function snapshot(form) {
        var data = {};
        var fd = new FormData(form);
        fd.forEach(function (value, key) {
            var el = form.querySelector('[name="' + key + '"]');
            if (el && el.hasAttribute && el.hasAttribute('data-no-change-ignore')) return;
            if (el && el.type === 'file') {
                if (!Object.prototype.hasOwnProperty.call(data, key)) data[key] = [];
                if (el.files && el.files.length > 0) {
                    for (var i = 0; i < el.files.length; i++) {
                        data[key].push(el.files[i].name + ':' + el.files[i].size);
                    }
                } else {
                    data[key].push('');
                }
                return;
            }
            if (typeof value !== 'string') return;
            if (!Object.prototype.hasOwnProperty.call(data, key)) data[key] = [];
            data[key].push(value);
        });
        Object.keys(data).forEach(function (key) { data[key].sort(); });
        return JSON.stringify(data);
    }

    function takeSnapshot(form) {
        try {
            form.setAttribute('data-nc-snapshot', snapshot(form));
        } catch (e) { /* ignore */ }
    }

    function ensurePopup() {
        var existing = document.getElementById(POPUP_ID);
        if (existing) return existing;
        var wrapper = document.createElement('div');
        wrapper.innerHTML =
            '<div class="modal fade" id="' + POPUP_ID + '" tabindex="-1" aria-hidden="true" style="z-index: 100060 !important;">'
            + '<div class="modal-dialog modal-dialog-centered modal-sm" style="max-width: 360px;">'
            + '<div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">'
            + '<div class="modal-body text-center p-4">'
            + '<div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 54px; height: 54px; background: #fef3c7; color: #d97706; font-size: 24px; box-shadow: 0 4px 12px rgba(217,119,6,0.15);">'
            + '<i class="bi bi-exclamation-triangle-fill"></i>'
            + '</div>'
            + '<h6 class="fw-bold mb-1 text-dark" style="font-size: 16px;">No Changes Detected</h6>'
            + '<p class="text-muted small mb-4" style="line-height: 1.5; font-size: 13px;">You didn\'t change anything, so nothing was updated.</p>'
            + '<button type="button" class="btn btn-warning btn-sm px-4 fw-semibold text-dark shadow-sm" data-bs-dismiss="modal" style="border-radius: 8px; min-width: 100px;">Got it</button>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '</div>';
        var modal = wrapper.firstElementChild;
        document.body.appendChild(modal);
        return modal;
    }

    function showPopup() {
        var modal = ensurePopup();
        try {
            if (window.bootstrap && window.bootstrap.Modal) {
                window.bootstrap.Modal.getOrCreateInstance(modal).show();
                return;
            }
        } catch (e) { /* fall through */ }
        if (typeof window.showSystemAlert === 'function') {
            window.showSystemAlert({
                title: 'No Changes Detected',
                message: 'No changes detected — nothing was updated.',
                variant: 'warning'
            });
            return;
        }
    }

    function showInlineNotice(form) {
        var modalBody = form.querySelector('.modal-body');
        var target = modalBody || form;
        var notice = document.createElement('div');
        notice.className = 'nc-inline-notice alert alert-warning alert-dismissible fade show d-flex align-items-center gap-2 mt-1 mb-3 shadow-sm';
        notice.setAttribute('role', 'alert');
        notice.style.cssText = 'background: #fef3c7; color: #92400e; border: 1px solid #fde68a; border-left: 4px solid #f59e0b !important; border-radius: 10px; padding: 10px 14px; font-size: 13px; font-weight: 500; z-index: 10; position: relative;';
        notice.innerHTML = '<i class="bi bi-exclamation-triangle-fill flex-shrink-0" style="font-size: 16px; color: #d97706;"></i>'
            + '<div class="flex-grow-1"><strong>No changes detected</strong> — nothing was updated.</div>'
            + '<button type="button" class="btn-close" aria-label="Close" style="padding: 10px; font-size: 10px;"></button>';
        var closeBtn = notice.querySelector('.btn-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function () { removeInlineNotice(notice); });
        }
        target.prepend(notice);
        if (modalBody) {
            modalBody.scrollTop = 0;
        } else {
            try {
                notice.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            } catch (e) { /* ignore */ }
        }
        // Auto-fade after a few seconds so it never lingers.
        notice._ncTimer = setTimeout(function () { removeInlineNotice(notice); }, 6000);
    }

    function removeInlineNotice(notice) {
        if (!notice || notice._ncGone) return;
        notice._ncGone = true;
        if (notice._ncTimer) clearTimeout(notice._ncTimer);
        notice.style.transition = 'opacity .35s ease';
        notice.style.opacity = '0';
        setTimeout(function () { if (notice.parentNode) notice.parentNode.removeChild(notice); }, 380);
    }

    function attach(form) {
        if (form.hasAttribute('data-nc-attached')) return;
        form.setAttribute('data-nc-attached', 'true');
        takeSnapshot(form);

        // JS-filled modals (fare/discount edit): re-snapshot on every open.
        var modal = form.closest('.modal');
        if (modal) {
            modal.addEventListener('shown.bs.modal', function () { takeSnapshot(form); });
            // Never show a stale notice: clear it whenever the modal closes.
            modal.addEventListener('hidden.bs.modal', function () {
                var old = form.querySelector('.nc-inline-notice');
                if (old) removeInlineNotice(old);
            });
        }

        // Clear any inline notice as soon as the user starts editing again.
        ['input', 'change'].forEach(function (evt) {
            form.addEventListener(evt, function () {
                var old = form.querySelector('.nc-inline-notice');
                if (old) removeInlineNotice(old);
            });
        });

        form.addEventListener('submit', function (e) {
            var old = form.querySelector('.nc-inline-notice');
            if (old) removeInlineNotice(old);

            // If user selected any file(s), changes are present — allow submission!
            var hasFileSelected = false;
            form.querySelectorAll('input[type="file"]').forEach(function (fi) {
                if (fi.files && fi.files.length > 0) {
                    hasFileSelected = true;
                }
            });
            if (hasFileSelected) {
                return;
            }

            var before = form.getAttribute('data-nc-snapshot');
            var now = null;
            try {
                now = snapshot(form);
            } catch (err) { return; }
            if (before !== null && before === now) {
                e.preventDefault();
                e.stopPropagation();
                // Forms inside a modal get an inline banner: opening a second
                // Bootstrap modal on top stacks backdrops and freezes the page.
                if (form.closest('.modal')) {
                    showInlineNotice(form);
                } else {
                    showPopup();
                }
            }
        });
    }

    function init() {
        document.querySelectorAll('form[data-no-change-guard]').forEach(attach);
    }

    if (document.readyState === 'complete') {
        init();
    } else {
        window.addEventListener('load', init);
    }
})();
