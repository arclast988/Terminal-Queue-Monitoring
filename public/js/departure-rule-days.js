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

    // Adding fills gaps; editing can correct a missing first round.
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
        var terminalScope = scope.split('|')[0] + '|';
        var configured = [];
        scopes.forEach(function (item) {
            if ((item.s === scope || item.s === terminalScope) && item.r >= 1 && item.r <= 999) configured.push(item.r);
        });
        if (scope === roundSelect.dataset.roundOriginalScope && Number(roundSelect.dataset.roundOriginal) > 0) {
            configured.push(Number(roundSelect.dataset.roundOriginal));
        }
        configured = Array.from(new Set(configured)).sort(function (a, b) { return a - b; });
        var choices = configured.slice();
        var newRound = 1;
        while (newRound <= 999 && choices.indexOf(newRound) !== -1) newRound++;
        if (creating && newRound <= 999) choices.push(newRound);
        if (!creating && choices.length && choices.indexOf(1) === -1) choices.push(1);
        if (!choices.length) choices.push(1);
        choices.sort(function (a, b) { return a - b; });
        var selected = Number(roundSelect.value) || initialRound;
        if (creating && previousScope !== null && previousScope !== scope && newRound <= 999) selected = newRound;
        if (choices.indexOf(selected) === -1) selected = choices[0];
        previousScope = scope;
        roundSelect.innerHTML = '';
        choices.forEach(function (n) {
            var opt = document.createElement('option');
            opt.value = String(n);
            opt.textContent = (creating && configured.indexOf(n) === -1 ? 'New round ' : 'Round ') + n;
            if (n === selected) opt.selected = true;
            roundSelect.appendChild(opt);
        });
        var single = choices.length === 1;
        roundSelect.hidden = single;
        roundSelect.classList.toggle('d-none', single);
        if (display) {
            display.hidden = !single;
            display.classList.toggle('d-none', !single);
            display.value = roundSelect.options[roundSelect.selectedIndex].textContent;
        }
        if (label) label.htmlFor = single ? 'round_number_display' : 'round_number';
    }
    if (routeSelect) routeSelect.addEventListener('change', rebuildRounds);
    if (terminalInput) terminalInput.addEventListener('change', rebuildRounds);
    rebuildRounds();
})();
