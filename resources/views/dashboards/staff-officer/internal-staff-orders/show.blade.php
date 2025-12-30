@extends('layouts.app')

@section('title', 'Internal Staff Order Details')
@section('page-title', 'Internal Staff Order Details')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.dashboard') }}">Staff Officer</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.internal-staff-orders.index') }}">Internal Staff Orders</a>
    <span>/</span>
    <span class="text-primary">Details</span>
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
        <!-- Back Button -->
        <div class="flex items-center justify-between">
            <a href="{{ route('staff-officer.internal-staff-orders.index') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                <i class="ki-filled ki-arrow-left"></i> Back to Internal Staff Orders
            </a>
            <div class="flex items-center gap-2">
                <a href="{{ route('print.internal-staff-order', $order->id) }}" 
                   target="_blank"
                   class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-printer"></i> Print Order
                </a>
                <a href="{{ route('staff-officer.internal-staff-orders.edit', $order->id) }}" class="kt-btn kt-btn-ghost">
                    <i class="ki-filled ki-pencil"></i> Edit
                </a>
            </div>
        </div>

        <!-- Order Details Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Internal Staff Order Details</h3>
                <div class="kt-card-toolbar">
                    <span class="kt-badge kt-badge-info kt-badge-sm">{{ $order->order_number }}</span>
                </div>
            </div>
            <div class="kt-card-content">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Order Number -->
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-semibold text-secondary-foreground">Order Number</label>
                        <p class="text-base text-foreground">{{ $order->order_number ?? 'N/A' }}</p>
                    </div>

                    <!-- Order Date -->
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-semibold text-secondary-foreground">Order Date</label>
                        <p class="text-base text-foreground">
                            {{ $order->order_date ? $order->order_date->format('d M Y') : 'N/A' }}
                        </p>
                    </div>

                    <!-- Command -->
                    @if($order->command)
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-semibold text-secondary-foreground">Command</label>
                            <p class="text-base text-foreground">{{ $order->command->name }}</p>
                        </div>
                    @endif

                    <!-- Prepared By -->
                    @if($order->preparedBy)
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-semibold text-secondary-foreground">Prepared By</label>
                            <p class="text-base text-foreground">
                                @if($order->preparedBy->officer)
                                    {{ $order->preparedBy->officer->initials ?? '' }} {{ $order->preparedBy->officer->surname ?? '' }}
                                @else
                                    {{ $order->preparedBy->email ?? 'N/A' }}
                                @endif
                            </p>
                        </div>
                    @endif

                    <!-- Created At -->
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-semibold text-secondary-foreground">Created At</label>
                        <p class="text-base text-foreground">
                            {{ $order->created_at ? $order->created_at->format('d M Y, h:i A') : 'N/A' }}
                        </p>
                    </div>

                    <!-- Updated At -->
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-semibold text-secondary-foreground">Last Updated</label>
                        <p class="text-base text-foreground">
                            {{ $order->updated_at ? $order->updated_at->format('d M Y, h:i A') : 'N/A' }}
                        </p>
                    </div>
                </div>

                <!-- Description -->
                @if($order->description)
                    <div class="flex flex-col gap-2 mt-5 pt-5 border-t border-border">
                        <label class="text-sm font-semibold text-secondary-foreground">Description</label>
                        <div class="text-base text-foreground whitespace-pre-wrap">{{ $order->description }}</div>
                    </div>
                @endif

                <!-- Actions -->
                <div class="flex items-center gap-3 mt-5 pt-5 border-t border-border">
                    <a href="{{ route('print.internal-staff-order', $order->id) }}" 
                       target="_blank"
                       class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-printer"></i> Print Order
                    </a>
                    <a href="{{ route('staff-officer.internal-staff-orders.edit', $order->id) }}" class="kt-btn kt-btn-ghost">
                        <i class="ki-filled ki-pencil"></i> Edit Order
                    </a>
                    <form action="{{ route('staff-officer.internal-staff-orders.destroy', $order->id) }}" 
                          method="POST" 
                          class="inline"
                          onsubmit="return confirm('Are you sure you want to delete this internal staff order? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="kt-btn kt-btn-danger">
                            <i class="ki-filled ki-trash"></i> Delete Order
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

