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
    var constrained = (typeof cores === 'number' && cores > 0 && cores <= 4)
        || (typeof memory === 'number' && memory > 0 && memory <= 4);

    /** @returns {'full'|'lite'|'reduced'} */
    function getMode() {
        return preference && preference.matches ? 'reduced' : (constrained ? 'lite' : 'full');
    }

    function applyPreference() {
        root.setAttribute('data-tq-motion', getMode());
    }

    function subscribe() {
        applyPreference();
        if (!preference) return;
        if (preference.addEventListener) preference.addEventListener('change', applyPreference);
        else if (preference.addListener) preference.addListener(applyPreference);
    }

    function unsubscribe() {
        if (!preference) return;
        if (preference.removeEventListener) preference.removeEventListener('change', applyPreference);
        else if (preference.removeListener) preference.removeListener(applyPreference);
    }

    window.TerminalMotion = Object.freeze({ getMode: getMode });
    subscribe();
    window.addEventListener('pagehide', unsubscribe);
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) subscribe();
    });
})(window, document);
