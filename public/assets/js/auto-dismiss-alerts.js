/**
 * Auto-Dismiss Alerts & Flash Notifications
 * Provides smooth animated entry, hover-to-pause, and automatic exit after 4.5 seconds.
 */
(function () {
    'use strict';

    function initAutoDismissAlerts() {
        // Select all flash and system alert elements (excluding permanent banners like retention-pill)
        var alerts = document.querySelectorAll(
            '.alert-modern, .alert-success-banner, .alert-error-banner, .contact-verification-notice, .alert:not(.alert-permanent):not(.retention-pill):not([data-permanent="true"])'
        );

        alerts.forEach(function (alert) {
            // Prevent double initialization
            if (alert.dataset.dismissInit === 'true') return;
            alert.dataset.dismissInit = 'true';

            // Add close button if not already present and not inside a form field validation
            if (!alert.classList.contains('contact-verification-notice') && !alert.querySelector('.alert-close-btn') && !alert.querySelector('.btn-close')) {
                var closeBtn = document.createElement('button');
                closeBtn.type = 'button';
                closeBtn.className = 'alert-close-btn';
                closeBtn.setAttribute('aria-label', 'Close notification');
                closeBtn.innerHTML = '&times;';
                closeBtn.style.cssText = 'background:none; border:none; color:inherit; opacity:0.6; font-size:18px; cursor:pointer; margin-left:auto; padding:0 6px; line-height:1; flex-shrink:0; transition:all 0.2s; align-self:flex-start;';
                
                closeBtn.addEventListener('mouseenter', function() { closeBtn.style.opacity = '1'; closeBtn.style.transform = 'scale(1.15)'; });
                closeBtn.addEventListener('mouseleave', function() { closeBtn.style.opacity = '0.6'; closeBtn.style.transform = 'scale(1)'; });
                
                closeBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    dismissAlert(alert);
                });

                alert.appendChild(closeBtn);
            }

            var duration = 4500; // 4.5 seconds
            var timerId = null;
            var remaining = duration;
            var startTime = Date.now();

            function startTimer() {
                startTime = Date.now();
                timerId = setTimeout(function () {
                    dismissAlert(alert);
                }, remaining);
            }

            function pauseTimer() {
                if (timerId) {
                    clearTimeout(timerId);
                    timerId = null;
                    remaining -= (Date.now() - startTime);
                    if (remaining < 1000) remaining = 1000; // Give at least 1s after un-hover
                }
            }

            alert.addEventListener('mouseenter', pauseTimer);
            alert.addEventListener('mouseleave', startTimer);

            // Start the auto-dismiss timer
            startTimer();
        });
    }

    function dismissAlert(alert) {
        if (!alert || alert.classList.contains('alert-dismissing')) return;

        // Animate height collapse and fade out
        alert.classList.add('alert-dismissing');
        alert.style.transition = 'opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1), transform 0.4s cubic-bezier(0.4, 0, 0.2, 1), max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1), margin 0.4s ease, padding 0.4s ease';
        alert.style.maxHeight = alert.scrollHeight + 'px';
        
        // Force reflow
        void alert.offsetHeight;

        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-10px) scale(0.98)';
        alert.style.maxHeight = '0px';
        alert.style.marginTop = '0px';
        alert.style.marginBottom = '0px';
        alert.style.paddingTop = '0px';
        alert.style.paddingBottom = '0px';
        alert.style.borderWidth = '0px';
        alert.style.pointerEvents = 'none';

        setTimeout(function () {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, 450);
    }

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAutoDismissAlerts);
    } else {
        initAutoDismissAlerts();
    }

    // Watch for dynamically added notifications (e.g. modals, AJAX forms)
    if (window.MutationObserver) {
        var alertObserver = new MutationObserver(function (mutations) {
            for (var m = 0; m < mutations.length; m++) {
                if (mutations[m].addedNodes.length > 0) {
                    initAutoDismissAlerts();
                    break;
                }
            }
        });
        var startAlertObserver = function () {
            if (document.body) {
                alertObserver.observe(document.body, { childList: true, subtree: true });
            }
        };
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', startAlertObserver);
        } else {
            startAlertObserver();
        }
    }

    // Export globally in case dynamic alerts are created via JS
    window.initAutoDismissAlerts = initAutoDismissAlerts;
    window.dismissAlert = dismissAlert;
})();
