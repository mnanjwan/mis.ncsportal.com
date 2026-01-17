@extends('layouts.app')

@section('title', 'Report Deceased Officer')
@section('page-title', 'Report Deceased Officer')

@section('breadcrumbs')
    @if(auth()->user()->hasRole('Area Controller'))
        <a class="text-secondary-foreground hover:text-primary" href="{{ route('area-controller.dashboard') }}">Area Controller</a>
    @elseif(auth()->user()->hasRole('Staff Officer'))
        <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.dashboard') }}">Staff Officer</a>
    @endif
    <span>/</span>
    <span class="text-primary">Report Deceased Officer</span>
@endsection

@section('content')
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="kt-card bg-success/10 border border-success/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-check-circle text-success text-xl"></i>
                    <p class="text-sm font-medium text-success">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

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
        <!-- Report Form -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Report Deceased Officer</h3>
            </div>
            <div class="kt-card-content">
                <form action="{{ auth()->user()->hasRole('Area Controller') ? route('area-controller.deceased-officers.store') : route('staff-officer.deceased-officers.store') }}" method="POST" enctype="multipart/form-data" id="reportForm">
                    @csrf

                    <div class="flex flex-col gap-5">
                        <!-- Officer Selection (Searchable Select) -->
                        <div>
                            <label class="block text-sm font-medium mb-1">
                                Officer <span class="text-danger">*</span>
                            </label>
                            <div class="relative">
                                <input type="hidden" name="officer_id" id="officer_id" value="{{ old('officer_id') }}" required>
                                <button type="button" 
                                        id="officer_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer @error('officer_id') border-danger @enderror">
                                    <span id="officer_select_text">{{ old('officer_id') ? ($officers->find(old('officer_id')) ? $officers->find(old('officer_id'))->initials . ' ' . $officers->find(old('officer_id'))->surname . ' - ' . $officers->find(old('officer_id'))->service_number : 'Select an officer') : 'Select an officer' }}</span>
                                    <i class="ki-filled ki-down text-gray-400"></i>
                                </button>
                                <div id="officer_dropdown" 
                                     class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                    <!-- Search Box -->
                                    <div class="p-3 border-b border-input">
                                        <div class="relative">
                                            <input type="text" 
                                                   id="officer_search_input" 
                                                   class="kt-input w-full" 
                                                   placeholder="Search officers..."
                                                   autocomplete="off">
                                        </div>
                                    </div>
                                    <!-- Options Container -->
                                    <div id="officer_options" class="max-h-60 overflow-y-auto">
                                        @foreach($officers as $officer)
                                            <div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 officer-option" 
                                                 data-id="{{ $officer->id }}" 
                                                 data-name="{{ $officer->initials }} {{ $officer->surname }}"
                                                 data-service="{{ $officer->service_number }}"
                                                 data-rank="{{ $officer->substantive_rank }}">
                                                <div class="text-sm text-foreground font-medium">{{ $officer->initials }} {{ $officer->surname }}</div>
                                                <div class="text-xs text-secondary-foreground">{{ $officer->service_number }} - {{ $officer->substantive_rank }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-secondary-foreground mt-1" id="officer_info">{{ $officers->count() }} officer{{ $officers->count() !== 1 ? 's' : '' }} available</p>
                            @error('officer_id')
                                <p class="text-danger text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Date of Death -->
                        <div>
                            <label for="date_of_death" class="block text-sm font-medium text-foreground mb-2">
                                Date of Death <span class="text-danger">*</span>
                            </label>
                            <input type="date" 
                                   id="date_of_death" 
                                   name="date_of_death" 
                                   value="{{ old('date_of_death') }}"
                                   class="kt-input w-full" 
                                   required
                                   max="{{ date('Y-m-d') }}">
                            <p class="text-xs text-secondary-foreground mt-1">Date cannot be in the future</p>
                            @error('date_of_death')
                                <p class="text-danger text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Death Certificate -->
                        <div>
                            <label for="death_certificate" class="block text-sm font-medium text-foreground mb-2">
                                Death Certificate (Optional)
                            </label>
                            <input type="file" 
                                   id="death_certificate" 
                                   name="death_certificate" 
                                   class="kt-input w-full"
                                   accept=".jpeg,.jpg,.png,.pdf">
                            <span class="text-xs" style="color: red; display: block; margin-top: 0.5rem;">
                                <strong>Document Type Allowed:</strong> JPEG, JPG, PNG, PDF<br>
                                <strong>Document Size Allowed:</strong> Maximum 5MB
                            </span>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-foreground mb-2">
                                Additional Notes (Optional)
                            </label>
                            <textarea id="notes" 
                                      name="notes" 
                                      rows="4"
                                      class="kt-input w-full"
                                      placeholder="Enter any additional information">{{ old('notes') }}</textarea>
                        </div>

                        <!-- Info Box -->
                        <div class="kt-card bg-info/10 border border-info/20">
                            <div class="kt-card-content p-4">
                                <div class="flex items-start gap-3">
                                    <i class="ki-filled ki-information text-info text-xl mt-0.5"></i>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-info mb-1">Important Information</p>
                                        <ul class="list-disc list-inside text-xs text-secondary-foreground space-y-1">
                                            <li>This report will be sent to Welfare for validation</li>
                                            <li>Welfare will verify the death certificate and generate comprehensive data</li>
                                            <li>The officer will be marked as deceased after Welfare validation</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                            <a href="{{ auth()->user()->hasRole('Area Controller') ? route('area-controller.dashboard') : route('staff-officer.dashboard') }}" class="kt-btn kt-btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-check"></i>
                                Submit Report
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
    document.addEventListener('DOMContentLoaded', function() {
        // Set max date to today for date of death field
        const dateOfDeathInput = document.getElementById('date_of_death');
        if (dateOfDeathInput) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const maxDate = today.toISOString().split('T')[0];
            dateOfDeathInput.setAttribute('max', maxDate);
            
            // Validate on input change
            dateOfDeathInput.addEventListener('change', function() {
                validateDateOfDeath(this);
            });
            
            // Validate on blur
            dateOfDeathInput.addEventListener('blur', function() {
                validateDateOfDeath(this);
            });
        }
        
        // Officers data
        @php
            $officersData = $officers->map(function($officer) {
                return [
                    'id' => $officer->id,
                    'name' => $officer->initials . ' ' . $officer->surname,
                    'service_number' => $officer->service_number ?? 'N/A',
                    'rank' => $officer->substantive_rank ?? 'N/A'
                ];
            })->values();
        @endphp
        const officers = @json($officersData);

        const officerSelectTrigger = document.getElementById('officer_select_trigger');
        const officerSelectText = document.getElementById('officer_select_text');
        const officerHiddenInput = document.getElementById('officer_id');
        const officerDropdown = document.getElementById('officer_dropdown');
        const officerOptions = document.getElementById('officer_options');
        const officerSearchInput = document.getElementById('officer_search_input');
        const officerInfo = document.getElementById('officer_info');

        // Render officer options
        function renderOfficerOptions(officersList) {
            if (officersList.length === 0) {
                officerOptions.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No officers found</div>';
                return;
            }
            
            officerOptions.innerHTML = officersList.map(officer => {
                const details = officer.service_number !== 'N/A' ? officer.service_number : '';
                const rank = officer.rank !== 'N/A' ? ' - ' + officer.rank : '';
                const displayText = officer.name + (details ? ' (' + details + rank + ')' : '');
                
                return `
                    <div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 officer-option" 
                         data-id="${officer.id}" 
                         data-name="${officer.name}"
                         data-service="${officer.service_number}"
                         data-rank="${officer.rank}">
                        <div class="text-sm text-foreground font-medium">${officer.name}</div>
                        <div class="text-xs text-secondary-foreground">${details}${rank}</div>
                    </div>
                `;
            }).join('');
            
            // Add click handlers
            officerOptions.querySelectorAll('.officer-option').forEach(option => {
                option.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;
                    const service = this.dataset.service;
                    const rank = this.dataset.rank;
                    
                    // Update hidden input
                    officerHiddenInput.value = id;
                    
                    // Update display text
                    const displayText = name + (service !== 'N/A' ? ' (' + service + (rank !== 'N/A' ? ' - ' + rank : '') + ')' : '');
                    officerSelectText.textContent = displayText;
                    
                    // Close dropdown
                    officerDropdown.classList.add('hidden');
                    
                    // Clear search
                    officerSearchInput.value = '';
                    
                    // Re-render all options
                    renderOfficerOptions(officers);
                });
            });
        }

        // Setup search functionality
        function setupOfficerSearch() {
            officerSearchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const filtered = officers.filter(officer => {
                    const nameMatch = officer.name.toLowerCase().includes(searchTerm);
                    const serviceMatch = officer.service_number && officer.service_number.toLowerCase().includes(searchTerm);
                    const rankMatch = officer.rank && officer.rank.toLowerCase().includes(searchTerm);
                    return nameMatch || serviceMatch || rankMatch;
                });
                
                renderOfficerOptions(filtered);
            });
        }

        // Initialize
        renderOfficerOptions(officers);
        setupOfficerSearch();

        // Toggle dropdown
        officerSelectTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            officerDropdown.classList.toggle('hidden');
            
            if (!officerDropdown.classList.contains('hidden')) {
                // Focus search input when dropdown opens
                setTimeout(() => {
                    officerSearchInput.focus();
                }, 100);
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (officerDropdown && !officerDropdown.contains(e.target) && !officerSelectTrigger.contains(e.target)) {
                officerDropdown.classList.add('hidden');
            }
        });

        // Set initial selected officer if old input exists
        @if(old('officer_id'))
            const selectedOfficer = officers.find(o => o.id == {{ old('officer_id') }});
            if (selectedOfficer) {
                const displayText = selectedOfficer.name + (selectedOfficer.service_number !== 'N/A' ? ' (' + selectedOfficer.service_number + (selectedOfficer.rank !== 'N/A' ? ' - ' + selectedOfficer.rank : '') + ')' : '');
                officerSelectText.textContent = displayText;
            }
        @endif
        
        // Form submission validation
        const reportForm = document.getElementById('reportForm');
        if (reportForm) {
            reportForm.addEventListener('submit', function(e) {
                const dateInput = document.getElementById('date_of_death');
                if (!validateDateOfDeath(dateInput)) {
                    e.preventDefault();
                    return false;
                }
            });
        }
    });
    
    // Validate date of death - cannot be in the future
    function validateDateOfDeath(input) {
        if (!input || !input.value) {
            return true; // Let required validation handle empty fields
        }
        
        const selectedDate = new Date(input.value);
        const today = new Date();
        today.setHours(23, 59, 59, 999); // End of today
        
        // Remove any existing error styling
        input.classList.remove('border-danger');
        
        // Remove existing error message if any
        let errorMsg = input.parentElement.querySelector('.date-error-msg');
        if (errorMsg) {
            errorMsg.remove();
        }
        
        if (selectedDate > today) {
            input.classList.add('border-danger');
            const errorDiv = document.createElement('p');
            errorDiv.className = 'text-danger text-sm mt-1 date-error-msg';
            errorDiv.textContent = 'Date of death cannot be in the future. Please select a date up to today.';
            input.parentElement.appendChild(errorDiv);
            input.setCustomValidity('Date of death cannot be in the future');
            return false;
        } else {
            input.setCustomValidity('');
            return true;
        }
    }
</script>
@endpush
