@extends('layouts.app')

@section('title', 'Manning Level')
@section('page-title', 'Manning Level')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.dashboard') }}">Staff Officer</a>
    <span>/</span>
    <span class="text-primary">Manning Level</span>
@endsection

@section('content')
@if(session('success'))
    <div class="kt-card bg-success/10 border border-success/20 mb-5">
        <div class="kt-card-content p-4">
            <div class="flex items-center gap-3">
                <i class="ki-filled ki-check-circle text-success text-xl"></i>
                <p class="text-sm text-success">{{ session('success') }}</p>
            </div>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
        <div class="kt-card-content p-4">
            <div class="flex items-center gap-3">
                <i class="ki-filled ki-information text-danger text-xl"></i>
                <p class="text-sm text-danger">{{ session('error') }}</p>
            </div>
        </div>
    </div>
@endif

@if(!$command)
    <div class="kt-card">
        <div class="kt-card-content">
            <div class="text-center py-12">
                <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                <p class="text-secondary-foreground">You are not assigned to a command. Please contact HRD for command assignment.</p>
            </div>
        </div>
    </div>
@else
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Actions -->
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-foreground">Manning Level Requests</h2>
            <a href="{{ route('staff-officer.manning-level.create') }}" class="kt-btn kt-btn-primary">
                <i class="ki-filled ki-plus"></i> Create Request
            </a>
        </div>
        
        <!-- Approved Officers Summary by Rank -->
        @if(isset($approvedOfficersByRank) && $approvedOfficersByRank->count() > 0)
            <div class="kt-card overflow-hidden">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Approved Officers by Rank</h3>
                    <div class="kt-card-toolbar">
                        <span class="kt-badge kt-badge-success kt-badge-sm">HRD Matched</span>
                    </div>
                </div>
                <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
                    <p class="text-sm text-secondary-foreground mb-4 px-4 md:px-0">
                        Summary of officers that HRD has matched for your approved manning requests. 
                        <span class="text-xs text-muted-foreground">Compare requested vs approved to see which ranks were partially or fully rejected by HRD.</span>
                    </p>
                    <!-- Table with horizontal scroll wrapper -->
                    <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                        <table class="kt-table" style="min-width: 600px; width: 100%;">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Rank</th>
                                    <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Requested</th>
                                    <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Approved</th>
                                    <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($approvedOfficersByRank as $rankSummary)
                                    @php
                                        $requested = (int)$rankSummary->requested_count;
                                        $approved = (int)$rankSummary->approved_count;
                                        $percentage = $requested > 0 ? round(($approved / $requested) * 100, 1) : 0;
                                        
                                        if ($approved == 0) {
                                            $statusClass = 'danger';
                                            $statusText = 'Rejected';
                                        } elseif ($approved < $requested) {
                                            $statusClass = 'warning';
                                            $statusText = 'Partial';
                                        } else {
                                            $statusClass = 'success';
                                            $statusText = 'Complete';
                                        }
                                    @endphp
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4" style="white-space: nowrap;">
                                            <span class="text-sm font-medium text-foreground">{{ $rankSummary->rank }}</span>
                                        </td>
                                        <td class="py-3 px-4 text-right" style="white-space: nowrap;">
                                            <span class="text-sm text-secondary-foreground">{{ $requested }}</span>
                                        </td>
                                        <td class="py-3 px-4 text-right" style="white-space: nowrap;">
                                            <span class="text-sm font-semibold {{ $approved > 0 ? 'text-success' : 'text-danger' }}">{{ $approved }}</span>
                                        </td>
                                        <td class="py-3 px-4 text-right" style="white-space: nowrap;">
                                            <span class="kt-badge kt-badge-{{ $statusClass }} kt-badge-xs">
                                                {{ $statusText }}
                                                @if($requested > 0)
                                                    ({{ $percentage }}%)
                                                @endif
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-primary">
                                    <td class="py-3 px-4" style="white-space: nowrap;">
                                        <span class="text-sm font-semibold text-foreground">Total</span>
                                    </td>
                                    <td class="py-3 px-4 text-right" style="white-space: nowrap;">
                                        <span class="text-sm font-bold text-secondary-foreground">{{ $approvedOfficersByRank->sum('requested_count') }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-right" style="white-space: nowrap;">
                                        <span class="text-sm font-bold text-primary">{{ $approvedOfficersByRank->sum('approved_count') }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-right" style="white-space: nowrap;">
                                        @php
                                            $totalRequested = $approvedOfficersByRank->sum('requested_count');
                                            $totalApproved = $approvedOfficersByRank->sum('approved_count');
                                            $totalPercentage = $totalRequested > 0 ? round(($totalApproved / $totalRequested) * 100, 1) : 0;
                                        @endphp
                                        <span class="text-xs text-secondary-foreground">{{ $totalPercentage }}%</span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @endif
        
        <!-- Manning Level Requests -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Requests for {{ $command->name }}</h3>
            </div>
            <div class="kt-card-content">
                @forelse($requests as $request)
                    <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted mb-4 last:mb-0">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                                <i class="ki-filled ki-people text-primary text-xl"></i>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-semibold text-foreground">
                                    Request #{{ str_pad($request->id, 6, '0', STR_PAD_LEFT) }}
                                </span>
                                <span class="text-xs text-secondary-foreground">
                                    {{ $request->items->count() }} requirement(s) | 
                                    Created: {{ $request->created_at->format('M d, Y') }}
                                </span>
                                @if($request->items->count() > 0)
                                    <div class="flex flex-wrap gap-2 mt-1">
                                        @foreach($request->items->take(3) as $item)
                                            <span class="text-xs px-2 py-1 rounded bg-muted text-secondary-foreground">
                                                {{ $item->rank ?? 'N/A' }} ({{ $item->quantity_needed }})
                                            </span>
                                        @endforeach
                                        @if($request->items->count() > 3)
                                            <span class="text-xs text-secondary-foreground">+{{ $request->items->count() - 3 }} more</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            @php
                                $statusColors = [
                                    'DRAFT' => 'secondary',
                                    'SUBMITTED' => 'warning',
                                    'APPROVED' => 'success',
                                    'REJECTED' => 'danger',
                                    'FULFILLED' => 'info',
                                ];
                                $statusColor = $statusColors[$request->status] ?? 'secondary';
                            @endphp
                            <span class="kt-badge kt-badge-{{ $statusColor }} kt-badge-sm">{{ $request->status }}</span>
                            <a href="{{ route('staff-officer.manning-level.show', $request->id) }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                                <i class="ki-filled ki-eye"></i> View
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <i class="ki-filled ki-people text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground mb-4">No manning level requests found</p>
                        <a href="{{ route('staff-officer.manning-level.create') }}" class="kt-btn kt-btn-primary">
                            Create First Request
                        </a>
                    </div>
                @endforelse
            </div>
            @if($requests->hasPages())
                <div class="kt-card-content border-t border-input pt-4">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>
@endif

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
