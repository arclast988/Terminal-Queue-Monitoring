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
})();
