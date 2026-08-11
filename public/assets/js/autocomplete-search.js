/**
 * Palompon Transit Management System - Searchable Location, Terminal, Route, Role & Select Autocomplete
 * Converts select fields across the application into search-bar style inputs without dropdown arrows.
 */

(function () {
    'use strict';

    // Helper functions to manage active z-index stacking context for open dropdowns
    function openDropdown(wrapper, dropdown) {
        if (!wrapper || !dropdown) return;
        wrapper.classList.add('is-open');
        wrapper.style.zIndex = '999999';
        dropdown.style.display = 'block';

        const parentContainer = wrapper.closest('.col, .col-12, .col-sm-6, .col-md-6, .col-lg-4, .col-xl-2, .col-xl-3, .mb-3, .row, .form-group, fieldset, .modern-card-body');
        if (parentContainer) {
            parentContainer.classList.add('autocomplete-parent-active');
            parentContainer.style.position = 'relative';
            parentContainer.style.zIndex = '99999';
        }
    }

    function closeDropdown(wrapper, dropdown) {
        if (!wrapper || !dropdown) return;
        wrapper.classList.remove('is-open');
        wrapper.style.zIndex = '';
        dropdown.style.display = 'none';

        const parentContainer = wrapper.closest('.col, .col-12, .col-sm-6, .col-md-6, .col-lg-4, .col-xl-2, .col-xl-3, .mb-3, .row, .form-group, fieldset, .modern-card-body');
        if (parentContainer) {
            parentContainer.classList.remove('autocomplete-parent-active');
            parentContainer.style.zIndex = '';
        }
    }

    // Inject base dropdown CSS styles to ensure complete appearance on all pages
    if (!document.getElementById('autocomplete-search-base-styles')) {
        const style = document.createElement('style');
        style.id = 'autocomplete-search-base-styles';
        style.textContent = `
            .autocomplete-wrapper {
                position: relative !important;
                z-index: 10 !important;
                overflow: visible !important;
            }
            .autocomplete-wrapper.is-open {
                z-index: 999999 !important;
            }
            .autocomplete-parent-active {
                z-index: 99999 !important;
                position: relative !important;
            }
            .autocomplete-dropdown {
                border-radius: 14px;
                overflow-y: auto;
                z-index: 9999999 !important;
                padding: 6px;
            }
            .autocomplete-item {
                border-radius: 8px;
                margin-bottom: 2px;
            }
            .autocomplete-item span {
                color: inherit;
            }
            .autocomplete-item:last-child {
                margin-bottom: 0;
            }

            /* Dark Mode Rules for Autocomplete Dropdown & Items */
            body.dark-mode .autocomplete-dropdown,
            body.admin-theme .autocomplete-dropdown,
            body.staff-theme .autocomplete-dropdown {
                background: #0f172a !important;
                background-color: #0f172a !important;
                border: 1px solid #334155 !important;
                box-shadow: 0 14px 35px rgba(0, 0, 0, 0.6) !important;
            }
            body.dark-mode .autocomplete-item,
            body.admin-theme .autocomplete-item,
            body.staff-theme .autocomplete-item {
                color: #f8fafc !important;
                background: #0f172a !important;
                background-color: #0f172a !important;
                border-bottom: 1px solid #1e293b !important;
            }
            body.dark-mode .autocomplete-item span,
            body.admin-theme .autocomplete-item span,
            body.staff-theme .autocomplete-item span,
            body.dark-mode .autocomplete-item .autocomplete-item-text,
            body.admin-theme .autocomplete-item .autocomplete-item-text,
            body.staff-theme .autocomplete-item .autocomplete-item-text {
                color: #f8fafc !important;
                -webkit-text-fill-color: #f8fafc !important;
            }
            body.dark-mode .autocomplete-item:hover,
            body.dark-mode .autocomplete-item.active-item,
            body.dark-mode .autocomplete-item.selected,
            body.admin-theme .autocomplete-item:hover,
            body.admin-theme .autocomplete-item.active-item,
            body.admin-theme .autocomplete-item.selected,
            body.staff-theme .autocomplete-item:hover,
            body.staff-theme .autocomplete-item.active-item,
            body.staff-theme .autocomplete-item.selected {
                background: #1e293b !important;
                background-color: #1e293b !important;
                color: #38bdf8 !important;
            }
            body.dark-mode .autocomplete-item:hover span,
            body.dark-mode .autocomplete-item.active-item span,
            body.dark-mode .autocomplete-item:hover .autocomplete-item-text,
            body.dark-mode .autocomplete-item.active-item .autocomplete-item-text,
            body.dark-mode .autocomplete-item:hover i,
            body.dark-mode .autocomplete-item.active-item i,
            body.admin-theme .autocomplete-item:hover span,
            body.admin-theme .autocomplete-item.active-item span,
            body.admin-theme .autocomplete-item:hover .autocomplete-item-text,
            body.admin-theme .autocomplete-item.active-item .autocomplete-item-text,
            body.admin-theme .autocomplete-item:hover i,
            body.admin-theme .autocomplete-item.active-item i,
            body.staff-theme .autocomplete-item:hover span,
            body.staff-theme .autocomplete-item.active-item span,
            body.staff-theme .autocomplete-item:hover .autocomplete-item-text,
            body.staff-theme .autocomplete-item.active-item .autocomplete-item-text,
            body.staff-theme .autocomplete-item:hover i,
            body.staff-theme .autocomplete-item.active-item i {
                color: #38bdf8 !important;
                -webkit-text-fill-color: #38bdf8 !important;
            }
            body.dark-mode .autocomplete-item mark.search-highlight,
            body.admin-theme .autocomplete-item mark.search-highlight,
            body.staff-theme .autocomplete-item mark.search-highlight {
                background: rgba(251, 191, 36, 0.3) !important;
                color: #fbbf24 !important;
            }
            body.dark-mode .autocomplete-badge,
            body.admin-theme .autocomplete-badge,
            body.staff-theme .autocomplete-badge {
                background: #334155 !important;
                color: #cbd5e1 !important;
                -webkit-text-fill-color: #cbd5e1 !important;
                border: 1px solid #475569 !important;
            }

            /* Explicit Light Theme Styling — ONLY when on Guest theme without dark mode */
            body:not(.admin-theme):not(.staff-theme):not(.dark-mode) .autocomplete-dropdown {
                background: #ffffff !important;
                background-color: #ffffff !important;
                border: 1px solid #cbd5e1 !important;
                box-shadow: 0 16px 40px rgba(0, 0, 0, 0.18) !important;
            }
            body:not(.admin-theme):not(.staff-theme):not(.dark-mode) .autocomplete-item {
                color: #1e293b !important;
                background: #ffffff !important;
                border-bottom: 1px solid #f1f5f9 !important;
            }
            body:not(.admin-theme):not(.staff-theme):not(.dark-mode) .autocomplete-item span,
            body:not(.admin-theme):not(.staff-theme):not(.dark-mode) .autocomplete-item .autocomplete-item-text {
                color: #1e293b !important;
                -webkit-text-fill-color: #1e293b !important;
            }

            /* --- ADMIN THEME (RED) — light mode only --- */
            body.admin-theme:not(.dark-mode) .autocomplete-item i,
            body.admin-theme:not(.dark-mode) .autocomplete-item .autocomplete-item-text i {
                color: #c62828 !important;
                -webkit-text-fill-color: #c62828 !important;
            }
            body.admin-theme:not(.dark-mode) .autocomplete-item:hover,
            body.admin-theme:not(.dark-mode) .autocomplete-item.active-item {
                background-color: #fdecea !important;
                color: #c62828 !important;
            }
            body.admin-theme:not(.dark-mode) .autocomplete-item:hover span,
            body.admin-theme:not(.dark-mode) .autocomplete-item.active-item span,
            body.admin-theme:not(.dark-mode) .autocomplete-item:hover .autocomplete-item-text,
            body.admin-theme:not(.dark-mode) .autocomplete-item.active-item .autocomplete-item-text,
            body.admin-theme:not(.dark-mode) .autocomplete-item:hover i,
            body.admin-theme:not(.dark-mode) .autocomplete-item.active-item i {
                color: #c62828 !important;
                -webkit-text-fill-color: #c62828 !important;
            }
            body.admin-theme:not(.dark-mode) .autocomplete-badge {
                background: #fdecea !important;
                color: #b71c1c !important;
                -webkit-text-fill-color: #b71c1c !important;
                border: 1px solid #fca5a5 !important;
            }

            /* --- STAFF THEME (BLUE) — light mode only --- */
            body.staff-theme:not(.dark-mode) .autocomplete-item i,
            body.staff-theme:not(.dark-mode) .autocomplete-item .autocomplete-item-text i {
                color: #1565c0 !important;
                -webkit-text-fill-color: #1565c0 !important;
            }
            body.staff-theme:not(.dark-mode) .autocomplete-item:hover,
            body.staff-theme:not(.dark-mode) .autocomplete-item.active-item {
                background-color: #e8effd !important;
                color: #1565c0 !important;
            }
            body.staff-theme:not(.dark-mode) .autocomplete-item:hover span,
            body.staff-theme:not(.dark-mode) .autocomplete-item.active-item span,
            body.staff-theme:not(.dark-mode) .autocomplete-item:hover .autocomplete-item-text,
            body.staff-theme:not(.dark-mode) .autocomplete-item.active-item .autocomplete-item-text,
            body.staff-theme:not(.dark-mode) .autocomplete-item:hover i,
            body.staff-theme:not(.dark-mode) .autocomplete-item.active-item i {
                color: #1565c0 !important;
                -webkit-text-fill-color: #1565c0 !important;
            }
            body.staff-theme:not(.dark-mode) .autocomplete-badge {
                background: #e8effd !important;
                color: #1565c0 !important;
                -webkit-text-fill-color: #1565c0 !important;
                border: 1px solid #93c5fd !important;
            }

            /* --- GUEST / DEFAULT THEME — light mode only --- */
            body:not(.admin-theme):not(.staff-theme):not(.dark-mode) .autocomplete-item i,
            body:not(.admin-theme):not(.staff-theme):not(.dark-mode) .autocomplete-item-text i {
                color: #ea580c !important;
                -webkit-text-fill-color: #ea580c !important;
            }
            body:not(.admin-theme):not(.staff-theme):not(.dark-mode) .autocomplete-item:hover,
            body:not(.admin-theme):not(.staff-theme):not(.dark-mode) .autocomplete-item.active-item {
                background-color: #fff7ed !important;
                color: #ea580c !important;
            }
            body:not(.admin-theme):not(.staff-theme):not(.dark-mode) .autocomplete-item:hover span,
            body:not(.admin-theme):not(.staff-theme):not(.dark-mode) .autocomplete-item.active-item span,
            body:not(.admin-theme):not(.staff-theme):not(.dark-mode) .autocomplete-item:hover .autocomplete-item-text,
            body:not(.admin-theme):not(.staff-theme):not(.dark-mode) .autocomplete-item.active-item .autocomplete-item-text,
            body:not(.admin-theme):not(.staff-theme):not(.dark-mode) .autocomplete-item:hover i,
            body:not(.admin-theme):not(.staff-theme):not(.dark-mode) .autocomplete-item.active-item i {
                color: #ea580c !important;
                -webkit-text-fill-color: #ea580c !important;
            }
            body:not(.admin-theme):not(.staff-theme):not(.dark-mode) .autocomplete-badge {
                background: #fff7ed !important;
                color: #c2410c !important;
                -webkit-text-fill-color: #c2410c !important;
                border: 1px solid #ffedd5 !important;
            }
        `;
        document.head.appendChild(style);
    }

    function initLocationAutocomplete() {
        // --- 1. Destination Text Inputs ---
        const textInputs = document.querySelectorAll(
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

            const dropdown = document.createElement('div');
            dropdown.className = 'autocomplete-dropdown shadow-lg rounded-3 border';
            dropdown.style.cssText = `
                position: absolute;
                top: calc(100% + 6px);
                left: 0;
                right: 0;
                z-index: 9999999 !important;
                max-height: 230px;
                overflow-y: auto;
                display: none;
                background-color: #ffffff;
                box-shadow: 0 16px 40px rgba(0, 0, 0, 0.18) !important;
                border-radius: 14px;
                padding: 6px;
            `;
            wrapper.appendChild(dropdown);

            let activeIndex = -1;

            function renderDropdown(query = '') {
                const cleanQuery = query.trim().toUpperCase();
                const filtered = suggestions.filter(loc => loc.toUpperCase().includes(cleanQuery));

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
                    item.addEventListener('mousedown', (e) => {
                        e.preventDefault();
                        selectItem(item.dataset.value);
                    });
                    item.addEventListener('mouseenter', () => {
                        dropdown.querySelectorAll('.autocomplete-item').forEach(i => i.classList.remove('active-item'));
                        item.classList.add('active-item');
                    });
                });
            }

            function selectItem(val) {
                input.value = val;
                closeDropdown(wrapper, dropdown);
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }

            input.addEventListener('focus', () => renderDropdown(input.value));
            input.addEventListener('input', () => renderDropdown(input.value));

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

            document.addEventListener('click', (e) => {
                if (!wrapper.contains(e.target)) {
                    closeDropdown(wrapper, dropdown);
                }
            });
        });

        // --- 2. Convert all eligible select elements to searchable inputs ---
        const selects = document.querySelectorAll('select:not([data-no-autocomplete])');

        selects.forEach(selectEl => {
            if (selectEl.dataset.autocompleteInitialized === "true") return;
            selectEl.dataset.autocompleteInitialized = "true";

            // Hide original select element
            selectEl.style.display = 'none';

            // Container wrapper
            const wrapper = document.createElement('div');
            wrapper.className = 'autocomplete-wrapper position-relative w-100';
            selectEl.parentNode.insertBefore(wrapper, selectEl);
            wrapper.appendChild(selectEl);

            const fieldName = (selectEl.name || selectEl.id || '').toLowerCase();

            // Create visible search input field
            const searchInput = document.createElement('input');
            searchInput.type = 'text';
            let inputClass = (selectEl.className || 'form-control')
                .replace(/\bform-select\b/g, 'form-control')
                .replace(/\bform-select-modern\b/g, 'form-control-modern')
                .replace(/\bselect-modern\b/g, 'input-modern');
            searchInput.className = inputClass;
            searchInput.style.display = 'block';

            let placeholderText = 'Search option...';
            if (fieldName.includes('role')) placeholderText = 'Search role...';
            else if (fieldName.includes('route')) placeholderText = 'Search assigned route...';
            else if (fieldName.includes('terminal')) placeholderText = 'Search terminal...';
            else if (fieldName.includes('type')) placeholderText = 'Search vehicle type...';
            else if (fieldName.includes('status')) placeholderText = 'Search status...';
            else if (fieldName.includes('destination')) placeholderText = 'Search destination...';
            searchInput.placeholder = placeholderText;
            searchInput.autocomplete = 'off';

            function isPlaceholderOption(option) {
                const text = option.text.trim();
                return option.value === '' || /^[\s-]*(select|choose)\b.*$/i.test(text);
            }

            function getAvailableOptions() {
                return Array.from(selectEl.options)
                    .filter(opt => !opt.disabled && opt.style.display !== 'none' && !isPlaceholderOption(opt))
                    .map(opt => ({
                        value: opt.value,
                        text: opt.text.trim(),
                        selected: opt.selected
                    }));
            }

            function updateInputValue() {
                const currentSelected = Array.from(selectEl.options).find(o => o.value === selectEl.value);
                if (currentSelected && !isPlaceholderOption(currentSelected)) {
                    searchInput.value = currentSelected.text.trim();
                } else {
                    searchInput.value = '';
                }
            }

            updateInputValue();
            wrapper.appendChild(searchInput);

            // Floating dropdown panel
            const dropdown = document.createElement('div');
            dropdown.className = 'autocomplete-dropdown shadow-lg rounded-3 border';
            dropdown.style.cssText = `
                position: absolute;
                top: calc(100% + 6px);
                left: 0;
                right: 0;
                z-index: 9999999 !important;
                max-height: 230px;
                overflow-y: auto;
                display: none;
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
                const filtered = opts.filter(opt => opt.text.toUpperCase().includes(cleanQuery));

                if (filtered.length === 0) {
                    closeDropdown(wrapper, dropdown);
                    return;
                }

                let categoryBadge = 'Option';
                let iconClass = 'bi bi-geo-alt-fill';
                let iconColor = '#2563eb';

                if (fieldName.includes('role')) {
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
                } else if (fieldName.includes('type')) {
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

                dropdown.innerHTML = filtered.map((opt, idx) => {
                    let displayName = opt.text;
                    // Only apply text highlight if actively filtering (not just focusing with the full text selected)
                    if (cleanQuery && cleanQuery !== currentSelectedTextUpper && cleanQuery !== opt.text.toUpperCase()) {
                        const regex = new RegExp(`(${cleanQuery.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
                        displayName = opt.text.replace(regex, '<mark class="search-highlight">$1</mark>');
                    }
                    return `
                        <div class="autocomplete-item" data-value="${opt.value}" data-text="${opt.text}" data-index="${idx}"
                             style="cursor: pointer; font-size: 14px; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between; transition: background 0.15s ease;">
                            <span class="autocomplete-item-text" style="display: inline-flex; align-items: center; gap: 10px;"><i class="${iconClass}" style="color: ${iconColor}; font-size: 16px;"></i>${displayName}</span>
                            <span class="badge rounded-pill autocomplete-badge" style="font-size: 11px; padding: 4px 10px; font-weight: 600;">${categoryBadge}</span>
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
                searchInput.value = text;
                closeDropdown(wrapper, dropdown);
                selectEl.dispatchEvent(new Event('input', { bubbles: true }));
                selectEl.dispatchEvent(new Event('change', { bubbles: true }));
            }

            searchInput.addEventListener('focus', () => {
                searchInput.select();
                renderSelectDropdown('');
            });

            searchInput.addEventListener('input', () => {
                renderSelectDropdown(searchInput.value);
            });

            selectEl.addEventListener('change', () => {
                updateInputValue();
            });

            searchInput.addEventListener('keydown', (e) => {
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

            document.addEventListener('click', (e) => {
                if (!wrapper.contains(e.target)) {
                    closeDropdown(wrapper, dropdown);
                    updateInputValue();
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLocationAutocomplete);
    } else {
        initLocationAutocomplete();
    }

    document.addEventListener('shown.bs.modal', function () {
        initLocationAutocomplete();
    });

    window.initLocationAutocomplete = initLocationAutocomplete;
})();
