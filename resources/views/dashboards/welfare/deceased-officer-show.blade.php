@extends('layouts.app')

@section('title', 'Deceased Officer Details')
@section('page-title', 'Deceased Officer Details')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('welfare.dashboard') }}">Welfare</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('welfare.deceased-officers') }}">Deceased Officers</a>
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
                        <span class="text-sm text-secondary-foreground">SVC no</span>
                        <p class="text-sm font-semibold text-foreground font-mono mt-1">
                            {{ $deceasedOfficer->officer->service_number ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Rank</span>
                        <p class="text-sm font-semibold text-foreground mt-1">
                            {{ $deceasedOfficer->officer->substantive_rank ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Name</span>
                        <p class="text-sm font-semibold text-foreground mt-1">
                            {{ ($deceasedOfficer->officer->initials ?? '') . ' ' . ($deceasedOfficer->officer->surname ?? '') }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">DOB</span>
                        <p class="text-sm font-semibold text-foreground mt-1">
                            {{ $deceasedOfficer->officer->date_of_birth ? $deceasedOfficer->officer->date_of_birth->format('d/m/Y') : 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Command</span>
                        <p class="text-sm font-semibold text-foreground mt-1">
                            {{ $deceasedOfficer->officer->presentStation->name ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Date of Death</span>
                        <p class="text-sm font-semibold text-foreground mt-1">
                            {{ $deceasedOfficer->date_of_death->format('d/m/Y') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Next of Kin Information -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Next of Kin(s)</h3>
            </div>
            <div class="kt-card-content">
                @if($deceasedOfficer->next_of_kin_data && count($deceasedOfficer->next_of_kin_data) > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($deceasedOfficer->next_of_kin_data as $index => $kin)
                            <div class="p-4 rounded-lg bg-muted/50 border border-input">
                                <h4 class="text-sm font-semibold text-foreground mb-3">Next of Kin {{ $index + 1 }}</h4>
                                <div class="flex flex-col gap-2">
                                    <div>
                                        <span class="text-xs text-secondary-foreground">Name</span>
                                        <p class="text-sm font-medium text-foreground">{{ $kin['name'] ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-xs text-secondary-foreground">Relationship</span>
                                        <p class="text-sm font-medium text-foreground">{{ $kin['relationship'] ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-xs text-secondary-foreground">Phone Number</span>
                                        <p class="text-sm font-medium text-foreground">{{ $kin['phone_number'] ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-xs text-secondary-foreground">Email</span>
                                        <p class="text-sm font-medium text-foreground">{{ $kin['email'] ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-xs text-secondary-foreground">Address</span>
                                        <p class="text-sm font-medium text-foreground">{{ $kin['address'] ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @elseif($deceasedOfficer->officer->nextOfKin && $deceasedOfficer->officer->nextOfKin->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($deceasedOfficer->officer->nextOfKin as $kin)
                            <div class="p-4 rounded-lg bg-muted/50 border border-input">
                                <h4 class="text-sm font-semibold text-foreground mb-3">Next of Kin</h4>
                                <div class="flex flex-col gap-2">
                                    <div>
                                        <span class="text-xs text-secondary-foreground">Name</span>
                                        <p class="text-sm font-medium text-foreground">{{ $kin->name }}</p>
                                    </div>
                                    <div>
                                        <span class="text-xs text-secondary-foreground">Relationship</span>
                                        <p class="text-sm font-medium text-foreground">{{ $kin->relationship }}</p>
                                    </div>
                                    <div>
                                        <span class="text-xs text-secondary-foreground">Phone Number</span>
                                        <p class="text-sm font-medium text-foreground">{{ $kin->phone_number ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-xs text-secondary-foreground">Email</span>
                                        <p class="text-sm font-medium text-foreground">{{ $kin->email ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-xs text-secondary-foreground">Address</span>
                                        <p class="text-sm font-medium text-foreground">{{ $kin->address ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-secondary-foreground text-center py-4">No Next of Kin information available</p>
                @endif
            </div>
        </div>

        <!-- Banking Information -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Banking Information</h3>
            </div>
            <div class="kt-card-content">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm text-secondary-foreground">Bank Name</span>
                        <p class="text-sm font-semibold text-foreground mt-1">
                            {{ $deceasedOfficer->bank_name ?? ($deceasedOfficer->officer->bank_name ?? 'N/A') }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Account Number</span>
                        <p class="text-sm font-semibold text-foreground font-mono mt-1">
                            {{ $deceasedOfficer->bank_account_number ?? ($deceasedOfficer->officer->bank_account_number ?? 'N/A') }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">RSA Administrator</span>
                        <p class="text-sm font-semibold text-foreground mt-1">
                            {{ $deceasedOfficer->rsa_administrator ?? ($deceasedOfficer->officer->pfa_name ?? 'N/A') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Information -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Report Information</h3>
            </div>
            <div class="kt-card-content">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm text-secondary-foreground">Reported By</span>
                        <p class="text-sm font-semibold text-foreground mt-1">
                            {{ $deceasedOfficer->reportedBy->name ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Reported At</span>
                        <p class="text-sm font-semibold text-foreground mt-1">
                            {{ $deceasedOfficer->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    @if($deceasedOfficer->validated_at)
                        <div>
                            <span class="text-sm text-secondary-foreground">Validated By</span>
                            <p class="text-sm font-semibold text-foreground mt-1">
                                {{ $deceasedOfficer->validatedBy->name ?? 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <span class="text-sm text-secondary-foreground">Validated At</span>
                            <p class="text-sm font-semibold text-foreground mt-1">
                                {{ $deceasedOfficer->validated_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    @endif
                    @if($deceasedOfficer->death_certificate_url)
                        <div class="md:col-span-2">
                            <span class="text-sm text-secondary-foreground">Death Certificate</span>
                            <p class="mt-2">
                                <a href="{{ asset('storage/' . $deceasedOfficer->death_certificate_url) }}" 
                                   target="_blank"
                                   class="kt-btn kt-btn-sm kt-btn-primary">
                                    <i class="ki-filled ki-file"></i> View Certificate
                                </a>
                            </p>
                        </div>
                    @endif
                    @if($deceasedOfficer->notes)
                        <div class="md:col-span-2">
                            <span class="text-sm text-secondary-foreground">Notes</span>
                            <p class="text-sm text-foreground mt-1">{{ $deceasedOfficer->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        @if(!$deceasedOfficer->validated_at)
            <div class="kt-card">
                <div class="kt-card-content">
                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('welfare.deceased-officers') }}" class="kt-btn kt-btn-secondary">
                            <i class="ki-filled ki-left"></i> Back to List
                        </a>
                        <button 
                            onclick="showValidateModal()"
                            class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-check"></i> Validate & Generate Data
                        </button>
                    </div>
                </div>
            </div>
        @else
            <!-- Benefits Processing Status -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Benefits Processing</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-sm text-secondary-foreground">Benefits Status</span>
                            <p class="mt-1">
                                @if($deceasedOfficer->benefits_processed)
                                    <span class="kt-badge kt-badge-success kt-badge-sm">Processed</span>
                                    <span class="text-xs text-secondary-foreground ml-2">
                                        {{ $deceasedOfficer->benefits_processed_at ? $deceasedOfficer->benefits_processed_at->format('d/m/Y H:i') : '' }}
                                    </span>
                                @else
                                    <span class="kt-badge kt-badge-warning kt-badge-sm">Pending</span>
                                @endif
                            </p>
                        </div>
                        @if(!$deceasedOfficer->benefits_processed)
                            <button 
                                onclick="showMarkProcessedModal()"
                                class="kt-btn kt-btn-sm kt-btn-success">
                                <i class="ki-filled ki-check"></i> Mark as Processed
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="kt-card">
                <div class="kt-card-content">
                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('welfare.deceased-officers') }}" class="kt-btn kt-btn-secondary">
                            <i class="ki-filled ki-left"></i> Back to List
                        </a>
                        <a href="{{ route('welfare.deceased-officers.report', $deceasedOfficer->id) }}" class="kt-btn kt-btn-info">
                            <i class="ki-filled ki-file"></i> View Report
                        </a>
                        <a href="{{ route('welfare.deceased-officers.export', $deceasedOfficer->id) }}" class="kt-btn kt-btn-success">
                            <i class="ki-filled ki-file-down"></i> Export Data
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Mark Benefits Processed Modal -->
    @if($deceasedOfficer->validated_at && !$deceasedOfficer->benefits_processed)
        <div class="kt-modal" data-kt-modal="true" id="mark-processed-modal">
            <div class="kt-modal-content max-w-[400px]">
                <div class="kt-modal-header py-4 px-5">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center size-10 rounded-full bg-success/10">
                            <i class="ki-filled ki-information text-success text-xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-foreground">Mark Benefits as Processed</h3>
                    </div>
                    <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                        <i class="ki-filled ki-cross"></i>
                    </button>
                </div>
                <div class="kt-modal-body py-5 px-5">
                    <p class="text-sm text-secondary-foreground">
                        Are you sure you want to mark benefits as processed for this deceased officer?
                    </p>
                </div>
                <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                    <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                        Cancel
                    </button>
                    <form action="{{ route('welfare.deceased-officers.mark-benefits-processed', $deceasedOfficer->id) }}" method="POST" class="inline" id="markProcessedForm">
                        @csrf
                        <button type="submit" class="kt-btn kt-btn-success">
                            <i class="ki-filled ki-check"></i>
                            <span>Mark as Processed</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Validate Confirmation Modal -->
    @if(!$deceasedOfficer->validated_at)
        <div class="kt-modal" data-kt-modal="true" id="validate-confirm-modal">
            <div class="kt-modal-content max-w-[400px]">
                <div class="kt-modal-header py-4 px-5">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center size-10 rounded-full bg-success/10">
                            <i class="ki-filled ki-information text-success text-xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-foreground">Confirm Validation</h3>
                    </div>
                    <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                        <i class="ki-filled ki-cross"></i>
                    </button>
                </div>
                <div class="kt-modal-body py-5 px-5">
                    <p class="text-sm text-secondary-foreground">
                        Are you sure you want to validate this deceased officer? This will generate comprehensive data including Next of Kin, Banking, and RSA information. The officer will be permanently marked as deceased.
                    </p>
                </div>
                <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                    <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                        Cancel
                    </button>
                    <form action="{{ route('welfare.deceased-officers.validate', $deceasedOfficer->id) }}" method="POST" class="inline" id="validateForm">
                        @csrf
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-check"></i>
                            <span>Validate</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
        <script>
            function showValidateModal() {
                const modal = document.getElementById('validate-confirm-modal');
                if (typeof KTModal !== 'undefined') {
                    const modalInstance = KTModal.getInstance(modal) || new KTModal(modal);
                    modalInstance.show();
                } else {
                    modal.style.display = 'flex';
                }
            }

            function showMarkProcessedModal() {
                const modal = document.getElementById('mark-processed-modal');
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
