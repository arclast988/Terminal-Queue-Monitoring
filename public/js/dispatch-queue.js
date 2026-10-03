(function () {
    'use strict';
    var script = document.currentScript;
    var routeFilter = 'all';
    var addFilter = 'all';
    var posting = false;
    var csrfName = script.dataset.csrfName;
    var csrfValue = script.dataset.csrfValue;

    function applyFilters() {
        var visible = 0;
        document.querySelectorAll('#queue-list .q-card[data-queue-route-key]').forEach(function (card) {
            var show = routeFilter === 'all' || card.dataset.queueRouteKey === routeFilter;
            card.classList.toggle('d-none', !show);
            if (show) visible++;
        });
        var empty = document.getElementById('queueRouteEmpty');
        if (empty) empty.classList.toggle('d-none', routeFilter === 'all' || visible > 0);
        document.querySelectorAll('[data-round-route]').forEach(function (row) {
            row.classList.toggle('d-none', routeFilter !== 'all' && row.dataset.roundRoute !== routeFilter);
        });
        var select = document.getElementById('addQueueRouteFilter');
        if (select && Array.from(select.options).some(function (option) { return option.value === addFilter; })) select.value = addFilter;
        var addRoute = select ? select.value : routeFilter;
        var search = document.getElementById('vehicleModalSearch');
        var query = search ? search.value.trim().toLowerCase() : '';
        document.querySelectorAll('#vehicleListContainer .vehicle-select-item').forEach(function (card) {
            var show = (addRoute === 'all' || card.dataset.queueRouteKey === addRoute)
                && (!query || (card.dataset.search || '').toLowerCase().includes(query));
            card.classList.toggle('d-none', !show);
            card.style.setProperty('display', show ? 'flex' : 'none', 'important');
        });
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
        var response = await fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-Silent': 'true' }, body: body });
        var result = await response.json();
        if (result.csrf) csrfValue = result.csrf;
        if (!response.ok || !result.success) throw new Error(result.message || 'The queue could not be updated.');
        if (window.QueueSync) window.QueueSync.refresh(true);
        return result;
    }

    function sync(newDoc) {
        // Use server HTML parsed into inert nodes. Round options consist only of text/selects.
        var source = newDoc.getElementById('queueRoundControls');
        var target = document.getElementById('queueRoundControls');
        if (source && target && !posting && window.DOMPurify) {
            var clean = window.DOMPurify.sanitize(source.cloneNode(true), { IN_PLACE: true });
            target.replaceChildren.apply(target, Array.from(clean.childNodes));
        }
        selectRoute(routeFilter, true);
    }

    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-queue-route]');
        if (button) selectRoute(button.dataset.queueRoute);
    });
    document.addEventListener('change', async function (event) {
        if (event.target.id === 'addQueueRouteFilter') {
            addFilter = event.target.value;
            applyFilters();
        }
        var select = event.target.closest('[data-dispatch-round]');
        if (!select || posting) return;
        var feedback = document.getElementById('queueRoundFeedback');
        posting = true;
        select.disabled = true;
        try {
            await post(script.dataset.roundUrl, { route_id: select.dataset.routeId, round_number: select.value });
            if (feedback) feedback.textContent = 'Round updated. Waiting vehicles now use this round’s departure rule.';
        } catch (error) {
            if (feedback) feedback.textContent = error.message;
            if (window.QueueSync) window.QueueSync.refresh(true);
        } finally {
            posting = false;
            select.disabled = false;
            if (window.QueueSync) window.QueueSync.refresh(true);
        }
    });
    document.addEventListener('vehicle-list-refreshed', applyFilters);
    document.addEventListener('shown.bs.modal', function (event) {
        if (event.target.id === 'addToQueueModal') {
            selectRoute(routeFilter, true);
            if (window.QueueSync) window.QueueSync.refresh(true);
        }
    });

    async function tick() {
        if (document.hidden || posting) return;
        try { await post(script.dataset.tickUrl); } catch (error) { /* The next boundary/visible refresh retries. */ }
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
