@extends('layouts.app')

@section('title', 'Fleet Request')
@section('page-title', 'Fleet Request')

@section('breadcrumbs')
    @php
        $user = auth()->user();
        $dashboardRoute = null;
        if ($user->hasRole('CD')) {
            $dashboardRoute = route('fleet.cd.dashboard');
        } elseif ($user->hasRole('O/C T&L')) {
            $dashboardRoute = route('fleet.oc-tl.dashboard');
        } elseif ($user->hasRole('Transport Store/Receiver')) {
            $dashboardRoute = route('fleet.store-receiver.dashboard');
        } elseif ($user->hasRole('CC T&L')) {
            $dashboardRoute = route('fleet.cc-tl.dashboard');
        } elseif ($user->hasRole('DCG FATS')) {
            $dashboardRoute = route('fleet.dcg-fats.dashboard');
        } elseif ($user->hasRole('ACG TS')) {
            $dashboardRoute = route('fleet.acg-ts.dashboard');
        } elseif ($user->hasRole('CGC')) {
            $dashboardRoute = route('cgc.dashboard');
        } elseif ($user->hasRole('Area Controller')) {
            $dashboardRoute = route('area-controller.dashboard');
        }
    @endphp
    @if($dashboardRoute)
        <a class="text-secondary-foreground hover:text-primary" href="{{ $dashboardRoute }}">@if($user->hasRole('CGC') || $user->hasRole('Area Controller'))Dashboard @else Fleet @endif</a>
        <span>/</span>
    @endif
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('fleet.requests.index') }}">Requests</a>
    <span>/</span>
    <span class="text-primary">View #{{ $fleetRequest->id }}</span>
@endsection

@section('content')
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-5 lg:gap-7.5">
        {{-- Request details: what you are reviewing --}}
        <div class="kt-card border-l-4 border-primary">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Request details (what you are reviewing)</h3>
            </div>
            <div class="kt-card-content p-5 lg:p-7.5 text-sm">
                <p class="text-secondary-foreground mb-4">
                    Request #{{ $fleetRequest->id }} — {{ str_replace('_', ' ', $fleetRequest->request_type) }} from <strong>{{ $fleetRequest->originCommand->name ?? 'Unknown' }}</strong>.
                    @if($fleetRequest->request_type === 'FLEET_NEW_VEHICLE')
                        Vehicle: <strong>{{ $fleetRequest->requested_quantity ?? 1 }} × {{ $fleetRequest->requested_vehicle_type ?? 'N/A' }}</strong>
                        @if($fleetRequest->requested_make || $fleetRequest->requested_model || $fleetRequest->requested_year)
                            ({{ trim(implode(' ', array_filter([$fleetRequest->requested_make, $fleetRequest->requested_model, $fleetRequest->requested_year ? (string)$fleetRequest->requested_year : null]))) }})
                        @endif
                    @endif
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div><strong>Origin Command:</strong> {{ $fleetRequest->originCommand->name ?? '-' }}</div>
                    <div><strong>Status:</strong> <span class="px-2 py-1 rounded-full bg-primary/10 text-primary font-medium">{{ $fleetRequest->status }}</span></div>
                    <div><strong>Created By:</strong> {{ $fleetRequest->createdBy->name ?? $fleetRequest->createdBy->email }}</div>
                    @if($fleetRequest->request_type === 'FLEET_NEW_VEHICLE')
                        <div><strong>Requested vehicle type:</strong> {{ $fleetRequest->requested_vehicle_type ?? '-' }}</div>
                        <div><strong>Requested quantity:</strong> {{ $fleetRequest->requested_quantity ?? 1 }}</div>
                        @if($fleetRequest->requested_make)<div><strong>Requested make:</strong> {{ $fleetRequest->requested_make }}</div>@endif
                        @if($fleetRequest->requested_model)<div><strong>Requested model:</strong> {{ $fleetRequest->requested_model }}</div>@endif
                        @if($fleetRequest->requested_year)<div><strong>Requested year:</strong> {{ $fleetRequest->requested_year }}</div>@endif
                    @endif
                    @if($fleetRequest->vehicle)
                        <div><strong>Target vehicle:</strong> {{ $fleetRequest->vehicle->make }} {{ $fleetRequest->vehicle->model }} ({{ $fleetRequest->vehicle->reg_no }})</div>
                    @endif
                    @if($fleetRequest->amount)
                        <div><strong>Amount:</strong> ₦{{ number_format($fleetRequest->amount, 2) }}</div>
                    @endif
                    @if($fleetRequest->document_path)
                        <div class="md:col-span-2 lg:col-span-3">
                            <strong>Supporting document:</strong>
                            <a href="{{ Storage::url($fleetRequest->document_path) }}" target="_blank" class="text-primary hover:underline inline-flex items-center gap-1 ml-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                View attachment
                            </a>
                        </div>
                    @endif
                </div>
                @if($fleetRequest->notes)
                    <div class="mt-4 p-3 bg-muted rounded">
                        <strong>Notes / description from requester:</strong><br>
                        {{ $fleetRequest->notes }}
                    </div>
                @else
                    <div class="mt-4 p-3 bg-muted/50 rounded text-muted-foreground">
                        <strong>Notes:</strong> None provided.
                    </div>
                @endif
            </div>
        </div>

        {{-- Specific vehicle(s) proposed by CC T&L (when any reserved) --}}
        @if($fleetRequest->request_type === 'FLEET_NEW_VEHICLE' && $fleetRequest->reservedVehicles->isNotEmpty())
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Vehicles proposed for this request ({{ $fleetRequest->reservedVehicles->count() }})</h3>
                </div>
                <div class="kt-card-content p-5 lg:p-7.5">
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full text-sm">
                            <thead>
                                <tr>
                                    <th class="text-left">Reg No</th>
                                    <th class="text-left">Chassis No</th>
                                    <th class="text-left">Engine No</th>
                                    <th class="text-left">Make</th>
                                    <th class="text-left">Model</th>
                                    <th class="text-left">Year</th>
                                    <th class="text-left">Type</th>
                                    <th class="text-left">Service status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fleetRequest->reservedVehicles as $v)
                                    <tr>
                                        <td>{{ $v->reg_no ?? '–' }}</td>
                                        <td>{{ $v->chassis_number ?? '–' }}</td>
                                        <td>{{ $v->engine_number ?? '–' }}</td>
                                        <td>{{ $v->make ?? '–' }}</td>
                                        <td>{{ $v->model ?? '–' }}</td>
                                        <td>{{ $v->year_of_manufacture ?? '–' }}</td>
                                        <td>{{ $v->vehicle_type ?? '–' }}</td>
                                        <td>{{ $v->service_status ?? '–' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- Specific vehicle requested (Re-allocation / Repair / OPE / Use) --}}
        @if(in_array($fleetRequest->request_type, ['FLEET_RE_ALLOCATION', 'FLEET_REPAIR', 'FLEET_OPE', 'FLEET_USE']) && $fleetRequest->vehicle)
            @php $v = $fleetRequest->vehicle; @endphp
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Vehicle requested (full details)</h3>
                </div>
                <div class="kt-card-content p-5 lg:p-7.5">
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full text-sm">
                            <tbody>
                                <tr><td class="font-medium w-48">Registration number</td><td>{{ $v->reg_no ?? '–' }}</td></tr>
                                <tr><td class="font-medium">Chassis number</td><td>{{ $v->chassis_number ?? '–' }}</td></tr>
                                <tr><td class="font-medium">Engine number</td><td>{{ $v->engine_number ?? '–' }}</td></tr>
                                <tr><td class="font-medium">Make</td><td>{{ $v->make ?? '–' }}</td></tr>
                                <tr><td class="font-medium">Model</td><td>{{ $v->model ?? '–' }}</td></tr>
                                <tr><td class="font-medium">Year of manufacture</td><td>{{ $v->year_of_manufacture ?? '–' }}</td></tr>
                                <tr><td class="font-medium">Vehicle type</td><td>{{ $v->vehicle_type ?? '–' }}</td></tr>
                                <tr><td class="font-medium">Service status</td><td>{{ $v->service_status ?? '–' }}</td></tr>
                                <tr><td class="font-medium">Lifecycle status</td><td>{{ $v->lifecycle_status ?? '–' }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @php
            $currentStep = $fleetRequest->steps->firstWhere('step_order', $fleetRequest->current_step_order);
            $currentRole = $currentStep?->role_name;
            $currentAction = $currentStep?->action;
            $userRoleNames = auth()->user()->roles()->wherePivot('is_active', true)->pluck('name')->toArray();
            $isMyStep = $currentRole && in_array($currentRole, $userRoleNames, true);
        @endphp

        {{-- CC T&L Specific Action Panels --}}
        @if($isMyStep && $currentRole === 'CC T&L')
            @if($fleetRequest->request_type === 'FLEET_NEW_VEHICLE' && $currentStep->step_order === 1)
                <div class="kt-card border-l-4 border-primary">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">CC T&L Inventory Check & Proposal (Step 1)</h3>
                    </div>
                    <div class="kt-card-content p-5 lg:p-7.5">
                        <form method="POST" action="{{ route('fleet.requests.cc-tl.propose', $fleetRequest) }}" class="grid gap-4" id="ccProposalForm">
                            @csrf
                            <div>
                                <label class="text-sm font-medium">Comment (optional)</label>
                                <textarea class="kt-textarea w-full" name="comment" rows="3"></textarea>
                            </div>
                            <div class="text-sm text-secondary-foreground mb-2">
                                Select available vehicles to reserve for this request. If none are selected, request becomes <strong>KIV</strong>.
                            </div>
                            <div class="overflow-x-auto max-h-64 mb-4">
                                <table class="kt-table w-full">
                                    <thead class="sticky top-0 bg-background">
                                        <tr>
                                            <th>Select</th>
                                            <th>Reg No</th>
                                            <th>Type</th>
                                            <th>Make/Model</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($availableVehicles as $v)
                                            <tr>
                                                <td><input type="checkbox" name="vehicle_ids[]" value="{{ $v->id }}" /></td>
                                                <td>{{ $v->reg_no }}</td>
                                                <td>{{ $v->vehicle_type }}</td>
                                                <td>{{ $v->make }} {{ $v->model }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4">No available vehicles found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <button class="kt-btn kt-btn-primary" type="submit">Submit Proposal</button>
                        </form>
                    </div>
                </div>
            @endif

            @if(($fleetRequest->request_type === 'FLEET_NEW_VEHICLE' && $currentStep->step_order === 5) || ($fleetRequest->request_type === 'FLEET_RE_ALLOCATION' && $currentStep->step_order === 1))
                <div class="kt-card border-l-4 border-success">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Release Vehicle(s) to Command</h3>
                    </div>
                    <div class="kt-card-content p-5 lg:p-7.5">
                        <p class="text-sm mb-4">
                            @if($fleetRequest->request_type === 'FLEET_NEW_VEHICLE')
                                Reserved vehicles: <strong>{{ $fleetRequest->reservedVehicles->count() }}</strong>
                            @else
                                Vehicle for re-allocation: <strong>{{ $fleetRequest->vehicle->reg_no }}</strong>
                            @endif
                        </p>
                        <form method="POST" action="{{ route('fleet.requests.cc-tl.release', $fleetRequest) }}" class="grid gap-4">
                            @csrf
                            <textarea class="kt-textarea w-full" name="comment" rows="3" placeholder="Notes on release..."></textarea>
                            <button class="kt-btn kt-btn-success" type="submit">Approve and Release Vehicle(s)</button>
                        </form>
                    </div>
                </div>
            @endif
        @endif

        {{-- Generic Action Panel --}}
        @if($isMyStep && $currentAction !== 'REVIEW')
            <div class="kt-card border-l-4 border-primary">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Action Required (Step {{ $currentStep->step_order }} - {{ $currentRole }})</h3>
                </div>
                <div class="kt-card-content p-5 lg:p-7.5">
                    <form method="POST" action="{{ route('fleet.requests.act', $fleetRequest) }}" class="grid gap-4" id="actionForm">
                        @csrf
                        <div>
                            <label class="text-sm font-medium">Comments</label>
                            <textarea class="kt-textarea w-full" name="comment" rows="3"></textarea>
                        </div>
                        <input type="hidden" name="decision" id="decisionInput" value="" />
                        <div class="flex gap-2">
                            @if($currentAction === 'FORWARD')
                                <button class="kt-btn kt-btn-primary" type="button" id="forwardBtn">Forward</button>
                            @elseif($currentAction === 'APPROVE')
                                <button class="kt-btn kt-btn-success" type="button" id="approveBtn">Approve</button>
                                <button class="kt-btn kt-btn-danger" type="button" id="rejectBtn">Reject</button>
                            @endif
                            <button class="kt-btn kt-btn-secondary" type="button" id="kivBtn">Place on KIV</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Workflow Progress</h3>
            </div>
            <div class="kt-card-content p-5 lg:p-7.5">
                <div class="overflow-x-auto">
                    <table class="kt-table w-full">
                        <thead>
                            <tr>
                                <th class="text-left">Order</th>
                                <th class="text-left">Role</th>
                                <th class="text-left">Action</th>
                                <th class="text-left">Decision</th>
                                <th class="text-left">Comment</th>
                                <th class="text-left">By</th>
                                <th class="text-left">At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fleetRequest->steps as $s)
                                @php
                                    $isCurrent = (int) $fleetRequest->current_step_order === (int) $s->step_order;
                                @endphp
                                <tr class="{{ $isCurrent ? 'bg-primary/5 font-semibold text-primary' : '' }}">
                                    <td>{{ $s->step_order }}</td>
                                    <td>{{ $s->role_name }}</td>
                                    <td>{{ $s->action }}</td>
                                    <td>
                                        @if($s->decision)
                                            <span class="px-2 py-0.5 rounded text-xs {{ $s->decision === 'APPROVED' ? 'bg-green-100 text-green-800' : 'bg-muted text-foreground' }}">
                                                {{ $s->decision }}
                                            </span>
                                        @else
                                            <span class="text-muted-foreground italic">Pending</span>
                                        @endif
                                    </td>
                                    <td class="max-w-[200px]">
                                        @if(!empty($s->comment))
                                            <span class="text-sm text-secondary-foreground" title="{{ $s->comment }}">{{ Str::limit($s->comment, 60) }}</span>
                                        @else
                                            <span class="text-muted-foreground">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $s->actedBy->name ?? ($s->actedBy->email ?? '-') }}</td>
                                    <td>{{ $s->acted_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="kt-modal hidden" data-kt-modal="true" id="confirm-modal">
        <div class="kt-modal-content max-w-[500px]">
            <div class="kt-modal-header py-4 px-5">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center size-10 rounded-full bg-warning/10" id="confirm-modal-icon">
                        <i class="ki-filled ki-information text-warning text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-foreground" id="confirm-modal-title">Confirm Action</h3>
                </div>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <div class="kt-modal-body py-5 px-5">
                <p class="text-sm text-secondary-foreground whitespace-pre-line" id="confirm-modal-message">
                    Are you sure you want to proceed?
                </p>
            </div>
            <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true" id="confirm-modal-cancel">
                    Cancel
                </button>
                <button class="kt-btn kt-btn-primary" id="confirm-modal-confirm">
                    <span class="kt-menu-icon"><i class="ki-filled ki-check"></i></span>
                    <span>Confirm</span>
                </button>
            </div>
        </div>
    </div>
    <!-- End of Confirmation Modal -->

    @push('scripts')
    <script>
        function showConfirmModal(title, message, onConfirm, type = 'warning') {
            const modal = document.getElementById('confirm-modal');
            const modalTitle = document.getElementById('confirm-modal-title');
            const modalMessage = document.getElementById('confirm-modal-message');
            const confirmBtn = document.getElementById('confirm-modal-confirm');
            const cancelBtn = document.getElementById('confirm-modal-cancel');
            const iconDiv = document.getElementById('confirm-modal-icon');

            modalTitle.textContent = title;
            modalMessage.textContent = message;

            if (type === 'error') {
                iconDiv.className = 'flex items-center justify-center size-10 rounded-full bg-danger/10';
                iconDiv.innerHTML = '<i class="ki-filled ki-information text-danger text-xl"></i>';
                confirmBtn.className = 'kt-btn kt-btn-danger';
            } else if (type === 'success') {
                iconDiv.className = 'flex items-center justify-center size-10 rounded-full bg-success/10';
                iconDiv.innerHTML = '<i class="ki-filled ki-check-circle text-success text-xl"></i>';
                confirmBtn.className = 'kt-btn kt-btn-success';
            } else {
                iconDiv.className = 'flex items-center justify-center size-10 rounded-full bg-warning/10';
                iconDiv.innerHTML = '<i class="ki-filled ki-information text-warning text-xl"></i>';
                confirmBtn.className = 'kt-btn kt-btn-primary';
            }

            confirmBtn.onclick = () => {
                onConfirm();
                if (typeof KTModal !== 'undefined') {
                    const modalInstance = KTModal.getInstance(modal) || new KTModal(modal);
                    modalInstance.hide();
                } else {
                    modal.classList.add('hidden');
                    modal.style.display = 'none';
                }
            };

            if (typeof KTModal !== 'undefined') {
                const modalInstance = KTModal.getInstance(modal) || new KTModal(modal);
                modalInstance.show();
            } else {
                modal.classList.remove('hidden');
                modal.style.display = 'flex';
            }
        }

        // Update form submissions to use modals
        document.addEventListener('DOMContentLoaded', function() {
            // CC T&L Proposal Form
            const proposalForm = document.getElementById('ccProposalForm');
            if (proposalForm) {
                proposalForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const requestId = {{ $fleetRequest->id }};
                    const requestedQuantity = {{ $fleetRequest->requested_quantity ?? 0 }};
                    const selectedVehicles = document.querySelectorAll('input[name="vehicle_ids[]"]:checked').length;
                    
                    let title = 'Submit Vehicle Proposal';
                    let message = 'Are you sure you want to submit your vehicle proposal for Request #' + requestId + '?\n\n';
                    let type = 'warning';
                    
                    if (selectedVehicles === 0) {
                        title = '⚠️ WARNING: No Vehicles Selected';
                        message += '⚠️ WARNING: No vehicles selected!\n\n';
                        message += 'WHAT WILL HAPPEN:\n';
                        message += '• The request will be placed on KIV (Keep In View)\n';
                        message += '• No vehicles will be reserved\n';
                        message += '• The request will remain at your step\n';
                        message += '• You can resume it later when vehicles become available\n\n';
                        message += 'WHY:\n';
                        message += 'Since no vehicles are available or selected, the request is paused until inventory becomes available.';
                        type = 'error';
                    } else {
                        message += 'WHAT WILL HAPPEN:\n';
                        message += '• ' + selectedVehicles + ' vehicle(s) will be reserved for this request\n';
                        message += '• The request will be forwarded to CGC Office for approval\n';
                        message += '• Reserved vehicles cannot be selected by other requests\n';
                        message += '• If approved, vehicles will proceed through the approval chain\n\n';
                        message += 'WHY:\n';
                        message += 'Reserving vehicles ensures they are available for this request. After CGC Office approval, the request goes through DCG FATS and ACG TS before final release.';
                    }
                    
                    showConfirmModal(title, message, function() {
                        proposalForm.submit();
                    }, type);
                });
            }

            // CC T&L Release Form
            const releaseForm = document.querySelector('form[action*="cc-tl.release"]');
            if (releaseForm) {
                releaseForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const requestId = {{ $fleetRequest->id }};
                    const requestType = '{{ $fleetRequest->request_type }}';
                    const vehicleCount = {{ $fleetRequest->request_type === 'FLEET_NEW_VEHICLE' ? $fleetRequest->reservedVehicles->count() : 1 }};
                    
                    let message = 'Are you sure you want to approve and release vehicle(s) for Request #' + requestId + '?\n\n';
                    
                    if (requestType === 'FLEET_NEW_VEHICLE') {
                        message += 'WHAT WILL HAPPEN:\n';
                        message += '• ' + vehicleCount + ' reserved vehicle(s) will be released to the requesting command\n';
                        message += '• Vehicles will be assigned to the command pool\n';
                        message += '• The request status will change to RELEASED\n';
                        message += '• The requesting command will be notified\n';
                        message += '• Vehicles will be available for issuance to officers\n\n';
                        message += 'WHY:\n';
                        message += 'This is the final step after all approvals. Releasing vehicles makes them available to the requesting command for their operational needs.';
                    } else {
                        message += 'WHAT WILL HAPPEN:\n';
                        message += '• The vehicle will be re-allocated to the requesting command\n';
                        message += '• The vehicle will be assigned to the command pool\n';
                        message += '• The request status will change to RELEASED\n';
                        message += '• The requesting command will be notified\n\n';
                        message += 'WHY:\n';
                        message += 'Re-allocation requests go directly to release after your approval, making the vehicle immediately available to the requesting command.';
                    }
                    
                    showConfirmModal('Approve and Release Vehicles', message, function() {
                        releaseForm.submit();
                    }, 'success');
                });
            }

            // Action Form buttons
            const actionForm = document.getElementById('actionForm');
            if (actionForm) {
                const forwardBtn = document.getElementById('forwardBtn');
                const approveBtn = document.getElementById('approveBtn');
                const rejectBtn = document.getElementById('rejectBtn');
                const kivBtn = document.getElementById('kivBtn');

                if (forwardBtn) {
                    forwardBtn.onclick = function() {
                        const requestId = {{ $fleetRequest->id }};
                        const roleName = '{{ $currentRole }}';
                        const message = 'Are you sure you want to FORWARD Request #' + requestId + '?\n\n' +
                            'WHAT WILL HAPPEN:\n' +
                            '• The request will be forwarded to the next step in the workflow\n' +
                            '• The next approver will receive a notification\n' +
                            '• The request will move to the next role in the chain\n' +
                            '• You will no longer see this request in your inbox\n\n' +
                            'WHY:\n' +
                            'Forwarding moves the request to the next step. This is typically used when your role is to review and pass along the request without making an approval decision.';
                        
                        showConfirmModal('Forward Request', message, function() {
                            document.getElementById('decisionInput').value = 'FORWARDED';
                            actionForm.submit();
                        }, 'warning');
                    };
                }

                if (approveBtn) {
                    approveBtn.onclick = function() {
                        const requestId = {{ $fleetRequest->id }};
                        const roleName = '{{ $currentRole }}';
                        const requestType = '{{ $fleetRequest->request_type }}';
                        const amount = {{ $fleetRequest->amount ?? 0 }};
                        
                        let message = 'Are you sure you want to APPROVE Request #' + requestId + '?\n\n';
                        message += 'WHAT WILL HAPPEN:\n';
                        
                        if (requestType === 'FLEET_REQUISITION') {
                            if (amount <= 300000) {
                                message += '• This requisition (₦' + amount.toLocaleString() + ') will be APPROVED and COMPLETED\n';
                                message += '• The request will be marked as RELEASED\n';
                                message += '• No further approvals are required (amount ≤ ₦300,000)\n';
                            } else if (amount <= 500000) {
                                message += '• This requisition (₦' + amount.toLocaleString() + ') will be forwarded to DCG FATS\n';
                                message += '• DCG FATS will review and approve (amount > ₦300,000)\n';
                            } else {
                                message += '• This requisition (₦' + amount.toLocaleString() + ') will be forwarded to CGC Office\n';
                                message += '• CGC Office will review and approve (amount > ₦500,000)\n';
                            }
                        } else if (requestType === 'FLEET_NEW_VEHICLE') {
                            message += '• The vehicle proposal will be approved\n';
                            message += '• The request will be forwarded to the next step in the chain\n';
                            message += '• Vehicles will proceed through DCG FATS → ACG TS → CC T&L Release\n';
                        } else {
                            message += '• The request will be approved\n';
                            message += '• The request will be forwarded to the next step\n';
                        }
                        
                        message += '• The requester will be notified of the approval\n\n';
                        message += 'WHY:\n';
                        message += 'Approving this request allows it to proceed through the workflow. For requisitions, the amount determines the approval chain.';
                        
                        showConfirmModal('Approve Request', message, function() {
                            document.getElementById('decisionInput').value = 'APPROVED';
                            actionForm.submit();
                        }, 'success');
                    };
                }

                if (rejectBtn) {
                    rejectBtn.onclick = function() {
                        const requestId = {{ $fleetRequest->id }};
                        const roleName = '{{ $currentRole }}';
                        const message = '⚠️ WARNING: Are you sure you want to REJECT Request #' + requestId + '?\n\n' +
                            'WHAT WILL HAPPEN:\n' +
                            '• The request will be immediately REJECTED\n' +
                            '• The request status will change to REJECTED\n' +
                            '• The workflow will stop\n' +
                            '• Any reserved vehicles will be released\n' +
                            '• The requester will be notified of the rejection\n' +
                            '• The request cannot be resumed automatically\n\n' +
                            'WHY:\n' +
                            'Rejection stops the workflow immediately. The requester may need to create a new request or address the issues that led to rejection.';
                        
                        showConfirmModal('⚠️ Reject Request', message, function() {
                            document.getElementById('decisionInput').value = 'REJECTED';
                            actionForm.submit();
                        }, 'error');
                    };
                }

                if (kivBtn) {
                    kivBtn.onclick = function() {
                        const requestId = {{ $fleetRequest->id }};
                        const roleName = '{{ $currentRole }}';
                        const message = 'Are you sure you want to place Request #' + requestId + ' on KIV (Keep In View)?\n\n' +
                            'WHAT WILL HAPPEN:\n' +
                            '• The request status will change to KIV\n' +
                            '• The request will remain at your step\n' +
                            '• The request will be paused temporarily\n' +
                            '• You can resume it later when ready\n' +
                            '• The requester will be notified of the KIV status\n' +
                            '• Any reserved vehicles will remain reserved\n\n' +
                            'WHY:\n' +
                            'KIV allows you to pause the request temporarily while you gather more information, wait for conditions to change, or review additional details. The request stays with you until you take further action.';
                        
                        showConfirmModal('Place on KIV', message, function() {
                            document.getElementById('decisionInput').value = 'KIV';
                            actionForm.submit();
                        }, 'warning');
                    };
                }
            }
        });
    </script>
    @endpush
@endsection

