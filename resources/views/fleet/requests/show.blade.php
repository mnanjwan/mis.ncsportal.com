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
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Request #{{ $fleetRequest->id }}</h3>
            </div>
            <div class="kt-card-content p-5 lg:p-7.5 text-sm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div><strong>Origin Command:</strong> {{ $fleetRequest->originCommand->name ?? '-' }}</div>
                    <div><strong>Status:</strong> {{ $fleetRequest->status }}</div>
                    <div><strong>Type:</strong> {{ $fleetRequest->requested_vehicle_type }}</div>
                    <div><strong>Quantity:</strong> {{ $fleetRequest->requested_quantity }}</div>
                    <div><strong>Make/Model:</strong> {{ trim(($fleetRequest->requested_make ?? '') . ' ' . ($fleetRequest->requested_model ?? '')) ?: '-' }}</div>
                    <div><strong>Year:</strong> {{ $fleetRequest->requested_year ?? '-' }}</div>
                    <div><strong>Created By:</strong> {{ $fleetRequest->createdBy->email ?? '-' }}</div>
                    <div><strong>Current Step:</strong> {{ $fleetRequest->current_step_order ?? '-' }}</div>
                </div>
            </div>
        </div>

        @if(auth()->user()->hasRole('CC T&L') && (int) $fleetRequest->current_step_order === 5)
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">CC T&L Inventory Check (Step 5)</h3>
                </div>
                <div class="kt-card-content p-5 lg:p-7.5">
                    <form method="POST" action="{{ route('fleet.requests.cc-tl.propose', $fleetRequest) }}" class="grid gap-4">
                        @csrf

                        <div>
                            <label class="text-sm font-medium">Comment (optional)</label>
                            <textarea class="kt-textarea w-full" name="comment" rows="3"></textarea>
                        </div>

                        <div class="text-sm text-secondary-foreground">
                            Select available vehicles to reserve for this request. If you select none, the request will be marked <strong>KIV</strong> and will remain at this step.
                        </div>

                        <div class="overflow-x-auto">
                            <table class="kt-table w-full">
                                <thead>
                                    <tr>
                                        <th class="text-left">Select</th>
                                        <th class="text-left">Reg No</th>
                                        <th class="text-left">Type</th>
                                        <th class="text-left">Make/Model</th>
                                        <th class="text-left">Chassis</th>
                                        <th class="text-left">Engine</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($availableVehicles as $v)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="vehicle_ids[]" value="{{ $v->id }}" />
                                            </td>
                                            <td>{{ $v->reg_no ?? '-' }}</td>
                                            <td>{{ $v->vehicle_type }}</td>
                                            <td>{{ trim(($v->make ?? '') . ' ' . ($v->model ?? '')) ?: '-' }}</td>
                                            <td>{{ $v->chassis_number }}</td>
                                            <td>{{ $v->engine_number ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-sm text-secondary-foreground">No matching IN_STOCK vehicles available right now.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <button class="kt-btn kt-btn-primary" type="submit">Save Proposal</button>
                    </form>
                </div>
            </div>
        @endif

        @if(auth()->user()->hasRole('CC T&L') && (int) $fleetRequest->current_step_order === 11)
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">CC T&L Release (Step 11)</h3>
                </div>
                <div class="kt-card-content p-5 lg:p-7.5">
                    <p class="text-sm text-secondary-foreground">
                        Reserved vehicles: <strong>{{ $fleetRequest->reservedVehicles->count() }}</strong>
                    </p>
                    <form method="POST" action="{{ route('fleet.requests.cc-tl.release', $fleetRequest) }}" class="grid gap-4 mt-4">
                        @csrf
                        <div>
                            <label class="text-sm font-medium">Comment (optional)</label>
                            <textarea class="kt-textarea w-full" name="comment" rows="3"></textarea>
                        </div>
                        <button class="kt-btn kt-btn-primary" type="submit">Release Reserved Vehicles to Command</button>
                    </form>
                </div>
            </div>
        @endif

        @php
            $currentStep = $fleetRequest->steps->firstWhere('step_order', $fleetRequest->current_step_order);
            $currentRole = $currentStep?->role_name;
            $currentAction = $currentStep?->action;
            $userRoleNames = auth()->user()->roles()->wherePivot('is_active', true)->pluck('name')->toArray();
            $canAct = $currentRole && in_array($currentRole, $userRoleNames, true);
        @endphp

        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Workflow Steps</h3>
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
                                <th class="text-left">By</th>
                                <th class="text-left">At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fleetRequest->steps as $s)
                                @php
                                    $isCurrent = (int) $fleetRequest->current_step_order === (int) $s->step_order;
                                @endphp
                                <tr class="{{ $isCurrent ? 'bg-primary/5' : '' }}">
                                    <td>{{ $s->step_order }}</td>
                                    <td>{{ $s->role_name }}</td>
                                    <td>{{ $s->action }}</td>
                                    <td>
                                        @if($s->decision)
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-muted">
                                                {{ $s->decision }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $s->actedBy->email ?? '-' }}</td>
                                    <td>{{ $s->acted_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($canAct && $currentStep)
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Action Required (Step {{ $currentStep->step_order }})</h3>
                </div>
                <div class="kt-card-content p-5 lg:p-7.5">
                    <form method="POST" action="{{ route('fleet.requests.act', $fleetRequest) }}" class="flex flex-wrap gap-2">
                        @csrf
                        <input type="hidden" name="comment" value="">
                        @if($currentAction === 'FORWARD')
                            <button class="kt-btn kt-btn-sm" name="decision" value="FORWARDED">Forward</button>
                        @elseif($currentAction === 'APPROVE')
                            <button class="kt-btn kt-btn-sm kt-btn-success" name="decision" value="APPROVED">Approve</button>
                            <button class="kt-btn kt-btn-sm kt-btn-danger" name="decision" value="REJECTED">Reject</button>
                        @elseif($currentAction === 'REVIEW')
                            <button class="kt-btn kt-btn-sm kt-btn-secondary" name="decision" value="REVIEWED">Review</button>
                        @endif
                    </form>
                    <p class="text-xs text-secondary-foreground mt-2">
                        This action aligns with the step definition. Use CC T&L panels above for inventory and release steps.
                    </p>
                </div>
            </div>
        @endif
    </div>
@endsection

