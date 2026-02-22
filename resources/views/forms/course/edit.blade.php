@extends('layouts.app')

@section('title', 'Edit Course Nomination')
@section('page-title', 'Edit Course Nomination')

@php $coursePrefix = $courseRoutePrefix ?? 'hrd'; $breadcrumbLabel = $coursePrefix === 'staff-officer' ? 'Staff Officer' : 'HRD'; @endphp
@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route($coursePrefix . '.dashboard') }}">{{ $breadcrumbLabel }}</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route($coursePrefix . '.courses') }}">Course Nominations</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route($coursePrefix . '.courses.show', $course->id) }}">View Course</a>
    <span>/</span>
    <span class="text-primary">Edit</span>
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
            <h3 class="kt-card-title">Edit Course Nomination</h3>
        </div>
        <div class="kt-card-content">
            <form action="{{ route($coursePrefix . '.courses.update', $course->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Officer (Searchable) -->
                <div class="space-y-2">
                    <label for="officer_search" class="block text-sm font-medium text-foreground">
                        Officer <span class="text-danger">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" 
                               id="officer_search" 
                               class="kt-input @error('officer_id') kt-input-error @enderror w-full" 
                               placeholder="Search officer by name or service number..."
                               autocomplete="off"
                               value="{{ old('officer_search') }}">
                        <input type="hidden" 
                               name="officer_id" 
                               id="officer_id"
                               value="{{ old('officer_id', $course->officer_id) }}"
                               required>
                        <div id="officer_dropdown" 
                             class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                            <!-- Options will be populated by JavaScript -->
                        </div>
                    </div>
                    <div id="selected_officer" class="mt-2 p-2 bg-muted/50 rounded-lg {{ old('officer_id', $course->officer_id) ? '' : 'hidden' }}">
                        <div class="flex items-center justify-between">
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-medium" id="selected_officer_name"></span>
                                <span class="text-xs text-secondary-foreground" id="selected_officer_details"></span>
                            </div>
                            <button type="button" 
                                    id="clear_officer" 
                                    class="kt-btn kt-btn-sm kt-btn-ghost text-danger">
                                <i class="ki-filled ki-cross"></i>
                            </button>
                        </div>
                    </div>
                    @error('officer_id')
                        <p class="text-sm text-danger">{{ $message }}</p>
                    @enderror
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
                            <span id="course_name_select_text">{{ old('course_name', $course->course_name) && $courses->contains('name', old('course_name', $course->course_name)) ? old('course_name', $course->course_name) : 'Select a course or enter new...' }}</span>
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
                           value="{{ old('course_name_custom', (old('course_name', $course->course_name) && !$courses->contains('name', old('course_name', $course->course_name))) ? old('course_name', $course->course_name) : '') }}"
                           class="kt-input @error('course_name') kt-input-error @enderror {{ (!$courses->contains('name', old('course_name', $course->course_name))) ? '' : 'hidden' }}"
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
                           value="{{ old('course_type', $course->course_type) }}"
                           class="kt-input @error('course_type') kt-input-error @enderror">
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
                               value="{{ old('start_date', $course->start_date ? $course->start_date->format('Y-m-d') : '') }}"
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
                               value="{{ old('end_date', $course->end_date ? $course->end_date->format('Y-m-d') : '') }}"
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
                              class="kt-input @error('notes') kt-input-error @enderror">{{ old('notes', $course->notes) }}</textarea>
                    @error('notes')
                        <p class="text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-4 pt-4 border-t border-border">
                    <a href="{{ route($coursePrefix . '.courses.show', $course->id) }}" class="kt-btn kt-btn-ghost">
                        Cancel
                    </a>
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-check"></i>
                        Update Course
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
        
        // Get current officer data for pre-selection
        $currentOfficerId = old('officer_id', $course->officer_id);
        $currentOfficer = $officers->firstWhere('id', $currentOfficerId);
    @endphp
    const officers = @json($officersData);
    let selectedOfficer = null;

    const officerSearchInput = document.getElementById('officer_search');
    const officerHiddenInput = document.getElementById('officer_id');
    const officerDropdown = document.getElementById('officer_dropdown');
    const selectedOfficerDiv = document.getElementById('selected_officer');
    const selectedOfficerName = document.getElementById('selected_officer_name');
    const selectedOfficerDetails = document.getElementById('selected_officer_details');

    // Initialize selected officer
    @if($currentOfficer)
        const currentOfficer = {
            id: {{ $currentOfficer->id }},
            name: '{{ ($currentOfficer->initials ?? '') . ' ' . ($currentOfficer->surname ?? '') }}',
            service_number: '{{ $currentOfficer->service_number ?? 'N/A' }}',
            rank: '{{ $currentOfficer->substantive_rank ?? 'N/A' }}'
        };
        selectedOfficer = currentOfficer;
        officerHiddenInput.value = currentOfficer.id;
        officerSearchInput.value = currentOfficer.name.trim();
        selectedOfficerName.textContent = currentOfficer.name.trim();
        selectedOfficerDetails.textContent = currentOfficer.service_number + (currentOfficer.rank !== 'N/A' ? ' - ' + currentOfficer.rank : '');
        selectedOfficerDiv.classList.remove('hidden');
    @elseif(old('officer_id'))
        const oldOfficerId = {{ old('officer_id') }};
        const oldOfficer = officers.find(o => o.id == oldOfficerId);
        if (oldOfficer) {
            selectedOfficer = oldOfficer;
            officerHiddenInput.value = oldOfficer.id;
            officerSearchInput.value = oldOfficer.name.trim();
            selectedOfficerName.textContent = oldOfficer.name.trim();
            selectedOfficerDetails.textContent = oldOfficer.service_number + (oldOfficer.rank !== 'N/A' ? ' - ' + oldOfficer.rank : '');
            selectedOfficerDiv.classList.remove('hidden');
        }
    @endif

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
                return '<div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0" ' +
                            'data-id="' + officer.id + '" ' +
                            'data-name="' + displayName.replace(/"/g, '&quot;') + '" ' +
                            'data-details="' + displayDetails.replace(/"/g, '&quot;') + '">' +
                            '<div class="text-sm font-medium text-foreground">' + displayName + '</div>' +
                            '<div class="text-xs text-secondary-foreground">' + displayDetails + '</div>' +
                        '</div>';
            }).join('');
            officerDropdown.classList.remove('hidden');
        } else {
            officerDropdown.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No officers found</div>';
            officerDropdown.classList.remove('hidden');
        }
    });

    // Handle option selection
    officerDropdown.addEventListener('click', function(e) {
        const option = e.target.closest('[data-id]');
        if (option) {
            const foundOfficer = officers.find(o => o.id == parseInt(option.dataset.id));
            if (foundOfficer) {
                selectedOfficer = foundOfficer;
                officerHiddenInput.value = foundOfficer.id;
                const displayName = foundOfficer.name.trim();
                const displayDetails = foundOfficer.service_number + (foundOfficer.rank !== 'N/A' ? ' - ' + foundOfficer.rank : '');
                officerSearchInput.value = displayName;
                selectedOfficerName.textContent = displayName;
                selectedOfficerDetails.textContent = displayDetails;
                selectedOfficerDiv.classList.remove('hidden');
                officerDropdown.classList.add('hidden');
            }
        }
    });

    // Clear selection
    document.getElementById('clear_officer')?.addEventListener('click', function() {
        selectedOfficer = null;
        officerHiddenInput.value = '';
        officerSearchInput.value = '';
        selectedOfficerDiv.classList.add('hidden');
        officerDropdown.classList.add('hidden');
    });

    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!officerSearchInput.contains(e.target) && !officerDropdown.contains(e.target) && !selectedOfficerDiv.contains(e.target)) {
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
        $coursesData = $courses->map(function($courseOption) {
            return ['id' => $courseOption->name, 'name' => $courseOption->name];
        })->values();
        // Add "Add New Course" option
        $coursesData->push(['id' => '__NEW__', 'name' => '+ Add New Course']);
        $currentCourseName = old('course_name', $course->course_name);
        $isCustomCourse = !$courses->contains('name', $currentCourseName);
    @endphp
    const courseNames = @json($coursesData);
    const currentCourseName = @json($currentCourseName);
    const isCustomCourse = @json($isCustomCourse);

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
    
    // Set initial value
    if (isCustomCourse) {
        courseNameHiddenInput.value = '__NEW__';
        document.getElementById('course_name_select_text').textContent = '+ Add New Course';
    } else {
        courseNameHiddenInput.value = currentCourseName;
        document.getElementById('course_name_select_text').textContent = currentCourseName;
    }
    
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

    // Form submission - use custom input if "__NEW__" is selected
    document.querySelector('form').addEventListener('submit', function(e) {
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

