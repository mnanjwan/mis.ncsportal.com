@extends('layouts.app')

@section('title', 'Course Details')
@section('page-title', 'Course Details')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.system-settings') }}">Settings</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.course-management.index') }}">Courses</a>
    <span>/</span>
    <span class="text-primary">View</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">{{ $course->name }}</h3>
            <div class="kt-card-toolbar">
                <a href="{{ route('hrd.course-management.edit', $course->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                    <i class="ki-filled ki-notepad-edit"></i> Edit
                </a>
            </div>
        </div>
        <div class="kt-card-content">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-sm font-semibold text-secondary-foreground">Course Name</label>
                    <p class="text-sm text-foreground mt-1">{{ $course->name }}</p>
                </div>
                <div>
                    <label class="text-sm font-semibold text-secondary-foreground">Status</label>
                    <p class="mt-1">
                        <span class="kt-badge kt-badge-{{ $course->is_active ? 'success' : 'secondary' }} kt-badge-sm">
                            {{ $course->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                </div>
                <div class="md:col-span-2">
                    <label class="text-sm font-semibold text-secondary-foreground">Description</label>
                    <p class="text-sm text-foreground mt-1">{{ $course->description ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-sm font-semibold text-secondary-foreground">Nominations Count</label>
                    <p class="text-sm text-foreground mt-1">{{ $course->nominations_count ?? 0 }}</p>
                </div>
                <div>
                    <label class="text-sm font-semibold text-secondary-foreground">Created At</label>
                    <p class="text-sm text-foreground mt-1">{{ $course->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

