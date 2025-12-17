@extends('layouts.app')

@section('title', 'Movement Order Details')
@section('page-title', 'Movement Order Details')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.movement-orders') }}">Movement Orders</a>
    <span>/</span>
    <span class="text-primary">View Order</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Back Button -->
        <div class="flex items-center justify-between">
            <a href="{{ route('hrd.movement-orders') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                <i class="ki-filled ki-arrow-left"></i> Back to Movement Orders
            </a>
        </div>

        <!-- Order Header -->
        <div class="kt-card">
            <div class="kt-card-content p-5 lg:p-7.5">
                <div class="flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-semibold text-mono">Movement Order #{{ $order->order_number ?? 'N/A' }}</h2>
                        <span class="kt-badge kt-badge-{{ $order->status === 'PUBLISHED' ? 'success' : ($order->status === 'CANCELLED' ? 'danger' : 'secondary') }} kt-badge-sm">
                            {{ $order->status ?? 'DRAFT' }}
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 text-sm">
                        <span class="text-secondary-foreground">
                            Created: <span class="font-semibold text-mono">{{ $order->created_at->format('d/m/Y') }}</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-5 lg:gap-7.5">
            <!-- Criteria Information -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Movement Criteria</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Criteria (Months at Station)</span>
                            <span class="text-sm font-semibold text-mono">
                                {{ $order->criteria_months_at_station ?? 'N/A' }} months
                            </span>
                        </div>
                        <div class="p-3 rounded-lg bg-muted/50">
                            <p class="text-xs text-secondary-foreground">
                                Officers who have been at their current station for {{ $order->criteria_months_at_station ?? 'N/A' }} months or more will be eligible for movement under this order.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manning Request Information -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Manning Request</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4">
                        @if($order->manningRequest)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">Request ID</span>
                                <span class="text-sm font-semibold text-mono">
                                    #{{ $order->manningRequest->id ?? 'N/A' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">Request Date</span>
                                <span class="text-sm font-semibold text-mono">
                                    {{ $order->manningRequest->created_at ? $order->manningRequest->created_at->format('d/m/Y') : 'N/A' }}
                                </span>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <p class="text-sm text-secondary-foreground">No manning request linked</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Postings Information -->
            <div class="kt-card md:col-span-2">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Officer Postings</h3>
                </div>
                <div class="kt-card-content">
                    @if($order->postings && $order->postings->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="kt-table w-full">
                                <thead>
                                    <tr class="border-b border-border">
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Officer</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">From Command</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">To Command</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Effective Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->postings as $posting)
                                        <tr class="border-b border-border last:border-0">
                                            <td class="py-3 px-4 text-sm">
                                                {{ $posting->officer ? ($posting->officer->initials . ' ' . $posting->officer->surname) : 'N/A' }}
                                            </td>
                                            <td class="py-3 px-4 text-sm text-secondary-foreground">
                                                {{ $posting->officer && $posting->officer->presentStation ? $posting->officer->presentStation->name : 'N/A' }}
                                            </td>
                                            <td class="py-3 px-4 text-sm text-secondary-foreground">
                                                {{ $posting->command->name ?? 'N/A' }}
                                            </td>
                                            <td class="py-3 px-4 text-sm text-secondary-foreground">
                                                {{ $posting->posting_date ? $posting->posting_date->format('d/m/Y') : 'N/A' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="ki-filled ki-abstract-26 text-4xl text-muted-foreground mb-4"></i>
                            <p class="text-sm text-secondary-foreground">No officer postings have been created for this movement order yet.</p>
                        </div>
                    @endif
                </div>
            </div>

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
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

