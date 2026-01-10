@extends('layouts.app')

@section('title', 'Staff Order Details')
@section('page-title', 'Staff Order Details')

@section('breadcrumbs')
    @if(isset($routePrefix) && $routePrefix === 'zone-coordinator')
        <a class="text-secondary-foreground hover:text-primary" href="{{ route('zone-coordinator.dashboard') }}">Zone Coordinator</a>
    @else
        <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    @endif
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route(($routePrefix ?? 'hrd') . '.staff-orders') }}">Staff Orders</a>
    <span>/</span>
    <span class="text-primary">View Order</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">

        <!-- Order Header -->
        <div class="kt-card">
            <div class="kt-card-content p-5 lg:p-7.5">
                <div class="flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-semibold text-mono">Staff Order #{{ $order->order_number ?? 'N/A' }}</h2>
                        <span class="kt-badge kt-badge-{{ $order->status === 'PUBLISHED' ? 'success' : ($order->status === 'CANCELLED' ? 'danger' : 'secondary') }} kt-badge-sm">
                            {{ $order->status ?? 'DRAFT' }}
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 text-sm">
                        <span class="text-secondary-foreground">
                            Created: <span class="font-semibold text-mono">{{ $order->created_at->format('d/m/Y') }}</span>
                        </span>
                        @if($order->effective_date)
                            <span class="text-secondary-foreground">
                                Effective Date: <span class="font-semibold text-mono">{{ $order->effective_date->format('d/m/Y') }}</span>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-5 lg:gap-7.5">
            <!-- Officer Information -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Officer Information</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Officer</span>
                            <span class="text-sm font-semibold text-mono">
                                {{ $order->officer ? ($order->officer->initials . ' ' . $order->officer->surname) : 'N/A' }}
                            </span>
                        </div>
                        @if($order->officer)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">Service Number</span>
                                <span class="text-sm font-semibold text-mono">{{ $order->officer->service_number ?? 'N/A' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">Rank</span>
                                <span class="text-sm font-semibold text-mono">{{ $order->officer->substantive_rank ?? 'N/A' }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Command Information -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Command Information</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">From Command</span>
                            <span class="text-sm font-semibold text-mono">
                                {{ $order->fromCommand->name ?? 'N/A' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">To Command</span>
                            <span class="text-sm font-semibold text-mono">
                                {{ $order->toCommand->name ?? 'N/A' }}
                            </span>
                        </div>
                        @if($order->order_type)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">Order Type</span>
                                <span class="text-sm font-semibold text-mono">{{ $order->order_type }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Description -->
            @if($order->description)
                <div class="kt-card md:col-span-2">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Description</h3>
                    </div>
                    <div class="kt-card-content">
                        <p class="text-sm text-secondary-foreground">{{ $order->description }}</p>
                    </div>
                </div>
            @endif

            <!-- Additional Information -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Additional Information</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4">
                        @if($order->createdBy)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">Created By</span>
                                <span class="text-sm font-semibold text-mono">{{ $order->createdBy->email ?? 'N/A' }}</span>
                            </div>
                        @endif
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Is Altered</span>
                            <span class="kt-badge kt-badge-{{ $order->is_altered ? 'warning' : 'success' }} kt-badge-sm">
                                {{ $order->is_altered ? 'Yes' : 'No' }}
                            </span>
                        </div>
                        @if($order->altered_at)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">Altered At</span>
                                <span class="text-sm font-semibold text-mono">{{ $order->altered_at->format('d/m/Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="kt-card">
            <div class="kt-card-content">
                <div class="flex gap-3">
                    <a href="{{ route(($routePrefix ?? 'hrd') . '.staff-orders') }}" class="kt-btn kt-btn-secondary">
                        Back to List
                    </a>
                    <a href="{{ route('print.staff-order', $order->id) }}" 
                       target="_blank"
                       class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-printer"></i> Print Order
                    </a>
                    <a href="{{ route(($routePrefix ?? 'hrd') . '.staff-orders.edit', $order->id) }}" class="kt-btn kt-btn-ghost">
                        <i class="ki-filled ki-pencil"></i> Edit Order
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

