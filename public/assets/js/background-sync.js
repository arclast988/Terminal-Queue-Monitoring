/** Keep already-open pages on the saved background, including disconnected sign-in pages. */
(function (window, document) {
    'use strict';
    if (window.TerminalBackgroundSync) return;
    var configNode = document.getElementById('backgroundSyncConfig');
    if (!configNode) return;
    var config;
    try { config = JSON.parse(configNode.textContent); } catch (error) { return; }
    if (!config.url) return;

    var active = true, timer = null, controller = null, revision = 0;
    var current = config.background || {};
    function signature(data) {
        return JSON.stringify([data.app_bg_mode, data.app_background_image, data.app_bg_slideshow]);
    }
    function onBranding(event) {
        var data = event.detail || {};
        if (data.category && data.category !== 'background' && data.category !== 'all') return;
        if (!data.app_bg_mode && !data.app_background_image && !data.app_bg_slideshow) return;
        current = Object.assign({}, current, data);
        revision++;
    }
    async function refresh() {
        if (!active || document.hidden || controller || typeof window.fetch !== 'function') return;
        var request = new AbortController();
        var startedAt = revision;
        controller = request;
        try {
            var response = await fetch(config.url, {
                signal: request.signal, cache: 'no-store', credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-Silent': 'true' }
            });
            if (!response.ok) return;
            var payload = await response.json();
            // A settings save or socket update received during this read takes precedence.
            if (!active || request.signal.aborted || startedAt !== revision || !payload.success || !payload.data) return;
            if (signature(current) !== signature(payload.data) && typeof window.applyLiveBranding === 'function') {
                window.applyLiveBranding(payload.data);
            }
        } catch (error) {
            // Keep the current artwork during a network outage; the next poll can retry.
        } finally {
            if (controller === request) controller = null;
        }
    }
    function schedule() {
        if (timer !== null) window.clearTimeout(timer);
        timer = null;
        if (!active || document.hidden) return;
        timer = window.setTimeout(function () {
            timer = null;
            if (!window.QueueWS || typeof window.QueueWS.isConnected !== 'function' || !window.QueueWS.isConnected()) refresh();
            schedule();
        }, 20000);
    }
    function suspend() {
        if (timer !== null) window.clearTimeout(timer);
        timer = null;
        if (controller) controller.abort();
        controller = null;
    }
    function onVisibility() {
        if (document.hidden) suspend();
        else { refresh(); schedule(); }
    }
    function onPageHide(event) {
        active = false;
        suspend();
        if (!event.persisted) {
            document.removeEventListener('visibilitychange', onVisibility);
            document.removeEventListener('pttm:branding-applied', onBranding);
            document.removeEventListener('pttm:ws-connected', refresh);
            window.removeEventListener('pagehide', onPageHide);
            window.removeEventListener('pageshow', onPageShow);
        }
    }
    function onPageShow(event) {
        if (!event.persisted || active) return;
        active = true;
        refresh(); schedule();
    }
    window.TerminalBackgroundSync = Object.freeze({ refresh: refresh });
    document.addEventListener('pttm:branding-applied', onBranding);
    document.addEventListener('pttm:ws-connected', refresh);
    document.addEventListener('visibilitychange', onVisibility);
    window.addEventListener('pagehide', onPageHide);
    window.addEventListener('pageshow', onPageShow);
    // Auth pages also need the shared connection; page-specific queue callbacks merge later.
    if (window.QueueWS && typeof window.QueueWS.init === 'function') window.QueueWS.init({});
    refresh(); schedule();
})(window, document);
