@extends('layouts.app')

@section('title', isset($leaveType) ? 'Edit Leave Type' : 'Create Leave Type')
@section('page-title', isset($leaveType) ? 'Edit Leave Type' : 'Create Leave Type')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.leave-types') }}">Leave Types</a>
    <span>/</span>
    <span class="text-primary">{{ isset($leaveType) ? 'Edit' : 'Create' }}</span>
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
            <h3 class="kt-card-title">{{ isset($leaveType) ? 'Edit Leave Type' : 'Create Leave Type' }}</h3>
        </div>
        <div class="kt-card-content">
            <form action="{{ isset($leaveType) ? route('hrd.leave-types.update', $leaveType->id) : route('hrd.leave-types.store') }}" 
                  method="POST" 
                  class="space-y-6">
                @csrf
                @if(isset($leaveType))
                    @method('PUT')
                @endif

                <!-- Name -->
                <div class="space-y-2">
                    <label for="name" class="block text-sm font-medium text-foreground">
                        Leave Type Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           id="name"
                           value="{{ old('name', $leaveType->name ?? '') }}"
                           class="kt-input @error('name') kt-input-error @enderror"
                           placeholder="e.g., Annual Leave"
                           required>
                    @error('name')
                        <p class="text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Code -->
                <div class="space-y-2">
                    <label for="code" class="block text-sm font-medium text-foreground">
                        Code <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           name="code" 
                           id="code"
                           value="{{ old('code', $leaveType->code ?? '') }}"
                           class="kt-input @error('code') kt-input-error @enderror font-mono"
                           placeholder="e.g., ANNUAL_LEAVE"
                           required>
                    @error('code')
                        <p class="text-sm text-danger">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-secondary-foreground">
                        Unique identifier for this leave type (uppercase, underscores allowed).
                    </p>
                </div>

                <!-- Duration Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Max Duration Days -->
                    <div class="space-y-2">
                        <label for="max_duration_days" class="block text-sm font-medium text-foreground">
                            Max Duration (Days)
                        </label>
                        <input type="number" 
                               name="max_duration_days" 
                               id="max_duration_days"
                               min="0"
                               max="365"
                               value="{{ old('max_duration_days', $leaveType->max_duration_days ?? '') }}"
                               class="kt-input @error('max_duration_days') kt-input-error @enderror"
                               placeholder="e.g., 30">
                        @error('max_duration_days')
                            <p class="text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Max Duration Months -->
                    <div class="space-y-2">
                        <label for="max_duration_months" class="block text-sm font-medium text-foreground">
                            Max Duration (Months)
                        </label>
                        <input type="number" 
                               name="max_duration_months" 
                               id="max_duration_months"
                               min="0"
                               max="12"
                               value="{{ old('max_duration_months', $leaveType->max_duration_months ?? '') }}"
                               class="kt-input @error('max_duration_months') kt-input-error @enderror"
                               placeholder="e.g., 2">
                        @error('max_duration_months')
                            <p class="text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <p class="text-xs text-secondary-foreground -mt-2">
                    Specify either days or months (or both). At least one is required.
                </p>

                <!-- Max Occurrences Per Year -->
                <div class="space-y-2">
                    <label for="max_occurrences_per_year" class="block text-sm font-medium text-foreground">
                        Max Occurrences Per Year
                    </label>
                    <input type="number" 
                           name="max_occurrences_per_year" 
                           id="max_occurrences_per_year"
                           min="0"
                           max="100"
                           value="{{ old('max_occurrences_per_year', $leaveType->max_occurrences_per_year ?? '') }}"
                           class="kt-input @error('max_occurrences_per_year') kt-input-error @enderror"
                           placeholder="e.g., 2 (leave empty for unlimited)">
                    @error('max_occurrences_per_year')
                        <p class="text-sm text-danger">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-secondary-foreground">
                        Maximum number of times this leave can be applied per year. Leave empty for unlimited.
                    </p>
                </div>

                <!-- Requires Medical Certificate -->
                <div class="space-y-2">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" 
                               name="requires_medical_certificate" 
                               value="1"
                               {{ old('requires_medical_certificate', isset($leaveType) ? $leaveType->requires_medical_certificate : false) ? 'checked' : '' }}
                               class="kt-checkbox">
                        <span class="text-sm font-medium text-foreground">Requires Medical Certificate</span>
                    </label>
                    <p class="text-xs text-secondary-foreground">
                        Check if officers must provide a medical certificate when applying for this leave type.
                    </p>
                </div>

                <!-- Requires Approval Level -->
                <div class="space-y-2">
                    <label for="requires_approval_level" class="block text-sm font-medium text-foreground">
                        Requires Approval Level
                    </label>
                    <select name="requires_approval_level" 
                            id="requires_approval_level"
                            class="kt-input @error('requires_approval_level') kt-input-error @enderror">
                        <option value="">None (No specific approval level required)</option>
                        <option value="DC Admin" {{ old('requires_approval_level', $leaveType->requires_approval_level ?? '') == 'DC Admin' ? 'selected' : '' }}>
                            DC Admin (Deputy Comptroller Administration)
                        </option>
                        <option value="Area Controller" {{ old('requires_approval_level', $leaveType->requires_approval_level ?? '') == 'Area Controller' ? 'selected' : '' }}>
                            Area Controller (Comptroller)
                        </option>
                        <option value="Staff Officer" {{ old('requires_approval_level', $leaveType->requires_approval_level ?? '') == 'Staff Officer' ? 'selected' : '' }}>
                            Staff Officer
                        </option>
                    </select>
                    @error('requires_approval_level')
                        <p class="text-sm text-danger">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-secondary-foreground">
                        Select the approval level required for this leave type (optional).
                    </p>
                </div>

                <!-- Is Active -->
                <div class="space-y-2">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', isset($leaveType) ? $leaveType->is_active : true) ? 'checked' : '' }}
                               class="kt-checkbox">
                        <span class="text-sm font-medium text-foreground">Active</span>
                    </label>
                    <p class="text-xs text-secondary-foreground">
                        Only active leave types will be available for officers to apply.
                    </p>
                </div>

                <!-- Description -->
                <div class="space-y-2">
                    <label for="description" class="block text-sm font-medium text-foreground">
                        Description
                    </label>
                    <textarea name="description" 
                              id="description"
                              rows="3"
                              class="kt-input @error('description') kt-input-error @enderror"
                              placeholder="Enter a description for this leave type...">{{ old('description', $leaveType->description ?? '') }}</textarea>
                    @error('description')
                        <p class="text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-4 pt-4 border-t border-border">
                    <a href="{{ route('hrd.leave-types') }}" class="kt-btn kt-btn-ghost">
                        Cancel
                    </a>
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-check"></i>
                        {{ isset($leaveType) ? 'Update Leave Type' : 'Create Leave Type' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

