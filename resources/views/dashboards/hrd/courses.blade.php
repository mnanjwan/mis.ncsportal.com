@extends('layouts.app')

@section('title', 'Course Nominations')
@section('page-title', 'Course Nominations')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <span class="text-primary">Course Nominations</span>
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
            <h3 class="kt-card-title">Course Nominations</h3>
            <div class="kt-card-toolbar flex items-center gap-2">
                <a href="{{ route('hrd.courses.print') }}" 
                   class="kt-btn kt-btn-sm kt-btn-primary"
                   target="_blank">
                    <i class="ki-filled ki-printer"></i> Print All
                </a>
                <a href="{{ route('hrd.courses.create') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                    <i class="ki-filled ki-plus"></i> Nominate Officer
                </a>
            </div>
        </div>
        <div class="kt-card-content">
            <p class="text-sm text-secondary-foreground mb-4">
                Nominate officers for courses and track completion. Completed courses are automatically recorded in the officer's record.
            </p>

            <!-- Desktop Table View -->
            <div class="hidden lg:block">
                <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'officer', 'sort_order' => request('sort_by') === 'officer' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Officer
                                            @if(request('sort_by') === 'officer')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'course_name', 'sort_order' => request('sort_by') === 'course_name' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Course Name
                                            @if(request('sort_by') === 'course_name')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'course_type', 'sort_order' => request('sort_by') === 'course_type' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Course Type
                                            @if(request('sort_by') === 'course_type')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'start_date', 'sort_order' => request('sort_by') === 'start_date' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Start Date
                                            @if(request('sort_by') === 'start_date' || !request('sort_by'))
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'status', 'sort_order' => request('sort_by') === 'status' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Status
                                            @if(request('sort_by') === 'status')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                        <tbody>
                            @forelse($courses as $course)
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="py-3 px-4">
                                        <span class="text-sm font-medium text-foreground">
                                            {{ $course->officer->initials ?? '' }} {{ $course->officer->surname ?? '' }}
                                        </span>
                                        <div class="text-xs text-secondary-foreground font-mono">
                                            {{ $course->officer->service_number ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $course->course_name }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $course->course_type ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $course->start_date ? $course->start_date->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($course->is_completed)
                                            <span class="kt-badge kt-badge-success kt-badge-sm">
                                                Completed
                                            </span>
                                            @if($course->completion_date)
                                                <div class="text-xs text-secondary-foreground mt-1">
                                                    {{ $course->completion_date->format('d/m/Y') }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="kt-badge kt-badge-warning kt-badge-sm">
                                                In Progress
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('hrd.courses.show', $course->id) }}" 
                                               class="kt-btn kt-btn-sm kt-btn-ghost">
                                                View
                                            </a>
                                            @if(!$course->is_completed)
                                                <a href="{{ route('hrd.courses.edit', $course->id) }}" 
                                                   class="kt-btn kt-btn-sm kt-btn-ghost">
                                                    <i class="ki-filled ki-notepad-edit"></i>
                                                </a>
                                                <button type="button" 
                                                        data-kt-modal-toggle="#delete-modal-{{ $course->id }}"
                                                        class="kt-btn kt-btn-sm kt-btn-danger">
                                                    <i class="ki-filled ki-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center">
                                        <i class="ki-filled ki-book text-4xl text-muted-foreground mb-4"></i>
                                        <p class="text-secondary-foreground mb-4">No course nominations found</p>
                                        <a href="{{ route('hrd.courses.create') }}" class="kt-btn kt-btn-primary">
                                            Nominate First Officer
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile Card View -->
            <div class="lg:hidden">
                <div class="flex flex-col gap-4">
                    @forelse($courses as $course)
                        <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                                    <i class="ki-filled ki-book text-info text-xl"></i>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-semibold text-foreground">
                                        {{ $course->officer->initials ?? '' }} {{ $course->officer->surname ?? '' }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground font-mono">
                                        {{ $course->officer->service_number ?? 'N/A' }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        {{ $course->course_name }}
                                    </span>
                                    <span class="text-xs">
                                        @if($course->is_completed)
                                            <span class="kt-badge kt-badge-success kt-badge-sm">Completed</span>
                                        @else
                                            <span class="kt-badge kt-badge-warning kt-badge-sm">In Progress</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('hrd.courses.show', $course->id) }}" 
                                   class="kt-btn kt-btn-sm kt-btn-ghost">
                                    View
                                </a>
                                @if(!$course->is_completed)
                                    <button type="button" 
                                            data-kt-modal-toggle="#delete-modal-{{ $course->id }}"
                                            class="kt-btn kt-btn-sm kt-btn-danger">
                                        <i class="ki-filled ki-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <i class="ki-filled ki-book text-4xl text-muted-foreground mb-4"></i>
                            <p class="text-secondary-foreground mb-4">No course nominations found</p>
                            <a href="{{ route('hrd.courses.create') }}" class="kt-btn kt-btn-primary">
                                Nominate First Officer
                            </a>
                        </div>
                    @endforelse
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
    @if(!$course->is_completed)
        <div class="kt-modal" data-kt-modal="true" id="delete-modal-{{ $course->id }}">
            <div class="kt-modal-content max-w-[400px]">
                <div class="kt-modal-header py-4 px-5">
                    <h3 class="kt-modal-title">Confirm Deletion</h3>
                    <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                        <i class="ki-filled ki-cross"></i>
                    </button>
                </div>
                <div class="kt-modal-body py-5 px-5">
                    <p class="text-sm text-secondary-foreground">
                        Are you sure you want to delete the course nomination for <strong>{{ $course->course_name }}</strong>? 
                        This action cannot be undone.
                    </p>
                </div>
                <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                    <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                        Cancel
                    </button>
                    <form action="{{ route('hrd.courses.destroy', $course->id) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="kt-btn kt-btn-danger">
                            <i class="ki-filled ki-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach
@endsection

