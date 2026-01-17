@extends('layouts.app')

@section('title', 'Retirement List')
@section('page-title', 'Retirement List')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <span class="text-primary">Retirement List</span>
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

    <!-- Retirement Lists Card -->
    <div class="kt-card overflow-hidden">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Retirement Lists</h3>
            <div class="kt-card-toolbar">
                <a href="{{ route('hrd.retirement-list.generate') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                    <i class="ki-filled ki-plus"></i> Generate Retirement List
                </a>
            </div>
        </div>
        <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
            <!-- Mobile scroll hint -->
            <div class="block md:hidden px-4 py-3 bg-muted/50 border-b border-border">
                <div class="flex items-center gap-2 text-xs text-secondary-foreground">
                    <i class="ki-filled ki-arrow-left-right"></i>
                    <span>Swipe left to view more columns</span>
                </div>
            </div>

            <!-- Table with horizontal scroll wrapper -->
            <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                <table class="kt-table" style="min-width: 900px; width: 100%;">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'year', 'sort_order' => request('sort_by') === 'year' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Retirement Year
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
                                            {{ $list->year ?? 'N/A' }}
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
                                            <a href="{{ route('hrd.retirement-list.show', $list->id) }}" 
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
                                        <i class="ki-filled ki-calendar text-4xl text-muted-foreground mb-4"></i>
                                        <p class="text-secondary-foreground mb-4">No retirement lists found</p>
                                        <a href="{{ route('hrd.retirement-list.generate') }}" class="kt-btn kt-btn-primary">
                                            Generate First List
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
            </div>

            <div class="flex flex-col gap-4">
                @forelse($lists as $list)
                    <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center justify-center size-12 rounded-full bg-warning/10">
                                <i class="ki-filled ki-calendar text-warning text-xl"></i>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-semibold text-foreground">
                                    Retirement Year: {{ $list->year ?? 'N/A' }}
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
                            <a href="{{ route('hrd.retirement-list.show', $list->id) }}" 
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
                        <i class="ki-filled ki-calendar text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground mb-4">No retirement lists found</p>
                        <a href="{{ route('hrd.retirement-list.generate') }}" class="kt-btn kt-btn-primary">
                            Generate First List
                        </a>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <x-pagination :paginator="$lists->withQueryString()" item-name="lists" />
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
                        Are you sure you want to delete the retirement list for year <strong>{{ $list->year }}</strong>? 
                        This action cannot be undone.
                    </p>
                </div>
                <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                    <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                        Cancel
                    </button>
                    <form action="{{ route('hrd.retirement-list.destroy', $list->id) }}" method="POST" class="inline">
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

<style>
    /* Prevent page from expanding beyond viewport on mobile */
    @media (max-width: 768px) {
        body {
            overflow-x: hidden;
        }

        .kt-card {
            max-width: 100vw;
        }
    }

    /* Smooth scrolling for mobile */
    .table-scroll-wrapper {
        position: relative;
        max-width: 100%;
    }

    /* Custom scrollbar for webkit browsers */
    .scrollbar-thin::-webkit-scrollbar {
        height: 8px;
    }

    .scrollbar-thin::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .scrollbar-thin::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .scrollbar-thin::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>
@endsection
