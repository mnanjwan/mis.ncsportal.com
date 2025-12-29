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
                <div class="kt-card-toolbar">
                    <button type="button" class="kt-btn kt-btn-sm kt-btn-primary" id="add-assignment-btn">
                        <i class="ki-filled ki-plus"></i> Add Assignment
                    </button>
                </div>
            </div>
            <div class="kt-card-content space-y-6">
                <!-- Leadership Selection -->
                <div class="kt-card shadow-none bg-info/10 border border-info/20">
                    <div class="kt-card-content p-4">
                        <h4 class="text-sm font-semibold text-foreground mb-4">Roster Leadership</h4>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1">
                                <label class="kt-form-label font-normal text-mono text-xs">Officer in Charge (OIC) <span class="text-danger">*</span></label>
                                <select class="kt-input" name="oic_officer_id" id="oic_officer_id" required>
                                    <option value="">Select OIC</option>
                                    @foreach($officers as $officer)
                                        <option value="{{ $officer->id }}" {{ $roster->oic_officer_id == $officer->id ? 'selected' : '' }}>
                                            {{ $officer->initials }} {{ $officer->surname }} ({{ $officer->service_number }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="kt-form-label font-normal text-mono text-xs">Second In Command (2IC)</label>
                                <select class="kt-input" name="second_in_command_officer_id" id="second_in_command_officer_id">
                                    <option value="">Select 2IC (Optional)</option>
                                    @foreach($officers as $officer)
                                        <option value="{{ $officer->id }}" {{ $roster->second_in_command_officer_id == $officer->id ? 'selected' : '' }}>
                                            {{ $officer->initials }} {{ $officer->surname }} ({{ $officer->service_number }})
                                        </option>
                                    @endforeach
                                </select>
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
                                            <select class="kt-input" name="assignments[{{ $index }}][officer_id]" required>
                                                <option value="">Select Officer</option>
                                                @foreach($officers as $officer)
                                                    <option value="{{ $officer->id }}" {{ $assignment->officer_id == $officer->id ? 'selected' : '' }}>
                                                        {{ $officer->initials }} {{ $officer->surname }} ({{ $officer->service_number }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="flex flex-col gap-1">
                                            <label class="kt-form-label font-normal text-mono text-xs">Duty Date <span class="text-danger">*</span></label>
                                            <input type="date" class="kt-input" name="assignments[{{ $index }}][duty_date]" 
                                                   value="{{ $assignment->duty_date->format('Y-m-d') }}" 
                                                   min="{{ $roster->roster_period_start->format('Y-m-d') }}"
                                                   max="{{ $roster->roster_period_end->format('Y-m-d') }}" required/>
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

@push('scripts')
<script>
let assignmentCount = {{ $roster->assignments->count() }};
const officers = @json($officers);
const periodStart = '{{ $roster->roster_period_start->format('Y-m-d') }}';
const periodEnd = '{{ $roster->roster_period_end->format('Y-m-d') }}';

// Assignment template
function createAssignmentTemplate(index) {
    const officersHtml = officers.map(officer => 
        `<option value="${officer.id}">${officer.initials} ${officer.surname} (${officer.service_number})</option>`
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
                        <select class="kt-input" name="assignments[${index}][officer_id]" required>
                            <option value="">Select Officer</option>
                            ${officersHtml}
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono text-xs">Duty Date <span class="text-danger">*</span></label>
                        <input type="date" class="kt-input" name="assignments[${index}][duty_date]" 
                               min="${periodStart}" max="${periodEnd}" required/>
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
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('add-assignment-btn').addEventListener('click', addAssignment);
    updateRemoveButtons();
    
    // Add event listeners for OIC and 2IC validation
    const oicSelect = document.getElementById('oic_officer_id');
    const secondIcSelect = document.getElementById('second_in_command_officer_id');
    
    if (oicSelect && secondIcSelect) {
        oicSelect.addEventListener('change', updateOic2icOptions);
        secondIcSelect.addEventListener('change', updateOic2icOptions);
        updateOic2icOptions(); // Initial check
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
});
</script>
@endpush
@endsection

