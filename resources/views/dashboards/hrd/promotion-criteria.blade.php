@extends('layouts.app')

@section('title', 'Promotion Criteria')
@section('page-title', 'Promotion Criteria Configuration')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <span class="text-primary">Promotion Criteria</span>
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

    <!-- Promotion Criteria Card -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Promotion Eligibility Criteria</h3>
            <div class="kt-card-toolbar">
                <a href="{{ route('hrd.promotion-criteria.create') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                    <i class="ki-filled ki-plus"></i> Add Criteria
                </a>
            </div>
        </div>
        <div class="kt-card-content">
            <p class="text-sm text-secondary-foreground mb-4">
                Configure the minimum years in rank required for officers to be eligible for promotion to each rank.
            </p>

            <!-- Desktop Table View -->
            <div class="hidden lg:block">
                <div class="overflow-x-auto">
                    <table class="kt-table w-full">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    Rank
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    Years in Rank Required
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    Status
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    Created By
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    Created Date
                                </th>
                                <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($criteria as $criterion)
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="py-3 px-4">
                                        <span class="text-sm font-medium text-foreground">
                                            {{ $criterion->rank }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ number_format($criterion->years_in_rank_required, 2) }} years
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="kt-badge kt-badge-{{ $criterion->is_active ? 'success' : 'secondary' }} kt-badge-sm">
                                            {{ $criterion->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $criterion->createdBy->email ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $criterion->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ route('hrd.promotion-criteria.edit', $criterion->id) }}" 
                                           class="kt-btn kt-btn-sm kt-btn-ghost">
                                            <i class="ki-filled ki-notepad-edit"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center">
                                        <i class="ki-filled ki-setting-2 text-4xl text-muted-foreground mb-4"></i>
                                        <p class="text-secondary-foreground mb-4">No promotion criteria configured</p>
                                        <a href="{{ route('hrd.promotion-criteria.create') }}" class="kt-btn kt-btn-primary">
                                            Add First Criteria
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
                    @forelse($criteria as $criterion)
                        <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                                    <i class="ki-filled ki-setting-2 text-success text-xl"></i>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-semibold text-foreground">
                                        {{ $criterion->rank }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        {{ number_format($criterion->years_in_rank_required, 2) }} years required
                                    </span>
                                    <span class="text-xs">
                                        <span class="kt-badge kt-badge-{{ $criterion->is_active ? 'success' : 'secondary' }} kt-badge-sm">
                                            {{ $criterion->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        Created: {{ $criterion->created_at->format('d/m/Y') }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <a href="{{ route('hrd.promotion-criteria.edit', $criterion->id) }}" 
                                   class="kt-btn kt-btn-sm kt-btn-ghost">
                                    <i class="ki-filled ki-notepad-edit"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <i class="ki-filled ki-setting-2 text-4xl text-muted-foreground mb-4"></i>
                            <p class="text-secondary-foreground mb-4">No promotion criteria configured</p>
                            <a href="{{ route('hrd.promotion-criteria.create') }}" class="kt-btn kt-btn-primary">
                                Add First Criteria
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Pagination -->
            @if($criteria->hasPages())
                <div class="mt-6 pt-4 border-t border-border">
                    {{ $criteria->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

