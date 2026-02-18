@extends('layouts.app')

@section('title', 'Preretirement Leave Details')
@section('page-title', 'Preretirement Leave Details')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('cgc.dashboard') }}">CGC Office</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('cgc.preretirement-leave.index') }}">Preretirement Leave</a>
    <span>/</span>
    <span class="text-primary">Details</span>
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

    <!-- Officer Information -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Officer Information</h3>
            <div class="kt-card-toolbar">
                <a href="{{ route('cgc.preretirement-leave.index') }}" class="kt-btn kt-btn-sm kt-btn-secondary">
                    <i class="ki-filled ki-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
        <div class="kt-card-content">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs text-secondary-foreground block mb-1">Service Number</label>
                    <p class="text-sm font-medium">{{ $item->officer->service_number ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-xs text-secondary-foreground block mb-1">Name</label>
                    <p class="text-sm font-medium">{{ $item->officer->full_name ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-xs text-secondary-foreground block mb-1">Rank</label>
                    <p class="text-sm font-medium">{{ $item->rank ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-xs text-secondary-foreground block mb-1">Date of Birth</label>
                    <p class="text-sm font-medium">{{ $item->date_of_birth ? $item->date_of_birth->format('d/m/Y') : 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-xs text-secondary-foreground block mb-1">Date of First Appointment</label>
                    <p class="text-sm font-medium">{{ $item->date_of_first_appointment ? $item->date_of_first_appointment->format('d/m/Y') : 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-xs text-secondary-foreground block mb-1">Retirement Condition</label>
                    <p class="text-sm font-medium">
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium 
                            {{ $item->retirement_condition === 'AGE' ? 'bg-info/10 text-info' : 'bg-primary/10 text-primary' }}">
                            {{ $item->retirement_condition }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Retirement Information -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Retirement Information</h3>
        </div>
        <div class="kt-card-content">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs text-secondary-foreground block mb-1">Preretirement Leave Date</label>
                    <p class="text-sm font-medium">{{ $item->date_of_pre_retirement_leave ? $item->date_of_pre_retirement_leave->format('d/m/Y') : 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-xs text-secondary-foreground block mb-1">Retirement Date</label>
                    <p class="text-sm font-medium">{{ $item->retirement_date ? $item->retirement_date->format('d/m/Y') : 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-xs text-secondary-foreground block mb-1">Preretirement Leave Status</label>
                    <p class="text-sm font-medium">
                        @if($item->preretirement_leave_status === 'AUTO_PLACED')
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium bg-info/10 text-info">
                                <i class="ki-filled ki-calendar-2"></i> Auto Placed
                            </span>
                        @elseif($item->preretirement_leave_status === 'CGC_APPROVED_IN_OFFICE')
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium bg-success/10 text-success">
                                <i class="ki-filled ki-check-circle"></i> CGC Office Approved (In Office)
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium bg-muted text-secondary-foreground">
                                {{ $item->preretirement_leave_status ?? 'Not Placed' }}
                            </span>
                        @endif
                    </p>
                </div>
                @if($item->auto_placed_at)
                <div>
                    <label class="text-xs text-secondary-foreground block mb-1">Auto Placed At</label>
                    <p class="text-sm font-medium">{{ $item->auto_placed_at->format('d/m/Y H:i') }}</p>
                </div>
                @endif
                @if($item->cgc_approved_at)
                <div>
                    <label class="text-xs text-secondary-foreground block mb-1">CGC Office Approved At</label>
                    <p class="text-sm font-medium">{{ $item->cgc_approved_at->format('d/m/Y H:i') }}</p>
                </div>
                @endif
                @if($item->cgcApprovedBy)
                <div>
                    <label class="text-xs text-secondary-foreground block mb-1">Approved By</label>
                    <p class="text-sm font-medium">{{ $item->cgcApprovedBy->email ?? 'N/A' }}</p>
                </div>
                @endif
                @if($item->cgc_approval_reason)
                <div class="md:col-span-2">
                    <label class="text-xs text-secondary-foreground block mb-1">Approval Reason</label>
                    <p class="text-sm font-medium">{{ $item->cgc_approval_reason }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Actions -->
    @if($item->preretirement_leave_status === 'AUTO_PLACED')
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Actions</h3>
        </div>
        <div class="kt-card-content">
            <button type="button" 
                    onclick="openApproveModal({{ $item->id }}, '{{ $item->officer->full_name ?? 'N/A' }}')"
                    class="kt-btn kt-btn-success">
                <i class="ki-filled ki-check"></i> Approve for Preretirement Leave In Office
            </button>
        </div>
    </div>
    @elseif($item->preretirement_leave_status === 'CGC_APPROVED_IN_OFFICE')
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Actions</h3>
        </div>
        <div class="kt-card-content">
            <button type="button" 
                    onclick="openCancelApprovalModal({{ $item->id }}, '{{ $item->officer->full_name ?? 'N/A' }}')"
                    class="kt-btn kt-btn-danger">
                <i class="ki-filled ki-cross"></i> Cancel CGC Office Approval
            </button>
        </div>
    </div>
    @endif
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

function openCancelApprovalModal(itemId, officerName) {
    document.getElementById('cancelOfficerName').textContent = officerName;
    document.getElementById('cancelApprovalForm').action = `/cgc/preretirement-leave/${itemId}/cancel-approval`;
    
    // Use KT UI modal toggle approach - create temporary button and trigger click
    const toggleBtn = document.createElement('button');
    toggleBtn.setAttribute('data-kt-modal-toggle', '#cancelApprovalModal');
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

function closeCancelApprovalModal() {
    const modalElement = document.getElementById('cancelApprovalModal');
    if (modalElement) {
        const dismissBtn = modalElement.querySelector('[data-kt-modal-dismiss]');
        if (dismissBtn) {
            dismissBtn.click();
        }
    }
}
</script>

<!-- Cancel Approval Confirmation Modal -->
<div class="kt-modal" data-kt-modal="true" id="cancelApprovalModal">
    <div class="kt-modal-content max-w-md">
        <div class="kt-modal-header py-4 px-5">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-10 rounded-full bg-warning/10">
                    <i class="ki-filled ki-information text-warning text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-foreground">Cancel CGC Office Approval</h3>
            </div>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true" onclick="closeCancelApprovalModal()">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form id="cancelApprovalForm" method="POST" onsubmit="closeCancelApprovalModal()">
            @csrf
            <div class="kt-modal-body py-5 px-5">
                <p class="text-sm text-secondary-foreground mb-2">
                    Officer: <span id="cancelOfficerName" class="font-medium"></span>
                </p>
                <p class="text-sm text-secondary-foreground">
                    Are you sure you want to cancel this approval? The officer will revert to automatic preretirement leave.
                </p>
            </div>
            <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeCancelApprovalModal()" class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                    Cancel
                </button>
                <button type="submit" class="kt-btn kt-btn-danger">
                    <i class="ki-filled ki-cross"></i> Cancel Approval
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

