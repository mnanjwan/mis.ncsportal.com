@extends('layouts.app')

@section('title', 'Onboarding - Step 2: Employment Details')
@section('page-title', 'Onboarding - Step 2: Employment Details')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Progress Indicator -->
    <div class="kt-card">
        <div class="kt-card-content p-5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full bg-success text-white flex items-center justify-center font-semibold">✓</div>
                    <span class="text-sm text-success">Personal Information</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full bg-primary text-white flex items-center justify-center font-semibold">2</div>
                    <span class="text-sm font-medium">Employment Details</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full bg-muted text-secondary-foreground flex items-center justify-center font-semibold">3</div>
                    <span class="text-sm text-secondary-foreground">Banking Information</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full bg-muted text-secondary-foreground flex items-center justify-center font-semibold">4</div>
                    <span class="text-sm text-secondary-foreground">Next of Kin</span>
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
                    <strong>Please fix the following errors:</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
            
            <form id="onboarding-step2-form" method="POST" action="{{ route('onboarding.step2.save') }}" class="flex flex-col gap-5">
                @csrf
                
                <div class="grid lg:grid-cols-2 gap-5">
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Date of First Appointment <span class="text-danger">*</span></label>
                        <input type="date" name="date_of_first_appointment" class="kt-input" value="{{ old('date_of_first_appointment', $savedData['date_of_first_appointment'] ?? '') }}" required/>
                        <span class="error-message text-danger text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Date of Present Appointment <span class="text-danger">*</span></label>
                        <input type="date" name="date_of_present_appointment" class="kt-input" value="{{ old('date_of_present_appointment', $savedData['date_of_present_appointment'] ?? '') }}" required/>
                        <span class="error-message text-danger text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Substantive Rank <span class="text-danger">*</span></label>
                        <select name="substantive_rank" class="kt-input" required>
                            <option value="">Select Rank...</option>
                            <option value="Assistant Superintendent" {{ old('substantive_rank', $savedData['substantive_rank'] ?? '') == 'Assistant Superintendent' ? 'selected' : '' }}>Assistant Superintendent</option>
                            <option value="Deputy Superintendent" {{ old('substantive_rank', $savedData['substantive_rank'] ?? '') == 'Deputy Superintendent' ? 'selected' : '' }}>Deputy Superintendent</option>
                            <option value="Superintendent" {{ old('substantive_rank', $savedData['substantive_rank'] ?? '') == 'Superintendent' ? 'selected' : '' }}>Superintendent</option>
                            <option value="Chief Superintendent" {{ old('substantive_rank', $savedData['substantive_rank'] ?? '') == 'Chief Superintendent' ? 'selected' : '' }}>Chief Superintendent</option>
                            <option value="Assistant Comptroller" {{ old('substantive_rank', $savedData['substantive_rank'] ?? '') == 'Assistant Comptroller' ? 'selected' : '' }}>Assistant Comptroller</option>
                            <option value="Deputy Comptroller" {{ old('substantive_rank', $savedData['substantive_rank'] ?? '') == 'Deputy Comptroller' ? 'selected' : '' }}>Deputy Comptroller</option>
                            <option value="Comptroller" {{ old('substantive_rank', $savedData['substantive_rank'] ?? '') == 'Comptroller' ? 'selected' : '' }}>Comptroller</option>
                            <option value="Assistant Comptroller General" {{ old('substantive_rank', $savedData['substantive_rank'] ?? '') == 'Assistant Comptroller General' ? 'selected' : '' }}>Assistant Comptroller General</option>
                            <option value="Deputy Comptroller General" {{ old('substantive_rank', $savedData['substantive_rank'] ?? '') == 'Deputy Comptroller General' ? 'selected' : '' }}>Deputy Comptroller General</option>
                        </select>
                        <span class="error-message text-danger text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Salary Grade Level <span class="text-danger">*</span></label>
                        <select name="salary_grade_level" class="kt-input" required>
                            <option value="">Select Grade Level...</option>
                            <option value="GL 01" {{ old('salary_grade_level', $savedData['salary_grade_level'] ?? '') == 'GL 01' ? 'selected' : '' }}>GL 01</option>
                            <option value="GL 02" {{ old('salary_grade_level', $savedData['salary_grade_level'] ?? '') == 'GL 02' ? 'selected' : '' }}>GL 02</option>
                            <option value="GL 03" {{ old('salary_grade_level', $savedData['salary_grade_level'] ?? '') == 'GL 03' ? 'selected' : '' }}>GL 03</option>
                            <option value="GL 04" {{ old('salary_grade_level', $savedData['salary_grade_level'] ?? '') == 'GL 04' ? 'selected' : '' }}>GL 04</option>
                            <option value="GL 05" {{ old('salary_grade_level', $savedData['salary_grade_level'] ?? '') == 'GL 05' ? 'selected' : '' }}>GL 05</option>
                            <option value="GL 06" {{ old('salary_grade_level', $savedData['salary_grade_level'] ?? '') == 'GL 06' ? 'selected' : '' }}>GL 06</option>
                            <option value="GL 07" {{ old('salary_grade_level', $savedData['salary_grade_level'] ?? '') == 'GL 07' ? 'selected' : '' }}>GL 07</option>
                            <option value="GL 08" {{ old('salary_grade_level', $savedData['salary_grade_level'] ?? '') == 'GL 08' ? 'selected' : '' }}>GL 08</option>
                            <option value="GL 09" {{ old('salary_grade_level', $savedData['salary_grade_level'] ?? '') == 'GL 09' ? 'selected' : '' }}>GL 09</option>
                            <option value="GL 10" {{ old('salary_grade_level', $savedData['salary_grade_level'] ?? '') == 'GL 10' ? 'selected' : '' }}>GL 10</option>
                            <option value="GL 11" {{ old('salary_grade_level', $savedData['salary_grade_level'] ?? '') == 'GL 11' ? 'selected' : '' }}>GL 11</option>
                            <option value="GL 12" {{ old('salary_grade_level', $savedData['salary_grade_level'] ?? '') == 'GL 12' ? 'selected' : '' }}>GL 12</option>
                            <option value="GL 13" {{ old('salary_grade_level', $savedData['salary_grade_level'] ?? '') == 'GL 13' ? 'selected' : '' }}>GL 13</option>
                            <option value="GL 14" {{ old('salary_grade_level', $savedData['salary_grade_level'] ?? '') == 'GL 14' ? 'selected' : '' }}>GL 14</option>
                            <option value="GL 15" {{ old('salary_grade_level', $savedData['salary_grade_level'] ?? '') == 'GL 15' ? 'selected' : '' }}>GL 15</option>
                            <option value="GL 16" {{ old('salary_grade_level', $savedData['salary_grade_level'] ?? '') == 'GL 16' ? 'selected' : '' }}>GL 16</option>
                            <option value="GL 17" {{ old('salary_grade_level', $savedData['salary_grade_level'] ?? '') == 'GL 17' ? 'selected' : '' }}>GL 17</option>
                        </select>
                        <span class="error-message text-danger text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Command/Present Station <span class="text-danger">*</span></label>
                        <div class="relative">
                            <input type="text" 
                                   id="command_search" 
                                   class="kt-input w-full" 
                                   placeholder="Search command..."
                                   autocomplete="off">
                            <input type="hidden" 
                                   name="command_id" 
                                   id="command_id" 
                                   value="{{ old('command_id', $savedData['command_id'] ?? '') }}"
                                   required>
                            <div id="command_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                <!-- Options will be populated by JavaScript -->
                            </div>
                        </div>
                        <div id="selected_command" class="mt-2 p-2 bg-muted/50 rounded-lg hidden">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium" id="selected_command_name"></span>
                                <button type="button" 
                                        class="kt-btn kt-btn-sm kt-btn-ghost text-danger"
                                        onclick="clearCommandSelection()">
                                    <i class="ki-filled ki-cross"></i>
                                </button>
                            </div>
                    </div>
                        <span class="error-message text-danger text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Date Posted to Station <span class="text-danger">*</span></label>
                        <input type="date" name="date_posted_to_station" class="kt-input" value="{{ old('date_posted_to_station', $savedData['date_posted_to_station'] ?? '') }}" required/>
                        <span class="error-message text-danger text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Unit</label>
                        <input type="text" name="unit" class="kt-input" value="{{ old('unit', $savedData['unit'] ?? '') }}"/>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Entry Qualification <span class="text-danger">*</span></label>
                        <select name="entry_qualification" class="kt-input" required>
                            <option value="">Select...</option>
                            <option value="WAEC" {{ old('entry_qualification', $savedData['entry_qualification'] ?? '') == 'WAEC' ? 'selected' : '' }}>WAEC</option>
                            <option value="NECO" {{ old('entry_qualification', $savedData['entry_qualification'] ?? '') == 'NECO' ? 'selected' : '' }}>NECO</option>
                            <option value="OND" {{ old('entry_qualification', $savedData['entry_qualification'] ?? '') == 'OND' ? 'selected' : '' }}>OND</option>
                            <option value="HND" {{ old('entry_qualification', $savedData['entry_qualification'] ?? '') == 'HND' ? 'selected' : '' }}>HND</option>
                            <option value="BSc" {{ old('entry_qualification', $savedData['entry_qualification'] ?? '') == 'BSc' ? 'selected' : '' }}>BSc</option>
                            <option value="MSc" {{ old('entry_qualification', $savedData['entry_qualification'] ?? '') == 'MSc' ? 'selected' : '' }}>MSc</option>
                            <option value="PhD" {{ old('entry_qualification', $savedData['entry_qualification'] ?? '') == 'PhD' ? 'selected' : '' }}>PhD</option>
                            <option value="Other" {{ old('entry_qualification', $savedData['entry_qualification'] ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        <span class="error-message text-danger text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Discipline <span class="text-muted">(Optional for WAEC, NECO and Below)</span></label>
                        <input type="text" name="discipline" class="kt-input" value="{{ old('discipline', $savedData['discipline'] ?? '') }}" placeholder="e.g., Computer Science, Accounting"/>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Additional Qualification (Optional)</label>
                        <input type="text" name="additional_qualification" class="kt-input" value="{{ old('additional_qualification', $savedData['additional_qualification'] ?? '') }}"/>
                    </div>
                </div>
                
                <div class="flex items-center justify-end gap-3 pt-5 border-t border-input">
                    <button type="button" onclick="window.location.href='{{ route('onboarding.step1') }}'" class="kt-btn kt-btn-secondary">Previous</button>
                    <button type="submit" class="kt-btn kt-btn-primary">Next: Banking Information</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    // Load commands
    const token = window.API_CONFIG?.token || '{{ auth()->user()?->createToken('token')->plainTextToken ?? '' }}';
    try {
        const res = await fetch('/api/v1/commands', {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        if (res.ok) {
            const data = await res.json();
            const savedCommandId = '{{ old('command_id', $savedData['command_id'] ?? '') }}';
            const savedCommandName = '{{ old('command_name', $savedData['command_name'] ?? '') }}';
            
            if (data.data) {
                window.commands = data.data.map(cmd => ({
                    id: cmd.id,
                    name: cmd.name
                }));
                
                // Initialize searchable select
                initializeCommandSearch();
                
                // If saved command exists, set it
                if (savedCommandId) {
                    const savedCmd = window.commands.find(c => c.id == savedCommandId);
                    if (savedCmd) {
                        document.getElementById('command_search').value = savedCmd.name;
                        document.getElementById('command_id').value = savedCmd.id;
                        document.getElementById('selected_command_name').textContent = savedCmd.name;
                        document.getElementById('selected_command').classList.remove('hidden');
                    } else if (savedCommandName) {
                        document.getElementById('command_search').value = savedCommandName;
                        document.getElementById('selected_command_name').textContent = savedCommandName;
                        document.getElementById('selected_command').classList.remove('hidden');
                    }
                }
            }
        }
    } catch (error) {
        console.error('Error loading commands:', error);
    }
});

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
    document.getElementById('command_search').value = '';
    document.getElementById('command_id').value = '';
    document.getElementById('selected_command').classList.add('hidden');
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
        'command_id': 'Command/Present Station is required',
        'date_posted_to_station': 'Date Posted to Station is required',
        'entry_qualification': 'Entry Qualification is required'
    };

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

    // Validate date logic
    const dofa = document.querySelector('[name="date_of_first_appointment"]')?.value;
    const dopa = document.querySelector('[name="date_of_present_appointment"]')?.value;
    const dopts = document.querySelector('[name="date_posted_to_station"]')?.value;

    if (dofa && dopa && new Date(dofa) > new Date(dopa)) {
        showError('date_of_present_appointment', 'Date of Present Appointment must be after Date of First Appointment');
        isValid = false;
    }

    if (dopa && dopts && new Date(dopa) > new Date(dopts)) {
        showError('date_posted_to_station', 'Date Posted to Station must be after Date of Present Appointment');
        isValid = false;
    }

    return isValid;
}

// Form submission handler
document.getElementById('onboarding-step2-form').addEventListener('submit', function(e) {
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

// Clear errors on input
document.querySelectorAll('#onboarding-step2-form input, #onboarding-step2-form select').forEach(input => {
    input.addEventListener('input', function() {
        clearError(this.name);
    });
    input.addEventListener('change', function() {
        clearError(this.name);
    });
});
</script>
@endpush
@endsection


