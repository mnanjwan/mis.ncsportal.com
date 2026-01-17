@extends('layouts.app')

@section('title', 'Leave Types')
@section('page-title', 'Leave Types Management')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <span class="text-primary">Leave Types</span>
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

    <!-- Leave Types Card -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Leave Types</h3>
            <div class="kt-card-toolbar">
                <a href="{{ route('hrd.leave-types.create') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                    <i class="ki-filled ki-plus"></i> Create Leave Type
                </a>
            </div>
        </div>
        <div class="kt-card-content">
            <p class="text-sm text-secondary-foreground mb-4">
                Manage leave types and their duration settings. Officers can apply for leave using these configured types.
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
                                            Name
                                            @if(request('sort_by') === 'name' || !request('sort_by'))
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'code', 'sort_order' => request('sort_by') === 'code' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Code
                                            @if(request('sort_by') === 'code')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'duration', 'sort_order' => request('sort_by') === 'duration' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Duration
                                            @if(request('sort_by') === 'duration')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'max_per_year', 'sort_order' => request('sort_by') === 'max_per_year' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Max/Year
                                            @if(request('sort_by') === 'max_per_year')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'applications', 'sort_order' => request('sort_by') === 'applications' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Applications
                                            @if(request('sort_by') === 'applications')
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
                            @forelse($leaveTypes as $leaveType)
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="py-3 px-4">
                                        <span class="text-sm font-medium text-foreground">
                                            {{ $leaveType->name }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground font-mono">
                                        {{ $leaveType->code }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        @if($leaveType->max_duration_days)
                                            {{ $leaveType->max_duration_days }} days
                                        @elseif($leaveType->max_duration_months)
                                            {{ $leaveType->max_duration_months }} months
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $leaveType->max_occurrences_per_year ?? 'Unlimited' }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $leaveType->applications_count ?? 0 }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="kt-badge kt-badge-{{ $leaveType->is_active ? 'success' : 'secondary' }} kt-badge-sm">
                                            {{ $leaveType->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('hrd.leave-types.edit', $leaveType->id) }}" 
                                               class="kt-btn kt-btn-sm kt-btn-ghost">
                                                <i class="ki-filled ki-notepad-edit"></i> Edit
                                            </a>
                                            @if(($leaveType->applications_count ?? 0) == 0)
                                                <button type="button" 
                                                        data-kt-modal-toggle="#delete-modal-{{ $leaveType->id }}"
                                                        class="kt-btn kt-btn-sm kt-btn-danger">
                                                    <i class="ki-filled ki-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center">
                                        <i class="ki-filled ki-calendar text-4xl text-muted-foreground mb-4"></i>
                                        <p class="text-secondary-foreground mb-4">No leave types found</p>
                                        <a href="{{ route('hrd.leave-types.create') }}" class="kt-btn kt-btn-primary">
                                            Create First Leave Type
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
                    @forelse($leaveTypes as $leaveType)
                        <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                                    <i class="ki-filled ki-calendar text-info text-xl"></i>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-semibold text-foreground">
                                        {{ $leaveType->name }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground font-mono">
                                        {{ $leaveType->code }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        @if($leaveType->max_duration_days)
                                            {{ $leaveType->max_duration_days }} days
                                        @elseif($leaveType->max_duration_months)
                                            {{ $leaveType->max_duration_months }} months
                                        @else
                                            N/A
                                        @endif
                                        @if($leaveType->max_occurrences_per_year)
                                            • Max {{ $leaveType->max_occurrences_per_year }}/year
                                        @endif
                                    </span>
                                    <span class="text-xs">
                                        <span class="kt-badge kt-badge-{{ $leaveType->is_active ? 'success' : 'secondary' }} kt-badge-sm">
                                            {{ $leaveType->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                        <span class="text-secondary-foreground ml-2">
                                            {{ $leaveType->applications_count ?? 0 }} applications
                                        </span>
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('hrd.leave-types.edit', $leaveType->id) }}" 
                                   class="kt-btn kt-btn-sm kt-btn-ghost">
                                    <i class="ki-filled ki-notepad-edit"></i>
                                </a>
                                @if(($leaveType->applications_count ?? 0) == 0)
                                    <button type="button" 
                                            data-kt-modal-toggle="#delete-modal-{{ $leaveType->id }}"
                                            class="kt-btn kt-btn-sm kt-btn-danger">
                                        <i class="ki-filled ki-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <i class="ki-filled ki-calendar text-4xl text-muted-foreground mb-4"></i>
                            <p class="text-secondary-foreground mb-4">No leave types found</p>
                            <a href="{{ route('hrd.leave-types.create') }}" class="kt-btn kt-btn-primary">
                                Create First Leave Type
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Pagination -->
            <x-pagination :paginator="$leaveTypes->withQueryString()" item-name="leave types" />
        </div>
    </div>
</div>

<!-- Delete Confirmation Modals -->
@foreach($leaveTypes as $leaveType)
    @if(($leaveType->applications_count ?? 0) == 0)
        <div class="kt-modal" data-kt-modal="true" id="delete-modal-{{ $leaveType->id }}">
            <div class="kt-modal-content max-w-[400px]">
                <div class="kt-modal-header py-4 px-5">
                    <h3 class="kt-modal-title">Confirm Deletion</h3>
                    <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                        <i class="ki-filled ki-cross"></i>
                    </button>
                </div>
                <div class="kt-modal-body py-5 px-5">
                    <p class="text-sm text-secondary-foreground">
                        Are you sure you want to delete the leave type <strong>{{ $leaveType->name }}</strong>? 
                        This action cannot be undone.
                    </p>
                </div>
                <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                    <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                        Cancel
                    </button>
                    <form action="{{ route('hrd.leave-types.destroy', $leaveType->id) }}" method="POST" class="inline">
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

