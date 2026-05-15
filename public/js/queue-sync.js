/**
 * Queue Sync Module
 * ─────────────────────────────────────────────────────────────
 * Consolidates all real-time synchronization logic that was
 * previously duplicated across admin queue, staff queue,
 * admin dashboard, history, and schedules pages.
 *
 * Features:
 *   • JSON API polling with configurable interval
 *   • Passenger-count DOM updates (both id-based and class-based)
 *   • Automatic full-refresh when items are added/removed
 *   • Visibility-aware: pauses polling when tab is hidden
 *   • Modal re-mounting after table refresh
 *   • Integrates with QueueWS for instant WebSocket updates
 *   • ALWAYS polls as fallback — WebSocket is the fast-path
 *
 * Usage:
 *   QueueSync.init({
 *       apiUrl:           '/api/queue-status',
 *       pollInterval:     3000,
 *       refreshUrl:       '/admin/queue',
 *       tableSelector:    '#adminQueueTable tbody',
 *       modalSelector:    '[id^="confirmDepartModal"]',
 *       extraRefresh:     function(newDoc) { ... },  // optional
 *       onlyWS:           false,                     // ignored — polling always runs
 *       customRefresh:    function() { ... },         // optional override
 *       customWSHandler:  function(msg) { ... }       // optional override
 *   });
 */
(function(window) {
    'use strict';

    var _config = null;
    var _pollTimer = null;
    var _lastIds = {};
    var _paused = false;

    /* ── Passenger UI helpers ── */

    function getCountSpan(id) {
        var el = document.getElementById('passenger-count-' + id);
        if (!el) {
            var container = document.getElementById('passenger-controls-' + id);
            if (container) el = container.querySelector('.passenger-count');
        }
        return el;
    }

    function getBadge(id) {
        var el = document.getElementById('full-badge-' + id);
        if (!el) {
            var container = document.getElementById('passenger-controls-' + id);
            if (container) {
                el = container.parentElement
                    ? container.parentElement.querySelector('.full-badge')
                    : null;
            }
        }
        return el;
    }

    function updatePassengerUI(id, count, capacity) {
        var span = getCountSpan(id);
        var badge = getBadge(id);

        if (span) {
            var text = count + ' / ' + capacity;
            if (span.textContent.trim() !== text) {
                span.textContent = text;
            }
            span.classList.toggle('text-danger', count >= capacity);
        }
        if (badge) {
            badge.style.display = (count >= capacity) ? 'inline-block' : 'none';
        }
    }

    /* ── Generic AJAX page-refresh ── */

    function ajaxRefresh() {
        if (!_config || !_config.refreshUrl) return;

        fetch(_config.refreshUrl, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.text(); })
        .then(function(html) {
            var parser = new DOMParser();
            var newDoc = parser.parseFromString(html, 'text/html');

            // Replace table body
            if (_config.tableSelector) {
                var newTbody = newDoc.querySelector(_config.tableSelector);
                var curTbody = document.querySelector(_config.tableSelector);
                if (newTbody && curTbody) {
                    curTbody.innerHTML = newTbody.innerHTML;
                }
            }

            // Re-mount modals (depart confirmation, etc.)
            if (_config.modalSelector) {
                document.querySelectorAll(_config.modalSelector).forEach(function(m) {
                    var inst = (typeof bootstrap !== 'undefined') ? bootstrap.Modal.getInstance(m) : null;
                    if (inst) inst.hide();
                    m.remove();
                });
                newDoc.querySelectorAll(_config.modalSelector).forEach(function(m) {
                    document.body.appendChild(m.cloneNode(true));
                });
            }

            // Optional extra refresh logic (stat cards, activity logs, etc.)
            if (_config.extraRefresh) {
                _config.extraRefresh(newDoc);
            }
        })
        .catch(function(err) {
            console.error('[QueueSync] Refresh error:', err);
        });
    }

    /* ── JSON API polling ── */

    function pollAPI() {
        if (!_config || !_config.apiUrl) return;

        fetch(_config.apiUrl, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(json) {
            if (!json.success || !json.queue) return;

            var needsRefresh = false;
            var currentIds = {};

            json.queue.forEach(function(item) {
                currentIds[item.id] = true;

                var span = getCountSpan(item.id);
                if (!span) {
                    // New item we don't have in DOM
                    if (_lastIds[item.id] === undefined) {
                        needsRefresh = true;
                    }
                    return;
                }

                updatePassengerUI(item.id, item.current_passengers, item.capacity);
            });

            // Detect removed items
            for (var oldId in _lastIds) {
                if (!currentIds[oldId]) {
                    needsRefresh = true;
                    break;
                }
            }

            _lastIds = currentIds;

            if (needsRefresh) {
                ajaxRefresh();
            }
        })
        .catch(function() { /* silent fail on poll */ });
    }

    /* ── Unified poll/refresh function ── */
    // Determines which refresh strategy to use based on config
    function doRefresh() {
        if (_config && _config.customRefresh) {
            _config.customRefresh();
        } else if (_config && _config.apiUrl) {
            pollAPI();
        } else {
            ajaxRefresh();
        }
    }

    function startPolling() {
        if (_pollTimer) return;
        var interval = _config ? (_config.pollInterval || 3000) : 3000;
        _pollTimer = setInterval(function() {
            if (!_paused) doRefresh();
        }, interval);
    }

    function stopPolling() {
        if (_pollTimer) {
            clearInterval(_pollTimer);
            _pollTimer = null;
        }
    }

    // Reset the poll timer (called after a WS message to avoid
    // double-fetching — we just got fresh data via WS)
    function resetPollTimer() {
        stopPolling();
        startPolling();
    }

    /* ── Visibility change handler ── */

    function onVisibilityChange() {
        if (document.hidden) {
            _paused = true;
        } else {
            _paused = false;
            // Immediately refresh on return to tab
            doRefresh();
        }
    }

    /* ── WebSocket message handler ── */

    function handleWSMessage(message) {
        var data = message.data || message;

        if (data.action === 'passenger_change') {
            var id = data.id;
            var newCount = parseInt(data.new_count, 10);
            var capacity = parseInt(data.capacity, 10);
            if (id && !isNaN(newCount) && !isNaN(capacity)) {
                updatePassengerUI(id, newCount, capacity);
                // If element not found, do full refresh
                if (!getCountSpan(id)) {
                    doRefresh();
                }
            }
        } else {
            // Status change, queue add/remove — do full refresh
            doRefresh();
        }

        // Reset the poll timer since we just got fresh data
        resetPollTimer();
    }

    /* ── Public API ── */

    window.QueueSync = {
        /**
         * Initialize sync for the current page.
         * @param {Object} cfg
         * @param {string}   cfg.apiUrl         - JSON API URL for polling (e.g. '/api/queue-status')
         * @param {number}   [cfg.pollInterval] - Polling interval in ms (default 5000)
         * @param {string}   cfg.refreshUrl     - URL to fetch HTML for full table refresh
         * @param {string}   [cfg.tableSelector]- CSS selector for the <tbody> to replace
         * @param {string}   [cfg.modalSelector]- CSS selector for modals to re-mount
         * @param {Function} [cfg.extraRefresh] - Extra callback(newDoc) after AJAX refresh
         * @param {boolean}  [cfg.onlyWS]       - DEPRECATED: polling always runs as fallback
         * @param {Function} [cfg.customWSHandler] - Override the default WS message handler
         * @param {Function} [cfg.customRefresh]   - Override the default AJAX refresh entirely
         */
        init: function(cfg) {
            _config = cfg || {};

            // Hook visibility change
            document.addEventListener('visibilitychange', onVisibilityChange);

            // ALWAYS start polling — this is the guaranteed fallback
            startPolling();

            // Initialize WebSocket (fast-path for instant updates)
            if (typeof QueueWS !== 'undefined') {
                try {
                    var wsHandler = _config.customWSHandler
                        ? function(message) {
                            _config.customWSHandler(message);
                            resetPollTimer();
                        }
                        : handleWSMessage;

                    QueueWS.init({
                        onQueueUpdate: wsHandler,
                        onConnected: function() {
                            // Immediately fetch fresh data on WS connect
                            doRefresh();
                        },
                        pollingFallback: function() {
                            doRefresh();
                        },
                        pollingInterval: 30000
                    });
                } catch(e) {
                    // WebSocket not available — polling handles it
                    console.warn('[QueueSync] WebSocket init failed, polling is active as fallback.');
                }
            }
        },

        /** Manually update passenger count in the UI */
        updatePassengerUI: updatePassengerUI,

        /** Manually trigger a full refresh */
        refresh: function() {
            doRefresh();
        },

        /** Stop all polling and listeners */
        destroy: function() {
            stopPolling();
            document.removeEventListener('visibilitychange', onVisibilityChange);
            _config = null;
            _lastIds = {};
        }
    };

})(window);
