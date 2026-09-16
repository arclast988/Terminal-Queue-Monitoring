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

    if (window.QueueWS && window.QueueWS._isLoaded) {
        return;
    }


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
        // Nginx proxies "/ws" to the PHP WS server.
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

                // Global document-level event dispatching for universal real-time reactivity
                try {
                    document.dispatchEvent(new CustomEvent('pttm:ws-message', { detail: message }));
                    if (message.type) {
                        document.dispatchEvent(new CustomEvent('pttm:ws-' + message.type, { detail: message }));
                    }
                } catch (e) { /* ignore event dispatch error */ }

                // Convenience: queue_update and vehicle_type_update specific handler
                if ((message.type === 'queue_update' || message.type === 'vehicle_type_update') && _config && _config.onQueueUpdate) {
                    _config.onQueueUpdate(message);
                }

                // Convenience: vehicle_type_update specific handler
                if (message.type === 'vehicle_type_update' && _config && _config.onVehicleTypeUpdate) {
                    _config.onVehicleTypeUpdate(message);
                }

                // Convenience: fare_update specific handler
                if (message.type === 'fare_update' && _config && _config.onFareUpdate) {
                    _config.onFareUpdate(message);
                }

                // Convenience: announcement_update specific handler
                if (message.type === 'announcement_update' && _config && _config.onAnnouncementUpdate) {
                    _config.onAnnouncementUpdate(message);
                }

                // Convenience: branding_updated specific handler
                if (message.type === 'branding_updated' && message.data) {
                    if (typeof window.applyLiveBranding === 'function') {
                        window.applyLiveBranding(message.data);
                    }
                    if (_config && _config.onBrandingUpdate) {
                        _config.onBrandingUpdate(message);
                    }
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
        _isLoaded: true,
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

    /**
     * Globally accessible helper to apply real-time branding changes across the DOM.
     * Updates navbar, drawer, footers, logo images, and dynamic theme colors without page reload.
     */
    function applyLiveBranding(data) {
        if (!data) return;

        // 1. Update App Name
        if (data.app_name) {
            document.querySelectorAll('#site-header .logo-text h1, .site-header .logo-text h1, .drawer-brand-title, .app-brand-name, .header-brand-title').forEach(function(el) {
                el.textContent = data.app_name;
            });
            document.querySelectorAll('.footer-app-name, .footer-brand-title, .guest-footer-title').forEach(function(el) {
                el.textContent = data.app_name;
            });
        }

        // 2. Update Subtitle / System Title
        if (data.system_title) {
            document.querySelectorAll('#site-header .logo-text p, .site-header .logo-text p, .drawer-brand-subtitle, .app-system-title, .header-brand-subtitle').forEach(function(el) {
                el.textContent = data.system_title;
            });
            document.querySelectorAll('.footer-system-title, .guest-footer-subtitle').forEach(function(el) {
                el.textContent = data.system_title;
            });
        }

        // 3. Update Logo Image
        if (data.app_logo) {
            var logoUrl = data.app_logo + (data.app_logo.indexOf('?') === -1 ? '?t=' : '&t=') + Date.now();
            document.querySelectorAll('#site-header img.logo, .site-header img.logo, .drawer-logo, img.header-logo, img.app-logo, img.report-logo').forEach(function(img) {
                img.src = logoUrl;
            });
        }

        // 4. Update Dynamic Theme Colors
        if (data.theme_admin_primary) {
            document.documentElement.style.setProperty('--primary-color', data.theme_admin_primary);
            document.documentElement.style.setProperty('--admin-primary', data.theme_admin_primary);
            document.documentElement.style.setProperty('--primary', data.theme_admin_primary);
        }
        if (data.theme_guest_primary) {
            document.documentElement.style.setProperty('--guest-primary', data.theme_guest_primary);
        }
        if (data.theme_staff_primary) {
            document.documentElement.style.setProperty('--staff-primary', data.theme_staff_primary);
        }

        // 5. Update Footer Text
        if (data.footer_about_title) {
            document.querySelectorAll('.footer-about-title').forEach(function(el) {
                el.textContent = data.footer_about_title;
            });
        }
        if (data.footer_about_desc) {
            document.querySelectorAll('.footer-about-desc').forEach(function(el) {
                el.textContent = data.footer_about_desc;
            });
        }
        if (data.footer_credit) {
            document.querySelectorAll('.footer-credit').forEach(function(el) {
                el.textContent = data.footer_credit;
            });
        }

        // 6. Broadcast event for page-specific custom listeners
        try {
            document.dispatchEvent(new CustomEvent('pttm:branding-applied', { detail: data }));
        } catch(e) {}
    }

    window.applyLiveBranding = applyLiveBranding;
    window.QueueWS.applyLiveBranding = applyLiveBranding;

})(window);
