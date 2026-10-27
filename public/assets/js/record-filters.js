(function (window, document) {
    'use strict';
    window.RecordFilters = {
        init: function (options) {
            var form = document.querySelector(options.form);
            if (!form || form.recordFilters) return form && form.recordFilters;
            var sequence = 0, controller = null, pending = false;
            var button = form.querySelector('button[type="submit"]');
            var results = document.querySelector(options.results);
            if (!results) return null;
            var error = document.createElement('div');
            error.className = 'alert-modern alert-modern-danger mt-3';
            error.setAttribute('role', 'alert'); error.hidden = true; error.style.display = 'none';
            form.insertAdjacentElement('afterend', error);

            function syncForm(url) {
                var params = new URL(url, location.href).searchParams;
                Array.from(form.elements).forEach(function (field) {
                    if (!field.name || !/^(INPUT|SELECT)$/.test(field.tagName)) return;
                    var value = params.get(field.name) || (field.name === 'sort_order' ? 'desc' : '');
                    if (field.tagName === 'SELECT') {
                        var option = Array.from(field.options).find(function (item) { return item.value.toLowerCase() === value.toLowerCase(); });
                        value = option ? option.value : '';
                    }
                    field.value = value;
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                });
                if (options.sync) options.sync(new URL(url, location.href));
            }
            function feedback(active) {
                pending = active;
                results.setAttribute('aria-busy', String(active));
                if (!window.GlobalLoader) return;
                if (active) {
                    if (button) GlobalLoader.showButtonSpinner(button, 'Filtering\u2026', true);
                    GlobalLoader.showTableLoader(results, 'Loading records\u2026');
                } else {
                    if (button) GlobalLoader.hideButtonSpinner(button);
                    GlobalLoader.hideTableLoader(results, true);
                }
            }
            function formUrl() {
                var url = new URL(form.action, location.href); url.search = '';
                new FormData(form).forEach(function (value, key) { if (value !== '') url.searchParams.append(key, value); });
                if (options.params) options.params(url);
                return url;
            }
            async function load(url, push) {
                url = new URL(url, location.href);
                if (url.origin !== location.origin || url.pathname !== new URL(form.action).pathname) return false;
                if (controller) controller.abort();
                controller = new AbortController();
                var request = ++sequence;
                error.hidden = true; error.style.display = 'none'; feedback(true);
                if (options.started) options.started(url);
                try {
                    var response = await fetch(options.requestUrl ? options.requestUrl(url) : url.href, { signal: controller.signal, headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-Silent': 'true' } });
                    if (!response.ok) throw new Error('Filter request failed');
                    var doc = options.json ? await response.json() : new DOMParser().parseFromString(await response.text(), 'text/html');
                    if (request !== sequence) return false;
                    if (options.json) {
                        if (!doc.success || !Array.isArray(doc.schedules)) throw new Error('Missing results');
                        options.render(doc, url);
                    } else {
                        var regions = (options.regions || [options.results]).map(function (selector) {
                            return { current: document.querySelector(selector), incoming: doc.querySelector(selector) };
                        });
                        if (regions.some(function (region) { return !region.current || !region.incoming; })) throw new Error('Missing results');
                        regions.forEach(function (region) { region.current.innerHTML = region.incoming.innerHTML; });
                        (options.text || []).forEach(function (selector) {
                            var current = document.querySelector(selector), incoming = doc.querySelector(selector);
                            if (current && incoming) current.textContent = incoming.textContent;
                        });
                        (options.links || []).forEach(function (selector) {
                            var current = document.querySelector(selector), incoming = doc.querySelector(selector);
                            if (current && incoming) current.href = incoming.href;
                        });
                    }
                    if (push !== false && url.href !== location.href) history.pushState({}, '', url.href);
                    if (options.updated) options.updated(doc, url);
                    return true;
                } catch (failure) {
                    if (request === sequence && failure.name !== 'AbortError') {
                        error.textContent = 'Unable to update the records. Please try again.';
                        error.hidden = false; error.style.display = '';
                    }
                    return false;
                } finally {
                    if (request === sequence) {
                        controller = null; feedback(false);
                        if (options.finished) options.finished();
                    }
                }
            }
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                if (!pending) load(formUrl(), true);
            });
            document.addEventListener('click', function (event) {
                if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
                var link = event.target.closest(options.linkSelector || '[data-record-filter-link]');
                if (!link || link.target === '_blank') return;
                var url = new URL(link.href, location.href);
                if (url.origin !== location.origin || url.pathname !== new URL(form.action).pathname) return;
                event.preventDefault(); syncForm(url); load(url, true);
            });
            window.addEventListener('popstate', function () { syncForm(location.href); load(location.href, false); });
            window.addEventListener('pagehide', function () {
                sequence++; if (controller) controller.abort(); controller = null; feedback(false);
                if (options.finished) options.finished();
            });
            form.recordFilters = { load: load, submit: function () { return load(formUrl(), true); }, isLoading: function () { return pending; } };
            return form.recordFilters;
        }
    };
})(window, document);
