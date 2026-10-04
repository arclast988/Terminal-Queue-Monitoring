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
        function syncWrap(wrap) {
            var display = wrap.querySelector('[data-clock-display]');
            var selH = wrap.querySelector('[data-clock-hour]');
            var selM = wrap.querySelector('[data-clock-minute]');
            var selP = wrap.querySelector('[data-clock-period]');
            var inH = wrap.querySelector('[data-tp-input="hour"]');
            var inM = wrap.querySelector('[data-tp-input="min"]');
            var periodBtns = wrap.querySelectorAll('[data-tp-action="period"]');

            var h = '12', m = '00', p = 'AM';
            var parts = display && display.value ? display.value.match(/^(\d+):(\d{2}) (AM|PM)$/i) : null;
            if (parts) {
                h = String(Number(parts[1]));
                m = parts[2];
                p = parts[3].toUpperCase();
            } else if (selH && selM && selP) {
                h = selH.value;
                m = selM.value;
                p = selP.value;
            }
            if (inH) inH.value = h;
            if (inM) inM.value = m;
            if (selH) selH.value = h;
            if (selM) selM.value = m;
            if (selP) selP.value = p;
            periodBtns.forEach(function (btn) {
                var isActive = btn.getAttribute('data-period') === p;
                btn.classList.toggle('active', isActive);
                btn.setAttribute('aria-pressed', String(isActive));
            });
        }

        section.querySelectorAll('[data-clock-field]').forEach(function (wrap) {
            syncWrap(wrap);
            var selH = wrap.querySelector('[data-clock-hour]');
            var selM = wrap.querySelector('[data-clock-minute]');
            var selP = wrap.querySelector('[data-clock-period]');
            [selH, selM, selP].forEach(function (sel) {
                if (sel) sel.addEventListener('change', function () { syncWrap(wrap); });
            });
            var inH = wrap.querySelector('[data-tp-input="hour"]');
            var inM = wrap.querySelector('[data-tp-input="min"]');
            if (inH) {
                inH.addEventListener('change', function () {
                    var val = parseInt(inH.value, 10);
                    if (isNaN(val) || val < 1) val = 1;
                    if (val > 12) val = 12;
                    inH.value = String(val);
                    if (selH) selH.value = String(val);
                });
            }
            if (inM) {
                inM.addEventListener('change', function () {
                    var val = parseInt(inM.value, 10);
                    if (isNaN(val) || val < 0) val = 0;
                    if (val > 59) val = 59;
                    inM.value = String(val).padStart(2, '0');
                    if (selM) selM.value = inM.value;
                });
            }
        });

        section.addEventListener('click', function (event) {
            var wrap = event.target.closest('[data-clock-field]');
            if (!wrap) return;
            var picker = wrap.querySelector('.dispatch-time-picker');

            if (event.target.closest('[data-clock-toggle], [data-clock-display]')) {
                if (!picker.hidden) { closePickers(); return; }
                syncWrap(wrap);
                openPicker(wrap);
                var inH = wrap.querySelector('[data-tp-input="hour"]');
                if (inH) inH.focus();
                return;
            }

            var inH = wrap.querySelector('[data-tp-input="hour"]');
            var inM = wrap.querySelector('[data-tp-input="min"]');
            var selH = wrap.querySelector('[data-clock-hour]');
            var selM = wrap.querySelector('[data-clock-minute]');
            var selP = wrap.querySelector('[data-clock-period]');

            var actionBtn = event.target.closest('[data-tp-action]');
            if (actionBtn) {
                var act = actionBtn.getAttribute('data-tp-action');
                if (act === 'hour-up') {
                    var h = parseInt(inH.value, 10) || 12;
                    h = h >= 12 ? 1 : h + 1;
                    inH.value = String(h);
                    if (selH) selH.value = String(h);
                } else if (act === 'hour-down') {
                    var h = parseInt(inH.value, 10) || 1;
                    h = h <= 1 ? 12 : h - 1;
                    inH.value = String(h);
                    if (selH) selH.value = String(h);
                } else if (act === 'min-up') {
                    var m = parseInt(inM.value, 10) || 0;
                    m = (m + 1) % 60;
                    inM.value = String(m).padStart(2, '0');
                    if (selM) selM.value = inM.value;
                } else if (act === 'min-down') {
                    var m = parseInt(inM.value, 10) || 0;
                    m = (m - 1 + 60) % 60;
                    inM.value = String(m).padStart(2, '0');
                    if (selM) selM.value = inM.value;
                } else if (act === 'period') {
                    var p = actionBtn.getAttribute('data-period');
                    wrap.querySelectorAll('[data-tp-action="period"]').forEach(function (btn) {
                        var isActive = btn === actionBtn;
                        btn.classList.toggle('active', isActive);
                        btn.setAttribute('aria-pressed', String(isActive));
                    });
                    if (selP) selP.value = p;
                }
                return;
            }

            if (event.target.closest('[data-clock-set]')) {
                var display = wrap.querySelector('[data-clock-display]');
                var curH = inH ? (parseInt(inH.value, 10) || 12) : 12;
                if (curH < 1) curH = 1;
                if (curH > 12) curH = 12;
                var curM = inM ? (parseInt(inM.value, 10) || 0) : 0;
                if (curM < 0) curM = 0;
                if (curM > 59) curM = 59;
                var activePeriodBtn = wrap.querySelector('.tp-period-btn.active');
                var curP = activePeriodBtn ? activePeriodBtn.getAttribute('data-period') : (selP ? selP.value : 'AM');

                var formattedMinute = String(curM).padStart(2, '0');
                if (inH) inH.value = String(curH);
                if (inM) inM.value = formattedMinute;
                if (selH) selH.value = String(curH);
                if (selM) selM.value = formattedMinute;
                if (selP) selP.value = curP;

                display.value = curH + ':' + formattedMinute + ' ' + curP;
                closePickers();
                display.dispatchEvent(new Event('change', { bubbles: true }));
                wrap.querySelector('[data-clock-toggle]').focus();
            }
        });
        document.addEventListener('click', function (event) { if (!event.target.closest('[data-clock-field]')) closePickers(); });
        section.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') { closePickers(); var wrap = event.target.closest('[data-clock-field]'); if (wrap) wrap.querySelector('[data-clock-toggle]').focus(); }
            if (event.key === 'Enter') {
                var wrap = event.target.closest('[data-clock-field]');
                if (wrap && !wrap.querySelector('.dispatch-time-picker').hidden) {
                    event.preventDefault();
                    var setBtn = wrap.querySelector('[data-clock-set]');
                    if (setBtn) setBtn.click();
                }
            }
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
