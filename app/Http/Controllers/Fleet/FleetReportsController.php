<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Models\FleetVehicle;
use App\Models\FleetVehicleAssignment;
use App\Services\Fleet\FleetWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class FleetReportsController extends Controller
{
    /**
     * Report: vehicles by type.
     * CC T&L and CGC: all commands. Unit Head (Area Controller) and CD: their command only.
     * Report states the officer allocated to each vehicle if any.
     */
    public function byTypeReport(Request $request, FleetWorkflowService $workflow)
    {
        $user = $request->user();

        $vehicleType = $request->input('vehicle_type');
        $vehicleTypes = config('fleet.vehicle_types', []);
        $vehicleTypeLabel = $vehicleType ? ($vehicleTypes[$vehicleType] ?? $vehicleType) : null;

        $vehicles = collect();
        $scopeLabel = '';

        if ($vehicleType) {
            $request->validate([
                'vehicle_type' => ['required', 'string', Rule::in(array_keys($vehicleTypes))],
            ]);

            $query = FleetVehicle::query()
                ->with(['currentCommand', 'currentOfficer'])
                ->where('vehicle_type', $vehicleType)
                ->orderBy('current_command_id')
                ->orderBy('reg_no');

            if ($user->hasRole('CC T&L') || $user->hasRole('CGC')) {
                $scopeLabel = 'All Commands';
            } elseif ($user->hasRole('Area Controller')) {
                $commandId = $workflow->getActiveCommandIdForRole($user, 'Area Controller');
                if ($commandId) {
                    $query->where('current_command_id', $commandId);
                    $command = \App\Models\Command::find($commandId);
                    $scopeLabel = $command ? $command->name : 'Command';
                } else {
                    $scopeLabel = 'My Command';
                }
            } elseif ($user->hasRole('CD')) {
                $commandId = $workflow->getActiveCommandIdForRole($user, 'CD');
                if ($commandId) {
                    $query->where('current_command_id', $commandId);
                    $command = \App\Models\Command::find($commandId);
                    $scopeLabel = $command ? $command->name : 'Command';
                } else {
                    $scopeLabel = 'My Command';
                }
            } else {
                abort(403, 'You do not have access to this report.');
            }

            $vehicles = $query->get();
        }

        return view('fleet.reports.by-type', [
            'vehicles' => $vehicles,
            'vehicleType' => $vehicleType,
            'vehicleTypeLabel' => $vehicleTypeLabel,
            'scopeLabel' => $scopeLabel,
            'vehicleTypes' => $vehicleTypes,
        ]);
    }

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
            ->whereNotNull('assigned_at')
            ->whereBetween('assigned_at', [$start, $end])
            ->with([
                'vehicle',
                'assignedToCommand',
                'assignedToOfficer',
            ])
            ->orderBy('assigned_at', 'asc');

        // Command scope for CD - include both command assignments and officer assignments
        if ($user->hasRole('CD')) {
            $commandId = $workflow->getActiveCommandIdForRole($user, 'CD');
            if ($commandId) {
                $query->where(function($q) use ($commandId) {
                    $q->where('assigned_to_command_id', $commandId)
                      ->orWhereHas('vehicle', function($vq) use ($commandId) {
                          $vq->where('current_command_id', $commandId);
                      })
                      ->orWhereHas('assignedToOfficer', function($oq) use ($commandId) {
                          $oq->where('present_station', $commandId);
                      });
                });
            }
        }

        $rows = $query->get();

        return view('fleet.reports.returns', [
            'rows' => $rows,
            'startDate' => $start->toDateString(),
            'endDate' => $end->toDateString(),
        ]);
    }

    public function serviceabilityReport(Request $request, FleetWorkflowService $workflow)
    {
        $user = $request->user();
        
        // Get month/year from request or use current month
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        
        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();

        $query = FleetVehicle::query()
            ->with(['currentCommand', 'currentOfficer', 'vehicleModel']);

        // Command scope for CD
        if ($user->hasRole('CD')) {
            $commandId = $workflow->getActiveCommandIdForRole($user, 'CD');
            if ($commandId) {
                $query->where('current_command_id', $commandId);
            }
        }

        $allVehicles = $query->get();
        
        // Count serviceable and unserviceable
        $serviceable = $allVehicles->where('service_status', 'SERVICEABLE')->count();
        $unserviceable = $allVehicles->where('service_status', 'UNSERVICEABLE')->count();
        $total = $allVehicles->count();
        
        // Get serviceable vehicles for detailed list
        $serviceableVehicles = $allVehicles
            ->where('service_status', 'SERVICEABLE')
            ->sortBy(function($vehicle) {
                if ($vehicle->vehicleModel) {
                    return $vehicle->vehicleModel->display_name;
                }
                return $vehicle->vehicle_type . ' ' . $vehicle->make . ' ' . ($vehicle->model ?? '');
            })
            ->values();

        // Get prepared by info (CD)
        $preparedBy = null;
        if ($user->hasRole('CD')) {
            $officer = $user->officer;
            if ($officer) {
                $preparedBy = [
                    'rank' => $officer->substantive_rank ?? 'CD',
                    'service_number' => $officer->service_number ?? '',
                    'name' => $officer->full_name ?? '',
                ];
            }
        }

        // Get command name for header
        $commandName = null;
        if ($user->hasRole('CD')) {
            $commandId = $workflow->getActiveCommandIdForRole($user, 'CD');
            if ($commandId) {
                $command = \App\Models\Command::find($commandId);
                $commandName = $command?->name ?? 'FOU ZONE "A", IKEJA – LAGOS';
            }
        } else {
            $commandName = 'FOU ZONE "A", IKEJA – LAGOS';
        }

        return view('fleet.reports.serviceability', [
            'serviceable' => $serviceable,
            'unserviceable' => $unserviceable,
            'total' => $total,
            'serviceableVehicles' => $serviceableVehicles,
            'month' => $month,
            'year' => $year,
            'monthName' => Carbon::create($year, $month, 1)->format('F'),
            'preparedBy' => $preparedBy,
            'commandName' => $commandName,
        ]);
    }
}

