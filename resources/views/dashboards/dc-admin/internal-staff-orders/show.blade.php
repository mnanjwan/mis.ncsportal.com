@extends('layouts.app')

@section('title', 'Internal Staff Order Details')
@section('page-title', 'Internal Staff Order Details')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('dc-admin.dashboard') }}">DC Admin</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('dc-admin.internal-staff-orders') }}">Internal Staff Orders</a>
    <span>/</span>
    <span class="text-primary">Review</span>
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

                    <!-- Current Assignment -->
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-semibold text-secondary-foreground">Current Assignment</label>
                        <p class="text-base text-foreground">
                            @if($order->current_unit)
                                <span class="font-semibold">{{ $order->current_unit }}</span>
                                @if($order->current_role)
                                    <br>
                                    <span class="text-sm text-secondary-foreground">Role: {{ $order->current_role }}</span>
                                @endif
                            @else
                                <span class="text-muted-foreground italic">Not currently assigned</span>
                            @endif
                        </p>
                    </div>

                    <!-- Target Assignment -->
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-semibold text-secondary-foreground">Target Assignment</label>
                        <p class="text-base text-foreground">
                            @if($order->target_unit)
                                <span class="font-semibold text-success">{{ $order->target_unit }}</span>
                                @if($order->target_role)
                                    <br>
                                    <span class="text-sm text-secondary-foreground">Role: {{ $order->target_role }}</span>
                                @endif
                            @else
                                <span class="text-muted-foreground italic">Not specified</span>
                            @endif
                        </p>
                    </div>

                    <!-- Description -->
                    @if($order->description)
                        <div class="flex flex-col gap-1 md:col-span-2">
                            <label class="text-sm font-semibold text-secondary-foreground">Description</label>
                            <p class="text-base text-foreground whitespace-pre-wrap">{{ $order->description }}</p>
                        </div>
                    @endif

                    <!-- Conflict Warning -->
                    @if($outgoingOfficer)
                        <div class="md:col-span-2">
                            <div class="kt-card bg-warning/10 border border-warning/20">
                                <div class="kt-card-content p-4">
                                    <div class="flex items-start gap-3">
                                        <i class="ki-filled ki-information text-warning text-xl mt-0.5"></i>
                                        <div class="flex-1">
                                            <p class="text-sm font-semibold text-warning mb-1">Role Conflict Detected</p>
                                            <p class="text-sm text-secondary-foreground">
                                                Approving this order will replace <strong>{{ $outgoingOfficer->initials }} {{ $outgoingOfficer->surname }}</strong> 
                                                ({{ $outgoingOfficer->service_number }}) as {{ $order->target_role }} of {{ $order->target_unit }}.
                                                The outgoing officer will be reassigned as a regular member of the unit.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 pt-5 border-t border-border">
                <a href="{{ route('dc-admin.internal-staff-orders') }}" class="kt-btn kt-btn-secondary">
                    Back to List
                </a>
                <a href="{{ route('print.internal-staff-order', $order->id) }}" 
                   target="_blank"
                   class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-printer"></i> Print Order
                </a>
                @if($order->status === 'PENDING_APPROVAL')
                    <button type="button" class="kt-btn kt-btn-success" data-kt-modal-toggle="#approve-order-modal">
                        <i class="ki-filled ki-check"></i> Approve Order
                    </button>
                    <button type="button" class="kt-btn kt-btn-danger" onclick="showRejectModal()">
                        <i class="ki-filled ki-cross"></i> Reject Order
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Approve Confirmation Modal -->
<div class="kt-modal" data-kt-modal="true" id="approve-order-modal">
    <div class="kt-modal-content max-w-[400px]">
        <div class="kt-modal-header py-4 px-5">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-10 rounded-full bg-success/10">
                    <i class="ki-filled ki-check-circle text-success text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-foreground">Approve Internal Staff Order</h3>
            </div>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body py-5 px-5">
            <p class="text-sm text-secondary-foreground">
                Are you sure you want to approve this internal staff order? Once approved, the officer will be reassigned and the roster will be updated automatically.
            </p>
            @if($outgoingOfficer)
                <div class="mt-3 p-3 bg-warning/10 border border-warning/20 rounded">
                    <p class="text-xs text-warning">
                        <strong>Note:</strong> This will replace {{ $outgoingOfficer->initials }} {{ $outgoingOfficer->surname }} 
                        as {{ $order->target_role }} of {{ $order->target_unit }}.
                    </p>
                </div>
            @endif
        </div>
        <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
            <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                Cancel
            </button>
            <form action="{{ route('dc-admin.internal-staff-orders.approve', $order->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="kt-btn kt-btn-success">
                    <i class="ki-filled ki-check"></i> Approve
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="reject-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="kt-card max-w-md w-full mx-4">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Reject Internal Staff Order</h3>
        </div>
        <form action="{{ route('dc-admin.internal-staff-orders.reject', $order->id) }}" method="POST" class="kt-card-content">
            @csrf
            <div class="flex flex-col gap-4">
                <div>
                    <label class="kt-form-label">Rejection Reason <span class="text-danger">*</span></label>
                    <textarea name="rejection_reason" class="kt-input" rows="4" placeholder="Enter reason for rejection" required></textarea>
                </div>
                <div class="flex gap-3 justify-end">
                    <button type="button" class="kt-btn kt-btn-outline" onclick="closeRejectModal()">Cancel</button>
                    <button type="submit" class="kt-btn kt-btn-danger">Reject</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function showRejectModal() {
    document.getElementById('reject-modal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('reject-modal').classList.add('hidden');
    document.querySelector('#reject-modal form').reset();
}

document.getElementById('reject-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});
</script>
@endpush
@endsection

