(function () {
    'use strict';
    var script = document.currentScript;
    var routeFilter = 'all';
    var roundFilter = 'all';
    var addFilter = 'all';
    var posting = false;
    var csrfName = script.dataset.csrfName;
    var csrfValue = script.dataset.csrfValue;

    function syncRouteInput(select) {
        if (!select || !select.syncAutocompleteValue) return;
        var wrapper = select.closest('.autocomplete-wrapper');
        // A live queue refresh must not erase a route search being typed.
        if (wrapper && wrapper.contains(document.activeElement)) return;
        select.syncAutocompleteValue();
    }

    function applyFilters() {
        var visible = 0;
        var routeCounts = {};
        var totalQueued = 0;
        document.querySelectorAll('#queue-list .q-card[data-queue-route-key]').forEach(function (card) {
            var rk = card.dataset.queueRouteKey;
            routeCounts[rk] = (routeCounts[rk] || 0) + 1;
            totalQueued++;
            var show = routeFilter === 'all' || rk === routeFilter;
            card.classList.toggle('d-none', !show);
            if (show) visible++;
        });
        document.querySelectorAll('[data-queue-route]').forEach(function (btn) {
            var countEl = btn.querySelector('.chip-count');
            if (countEl) {
                var rk = btn.dataset.queueRoute;
                countEl.textContent = rk === 'all' ? totalQueued : (routeCounts[rk] || 0);
            }
        });
        var empty = document.getElementById('queueRouteEmpty');
        if (empty) empty.classList.toggle('d-none', routeFilter === 'all' || visible > 0);
        document.querySelectorAll('[data-round-route]').forEach(function (row) {
            row.classList.toggle('d-none', roundFilter !== 'all' && row.dataset.roundRoute !== roundFilter);
        });
        var roundRoute = document.getElementById('queueRoundRouteFilter');
        if (roundRoute) {
            roundRoute.value = roundFilter;
            syncRouteInput(roundRoute);
        }
        var select = document.getElementById('addQueueRouteFilter');
        if (select && Array.from(select.options).some(function (option) { return option.value === addFilter; })) select.value = addFilter;
        syncRouteInput(select);
        var addRoute = select ? select.value : routeFilter;
        var search = document.getElementById('vehicleModalSearch');
        var query = search ? search.value.trim().toLowerCase() : '';
        var visibleCount = 0;
        document.querySelectorAll('#vehicleListContainer .vehicle-select-item').forEach(function (card) {
            var show = (addRoute === 'all' || card.dataset.queueRouteKey === addRoute)
                && (!query || (card.dataset.search || '').toLowerCase().includes(query));
            card.classList.toggle('d-none', !show);
            card.style.setProperty('display', show ? 'flex' : 'none', 'important');
            if (show) visibleCount++;
        });
        var emptyNotice = document.getElementById('noMatchingVehiclesNotice');
        if (emptyNotice) {
            emptyNotice.style.setProperty('display', visibleCount === 0 ? 'block' : 'none', 'important');
        }
        var selectAllBtn = document.getElementById('selectAllVehiclesBtn');
        if (selectAllBtn) {
            selectAllBtn.disabled = (visibleCount === 0);
        }
    }

    function selectRoute(key, preserveAdd) {
        routeFilter = key;
        if (!preserveAdd) addFilter = key;
        document.querySelectorAll('[data-queue-route]').forEach(function (button) {
            var active = button.dataset.queueRoute === key;
            button.classList.toggle('active', active);
            button.setAttribute('aria-pressed', String(active));
        });
        var add = document.getElementById('addQueueRouteFilter');
        if (add && Array.from(add.options).some(function (option) { return option.value === addFilter; })) add.value = addFilter;
        var order = document.getElementById('queueOrderGroupSelect');
        if (order && key !== 'all') {
            var option = Array.from(order.options).find(function (item) { return item.dataset.routeKey === key; });
            if (option) {
                order.value = option.value;
                order.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
        applyFilters();
    }

    async function post(url, values) {
        var body = new URLSearchParams(values || {});
        body.set(csrfName, csrfValue);
        var result;
        if (url === script.dataset.roundUrl && window.QueueActions) {
            result = await window.QueueActions.request(url, body);
        } else {
            var response = await fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-Silent': 'true' }, body: body });
            result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'The queue could not be updated.');
        }
        if (result.csrf) csrfValue = result.csrf;
        if (window.QueueSync) window.QueueSync.refresh(true);
        return result;
    }

    function sync(newDoc) {
        // Use server HTML parsed into inert nodes for the currently available rounds.
        var source = newDoc.getElementById('queueRoundControls');
        var target = document.getElementById('queueRoundControls');
        var choosingRound = document.activeElement && document.activeElement.matches('[data-dispatch-round]');
        if (source && target && !posting && !choosingRound && window.DOMPurify) {
            var clean = window.DOMPurify.sanitize(source.cloneNode(true), { IN_PLACE: true });
            target.replaceChildren.apply(target, Array.from(clean.childNodes));
        }
        selectRoute(routeFilter, true);
    }

    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-queue-route]');
        if (button) selectRoute(button.dataset.queueRoute);
        var roundButton = event.target.closest('button[data-dispatch-round]');
        if (roundButton) roundButton.dispatchEvent(new Event('change', { bubbles: true }));
    });
    document.addEventListener('change', async function (event) {
        if (event.target.id === 'addQueueRouteFilter') {
            addFilter = event.target.value || 'all';
            applyFilters();
        }
        if (event.target.id === 'queueRoundRouteFilter') {
            roundFilter = event.target.value || 'all';
            applyFilters();
        }
        var select = event.target.closest('[data-dispatch-round]');
        if (!select || posting) return;
        var feedback = document.getElementById('queueRoundFeedback');
        var previousRound = select.dataset.currentRound;
        posting = true;
        document.querySelectorAll('[data-dispatch-round]').forEach(function (choice) { choice.disabled = true; });
        try {
            await post(script.dataset.roundUrl, { route_id: select.dataset.routeId, round_number: select.value });
            select.dataset.currentRound = select.value;
            if (window.QueueActions) window.QueueActions.notice('Round updated', 'Active vehicles now use this round’s departure rule. Boarding timers keep the time already elapsed.', 'success', feedback);
        } catch (error) {
            if (select.tagName === 'SELECT') select.value = previousRound;
            if (window.QueueActions) window.QueueActions.notice('Round could not be changed', error.message, error.variant || 'danger', feedback);
            if (window.QueueSync) window.QueueSync.refresh(true);
        } finally {
            posting = false;
            document.querySelectorAll('[data-dispatch-round]').forEach(function (choice) { choice.disabled = choice.dataset.roundUnavailable === 'true'; });
            if (window.QueueSync) window.QueueSync.refresh(true);
        }
    });
    document.addEventListener('vehicle-list-refreshed', function () {
        var modal = document.getElementById('addToQueueModal');
        if (modal && window.initLocationAutocomplete) window.initLocationAutocomplete(modal);
        applyFilters();
    });
    document.addEventListener('show.bs.modal', function (event) {
        if (event.target.id === 'queueRoundModal') {
            roundFilter = routeFilter;
            var feedback = document.getElementById('queueRoundFeedback');
            if (feedback) feedback.hidden = true;
            applyFilters();
            if (window.QueueSync) window.QueueSync.refresh(true);
        }
    });
    document.addEventListener('shown.bs.modal', function (event) {
        if (event.target.id === 'addToQueueModal') {
            selectRoute(routeFilter, true);
            if (window.QueueSync) window.QueueSync.refresh(true);
        }
    });

    var serviceDate = script.dataset.serviceDate || '';
    async function tick() {
        if (document.hidden || posting) return;
        try {
            var result = await post(script.dataset.tickUrl);
            if (result.service_date && result.service_date !== serviceDate) {
                serviceDate = result.service_date;
                if (window.QueueSync) window.QueueSync.refresh(true);
            }
        } catch (error) { /* The next boundary/visible refresh retries. */ }
    }
    function scheduleBoundary() {
        var delay = 300000 - (Date.now() % 300000) + 50;
        setTimeout(function () { tick(); scheduleBoundary(); }, delay);
    }
    document.addEventListener('visibilitychange', function () { if (!document.hidden) tick(); });
    window.DispatchQueue = { applyFilters: applyFilters, sync: sync };
    applyFilters();
    scheduleBoundary();
})();

