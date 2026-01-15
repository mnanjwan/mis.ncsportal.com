@extends('layouts.app')

@section('title', 'Nominate Officer for Course')
@section('page-title', 'Nominate Officer for Course')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.courses') }}">Course Nominations</a>
    <span>/</span>
    <span class="text-primary">Nominate Officer</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="kt-card bg-success/10 border border-success/20">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-check-circle text-success text-xl"></i>
                    <p class="text-sm text-success font-medium">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="kt-card bg-danger/10 border border-danger/20">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-danger text-xl"></i>
                    <p class="text-sm text-danger font-medium">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Form Card -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Nominate Officer for Course</h3>
        </div>
        <div class="kt-card-content">
            <form action="{{ route('hrd.courses.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Officers (Multiple Selection with Search and Checkboxes) -->
                <div class="space-y-2">
                    <label for="officer_search" class="block text-sm font-medium text-foreground">
                        Officers <span class="text-danger">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" 
                               id="officer_search" 
                               class="kt-input @error('officer_ids') kt-input-error @enderror w-full" 
                               placeholder="Search officers by name or service number..."
                               autocomplete="off">
                        <div id="officer_dropdown" 
                             class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                            <!-- Options will be populated by JavaScript -->
                        </div>
                    </div>
                    <div id="selected_officers" class="mt-2 space-y-2">
                        <!-- Selected officers will be displayed here -->
                    </div>
                    <!-- Hidden inputs for officer IDs will be added dynamically -->
                    <div id="officer_ids_hidden"></div>
                    @error('officer_ids')
                        <p class="text-sm text-danger">{{ $message }}</p>
                    @enderror
                    @error('officer_ids.*')
                        <p class="text-sm text-danger">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-secondary-foreground">
                        Search and select multiple officers by checking the boxes in the dropdown.
                    </p>
                </div>

                <!-- Course Name (Searchable Select) -->
                <div class="space-y-2">
                    <label for="course_name" class="block text-sm font-medium text-foreground">
                        Course Name <span class="text-danger">*</span>
                    </label>
                    <div class="relative">
                        <input type="hidden" name="course_name" id="course_name" required>
                        <button type="button" 
                                id="course_name_select_trigger" 
                                class="kt-input w-full text-left flex items-center justify-between cursor-pointer @error('course_name') kt-input-error @enderror">
                            <span id="course_name_select_text">Select a course or enter new...</span>
                            <i class="ki-filled ki-down text-gray-400"></i>
                        </button>
                        <div id="course_name_dropdown" 
                             class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                            <!-- Search Box -->
                            <div class="p-3 border-b border-input">
                                <div class="relative">
                                    <input type="text" 
                                           id="course_name_search_input" 
                                           class="kt-input w-full pl-10" 
                                           placeholder="Search courses..."
                                           autocomplete="off">
                                </div>
                            </div>
                            <!-- Options Container -->
                            <div id="course_name_options" class="max-h-60 overflow-y-auto">
                                <!-- Options will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                    <input type="text" 
                           name="course_name_custom" 
                           id="course_name_custom"
                           value="{{ old('course_name_custom', (old('course_name') && old('course_name') !== '__NEW__' && !$courses->contains('name', old('course_name'))) ? old('course_name') : '') }}"
                           class="kt-input @error('course_name') kt-input-error @enderror hidden"
                           placeholder="Enter new course name...">
                    @error('course_name')
                        <p class="text-sm text-danger">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-secondary-foreground">
                        Select from existing courses or choose "Add New Course" to enter a custom course name.
                    </p>
                </div>

                <!-- Course Type -->
                <div class="space-y-2">
                    <label for="course_type" class="block text-sm font-medium text-foreground">
                        Course Type
                    </label>
                    <input type="text" 
                           name="course_type" 
                           id="course_type"
                           value="{{ old('course_type') }}"
                           class="kt-input @error('course_type') kt-input-error @enderror"
                           placeholder="e.g., Professional Development, Technical Training">
                    @error('course_type')
                        <p class="text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Start Date -->
                    <div class="space-y-2">
                        <label for="start_date" class="block text-sm font-medium text-foreground">
                            Start Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" 
                               name="start_date" 
                               id="start_date"
                               value="{{ old('start_date') }}"
                               class="kt-input @error('start_date') kt-input-error @enderror"
                               required>
                        @error('start_date')
                            <p class="text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- End Date -->
                    <div class="space-y-2">
                        <label for="end_date" class="block text-sm font-medium text-foreground">
                            End Date
                        </label>
                        <input type="date" 
                               name="end_date" 
                               id="end_date"
                               value="{{ old('end_date') }}"
                               class="kt-input @error('end_date') kt-input-error @enderror">
                        @error('end_date')
                            <p class="text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Notes -->
                <div class="space-y-2">
                    <label for="notes" class="block text-sm font-medium text-foreground">
                        Notes
                    </label>
                    <textarea name="notes" 
                              id="notes"
                              rows="3"
                              class="kt-input @error('notes') kt-input-error @enderror"
                              placeholder="Additional information about the course nomination...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-4 pt-4 border-t border-border">
                    <a href="{{ route('hrd.courses') }}" class="kt-btn kt-btn-ghost">
                        Cancel
                    </a>
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-check"></i>
                        Nominate Officers
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Officers data
    @php
        $officersData = $officers->map(function($officer) {
            return [
                'id' => $officer->id,
                'name' => ($officer->initials ?? '') . ' ' . ($officer->surname ?? ''),
                'service_number' => $officer->service_number ?? 'N/A',
                'rank' => $officer->substantive_rank ?? 'N/A',
            ];
        })->values();
    @endphp
    const officers = @json($officersData);
    let selectedOfficers = new Map(); // Map of officerId -> officer object

    const officerSearchInput = document.getElementById('officer_search');
    const officerDropdown = document.getElementById('officer_dropdown');
    const selectedOfficersDiv = document.getElementById('selected_officers');

    // Initialize selected officers if old input exists (for error recovery)
    @if(old('officer_ids'))
        const oldOfficerIds = @json(old('officer_ids', []));
        oldOfficerIds.forEach(officerId => {
            const officer = officers.find(o => o.id == officerId);
            if (officer) {
                selectedOfficers.set(officer.id, officer);
            }
        });
        updateSelectedOfficersDisplay();
        updateOfficerIdsInput();
    @endif

    // Update the hidden inputs with selected officer IDs
    function updateOfficerIdsInput() {
        const ids = Array.from(selectedOfficers.keys());
        const hiddenContainer = document.getElementById('officer_ids_hidden');
        hiddenContainer.innerHTML = ids.map(id => 
            '<input type="hidden" name="officer_ids[]" value="' + id + '">'
        ).join('');
    }

    // Update the display of selected officers
    function updateSelectedOfficersDisplay() {
        if (selectedOfficers.size === 0) {
            selectedOfficersDiv.innerHTML = '';
            return;
        }

        selectedOfficersDiv.innerHTML = Array.from(selectedOfficers.values()).map(officer => {
            const displayName = officer.name.trim();
            const displayDetails = officer.service_number + (officer.rank !== 'N/A' ? ' - ' + officer.rank : '');
            return '<div class="flex items-center justify-between p-2 bg-muted/50 rounded-lg" data-officer-id="' + officer.id + '">' +
                        '<div class="flex flex-col gap-1">' +
                            '<span class="text-sm font-medium">' + displayName + '</span>' +
                            '<span class="text-xs text-secondary-foreground">' + displayDetails + '</span>' +
                        '</div>' +
                        '<button type="button" class="kt-btn kt-btn-sm kt-btn-ghost text-danger remove-officer" data-officer-id="' + officer.id + '">' +
                            '<i class="ki-filled ki-cross"></i>' +
                        '</button>' +
                    '</div>';
        }).join('');

        // Attach remove event listeners
        selectedOfficersDiv.querySelectorAll('.remove-officer').forEach(btn => {
            btn.addEventListener('click', function() {
                const officerId = parseInt(this.dataset.officerId);
                selectedOfficers.delete(officerId);
                updateSelectedOfficersDisplay();
                updateOfficerIdsInput();
                // Refresh dropdown to update checkbox states
                if (officerSearchInput.value.trim().length > 0) {
                    officerSearchInput.dispatchEvent(new Event('input'));
                }
            });
        });
    }

    // Search functionality
    officerSearchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        
        if (searchTerm.length === 0) {
            officerDropdown.classList.add('hidden');
            return;
        }

        const filtered = officers.filter(officer => {
            const nameMatch = officer.name.toLowerCase().includes(searchTerm);
            const serviceMatch = officer.service_number.toLowerCase().includes(searchTerm);
            const rankMatch = officer.rank.toLowerCase().includes(searchTerm);
            return nameMatch || serviceMatch || rankMatch;
        });

        if (filtered.length > 0) {
            officerDropdown.innerHTML = filtered.map(officer => {
                const displayName = officer.name.trim();
                const displayDetails = officer.service_number + (officer.rank !== 'N/A' ? ' - ' + officer.rank : '');
                const isChecked = selectedOfficers.has(officer.id);
                return '<div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 flex items-center gap-3" ' +
                            'data-id="' + officer.id + '">' +
                            '<input type="checkbox" class="kt-checkbox officer-checkbox" data-officer-id="' + officer.id + '" ' + (isChecked ? 'checked' : '') + '>' +
                            '<div class="flex-1" data-selectable="true">' +
                            '<div class="text-sm font-medium text-foreground">' + displayName + '</div>' +
                            '<div class="text-xs text-secondary-foreground">' + displayDetails + '</div>' +
                            '</div>' +
                        '</div>';
            }).join('');
            officerDropdown.classList.remove('hidden');
        } else {
            officerDropdown.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No officers found</div>';
            officerDropdown.classList.remove('hidden');
        }

        // Attach checkbox event listeners
        officerDropdown.querySelectorAll('.officer-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function(e) {
                e.stopPropagation();
                const officerId = parseInt(this.dataset.officerId);
                const officer = officers.find(o => o.id === officerId);
                if (officer) {
                    if (this.checked) {
                        selectedOfficers.set(officer.id, officer);
                    } else {
                        selectedOfficers.delete(officer.id);
                    }
                    updateSelectedOfficersDisplay();
                    updateOfficerIdsInput();
                }
            });
        });

        // Also allow clicking on the row to toggle checkbox
        officerDropdown.querySelectorAll('[data-id]').forEach(row => {
            row.addEventListener('click', function(e) {
                if (e.target.type !== 'checkbox') {
                    const checkbox = this.querySelector('.officer-checkbox');
                    if (checkbox) {
                        checkbox.checked = !checkbox.checked;
                        checkbox.dispatchEvent(new Event('change'));
            }
        }
    });
        });
    });

    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!officerSearchInput.contains(e.target) && !officerDropdown.contains(e.target) && !selectedOfficersDiv.contains(e.target)) {
            officerDropdown.classList.add('hidden');
        }
    });

    // Show dropdown when focusing on search input
    officerSearchInput.addEventListener('focus', function() {
        if (this.value.trim().length > 0) {
            this.dispatchEvent(new Event('input'));
        }
    });

    // Course Name data
    @php
        $coursesData = $courses->map(function($course) {
            return ['id' => $course->name, 'name' => $course->name];
        })->values();
        // Add "Add New Course" option
        $coursesData->push(['id' => '__NEW__', 'name' => '+ Add New Course']);
    @endphp
    const courseNames = @json($coursesData);

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

        let selectedOption = null;
        let filteredOptions = [...options];

        // Render options
        function renderOptions(opts) {
            if (opts.length === 0) {
                optionsContainer.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No options found</div>';
                return;
            }

            optionsContainer.innerHTML = opts.map(opt => {
                const display = displayFn ? displayFn(opt) : (opt.name || opt.id);
                const value = opt.id || opt.value || '';
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
                    selectedOption = options.find(o => (o.id || o.value || '') == id);
                    
                    if (selectedOption || id === '') {
                        hiddenInput.value = id;
                        displayText.textContent = name || placeholder;
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
                const display = displayFn ? displayFn(opt) : (opt.name || opt.id || '');
                return display.toLowerCase().includes(searchTerm);
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

    // Initialize course name searchable select
    const courseNameCustom = document.getElementById('course_name_custom');
    const courseNameHiddenInput = document.getElementById('course_name');
    
    createSearchableSelect({
        triggerId: 'course_name_select_trigger',
        hiddenInputId: 'course_name',
        dropdownId: 'course_name_dropdown',
        searchInputId: 'course_name_search_input',
        optionsContainerId: 'course_name_options',
        displayTextId: 'course_name_select_text',
        options: courseNames,
        placeholder: 'Select a course or enter new...',
        searchPlaceholder: 'Search courses...',
        onSelect: function(option) {
            if (option.id === '__NEW__') {
                courseNameCustom.classList.remove('hidden');
                courseNameCustom.required = true;
                courseNameHiddenInput.required = false;
                courseNameCustom.focus();
            } else {
                courseNameCustom.classList.add('hidden');
                courseNameCustom.required = false;
                courseNameHiddenInput.required = true;
            }
        }
    });

    // Initialize on page load - check if custom input has a value
    @if(old('course_name') && old('course_name') !== '__NEW__' && !$courses->contains('name', old('course_name')))
        // If old course_name exists but is not in the list, show custom input
        courseNameCustom.classList.remove('hidden');
        courseNameCustom.required = true;
        courseNameHiddenInput.required = false;
        courseNameHiddenInput.value = '__NEW__';
        document.getElementById('course_name_select_text').textContent = '+ Add New Course';
    @elseif(old('course_name') === '__NEW__')
        courseNameCustom.classList.remove('hidden');
        courseNameCustom.required = true;
        courseNameHiddenInput.required = false;
        document.getElementById('course_name_select_text').textContent = '+ Add New Course';
    @elseif(old('course_name') && $courses->contains('name', old('course_name')))
        // Set the selected course
        const oldCourseName = '{{ old('course_name') }}';
        courseNameHiddenInput.value = oldCourseName;
        document.getElementById('course_name_select_text').textContent = oldCourseName;
    @endif

    // Form submission - use custom input if "__NEW__" is selected and validate officer selection
    document.querySelector('form').addEventListener('submit', function(e) {
        // Validate at least one officer is selected
        if (selectedOfficers.size === 0) {
            e.preventDefault();
            alert('Please select at least one officer');
            officerSearchInput.focus();
            return false;
        }

        // Validate course name
        if (courseNameHiddenInput.value === '__NEW__') {
            if (!courseNameCustom.value.trim()) {
                e.preventDefault();
                alert('Please enter a course name');
                courseNameCustom.focus();
                return false;
            }
            // Keep course_name as "__NEW__" so controller knows to use course_name_custom
        }
    });
</script>
@endpush
@endsection

