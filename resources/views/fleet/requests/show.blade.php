@extends('layouts.app')

@section('title', 'Fleet Request')
@section('page-title', 'Fleet Request')

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
                                <tr>
                                    <td>{{ $s->step_order }}</td>
                                    <td>{{ $s->role_name }}</td>
                                    <td>{{ $s->action }}</td>
                                    <td>{{ $s->decision ?? '-' }}</td>
                                    <td>{{ $s->actedBy->email ?? '-' }}</td>
                                    <td>{{ $s->acted_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Quick Action</h3>
            </div>
            <div class="kt-card-content p-5 lg:p-7.5">
                <form method="POST" action="{{ route('fleet.requests.act', $fleetRequest) }}" class="flex flex-wrap gap-2">
                    @csrf
                    <input type="hidden" name="comment" value="">
                    <button class="kt-btn kt-btn-sm" name="decision" value="FORWARDED">Forward</button>
                    <button class="kt-btn kt-btn-sm kt-btn-success" name="decision" value="APPROVED">Approve</button>
                    <button class="kt-btn kt-btn-sm kt-btn-danger" name="decision" value="REJECTED">Reject</button>
                    <button class="kt-btn kt-btn-sm kt-btn-secondary" name="decision" value="REVIEWED">Review</button>
                </form>
                <p class="text-xs text-secondary-foreground mt-2">
                    Note: buttons are role-validated server-side. Use the CC T&L panels above for inventory check/release steps.
                </p>
            </div>
        </div>
    </div>
@endsection

