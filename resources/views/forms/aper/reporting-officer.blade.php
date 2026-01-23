@extends('layouts.app')

@section('title', 'APER Form - Reporting Officer Assessment')
@section('page-title', 'APER Form Assessment - ' . $form->year)

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('dashboard') }}">Dashboard</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary"
        href="{{ (auth()->user()->hasRole('Staff Officer') || auth()->user()->hasRole('HRD')) ? route('staff-officer.aper-forms.reporting-officer.search') : route('officer.aper-forms.search-officers') }}">APER Forms</a>
    <span>/</span>
    <span class="text-primary">Assessment</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Officer Passport Picture -->
        @php
            $profilePictureUrl = $officer->getProfilePictureUrlFull();
        @endphp
        <div class="kt-card bg-background border border-border shadow-sm">
            <div class="kt-card-content p-5">
                <div class="flex items-center gap-6">
                    @if($profilePictureUrl)
                        <div class="flex-shrink-0">
                            <img src="{{ $profilePictureUrl }}" 
                                 alt="Officer Passport Photo" 
                                 class="w-40 h-40 object-cover rounded-lg border-2 border-border shadow-md"
                                 style="width: 160px; height: 160px;">
                        </div>
                    @else
                        <div class="flex-shrink-0 w-40 h-40 bg-muted rounded-lg border-2 border-border flex items-center justify-center">
                            <div class="text-center">
                                <i class="ki-filled ki-user text-4xl text-secondary-foreground"></i>
                                <p class="text-xs text-secondary-foreground mt-2">No Photo</p>
                            </div>
                        </div>
                    @endif
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-foreground mb-2">
                            {{ $officer->initials }} {{ $officer->surname }}
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <p class="text-xs text-secondary-foreground font-medium uppercase tracking-wide mb-1">Service Number</p>
                                <p class="text-base font-semibold text-foreground">{{ $officer->service_number }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-secondary-foreground font-medium uppercase tracking-wide mb-1">Rank</p>
                                <p class="text-base font-semibold text-foreground">{{ $officer->rank ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-secondary-foreground font-medium uppercase tracking-wide mb-1">Assessment Year</p>
                                <p class="text-base font-semibold text-foreground">{{ $form->year }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-secondary-foreground font-medium uppercase tracking-wide mb-1">Form Status</p>
                                <p class="text-base font-semibold text-foreground">Reporting Officer Assessment</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                        <i class="ki-filled ki-cross-circle text-danger text-xl"></i>
                        <p class="text-sm font-medium text-danger">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Officer Info Card -->
        <div class="kt-card bg-info/10 border border-info/20 shadow-sm">
            <div class="kt-card-content p-5">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="flex flex-col">
                        <p class="text-xs text-secondary-foreground font-medium uppercase tracking-wide mb-1">Officer Name</p>
                        <p class="text-base font-semibold text-foreground">{{ $officer->initials }} {{ $officer->surname }}</p>
                    </div>
                    <div class="flex flex-col">
                        <p class="text-xs text-secondary-foreground font-medium uppercase tracking-wide mb-1">Service Number</p>
                        <p class="text-base font-semibold text-foreground">{{ $officer->service_number }}</p>
                    </div>
                    <div class="flex flex-col">
                        <p class="text-xs text-secondary-foreground font-medium uppercase tracking-wide mb-1">Assessment Year</p>
                        <p class="text-base font-semibold text-foreground">{{ $form->year }}</p>
                    </div>
                    <div class="flex flex-col">
                        <p class="text-xs text-secondary-foreground font-medium uppercase tracking-wide mb-1">Rank</p>
                        <p class="text-base font-semibold text-foreground">{{ $officer->rank ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form action="{{ (auth()->user()->hasRole('Staff Officer') || auth()->user()->hasRole('HRD')) ? route('staff-officer.aper-forms.update-reporting-officer', $form->id) : route('officer.aper-forms.update-reporting-officer', $form->id) }}" method="POST"
            id="reporting-officer-form">
            @csrf

            <!-- Section 9: Assessment of Performance -->
            @include('forms.aper.partials.reporting-officer.section9-assessment', ['form' => $form])

            <!-- Section 10: Aspects of Performance -->
            @include('forms.aper.partials.reporting-officer.section10-aspects', ['form' => $form])

            <!-- Section 11: Overall Assessment -->
            @include('forms.aper.partials.reporting-officer.section11-overall', ['form' => $form])

            <!-- Section 12-15: Training Needs, Remarks, Suggestions, Promotability -->
            @include('forms.aper.partials.reporting-officer.section12-15-final', ['form' => $form])

            <!-- Form Actions -->
            <div class="kt-card">
                <div class="kt-card-content">
                    <div class="flex items-center justify-between pt-4 border-t border-border">
                        <a href="{{ (auth()->user()->hasRole('Staff Officer') || auth()->user()->hasRole('HRD')) ? route('staff-officer.aper-forms.reporting-officer.search') : route('officer.aper-forms.search-officers') }}"
                            class="kt-btn kt-btn-secondary">
                            <i class="ki-filled ki-arrow-left"></i> Back
                        </a>
                        <div class="flex gap-3">
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-check"></i> Save Assessment
                            </button>
                            <button type="button" id="complete-forward-btn" class="kt-btn kt-btn-success">
                                <i class="ki-filled ki-send"></i> Complete & Forward
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>

    <script>
        // Grade selection visual feedback
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize YES/NO dropdowns for Section 9
            function initYesNoDropdown(triggerId, dropdownId, optionsId, hiddenInputId, displayTextId, searchInputId) {
                const trigger = document.getElementById(triggerId);
                const dropdown = document.getElementById(dropdownId);
                const optionsContainer = document.getElementById(optionsId);
                const hiddenInput = document.getElementById(hiddenInputId);
                const displayText = document.getElementById(displayTextId);
                const searchInput = document.getElementById(searchInputId);

                if (!trigger || !dropdown || !optionsContainer || !hiddenInput || !displayText) {
                    return;
                }

                // Options for YES/NO dropdown
                const options = [
                    { value: 'YES', label: 'YES' },
                    { value: 'NO', label: 'NO' }
                ];

                // Render options
                function renderOptions(filteredOptions = options) {
                    optionsContainer.innerHTML = '';
                    filteredOptions.forEach(option => {
                        const optionDiv = document.createElement('div');
                        optionDiv.className = 'px-4 py-2 hover:bg-muted/50 cursor-pointer transition-colors';
                        optionDiv.textContent = option.label;
                        optionDiv.dataset.value = option.value;
                        
                        // Highlight selected option
                        if (hiddenInput.value === option.value) {
                            optionDiv.classList.add('bg-primary/10', 'text-primary', 'font-semibold');
                        }
                        
                        optionDiv.addEventListener('click', function() {
                            hiddenInput.value = option.value;
                            displayText.textContent = option.label;
                            dropdown.classList.add('hidden');
                            if (searchInput) {
                                searchInput.value = '';
                            }
                            renderOptions(options);
                        });
                        
                        optionsContainer.appendChild(optionDiv);
                    });
                }

                // Initial render - ensure display text matches hidden input value
                const currentValue = hiddenInput.value;
                if (currentValue) {
                    const currentOption = options.find(opt => opt.value === currentValue);
                    if (currentOption) {
                        displayText.textContent = currentOption.label;
                    }
                }
                renderOptions();

                // Handle search input if present
                if (searchInput) {
                    searchInput.addEventListener('input', function() {
                        const searchTerm = this.value.toLowerCase();
                        const filteredOptions = options.filter(opt => 
                            opt.label.toLowerCase().includes(searchTerm)
                        );
                        renderOptions(filteredOptions);
                    });
                }

                // Toggle dropdown on trigger click
                trigger.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdown.classList.toggle('hidden');
                    if (!dropdown.classList.contains('hidden') && searchInput) {
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

            // Initialize all YES/NO dropdowns
            initYesNoDropdown(
                'targets_agreed_select_trigger',
                'targets_agreed_dropdown',
                'targets_agreed_options',
                'targets_agreed_id',
                'targets_agreed_select_text',
                'targets_agreed_search_input'
            );

            initYesNoDropdown(
                'duties_agreed_select_trigger',
                'duties_agreed_dropdown',
                'duties_agreed_options',
                'duties_agreed_id',
                'duties_agreed_select_text',
                'duties_agreed_search_input'
            );

            initYesNoDropdown(
                'disciplinary_action_select_trigger',
                'disciplinary_action_dropdown',
                'disciplinary_action_options',
                'disciplinary_action_id',
                'disciplinary_action_select_text',
                'disciplinary_action_search_input'
            );

            initYesNoDropdown(
                'special_commendation_select_trigger',
                'special_commendation_dropdown',
                'special_commendation_options',
                'special_commendation_id',
                'special_commendation_select_text',
                'special_commendation_search_input'
            );

            initYesNoDropdown(
                'suggest_different_job_select_trigger',
                'suggest_different_job_dropdown',
                'suggest_different_job_options',
                'suggest_different_job_id',
                'suggest_different_job_select_text',
                'suggest_different_job_search_input'
            );

            initYesNoDropdown(
                'suggest_transfer_select_trigger',
                'suggest_transfer_dropdown',
                'suggest_transfer_options',
                'suggest_transfer_id',
                'suggest_transfer_select_text',
                'suggest_transfer_search_input'
            );

            // Handle grade radio button changes
            document.querySelectorAll('.grade-group').forEach(group => {
                const radios = group.querySelectorAll('.grade-radio');
                const labels = group.querySelectorAll('.grade-option');
                
                radios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        // Remove active styling from all labels in this group
                        labels.forEach(label => {
                            label.classList.remove('border-primary', 'bg-primary/10', 'text-primary', 'font-semibold');
                            label.classList.add('border-border/50', 'bg-background', 'text-foreground');
                        });
                        
                        // Add active styling to selected label
                        const selectedLabel = this.closest('.grade-option');
                        if (selectedLabel) {
                            selectedLabel.classList.remove('border-border/50', 'bg-background', 'text-foreground');
                            selectedLabel.classList.add('border-primary', 'bg-primary/10', 'text-primary', 'font-semibold');
                        }
                    });
                });
            });

            // Complete & Forward confirmation with validation
            const completeForwardBtn = document.getElementById('complete-forward-btn');
            const mainForm = document.getElementById('reporting-officer-form');

            if (completeForwardBtn && mainForm) {
                completeForwardBtn.addEventListener('click', function (e) {
                    e.preventDefault();

                    // Check for required fields
                    const requiredFields = {
                        'job_understanding_grade': 'Job Understanding',
                        'knowledge_application_grade': 'Knowledge Application',
                        'accomplishment_grade': 'Accomplishment',
                        'judgement_grade': 'Judgement',
                        'work_speed_accuracy_grade': 'Work Speed & Accuracy',
                        'written_expression_grade': 'Written Expression',
                        'oral_expression_grade': 'Oral Expression',
                        'staff_relations_grade': 'Staff Relations',
                        'public_relations_grade': 'Public Relations',
                        'overall_assessment': 'Overall Assessment',
                        'promotability': 'Promotability'
                    };

                    const missingFields = [];
                    for (const [fieldName, fieldLabel] of Object.entries(requiredFields)) {
                        const field = document.querySelector(`input[name="${fieldName}"]:checked`);
                        if (!field) {
                            missingFields.push(fieldLabel);
                        }
                    }

                    if (missingFields.length > 0) {
                        const fieldsList = missingFields.join(', ');
                        const message = missingFields.length === 1
                            ? `Please complete the following required field: ${fieldsList}`
                            : `Please complete the following required fields: ${fieldsList}`;

                        Swal.fire({
                            title: 'Incomplete Assessment',
                            html: `<p>${message}</p><p class="text-sm mt-2 text-secondary-foreground">Please fill in all required assessment grades before forwarding the form.</p>`,
                            icon: 'error',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#dc3545'
                        });
                        return;
                    }

                    // Show confirmation dialog using SweetAlert2
                    Swal.fire({
                        title: 'Complete & Forward Assessment?',
                        text: 'Are you sure you want to complete and forward this assessment to Countersigning Officer? This action cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Complete & Forward',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true,
                        confirmButtonColor: '#10b981',
                        cancelButtonColor: '#6b7280'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Add hidden input to indicate complete and forward action
                            const hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = 'complete_and_forward';
                            hiddenInput.value = '1';
                            mainForm.appendChild(hiddenInput);
                            
                            // Submit the main form with all data
                            mainForm.submit();
                        }
                    });
                });
            }

            // Form submission validation
            const form = document.getElementById('reporting-officer-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // You can add validation here if needed
                    // For now, just show a loading indicator
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="ki-filled ki-loading"></i> Saving...';
                    }
                });
            }
        });
    </script>
@endsection