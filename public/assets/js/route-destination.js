(function () {
    'use strict';
    function init() {
        var terminal = document.getElementById('terminal_id');
        var destination = document.getElementById('destination');
        var error = document.getElementById('routeDestinationError');
        if (!terminal || !destination || !error || !destination.form) return;
        var message = 'Choose a destination different from the origin terminal.';
        function normalize(value) {
            return String(value || '').replace(/[\s\p{Z}]+/gu, ' ').trim().toUpperCase();
        }
        function originName() {
            var option = terminal.tagName === 'SELECT' ? terminal.options[terminal.selectedIndex] : terminal;
            return option && option.dataset.name || '';
        }
        function validate() {
            var origin = normalize(originName());
            var invalid = origin !== '' && normalize(destination.value) === origin;
            destination.dataset.excludeLocation = origin;
            destination.setCustomValidity(invalid ? message : '');
            destination.classList.toggle('is-invalid', invalid);
            if (invalid) destination.setAttribute('aria-invalid', 'true');
            else destination.removeAttribute('aria-invalid');
            error.hidden = !invalid;
            return !invalid;
        }
        destination.addEventListener('input', validate);
        destination.addEventListener('change', validate);
        terminal.addEventListener('change', function () {
            validate();
            // Re-filter an already-open suggestion list without replacing typed text.
            destination.dispatchEvent(new Event('input', {bubbles:true}));
        });
        destination.form.addEventListener('submit', function (event) {
            if (!validate()) {
                event.preventDefault();
                destination.reportValidity();
            }
        });
        validate();
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, {once:true});
    else init();
})();
