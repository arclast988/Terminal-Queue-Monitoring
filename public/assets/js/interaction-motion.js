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
    var viewportTimer = 0;
    var viewportMoving = false;
    var viewport = window.visualViewport;
    var backgroundMode = 'none';
    /** @type {readonly string[]} */
    var backgroundSlides = [];
    /** @type {{fingerprint: string, epoch: number}|null} */
    var backgroundTimeline = null;
    var constrained = (typeof cores === 'number' && cores > 0 && cores <= 4)
        || (typeof memory === 'number' && memory > 0 && memory <= 4);

    /** @returns {'full'|'lite'|'reduced'} */
    function getMode() {
        return preference && preference.matches ? 'reduced' : (constrained ? 'lite' : 'full');
    }

    function applyPreference() {
        root.setAttribute('data-tq-motion', getMode());
        realignBackground();
    }

    function applyVisibility() {
        if (document.hidden) root.setAttribute('data-tq-page-hidden', '');
        else root.removeAttribute('data-tq-page-hidden');
        if (document.hidden) stopViewportMotion();
        else realignBackground();
    }

    function stopViewportMotion() {
        clearTimeout(viewportTimer);
        viewportTimer = 0;
        viewportMoving = false;
        root.removeAttribute('data-tq-viewport-moving');
        realignBackground();
    }

    // Decorative photos can wait while scrolling, pinch zoom or resizing uses
    // the rendering budget. Write once per gesture, never measure layout here.
    function markViewportMotion() {
        if (document.hidden) return;
        if (!viewportMoving) {
            viewportMoving = true;
            root.setAttribute('data-tq-viewport-moving', '');
        }
        clearTimeout(viewportTimer);
        viewportTimer = setTimeout(stopViewportMotion, 250);
    }

    /** @param {readonly string[]} slides */
    function getBackgroundPhase(slides) {
        var now = Date.now(), fingerprint = JSON.stringify(slides);
        var storageKey = 'tq-background-timeline:' + fingerprint;
        var timeline = backgroundTimeline;
        // Local storage shares the epoch across tabs; session storage also works
        // when persistent storage is blocked. Only a changed picture list resets it.
        [function () { return window.localStorage; }, function () { return window.sessionStorage; }].some(function (getStorage) {
            try {
                var saved = JSON.parse(getStorage().getItem(storageKey) || 'null');
                if (saved && saved.fingerprint === fingerprint && Number.isFinite(saved.epoch) && saved.epoch > 0 && saved.epoch <= now) {
                    timeline = saved; return true;
                }
            } catch (error) { /* Storage is optional presentation state. */ }
            return false;
        });
        if (!timeline || timeline.fingerprint !== fingerprint || timeline.epoch > now) timeline = {fingerprint: fingerprint, epoch: now};
        backgroundTimeline = timeline;
        [function () { return window.localStorage; }, function () { return window.sessionStorage; }].forEach(function (getStorage) {
            try { getStorage().setItem(storageKey, JSON.stringify(timeline)); } catch (error) {}
        });
        var elapsed = Math.max(0, now - timeline.epoch);
        return {elapsed: elapsed, index: slides.length ? Math.floor(elapsed / 6000) % slides.length : 0, remaining: 6000 - elapsed % 6000};
    }

    // Rejoin the shared clock after a modal, gesture, or hidden tab paused CSS.
    // No frame loop or extra recurring timer is needed.
    function realignBackground() {
        if (backgroundMode !== 'slideshow' || backgroundSlides.length < 2 || getMode() !== 'full' || document.hidden) return;
        var phase = getBackgroundPhase(backgroundSlides);
        if (typeof document.getAnimations !== 'function') return;
        document.getAnimations().forEach(function (animation) {
            if (!('animationName' in animation) || !/BgSlideshow(?:Auth)?(?:Live)?$/.test(String(animation.animationName)) || !animation.effect) return;
            var delay = Number(animation.effect.getTiming().delay) || 0;
            animation.currentTime = Math.max(0, phase.elapsed + delay);
        });
    }

    /** @param {string} mode @param {readonly string[]} slides */
    function syncBackground(mode, slides) {
        backgroundMode = mode;
        backgroundSlides = slides;
        if (mode !== 'slideshow' || slides.length < 2) return;
        var phase = getBackgroundPhase(slides);
        root.style.setProperty('--tq-background-photo', 'url(' + JSON.stringify(slides[getMode() === 'full' ? phase.index : 0]) + ')');
        root.style.setProperty('--tq-background-delay', '-' + (phase.elapsed / 1000) + 's');
        realignBackground();
    }

    /** @param {Event} event */
    function onBackgroundBranding(event) {
        var data = /** @type {CustomEvent<import('../../../types/interaction-contracts').AuthBackgroundUpdate>} */ (event).detail || {};
        if (data.category && data.category !== 'background' && data.category !== 'all') return;
        if (!data.app_bg_mode && !data.app_bg_slideshow) return;
        syncBackground(data.app_bg_mode || backgroundMode, data.app_bg_slideshow || backgroundSlides);
    }

    function subscribe() {
        applyPreference();
        applyVisibility();
        if (subscribed) return;
        subscribed = true;
        document.addEventListener('visibilitychange', applyVisibility);
        document.addEventListener('pttm:branding-applied', onBackgroundBranding);
        document.addEventListener('hidden.bs.modal', realignBackground);
        ['scroll', 'wheel', 'touchmove', 'resize'].forEach(function (name) {
            window.addEventListener(name, markViewportMotion, { passive: true });
        });
        if (viewport) {
            viewport.addEventListener('resize', markViewportMotion, { passive: true });
            viewport.addEventListener('scroll', markViewportMotion, { passive: true });
        }
        if (!preference) return;
        if (preference.addEventListener) preference.addEventListener('change', applyPreference);
        else if (preference.addListener) preference.addListener(applyPreference);
    }

    function unsubscribe() {
        subscribed = false;
        document.removeEventListener('visibilitychange', applyVisibility);
        document.removeEventListener('pttm:branding-applied', onBackgroundBranding);
        document.removeEventListener('hidden.bs.modal', realignBackground);
        ['scroll', 'wheel', 'touchmove', 'resize'].forEach(function (name) {
            window.removeEventListener(name, markViewportMotion);
        });
        if (viewport) {
            viewport.removeEventListener('resize', markViewportMotion);
            viewport.removeEventListener('scroll', markViewportMotion);
        }
        stopViewportMotion();
        if (!preference) return;
        if (preference.removeEventListener) preference.removeEventListener('change', applyPreference);
        else if (preference.removeListener) preference.removeListener(applyPreference);
    }

    window.TerminalMotion = Object.freeze({ getMode: getMode, getBackgroundPhase: getBackgroundPhase, syncBackground: syncBackground });
    subscribe();
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', realignBackground, {once: true});
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
    // Internal navigation gets one opaque reveal. Browsers without a usable
    // snapshot render fully painted, with nested entrances disabled before paint.
    try {
        var navigation = window.performance && /** @type {PerformanceNavigationTiming|undefined} */ (window.performance.getEntriesByType('navigation')[0]);
        // Some engines publish the modern entry only after load. The legacy
        // navigation type is already available while parsing the head.
        var legacyType = window.performance && window.performance.navigation && window.performance.navigation.type;
        if ((navigation && (navigation.type === 'reload' || navigation.type === 'back_forward'))
            || legacyType === 1 || legacyType === 2) {
            root.setAttribute('data-tq-navigation-reveal', '');
        } else if (document.referrer && new URL(document.referrer).origin === window.location.origin) {
            root.setAttribute('data-tq-navigation-reveal', '');
            root.setAttribute('data-tq-navigation-fallback', '');
        }
    } catch (error) { /* Older browsers may omit navigation timing/referrers. */ }
    // Native page navigation keeps the old page visible while the response loads.
    // The browser owns routing, form submission, history and transition timing.
    /** @param {ViewTransition} transition */
    function observeTransition(transition) {
        // A fast second navigation or expired snapshot may skip an animation.
        // Observe its lifecycle promises without holding up the destination.
        [transition.ready, transition.finished, transition.updateCallbackDone].forEach(function (promise) {
            if (promise) promise.catch(function () {});
        });
    }
    window.addEventListener('pageswap', function (event) {
        if (!event.viewTransition) return;
        observeTransition(event.viewTransition);
    });
    window.addEventListener('pagereveal', function (event) {
        if (!event.viewTransition) return;
        observeTransition(event.viewTransition);
        root.removeAttribute('data-tq-navigation-fallback');
        // Retain for this document so entry animations do not restart after arrival.
        root.setAttribute('data-tq-navigation-reveal', '');
    });
    window.addEventListener('pagehide', unsubscribe);
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) subscribe();
    });
})(window, document);
