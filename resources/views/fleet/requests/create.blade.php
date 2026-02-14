@extends('layouts.app')

@section('title', 'New Fleet Request')
@section('page-title', 'New Fleet Request')

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
        }
    @endphp
    @if($dashboardRoute)
        <a class="text-secondary-foreground hover:text-primary" href="{{ $dashboardRoute }}">Fleet</a>
        <span>/</span>
    @endif
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('fleet.requests.index') }}">Requests</a>
    <span>/</span>
    <span class="text-primary">Create</span>
@endsection

@section('content')
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">New Transport & Logistics Request</h3>
        </div>
        <div class="kt-card-content p-5 lg:p-7.5">
            @if($errors->any())
                <div class="mb-4 p-4 rounded-lg bg-danger/10 border border-danger/20 text-danger">
                    <p class="font-medium">The request could not be saved. Please fix the following:</p>
                    <ul class="list-disc list-inside mt-2 text-sm">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form method="POST" action="{{ route('fleet.requests.store') }}" enctype="multipart/form-data"
                class="grid gap-4 max-w-2xl">
                @csrf

                <div>
                    <label class="text-sm font-medium">Request Type</label>
                    <select name="request_type" id="request_type" class="kt-select w-full" required
                        onchange="toggleFields()">
                        <option value="">Select request type</option>
                        <option value="FLEET_NEW_VEHICLE" @selected(old('request_type') === 'FLEET_NEW_VEHICLE')>New Vehicle
                            Request</option>
                        <option value="FLEET_RE_ALLOCATION" @selected(old('request_type') === 'FLEET_RE_ALLOCATION')>
                            Re-Allocation</option>
                        <option value="FLEET_OPE" @selected(old('request_type') === 'FLEET_OPE')>OPE Request (Out of Pocket)
                        </option>
                        <option value="FLEET_REPAIR" @selected(old('request_type') === 'FLEET_REPAIR')>Repair Request</option>
                        <option value="FLEET_USE" @selected(old('request_type') === 'FLEET_USE')>Request for Use of Vehicle
                        </option>
                        <option value="FLEET_REQUISITION" @selected(old('request_type') === 'FLEET_REQUISITION')>Maintenance
                            Requisition</option>
                    </select>
                    @error('request_type')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div id="vehicle_specs" class="grid grid-cols-1 md:grid-cols-2 gap-4 hidden">
                    <div>
                        <label class="text-sm font-medium">Vehicle Type</label>
                        <select name="requested_vehicle_type" class="kt-select w-full">
                            <option value="">Select type</option>
                            <option value="SALOON" @selected(old('requested_vehicle_type') === 'SALOON')>Saloon</option>
                            <option value="SUV" @selected(old('requested_vehicle_type') === 'SUV')>SUV</option>
                            <option value="BUS" @selected(old('requested_vehicle_type') === 'BUS')>Bus</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium">Quantity</label>
                        <input type="number" name="requested_quantity" class="kt-input w-full" min="1"
                            value="{{ old('requested_quantity', 1) }}" />
                    </div>
                </div>

                <div id="vehicle_select" class="hidden">
                    <label class="text-sm font-medium">Select Vehicle</label>
                    <select name="fleet_vehicle_id" class="kt-select w-full">
                        <option value="">Select vehicle</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" @selected(old('fleet_vehicle_id') == $vehicle->id)>
                                {{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->reg_no }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="amount_field" class="hidden">
                    <label class="text-sm font-medium">Estimated Amount (if applicable)</label>
                    <input type="number" name="amount" class="kt-input w-full" step="0.01" value="{{ old('amount') }}" />
                </div>

                <div>
                    <label class="text-sm font-medium">Notes / Description</label>
                    <textarea name="notes" class="kt-input w-full h-24">{{ old('notes') }}</textarea>
                </div>

                <div>
                    <label class="text-sm font-medium">Supporting Document (Upload Bill/Requisition/Receipt)</label>
                    <input type="file" name="document" class="kt-input w-full" />
                    <p class="text-xs text-gray-500 mt-1">PDF, JPG, PNG (Max 5MB)</p>
                </div>

                <div class="flex gap-3 mt-4">
                    <button class="kt-btn kt-btn-primary" type="submit" id="saveDraftBtn">Save Draft</button>
                    <a class="kt-btn kt-btn-secondary" href="{{ route('fleet.requests.index') }}">Back</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleFields() {
            const type = document.getElementById('request_type').value;
            const specs = document.getElementById('vehicle_specs');
            const select = document.getElementById('vehicle_select');
            const amount = document.getElementById('amount_field');

            specs.classList.add('hidden');
            select.classList.add('hidden');
            amount.classList.add('hidden');

            if (type === 'FLEET_NEW_VEHICLE') {
                specs.classList.remove('hidden');
            } else if (type === 'FLEET_REQUISITION' || type === 'FLEET_OPE') {
                amount.classList.remove('hidden');
                select.classList.remove('hidden');
            } else if (type === 'FLEET_RE_ALLOCATION' || type === 'FLEET_REPAIR' || type === 'FLEET_USE') {
                select.classList.remove('hidden');
            }
        }
        document.addEventListener('DOMContentLoaded', toggleFields);
    </script>

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
            const saveDraftBtn = document.getElementById('saveDraftBtn');
            const createForm = saveDraftBtn ? saveDraftBtn.closest('form') : null;
            if (!createForm) return;

            function doSubmit() {
                try {
                    createForm.submit();
                } catch (err) {
                    createForm.submit();
                }
            }

            createForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const requestTypeEl = document.getElementById('request_type');
                const requestType = requestTypeEl ? requestTypeEl.value : '';

                if (!requestType) {
                    try {
                        showConfirmModal(
                            'Request Type Required',
                            'Please select a request type first.',
                            function() {},
                            'error'
                        );
                    } catch (modalErr) {
                        return;
                    }
                    return;
                }

                try {
                    showConfirmModal(
                        'Save as Draft',
                        'Are you sure you want to save this request as DRAFT?\n\n' +
                        'WHAT WILL HAPPEN:\n' +
                        '• The request will be saved but NOT submitted\n' +
                        '• You can edit it later from "My Requests"\n' +
                        '• The request will remain in DRAFT status\n' +
                        '• No notifications will be sent\n' +
                        '• You can submit it when ready\n\n' +
                        'WHY:\n' +
                        'Saving as draft allows you to complete the request details later. You must submit the request to start the approval workflow.',
                        doSubmit,
                        'warning'
                    );
                } catch (modalErr) {
                    doSubmit();
                }
            });
        });
    </script>
    @endpush
@endsection