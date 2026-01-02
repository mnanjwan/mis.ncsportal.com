@extends('layouts.app')

@section('title', 'Create Course')
@section('page-title', 'Create Course')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.system-settings') }}">Settings</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.course-management.index') }}">Courses</a>
    <span>/</span>
    <span class="text-primary">Create</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Form Card -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Create Course</h3>
        </div>
        <div class="kt-card-content">
            <form action="{{ route('hrd.course-management.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Course Name -->
                <div class="space-y-2">
                    <label for="name" class="block text-sm font-medium text-foreground">
                        Course Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           id="name"
                           value="{{ old('name') }}"
                           class="kt-input @error('name') kt-input-error @enderror"
                           placeholder="e.g., Advanced Leadership Training"
                           required>
                    @error('name')
                        <p class="text-sm text-danger">{{ $message }}</p>
                    @enderror
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
                              placeholder="Course description...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Active Status -->
                <div class="space-y-2">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="rounded border-input">
                        <span class="text-sm font-medium text-foreground">Active</span>
                    </label>
                    <p class="text-xs text-secondary-foreground">
                        Only active courses will appear in the course nomination dropdown.
                    </p>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-4 pt-4 border-t border-border">
                    <a href="{{ route('hrd.course-management.index') }}" class="kt-btn kt-btn-ghost">
                        Cancel
                    </a>
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-check"></i>
                        Create Course
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

