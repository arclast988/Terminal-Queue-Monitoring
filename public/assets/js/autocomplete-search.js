/**
 * Palompon Transit Management System - Searchable Location, Terminal, Route, Role & Select Autocomplete
 * Converts select fields across the application into search-bar style inputs without dropdown arrows.
 */

(function () {
    'use strict';

    var MAX_DROPDOWN_ITEMS = 50;
    var INPUT_DEBOUNCE_MS = 150;

    function getDefaultDropdownMaxHeight(dropdown) {
        if (!dropdown) return 240;

        if (!dropdown.dataset.defaultMaxHeight) {
            var configuredHeight = parseInt(dropdown.style.maxHeight || '', 10);
            dropdown.dataset.defaultMaxHeight = String(Number.isFinite(configuredHeight) && configuredHeight > 0 ? configuredHeight : 240);
        }

        return parseInt(dropdown.dataset.defaultMaxHeight, 10) || 240;
    }

    function debounce(fn, wait) {
        var t = null;
        return function () {
            var args = arguments;
            var ctx = this;
            if (t) clearTimeout(t);
            t = setTimeout(function () {
                t = null;
                fn.apply(ctx, args);
            }, wait);
        };
    }

    // Helper functions to manage active z-index stacking context for open dropdowns
    function closeAllAutocompleteDropdowns(exceptWrapper) {
        // Single-open invariant: only one autocomplete dropdown may be open
        // at a time, otherwise panels overlap and labels bleed through.
        document.querySelectorAll('.autocomplete-wrapper.is-open').forEach(function (w) {
            if (w === exceptWrapper) return;
            w.classList.remove('is-open');
            w.style.zIndex = '';
            var dd = w.querySelector('.autocomplete-dropdown');
            if (dd) dd.style.display = 'none';
            var parentContainer = w.closest('.col, .col-12, .col-sm-6, .col-md-6, .col-lg-4, .col-xl-2, .col-xl-3, .mb-3, .mb-4, .row, .form-group, fieldset');
            if (parentContainer) {
                parentContainer.classList.remove('autocomplete-parent-active');
                parentContainer.style.removeProperty('z-index');
            }
        });
    }

    function openDropdown(wrapper, dropdown) {
        if (!wrapper || !dropdown) return;
        closeAllAutocompleteDropdowns(wrapper);
        wrapper.classList.add('is-open');
        var modalEl = wrapper.closest('.modal-content, .modal-body, .modal-dialog, .modal');
        var isInModal = Boolean(modalEl);
        wrapper.style.zIndex = isInModal ? '1065' : '100';
        dropdown.style.display = 'block';
        var defaultMaxHeight = getDefaultDropdownMaxHeight(dropdown);

        // Dynamic smart placement: flip upward if space below is limited and space above has more room
        try {
            var rect = wrapper.getBoundingClientRect();
            var boundaryBottom = window.innerHeight;
            var boundaryTop = 0;

            if (modalEl) {
                var modalContent = wrapper.closest('.modal-content') || modalEl;
                var mRect = modalContent.getBoundingClientRect();
                boundaryBottom = mRect.bottom;
                boundaryTop = mRect.top;

                var modalFooter = modalContent.querySelector('.modal-footer');
                if (modalFooter) {
                    var fRect = modalFooter.getBoundingClientRect();
                    if (fRect.top > rect.bottom) {
                        boundaryBottom = Math.min(boundaryBottom, fRect.top);
                    }
                }
            }

            var spaceBelow = boundaryBottom - rect.bottom;
            var spaceAbove = rect.top - boundaryTop;

            if (spaceBelow < defaultMaxHeight && spaceAbove > spaceBelow) {
                dropdown.style.top = 'auto';
                dropdown.style.bottom = 'calc(100% + 6px)';
                dropdown.classList.add('dropdown-flipped');
                dropdown.style.maxHeight = Math.min(defaultMaxHeight, Math.max(120, spaceAbove - 15)) + 'px';
            } else {
                dropdown.style.top = 'calc(100% + 6px)';
                dropdown.style.bottom = 'auto';
                dropdown.classList.remove('dropdown-flipped');
                if (spaceBelow < defaultMaxHeight + 10) {
                    dropdown.style.maxHeight = Math.min(defaultMaxHeight, Math.max(120, spaceBelow - 15)) + 'px';
                } else {
                    // Keep the component's configured cap. Clearing this value
                    // made short mobile select menus expand across the page.
                    dropdown.style.maxHeight = defaultMaxHeight + 'px';
                }
            }
        } catch (err) {}

        const parentContainer = wrapper.closest('.col, .col-12, .col-sm-6, .col-md-6, .col-lg-4, .col-xl-2, .col-xl-3, .mb-3, .mb-4, .row, .form-group, fieldset');
        if (parentContainer) {
            parentContainer.classList.add('autocomplete-parent-active');
            parentContainer.style.position = 'relative';
            // The active field must rise above later sibling fields. Several
            // filter layouts use z-index:100 on every form group.
            parentContainer.style.setProperty('z-index', isInModal ? '1060' : '200', 'important');
        }
    }

    function closeDropdown(wrapper, dropdown) {
        if (!wrapper || !dropdown) return;
        wrapper.classList.remove('is-open');
        wrapper.style.zIndex = '';
        dropdown.style.display = 'none';
        dropdown.classList.remove('dropdown-flipped');
        dropdown.style.maxHeight = getDefaultDropdownMaxHeight(dropdown) + 'px';

        const parentContainer = wrapper.closest('.col, .col-12, .col-sm-6, .col-md-6, .col-lg-4, .col-xl-2, .col-xl-3, .mb-3, .mb-4, .row, .form-group, fieldset');
        if (parentContainer) {
            parentContainer.classList.remove('autocomplete-parent-active');
            parentContainer.style.removeProperty('z-index');
        }
    }

    // Always close autocomplete dropdowns when modals open or forms submit
    document.addEventListener('show.bs.modal', function () { closeAllAutocompleteDropdowns(); });
    document.addEventListener('submit', function () { closeAllAutocompleteDropdowns(); });
    // One outside-click listener is enough, even on pages with many selects.
    document.addEventListener('click', function (event) {
        document.querySelectorAll('.autocomplete-wrapper.is-open').forEach(function (wrapper) {
            if (wrapper.contains(event.target)) return;
            var dropdown = wrapper.querySelector('.autocomplete-dropdown');
            closeDropdown(wrapper, dropdown);
            if (wrapper.syncAutocompleteValue) wrapper.syncAutocompleteValue();
        });
    });

    // Inject base dropdown CSS styles to ensure complete appearance on all pages
    if (!document.getElementById('autocomplete-search-base-styles')) {
        const style = document.createElement('style');
        style.id = 'autocomplete-search-base-styles';
        style.textContent = `
            .autocomplete-wrapper {
                position: relative !important;
                z-index: 10;
                overflow: visible !important;
            }
            .autocomplete-wrapper.is-open {
                z-index: 100 !important;
            }
            .modal .autocomplete-wrapper.is-open {
                z-index: 1065 !important;
            }
            .autocomplete-parent-active {
                z-index: 200 !important;
                position: relative !important;
            }
            .modal .autocomplete-parent-active {
                z-index: 1060 !important;
            }
            .autocomplete-dropdown {
                background: #ffffff;
                border: 1px solid #cbd5e1;
                box-shadow: 0 16px 40px rgba(0, 0, 0, 0.18);
                border-radius: 14px;
                overflow-y: auto;
                z-index: 1000 !important;
                padding: 6px;
            }
            .modal .autocomplete-dropdown {
                z-index: 1070 !important;
            }
            .autocomplete-dropdown.dropdown-flipped {
                box-shadow: 0 -12px 32px rgba(0, 0, 0, 0.16) !important;
            }
            .autocomplete-item {
                color: #1e293b;
                background: #ffffff;
                border-radius: 8px;
                margin-bottom: 2px;
            }
            .autocomplete-item span {
                color: inherit;
            }
            .autocomplete-item:last-child {
                margin-bottom: 0;
            }
            .autocomplete-item:hover,
            .autocomplete-item.active-item {
                background-color: #f1f5f9;
                color: #1e40af;
            }
            .autocomplete-item mark.search-highlight {
                background: rgba(30, 64, 175, 0.12);
                color: #1e40af;
                border-radius: 3px;
                padding: 0 3px;
                font-weight: 700;
            }
            .autocomplete-badge {
                background: #f1f5f9;
                color: #475569;
                border: 1px solid #e2e8f0;
            }

            /* Autocomplete & Filter Clear (x) Button - Clean, Instant, No Animations */
            .autocomplete-wrapper ~ .dropdown-chevron-icon,
            .position-relative:has(.autocomplete-wrapper) > .dropdown-chevron-icon {
                display: none !important;
            }

            .autocomplete-clear-btn {
                position: absolute !important;
                right: 12px !important;
                top: 50% !important;
                transform: translateY(-50%) !important;
                background: transparent !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                color: #94a3b8 !important;
                cursor: pointer !important;
                display: none;
                align-items: center !important;
                justify-content: center !important;
                z-index: 15 !important;
                line-height: 1 !important;
                outline: none !important;
                box-shadow: none !important;
                transition: color 0.15s ease !important;
            }
            .autocomplete-clear-btn:hover {
                color: #475569 !important;
            }
            .autocomplete-clear-btn svg {
                width: 15px !important;
                height: 15px !important;
                display: block !important;
                pointer-events: none !important;
            }

            /* Explicit Light Theme Styling (Base Container) */
            .autocomplete-dropdown {
                background: #ffffff !important;
                background-color: #ffffff !important;
                border-color: #cbd5e1 !important;
                box-shadow: 0 16px 40px rgba(0, 0, 0, 0.18) !important;
            }
            .autocomplete-item {
                color: #1e293b !important;
                background: #ffffff !important;
                border-bottom: 1px solid #f1f5f9 !important;
            }
            .autocomplete-item span,
            .autocomplete-item .autocomplete-item-text {
                color: #1e293b !important;
                -webkit-text-fill-color: #1e293b !important;
            }

            /* --- ADMIN THEME --- */
            body.admin-theme .autocomplete-item i,
            body.admin-theme .autocomplete-item .autocomplete-item-text i {
                color: var(--primary, #B71C1C) !important;
                -webkit-text-fill-color: var(--primary, #B71C1C) !important;
            }
            body.admin-theme .autocomplete-item:hover,
            body.admin-theme .autocomplete-item.active-item {
                background-color: var(--primary-soft, #fee2e2) !important;
                color: var(--primary, #B71C1C) !important;
            }
            body.admin-theme .autocomplete-item:hover span,
            body.admin-theme .autocomplete-item.active-item span,
            body.admin-theme .autocomplete-item:hover .autocomplete-item-text,
            body.admin-theme .autocomplete-item.active-item .autocomplete-item-text,
            body.admin-theme .autocomplete-item:hover i,
            body.admin-theme .autocomplete-item.active-item i {
                color: var(--primary, #B71C1C) !important;
                -webkit-text-fill-color: var(--primary, #B71C1C) !important;
            }
            body.admin-theme .autocomplete-badge {
                background: var(--primary-soft, #fee2e2) !important;
                color: var(--primary, #B71C1C) !important;
                -webkit-text-fill-color: var(--primary, #B71C1C) !important;
                border: 1px solid var(--primary-soft, #fca5a5) !important;
            }

            /* --- STAFF THEME --- */
            body.staff-theme .autocomplete-item i,
            body.staff-theme .autocomplete-item .autocomplete-item-text i {
                color: var(--primary, #15803d) !important;
                -webkit-text-fill-color: var(--primary, #15803d) !important;
            }
            body.staff-theme .autocomplete-item:hover,
            body.staff-theme .autocomplete-item.active-item {
                background-color: var(--primary-soft, #dcfce7) !important;
                color: var(--primary, #15803d) !important;
            }
            body.staff-theme .autocomplete-item:hover span,
            body.staff-theme .autocomplete-item.active-item span,
            body.staff-theme .autocomplete-item:hover .autocomplete-item-text,
            body.staff-theme .autocomplete-item.active-item .autocomplete-item-text,
            body.staff-theme .autocomplete-item:hover i,
            body.staff-theme .autocomplete-item.active-item i {
                color: var(--primary, #15803d) !important;
                -webkit-text-fill-color: var(--primary, #15803d) !important;
            }
            body.staff-theme .autocomplete-badge {
                background: var(--primary-soft, #dcfce7) !important;
                color: var(--primary, #15803d) !important;
                -webkit-text-fill-color: var(--primary, #15803d) !important;
                border: 1px solid var(--primary-soft, #86efac) !important;
            }

            /* --- GUEST / DEFAULT THEME --- */
            body:not(.admin-theme):not(.staff-theme) .autocomplete-item i,
            body:not(.admin-theme):not(.staff-theme) .autocomplete-item-text i {
                color: var(--primary, #C62828) !important;
                -webkit-text-fill-color: var(--primary, #C62828) !important;
            }
            body:not(.admin-theme):not(.staff-theme) .autocomplete-item:hover,
            body:not(.admin-theme):not(.staff-theme) .autocomplete-item.active-item {
                background-color: var(--primary-soft, #fee2e2) !important;
                color: var(--primary, #C62828) !important;
            }
            body:not(.admin-theme):not(.staff-theme) .autocomplete-item:hover span,
            body:not(.admin-theme):not(.staff-theme) .autocomplete-item.active-item span,
            body:not(.admin-theme):not(.staff-theme) .autocomplete-item:hover .autocomplete-item-text,
            body:not(.admin-theme):not(.staff-theme) .autocomplete-item.active-item .autocomplete-item-text,
            body:not(.admin-theme):not(.staff-theme) .autocomplete-item:hover i,
            body:not(.admin-theme):not(.staff-theme) .autocomplete-item.active-item i {
                color: var(--primary, #C62828) !important;
                -webkit-text-fill-color: var(--primary, #C62828) !important;
            }
            body:not(.admin-theme):not(.staff-theme) .autocomplete-badge {
                background: var(--primary-soft, #fee2e2) !important;
                color: var(--primary, #C62828) !important;
                -webkit-text-fill-color: var(--primary, #C62828) !important;
                border: 1px solid var(--primary-soft, #fecaca) !important;
            }
        `;
        document.head.appendChild(style);
    }

    function initLocationAutocomplete(root = document) {
        if (!root || typeof root.querySelectorAll !== 'function') root = document;
        // --- 1. Destination Text Inputs ---
        const textInputs = root.querySelectorAll(
            '.autocomplete-location, input#destination, input#add_destination, input#edit_fare_destination'
        );

        textInputs.forEach(input => {
            if (input.dataset.autocompleteInitialized === "true") return;
            input.dataset.autocompleteInitialized = "true";

            let suggestions = [];
            if (input.dataset.suggestions) {
                try {
                    const parsed = JSON.parse(input.dataset.suggestions);
                    if (Array.isArray(parsed) && parsed.length > 0) {
                        suggestions = Array.from(new Set(parsed)).sort();
                    }
                } catch (e) {}
            }

            if (suggestions.length === 0) return;

            // Wrap input in relative container
            let wrapper = input.parentElement;
            if (!wrapper.classList.contains('autocomplete-wrapper')) {
                wrapper = document.createElement('div');
                wrapper.className = 'autocomplete-wrapper position-relative w-100';
                input.parentNode.insertBefore(wrapper, input);
                wrapper.appendChild(input);
            }

            input.style.paddingRight = '32px';

            const clearBtn = document.createElement('button');
            clearBtn.type = 'button';
            clearBtn.className = 'autocomplete-clear-btn';
            clearBtn.title = 'Clear input';
            clearBtn.setAttribute('aria-label', 'Clear input');
            clearBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/></svg>';

            function toggleClearBtn() {
                if (input.value && input.value.trim() !== '') {
                    clearBtn.style.display = 'inline-flex';
                } else {
                    clearBtn.style.display = 'none';
                }
            }

            toggleClearBtn();

            clearBtn.addEventListener('mousedown', (e) => {
                e.preventDefault();
                e.stopPropagation();
            });

            clearBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                input.value = '';
                toggleClearBtn();
                closeDropdown(wrapper, dropdown);
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
                input.focus();
            });

            input.addEventListener('input', toggleClearBtn);
            wrapper.appendChild(clearBtn);

            const dropdown = document.createElement('div');
            dropdown.className = 'autocomplete-dropdown shadow-lg rounded-3 border';
            dropdown.style.cssText = `
                position: absolute;
                top: calc(100% + 6px);
                left: 0;
                right: 0;
                z-index: 9999999 !important;
                max-height: 320px;
                overflow-y: auto;
                display: none;
                background-color: #ffffff;
                box-shadow: 0 16px 40px rgba(0, 0, 0, 0.18) !important;
                border-radius: 14px;
                padding: 6px;
            `;
            wrapper.appendChild(dropdown);

            let activeIndex = -1;
            let isSelecting = false;

            function renderDropdown(query = '') {
                if (isSelecting) {
                    closeDropdown(wrapper, dropdown);
                    return;
                }

                const cleanQuery = query.trim().toUpperCase();
                const filtered = suggestions.filter(loc => loc.toUpperCase().includes(cleanQuery)).slice(0, MAX_DROPDOWN_ITEMS);

                if (filtered.length === 0) {
                    closeDropdown(wrapper, dropdown);
                    return;
                }

                dropdown.innerHTML = filtered.map((loc, idx) => {
                    let displayName = loc;
                    if (cleanQuery && cleanQuery !== loc.toUpperCase()) {
                        const regex = new RegExp(`(${cleanQuery.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
                        displayName = loc.replace(regex, '<mark class="search-highlight">$1</mark>');
                    }
                    return `
                        <div class="autocomplete-item" data-value="${loc}" data-index="${idx}"
                             style="cursor: pointer; font-size: 14px; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between; transition: background 0.15s ease;">
                            <span class="autocomplete-item-text" style="display: inline-flex; align-items: center; gap: 10px;"><i class="bi bi-geo-alt-fill" style="color: #e11d48; font-size: 16px;"></i>${displayName}</span>
                            <span class="badge rounded-pill autocomplete-badge" style="font-size: 11px; padding: 4px 10px; font-weight: 600;">Recorded Destination</span>
                        </div>
                    `;
                }).join('');

                openDropdown(wrapper, dropdown);
                activeIndex = -1;

                dropdown.querySelectorAll('.autocomplete-item').forEach(item => {
                    const handlePick = (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        selectItem(item.dataset.value);
                    };
                    item.addEventListener('mousedown', handlePick);
                    item.addEventListener('click', handlePick);
                    item.addEventListener('mouseenter', () => {
                        dropdown.querySelectorAll('.autocomplete-item').forEach(i => i.classList.remove('active-item'));
                        item.classList.add('active-item');
                    });
                });
            }

            function selectItem(val) {
                isSelecting = true;
                input.value = val;
                toggleClearBtn();
                closeDropdown(wrapper, dropdown);
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
                closeDropdown(wrapper, dropdown);
                input.blur();
                setTimeout(() => {
                    isSelecting = false;
                    closeDropdown(wrapper, dropdown);
                }, INPUT_DEBOUNCE_MS + 100);
            }

            input.addEventListener('focus', () => {
                if (isSelecting) return;
                renderDropdown(input.value);
            });
            input.addEventListener('click', (e) => {
                e.stopPropagation();
                if (isSelecting) return;
                if (dropdown.style.display === 'none' || !wrapper.classList.contains('is-open')) {
                    renderDropdown(input.value);
                }
            });
            input.addEventListener('input', debounce(() => {
                if (isSelecting) return;
                renderDropdown(input.value);
            }, INPUT_DEBOUNCE_MS));
            input.addEventListener('blur', () => {
                setTimeout(() => {
                    if (!isSelecting) {
                        closeDropdown(wrapper, dropdown);
                    }
                }, 150);
            });

            input.addEventListener('keydown', (e) => {
                const items = dropdown.querySelectorAll('.autocomplete-item');
                if (dropdown.style.display === 'none' || items.length === 0) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    activeIndex = (activeIndex + 1) % items.length;
                    updateActive(items);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    activeIndex = (activeIndex - 1 + items.length) % items.length;
                    updateActive(items);
                } else if (e.key === 'Enter') {
                    if (activeIndex >= 0 && items[activeIndex]) {
                        e.preventDefault();
                        selectItem(items[activeIndex].dataset.value);
                    }
                } else if (e.key === 'Escape') {
                    closeDropdown(wrapper, dropdown);
                }
            });

            function updateActive(items) {
                items.forEach((item, idx) => {
                    if (idx === activeIndex) {
                        item.classList.add('active-item');
                        item.scrollIntoView({ block: 'nearest' });
                    } else {
                        item.classList.remove('active-item');
                    }
                });
            }

        });

        // --- 2. Convert all eligible select elements to searchable inputs ---
        const selects = root.querySelectorAll('select:not([data-no-autocomplete])');

        selects.forEach(selectEl => {
            if (selectEl.dataset.autocompleteInitialized === "true") return;
            selectEl.dataset.autocompleteInitialized = "true";

            // Visually hide original select while keeping it validatable by HTML5 form checks
            selectEl.style.cssText = 'position: absolute !important; opacity: 0 !important; pointer-events: none !important; left: 16px !important; top: 0 !important; width: calc(100% - 32px) !important; height: 100% !important; margin: 0 !important; padding: 0 !important; border: 0 !important; z-index: -1 !important;';
            selectEl.tabIndex = -1;

            // Container wrapper
            const wrapper = document.createElement('div');
            wrapper.className = 'autocomplete-wrapper position-relative w-100';
            const parent = selectEl.parentNode;
            parent.insertBefore(wrapper, selectEl);
            wrapper.appendChild(selectEl);

            // Hide any adjacent dropdown chevron icon that was rendered for the raw select
            if (parent) {
                const chevron = parent.querySelector('.dropdown-chevron-icon');
                if (chevron) {
                    chevron.style.setProperty('display', 'none', 'important');
                }
            }

            // If inside an input-group-modern flex container, inherit flex properties
            if (wrapper.parentNode && wrapper.parentNode.classList.contains('input-group-modern')) {
                wrapper.style.flex = '1 1 auto';
                wrapper.style.minWidth = '0';
                wrapper.style.width = '100%';
            }

            const fieldName = (selectEl.name || selectEl.id || '').toLowerCase();

            // Create visible search input field
            const searchInput = document.createElement('input');
            searchInput.type = 'text';
            if (selectEl.id) {
                searchInput.id = selectEl.id + '_autocomplete_search';
            } else if (selectEl.name) {
                searchInput.id = selectEl.name.replace(/[^a-zA-Z0-9_-]/g, '_') + '_autocomplete_search';
            } else {
                searchInput.id = 'autocomplete_search_' + Math.random().toString(36).slice(2, 9);
            }
            let inputClass = (selectEl.className || 'form-control')
                .replace(/\bform-select\b/g, 'form-control')
                .replace(/\bform-select-modern\b/g, 'form-control-modern')
                .replace(/\bselect-modern\b/g, 'input-modern');
            searchInput.className = inputClass;
            searchInput.style.display = 'block';

            let placeholderText = 'Search option...';
            if (fieldName.includes('action_type')) placeholderText = 'Search action type...';
            else if (fieldName.includes('action')) placeholderText = 'Search action...';
            else if (fieldName.includes('user') || fieldName.includes('driver') || fieldName.includes('operator')) placeholderText = 'Search user...';
            else if (fieldName.includes('role')) placeholderText = 'Search role...';
            else if (fieldName.includes('route')) placeholderText = 'Search assigned route...';
            else if (fieldName.includes('terminal')) placeholderText = 'Search terminal...';
            else if (fieldName.includes('vehicle_type') || (fieldName.includes('type') && !fieldName.includes('action'))) placeholderText = 'Search vehicle type...';
            else if (fieldName.includes('status')) placeholderText = 'Search status...';
            else if (fieldName.includes('destination')) placeholderText = 'Search destination...';
            searchInput.placeholder = selectEl.dataset.autocompletePlaceholder || placeholderText;
            if (selectEl.hasAttribute('aria-label')) searchInput.setAttribute('aria-label', selectEl.getAttribute('aria-label'));
            searchInput.autocomplete = 'off';
            searchInput.style.paddingRight = '32px';

            const clearBtn = document.createElement('button');
            clearBtn.type = 'button';
            clearBtn.className = 'autocomplete-clear-btn';
            clearBtn.title = 'Clear selection';
            clearBtn.setAttribute('aria-label', 'Clear selection');
            clearBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/></svg>';

            function toggleClearBtn() {
                if (searchInput.value && searchInput.value.trim() !== '') {
                    clearBtn.style.display = 'inline-flex';
                } else {
                    clearBtn.style.display = 'none';
                }
            }

            clearBtn.addEventListener('mousedown', (e) => {
                e.preventDefault();
                e.stopPropagation();
            });

            clearBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                selectEl.value = '';
                searchInput.value = '';
                searchInput.style.color = '';
                searchInput.style.borderColor = '';
                toggleClearBtn();
                closeDropdown(wrapper, dropdown);
                selectEl.dispatchEvent(new Event('input', { bubbles: true }));
                selectEl.dispatchEvent(new Event('change', { bubbles: true }));
                searchInput.dispatchEvent(new Event('input', { bubbles: true }));
            });

            function isPlaceholderOption(option) {
                if (!option) return true;
                const text = (option.text || '').trim();
                return option.value === '' || /^[\s-]*(select|choose|all)\b.*$/i.test(text);
            }

            function getAvailableOptions() {
                return Array.from(selectEl.options)
                    .filter(opt => !opt.disabled && opt.style.display !== 'none')
                    .map(opt => ({
                        value: opt.value,
                        text: opt.text.trim(),
                        selected: opt.selected,
                        color: opt.getAttribute('data-color') || '',
                        icon: opt.getAttribute('data-icon') || '',
                        isPlaceholder: isPlaceholderOption(opt),
                        isDeparted: opt.getAttribute('data-departed') === 'true' || opt.text.toUpperCase().includes('DEPARTED')
                    }));
            }

            function updateInputValue() {
                const currentSelected = Array.from(selectEl.options).find(o => o.value === selectEl.value);
                if (currentSelected && !isPlaceholderOption(currentSelected)) {
                    searchInput.value = currentSelected.text.trim();
                    // Tint the input with the option color (e.g. vehicle type)
                    // so the field matches badges/cards system-wide.
                    const optColor = currentSelected.getAttribute('data-color');
                    searchInput.style.color = optColor || '';
                    searchInput.style.borderColor = optColor || '';
                } else {
                    searchInput.value = '';
                    searchInput.style.color = '';
                    searchInput.style.borderColor = '';
                }
                toggleClearBtn();
            }

            let isValidationFocus = false;

            updateInputValue();
            selectEl.addEventListener('change', updateInputValue);
            selectEl.addEventListener('invalid', function () {
                // Validation visits every invalid control. Let the browser
                // choose the first one instead of focusing/opening each select.
                closeAllAutocompleteDropdowns();
                searchInput.classList.add('is-invalid');
            });
            selectEl.addEventListener('focus', function () {
                // Keep native validation focus visible without opening a menu.
                isValidationFocus = true;
                searchInput.focus();
                isValidationFocus = false;
            });
            searchInput.addEventListener('input', function () {
                searchInput.classList.remove('is-invalid');
            });
            wrapper.appendChild(searchInput);
            wrapper.appendChild(clearBtn);

            // Floating dropdown panel
            const dropdown = document.createElement('div');
            dropdown.className = 'autocomplete-dropdown shadow-lg rounded-3 border';
            dropdown.style.cssText = `
                position: absolute;
                top: calc(100% + 6px);
                left: 0;
                min-width: max(100%, 260px);
                max-width: min(380px, 92vw);
                z-index: 9999999 !important;
                max-height: 320px;
                overflow-y: auto;
                overflow-x: hidden;
                display: none;
                background-color: #ffffff;
                box-shadow: 0 16px 40px rgba(0, 0, 0, 0.18) !important;
                border-radius: 14px;
                padding: 6px;
            `;
            wrapper.appendChild(dropdown);

            let activeIndex = -1;

            function renderSelectDropdown(query = '') {
                const opts = getAvailableOptions();
                const cleanQuery = query.trim().toUpperCase();
                const currentSelectedTextUpper = (searchInput.value || '').trim().toUpperCase();
                const filtered = opts.filter(opt => opt.text.toUpperCase().includes(cleanQuery)).slice(0, MAX_DROPDOWN_ITEMS);

                if (filtered.length === 0) {
                    closeDropdown(wrapper, dropdown);
                    return;
                }

                let categoryBadge = 'Option';
                let iconClass = 'bi bi-check2-circle';
                let iconColor = '#2563eb';

                if (fieldName.includes('action')) {
                    categoryBadge = 'Action';
                    iconClass = 'bi bi-lightning-charge-fill';
                    iconColor = '#d97706';
                } else if (fieldName.includes('user') || fieldName.includes('driver') || fieldName.includes('operator')) {
                    categoryBadge = 'User';
                    iconClass = 'bi bi-person-fill';
                    iconColor = '#7c3aed';
                } else if (fieldName.includes('role')) {
                    categoryBadge = 'Role';
                    iconClass = 'bi bi-shield-lock-fill';
                    iconColor = '#d97706';
                } else if (fieldName.includes('route')) {
                    categoryBadge = 'Route';
                    iconClass = 'bi bi-signpost-split-fill';
                    iconColor = '#0284c7';
                } else if (fieldName.includes('terminal')) {
                    categoryBadge = 'Terminal';
                    iconClass = 'bi bi-building-fill';
                    iconColor = '#7c3aed';
                } else if (fieldName.includes('vehicle_type') || (fieldName.includes('type') && !fieldName.includes('action'))) {
                    categoryBadge = 'Type';
                    iconClass = 'bi bi-truck-front-fill';
                    iconColor = '#c62828';
                } else if (fieldName.includes('status')) {
                    categoryBadge = 'Status';
                    iconClass = 'bi bi-check-circle-fill';
                    iconColor = '#16a34a';
                } else if (fieldName.includes('destination')) {
                    categoryBadge = 'Destination';
                    iconClass = 'bi bi-geo-alt-fill';
                    iconColor = '#e11d48';
                }

                function resolveVehicleTypeIconMeta(val, txt) {
                    const key = ((val || '') + ' ' + (txt || '')).toLowerCase();
                    if (key.includes('van') || key.includes('shuttle')) return { icon: 'fa-van-shuttle', color: '#c62828' };
                    if (key.includes('jeep')) return { icon: 'fa-truck-front', color: '#1565c0' };
                    if (key.includes('minibus')) return { icon: 'fa-bus', color: '#2e7d32' };
                    if (key.includes('bus') || key.includes('coach')) return { icon: 'fa-bus-simple', color: '#ea580c' };
                    if (key.includes('tri') || key.includes('habal') || key.includes('motor') || key.includes('bike')) return { icon: 'fa-motorcycle', color: '#7c3aed' };
                    if (key.includes('taxi') || key.includes('cab')) return { icon: 'fa-taxi', color: '#ca8a04' };
                    if (key.includes('car') || key.includes('sedan')) return { icon: 'fa-car', color: '#0891b2' };
                    return null;
                }

                dropdown.innerHTML = filtered.map((opt, idx) => {
                    let displayName = opt.text;
                    // Only apply text highlight if actively filtering (not just focusing with the full text selected)
                    if (cleanQuery && cleanQuery !== currentSelectedTextUpper && cleanQuery !== opt.text.toUpperCase()) {
                        const regex = new RegExp(`(${cleanQuery.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
                        displayName = opt.text.replace(regex, '<mark class="search-highlight">$1</mark>');
                    }
                    const departedBadgeHtml = opt.isDeparted ? `<span class="badge bg-secondary bg-opacity-75 text-white ms-2" style="font-size: 10px; padding: 2px 7px; border-radius: 4px;"><i class="bi bi-send-check me-1"></i>DEPARTED</span>` : '';
                    const isAllOption = /^all\b/i.test(opt.text);
                    const itemBadge = isAllOption ? 'All' : categoryBadge;
                    let itemIcon = isAllOption ? 'bi bi-grid-fill' : iconClass;
                    let itemColor = isAllOption ? '#64748b' : (opt.color || iconColor);

                    if (!isAllOption) {
                        if (opt.icon) {
                            if (opt.icon.startsWith('fa-')) {
                                itemIcon = 'fas ' + opt.icon;
                            } else if (opt.icon.startsWith('bi-')) {
                                itemIcon = 'bi ' + opt.icon;
                            } else {
                                itemIcon = opt.icon;
                            }
                        } else if (fieldName.includes('type') || fieldName.includes('vehicle')) {
                            const vtMeta = resolveVehicleTypeIconMeta(opt.value, opt.text);
                            if (vtMeta) {
                                itemIcon = 'fas ' + vtMeta.icon;
                                if (!opt.color) itemColor = vtMeta.color;
                            }
                        }
                    }

                    return `
                        <div class="autocomplete-item" data-value="${opt.value}" data-text="${opt.text}" data-index="${idx}"
                             style="cursor: pointer; font-size: 13.5px; padding: 9px 12px; display: flex; align-items: center; justify-content: space-between; gap: 10px; border-radius: 8px; transition: background 0.15s ease;">
                            <span class="autocomplete-item-text" style="display: inline-flex; align-items: center; gap: 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <i class="${itemIcon}" style="color: ${itemColor}; font-size: 15px; flex-shrink: 0;"></i>
                                <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${displayName}</span>${departedBadgeHtml}
                            </span>
                            <span class="badge rounded-pill autocomplete-badge" style="font-size: 10.5px; padding: 3px 8px; font-weight: 700; flex-shrink: 0;">${itemBadge}</span>
                        </div>
                    `;
                }).join('');

                openDropdown(wrapper, dropdown);
                activeIndex = -1;

                dropdown.querySelectorAll('.autocomplete-item').forEach(item => {
                    item.addEventListener('mousedown', (e) => {
                        e.preventDefault();
                        selectOption(item.dataset.value, item.dataset.text);
                    });
                    item.addEventListener('mouseenter', () => {
                        dropdown.querySelectorAll('.autocomplete-item').forEach(i => i.classList.remove('active-item'));
                        item.classList.add('active-item');
                    });
                });
            }

            function selectOption(val, text) {
                selectEl.value = val;
                const optObj = Array.from(selectEl.options).find(o => o.value === val);
                if (optObj && isPlaceholderOption(optObj)) {
                    searchInput.value = '';
                    searchInput.style.color = '';
                    searchInput.style.borderColor = '';
                } else {
                    searchInput.value = text;
                    const optColor = optObj ? optObj.getAttribute('data-color') : '';
                    searchInput.style.color = optColor || '';
                    searchInput.style.borderColor = optColor || '';
                }
                toggleClearBtn();
                closeDropdown(wrapper, dropdown);
                selectEl.dispatchEvent(new Event('input', { bubbles: true }));
                selectEl.dispatchEvent(new Event('change', { bubbles: true }));
                searchInput.blur();
            }

            searchInput.addEventListener('focus', () => {
                if (isValidationFocus) return;
                // No select-all: the theme selection color looks like an
                // error block on filled inputs. Just open the dropdown.
                renderSelectDropdown('');
            });

            searchInput.addEventListener('click', (e) => {
                e.stopPropagation();
                if (dropdown.style.display === 'none' || !wrapper.classList.contains('is-open')) {
                    renderSelectDropdown('');
                }
            });

            searchInput.addEventListener('input', () => {
                toggleClearBtn();
            });

            searchInput.addEventListener('input', debounce(() => {
                if (document.activeElement !== searchInput) return;
                renderSelectDropdown(searchInput.value);
            }, INPUT_DEBOUNCE_MS));

            searchInput.addEventListener('blur', () => {
                if (selectEl.hasAttribute('data-queue-route-search')) {
                    // Inline mobile menus change a centered dialog's height. Let
                    // the clicked close/action button receive mouseup before it moves.
                    setTimeout(() => {
                        if (document.activeElement !== searchInput) closeDropdown(wrapper, dropdown);
                    }, 150);
                } else closeDropdown(wrapper, dropdown);
            });

            selectEl.addEventListener('change', () => {
                updateInputValue();
            });

            selectEl.addEventListener('input', () => {
                updateInputValue();
            });

            selectEl.syncAutocompleteValue = updateInputValue;
            wrapper.syncAutocompleteValue = updateInputValue;

            searchInput.addEventListener('keydown', (e) => {
                if ((e.key === 'ArrowDown' || e.key === 'ArrowUp') && dropdown.style.display === 'none') {
                    renderSelectDropdown('');
                }
                const items = dropdown.querySelectorAll('.autocomplete-item');
                if (dropdown.style.display === 'none' || items.length === 0) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    activeIndex = (activeIndex + 1) % items.length;
                    updateActive(items);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    activeIndex = (activeIndex - 1 + items.length) % items.length;
                    updateActive(items);
                } else if (e.key === 'Enter') {
                    if (activeIndex >= 0 && items[activeIndex]) {
                        e.preventDefault();
                        selectOption(items[activeIndex].dataset.value, items[activeIndex].dataset.text);
                    }
                } else if (e.key === 'Escape') {
                    closeDropdown(wrapper, dropdown);
                }
            });

            function updateActive(items) {
                items.forEach((item, idx) => {
                    if (idx === activeIndex) {
                        item.classList.add('active-item');
                        item.scrollIntoView({ block: 'nearest' });
                    } else {
                        item.classList.remove('active-item');
                    }
                });
            }

        });

        // --- 3. Ensure any standalone search/filter input has a working clear button ---
        const searchInputs = root.querySelectorAll('input[type="search"], input[id*="search"], input[id*="Search"], input[name="q"], input.search-input, input.search-input-modern');
        searchInputs.forEach(inputEl => {
            if (inputEl.closest('.autocomplete-wrapper')) return;
            const parent = inputEl.parentElement;
            if (!parent) return;
            const existingBtn = parent.querySelector('.btn-clear-search, .guest-clear-search-btn, .fare-search-clear, .autocomplete-clear-btn');
            if (existingBtn) return;

            const computedPos = window.getComputedStyle(parent).position;
            if (computedPos === 'static') {
                parent.style.position = 'relative';
            }

            inputEl.style.paddingRight = '32px';

            const clearBtn = document.createElement('button');
            clearBtn.type = 'button';
            clearBtn.className = 'autocomplete-clear-btn';
            clearBtn.title = 'Clear search';
            clearBtn.setAttribute('aria-label', 'Clear search');
            clearBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/></svg>';

            function updateBtn() {
                clearBtn.style.display = (inputEl.value && inputEl.value.trim() !== '') ? 'inline-flex' : 'none';
            }
            updateBtn();

            clearBtn.addEventListener('mousedown', (e) => {
                e.preventDefault();
                e.stopPropagation();
            });

            clearBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                inputEl.value = '';
                updateBtn();
                inputEl.dispatchEvent(new Event('input', { bubbles: true }));
                inputEl.dispatchEvent(new Event('change', { bubbles: true }));
                inputEl.focus();
            });

            inputEl.addEventListener('input', updateBtn);
            inputEl.addEventListener('change', updateBtn);
            parent.appendChild(clearBtn);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { initLocationAutocomplete(); });
    } else {
        initLocationAutocomplete();
    }

    document.addEventListener('shown.bs.modal', function (event) {
        initLocationAutocomplete(event.target);
    });

    window.initLocationAutocomplete = initLocationAutocomplete;
})();
