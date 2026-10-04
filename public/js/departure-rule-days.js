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

    // Offer only this destination's rounds plus the next one (1, 2, 3 → 1–4).
    var roundSelect = document.getElementById('round_number');
    var routeSelect = document.getElementById('route_id');
    var terminalInput = document.getElementById('terminal_id');
    if (!roundSelect || !roundSelect.dataset.roundScopes) return;
    var scopes = [];
    try { scopes = JSON.parse(roundSelect.dataset.roundScopes) || []; } catch (e) { scopes = []; }
    var initialRound = Number(roundSelect.value) || 1;
    function currentScope() {
        var option = routeSelect && routeSelect.selectedIndex >= 0 ? routeSelect.options[routeSelect.selectedIndex] : null;
        if (option && option.value && option.dataset.roundScope) return option.dataset.roundScope;
        return (terminalInput ? terminalInput.value : '') + '|';
    }
    function rebuildRounds() {
        var scope = currentScope();
        var max = 0;
        scopes.forEach(function (item) { if (item.s === scope && item.r > max) max = item.r; });
        var selected = Number(roundSelect.value) || initialRound;
        var limit = Math.min(999, max + 1);
        if (selected > limit) selected = limit;
        roundSelect.innerHTML = '';
        for (var n = 1; n <= limit; n++) {
            var opt = document.createElement('option');
            opt.value = String(n);
            opt.textContent = 'Round ' + n;
            if (n === selected) opt.selected = true;
            roundSelect.appendChild(opt);
        }
    }
    if (routeSelect) routeSelect.addEventListener('change', rebuildRounds);
    if (terminalInput) terminalInput.addEventListener('change', rebuildRounds);
    rebuildRounds();
})();
