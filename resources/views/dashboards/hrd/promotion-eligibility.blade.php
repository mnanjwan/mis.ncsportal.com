@extends('layouts.app')

@section('title', 'Promotion Eligibility')
@section('page-title', 'Promotion Eligibility')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <span class="text-primary">Promotion Eligibility</span>
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

    <!-- Eligibility Lists Card -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Promotion Eligibility Lists</h3>
            <div class="kt-card-toolbar">
                <a href="{{ route('hrd.promotion-eligibility.create') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                    <i class="ki-filled ki-plus"></i> Generate Eligibility List
                </a>
            </div>
        </div>
        <div class="kt-card-content">
            <!-- Desktop Table View -->
            <div class="hidden lg:block">
                <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'year', 'sort_order' => request('sort_by') === 'year' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Year
                                            @if(request('sort_by') === 'year')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'officers_count', 'sort_order' => request('sort_by') === 'officers_count' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Officers Count
                                            @if(request('sort_by') === 'officers_count')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_order' => request('sort_by') === 'created_at' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Generated
                                            @if(request('sort_by') === 'created_at' || !request('sort_by'))
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
                            @forelse($lists as $list)
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="py-3 px-4">
                                        <span class="text-sm font-medium text-foreground">
                                            Year: {{ $list->year ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $list->officers_count ?? 0 }} officers
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $list->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('hrd.promotion-eligibility.show', $list->id) }}" 
                                               class="kt-btn kt-btn-sm kt-btn-ghost">
                                                View List
                                            </a>
                                            @if(($list->officers_count ?? 0) == 0)
                                                <button type="button" 
                                                        data-kt-modal-toggle="#delete-modal-{{ $list->id }}"
                                                        class="kt-btn kt-btn-sm kt-btn-danger">
                                                    <i class="ki-filled ki-trash"></i> Delete
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-12 text-center">
                                        <i class="ki-filled ki-arrow-up text-4xl text-muted-foreground mb-4"></i>
                                        <p class="text-secondary-foreground mb-4">No eligibility lists found</p>
                                        <a href="{{ route('hrd.promotion-eligibility.create') }}" class="kt-btn kt-btn-primary">
                                            Generate First List
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
                    @forelse($lists as $list)
                        <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                                    <i class="ki-filled ki-arrow-up text-success text-xl"></i>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-semibold text-foreground">
                                        Year: {{ $list->year ?? 'N/A' }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        {{ $list->officers_count ?? 0 }} officers
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        Generated: {{ $list->created_at->format('d/m/Y') }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('hrd.promotion-eligibility.show', $list->id) }}" 
                                   class="kt-btn kt-btn-sm kt-btn-ghost">
                                    View
                                </a>
                                @if(($list->officers_count ?? 0) == 0)
                                    <button type="button" 
                                            data-kt-modal-toggle="#delete-modal-{{ $list->id }}"
                                            class="kt-btn kt-btn-sm kt-btn-danger">
                                        <i class="ki-filled ki-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <i class="ki-filled ki-arrow-up text-4xl text-muted-foreground mb-4"></i>
                            <p class="text-secondary-foreground mb-4">No eligibility lists found</p>
                            <a href="{{ route('hrd.promotion-eligibility.create') }}" class="kt-btn kt-btn-primary">
                                Generate First List
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Pagination -->
            @if($lists->hasPages())
                <div class="mt-6 pt-4 border-t border-border">
                    {{ $lists->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modals -->
@foreach($lists as $list)
    @if(($list->officers_count ?? 0) == 0)
        <div class="kt-modal" data-kt-modal="true" id="delete-modal-{{ $list->id }}">
            <div class="kt-modal-content max-w-[400px]">
                <div class="kt-modal-header py-4 px-5">
                    <h3 class="kt-modal-title">Confirm Deletion</h3>
                    <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                        <i class="ki-filled ki-cross"></i>
                    </button>
                </div>
                <div class="kt-modal-body py-5 px-5">
                    <p class="text-sm text-secondary-foreground">
                        Are you sure you want to delete the promotion eligibility list for year <strong>{{ $list->year }}</strong>? 
                        This action cannot be undone.
                    </p>
                </div>
                <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                    <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                        Cancel
                    </button>
                    <form action="{{ route('hrd.promotion-eligibility.destroy', $list->id) }}" method="POST" class="inline">
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
