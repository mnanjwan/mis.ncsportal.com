@extends('layouts.app')

@section('title', 'Create Quarter')
@section('page-title', 'Create Quarter')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Create New Quarter</h3>
        </div>
        <div class="kt-card-content">
            <form id="create-quarter-form" class="flex flex-col gap-5">
                <!-- Command Selection (Readonly - Building Unit's Command) -->
                <div class="flex flex-col gap-2">
                    <label class="kt-form-label">Command <span class="text-danger">*</span></label>
                    @if($commandId)
                        <input type="text" 
                               id="command-display" 
                               class="kt-input bg-muted/50 cursor-not-allowed" 
                               value="{{ $commandName ?? 'N/A' }}"
                               readonly
                               disabled>
                        <input type="hidden" 
                               id="command-id" 
                               name="command_id" 
                               value="{{ $commandId }}">
                        <span class="text-xs text-secondary-foreground">Command is automatically set based on your Building Unit assignment</span>
                    @else
                        <div class="kt-alert kt-alert-warning">
                            <i class="ki-filled ki-information"></i>
                            <div>
                                <strong>No Command Assigned:</strong> You must be assigned to a command to create quarters. Please contact HRD.
                            </div>
                        </div>
                        <input type="hidden" id="command-id" name="command_id" value="">
                    @endif
                </div>

                <!-- Quarter Number -->
                <div class="flex flex-col gap-2">
                    <label class="kt-form-label">Quarter Number <span class="text-danger">*</span></label>
                    <input type="text" id="quarter-number" name="quarter_number" 
                        class="kt-input" 
                        placeholder="e.g., Q001, Block A-101"
                        maxlength="50"
                        required />
                    <span class="text-xs text-secondary-foreground">Enter a unique quarter number or identifier</span>
                </div>

                <!-- Quarter Type -->
                <div class="flex flex-col gap-2">
                    <label class="kt-form-label">Quarter Type <span class="text-danger">*</span></label>
                    <div class="relative">
                        <input type="hidden" name="quarter_type" id="quarter-type" value="{{ old('quarter_type') ?? '' }}" required>
                        <button type="button" 
                                id="quarter_type_select_trigger" 
                                class="kt-select w-full text-left flex items-center justify-between cursor-pointer">
                            <span id="quarter_type_select_text">{{ old('quarter_type') ? old('quarter_type') : 'Select type' }}</span>
                            <i class="ki-filled ki-down text-gray-400"></i>
                        </button>
                        <div id="quarter_type_dropdown" 
                             class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                            <div class="p-3 border-b border-input">
                                <input type="text" 
                                       id="quarter_type_search_input" 
                                       class="kt-input w-full pl-10" 
                                       placeholder="Search quarter type..."
                                       autocomplete="off">
                            </div>
                            <div id="quarter_type_options" class="max-h-60 overflow-y-auto"></div>
                        </div>
                    </div>
                </div>

                <!-- Custom Type Input (if Other selected) -->
                <div class="flex flex-col gap-2" id="custom-type-container" style="display: none;">
                    <label class="kt-form-label">Specify Type <span class="text-danger">*</span></label>
                    <input type="text" id="custom-quarter-type" name="custom_quarter_type" 
                        class="kt-input" 
                        placeholder="Enter quarter type" />
                </div>

                <!-- Submit Button -->
                <div class="flex gap-3">
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-check"></i> Create Quarter
                    </button>
                    <a href="{{ route('building.quarters') }}" class="kt-btn kt-btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Reusable function to create searchable select
function createSearchableSelect(config) {
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
        placeholder = 'Select...',
        searchPlaceholder = 'Search...'
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

    let selectedOption = null;
    let filteredOptions = [...options];

    // Render options
    function renderOptions(opts) {
        if (opts.length === 0) {
            optionsContainer.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No options found</div>';
            return;
        }

        optionsContainer.innerHTML = opts.map(opt => {
            const display = displayFn ? displayFn(opt) : (opt.name || opt.id || opt);
            const value = opt.id !== undefined ? opt.id : (opt.value !== undefined ? opt.value : opt);
            return `
                <div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 select-option" 
                     data-id="${value}" 
                     data-name="${display}">
                    <div class="text-sm text-foreground">${display}</div>
                </div>
            `;
        }).join('');

        // Add click handlers
        optionsContainer.querySelectorAll('.select-option').forEach(option => {
            option.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                selectedOption = options.find(o => {
                    const optValue = o.id !== undefined ? o.id : (o.value !== undefined ? o.value : o);
                    return String(optValue) === String(id);
                });
                
                if (selectedOption || id === '') {
                    hiddenInput.value = id;
                    displayText.textContent = name;
                    dropdown.classList.add('hidden');
                    searchInput.value = '';
                    filteredOptions = [...options];
                    renderOptions(filteredOptions);
                    
                    if (onSelect) onSelect(selectedOption || {id: id, name: name});
                }
            });
        });
    }

    // Initial render
    renderOptions(filteredOptions);

    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        filteredOptions = options.filter(opt => {
            const display = displayFn ? displayFn(opt) : (opt.name || opt.id || opt);
            return String(display).toLowerCase().includes(searchTerm);
        });
        renderOptions(filteredOptions);
    });

    // Toggle dropdown
    trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdown.classList.toggle('hidden');
        if (!dropdown.classList.contains('hidden')) {
            setTimeout(() => searchInput.focus(), 100);
        }
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!trigger.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Quarter type options
    const quarterTypeOptions = [
        {id: '', name: 'Select type'},
        {id: 'Single Room', name: 'Single Room'},
        {id: 'One Bedroom', name: 'One Bedroom'},
        {id: 'Two Bedroom', name: 'Two Bedroom'},
        {id: 'Three Bedroom', name: 'Three Bedroom'},
        {id: 'Four Bedroom', name: 'Four Bedroom'},
        {id: 'Duplex', name: 'Duplex'},
        {id: 'Bungalow', name: 'Bungalow'},
        {id: 'Other', name: 'Other'}
    ];

    // Initialize quarter type select
    if (document.getElementById('quarter_type_select_trigger')) {
        createSearchableSelect({
            triggerId: 'quarter_type_select_trigger',
            hiddenInputId: 'quarter-type',
            dropdownId: 'quarter_type_dropdown',
            searchInputId: 'quarter_type_search_input',
            optionsContainerId: 'quarter_type_options',
            displayTextId: 'quarter_type_select_text',
            options: quarterTypeOptions,
            placeholder: 'Select type',
            searchPlaceholder: 'Search quarter type...',
            onSelect: function(option) {
                toggleCustomType();
            }
        });
    }

    function toggleCustomType() {
        const quarterTypeHiddenInput = document.getElementById('quarter-type');
        const customContainer = document.getElementById('custom-type-container');
        const customInput = document.getElementById('custom-quarter-type');
        
        if (quarterTypeHiddenInput && customContainer && customInput) {
            const selectedType = quarterTypeHiddenInput.value;
            if (selectedType === 'Other') {
                customContainer.style.display = 'block';
                customInput.required = true;
            } else {
                customContainer.style.display = 'none';
                customInput.required = false;
                customInput.value = '';
            }
        }
    }

    // Listen for changes on the hidden input
    const quarterTypeHiddenInput = document.getElementById('quarter-type');
    if (quarterTypeHiddenInput) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                    toggleCustomType();
                }
            });
        });
        observer.observe(quarterTypeHiddenInput, { attributes: true, attributeFilter: ['value'] });
        
        quarterTypeHiddenInput.addEventListener('input', toggleCustomType);
    }

    // Check on load
    toggleCustomType();

    @if(!$commandId)
        // Disable form if no command assigned
        document.getElementById('create-quarter-form').querySelectorAll('input, button[type="submit"]').forEach(el => {
            el.disabled = true;
        });
    @endif
    
    document.getElementById('create-quarter-form').addEventListener('submit', handleSubmit);
});

async function handleSubmit(e) {
    e.preventDefault();
    
    const commandId = document.getElementById('command-id').value;
    const quarterNumber = document.getElementById('quarter-number').value.trim();
    const quarterTypeHiddenInput = document.getElementById('quarter-type');
    let quarterType = quarterTypeHiddenInput ? quarterTypeHiddenInput.value : '';
    
    if (quarterType === 'Other') {
        const customInput = document.getElementById('custom-quarter-type');
        quarterType = customInput ? customInput.value.trim() : '';
        if (!quarterType) {
            showError('Please specify the quarter type');
            return;
        }
    }
    
    @if(!$commandId)
        showError('You must be assigned to a command to create quarters. Please contact HRD.');
        return;
    @endif
    
    if (!commandId) {
        showError('Command is required');
        return;
    }
    
    if (!quarterNumber) {
        showError('Please enter a quarter number');
        return;
    }
    
    try {
        const token = window.API_CONFIG.token;
        const res = await fetch('/api/v1/quarters', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                command_id: parseInt(commandId),
                quarter_number: quarterNumber,
                quarter_type: quarterType
            })
        });
        
        const data = await res.json();
        
        if (res.ok && data.success) {
            showSuccess('Quarter created successfully!');
            setTimeout(() => {
                window.location.href = '{{ route("building.quarters") }}';
            }, 1500);
        } else {
            const errorMsg = data.message || 'Failed to create quarter';
            console.error('API Error:', errorMsg);
            showError(errorMsg);
        }
    } catch (error) {
        console.error('Error creating quarter:', error);
        showError('Error creating quarter. Please try again.');
    }
}

function showSuccess(message) {
    const notification = document.createElement('div');
    notification.className = 'kt-card bg-success/10 border border-success/20 mb-4';
    notification.innerHTML = `
        <div class="kt-card-content p-4">
            <div class="flex items-center gap-3">
                <i class="ki-filled ki-check-circle text-success text-xl"></i>
                <p class="text-sm text-success font-medium">${message}</p>
            </div>
        </div>
    `;
    
    const content = document.querySelector('.grid.gap-5');
    if (content) {
        content.insertBefore(notification, content.firstChild);
        setTimeout(() => notification.remove(), 5000);
    } else {
        alert(message);
    }
}

function showError(message) {
    const notification = document.createElement('div');
    notification.className = 'kt-card bg-danger/10 border border-danger/20 mb-4';
    notification.innerHTML = `
        <div class="kt-card-content p-4">
            <div class="flex items-center gap-3">
                <i class="ki-filled ki-information text-danger text-xl"></i>
                <p class="text-sm text-danger font-medium">${message}</p>
            </div>
        </div>
    `;
    
    const content = document.querySelector('.grid.gap-5');
    if (content) {
        content.insertBefore(notification, content.firstChild);
        setTimeout(() => notification.remove(), 5000);
    } else {
        alert(message);
    }
}
</script>
@endpush
@endsection

