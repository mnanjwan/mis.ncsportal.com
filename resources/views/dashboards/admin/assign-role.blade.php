@extends('layouts.app')

@section('title', 'Assign Role')
@section('page-title', 'Assign Role to Officer')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('admin.dashboard') }}">Admin</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('admin.role-assignments') }}">Role Assignments</a>
    <span>/</span>
    <span class="text-primary">Assign Role</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Assign Role to Officer</h3>
            </div>
            <div class="kt-card-content">
                <form action="{{ route('admin.role-assignments.store') }}" method="POST" class="flex flex-col gap-5">
                    @csrf

                    <!-- Information Banner -->
                    <div class="bg-info/10 border border-info/20 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <i class="ki-filled ki-information-2 text-info text-xl mt-0.5"></i>
                            <div class="flex-1">
                                <h4 class="font-semibold text-info mb-2">Role Assignment Guidelines</h4>
                                <ul class="text-sm text-secondary-foreground space-y-1 list-disc list-inside">
                                    <li>You can assign roles to officers in your command: <strong>{{ $adminCommand->name }}</strong></li>
                                    <li>You can assign the following roles: <strong>Staff Officer</strong>, <strong>Area Controller</strong>, and <strong>DC Admin</strong></li>
                                    <li>All role assignments are specific to your command</li>
                                    <li>The officer will receive email and app notifications when assigned</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Command Display (Read-only) -->
                    <div>
                        <label class="block text-sm font-medium mb-1">Command</label>
                        <input type="text" 
                               value="{{ $adminCommand->name }}" 
                               class="kt-input w-full bg-muted/50" 
                               readonly>
                        <p class="text-xs text-secondary-foreground mt-1">You can only assign roles within your assigned command</p>
                    </div>

                    <!-- Officer Selection -->
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Officer <span class="text-danger">*</span>
                        </label>
                        <select name="officer_id" id="officer_id" class="kt-input w-full" required>
                            <option value="">Select Officer</option>
                            @foreach($officers as $officer)
                                <option value="{{ $officer->id }}">
                                    {{ trim(($officer->initials ?? '') . ' ' . ($officer->surname ?? '') . ' ' . ($officer->first_name ?? '')) }} 
                                    ({{ $officer->service_number ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                        @error('officer_id')
                            <p class="text-danger text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-secondary-foreground mt-1">Select an officer from your command</p>
                    </div>

                    <!-- Role Selection -->
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Role <span class="text-danger">*</span>
                        </label>
                        <select name="role_id" id="role_id" class="kt-input w-full" required>
                            <option value="">Select Role</option>
                            @foreach($assignableRoles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        @error('role_id')
                            <p class="text-danger text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-secondary-foreground mt-1">Select a role to assign</p>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                        <a href="{{ route('admin.role-assignments') }}" class="kt-btn kt-btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-check"></i> Assign Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

