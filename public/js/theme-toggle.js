/**
 * Theme Toggle / Adaptive Theme — Palompon Transit
 * ───────────────────────────────────────────────────────────────
 * PRESENTATIONAL ONLY. No data, no endpoints, no system behavior.
 *
 *  • Operator pages (admin/staff): persistent light/dark toggle.
 *    Default dark. Stored in localStorage['tqTheme']. A pre-paint
 *    inline snippet in header.php sets the attribute first to avoid
 *    a flash; this file wires the navbar button(s) and keeps in sync.
 *
 *  • Public board (<body class="tq-public">): ADAPTIVE — high-contrast
 *    light "daylight" theme 06:00–17:59, oceanic dark otherwise.
 *    Re-checks every 10 minutes. Honors an optional manual override
 *    in localStorage['tqPublicOverride'] ('light' | 'dark').
 */
(function (w, d) {
    'use strict';

    var KEY = 'tqTheme';            // operator persisted choice
    var OVR = 'tqPublicOverride';   // public manual override (optional)
    var root = d.documentElement;

    function readLS(k) { try { return w.localStorage.getItem(k); } catch (e) { return null; } }
    function writeLS(k, v) { try { w.localStorage.setItem(k, v); } catch (e) {} }

    function setTheme(t) { root.setAttribute('data-theme', t === 'light' ? 'light' : 'dark'); }
    function current() { return root.getAttribute('data-theme') || 'dark'; }

    function isPublic() { return !!(d.body && d.body.classList.contains('tq-public')); }

    /* ── Adaptive day/night (public board) ── */
    function dayNight() {
        var h = new Date().getHours();
        return (h >= 6 && h < 18) ? 'light' : 'dark';
    }
    function applyAdaptive() {
        var override = readLS(OVR);
        setTheme(override === 'light' || override === 'dark' ? override : dayNight());
    }

    /* ── Operator toggle ── */
    function syncButtons() {
        var label = current() === 'dark' ? 'Switch to light mode' : 'Switch to dark mode';
        var btns = d.querySelectorAll('.tq-theme-toggle');
        for (var i = 0; i < btns.length; i++) {
            btns[i].setAttribute('aria-label', label);
            btns[i].title = label;
        }
    }
    function toggle() {
        var next = current() === 'dark' ? 'light' : 'dark';
        setTheme(next);
        writeLS(KEY, next);
        syncButtons();
    }

    function init() {
        if (isPublic()) {
            applyAdaptive();
            w.setInterval(applyAdaptive, 10 * 60 * 1000);
            return;
        }
        // operator: reflect stored preference (boot snippet already applied it)
        setTheme(readLS(KEY) || 'dark');
        var btns = d.querySelectorAll('.tq-theme-toggle');
        for (var i = 0; i < btns.length; i++) {
            btns[i].addEventListener('click', toggle);
        }
        syncButtons();
    }

    w.TQTheme = { toggle: toggle, set: setTheme, applyAdaptive: applyAdaptive, init: init };

    if (d.readyState === 'loading') {
        d.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})(window, document);
