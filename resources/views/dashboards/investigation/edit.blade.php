@extends('layouts.app')

@section('title', 'Update Investigation Status')
@section('page-title', 'Update Investigation Status')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('investigation.dashboard') }}">Investigation Unit</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('investigation.index') }}">Investigations</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('investigation.show', $investigation->id) }}">Details</a>
    <span>/</span>
    <span class="text-primary">Update Status</span>
@endsection

@section('content')
@if(session('error'))
    <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
        <div class="kt-card-content p-4">
            <div class="flex items-center gap-3">
                <i class="ki-filled ki-information text-danger text-xl"></i>
                <p class="text-sm text-danger">{{ session('error') }}</p>
            </div>
        </div>
    </div>
@endif

<div class="grid gap-5 lg:gap-7.5">
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Update Investigation Status</h3>
        </div>
        <div class="kt-card-content">
            <!-- Officer Information -->
            <div class="mb-5 p-4 bg-muted/50 rounded-lg border border-input">
                <h4 class="font-semibold mb-3">Officer Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm text-secondary-foreground">Name:</span>
                        <p class="font-medium">{{ $investigation->officer->initials }} {{ $investigation->officer->surname }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Service Number:</span>
                        <p class="font-medium">{{ $investigation->officer->service_number }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Current Status:</span>
                        <p class="font-medium">
                            @if($investigation->status === 'INVITED')
                                <span class="kt-badge kt-badge-info">Invited</span>
                            @elseif($investigation->status === 'ONGOING_INVESTIGATION')
                                <span class="kt-badge kt-badge-warning">Ongoing Investigation</span>
                            @elseif($investigation->status === 'INTERDICTED')
                                <span class="kt-badge kt-badge-danger">Interdicted</span>
                            @elseif($investigation->status === 'SUSPENDED')
                                <span class="kt-badge kt-badge-danger">Suspended</span>
                            @elseif($investigation->status === 'DISMISSED')
                                <span class="kt-badge kt-badge-danger">Dismissed</span>
                            @elseif($investigation->status === 'RESOLVED')
                                <span class="kt-badge kt-badge-success">Resolved</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <form action="{{ route('investigation.update', $investigation->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="grid gap-5">
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Investigation Status <span class="text-danger">*</span>
                        </label>
                        <div class="relative">
                            <input type="hidden" name="status" id="status_id" value="{{ old('status', $investigation->status) ?? '' }}" required>
                            <button type="button" 
                                    id="status_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer @error('status') border-danger @enderror">
                                <span id="status_select_text">{{ old('status', $investigation->status) ? (old('status', $investigation->status) === 'ONGOING_INVESTIGATION' ? 'Ongoing Investigation' : (old('status', $investigation->status) === 'INTERDICTED' ? 'Interdicted' : (old('status', $investigation->status) === 'SUSPENDED' ? 'Suspended' : (old('status', $investigation->status) === 'DISMISSED' ? 'Dismissed' : (old('status', $investigation->status) === 'RESOLVED' ? 'Resolved' : 'Select Status'))))) : 'Select Status' }}</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="status_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <div class="p-3 border-b border-input">
                                    <input type="text" 
                                           id="status_search_input" 
                                           class="kt-input w-full pl-10" 
                                           placeholder="Search status..."
                                           autocomplete="off">
                                </div>
                                <div id="status_options" class="max-h-60 overflow-y-auto"></div>
                            </div>
                        </div>
                        <p class="text-xs text-secondary-foreground mt-1">
                            <strong>Note:</strong> Officers with Ongoing Investigation, Interdiction, Suspension, or Dismissal status cannot appear on Promotion Eligibility Lists. Interdicted officers will appear on Accounts unit's interdicted officers list. Setting status to Resolved will clear investigation flags (but not dismissal, which is permanent).
                        </p>
                        @error('status')
                            <p class="text-danger text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Investigation Notes (Optional)
                        </label>
                        <textarea name="notes" 
                                  rows="4" 
                                  class="kt-input w-full @error('notes') border-danger @enderror"
                                  placeholder="Enter any additional notes about this investigation status change...">{{ old('notes', $investigation->notes) }}</textarea>
                        @error('notes')
                            <p class="text-danger text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-check-circle"></i> Update Status
                        </button>
                        <a href="{{ route('investigation.show', $investigation->id) }}" class="kt-btn kt-btn-secondary">
                            Cancel
                        </a>
                    </div>
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

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Status options
        const statusOptions = [
            {id: '', name: 'Select Status'},
            {id: 'ONGOING_INVESTIGATION', name: 'Ongoing Investigation'},
            {id: 'INTERDICTED', name: 'Interdicted'},
            {id: 'SUSPENDED', name: 'Suspended'},
            {id: 'DISMISSED', name: 'Dismissed'},
            {id: 'RESOLVED', name: 'Resolved'}
        ];

        // Initialize status select
        createSearchableSelect({
            triggerId: 'status_select_trigger',
            hiddenInputId: 'status_id',
            dropdownId: 'status_dropdown',
            searchInputId: 'status_search_input',
            optionsContainerId: 'status_options',
            displayTextId: 'status_select_text',
            options: statusOptions,
            placeholder: 'Select Status',
            searchPlaceholder: 'Search status...'
        });
    });
</script>
@endpush
@endsection


