<?php

namespace App\Services\Fleet;

use App\Models\FleetRequestFulfillment;
use App\Models\FleetRequest;
use App\Models\FleetRequestStep;
use App\Models\FleetVehicle;
use App\Models\FleetVehicleAssignment;
use App\Models\Role;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FleetWorkflowService
{
    /**
     * Get the active command_id for a specific role assignment.
     */
    public function getActiveCommandIdForRole(User $user, string $roleName): ?int
    {
        $role = $user->roles()
            ->where('name', $roleName)
            ->wherePivot('is_active', true)
            ->first();

        return $role?->pivot?->command_id;
    }

    public function userHasActiveRole(User $user, string $roleName): bool
    {
        return $user->roles()
            ->where('name', $roleName)
            ->wherePivot('is_active', true)
            ->exists();
    }

    public function createRequest(User $user, array $data): FleetRequest
    {
        $requestType = $data['request_type'];

        // Determine the role required to create this request
        $requiredRole = match ($requestType) {
            'FLEET_REQUISITION' => 'OC Workshop',
            'FLEET_OPE', 'FLEET_REPAIR', 'FLEET_USE' => 'Staff Officer T&L',
            default => 'Area Controller',
        };

        $originCommandId = $this->getActiveCommandIdForRole($user, $requiredRole);
        if (!$originCommandId) {
            // Fallback to CD or any active role if specific command role isn't found
            $originCommandId = $user->roles()->wherePivot('is_active', true)->first()?->pivot?->command_id;
        }

        if (!$originCommandId) {
            throw ValidationException::withMessages([
                'role' => "You must have an active {$requiredRole} role assigned to a command.",
            ]);
        }

        return FleetRequest::create([
            'request_type' => $requestType,
            'status' => 'DRAFT',
            'origin_command_id' => $originCommandId,
            'requested_vehicle_type' => $data['requested_vehicle_type'] ?? null,
            'requested_make' => $data['requested_make'] ?? null,
            'requested_model' => $data['requested_model'] ?? null,
            'requested_year' => $data['requested_year'] ?? null,
            'requested_quantity' => $data['requested_quantity'] ?? 1,
            'amount' => $data['amount'] ?? null,
            'fleet_vehicle_id' => $data['fleet_vehicle_id'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_by' => $user->id,
        ]);
    }

    /**
     * Submit a draft request and seed the workflow steps.
     */
    public function submit(FleetRequest $request, User $user): FleetRequest
    {
        if ($request->status !== 'DRAFT') {
            throw ValidationException::withMessages([
                'status' => 'Only DRAFT requests can be submitted.',
            ]);
        }

        if ((int) $request->created_by !== (int) $user->id) {
            throw ValidationException::withMessages([
                'created_by' => 'Only the creator can submit this request.',
            ]);
        }

        DB::transaction(function () use ($request) {
            $request->update([
                'status' => 'SUBMITTED',
                'submitted_at' => now(),
                'current_step_order' => 1,
            ]);

            $this->seedSteps($request);
        });

        $this->notifyNextStepUsers($request);

        return $request->fresh(['steps', 'fulfillment', 'originCommand', 'createdBy']);
    }

    /**
     * Act on the current step.
     */
    public function act(FleetRequest $request, User $user, string $decision, ?string $comment = null): FleetRequest
    {
        $currentOrder = $request->current_step_order;
        if (!$currentOrder) {
            throw ValidationException::withMessages([
                'current_step_order' => 'This request has no active step.',
            ]);
        }

        /** @var FleetRequestStep|null $step */
        $step = $request->steps()->where('step_order', $currentOrder)->first();
        if (!$step) {
            throw ValidationException::withMessages([
                'step' => 'Current workflow step not found.',
            ]);
        }

        // Role authorization
        if (!$user->hasRole($step->role_name)) {
            throw ValidationException::withMessages([
                'role' => "You do not have permission to act on this step ({$step->role_name}).",
            ]);
        }

        $validDecisions = match ($step->action) {
            'FORWARD' => ['FORWARDED'],
            'REVIEW' => ['REVIEWED'],
            'APPROVE' => ['APPROVED', 'REJECTED'],
            default => [],
        };

        if ($decision === 'KIV') {
            $validDecisions[] = 'KIV';
        }

        if (!in_array($decision, $validDecisions, true)) {
            throw ValidationException::withMessages([
                'decision' => "Decision {$decision} is not valid for action {$step->action}.",
            ]);
        }

        DB::transaction(function () use ($request, $step, $user, $decision, $comment) {
            $step->update([
                'acted_by_user_id' => $user->id,
                'acted_at' => now(),
                'decision' => $decision,
                'comment' => $comment,
            ]);

            if ($decision === 'REJECTED') {
                $request->update([
                    'status' => 'REJECTED',
                    'current_step_order' => null,
                ]);
                return;
            }

            // Determine next step based on decision and request type
            $nextOrder = $this->calculateNextStepOrder($request, $step, $decision);

            $request->update([
                'status' => $this->nextStatus($request, $step, $decision, $nextOrder),
                'current_step_order' => $nextOrder,
            ]);
        });

        $this->notifyNextStepUsers($request);

        // Notify creator when request is REJECTED or RELEASED (in-app + email)
        $creator = $request->createdBy ?? User::find($request->created_by);
        if ($creator && $request->status === 'REJECTED') {
            app(NotificationService::class)->notify(
                $creator,
                'fleet_request_rejected',
                "Fleet Request #{$request->id} Rejected",
                "Your fleet request #{$request->id} ({$request->request_type}) has been rejected.",
                'fleet_request',
                $request->id,
                true
            );
        }
        if ($creator && $request->status === 'RELEASED') {
            app(NotificationService::class)->notify(
                $creator,
                'fleet_request_released',
                "Fleet Request #{$request->id} Released",
                "Your fleet request #{$request->id} has been approved and released.",
                'fleet_request',
                $request->id,
                true
            );
            $this->notifyCd($request, 'Fleet Request Released', "Request #{$request->id} has been approved and released.");
        }

        return $request->fresh(['steps', 'fulfillment', 'originCommand', 'createdBy']);
    }

    private function calculateNextStepOrder(FleetRequest $request, FleetRequestStep $currentStep, string $decision): ?int
    {
        $nextOrder = $currentStep->step_order + 1;

        // Requisition threshold logic
        if ($request->request_type === 'FLEET_REQUISITION') {
            $amount = (float) $request->amount;

            // ACG TS Step (order 1) logic
            if ($currentStep->step_order === 1) {
                if ($amount <= 300000) {
                    return null; // Approved and finished
                }
                return 2; // Move to DCG FATS
            }

            // DCG FATS Step (order 2) logic
            if ($currentStep->step_order === 2) {
                if ($amount <= 500000) {
                    return null; // Approved and finished
                }
                return 3; // Move to CGC
            }

            // CGC Step (order 3) logic
            if ($currentStep->step_order === 3) {
                return null; // Approved and finished
            }
        }

        return $this->hasStep($request, $nextOrder) ? $nextOrder : null;
    }

    /**
     * CC T&L inventory check (proposal creation).
     */
    public function ccTlPropose(FleetRequest $request, User $user, array $vehicleIds = [], ?string $comment = null): FleetRequest
    {
        // For Re-allocation, this might be step 2. For New Vehicle, step 2.
        // We'll check the role and current action.

        $step = $request->steps()->where('step_order', $request->current_step_order)->first();
        if (!$step || $step->role_name !== 'CC T&L' || $step->action !== 'REVIEW') {
            throw ValidationException::withMessages([
                'current_step_order' => 'This request is not at the CC T&L proposal step.',
            ]);
        }

        if (!$user->hasRole('CC T&L')) {
            throw ValidationException::withMessages([
                'role' => 'Only CC T&L can perform this action.',
            ]);
        }

        $vehicleIds = array_values(array_filter(array_map('intval', $vehicleIds)));

        return DB::transaction(function () use ($request, $user, $vehicleIds, $comment) {
            // Reserve logic... (omitted for brevity, assume similar to original but adapted)
            // For now, let's keep the reservation logic if it's a NEW vehicle request.
            if ($request->request_type === 'FLEET_NEW_VEHICLE') {
                // ... reservation logic ...
                // (Re-using original reservation logic here)
                FleetVehicle::where('reserved_fleet_request_id', $request->id)->update([
                    'reserved_fleet_request_id' => null,
                    'reserved_by_user_id' => null,
                    'reserved_at' => null,
                ]);

                $selected = collect();
                if (!empty($vehicleIds)) {
                    $selected = FleetVehicle::query()
                        ->whereIn('id', $vehicleIds)
                        ->where('lifecycle_status', 'IN_STOCK')
                        ->whereNull('reserved_fleet_request_id')
                        ->lockForUpdate()
                        ->get();

                    if ($selected->count() !== count($vehicleIds)) {
                        throw ValidationException::withMessages(['vehicle_ids' => 'One or more vehicles unavailable.']);
                    }

                    FleetVehicle::whereIn('id', $selected->pluck('id')->all())->update([
                        'reserved_fleet_request_id' => $request->id,
                        'reserved_by_user_id' => $user->id,
                        'reserved_at' => now(),
                    ]);
                }

                $fulfilledQty = $selected->count();
                $kivQty = max(0, (int) $request->requested_quantity - $fulfilledQty);

                FleetRequestFulfillment::updateOrCreate(
                    ['fleet_request_id' => $request->id],
                    [
                        'fulfilled_quantity' => $fulfilledQty,
                        'kiv_quantity' => $kivQty,
                        'fulfilled_by_user_id' => $user->id,
                        'fulfilled_at' => now(),
                        'notes' => $comment,
                    ]
                );

                if ($fulfilledQty === 0) {
                    $request->update(['status' => 'KIV']);
                    $request->steps()->where('step_order', $request->current_step_order)->update([
                        'acted_by_user_id' => $user->id,
                        'acted_at' => now(),
                        'decision' => 'KIV',
                        'comment' => $comment,
                    ]);
                    return $request->fresh(['steps', 'fulfillment']);
                }
            }

            // Normal progression
            return $this->act($request, $user, 'REVIEWED', $comment);
        });
    }

    /**
     * CC T&L release.
     */
    public function ccTlRelease(FleetRequest $request, User $user, ?string $comment = null): FleetRequest
    {
        $step = $request->steps()->where('step_order', $request->current_step_order)->first();
        if (!$step || $step->role_name !== 'CC T&L' || $step->action !== 'REVIEW') {
            throw ValidationException::withMessages([
                'current_step_order' => 'This request is not at the CC T&L release step.',
            ]);
        }

        if (!$user->hasRole('CC T&L')) {
            throw ValidationException::withMessages([
                'role' => 'Only CC T&L can release vehicles.',
            ]);
        }

        return DB::transaction(function () use ($request, $user, $comment) {
            if ($request->request_type === 'FLEET_NEW_VEHICLE') {
                $reserved = FleetVehicle::query()
                    ->where('reserved_fleet_request_id', $request->id)
                    ->lockForUpdate()
                    ->get();

                foreach ($reserved as $v) {
                    FleetVehicleAssignment::create([
                        'fleet_vehicle_id' => $v->id,
                        'assigned_to_command_id' => $request->origin_command_id,
                        'assigned_by_user_id' => $user->id,
                        'assigned_at' => now(),
                        'released_by_user_id' => $user->id,
                        'released_at' => now(),
                    ]);

                    $v->update([
                        'current_command_id' => $request->origin_command_id,
                        'lifecycle_status' => 'AT_COMMAND_POOL',
                        'reserved_fleet_request_id' => null,
                        'reserved_by_user_id' => null,
                        'reserved_at' => null,
                    ]);
                }
            } elseif ($request->request_type === 'FLEET_RE_ALLOCATION' && $request->fleet_vehicle_id) {
                $vehicle = FleetVehicle::findOrFail($request->fleet_vehicle_id);

                FleetVehicleAssignment::create([
                    'fleet_vehicle_id' => $vehicle->id,
                    'assigned_to_command_id' => $request->origin_command_id,
                    'assigned_by_user_id' => $user->id,
                    'assigned_at' => now(),
                    'released_by_user_id' => $user->id,
                    'released_at' => now(),
                ]);

                $vehicle->update([
                    'current_command_id' => $request->origin_command_id,
                    'lifecycle_status' => 'AT_COMMAND_POOL',
                ]);
            }

            return $this->act($request, $user, 'REVIEWED', $comment);
        });
    }

    private function seedSteps(FleetRequest $request): void
    {
        $steps = match ($request->request_type) {
            'FLEET_NEW_VEHICLE' => [
                ['step_order' => 1, 'role_name' => 'CC T&L', 'action' => 'REVIEW'], // Propose
                ['step_order' => 2, 'role_name' => 'CGC', 'action' => 'APPROVE'],
                ['step_order' => 3, 'role_name' => 'DCG FATS', 'action' => 'FORWARD'],
                ['step_order' => 4, 'role_name' => 'ACG TS', 'action' => 'FORWARD'],
                ['step_order' => 5, 'role_name' => 'CC T&L', 'action' => 'REVIEW'], // Release
            ],
            'FLEET_RE_ALLOCATION' => [
                ['step_order' => 1, 'role_name' => 'CC T&L', 'action' => 'REVIEW'], // Approve & Release
            ],
            'FLEET_REQUISITION' => [
                ['step_order' => 1, 'role_name' => 'ACG TS', 'action' => 'APPROVE'],
                ['step_order' => 2, 'role_name' => 'DCG FATS', 'action' => 'APPROVE'],
                ['step_order' => 3, 'role_name' => 'CGC', 'action' => 'APPROVE'],
            ],
            default => [
                ['step_order' => 1, 'role_name' => 'Staff Officer T&L', 'action' => 'APPROVE'],
            ],
        };

        foreach ($steps as $s) {
            FleetRequestStep::create([
                'fleet_request_id' => $request->id,
                ...$s,
            ]);
        }
    }

    private function nextStatus(FleetRequest $request, FleetRequestStep $step, string $decision, ?int $nextOrder): string
    {
        if ($decision === 'REJECTED')
            return 'REJECTED';
        if ($decision === 'KIV')
            return 'KIV';
        if ($nextOrder === null)
            return 'RELEASED'; // Or COMPLETED/APPROVED

        return 'IN_REVIEW';
    }

    private function hasStep(FleetRequest $request, int $order): bool
    {
        return $request->steps()->where('step_order', $order)->exists();
    }

    private function notifyNextStepUsers(FleetRequest $request): void
    {
        $nextOrder = $request->current_step_order;
        if (!$nextOrder)
            return;

        $step = $request->steps()->where('step_order', $nextOrder)->first();
        if (!$step)
            return;

        $roleName = $step->role_name;
        $notificationService = app(NotificationService::class);

        $query = User::whereHas('roles', function ($q) use ($roleName, $request) {
            $q->where('name', $roleName)
                ->where('user_roles.is_active', true);

            if (in_array($roleName, ['CD', 'O/C T&L', 'Transport Store/Receiver', 'Area Controller', 'Staff Officer T&L', 'OC Workshop', 'T&L Officer'], true)) {
                $q->where('user_roles.command_id', $request->origin_command_id);
            }
        })->where('is_active', true);

        $originName = $request->originCommand?->name ?? 'Unknown Command';
        $title = "Fleet Request #{$request->id} awaiting action";
        $message = "A fleet request ({$request->request_type}) from {$originName} is waiting at Step {$nextOrder} ({$roleName}).";

        foreach ($query->get() as $recipient) {
            $notificationService->notify(
                $recipient,
                'fleet_request_pending',
                $title,
                $message,
                'fleet_request',
                $request->id,
                true
            );
        }
    }

    private function notifyCd(FleetRequest $request, string $title, string $message): void
    {
        if (!$request->origin_command_id) {
            return;
        }

        $notificationService = app(NotificationService::class);

        $cdUsers = User::whereHas('roles', function ($q) use ($request) {
            $q->where('name', 'CD')
                ->where('user_roles.is_active', true)
                ->where('user_roles.command_id', $request->origin_command_id);
        })->where('is_active', true)->get();

        foreach ($cdUsers as $cd) {
            $notificationService->notify(
                $cd,
                'fleet_request_update',
                $title,
                $message,
                'fleet_request',
                $request->id,
                true
            );
        }
    }
}

