<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Models\FleetVehicleAssignment;
use App\Services\Fleet\FleetWorkflowService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FleetAssignmentController extends Controller
{
    /**
     * Area Controller acknowledges receipt of released vehicles for their command.
     */
    public function receive(Request $request, FleetVehicleAssignment $assignment, FleetWorkflowService $workflow)
    {
        abort_unless($request->user()->hasRole('Area Controller'), 403);

        $commandId = $workflow->getActiveCommandIdForRole($request->user(), 'Area Controller');
        if (!$commandId || (int) $assignment->assigned_to_command_id !== (int) $commandId) {
            abort(403);
        }

        $releasedByUserId = $assignment->released_by_user_id;

        DB::transaction(function () use ($assignment, $request) {
            $assignment->update([
                'received_by_user_id' => $request->user()->id,
                'received_at' => now(),
            ]);
        });

        $vehicle = $assignment->vehicle;
        $vehicleLabel = $vehicle ? (trim(($vehicle->make ?? '') . ' ' . ($vehicle->model ?? '')) ?: $vehicle->reg_no ?? $vehicle->chassis_number) : 'Vehicle';
        $notificationService = app(NotificationService::class);

        // Notify the user who released (e.g. CC T&L) that the command has acknowledged receipt
        if ($releasedByUserId) {
            $releasedBy = \App\Models\User::find($releasedByUserId);
            if ($releasedBy && $releasedBy->is_active) {
                $notificationService->notify(
                    $releasedBy,
                    'fleet_vehicle_received_by_command',
                    'Vehicle received by command',
                    "{$vehicleLabel} has been received and acknowledged by the command (Area Controller).",
                    'fleet_vehicle',
                    $vehicle?->id,
                    true
                );
            }
        }

        // Notify CD(s) for this command that the vehicle is received and available to issue to officers
        $commandId = (int) $assignment->assigned_to_command_id;
        /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $cdUsers */
        $cdUsers = \App\Models\User::whereHas('roles', function ($q) use ($commandId) {
            $q->where('name', 'CD')
                ->where('user_roles.is_active', true)
                ->where('user_roles.command_id', $commandId);
        })->where('is_active', true)->get();

        foreach ($cdUsers as $cdUser) {
            $notificationService->notify(
                $cdUser,
                'fleet_vehicle_received_by_command',
                'Vehicle received – ready to issue',
                "{$vehicleLabel} has been received by the command and is in the pool. You can issue it to an officer from the Vehicles list.",
                'fleet_vehicle',
                $vehicle?->id,
                true
            );
        }

        return redirect()
            ->back()
            ->with('success', 'Vehicle receipt acknowledged.');
    }
}

