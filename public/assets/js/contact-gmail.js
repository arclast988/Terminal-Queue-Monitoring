(function () {
    'use strict';

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
            body: (name ? 'Name: ' + name + '\n' : '') + 'Type: ' + label
                + (subject ? '\nSubject: ' + subject : '') + (message ? '\n\nMessage:\n' + message : '')
        });
        var url = 'https://mail.google.com/mail/?' + params.toString();
        form.querySelector('[data-contact-draft-link]').href = url;
        form.querySelector('[data-contact-draft-notice]').hidden = false;
        // Open during the submit gesture. The visible link also works if popups are blocked.
        window.open(url, '_blank', 'noopener,noreferrer');
        form.querySelector('[data-contact-draft-notice]').focus({preventScroll: true});
    });

    function invalidateDraft(event) {
        var form = event.target.closest('[data-contact-gmail]');
        if (!form) return;
        if (event.target.setCustomValidity) event.target.setCustomValidity('');
        var notice = form.querySelector('[data-contact-draft-notice]');
        notice.hidden = true;
        form.querySelector('[data-contact-draft-link]').removeAttribute('href');
    }
    document.addEventListener('input', invalidateDraft);
    document.addEventListener('change', invalidateDraft);
})();
