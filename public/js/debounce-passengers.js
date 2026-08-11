/**
 * Debounced Passenger Update Module
 * ─────────────────────────────────────────────────────────────
 * Prevents flooding the server when rapidly clicking +/- buttons.
 *
 * How it works:
 *   1. On each click, immediately update the UI (optimistic)
 *   2. Send the first click immediately for fast WebSocket fan-out
 *   3. Coalesce rapid follow-up clicks while a request is in flight
 *   4. The server sets the latest count directly (not increment/decrement)
 *
 * Usage:
 *   PassengerDebounce.init({
 *       // URL template — {id} will be replaced with the queue item ID
 *       setUrl: '/admin/queue/setPassengers/{id}',
 *       // Optional: called after server confirms the update
 *       onUpdated: function(id, data) { ... },
 *       // Optional: called on error
 *       onError: function(id, err) { ... }
 *   });
 *
 *   // Then bind your buttons like:
 *   onclick="PassengerDebounce.adjust(queueId, 'increment', capacity)"
 *   onclick="PassengerDebounce.adjust(queueId, 'decrement', capacity)"
 *   onclick="PassengerDebounce.adjust(queueId, 'max', capacity)"
 */
(function(window) {
    'use strict';

    var DEBOUNCE_MS = 120;

    // Pending timers keyed by queue item ID
    var _timers = {};
    // Pending counts keyed by queue item ID (optimistic local state)
    var _pending = {};
    // In-flight requests keyed by queue item ID
    var _inflight = {};
    // Config
    var _config = {};

    function getCountSpan(id) {
        // Try the ID-based selector first (queue pages), then class-based (dashboard)
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
                el = container.parentElement ? container.parentElement.querySelector('.full-badge') : null;
            }
        }
        return el;
    }

    function getCurrentCount(id) {
        // If we have a pending optimistic value, use that
        if (_pending[id] !== undefined) return _pending[id];

        var span = getCountSpan(id);
        if (span) {
            var text = span.textContent.trim();
            var parts = text.split('/');
            return parseInt(parts[0].trim(), 10) || 0;
        }
        return 0;
    }

    function updateUI(id, newCount, capacity) {
        var span = getCountSpan(id);
        var badge = getBadge(id);

        if (span) {
            span.textContent = newCount + ' / ' + capacity;
            if (newCount >= capacity) {
                span.classList.add('text-danger');
            } else {
                span.classList.remove('text-danger');
            }
        }
        if (badge) {
            badge.style.display = (newCount >= capacity) ? 'inline-block' : 'none';
        }
    }

    function scheduleSend(id, delay) {
        if (_timers[id]) clearTimeout(_timers[id]);
        _timers[id] = setTimeout(function() {
            delete _timers[id];
            sendToServer(id);
        }, delay);
    }

    function sendToServer(id) {
        var count = _pending[id];
        if (count === undefined) return;

        if (_inflight[id] !== undefined) {
            scheduleSend(id, DEBOUNCE_MS);
            return;
        }

        _inflight[id] = count;

        var url = _config.setUrl.replace('{id}', id);

        // Read CSRF token from meta tag
        var csrfToken = '';
        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (csrfMeta) csrfToken = csrfMeta.getAttribute('content');

        var headers = {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };
        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken;
        }

        fetch(url, {
            method: 'POST',
            headers: headers,
            body: JSON.stringify({ count: count })
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            var sentCount = _inflight[id];
            delete _inflight[id];

            if (data.success) {
                var serverCount = parseInt(data.new_count, 10);
                var serverCapacity = parseInt(data.capacity, 10);
                var hasNewerPending = _pending[id] !== undefined && _pending[id] !== sentCount;

                if (!hasNewerPending) {
                    delete _pending[id];
                    if (!isNaN(serverCount) && !isNaN(serverCapacity)) {
                        updateUI(id, serverCount, serverCapacity);
                    }
                } else {
                    scheduleSend(id, 0);
                }
            } else {
                delete _pending[id];
            }

            if (data.success && _config.onUpdated) {
                _config.onUpdated(id, data);
            }
            if (!data.success && _config.onError) {
                _config.onError(id, data.message || 'Server rejected the update');
            }
        })
        .catch(function(err) {
            delete _inflight[id];
            delete _pending[id];
            console.error('[PassengerDebounce] Error for queue #' + id + ':', err);
            if (_config.onError) _config.onError(id, err);
        });
    }

    // Public API
    window.PassengerDebounce = {
        /**
         * Initialize the debounce module.
         * @param {Object} config
         * @param {string} config.setUrl - URL template with {id} placeholder
         * @param {Function} [config.onUpdated] - Callback after server confirms
         * @param {Function} [config.onError] - Callback on error
         */
        init: function(config) {
            _config = config || {};
        },

        /**
         * Adjust passenger count with debounce.
         * @param {number} id - Queue item ID
         * @param {string} action - 'increment', 'decrement', or 'max'
         * @param {number} capacity - Vehicle capacity
         */
        adjust: function(id, action, capacity) {
            var current = getCurrentCount(id);

            var newCount;
            if (action === 'increment') {
                newCount = Math.min(current + 1, capacity);
            } else if (action === 'decrement') {
                newCount = Math.max(current - 1, 0);
            } else if (action === 'max') {
                newCount = capacity;
            } else {
                return;
            }

            // Store optimistic value
            _pending[id] = newCount;

            // Immediately update the UI
            updateUI(id, newCount, capacity);

            // Notify QueueSync (if present) to set a cooldown so the pending
            // poll doesn't overwrite this optimistic update with stale data.
            var ev = new CustomEvent('passenger-optimistic-update', { detail: { id: id, count: newCount, capacity: capacity } });
            document.dispatchEvent(ev);

            // Send the first click immediately so WebSocket clients update
            // without waiting for the polling interval. Rapid follow-up clicks
            // are coalesced while a request is in flight, and the latest count
            // is sent as soon as the current request finishes.
            scheduleSend(id, _inflight[id] === undefined ? 0 : DEBOUNCE_MS);
        }
    };

})(window);
