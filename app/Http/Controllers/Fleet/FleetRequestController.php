<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Models\FleetRequest;
use App\Models\FleetVehicle;
use App\Services\Fleet\FleetWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FleetRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Requests created by the user (CD)
        $myRequests = FleetRequest::query()
            ->where('created_by', $user->id)
            ->with(['originCommand', 'steps'])
            ->latest()
            ->take(50)
            ->get();

        // Inbox: requests where the current step role matches any of the user roles
        $userRoleNames = $user->roles()
            ->wherePivot('is_active', true)
            ->pluck('name')
            ->toArray();

        $inbox = FleetRequest::query()
            ->whereNotNull('current_step_order')
            ->whereHas('steps', function ($q) use ($userRoleNames) {
                $q->whereColumn('fleet_request_steps.step_order', 'fleet_requests.current_step_order')
                    ->whereIn('fleet_request_steps.role_name', $userRoleNames);
            })
            ->with(['originCommand', 'createdBy', 'steps'])
            ->orderByDesc('updated_at')
            ->take(50)
            ->get();

        return view('fleet.requests.index', compact('myRequests', 'inbox'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $roles = ['CD', 'Area Controller', 'OC Workshop', 'Staff Officer T&L', 'CC T&L'];
        $hasRole = false;
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                $hasRole = true;
                break;
            }
        }
        abort_unless($hasRole, 403);

        $vehicles = FleetVehicle::all(); // For re-allocation/repair etc.

        return view('fleet.requests.create', compact('vehicles'));
    }

    public function show(Request $request, FleetRequest $fleetRequest)
    {
        $fleetRequest->load([
            'originCommand',
            'createdBy',
            'steps.actedBy',
            'fulfillment',
            'reservedVehicles',
            'vehicle',
        ]);

        $availableVehicles = collect();
        // Check if current step role matches user and if it's a CC T&L proposal step
        $userRoleNames = $request->user()->roles()->wherePivot('is_active', true)->pluck('name')->toArray();
        $currentStep = $fleetRequest->steps()->where('step_order', $fleetRequest->current_step_order)->first();

        if ($currentStep && $currentStep->role_name === 'CC T&L' && $currentStep->action === 'REVIEW' && $fleetRequest->request_type === 'FLEET_NEW_VEHICLE') {
            $availableVehicles = FleetVehicle::query()
                ->with('vehicleModel')
                ->where('lifecycle_status', 'IN_STOCK')
                ->whereNull('reserved_fleet_request_id')
                ->when($fleetRequest->requested_vehicle_type, function($q) use ($fleetRequest) {
                    $q->where(function($query) use ($fleetRequest) {
                        $query->where('vehicle_type', $fleetRequest->requested_vehicle_type)
                              ->orWhereHas('vehicleModel', function($vm) use ($fleetRequest) {
                                  $vm->where('vehicle_type', $fleetRequest->requested_vehicle_type);
                              });
                    });
                })
                ->when($fleetRequest->requested_make, function($q) use ($fleetRequest) {
                    $q->where(function($query) use ($fleetRequest) {
                        $query->where('make', $fleetRequest->requested_make)
                              ->orWhereHas('vehicleModel', function($vm) use ($fleetRequest) {
                                  $vm->where('make', $fleetRequest->requested_make);
                              });
                    });
                })
                ->when($fleetRequest->requested_year, function($q) use ($fleetRequest) {
                    $q->where(function($query) use ($fleetRequest) {
                        $query->where('year_of_manufacture', $fleetRequest->requested_year)
                              ->orWhereHas('vehicleModel', function($vm) use ($fleetRequest) {
                                  $vm->where('year_of_manufacture', $fleetRequest->requested_year);
                              });
                    });
                })
                ->orderBy('make')
                ->orderBy('vehicle_type')
                ->take(200)
                ->get();
        }

        return view('fleet.requests.show', compact('fleetRequest', 'availableVehicles'));
    }

    public function store(Request $request, FleetWorkflowService $service)
    {
        $data = $request->validate([
            'request_type' => [
                'required',
                'string',
                Rule::in([
                    'FLEET_NEW_VEHICLE',
                    'FLEET_RE_ALLOCATION',
                    'FLEET_OPE',
                    'FLEET_REPAIR',
                    'FLEET_USE',
                    'FLEET_REQUISITION'
                ])
            ],
            'requested_vehicle_type' => ['nullable', 'string', Rule::in(['SALOON', 'SUV', 'BUS'])],
            'requested_make' => ['nullable', 'string', 'max:100'],
            'requested_model' => ['nullable', 'string', 'max:100'],
            'requested_year' => ['nullable', 'integer', 'min:1950', 'max:' . (int) date('Y')],
            'requested_quantity' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'fleet_vehicle_id' => ['nullable', 'exists:fleet_vehicles,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        if ($request->hasFile('document')) {
            $data['document_path'] = $request->file('document')->store('fleet/requests', 'public');
        }

        $fleetRequest = $service->createRequest($request->user(), $data);

        return redirect()
            ->route('fleet.requests.index')
            ->with('success', "Request #{$fleetRequest->id} created as DRAFT.");
    }

    public function submit(Request $request, FleetRequest $fleetRequest, FleetWorkflowService $service)
    {
        // Permission check: only creator can submit
        abort_unless((int) $fleetRequest->created_by === (int) $request->user()->id, 403);

        $service->submit($fleetRequest, $request->user());

        return redirect()
            ->route('fleet.requests.index')
            ->with('success', "Request #{$fleetRequest->id} submitted into workflow.");
    }

    public function act(Request $request, FleetRequest $fleetRequest, FleetWorkflowService $service)
    {
        $data = $request->validate([
            'decision' => ['required', 'string', Rule::in(['FORWARDED', 'APPROVED', 'REJECTED', 'REVIEWED', 'KIV'])],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $service->act($fleetRequest, $request->user(), $data['decision'], $data['comment'] ?? null);

        return redirect()
            ->route('fleet.requests.index')
            ->with('success', "Action recorded for Request #{$fleetRequest->id}.");
    }

    public function ccTlPropose(Request $request, FleetRequest $fleetRequest, FleetWorkflowService $service)
    {
        abort_unless($request->user()->hasRole('CC T&L'), 403);

        $data = $request->validate([
            'vehicle_ids' => ['array'],
            'vehicle_ids.*' => ['integer'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $service->ccTlPropose($fleetRequest, $request->user(), $data['vehicle_ids'] ?? [], $data['comment'] ?? null);

        return redirect()
            ->route('fleet.requests.show', $fleetRequest)
            ->with('success', "CC T&L proposal updated for Request #{$fleetRequest->id}.");
    }

    public function ccTlRelease(Request $request, FleetRequest $fleetRequest, FleetWorkflowService $service)
    {
        abort_unless($request->user()->hasRole('CC T&L'), 403);

        $data = $request->validate([
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $service->ccTlRelease($fleetRequest, $request->user(), $data['comment'] ?? null);

        return redirect()
            ->route('fleet.requests.show', $fleetRequest)
            ->with('success', "Vehicles released for Request #{$fleetRequest->id}.");
    }
}

