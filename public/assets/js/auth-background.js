/** Decorative backgrounds only; authentication and verification own their handlers. */
(function (window, document) {
    'use strict';
    if (window.TerminalAuthBackground) return;

    /** @type {(() => void) | null} */
    var unmount = null;

    /** @param {import('../../../types/interaction-contracts').AuthBackgroundConfig} config */
    function mount(config) {
        if (unmount) unmount();
        var container = document.getElementById('authBgSlideshow');
        if (!container) return function () {};
        var root = container;
        var layers = root.querySelectorAll('img');
        if (layers.length < 2) return function () {};
        var slides = config.slides.slice();
        var defaults = config.defaults;
        var preference = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : null;
        var current = 0;
        var index = 0;
        var generation = 0;
        var active = true;
        /** @type {number | null} */
        var timer = null;
        /** @type {number | null} */
        var frame = null;
        var frameUsesRAF = false;
        /** @type {HTMLImageElement | null} */
        var incomingImage = null;

        function canAnimate() {
            return active && !document.hidden && !(preference && preference.matches)
                && (!window.TerminalMotion || window.TerminalMotion.getMode() === 'full');
        }

        function cancelPending() {
            generation++;
            if (timer !== null) window.clearTimeout(timer);
            timer = null;
            if (frame !== null) {
                if (frameUsesRAF) window.cancelAnimationFrame(frame);
                else window.clearTimeout(frame);
            }
            frame = null;
            if (incomingImage) {
                incomingImage.onload = incomingImage.onerror = null;
                incomingImage.removeAttribute('src');
                incomingImage = null;
            }
        }

        function schedule() {
            cancelPending();
            if (canAnimate() && slides.length > 1) timer = window.setTimeout(advance, 6000);
        }

        function advance() {
            timer = null;
            if (!canAnimate()) return;
            var cycle = generation;
            var nextIndex = (index + 1) % slides.length;
            var incoming = layers[1 - current];
            var outgoing = layers[current];
            var finished = false;
            incomingImage = incoming;
            function loaded() {
                if (finished || cycle !== generation) return;
                finished = true;
                incoming.onload = incoming.onerror = null;
                incomingImage = null;
                if (!canAnimate()) { schedule(); return; }
                function commit() {
                    frame = null;
                    if (cycle !== generation || !canAnimate()) return;
                    incoming.classList.add('is-active');
                    outgoing.classList.remove('is-active');
                    current = 1 - current;
                    index = nextIndex;
                    schedule();
                }
                frameUsesRAF = typeof window.requestAnimationFrame === 'function'
                    && typeof window.cancelAnimationFrame === 'function';
                frame = frameUsesRAF ? window.requestAnimationFrame(commit) : window.setTimeout(commit, 0);
            }
            function failed() {
                if (finished || cycle !== generation) return;
                finished = true;
                incoming.onload = incoming.onerror = null;
                incomingImage = null;
                index = nextIndex;
                schedule();
            }
            incoming.onload = loaded;
            incoming.onerror = failed;
            incoming.src = slides[nextIndex];
            if (incoming.complete && incoming.naturalWidth > 0) loaded();
        }

        /** @param {Event} event */
        function onBranding(event) {
            var data = /** @type {CustomEvent<import('../../../types/interaction-contracts').AuthBackgroundUpdate>} */ (event).detail || {};
            if (data.category && data.category !== 'background' && data.category !== 'all') return;
            if (!data.app_bg_mode && !data.app_bg_slideshow && !data.app_background_image) return;
            var single = data.app_bg_mode === 'single';
            var urls = single ? [data.app_background_image || defaults[0]]
                : (Array.isArray(data.app_bg_slideshow) && data.app_bg_slideshow.length ? data.app_bg_slideshow : defaults);
            var valid = urls.filter(function (url) { return typeof url === 'string' && url.length > 0; });
            if (!valid.length) return;
            cancelPending();
            slides = valid;
            current = index = 0;
            layers[0].src = slides[0];
            layers[0].classList.add('is-active');
            layers[1].classList.remove('is-active');
            layers[1].removeAttribute('src');
            root.style.opacity = single ? '0.28' : '0.22';
            schedule();
        }

        function subscribe() {
            document.addEventListener('visibilitychange', schedule);
            document.addEventListener('pttm:branding-applied', onBranding);
            if (preference) {
                if (preference.addEventListener) preference.addEventListener('change', schedule);
                else if (preference.addListener) preference.addListener(schedule);
            }
        }
        function unsubscribe() {
            document.removeEventListener('visibilitychange', schedule);
            document.removeEventListener('pttm:branding-applied', onBranding);
            if (preference) {
                if (preference.removeEventListener) preference.removeEventListener('change', schedule);
                else if (preference.removeListener) preference.removeListener(schedule);
            }
        }
        function dispose() {
            active = false;
            cancelPending();
            unsubscribe();
            window.removeEventListener('pagehide', onPageHide);
            window.removeEventListener('pageshow', onPageShow);
            if (unmount === dispose) unmount = null;
        }
        /** @param {PageTransitionEvent} event */
        function onPageHide(event) {
            if (!event.persisted) { dispose(); return; }
            active = false;
            cancelPending();
            unsubscribe();
        }
        /** @param {PageTransitionEvent} event */
        function onPageShow(event) {
            if (!event.persisted || active) return;
            active = true;
            subscribe();
            schedule();
        }
        unmount = dispose;
        subscribe();
        window.addEventListener('pagehide', onPageHide);
        window.addEventListener('pageshow', onPageShow);
        schedule();
        return dispose;
    }
    window.TerminalAuthBackground = Object.freeze({ mount: mount });
})(window, document);
