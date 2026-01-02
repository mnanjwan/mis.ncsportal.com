@extends('layouts.app')

@section('title', 'Internal Staff Orders')
@section('page-title', 'Internal Staff Orders - Pending Approval')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('dc-admin.dashboard') }}">DC Admin</a>
    <span>/</span>
    <span class="text-primary">Internal Staff Orders</span>
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

    <div class="grid gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Pending Internal Staff Orders</h3>
            </div>
            <div class="kt-card-content">
                @if($orders->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Order Number</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Officer</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Current Assignment</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Target Assignment</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Command</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Submitted</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4 text-sm font-semibold">{{ $order->order_number }}</td>
                                        <td class="py-3 px-4 text-sm">
                                            @if($order->officer)
                                                {{ $order->officer->initials }} {{ $order->officer->surname }}
                                                <br>
                                                <span class="text-xs text-secondary-foreground">{{ $order->officer->service_number }}</span>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-sm">
                                            @if($order->current_unit)
                                                {{ $order->current_unit }}
                                                @if($order->current_role)
                                                    <br>
                                                    <span class="text-xs text-secondary-foreground">Role: {{ $order->current_role }}</span>
                                                @endif
                                            @else
                                                <span class="text-muted-foreground italic">Not assigned</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-sm">
                                            @if($order->target_unit)
                                                <span class="font-semibold">{{ $order->target_unit }}</span>
                                                @if($order->target_role)
                                                    <br>
                                                    <span class="text-xs text-secondary-foreground">Role: {{ $order->target_role }}</span>
                                                @endif
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-sm">{{ $order->command->name ?? 'N/A' }}</td>
                                        <td class="py-3 px-4 text-sm">{{ $order->created_at->format('M d, Y') }}</td>
                                        <td class="py-3 px-4">
                                            <a href="{{ route('dc-admin.internal-staff-orders.show', $order->id) }}"
                                                class="kt-btn kt-btn-sm kt-btn-primary">
                                                <i class="ki-filled ki-eye"></i> Review
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="lg:hidden space-y-4">
                        @foreach($orders as $order)
                            <div class="kt-card">
                                <div class="kt-card-content p-4">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex-1">
                                            <h4 class="text-sm font-semibold text-foreground mb-1">{{ $order->order_number }}</h4>
                                            <p class="text-xs text-secondary-foreground">
                                                Submitted: {{ $order->created_at->format('M d, Y') }}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    @if($order->officer)
                                        <div class="mb-2">
                                            <p class="text-xs text-secondary-foreground mb-1">Officer</p>
                                            <p class="text-sm text-foreground">
                                                {{ $order->officer->initials }} {{ $order->officer->surname }}
                                                <span class="text-xs text-secondary-foreground">({{ $order->officer->service_number }})</span>
                                            </p>
                                        </div>
                                    @endif

                                    @if($order->current_unit)
                                        <div class="mb-2">
                                            <p class="text-xs text-secondary-foreground mb-1">Current Assignment</p>
                                            <p class="text-sm text-foreground">
                                                {{ $order->current_unit }}
                                                @if($order->current_role)
                                                    <span class="text-xs text-secondary-foreground"> - {{ $order->current_role }}</span>
                                                @endif
                                            </p>
                                        </div>
                                    @else
                                        <div class="mb-2">
                                            <p class="text-xs text-secondary-foreground mb-1">Current Assignment</p>
                                            <p class="text-sm text-muted-foreground italic">Not assigned</p>
                                        </div>
                                    @endif

                                    @if($order->target_unit)
                                        <div class="mb-2">
                                            <p class="text-xs text-secondary-foreground mb-1">Target Assignment</p>
                                            <p class="text-sm font-semibold text-foreground">
                                                {{ $order->target_unit }}
                                                @if($order->target_role)
                                                    <span class="text-xs text-secondary-foreground"> - {{ $order->target_role }}</span>
                                                @endif
                                            </p>
                                        </div>
                                    @endif

                                    @if($order->command)
                                        <div class="mb-3">
                                            <p class="text-xs text-secondary-foreground mb-1">Command</p>
                                            <p class="text-sm text-foreground">{{ $order->command->name }}</p>
                                        </div>
                                    @endif

                                    <div class="flex items-center gap-2 pt-3 border-t border-border">
                                        <a href="{{ route('dc-admin.internal-staff-orders.show', $order->id) }}"
                                            class="kt-btn kt-btn-sm kt-btn-primary flex-1">
                                            <i class="ki-filled ki-eye"></i> Review
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        {{ $orders->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="ki-filled ki-document text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No pending internal staff orders</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

