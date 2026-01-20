<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Models\FleetVehicle;
use App\Models\FleetVehicleAudit;
use App\Models\FleetVehicleReceipt;
use App\Models\User;
use App\Services\Fleet\FleetWorkflowService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FleetVehicleController extends Controller
{
    public function index(Request $request, FleetWorkflowService $workflow)
    {
        $user = $request->user();

        $query = FleetVehicle::query()->with(['currentCommand', 'currentOfficer']);

        // Command-scoped view for CD / O/C T&L / Store Receiver (HQ roles see all)
        if ($user->hasRole('CD')) {
            $commandId = $workflow->getActiveCommandIdForRole($user, 'CD');
            if ($commandId) {
                $query->where('current_command_id', $commandId);
            }
        } elseif ($user->hasRole('O/C T&L')) {
            $commandId = $workflow->getActiveCommandIdForRole($user, 'O/C T&L');
            if ($commandId) {
                $query->where('current_command_id', $commandId);
            }
        } elseif ($user->hasRole('Transport Store/Receiver')) {
            $commandId = $workflow->getActiveCommandIdForRole($user, 'Transport Store/Receiver');
            if ($commandId) {
                $query->where('current_command_id', $commandId);
            }
        }

        $vehicles = $query->orderByDesc('updated_at')->take(200)->get();

        return view('fleet.vehicles.index', compact('vehicles'));
    }

    public function show(FleetVehicle $vehicle)
    {
        $vehicle->load([
            'currentCommand',
            'currentOfficer',
            'audits.changedBy',
            'assignments.assignedToCommand',
            'assignments.assignedToOfficer',
            'assignments.assignedBy',
        ]);

        return view('fleet.vehicles.show', compact('vehicle'));
    }

    public function createIntake(Request $request)
    {
        abort_unless($request->user()->hasRole('Transport Store/Receiver'), 403);

        return view('fleet.vehicles.intake');
    }

    public function storeIntake(
        Request $request,
        FleetWorkflowService $workflow,
        NotificationService $notifications
    ) {
        abort_unless($request->user()->hasRole('Transport Store/Receiver'), 403);

        $data = $request->validate([
            'make' => ['required', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'year_of_manufacture' => ['nullable', 'integer', 'min:1950', 'max:' . (int) date('Y')],
            'vehicle_type' => ['required', 'string', 'in:SALOON,SUV,BUS'],
            'reg_no' => ['nullable', 'string', 'max:50'],
            'chassis_number' => ['required', 'string', 'max:100'],
            'engine_number' => ['nullable', 'string', 'max:100'],
            'received_at' => ['nullable', 'date'],
            'date_of_allocation' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $commandId = $workflow->getActiveCommandIdForRole($request->user(), 'Transport Store/Receiver');

        $vehicle = DB::transaction(function () use ($request, $data, $commandId) {
            $vehicle = FleetVehicle::create([
                'make' => $data['make'],
                'model' => $data['model'] ?? null,
                'year_of_manufacture' => $data['year_of_manufacture'] ?? null,
                'vehicle_type' => $data['vehicle_type'],
                'reg_no' => isset($data['reg_no']) && trim($data['reg_no']) !== '' ? trim($data['reg_no']) : null,
                'chassis_number' => trim($data['chassis_number']),
                'engine_number' => isset($data['engine_number']) && trim($data['engine_number']) !== '' ? trim($data['engine_number']) : null,
                'service_status' => 'SERVICEABLE',
                'lifecycle_status' => 'IN_STOCK',
                'current_command_id' => $commandId,
                'created_by' => $request->user()->id,
            ]);

            FleetVehicleReceipt::create([
                'fleet_vehicle_id' => $vehicle->id,
                'command_id' => $commandId,
                'date_of_allocation' => $data['date_of_allocation'] ?? null,
                'received_by_user_id' => $request->user()->id,
                'received_at' => isset($data['received_at']) ? $data['received_at'] : now(),
                'notes' => $data['notes'] ?? null,
            ]);

            return $vehicle;
        });

        // Notify CC T&L when this vehicle can satisfy KIV/partial requests (simple matching)
        $ccTlUsers = User::whereHas('roles', function ($q) {
            $q->where('roles.name', 'CC T&L')
                ->where('user_roles.is_active', true);
        })->get();

        if ($ccTlUsers->isNotEmpty()) {
            $notifications->notifyMany(
                $ccTlUsers,
                'fleet_vehicle_received',
                'New Vehicle Added to Inventory',
                "A new {$vehicle->vehicle_type} ({$vehicle->make} {$vehicle->model}) was received into inventory. Check KIV/partial requests for matches.",
                'fleet_vehicle',
                $vehicle->id,
                false
            );
        }

        return redirect()
            ->route('fleet.vehicles.show', $vehicle)
            ->with('success', 'Vehicle received into inventory.');
    }

    public function editIdentifiers(FleetVehicle $vehicle)
    {
        return view('fleet.vehicles.edit-identifiers', compact('vehicle'));
    }

    public function updateIdentifiers(Request $request, FleetVehicle $vehicle)
    {
        $user = $request->user();

        // Only CD / Store Receiver / CC T&L can change identifiers
        $canEdit = $user->hasAnyRole(['CD', 'Transport Store/Receiver', 'CC T&L']);
        if (!$canEdit) {
            abort(403);
        }

        $data = $request->validate([
            'reg_no' => ['nullable', 'string', 'max:50'],
            'engine_number' => ['nullable', 'string', 'max:100'],
        ]);

        // Normalize empty strings to null
        $newReg = isset($data['reg_no']) && trim($data['reg_no']) !== '' ? trim($data['reg_no']) : null;
        $newEngine = isset($data['engine_number']) && trim($data['engine_number']) !== '' ? trim($data['engine_number']) : null;

        try {
            $this->applyIdentifierChange($vehicle, $user->id, 'REG_NO', $vehicle->reg_no, $newReg);
            $this->applyIdentifierChange($vehicle, $user->id, 'ENGINE_NO', $vehicle->engine_number, $newEngine);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        return redirect()
            ->route('fleet.vehicles.show', $vehicle)
            ->with('success', 'Vehicle identifiers updated.');
    }

    public function updateServiceStatus(Request $request, FleetVehicle $vehicle, FleetWorkflowService $workflow)
    {
        abort_unless($request->user()->hasRole('CD'), 403);

        $commandId = $workflow->getActiveCommandIdForRole($request->user(), 'CD');
        abort_unless($commandId && (int) $vehicle->current_command_id === (int) $commandId, 403);

        $data = $request->validate([
            'service_status' => ['required', 'string', 'in:SERVICEABLE,UNSERVICEABLE'],
        ]);

        $vehicle->update([
            'service_status' => $data['service_status'],
        ]);

        return redirect()
            ->route('fleet.vehicles.show', $vehicle)
            ->with('success', 'Vehicle service status updated.');
    }

    private function applyIdentifierChange(FleetVehicle $vehicle, int $userId, string $fieldName, ?string $old, ?string $new): void
    {
        // No change
        if (($old ?? '') === ($new ?? '')) {
            return;
        }

        // Apply the change on the vehicle first; database uniques will enforce global uniqueness
        if ($fieldName === 'REG_NO') {
            $vehicle->reg_no = $new;
        } elseif ($fieldName === 'ENGINE_NO') {
            $vehicle->engine_number = $new;
        }

        $vehicle->save();

        FleetVehicleAudit::create([
            'fleet_vehicle_id' => $vehicle->id,
            'field_name' => $fieldName,
            'old_value' => $old,
            'new_value' => $new,
            'changed_by_user_id' => $userId,
            'changed_at' => now(),
        ]);
    }
}

