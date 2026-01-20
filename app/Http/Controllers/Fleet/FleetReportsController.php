<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Models\FleetVehicleAssignment;
use App\Services\Fleet\FleetWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FleetReportsController extends Controller
{
    public function returnsReport(Request $request, FleetWorkflowService $workflow)
    {
        $user = $request->user();

        $start = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : now()->subDays(30)->startOfDay();
        $end = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : now()->endOfDay();

        $query = FleetVehicleAssignment::query()
            ->whereNotNull('assigned_to_command_id')
            ->whereNotNull('assigned_at')
            ->whereBetween('assigned_at', [$start, $end])
            ->with([
                'vehicle',
                'assignedToCommand',
            ])
            ->orderBy('assigned_at', 'asc');

        // Command scope for CD
        if ($user->hasRole('CD')) {
            $commandId = $workflow->getActiveCommandIdForRole($user, 'CD');
            if ($commandId) {
                $query->where('assigned_to_command_id', $commandId);
            }
        }

        $rows = $query->get();

        return view('fleet.reports.returns', [
            'rows' => $rows,
            'startDate' => $start->toDateString(),
            'endDate' => $end->toDateString(),
        ]);
    }
}

