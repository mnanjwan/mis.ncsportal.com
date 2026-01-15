@extends('layouts.app')

@section('title', 'Add Next of KIN')
@section('page-title', 'Add Next of KIN')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.next-of-kin.index') }}">Next of KIN</a>
    <span>/</span>
    <span class="text-primary">Add</span>
@endsection

@section('content')
    <!-- Success/Error Messages -->
    @if(session('error'))
        <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-danger text-xl"></i>
                    <p class="text-sm font-medium text-danger">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-start gap-3">
                    <i class="ki-filled ki-information text-danger text-xl mt-0.5"></i>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-danger mb-2">Please fix the following errors:</p>
                        <ul class="list-disc list-inside text-sm text-danger space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="grid gap-5 lg:gap-7.5">
        <!-- Add Next of KIN Form -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Add Next of KIN</h3>
            </div>
            <div class="kt-card-content">
                <form action="{{ route('officer.next-of-kin.store') }}" method="POST" id="addForm">
                    @csrf

                    <div class="flex flex-col gap-5">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-foreground mb-2">
                                Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}"
                                   class="kt-input w-full" 
                                   placeholder="Enter full name"
                                   required
                                   maxlength="255">
                        </div>

                        <!-- Relationship -->
                        <div>
                            <label for="relationship" class="block text-sm font-medium text-foreground mb-2">
                                Relationship <span class="text-danger">*</span>
                            </label>
                            <div class="relative">
                                <input type="hidden" name="relationship" id="relationship" value="{{ old('relationship') ?? '' }}" required>
                                <button type="button" 
                                        id="relationship_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                    <span id="relationship_select_text">{{ old('relationship') ? old('relationship') : 'Select Relationship' }}</span>
                                    <i class="ki-filled ki-down text-gray-400"></i>
                                </button>
                                <div id="relationship_dropdown" 
                                     class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                    <div class="p-3 border-b border-input">
                                        <input type="text" 
                                               id="relationship_search_input" 
                                               class="kt-input w-full pl-10" 
                                               placeholder="Search relationship..."
                                               autocomplete="off">
                                    </div>
                                    <div id="relationship_options" class="max-h-60 overflow-y-auto"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Phone Number -->
                        <div>
                            <label for="phone_number" class="block text-sm font-medium text-foreground mb-2">
                                Phone Number
                            </label>
                            <input type="text" 
                                   id="phone_number" 
                                   name="phone_number" 
                                   value="{{ old('phone_number') }}"
                                   class="kt-input w-full" 
                                   placeholder="Enter phone number"
                                   maxlength="20">
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-foreground mb-2">
                                Email
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}"
                                   class="kt-input w-full" 
                                   placeholder="Enter email address"
                                   maxlength="255">
                        </div>

                        <!-- Address -->
                        <div>
                            <label for="address" class="block text-sm font-medium text-foreground mb-2">
                                Address
                            </label>
                            <textarea id="address" 
                                      name="address" 
                                      rows="4"
                                      class="kt-input w-full"
                                      placeholder="Enter address">{{ old('address') }}</textarea>
                        </div>

                        <!-- Is Primary -->
                        <div>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" 
                                       name="is_primary" 
                                       id="is_primary" 
                                       value="1" 
                                       class="kt-checkbox"
                                       {{ old('is_primary') ? 'checked' : '' }}>
                                <span class="text-sm">Set as Primary Next of KIN</span>
                            </label>
                        </div>

                        <!-- Info Box -->
                        <div class="kt-card bg-info/10 border border-info/20">
                            <div class="kt-card-content p-4">
                                <div class="flex items-start gap-3">
                                    <i class="ki-filled ki-information text-info text-xl mt-0.5"></i>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-info mb-1">Important Information</p>
                                        <ul class="list-disc list-inside text-xs text-secondary-foreground space-y-1">
                                            <li>Your request will be reviewed by the Welfare Section</li>
                                            <li>You will be notified once your request is processed</li>
                                            <li>Name and Relationship are required fields</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                            <a href="{{ route('officer.next-of-kin.index') }}" class="kt-btn kt-btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-check"></i>
                                Submit Request
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

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

// Initialize relationship select
document.addEventListener('DOMContentLoaded', function() {
    const relationshipOptions = [
        {id: '', name: 'Select Relationship'},
        {id: 'Spouse', name: 'Spouse'},
        {id: 'Father', name: 'Father'},
        {id: 'Mother', name: 'Mother'},
        {id: 'Brother', name: 'Brother'},
        {id: 'Sister', name: 'Sister'},
        {id: 'Son', name: 'Son'},
        {id: 'Daughter', name: 'Daughter'},
        {id: 'Uncle', name: 'Uncle'},
        {id: 'Aunt', name: 'Aunt'},
        {id: 'Other', name: 'Other'}
    ];

    if (document.getElementById('relationship_select_trigger')) {
        createSearchableSelect({
            triggerId: 'relationship_select_trigger',
            hiddenInputId: 'relationship',
            dropdownId: 'relationship_dropdown',
            searchInputId: 'relationship_search_input',
            optionsContainerId: 'relationship_options',
            displayTextId: 'relationship_select_text',
            options: relationshipOptions,
            placeholder: 'Select Relationship',
            searchPlaceholder: 'Search relationship...'
        });
    }
});
</script>
@endpush
