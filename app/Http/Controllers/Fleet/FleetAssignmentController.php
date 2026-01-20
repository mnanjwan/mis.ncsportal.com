<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Models\FleetVehicleAssignment;
use App\Services\Fleet\FleetWorkflowService;
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

        DB::transaction(function () use ($assignment, $request) {
            $assignment->update([
                'received_by_user_id' => $request->user()->id,
                'received_at' => now(),
            ]);
        });

        return redirect()
            ->back()
            ->with('success', 'Vehicle receipt acknowledged.');
    }
}

