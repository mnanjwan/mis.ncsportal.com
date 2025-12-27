@extends('layouts.app')

@section('title', 'Edit APER Form')
@section('page-title', 'Edit APER Form')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.aper-forms') }}">APER Forms</a>
    <span>/</span>
    <span class="text-primary">Edit</span>
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

    <!-- Draft Notice -->
    <div class="kt-card bg-warning/10 border border-warning/20">
        <div class="kt-card-content p-4">
            <div class="flex items-center gap-3">
                <i class="ki-filled ki-information text-warning text-xl"></i>
                <div>
                    <p class="text-sm font-medium text-warning">Editing Draft Form</p>
                    <p class="text-xs text-secondary-foreground mt-1">
                        You are editing a draft form. You can save your changes and continue editing, or submit when ready.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <form action="{{ route('officer.aper-forms.update', $form->id) }}" method="POST" id="aper-form">
        @csrf
        @method('PUT')
        
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
                    <a href="{{ route('officer.aper-forms.show', $form->id) }}" class="kt-btn kt-btn-secondary">
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
                <select name="${namePrefix}[${index}][type]" class="kt-input text-sm">
                    <option value="">Select...</option>
                    <option value="Hospitalisation">Hospitalisation</option>
                    <option value="Treatment Abroad">Treatment Received Abroad</option>
                    <option value="Sick Leave">Sick Leave</option>
                </select>
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
</script>
@endsection

