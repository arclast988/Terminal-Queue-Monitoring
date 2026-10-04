(function () {
    'use strict';
    document.addEventListener('DOMContentLoaded', function () {
        var section = document.getElementById('dispatchRuleTimes');
        if (!section) return;
        var form = section.closest('form'), from = form.elements.time_from, to = form.elements.time_to;
        var existing = JSON.parse(section.dataset.existingRules || '[]');
        var alert = document.getElementById('rule-contradiction-alert');
        var message = document.getElementById('rule-contradiction-msg');
        function minutes(value) {
            var match = value.trim().match(/^(0?[1-9]|1[0-2]):([0-5]\d)\s*(AM|PM)$/i);
            return match ? (Number(match[1]) % 12 + (match[3].toUpperCase() === 'PM' ? 12 : 0)) * 60 + Number(match[2]) : null;
        }
        function savedMinutes(value) { var parts = value.split(':'); return Number(parts[0]) * 60 + Number(parts[1]); }
        function openPicker(wrap) {
            section.querySelectorAll('[data-clock-field]').forEach(function (other) {
                other.querySelector('[data-clock-toggle]').setAttribute('aria-expanded', String(other === wrap));
                other.querySelector('.dispatch-time-picker').hidden = other !== wrap;
            });
        }
        function closePickers() {
            section.querySelectorAll('[data-clock-field]').forEach(function (wrap) {
                wrap.querySelector('[data-clock-toggle]').setAttribute('aria-expanded', 'false');
                wrap.querySelector('.dispatch-time-picker').hidden = true;
            });
        }
        section.addEventListener('click', function (event) {
            var wrap = event.target.closest('[data-clock-field]');
            if (!wrap) return;
            if (event.target.closest('[data-clock-toggle], [data-clock-display]')) {
                if (!wrap.querySelector('.dispatch-time-picker').hidden) { closePickers(); return; }
                var parts = wrap.querySelector('[data-clock-display]').value.match(/^(\d+):(\d{2}) (AM|PM)$/);
                if (parts) {
                    wrap.querySelector('[data-clock-hour]').value = String(Number(parts[1]));
                    wrap.querySelector('[data-clock-minute]').value = parts[2];
                    wrap.querySelector('[data-clock-period]').value = parts[3];
                }
                openPicker(wrap); wrap.querySelector('[data-clock-hour]').focus();
            }
            if (event.target.closest('[data-clock-set]')) {
                var display = wrap.querySelector('[data-clock-display]');
                display.value = wrap.querySelector('[data-clock-hour]').value + ':' + wrap.querySelector('[data-clock-minute]').value + ' ' + wrap.querySelector('[data-clock-period]').value;
                closePickers(); display.dispatchEvent(new Event('change', {bubbles:true}));
                wrap.querySelector('[data-clock-toggle]').focus();
            }
        });
        document.addEventListener('click', function (event) { if (!event.target.closest('[data-clock-field]')) closePickers(); });
        section.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') { closePickers(); var wrap = event.target.closest('[data-clock-field]'); if (wrap) wrap.querySelector('[data-clock-toggle]').focus(); }
        });
        function validate() {
            var fromChoice = document.getElementById('time_fromMinute'), toChoice = document.getElementById('time_toMinute');
            fromChoice.setCustomValidity(''); toChoice.setCustomValidity('');
            if (alert) alert.style.display = 'none';
            var start = minutes(from.value), end = minutes(to.value), issue = '', target = toChoice;
            if (from.value && start === null) { issue = 'Choose a valid start time.'; target = fromChoice; }
            else if (to.value && end === null) issue = 'Enter a valid end time, such as 5:00 PM.';
            else if (start !== null && end !== null) {
                if (start >= end) issue = 'End time must be later than start time.';
                else {
                    var days = Array.from(form.querySelectorAll('[name="days_of_week[]"]:checked')).map(box => Number(box.value));
                    var route = form.elements.route_id.value, terminal = form.elements.terminal_id.value;
                    var round = form.elements.round_number.value;
                    var overlap = existing.find(rule => {
                        var ruleDays = rule.days_of_week ? String(rule.days_of_week).split(',').map(Number) : rule.day_of_week ? [Number(rule.day_of_week)] : [1,2,3,4,5,6,7];
                        return Number(rule.id) !== Number(section.dataset.ruleId) && String(rule.round_number || 1) === round
                            && (route ? String(rule.route_id) === route : !rule.route_id && String(rule.terminal_id) === terminal)
                            && (days.length === 7) === (ruleDays.length === 7) && days.some(day => ruleDays.includes(day))
                            && savedMinutes(rule.time_from) < end && savedMinutes(rule.time_to) > start;
                    });
                    if (overlap) issue = 'This time window overlaps another rule for the selected days and round. Adjust the time or edit that rule.';
                }
            }
            if (issue) { target.setCustomValidity(issue); if (message) message.textContent = issue; if (alert) alert.style.display = 'flex'; }
            return issue ? target : null;
        }
        form.addEventListener('input', validate);
        form.addEventListener('change', validate);
        form.addEventListener('invalid', function (event) {
            var wrap = event.target.closest('[data-clock-field]');
            if (wrap) openPicker(wrap);
        }, true);
        form.addEventListener('submit', function (event) {
            var invalid = validate();
            if (invalid) { event.preventDefault(); openPicker(invalid.closest('[data-clock-field]')); invalid.focus(); invalid.reportValidity(); }
        });
        validate();
    });
})();
