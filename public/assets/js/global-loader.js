/**
 * Global Top Progress Bar & Universal Loader System (GlobalLoader)
 * ─────────────────────────────────────────────────────────────
 * Ultra-lightweight, non-blocking progress bar (YouTube / GitHub style).
 * Gives instant visual feedback on user-initiated AJAX requests, internal
 * navigations, and valid form submissions.
 *
 * Adaptive / Debounced Loader Pattern:
 *   • Fast / Responsive System (< 250ms): The loader is NEVER shown. Zero visual
 *     flicker, zero layout jumping, giving a blazing-fast feel.
 *   • Slow Operations (> 250ms): The top progress bar and button spinners smoothly
 *     fade in to reassure the user that work is in progress.
 *   • Guaranteed Minimum Visibility (~250ms): If the loader was displayed because
 *     an operation took > 250ms, it remains visible for at least 250ms to ensure
 *     a clean, professional completion animation rather than an abrupt blink.
 *   • Smart Silent Polling Filter: Ignores background polls (/api/queue-status, /status, etc.).
 *   • Never blocks clicks or input interactions (pointer-events: none).
 *
 * API:
 *   GlobalLoader.start([immediate])
 *   GlobalLoader.done()
 *   GlobalLoader.set(percent)
 *   GlobalLoader.isRunning()
 *   GlobalLoader.isVisible()
 *   GlobalLoader.setDelay(ms)
 *   GlobalLoader.getDelay()
 *   GlobalLoader.showTableLoader(container, label, [immediate])
 *   GlobalLoader.hideTableLoader(container)
 *   GlobalLoader.showButtonSpinner(btn, label, [immediate])
 *   GlobalLoader.hideButtonSpinner(btn)
 */
(function (window, document) {
    'use strict';

    if (window.GlobalLoader) return; // Prevent duplicate init

    var activeRequests = 0;
    var progress = 0;
    var startTime = 0;
    var trickleTimer = null;
    var safetyTimer = null;
    var showTimer = null;
    var isRunning = false;
    var isVisible = false;
    var barEl = null;
    var innerEl = null;
    var glowEl = null;

    // Adaptive Delay Thresholds (in milliseconds)
    var SHOW_DELAY = 250;      // Threshold before showing top progress bar
    var MIN_VISIBLE = 250;     // Minimum display time if the bar became visible
    var BUTTON_DELAY = 200;    // Threshold before showing button spinner
    var TABLE_DELAY = 200;     // Threshold before showing table loader overlay

    // Background polling endpoints that must NEVER trigger the progress bar
    var SILENT_PATTERNS = [
        /\/api\/queue-status/i,
        /\/status(\?|$)/i,
        /\/schedules\/status/i,
        /\/api\/fares/i,
        /\/api\/announcements/i,
        /\/api\/check-vehicle-availability/i,
        /\/admin\/vehicles\/check-plate/i,
        /\/ws(\/|$)/i,
        /[?&]silent=1/i,
        /[?&]silent=true/i
    ];

    /** Self-inject base CSS rules if not already present */
    function injectStyles() {
        if (document.getElementById('global-loader-injected-css')) return;
        var style = document.createElement('style');
        style.id = 'global-loader-injected-css';
        style.textContent = [
            '/* Global Progress Bar (4px Vibrant Glow) */',
            '.global-progress-bar {',
            '  position: fixed !important; top: 0 !important; left: 0 !important; width: 100% !important; height: 4px !important; z-index: 999999 !important;',
            '  pointer-events: none !important; user-select: none !important; -webkit-user-select: none !important;',
            '  opacity: 0; transition: opacity 0.25s ease;',
            '}',
            '.global-progress-bar.is-active { opacity: 1; }',
            '.global-progress-bar.is-done { opacity: 0; transition: opacity 0.3s ease 0.12s; }',
            '.global-progress-bar-inner {',
            '  position: absolute; top: 0; left: 0; height: 100%; width: 0%;',
            '  background: linear-gradient(90deg, #b91c1c 0%, #ea580c 45%, #f59e0b 80%, #38bdf8 100%);',
            '  box-shadow: 0 0 16px rgba(234, 88, 12, 1), 0 0 8px rgba(185, 28, 28, 1);',
            '  transition: width 0.22s cubic-bezier(0.4, 0, 0.2, 1);',
            '  border-radius: 0 3px 3px 0;',
            '}',
            '.global-progress-bar-glow {',
            '  position: absolute; right: 0; top: -3px; width: 85px; height: 10px;',
            '  background: radial-gradient(circle, rgba(255, 255, 255, 0.95) 0%, rgba(249, 115, 22, 0.75) 45%, transparent 80%);',
            '  border-radius: 50%; box-shadow: 0 0 10px rgba(249, 115, 22, 1);',
            '}',
            '/* Mobile browsers already provide their own top loading bar. Keep only contextual button/table loaders there. */',
            '@media (max-width: 768px), (hover: none) and (pointer: coarse) {',
            '  .global-progress-bar { display: none !important; }',
            '}',
            '/* In-Table / In-Card Loading Overlay */',
            '.table-loader-overlay {',
            '  position: absolute; top: 0; left: 0; width: 100%; height: 100%; min-height: 100px;',
            '  background: rgba(255, 255, 255, 0.92);',
            '  display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px;',
            '  z-index: 50; border-radius: inherit; transition: opacity 0.2s ease;',
            '}',
            '.table-loader-spinner {',
            '  width: 2rem; height: 2rem; border: 3px solid rgba(234, 88, 12, 0.2);',
            '  border-top-color: #ea580c; border-radius: 50%;',
            '  animation: gl-spin 0.7s linear infinite;',
            '}',
            '.table-loader-text { font-size: 13.5px; font-weight: 600; color: #475569; }',
            '@keyframes gl-spin { to { transform: rotate(360deg); } }',
            '/* In-Button Loading State */',
            '.btn.gl-btn-loading { pointer-events: none !important; opacity: 0.88 !important; }',
            '.gl-btn-spinner {',
            '  display: inline-block; width: 1em; height: 1em; vertical-align: -0.15em;',
            '  border: 2px solid currentColor; border-right-color: transparent;',
            '  border-radius: 50%; animation: gl-spin 0.65s linear infinite; margin-right: 6px;',
            '}',
            '/* Validation Shake Effect */',
            '@keyframes gl-shake {',
            '  0%, 100% { transform: translateX(0); }',
            '  20%, 60% { transform: translateX(-6px); }',
            '  40%, 80% { transform: translateX(6px); }',
            '}',
            '.gl-shake { animation: gl-shake 0.38s ease-in-out !important; }'
        ].join('\n');
        document.head.appendChild(style);
    }

    /** Create and inject the loader elements into DOM */
    function ensureElements() {
        injectStyles();
        if (barEl && document.body && document.body.contains(barEl)) return;

        barEl = document.getElementById('global-progress-bar');
        if (!barEl) {
            barEl = document.createElement('div');
            barEl.id = 'global-progress-bar';
            barEl.className = 'global-progress-bar';
            barEl.setAttribute('aria-hidden', 'true');

            innerEl = document.createElement('div');
            innerEl.className = 'global-progress-bar-inner';

            glowEl = document.createElement('div');
            glowEl.className = 'global-progress-bar-glow';

            innerEl.appendChild(glowEl);
            barEl.appendChild(innerEl);
        } else {
            innerEl = barEl.querySelector('.global-progress-bar-inner');
            glowEl = barEl.querySelector('.global-progress-bar-glow');
        }

        if (document.body && !document.body.contains(barEl)) {
            document.body.appendChild(barEl);
        }
    }

    /** Set the width percentage */
    function setProgress(pct) {
        ensureElements();
        progress = Math.max(0, Math.min(100, pct));
        if (innerEl) {
            innerEl.style.width = progress + '%';
        }
    }

    /** Visually render the active progress bar (called only after threshold delay) */
    function renderStart() {
        isVisible = true;
        if (barEl) {
            barEl.classList.remove('is-done');
            barEl.classList.add('is-active');
        }
        setProgress(20 + Math.random() * 15); // Jump to ~20-35%

        if (trickleTimer) clearInterval(trickleTimer);
        trickleTimer = setInterval(function () {
            if (progress < 85) {
                var step = (85 - progress) * 0.14;
                if (step < 0.6) step = 0.6;
                setProgress(progress + step);
            }
        }, 180);
    }

    /**
     * Start loader with adaptive threshold debouncing.
     * If the action completes within SHOW_DELAY (< 250ms), the loader is NEVER shown.
     * If the action takes >= SHOW_DELAY, it smoothly reveals the progress bar.
     * @param {boolean} [immediate=false] Optional flag to bypass debounce (e.g. for heavy long exports)
     */
    function start(immediate) {
        ensureElements();
        if (safetyTimer) clearTimeout(safetyTimer);

        if (!isRunning) {
            isRunning = true;
            isVisible = false;
            startTime = Date.now();

            if (showTimer) {
                clearTimeout(showTimer);
                showTimer = null;
            }

            if (immediate || SHOW_DELAY <= 0) {
                renderStart();
            } else {
                showTimer = setTimeout(function () {
                    if (isRunning && !isVisible) {
                        renderStart();
                    }
                }, SHOW_DELAY);
            }
        }

        // Safety watchdog: automatically complete after 12s if request hangs
        safetyTimer = setTimeout(function () {
            if (isRunning) {
                forceDone();
            }
        }, 12000);
    }

    /**
     * Complete loader operation.
     * - If completed in < SHOW_DELAY: cancels timer immediately. No UI flicker whatsoever.
     * - If displayed: ensures a brief minimum duration (MIN_VISIBLE) so the completion feels smooth.
     */
    function done() {
        if (!isRunning) return;

        if (showTimer) {
            clearTimeout(showTimer);
            showTimer = null;
        }
        if (safetyTimer) {
            clearTimeout(safetyTimer);
            safetyTimer = null;
        }

        // Fast response: request completed before SHOW_DELAY threshold!
        if (!isVisible) {
            isRunning = false;
            activeRequests = 0;
            if (trickleTimer) {
                clearInterval(trickleTimer);
                trickleTimer = null;
            }
            setProgress(0);
            var fastLoadingBtns = document.querySelectorAll('.gl-btn-loading');
            for (var f = 0; f < fastLoadingBtns.length; f++) {
                hideButtonSpinner(fastLoadingBtns[f]);
            }
            return;
        }

        // Slow response: bar was actually visible to user.
        if (trickleTimer) {
            clearInterval(trickleTimer);
            trickleTimer = null;
        }

        var visibleElapsed = Date.now() - (startTime + SHOW_DELAY);
        var remainingDelay = (visibleElapsed < MIN_VISIBLE) ? (MIN_VISIBLE - visibleElapsed) : 0;

        setTimeout(function () {
            setProgress(100);

            setTimeout(function () {
                if (barEl) {
                    barEl.classList.add('is-done');
                }
                setTimeout(function () {
                    if (barEl) {
                        barEl.classList.remove('is-active', 'is-done');
                        setProgress(0);
                    }
                    isRunning = false;
                    isVisible = false;
                    activeRequests = 0;
                    var slowLoadingBtns = document.querySelectorAll('.gl-btn-loading');
                    for (var s = 0; s < slowLoadingBtns.length; s++) {
                        hideButtonSpinner(slowLoadingBtns[s]);
                    }
                }, 280);
            }, 160);
        }, remainingDelay);
    }

    function forceDone() {
        activeRequests = 0;
        if (showTimer) {
            clearTimeout(showTimer);
            showTimer = null;
        }
        if (trickleTimer) {
            clearInterval(trickleTimer);
            trickleTimer = null;
        }
        if (safetyTimer) {
            clearTimeout(safetyTimer);
            safetyTimer = null;
        }
        if (barEl) {
            barEl.classList.remove('is-active', 'is-done');
        }
        setProgress(0);
        isRunning = false;
        isVisible = false;

        var allLoadingBtns = document.querySelectorAll('.gl-btn-loading');
        for (var a = 0; a < allLoadingBtns.length; a++) {
            hideButtonSpinner(allLoadingBtns[a]);
        }
    }

    /** Helper to check if a URL or Request should be ignored */
    function isSilentRequest(url, headers) {
        if (!url) return false;
        var urlStr = typeof url === 'string' ? url : (url.url || '');

        // Check silent pattern matches
        for (var i = 0; i < SILENT_PATTERNS.length; i++) {
            if (SILENT_PATTERNS[i].test(urlStr)) return true;
        }

        // Check headers for X-Silent
        if (headers) {
            try {
                if (typeof headers.get === 'function') {
                    var val = headers.get('X-Silent') || headers.get('x-silent');
                    if (val === 'true' || val === true) return true;
                } else if (Array.isArray(headers)) {
                    for (var h = 0; h < headers.length; h++) {
                        var k = headers[h][0];
                        var v = headers[h][1];
                        if (k && String(k).toLowerCase() === 'x-silent' && (v === 'true' || v === true)) {
                            return true;
                        }
                    }
                } else if (typeof headers === 'object') {
                    for (var key in headers) {
                        if (headers.hasOwnProperty(key) && String(key).toLowerCase() === 'x-silent') {
                            var ov = String(headers[key]).toLowerCase();
                            if (ov === 'true' || ov === '1') return true;
                        }
                    }
                }
            } catch (e) { /* ignore header inspection error */ }
        }

        return false;
    }

    /** Hook into window.fetch safely without altering behavior or arguments */
    function hookFetch() {
        if (typeof window.fetch !== 'function') return;
        var originalFetch = window.fetch;

        window.fetch = function (input, init) {
            var url = (typeof input === 'string') ? input : (input ? input.url : '');
            var headers = (init && init.headers) ? init.headers : (input && input.headers ? input.headers : null);

            var silent = isSilentRequest(url, headers);

            if (!silent) {
                activeRequests++;
                start();
            }

            return originalFetch.apply(window, arguments)
                .then(function (response) {
                    if (!silent) {
                        activeRequests = Math.max(0, activeRequests - 1);
                        if (activeRequests === 0) done();
                    }
                    return response;
                })
                .catch(function (error) {
                    if (!silent) {
                        activeRequests = Math.max(0, activeRequests - 1);
                        if (activeRequests === 0) done();
                    }
                    throw error;
                });
        };
    }

    /** Hook into XMLHttpRequest safely */
    function hookXHR() {
        if (typeof window.XMLHttpRequest !== 'function') return;
        var originalOpen = XMLHttpRequest.prototype.open;
        var originalSend = XMLHttpRequest.prototype.send;
        var originalSetRequestHeader = XMLHttpRequest.prototype.setRequestHeader;

        XMLHttpRequest.prototype.open = function (method, url) {
            this._gl_url = url;
            this._gl_silent = isSilentRequest(url, null);
            return originalOpen.apply(this, arguments);
        };

        XMLHttpRequest.prototype.setRequestHeader = function (header, value) {
            if (header && String(header).toLowerCase() === 'x-silent' && (value === 'true' || value === true)) {
                this._gl_silent = true;
            }
            return originalSetRequestHeader.apply(this, arguments);
        };

        XMLHttpRequest.prototype.send = function () {
            var xhr = this;
            if (!xhr._gl_silent) {
                activeRequests++;
                start();

                var completed = false;
                var onComplete = function () {
                    if (completed) return;
                    completed = true;
                    xhr.removeEventListener('loadend', onComplete);
                    xhr.removeEventListener('error', onComplete);
                    xhr.removeEventListener('abort', onComplete);
                    xhr.removeEventListener('timeout', onComplete);
                    activeRequests = Math.max(0, activeRequests - 1);
                    if (activeRequests === 0) done();
                };

                xhr.addEventListener('loadend', onComplete);
                xhr.addEventListener('error', onComplete);
                xhr.addEventListener('abort', onComplete);
                xhr.addEventListener('timeout', onComplete);
            }
            return originalSend.apply(this, arguments);
        };
    }

    /** Determine contextual loading text based on button content, attributes, and actions */
    function getButtonLoadingLabel(btn, customLabel) {
        if (customLabel) return customLabel;
        if (!btn) return 'Processing\u2026';

        var explicit = btn.getAttribute('data-loading-text');
        if (explicit) return explicit;

        // Extract visible text, fallback to title or aria-label for icon buttons
        var rawText = (btn.innerText || btn.textContent || btn.value || '').trim().replace(/\s+/g, ' ');
        if (!rawText) {
            rawText = (btn.getAttribute('title') || btn.getAttribute('aria-label') || '').trim().replace(/\s+/g, ' ');
        }
        if (!rawText) return 'Processing\u2026';

        var lower = rawText.toLowerCase();

        // Specific actions & multi-word phrases first
        if (lower.indexOf('apply filter') !== -1 || lower.indexOf('apply') !== -1) return 'Applying\u2026';
        if (lower.indexOf('filter') !== -1) return 'Filtering\u2026';
        if (lower.indexOf('track status') !== -1 || lower.indexOf('search') !== -1 || lower.indexOf('find') !== -1) return 'Searching\u2026';
        if (lower.indexOf('sign in') !== -1 || lower.indexOf('log in') !== -1 || lower === 'login') return 'Signing in\u2026';
        if (lower.indexOf('sign out') !== -1 || lower.indexOf('log out') !== -1 || lower === 'logout') return 'Signing out\u2026';
        if (lower.indexOf('send') !== -1) return 'Sending\u2026';
        if (lower.indexOf('verify') !== -1) return 'Verifying\u2026';
        if (lower.indexOf('reset') !== -1) return 'Resetting\u2026';
        if (lower.indexOf('delete') !== -1 || lower.indexOf('remove') !== -1) return 'Deleting\u2026';
        if (lower.indexOf('update') !== -1 || lower.indexOf('edit') !== -1) return 'Updating\u2026';
        if (lower.indexOf('create') !== -1) return 'Creating\u2026';
        if (lower.indexOf('add') !== -1) return 'Adding\u2026';
        if (lower.indexOf('save') !== -1) return 'Saving\u2026';
        if (lower.indexOf('upload') !== -1 || lower.indexOf('import') !== -1) return 'Uploading\u2026';
        if (lower.indexOf('download') !== -1 || lower.indexOf('export') !== -1) return 'Exporting\u2026';
        if (lower.indexOf('submit report') !== -1 || lower.indexOf('submit message') !== -1 || lower.indexOf('submit') !== -1) return 'Submitting\u2026';
        if (lower.indexOf('depart') !== -1) return 'Departing\u2026';
        if (lower.indexOf('assign') !== -1) return 'Assigning\u2026';
        if (lower.indexOf('confirm') !== -1 || lower.indexOf('proceed') !== -1 || lower.indexOf('continue') !== -1) return 'Processing\u2026';
        if (lower.indexOf('refresh') !== -1 || lower.indexOf('reload') !== -1) return 'Refreshing\u2026';
        if (lower.indexOf('print') !== -1) return 'Printing\u2026';
        if (lower.indexOf('clear') !== -1) return 'Clearing\u2026';
        if (lower.indexOf('check') !== -1) return 'Checking\u2026';
        if (lower.indexOf('cancel') !== -1) return 'Cancelling\u2026';

        // Intelligent English gerund fallback from first word
        var firstWord = lower.split(' ')[0].replace(/[^a-z]/g, '');
        if (firstWord.length >= 3) {
            var gerund = firstWord;
            if (firstWord.endsWith('e') && !firstWord.endsWith('ee')) {
                gerund = firstWord.slice(0, -1) + 'ing';
            } else if (firstWord.endsWith('y')) {
                gerund = firstWord + 'ing';
            } else {
                gerund = firstWord + 'ing';
            }
            return gerund.charAt(0).toUpperCase() + gerund.slice(1) + '\u2026';
        }

        return 'Processing\u2026';
    }

    /**
     * Button Spinner Helpers with Adaptive Delay
     * If the action completes within BUTTON_DELAY (200ms), the button text/layout never flickers.
     */
    function showButtonSpinner(btn, customLabel, immediate) {
        if (!btn || btn.classList.contains('gl-btn-loading')) return;

        if (btn._gl_btn_timer) {
            clearTimeout(btn._gl_btn_timer);
            btn._gl_btn_timer = null;
        }

        function applySpinner() {
            if (!btn || btn.classList.contains('gl-btn-loading')) return;
            btn.classList.add('gl-btn-loading');
            btn.setAttribute('data-gl-orig-html', btn.innerHTML);

            var hasVisibleText = Boolean((btn.innerText || btn.textContent || btn.value || '').trim());
            var isIconOnly = !hasVisibleText && !customLabel && !btn.getAttribute('data-loading-text');

            if (isIconOnly) {
                btn.innerHTML = '<span class="gl-btn-spinner" style="margin-right:0;" aria-hidden="true"></span>';
            } else {
                var label = getButtonLoadingLabel(btn, customLabel);
                btn.innerHTML = '<span class="gl-btn-spinner" aria-hidden="true"></span> ' + label;
            }
        }

        if (immediate || BUTTON_DELAY <= 0) {
            applySpinner();
        } else {
            btn._gl_btn_timer = setTimeout(applySpinner, BUTTON_DELAY);
        }
    }

    function hideButtonSpinner(btn) {
        if (!btn) return;
        if (btn._gl_btn_timer) {
            clearTimeout(btn._gl_btn_timer);
            btn._gl_btn_timer = null;
        }
        if (!btn.classList.contains('gl-btn-loading')) return;
        var orig = btn.getAttribute('data-gl-orig-html');
        if (orig !== null) {
            btn.innerHTML = orig;
            btn.removeAttribute('data-gl-orig-html');
        }
        btn.classList.remove('gl-btn-loading');
    }

    /** Table / Card Overlay Loader Helpers with Adaptive Delay */
    function showTableLoader(container, labelText, immediate) {
        if (!container) return;
        var el = (typeof container === 'string') ? document.querySelector(container) : container;
        if (!el) return;

        if (el._gl_table_timer) {
            clearTimeout(el._gl_table_timer);
            el._gl_table_timer = null;
        }

        function applyOverlay() {
            if (!el) return;
            // Ensure relative positioning
            var curPos = window.getComputedStyle(el).position;
            if (!curPos || curPos === 'static') {
                el.style.position = 'relative';
            }

            var defaultText = 'Loading\u2026';
            if (el.id && (el.id.indexOf('schedule') !== -1 || el.classList.contains('schedule-card'))) {
                defaultText = 'Loading schedules\u2026';
            } else if (el.id && el.id.indexOf('fare') !== -1) {
                defaultText = 'Loading fares\u2026';
            } else if (el.id && el.id.indexOf('queue') !== -1) {
                defaultText = 'Updating queue\u2026';
            }

            var text = labelText || defaultText;

            var overlay = el.querySelector('.table-loader-overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.className = 'table-loader-overlay';
                overlay.innerHTML = '<div class="table-loader-spinner" aria-hidden="true"></div><div class="table-loader-text">' + text + '</div>';
                el.appendChild(overlay);
            } else {
                var textEl = overlay.querySelector('.table-loader-text');
                if (textEl) textEl.textContent = text;
            }
            overlay.style.opacity = '1';
            overlay.style.display = 'flex';
        }

        if (immediate || TABLE_DELAY <= 0) {
            applyOverlay();
        } else {
            el._gl_table_timer = setTimeout(applyOverlay, TABLE_DELAY);
        }
    }

    function hideTableLoader(container) {
        if (!container) return;
        var el = (typeof container === 'string') ? document.querySelector(container) : container;
        if (!el) return;

        if (el._gl_table_timer) {
            clearTimeout(el._gl_table_timer);
            el._gl_table_timer = null;
        }

        var overlay = el.querySelector('.table-loader-overlay');
        if (overlay) {
            overlay.style.opacity = '0';
            setTimeout(function () {
                if (overlay && overlay.parentNode) overlay.parentNode.removeChild(overlay);
            }, 200);
        }
    }

    /** Intercept Form Submissions & Internal Navigation Links */
    function setupInteractions() {
        // Form submits (standard POST/GET page transitions)
        document.addEventListener('submit', function (e) {
            var form = e.target;
            if (!form || form.hasAttribute('data-no-loader')) return;
            if (form.getAttribute('target') === '_blank') return;

            // HTML5 client-side validation check
            if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
                return;
            }

            var submitBtn = e.submitter || form.querySelector('button[type="submit"], input[type="submit"], button:not([type])');

            // Wait a micro-tick to let custom scripts (e.g. no-change-guard, modal validations) preventDefault
            setTimeout(function () {
                if (!e.defaultPrevented) {
                    try { sessionStorage.setItem('gl_navigating', '1'); } catch (err) {}
                    start();
                    if (submitBtn) showButtonSpinner(submitBtn);
                }
            }, 40);
        }, false);

        // Internal link clicks
        document.addEventListener('click', function (e) {
            var a = e.target.closest('a');
            if (!a || !a.href) return;

            // Exclude links meant for modals, dropdowns, tabs, accordions, downloads, or new tabs
            if (a.hasAttribute('data-no-loader') || a.hasAttribute('download')) return;
            if (a.hasAttribute('data-bs-toggle') || a.hasAttribute('data-toggle')) return;
            if (a.hasAttribute('data-bs-target') || a.hasAttribute('data-target')) return;
            if (a.target === '_blank') return;
            if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey || e.button !== 0) return;

            var rawHref = a.getAttribute('href') || '';
            if (!rawHref || rawHref.charAt(0) === '#' || rawHref.indexOf('javascript:') === 0 || rawHref.indexOf('mailto:') === 0 || rawHref.indexOf('tel:') === 0 || rawHref.indexOf('blob:') === 0 || rawHref.indexOf('data:') === 0) {
                return;
            }

            // Verify if same origin and actually navigating to a different page/search
            try {
                var url = new URL(a.href, window.location.href);
                if (url.origin !== window.location.origin) return;

                // If identical pathname and search, it's just an in-page anchor jump -> ignore
                if (url.pathname === window.location.pathname && url.search === window.location.search) {
                    return;
                }

                // Check after event dispatch if prevented by custom handler
                setTimeout(function () {
                    if (!e.defaultPrevented) {
                        try { sessionStorage.setItem('gl_navigating', '1'); } catch (err) {}
                        // Reset any stale loader state from a previous navigation or rapid click
                        // before starting a new one so pointer-events are never left stuck.
                        forceDone();
                        start();
                    }
                }, 20);
            } catch (err) { /* ignore */ }
        }, false);

        // Handle BFCache restore (Back/Forward navigation restores DOM from memory)
        window.addEventListener('pageshow', function () {
            forceDone();
        });

        // Clean loader state when the browser is actually leaving the page,
        // preventing pointer-events from being stuck on the incoming page.
        window.addEventListener('pagehide', function () {
            forceDone();
        });
        window.addEventListener('beforeunload', function () {
            forceDone();
        });
    }

    // Initialize hooks immediately
    hookFetch();
    hookXHR();

    function onInit() {
        ensureElements();
        setupInteractions();
        try {
            // Clean up navigation flag cleanly without replaying a fake progress bar
            sessionStorage.removeItem('gl_navigating');
        } catch (err) {}
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', onInit);
    } else {
        onInit();
    }

    // Expose comprehensive public API
    window.GlobalLoader = {
        start: start,
        done: done,
        set: setProgress,
        isRunning: function () { return isRunning; },
        isVisible: function () { return isVisible; },
        setDelay: function (ms) {
            if (typeof ms === 'number' && ms >= 0) {
                SHOW_DELAY = ms;
                BUTTON_DELAY = Math.min(ms, 200);
                TABLE_DELAY = Math.min(ms, 200);
            }
        },
        getDelay: function () { return SHOW_DELAY; },
        addSilentPattern: function (pattern) {
            if (pattern) SILENT_PATTERNS.push(pattern);
        },
        showTableLoader: showTableLoader,
        hideTableLoader: hideTableLoader,
        showButtonSpinner: showButtonSpinner,
        hideButtonSpinner: hideButtonSpinner
    };

})(window, document);
