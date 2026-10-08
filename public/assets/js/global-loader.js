/**
 * Non-blocking progress and pending feedback. All request arguments, response
 * identities, native form fields and existing button child nodes are preserved.
 * Visual timers never release an API-owned pending button or reset request counts.
 */
(function (window, document) {
    'use strict';
    if (window.GlobalLoader) return;

    var activeRequests = 0;
    var requestEpoch = 0;
    var progress = 0;
    var visibleAt = 0;
    var safetyTimer = null;
    var showTimer = null;
    var finishTimer = null;
    var fadeTimer = null;
    var isRunning = false;
    var isVisible = false;
    var barEl = null;
    var innerEl = null;
    var pendingButtons = new Map();
    var pendingForms = new Set();
    var tableContainers = new Set();
    var SHOW_DELAY = 250;
    var MIN_VISIBLE = 100;
    var BUTTON_DELAY = 200;
    var TABLE_DELAY = 200;
    var SILENT_PATTERNS = [
        /\/api\/queue-status/i, /\/status(\?|$)/i, /\/schedules\/status/i,
        /\/api\/fares/i, /\/api\/announcements/i, /\/api\/check-vehicle-availability/i,
        /\/ws(\/|$)/i,
        /[?&]silent=1/i, /[?&]silent=true/i
    ];

    function motionMode() {
        if (window.TerminalMotion) return window.TerminalMotion.getMode();
        return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'reduced' : 'full';
    }

    function injectStyles() {
        if (document.getElementById('global-loader-injected-css')) return;
        var style = document.createElement('style');
        style.id = 'global-loader-injected-css';
        style.textContent = [
            '.global-progress-bar { position:fixed !important; top:0 !important; left:0 !important; width:100% !important; height:4px !important; overflow:hidden; z-index:999999 !important; pointer-events:none !important; user-select:none !important; opacity:0; transition:opacity .16s ease; }',
            '.global-progress-bar.is-active { opacity:1; }',
            '.global-progress-bar.is-done { opacity:0; }',
            '.global-progress-bar-inner { position:absolute; top:0; left:0; height:100%; width:100%; transform:scaleX(0); transform-origin:left center; background:linear-gradient(90deg,#b91c1c,#ea580c 45%,#f59e0b 80%,#38bdf8); box-shadow:none; transition:transform .16s cubic-bezier(.2,.8,.2,1); border-radius:0 3px 3px 0; }',
            '@keyframes gl-progress-sweep { from { transform:translateX(-100%); } to { transform:translateX(350%); } }',
            '@media (prefers-reduced-motion:no-preference) { html:not([data-tq-motion="reduced"]) .global-progress-bar.is-active:not(.is-done)::after { content:""; position:absolute; inset:0 auto 0 0; width:30%; background:linear-gradient(90deg,transparent,#f59e0b,#38bdf8); animation:gl-progress-sweep 1.2s linear infinite !important; } }',
            'html[data-tq-page-hidden] .global-progress-bar::after { animation-play-state:paused !important; }',
            '.table-loader-overlay { position:absolute; inset:0; min-height:100px; background:rgba(255,255,255,.92); display:flex; flex-direction:column; align-items:center; justify-content:center; gap:10px; z-index:50; border-radius:inherit; pointer-events:none !important; transition:opacity .16s ease; }',
            '.gl-table-skeleton { width:72%; max-width:22rem; display:grid; gap:8px; }',
            '.gl-table-skeleton span { display:block; height:8px; border-radius:4px; background:#e2e8f0; }',
            '.gl-table-skeleton span:last-child { width:65%; }',
            '.table-loader-spinner { width:2rem; height:2rem; border:3px solid var(--primary-soft,#fed7aa); border-top-color:var(--primary,#ea580c); border-radius:50%; animation:gl-spin .7s linear infinite; }',
            '.table-loader-text { font-size:13.5px; font-weight:600; color:#475569; }',
            '.gl-btn-pending { cursor:progress; }',
            '.gl-btn-loading { pointer-events:auto !important; }',
            '.gl-btn-loading > :not(.gl-btn-feedback) { opacity:0 !important; }',
            '.gl-btn-feedback { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; gap:6px; padding:0 6px; box-sizing:border-box; border-radius:inherit; overflow:hidden; pointer-events:none; font-size:inherit; font-weight:inherit; line-height:1.2; opacity:1 !important; }',
            '.gl-btn-label { min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }',
            '.gl-btn-spinner { display:block; box-sizing:border-box; width:1em; height:1em; flex:0 0 1em; margin:0; padding:0; border:2px solid currentColor; border-right-color:transparent; border-radius:50%; transform-origin:center; animation:gl-spin .8s linear infinite; }',
            '@keyframes gl-spin { to { transform:rotate(360deg); } }',
            '@keyframes gl-shake { 0%,100% { transform:translateX(0); } 20%,60% { transform:translateX(-6px); } 40%,80% { transform:translateX(6px); } }',
            '.gl-shake { animation:gl-shake .24s ease-in-out !important; }',
            'html[data-tq-motion="lite"] .global-progress-bar-inner, html[data-tq-motion="reduced"] .global-progress-bar-inner { transition:none; will-change:auto; }',
            'html[data-tq-motion="reduced"] .gl-btn-spinner { animation:none !important; }',
            'html[data-tq-page-hidden] .gl-btn-spinner { animation-play-state:paused !important; }',
            '@media (prefers-reduced-motion:reduce) { .global-progress-bar, .global-progress-bar-inner, .table-loader-overlay { transition:none; } .gl-btn-spinner, .table-loader-spinner, .gl-shake { animation:none !important; } }'
        ].join('\n');
        document.head.appendChild(style);
    }

    function ensureElements() {
        injectStyles();
        if (barEl && barEl.isConnected) return;
        barEl = document.getElementById('global-progress-bar');
        if (!barEl) {
            barEl = document.createElement('div');
            barEl.id = 'global-progress-bar';
            barEl.className = 'global-progress-bar';
            barEl.setAttribute('aria-hidden', 'true');
            innerEl = document.createElement('div');
            innerEl.className = 'global-progress-bar-inner';
            barEl.appendChild(innerEl);
        } else {
            innerEl = barEl.querySelector('.global-progress-bar-inner');
        }
        if (document.body && !barEl.isConnected) document.body.appendChild(barEl);
    }

    function setProgress(pct) {
        ensureElements();
        progress = Math.max(0, Math.min(100, Number(pct) || 0));
        if (innerEl) innerEl.style.transform = 'scaleX(' + (progress / 100) + ')';
    }

    function clearCompletion() {
        if (finishTimer !== null) clearTimeout(finishTimer);
        if (fadeTimer !== null) clearTimeout(fadeTimer);
        finishTimer = fadeTimer = null;
    }

    function clearOperationTimers() {
        if (showTimer !== null) clearTimeout(showTimer);
        if (safetyTimer !== null) clearTimeout(safetyTimer);
        showTimer = safetyTimer = null;
    }

    function renderStart() {
        isVisible = true;
        visibleAt = Date.now();
        barEl.classList.remove('is-done');
        barEl.classList.add('is-active');
        if (progress === 0 || progress === 100) setProgress(25);
    }

    function start(immediate) {
        ensureElements();
        clearCompletion();
        if (safetyTimer !== null) clearTimeout(safetyTimer);
        if (!isRunning) {
            isRunning = true;
            if (isVisible || immediate || SHOW_DELAY <= 0) renderStart();
            else showTimer = setTimeout(function () {
                showTimer = null;
                if (isRunning) renderStart();
            }, SHOW_DELAY);
        }
        // This is a visual watchdog only. It must not unlock a still-pending request.
        safetyTimer = setTimeout(forceDone, 12000);
    }

    function done() {
        if (!isRunning) return;
        isRunning = false;
        clearOperationTimers();
        clearCompletion();
        if (!isVisible) { setProgress(0); return; }
        var wait = motionMode() === 'full' ? Math.max(0, MIN_VISIBLE - (Date.now() - visibleAt)) : 0;
        finishTimer = setTimeout(function () {
            finishTimer = null;
            setProgress(100);
            barEl.classList.add('is-done');
            fadeTimer = setTimeout(function () {
                fadeTimer = null;
                barEl.classList.remove('is-active', 'is-done');
                isVisible = false;
                setProgress(0);
            }, motionMode() === 'full' ? 160 : 0);
        }, wait);
    }

    function forceDone() {
        clearOperationTimers();
        clearCompletion();
        if (barEl) barEl.classList.remove('is-active', 'is-done');
        setProgress(0);
        isRunning = isVisible = false;
    }

    function finishRequest(epoch) {
        if (epoch !== requestEpoch) return;
        activeRequests = Math.max(0, activeRequests - 1);
        if (activeRequests === 0) done();
    }

    function isSilentRequest(url, headers) {
        var urlStr = typeof url === 'string' ? url : (url && (url.url || url.href) || '');
        if (SILENT_PATTERNS.some(function (pattern) { return pattern.test(urlStr); })) return true;
        try {
            if (headers && typeof headers.get === 'function') return String(headers.get('X-Silent')).toLowerCase() === 'true';
            if (Array.isArray(headers)) return headers.some(function (pair) { return String(pair[0]).toLowerCase() === 'x-silent' && /^(true|1)$/i.test(String(pair[1])); });
            if (headers) return Object.keys(headers).some(function (key) { return key.toLowerCase() === 'x-silent' && /^(true|1)$/i.test(String(headers[key])); });
        } catch (error) { /* Header inspection must never affect a request. */ }
        return false;
    }

    function hookFetch() {
        if (typeof window.fetch !== 'function') return;
        var originalFetch = window.fetch;
        window.fetch = function (input, init) {
            var silent = isSilentRequest(input, init && init.headers || input && input.headers);
            var epoch = requestEpoch;
            if (!silent) { activeRequests++; start(); }
            var request;
            try { request = originalFetch.apply(this, arguments); }
            catch (error) { if (!silent) finishRequest(epoch); throw error; }
            return request.then(function (response) {
                if (!silent) finishRequest(epoch);
                return response;
            }, function (error) {
                if (!silent) finishRequest(epoch);
                throw error;
            });
        };
    }

    function hookXHR() {
        if (typeof window.XMLHttpRequest !== 'function') return;
        var proto = window.XMLHttpRequest.prototype;
        var originalOpen = proto.open;
        var originalSend = proto.send;
        var originalHeader = proto.setRequestHeader;
        proto.open = function (method, url) {
            this._gl_silent = isSilentRequest(url);
            return originalOpen.apply(this, arguments);
        };
        proto.setRequestHeader = function (header, value) {
            if (String(header).toLowerCase() === 'x-silent' && /^(true|1)$/i.test(String(value))) this._gl_silent = true;
            return originalHeader.apply(this, arguments);
        };
        proto.send = function () {
            var xhr = this;
            var epoch = requestEpoch;
            var tracked = !xhr._gl_silent;
            var completed = false;
            function complete() {
                if (completed) return;
                completed = true;
                xhr.removeEventListener('loadend', complete);
                if (tracked) finishRequest(epoch);
            }
            if (tracked) { activeRequests++; start(); xhr.addEventListener('loadend', complete); }
            try { return originalSend.apply(xhr, arguments); }
            catch (error) { complete(); throw error; }
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
     * Lock duplicate activations immediately; animate feedback only after the delay.
     * Never disable the native submitter or change its name/value. Existing child
     * nodes and their handlers stay mounted under the absolute feedback overlay.
     * @param {HTMLElement|null} btn
     * @param {string} [customLabel]
     * @param {boolean} [immediate]
     */
    function showButtonSpinner(btn, customLabel, immediate) {
        if (!btn || pendingButtons.has(btn)) return;
        var state = {
            busy: btn.getAttribute('aria-busy'),
            disabled: btn.getAttribute('aria-disabled'),
            position: btn.style.position,
            overlay: null,
            textStyles: null,
            timer: null
        };
        pendingButtons.set(btn, state);
        btn.classList.add('gl-btn-pending');
        btn.setAttribute('aria-busy', 'true');
        btn.setAttribute('aria-disabled', 'true');
        var hasText = Boolean((btn.innerText || btn.textContent || btn.value || '').trim());
        var label = hasText || customLabel || btn.getAttribute('data-loading-text')
            ? getButtonLoadingLabel(btn, customLabel) : '';
        function applySpinner() {
            state.timer = null;
            if (!btn.isConnected || !pendingButtons.has(btn)) { hideButtonSpinner(btn); return; }
            // Inputs cannot have children: keep their successful-control value intact.
            if (btn.tagName === 'INPUT') return;
            var buttonStyle = window.getComputedStyle(btn);
            var color = buttonStyle.color;
            if (buttonStyle.position === 'static') btn.style.position = 'relative';
            var overlay = document.createElement('span');
            overlay.className = 'gl-btn-feedback';
            overlay.style.setProperty('color', color, 'important');
            overlay.style.setProperty('-webkit-text-fill-color', color, 'important');
            overlay.setAttribute('role', 'status');
            overlay.setAttribute('aria-live', 'polite');
            var spinner = document.createElement('span');
            spinner.className = 'gl-btn-spinner';
            spinner.setAttribute('aria-hidden', 'true');
            overlay.appendChild(spinner);
            if (label) {
                var labelEl = document.createElement('span');
                labelEl.className = 'gl-btn-label';
                labelEl.textContent = label;
                overlay.appendChild(labelEl);
            }
            // Theme !important rules (including WebKit text fill) can expose raw
            // text beneath the overlay. Hide its paint without replacing nodes,
            // changing button dimensions or disabling the native submitter.
            state.textStyles = ['color', '-webkit-text-fill-color'].map(function (property) {
                var original = {
                    property: property,
                    value: btn.style.getPropertyValue(property),
                    priority: btn.style.getPropertyPriority(property)
                };
                btn.style.setProperty(property, 'transparent', 'important');
                return original;
            });
            state.overlay = overlay;
            btn.appendChild(overlay);
            btn.classList.add('gl-btn-loading');
        }
        if (immediate || BUTTON_DELAY <= 0) applySpinner();
        else state.timer = setTimeout(applySpinner, BUTTON_DELAY);
    }

    function restoreAttribute(el, name, value) {
        if (value === null) el.removeAttribute(name);
        else el.setAttribute(name, value);
    }

    function hideButtonSpinner(btn) {
        var state = btn && pendingButtons.get(btn);
        if (!state) return;
        if (state.timer !== null) clearTimeout(state.timer);
        if (state.overlay) state.overlay.remove();
        if (state.textStyles) state.textStyles.forEach(function (original) {
            if (original.value) btn.style.setProperty(original.property, original.value, original.priority);
            else btn.style.removeProperty(original.property);
        });
        btn.style.position = state.position;
        restoreAttribute(btn, 'aria-busy', state.busy);
        restoreAttribute(btn, 'aria-disabled', state.disabled);
        btn.classList.remove('gl-btn-pending', 'gl-btn-loading');
        pendingButtons.delete(btn);
        if (btn.form) pendingForms.delete(btn.form);
    }

    function showTableLoader(container, labelText, immediate) {
        var el = typeof container === 'string' ? document.querySelector(container) : container;
        if (!el) return;
        tableContainers.add(el);
        clearTimeout(el._gl_table_timer);
        clearTimeout(el._gl_table_hide_timer);
        el._gl_table_hide_timer = null;
        function applyOverlay() {
            el._gl_table_timer = null;
            if (!el.isConnected) { tableContainers.delete(el); return; }
            if (window.getComputedStyle(el).position === 'static') {
                el._gl_table_original_position = el.style.position;
                el.style.position = 'relative';
            }
            var text = labelText || (el.id.indexOf('schedule') !== -1 ? 'Loading schedules\u2026'
                : el.id.indexOf('fare') !== -1 ? 'Loading fares\u2026'
                : el.id.indexOf('queue') !== -1 ? 'Updating queue\u2026' : 'Loading\u2026');
            var overlay = el.querySelector('.table-loader-overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.className = 'table-loader-overlay';
                var skeleton = document.createElement('div');
                skeleton.className = 'gl-table-skeleton';
                skeleton.setAttribute('aria-hidden', 'true');
                for (var i = 0; i < 3; i++) skeleton.appendChild(document.createElement('span'));
                var label = document.createElement('div');
                label.className = 'table-loader-text';
                label.setAttribute('role', 'status');
                overlay.appendChild(skeleton);
                overlay.appendChild(label);
                el.appendChild(overlay);
            }
            overlay.querySelector('.table-loader-text').textContent = text;
            overlay.style.opacity = '1';
        }
        if (immediate || TABLE_DELAY <= 0) applyOverlay();
        else el._gl_table_timer = setTimeout(applyOverlay, TABLE_DELAY);
    }

    function hideTableLoader(container, immediate) {
        var el = typeof container === 'string' ? document.querySelector(container) : container;
        if (!el) return;
        clearTimeout(el._gl_table_timer);
        clearTimeout(el._gl_table_hide_timer);
        el._gl_table_timer = null;
        var overlay = el.querySelector('.table-loader-overlay');
        function removeOverlay() {
            if (overlay) overlay.remove();
            el._gl_table_hide_timer = null;
            if (el._gl_table_original_position !== undefined) {
                el.style.position = el._gl_table_original_position;
                delete el._gl_table_original_position;
            }
            tableContainers.delete(el);
        }
        if (overlay && !immediate && motionMode() === 'full') {
            overlay.style.opacity = '0';
            el._gl_table_hide_timer = setTimeout(removeOverlay, 160);
        } else removeOverlay();
    }

    function resetPage() {
        requestEpoch++;
        activeRequests = 0;
        forceDone();
        pendingButtons.forEach(function (state, btn) { hideButtonSpinner(btn); });
        pendingForms.clear();
        tableContainers.forEach(function (el) { hideTableLoader(el, true); });
    }

    function setupInteractions() {
        // Capture suppresses only activations of a button already owned by a pending
        // operation. The first click and all unrelated event handlers run immediately.
        document.addEventListener('click', function (event) {
            var btn = event.target.closest && event.target.closest('.gl-btn-pending');
            if (btn && pendingButtons.has(btn)) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        }, true);
        document.addEventListener('submit', function (event) {
            if (pendingForms.has(event.target)) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        }, true);
        document.addEventListener('submit', function (event) {
            var form = event.target;
            if (!form || form.hasAttribute('data-no-loader') || form.getAttribute('target') === '_blank') return;
            // Native validation has already run. Do not re-run checkValidity(),
            // fire additional invalid events, or override novalidate/formnovalidate.
            var submitBtn = event.submitter || form.querySelector('button[type="submit"], input[type="submit"], button:not([type])');
            var target = submitBtn && submitBtn.getAttribute('formtarget') || form.getAttribute('target');
            var method = submitBtn && submitBtn.getAttribute('formmethod') || form.getAttribute('method');
            // Named-frame submissions and dialog forms do not navigate this page.
            if (method && method.toLowerCase() === 'dialog') return;
            if (target && !/^_(self|parent|top)$/i.test(target) && target !== window.name) return;
            // This observes cancellation after all synchronous handlers; it does not
            // defer, prevent, re-dispatch or recreate the original submission.
            Promise.resolve().then(function () {
                if (event.defaultPrevented) return;
                pendingForms.add(form);
                start(true);
                if (submitBtn) showButtonSpinner(submitBtn, undefined, true);
            });
        }, false);
        document.addEventListener('click', function (event) {
            var a = event.target.closest && event.target.closest('a');
            if (!a || !a.href || a.hasAttribute('data-no-loader') || a.hasAttribute('download')) return;
            if (a.hasAttribute('data-bs-toggle') || a.hasAttribute('data-toggle') || a.hasAttribute('data-bs-target') || a.hasAttribute('data-target')) return;
            if (a.target === '_blank' || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0) return;
            var raw = a.getAttribute('href') || '';
            if (!raw || /^(#|javascript:|mailto:|tel:|blob:|data:)/i.test(raw)) return;
            try {
                var url = new URL(a.href, window.location.href);
                if (url.origin !== window.location.origin) return;
                if (url.pathname === window.location.pathname && url.search === window.location.search) return;
                Promise.resolve().then(function () { if (!event.defaultPrevented) start(true); });
            } catch (error) { /* Not an internal navigation. */ }
        }, false);
        window.addEventListener('pagehide', resetPage);
        window.addEventListener('pageshow', function (event) { if (event.persisted) resetPage(); });
    }

    hookFetch();
    hookXHR();
    function onInit() {
        var standalone = (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches)
            || window.navigator.standalone === true;
        if (standalone) document.documentElement.classList.add('gl-standalone');
        ensureElements();
        setupInteractions();
        try { window.sessionStorage.removeItem('gl_navigating'); } catch (error) {}
        if (standalone) {
            var firstPage = true;
            try {
                firstPage = window.sessionStorage.getItem('gl_standalone_opened') !== '1';
                window.sessionStorage.setItem('gl_standalone_opened', '1');
            } catch (error) {}
            if (firstPage) {
                start(true);
                if (document.readyState === 'complete') done();
                else window.addEventListener('load', done, { once: true });
            }
        }
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', onInit, { once: true });
    else onInit();

    window.GlobalLoader = {
        start: start, done: done, set: setProgress,
        isRunning: function () { return isRunning; },
        isVisible: function () { return isVisible; },
        setDelay: function (ms) {
            if (typeof ms === 'number' && ms >= 0 && Number.isFinite(ms)) {
                SHOW_DELAY = ms;
                BUTTON_DELAY = TABLE_DELAY = Math.min(ms, 200);
            }
        },
        getDelay: function () { return SHOW_DELAY; },
        addSilentPattern: function (pattern) { if (pattern) SILENT_PATTERNS.push(pattern); },
        showTableLoader: showTableLoader, hideTableLoader: hideTableLoader,
        showButtonSpinner: showButtonSpinner, hideButtonSpinner: hideButtonSpinner
    };
})(window, document);
