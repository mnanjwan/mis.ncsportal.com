@extends('layouts.app')

@section('title', 'Course Management')
@section('page-title', 'Course Management')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.system-settings') }}">Settings</a>
    <span>/</span>
    <span class="text-primary">Courses</span>
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

    <!-- Courses Card -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Courses</h3>
            <div class="kt-card-toolbar">
                <a href="{{ route('hrd.course-management.create') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                    <i class="ki-filled ki-plus"></i> Create Course
                </a>
            </div>
        </div>
        <div class="kt-card-content">
            <p class="text-sm text-secondary-foreground mb-4">
                Manage course master data. These courses can be selected when nominating officers for training.
            </p>

            <!-- Desktop Table View -->
            <div class="hidden lg:block">
                <div class="overflow-x-auto">
                    <table class="kt-table w-full">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_order' => request('sort_by') === 'name' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center gap-1 hover:text-primary transition-colors">
                                        Course Name
                                        @if(request('sort_by') === 'name' || !request('sort_by'))
                                            <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                        @else
                                            <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Description</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Status</th>
                                <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($courses as $course)
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="py-3 px-4">
                                        <span class="text-sm font-medium text-foreground">{{ $course->name }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ Str::limit($course->description ?? 'N/A', 50) }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="kt-badge kt-badge-{{ $course->is_active ? 'success' : 'secondary' }} kt-badge-sm">
                                            {{ $course->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('hrd.course-management.edit', $course->id) }}" 
                                               class="kt-btn kt-btn-sm kt-btn-ghost">
                                                <i class="ki-filled ki-notepad-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    data-kt-modal-toggle="#delete-modal-{{ $course->id }}"
                                                    class="kt-btn kt-btn-sm kt-btn-danger">
                                                <i class="ki-filled ki-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-12 text-center">
                                        <i class="ki-filled ki-book text-4xl text-muted-foreground mb-4"></i>
                                        <p class="text-secondary-foreground mb-4">No courses found</p>
                                        <a href="{{ route('hrd.course-management.create') }}" class="kt-btn kt-btn-primary">
                                            Create First Course
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if($courses->hasPages())
                <div class="mt-6 pt-4 border-t border-border">
                    {{ $courses->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modals -->
@foreach($courses as $course)
    <div class="kt-modal" data-kt-modal="true" id="delete-modal-{{ $course->id }}">
        <div class="kt-modal-content max-w-[400px]">
            <div class="kt-modal-header py-4 px-5">
                <h3 class="text-lg font-semibold text-foreground">Confirm Deletion</h3>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <div class="kt-modal-body py-5 px-5">
                <p class="text-sm text-secondary-foreground">
                    Are you sure you want to delete the course <strong>{{ $course->name }}</strong>? 
                    This action cannot be undone.
                </p>
            </div>
            <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">Cancel</button>
                <form action="{{ route('hrd.course-management.destroy', $course->id) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="kt-btn kt-btn-danger">
                        <i class="ki-filled ki-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endsection

