(function () {
    'use strict';

    function readPayload(id) {
        var node = document.getElementById(id);
        if (!node) return {};
        try { return JSON.parse(node.textContent || '{}'); } catch (error) { return {}; }
    }

    function replaceLabel(element, value) {
        if (!element || !value) return;
        Array.prototype.slice.call(element.childNodes).forEach(function (node) {
            if (node.nodeType === Node.TEXT_NODE) node.remove();
        });
        var trailingIcon = element.querySelector('.accordion-header-icon, .fa-chevron-down, .fa-chevron-up');
        var leadingIcon = element.querySelector('i:not(.accordion-header-icon):not(.fa-chevron-down):not(.fa-chevron-up)');
        var text = document.createTextNode((leadingIcon ? ' ' : '') + value + (trailingIcon ? ' ' : ''));
        if (trailingIcon) element.insertBefore(text, trailingIcon);
        else if (leadingIcon && leadingIcon.nextSibling) element.insertBefore(text, leadingIcon.nextSibling);
        else element.appendChild(text);
    }

    function replaceBody(element, value) {
        if (!element || !value) return;
        element.textContent = value;
        element.style.whiteSpace = 'pre-line';
        element.style.lineHeight = '1.7';
    }

    function applyGuide(payload, options) {
        var root = document.querySelector(options.root);
        if (!root) return;
        replaceLabel(document.querySelector(options.title), payload[options.prefix + '_title']);
        if (options.intro) replaceBody(document.querySelector(options.intro), payload[options.prefix + '_intro']);
        root.querySelectorAll('.accordion-item').forEach(function (item, index) {
            var number = index + 1;
            replaceLabel(item.querySelector('.accordion-header-text'), payload[options.prefix + '_' + number + '_title']);
            replaceBody(item.querySelector('.accordion-body'), payload[options.prefix + '_' + number + '_body']);
        });
    }

    function applyGuest(payload) {
        applyGuide(payload, {
            root: '#helpModal .accordion-list',
            title: '#helpModal .support-modal > h3',
            intro: '#helpModal .support-modal > p',
            prefix: 'content_commuter'
        });

        replaceBody(document.querySelector('#contactModal .support-modal > p'), payload.content_contact_intro);

        document.querySelectorAll('#faqModal .faq-item').forEach(function (item, index) {
            var number = index + 1;
            replaceLabel(item.querySelector('.faq-question'), payload['content_faq_' + number + '_title']);
            replaceBody(item.querySelector('.faq-answer'), payload['content_faq_' + number + '_body']);
        });

        var termsContainer = document.querySelector('#termsModal .support-modal > div');
        if (!termsContainer) return;
        var headings = Array.prototype.slice.call(termsContainer.querySelectorAll(':scope > h4'));
        headings.forEach(function (heading, index) {
            replaceLabel(heading, payload['content_terms_' + (index + 1) + '_title']);
        });
        headings.slice().reverse().forEach(function (heading, reverseIndex) {
            var number = headings.length - reverseIndex;
            var value = payload['content_terms_' + number + '_body'];
            if (!value) return;
            var cursor = heading.nextSibling;
            while (cursor && !(cursor.nodeType === Node.ELEMENT_NODE && cursor.matches('h4'))) {
                var next = cursor.nextSibling;
                cursor.remove();
                cursor = next;
            }
            var replacement = document.createElement('div');
            replacement.textContent = value;
            replacement.style.whiteSpace = 'pre-line';
            replacement.style.margin = '6px 0 14px';
            replacement.style.lineHeight = '1.7';
            heading.after(replacement);
        });
    }

    window.ManagedContent = {
        readPayload: readPayload,
        applyGuide: applyGuide,
        applyGuest: applyGuest
    };
})();
