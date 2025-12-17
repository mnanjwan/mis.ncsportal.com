@extends('layouts.app')

@section('title', 'Course Details')
@section('page-title', 'Course Details')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.courses') }}">Course Nominations</a>
    <span>/</span>
    <span class="text-primary">View Course</span>
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

    <!-- Back Button -->
    <div class="flex items-center justify-between">
        <a href="{{ route('hrd.courses') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
            <i class="ki-filled ki-arrow-left"></i> Back to Courses
        </a>
        @if(!$course->is_completed)
            <a href="{{ route('hrd.courses.edit', $course->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                <i class="ki-filled ki-notepad-edit"></i> Edit
            </a>
        @endif
    </div>

    <!-- Course Details -->
    <div class="kt-card">
        <div class="kt-card-content p-5 lg:p-7.5">
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-semibold text-mono">{{ $course->course_name }}</h2>
                    @if($course->is_completed)
                        <span class="kt-badge kt-badge-success kt-badge-sm">
                            Completed
                        </span>
                    @else
                        <span class="kt-badge kt-badge-warning kt-badge-sm">
                            In Progress
                        </span>
                    @endif
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-secondary-foreground">Officer:</span>
                        <span class="font-semibold text-mono ml-2">
                            {{ $course->officer->initials ?? '' }} {{ $course->officer->surname ?? '' }}
                        </span>
                        <div class="text-xs text-secondary-foreground font-mono mt-1">
                            SVC: {{ $course->officer->service_number ?? 'N/A' }}
                        </div>
                    </div>
                    <div>
                        <span class="text-secondary-foreground">Course Type:</span>
                        <span class="font-semibold text-mono ml-2">{{ $course->course_type ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-secondary-foreground">Start Date:</span>
                        <span class="font-semibold text-mono ml-2">
                            {{ $course->start_date ? $course->start_date->format('d/m/Y') : 'N/A' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-secondary-foreground">End Date:</span>
                        <span class="font-semibold text-mono ml-2">
                            {{ $course->end_date ? $course->end_date->format('d/m/Y') : 'N/A' }}
                        </span>
                    </div>
                    @if($course->is_completed && $course->completion_date)
                        <div>
                            <span class="text-secondary-foreground">Completion Date:</span>
                            <span class="font-semibold text-mono ml-2">
                                {{ $course->completion_date->format('d/m/Y') }}
                            </span>
                        </div>
                    @endif
                    <div>
                        <span class="text-secondary-foreground">Nominated By:</span>
                        <span class="font-semibold text-mono ml-2">
                            {{ $course->nominatedBy->email ?? 'N/A' }}
                        </span>
                    </div>
                </div>

                @if($course->notes)
                    <div class="pt-4 border-t border-border">
                        <p class="text-sm text-secondary-foreground">
                            <strong>Notes:</strong> {{ $course->notes }}
                        </p>
                    </div>
                @endif

                @if($course->certificate_url)
                    <div class="pt-4 border-t border-border">
                        <p class="text-sm text-secondary-foreground mb-2">
                            <strong>Certificate:</strong>
                        </p>
                        <a href="{{ $course->certificate_url }}" target="_blank" class="kt-btn kt-btn-sm kt-btn-primary">
                            <i class="ki-filled ki-file"></i> View Certificate
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Mark as Complete (if not completed) -->
    @if(!$course->is_completed)
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Mark Course as Completed</h3>
            </div>
            <div class="kt-card-content">
                <p class="text-sm text-secondary-foreground mb-4">
                    Once marked as completed, this course will be permanently recorded in the officer's record and cannot be deleted.
                </p>
                <form action="{{ route('hrd.courses.complete', $course->id) }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="space-y-2">
                        <label for="completion_date" class="block text-sm font-medium text-foreground">
                            Completion Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" 
                               name="completion_date" 
                               id="completion_date"
                               value="{{ old('completion_date', $course->end_date ? $course->end_date->format('Y-m-d') : '') }}"
                               class="kt-input @error('completion_date') kt-input-error @enderror"
                               min="{{ $course->start_date ? $course->start_date->format('Y-m-d') : '' }}"
                               required>
                        @error('completion_date')
                            <p class="text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="certificate_url" class="block text-sm font-medium text-foreground">
                            Certificate URL (Optional)
                        </label>
                        <input type="url" 
                               name="certificate_url" 
                               id="certificate_url"
                               value="{{ old('certificate_url') }}"
                               class="kt-input @error('certificate_url') kt-input-error @enderror"
                               placeholder="https://...">
                        @error('certificate_url')
                            <p class="text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="completion_notes" class="block text-sm font-medium text-foreground">
                            Completion Notes (Optional)
                        </label>
                        <textarea name="notes" 
                                  id="completion_notes"
                                  rows="3"
                                  class="kt-input @error('notes') kt-input-error @enderror"
                                  placeholder="Additional notes about course completion...">{{ old('notes', $course->notes) }}</textarea>
                        @error('notes')
                            <p class="text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-border">
                        <button type="submit" class="kt-btn kt-btn-success">
                            <i class="ki-filled ki-check"></i>
                            Mark as Completed
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection

