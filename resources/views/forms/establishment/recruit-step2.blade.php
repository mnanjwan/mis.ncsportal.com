@extends('layouts.public')

@section('title', 'Recruit Onboarding - Step 2: Employment Details')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Progress Indicator -->
    <div class="kt-card">
        <div class="kt-card-content p-4 lg:p-5">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-2">
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full flex items-center justify-center font-semibold text-sm" style="background-color: #28a745; color: white;">✓</div>
                    <span class="text-xs sm:text-sm font-medium" style="color: #28a745;">Personal Information</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full flex items-center justify-center font-semibold text-sm" style="background-color: #068b57; color: white;">2</div>
                    <span class="text-xs sm:text-sm font-medium" style="color: #068b57;">Employment Details</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full flex items-center justify-center font-semibold text-sm" style="background-color: #6c757d; color: white;">3</div>
                    <span class="text-xs sm:text-sm" style="color: #6c757d;">Banking Information</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full flex items-center justify-center font-semibold text-sm" style="background-color: #6c757d; color: white;">4</div>
                    <span class="text-xs sm:text-sm" style="color: #6c757d;">Next of Kin</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full flex items-center justify-center font-semibold text-sm" style="background-color: #6c757d; color: white;">5</div>
                    <span class="text-xs sm:text-sm" style="color: #6c757d;">Preview</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Form -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Employment Details</h3>
        </div>
        <div class="kt-card-content">
            @if($errors->any())
            <div class="kt-alert kt-alert-danger mb-5">
                <div class="kt-alert-content">
                    <strong class="text-danger">Please fix the following errors:</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach($errors->all() as $error)
                        <li class="text-danger">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
            
            <form id="recruit-step2-form" method="POST" action="{{ route('recruit.onboarding.step2.save') }}" enctype="multipart/form-data" class="flex flex-col gap-5 w-full overflow-hidden">
                @csrf
                <input type="hidden" name="token" value="{{ request('token') ?? session('recruit_onboarding_token') }}">
                
                <div class="grid lg:grid-cols-2 gap-5">
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Date of First Appointment <span class="text-danger">*</span></label>
                        @php
                            $firstAppointment = old('date_of_first_appointment', $savedData['date_of_first_appointment'] ?? ($recruit && $recruit->date_of_first_appointment ? \Carbon\Carbon::parse($recruit->date_of_first_appointment)->format('Y-m-d') : ''));
                        @endphp
                        <input type="text" class="kt-input" value="{{ $firstAppointment }}" readonly/>
                        <input type="hidden" name="date_of_first_appointment" value="{{ $firstAppointment }}">
                        <span class="error-message text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Date of Present Appointment <span class="text-danger">*</span></label>
                        @php
                            $presentAppointment = old('date_of_present_appointment', $savedData['date_of_present_appointment'] ?? ($recruit && $recruit->date_of_present_appointment ? \Carbon\Carbon::parse($recruit->date_of_present_appointment)->format('Y-m-d') : ''));
                        @endphp
                        <input type="text" class="kt-input" value="{{ $presentAppointment }}" readonly/>
                        <input type="hidden" name="date_of_present_appointment" value="{{ $presentAppointment }}">
                        <span class="error-message text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Substantive Rank <span class="text-danger">*</span></label>
                        @php
                            $rankValue = old('substantive_rank', $savedData['substantive_rank'] ?? ($recruit && $recruit->substantive_rank ? $recruit->substantive_rank : ''));
                        @endphp
                        <input type="text" id="substantive_rank" class="kt-input" value="{{ $rankValue }}" readonly/>
                        <input type="hidden" name="substantive_rank" value="{{ $rankValue }}">
                        <span class="error-message text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Salary Grade Level <span class="text-danger">*</span></label>
                        @php
                            $gradeLevelValue = old('salary_grade_level', $savedData['salary_grade_level'] ?? ($recruit && $recruit->salary_grade_level ? $recruit->salary_grade_level : ''));
                        @endphp
                        <input type="text" id="salary_grade_level" class="kt-input" value="{{ $gradeLevelValue }}" readonly/>
                        <input type="hidden" name="salary_grade_level" value="{{ $gradeLevelValue }}">
                        <span class="error-message text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Zone <span class="text-danger">*</span></label>
                        @php
                            $zoneId = old('zone_id', $savedData['zone_id'] ?? '');
                            $zoneName = '';
                            if ($zoneId && isset($zones)) {
                                $selectedZone = $zones->firstWhere('id', $zoneId);
                                $zoneName = $selectedZone ? $selectedZone->name : '';
                            }
                        @endphp
                        <input type="text" id="zone_id_display" class="kt-input" value="{{ $zoneName }}" readonly/>
                        <input type="hidden" name="zone_id" id="zone_id" value="{{ $zoneId }}" required>
                        <span class="error-message text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Command/Present Station <span class="text-danger">*</span></label>
                        @php
                            $commandId = old('command_id', $savedData['command_id'] ?? '');
                            $commandName = '';
                            if ($commandId && isset($commands)) {
                                $selectedCommand = collect($commands)->firstWhere('id', $commandId);
                                $commandName = $selectedCommand ? $selectedCommand->name : '';
                            }
                        @endphp
                        <input type="text" id="command_display" class="kt-input" value="{{ $commandName }}" readonly/>
                        <input type="hidden" name="command_id" id="command_id" value="{{ $commandId }}" required>
                        <span class="error-message text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Date Posted to Station <span class="text-danger">*</span></label>
                        @php
                            $datePosted = old('date_posted_to_station', $savedData['date_posted_to_station'] ?? ($recruit && $recruit->date_posted_to_station ? \Carbon\Carbon::parse($recruit->date_posted_to_station)->format('Y-m-d') : ''));
                        @endphp
                        <input type="text" class="kt-input" value="{{ $datePosted }}" readonly/>
                        <input type="hidden" name="date_posted_to_station" value="{{ $datePosted }}">
                        <span class="error-message text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Unit</label>
                        @php
                            $unitValue = old('unit', $savedData['unit'] ?? ($recruit && $recruit->unit ? $recruit->unit : ''));
                        @endphp
                        <input type="text" class="kt-input" value="{{ $unitValue }}" readonly/>
                        <input type="hidden" name="unit" value="{{ $unitValue }}">
                        <span class="error-message text-sm hidden"></span>
                    </div>
                </div>
                
                <!-- Education Section -->
                <div class="flex flex-col gap-5 pt-5 border-t border-input">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Education</h3>
                        <button type="button" id="add-education-btn" class="kt-btn kt-btn-sm kt-btn-primary">
                            <i class="ki-filled ki-plus"></i> Add Education
                        </button>
                    </div>
                    
                    <div id="education-entries" class="flex flex-col gap-5">
                        <!-- Education entries will be added here dynamically -->
                    </div>
                </div>
                
                <!-- Document Upload Section -->
                <div class="flex flex-col gap-1 pt-5 border-t border-input">
                    <label class="kt-form-label">Upload Documents <span class="text-danger">*</span> <span class="text-muted">(Preferably in JPEG format)</span></label>
                    <div class="flex flex-col gap-3">
                        <div class="relative">
                            <input type="file" id="documents-input" name="documents[]" class="hidden" multiple accept="image/jpeg,image/jpg,image/png"/>
                            <label for="documents-input" class="kt-btn kt-btn-primary cursor-pointer inline-flex items-center justify-center gap-2">
                                <i class="ki-filled ki-file-up"></i>
                                <span id="upload-button-text">Choose Files</span>
                            </label>
                        </div>
                        <div id="selected-files-list" class="flex flex-col gap-2 hidden">
                            <!-- Selected files will be displayed here -->
                        </div>
                        <small class="text-muted">You can upload multiple documents. JPEG format is preferred to save space. <span class="text-danger">At least one document is required.</span></small>
                        <span class="text-xs" style="color: red;">
                            <strong>Document Type Allowed:</strong> JPEG, JPG, PNG (multiple files allowed)<br>
                            <strong>Document Size Allowed:</strong> Maximum 5MB per file
                        </span>
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-5 border-t border-input">
                    <a href="{{ route('recruit.onboarding.step1', ['token' => request('token') ?? session('recruit_onboarding_token')]) }}" class="kt-btn kt-btn-secondary w-full sm:flex-1 whitespace-nowrap">Previous</a>
                    <button type="submit" class="kt-btn kt-btn-primary w-full sm:flex-1 whitespace-nowrap">Next: Banking Information</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Ensure all asterisks in onboarding forms are red */
    .kt-form-label span.text-danger,
    .kt-form-label .text-danger,
    label span.text-danger,
    label .text-danger {
        color: #dc3545 !important;
    }
    
    /* Error messages should be red only when visible (not hidden) */
    .error-message:not(.hidden) {
        color: #dc3545 !important;
    }
    
    /* Laravel validation errors */
    .kt-alert-danger,
    .kt-alert-danger strong,
    .kt-alert-danger li,
    .kt-alert-danger p {
        color: #dc3545 !important;
    }
</style>
@endpush

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
    // Zone and Command are readonly and pre-populated server-side
    // No JavaScript needed for these fields
    
    // Initialize education entries
    initializeEducationSection();
    
    // Initialize document upload
    loadSavedDocuments();
});

// Institution master list (from DB)
const institutionMasterList = @json($institutions ?? []);

// Qualifications List - Entry Qualifications prioritized
const qualifications = [
    'WAEC',
    'NECO',
    'NABTEB',
    'HND',
    'OND',
    'PhD',
    'MBBS',
    'MSc',
    'MPhil',
    'MA',
    'B TECH',
    'BA',
    'BSc',
    'TRADE TEST',
    'DSc',
    'DPharm',
    'D Litt',
    'DDS',
    'DA',
    'MMed',
    'MEng',
    'BArch',
    'LLM',
    'LLB',
    'MBA',
    'BEd',
    'BPharm',
    'BVSc',
    'DVM',
    'BDS',
    'BEng',
    'BTech',
    'BBA',
    'BCom',
    'BFA',
    'BPE',
    'BSc (Ed)',
    'PGD',
    'PGDE',
    'Other'
];

// Discipline master list (from DB)
const disciplineMasterList = @json($disciplines ?? []);

const ADD_NEW_VALUE = '__ADD_NEW__';

let educationEntryCount = 0;

function initializeEducationSection() {
    const addBtn = document.getElementById('add-education-btn');
    const entriesContainer = document.getElementById('education-entries');
    
    // Load saved education entries
    const savedEducation = @json(old('education', $savedData['education'] ?? []));
    
    if (savedEducation && Array.isArray(savedEducation) && savedEducation.length > 0) {
        // Handle both array of objects and object with numeric keys
        const educationArray = Array.isArray(savedEducation) 
            ? savedEducation 
            : Object.values(savedEducation);
            
        educationArray.forEach(edu => {
            if (edu && (edu.university || edu.qualification)) {
                addEducationEntry(edu);
            }
        });
    }
    
    // If no saved entries, add one empty entry by default
    if (entriesContainer.children.length === 0) {
        addEducationEntry();
    }
    
    addBtn.addEventListener('click', () => {
        addEducationEntry();
    });
}

function addEducationEntry(data = null) {
    const entriesContainer = document.getElementById('education-entries');
    const entryId = educationEntryCount++;
    
    const entryDiv = document.createElement('div');
    entryDiv.className = 'kt-card p-5 border border-input rounded-lg';
    entryDiv.dataset.entryId = entryId;
    
    const savedUniversity = data && data.university ? data.university : '';
    const savedQualification = data && data.qualification ? data.qualification : '';
    const savedDiscipline = data && data.discipline ? data.discipline : '';
    const savedYearObtained = data && data.year_obtained ? data.year_obtained : '';
    
    entryDiv.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="flex flex-col gap-1">
                <label class="kt-form-label">Institution <span class="text-danger">*</span></label>
                <div class="relative">
                    <input type="hidden" name="education[${entryId}][university]" id="education_university_${entryId}_id" value="${savedUniversity}" required>
                    <button type="button" 
                            id="education_university_${entryId}_select_trigger" 
                            class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                        <span id="education_university_${entryId}_select_text">${savedUniversity ? savedUniversity : '-- Select Institution --'}</span>
                        <i class="ki-filled ki-down text-gray-400"></i>
                    </button>
                    <div id="education_university_${entryId}_dropdown" 
                         class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                        <div class="p-3 border-b border-input">
                            <input type="text" 
                                   id="education_university_${entryId}_search_input" 
                                   class="kt-input w-full pl-10" 
                                   placeholder="Search institution..."
                                   autocomplete="off">
                        </div>
                        <div id="education_university_${entryId}_options" class="max-h-60 overflow-y-auto"></div>
                    </div>
                    <input type="text"
                           id="education_university_${entryId}_custom"
                           class="kt-input mt-2 hidden"
                           placeholder="Type institution name..."
                           autocomplete="off">
                </div>
                <span class="error-message text-danger text-sm hidden"></span>
            </div>
            <div class="flex flex-col gap-1">
                <label class="kt-form-label">Entry Qualification <span class="text-danger">*</span></label>
                <div class="relative">
                    <input type="hidden" name="education[${entryId}][qualification]" id="education_qualification_${entryId}_id" value="${savedQualification}" required>
                    <button type="button" 
                            id="education_qualification_${entryId}_select_trigger" 
                            class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                        <span id="education_qualification_${entryId}_select_text">${savedQualification ? savedQualification : '-- Select Qualification --'}</span>
                        <i class="ki-filled ki-down text-gray-400"></i>
                    </button>
                    <div id="education_qualification_${entryId}_dropdown" 
                         class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                        <div class="p-3 border-b border-input">
                            <input type="text" 
                                   id="education_qualification_${entryId}_search_input" 
                                   class="kt-input w-full pl-10" 
                                   placeholder="Search qualification..."
                                   autocomplete="off">
                        </div>
                        <div id="education_qualification_${entryId}_options" class="max-h-60 overflow-y-auto"></div>
                    </div>
                </div>
                <span class="error-message text-danger text-sm hidden"></span>
            </div>
            <div class="flex flex-col gap-1">
                <label class="kt-form-label">Year Obtained <span class="text-danger">*</span></label>
                <input type="number" 
                       name="education[${entryId}][year_obtained]" 
                       class="kt-input education-year-obtained" 
                       value="${savedYearObtained}"
                       placeholder="e.g., 2020"
                       min="1950"
                       max="${new Date().getFullYear()}"
                       required>
                <span class="error-message text-danger text-sm hidden"></span>
            </div>
            <div class="flex flex-col gap-1">
                <label class="kt-form-label">Discipline <span class="text-muted">(Optional)</span></label>
                <div class="relative">
                    <input type="hidden" name="education[${entryId}][discipline]" id="education_discipline_${entryId}_id" value="${savedDiscipline}">
                    <button type="button" 
                            id="education_discipline_${entryId}_select_trigger" 
                            class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                        <span id="education_discipline_${entryId}_select_text">${savedDiscipline ? savedDiscipline : '-- Select Discipline --'}</span>
                        <i class="ki-filled ki-down text-gray-400"></i>
                    </button>
                    <div id="education_discipline_${entryId}_dropdown" 
                         class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                        <div class="p-3 border-b border-input">
                            <input type="text" 
                                   id="education_discipline_${entryId}_search_input" 
                                   class="kt-input w-full pl-10" 
                                   placeholder="Search discipline..."
                                   autocomplete="off">
                        </div>
                        <div id="education_discipline_${entryId}_options" class="max-h-60 overflow-y-auto"></div>
                    </div>
                    <input type="text"
                           id="education_discipline_${entryId}_custom"
                           class="kt-input mt-2 hidden"
                           placeholder="Type discipline (optional)..."
                           autocomplete="off">
                </div>
                <span class="error-message text-danger text-sm hidden"></span>
            </div>
        </div>
        <div class="flex items-center justify-end mt-3">
            <button type="button" class="kt-btn kt-btn-sm kt-btn-danger remove-education-btn" onclick="removeEducationEntry(${entryId})">
                <i class="ki-filled ki-trash"></i> Remove
            </button>
        </div>
    `;
    
    entriesContainer.appendChild(entryDiv);
    
    // Initialize all education selects using createSearchableSelect
    if (typeof createSearchableSelect === 'function') {
        // Initialize Institution select
        const institutionOptions = [
            {id: '', name: '-- Select Institution --'},
            {id: ADD_NEW_VALUE, name: '-- Add New Institution (type below) --'},
            ...institutionMasterList.map(name => ({id: name, name: name}))
        ];
        
        createSearchableSelect({
            triggerId: `education_university_${entryId}_select_trigger`,
            hiddenInputId: `education_university_${entryId}_id`,
            dropdownId: `education_university_${entryId}_dropdown`,
            searchInputId: `education_university_${entryId}_search_input`,
            optionsContainerId: `education_university_${entryId}_options`,
            displayTextId: `education_university_${entryId}_select_text`,
            options: institutionOptions,
            placeholder: '-- Select Institution --',
            searchPlaceholder: 'Search institution...',
            onSelect: function(option) {
                const hidden = document.getElementById(`education_university_${entryId}_id`);
                const customInput = document.getElementById(`education_university_${entryId}_custom`);
                const displayText = document.getElementById(`education_university_${entryId}_select_text`);

                if (!hidden || !customInput || !displayText) return;

                if (option.id === ADD_NEW_VALUE) {
                    customInput.classList.remove('hidden');
                    customInput.value = (hidden.value && hidden.value !== ADD_NEW_VALUE) ? hidden.value : '';
                    hidden.value = customInput.value.trim();
                    displayText.textContent = '-- Add New Institution (type below) --';
                    setTimeout(() => customInput.focus(), 0);
                } else {
                    customInput.classList.add('hidden');
                    customInput.value = '';
                }
            }
        });

        const institutionCustomInput = document.getElementById(`education_university_${entryId}_custom`);
        const institutionHidden = document.getElementById(`education_university_${entryId}_id`);
        if (institutionCustomInput && institutionHidden) {
            institutionCustomInput.addEventListener('input', function() {
                institutionHidden.value = this.value.trim();
            });
        }
        
        // Initialize Entry Qualification select
        const qualificationOptions = [
            {id: '', name: '-- Select Qualification --'},
            ...qualifications.map(qual => ({id: qual, name: qual}))
        ];
        
        createSearchableSelect({
            triggerId: `education_qualification_${entryId}_select_trigger`,
            hiddenInputId: `education_qualification_${entryId}_id`,
            dropdownId: `education_qualification_${entryId}_dropdown`,
            searchInputId: `education_qualification_${entryId}_search_input`,
            optionsContainerId: `education_qualification_${entryId}_options`,
            displayTextId: `education_qualification_${entryId}_select_text`,
            options: qualificationOptions,
            placeholder: '-- Select Qualification --',
            searchPlaceholder: 'Search qualification...'
        });
        
        // Initialize Discipline select
        const disciplineOptions = [
            {id: '', name: '-- Select Discipline --'},
            {id: ADD_NEW_VALUE, name: '-- Add New Discipline (type below) --'},
            ...disciplineMasterList.map(name => ({id: name, name: name}))
        ];
        
        createSearchableSelect({
            triggerId: `education_discipline_${entryId}_select_trigger`,
            hiddenInputId: `education_discipline_${entryId}_id`,
            dropdownId: `education_discipline_${entryId}_dropdown`,
            searchInputId: `education_discipline_${entryId}_search_input`,
            optionsContainerId: `education_discipline_${entryId}_options`,
            displayTextId: `education_discipline_${entryId}_select_text`,
            options: disciplineOptions,
            placeholder: '-- Select Discipline --',
            searchPlaceholder: 'Search discipline...',
            onSelect: function(option) {
                const hidden = document.getElementById(`education_discipline_${entryId}_id`);
                const customInput = document.getElementById(`education_discipline_${entryId}_custom`);
                const displayText = document.getElementById(`education_discipline_${entryId}_select_text`);

                if (!hidden || !customInput || !displayText) return;

                if (option.id === ADD_NEW_VALUE) {
                    customInput.classList.remove('hidden');
                    customInput.value = (hidden.value && hidden.value !== ADD_NEW_VALUE) ? hidden.value : '';
                    hidden.value = customInput.value.trim();
                    displayText.textContent = '-- Add New Discipline (type below) --';
                    setTimeout(() => customInput.focus(), 0);
                } else {
                    customInput.classList.add('hidden');
                    customInput.value = '';
                }
            }
        });

        const disciplineCustomInput = document.getElementById(`education_discipline_${entryId}_custom`);
        const disciplineHidden = document.getElementById(`education_discipline_${entryId}_id`);
        if (disciplineCustomInput && disciplineHidden) {
            disciplineCustomInput.addEventListener('input', function() {
                disciplineHidden.value = this.value.trim();
            });
        }
    }
}

function removeEducationEntry(entryId) {
    const entry = document.querySelector(`[data-entry-id="${entryId}"]`);
    if (entry) {
        entry.remove();
    }
    
    // If no entries left, add one
    const entriesContainer = document.getElementById('education-entries');
    if (entriesContainer.children.length === 0) {
        addEducationEntry();
    }
}

// Initialize university search functionality
function initializeUniversitySearch(entryId) {
    const universityInput = document.getElementById(`university_search_${entryId}`);
    const universityDropdown = document.getElementById(`university_dropdown_${entryId}`);
    
    if (!universityInput || !universityDropdown) return;
    
    universityInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        
        if (searchTerm.length === 0) {
            universityDropdown.classList.add('hidden');
            return;
        }
        
        const filtered = institutionMasterList.filter(uni =>
            String(uni).toLowerCase().includes(searchTerm)
        );
        
        if (filtered.length > 0) {
            universityDropdown.innerHTML = filtered.map(uni => 
                '<div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0" ' +
                'data-value="' + uni + '">' + uni + '</div>'
            ).join('');
            universityDropdown.classList.remove('hidden');
        } else {
            universityDropdown.classList.add('hidden');
        }
    });
    
    // Handle selection from dropdown
    universityDropdown.addEventListener('click', function(e) {
        const option = e.target.closest('[data-value]');
        if (option) {
            const selectedValue = option.dataset.value;
            universityInput.value = selectedValue;
            universityDropdown.classList.add('hidden');
            clearError(`education[${entryId}][university]`);
        }
    });
    
    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!universityInput.contains(e.target) && !universityDropdown.contains(e.target)) {
            universityDropdown.classList.add('hidden');
        }
    });
    
    // Allow free text input (user can type custom university)
    universityInput.addEventListener('blur', function() {
        // Small delay to allow dropdown click to register
        setTimeout(() => {
            universityDropdown.classList.add('hidden');
        }, 200);
    });
}

// Initialize discipline search functionality
function initializeDisciplineSearch(entryId) {
    const disciplineInput = document.getElementById(`discipline_search_${entryId}`);
    const disciplineDropdown = document.getElementById(`discipline_dropdown_${entryId}`);
    const disciplineHidden = document.getElementById(`discipline_hidden_${entryId}`);
    
    if (!disciplineInput || !disciplineDropdown || !disciplineHidden) return;
    
    disciplineInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        
        // Update hidden field with current value
        disciplineHidden.value = this.value.trim();
        
        if (searchTerm.length === 0) {
            disciplineDropdown.classList.add('hidden');
            return;
        }
        
        const filtered = disciplineMasterList.filter(disc =>
            String(disc).toLowerCase().includes(searchTerm)
        );
        
        if (filtered.length > 0) {
            disciplineDropdown.innerHTML = filtered.map(disc => 
                '<div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0" ' +
                'data-value="' + disc + '">' + disc + '</div>'
            ).join('');
            disciplineDropdown.classList.remove('hidden');
        } else {
            disciplineDropdown.classList.add('hidden');
        }
    });
    
    // Handle selection from dropdown
    disciplineDropdown.addEventListener('click', function(e) {
        const option = e.target.closest('[data-value]');
        if (option) {
            const selectedValue = option.dataset.value;
            disciplineInput.value = selectedValue;
            disciplineHidden.value = selectedValue;
            disciplineDropdown.classList.add('hidden');
        }
    });
    
    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!disciplineInput.contains(e.target) && !disciplineDropdown.contains(e.target)) {
            disciplineDropdown.classList.add('hidden');
        }
    });
    
    // Allow free text input (user can type custom discipline)
    disciplineInput.addEventListener('blur', function() {
        // Small delay to allow dropdown click to register
        setTimeout(() => {
            disciplineDropdown.classList.add('hidden');
            // Ensure hidden field is updated
            disciplineHidden.value = this.value.trim();
        }, 200);
    });
}

function loadCommandsForZone(zoneId, savedCommandId = null) {
    // Filter commands by zone - check both zone_id and zone.id
    window.commands = window.allCommands.filter(cmd => {
        const cmdZoneId = cmd.zone?.id || cmd.zone_id;
        return cmdZoneId == zoneId;
    });
    
    // Clear previous command selection
    clearCommandSelection();
    
    // Enable command search
    const commandSearch = document.getElementById('command_search');
    commandSearch.readOnly = false;
    commandSearch.placeholder = 'Search command...';
    
    // Re-initialize searchable select with filtered commands
    initializeCommandSearch();
    
    // If saved command exists and belongs to this zone, set it
    if (savedCommandId) {
        const savedCmd = window.commands.find(c => c.id == savedCommandId);
        if (savedCmd) {
            commandSearch.value = savedCmd.name;
            document.getElementById('command_id').value = savedCmd.id;
            document.getElementById('selected_command_name').textContent = savedCmd.name;
            document.getElementById('selected_command').classList.remove('hidden');
        }
    }
}

function initializeCommandSearch() {
    const commandSearch = document.getElementById('command_search');
    const commandId = document.getElementById('command_id');
    const commandDropdown = document.getElementById('command_dropdown');
    const selectedCommand = document.getElementById('selected_command');
    const selectedCommandName = document.getElementById('selected_command_name');
    
    commandSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const commands = window.commands || [];
        
        const filtered = commands.filter(cmd => 
            cmd.name.toLowerCase().includes(searchTerm)
        );
        
        if (filtered.length > 0 && searchTerm.length > 0) {
            commandDropdown.innerHTML = filtered.map(cmd => 
                '<div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0" ' +
                'data-id="' + cmd.id + '" ' +
                'data-name="' + cmd.name + '">' + cmd.name + '</div>'
            ).join('');
            commandDropdown.classList.remove('hidden');
        } else {
            commandDropdown.classList.add('hidden');
        }
    });
    
    commandDropdown.addEventListener('click', function(e) {
        const option = e.target.closest('[data-id]');
        if (option) {
            const cmdId = option.dataset.id;
            const cmdName = option.dataset.name;
            commandId.value = cmdId;
            commandSearch.value = cmdName;
            selectedCommandName.textContent = cmdName;
            selectedCommand.classList.remove('hidden');
            commandDropdown.classList.add('hidden');
            clearError('command_id');
        }
    });
    
    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!commandSearch.contains(e.target) && !commandDropdown.contains(e.target)) {
            commandDropdown.classList.add('hidden');
        }
    });
}

function clearCommandSelection() {
    const commandSearch = document.getElementById('command_search');
    const commandId = document.getElementById('command_id');
    const selectedCommand = document.getElementById('selected_command');
    
    commandSearch.value = '';
    commandId.value = '';
    selectedCommand.classList.add('hidden');
    
    // Reset command search to readonly if no zone selected
    const zoneId = document.getElementById('zone_id').value;
    if (!zoneId) {
        commandSearch.readOnly = true;
        commandSearch.placeholder = 'Select zone first, then search command...';
    }
    
    clearError('command_id');
}

// Validation functions
function showError(field, message) {
    const input = document.querySelector(`[name="${field}"]`);
    const errorSpan = input?.parentElement?.querySelector('.error-message');
    if (errorSpan) {
        errorSpan.textContent = message;
        errorSpan.classList.remove('hidden');
        input?.classList.add('border-danger');
    }
}

function clearError(field) {
    const input = document.querySelector(`[name="${field}"]`);
    const errorSpan = input?.parentElement?.querySelector('.error-message');
    if (errorSpan) {
        errorSpan.textContent = '';
        errorSpan.classList.add('hidden');
        input?.classList.remove('border-danger');
    }
}

function validateStep2() {
    let isValid = true;
    
    const requiredFields = {
        'date_of_first_appointment': 'Date of First Appointment is required',
        'date_of_present_appointment': 'Date of Present Appointment is required',
        'substantive_rank': 'Substantive Rank is required',
        'salary_grade_level': 'Salary Grade Level is required',
        'zone_id': 'Zone is required',
        'command_id': 'Command/Present Station is required',
        'date_posted_to_station': 'Date Posted to Station is required'
    };
    
    // Validate education entries
    const educationCards = document.querySelectorAll('#education-entries .kt-card');
    let hasEducationError = false;
    
    educationCards.forEach((card, index) => {
        const entryId = card.dataset.entryId;
        const university = document.getElementById(`education_university_${entryId}_id`);
        const qualification = document.getElementById(`education_qualification_${entryId}_id`);
        const yearObtained = card.querySelector('.education-year-obtained');
        
        if (!university || !university.value.trim()) {
            const errorSpan = university?.parentElement?.querySelector('.error-message');
            if (errorSpan) {
                errorSpan.textContent = 'Institution is required';
                errorSpan.classList.remove('hidden');
                const trigger = document.getElementById(`education_university_${entryId}_select_trigger`);
                trigger?.classList.add('border-danger');
            }
            isValid = false;
            hasEducationError = true;
        }
        
        if (!qualification || !qualification.value.trim()) {
            const errorSpan = qualification?.parentElement?.querySelector('.error-message');
            if (errorSpan) {
                errorSpan.textContent = 'Entry Qualification is required';
                errorSpan.classList.remove('hidden');
                const trigger = document.getElementById(`education_qualification_${entryId}_select_trigger`);
                trigger?.classList.add('border-danger');
            }
            isValid = false;
            hasEducationError = true;
        }
        
        if (!yearObtained || !yearObtained.value.trim()) {
            const errorSpan = yearObtained?.parentElement?.querySelector('.error-message');
            if (errorSpan) {
                errorSpan.textContent = 'Year Obtained is required';
                errorSpan.classList.remove('hidden');
                yearObtained?.classList.add('border-danger');
            }
            isValid = false;
            hasEducationError = true;
        }
    });

    // Clear all errors first
    Object.keys(requiredFields).forEach(field => clearError(field));

    // Validate required fields
    Object.keys(requiredFields).forEach(field => {
        const input = document.querySelector(`[name="${field}"]`);
        const value = input?.value?.trim();
        
        if (!value || value === '') {
            showError(field, requiredFields[field]);
            isValid = false;
        }
    });
    
    // Validate that if zone is selected, command must be selected
    const zoneId = document.querySelector('[name="zone_id"]')?.value;
    const commandId = document.querySelector('[name="command_id"]')?.value;
    if (zoneId && !commandId) {
        showError('command_id', 'Please select a command from the selected zone');
        isValid = false;
    }

    // Validate date logic
    const dofa = document.querySelector('[name="date_of_first_appointment"]')?.value;
    const dopa = document.querySelector('[name="date_of_present_appointment"]')?.value;
    const dopts = document.querySelector('[name="date_posted_to_station"]')?.value;

    if (dofa && dopa && new Date(dofa) > new Date(dopa)) {
        showError('date_of_present_appointment', 'Date of Present Appointment must be after Date of First Appointment');
        isValid = false;
    }

    
    // Validate documents - at least one document is required (either new files or saved from session)
    const documentsInput = document.getElementById('documents-input');
    const selectedFilesList = document.getElementById('selected-files-list');
    const hasNewFiles = documentsInput && documentsInput.files && documentsInput.files.length > 0;
    // Check if there are saved files displayed in the list
    const hasSavedFiles = selectedFilesList && 
                         !selectedFilesList.classList.contains('hidden') && 
                         selectedFilesList.children.length > 0;
    // Also check the selectedFiles array if it exists
    const hasFilesInArray = typeof selectedFiles !== 'undefined' && selectedFiles && selectedFiles.length > 0;
    
    if (!hasNewFiles && !hasSavedFiles && !hasFilesInArray) {
        // Show error message near the document upload section
        const uploadSection = documentsInput?.closest('.flex.flex-col.gap-1');
        if (uploadSection) {
            let errorDiv = uploadSection.querySelector('.document-error-message');
            if (!errorDiv) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'document-error-message text-danger text-sm mt-1';
                uploadSection.appendChild(errorDiv);
            }
            errorDiv.textContent = 'At least one document is required.';
            errorDiv.classList.remove('hidden');
        }
        isValid = false;
    } else {
        // Clear error if documents exist
        const uploadSection = documentsInput?.closest('.flex.flex-col.gap-1');
        if (uploadSection) {
            const errorDiv = uploadSection.querySelector('.document-error-message');
            if (errorDiv) {
                errorDiv.textContent = '';
                errorDiv.classList.add('hidden');
            }
        }
    }

    return isValid;
}

// Form submission handler
document.getElementById('recruit-step2-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!validateStep2()) {
        const firstError = document.querySelector('.error-message:not(.hidden)');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return false;
    }
    
    this.submit();
});

// Rank to Grade Level mapping
const rankToGradeMap = {
    'CGC': 'GL 18',
    'DCG': 'GL 17',
    'ACG': 'GL 16',
    'CC': 'GL 15',
    'DC': 'GL 14',
    'AC': 'GL 13',
    'CSC': 'GL 12',
    'SC': 'GL 11',
    'DSC': 'GL 10',
    'ASC I': 'GL 09',
    'ASC II': 'GL 08',
    'IC': 'GL 07',
    'AIC': 'GL 06',
    'CA I': 'GL 05',
    'CA II': 'GL 04',
    'CA III': 'GL 03',
};

// Auto-populate grade level when rank is selected
document.addEventListener('DOMContentLoaded', function() {
    const rankSelect = document.getElementById('substantive_rank');
    const gradeLevelSelect = document.getElementById('salary_grade_level');
    
    if (rankSelect && gradeLevelSelect) {
        rankSelect.addEventListener('change', function() {
            const selectedRank = this.value;
            
            if (selectedRank && rankToGradeMap[selectedRank]) {
                const gradeLevel = rankToGradeMap[selectedRank];
                gradeLevelSelect.value = gradeLevel;
                clearError('salary_grade_level');
                
                // Trigger change event to ensure form validation recognizes the change
                gradeLevelSelect.dispatchEvent(new Event('change'));
            } else if (!selectedRank) {
                gradeLevelSelect.value = '';
            }
        });
        
        // Auto-populate on page load if rank is already selected
        if (rankSelect.value && rankToGradeMap[rankSelect.value]) {
            gradeLevelSelect.value = rankToGradeMap[rankSelect.value];
        }
    }
});

// Clear errors on input
document.querySelectorAll('#recruit-step2-form input, #recruit-step2-form select').forEach(input => {
    input.addEventListener('input', function() {
        clearError(this.name);
        // Clear education field errors
        const errorSpan = this.parentElement?.querySelector('.error-message');
        if (errorSpan && (this.classList.contains('education-year-obtained'))) {
            errorSpan.textContent = '';
            errorSpan.classList.add('hidden');
            this.classList.remove('border-danger');
        }
        // Clear errors for education selects (they use hidden inputs)
        if (this.name && this.name.includes('education[') && (this.name.includes('[university]') || this.name.includes('[qualification]') || this.name.includes('[discipline]'))) {
            const entryIdMatch = this.name.match(/education\[(\d+)\]/);
            if (entryIdMatch) {
                const entryId = entryIdMatch[1];
                const fieldType = this.name.includes('[university]') ? 'university' : (this.name.includes('[qualification]') ? 'qualification' : 'discipline');
                const trigger = document.getElementById(`education_${fieldType}_${entryId}_select_trigger`);
                const errorSpan = trigger?.parentElement?.querySelector('.error-message');
                if (errorSpan) {
                    errorSpan.textContent = '';
                    errorSpan.classList.add('hidden');
                    trigger?.classList.remove('border-danger');
                }
            }
        }
    });
    input.addEventListener('change', function() {
        clearError(this.name);
        // Clear education field errors
        const errorSpan = this.parentElement?.querySelector('.error-message');
        if (errorSpan && (this.classList.contains('education-year-obtained'))) {
            errorSpan.textContent = '';
            errorSpan.classList.add('hidden');
            this.classList.remove('border-danger');
        }
        // Clear errors for education selects (they use hidden inputs)
        if (this.name && this.name.includes('education[') && (this.name.includes('[university]') || this.name.includes('[qualification]') || this.name.includes('[discipline]'))) {
            const entryIdMatch = this.name.match(/education\[(\d+)\]/);
            if (entryIdMatch) {
                const entryId = entryIdMatch[1];
                const fieldType = this.name.includes('[university]') ? 'university' : (this.name.includes('[qualification]') ? 'qualification' : 'discipline');
                const trigger = document.getElementById(`education_${fieldType}_${entryId}_select_trigger`);
                const errorSpan = trigger?.parentElement?.querySelector('.error-message');
                if (errorSpan) {
                    errorSpan.textContent = '';
                    errorSpan.classList.add('hidden');
                    trigger?.classList.remove('border-danger');
                }
            }
        }
    });
});

// Handle document file uploads
const documentsInput = document.getElementById('documents-input');
const selectedFilesList = document.getElementById('selected-files-list');
const uploadButtonText = document.getElementById('upload-button-text');
let selectedFiles = [];
let filePreviewUrls = []; // Store object URLs for cleanup

// Load saved documents from session
function loadSavedDocuments() {
    const savedDocuments = @json($savedData['documents'] ?? []);
    
    if (savedDocuments && Array.isArray(savedDocuments) && savedDocuments.length > 0) {
        console.log('Loading saved documents:', savedDocuments);
        savedDocuments.forEach((doc, index) => {
            if (doc.temp_path || doc.name) {
                // Determine MIME type from file extension if not provided
                let mimeType = doc.type || 'application/octet-stream';
                if (!mimeType || mimeType === 'application/octet-stream') {
                    const fileName = doc.name || '';
                    if (fileName.toLowerCase().endsWith('.png')) {
                        mimeType = 'image/png';
                    } else if (fileName.toLowerCase().endsWith('.jpg') || fileName.toLowerCase().endsWith('.jpeg')) {
                        mimeType = 'image/jpeg';
                    } else if (fileName.toLowerCase().endsWith('.gif')) {
                        mimeType = 'image/gif';
                    }
                }
                
                // Create a placeholder file object for display
                const fileInfo = {
                    name: doc.name || 'Document',
                    size: doc.size || 0,
                    type: mimeType,
                    temp_path: doc.temp_path || null,
                    isSaved: true, // Mark as saved document
                    index: index
                };
                
                console.log('Document info:', fileInfo);
                
                // Add to selectedFiles array
                selectedFiles.push(fileInfo);
                
                // For images, create preview URL from server
                if (mimeType.startsWith('image/') && doc.temp_path) {
                    // Use server endpoint to preview saved document
                    const previewUrl = `/recruit/onboarding/document-preview?path=${encodeURIComponent(doc.temp_path)}`;
                    filePreviewUrls.push(previewUrl);
                    console.log('Added preview URL for image:', previewUrl);
                } else {
                    filePreviewUrls.push(null);
                }
            }
        });
        
        if (selectedFiles.length > 0) {
            updateFileDisplay();
        }
    }
}

if (documentsInput) {
    documentsInput.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        
        // Add new files to the list
        files.forEach(file => {
            if (!selectedFiles.find(f => f.name === file.name && f.size === file.size)) {
                selectedFiles.push(file);
                // Create preview URL for images
                if (file.type.startsWith('image/')) {
                    filePreviewUrls.push(URL.createObjectURL(file));
                } else {
                    filePreviewUrls.push(null);
                }
            }
        });
        
        updateFileDisplay();
    });
}

function updateFileDisplay() {
    if (selectedFiles.length === 0) {
        selectedFilesList.classList.add('hidden');
        uploadButtonText.textContent = 'Choose Files';
        documentsInput.value = '';
    } else {
        selectedFilesList.classList.remove('hidden');
        uploadButtonText.textContent = `${selectedFiles.length} file${selectedFiles.length > 1 ? 's' : ''} selected`;
        
        selectedFilesList.innerHTML = selectedFiles.map((file, index) => {
            const isImage = file.type && file.type.startsWith('image/');
            const fileSize = file.size ? (file.size / 1024).toFixed(1) : '0';
            const previewUrl = filePreviewUrls[index];
            const isSaved = file.isSaved || false;
            
            // Determine image source
            let imageSrc = null;
            if (isSaved && isImage && file.temp_path) {
                // For saved documents, always use server preview URL
                imageSrc = `/recruit/onboarding/document-preview?path=${encodeURIComponent(file.temp_path)}`;
                console.log('Setting imageSrc for saved document:', imageSrc, 'isImage:', isImage, 'temp_path:', file.temp_path);
            } else if (!isSaved && isImage && previewUrl) {
                // For new files, use the blob URL
                imageSrc = previewUrl;
            }
            
            console.log('File display:', {
                name: file.name,
                type: file.type,
                isImage: isImage,
                isSaved: isSaved,
                temp_path: file.temp_path,
                imageSrc: imageSrc,
                previewUrl: previewUrl
            });
            
            return `
                <div class="relative p-3 bg-muted/50 rounded-lg border border-input" data-file-index="${index}">
                    <div class="flex items-start gap-3">
                        ${isImage && imageSrc ? `
                            <div class="flex-shrink-0 relative">
                                <img src="${imageSrc}" 
                                     alt="${file.name}" 
                                     class="w-20 h-20 object-cover rounded-lg border border-input cursor-pointer hover:opacity-80 transition-opacity"
                                     style="display: block !important; max-width: 80px !important; max-height: 80px !important; min-width: 80px !important; min-height: 80px !important; width: 80px !important; height: 80px !important; visibility: visible !important; opacity: 1 !important;"
                                     onclick="window.open('${imageSrc}', '_blank')"
                                     title="Click to view full size"
                                     onerror="console.error('Image failed to load:', '${imageSrc}'); this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                     onload="console.log('Image loaded successfully:', '${file.name}'); this.style.display='block'; this.style.visibility='visible'; this.style.opacity='1';">
                                <div class="w-20 h-20 hidden items-center justify-center bg-muted rounded-lg border border-input" style="display: none;">
                                    <i class="ki-filled ki-file text-primary text-2xl"></i>
                                </div>
                            </div>
                        ` : `
                            <div class="flex-shrink-0 w-20 h-20 flex items-center justify-center bg-muted rounded-lg border border-input">
                                <i class="ki-filled ki-file text-primary text-2xl"></i>
                            </div>
                        `}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <span class="text-sm font-medium truncate" title="${file.name}">${file.name}</span>
                                <button type="button" 
                                        class="kt-btn kt-btn-sm kt-btn-ghost text-danger flex-shrink-0" 
                                        onclick="removeFile(${index})"
                                        title="Remove file">
                                    <i class="ki-filled ki-cross"></i>
                                </button>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-muted">
                                <span>${fileSize} KB</span>
                                ${isImage ? `<span>• Image</span>` : `<span>• ${file.type || 'File'}</span>`}
                                ${isSaved ? `<span class="text-success">• Saved</span>` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        
        // Update the file input with remaining files
        updateFileInput();
    }
}

function removeFile(index) {
    // Revoke object URL to prevent memory leak (only for blob URLs, not server URLs)
    if (filePreviewUrls[index] && typeof filePreviewUrls[index] === 'string' && filePreviewUrls[index].startsWith('blob:')) {
        URL.revokeObjectURL(filePreviewUrls[index]);
    }
    
    selectedFiles.splice(index, 1);
    filePreviewUrls.splice(index, 1);
    updateFileDisplay();
}

function updateFileInput() {
    // Create a new DataTransfer object to hold only NEW files (not saved ones)
    const dataTransfer = new DataTransfer();
    selectedFiles.forEach(file => {
        // Only add files that are not saved (i.e., newly uploaded)
        if (!file.isSaved && file instanceof File) {
            dataTransfer.items.add(file);
        }
    });
    
    // Replace the files in the input (only new files)
    documentsInput.files = dataTransfer.files;
}

</script>
@endpush
@endsection


