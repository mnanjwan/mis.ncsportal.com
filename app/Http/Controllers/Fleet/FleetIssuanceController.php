<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Models\FleetVehicle;
use App\Models\FleetVehicleAssignment;
use App\Models\FleetVehicleReturn;
use App\Models\Officer;
use App\Models\User;
use App\Services\Fleet\FleetWorkflowService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FleetIssuanceController extends Controller
{
    public function createIssue(Request $request, FleetVehicle $vehicle, FleetWorkflowService $workflow)
    {
        abort_unless($request->user()->hasRole('CD'), 403);

        $commandId = $workflow->getActiveCommandIdForRole($request->user(), 'CD');
        abort_unless($commandId && (int) $vehicle->current_command_id === (int) $commandId, 403);

        // Only issue vehicles that are in the command pool and not reserved
        abort_unless($vehicle->lifecycle_status === 'AT_COMMAND_POOL' && !$vehicle->reserved_fleet_request_id, 422);

        $officers = Officer::query()
            ->where('present_station', $commandId)
            ->orderBy('surname')
            ->take(500)
            ->get();

        return view('fleet.vehicles.issue', compact('vehicle', 'officers'));
    }

    public function storeIssue(Request $request, FleetVehicle $vehicle, FleetWorkflowService $workflow)
    {
        abort_unless($request->user()->hasRole('CD'), 403);

        $commandId = $workflow->getActiveCommandIdForRole($request->user(), 'CD');
        abort_unless($commandId && (int) $vehicle->current_command_id === (int) $commandId, 403);

        $data = $request->validate([
            'officer_id' => ['required', 'integer', 'exists:officers,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $officer = Officer::findOrFail($data['officer_id']);
        abort_unless((int) $officer->present_station === (int) $commandId, 422);

        DB::transaction(function () use ($vehicle, $officer, $request, $data) {
            FleetVehicleAssignment::create([
                'fleet_vehicle_id' => $vehicle->id,
                'assigned_to_officer_id' => $officer->id,
                'assigned_by_user_id' => $request->user()->id,
                'assigned_at' => now(),
                'notes' => $data['notes'] ?? null,
            ]);

            $vehicle->update([
                'current_officer_id' => $officer->id,
                'lifecycle_status' => 'IN_OFFICER_CUSTODY',
            ]);
        });

        $notificationService = app(NotificationService::class);
        $notificationService->notify(
            $request->user(),
            'fleet_vehicle_issued',
            'Vehicle issued',
            "Vehicle {$vehicle->make} {$vehicle->model} issued to {$officer->surname}.",
            'fleet_vehicle',
            $vehicle->id,
            true
        );
        $this->notifyCd($commandId, 'Vehicle issued', "Vehicle {$vehicle->make} {$vehicle->model} issued to {$officer->surname}.");

        return redirect()
            ->route('fleet.vehicles.show', $vehicle)
            ->with('success', 'Vehicle issued to officer.');
    }

    public function returnFromOfficer(Request $request, FleetVehicle $vehicle, FleetWorkflowService $workflow)
    {
        // Allow officer to return their vehicle OR CD to process return
        $user = $request->user();
        $officer = $user->officer;

        $isOfficerReturningOwn = $user->hasRole('Officer') && $officer && (int) $vehicle->current_officer_id === (int) $officer->id;
        $isCd = $user->hasRole('CD');

        if (!$isOfficerReturningOwn && !$isCd) {
            abort(403);
        }

        DB::transaction(function () use ($vehicle, $user, $officer, $workflow) {
            $activeAssignment = FleetVehicleAssignment::query()
                ->where('fleet_vehicle_id', $vehicle->id)
                ->whereNotNull('assigned_to_officer_id')
                ->active()
                ->orderByDesc('assigned_at')
                ->lockForUpdate()
                ->first();

            if (!$activeAssignment) {
                abort(422);
            }

            $returnedByOfficerId = $officer?->id;
            if ($user->hasRole('CD') && $vehicle->current_officer_id) {
                $returnedByOfficerId = $vehicle->current_officer_id;
            }

            FleetVehicleReturn::create([
                'fleet_vehicle_assignment_id' => $activeAssignment->id,
                'returned_by_officer_id' => $returnedByOfficerId,
                'received_by_user_id' => $user->id,
                'returned_at' => now(),
            ]);

            $activeAssignment->update([
                'ended_at' => now(),
                'end_reason' => 'RETURNED',
            ]);

            // Return vehicle back to command pool
            $vehicle->update([
                'current_officer_id' => null,
                'lifecycle_status' => 'AT_COMMAND_POOL',
            ]);
        });

        $notificationService = app(NotificationService::class);
        $notificationService->notify(
            $user,
            'fleet_vehicle_returned',
            'Vehicle returned',
            "Vehicle {$vehicle->make} {$vehicle->model} returned to command pool.",
            'fleet_vehicle',
            $vehicle->id,
            true
        );
        $commandId = $vehicle->current_command_id;
        if ($commandId) {
            $this->notifyCd($commandId, 'Vehicle returned', "Vehicle {$vehicle->make} {$vehicle->model} returned to command pool.");
        }

        return redirect()
            ->route('fleet.vehicles.show', $vehicle)
            ->with('success', 'Vehicle returned to command pool.');
    }

    private function notifyCd(int $commandId, string $title, string $message): void
    {
        $notificationService = app(NotificationService::class);
        $cdUsers = User::whereHas('roles', function ($q) use ($commandId) {
            $q->where('name', 'CD')
                ->where('user_roles.is_active', true)
                ->where('user_roles.command_id', $commandId);
        })->where('is_active', true)->get();

        foreach ($cdUsers as $cd) {
            $notificationService->notify(
                $cd,
                'fleet_vehicle_update',
                $title,
                $message,
                'fleet_vehicle',
                null,
                true
            );
        }
    }
}

