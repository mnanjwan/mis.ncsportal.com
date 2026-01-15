@extends('layouts.app')

@section('title', 'Apply for Leave')
@section('page-title', 'Apply for Leave')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
    <span>/</span>
    <span class="text-primary">Apply for Leave</span>
@endsection

@section('content')
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 lg:gap-9 mb-5 lg:mb-10">
        <div class="xl:col-span-2 space-y-5">
            <!-- Leave Balance Info -->
            <div class="kt-card bg-success/10 border border-success/20">
                <div class="kt-card-content p-5">
                    <div class="flex items-center gap-3">
                        <i class="ki-filled ki-information text-2xl text-success"></i>
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-semibold text-mono">Leave Balance Available</span>
                            <span class="text-xs text-secondary-foreground" id="leave-balance-info">
                                Annual Leave: 30 days remaining (Standard)
                            </span>
                            <span class="text-xs text-secondary-foreground">
                                Annual leave can be applied for a maximum of 2 times per year
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End of Leave Balance Info -->

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

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form -->
            <form class="kt-card" action="{{ route('leave.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Leave Application Form</h3>
                </div>
                <div class="kt-card-content space-y-5">
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono">Leave Type</label>
                        <div class="relative">
                            <input type="hidden" name="leave_type_id" id="leave-type" value="{{ old('leave_type_id') ?? '' }}" required>
                            <button type="button" 
                                    id="leave_type_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="leave_type_select_text">
                                    @if(old('leave_type_id'))
                                        @php $selectedType = $leaveTypes->firstWhere('id', old('leave_type_id')); @endphp
                                        {{ $selectedType ? $selectedType->name : 'Select Leave Type' }}
                                    @else
                                        Select Leave Type
                                    @endif
                                </span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="leave_type_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <div class="p-3 border-b border-input">
                                    <input type="text" 
                                           id="leave_type_search_input" 
                                           class="kt-input w-full pl-10" 
                                           placeholder="Search leave type..."
                                           autocomplete="off">
                                </div>
                                <div id="leave_type_options" class="max-h-60 overflow-y-auto"></div>
                            </div>
                        </div>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label font-normal text-mono">Start Date</label>
                            <input class="kt-input" type="date" name="start_date" id="start-date"
                                value="{{ old('start_date') }}" required />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label font-normal text-mono">End Date</label>
                            <input class="kt-input" type="date" name="end_date" id="end-date" value="{{ old('end_date') }}"
                                required />
                        </div>
                    </div>
                    <div class="flex flex-col gap-1" id="edd_field" style="display: none;">
                        <label class="kt-form-label font-normal text-mono">Expected Date of Delivery (EDD)</label>
                        <input class="kt-input" type="date" name="expected_date_of_delivery"
                            value="{{ old('expected_date_of_delivery') }}" />
                        <span class="text-xs text-secondary-foreground">Required for Maternity Leave</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono">Reason for Leave</label>
                        <textarea class="kt-input" placeholder="Enter reason for leave" name="reason" rows="4"
                            required>{{ old('reason') }}</textarea>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono">Supporting Documents</label>
                        <input class="kt-input" type="file" name="medical_certificate"
                            accept="image/jpeg,application/pdf" />
                        <span class="text-xs text-secondary-foreground">Upload supporting documents</span>
                        <span class="text-xs" style="color: red;">
                            <strong>Document Type Allowed:</strong> JPEG or PDF format<br>
                            <strong>Document Size Allowed:</strong> Maximum 5MB
                        </span>
                    </div>
                </div>
                <div class="kt-card-footer flex justify-end items-center flex-wrap gap-3">
                    <a class="kt-btn kt-btn-outline" href="{{ route('officer.dashboard') }}">Cancel</a>
                    <button class="kt-btn kt-btn-primary" type="submit" id="submit-btn">
                        Submit Application
                        <i class="ki-filled ki-check text-base"></i>
                    </button>
                </div>
            </form>
            <!-- End of Form -->
        </div>
        <div class="xl:col-span-1">
            <!-- Leave Rules Card -->
            <div class="kt-card bg-accent/50">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Leave Rules</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-3 text-sm">
                        <div class="kt-card shadow-none bg-info/10 border border-info/20">
                            <div class="kt-card-content p-4">
                                <p class="text-xs text-secondary-foreground">
                                    <strong class="text-mono">Annual Leave:</strong>
                                </p>
                                <ul class="text-xs text-secondary-foreground mt-2 list-disc list-inside space-y-1">
                                    <li>GL 07 and Below: 28 Days</li>
                                    <li>Level 08 and above: 30 days</li>
                                    <li>Maximum 2 times per year</li>
                                </ul>
                            </div>
                        </div>
                        <p class="text-xs text-secondary-foreground">
                            Your application will be reviewed by Staff Officer, then minuted to DC Admin for approval.
                        </p>
                        <p class="text-xs text-secondary-foreground">
                            You will receive a notification once your leave is approved.
                        </p>
                    </div>
                </div>
            </div>
            <!-- End of Leave Rules Card -->
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

            document.addEventListener('DOMContentLoaded', () => {
                // Leave type options
                const leaveTypeOptions = [
                    {id: '', name: 'Select Leave Type'},
                    @foreach($leaveTypes as $type)
                    {id: '{{ $type->id }}', name: '{{ $type->name }}'},
                    @endforeach
                ];

                // Initialize leave type select
                if (document.getElementById('leave_type_select_trigger')) {
                    createSearchableSelect({
                        triggerId: 'leave_type_select_trigger',
                        hiddenInputId: 'leave-type',
                        dropdownId: 'leave_type_dropdown',
                        searchInputId: 'leave_type_search_input',
                        optionsContainerId: 'leave_type_options',
                        displayTextId: 'leave_type_select_text',
                        options: leaveTypeOptions,
                        placeholder: 'Select Leave Type',
                        searchPlaceholder: 'Search leave type...',
                        onSelect: function(option) {
                            toggleEddField();
                        }
                    });
                }

                // Show EDD field for Maternity Leave
                const leaveTypeHiddenInput = document.getElementById('leave-type');
                const eddField = document.getElementById('edd_field');
                const eddInput = eddField?.querySelector('input');

                function toggleEddField() {
                    if (!eddField || !eddInput) return;
                    
                    const leaveTypeId = leaveTypeHiddenInput?.value;
                    const leaveTypeName = document.getElementById('leave_type_select_text')?.textContent || '';
                    
                    if (leaveTypeName.includes('Maternity Leave')) {
                        eddField.style.display = 'block';
                        eddInput.required = true;
                    } else {
                        eddField.style.display = 'none';
                        eddInput.required = false;
                    }
                }

                // Listen for changes on the hidden input
                if (leaveTypeHiddenInput) {
                    const observer = new MutationObserver(function(mutations) {
                        mutations.forEach(function(mutation) {
                            if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                                toggleEddField();
                            }
                        });
                    });
                    observer.observe(leaveTypeHiddenInput, { attributes: true, attributeFilter: ['value'] });
                    
                    leaveTypeHiddenInput.addEventListener('input', toggleEddField);
                }

                // Check on load in case of validation errors redirecting back
                toggleEddField();
            });
        </script>
    @endpush
@endsection