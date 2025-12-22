@extends('layouts.app')

@section('title', 'Officers Approaching Preretirement Leave')
@section('page-title', 'Officers Approaching Preretirement Leave')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('cgc.dashboard') }}">CGC</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('cgc.preretirement-leave.index') }}">Preretirement Leave</a>
    <span>/</span>
    <span class="text-primary">Approaching</span>
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

    <!-- Info Card -->
    <div class="kt-card bg-info/10 border border-info/20">
        <div class="kt-card-content p-4">
            <div class="flex items-start gap-3">
                <i class="ki-filled ki-information text-info text-xl mt-0.5"></i>
                <div>
                    <p class="text-sm font-medium text-info mb-1">Officers Approaching Preretirement Leave</p>
                    <p class="text-xs text-secondary-foreground">
                        These officers will be automatically placed on preretirement leave 3 months before their retirement date. 
                        You can proactively approve them to work during preretirement period if needed.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Filter -->
    <div class="kt-card">
        <div class="kt-card-content p-5">
            <form method="GET" action="{{ route('cgc.preretirement-leave.approaching') }}" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search by service number or name..." 
                       class="kt-input flex-1">
                <button type="submit" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-magnifier"></i> Search
                </button>
                @if(request('search'))
                    <a href="{{ route('cgc.preretirement-leave.approaching') }}" class="kt-btn kt-btn-secondary">
                        <i class="ki-filled ki-cross"></i> Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Approaching Officers List -->
    <div class="kt-card overflow-hidden">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Officers Approaching Preretirement Leave (Next 3 Months)</h3>
            <div class="kt-card-toolbar">
                <a href="{{ route('cgc.preretirement-leave.index') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                    View All Preretirement Leave
                </a>
            </div>
        </div>
        <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
            <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                <table class="kt-table" style="min-width: 1000px; width: 100%;">
                    <thead>
                        <tr class="border-b border-border">
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">SVC No</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Name</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Rank</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Preretirement Date</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Retirement Date</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Days Until</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            @php
                                $daysUntil = now()->diffInDays($item->date_of_pre_retirement_leave, false);
                            @endphp
                            <tr class="border-b border-border hover:bg-muted/30 transition-colors">
                                <td class="py-3 px-4 text-sm">{{ $item->officer->service_number ?? 'N/A' }}</td>
                                <td class="py-3 px-4 text-sm font-medium">{{ $item->officer->full_name ?? 'N/A' }}</td>
                                <td class="py-3 px-4 text-sm">{{ $item->rank ?? 'N/A' }}</td>
                                <td class="py-3 px-4 text-sm">{{ $item->date_of_pre_retirement_leave ? $item->date_of_pre_retirement_leave->format('d/m/Y') : 'N/A' }}</td>
                                <td class="py-3 px-4 text-sm">{{ $item->retirement_date ? $item->retirement_date->format('d/m/Y') : 'N/A' }}</td>
                                <td class="py-3 px-4 text-sm">
                                    @if($daysUntil > 0)
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium bg-warning/10 text-warning">
                                            {{ $daysUntil }} days
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium bg-info/10 text-info">
                                            Due Now
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-sm">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('cgc.preretirement-leave.show', $item->id) }}" 
                                           class="kt-btn kt-btn-sm kt-btn-secondary">
                                            <i class="ki-filled ki-eye"></i> View
                                        </a>
                                        <button type="button" 
                                                onclick="openApproveModal({{ $item->id }}, '{{ $item->officer->full_name ?? 'N/A' }}')"
                                                class="kt-btn kt-btn-sm kt-btn-success">
                                            <i class="ki-filled ki-check"></i> Approve In Office
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-secondary-foreground">
                                    <i class="ki-filled ki-information text-4xl mb-2"></i>
                                    <p>No officers approaching preretirement leave in the next 3 months.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($items->hasPages())
                <div class="p-4 border-t border-border">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Approve In Office Modal -->
<div class="kt-modal" data-kt-modal="true" id="approveModal">
    <div class="kt-modal-content max-w-md">
        <div class="kt-modal-header py-4 px-5">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-10 rounded-full bg-success/10">
                    <i class="ki-filled ki-check-circle text-success text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-foreground">Approve Officer for Preretirement Leave In Office</h3>
            </div>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true" onclick="closeApproveModal()">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form id="approveForm" method="POST">
            @csrf
            <div class="kt-modal-body py-5 px-5">
                <div class="mb-4">
                    <p class="text-sm text-secondary-foreground mb-2">
                        Officer: <span id="officerName" class="font-medium"></span>
                    </p>
                    <p class="text-xs text-secondary-foreground">
                        This will allow the officer to continue working during the preretirement period (last 3 months before retirement).
                    </p>
                </div>
                <div class="mb-4">
                    <label for="approval_reason" class="block text-sm font-medium mb-2">Approval Reason (Optional)</label>
                    <textarea id="approval_reason" name="approval_reason" rows="3" 
                              class="kt-input w-full" 
                              placeholder="Enter reason for approval..."></textarea>
                </div>
            </div>
            <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeApproveModal()" class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                    Cancel
                </button>
                <button type="submit" class="kt-btn kt-btn-success">
                    <i class="ki-filled ki-check"></i> Approve
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openApproveModal(itemId, officerName) {
    document.getElementById('officerName').textContent = officerName;
    document.getElementById('approveForm').action = `/cgc/preretirement-leave/${itemId}/approve-in-office`;
    
    // Use KT UI modal toggle approach - create temporary button and trigger click
    const toggleBtn = document.createElement('button');
    toggleBtn.setAttribute('data-kt-modal-toggle', '#approveModal');
    toggleBtn.style.display = 'none';
    document.body.appendChild(toggleBtn);
    
    // Trigger click event
    toggleBtn.click();
    
    // Clean up after a short delay to allow modal to open
    setTimeout(() => {
        if (document.body.contains(toggleBtn)) {
            document.body.removeChild(toggleBtn);
        }
    }, 100);
}

function closeApproveModal() {
    const modalElement = document.getElementById('approveModal');
    if (modalElement) {
        const dismissBtn = modalElement.querySelector('[data-kt-modal-dismiss]');
        if (dismissBtn) {
            dismissBtn.click();
        }
    }
    document.getElementById('approveForm').reset();
}
</script>
@endsection

