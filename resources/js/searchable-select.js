/**
 * Shared Searchable Select Component
 * Robust implementation for custom dropdown selects across the site.
 * Use window.createSearchableSelect(config) - available globally after app.js loads.
 */

export function createSearchableSelect(config) {
    const {
        triggerId,
        hiddenInputId,
        dropdownId,
        searchInputId,
        optionsContainerId,
        displayTextId,
        options,
        displayFn,
        onSelect,
    } = config;

    const trigger = document.getElementById(triggerId);
    const hiddenInput = document.getElementById(hiddenInputId);
    const dropdown = document.getElementById(dropdownId);
    const searchInput = document.getElementById(searchInputId);
    const optionsContainer = document.getElementById(optionsContainerId);
    const displayText = document.getElementById(displayTextId);

    if (!trigger || !hiddenInput || !dropdown || !searchInput || !optionsContainer || !displayText) {
        return;
    }

    let filteredOptions = [...options];

    function renderOptions(opts) {
        if (opts.length === 0) {
            optionsContainer.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No options found</div>';
            return;
        }

        optionsContainer.innerHTML = opts.map(opt => {
            const display = displayFn ? displayFn(opt) : (opt.name ?? opt.id ?? opt);
            const value = opt.id !== undefined ? opt.id : (opt.value !== undefined ? opt.value : opt);
            return `
                <div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 select-option" 
                     data-id="${value}" 
                     data-name="${display}">
                    <div class="text-sm text-foreground">${display}</div>
                </div>
            `;
        }).join('');

        optionsContainer.querySelectorAll('.select-option').forEach(option => {
            option.addEventListener('click', function (e) {
                e.stopPropagation();
                const id = this.dataset.id;
                const name = this.dataset.name;
                const selectedOption = options.find(o => {
                    const optValue = o.id !== undefined ? o.id : (o.value !== undefined ? o.value : o);
                    return String(optValue) === String(id);
                });

                if (selectedOption || id === '' || id === undefined) {
                    hiddenInput.value = id ?? '';
                    displayText.textContent = name ?? '';
                    closeDropdown();
                    searchInput.value = '';
                    filteredOptions = [...options];
                    renderOptions(filteredOptions);
                    if (onSelect) onSelect(selectedOption ?? { id: id, name: name });
                }
            });
        });
    }

    function openDropdown() {
        dropdown.classList.remove('hidden');
        const rect = trigger.getBoundingClientRect();
        dropdown.style.cssText = `position:fixed;z-index:99999;top:${rect.bottom + 4}px;left:${rect.left}px;width:${rect.width}px;min-width:${rect.width}px;`;
        setTimeout(() => searchInput.focus(), 100);
    }

    function closeDropdown() {
        dropdown.classList.add('hidden');
        dropdown.style.cssText = '';
    }

    function isOpen() {
        return !dropdown.classList.contains('hidden');
    }

    // Initial render
    renderOptions(filteredOptions);

    // Search
    searchInput.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();
        filteredOptions = options.filter(opt => {
            const display = displayFn ? displayFn(opt) : (opt.name ?? opt.id ?? opt);
            return String(display).toLowerCase().includes(searchTerm);
        });
        renderOptions(filteredOptions);
    });

    // Toggle on trigger click
    trigger.addEventListener('click', function (e) {
        e.stopPropagation();
        if (isOpen()) {
            closeDropdown();
        } else {
            openDropdown();
        }
    });

    // Outside click: defer check to avoid race with trigger click
    document.addEventListener('click', function (e) {
        setTimeout(() => {
            if (!trigger.contains(e.target) && !dropdown.contains(e.target)) {
                closeDropdown();
            }
        }, 0);
    });
}
