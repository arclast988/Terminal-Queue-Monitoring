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
    // Native page navigation keeps the old page visible while the response loads.
    // The browser owns routing, form submission, history and transition timing.
    window.addEventListener('pageswap', function (event) {
        if (!event.viewTransition) return;
        // A skipped/expired snapshot rejects ready; navigation still succeeds.
        event.viewTransition.ready.catch(function () {});
        if (getMode() !== 'full') event.viewTransition.skipTransition();
    });
    window.addEventListener('pagereveal', function (event) {
        if (!event.viewTransition) return;
        event.viewTransition.ready.catch(function () {});
        if (getMode() !== 'full') {
            event.viewTransition.skipTransition();
            return;
        }
        // Retain for this document so entry animations do not restart after the fade.
        root.setAttribute('data-tq-navigation-reveal', '');
    });
    window.addEventListener('pagehide', unsubscribe);
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) subscribe();
    });
})(window, document);
