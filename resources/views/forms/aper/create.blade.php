@extends('layouts.app')

@section('title', 'Create APER Form')
@section('page-title', 'Create APER Form')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.aper-forms') }}">APER Forms</a>
    <span>/</span>
    <span class="text-primary">Create</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
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

    @if($errors->any())
        <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
            <div class="kt-card-content p-4">
                <ul class="list-disc list-inside text-sm text-danger">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Timeline Info -->
    <div class="kt-card bg-info/10 border border-info/20">
        <div class="kt-card-content p-4">
            <div class="flex items-center gap-3">
                <i class="ki-filled ki-information text-info text-xl"></i>
                <div>
                    <p class="text-sm font-medium text-info">APER Timeline: {{ $activeTimeline->year }}</p>
                    <p class="text-xs text-secondary-foreground">
                        Period: {{ $activeTimeline->start_date->format('d/m/Y') }} - {{ $activeTimeline->end_date->format('d/m/Y') }}
                        @if($activeTimeline->days_remaining > 0)
                            | {{ $activeTimeline->days_remaining }} days remaining
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <form action="{{ route('officer.aper-forms.store') }}" method="POST" id="aper-form">
        @csrf
        
        <!-- Part 1: Personal Records -->
        @include('forms.aper.partials.part1-personal-records', ['formData' => $formData ?? [], 'officer' => $officer])
        
        <!-- Part 2: Leave Records, Target Setting, Job Description -->
        @include('forms.aper.partials.part2-leave-targets-job', ['formData' => $formData ?? [], 'activeTimeline' => $activeTimeline])
        
        <!-- Part 3: Training and Job Performance -->
        @include('forms.aper.partials.part3-training-performance', ['formData' => $formData ?? []])

        <!-- Form Actions -->
        <div class="kt-card">
            <div class="kt-card-content">
                <div class="flex items-center justify-between pt-4 border-t border-border">
                    <a href="{{ route('officer.aper-forms') }}" class="kt-btn kt-btn-secondary">
                        <i class="ki-filled ki-arrow-left"></i> Cancel
                    </a>
                    <div class="flex gap-3">
                        <button type="submit" name="action" value="save" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-check"></i> Save Draft
                        </button>
                        <button type="submit" name="action" value="submit" class="kt-btn kt-btn-success">
                            <i class="ki-filled ki-send"></i> Submit Form
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.getElementById('aper-form').addEventListener('submit', function(e) {
    const submitButton = e.submitter;
    if (submitButton && submitButton.value === 'submit') {
        if (!confirm('Are you sure you want to submit this APER form? You will not be able to edit it after submission.')) {
            e.preventDefault();
            return false;
        }
    }
});

// Target Rows Management
let divisionTargetIndex = {{ max(1, count($formData['division_targets'] ?? [])) }};
let individualTargetIndex = {{ max(1, count($formData['individual_targets'] ?? [])) }};

function addTargetRow(type) {
    const container = document.getElementById(type + '-targets-container');
    const row = document.createElement('div');
    row.className = 'target-row flex items-start gap-3';
    
    const romanNumerals = ['I', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii', 'ix', 'x'];
    const index = type === 'division' ? divisionTargetIndex : individualTargetIndex;
    const fieldName = type === 'division' ? 'division_targets' : 'individual_targets';
    
    row.innerHTML = `
        <label class="kt-form-label text-sm pt-2 min-w-[40px]">
            (${romanNumerals[index] || (index + 1)})
        </label>
        <div class="flex-1">
            <textarea name="${fieldName}[${index}]" class="kt-input" rows="2" placeholder="Enter target"></textarea>
        </div>
        <button type="button" class="kt-btn kt-btn-sm kt-btn-danger mt-2 remove-target-row" onclick="removeTargetRow(this, '${type}')">
            <i class="ki-filled ki-cross"></i>
        </button>
    `;
    
    container.appendChild(row);
    if (type === 'division') {
        divisionTargetIndex++;
    } else {
        individualTargetIndex++;
    }
}

function removeTargetRow(button, type) {
    button.closest('.target-row').remove();
}

// Leave Records Management
let sickLeaveIndex = {{ max(1, count($formData['sick_leave_records'] ?? [])) }};
let maternityLeaveIndex = {{ max(1, count($formData['maternity_leave_records'] ?? [])) }};
let annualLeaveIndex = {{ max(1, count($formData['annual_casual_leave_records'] ?? [])) }};

function addLeaveRow(type) {
    let tbody, index, namePrefix;
    
    if (type === 'sick') {
        tbody = document.getElementById('sick-leave-tbody');
        index = sickLeaveIndex++;
        namePrefix = 'sick_leave_records';
    } else if (type === 'maternity') {
        tbody = document.getElementById('maternity-leave-tbody');
        index = maternityLeaveIndex++;
        namePrefix = 'maternity_leave_records';
    } else {
        tbody = document.getElementById('annual-leave-tbody');
        index = annualLeaveIndex++;
        namePrefix = 'annual_casual_leave_records';
    }
    
    const row = document.createElement('tr');
    row.className = 'leave-row';
    
    if (type === 'sick') {
        row.innerHTML = `
            <td class="py-2 px-3">
                <div class="relative">
                    <input type="hidden" name="${namePrefix}[${index}][type]" id="sick_leave_type_${index}_id" value="">
                    <button type="button" 
                            id="sick_leave_type_${index}_select_trigger" 
                            class="kt-input text-sm w-full text-left flex items-center justify-between cursor-pointer">
                        <span id="sick_leave_type_${index}_select_text">Select...</span>
                        <i class="ki-filled ki-down text-gray-400"></i>
                    </button>
                    <div id="sick_leave_type_${index}_dropdown" 
                         class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                        <div class="p-3 border-b border-input">
                            <input type="text" 
                                   id="sick_leave_type_${index}_search_input" 
                                   class="kt-input w-full pl-10 text-sm" 
                                   placeholder="Search..."
                                   autocomplete="off">
                        </div>
                        <div id="sick_leave_type_${index}_options" class="max-h-60 overflow-y-auto"></div>
                    </div>
                </div>
            </td>
            <td class="py-2 px-3">
                <input type="date" name="${namePrefix}[${index}][from]" class="kt-input text-sm">
            </td>
            <td class="py-2 px-3">
                <input type="date" name="${namePrefix}[${index}][to]" class="kt-input text-sm">
            </td>
            <td class="py-2 px-3">
                <input type="number" name="${namePrefix}[${index}][days]" class="kt-input text-sm" min="0">
            </td>
            <td class="py-2 px-3">
                <button type="button" class="kt-btn kt-btn-sm kt-btn-danger remove-leave-row" onclick="removeLeaveRow(this, '${type}')">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </td>
        `;
    } else {
        row.innerHTML = `
            <td class="py-2 px-3">
                <input type="date" name="${namePrefix}[${index}][from]" class="kt-input text-sm">
            </td>
            <td class="py-2 px-3">
                <input type="date" name="${namePrefix}[${index}][to]" class="kt-input text-sm">
            </td>
            <td class="py-2 px-3">
                <input type="number" name="${namePrefix}[${index}][days]" class="kt-input text-sm" min="0">
            </td>
            <td class="py-2 px-3">
                <button type="button" class="kt-btn kt-btn-sm kt-btn-danger remove-leave-row" onclick="removeLeaveRow(this, '${type}')">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </td>
        `;
    }
    
    tbody.appendChild(row);
    
    // Initialize searchable select for sick leave type if it's a sick leave row
    if (type === 'sick') {
        const sickLeaveTypeOptions = [
            {id: '', name: 'Select...'},
            {id: 'Hospitalisation', name: 'Hospitalisation'},
            {id: 'Treatment Abroad', name: 'Treatment Received Abroad'},
            {id: 'Sick Leave', name: 'Sick Leave'}
        ];
        
        createSearchableSelect({
            triggerId: `sick_leave_type_${index}_select_trigger`,
            hiddenInputId: `sick_leave_type_${index}_id`,
            dropdownId: `sick_leave_type_${index}_dropdown`,
            searchInputId: `sick_leave_type_${index}_search_input`,
            optionsContainerId: `sick_leave_type_${index}_options`,
            displayTextId: `sick_leave_type_${index}_select_text`,
            options: sickLeaveTypeOptions,
            placeholder: 'Select...',
            searchPlaceholder: 'Search...'
        });
    }
}

function removeLeaveRow(button, type) {
    button.closest('tr').remove();
}

// Training Courses Management
let trainingIndex = {{ max(1, count($formData['training_courses'] ?? [])) }};

function addTrainingRow() {
    const tbody = document.getElementById('training-courses-tbody');
    const row = document.createElement('tr');
    row.className = 'training-row';
    
    row.innerHTML = `
        <td class="py-2 px-3">
            <input type="text" name="training_courses[${trainingIndex}][type]" class="kt-input text-sm" placeholder="Training type">
        </td>
        <td class="py-2 px-3">
            <input type="text" name="training_courses[${trainingIndex}][where]" class="kt-input text-sm" placeholder="Location">
        </td>
        <td class="py-2 px-3">
            <input type="date" name="training_courses[${trainingIndex}][from]" class="kt-input text-sm">
        </td>
        <td class="py-2 px-3">
            <input type="date" name="training_courses[${trainingIndex}][to]" class="kt-input text-sm">
        </td>
        <td class="py-2 px-3">
            <button type="button" class="kt-btn kt-btn-sm kt-btn-danger remove-training-row" onclick="removeTrainingRow(this)">
                <i class="ki-filled ki-cross"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
    trainingIndex++;
}

function removeTrainingRow(button) {
    button.closest('tr').remove();
}

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

// Initialize all searchable selects on page load
document.addEventListener('DOMContentLoaded', function() {
    // YES/NO options
    const yesNoOptions = [
        {id: '', name: 'Select...'},
        {id: 'YES', name: 'YES'},
        {id: 'NO', name: 'NO'}
    ];

    const yesNoOptionsWithLabel = [
        {id: '', name: '-- Select YES or NO --'},
        {id: 'YES', name: 'YES'},
        {id: 'NO', name: 'NO'}
    ];

    // Title options
    const titleOptions = [
        {id: '', name: 'Select...'},
        {id: 'Mr', name: 'Mr.'},
        {id: 'Mrs', name: 'Mrs.'},
        {id: 'Miss', name: 'Miss.'}
    ];

    // Cadre options
    const cadreOptions = [
        {id: '', name: 'Select...'},
        {id: 'GD', name: 'GD'},
        {id: 'SS', name: 'SS'}
    ];

    // Sick leave type options
    const sickLeaveTypeOptions = [
        {id: '', name: 'Select...'},
        {id: 'Hospitalisation', name: 'Hospitalisation'},
        {id: 'Treatment Abroad', name: 'Treatment Received Abroad'},
        {id: 'Sick Leave', name: 'Sick Leave'}
    ];

    // Initialize Part 1 selects
    if (document.getElementById('title_select_trigger')) {
        createSearchableSelect({
            triggerId: 'title_select_trigger',
            hiddenInputId: 'title_id',
            dropdownId: 'title_dropdown',
            searchInputId: 'title_search_input',
            optionsContainerId: 'title_options',
            displayTextId: 'title_select_text',
            options: titleOptions,
            placeholder: 'Select...',
            searchPlaceholder: 'Search...'
        });
    }

    if (document.getElementById('cadre_select_trigger')) {
        createSearchableSelect({
            triggerId: 'cadre_select_trigger',
            hiddenInputId: 'cadre_id',
            dropdownId: 'cadre_dropdown',
            searchInputId: 'cadre_search_input',
            optionsContainerId: 'cadre_options',
            displayTextId: 'cadre_select_text',
            options: cadreOptions,
            placeholder: 'Select...',
            searchPlaceholder: 'Search...'
        });
    }

    // Initialize Part 2 selects
    const part2Selects = [
        {id: 'joint_discussion', options: yesNoOptions},
        {id: 'properly_equipped', options: yesNoOptions},
        {id: 'performance_measure_up', options: yesNoOptions},
        {id: 'adhoc_affected_duties', options: yesNoOptions}
    ];

    part2Selects.forEach(select => {
        if (document.getElementById(`${select.id}_select_trigger`)) {
            createSearchableSelect({
                triggerId: `${select.id}_select_trigger`,
                hiddenInputId: `${select.id}_id`,
                dropdownId: `${select.id}_dropdown`,
                searchInputId: `${select.id}_search_input`,
                optionsContainerId: `${select.id}_options`,
                displayTextId: `${select.id}_select_text`,
                options: select.options,
                placeholder: 'Select...',
                searchPlaceholder: 'Search...'
            });
        }
    });

    // Initialize existing sick leave type selects in the table
    document.querySelectorAll('[id^="sick_leave_type_"][id$="_select_trigger"]').forEach(trigger => {
        const match = trigger.id.match(/sick_leave_type_(\d+)_select_trigger/);
        if (match) {
            const index = match[1];
            createSearchableSelect({
                triggerId: `sick_leave_type_${index}_select_trigger`,
                hiddenInputId: `sick_leave_type_${index}_id`,
                dropdownId: `sick_leave_type_${index}_dropdown`,
                searchInputId: `sick_leave_type_${index}_search_input`,
                optionsContainerId: `sick_leave_type_${index}_options`,
                displayTextId: `sick_leave_type_${index}_select_text`,
                options: sickLeaveTypeOptions,
                placeholder: 'Select...',
                searchPlaceholder: 'Search...'
            });
        }
    });

    // Initialize Part 3 selects
    const part3Selects = [
        {id: 'effective_use_capabilities', options: yesNoOptions},
        {id: 'job_satisfaction', options: yesNoOptions}
    ];

    part3Selects.forEach(select => {
        if (document.getElementById(`${select.id}_select_trigger`)) {
            createSearchableSelect({
                triggerId: `${select.id}_select_trigger`,
                hiddenInputId: `${select.id}_id`,
                dropdownId: `${select.id}_dropdown`,
                searchInputId: `${select.id}_search_input`,
                optionsContainerId: `${select.id}_options`,
                displayTextId: `${select.id}_select_text`,
                options: select.options,
                placeholder: 'Select...',
                searchPlaceholder: 'Search...'
            });
        }
    });

    // Initialize Reporting Officer selects
    const reportingOfficerSelects = [
        {id: 'targets_agreed', options: yesNoOptionsWithLabel},
        {id: 'duties_agreed', options: yesNoOptionsWithLabel},
        {id: 'disciplinary_action', options: yesNoOptionsWithLabel},
        {id: 'special_commendation', options: yesNoOptionsWithLabel},
        {id: 'suggest_different_job', options: yesNoOptionsWithLabel},
        {id: 'suggest_transfer', options: yesNoOptionsWithLabel}
    ];

    reportingOfficerSelects.forEach(select => {
        if (document.getElementById(`${select.id}_select_trigger`)) {
            createSearchableSelect({
                triggerId: `${select.id}_select_trigger`,
                hiddenInputId: `${select.id}_id`,
                dropdownId: `${select.id}_dropdown`,
                searchInputId: `${select.id}_search_input`,
                optionsContainerId: `${select.id}_options`,
                displayTextId: `${select.id}_select_text`,
                options: select.options,
                placeholder: select.options[0].name,
                searchPlaceholder: 'Search...'
            });
        }
    });
});
</script>
@endsection

