/**
 * Shared WebSocket Client Module
 * ─────────────────────────────────────────────────────────────
 * Provides a consistent, reusable WebSocket connection for all
 * pages in the application. Features:
 *   • Exponential backoff reconnect (max 15s)
 *   • Polling fallback when WS is disconnected
 *   • Event callback registration (updateable)
 *   • Tab-visibility awareness (pauses reconnect when hidden)
 *   • Consistent console logging
 *
 * Usage:
 *   QueueWS.init({
 *       onQueueUpdate: function(message) { ... },
 *       onMessage: function(message) { ... },    // generic handler
 *       onConnected: function() { ... },          // optional
 *       pollingFallback: function() { ... },      // optional
 *       pollingInterval: 20000                    // optional, default 20s
 *   });
 */
(function(window) {
    'use strict';

    var WS_PORT = 8081; // backend port the Nginx "/ws" proxy forwards to (see buildUrl)
    var MIN_RETRY = 1000;
    var MAX_RETRY = 15000;
    var RETRY_MULTIPLIER = 1.5;

    // Internal state
    var _socket = null;
    var _connected = false;
    var _retryDelay = MIN_RETRY;
    var _retryTimer = null;
    var _pollTimer = null;
    var _config = null;
    var _initialized = false;
    var _tabHidden = false;

    // Logging helpers
    function logOk(label, msg) {
        console.log('%c[' + label + '] ' + msg, 'color: #00e676; font-weight: bold;');
    }
    function logWarn(label, msg) {
        console.log('%c[' + label + '] ' + msg, 'color: #ffab00; font-weight: bold;');
    }
    function logErr(label, msg) {
        console.error('%c[' + label + '] ' + msg, 'color: #ff1744; font-weight: bold;');
    }

    function buildUrl() {
        // Connect same-origin through the Nginx "/ws" reverse proxy so the
        // socket rides on the page's own host + port + TLS:
        //   https:// page  ->  wss://<host>/ws
        //   http://  page  ->  ws://<host>/ws
        // This is required for HTTPS deployments (browsers block insecure ws://
        // from an https:// page) and avoids exposing the raw WS port publicly.
        // Nginx proxies "/ws" to the PHP WS server on 127.0.0.1:8081.
        var scheme = (window.location.protocol === 'https:') ? 'wss://' : 'ws://';
        return scheme + window.location.host + '/ws';
    }

    function startPolling() {
        if (_pollTimer || !_config || !_config.pollingFallback) return;
        var interval = _config.pollingInterval || 20000;
        _pollTimer = setInterval(function() {
            if (!_connected && !_tabHidden && _config.pollingFallback) {
                _config.pollingFallback();
            }
        }, interval);
    }

    function stopPolling() {
        if (_pollTimer) {
            clearInterval(_pollTimer);
            _pollTimer = null;
        }
    }

    function connect() {
        // Guard: don't open if one is already open/connecting
        if (_socket && (_socket.readyState === WebSocket.CONNECTING || _socket.readyState === WebSocket.OPEN)) {
            return;
        }

        // Don't attempt connection when tab is hidden
        if (_tabHidden) return;

        var url = buildUrl();
        try {
            _socket = new WebSocket(url);
        } catch (e) {
            logErr('WS', 'Failed to create WebSocket: ' + e.message);
            scheduleReconnect();
            return;
        }

        _socket.onopen = function() {
            _connected = true;
            _retryDelay = MIN_RETRY;
            stopPolling();
            logOk('WS', 'Connected to ' + url);

            if (_config && _config.onConnected) {
                _config.onConnected();
            }
        };

        _socket.onmessage = function(event) {
            try {
                var message = JSON.parse(event.data);

                // Ignore heartbeats
                if (message.type === 'ping') return;

                // Generic handler (fires for all message types)
                if (_config && _config.onMessage) {
                    _config.onMessage(message);
                }

                // Convenience: queue_update specific handler
                if (message.type === 'queue_update' && _config && _config.onQueueUpdate) {
                    _config.onQueueUpdate(message);
                }
            } catch (e) {
                // Ignore malformed messages
            }
        };

        _socket.onclose = function() {
            _connected = false;
            _socket = null;
            logWarn('WS', 'Disconnected. Retrying in ' + (_retryDelay / 1000).toFixed(1) + 's...');
            startPolling();
            scheduleReconnect();
        };

        _socket.onerror = function() {
            if (_socket) {
                _socket.close();
            }
        };
    }

    function scheduleReconnect() {
        if (_retryTimer) clearTimeout(_retryTimer);
        // Don't schedule reconnect if tab is hidden
        if (_tabHidden) return;
        _retryTimer = setTimeout(function() {
            _retryTimer = null;
            connect();
        }, _retryDelay);
        _retryDelay = Math.min(_retryDelay * RETRY_MULTIPLIER, MAX_RETRY);
    }

    // Visibility change: pause reconnect when tab is hidden
    function onVisibilityChange() {
        _tabHidden = document.hidden;
        if (!_tabHidden && _initialized && !_connected) {
            // Tab became visible and we're disconnected — reconnect now
            _retryDelay = MIN_RETRY;
            connect();
        }
    }

    document.addEventListener('visibilitychange', onVisibilityChange);

    // Public API
    window.QueueWS = {
        /**
         * Initialize the WebSocket connection.
         * Can be called multiple times — subsequent calls update the config
         * without creating duplicate connections.
         *
         * @param {Object} config
         * @param {Function} config.onQueueUpdate    - Called on queue_update messages.
         * @param {Function} [config.onMessage]      - Called on ANY message (generic handler).
         * @param {Function} [config.onConnected]    - Called when WS connects.
         * @param {Function} [config.pollingFallback]- Called periodically when WS is disconnected.
         * @param {number}   [config.pollingInterval]- Polling interval in ms (default 20000).
         */
        init: function(config) {
            if (_initialized) {
                // Already connected — just update the callbacks
                _config = config || {};
                logOk('WS', 'Config updated (connection already active)');
                return;
            }
            _initialized = true;
            _config = config || {};
            connect();
        },

        /**
         * Update callbacks without reinitializing the connection.
         * @param {Object} config - Same shape as init() config.
         */
        updateConfig: function(config) {
            if (config) {
                _config = config;
            }
        },

        /** Returns true if the WebSocket is currently connected. */
        isConnected: function() {
            return _connected;
        },

        /** Force a disconnect (e.g., on page unload). */
        destroy: function() {
            stopPolling();
            if (_retryTimer) { clearTimeout(_retryTimer); _retryTimer = null; }
            if (_socket) {
                _socket.onclose = null;
                _socket.close();
                _socket = null;
            }
            _connected = false;
            _initialized = false;
            _config = null;
        }
    };

})(window);
