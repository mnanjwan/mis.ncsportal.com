@extends('layouts.app')

@section('title', 'Zone Coordinator Dashboard')
@section('page-title')
Zone Coordinator Dashboard
@if($coordinatorZone)
    <span class="text-sm text-secondary-foreground font-normal">({{ $coordinatorZone->name }})</span>
@endif
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    @if(!$coordinatorZone)
        <div class="kt-card">
            <div class="kt-card-content">
                <div class="text-center py-12">
                    <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-secondary-foreground">You are not assigned to a zone. Please contact HRD for zone assignment.</p>
                </div>
            </div>
        </div>
    @else
        <!-- Zone Information Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Zone Information</h3>
            </div>
            <div class="kt-card-content">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="text-xs text-secondary-foreground uppercase">Zone Name</label>
                        <p class="text-sm font-medium text-foreground mt-1">{{ $coordinatorZone->name }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-secondary-foreground uppercase">Zone Code</label>
                        <p class="text-sm font-mono text-foreground mt-1">{{ $coordinatorZone->code }}</p>
                    </div>
                    @if($coordinatorCommand)
                        <div>
                            <label class="text-xs text-secondary-foreground uppercase">Assigned Command</label>
                            <p class="text-sm text-foreground mt-1">{{ $coordinatorCommand->name }}</p>
                        </div>
                    @endif
                    <div>
                        <label class="text-xs text-secondary-foreground uppercase">Total Commands</label>
                        <p class="text-sm font-semibold text-foreground mt-1">{{ $zoneStats['total_commands'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 lg:gap-7.5">
            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Total Officers</span>
                            <span class="text-2xl font-semibold text-mono">{{ $zoneStats['total_officers'] }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                            <i class="ki-filled ki-people text-2xl text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Eligible Officers</span>
                            <span class="text-2xl font-semibold text-mono">{{ $zoneStats['eligible_officers'] }}</span>
                            <span class="text-xs text-secondary-foreground">GL 07 & Below</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                            <i class="ki-filled ki-user-check text-2xl text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Total Commands</span>
                            <span class="text-2xl font-semibold text-mono">{{ $zoneStats['total_commands'] }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                            <i class="ki-filled ki-map text-2xl text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Recent Orders</span>
                            <span class="text-2xl font-semibold text-mono">{{ $zoneStats['recent_orders'] }}</span>
                            <span class="text-xs text-secondary-foreground">Last 30 Days</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-warning/10">
                            <i class="ki-filled ki-file-up text-2xl text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Quick Actions</h3>
            </div>
            <div class="kt-card-content">
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <a href="{{ route('zone-coordinator.staff-orders.create') }}" class="kt-btn kt-btn-primary w-full">
                        <i class="ki-filled ki-file-up"></i>
                        Create Staff Order
                    </a>
                    <a href="{{ route('zone-coordinator.staff-orders') }}" class="kt-btn kt-btn-ghost w-full">
                        <i class="ki-filled ki-file-down"></i>
                        View Staff Orders
                    </a>
                    <a href="{{ route('zone-coordinator.officers') }}" class="kt-btn kt-btn-ghost w-full">
                        <i class="ki-filled ki-people"></i>
                        View Zone Officers
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Staff Orders -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Recent Staff Orders</h3>
                <div class="kt-card-toolbar">
                    <a href="{{ route('zone-coordinator.staff-orders') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                        View All
                    </a>
                </div>
            </div>
            <div class="kt-card-content">
                @if($recentOrders->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Order Number</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Officer</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">From Command</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">To Command</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Status</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4">
                                            <a href="{{ route('zone-coordinator.staff-orders.show', $order->id) }}" class="text-sm font-mono text-primary hover:underline">
                                                {{ $order->order_number }}
                                            </a>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-foreground">
                                            {{ $order->officer->initials }} {{ $order->officer->surname }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $order->fromCommand->name }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $order->toCommand->name }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="kt-badge kt-badge-{{ strtolower($order->status) === 'published' ? 'success' : (strtolower($order->status) === 'draft' ? 'warning' : 'danger') }} kt-badge-sm">
                                                {{ $order->status }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $order->created_at->format('M d, Y') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No staff orders found for your zone</p>
                        <a href="{{ route('zone-coordinator.staff-orders.create') }}" class="kt-btn kt-btn-sm kt-btn-primary mt-4">
                            Create First Order
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Zone Officers Preview -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Zone Officers (Sample)</h3>
                <div class="kt-card-toolbar">
                    <a href="{{ route('zone-coordinator.officers') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                        View All
                    </a>
                </div>
            </div>
            <div class="kt-card-content">
                @if($zoneOfficers->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Officer Name</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Service Number</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Command</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Grade Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($zoneOfficers as $officer)
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4 text-sm font-medium text-foreground">
                                            {{ $officer->initials }} {{ $officer->surname }}
                                        </td>
                                        <td class="py-3 px-4 text-sm font-mono text-secondary-foreground">
                                            {{ $officer->service_number }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $officer->presentStation->name ?? 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="kt-badge kt-badge-{{ (int)filter_var($officer->salary_grade_level, FILTER_SANITIZE_NUMBER_INT) <= 7 ? 'success' : 'warning' }} kt-badge-sm">
                                                {{ $officer->salary_grade_level }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No officers found in your zone</p>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection

