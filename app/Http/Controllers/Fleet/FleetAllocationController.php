<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Command;
use App\Models\FleetVehicle;
use App\Models\FleetVehicleAssignment;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FleetAllocationController extends Controller
{
    /**
     * CC T&L: Show form to allocate a vehicle directly to a command.
     * The command will then receive it (Area Controller acknowledges receipt).
     */
    public function create(Request $request)
    {
        $this->authorizeRole('CC T&L');

        $vehicles = FleetVehicle::query()
            ->where('lifecycle_status', 'IN_STOCK')
            ->whereNull('reserved_fleet_request_id')
            ->with(['currentCommand', 'currentOfficer', 'vehicleModel'])
            ->orderBy('reg_no')
            ->get();

        $commands = Command::where('is_active', true)->orderBy('name')->get();

        return view('fleet.allocate-to-command', compact('vehicles', 'commands'));
    }

    /**
     * CC T&L: Allocate the selected vehicle to the selected command.
     * Creates an assignment (released to command); Area Controller acknowledges receipt later.
     */
    public function store(Request $request)
    {
        $this->authorizeRole('CC T&L');

        $validated = $request->validate([
            'fleet_vehicle_id' => 'required|exists:fleet_vehicles,id',
            'command_id' => 'required|exists:commands,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $vehicle = FleetVehicle::findOrFail($validated['fleet_vehicle_id']);

        if ($vehicle->lifecycle_status !== 'IN_STOCK') {
            throw ValidationException::withMessages([
                'fleet_vehicle_id' => 'Vehicle must be in stock (IN_STOCK) to allocate directly.',
            ]);
        }
        if ($vehicle->reserved_fleet_request_id !== null) {
            throw ValidationException::withMessages([
                'fleet_vehicle_id' => 'Vehicle is reserved for a request and cannot be allocated directly.',
            ]);
        }

        DB::transaction(function () use ($vehicle, $validated, $request) {
            FleetVehicleAssignment::create([
                'fleet_vehicle_id' => $vehicle->id,
                'assigned_to_command_id' => $validated['command_id'],
                'assigned_to_officer_id' => null,
                'assigned_by_user_id' => $request->user()->id,
                'assigned_at' => now(),
                'released_by_user_id' => $request->user()->id,
                'released_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ]);

            $vehicle->update([
                'current_command_id' => $validated['command_id'],
                'current_officer_id' => null,
                'lifecycle_status' => 'AT_COMMAND_POOL',
            ]);
        });

        // Notify Area Controller(s) for the command that a vehicle is allocated and pending receipt
        $commandId = (int) $validated['command_id'];
        $areaControllers = User::whereHas('roles', function ($q) use ($commandId) {
            $q->where('name', 'Area Controller')
                ->where('user_roles.is_active', true)
                ->where('user_roles.command_id', $commandId);
        })->where('is_active', true)->get();

        $vehicleLabel = trim(($vehicle->make ?? '') . ' ' . ($vehicle->model ?? '')) ?: $vehicle->reg_no ?? $vehicle->chassis_number;
        $notificationService = app(NotificationService::class);
        foreach ($areaControllers as $user) {
            $notificationService->notify(
                $user,
                'fleet_vehicle_allocated_to_command',
                'Vehicle allocated to your command',
                "A vehicle ({$vehicleLabel}) has been allocated to your command and is pending receipt. Acknowledge receipt on the vehicle page.",
                'fleet_vehicle',
                $vehicle->id,
                true
            );
        }

        return redirect()
            ->route('fleet.vehicles.show', $vehicle)
            ->with('success', 'Vehicle allocated to command. The command (Area Controller) can acknowledge receipt on the vehicle page.');
    }
}
