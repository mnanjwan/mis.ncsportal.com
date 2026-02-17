@extends('layouts.app')

@section('title', 'Create Duty Roster')
@section('page-title', 'Create Duty Roster')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.dashboard') }}">Staff Officer</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.roster') }}">Duty Roster</a>
    <span>/</span>
    <span class="text-primary">Create</span>
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

@php
    $user = auth()->user();
    $staffOfficerRole = $user->roles()
        ->where('name', 'Staff Officer')
        ->wherePivot('is_active', true)
        ->first();
    $commandId = $staffOfficerRole?->pivot->command_id ?? null;
    $command = $commandId ? \App\Models\Command::find($commandId) : null;
@endphp

@if(!$command)
    <div class="kt-card">
        <div class="kt-card-content">
            <div class="text-center py-12">
                <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                <p class="text-secondary-foreground">You are not assigned to a command. Please contact HRD for command assignment.</p>
            </div>
        </div>
    </div>
@else
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 lg:gap-9 mb-5 lg:mb-10">
        <div class="xl:col-span-2 space-y-5">
            <!-- Info Card -->
            <div class="kt-card bg-info/10 border border-info/20">
                <div class="kt-card-content p-5">
                    <div class="flex items-center gap-3">
                        <i class="ki-filled ki-information text-2xl text-info"></i>
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-semibold text-foreground">Duty Roster</span>
                            <span class="text-xs text-secondary-foreground">
                                Create a duty roster for {{ $command->name }}. The roster will need to be approved by DC Admin.
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Form -->
            <form class="kt-card" method="POST" action="{{ route('staff-officer.roster.store') }}">
                @csrf
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Duty Roster Form</h3>
                </div>
                <div class="kt-card-content space-y-5">
                    <input type="hidden" name="command_id" value="{{ $command->id }}"/>
                    
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono">Unit/Title <span class="text-danger">*</span></label>
                        <div class="flex gap-2">
                            <select class="kt-input flex-1" name="unit" id="unit-select" required>
                                <option value="">Select Unit</option>
                                @if(isset($allUnits) && count($allUnits) > 0)
                                    @foreach($allUnits as $unit)
                                        <option value="{{ $unit }}" {{ old('unit') === $unit ? 'selected' : '' }}>{{ $unit }}</option>
                                    @endforeach
                                @endif
                                <option value="__NEW__">➕ Create New Unit</option>
                            </select>
                            <input type="text" class="kt-input flex-1 hidden" name="unit_custom" id="unit-custom-input" placeholder="Enter new unit name" value="{{ old('unit_custom') }}"/>
                        </div>
                        <p class="text-xs text-secondary-foreground mt-1">Select a unit from the list or create a new one</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label font-normal text-mono">Roster Period Start <span class="text-danger">*</span></label>
                            <input type="date" class="kt-input" name="roster_period_start" value="{{ old('roster_period_start', date('Y-m-01')) }}" required/>
                        </div>
                        
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label font-normal text-mono">Roster Period End <span class="text-danger">*</span></label>
                            <input type="date" class="kt-input" name="roster_period_end" value="{{ old('roster_period_end', date('Y-m-t')) }}" required/>
                        </div>
                    </div>

                    <!-- Leadership Selection -->
                    <div class="kt-card shadow-none bg-info/10 border border-info/20">
                        <div class="kt-card-content p-4">
                            <h4 class="text-sm font-semibold text-foreground mb-4">Roster Leadership</h4>
                            <div class="grid sm:grid-cols-2 gap-4">
                                <!-- OIC Selection -->
                                <div>
                                    <label class="block text-sm font-medium mb-1">
                                        Officer in Charge (OIC)
                                    </label>
                                    <div class="relative">
                                        <input type="hidden" name="oic_officer_id" id="oic_officer_id" value="{{ old('oic_officer_id') }}">
                                        <input type="hidden" name="reassign_oic_roster_id" id="reassign_oic_roster_id" value="">
                                        <button type="button" 
                                                id="oic_select_trigger" 
                                                class="kt-input w-full text-left flex items-center justify-between cursor-pointer {{ $errors->has('oic_officer_id') ? 'border-danger' : '' }}">
                                            <span id="oic_select_text">
                                                @if(old('oic_officer_id'))
                                                    @php $oic = $officers->find(old('oic_officer_id')); @endphp
                                                    {{ $oic ? $oic->initials . ' ' . $oic->surname . ' (' . $oic->service_number . ')' : 'Select OIC' }}
                                                @else
                                                    Select OIC
                                                @endif
                                            </span>
                                            <i class="ki-filled ki-down text-gray-400"></i>
                                        </button>
                                        <div id="oic_dropdown" 
                                             class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                            <div class="p-3 border-b border-input">
                                                <input type="text" id="oic_search_input" class="kt-input w-full" placeholder="Search officers..." autocomplete="off">
                                            </div>
                                            <div id="oic_options" class="max-h-60 overflow-y-auto">
                                                @foreach($officers as $officerData)
                                                    @php
                                                        $officer = $officerData['officer'];
                                                        $isAssigned = $officerData['is_assigned'];
                                                        $assignedRoster = $officerData['assigned_roster'];
                                                    @endphp
                                                    <div class="p-3 border-b border-input last:border-0 officer-option {{ $isAssigned ? 'opacity-60 cursor-pointer hover:bg-muted/50' : 'hover:bg-muted/50 cursor-pointer' }}" 
                                                         data-id="{{ $officer->id }}" 
                                                         data-name="{{ $officer->initials }} {{ $officer->surname }}"
                                                         data-service="{{ $officer->service_number }}"
                                                         data-rank="{{ $officer->substantive_rank }}"
                                                         data-assigned="{{ $isAssigned ? 'true' : 'false' }}"
                                                         data-roster-name="{{ $isAssigned && $assignedRoster ? $assignedRoster['display_name'] : '' }}"
                                                         data-roster-id="{{ $isAssigned && $assignedRoster ? $assignedRoster['id'] : '' }}">
                                                        <div class="flex items-center justify-between">
                                                            <div class="flex-1">
                                                                <div class="text-sm text-foreground">{{ $officer->initials }} {{ $officer->surname }}</div>
                                                                <div class="text-xs text-secondary-foreground">{{ $officer->service_number }} - {{ $officer->substantive_rank }}</div>
                                                            </div>
                                                            @if($isAssigned && $assignedRoster)
                                                                <div class="ml-2 text-xs text-warning flex items-center gap-1">
                                                                    <i class="ki-filled ki-information text-warning"></i>
                                                                    <span>Assigned to {{ $assignedRoster['display_name'] }}</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- 2IC Selection -->
                                <div>
                                    <label class="block text-sm font-medium mb-1">
                                        Second In Command (2IC)
                                    </label>
                                    <div class="relative">
                                        <input type="hidden" name="second_in_command_officer_id" id="second_in_command_officer_id" value="{{ old('second_in_command_officer_id') }}">
                                        <input type="hidden" name="reassign_2ic_roster_id" id="reassign_2ic_roster_id" value="">
                                        <button type="button" 
                                                id="second_ic_select_trigger" 
                                                class="kt-input w-full text-left flex items-center justify-between cursor-pointer {{ $errors->has('second_in_command_officer_id') ? 'border-danger' : '' }}">
                                            <span id="second_ic_select_text">
                                                @if(old('second_in_command_officer_id'))
                                                    @php $second_ic = $officers->find(old('second_in_command_officer_id')); @endphp
                                                    {{ $second_ic ? $second_ic->initials . ' ' . $second_ic->surname . ' (' . $second_ic->service_number . ')' : 'Select 2IC (Optional)' }}
                                                @else
                                                    Select 2IC (Optional)
                                                @endif
                                            </span>
                                            <i class="ki-filled ki-down text-gray-400"></i>
                                        </button>
                                        <div id="second_ic_dropdown" 
                                             class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                            <div class="p-3 border-b border-input">
                                                <input type="text" id="second_ic_search_input" class="kt-input w-full" placeholder="Search officers..." autocomplete="off">
                                            </div>
                                            <div id="second_ic_options" class="max-h-60 overflow-y-auto">
                                                @foreach($officers as $officerData)
                                                    @php
                                                        $officer = $officerData['officer'];
                                                        $isAssigned = $officerData['is_assigned'];
                                                        $assignedRoster = $officerData['assigned_roster'];
                                                    @endphp
                                                    <div class="p-3 border-b border-input last:border-0 officer-option {{ $isAssigned ? 'opacity-60 cursor-not-allowed bg-muted/30' : 'hover:bg-muted/50 cursor-pointer' }}" 
                                                         data-id="{{ $officer->id }}" 
                                                         data-name="{{ $officer->initials }} {{ $officer->surname }}"
                                                         data-service="{{ $officer->service_number }}"
                                                         data-rank="{{ $officer->substantive_rank }}"
                                                         data-assigned="{{ $isAssigned ? 'true' : 'false' }}"
                                                         data-roster-name="{{ $isAssigned && $assignedRoster ? $assignedRoster['display_name'] : '' }}"
                                                         data-roster-id="{{ $isAssigned && $assignedRoster ? $assignedRoster['id'] : '' }}">
                                                        <div class="flex items-center justify-between">
                                                            <div class="flex-1">
                                                                <div class="text-sm text-foreground">{{ $officer->initials }} {{ $officer->surname }}</div>
                                                                <div class="text-xs text-secondary-foreground">{{ $officer->service_number }} - {{ $officer->substantive_rank }}</div>
                                                            </div>
                                                            @if($isAssigned && $assignedRoster)
                                                                <div class="ml-2 text-xs text-warning flex items-center gap-1">
                                                                    <i class="ki-filled ki-information text-warning"></i>
                                                                    <span>Assigned to {{ $assignedRoster['display_name'] }}</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="kt-card bg-muted/50 border border-input">
                        <div class="kt-card-content p-4">
                            <p class="text-sm text-secondary-foreground">
                                <strong>Note:</strong> After creating the roster, you can add regular officer assignments by editing the roster.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="kt-card-footer flex justify-end items-center flex-wrap gap-3">
                    <a class="kt-btn kt-btn-outline" href="{{ route('staff-officer.roster') }}">Cancel</a>
                    <button class="kt-btn kt-btn-primary" type="submit">
                        Create Roster
                        <i class="ki-filled ki-check text-base"></i>
                    </button>
                </div>
            </form>
        </div>
        
        <div class="xl:col-span-1">
            <!-- Instructions Card -->
            <div class="kt-card bg-accent/50">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Instructions</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-3 text-sm">
                        <div class="kt-card shadow-none bg-info/10 border border-info/20">
                            <div class="kt-card-content p-4">
                                <p class="text-xs text-secondary-foreground mb-2">
                                    <strong class="text-foreground">Roster Process:</strong>
                                </p>
                                <ul class="text-xs text-secondary-foreground mt-2 list-disc list-inside space-y-1">
                                    <li>Select the start and end dates for the roster period</li>
                                    <li>Create the roster (status: DRAFT)</li>
                                    <li>Edit the roster to add officer assignments</li>
                                    <li>Submit for DC Admin approval</li>
                                </ul>
                            </div>
                        </div>
                        <p class="text-xs text-secondary-foreground">
                            The roster will be created as a draft. You can add officer assignments and submit it for approval later.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Site standard confirmation modal for reassignment -->
    <div class="kt-modal hidden" data-kt-modal="true" id="confirm-reassign-modal">
        <div class="kt-modal-content max-w-[500px]">
            <div class="kt-modal-header py-4 px-5">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center size-10 rounded-full bg-warning/10" id="confirm-reassign-modal-icon">
                        <i class="ki-filled ki-information text-warning text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-foreground" id="confirm-reassign-modal-title">Reassign Officer</h3>
                </div>
                <button type="button" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true" aria-label="Close">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <div class="kt-modal-body py-5 px-5">
                <p class="text-sm text-secondary-foreground whitespace-pre-line" id="confirm-reassign-modal-message"></p>
            </div>
            <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                <button type="button" class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true" id="confirm-reassign-modal-cancel">Cancel</button>
                <button type="button" class="kt-btn kt-btn-primary" id="confirm-reassign-modal-confirm">
                    <span class="kt-menu-icon"><i class="ki-filled ki-check"></i></span>
                    <span>Confirm</span>
                </button>
            </div>
        </div>
    </div>
@endif

@push('styles')
<style>
    .relative {
        position: relative;
    }
</style>
@endpush

@push('scripts')
<script>
let _pendingReassignConfirm = null;

function showConfirmReassignModal(message, onConfirm) {
    const modal = document.getElementById('confirm-reassign-modal');
    const modalMessage = document.getElementById('confirm-reassign-modal-message');
    if (!modal || !modalMessage) return;
    modalMessage.textContent = message;
    _pendingReassignConfirm = onConfirm;
    if (typeof KTModal !== 'undefined') {
        const instance = KTModal.getInstance(modal) || new KTModal(modal);
        instance.show();
    } else {
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
    }
}

document.addEventListener('click', function confirmReassignModalHandler(e) {
    const confirmBtn = e.target.closest('#confirm-reassign-modal-confirm');
    if (!confirmBtn || !_pendingReassignConfirm) return;
    e.preventDefault();
    e.stopPropagation();
    const fn = _pendingReassignConfirm;
    _pendingReassignConfirm = null;
    if (typeof fn === 'function') fn();
    const modal = document.getElementById('confirm-reassign-modal');
    if (modal) {
        if (typeof KTModal !== 'undefined') {
            const instance = KTModal.getInstance(modal) || new KTModal(modal);
            instance.hide();
        } else {
            modal.classList.add('hidden');
        }
    }
}, true);

document.addEventListener('DOMContentLoaded', function() {
    const unitSelect = document.getElementById('unit-select');
    const unitCustomInput = document.getElementById('unit-custom-input');
    
    if (unitSelect) {
        unitSelect.addEventListener('change', function() {
            if (this.value === '__NEW__') {
                unitCustomInput.classList.remove('hidden');
                unitCustomInput.required = true;
                unitSelect.required = false;
                unitCustomInput.focus();
            } else {
                unitCustomInput.classList.add('hidden');
                unitCustomInput.required = false;
                unitSelect.required = true;
                unitCustomInput.value = '';
            }
        });
    }
    
    // Handle form submission
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (unitSelect && unitSelect.value === '__NEW__') {
                if (!unitCustomInput.value.trim()) {
                    e.preventDefault();
                    alert('Please enter a unit name');
                    unitCustomInput.focus();
                    return false;
                }
            }
        });
    }

    // Searchable Dropdown Logic
    function setupSearchableDropdown(prefix) {
        const trigger = document.getElementById(`${prefix}_select_trigger`);
        const text = document.getElementById(`${prefix}_select_text`);
        // Handle special case for second_ic which uses second_in_command_officer_id
        const hiddenInputId = prefix === 'second_ic' ? 'second_in_command_officer_id' : `${prefix}_officer_id`;
        const hiddenInput = document.getElementById(hiddenInputId);
        const dropdown = document.getElementById(`${prefix}_dropdown`);
        const searchInput = document.getElementById(`${prefix}_search_input`);
        const optionsContainer = document.getElementById(`${prefix}_options`);
        const options = optionsContainer.querySelectorAll('.officer-option');

        if (!trigger || !hiddenInput) return;

        trigger.addEventListener('click', function() {
            dropdown.classList.toggle('hidden');
            if (!dropdown.classList.contains('hidden')) {
                searchInput.focus();
            }
        });

        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            options.forEach(option => {
                const name = option.dataset.name.toLowerCase();
                const service = option.dataset.service.toLowerCase();
                const rank = option.dataset.rank.toLowerCase();
                if (name.includes(searchTerm) || service.includes(searchTerm) || rank.includes(searchTerm)) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            });
        });

        const reassignInput = document.getElementById(prefix === 'oic' ? 'reassign_oic_roster_id' : 'reassign_2ic_roster_id');

        optionsContainer.addEventListener('click', function(e) {
            const option = e.target.closest('.officer-option');
            if (option) {
                const isAssigned = option.dataset.assigned === 'true';
                const rosterName = option.dataset.rosterName || '';
                const rosterId = option.dataset.rosterId || '';
                const id = option.dataset.id;
                const name = option.dataset.name;
                const service = option.dataset.service;

                const applySelection = () => {
                    if (reassignInput) reassignInput.value = isAssigned ? rosterId : '';
                    hiddenInput.value = id;
                    text.textContent = `${name} (${service})`;
                    dropdown.classList.add('hidden');
                    searchInput.value = '';
                    options.forEach(opt => opt.style.display = 'block');
                };

                if (isAssigned) {
                    const msg = `This officer is already assigned to: ${rosterName}.\n\nReassign to this roster? They will be removed from the previous roster and the officer plus relevant parties will be notified.`;
                    showConfirmReassignModal(msg, applySelection);
                } else {
                    applySelection();
                }
            }
        });

        // Close when clicking outside
        document.addEventListener('click', function(e) {
            if (!trigger.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    }

    setupSearchableDropdown('oic');
    setupSearchableDropdown('second_ic');
});
</script>
@endpush
@endsection

