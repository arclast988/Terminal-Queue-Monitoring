(function () {
    'use strict';
    var script = document.currentScript;
    var pending = new Map();
    var canceledCards = new Map();
    var statusActions = new Map();

    function statusState(id) {
        id = String(id);
        if (!statusActions.has(id)) statusActions.set(id, {action:0, confirmation:0, status:null});
        return statusActions.get(id);
    }

    function beginStatus(id) {
        var state = statusState(id);
        return {action:++state.action, confirmation:state.confirmation};
    }

    function isCurrentStatusAction(id, token, unconfirmed) {
        var state = statusState(id);
        return token && token.action === state.action && (!unconfirmed || token.confirmation === state.confirmation);
    }

    function rememberCard(id) {
        var card = document.getElementById('card-' + id);
        if (!card) return;
        canceledCards.set(String(id), {card:card.cloneNode(true), nextId:card.nextElementSibling && card.nextElementSibling.id});
        while (canceledCards.size > 30) canceledCards.delete(canceledCards.keys().next().value);
    }

    function departureDate(value) {
        if (!value) return null;
        var date = String(value).replace(' ', 'T');
        if (!/(?:Z|[+-]\d{2}:?\d{2})$/i.test(date)) date += '+08:00';
        var result = new Date(date);
        return Number.isNaN(result.getTime()) ? null : result;
    }

    // Apply only a server-confirmed change. The background refresh reconciles
    // route positions and other trips without delaying the acted-on card.
    function applyStatus(id, status, item, token, restore) {
        id = String(id);
        var state = statusState(id);
        if (token) {
            if (!isCurrentStatusAction(id, token)) return false;
            // A newer broadcast is already visible. Its matching status can
            // complete local feedback, but an older response cannot rewrite it.
            if (token.confirmation !== state.confirmation) return state.status === status ? 'confirmed' : false;
        }
        state.confirmation++;
        state.status = status;
        var list = document.getElementById('queue-list');
        if (!list) return 'applied';
        var card = document.getElementById('card-' + id);
        if (status === 'departed' || status === 'canceled') {
            if (card) {
                if (status === 'canceled') {
                    rememberCard(id);
                }
                card.remove();
            }
        } else if (status === 'waiting' || status === 'boarding') {
            if (!card && status === 'waiting' && (!token || restore) && canceledCards.has(id)) {
                var saved = canceledCards.get(id);
                card = saved.card;
                list.insertBefore(card, saved.nextId ? document.getElementById(saved.nextId) : null);
                canceledCards.delete(id);
            }
            if (card) {
                card.dataset.queueStatus = status;
                if (item) {
                    var position = card.querySelector('[data-queue-position]');
                    var round = card.querySelector('[data-queue-round]');
                    if (position && item.position != null) position.textContent = '#' + item.position;
                    if (round && item.round_number != null) round.textContent = 'Round ' + item.round_number;
                    if (item.terminal_id != null && item.destination != null) card.dataset.queueRouteKey = item.terminal_id + '|' + item.destination;
                    if (window.QueueSync && item.current_passengers != null && item.capacity != null) {
                        window.QueueSync.updatePassengerUI(id, item.current_passengers, item.capacity);
                    }
                }
                var badge = card.querySelector('[data-queue-status]');
                if (badge) {
                    badge.className = 'badge-modern badge-modern-' + (status === 'boarding' ? 'success' : 'warning');
                    var icon = document.createElement('i');
                    icon.className = 'bi me-1 ' + (status === 'boarding' ? 'bi-play-circle-fill' : 'bi-hourglass-split');
                    badge.replaceChildren(icon, document.createTextNode(status === 'boarding' ? 'Boarding' : 'Waiting'));
                }
                var current = card.querySelector('[data-action="update-status"], [data-action="open-depart-modal"]');
                if (current) {
                    // Replacing the pending button prevents its completion handler
                    // from restoring the old label over the newly confirmed action.
                    var action = document.createElement('button');
                    action.type = 'button';
                    action.style.whiteSpace = 'nowrap';
                    var cancel = card.querySelector('[data-action="open-cancel-modal"]');
                    var passengers = card.querySelector('.passenger-count-num');
                    var counter = document.getElementById('passenger-count-' + id);
                    action.disabled = false;
                    action.className = 'btn-modern flex-fill btn-modern-' + (status === 'boarding' ? 'success' : 'primary');
                    action.dataset.action = status === 'boarding' ? 'open-depart-modal' : 'update-status';
                    action.dataset.id = id;
                    action.textContent = status === 'boarding' ? 'Depart' : 'Start boarding now';
                    if (status === 'boarding') {
                        delete action.dataset.status;
                        action.dataset.plate = item && item.plate_number || cancel && cancel.dataset.plate || '';
                        action.dataset.type = cancel && cancel.dataset.type || '';
                        action.dataset.passengers = item && item.current_passengers != null ? item.current_passengers : passengers ? passengers.textContent.trim() : '0';
                        action.dataset.capacity = item && item.capacity != null ? item.capacity : counter ? counter.dataset.capacity : '0';
                    } else {
                        action.dataset.status = 'boarding';
                    }
                    current.replaceWith(action);
                }
                var timing = card.querySelector('[data-queue-departure]');
                if (timing) {
                    timing.querySelectorAll('.countdown-timer, [data-queue-boarding-time]').forEach(el => el.remove());
                    if (item) {
                        var departure = departureDate(item.estimated_departure);
                        var value = timing.querySelector('[data-queue-departure-time]');
                        if (value) value.textContent = departure ? departure.toLocaleTimeString('en-US', {timeZone:'Asia/Manila',hour:'numeric',minute:'2-digit'}) : 'TBA';
                        if (departure && status === 'boarding') {
                            var countdown = document.createElement('div');
                            countdown.className = 'countdown-timer';
                            countdown.dataset.departure = departure.toISOString();
                            timing.append(countdown);
                        }
                        var boarding = departureDate(item.boarding_start);
                        if (boarding && status === 'waiting') {
                            var boardingTime = document.createElement('div');
                            boardingTime.className = 'small text-muted';
                            boardingTime.dataset.queueBoardingTime = '';
                            boardingTime.textContent = 'Boarding ' + boarding.toLocaleTimeString('en-US', {timeZone:'Asia/Manila',hour:'numeric',minute:'2-digit'});
                            timing.append(boardingTime);
                        }
                    }
                }
            }
        }
        var empty = document.getElementById('queueEmptyState');
        if (!empty) {
            empty = document.createElement('div');
            empty.id = 'queueEmptyState';
            empty.className = 'text-center text-muted py-5';
            empty.setAttribute('role', 'status');
            var message = document.createElement('span');
            message.dataset.queueEmptyMessage = '';
            message.textContent = 'Queue is currently empty.';
            empty.append(message);
            list.append(empty);
        }
        empty.classList.toggle('d-none', !!list.querySelector('.q-card'));
        if (window.QueueOrderManager) window.QueueOrderManager.setStatus(id, status);
        if (window.DispatchQueue) window.DispatchQueue.applyFilters();
        document.dispatchEvent(new CustomEvent('queue-status-applied', {detail:{id:id,status:status}}));
        return 'applied';
    }
    async function request(url, body) {
        // Share an in-flight mutation so double clicks cannot send duplicate requests.
        var key = url + ':' + (body ? body.toString() : '');
        if (pending.has(key)) return pending.get(key);
        var work = (async function () {
            var controller = new AbortController(), timeout = setTimeout(() => controller.abort(), 20000);
            try {
                var meta = document.querySelector('meta[name="csrf-token"]');
                var header = document.querySelector('meta[name="csrf-header"]');
                var headers = {'X-Requested-With': 'XMLHttpRequest'};
                headers[header ? header.content : 'X-CSRF-TOKEN'] = meta ? meta.content : script.dataset.csrfValue;
                var response = await fetch(url, {method:'POST', headers:headers, body:body, signal:controller.signal});
                var data = null;
                try { data = await response.json(); } catch (_) { /* A session redirect or server error can be HTML. */ }
                if (!response.ok || !data || !data.success) {
                    var fallback = response.status === 403 ? 'Your session could not be verified. Refresh the page and try again.'
                        : response.redirected ? 'Your session has ended. Sign in again to continue.'
                        : 'The action could not be completed. Refresh the queue and try again.';
                    var error = new Error(data && data.message || fallback);
                    error.variant = data && data.variant || (response.status === 409 ? 'warning' : 'danger');
                    throw error;
                }
                if (data.csrf && meta) meta.content = data.csrf;
                return data;
            } catch (error) {
                if (error.name === 'AbortError' || error instanceof TypeError) {
                    error = new Error('The connection was interrupted. Refresh the queue to check whether the action completed before trying again.');
                }
                throw error;
            } finally { clearTimeout(timeout); }
        })();
        pending.set(key, work);
        try { return await work; } finally { pending.delete(key); }
    }
    function notice(title, text, variant, target) {
        var container = target || document.getElementById('queueActionFeedback');
        if (!container) return;
        container.replaceChildren(); container.hidden = false;
        container.className = 'queue-action-notice queue-notice-' + (['success','warning','danger'].includes(variant) ? variant : 'danger');
        var icon = document.createElement('i'); icon.className = 'bi ' + (variant === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle'); icon.setAttribute('aria-hidden','true');
        var copy = document.createElement('div'), heading = document.createElement('strong'), message = document.createElement('p');
        heading.textContent = title; message.textContent = text; copy.append(heading,message);
        var close = document.createElement('button'); close.type='button'; close.className='queue-notice-close'; close.setAttribute('aria-label','Dismiss notice'); close.textContent='×'; close.onclick=()=>{container.hidden=true;};
        container.append(icon,copy,close);
        container.scrollIntoView({block:'nearest',behavior:'smooth'});
    }
    var modal = document.getElementById('cancelSelectionModal'), refreshing = false, busy = false, skipHiddenRefresh = false;
    function selection() { return modal ? Array.from(modal.querySelectorAll('[name="cancel_queue_ids[]"]:checked')).map(box=>box.value) : []; }
    function updateSelection() {
        if (!modal) return;
        var route = modal.querySelector('#cancelRouteFilter').value || 'all';
        var search = modal.querySelector('#cancelVehicleSearch');
        var query = search.value.trim().toLowerCase();
        var terms = query ? query.split(/\s+/) : [];
        var rows = Array.from(modal.querySelectorAll('.queue-cancel-item'));
        rows.forEach(row=>{
            var details = row.textContent.toLowerCase();
            row.hidden = (route !== 'all' && row.dataset.cancelRoute !== route) || !terms.every(term=>details.includes(term));
        });
        modal.querySelector('#clearCancelVehicleSearch').hidden = !search.value;
        var visible = rows.filter(row=>!row.hidden).map(row=>row.querySelector('input'));
        var checkedVisible = visible.filter(box=>box.checked);
        var selectAll = modal.querySelector('#cancelSelectVisible');
        selectAll.checked = visible.length > 0 && checkedVisible.length === visible.length;
        selectAll.indeterminate = checkedVisible.length > 0 && checkedVisible.length < visible.length;
        selectAll.disabled = !visible.length || busy;
        var count = selection().length;
        modal.querySelector('#cancelSelectionCount').textContent = count + ' trip' + (count === 1 ? '' : 's') + ' selected';
        var submit = modal.querySelector('#cancelSelectedSubmit'); submit.disabled = !count || busy || refreshing;
        submit.textContent = busy ? 'Canceling…' : count ? 'Cancel ' + count + ' selected trip' + (count === 1 ? '' : 's') : 'Cancel selected trips';
        var empty = modal.querySelector('#cancelSelectionEmpty');
        empty.hidden = visible.length > 0;
        empty.textContent = query ? (route === 'all' ? 'No trips match your search.' : 'No trips match your search for this route.') : 'No active trips for this route.';
    }
    function sync(newDoc) {
        if (!modal || !newDoc || modal.classList.contains('show') || refreshing) return;
        var source = newDoc.getElementById('cancelSelectionList');
        if (source) modal.querySelector('#cancelSelectionList').replaceChildren(...Array.from(source.children).map(row=>row.cloneNode(true)));
        updateSelection();
    }
    async function refreshSelection() {
        if (refreshing || busy) return;
        refreshing = true;
        var button = modal.querySelector('#refreshCancelSelection'), list = modal.querySelector('#cancelSelectionList');
        button.disabled = true; button.textContent = 'Refreshing…'; list.setAttribute('aria-busy', 'true');
        modal.querySelector('#cancelSelectionFeedback').hidden = true;
        updateSelection();
        var controller = new AbortController(), timeout = setTimeout(() => controller.abort(), 15000);
        try {
            var response = await fetch(script.dataset.refreshUrl, {
                cache: 'no-store', headers: {'X-Requested-With':'XMLHttpRequest'}, signal:controller.signal
            });
            var data = null;
            try { data = await response.json(); } catch (_) {}
            if (!response.ok || !data || !data.success || typeof data.html !== 'string') {
                throw new Error(response.redirected ? 'Your session has ended. Sign in again to refresh the trips.'
                    : data && data.message || 'The latest trips could not be loaded. Try refreshing again.');
            }
            // Preserve the current selection, including changes made while loading.
            var checked = new Set(selection());
            var template = document.createElement('template');
            template.innerHTML = window.DOMPurify.sanitize(data.html);
            list.replaceChildren(template.content);
            list.querySelectorAll('[name="cancel_queue_ids[]"]').forEach(box => { box.checked = checked.has(box.value); });
            var removed = checked.size - selection().length;
            notice('Selection refreshed', removed ? removed + ' selected trip' + (removed === 1 ? ' is' : 's are') + ' no longer active and ' + (removed === 1 ? 'was' : 'were') + ' removed. Your other selections were kept.'
                : 'The latest active trips are loaded. Your selections were kept.', 'success', modal.querySelector('#cancelSelectionFeedback'));
        } catch (error) {
            var message = error.name === 'AbortError' || error instanceof TypeError
                ? 'The connection was interrupted. Your selections were kept. Try refreshing again.' : error.message;
            notice('Could not refresh the trips', message, 'danger', modal.querySelector('#cancelSelectionFeedback'));
        } finally {
            clearTimeout(timeout); refreshing = false;
            button.disabled = false; button.textContent = 'Refresh selection'; list.removeAttribute('aria-busy'); updateSelection();
        }
    }
    if (modal) {
        modal.querySelector('#cancelVehicleSearch').addEventListener('input',updateSelection);
        modal.querySelector('#clearCancelVehicleSearch').addEventListener('click',function(){
            var search = modal.querySelector('#cancelVehicleSearch');
            search.value = ''; search.focus(); updateSelection();
        });
        modal.addEventListener('change',function(event){
            if (event.target.id === 'cancelSelectVisible') modal.querySelectorAll('.queue-cancel-item:not([hidden]) input').forEach(box=>{box.checked=event.target.checked;});
            updateSelection();
        });
        modal.addEventListener('show.bs.modal',function(){
            modal.querySelectorAll('[name="cancel_queue_ids[]"]').forEach(box=>{box.checked=false;});
            modal.querySelector('#cancelSelectionFeedback').hidden=true;
            modal.querySelector('#cancelVehicleSearch').value = '';
            var active = document.querySelector('[data-queue-route][aria-pressed="true"]');
            var filter = modal.querySelector('#cancelRouteFilter');
            filter.value = active ? active.dataset.queueRoute : 'all'; if (!filter.value) filter.value='all';
            if (filter.syncAutocompleteValue) filter.syncAutocompleteValue();
            updateSelection();
        });
        modal.addEventListener('hidden.bs.modal',function(){
            if (skipHiddenRefresh) { skipHiddenRefresh = false; return; }
            if(window.QueueSync)window.QueueSync.refresh(true);
        });
        modal.querySelector('#refreshCancelSelection').addEventListener('click',refreshSelection);
        modal.querySelector('#cancelSelectedSubmit').addEventListener('click',async function(){
            if (busy || refreshing || !selection().length) return;
            var ids=selection(), body=new URLSearchParams(); ids.forEach(id=>body.append('queue_ids[]',id));
            var actionTokens = new Map(ids.map(id=>[String(id),beginStatus(id)]));
            ids.forEach(rememberCard);
            busy=true; updateSelection();
            modal.querySelectorAll('input,select,[data-bs-dismiss],#refreshCancelSelection,#clearCancelVehicleSearch').forEach(el=>{el.disabled=true;});
            try {
                var data=await request(script.dataset.cancelUrl,body);
                var needsRefresh = false;
                (data.queue_ids || ids).forEach(id=>{
                    var token = actionTokens.get(String(id));
                    if (token && applyStatus(id,'canceled',null,token) === 'applied') needsRefresh = true;
                });
                skipHiddenRefresh = true;
                bootstrap.Modal.getOrCreateInstance(modal).hide();
                notice('Trips canceled',data.message,'success');
                if(needsRefresh && window.QueueSync)window.QueueSync.refresh(true);
            } catch(error){notice('Could not cancel the selection',error.message,error.variant,modal.querySelector('#cancelSelectionFeedback'));}
            finally {busy=false;modal.querySelectorAll('input,select,[data-bs-dismiss],#refreshCancelSelection,#clearCancelVehicleSearch').forEach(el=>{el.disabled=false;});updateSelection();}
        });
        updateSelection();
    }
    window.QueueActions={request:request,notice:notice,sync:sync,applyStatus:applyStatus,rememberCard:rememberCard,beginStatus:beginStatus,isCurrentStatusAction:isCurrentStatusAction};
})();
