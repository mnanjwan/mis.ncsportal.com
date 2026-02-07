@extends('layouts.app')

@section('title', 'Fleet Requests')
@section('page-title', 'Fleet Requests')

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
    <span class="text-primary">List</span>
@endsection

@section('content')
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-header flex items-center justify-between">
                <h3 class="kt-card-title">Inbox</h3>
                @php
                    $user = auth()->user();
                    $creatorRoles = ['CD', 'Area Controller', 'OC Workshop', 'Staff Officer T&L', 'CC T&L'];
                    $canCreate = false;
                    foreach($creatorRoles as $r) { if($user->hasRole($r)) { $canCreate = true; break; } }
                @endphp
                @if($canCreate)
                    <a class="kt-btn kt-btn-primary" href="{{ route('fleet.requests.create') }}">
                        <i class="ki-filled ki-plus"></i>
                        New Request
                    </a>
                @endif
            </div>
            <div class="kt-card-content p-5 lg:p-7.5">
                @if($inbox->isEmpty())
                    <p class="text-sm text-secondary-foreground">No pending requests awaiting your action.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr>
                                    <th class="text-left">ID</th>
                                    <th class="text-left">Origin</th>
                                    <th class="text-left">Request Type</th>
                                    <th class="text-left">Details</th>
                                    <th class="text-left">Status</th>
                                    <th class="text-left">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($inbox as $req)
                                    <tr>
                                        <td>#{{ $req->id }}</td>
                                        <td>{{ $req->originCommand->name ?? 'N/A' }}</td>
                                        <td><span class="text-xs font-semibold uppercase">{{ str_replace('_', ' ', $req->request_type) }}</span></td>
                                        <td>
                                            @if($req->request_type === 'FLEET_NEW_VEHICLE')
                                                {{ $req->requested_quantity }}x {{ $req->requested_vehicle_type }}
                                            @elseif($req->amount)
                                                ₦{{ number_format($req->amount, 2) }}
                                            @elseif($req->fleet_vehicle_id)
                                                {{ $req->vehicle->reg_no ?? '-' }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td><span class="px-2 py-0.5 rounded-full bg-primary/10 text-primary text-xs">{{ $req->status }}</span></td>
                                        <td>
                                            <a class="kt-btn kt-btn-sm" href="{{ route('fleet.requests.show', $req) }}">Open</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">My Requests</h3>
            </div>
            <div class="kt-card-content p-5 lg:p-7.5">
                @if($myRequests->isEmpty())
                    <p class="text-sm text-secondary-foreground">You have not created any requests yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr>
                                    <th class="text-left">ID</th>
                                    <th class="text-left">Request Type</th>
                                    <th class="text-left">Details</th>
                                    <th class="text-left">Status</th>
                                    <th class="text-left">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($myRequests as $req)
                                    <tr>
                                        <td>#{{ $req->id }}</td>
                                        <td><span class="text-xs font-semibold uppercase">{{ str_replace('_', ' ', $req->request_type) }}</span></td>
                                        <td>
                                            @if($req->request_type === 'FLEET_NEW_VEHICLE')
                                                {{ $req->requested_quantity }}x {{ $req->requested_vehicle_type }}
                                            @elseif($req->amount)
                                                ₦{{ number_format($req->amount, 2) }}
                                            @elseif($req->fleet_vehicle_id)
                                                {{ $req->vehicle->reg_no ?? '-' }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td><span class="px-2 py-0.5 rounded-full bg-muted text-xs">{{ $req->status }}</span></td>
                                        <td>
                                            @if($req->status === 'DRAFT')
                                                <form method="POST" action="{{ route('fleet.requests.submit', $req) }}" class="inline submit-request-form" data-request-id="{{ $req->id }}" data-request-type="{{ str_replace('_', ' ', $req->request_type) }}">
                                                    @csrf
                                                    <button class="kt-btn kt-btn-sm kt-btn-primary" type="submit">Submit</button>
                                                </form>
                                            @endif
                                            <a class="kt-btn kt-btn-sm" href="{{ route('fleet.requests.show', $req) }}">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
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

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.submit-request-form').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const requestId = form.getAttribute('data-request-id');
                    const requestType = form.getAttribute('data-request-type');

                    showConfirmModal(
                        'Submit Request',
                        'Are you sure you want to submit Request #' + requestId + ' (' + requestType + ')?\n\n' +
                        'WHAT WILL HAPPEN:\n' +
                        '• This request will be submitted into the workflow\n' +
                        '• It will be sent to the next approver in the chain\n' +
                        '• You will no longer be able to edit the request\n' +
                        '• Notifications will be sent to the next step approver\n\n' +
                        'WHY:\n' +
                        'Submitting the request initiates the approval process. Once submitted, the request moves through the workflow based on its type and amount (for requisitions).',
                        function() {
                            form.submit();
                        },
                        'warning'
                    );
                });
            });
        });
    </script>
    @endpush
@endsection

