(function () {
    'use strict';
    var script = document.currentScript;
    if (!script || window.AccountSessionGuard) return;
    var statusUrl = script.dataset.statusUrl;
    var loginUrl = script.dataset.loginUrl;
    if (!statusUrl || !loginUrl) return;
    window.AccountSessionGuard = true;
    var pending = false;
    var ended = false;
    var timer;

    async function checkSession() {
        if (pending || ended || document.hidden) return;
        pending = true;
        var controller = new AbortController();
        var timeout = setTimeout(function () { controller.abort(); }, 8000);
        try {
            var response = await fetch(statusUrl, {
                credentials: 'same-origin',
                cache: 'no-store',
                signal: controller.signal,
                headers: {'X-Requested-With': 'XMLHttpRequest', 'X-Silent': 'true'}
            });
            if (response.status === 401) {
                ended = true;
                clearInterval(timer);
                window.location.replace(loginUrl);
            }
        } catch (error) {
            // Retry when connectivity returns; an outage does not end a session.
        } finally {
            clearTimeout(timeout);
            pending = false;
        }
    }

    function start() {
        if (ended) return;
        clearInterval(timer);
        checkSession();
        timer = setInterval(checkSession, 10000);
    }
    document.addEventListener('visibilitychange', checkSession);
    window.addEventListener('pageshow', start);
    window.addEventListener('pagehide', function () { clearInterval(timer); });
    start();
}());
