@extends('layouts.app')

@section('title', 'Edit Duty Roster')
@section('page-title', 'Edit Duty Roster')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.dashboard') }}">Staff Officer</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.roster') }}">Duty Roster</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.roster.show', $roster->id) }}">View</a>
    <span>/</span>
    <span class="text-primary">Edit</span>
@endsection

@section('content')
@if(session('success'))
    <div class="kt-card bg-success/10 border border-success/20 mb-5">
        <div class="kt-card-content p-4">
            <div class="flex items-center gap-3">
                <i class="ki-filled ki-check-circle text-success text-xl"></i>
                <p class="text-sm text-success">{{ session('success') }}</p>
            </div>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
        <div class="kt-card-content p-4">
            <div class="flex items-center gap-3">
                <i class="ki-filled ki-cross-circle text-danger text-xl"></i>
                <p class="text-sm text-danger">{{ session('error') }}</p>
            </div>
        </div>
    </div>
@endif

@if($errors->any())
    <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
        <div class="kt-card-content p-4">
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-cross-circle text-danger text-xl"></i>
                    <p class="text-sm font-semibold text-danger">Please fix the following errors:</p>
                </div>
                <ul class="list-disc list-inside text-sm text-danger ml-8">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

@if($roster->status !== 'DRAFT')
    <div class="kt-card">
        <div class="kt-card-content">
            <div class="text-center py-12">
                <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                <p class="text-secondary-foreground">Only DRAFT rosters can be edited.</p>
                <a href="{{ route('staff-officer.roster.show', $roster->id) }}" class="kt-btn kt-btn-primary mt-4">
                    Back to Roster
                </a>
            </div>
        </div>
    </div>
@else
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Info Card -->
        <div class="kt-card bg-info/10 border border-info/20">
            <div class="kt-card-content p-5">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-2xl text-info"></i>
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-semibold text-foreground">Edit Duty Roster</span>
                        <span class="text-xs text-secondary-foreground">
                            Period: {{ $roster->roster_period_start->format('M d, Y') }} - {{ $roster->roster_period_end->format('M d, Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Form -->
        <form class="kt-card" method="POST" action="{{ route('staff-officer.roster.update', $roster->id) }}" id="roster-edit-form">
            @csrf
            @method('PUT')
            <div class="kt-card-header">
                <h3 class="kt-card-title">Roster Leadership & Assignments</h3>
            </div>
            <div class="kt-card-content space-y-6">
                <!-- Unit Selection -->
                <div class="flex flex-col gap-1">
                    <label class="kt-form-label font-normal text-mono">Unit/Title</label>
                    <div class="flex gap-2">
                        <select class="kt-input flex-1" name="unit" id="unit-select-edit">
                            <option value="">Select Unit</option>
                            @if(isset($allUnits) && count($allUnits) > 0)
                                @foreach($allUnits as $unit)
                                    <option value="{{ $unit }}" {{ $roster->unit === $unit ? 'selected' : '' }}>{{ $unit }}</option>
                                @endforeach
                            @endif
                            <option value="__NEW__" {{ !isset($allUnits) || !in_array($roster->unit, $allUnits) && $roster->unit ? 'selected' : '' }}>➕ Create New Unit</option>
                        </select>
                        <input type="text" class="kt-input flex-1 {{ (isset($allUnits) && in_array($roster->unit, $allUnits)) || !$roster->unit ? 'hidden' : '' }}" name="unit_custom" id="unit-custom-input-edit" placeholder="Enter new unit name" value="{{ (!isset($allUnits) || !in_array($roster->unit, $allUnits)) && $roster->unit ? $roster->unit : old('unit_custom') }}"/>
                    </div>
                    <p class="text-xs text-secondary-foreground mt-1">Select a unit from the list or create a new one</p>
                </div>
                
                <!-- Leadership Selection -->
                <div class="kt-card shadow-none bg-info/10 border border-info/20">
                    <div class="kt-card-content p-4">
                        <h4 class="text-sm font-semibold text-foreground mb-4">Roster Leadership</h4>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1">
                                <label class="kt-form-label font-normal text-mono text-xs">Officer in Charge (OIC) <span class="text-danger">*</span></label>
                                <div class="relative">
                                    <input type="text" 
                                           class="kt-input officer-search-input" 
                                           placeholder="Search officer by name or service number..." 
                                           data-select-id="oic_officer_id"
                                           autocomplete="off">
                                    <select class="kt-input" name="oic_officer_id" id="oic_officer_id" required>
                                        <option value="">Select OIC</option>
                                        @foreach($officers as $officer)
                                            <option value="{{ $officer->id }}" 
                                                    data-search-text="{{ strtolower($officer->initials . ' ' . $officer->surname . ' ' . $officer->service_number) }}"
                                                    {{ $roster->oic_officer_id == $officer->id ? 'selected' : '' }}>
                                                {{ $officer->initials }} {{ $officer->surname }} ({{ $officer->service_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="kt-form-label font-normal text-mono text-xs">Second In Command (2IC)</label>
                                <div class="relative">
                                    <input type="text" 
                                           class="kt-input officer-search-input" 
                                           placeholder="Search officer by name or service number..." 
                                           data-select-id="second_in_command_officer_id"
                                           autocomplete="off">
                                    <select class="kt-input" name="second_in_command_officer_id" id="second_in_command_officer_id">
                                        <option value="">Select 2IC (Optional)</option>
                                        @foreach($officers as $officer)
                                            <option value="{{ $officer->id }}" 
                                                    data-search-text="{{ strtolower($officer->initials . ' ' . $officer->surname . ' ' . $officer->service_number) }}"
                                                    {{ $roster->second_in_command_officer_id == $officer->id ? 'selected' : '' }}>
                                                {{ $officer->initials }} {{ $officer->surname }} ({{ $officer->service_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-secondary-foreground mt-3">
                            <i class="ki-filled ki-information"></i> OIC and 2IC will be notified along with all assigned officers when the roster is updated.
                        </p>
                    </div>
                </div>
                
                <!-- Assignments Section -->
                <div>
                    <h4 class="text-sm font-semibold text-foreground mb-4">Officer Assignments</h4>
                <div id="assignments-container" class="space-y-4">
                    @if($roster->assignments->count() > 0)
                        @foreach($roster->assignments as $index => $assignment)
                            <div class="kt-card shadow-none bg-muted/30 border border-input" data-assignment-index="{{ $index }}">
                                <div class="kt-card-content p-4 space-y-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-semibold text-mono">Assignment #{{ $index + 1 }}</span>
                                        <button type="button" class="kt-btn kt-btn-sm kt-btn-ghost text-danger remove-assignment-btn" data-index="{{ $index }}">
                                            <i class="ki-filled ki-trash"></i> Remove
                                        </button>
                                    </div>
                                    <div class="grid sm:grid-cols-2 gap-4">
                                        <div class="flex flex-col gap-1">
                                            <label class="kt-form-label font-normal text-mono text-xs">Officer <span class="text-danger">*</span></label>
                                            <div class="relative">
                                                <input type="text" 
                                                       class="kt-input officer-search-input" 
                                                       placeholder="Search officer by name or service number..." 
                                                       data-select-id="assignment-officer-{{ $index }}"
                                                       autocomplete="off">
                                                <select class="kt-input assignment-officer-select" 
                                                        name="assignments[{{ $index }}][officer_id]" 
                                                        id="assignment-officer-{{ $index }}"
                                                        required>
                                                    <option value="">Select Officer</option>
                                                    @foreach($officersForAssignments ?? $officers as $officer)
                                                        <option value="{{ $officer->id }}" 
                                                                data-search-text="{{ strtolower($officer->initials . ' ' . $officer->surname . ' ' . $officer->service_number) }}"
                                                                {{ $assignment->officer_id == $officer->id ? 'selected' : '' }}>
                                                            {{ $officer->initials }} {{ $officer->surname }} ({{ $officer->service_number }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="flex flex-col gap-1">
                                            <label class="kt-form-label font-normal text-mono text-xs">Duty Date</label>
                                            <input type="date" class="kt-input" name="assignments[{{ $index }}][duty_date]" 
                                                   value="{{ $assignment->duty_date ? $assignment->duty_date->format('Y-m-d') : '' }}" 
                                                   min="{{ $roster->roster_period_start->format('Y-m-d') }}"
                                                   max="{{ $roster->roster_period_end->format('Y-m-d') }}"/>
                                        </div>
                                        <div class="flex flex-col gap-1">
                                            <label class="kt-form-label font-normal text-mono text-xs">Shift</label>
                                            <input type="text" class="kt-input" name="assignments[{{ $index }}][shift]" 
                                                   value="{{ $assignment->shift }}" placeholder="e.g., Morning, Evening, Night"/>
                                        </div>
                                        <div class="flex flex-col gap-1">
                                            <label class="kt-form-label font-normal text-mono text-xs">Notes</label>
                                            <input type="text" class="kt-input" name="assignments[{{ $index }}][notes]" 
                                                   value="{{ $assignment->notes }}" placeholder="Optional notes"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                    </div>
                    <div class="mt-4">
                        <button type="button" class="kt-btn kt-btn-primary" id="add-assignment-btn">
                            <i class="ki-filled ki-plus"></i> Add Assignment
                        </button>
                    </div>
                </div>
            </div>
            <div class="kt-card-footer flex justify-end items-center flex-wrap gap-3">
                <a class="kt-btn kt-btn-outline" href="{{ route('staff-officer.roster.show', $roster->id) }}">Cancel</a>
                <button class="kt-btn kt-btn-primary" type="submit">
                    Save Changes
                    <i class="ki-filled ki-check text-base"></i>
                </button>
            </div>
        </form>
    </div>
@endif

@push('styles')
<style>
    .relative {
        position: relative;
    }
    
    .officer-search-input {
        background: white;
        border: 1px solid #e5e7eb;
        padding: 0.5rem 0.75rem;
    }
    
    .officer-search-input:focus {
        outline: 2px solid #3b82f6;
        outline-offset: -2px;
        z-index: 10;
    }
    
    .assignment-officer-select[size] {
        margin-top: 0;
        border-top: none;
        border-top-left-radius: 0;
        border-top-right-radius: 0;
    }
    
    .assignment-officer-select[size="1"] {
        margin-top: 0;
    }
</style>
@endpush

@push('scripts')
<script>
let assignmentCount = {{ $roster->assignments->count() }};
const allOfficers = @json($allOfficers ?? $officers);
const periodStart = '{{ $roster->roster_period_start->format('Y-m-d') }}';
const periodEnd = '{{ $roster->roster_period_end->format('Y-m-d') }}';

// Get officers available for assignments (excludes OIC and 2IC)
function getAvailableOfficersForAssignments() {
    const oicSelect = document.getElementById('oic_officer_id');
    const secondIcSelect = document.getElementById('second_in_command_officer_id');
    const oicId = oicSelect ? oicSelect.value : null;
    const secondIcId = secondIcSelect ? secondIcSelect.value : null;
    
    return allOfficers.filter(officer => {
        return officer.id != oicId && officer.id != secondIcId;
    });
}

// Assignment template
function createAssignmentTemplate(index) {
    const availableOfficers = getAvailableOfficersForAssignments();
    const officersHtml = availableOfficers.map(officer => 
        `<option value="${officer.id}" data-search-text="${(officer.initials + ' ' + officer.surname + ' ' + officer.service_number).toLowerCase()}">${officer.initials} ${officer.surname} (${officer.service_number})</option>`
    ).join('');
    
    return `
        <div class="kt-card shadow-none bg-muted/30 border border-input" data-assignment-index="${index}">
            <div class="kt-card-content p-4 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-mono">Assignment #${index + 1}</span>
                    <button type="button" class="kt-btn kt-btn-sm kt-btn-ghost text-danger remove-assignment-btn" data-index="${index}">
                        <i class="ki-filled ki-trash"></i> Remove
                    </button>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono text-xs">Officer <span class="text-danger">*</span></label>
                        <div class="relative">
                            <input type="text" 
                                   class="kt-input officer-search-input" 
                                   placeholder="Search officer by name or service number..." 
                                   data-select-id="assignment-officer-${index}"
                                   autocomplete="off">
                            <select class="kt-input assignment-officer-select" 
                                    name="assignments[${index}][officer_id]" 
                                    id="assignment-officer-${index}"
                                    required>
                                <option value="">Select Officer</option>
                                ${officersHtml}
                            </select>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono text-xs">Duty Date</label>
                        <input type="date" class="kt-input" name="assignments[${index}][duty_date]" 
                               min="${periodStart}" max="${periodEnd}"/>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono text-xs">Shift</label>
                        <input type="text" class="kt-input" name="assignments[${index}][shift]" placeholder="e.g., Morning, Evening, Night"/>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono text-xs">Notes</label>
                        <input type="text" class="kt-input" name="assignments[${index}][notes]" placeholder="Optional notes"/>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Add assignment
function addAssignment() {
    const container = document.getElementById('assignments-container');
    const assignmentHtml = createAssignmentTemplate(assignmentCount);
    container.insertAdjacentHTML('beforeend', assignmentHtml);
    assignmentCount++;
    updateRemoveButtons();
    // Make sure new assignment dropdown excludes OIC/2IC
    updateAssignmentDropdowns();
}

// Remove assignment
function removeAssignment(index) {
    const assignment = document.querySelector(`[data-assignment-index="${index}"]`);
    if (assignment) {
        assignment.remove();
        updateAssignmentNumbers();
    }
}

// Update assignment numbers
function updateAssignmentNumbers() {
    const assignments = document.querySelectorAll('[data-assignment-index]');
    assignments.forEach((assignment, index) => {
        const oldIndex = assignment.getAttribute('data-assignment-index');
        assignment.setAttribute('data-assignment-index', index);
        
        const title = assignment.querySelector('.text-mono');
        if (title) {
            title.textContent = `Assignment #${index + 1}`;
        }
        
        // Update all inputs and selects
        assignment.querySelectorAll('input, select').forEach(input => {
            const name = input.getAttribute('name');
            if (name && name.includes('assignments[')) {
                const newName = name.replace(/assignments\[\d+\]/, `assignments[${index}]`);
                input.setAttribute('name', newName);
            }
        });
    });
    assignmentCount = assignments.length;
    updateRemoveButtons();
}

// Update remove buttons
function updateRemoveButtons() {
    const removeButtons = document.querySelectorAll('.remove-assignment-btn');
    removeButtons.forEach(btn => {
        btn.replaceWith(btn.cloneNode(true));
    });
    document.querySelectorAll('.remove-assignment-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const index = parseInt(this.getAttribute('data-index'));
            removeAssignment(index);
        });
    });
}

// Update assignment dropdowns to exclude OIC and 2IC
function updateAssignmentDropdowns() {
    const availableOfficers = getAvailableOfficersForAssignments();
    const availableOfficerIds = availableOfficers.map(o => o.id);
    
    // Update all assignment officer selects
    document.querySelectorAll('.assignment-officer-select').forEach(select => {
        const currentValue = select.value;
        const oicSelect = document.getElementById('oic_officer_id');
        const secondIcSelect = document.getElementById('second_in_command_officer_id');
        const oicId = oicSelect ? oicSelect.value : null;
        const secondIcId = secondIcSelect ? secondIcSelect.value : null;
        
        // Clear and rebuild options
        select.innerHTML = '<option value="">Select Officer</option>';
        
        allOfficers.forEach(officer => {
            // Skip if officer is OIC or 2IC
            if (officer.id == oicId || officer.id == secondIcId) {
                return;
            }
            
            const option = document.createElement('option');
            option.value = officer.id;
            option.textContent = `${officer.initials} ${officer.surname} (${officer.service_number})`;
            
            // Restore selected value if it's still valid
            if (currentValue == officer.id) {
                option.selected = true;
            }
            
            select.appendChild(option);
        });
        
        // If current selection is no longer valid (became OIC/2IC), clear it
        if (currentValue && (currentValue == oicId || currentValue == secondIcId)) {
            select.value = '';
        }
    });
}

// Prevent OIC from being selected as 2IC and vice versa
function updateOic2icOptions() {
    const oicSelect = document.getElementById('oic_officer_id');
    const secondIcSelect = document.getElementById('second_in_command_officer_id');
    
    const oicValue = oicSelect.value;
    const secondIcValue = secondIcSelect.value;
    
    // Update 2IC options - exclude OIC
    Array.from(secondIcSelect.options).forEach(option => {
        if (option.value && option.value === oicValue) {
            option.disabled = true;
            option.style.display = 'none';
            if (secondIcSelect.value === oicValue) {
                secondIcSelect.value = '';
            }
        } else {
            option.disabled = false;
            option.style.display = '';
        }
    });
    
    // Update OIC options - exclude 2IC
    Array.from(oicSelect.options).forEach(option => {
        if (option.value && option.value === secondIcValue) {
            option.disabled = true;
            option.style.display = 'none';
            if (oicSelect.value === secondIcValue) {
                oicSelect.value = '';
            }
        } else {
            option.disabled = false;
            option.style.display = '';
        }
    });
    
    // Update assignment dropdowns when OIC/2IC changes
    updateAssignmentDropdowns();
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('add-assignment-btn').addEventListener('click', addAssignment);
    updateRemoveButtons();
    
    // Unit dropdown handling
    const unitSelectEdit = document.getElementById('unit-select-edit');
    const unitCustomInputEdit = document.getElementById('unit-custom-input-edit');
    
    if (unitSelectEdit && unitCustomInputEdit) {
        unitSelectEdit.addEventListener('change', function() {
            if (this.value === '__NEW__') {
                unitCustomInputEdit.classList.remove('hidden');
                unitCustomInputEdit.required = true;
                unitSelectEdit.required = false;
                unitCustomInputEdit.focus();
            } else {
                unitCustomInputEdit.classList.add('hidden');
                unitCustomInputEdit.required = false;
                unitSelectEdit.required = true;
                if (this.value) {
                    unitCustomInputEdit.value = '';
                }
            }
        });
        
        // Handle form submission - same logic as create form
        const form = document.getElementById('roster-edit-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (unitSelectEdit.value === '__NEW__') {
                    if (!unitCustomInputEdit.value.trim()) {
                        e.preventDefault();
                        alert('Please enter a unit name');
                        unitCustomInputEdit.focus();
                        return false;
                    }
                    // Set the custom unit value to the select
                    unitSelectEdit.value = unitCustomInputEdit.value.trim();
                }
            });
        }
    }
    
    // Add event listeners for OIC and 2IC validation
    const oicSelect = document.getElementById('oic_officer_id');
    const secondIcSelect = document.getElementById('second_in_command_officer_id');
    
    if (oicSelect && secondIcSelect) {
        oicSelect.addEventListener('change', updateOic2icOptions);
        secondIcSelect.addEventListener('change', updateOic2icOptions);
        updateOic2icOptions(); // Initial check - this also calls updateAssignmentDropdowns()
    } else {
        // If OIC/2IC selects don't exist, still filter assignments on load
        updateAssignmentDropdowns();
    }
    
    // Form validation before submit
    document.getElementById('roster-edit-form').addEventListener('submit', function(e) {
        const oicValue = oicSelect.value;
        const secondIcValue = secondIcSelect.value;
        
        if (oicValue && secondIcValue && oicValue === secondIcValue) {
            e.preventDefault();
            alert('The Officer in Charge (OIC) cannot be the same as the Second In Command (2IC). Please select different officers.');
            return false;
        }
    });

    // Initialize searchable selects for officer assignments
    function initializeOfficerSearch() {
        document.querySelectorAll('.officer-search-input').forEach(searchInput => {
            const selectId = searchInput.getAttribute('data-select-id');
            const select = document.getElementById(selectId);
            
            if (!select) return;
            
            // Hide search input initially
            searchInput.style.display = 'none';
            
            // Show search input when select is clicked/focused
            select.addEventListener('mousedown', function(e) {
                e.preventDefault();
                searchInput.style.display = 'block';
                searchInput.style.position = 'absolute';
                searchInput.style.top = '0';
                searchInput.style.left = '0';
                searchInput.style.width = '100%';
                searchInput.style.zIndex = '10';
                searchInput.focus();
            });
            
            select.addEventListener('focus', function() {
                searchInput.style.display = 'block';
                searchInput.style.position = 'absolute';
                searchInput.style.top = '0';
                searchInput.style.left = '0';
                searchInput.style.width = '100%';
                searchInput.style.zIndex = '10';
            });
            
            // Filter options based on search input
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                const options = select.querySelectorAll('option');
                let visibleCount = 0;
                
                options.forEach(option => {
                    if (option.value === '') {
                        option.style.display = '';
                        visibleCount++;
                        return;
                    }
                    
                    const searchText = option.getAttribute('data-search-text') || option.textContent.toLowerCase();
                    if (searchText.includes(searchTerm)) {
                        option.style.display = '';
                        visibleCount++;
                    } else {
                        option.style.display = 'none';
                    }
                });
                
                // Expand select to show filtered options
                if (visibleCount > 1 && searchTerm) {
                    select.size = Math.min(visibleCount, 10);
                    select.style.position = 'relative';
                    select.style.zIndex = '11';
                } else {
                    select.size = 1;
                }
            });
            
            // Handle selection
            select.addEventListener('change', function() {
                if (this.value) {
                    // Update search input to show selected value
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption) {
                        searchInput.value = selectedOption.textContent;
                    }
                    searchInput.style.display = 'none';
                    select.size = 1;
                    select.style.position = '';
                    select.style.zIndex = '';
                    
                    // Reset all options visibility
                    select.querySelectorAll('option').forEach(opt => {
                        opt.style.display = '';
                    });
                }
            });
            
            // Allow keyboard navigation
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowDown' || e.key === 'Enter') {
                    e.preventDefault();
                    select.focus();
                    if (select.size === 1) {
                        select.size = Math.min(select.querySelectorAll('option:not([style*="display: none"])').length, 10);
                    }
                }
            });
            
            // Hide search when clicking outside
            document.addEventListener('click', function(e) {
                if (!select.contains(e.target) && !searchInput.contains(e.target)) {
                    searchInput.style.display = 'none';
                    select.size = 1;
                    select.style.position = '';
                    select.style.zIndex = '';
                }
            });
        });
    }
    
    // Initialize on page load
    initializeOfficerSearch();
    
    // Re-initialize when new assignments are added
    const originalAddAssignment = window.addAssignment || addAssignment;
    window.addAssignment = function() {
        originalAddAssignment();
        setTimeout(initializeOfficerSearch, 100);
    };
});
</script>
@endpush
@endsection

