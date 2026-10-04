(function () {
    'use strict';
    var script = document.currentScript;
    var pending = new Map();
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
    var modal = document.getElementById('cancelSelectionModal'), needsSelectionRefresh = false, busy = false;
    function selection() { return modal ? Array.from(modal.querySelectorAll('[name="cancel_queue_ids[]"]:checked')).map(box=>box.value) : []; }
    function updateSelection() {
        if (!modal) return;
        var route = modal.querySelector('#cancelRouteFilter').value;
        var rows = Array.from(modal.querySelectorAll('.queue-cancel-item'));
        rows.forEach(row=>{row.hidden=route !== 'all' && row.dataset.cancelRoute !== route;});
        var visible = rows.filter(row=>!row.hidden).map(row=>row.querySelector('input'));
        var checkedVisible = visible.filter(box=>box.checked);
        var selectAll = modal.querySelector('#cancelSelectVisible');
        selectAll.checked = visible.length > 0 && checkedVisible.length === visible.length;
        selectAll.indeterminate = checkedVisible.length > 0 && checkedVisible.length < visible.length;
        selectAll.disabled = !visible.length || busy;
        var count = selection().length;
        modal.querySelector('#cancelSelectionCount').textContent = count + ' trip' + (count === 1 ? '' : 's') + ' selected';
        var submit = modal.querySelector('#cancelSelectedSubmit'); submit.disabled = !count || busy;
        submit.textContent = busy ? 'Canceling…' : count ? 'Cancel ' + count + ' selected trip' + (count === 1 ? '' : 's') : 'Cancel selected trips';
        modal.querySelector('#cancelSelectionEmpty').hidden = visible.length > 0;
    }
    function sync(newDoc) {
        if (!modal || !newDoc || (modal.classList.contains('show') && !needsSelectionRefresh)) return;
        var source = newDoc.getElementById('cancelSelectionList');
        if (source) modal.querySelector('#cancelSelectionList').replaceChildren(...Array.from(source.children).map(row=>row.cloneNode(true)));
        needsSelectionRefresh=false; updateSelection();
    }
    if (modal) {
        modal.addEventListener('change',function(event){
            if (event.target.id === 'cancelSelectVisible') modal.querySelectorAll('.queue-cancel-item:not([hidden]) input').forEach(box=>{box.checked=event.target.checked;});
            updateSelection();
        });
        modal.addEventListener('show.bs.modal',function(){
            modal.querySelectorAll('[name="cancel_queue_ids[]"]').forEach(box=>{box.checked=false;});
            modal.querySelector('#cancelSelectionFeedback').hidden=true;
            var active = document.querySelector('[data-queue-route][aria-pressed="true"]');
            var filter = modal.querySelector('#cancelRouteFilter');
            filter.value = active ? active.dataset.queueRoute : 'all'; if (!filter.value) filter.value='all';
            updateSelection();
        });
        modal.addEventListener('hidden.bs.modal',function(){if(window.QueueSync)window.QueueSync.refresh(true);});
        modal.querySelector('#refreshCancelSelection').addEventListener('click',function(){needsSelectionRefresh=true;if(window.QueueSync)window.QueueSync.refresh(true);});
        modal.querySelector('#cancelSelectedSubmit').addEventListener('click',async function(){
            if (busy || !selection().length) return;
            var ids=selection(), body=new URLSearchParams(); ids.forEach(id=>body.append('queue_ids[]',id));
            busy=true; updateSelection();
            modal.querySelectorAll('input,select,[data-bs-dismiss],#refreshCancelSelection').forEach(el=>{el.disabled=true;});
            try {
                var data=await request(script.dataset.cancelUrl,body);
                (data.queue_ids || ids).forEach(id=>{if(window.QueueOrderManager)window.QueueOrderManager.setStatus(id,'canceled');});
                bootstrap.Modal.getOrCreateInstance(modal).hide();
                notice('Trips canceled',data.message,'success');
                if(window.QueueSync)window.QueueSync.refresh(true);
            } catch(error){notice('Could not cancel the selection',error.message,error.variant,modal.querySelector('#cancelSelectionFeedback'));}
            finally {busy=false;modal.querySelectorAll('input,select,[data-bs-dismiss],#refreshCancelSelection').forEach(el=>{el.disabled=false;});updateSelection();}
        });
        updateSelection();
    }
    window.QueueActions={request:request,notice:notice,sync:sync};
})();
