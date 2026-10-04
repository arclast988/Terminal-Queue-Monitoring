(function () {
    'use strict';
    var modal = document.getElementById('guestContactVerificationModal');
    if (!modal || !window.fetch) return;
    var content = document.getElementById('guestContactVerificationContent');
    var busy = false, timer, returnFocus, backgroundFocus;
    document.addEventListener('focusin', function (event) {
        if (!event.target.closest('.support-modal-backdrop')) backgroundFocus = event.target;
    });
    function active() { return modal.classList.contains('active'); }
    function updateResend() {
        var button = document.getElementById('guestContactResend');
        if (!button) return;
        if (!button.dataset.availableAt) button.dataset.availableAt = String(Date.now() + Number(button.dataset.resendWait || 0) * 1000);
        var remaining = Math.max(0, Math.ceil((Number(button.dataset.availableAt) - Date.now()) / 1000));
        button.disabled = busy || remaining > 0;
        button.textContent = remaining ? 'Resend code in ' + remaining + 's' : 'Resend code';
        return remaining;
    }
    function countdown() {
        clearInterval(timer);
        if (updateResend() > 0 && active()) timer = setInterval(function () { if (!updateResend()) clearInterval(timer); }, 1000);
    }
    function syncResume() {
        document.querySelectorAll('[data-contact-resume]').forEach(function (button) {
            button.hidden = button.form.elements.type.value !== modal.dataset.pendingType;
        });
    }
    function switchModal(id) {
        document.querySelectorAll('.support-modal-backdrop.active').forEach(function (open) {
            if (open.id !== id) closeSupportModal(open.id);
        });
        openSupportModal(id);
    }
    function openVerification() {
        if (!active()) returnFocus = backgroundFocus;
        switchModal(modal.id);
        countdown();
        var focus = content.querySelector('[role="alert"], #guestContactCode, button[type="submit"]');
        if (focus) { if (focus.getAttribute('role') === 'alert') focus.tabIndex = -1; focus.focus({preventScroll:true}); }
    }
    function scheduleNoticeDismiss(notice) {
        if (!notice || notice.dataset.dismissScheduled === 'true') return;
        notice.dataset.dismissScheduled = 'true';
        var timer = null;
        var startDismiss = function () {
            timer = setTimeout(function () {
                if (notice && notice.parentNode && !notice.classList.contains('alert-dismissing')) {
                    notice.classList.add('alert-dismissing');
                    setTimeout(function () {
                        if (notice.parentNode) notice.parentNode.removeChild(notice);
                    }, 450);
                }
            }, 4500);
        };
        notice.addEventListener('mouseenter', function () { if (timer) clearTimeout(timer); });
        notice.addEventListener('mouseleave', function () { startDismiss(); });
        startDismiss();
    }
    function feedback(target, message, error) {
        target.querySelectorAll('[data-contact-feedback]').forEach(function (notice) { notice.remove(); });
        if (!message) return;
        var notice = document.createElement('div');
        notice.className = 'contact-verification-notice' + (error ? ' is-error' : '');
        notice.dataset.contactFeedback = 'true';
        notice.setAttribute('role', error ? 'alert' : 'status');
        notice.tabIndex = -1; notice.textContent = message;
        var heading = target.querySelector('h3');
        if (heading) heading.after(notice); else target.prepend(notice);
        notice.focus({preventScroll:true});
        scheduleNoticeDismiss(notice);
    }
    function source(type) { return document.getElementById(type === 'report' ? 'reportModal' : 'contactModal'); }
    function updateCsrf(csrf) {
        if (!csrf || !csrf.name || !csrf.hash) return;
        document.querySelectorAll('input[type="hidden"]').forEach(function (input) { if (input.name === csrf.name) input.value = csrf.hash; });
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) meta.content = csrf.hash;
    }
    function receive(payload) {
        if (payload.stage === 'verify' && typeof payload.html === 'string') {
            content.innerHTML = payload.html;
            modal.dataset.pendingType = payload.type;
            syncResume(); openVerification();
            content.querySelectorAll('.contact-verification-notice').forEach(scheduleNoticeDismiss);
        } else if (payload.stage === 'form' || payload.stage === 'complete') {
            returnFocus = null;
            var heading = document.createElement('h3');
            heading.id = 'contactVerificationTitle'; heading.textContent = 'Email verification';
            content.replaceChildren(heading);
            modal.dataset.pendingType = ''; syncResume();
            var target = source(payload.type), form = target.querySelector('[data-contact-async]');
            if (payload.stage === 'complete') form.reset();
            if (payload.draft) ['name','email','subject','message'].forEach(function (key) {
                if (typeof payload.draft[key] === 'string') form.elements[key].value = payload.draft[key];
            });
            switchModal(target.id);
            feedback(target.querySelector('.support-modal'), payload.message, !payload.success);
            if (!payload.message) form.elements.email.focus({preventScroll:true});
        } else throw new Error('Unexpected response');
        updateCsrf(payload.csrf);
    }
    document.addEventListener('submit', async function (event) {
        var form = event.target.closest('[data-contact-async]');
        if (!form) return;
        event.preventDefault();
        if (busy) return;
        busy = true;
        var data = new FormData(form);
        var button = event.submitter || form.querySelector('button[type="submit"]');
        var controls = Array.from(document.querySelectorAll('[data-contact-async] button, [data-contact-async] input:not([type="hidden"]), [data-contact-async] textarea, [data-contact-async] select'));
        var states = controls.map(function (control) { return control.disabled; });
        var label = button && button.innerHTML;
        controls.forEach(function (control) { control.disabled = true; });
        if (button) {
            button.setAttribute('aria-busy','true');
            button.textContent = form.action.includes('/send') || form.action.includes('/resend') ? 'Sending code…' : form.action.includes('/edit') ? 'Opening message…' : 'Sending…';
        }
        var abort = new AbortController();
        var timeout = setTimeout(function () { abort.abort(); }, 25000);
        try {
            var response = await fetch(form.action, {method:'POST', body:data, credentials:'same-origin', signal:abort.signal, headers:{Accept:'application/json', 'X-Requested-With':'XMLHttpRequest'}});
            if (!response.ok) throw new Error(response.status === 403 ? 'expired' : 'request');
            var payload = await response.json();
            receive(payload);
        } catch (error) {
            var target = form.closest('.support-modal');
            feedback(target, error.message === 'expired' ? 'This page has expired. Refresh the page and try again.' : 'We couldn’t complete this request. Please try again. Your message has not been confirmed as sent.', true);
        } finally {
            clearTimeout(timeout);
            busy = false;
            controls.forEach(function (control,index) { if (control.isConnected) control.disabled = states[index]; });
            if (button && button.isConnected) { button.innerHTML = label; button.removeAttribute('aria-busy'); }
            countdown();
        }
    });
    document.addEventListener('click', function (event) {
        if (event.target.closest('[data-contact-resume]')) openVerification();
    });
    modal.addEventListener('keydown', function (event) {
        if (event.key !== 'Tab' || !active()) return;
        var items = Array.from(modal.querySelectorAll('button:not(:disabled), input:not([type="hidden"]):not(:disabled), summary')).filter(function (item) { return item.getClientRects().length > 0; });
        var first = items[0], last = items[items.length-1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    });
    new MutationObserver(function () {
        if (active()) { countdown(); var field = content.querySelector('#guestContactCode, button[type="submit"]'); if (field && !modal.contains(document.activeElement)) field.focus({preventScroll:true}); }
        else { clearInterval(timer); if (returnFocus && returnFocus.isConnected) returnFocus.focus({preventScroll:true}); }
    }).observe(modal, {attributes:true, attributeFilter:['class']});
    updateResend(); syncResume();
    document.querySelectorAll('.contact-verification-notice').forEach(scheduleNoticeDismiss);
    if (active()) openVerification();
})();
