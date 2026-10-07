(function () {
    'use strict';
    var group = document.getElementById('ruleDays');
    if (!group) return;
    var boxes = Array.from(group.querySelectorAll('input[name="days_of_week[]"]'));
    var all = document.getElementById('ruleEveryDay');
    var summary = document.getElementById('ruleDaysSummary');
    var names = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    function update() {
        var days = boxes.filter(function (box) { return box.checked; }).map(function (box) { return Number(box.value); });
        all.checked = days.length === 7;
        all.indeterminate = days.length > 0 && days.length < 7;
        boxes[0].setCustomValidity(days.length ? '' : 'Select at least one day.');
        var parts = [];
        for (var i = 0; i < days.length; i++) {
            var first = days[i], last = first;
            while (days[i + 1] === last + 1) last = days[++i];
            parts.push(first === last ? names[first - 1] : names[first - 1] + '–' + names[last - 1]);
        }
        summary.textContent = days.length === 7 ? 'Every day' : (parts.join(', ') || 'Select at least one day.');
    }
    all.addEventListener('change', function () {
        boxes.forEach(function (box) { box.checked = all.checked; });
        update();
        group.dispatchEvent(new Event('change', { bubbles: true }));
    });
    boxes.forEach(function (box) { box.addEventListener('change', update); });
    update();

    // Assign only rounds that are unused for the selected destination and days.
    var roundSelect = document.getElementById('round_number');
    var routeSelect = document.getElementById('route_id');
    var terminalInput = document.getElementById('terminal_id');
    if (!roundSelect || !roundSelect.dataset.roundScopes) return;
    var scopes = [];
    try { scopes = JSON.parse(roundSelect.dataset.roundScopes) || []; } catch (e) { scopes = []; }
    var initialRound = Number(roundSelect.value) || 1;
    var display = document.getElementById('round_number_display');
    var label = document.getElementById('round_number_label');
    var creating = roundSelect.dataset.roundCreate === 'true';
    var previousScope = null;
    function currentScope() {
        var option = routeSelect && routeSelect.selectedIndex >= 0 ? routeSelect.options[routeSelect.selectedIndex] : null;
        if (option && option.value && option.dataset.roundScope) return option.dataset.roundScope;
        return (terminalInput ? terminalInput.value : '') + '|';
    }
    function rebuildRounds() {
        var scope = currentScope();
        var selectedDays = boxes.filter(function (box) { return box.checked; }).map(function (box) { return Number(box.value); });
        var original = Number(roundSelect.dataset.roundOriginal) || initialRound;
        var ownRule = Number(roundSelect.dataset.roundRuleId) || 0;
        var configured = [];
        scopes.forEach(function (item) {
            var ruleDays = Array.isArray(item.d) ? item.d : [1, 2, 3, 4, 5, 6, 7];
            if (item.s === scope && item.i !== ownRule && item.r >= 1 && item.r <= 999
                && selectedDays.some(function (day) { return ruleDays.indexOf(day) !== -1; })) {
                configured.push(item.r);
            }
        });
        configured = Array.from(new Set(configured)).sort(function (a, b) { return a - b; });
        var allowNewRound = creating || scope !== roundSelect.dataset.roundOriginalScope || configured.indexOf(original) !== -1;
        var choices = [];
        if (!creating && scope === roundSelect.dataset.roundOriginalScope && configured.indexOf(original) === -1) choices.push(original);
        var newRound = 1;
        while (newRound <= 999 && configured.indexOf(newRound) !== -1) newRound++;
        if ((allowNewRound || !choices.length) && newRound <= 999) choices.push(newRound);
        if (!allowNewRound && original > 1 && configured.indexOf(1) === -1) choices.push(1);
        choices = Array.from(new Set(choices)).sort(function (a, b) { return a - b; });
        var selected = Number(roundSelect.value) || initialRound;
        if (previousScope !== null && previousScope !== scope) {
            if (allowNewRound && newRound <= 999) selected = newRound;
            else if (!creating && scope === roundSelect.dataset.roundOriginalScope) selected = Number(roundSelect.dataset.roundOriginal) || initialRound;
        }
        if (choices.indexOf(selected) === -1) selected = choices[0];
        previousScope = scope;
        roundSelect.innerHTML = '';
        roundSelect.setCustomValidity(choices.length ? '' : 'All rounds for these days are already configured.');
        choices.forEach(function (n) {
            var opt = document.createElement('option');
            opt.value = String(n);
            opt.textContent = (allowNewRound && configured.indexOf(n) === -1 ? 'New round ' : 'Round ') + n;
            if (n === selected) opt.selected = true;
            roundSelect.appendChild(opt);
        });
        var single = choices.length === 1 && configured.length === 0;
        roundSelect.hidden = single;
        roundSelect.classList.toggle('d-none', single);
        if (display) {
            display.hidden = !single;
            display.classList.toggle('d-none', !single);
            display.value = roundSelect.options[roundSelect.selectedIndex] ? roundSelect.options[roundSelect.selectedIndex].textContent : 'No available round';
        }
        if (label) label.htmlFor = single ? 'round_number_display' : 'round_number';
        roundSelect.dispatchEvent(new Event('change', { bubbles: true }));
    }
    if (routeSelect) routeSelect.addEventListener('change', rebuildRounds);
    if (terminalInput) terminalInput.addEventListener('change', rebuildRounds);
    group.addEventListener('change', rebuildRounds);
    rebuildRounds();
})();
