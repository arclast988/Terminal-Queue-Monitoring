(function () {
    'use strict';

    var activeDraftForm = null;
    var leftForEmail = false;

    function dismissNotice(form) {
        var notice = form && form.querySelector('[data-contact-draft-notice]');
        if (notice) notice.hidden = true;
        if (activeDraftForm === form) {
            activeDraftForm = null;
            leftForEmail = false;
        }
    }

    document.addEventListener('submit', function (event) {
        var form = event.target.closest('[data-contact-gmail]');
        if (!form) return;
        event.preventDefault();
        if (!form.reportValidity()) return;
        // An unavailable recipient falls back to the server's validation message.
        if (!form.dataset.recipient) {
            HTMLFormElement.prototype.submit.call(form);
            return;
        }
        var type = form.elements.type.value;
        var name = form.elements.name.value.trim();
        var subject = form.elements.subject.value.trim();
        var message = form.elements.message.value.trim();
        if (/[\r\n]/.test(name + subject)) {
            form.elements.name.setCustomValidity(/[\r\n]/.test(name) ? 'Enter your name on one line.' : '');
            form.elements.subject.setCustomValidity(/[\r\n]/.test(subject) ? 'Enter the subject on one line.' : '');
            form.reportValidity();
            return;
        }
        var label = type === 'report' ? 'Report Issue' : 'Contact Us';
        var params = new URLSearchParams({
            view: 'cm', fs: '1', tf: 'cm', to: form.dataset.recipient,
            su: '[' + form.dataset.acronym + '] ' + label + (subject ? ': ' + subject : ''),
            body: (name ? 'Name: ' + name : '') + (name && message ? '\n\n' : '') + message
        });
        var url = 'https://mail.google.com/mail/?' + params.toString();
        var appUrl = 'mailto:' + encodeURIComponent(form.dataset.recipient).replace(/%40/g, '@')
            + '?subject=' + encodeURIComponent(params.get('su'))
            + '&body=' + encodeURIComponent(params.get('body').replace(/\r\n|\r|\n/g, '\r\n'));
        var draftLink = form.querySelector('[data-contact-draft-link]');
        var appLink = form.querySelector('[data-contact-app-link]');
        var notice = form.querySelector('[data-contact-draft-notice]');
        if (draftLink) draftLink.href = url;
        if (appLink) appLink.href = appUrl;
        if (notice) notice.hidden = false;
        activeDraftForm = form;
        leftForEmail = false;
        // Launch during the user's gesture. Keep both links available if the device cannot open it.
        if (event.submitter && event.submitter.value === 'gmail') {
            window.open(url, '_blank', 'noopener,noreferrer');
        } else {
            var launchLink = appLink || document.createElement('a');
            launchLink.href = appUrl;
            launchLink.setAttribute('data-no-loader', '');
            launchLink.click();
        }
        if (notice) notice.focus({preventScroll: true});
    });

    function invalidateDraft(event) {
        var form = event.target.closest('[data-contact-gmail]');
        if (!form) return;
        if (event.target.setCustomValidity) event.target.setCustomValidity('');
        dismissNotice(form);
        var draftLink = form.querySelector('[data-contact-draft-link]');
        if (draftLink) draftLink.removeAttribute('href');
        var appLink = form.querySelector('[data-contact-app-link]');
        if (appLink) appLink.removeAttribute('href');
    }
    document.addEventListener('input', invalidateDraft);
    document.addEventListener('change', invalidateDraft);
    document.addEventListener('click', function (event) {
        var close = event.target.closest('[data-contact-dismiss-notice]');
        if (close) dismissNotice(close.closest('[data-contact-gmail]'));
        var draftLink = event.target.closest('[data-contact-app-link], [data-contact-draft-link]');
        if (draftLink && draftLink.hasAttribute('href')) {
            activeDraftForm = draftLink.closest('[data-contact-gmail]');
            leftForEmail = false;
        }
    });
    // Email apps and Gmail do not report delivery back to this page. Hide only the reminder on return.
    function leaveForEmail() { if (activeDraftForm) leftForEmail = true; }
    function returnToSite() { if (leftForEmail) dismissNotice(activeDraftForm); }
    document.addEventListener('visibilitychange', function () {
        if (document.hidden) leaveForEmail();
        else returnToSite();
    });
    window.addEventListener('blur', leaveForEmail);
    window.addEventListener('focus', returnToSite);
})();
