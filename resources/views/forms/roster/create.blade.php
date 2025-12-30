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
                                @if(isset($predefinedUnits) && count($predefinedUnits) > 0)
                                    <optgroup label="Standard Units">
                                        @foreach($predefinedUnits as $unit)
                                            <option value="{{ $unit }}" {{ old('unit') === $unit ? 'selected' : '' }}>{{ $unit }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                                @if(isset($customUnits) && count($customUnits) > 0)
                                    <optgroup label="Custom Units">
                                        @foreach($customUnits as $unit)
                                            <option value="{{ $unit }}" {{ old('unit') === $unit ? 'selected' : '' }}>{{ $unit }}</option>
                                        @endforeach
                                    </optgroup>
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
                    
                    <div class="kt-card bg-muted/50 border border-input">
                        <div class="kt-card-content p-4">
                            <p class="text-sm text-secondary-foreground">
                                <strong>Note:</strong> After creating the roster, you can add officer assignments by editing the roster.
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
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const unitSelect = document.getElementById('unit-select');
    const unitCustomInput = document.getElementById('unit-custom-input');
    
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
    
    // Handle form submission
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        if (unitSelect.value === '__NEW__') {
            if (!unitCustomInput.value.trim()) {
                e.preventDefault();
                alert('Please enter a unit name');
                unitCustomInput.focus();
                return false;
            }
            // Set the custom unit value to the select
            unitSelect.value = unitCustomInput.value.trim();
        }
    });
});
</script>
@endpush
@endsection

