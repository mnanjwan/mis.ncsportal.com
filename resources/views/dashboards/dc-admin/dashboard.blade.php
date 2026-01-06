@extends('layouts.app')

@section('title', 'DC Admin Dashboard')
@section('page-title', 'DC Admin Dashboard')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Pending Leave</span>
                        <span class="text-2xl font-semibold text-mono" id="pending-leave">Loading...</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-warning/10">
                        <i class="ki-filled ki-calendar text-2xl text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Pending Pass</span>
                        <span class="text-2xl font-semibold text-mono" id="pending-pass">Loading...</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                        <i class="ki-filled ki-calendar-tick text-2xl text-info"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Pending Duty Rosters</span>
                        <span class="text-2xl font-semibold text-mono">{{ $pendingRosters ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                        <i class="ki-filled ki-calendar-tick text-2xl text-info"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Pending Internal Staff Orders</span>
                        <span class="text-2xl font-semibold text-mono">{{ $pendingInternalStaffOrders ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                        <i class="ki-filled ki-document text-2xl text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Pending Manning Requests</span>
                        <span class="text-2xl font-semibold text-mono">{{ $pendingManningRequests ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                        <i class="ki-filled ki-people text-2xl text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 lg:gap-7.5">
        <!-- Recent Manning Requests -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Recent Manning Requests</h3>
            </div>
            <div class="kt-card-content">
                @if(isset($recentManningRequests) && $recentManningRequests->count() > 0)
                    <div class="flex flex-col gap-3">
                        @foreach($recentManningRequests as $request)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50 border border-input">
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-semibold text-foreground">
                                        {{ $request->command->name ?? 'N/A' }} - {{ $request->command->zone->name ?? 'N/A' }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        Requested by: {{ $request->requestedBy->email ?? 'N/A' }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        Submitted: {{ $request->submitted_at ? $request->submitted_at->format('M d, Y') : ($request->created_at ? $request->created_at->format('M d, Y') : 'N/A') }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        Items: {{ $request->items->count() }} item(s)
                                    </span>
                                </div>
                                <a href="{{ route('dc-admin.manning-level.show', $request->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                                    <i class="ki-filled ki-eye"></i> Review
                                </a>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('dc-admin.manning-level') }}" class="kt-btn kt-btn-outline w-full">
                            View All Manning Requests
                        </a>
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="ki-filled ki-people text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No pending manning requests</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Recent Duty Rosters -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Recent Duty Rosters</h3>
            </div>
            <div class="kt-card-content">
                @if(isset($recentRosters) && $recentRosters->count() > 0)
                    <div class="flex flex-col gap-3">
                        @foreach($recentRosters as $roster)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50 border border-input">
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-semibold text-foreground">
                                        {{ $roster->command->name ?? 'N/A' }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        Period: {{ $roster->roster_period_start ? $roster->roster_period_start->format('M d, Y') : 'N/A' }} - {{ $roster->roster_period_end ? $roster->roster_period_end->format('M d, Y') : 'N/A' }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        Prepared by: {{ $roster->preparedBy->email ?? 'N/A' }}
                                    </span>
                                </div>
                                <a href="{{ route('dc-admin.roster.show', $roster->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                                    <i class="ki-filled ki-eye"></i> Review
                                </a>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('dc-admin.roster') }}" class="kt-btn kt-btn-outline w-full">
                            View All Duty Rosters
                        </a>
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="ki-filled ki-calendar-tick text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No pending duty rosters</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Recent Internal Staff Orders -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Recent Internal Staff Orders</h3>
            </div>
            <div class="kt-card-content">
                @if(isset($recentInternalStaffOrders) && $recentInternalStaffOrders->count() > 0)
                    <div class="flex flex-col gap-3">
                        @foreach($recentInternalStaffOrders as $order)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50 border border-input">
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-semibold text-foreground">
                                        Order #{{ $order->order_number }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        Officer: {{ $order->officer->initials ?? '' }} {{ $order->officer->surname ?? 'N/A' }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        Target: {{ $order->target_unit ?? 'N/A' }} - {{ $order->target_role ?? 'N/A' }}
                                    </span>
                                </div>
                                <a href="{{ route('dc-admin.internal-staff-orders.show', $order->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                                    <i class="ki-filled ki-eye"></i> Review
                                </a>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('dc-admin.internal-staff-orders') }}" class="kt-btn kt-btn-outline w-full">
                            View All Internal Staff Orders
                        </a>
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="ki-filled ki-document text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No pending internal staff orders</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Quick Actions</h3>
        </div>
        <div class="kt-card-content">
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('dc-admin.leave-pass') }}" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-calendar"></i> Manage Leave & Pass
                </a>
                <a href="{{ route('dc-admin.roster') }}" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-calendar-tick"></i> Duty Rosters
                </a>
                <a href="{{ route('dc-admin.internal-staff-orders') }}" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-document"></i> Internal Staff Orders
                </a>
                <a href="{{ route('dc-admin.manning-level') }}" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-people"></i> Manning Requests
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    const token = window.API_CONFIG.token;
    
    try {
        const [leaveRes, passRes] = await Promise.all([
            fetch('/api/v1/leave-applications?status=PENDING&per_page=1', { headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' } }),
            fetch('/api/v1/pass-applications?status=PENDING&per_page=1', { headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' } })
        ]);
        
        if (leaveRes.ok) {
            const data = await leaveRes.json();
            document.getElementById('pending-leave').textContent = data.meta?.total || 0;
        }
        
        if (passRes.ok) {
            const data = await passRes.json();
            document.getElementById('pending-pass').textContent = data.meta?.total || 0;
        }
    } catch (error) {
        console.error('Error loading dashboard data:', error);
    }
});
</script>
@endpush
@endsection


