(function () {
    'use strict';
    var button = document.getElementById('guestContactResend');
    if (!button) return;
    var availableAt = Date.now() + Number(button.dataset.resendWait || 0) * 1000;
    var timer;
    function update() {
        var remaining = Math.max(0, Math.ceil((availableAt - Date.now()) / 1000));
        button.disabled = remaining > 0;
        button.textContent = remaining ? 'Resend code in ' + remaining + 's' : 'Resend code';
        if (!remaining && timer) clearInterval(timer);
    }
    update();
    if (availableAt > Date.now()) timer = setInterval(update, 1000);
})();
