@extends('layouts.app')

@section('title', 'Pending Allocations')
@section('page-title', 'Pending Quarter Allocations')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Filters Card -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Filter Pending Allocations</h3>
        </div>
        <div class="kt-card-content">
            <div class="flex flex-col gap-4">
                <div class="flex flex-col md:flex-row gap-3 items-end">
                    <!-- From Date -->
                    <div class="w-full md:w-48">
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">From Date</label>
                        <input type="date" id="filter-from-date" class="kt-input w-full" value="{{ request('from_date') }}" onchange="applyFilters()">
                    </div>

                    <!-- To Date -->
                    <div class="w-full md:w-48">
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">To Date</label>
                        <input type="date" id="filter-to-date" class="kt-input w-full" value="{{ request('to_date') }}" onchange="applyFilters()">
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-2 flex-shrink-0">
                        <button type="button" onclick="clearFilters()" class="kt-btn kt-btn-outline w-full md:w-auto">
                            <i class="ki-filled ki-cross"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Allocations List -->
    <div class="kt-card overflow-hidden">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Pending Quarter Allocations ({{ $pendingAllocations->count() }})</h3>
        </div>
        <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
            <!-- Table with horizontal scroll wrapper -->
            <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                <table class="kt-table" style="min-width: 1000px; width: 100%;">
                    <thead>
                        <tr class="border-b border-border">
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_order' => request('sort_by') === 'created_at' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                   class="flex items-center gap-1 hover:text-primary transition-colors">
                                    Allocated Date
                                    @if(request('sort_by') === 'created_at' || !request('sort_by'))
                                        <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' || !request('sort_order') ? 'down' : 'up' }} text-xs"></i>
                                    @else
                                        <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'officer_name', 'sort_order' => request('sort_by') === 'officer_name' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                   class="flex items-center gap-1 hover:text-primary transition-colors">
                                    Officer
                                    @if(request('sort_by') === 'officer_name')
                                        <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                    @else
                                        <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'service_number', 'sort_order' => request('sort_by') === 'service_number' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                   class="flex items-center gap-1 hover:text-primary transition-colors">
                                    Service Number
                                    @if(request('sort_by') === 'service_number')
                                        <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                    @else
                                        <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                Rank
                            </th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'quarter_number', 'sort_order' => request('sort_by') === 'quarter_number' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                   class="flex items-center gap-1 hover:text-primary transition-colors">
                                    Quarter Number
                                    @if(request('sort_by') === 'quarter_number')
                                        <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                    @else
                                        <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                Quarter Type
                            </th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                Allocated By
                            </th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                Allocation Date
                            </th>
                            <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody id="pending-allocations-list">
                        @forelse($pendingAllocations as $allocation)
                            <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    {{ $allocation->created_at ? $allocation->created_at->format('d/m/Y H:i') : 'N/A' }}
                                </td>
                                <td class="py-3 px-4 text-sm font-medium text-foreground" style="white-space: nowrap;">
                                    {{ ($allocation->officer->initials ?? '') . ' ' . ($allocation->officer->surname ?? '') }}
                                </td>
                                <td class="py-3 px-4" style="white-space: nowrap;">
                                    <span class="text-sm font-mono text-foreground">{{ $allocation->officer->service_number ?? 'N/A' }}</span>
                                </td>
                                <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    {{ $allocation->officer->substantive_rank ?? 'N/A' }}
                                </td>
                                <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    {{ $allocation->quarter->quarter_number ?? 'N/A' }}
                                </td>
                                <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    {{ $allocation->quarter->quarter_type ?? 'N/A' }}
                                </td>
                                <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    @if($allocation->allocatedBy)
                                        @if($allocation->allocatedBy->officer)
                                            {{ ($allocation->allocatedBy->officer->initials ?? '') . ' ' . ($allocation->allocatedBy->officer->surname ?? '') }}
                                        @else
                                            {{ $allocation->allocatedBy->email ?? 'N/A' }}
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    {{ $allocation->allocated_date ? $allocation->allocated_date->format('d/m/Y') : 'N/A' }}
                                </td>
                                <td class="py-3 px-4 text-right" style="white-space: nowrap;">
                                    <span class="kt-badge kt-badge-warning kt-badge-sm">
                                        <i class="ki-filled ki-time"></i> PENDING
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center">
                                    <i class="ki-filled ki-check-circle text-4xl text-success mb-4"></i>
                                    <p class="text-secondary-foreground">No pending allocations found</p>
                                    <p class="text-xs text-secondary-foreground mt-2">All officers have responded to their quarter allocations</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

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

@push('scripts')
<script>
function applyFilters() {
    const fromDate = document.getElementById('filter-from-date').value;
    const toDate = document.getElementById('filter-to-date').value;
    
    // Build URL with filters
    let url = new URL(window.location.href);
    if (fromDate) {
        url.searchParams.set('from_date', fromDate);
    } else {
        url.searchParams.delete('from_date');
    }
    if (toDate) {
        url.searchParams.set('to_date', toDate);
    } else {
        url.searchParams.delete('to_date');
    }
    
    window.location.href = url.toString();
}

function clearFilters() {
    document.getElementById('filter-from-date').value = '';
    document.getElementById('filter-to-date').value = '';
    window.location.href = window.location.pathname;
}
</script>
@endpush
@endsection

