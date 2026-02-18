<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Command;
use App\Models\FleetVehicle;
use App\Models\FleetVehicleAssignment;
use App\Services\Fleet\FleetWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class FleetReportsController extends Controller
{
    /**
     * Report by selected vehicles. User selects specific vehicles (checkboxes);
     * report shows those vehicles sectioned by vehicle type on print. Vehicle-specific.
     * CC T&L and CGC: can filter by command scope. Area Controller and CD: their command only.
     */
    public function byTypeReport(Request $request, FleetWorkflowService $workflow)
    {
        $user = $request->user();
        $vehicleTypes = config('fleet.vehicle_types', []);

        $commands = collect();
        $showCommandScope = $user->hasRole('CC T&L') || $user->hasRole('CGC');
        if ($showCommandScope) {
            $commands = Command::where('is_active', true)->orderBy('name')->get();
        }

        $scopeQuery = FleetVehicle::query()->with(['currentCommand', 'currentOfficer']);
        $scopeLabel = '';

        if ($user->hasRole('CC T&L') || $user->hasRole('CGC')) {
            $commandId = $request->input('command_id');
            if ($commandId && $commands->contains('id', (int) $commandId)) {
                $scopeQuery->where('current_command_id', (int) $commandId);
                $command = $commands->firstWhere('id', (int) $commandId);
                $scopeLabel = $command ? $command->name : 'Selected Command';
            } else {
                $scopeLabel = 'All Commands';
            }
        } elseif ($user->hasRole('Area Controller')) {
            $commandId = $workflow->getActiveCommandIdForRole($user, 'Area Controller');
            if ($commandId) {
                $scopeQuery->where('current_command_id', $commandId);
                $command = \App\Models\Command::find($commandId);
                $scopeLabel = $command ? $command->name : 'Command';
            } else {
                $scopeLabel = 'My Command';
            }
        } elseif ($user->hasRole('CD')) {
            $commandId = $workflow->getActiveCommandIdForRole($user, 'CD');
            if ($commandId) {
                $scopeQuery->where('current_command_id', $commandId);
                $command = \App\Models\Command::find($commandId);
                $scopeLabel = $command ? $command->name : 'Command';
            } else {
                $scopeLabel = 'My Command';
            }
        } else {
            abort(403, 'You do not have access to this report.');
        }

        $availableVehicles = $scopeQuery->orderBy('vehicle_type')->orderBy('reg_no')->get();
        $allowedIds = $availableVehicles->pluck('id')->all();

        $vehicleIds = $request->input('vehicle_ids', []);
        if (! is_array($vehicleIds)) {
            $vehicleIds = array_filter(explode(',', (string) $vehicleIds));
        }
        $vehicleIds = array_values(array_filter(array_map('intval', $vehicleIds)));
        $selectedVehicleIds = array_values(array_intersect($vehicleIds, $allowedIds));

        if ($request->hasAny(['vehicle_ids', 'command_id']) && empty($selectedVehicleIds)) {
            validator(
                ['vehicle_ids' => $request->input('vehicle_ids')],
                ['vehicle_ids' => ['required']],
                ['vehicle_ids.required' => 'Select at least one vehicle.']
            )->validate();
        }

        $vehiclesGroupedByType = collect();
        if (! empty($selectedVehicleIds)) {
            $selected = FleetVehicle::query()
                ->with(['currentCommand', 'currentOfficer'])
                ->whereIn('id', $selectedVehicleIds)
                ->orderBy('vehicle_type')
                ->orderBy('reg_no')
                ->get();
            $vehiclesGroupedByType = $selected->groupBy('vehicle_type');
        }

        $commandOptionsForJs = array_merge(
            [['id' => '', 'name' => 'All Commands']],
            $commands->map(fn ($c) => ['id' => (string) $c->id, 'name' => $c->name])->all()
        );

        $vehiclesForJs = $availableVehicles->map(fn ($v) => [
            'id' => $v->id,
            'label' => ($v->reg_no ?? $v->chassis_number) . ' — ' . trim(($v->make ?? '') . ' ' . ($v->model ?? '')) . ' (' . ($vehicleTypes[$v->vehicle_type] ?? $v->vehicle_type) . ')',
        ])->values()->all();

        if ($request->boolean('print') && $vehiclesGroupedByType->isNotEmpty()) {
            return view('fleet.reports.by-type-print', [
                'vehiclesGroupedByType' => $vehiclesGroupedByType,
                'vehicleTypes' => $vehicleTypes,
                'scopeLabel' => $scopeLabel,
            ]);
        }

        return view('fleet.reports.by-type', [
            'vehiclesGroupedByType' => $vehiclesGroupedByType,
            'vehicleTypes' => $vehicleTypes,
            'scopeLabel' => $scopeLabel,
            'commands' => $commands,
            'showCommandScope' => $showCommandScope,
            'commandOptionsForJs' => $commandOptionsForJs,
            'vehiclesForJs' => $vehiclesForJs,
            'availableVehicles' => $availableVehicles,
            'selectedVehicleIds' => $selectedVehicleIds,
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

