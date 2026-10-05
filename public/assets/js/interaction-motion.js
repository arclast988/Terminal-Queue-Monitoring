/**
 * Presentation policy only. Never gates input, requests, validation or routing.
 * Hardware values are optional hints, not a device benchmark.
 */
(function (window, document) {
    'use strict';
    if (window.TerminalMotion) return;

    var root = document.documentElement;
    var preference = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : null;
    var cores = window.navigator.hardwareConcurrency;
    var memory = window.navigator.deviceMemory;
    var subscribed = false;
    var constrained = (typeof cores === 'number' && cores > 0 && cores <= 4)
        || (typeof memory === 'number' && memory > 0 && memory <= 4);

    /** @returns {'full'|'lite'|'reduced'} */
    function getMode() {
        return preference && preference.matches ? 'reduced' : (constrained ? 'lite' : 'full');
    }

    function applyPreference() {
        root.setAttribute('data-tq-motion', getMode());
    }

    function applyVisibility() {
        if (document.hidden) root.setAttribute('data-tq-page-hidden', '');
        else root.removeAttribute('data-tq-page-hidden');
    }

    // Keep decorative photos on the same timeline across pages and refreshes.
    // Storage is optional; private/blocked storage must never affect navigation.
    function applyBackgroundPhase() {
        try {
            var now = Date.now();
            var epoch = Number(window.sessionStorage.getItem('tq-background-epoch'));
            if (!Number.isFinite(epoch) || epoch <= 0 || epoch > now) {
                epoch = now;
                window.sessionStorage.setItem('tq-background-epoch', String(epoch));
            }
            root.style.setProperty('--tq-background-delay', '-' + ((now - epoch) / 1000) + 's');
        } catch (error) { /* Static backgrounds and input remain available. */ }
    }

    function subscribe() {
        applyPreference();
        applyVisibility();
        if (subscribed) return;
        subscribed = true;
        document.addEventListener('visibilitychange', applyVisibility);
        if (!preference) return;
        if (preference.addEventListener) preference.addEventListener('change', applyPreference);
        else if (preference.addListener) preference.addListener(applyPreference);
    }

    function unsubscribe() {
        subscribed = false;
        document.removeEventListener('visibilitychange', applyVisibility);
        if (!preference) return;
        if (preference.removeEventListener) preference.removeEventListener('change', applyPreference);
        else if (preference.removeListener) preference.removeListener(applyPreference);
    }

    window.TerminalMotion = Object.freeze({ getMode: getMode });
    subscribe();
    applyBackgroundPhase();
    // Reload timing is not exposed early by every browser. Remember only the
    // public path (never query parameters) so revisits also render fully painted.
    try {
        var path = window.location.pathname;
        if (path) {
            if (window.sessionStorage.getItem('tq-last-document-path') === path) {
                root.setAttribute('data-tq-navigation-reveal', '');
            }
            window.sessionStorage.setItem('tq-last-document-path', path);
        }
    } catch (error) { /* Optional presentation state only. */ }
    // Avoid another entry fade on browsers without native page snapshots too.
    // Set this in the head, before the destination content can be painted.
    try {
        var navigation = window.performance && /** @type {PerformanceNavigationTiming|undefined} */ (window.performance.getEntriesByType('navigation')[0]);
        // Some engines publish the modern entry only after load. The legacy
        // navigation type is already available while parsing the head.
        var legacyType = window.performance && window.performance.navigation && window.performance.navigation.type;
        if ((navigation && (navigation.type === 'reload' || navigation.type === 'back_forward'))
            || legacyType === 1 || legacyType === 2
            || (document.referrer && new URL(document.referrer).origin === window.location.origin)) {
            root.setAttribute('data-tq-navigation-reveal', '');
        }
    } catch (error) { /* Older browsers may omit navigation timing/referrers. */ }
    // Native page navigation keeps the old page visible while the response loads.
    // The browser owns routing, form submission, history and transition timing.
    window.addEventListener('pageswap', function (event) {
        if (!event.viewTransition) return;
        // A skipped/expired snapshot rejects ready; navigation still succeeds.
        event.viewTransition.ready.catch(function () {});
    });
    window.addEventListener('pagereveal', function (event) {
        if (!event.viewTransition) return;
        event.viewTransition.ready.catch(function () {});
        // Retain for this document so entry animations do not restart after the fade.
        root.setAttribute('data-tq-navigation-reveal', '');
    });
    window.addEventListener('pagehide', unsubscribe);
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) subscribe();
    });
})(window, document);
