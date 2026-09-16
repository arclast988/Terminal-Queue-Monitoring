/**
 * Vehicle Type Live Colors
 * ─────────────────────────────────────────────────────────────
 * Keeps every vehicle-type color on the page in sync with the admin
 * configuration WITHOUT a reload. Works on pages that do NOT use
 * QueueSync (fares, search, vehicle registry, ...).
 *
 * - Pages WITH QueueSync: skipped, QueueSync already applies colors.
 * - Pages WITHOUT: opens the shared WebSocket (via QueueWS) and applies
 *   `vehicle_type_update` color maps to :root CSS vars. All badges,
 *   icon boxes, chips and fare cards use those vars, so they recolor
 *   instantly. A `vt-colors-updated` DOM event is also dispatched so
 *   pages with custom renderers (e.g. fares grid) can rebuild.
 */
(function (window, document) {
    'use strict';

    function applyColors(colors) {
        if (!colors || typeof colors !== 'object') return;
        var root = document.documentElement;
        var applied = 0;
        Object.keys(colors).forEach(function (slug) {
            var entry = colors[slug];
            var color = (entry && typeof entry === 'object') ? entry.color : entry;
            if (typeof color !== 'string' || !/^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/.test(color)) return;
            var key = String(slug).toLowerCase().replace(/[^a-z0-9_-]+/g, '_').replace(/^[_-]+|[_-]+$/g, '');
            if (!key) return;
            try {
                root.style.setProperty('--vehicle-' + key, color);
                root.style.setProperty('--vehicle-' + key + '-soft', color + '18');
                applied++;
            } catch (e) { /* ignore */ }
        });
        if (applied > 0) {
            try {
                // Refresh static per-option colors used by autocomplete
                // dropdowns so the next open shows fresh colors.
                syncOptionColors(colors);
            } catch (e) { /* ignore */ }
            try {
                document.dispatchEvent(new CustomEvent('vt-colors-updated'));
            } catch (e) { /* ignore */ }
        }
    }

    function syncOptionColors(colors) {
        var opts = document.querySelectorAll('option[data-color]');
        Array.prototype.forEach.call(opts, function (opt) {
            var slug = String(opt.value || '').toLowerCase();
            var entry = colors[slug] || colors[opt.value];
            var color = (entry && typeof entry === 'object') ? entry.color : entry;
            if (typeof color === 'string' && /^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/.test(color)) {
                opt.setAttribute('data-color', color);
            }
        });
    }

    function applyPhotos(colorsOrTypes) {
        if (!colorsOrTypes || typeof colorsOrTypes !== 'object') return;
        Object.keys(colorsOrTypes).forEach(function (slug) {
            var entry = colorsOrTypes[slug];
            var photo = (entry && typeof entry === 'object') ? entry.photo : null;
            if (!photo && typeof entry === 'string' && (entry.indexOf('http') === 0 || entry.indexOf('/') === 0)) {
                photo = entry;
            }
            var icon = (entry && typeof entry === 'object' && entry.icon) ? entry.icon : 'fa-bus';
            var color = (entry && typeof entry === 'object' && entry.color) ? entry.color : '';
            var key = String(slug).toLowerCase().replace(/[^a-z0-9_-]+/g, '_').replace(/^[_-]+|[_-]+$/g, '');
            if (!key) return;

            if (photo) {
                // Photo is uploaded: Update any existing img, or replace icon boxes with img
                var existingImgs = document.querySelectorAll([
                    'img[data-vt-photo="' + key + '"]',
                    '.vehicle-type-' + key + ' img',
                    '.vehicle-type-icon.vehicle-type-' + key + ' img',
                    '#fare-card-' + key + ' .card-header img',
                    '[data-vehicle-type="' + key + '"] .vehicle-type-icon img',
                    '[data-vehicle-type="' + key + '"] .vehicle-thumb-img',
                    'button[data-type="' + key + '"] img',
                    '.vf-' + key + ' img'
                ].join(', '));

                if (existingImgs.length > 0) {
                    existingImgs.forEach(function (img) {
                        img.src = photo;
                    });
                }

                // If fare card currently shows an icon box, upgrade to img
                var fareCardIcon = document.querySelector('#fare-card-' + key + ' .card-header [data-vt-icon-box="' + key + '"]');
                if (fareCardIcon) {
                    var newImg = document.createElement('img');
                    newImg.src = photo;
                    newImg.alt = key;
                    newImg.style.width = '36px';
                    newImg.style.height = '36px';
                    newImg.style.objectFit = 'contain';
                    newImg.setAttribute('data-vt-photo', key);
                    if (fareCardIcon.parentNode) {
                        fareCardIcon.parentNode.replaceChild(newImg, fareCardIcon);
                    }
                }

                // If table cells or queue items have .vehicle-type-icon with <i> and no <img>
                document.querySelectorAll('.vehicle-type-icon.vehicle-type-' + key + ', [data-vehicle-type="' + key + '"] .vehicle-type-icon').forEach(function (iconBox) {
                    if (!iconBox.querySelector('img')) {
                        var iEl = iconBox.querySelector('i');
                        var img = document.createElement('img');
                        img.src = photo;
                        img.alt = key;
                        img.style.height = '28px';
                        img.style.width = 'auto';
                        img.style.maxWidth = '32px';
                        img.style.objectFit = 'contain';
                        img.setAttribute('data-vt-photo', key);
                        if (iEl) {
                            iconBox.replaceChild(img, iEl);
                        } else {
                            iconBox.appendChild(img);
                        }
                    }
                });

                // Enhanced dashboard thumb box
                document.querySelectorAll('[data-vehicle-type="' + key + '"] .vehicle-thumb-box').forEach(function (thumbBox) {
                    var oldImg = thumbBox.querySelector('img');
                    if (oldImg) {
                        oldImg.src = photo;
                    } else {
                        var oldI = thumbBox.querySelector('i');
                        var img = document.createElement('img');
                        img.src = photo;
                        img.alt = key;
                        img.className = 'vehicle-thumb-img';
                        img.setAttribute('data-vt-photo', key);
                        if (oldI) {
                            thumbBox.replaceChild(img, oldI);
                        } else {
                            thumbBox.insertBefore(img, thumbBox.firstChild);
                        }
                    }
                });

                // Filter buttons / tabs
                document.querySelectorAll('button[data-type="' + key + '"], .vf-' + key + ', .dep-filter-btn[data-type="' + key + '"]').forEach(function (btn) {
                    var oldI = btn.querySelector('i.fas, i.bi');
                    if (oldI && !btn.querySelector('img')) {
                        var img = document.createElement('img');
                        img.src = photo;
                        img.alt = key;
                        img.className = 'dep-filter-icon';
                        img.style.height = '18px';
                        img.style.width = 'auto';
                        img.style.maxWidth = '24px';
                        img.style.objectFit = 'contain';
                        img.setAttribute('data-vt-photo', key);
                        btn.replaceChild(img, oldI);
                    }
                });
            } else {
                // Photo was removed / deleted / is null: revert to assigned icon!
                var fareCardImg = document.querySelector('#fare-card-' + key + ' .card-header img[data-vt-photo="' + key + '"], #fare-card-' + key + ' .card-header img');
                if (fareCardImg) {
                    var iconSpan = document.createElement('span');
                    iconSpan.className = 'd-inline-flex align-items-center justify-content-center rounded-2 text-white me-2';
                    iconSpan.style.width = '36px';
                    iconSpan.style.height = '36px';
                    iconSpan.style.backgroundColor = color || 'var(--vehicle-' + key + ', #c62828)';
                    iconSpan.style.fontSize = '16px';
                    iconSpan.setAttribute('data-vt-icon-box', key);
                    iconSpan.innerHTML = '<i class="fas ' + icon + '"></i>';
                    if (fareCardImg.parentNode) {
                        fareCardImg.parentNode.replaceChild(iconSpan, fareCardImg);
                    }
                } else {
                    var existingBox = document.querySelector('#fare-card-' + key + ' .card-header [data-vt-icon-box="' + key + '"]');
                    if (existingBox) {
                        var curI = existingBox.querySelector('i');
                        if (curI) curI.className = 'fas ' + icon;
                        if (color) existingBox.style.backgroundColor = color;
                    }
                }

                // Table cells or queue items: swap <img> with <i>
                document.querySelectorAll('.vehicle-type-icon.vehicle-type-' + key + ', [data-vehicle-type="' + key + '"] .vehicle-type-icon').forEach(function (iconBox) {
                    var oldImg = iconBox.querySelector('img');
                    if (oldImg) {
                        var iEl = document.createElement('i');
                        iEl.className = 'fas ' + icon;
                        iEl.style.color = color || 'var(--vehicle-' + key + ')';
                        iEl.style.fontSize = '16px';
                        iconBox.replaceChild(iEl, oldImg);
                    } else {
                        var curI = iconBox.querySelector('i');
                        if (curI) {
                            curI.className = 'fas ' + icon;
                            if (color) curI.style.color = color;
                        }
                    }
                });

                // Enhanced dashboard thumb box
                document.querySelectorAll('[data-vehicle-type="' + key + '"] .vehicle-thumb-box').forEach(function (thumbBox) {
                    var oldImg = thumbBox.querySelector('img');
                    if (oldImg) {
                        var iEl = document.createElement('i');
                        iEl.className = 'fas ' + icon;
                        iEl.style.fontSize = '28px';
                        iEl.style.color = color || 'var(--vehicle-' + key + ')';
                        thumbBox.replaceChild(iEl, oldImg);
                    } else {
                        var curI = thumbBox.querySelector('i');
                        if (curI) {
                            curI.className = 'fas ' + icon;
                            if (color) curI.style.color = color;
                        }
                    }
                });

                // Filter buttons / tabs
                document.querySelectorAll('button[data-type="' + key + '"], .vf-' + key + ', .dep-filter-btn[data-type="' + key + '"]').forEach(function (btn) {
                    var oldImg = btn.querySelector('img');
                    if (oldImg) {
                        var iEl = document.createElement('i');
                        iEl.className = 'fas ' + icon + ' me-1';
                        iEl.style.color = color || 'var(--vehicle-' + key + ')';
                        iEl.style.fontSize = '13px';
                        btn.replaceChild(iEl, oldImg);
                    } else {
                        var curI = btn.querySelector('i.fas, i.bi');
                        if (curI) {
                            curI.className = 'fas ' + icon + ' me-1';
                            if (color) curI.style.color = color;
                        }
                    }
                });
            }
        });
    }

    function handleMessage(message) {
        var data = (message && message.data) || message || {};
        if (data.colors) {
            applyColors(data.colors);
            applyPhotos(data.colors);
        } else if (message && message.colors) {
            applyColors(message.colors);
            applyPhotos(message.colors);
        }

        if (data.photo && (data.new_slug || data.slug)) {
            var obj = {};
            obj[data.new_slug || data.slug] = { photo: data.photo, icon: data.icon, color: data.color };
            applyPhotos(obj);
        }

        // If photo or type details updated, dispatch event and update photo thumbnails
        if (data.type) {
            try {
                document.dispatchEvent(new CustomEvent('pttm:vehicle-type-updated', { detail: data.type }));
            } catch (e) {}
        }

        // Any vehicle-type change or structural refresh implies colors/icons/photos changed.
        if (data.action && data.action !== 'passenger_change') {
            try {
                document.dispatchEvent(new CustomEvent('vt-colors-updated', { detail: data }));
            } catch (e) { /* ignore */ }
        }
    }

    function init() {
        // QueueSync pages already handle live colors — do not double-connect.
        if (window.QueueSync) return;
        if (!window.QueueWS) return;
        try {
            window.QueueWS.init({
                onQueueUpdate: handleMessage,
                onVehicleTypeUpdate: handleMessage,
                onFareUpdate: handleMessage,
                onAnnouncementUpdate: handleMessage,
                pollingInterval: 30000
            });
        } catch (e) { /* WebSocket unavailable — static colors remain */ }
    }

    window.VehicleTypeLive = { apply: applyColors };

    // Defer to window load so pages that bring their own QueueSync/QueueWS
    // (loaded after this script) are detected and not double-connected.
    if (document.readyState === 'complete') {
        init();
    } else {
        window.addEventListener('load', init);
    }
})(window, document);
