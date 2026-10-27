<?php /* Shared typing rules for the 24-hour clock and duration fields. */ ?>
function format24HourInput(input, event) {
    var value = input.value, separator = value.indexOf(':'), hours, minutes;
    var digits = value.replace(/\D/g, '').slice(0, 4);
    if (separator < 0 && input.id === 'wait_duration' && digits.length <= 2) {
        input.value = digits; // A short duration is interpreted as minutes on blur.
        return;
    }
    if (separator >= 0) {
        hours = value.slice(0, separator).replace(/\D/g, '').slice(0, 2);
        minutes = value.slice(separator + 1).replace(/\D/g, '').slice(0, 2);
    } else {
        hours = digits.slice(0, 2);
        minutes = digits.slice(2);
    }
    if (!hours) { input.value = ''; return; }
    if (hours.length === 2 || separator >= 0) hours = String(Math.min(23, Number(hours))).padStart(2, '0');
    if (minutes.length === 2) minutes = String(Math.min(59, Number(minutes))).padStart(2, '0');
    var showSeparator = separator >= 0 || digits.length > 2
        || (digits.length === 2 && event.inputType !== 'deleteContentBackward');
    input.value = hours + (showSeparator ? ':' + minutes : '');
}
