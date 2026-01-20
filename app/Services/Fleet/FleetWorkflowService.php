<?php

namespace App\Services\Fleet;

use App\Models\FleetRequestFulfillment;
use App\Models\FleetRequest;
use App\Models\FleetRequestStep;
use App\Models\FleetVehicle;
use App\Models\FleetVehicleAssignment;
use App\Models\User;
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

    public function createCommandRequisition(User $user, array $data): FleetRequest
    {
        $originCommandId = $this->getActiveCommandIdForRole($user, 'CD');
        if (!$originCommandId) {
            throw ValidationException::withMessages([
                'origin_command_id' => 'CD role must be assigned to a command.',
            ]);
        }

        return FleetRequest::create([
            'request_type' => 'COMMAND_REQUISITION',
            'status' => 'DRAFT',
            'origin_command_id' => $originCommandId,
            'requested_vehicle_type' => $data['requested_vehicle_type'] ?? null,
            'requested_make' => $data['requested_make'] ?? null,
            'requested_model' => $data['requested_model'] ?? null,
            'requested_year' => $data['requested_year'] ?? null,
            'requested_quantity' => $data['requested_quantity'] ?? 1,
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

            $this->seedStepsForCommandRequisition($request);
        });

        return $request->fresh(['steps', 'fulfillment', 'originCommand', 'createdBy']);
    }

    /**
     * Act on the current step.
     *
     * decision values used here:
     * - FORWARDED (for FORWARD steps)
     * - APPROVED / REJECTED (for APPROVE steps)
     * - REVIEWED (for REVIEW steps; meaning "completed this review step and forwarded")
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

        // Role authorization: user must have the step role active
        if (!$user->hasRole($step->role_name)) {
            throw ValidationException::withMessages([
                'role' => "You do not have permission to act on this step ({$step->role_name}).",
            ]);
        }

        DB::transaction(function () use ($request, $step, $user, $decision, $comment) {
            $step->update([
                'acted_by_user_id' => $user->id,
                'acted_at' => now(),
                'decision' => $decision,
                'comment' => $comment,
            ]);

            // Status transitions (minimal scaffold; will be refined as features land)
            $nextOrder = $step->step_order + 1;
            $request->update([
                'status' => $this->nextStatus($request, $step, $decision),
                'current_step_order' => $this->hasStep($request, $nextOrder) ? $nextOrder : null,
            ]);
        });

        return $request->fresh(['steps', 'fulfillment', 'originCommand', 'createdBy']);
    }

    /**
     * CC T&L inventory check (step 5).
     *
     * - If no vehicles selected -> mark KIV and do NOT advance the workflow.
     * - If vehicles selected -> reserve them and advance workflow upward for approval.
     */
    public function ccTlPropose(FleetRequest $request, User $user, array $vehicleIds = [], ?string $comment = null): FleetRequest
    {
        if ($request->current_step_order !== 5) {
            throw ValidationException::withMessages([
                'current_step_order' => 'This request is not at the CC T&L inventory check step.',
            ]);
        }

        if (!$user->hasRole('CC T&L')) {
            throw ValidationException::withMessages([
                'role' => 'Only CC T&L can perform inventory check.',
            ]);
        }

        $vehicleIds = array_values(array_filter(array_map('intval', $vehicleIds)));

        return DB::transaction(function () use ($request, $user, $vehicleIds, $comment) {
            // Clear previous reservations for this request (if any)
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

                // Ensure all requested IDs were found and available
                if ($selected->count() !== count($vehicleIds)) {
                    throw ValidationException::withMessages([
                        'vehicle_ids' => 'One or more selected vehicles are no longer available.',
                    ]);
                }

                // Criteria matching (type/make/model/year) where provided
                foreach ($selected as $v) {
                    if ($request->requested_vehicle_type && $v->vehicle_type !== $request->requested_vehicle_type) {
                        throw ValidationException::withMessages([
                            'vehicle_ids' => 'Selected vehicles must match requested vehicle type.',
                        ]);
                    }
                    if ($request->requested_make && strcasecmp($v->make, $request->requested_make) !== 0) {
                        throw ValidationException::withMessages([
                            'vehicle_ids' => 'Selected vehicles must match requested make.',
                        ]);
                    }
                    if ($request->requested_model && strcasecmp((string) $v->model, (string) $request->requested_model) !== 0) {
                        throw ValidationException::withMessages([
                            'vehicle_ids' => 'Selected vehicles must match requested model.',
                        ]);
                    }
                    if ($request->requested_year && (int) $v->year_of_manufacture !== (int) $request->requested_year) {
                        throw ValidationException::withMessages([
                            'vehicle_ids' => 'Selected vehicles must match requested year.',
                        ]);
                    }
                }

                // Reserve
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
                // KIV and pause workflow at step 5
                $request->update([
                    'status' => 'KIV',
                    'current_step_order' => 5,
                ]);

                // Also record the step action for auditability without advancing
                $request->steps()->where('step_order', 5)->update([
                    'acted_by_user_id' => $user->id,
                    'acted_at' => now(),
                    'decision' => 'KIV',
                    'comment' => $comment,
                ]);

                return $request->fresh(['steps', 'fulfillment']);
            }

            // Move workflow forward for approval
            $this->act($request, $user, 'REVIEWED', $comment);

            // Preserve info about partial availability
            if ($kivQty > 0) {
                $request->update(['status' => 'PARTIALLY_FULFILLED']);
            }

            return $request->fresh(['steps', 'fulfillment']);
        });
    }

    /**
     * CC T&L release (step 11): converts reserved vehicles into command allocations.
     */
    public function ccTlReleaseReserved(FleetRequest $request, User $user, ?string $comment = null): FleetRequest
    {
        if ($request->current_step_order !== 11) {
            throw ValidationException::withMessages([
                'current_step_order' => 'This request is not at the CC T&L release step.',
            ]);
        }

        if (!$user->hasRole('CC T&L')) {
            throw ValidationException::withMessages([
                'role' => 'Only CC T&L can release vehicles.',
            ]);
        }

        if ($request->status !== 'APPROVED' && $request->status !== 'PARTIALLY_FULFILLED') {
            throw ValidationException::withMessages([
                'status' => 'Request must be approved before release.',
            ]);
        }

        if (!$request->origin_command_id) {
            throw ValidationException::withMessages([
                'origin_command_id' => 'Origin command is missing.',
            ]);
        }

        return DB::transaction(function () use ($request, $user, $comment) {
            $reserved = FleetVehicle::query()
                ->where('reserved_fleet_request_id', $request->id)
                ->lockForUpdate()
                ->get();

            if ($reserved->isEmpty()) {
                throw ValidationException::withMessages([
                    'reserved' => 'No reserved vehicles found for this request.',
                ]);
            }

            foreach ($reserved as $v) {
                FleetVehicleAssignment::create([
                    'fleet_vehicle_id' => $v->id,
                    'assigned_to_command_id' => $request->origin_command_id,
                    'assigned_by_user_id' => $user->id,
                    'assigned_at' => now(),
                    'released_by_user_id' => $user->id,
                    'released_at' => now(),
                    'notes' => $comment,
                ]);

                $v->update([
                    'current_command_id' => $request->origin_command_id,
                    'current_officer_id' => null,
                    'lifecycle_status' => 'AT_COMMAND_POOL',
                    'reserved_fleet_request_id' => null,
                    'reserved_by_user_id' => null,
                    'reserved_at' => null,
                ]);
            }

            $this->act($request, $user, 'REVIEWED', $comment);

            return $request->fresh(['steps', 'fulfillment']);
        });
    }

    private function seedStepsForCommandRequisition(FleetRequest $request): void
    {
        $steps = [
            // Forwarding chain up to CC T&L
            ['step_order' => 1, 'role_name' => 'Area Controller', 'action' => 'FORWARD'],
            ['step_order' => 2, 'role_name' => 'CGC', 'action' => 'FORWARD'],
            ['step_order' => 3, 'role_name' => 'DCG FATS', 'action' => 'FORWARD'],
            ['step_order' => 4, 'role_name' => 'ACG TS', 'action' => 'FORWARD'],
            // CC T&L inventory check (proposal creation)
            ['step_order' => 5, 'role_name' => 'CC T&L', 'action' => 'REVIEW'],
            // Proposal routes back up for approval
            ['step_order' => 6, 'role_name' => 'ACG TS', 'action' => 'FORWARD'],
            ['step_order' => 7, 'role_name' => 'DCG FATS', 'action' => 'FORWARD'],
            ['step_order' => 8, 'role_name' => 'CGC', 'action' => 'APPROVE'],
            // Approved routes back down to CC T&L
            ['step_order' => 9, 'role_name' => 'DCG FATS', 'action' => 'FORWARD'],
            ['step_order' => 10, 'role_name' => 'ACG TS', 'action' => 'FORWARD'],
            // CC T&L release step
            ['step_order' => 11, 'role_name' => 'CC T&L', 'action' => 'REVIEW'],
        ];

        foreach ($steps as $s) {
            FleetRequestStep::create([
                'fleet_request_id' => $request->id,
                ...$s,
            ]);
        }
    }

    private function nextStatus(FleetRequest $request, FleetRequestStep $step, string $decision): string
    {
        if ($decision === 'KIV') {
            return 'KIV';
        }

        if ($step->action === 'APPROVE') {
            return $decision === 'APPROVED' ? 'APPROVED' : 'REJECTED';
        }

        // Rough phases based on step order
        if ($step->step_order <= 4) {
            return 'IN_REVIEW';
        }
        if ($step->step_order === 5) {
            return 'PENDING_CGC_APPROVAL';
        }
        if ($step->step_order >= 6 && $step->step_order <= 8) {
            return 'PENDING_CGC_APPROVAL';
        }
        if ($step->step_order >= 9 && $step->step_order <= 10) {
            return 'APPROVED';
        }
        if ($step->step_order === 11) {
            return 'RELEASED';
        }

        return $request->status;
    }

    private function hasStep(FleetRequest $request, int $order): bool
    {
        return $request->steps()->where('step_order', $order)->exists();
    }
}

