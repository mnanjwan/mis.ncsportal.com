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

        // Notify the user who released (e.g. CC T&L) that the command has acknowledged receipt
        if ($releasedByUserId) {
            $vehicle = $assignment->vehicle;
            $vehicleLabel = $vehicle ? (trim(($vehicle->make ?? '') . ' ' . ($vehicle->model ?? '')) ?: $vehicle->reg_no ?? $vehicle->chassis_number) : 'Vehicle';
            $releasedBy = \App\Models\User::find($releasedByUserId);
            if ($releasedBy && $releasedBy->is_active) {
                app(NotificationService::class)->notify(
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

        return redirect()
            ->back()
            ->with('success', 'Vehicle receipt acknowledged.');
    }
}

