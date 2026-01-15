@extends('layouts.app')

@section('title', 'Raise Emolument')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.emoluments') }}">Emoluments</a>
    <span>/</span>
    <span class="text-primary">Raise</span>
@endsection

@section('content')
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Raise Emolument</h1>
                <p class="text-sm text-gray-600 mt-1">Submit your annual emolument for processing</p>
            </div>
            <a href="{{ route('officer.emoluments') }}" class="kt-btn kt-btn-secondary">
                <i class="ki-filled ki-left"></i>
                Back to Emoluments
            </a>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Emolument Information</h3>
            </div>
            <div class="kt-card-content">
                <form action="{{ route('emolument.store') }}" method="POST" id="raiseEmolumentForm">
                    @csrf

                    <!-- Timeline Selection -->
                    <div class="mb-6">
                        <label for="timeline_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Emolument Timeline <span class="text-red-500">*</span>
                        </label>
                        @php
                            $selectedTimelineId = old('timeline_id', $timelines->isNotEmpty() ? $timelines->first()->id : '');
                        @endphp
                        <div class="relative">
                            <input type="hidden" name="timeline_id" id="timeline_id" value="{{ $selectedTimelineId }}" required>
                            <button type="button" 
                                    id="timeline_id_select_trigger" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed opacity-60 text-left flex items-center justify-between"
                                    disabled>
                                <span id="timeline_id_select_text">
                                    @if($selectedTimelineId && $timelines->isNotEmpty())
                                        @php $selectedTimeline = $timelines->firstWhere('id', $selectedTimelineId); @endphp
                                        @if($selectedTimeline)
                                            {{ $selectedTimeline->year }} ({{ $selectedTimeline->start_date->format('d M Y') }} to {{ $selectedTimeline->end_date->format('d M Y') }})
                                        @else
                                            Select Timeline
                                        @endif
                                    @else
                                        Select Timeline
                                    @endif
                                </span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="timeline_id_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <div class="p-3 border-b border-input">
                                    <input type="text" 
                                           id="timeline_id_search_input" 
                                           class="kt-input w-full pl-10" 
                                           placeholder="Search timeline..."
                                           autocomplete="off"
                                           disabled>
                                </div>
                                <div id="timeline_id_options" class="max-h-60 overflow-y-auto"></div>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Select the active emolument timeline</p>
                    </div>

                    <!-- Bank Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Bank Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="bank_name" name="bank_name" required readonly
                                value="{{ old('bank_name', $officer->bank_name ?? '') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed opacity-60"
                                placeholder="Enter bank name">
                        </div>

                        <div>
                            <label for="bank_account_number" class="block text-sm font-medium text-gray-700 mb-2">
                                Account Number <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="bank_account_number" name="bank_account_number" required readonly
                                value="{{ old('bank_account_number', $officer->bank_account_number ?? '') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed opacity-60"
                                placeholder="Enter account number">
                        </div>
                    </div>

                    <!-- PFA Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="pfa_name" class="block text-sm font-medium text-gray-700 mb-2">
                                PFA Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="pfa_name" name="pfa_name" required readonly
                                value="{{ old('pfa_name', $officer->pfa_name ?? '') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed opacity-60"
                                placeholder="Enter PFA name">
                        </div>

                        <div>
                            <label for="rsa_pin" class="block text-sm font-medium text-gray-700 mb-2">
                                RSA PIN <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="rsa_pin" name="rsa_pin" required readonly
                                value="{{ old('rsa_pin', $officer->rsa_number ?? '') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed opacity-60"
                                placeholder="Enter RSA PIN">
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Additional Notes (Optional)
                        </label>
                        <textarea id="notes" name="notes" rows="4" readonly
                            class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed opacity-60"
                            placeholder="Enter any additional information">{{ old('notes') }}</textarea>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t">
                        <a href="{{ route('officer.emoluments') }}" class="kt-btn kt-btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-check"></i>
                            Submit Emolument
                        </button>
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

            document.addEventListener('DOMContentLoaded', function () {
                // Initialize timeline select (disabled, but using searchable pattern for consistency)
                const timelineOptions = [
                    {id: '', name: 'Select Timeline'},
                    @forelse($timelines as $timeline)
                    {id: '{{ $timeline->id }}', name: '{{ $timeline->year }} ({{ $timeline->start_date->format('d M Y') }} to {{ $timeline->end_date->format('d M Y') }})'},
                    @empty
                    {id: '', name: 'No active timeline available'},
                    @endforelse
                ];

                if (document.getElementById('timeline_id_select_trigger')) {
                    createSearchableSelect({
                        triggerId: 'timeline_id_select_trigger',
                        hiddenInputId: 'timeline_id',
                        dropdownId: 'timeline_id_dropdown',
                        searchInputId: 'timeline_id_search_input',
                        optionsContainerId: 'timeline_id_options',
                        displayTextId: 'timeline_id_select_text',
                        options: timelineOptions,
                        placeholder: 'Select Timeline',
                        searchPlaceholder: 'Search timeline...'
                    });
                }

                const form = document.getElementById('raiseEmolumentForm');
                let isSubmitting = false;

                form.addEventListener('submit', function (e) {
                    // If already confirmed, allow submission
                    if (isSubmitting) {
                        return true;
                    }

                    // Prevent default submission
                    e.preventDefault();

                    Swal.fire({
                        title: 'Confirm Submission',
                        text: 'Are you sure you want to submit this emolument?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Submit',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            isSubmitting = true;
                            // Use HTMLFormElement.prototype.submit() to bypass event listener
                            HTMLFormElement.prototype.submit.call(form);
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection