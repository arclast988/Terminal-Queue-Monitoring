/**
 * Palompon Transit Management System - Searchable Location, Terminal, Route, Role & Select Autocomplete
 * Converts select fields across the application into search-bar style inputs without dropdown arrows.
 */

(function () {
    'use strict';

    // Inject base dropdown CSS styles to ensure complete appearance on all pages
    if (!document.getElementById('autocomplete-search-base-styles')) {
        const style = document.createElement('style');
        style.id = 'autocomplete-search-base-styles';
        style.textContent = `
            .autocomplete-wrapper {
                position: relative !important;
                z-index: 1050 !important;
                overflow: visible !important;
            }
            .autocomplete-wrapper:focus-within,
            .autocomplete-wrapper:hover,
            .autocomplete-wrapper:active {
                z-index: 9999 !important;
                overflow: visible !important;
            }
            .autocomplete-dropdown {
                background: #ffffff !important;
                background-color: #ffffff !important;
                border: 1px solid #e2e8f0 !important;
                box-shadow: 0 16px 40px rgba(0, 0, 0, 0.15) !important;
                border-radius: 14px !important;
                overflow-y: auto !important;
                z-index: 99999 !important;
                padding: 6px !important;
            }
            .autocomplete-item {
                color: #1e293b !important;
                background: #ffffff !important;
                border-radius: 8px !important;
                margin-bottom: 2px !important;
            }
            .autocomplete-item:last-child {
                margin-bottom: 0 !important;
            }
            .autocomplete-item:hover,
            .autocomplete-item.active-item {
                background-color: #f1f5f9 !important;
                color: #1e40af !important;
            }
            .autocomplete-item mark.search-highlight {
                background: rgba(30, 64, 175, 0.12) !important;
                color: #1e40af !important;
                border-radius: 3px !important;
                padding: 0 3px !important;
                font-weight: 700 !important;
            }
            @media (prefers-color-scheme: dark) {
                .autocomplete-dropdown {
                    background: #1e293b !important;
                    background-color: #1e293b !important;
                    border-color: #334155 !important;
                    box-shadow: 0 14px 35px rgba(0, 0, 0, 0.6) !important;
                }
                .autocomplete-item {
                    color: #f8fafc !important;
                    background: #1e293b !important;
                    border-bottom-color: #334155 !important;
                }
                .autocomplete-item:hover,
                .autocomplete-item.active-item {
                    background-color: #334155 !important;
                    color: #60a5fa !important;
                }
                .autocomplete-item mark.search-highlight {
                    background: rgba(251, 191, 36, 0.25) !important;
                    color: #fbbf24 !important;
                }
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
                z-index: 99999 !important;
                max-height: 230px;
                overflow-y: auto;
                display: none;
                background-color: #ffffff;
                box-shadow: 0 16px 40px rgba(0, 0, 0, 0.15) !important;
                border-radius: 14px;
                padding: 6px;
            `;
            wrapper.appendChild(dropdown);

            let activeIndex = -1;

            function renderDropdown(query = '') {
                const cleanQuery = query.trim().toUpperCase();
                const filtered = suggestions.filter(loc => loc.toUpperCase().includes(cleanQuery));

                if (filtered.length === 0) {
                    dropdown.style.display = 'none';
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
                            <span style="display: inline-flex; align-items: center; gap: 10px;"><i class="bi bi-geo-alt-fill text-primary opacity-75"></i>${displayName}</span>
                            <span class="badge rounded-pill" style="font-size: 11px; padding: 4px 10px; background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; font-weight: 600;">Recorded Destination</span>
                        </div>
                    `;
                }).join('');

                dropdown.style.display = 'block';
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
                dropdown.style.display = 'none';
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
                    dropdown.style.display = 'none';
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
                    dropdown.style.display = 'none';
                }
            });
        });

        // --- 2. Convert ALL Select Elements to Search-Bar Inputs (No Dropdown Arrow) ---
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

            function getAvailableOptions() {
                return Array.from(selectEl.options)
                    .filter(opt => !opt.disabled && opt.style.display !== 'none')
                    .map(opt => ({
                        value: opt.value,
                        text: opt.text.trim(),
                        selected: opt.selected
                    }));
            }

            function updateInputValue() {
                const currentSelected = Array.from(selectEl.options).find(o => o.value === selectEl.value);
                if (currentSelected && currentSelected.text.trim() !== '') {
                    searchInput.value = currentSelected.text.trim();
                } else {
                    const firstOpt = selectEl.options[selectEl.selectedIndex];
                    searchInput.value = firstOpt ? firstOpt.text.trim() : '';
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
                z-index: 99999 !important;
                max-height: 230px;
                overflow-y: auto;
                display: none;
                background-color: #ffffff;
                box-shadow: 0 16px 40px rgba(0, 0, 0, 0.15) !important;
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
                    dropdown.style.display = 'none';
                    return;
                }

                let categoryBadge = 'Option';
                let iconClass = 'bi bi-geo-alt';

                if (fieldName.includes('role')) {
                    categoryBadge = 'Role';
                    iconClass = 'bi bi-shield-lock';
                } else if (fieldName.includes('route')) {
                    categoryBadge = 'Route';
                    iconClass = 'bi bi-signpost-split';
                } else if (fieldName.includes('terminal')) {
                    categoryBadge = 'Terminal';
                    iconClass = 'bi bi-building';
                } else if (fieldName.includes('type')) {
                    categoryBadge = 'Type';
                    iconClass = 'bi bi-truck-front';
                } else if (fieldName.includes('status')) {
                    categoryBadge = 'Status';
                    iconClass = 'bi bi-check-circle';
                } else if (fieldName.includes('destination')) {
                    categoryBadge = 'Destination';
                    iconClass = 'bi bi-geo-alt';
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
                            <span style="display: inline-flex; align-items: center; gap: 10px;"><i class="${iconClass} text-primary opacity-75"></i>${displayName}</span>
                            <span class="badge rounded-pill" style="font-size: 11px; padding: 4px 10px; background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; font-weight: 600;">${categoryBadge}</span>
                        </div>
                    `;
                }).join('');

                dropdown.style.display = 'block';
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
                dropdown.style.display = 'none';
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
                    dropdown.style.display = 'none';
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
                    dropdown.style.display = 'none';
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
