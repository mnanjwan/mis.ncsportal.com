@extends('layouts.app')

@section('title', 'Internal Staff Order Details')
@section('page-title', 'Internal Staff Order Details')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('fleet.cd.dashboard') }}">Fleet CD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('fleet.cd.internal-staff-orders.index') }}">Internal Staff Orders (Transport)</a>
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

                    <!-- Status -->
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-semibold text-secondary-foreground">Status</label>
                        <div>
                            @if($order->status === 'DRAFT')
                                <span class="kt-badge kt-badge-info">DRAFT</span>
                            @elseif($order->status === 'PENDING_APPROVAL')
                                <span class="kt-badge kt-badge-warning">PENDING APPROVAL</span>
                            @elseif($order->status === 'APPROVED')
                                <span class="kt-badge kt-badge-success">APPROVED</span>
                            @elseif($order->status === 'REJECTED')
                                <span class="kt-badge kt-badge-danger">REJECTED</span>
                            @endif
                        </div>
                    </div>

                    <!-- Officer -->
                    @if($order->officer)
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-semibold text-secondary-foreground">Officer</label>
                            <p class="text-base text-foreground">
                                {{ $order->officer->initials }} {{ $order->officer->surname }} ({{ $order->officer->service_number }})
                            </p>
                        </div>
                    @endif

                    <!-- Current Unit -->
                    @if($order->current_unit)
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-semibold text-secondary-foreground">Current Unit</label>
                            <p class="text-base text-foreground">{{ $order->current_unit }}</p>
                        </div>
                    @endif

                    <!-- Current Role -->
                    @if($order->current_role)
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-semibold text-secondary-foreground">Current Role</label>
                            <p class="text-base text-foreground">{{ $order->current_role }}</p>
                        </div>
                    @endif

                    <!-- Target Unit -->
                    @if($order->target_unit)
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-semibold text-secondary-foreground">Target Unit</label>
                            <p class="text-base text-foreground">{{ $order->target_unit }}</p>
                        </div>
                    @endif

                    <!-- Target Role -->
                    @if($order->target_role)
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-semibold text-secondary-foreground">Target Role</label>
                            <p class="text-base text-foreground">{{ $order->target_role }}</p>
                        </div>
                    @endif

                    <!-- Approved By -->
                    @if($order->approvedBy)
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-semibold text-secondary-foreground">Approved By</label>
                            <p class="text-base text-foreground">
                                {{ $order->approvedBy->name ?? $order->approvedBy->email }}
                            </p>
                        </div>
                    @endif

                    <!-- Approved At -->
                    @if($order->approved_at)
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-semibold text-secondary-foreground">Approved At</label>
                            <p class="text-base text-foreground">
                                {{ $order->approved_at->format('d M Y, h:i A') }}
                            </p>
                        </div>
                    @endif

                    <!-- Rejection Reason -->
                    @if($order->rejection_reason)
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-semibold text-secondary-foreground">Rejection Reason</label>
                            <p class="text-base text-foreground text-danger">{{ $order->rejection_reason }}</p>
                        </div>
                    @endif
                </div>

                <!-- Conflict Warning -->
                @if($conflict)
                    <div class="kt-card bg-warning/10 border border-warning/20 mt-5">
                        <div class="kt-card-content p-4">
                            <div class="flex items-start gap-3">
                                <i class="ki-filled ki-information text-warning text-xl mt-0.5"></i>
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-warning mb-2">Role Takeover Warning</h4>
                                    <p class="text-sm text-foreground mb-2">
                                        This action will replace the current {{ $conflict['type'] }} in the selected unit.
                                    </p>
                                    <div class="text-xs text-secondary-foreground">
                                        <strong>Current {{ $conflict['type'] }}:</strong><br>
                                        {{ $conflict['officer']->initials }} {{ $conflict['officer']->surname }} ({{ $conflict['officer']->service_number }})<br>
                                        Rank: {{ $conflict['officer']->substantive_rank ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Description -->
                @if($order->description)
                    <div class="flex flex-col gap-2 mt-5 pt-5 border-t border-border">
                        <label class="text-sm font-semibold text-secondary-foreground">Description</label>
                        <div class="text-base text-foreground whitespace-pre-wrap">{{ $order->description }}</div>
                    </div>
                @endif

                <!-- Actions -->
                <div class="flex gap-3 pt-5 border-t border-border">
                    <a href="{{ route('fleet.cd.internal-staff-orders.index') }}" class="kt-btn kt-btn-secondary">
                        Back to List
                    </a>
                    <a href="{{ route('print.internal-staff-order', $order->id) }}" 
                       target="_blank"
                       class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-printer"></i> Print Order
                    </a>
                    @if($order->status === 'DRAFT')
                        <button type="button" class="kt-btn kt-btn-success" data-kt-modal-toggle="#submit-order-modal">
                            <i class="ki-filled ki-check"></i> Submit for Approval
                        </button>
                        <a href="{{ route('fleet.cd.internal-staff-orders.edit', $order->id) }}" class="kt-btn kt-btn-ghost">
                            <i class="ki-filled ki-pencil"></i> Edit Order
                        </a>
                        <button type="button" class="kt-btn kt-btn-danger" data-kt-modal-toggle="#delete-order-modal">
                            <i class="ki-filled ki-trash"></i> Delete Order
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Submit for Approval Confirmation Modal -->
    <div class="kt-modal" data-kt-modal="true" id="submit-order-modal">
        <div class="kt-modal-content max-w-[400px]">
            <div class="kt-modal-header py-4 px-5">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center size-10 rounded-full bg-success/10">
                        <i class="ki-filled ki-check-circle text-success text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-foreground">Submit for Approval</h3>
                </div>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <div class="kt-modal-body py-5 px-5">
                <p class="text-sm text-secondary-foreground">
                    Are you sure you want to submit this order for DC Admin approval? Once submitted, you will not be able to edit it until it is reviewed.
                </p>
            </div>
            <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                    Cancel
                </button>
                <form action="{{ route('fleet.cd.internal-staff-orders.submit', $order->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="kt-btn kt-btn-success">
                        <i class="ki-filled ki-check"></i> Submit for Approval
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Order Confirmation Modal -->
    <div class="kt-modal" data-kt-modal="true" id="delete-order-modal">
        <div class="kt-modal-content max-w-[400px]">
            <div class="kt-modal-header py-4 px-5">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center size-10 rounded-full bg-danger/10">
                        <i class="ki-filled ki-information text-danger text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-foreground">Delete Order</h3>
                </div>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <div class="kt-modal-body py-5 px-5">
                <p class="text-sm text-secondary-foreground">
                    Are you sure you want to delete this internal staff order? This action cannot be undone.
                </p>
            </div>
            <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                    Cancel
                </button>
                <form action="{{ route('fleet.cd.internal-staff-orders.destroy', $order->id) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="kt-btn kt-btn-danger">
                        <i class="ki-filled ki-trash"></i> Delete Order
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

