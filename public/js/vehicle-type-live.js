/**
 * Vehicle Type Live Colors
 * ─────────────────────────────────────────────────────────────
 * Keeps every vehicle-type color on the page in sync with the admin
 * configuration WITHOUT a reload. Works on pages that do NOT use
 * QueueSync (fares, search, vehicle registry, ...).
 *
 * - Pages WITH QueueSync: skipped, QueueSync already applies colors.
 * - Pages WITHOUT: opens the shared WebSocket (via QueueWS) and applies
 *   `vehicle_type_update` color maps to :root CSS vars. All badges,
 *   icon boxes, chips and fare cards use those vars, so they recolor
 *   instantly. A `vt-colors-updated` DOM event is also dispatched so
 *   pages with custom renderers (e.g. fares grid) can rebuild.
 */
(function (window, document) {
    'use strict';

    function applyColors(colors) {
        if (!colors || typeof colors !== 'object') return;
        var root = document.documentElement;
        var applied = 0;
        Object.keys(colors).forEach(function (slug) {
            var entry = colors[slug];
            var color = (entry && typeof entry === 'object') ? entry.color : entry;
            if (typeof color !== 'string' || !/^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/.test(color)) return;
            var key = String(slug).toLowerCase().replace(/[^a-z0-9_-]+/g, '_').replace(/^[_-]+|[_-]+$/g, '');
            if (!key) return;
            try {
                root.style.setProperty('--vehicle-' + key, color);
                root.style.setProperty('--vehicle-' + key + '-soft', color + '18');
                applied++;
            } catch (e) { /* ignore */ }
        });
        if (applied > 0) {
            try {
                // Refresh static per-option colors used by autocomplete
                // dropdowns so the next open shows fresh colors.
                syncOptionColors(colors);
            } catch (e) { /* ignore */ }
            try {
                document.dispatchEvent(new CustomEvent('vt-colors-updated'));
            } catch (e) { /* ignore */ }
        }
    }

    function syncOptionColors(colors) {
        var opts = document.querySelectorAll('option[data-color]');
        Array.prototype.forEach.call(opts, function (opt) {
            var slug = String(opt.value || '').toLowerCase();
            var entry = colors[slug] || colors[opt.value];
            var color = (entry && typeof entry === 'object') ? entry.color : entry;
            if (typeof color === 'string' && /^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/.test(color)) {
                opt.setAttribute('data-color', color);
            }
        });
    }

    function handleMessage(message) {
        var data = (message && message.data) || message || {};
        if (data.colors) applyColors(data.colors);
        else if (message && message.colors) applyColors(message.colors);
        // Any structural refresh also implies colors may have changed.
        else if (data.action && data.action !== 'passenger_change') {
            try {
                document.dispatchEvent(new CustomEvent('vt-colors-updated'));
            } catch (e) { /* ignore */ }
        }
    }

    function init() {
        // QueueSync pages already handle live colors — do not double-connect.
        if (window.QueueSync) return;
        if (!window.QueueWS) return;
        try {
            window.QueueWS.init({
                onQueueUpdate: handleMessage,
                onVehicleTypeUpdate: handleMessage,
                onFareUpdate: handleMessage,
                onAnnouncementUpdate: handleMessage,
                pollingInterval: 30000
            });
        } catch (e) { /* WebSocket unavailable — static colors remain */ }
    }

    window.VehicleTypeLive = { apply: applyColors };

    // Defer to window load so pages that bring their own QueueSync/QueueWS
    // (loaded after this script) are detected and not double-connected.
    if (document.readyState === 'complete') {
        init();
    } else {
        window.addEventListener('load', init);
    }
})(window, document);
