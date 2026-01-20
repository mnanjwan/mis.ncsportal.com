@extends('layouts.app')

@section('title', 'Request Education Approval')
@section('page-title', 'Request Education Qualification Approval')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.education-requests.index') }}">Education Requests</a>
    <span>/</span>
    <span class="text-primary">Request Approval</span>
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
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Education Qualification Request</h3>
            </div>
            <div class="kt-card-content">
                <form action="{{ route('officer.education-requests.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="flex flex-col gap-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                            <div class="flex flex-col gap-1">
                                <label class="kt-form-label">Institution <span class="text-danger">*</span></label>
                                <div class="relative">
                                    <input type="hidden" name="university" id="education_university_id" value="{{ old('university') }}" required>
                                    <button type="button"
                                            id="education_university_select_trigger"
                                            class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                        <span id="education_university_select_text">
                                            {{ old('university') ? old('university') : '-- Select Institution --' }}
                                        </span>
                                        <i class="ki-filled ki-down text-gray-400"></i>
                                    </button>
                                    <div id="education_university_dropdown"
                                         class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                        <div class="p-3 border-b border-input">
                                            <input type="text"
                                                   id="education_university_search_input"
                                                   class="kt-input w-full pl-10"
                                                   placeholder="Search institution..."
                                                   autocomplete="off">
                                        </div>
                                        <div id="education_university_options" class="max-h-60 overflow-y-auto"></div>
                                    </div>
                                    <input type="text"
                                           id="education_university_custom"
                                           class="kt-input mt-2 hidden"
                                           placeholder="Type institution name..."
                                           autocomplete="off">
                                </div>
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="kt-form-label">Entry Qualification <span class="text-danger">*</span></label>
                                <div class="relative">
                                    <input type="hidden" name="qualification" id="education_qualification_id" value="{{ old('qualification') }}" required>
                                    <button type="button"
                                            id="education_qualification_select_trigger"
                                            class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                        <span id="education_qualification_select_text">
                                            {{ old('qualification') ? old('qualification') : '-- Select Qualification --' }}
                                        </span>
                                        <i class="ki-filled ki-down text-gray-400"></i>
                                    </button>
                                    <div id="education_qualification_dropdown"
                                         class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                        <div class="p-3 border-b border-input">
                                            <input type="text"
                                                   id="education_qualification_search_input"
                                                   class="kt-input w-full pl-10"
                                                   placeholder="Search qualification..."
                                                   autocomplete="off">
                                        </div>
                                        <div id="education_qualification_options" class="max-h-60 overflow-y-auto"></div>
                                    </div>
                                    <input type="text"
                                           id="education_qualification_custom"
                                           class="kt-input mt-2 hidden"
                                           placeholder="Type qualification..."
                                           autocomplete="off">
                                </div>
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="kt-form-label">Year Obtained <span class="text-danger">*</span></label>
                                <input type="number"
                                       id="year_obtained"
                                       name="year_obtained"
                                       class="kt-input"
                                       value="{{ old('year_obtained') }}"
                                       placeholder="e.g., 2020"
                                       min="1950"
                                       max="{{ date('Y') }}"
                                       required>
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="kt-form-label">Discipline <span class="text-muted">(Optional)</span></label>
                                <div class="relative">
                                    <input type="hidden" name="discipline" id="education_discipline_id" value="{{ old('discipline') }}">
                                    <button type="button"
                                            id="education_discipline_select_trigger"
                                            class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                        <span id="education_discipline_select_text">
                                            {{ old('discipline') ? old('discipline') : '-- Select Discipline --' }}
                                        </span>
                                        <i class="ki-filled ki-down text-gray-400"></i>
                                    </button>
                                    <div id="education_discipline_dropdown"
                                         class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                        <div class="p-3 border-b border-input">
                                            <input type="text"
                                                   id="education_discipline_search_input"
                                                   class="kt-input w-full pl-10"
                                                   placeholder="Search discipline..."
                                                   autocomplete="off">
                                        </div>
                                        <div id="education_discipline_options" class="max-h-60 overflow-y-auto"></div>
                                    </div>
                                    <input type="text"
                                           id="education_discipline_custom"
                                           class="kt-input mt-2 hidden"
                                           placeholder="Type discipline (optional)..."
                                           autocomplete="off">
                                </div>
                            </div>
                        </div>

                        <!-- Supporting Documents (Optional) -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Supporting Documents <span class="text-muted">(Optional)</span></label>
                            <input type="file"
                                   name="documents[]"
                                   class="kt-input"
                                   multiple
                                   accept=".pdf,image/*">
                            <p class="text-xs text-secondary-foreground">
                                You may upload multiple files. Allowed: PDF, JPG/JPEG/PNG. Max 5MB per file.
                            </p>
                        </div>

                        <div class="kt-card bg-info/10 border border-info/20">
                            <div class="kt-card-content p-4">
                                <div class="flex items-start gap-3">
                                    <i class="ki-filled ki-information text-info text-xl mt-0.5"></i>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-info mb-1">Important Information</p>
                                        <ul class="list-disc list-inside text-xs text-secondary-foreground space-y-1">
                                            <li>Your request will be reviewed by HRD.</li>
                                            <li>You will be notified once your request is approved or rejected.</li>
                                            <li>Approved qualifications will be added to your Educational History.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                            <a href="{{ route('officer.education-requests.index') }}" class="kt-btn kt-btn-secondary">
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
// Reusable function to create searchable select (same UX as onboarding step 2)
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

    let filteredOptions = [...options];

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
                     data-name="${String(display).replace(/"/g, '&quot;')}">
                    <div class="text-sm text-foreground">${display}</div>
                </div>
            `;
        }).join('');

        optionsContainer.querySelectorAll('.select-option').forEach(option => {
            option.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;

                hiddenInput.value = id;
                displayText.textContent = name || placeholder;
                dropdown.classList.add('hidden');
                searchInput.value = '';
                filteredOptions = [...options];
                renderOptions(filteredOptions);

                const selectedOption = options.find(o => {
                    const optValue = o.id !== undefined ? o.id : (o.value !== undefined ? o.value : o);
                    return String(optValue) === String(id);
                });
                if (onSelect) onSelect(selectedOption || {id: id, name: name});
            });
        });
    }

    renderOptions(filteredOptions);

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        filteredOptions = options.filter(opt => {
            const display = displayFn ? displayFn(opt) : (opt.name || opt.id || opt);
            return String(display).toLowerCase().includes(searchTerm);
        });
        renderOptions(filteredOptions);
    });

    trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdown.classList.toggle('hidden');
        if (!dropdown.classList.contains('hidden')) {
            setTimeout(() => searchInput.focus(), 100);
        }
    });

    document.addEventListener('click', function(e) {
        if (!trigger.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const institutionMasterList = @json($institutions ?? []);
    const qualificationMasterList = @json($qualifications ?? []);
    const disciplineMasterList = @json($disciplines ?? []);
    const ADD_NEW_VALUE = '__ADD_NEW__';

    const institutionOptions = [
        {id: '', name: '-- Select Institution --'},
        {id: ADD_NEW_VALUE, name: '-- Add New Institution (type below) --'},
        ...institutionMasterList.map(name => ({id: name, name: name}))
    ];

    createSearchableSelect({
        triggerId: 'education_university_select_trigger',
        hiddenInputId: 'education_university_id',
        dropdownId: 'education_university_dropdown',
        searchInputId: 'education_university_search_input',
        optionsContainerId: 'education_university_options',
        displayTextId: 'education_university_select_text',
        options: institutionOptions,
        placeholder: '-- Select Institution --',
        searchPlaceholder: 'Search institution...',
        onSelect: function(option) {
            const hidden = document.getElementById('education_university_id');
            const customInput = document.getElementById('education_university_custom');
            const displayText = document.getElementById('education_university_select_text');
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

    const institutionCustom = document.getElementById('education_university_custom');
    const institutionHidden = document.getElementById('education_university_id');
    if (institutionCustom && institutionHidden) {
        if (institutionHidden.value && !institutionMasterList.includes(institutionHidden.value)) {
            // show custom if prefilled with non-master value
            institutionCustom.classList.remove('hidden');
        }
        institutionCustom.addEventListener('input', function() {
            institutionHidden.value = this.value.trim();
        });
    }

    const qualificationOptions = [
        {id: '', name: '-- Select Qualification --'},
        {id: ADD_NEW_VALUE, name: '-- Add New Qualification (type below) --'},
        ...qualificationMasterList.map(name => ({id: name, name: name}))
    ];

    createSearchableSelect({
        triggerId: 'education_qualification_select_trigger',
        hiddenInputId: 'education_qualification_id',
        dropdownId: 'education_qualification_dropdown',
        searchInputId: 'education_qualification_search_input',
        optionsContainerId: 'education_qualification_options',
        displayTextId: 'education_qualification_select_text',
        options: qualificationOptions,
        placeholder: '-- Select Qualification --',
        searchPlaceholder: 'Search qualification...',
        onSelect: function(option) {
            const hidden = document.getElementById('education_qualification_id');
            const customInput = document.getElementById('education_qualification_custom');
            const displayText = document.getElementById('education_qualification_select_text');
            if (!hidden || !customInput || !displayText) return;

            if (option.id === ADD_NEW_VALUE) {
                customInput.classList.remove('hidden');
                customInput.value = (hidden.value && hidden.value !== ADD_NEW_VALUE) ? hidden.value : '';
                hidden.value = customInput.value.trim();
                displayText.textContent = '-- Add New Qualification (type below) --';
                setTimeout(() => customInput.focus(), 0);
            } else {
                customInput.classList.add('hidden');
                customInput.value = '';
            }
        }
    });

    const qualificationCustom = document.getElementById('education_qualification_custom');
    const qualificationHidden = document.getElementById('education_qualification_id');
    if (qualificationCustom && qualificationHidden) {
        if (qualificationHidden.value && !qualificationMasterList.includes(qualificationHidden.value)) {
            qualificationCustom.classList.remove('hidden');
        }
        qualificationCustom.addEventListener('input', function() {
            qualificationHidden.value = this.value.trim();
        });
    }

    const disciplineOptions = [
        {id: '', name: '-- Select Discipline --'},
        {id: ADD_NEW_VALUE, name: '-- Add New Discipline (type below) --'},
        ...disciplineMasterList.map(name => ({id: name, name: name}))
    ];

    createSearchableSelect({
        triggerId: 'education_discipline_select_trigger',
        hiddenInputId: 'education_discipline_id',
        dropdownId: 'education_discipline_dropdown',
        searchInputId: 'education_discipline_search_input',
        optionsContainerId: 'education_discipline_options',
        displayTextId: 'education_discipline_select_text',
        options: disciplineOptions,
        placeholder: '-- Select Discipline --',
        searchPlaceholder: 'Search discipline...',
        onSelect: function(option) {
            const hidden = document.getElementById('education_discipline_id');
            const customInput = document.getElementById('education_discipline_custom');
            const displayText = document.getElementById('education_discipline_select_text');
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

    const disciplineCustom = document.getElementById('education_discipline_custom');
    const disciplineHidden = document.getElementById('education_discipline_id');
    if (disciplineCustom && disciplineHidden) {
        if (disciplineHidden.value && !disciplineMasterList.includes(disciplineHidden.value)) {
            disciplineCustom.classList.remove('hidden');
        }
        disciplineCustom.addEventListener('input', function() {
            disciplineHidden.value = this.value.trim();
        });
    }
});
</script>
@endpush

