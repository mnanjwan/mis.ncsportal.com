@extends('layouts.app')

@section('title', 'Account Change Request Details')
@section('page-title', 'Account Change Request Details')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('accounts.dashboard') }}">Accounts</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('accounts.account-change.pending') }}">Account Change Requests</a>
    <span>/</span>
    <span class="text-primary">Details</span>
@endsection

@section('content')
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="kt-card bg-success/10 border border-success/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-check-circle text-success text-xl"></i>
                    <p class="text-sm font-medium text-success">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-danger text-xl"></i>
                    <p class="text-sm font-medium text-danger">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid gap-5 lg:gap-7.5">
        <!-- Officer Information -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Officer Information</h3>
            </div>
            <div class="kt-card-content">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm text-secondary-foreground">Name</span>
                        <p class="text-sm font-semibold text-foreground mt-1">
                            {{ ($request->officer->initials ?? '') . ' ' . ($request->officer->surname ?? '') }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Service Number</span>
                        <p class="text-sm font-semibold text-foreground font-mono mt-1">
                            {{ $request->officer->service_number ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Command</span>
                        <p class="text-sm font-semibold text-foreground mt-1">
                            {{ $request->officer->presentStation->name ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Request Date</span>
                        <p class="text-sm font-semibold text-foreground mt-1">
                            {{ $request->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Information -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Current Information</h3>
            </div>
            <div class="kt-card-content">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm text-secondary-foreground">Current Bank Name</span>
                        <p class="text-sm font-semibold text-foreground mt-1">
                            {{ $request->current_bank_name ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Current Account Number</span>
                        <p class="text-sm font-semibold text-foreground font-mono mt-1">
                            {{ $request->current_account_number ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Current Sort Code</span>
                        <p class="text-sm font-semibold text-foreground font-mono mt-1">
                            {{ $request->current_sort_code ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Current PFA Name</span>
                        <p class="text-sm font-semibold text-foreground mt-1">
                            {{ $request->current_pfa_name ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Current RSA PIN</span>
                        <p class="text-sm font-semibold text-foreground font-mono mt-1">
                            {{ $request->current_rsa_pin ?? 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Requested Changes -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Requested Changes</h3>
            </div>
            <div class="kt-card-content">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($request->new_bank_name)
                        <div>
                            <span class="text-sm text-secondary-foreground">New Bank Name</span>
                            <p class="text-sm font-semibold text-foreground mt-1">
                                {{ $request->new_bank_name }}
                            </p>
                        </div>
                    @endif
                    @if($request->new_account_number)
                        <div>
                            <span class="text-sm text-secondary-foreground">New Account Number</span>
                            <p class="text-sm font-semibold text-foreground font-mono mt-1">
                                {{ $request->new_account_number }}
                            </p>
                        </div>
                    @endif
                    @if($request->new_sort_code)
                        <div>
                            <span class="text-sm text-secondary-foreground">New Sort Code</span>
                            <p class="text-sm font-semibold text-foreground font-mono mt-1">
                                {{ $request->new_sort_code }}
                            </p>
                        </div>
                    @endif
                    @if($request->new_pfa_name)
                        <div>
                            <span class="text-sm text-secondary-foreground">New PFA Name</span>
                            <p class="text-sm font-semibold text-foreground mt-1">
                                {{ $request->new_pfa_name }}
                            </p>
                        </div>
                    @endif
                    @if($request->new_rsa_pin)
                        <div>
                            <span class="text-sm text-secondary-foreground">New RSA PIN</span>
                            <p class="text-sm font-semibold text-foreground font-mono mt-1">
                                {{ $request->new_rsa_pin }}
                            </p>
                        </div>
                    @endif
                </div>
                @if($request->reason)
                    <div class="mt-4 pt-4 border-t border-border">
                        <span class="text-sm text-secondary-foreground">Reason for Change</span>
                        <p class="text-sm text-foreground mt-1">{{ $request->reason }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Status Information -->
        @if($request->status !== 'PENDING')
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Verification Details</h3>
                </div>
                <div class="kt-card-content">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm text-secondary-foreground">Status</span>
                            <p class="mt-1">
                                <span class="kt-badge kt-badge-{{ $request->status === 'APPROVED' ? 'success' : 'danger' }} kt-badge-sm">
                                    {{ $request->status }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <span class="text-sm text-secondary-foreground">Verified By</span>
                            <p class="text-sm font-semibold text-foreground mt-1">
                                {{ $request->verifier->name ?? 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <span class="text-sm text-secondary-foreground">Verified At</span>
                            <p class="text-sm font-semibold text-foreground mt-1">
                                {{ $request->verified_at ? $request->verified_at->format('d/m/Y H:i') : 'N/A' }}
                            </p>
                        </div>
                        @if($request->rejection_reason)
                            <div class="md:col-span-2">
                                <span class="text-sm text-secondary-foreground">Rejection Reason</span>
                                <p class="text-sm text-foreground mt-1">{{ $request->rejection_reason }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        @if($request->status === 'PENDING')
            <div class="kt-card">
                <div class="kt-card-content">
                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('accounts.account-change.pending') }}" class="kt-btn kt-btn-secondary">
                            <i class="ki-filled ki-left"></i> Back to List
                        </a>
                        <button 
                            onclick="showRejectModal()"
                            class="kt-btn kt-btn-danger">
                            <i class="ki-filled ki-cross"></i> Reject
                        </button>
                        <button 
                            onclick="showApproveModal()"
                            class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-check"></i> Approve
                        </button>
                    </div>
                </div>
            </div>
        @else
            <div class="kt-card">
                <div class="kt-card-content">
                    <div class="flex items-center justify-end">
                        <a href="{{ route('accounts.account-change.pending') }}" class="kt-btn kt-btn-secondary">
                            <i class="ki-filled ki-left"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Approve Confirmation Modal -->
    @if($request->status === 'PENDING')
        <div class="kt-modal" data-kt-modal="true" id="approve-confirm-modal">
            <div class="kt-modal-content max-w-[400px]">
                <div class="kt-modal-header py-4 px-5">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center size-10 rounded-full bg-success/10">
                            <i class="ki-filled ki-information text-success text-xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-foreground">Confirm Approval</h3>
                    </div>
                    <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                        <i class="ki-filled ki-cross"></i>
                    </button>
                </div>
                <div class="kt-modal-body py-5 px-5">
                    <p class="text-sm text-secondary-foreground">
                        Are you sure you want to approve this change request? The officer's bank account information will be updated accordingly.
                    </p>
                </div>
                <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                    <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                        Cancel
                    </button>
                    <form action="{{ route('accounts.account-change.approve', $request->id) }}" method="POST" class="inline" id="approveForm">
                        @csrf
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-check"></i>
                            <span>Approve</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div class="kt-modal" data-kt-modal="true" id="rejectModal">
            <div class="kt-modal-content max-w-[500px]">
                <div class="kt-modal-header py-4 px-5">
                    <h3 class="text-lg font-semibold text-foreground">Reject Request</h3>
                    <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                        <i class="ki-filled ki-cross"></i>
                    </button>
                </div>
                <form action="{{ route('accounts.account-change.reject', $request->id) }}" method="POST" id="rejectForm">
                    @csrf
                    <div class="kt-modal-body py-5 px-5">
                        <div class="flex flex-col gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Rejection Reason <span class="text-danger">*</span></label>
                                <textarea name="rejection_reason" 
                                          rows="4" 
                                          class="kt-input w-full" 
                                          required
                                          placeholder="Enter reason for rejection"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                        <button type="button" class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                            Cancel
                        </button>
                        <button type="submit" class="kt-btn kt-btn-danger">
                            Reject Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @push('scripts')
        <script>
            function showApproveModal() {
                const modal = document.getElementById('approve-confirm-modal');
                if (typeof KTModal !== 'undefined') {
                    const modalInstance = KTModal.getInstance(modal) || new KTModal(modal);
                    modalInstance.show();
                } else {
                    modal.style.display = 'flex';
                }
            }

            function showRejectModal() {
                const modal = document.getElementById('rejectModal');
                if (typeof KTModal !== 'undefined') {
                    const modalInstance = KTModal.getInstance(modal) || new KTModal(modal);
                    modalInstance.show();
                } else {
                    modal.style.display = 'flex';
                }
            }
        </script>
    @endpush
@endsection
