/**
 * Shared WebSocket Client Module
 * ─────────────────────────────────────────────────────────────
 * Provides a consistent, reusable WebSocket connection for all
 * pages in the application. Features:
 *   • Exponential backoff reconnect with jitter (max 15s base delay)
 *   • Optional polling fallback when WS is disconnected
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
    var INITIAL_CONNECT_JITTER_MS = 750;
    var MAX_RETRY_JITTER_MS = 2000;

    // Internal state
    var _socket = null;
    var _connected = false;
    var _retryDelay = MIN_RETRY;
    var _retryTimer = null;
    var _pollTimer = null;
    var _config = null;
    var _initialized = false;
    var _initializedAt = 0;
    var _hasConnectedBefore = false;
    var _tabHidden = false;
    var _sharedBc = null;

    function getSharedBroadcastChannel() {
        if (!window.BroadcastChannel) return null;
        if (!_sharedBc) {
            try {
                _sharedBc = new BroadcastChannel('pttm_queue_channel');
            } catch (e) {
                _sharedBc = null;
            }
        }
        return _sharedBc;
    }

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
            var connectionInfo = {
                reconnected: _hasConnectedBefore,
                connectedAfterMs: _initializedAt ? Math.max(0, Date.now() - _initializedAt) : 0
            };
            _hasConnectedBefore = true;
            _connected = true;
            _retryDelay = MIN_RETRY;
            stopPolling();
            logOk('WS', 'Connected to ' + url);

            if (_config && _config.onConnected) {
                _config.onConnected(connectionInfo);
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
                    var _bc = getSharedBroadcastChannel();
                    if (_bc) {
                        try {
                            var _bcPayload = (typeof message === 'object' && message !== null)
                                ? Object.assign({}, message, { _fromWs: true })
                                : message;
                            _bc.postMessage(_bcPayload);
                        } catch (bcErr) { /* ignore postMessage error */ }
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
        // Jitter prevents every connected phone from reconnecting and refreshing
        // PHP/PostgreSQL in the same millisecond after a service restart.
        var jitterWindow = Math.min(MAX_RETRY_JITTER_MS, Math.max(1, Math.round(_retryDelay * 0.25)));
        var reconnectDelay = _retryDelay + Math.floor(Math.random() * jitterWindow);
        logWarn('WS', 'Disconnected. Retrying in ' + (reconnectDelay / 1000).toFixed(1) + 's...');
        _retryTimer = setTimeout(function() {
            _retryTimer = null;
            connect();
        }, reconnectDelay);
        _retryDelay = Math.min(_retryDelay * RETRY_MULTIPLIER, MAX_RETRY);
    }

    function scheduleInitialConnect() {
        if (_retryTimer) clearTimeout(_retryTimer);
        var initialDelay = Math.floor(Math.random() * (INITIAL_CONNECT_JITTER_MS + 1));
        _retryTimer = setTimeout(function() {
            _retryTimer = null;
            connect();
        }, initialDelay);
    }

    // Visibility change: pause reconnect when tab is hidden
    function onVisibilityChange() {
        _tabHidden = document.hidden;
        if (_retryTimer) {
            clearTimeout(_retryTimer);
            _retryTimer = null;
        }
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
                // Already connected — safely merge/update callbacks
                _config = Object.assign(_config || {}, config || {});
                logOk('WS', 'Config updated (connection already active)');
                return;
            }
            _initialized = true;
            _initializedAt = Date.now();
            _config = Object.assign(_config || {}, config || {});
            scheduleInitialConnect();
        },

        /**
         * Update callbacks without reinitializing the connection.
         * @param {Object} config - Same shape as init() config.
         */
        updateConfig: function(config) {
            if (config) {
                _config = Object.assign(_config || {}, config);
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
            _initializedAt = 0;
            _hasConnectedBefore = false;
            _config = null;
            if (_sharedBc) {
                try { _sharedBc.close(); } catch (e) {}
                _sharedBc = null;
            }
        }
    };

    // Color helper functions for dynamic live CSS calculation
    function hexToRgb(hex) {
        if (!hex || typeof hex !== 'string') return { r: 198, g: 40, b: 40 };
        hex = hex.replace('#', '');
        if (hex.length === 3) hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
        var num = parseInt(hex, 16);
        return { r: (num >> 16) & 255, g: (num >> 8) & 255, b: num & 255 };
    }
    function colorDarken(hex, factor) {
        var rgb = hexToRgb(hex);
        var r = Math.max(0, Math.round(rgb.r * (1 - factor)));
        var g = Math.max(0, Math.round(rgb.g * (1 - factor)));
        var b = Math.max(0, Math.round(rgb.b * (1 - factor)));
        return '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
    }
    function colorSoft(hex, alpha) {
        var rgb = hexToRgb(hex);
        return 'rgba(' + rgb.r + ', ' + rgb.g + ', ' + rgb.b + ', ' + alpha + ')';
    }
    function contrastText(hex) {
        var rgb = hexToRgb(hex);
        var yiq = ((rgb.r * 299) + (rgb.g * 587) + (rgb.b * 114)) / 1000;
        return (yiq >= 150) ? '#1c2430' : '#ffffff';
    }
    function colorBrightness(hex) {
        var rgb = hexToRgb(hex);
        return ((rgb.r * 299) + (rgb.g * 587) + (rgb.b * 114)) / 1000;
    }

    /**
     * Globally accessible helper to apply real-time branding changes across the DOM.
     * Updates navbar, drawer, footers, logo images, and dynamic theme colors without page reload.
     */
    function applyLiveBranding(data) {
        if (!data) return;

        var category = data.category || null;
        var affectsAll = !category;
        var affectsIdentity = affectsAll || category === 'identity';
        var affectsLogo = affectsAll || category === 'logo';
        var affectsTheme = affectsAll || category === 'theme';
        var affectsBackground = affectsAll || category === 'background';
        var affectsFooter = affectsAll || category === 'footer';

        // 1. Update App Name
        if (affectsIdentity && data.app_name) {
            document.querySelectorAll('#site-header .logo-text h1, .site-header .logo-text h1, .guest-header .logo-text h1, .drawer-brand-title, .app-brand-name, .header-brand-title, .previewNameEl').forEach(function(el) {
                el.textContent = data.app_name;
            });
            document.querySelectorAll('.footer-app-name, .footer-brand-title, .guest-footer-title').forEach(function(el) {
                el.textContent = data.app_name;
            });
            // Update browser document tab title in real-time
            if (document.title) {
                var titleParts = document.title.split(' - ');
                if (titleParts.length > 1) {
                    titleParts[titleParts.length - 1] = data.app_name;
                    document.title = titleParts.join(' - ');
                } else {
                    document.title = data.app_name;
                }
            }
        }

        // 2. Update Subtitle
        if (affectsIdentity && data.app_subtitle) {
            document.querySelectorAll('#site-header .logo-text p, .site-header .logo-text p, .guest-header .logo-text p, .drawer-brand-subtitle, .header-brand-subtitle, .previewSubEl').forEach(function(el) {
                el.textContent = data.app_subtitle;
            });
        }

        // 3. Update System Title
        if (affectsIdentity && data.system_title) {
            document.querySelectorAll('.app-system-title, .footer-system-title, .guest-footer-subtitle, #previewSysTitleCard').forEach(function(el) {
                el.textContent = data.system_title;
            });
        }

        // 3b. Update Acronym
        if (affectsIdentity && data.acronym) {
            document.querySelectorAll('.app-acronym, #previewAcronymCard').forEach(function(el) {
                el.textContent = data.acronym;
            });
        }

        // 4. Update Logo Image
        if (affectsLogo && data.app_logo) {
            var logoUrl = data.app_logo + (data.app_logo.indexOf('?') === -1 ? '?t=' : '&t=') + Date.now();
            document.querySelectorAll('#site-header img.logo, .site-header img.logo, .guest-header img.logo, .guest-header .logo, .drawer-logo, img.header-logo, img.app-logo, img.report-logo, img.previewLogoImg, img.logo').forEach(function(img) {
                img.src = logoUrl;
            });
            // Dedicated app icons must remain square when the display logo changes.
            document.querySelectorAll("link[rel*='icon']:not([data-app-icon])").forEach(function(icon) {
                icon.href = logoUrl;
            });
        }

        // 4b. Update Login Card Image
        if ((affectsIdentity || affectsLogo) && data.app_login_card_image) {
            var loginCardUrl = data.app_login_card_image + (data.app_login_card_image.indexOf('?') === -1 ? '?t=' : '&t=') + Date.now();
            var loginImg = document.getElementById('loginHeroImg');
            if (loginImg) {
                loginImg.src = loginCardUrl;
            }
            var loginPreviewImg = document.getElementById('loginCardActiveImg');
            if (loginPreviewImg) {
                loginPreviewImg.src = loginCardUrl;
            }
        }

        // 5. Update Dynamic Theme Colors & Live Injected Styles (ONLY if theme category or full sync)
        var liveThemeRules = '';

        if (affectsTheme && (data.theme_guest_primary || data.theme_guest_nav_bg || data.theme_guest_nav_text)) {
            var gp_hex = data.theme_guest_primary || getComputedStyle(document.documentElement).getPropertyValue('--guest-primary').trim() || '#C62828';
            var gp_dark = colorDarken(gp_hex, 0.20);
            var gp_soft = colorSoft(gp_hex, 0.12);
            var gp_on = contrastText(gp_hex);
            var gn_hex = data.theme_guest_nav_bg || '#ffffff';
            var gt_hex = data.theme_guest_nav_text || '#1c2430';
            var gn_bright = colorBrightness(gn_hex);
            // Ensure proper contrast: if navbar is dark, text MUST be light; if navbar is light, text MUST be dark
            if (gn_bright < 135 && colorBrightness(gt_hex) < 135) {
                gt_hex = '#ffffff';
            } else if (gn_bright >= 135 && colorBrightness(gt_hex) >= 135) {
                gt_hex = '#1c2430';
            }
            var gn_chipBg = (gn_bright > 155) ? 'rgba(0, 0, 0, 0.08)' : 'rgba(255, 255, 255, 0.18)';
            var gn_divider = (gn_bright > 155) ? 'rgba(0, 0, 0, 0.12)' : 'rgba(255, 255, 255, 0.22)';
            var gt_soft = (colorBrightness(gt_hex) > 155) ? 'rgba(255, 255, 255, 0.88)' : 'rgba(15, 23, 42, 0.82)';

            var isGuestPage = !document.body.classList.contains('admin-theme') && !document.body.classList.contains('staff-theme');
            if (isGuestPage) {
                var guestVars = {
                    '--primary': gp_hex,
                    '--primary-color': gp_hex,
                    '--primary-dark': gp_dark,
                    '--primary-soft': gp_soft,
                    '--primary-red': gp_hex,
                    '--primary-red-dark': gp_dark,
                    '--primary-red-light': gp_soft,
                    '--on-primary': gp_on,
                    '--guest-primary': gp_hex,
                    '--nav-bg': gn_hex,
                    '--nav-text': gt_hex,
                    '--nav-text-soft': gt_soft,
                    '--nav-accent': gp_hex,
                    '--nav-cta-bg': gp_hex,
                    '--nav-cta-text': gp_on,
                    '--nav-chip-bg': gn_chipBg,
                    '--nav-divider': gn_divider
                };
                for (var gk in guestVars) {
                    try {
                        document.documentElement.style.setProperty(gk, guestVars[gk], 'important');
                        document.body.style.setProperty(gk, guestVars[gk], 'important');
                    } catch(e) {}
                }

                // Force direct element style updates for elements that don't
                // re-evaluate CSS variables (e.g., gradients, computed backgrounds).
                var advBar = document.querySelector('.advisory-bar');
                if (advBar) {
                    advBar.style.setProperty('background', 'linear-gradient(135deg, ' + gp_hex + ' 0%, ' + gp_dark + ' 100%)', 'important');
                }
                document.querySelectorAll('.advisory-icon').forEach(function(el) {
                    el.style.setProperty('color', gp_hex, 'important');
                });
                document.querySelectorAll('.nav-menu a.login-btn').forEach(function(el) {
                    el.style.setProperty('background', gp_hex, 'important');
                    el.style.setProperty('color', gp_on, 'important');
                });
                document.querySelectorAll('.nav-menu a.login-btn i').forEach(function(el) {
                    el.style.setProperty('color', gp_on, 'important');
                });
                document.querySelectorAll('.nav-menu a::after, .nav-menu a.active, .nav-menu a:hover').forEach(function(el) {
                    el.style.setProperty('color', gp_hex, 'important');
                });
                // Hero section buttons (SEARCH, Apply Filters, etc.)
                document.querySelectorAll('.search-bar button, .filter-btn, .search-btn, .btn-primary, .apply-btn').forEach(function(el) {
                    el.style.setProperty('background', gp_hex, 'important');
                    el.style.setProperty('color', gp_on, 'important');
                });
                // Route filter chips active state
                document.querySelectorAll('.route-chip.active, .route-filter-chip.active').forEach(function(el) {
                    el.style.setProperty('background', gp_hex, 'important');
                });
                // Mobile toggle
                document.querySelectorAll('.mobile-toggle').forEach(function(el) {
                    el.style.setProperty('color', gp_hex, 'important');
                });
                // Breadcrumb active link
                document.querySelectorAll('.breadcrumb-section a').forEach(function(el) {
                    el.style.setProperty('color', gp_hex, 'important');
                });

                // Force browser repaint by reading layout
                void document.body.offsetHeight;

                console.log('[applyLiveBranding] Guest CSS vars SET. --primary =', guestVars['--primary'], 'isGuestPage:', isGuestPage);
            }

            liveThemeRules += `
                :root, body.guest-theme, html body.guest-theme, body:not(.admin-theme):not(.staff-theme) {
                    --primary: ${gp_hex} !important;
                    --primary-dark: ${gp_dark} !important;
                    --primary-soft: ${gp_soft} !important;
                    --primary-red: ${gp_hex} !important;
                    --primary-red-dark: ${gp_dark} !important;
                    --primary-red-light: ${gp_soft} !important;
                    --on-primary: ${gp_on} !important;
                    --nav-bg: ${gn_hex} !important;
                    --nav-text: ${gt_hex} !important;
                    --nav-text-soft: ${gt_soft} !important;
                    --nav-accent: ${gp_hex} !important;
                    --nav-cta-bg: ${gp_hex} !important;
                    --nav-cta-text: ${gp_on} !important;
                    --nav-chip-bg: ${gn_chipBg} !important;
                    --nav-divider: ${gn_divider} !important;
                }
                .guest-header, body.guest-theme header#site-header,
                body:not(.admin-theme):not(.staff-theme) header#site-header {
                    background: ${gn_hex} !important;
                    border-bottom: 1px solid ${gn_divider} !important;
                }
                .guest-header .logo-text h1,
                body.guest-theme #site-header .logo-text h1,
                body.guest-theme .logo-section .logo-text h1,
                body.guest-theme .logo-text h1,
                body:not(.admin-theme):not(.staff-theme) #site-header .logo-text h1,
                body:not(.admin-theme):not(.staff-theme) .logo-section .logo-text h1 {
                    color: ${gt_hex} !important;
                    -webkit-text-fill-color: ${gt_hex} !important;
                }
                .guest-header .logo-text p,
                body.guest-theme #site-header .logo-text p,
                body.guest-theme .logo-section .logo-text p,
                body.guest-theme .logo-text p,
                body:not(.admin-theme):not(.staff-theme) #site-header .logo-text p,
                body:not(.admin-theme):not(.staff-theme) .logo-section .logo-text p {
                    color: ${gt_soft} !important;
                    -webkit-text-fill-color: ${gt_soft} !important;
                    opacity: 0.90 !important;
                }
                .guest-header .nav-menu > a:not(.btn):not(.profile-dropdown-item),
                .guest-header .nav-menu a:not(.btn):not(.profile-dropdown-item):not(.dropdown-content a):not(.dropdown-item),
                body.guest-theme .nav-menu > a:not(.btn):not(.profile-dropdown-item),
                body.guest-theme .nav-menu a:not(.btn):not(.profile-dropdown-item):not(.dropdown-content a):not(.dropdown-item),
                body:not(.admin-theme):not(.staff-theme) .nav-menu > a:not(.btn):not(.profile-dropdown-item),
                body:not(.admin-theme):not(.staff-theme) .nav-menu a:not(.btn):not(.profile-dropdown-item):not(.dropdown-content a):not(.dropdown-item) {
                    color: ${gt_hex} !important;
                    -webkit-text-fill-color: ${gt_hex} !important;
                }
                .guest-header .nav-menu > a:not(.btn):not(.profile-dropdown-item) i,
                .guest-header .nav-menu a:not(.btn):not(.profile-dropdown-item):not(.dropdown-content a):not(.dropdown-item) i,
                body.guest-theme .nav-menu > a:not(.btn):not(.profile-dropdown-item) i,
                body.guest-theme .nav-menu a:not(.btn):not(.profile-dropdown-item):not(.dropdown-content a):not(.dropdown-item) i,
                body:not(.admin-theme):not(.staff-theme) .nav-menu > a:not(.btn):not(.profile-dropdown-item) i,
                body:not(.admin-theme):not(.staff-theme) .nav-menu a:not(.btn):not(.profile-dropdown-item):not(.dropdown-content a):not(.dropdown-item) i {
                    color: ${gt_soft} !important;
                    -webkit-text-fill-color: ${gt_soft} !important;
                }
                .guest-header .nav-menu > a:not(.login-btn):hover,
                body.guest-theme .nav-menu > a:not(.login-btn):hover,
                body:not(.admin-theme):not(.staff-theme) .nav-menu > a:not(.login-btn):hover {
                    color: ${gp_hex} !important;
                    -webkit-text-fill-color: ${gp_hex} !important;
                    background: ${gp_soft} !important;
                }
                .guest-header .nav-menu > a:not(.login-btn):hover i,
                body.guest-theme .nav-menu > a:not(.login-btn):hover i,
                body:not(.admin-theme):not(.staff-theme) .nav-menu > a:not(.login-btn):hover i {
                    color: ${gp_hex} !important;
                    -webkit-text-fill-color: ${gp_hex} !important;
                }
                .guest-header .nav-menu > a:not(.login-btn).active,
                body.guest-theme .nav-menu > a:not(.login-btn).active,
                body:not(.admin-theme):not(.staff-theme) .nav-menu > a:not(.login-btn).active {
                    color: ${gp_hex} !important;
                    -webkit-text-fill-color: ${gp_hex} !important;
                    background: ${gp_soft} !important;
                }
                .guest-header .nav-menu > a:not(.login-btn).active i,
                body.guest-theme .nav-menu > a:not(.login-btn).active i,
                body:not(.admin-theme):not(.staff-theme) .nav-menu > a:not(.login-btn).active i {
                    color: ${gp_hex} !important;
                    -webkit-text-fill-color: ${gp_hex} !important;
                }
                .guest-header .mobile-toggle,
                body.guest-theme .nav-hamburger-btn,
                body:not(.admin-theme):not(.staff-theme) .nav-hamburger-btn {
                    color: ${gt_hex} !important;
                }
                .guest-header .mobile-toggle i,
                body.guest-theme .nav-hamburger-btn i,
                body:not(.admin-theme):not(.staff-theme) .nav-hamburger-btn i {
                    color: ${gt_hex} !important;
                    -webkit-text-fill-color: ${gt_hex} !important;
                }
                .guest-header .header-clock-pill,
                .header-clock-pill,
                .header-clock-pill span {
                    color: ${gt_hex} !important;
                    -webkit-text-fill-color: ${gt_hex} !important;
                }
                .guest-header .header-clock-pill i,
                .header-clock-pill i {
                    color: ${gp_hex} !important;
                    -webkit-text-fill-color: ${gp_hex} !important;
                }
                body.guest-theme .guest-header .nav-menu a.login-btn,
                body.guest-theme .nav-menu a.login-btn,
                body:not(.admin-theme):not(.staff-theme) .guest-header .nav-menu a.login-btn,
                body:not(.admin-theme):not(.staff-theme) .nav-menu a.login-btn {
                    background: ${gp_hex} !important;
                    color: ${gp_on} !important;
                    -webkit-text-fill-color: ${gp_on} !important;
                }
                body.guest-theme .guest-header .nav-menu a.login-btn:hover,
                body.guest-theme .nav-menu a.login-btn:hover,
                body:not(.admin-theme):not(.staff-theme) .guest-header .nav-menu a.login-btn:hover,
                body:not(.admin-theme):not(.staff-theme) .nav-menu a.login-btn:hover {
                    background: ${gp_dark} !important;
                }
                body.guest-theme .search-bar button:not(.guest-clear-search-btn),
                body:not(.admin-theme):not(.staff-theme) .search-bar button:not(.guest-clear-search-btn),
                body.guest-theme .filter-btn,
                body:not(.admin-theme):not(.staff-theme) .filter-btn,
                body.guest-theme .btn-primary,
                body:not(.admin-theme):not(.staff-theme) .btn-primary,
                body.guest-theme .btn-hero-action:not(.btn-hero-outline),
                body:not(.admin-theme):not(.staff-theme) .btn-hero-action:not(.btn-hero-outline) {
                    background: ${gp_hex} !important;
                    background-color: ${gp_hex} !important;
                    border-color: ${gp_hex} !important;
                    color: ${gp_on} !important;
                    -webkit-text-fill-color: ${gp_on} !important;
                }
                body.guest-theme .search-bar button:not(.guest-clear-search-btn):hover,
                body:not(.admin-theme):not(.staff-theme) .search-bar button:not(.guest-clear-search-btn):hover,
                body.guest-theme .filter-btn:hover,
                body:not(.admin-theme):not(.staff-theme) .filter-btn:hover,
                body.guest-theme .btn-primary:hover,
                body:not(.admin-theme):not(.staff-theme) .btn-primary:hover {
                    background: ${gp_dark} !important;
                    background-color: ${gp_dark} !important;
                    border-color: ${gp_dark} !important;
                }
                body.guest-theme .search-bar:focus-within,
                body:not(.admin-theme):not(.staff-theme) .search-bar:focus-within {
                    border-color: ${gp_hex} !important;
                    box-shadow: 0 0 0 4px ${gp_soft}, var(--shadow-lg) !important;
                }
                body.guest-theme .filter-chip.active,
                body:not(.admin-theme):not(.staff-theme) .filter-chip.active,
                body.guest-theme .route-chip.active,
                body:not(.admin-theme):not(.staff-theme) .route-chip.active,
                body.guest-theme .rules-route-chip.active,
                body:not(.admin-theme):not(.staff-theme) .rules-route-chip.active {
                    background: ${gp_hex} !important;
                    background-color: ${gp_hex} !important;
                    border-color: ${gp_hex} !important;
                    color: ${gp_on} !important;
                    -webkit-text-fill-color: ${gp_on} !important;
                }
                body.guest-theme .filter-chip:hover:not(.active),
                body:not(.admin-theme):not(.staff-theme) .filter-chip:hover:not(.active),
                body.guest-theme .route-chip:hover:not(.active),
                body:not(.admin-theme):not(.staff-theme) .route-chip:hover:not(.active) {
                    border-color: ${gp_hex} !important;
                    color: ${gp_hex} !important;
                    -webkit-text-fill-color: ${gp_hex} !important;
                    background: ${gp_soft} !important;
                }
                body.guest-theme .count-badge,
                body:not(.admin-theme):not(.staff-theme) .count-badge,
                body.guest-theme .badge-count,
                body:not(.admin-theme):not(.staff-theme) .badge-count {
                    background: ${gp_soft} !important;
                    color: ${gp_hex} !important;
                    -webkit-text-fill-color: ${gp_hex} !important;
                }
                body.guest-theme .card-header-modern .card-title-modern i:first-child,
                body.guest-theme .modern-card-header .modern-card-title i:first-child,
                body.guest-theme .schedule-card .card-header h3 i:first-child,
                body:not(.admin-theme):not(.staff-theme) .schedule-card .card-header h3 i:first-child {
                    color: ${gp_hex} !important;
                }
                body.guest-theme footer .f-about h3,
                body:not(.admin-theme):not(.staff-theme) footer .f-about h3,
                body.guest-theme footer .f-links a:hover,
                body:not(.admin-theme):not(.staff-theme) footer .f-links a:hover,
                body.guest-theme footer .f-links li i.fa-map-marker-alt,
                body.guest-theme footer .f-links li i.fa-phone,
                body.guest-theme footer .f-links li i.fa-envelope,
                body:not(.admin-theme):not(.staff-theme) footer .f-links li i.fa-map-marker-alt,
                body:not(.admin-theme):not(.staff-theme) footer .f-links li i.fa-phone,
                body:not(.admin-theme):not(.staff-theme) footer .f-links li i.fa-envelope {
                    color: ${gp_hex} !important;
                    -webkit-text-fill-color: ${gp_hex} !important;
                }
                body.guest-theme footer .f-links h4::after,
                body:not(.admin-theme):not(.staff-theme) footer .f-links h4::after {
                    background: ${gp_hex} !important;
                }
                body:not(.admin-theme):not(.staff-theme) .autocomplete-item:hover,
                body:not(.admin-theme):not(.staff-theme) .autocomplete-item.active-item {
                    background-color: ${gp_soft} !important;
                    color: ${gp_hex} !important;
                }
                body:not(.admin-theme):not(.staff-theme) .autocomplete-item:hover i,
                body:not(.admin-theme):not(.staff-theme) .autocomplete-item.active-item i,
                body:not(.admin-theme):not(.staff-theme) .autocomplete-item:hover span,
                body:not(.admin-theme):not(.staff-theme) .autocomplete-item.active-item span {
                    color: ${gp_hex} !important;
                    -webkit-text-fill-color: ${gp_hex} !important;
                }
                body:not(.admin-theme):not(.staff-theme) .autocomplete-badge {
                    background: ${gp_soft} !important;
                    color: ${gp_hex} !important;
                    -webkit-text-fill-color: ${gp_hex} !important;
                    border: 1px solid ${gp_soft} !important;
                }
            `;
        }

        if (affectsTheme && (data.theme_staff_primary || data.theme_staff_nav_bg || data.theme_staff_nav_text)) {
            var sp_hex = data.theme_staff_primary || getComputedStyle(document.documentElement).getPropertyValue('--staff-primary').trim() || '#15803d';
            var sp_dark = colorDarken(sp_hex, 0.20);
            var sp_soft = colorSoft(sp_hex, 0.12);
            var sp_on = contrastText(sp_hex);
            var sn_hex = data.theme_staff_nav_bg || sp_hex;
            var st_hex = data.theme_staff_nav_text || '#ffffff';
            var sn_bright = colorBrightness(sn_hex);
            var sn_chipBg = (sn_bright > 155) ? 'rgba(0, 0, 0, 0.08)' : 'rgba(255, 255, 255, 0.18)';
            var sn_divider = (sn_bright > 155) ? 'rgba(0, 0, 0, 0.12)' : 'rgba(255, 255, 255, 0.22)';
            var st_soft = (colorBrightness(st_hex) > 155) ? 'rgba(255, 255, 255, 0.88)' : 'rgba(15, 23, 42, 0.82)';

            var isStaffPage = document.body.classList.contains('staff-theme');
            if (isStaffPage) {
                var staffVars = {
                    '--primary': sp_hex,
                    '--primary-dark': sp_dark,
                    '--primary-soft': sp_soft,
                    '--primary-red': sp_hex,
                    '--primary-red-dark': sp_dark,
                    '--primary-red-light': sp_soft,
                    '--on-primary': sp_on,
                    '--staff-primary': sp_hex,
                    '--nav-bg': sn_hex,
                    '--nav-text': st_hex,
                    '--nav-text-soft': st_soft,
                    '--nav-accent': st_hex,
                    '--nav-chip-bg': sn_chipBg,
                    '--nav-divider': sn_divider,
                    '--nav-drawer-bg': sp_dark,
                    '--nav-cta-bg': sp_on,
                    '--nav-cta-text': sp_hex
                };
                for (var sk in staffVars) {
                    try {
                        document.documentElement.style.setProperty(sk, staffVars[sk], 'important');
                        document.body.style.setProperty(sk, staffVars[sk], 'important');
                    } catch(e) {}
                }
            }

            liveThemeRules += `
                body.staff-theme, html body.staff-theme {
                    --primary: ${sp_hex} !important;
                    --primary-dark: ${sp_dark} !important;
                    --primary-soft: ${sp_soft} !important;
                    --on-primary: ${sp_on} !important;
                    --staff-primary: ${sp_hex} !important;
                    --nav-bg: ${sn_hex} !important;
                    --nav-text: ${st_hex} !important;
                    --nav-text-soft: ${st_soft} !important;
                    --nav-accent: ${st_hex} !important;
                    --nav-chip-bg: ${sn_chipBg} !important;
                    --nav-divider: ${sn_divider} !important;
                }
                body.staff-theme header#site-header, html body.staff-theme header#site-header {
                    background: ${sn_hex} !important;
                    border-bottom: 1px solid ${sn_divider} !important;
                }
                body.staff-theme header#site-header .logo-text h1,
                body.staff-theme .logo-section .logo-text h1,
                body.staff-theme .logo-text h1 {
                    color: ${st_hex} !important;
                    -webkit-text-fill-color: ${st_hex} !important;
                }
                body.staff-theme header#site-header .logo-text p,
                body.staff-theme .logo-section .logo-text p,
                body.staff-theme .logo-text p {
                    color: ${st_hex} !important;
                    -webkit-text-fill-color: ${st_hex} !important;
                    opacity: 0.88;
                }
                html body.staff-theme .nav-menu > a:not(.btn):not(.profile-dropdown-item),
                html body.staff-theme .nav-menu a:not(.btn):not(.profile-dropdown-item):not(.dropdown-content a):not(.dropdown-item),
                body.staff-theme .nav-menu > a:not(.btn):not(.profile-dropdown-item),
                body.staff-theme .nav-menu a:not(.btn):not(.profile-dropdown-item):not(.dropdown-content a):not(.dropdown-item),
                body.staff-theme .nav-menu > a:not(.profile-dropdown-item),
                body.staff-theme .nav-menu > .dropdown > .dropbtn {
                    color: ${st_hex} !important;
                    -webkit-text-fill-color: ${st_hex} !important;
                }
                html body.staff-theme .nav-menu > a:not(.btn):not(.profile-dropdown-item) i,
                html body.staff-theme .nav-menu a:not(.btn):not(.profile-dropdown-item):not(.dropdown-content a):not(.dropdown-item) i,
                body.staff-theme .nav-menu > a:not(.btn):not(.profile-dropdown-item) i,
                body.staff-theme .nav-menu a:not(.btn):not(.profile-dropdown-item):not(.dropdown-content a):not(.dropdown-item) i,
                body.staff-theme .nav-menu > a:not(.profile-dropdown-item) i,
                body.staff-theme .nav-menu > .dropdown > .dropbtn i {
                    color: ${st_hex} !important;
                    -webkit-text-fill-color: ${st_hex} !important;
                }
                body.staff-theme .nav-menu > a:not(.profile-dropdown-item):hover,
                body.staff-theme .nav-menu > .dropdown > .dropbtn:hover {
                    background: ${sn_chipBg} !important;
                    color: ${st_hex} !important;
                    -webkit-text-fill-color: ${st_hex} !important;
                }
                body.staff-theme .nav-menu > a:not(.profile-dropdown-item):hover i,
                body.staff-theme .nav-menu > .dropdown > .dropbtn:hover i {
                    color: ${st_hex} !important;
                    -webkit-text-fill-color: ${st_hex} !important;
                    opacity: 1 !important;
                }
                body.staff-theme .nav-menu > a.active:not(.profile-dropdown-item),
                body.staff-theme .nav-menu > .dropdown > .dropbtn.active {
                    background: ${sn_chipBg} !important;
                    color: ${st_hex} !important;
                    -webkit-text-fill-color: ${st_hex} !important;
                }
                body.staff-theme .nav-menu > a.active:not(.profile-dropdown-item) i,
                body.staff-theme .nav-menu > .dropdown > .dropbtn.active i {
                    color: ${st_hex} !important;
                    -webkit-text-fill-color: ${st_hex} !important;
                    opacity: 1 !important;
                }
                body.staff-theme .profile-trigger-name {
                    color: ${st_hex} !important;
                    -webkit-text-fill-color: ${st_hex} !important;
                    text-shadow: none !important;
                }
                body.staff-theme .profile-trigger-caret {
                    color: ${st_soft} !important;
                    -webkit-text-fill-color: ${st_soft} !important;
                }
                body.staff-theme .profile-trigger-btn {
                    background: ${sn_chipBg} !important;
                    border-color: ${sn_divider} !important;
                }
                body.staff-theme .profile-trigger-btn:hover {
                    background: ${sn_chipBg} !important;
                    border-color: ${sn_divider} !important;
                    filter: brightness(0.92);
                }
                body.staff-theme .user-profile-dropdown {
                    border-left-color: ${sn_divider} !important;
                }
                body.staff-theme .btn-primary {
                    background: ${sp_hex} !important;
                    border-color: ${sp_hex} !important;
                    color: ${sp_on} !important;
                }
                body.staff-theme .dep-filter-btn.active,
                body.staff-theme .route-chip.active {
                    background: ${sp_hex} !important;
                    border-color: ${sp_hex} !important;
                    color: ${sp_on} !important;
                }
                body.staff-theme .nav-hamburger-btn,
                body.staff-theme .nav-hamburger-btn i {
                    color: ${st_hex} !important;
                    -webkit-text-fill-color: ${st_hex} !important;
                }
                body.staff-theme .drawer-brand-subtitle {
                    color: ${sp_hex} !important;
                }
                body.staff-theme .drawer-nav-item:hover {
                    background: ${sp_soft} !important;
                    color: ${sp_hex} !important;
                }
                body.staff-theme .drawer-nav-item:hover i {
                    color: ${sp_hex} !important;
                }
                body.staff-theme .drawer-nav-item.active {
                    background: ${sp_soft} !important;
                    color: ${sp_hex} !important;
                }
                body.staff-theme .drawer-nav-item.active i {
                    color: ${sp_hex} !important;
                }
                body.staff-theme .logout-stripe {
                    background: linear-gradient(90deg, ${sp_hex} 0%, ${sp_dark} 100%) !important;
                }
                body.staff-theme .logout-btn-confirm {
                    background: linear-gradient(135deg, ${sp_hex} 0%, ${sp_dark} 100%) !important;
                }
                body.staff-theme .autocomplete-item:hover,
                body.staff-theme .autocomplete-item.active-item {
                    background-color: ${sp_soft} !important;
                    color: ${sp_hex} !important;
                }
                body.staff-theme .autocomplete-badge {
                    background: ${sp_soft} !important;
                    color: ${sp_hex} !important;
                    border: 1px solid ${sp_soft} !important;
                }
            `;
        }

        if (affectsTheme && (data.theme_admin_primary || data.theme_admin_nav_bg || data.theme_admin_nav_text)) {
            var ap_hex = data.theme_admin_primary || getComputedStyle(document.documentElement).getPropertyValue('--admin-primary').trim() || '#B71C1C';
            var ap_dark = colorDarken(ap_hex, 0.20);
            var ap_soft = colorSoft(ap_hex, 0.12);
            var ap_on = contrastText(ap_hex);
            var an_hex = data.theme_admin_nav_bg || ap_hex;
            var at_hex = data.theme_admin_nav_text || '#ffffff';
            var an_bright = colorBrightness(an_hex);
            var an_chipBg = (an_bright > 155) ? 'rgba(0, 0, 0, 0.08)' : 'rgba(255, 255, 255, 0.18)';
            var an_divider = (an_bright > 155) ? 'rgba(0, 0, 0, 0.12)' : 'rgba(255, 255, 255, 0.22)';
            var at_soft = (colorBrightness(at_hex) > 155) ? 'rgba(255, 255, 255, 0.88)' : 'rgba(15, 23, 42, 0.82)';

            var isAdminPage = document.body.classList.contains('admin-theme');
            if (isAdminPage) {
                var adminVars = {
                    '--primary': ap_hex,
                    '--primary-dark': ap_dark,
                    '--primary-soft': ap_soft,
                    '--primary-red': ap_hex,
                    '--primary-red-dark': ap_dark,
                    '--primary-red-light': ap_soft,
                    '--on-primary': ap_on,
                    '--admin-primary': ap_hex,
                    '--sb-primary': ap_hex,
                    '--sb-primary-hover': ap_dark,
                    '--nav-bg': an_hex,
                    '--nav-text': at_hex,
                    '--nav-text-soft': at_soft,
                    '--nav-accent': at_hex,
                    '--nav-chip-bg': an_chipBg,
                    '--nav-divider': an_divider,
                    '--nav-drawer-bg': ap_dark,
                    '--nav-cta-bg': ap_on,
                    '--nav-cta-text': ap_hex
                };
                for (var ak in adminVars) {
                    try {
                        document.documentElement.style.setProperty(ak, adminVars[ak], 'important');
                        document.body.style.setProperty(ak, adminVars[ak], 'important');
                    } catch(e) {}
                }
            }

            liveThemeRules += `
                body.admin-theme, html body.admin-theme {
                    --primary: ${ap_hex} !important;
                    --primary-dark: ${ap_dark} !important;
                    --primary-soft: ${ap_soft} !important;
                    --sb-primary: ${ap_hex} !important;
                    --sb-primary-hover: ${ap_dark} !important;
                    --admin-primary: ${ap_hex} !important;
                    --on-primary: ${ap_on} !important;
                    --nav-bg: ${an_hex} !important;
                    --nav-text: ${at_hex} !important;
                    --nav-text-soft: ${at_soft} !important;
                    --nav-accent: ${at_hex} !important;
                    --nav-chip-bg: ${an_chipBg} !important;
                    --nav-divider: ${an_divider} !important;
                }
                body.admin-theme header#site-header, html body.admin-theme header#site-header {
                    background: ${an_hex} !important;
                    border-bottom: 1px solid ${an_divider} !important;
                }
                body.admin-theme header#site-header .logo-text h1,
                body.admin-theme .logo-section .logo-text h1,
                body.admin-theme .logo-text h1 {
                    color: ${at_hex} !important;
                    -webkit-text-fill-color: ${at_hex} !important;
                }
                body.admin-theme header#site-header .logo-text p,
                body.admin-theme .logo-section .logo-text p,
                body.admin-theme .logo-text p {
                    color: ${at_hex} !important;
                    -webkit-text-fill-color: ${at_hex} !important;
                    opacity: 0.88;
                }
                html body.admin-theme .nav-menu > a:not(.btn):not(.profile-dropdown-item),
                html body.admin-theme .nav-menu a:not(.btn):not(.profile-dropdown-item):not(.dropdown-content a):not(.dropdown-item),
                body.admin-theme .nav-menu > a:not(.btn):not(.profile-dropdown-item),
                body.admin-theme .nav-menu a:not(.btn):not(.profile-dropdown-item):not(.dropdown-content a):not(.dropdown-item),
                body.admin-theme .nav-menu > a:not(.profile-dropdown-item),
                body.admin-theme .nav-menu > .dropdown > .dropbtn {
                    color: ${at_hex} !important;
                    -webkit-text-fill-color: ${at_hex} !important;
                }
                html body.admin-theme .nav-menu > a:not(.btn):not(.profile-dropdown-item) i,
                html body.admin-theme .nav-menu a:not(.btn):not(.profile-dropdown-item):not(.dropdown-content a):not(.dropdown-item) i,
                body.admin-theme .nav-menu > a:not(.btn):not(.profile-dropdown-item) i,
                body.admin-theme .nav-menu a:not(.btn):not(.profile-dropdown-item):not(.dropdown-content a):not(.dropdown-item) i,
                body.admin-theme .nav-menu > a:not(.profile-dropdown-item) i,
                body.admin-theme .nav-menu > .dropdown > .dropbtn i {
                    color: ${at_hex} !important;
                    -webkit-text-fill-color: ${at_hex} !important;
                }
                body.admin-theme .nav-menu > a:not(.profile-dropdown-item):hover,
                body.admin-theme .nav-menu > .dropdown > .dropbtn:hover {
                    background: ${an_chipBg} !important;
                    color: ${at_hex} !important;
                    -webkit-text-fill-color: ${at_hex} !important;
                }
                body.admin-theme .nav-menu > a:not(.profile-dropdown-item):hover i,
                body.admin-theme .nav-menu > .dropdown > .dropbtn:hover i {
                    color: ${at_hex} !important;
                    -webkit-text-fill-color: ${at_hex} !important;
                    opacity: 1 !important;
                }
                body.admin-theme .nav-menu > a.active:not(.profile-dropdown-item),
                body.admin-theme .nav-menu > .dropdown > .dropbtn.active {
                    background: ${an_chipBg} !important;
                    color: ${at_hex} !important;
                    -webkit-text-fill-color: ${at_hex} !important;
                }
                body.admin-theme .nav-menu > a.active:not(.profile-dropdown-item) i,
                body.admin-theme .nav-menu > .dropdown > .dropbtn.active i {
                    color: ${at_hex} !important;
                    -webkit-text-fill-color: ${at_hex} !important;
                    opacity: 1 !important;
                }
                body.admin-theme .profile-trigger-name {
                    color: ${at_hex} !important;
                    -webkit-text-fill-color: ${at_hex} !important;
                    text-shadow: none !important;
                }
                body.admin-theme .profile-trigger-caret {
                    color: ${at_soft} !important;
                    -webkit-text-fill-color: ${at_soft} !important;
                }
                body.admin-theme .profile-trigger-btn {
                    background: ${an_chipBg} !important;
                    border-color: ${an_divider} !important;
                }
                body.admin-theme .profile-trigger-btn:hover {
                    background: ${an_chipBg} !important;
                    border-color: ${an_divider} !important;
                    filter: brightness(0.92);
                }
                body.admin-theme .user-profile-dropdown {
                    border-left-color: ${an_divider} !important;
                }
                body.admin-theme .btn-primary {
                    background: ${ap_hex} !important;
                    border-color: ${ap_hex} !important;
                    color: ${ap_on} !important;
                }
                body.admin-theme .btn-filter.active,
                body.admin-theme .admin-chip.active {
                    background: ${ap_hex} !important;
                    border-color: ${ap_hex} !important;
                    color: ${ap_on} !important;
                }
                body.admin-theme .nav-hamburger-btn,
                body.admin-theme .nav-hamburger-btn i {
                    color: ${at_hex} !important;
                    -webkit-text-fill-color: ${at_hex} !important;
                }
                body.admin-theme .drawer-brand-subtitle {
                    color: ${ap_hex} !important;
                }
                body.admin-theme .drawer-nav-item:hover {
                    background: ${ap_soft} !important;
                    color: ${ap_hex} !important;
                }
                body.admin-theme .drawer-nav-item:hover i {
                    color: ${ap_hex} !important;
                }
                body.admin-theme .drawer-nav-item.active {
                    background: ${ap_soft} !important;
                    color: ${ap_hex} !important;
                }
                body.admin-theme .drawer-nav-item.active i {
                    color: ${ap_hex} !important;
                }
                body.admin-theme .logout-stripe {
                    background: linear-gradient(90deg, ${ap_hex} 0%, ${ap_dark} 100%) !important;
                }
                body.admin-theme .logout-btn-confirm {
                    background: linear-gradient(135deg, ${ap_hex} 0%, ${ap_dark} 100%) !important;
                }
                body.admin-theme .nav-tabs .nav-link.active,
                body.admin-theme .nav-pills .nav-link.active,
                body.admin-theme .settings-tab-btn.active,
                body.admin-theme .tab-pill.active {
                    background-color: ${ap_hex} !important;
                    border-color: ${ap_hex} !important;
                    color: ${ap_on} !important;
                    -webkit-text-fill-color: ${ap_on} !important;
                }
                body.admin-theme .autocomplete-item:hover,
                body.admin-theme .autocomplete-item.active-item {
                    background-color: ${ap_soft} !important;
                    color: ${ap_hex} !important;
                }
                body.admin-theme .autocomplete-badge {
                    background: ${ap_soft} !important;
                    color: ${ap_hex} !important;
                    border: 1px solid ${ap_soft} !important;
                }
            `;
        }

        // Apply theme rules if updated
        if (affectsTheme && liveThemeRules) {
            var liveThemeStyle = document.getElementById('app-live-theme-overrides');
            if (!liveThemeStyle) {
                liveThemeStyle = document.createElement('style');
                liveThemeStyle.id = 'app-live-theme-overrides';
                document.head.appendChild(liveThemeStyle);
            }
            liveThemeStyle.textContent = liveThemeRules;
        }

        // 5b. Update Universal Background Picture & Mode in Real-Time (ONLY if background category or full sync)
        var liveBgRules = '';
        if (affectsBackground && (data.app_background_image || data.app_bg_mode || data.app_bg_slideshow)) {
            var bgUrl = data.app_background_image || '';
            var bgMode = data.app_bg_mode || 'slideshow';
            var slides = Array.isArray(data.app_bg_slideshow) ? data.app_bg_slideshow : [];
            if (slides.length === 0 && bgUrl) {
                slides = [bgUrl];
            }

            if (bgMode === 'single' || slides.length <= 1) {
                var singleTarget = bgUrl || (slides.length > 0 ? slides[0] : '');
                liveBgRules += `
                    body:not(.auth-page)::before {
                        content: "" !important;
                        position: fixed !important;
                        inset: 0 !important;
                        width: 100vw !important;
                        height: 100vh !important;
                        background-repeat: no-repeat !important;
                        background-position: center center !important;
                        background-size: cover !important;
                        background-image: url('${singleTarget}') !important;
                        opacity: 0.12 !important;
                        animation: none !important;
                        z-index: 0 !important;
                        pointer-events: none !important;
                    }
                    body::after {
                        content: "" !important;
                        position: fixed !important;
                        inset: 0 !important;
                        width: 100vw !important;
                        height: 100vh !important;
                        background-repeat: no-repeat !important;
                        background-position: center center !important;
                        background-size: cover !important;
                        background-image: url('${singleTarget}') !important;
                        opacity: 0.28 !important;
                        animation: none !important;
                        z-index: 0 !important;
                        pointer-events: none !important;
                    }
                `;
            } else {
                var count = slides.length;
                var duration = count * 6;
                var kfNormal = `@keyframes palomponBgSlideshowLive {\n`;
                var kfAuth   = `@keyframes palomponBgSlideshowAuthLive {\n`;
                for (var i = 0; i < count; i++) {
                    var sUrl = slides[i];
                    var startPct = Math.round((i / count) * 1000) / 10;
                    var holdPct  = Math.round(((i + 0.85) / count) * 1000) / 10;
                    var fadePct  = Math.round(((i + 0.95) / count) * 1000) / 10;
                    // NOTE: NEVER use !important inside @keyframes blocks as browsers discard it as invalid CSS!
                    kfNormal += `    ${startPct}%, ${holdPct}% { background-image: url('${sUrl}'); opacity: 0.12; }\n`;
                    kfNormal += `    ${fadePct}% { opacity: 0.03; }\n`;
                    kfAuth   += `    ${startPct}%, ${holdPct}% { background-image: url('${sUrl}'); opacity: 0.22; }\n`;
                    kfAuth   += `    ${fadePct}% { opacity: 0.03; }\n`;
                }
                kfNormal += `    100% { opacity: 0.12; }\n`;
                kfNormal += `}\n`;
                kfAuth   += `    100% { opacity: 0.22; }\n`;
                kfAuth   += `}\n`;

                liveBgRules += `
                    ${kfNormal}
                    ${kfAuth}
                    body:not(.auth-page)::before {
                        content: "" !important;
                        position: fixed !important;
                        inset: 0 !important;
                        width: 100vw !important;
                        height: 100vh !important;
                        background-repeat: no-repeat !important;
                        background-position: center center !important;
                        background-size: cover !important;
                        background-image: url('${slides[0]}');
                        opacity: 0.12 !important;
                        animation: palomponBgSlideshowLive ${duration}s infinite ease-in-out !important;
                        z-index: 0 !important;
                        pointer-events: none !important;
                    }
                    body::after {
                        content: "" !important;
                        position: fixed !important;
                        inset: 0 !important;
                        width: 100vw !important;
                        height: 100vh !important;
                        background-repeat: no-repeat !important;
                        background-position: center center !important;
                        background-size: cover !important;
                        background-image: url('${slides[0]}');
                        opacity: 0.22 !important;
                        animation: palomponBgSlideshowAuthLive ${duration}s infinite ease-in-out !important;
                        z-index: 0 !important;
                        pointer-events: none !important;
                    }
                `;
            }
            if (typeof window.applyLiveBgMode === 'function') {
                window.applyLiveBgMode(bgMode, bgUrl, slides);
            }
        }

        if (affectsBackground && liveBgRules) {
            var liveBgStyle = document.getElementById('app-live-bg-overrides');
            if (!liveBgStyle) {
                liveBgStyle = document.createElement('style');
                liveBgStyle.id = 'app-live-bg-overrides';
                document.head.appendChild(liveBgStyle);
            }
            liveBgStyle.textContent = liveBgRules;
        }

        // 6. Update Footer Text
        if (affectsFooter) {
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
        }

        // 7. Broadcast event for page-specific custom listeners
        try {
            document.dispatchEvent(new CustomEvent('pttm:branding-applied', { detail: data }));
        } catch(e) {}
    }

    // Cross-tab broadcast channel for immediate multi-tab sync across all open tabs/windows
    var _brandingChannel = null;
    try {
        if (window.BroadcastChannel) {
            _brandingChannel = new BroadcastChannel('pttm_branding_channel');
            _brandingChannel.onmessage = function(e) {
                if (e.data && e.data.type === 'branding_updated' && e.data.data) {
                    applyLiveBranding(e.data.data);
                }
            };
        }
    } catch(e) {}

    // Cross-tab localStorage fallback for instant cross-tab sync
    window.addEventListener('storage', function(e) {
        if (e.key === 'pttm_live_branding' && e.newValue) {
            try {
                var parsed = JSON.parse(e.newValue);
                if (parsed && parsed.data) {
                    applyLiveBranding(parsed.data);
                }
            } catch(err) {}
        }
    });

    function broadcastLiveBranding(data) {
        if (!data) return;
        applyLiveBranding(data);
        if (_brandingChannel) {
            try {
                _brandingChannel.postMessage({ type: 'branding_updated', data: data });
            } catch(e) {}
        }
        try {
            localStorage.setItem('pttm_live_branding', JSON.stringify({ data: data, time: Date.now() }));
        } catch(e) {}
    }

    window.applyLiveBranding = applyLiveBranding;
    window.broadcastLiveBranding = broadcastLiveBranding;
    window.QueueWS.applyLiveBranding = applyLiveBranding;
    window.QueueWS.broadcastLiveBranding = broadcastLiveBranding;

})(window);
