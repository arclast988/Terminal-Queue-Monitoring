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
            if (el && el.type === 'file') return;
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
            '<div class="modal fade" id="' + POPUP_ID + '" tabindex="-1" aria-hidden="true">'
            + '<div class="modal-dialog modal-dialog-centered modal-sm">'
            + '<div class="modal-content">'
            + '<div class="modal-body text-center p-4">'
            + '<div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:52px;height:52px;background:#fef9c3;color:#a16207;font-size:22px;">'
            + '<i class="bi bi-exclamation-circle-fill"></i>'
            + '</div>'
            + '<h6 class="fw-bold mb-2">No changes detected</h6>'
            + '<p class="text-muted small mb-3">You didn\'t change anything, so nothing was updated.</p>'
            + '<button type="button" class="btn btn-modern btn-modern-primary btn-sm px-4" data-bs-dismiss="modal">OK</button>'
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
        window.alert('No changes detected — nothing was updated.');
    }

    function attach(form) {
        if (form.hasAttribute('data-nc-attached')) return;
        form.setAttribute('data-nc-attached', 'true');
        takeSnapshot(form);

        // JS-filled modals (fare/discount edit): re-snapshot on every open.
        var modal = form.closest('.modal');
        if (modal) {
            modal.addEventListener('shown.bs.modal', function () { takeSnapshot(form); });
        }

        form.addEventListener('submit', function (e) {
            var before = form.getAttribute('data-nc-snapshot');
            var now = null;
            try {
                now = snapshot(form);
            } catch (err) { return; }
            if (before !== null && before === now) {
                e.preventDefault();
                e.stopPropagation();
                showPopup();
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
