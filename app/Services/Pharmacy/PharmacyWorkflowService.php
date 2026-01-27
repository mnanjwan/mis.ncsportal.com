<?php

namespace App\Services\Pharmacy;

use App\Models\PharmacyDrug;
use App\Models\PharmacyProcurement;
use App\Models\PharmacyRequisition;
use App\Models\PharmacyStock;
use App\Models\PharmacyStockMovement;
use App\Models\PharmacyWorkflowStep;
use App\Models\Role;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PharmacyWorkflowService
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

    // ============================================================
    // PROCUREMENT WORKFLOW
    // ============================================================

    /**
     * Create a procurement draft.
     */
    public function createProcurement(User $user, array $data): PharmacyProcurement
    {
        if (!$this->userHasActiveRole($user, 'Controller Procurement')) {
            throw ValidationException::withMessages([
                'role' => 'You must have the Controller Procurement role.',
            ]);
        }

        $procurement = PharmacyProcurement::create([
            'status' => 'DRAFT',
            'notes' => $data['notes'] ?? null,
            'created_by' => $user->id,
        ]);

        $procurement->reference_number = $procurement->generateReferenceNumber();
        $procurement->save();

        return $procurement;
    }

    /**
     * Submit a procurement draft.
     */
    public function submitProcurement(PharmacyProcurement $procurement, User $user): PharmacyProcurement
    {
        if ($procurement->status !== 'DRAFT') {
            throw ValidationException::withMessages([
                'status' => 'Only DRAFT procurements can be submitted.',
            ]);
        }

        if ((int) $procurement->created_by !== (int) $user->id) {
            throw ValidationException::withMessages([
                'created_by' => 'Only the creator can submit this procurement.',
            ]);
        }

        if ($procurement->items()->count() === 0) {
            throw ValidationException::withMessages([
                'items' => 'Procurement must have at least one item.',
            ]);
        }

        DB::transaction(function () use ($procurement) {
            $procurement->update([
                'status' => 'SUBMITTED',
                'submitted_at' => now(),
                'current_step_order' => 1,
            ]);

            $this->seedProcurementWorkflowSteps($procurement);
        });

        // Notify OC Pharmacy
        $this->notifyNextStepUsers($procurement, 'procurement');

        return $procurement->fresh(['steps', 'items.drug', 'createdBy']);
    }

    /**
     * Act on the current procurement step.
     */
    public function actOnProcurement(PharmacyProcurement $procurement, User $user, string $decision, ?string $comment = null): PharmacyProcurement
    {
        $currentOrder = $procurement->current_step_order;
        if (!$currentOrder) {
            throw ValidationException::withMessages([
                'current_step_order' => 'This procurement has no active step.',
            ]);
        }

        $step = $procurement->steps()->where('step_order', $currentOrder)->first();
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

        $validDecisions = $step->getValidDecisions();
        if (!in_array($decision, $validDecisions, true)) {
            throw ValidationException::withMessages([
                'decision' => "Decision {$decision} is not valid for action {$step->action}.",
            ]);
        }

        DB::transaction(function () use ($procurement, $step, $user, $decision, $comment) {
            $step->update([
                'acted_by_user_id' => $user->id,
                'acted_at' => now(),
                'decision' => $decision,
                'comment' => $comment,
            ]);

            $nextOrder = $step->step_order + 1;
            $hasNextStep = $procurement->steps()->where('step_order', $nextOrder)->exists();

            if ($decision === 'REJECTED') {
                $procurement->update([
                    'status' => 'REJECTED',
                    'current_step_order' => null,
                ]);
            } elseif ($decision === 'APPROVED' && $step->action === 'APPROVE') {
                $procurement->update([
                    'status' => 'APPROVED',
                    'approved_at' => now(),
                    'current_step_order' => $hasNextStep ? $nextOrder : null,
                ]);
            } else {
                $procurement->update([
                    'current_step_order' => $hasNextStep ? $nextOrder : null,
                ]);
            }
        });

        $this->notifyNextStepUsers($procurement, 'procurement');
        $this->notifyCreator($procurement, 'procurement', "Procurement #{$procurement->id} - {$decision}");

        return $procurement->fresh(['steps', 'items.drug', 'createdBy']);
    }

    /**
     * Receive procurement items at Central Medical Store.
     */
    public function receiveProcurement(PharmacyProcurement $procurement, User $user, array $receivedItems, ?string $comment = null): PharmacyProcurement
    {
        if (!$user->hasRole('Central Medical Store')) {
            throw ValidationException::withMessages([
                'role' => 'Only Central Medical Store can receive procurements.',
            ]);
        }

        if ($procurement->status !== 'APPROVED') {
            throw ValidationException::withMessages([
                'status' => 'Only APPROVED procurements can be received.',
            ]);
        }

        return DB::transaction(function () use ($procurement, $user, $receivedItems, $comment) {
            foreach ($receivedItems as $itemData) {
                $item = $procurement->items()->find($itemData['id']);
                if (!$item) {
                    continue;
                }

                $quantityReceived = (int) ($itemData['quantity_received'] ?? 0);
                $expiryDate = $itemData['expiry_date'] ?? null;
                $batchNumber = $itemData['batch_number'] ?? null;

                if ($quantityReceived <= 0) {
                    continue;
                }

                // Find or create the drug in the catalog
                $drugId = $item->pharmacy_drug_id;
                if (!$drugId && $item->drug_name) {
                    // Look for existing drug by name or create new one
                    $drug = PharmacyDrug::firstOrCreate(
                        ['name' => $item->drug_name],
                        [
                            'unit_of_measure' => $item->unit_of_measure ?? 'units',
                            'is_active' => true,
                        ]
                    );
                    $drugId = $drug->id;
                    
                    // Link the item to the drug
                    $item->update(['pharmacy_drug_id' => $drugId]);
                }

                if (!$drugId) {
                    continue; // Skip if no drug can be identified
                }

                // Update procurement item
                $item->update([
                    'quantity_received' => $quantityReceived,
                    'expiry_date' => $expiryDate,
                    'batch_number' => $batchNumber,
                ]);

                // Update or create stock record
                $stock = PharmacyStock::firstOrCreate([
                    'pharmacy_drug_id' => $drugId,
                    'location_type' => 'CENTRAL_STORE',
                    'command_id' => null,
                    'batch_number' => $batchNumber,
                    'expiry_date' => $expiryDate,
                ], [
                    'quantity' => 0,
                ]);

                $stock->increment('quantity', $quantityReceived);

                // Record stock movement
                PharmacyStockMovement::create([
                    'pharmacy_drug_id' => $drugId,
                    'movement_type' => 'PROCUREMENT_RECEIPT',
                    'reference_id' => $procurement->id,
                    'reference_type' => PharmacyProcurement::class,
                    'location_type' => 'CENTRAL_STORE',
                    'command_id' => null,
                    'quantity' => $quantityReceived,
                    'expiry_date' => $expiryDate,
                    'batch_number' => $batchNumber,
                    'notes' => $comment,
                    'created_by' => $user->id,
                ]);
            }

            // Mark current step as completed
            $currentStep = $procurement->getCurrentStep();
            if ($currentStep) {
                $currentStep->update([
                    'acted_by_user_id' => $user->id,
                    'acted_at' => now(),
                    'decision' => 'REVIEWED',
                    'comment' => $comment,
                ]);
            }

            $procurement->update([
                'status' => 'RECEIVED',
                'received_at' => now(),
                'current_step_order' => null,
            ]);

            $this->notifyCreator($procurement, 'procurement', 'Procurement received at Central Medical Store');

            return $procurement->fresh(['steps', 'items.drug', 'createdBy']);
        });
    }

    /**
     * Seed workflow steps for procurement.
     */
    private function seedProcurementWorkflowSteps(PharmacyProcurement $procurement): void
    {
        $steps = [
            ['step_order' => 1, 'role_name' => 'OC Pharmacy', 'action' => 'APPROVE'],
            ['step_order' => 2, 'role_name' => 'Central Medical Store', 'action' => 'REVIEW'],
        ];

        foreach ($steps as $step) {
            PharmacyWorkflowStep::create([
                'pharmacy_procurement_id' => $procurement->id,
                ...$step,
            ]);
        }
    }

    // ============================================================
    // REQUISITION WORKFLOW
    // ============================================================

    /**
     * Create a requisition draft.
     */
    public function createRequisition(User $user, array $data): PharmacyRequisition
    {
        $commandId = $this->getActiveCommandIdForRole($user, 'Command Pharmacist');
        if (!$commandId) {
            throw ValidationException::withMessages([
                'command' => 'Command Pharmacist role must be assigned to a command.',
            ]);
        }

        $requisition = PharmacyRequisition::create([
            'status' => 'DRAFT',
            'command_id' => $commandId,
            'notes' => $data['notes'] ?? null,
            'created_by' => $user->id,
        ]);

        $requisition->reference_number = $requisition->generateReferenceNumber();
        $requisition->save();

        return $requisition;
    }

    /**
     * Submit a requisition draft.
     */
    public function submitRequisition(PharmacyRequisition $requisition, User $user): PharmacyRequisition
    {
        if ($requisition->status !== 'DRAFT') {
            throw ValidationException::withMessages([
                'status' => 'Only DRAFT requisitions can be submitted.',
            ]);
        }

        if ((int) $requisition->created_by !== (int) $user->id) {
            throw ValidationException::withMessages([
                'created_by' => 'Only the creator can submit this requisition.',
            ]);
        }

        if ($requisition->items()->count() === 0) {
            throw ValidationException::withMessages([
                'items' => 'Requisition must have at least one item.',
            ]);
        }

        DB::transaction(function () use ($requisition) {
            $requisition->update([
                'status' => 'SUBMITTED',
                'submitted_at' => now(),
                'current_step_order' => 1,
            ]);

            $this->seedRequisitionWorkflowSteps($requisition);
        });

        // Notify OC Pharmacy
        $this->notifyNextStepUsers($requisition, 'requisition');

        return $requisition->fresh(['steps', 'items.drug', 'createdBy', 'command']);
    }

    /**
     * Act on the current requisition step.
     */
    public function actOnRequisition(PharmacyRequisition $requisition, User $user, string $decision, ?string $comment = null): PharmacyRequisition
    {
        $currentOrder = $requisition->current_step_order;
        if (!$currentOrder) {
            throw ValidationException::withMessages([
                'current_step_order' => 'This requisition has no active step.',
            ]);
        }

        $step = $requisition->steps()->where('step_order', $currentOrder)->first();
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

        $validDecisions = $step->getValidDecisions();
        if (!in_array($decision, $validDecisions, true)) {
            throw ValidationException::withMessages([
                'decision' => "Decision {$decision} is not valid for action {$step->action}.",
            ]);
        }

        DB::transaction(function () use ($requisition, $step, $user, $decision, $comment) {
            $step->update([
                'acted_by_user_id' => $user->id,
                'acted_at' => now(),
                'decision' => $decision,
                'comment' => $comment,
            ]);

            $nextOrder = $step->step_order + 1;
            $hasNextStep = $requisition->steps()->where('step_order', $nextOrder)->exists();

            if ($decision === 'REJECTED') {
                $requisition->update([
                    'status' => 'REJECTED',
                    'current_step_order' => null,
                ]);
            } elseif ($decision === 'APPROVED' && $step->action === 'APPROVE') {
                $requisition->update([
                    'status' => 'APPROVED',
                    'approved_at' => now(),
                    'current_step_order' => $hasNextStep ? $nextOrder : null,
                ]);
            } else {
                $requisition->update([
                    'current_step_order' => $hasNextStep ? $nextOrder : null,
                ]);
            }
        });

        $this->notifyNextStepUsers($requisition, 'requisition');
        $this->notifyCreator($requisition, 'requisition', "Requisition #{$requisition->id} - {$decision}");

        return $requisition->fresh(['steps', 'items.drug', 'createdBy', 'command']);
    }

    /**
     * Issue requisition items from Central Medical Store.
     */
    public function issueRequisition(PharmacyRequisition $requisition, User $user, array $issuedItems, ?string $comment = null): PharmacyRequisition
    {
        if (!$user->hasRole('Central Medical Store')) {
            throw ValidationException::withMessages([
                'role' => 'Only Central Medical Store can issue requisitions.',
            ]);
        }

        if ($requisition->status !== 'APPROVED') {
            throw ValidationException::withMessages([
                'status' => 'Only APPROVED requisitions can be issued.',
            ]);
        }

        return DB::transaction(function () use ($requisition, $user, $issuedItems, $comment) {
            foreach ($issuedItems as $itemData) {
                $item = $requisition->items()->find($itemData['id']);
                if (!$item) {
                    continue;
                }

                $quantityIssued = (int) ($itemData['quantity_issued'] ?? 0);
                if ($quantityIssued <= 0) {
                    continue;
                }

                // Check available stock at Central Store
                $centralStock = PharmacyStock::where('pharmacy_drug_id', $item->pharmacy_drug_id)
                    ->where('location_type', 'CENTRAL_STORE')
                    ->whereNull('command_id')
                    ->where('quantity', '>', 0)
                    ->orderBy('expiry_date') // FEFO - First Expiry First Out
                    ->get();

                $remainingToIssue = $quantityIssued;
                foreach ($centralStock as $stock) {
                    if ($remainingToIssue <= 0) {
                        break;
                    }

                    $issueFromStock = min($stock->quantity, $remainingToIssue);
                    $stock->decrement('quantity', $issueFromStock);
                    $remainingToIssue -= $issueFromStock;

                    // Record stock movement (out of central store)
                    PharmacyStockMovement::create([
                        'pharmacy_drug_id' => $item->pharmacy_drug_id,
                        'movement_type' => 'REQUISITION_ISSUE',
                        'reference_id' => $requisition->id,
                        'reference_type' => PharmacyRequisition::class,
                        'location_type' => 'CENTRAL_STORE',
                        'command_id' => null,
                        'quantity' => -$issueFromStock,
                        'expiry_date' => $stock->expiry_date,
                        'batch_number' => $stock->batch_number,
                        'notes' => $comment,
                        'created_by' => $user->id,
                    ]);

                    // Add to command pharmacy stock
                    $commandStock = PharmacyStock::firstOrCreate([
                        'pharmacy_drug_id' => $item->pharmacy_drug_id,
                        'location_type' => 'COMMAND_PHARMACY',
                        'command_id' => $requisition->command_id,
                        'batch_number' => $stock->batch_number,
                        'expiry_date' => $stock->expiry_date,
                    ], [
                        'quantity' => 0,
                    ]);

                    $commandStock->increment('quantity', $issueFromStock);

                    // Record stock movement (into command pharmacy)
                    PharmacyStockMovement::create([
                        'pharmacy_drug_id' => $item->pharmacy_drug_id,
                        'movement_type' => 'REQUISITION_ISSUE',
                        'reference_id' => $requisition->id,
                        'reference_type' => PharmacyRequisition::class,
                        'location_type' => 'COMMAND_PHARMACY',
                        'command_id' => $requisition->command_id,
                        'quantity' => $issueFromStock,
                        'expiry_date' => $stock->expiry_date,
                        'batch_number' => $stock->batch_number,
                        'notes' => $comment,
                        'created_by' => $user->id,
                    ]);
                }

                $actualIssued = $quantityIssued - $remainingToIssue;
                $item->update(['quantity_issued' => $actualIssued]);
            }

            // Mark current step as completed
            $currentStep = $requisition->getCurrentStep();
            if ($currentStep) {
                $currentStep->update([
                    'acted_by_user_id' => $user->id,
                    'acted_at' => now(),
                    'decision' => 'REVIEWED',
                    'comment' => $comment,
                ]);
            }

            $requisition->update([
                'status' => 'ISSUED',
                'issued_at' => now(),
                'current_step_order' => null,
            ]);

            $this->notifyCreator($requisition, 'requisition', 'Requisition items issued from Central Medical Store');

            return $requisition->fresh(['steps', 'items.drug', 'createdBy', 'command']);
        });
    }

    /**
     * Command Pharmacist dispenses drugs to patients.
     */
    public function dispenseFromRequisition(PharmacyRequisition $requisition, User $user, array $dispensedItems, ?string $comment = null): PharmacyRequisition
    {
        $commandId = $this->getActiveCommandIdForRole($user, 'Command Pharmacist');
        if (!$commandId || $commandId !== $requisition->command_id) {
            throw ValidationException::withMessages([
                'command' => 'You can only dispense from your command pharmacy.',
            ]);
        }

        if ($requisition->status !== 'ISSUED') {
            throw ValidationException::withMessages([
                'status' => 'Only ISSUED requisitions can be dispensed.',
            ]);
        }

        return DB::transaction(function () use ($requisition, $user, $dispensedItems, $comment, $commandId) {
            foreach ($dispensedItems as $itemData) {
                $item = $requisition->items()->find($itemData['id']);
                if (!$item) {
                    continue;
                }

                $quantityDispensed = (int) ($itemData['quantity_dispensed'] ?? 0);
                if ($quantityDispensed <= 0) {
                    continue;
                }

                // Deduct from command pharmacy stock
                $commandStock = PharmacyStock::where('pharmacy_drug_id', $item->pharmacy_drug_id)
                    ->where('location_type', 'COMMAND_PHARMACY')
                    ->where('command_id', $commandId)
                    ->where('quantity', '>', 0)
                    ->orderBy('expiry_date') // FEFO
                    ->get();

                $remainingToDispense = $quantityDispensed;
                foreach ($commandStock as $stock) {
                    if ($remainingToDispense <= 0) {
                        break;
                    }

                    $dispenseFromStock = min($stock->quantity, $remainingToDispense);
                    $stock->decrement('quantity', $dispenseFromStock);
                    $remainingToDispense -= $dispenseFromStock;

                    // Record stock movement
                    PharmacyStockMovement::create([
                        'pharmacy_drug_id' => $item->pharmacy_drug_id,
                        'movement_type' => 'DISPENSED',
                        'reference_id' => $requisition->id,
                        'reference_type' => PharmacyRequisition::class,
                        'location_type' => 'COMMAND_PHARMACY',
                        'command_id' => $commandId,
                        'quantity' => -$dispenseFromStock,
                        'expiry_date' => $stock->expiry_date,
                        'batch_number' => $stock->batch_number,
                        'notes' => $comment,
                        'created_by' => $user->id,
                    ]);
                }
            }

            $requisition->update([
                'status' => 'DISPENSED',
                'dispensed_at' => now(),
            ]);

            // Notify OC Pharmacy about dispensed requisition
            $this->notifyByRole('OC Pharmacy', 'requisition', $requisition, 'Requisition dispensed at ' . ($requisition->command?->name ?? 'Command Pharmacy'));

            return $requisition->fresh(['steps', 'items.drug', 'createdBy', 'command']);
        });
    }

    /**
     * Seed workflow steps for requisition.
     */
    private function seedRequisitionWorkflowSteps(PharmacyRequisition $requisition): void
    {
        $steps = [
            ['step_order' => 1, 'role_name' => 'OC Pharmacy', 'action' => 'APPROVE'],
            ['step_order' => 2, 'role_name' => 'Central Medical Store', 'action' => 'REVIEW'],
        ];

        foreach ($steps as $step) {
            PharmacyWorkflowStep::create([
                'pharmacy_requisition_id' => $requisition->id,
                ...$step,
            ]);
        }
    }

    // ============================================================
    // NOTIFICATIONS
    // ============================================================

    /**
     * Notify users at the next step.
     */
    private function notifyNextStepUsers($entity, string $type): void
    {
        $nextOrder = $entity->current_step_order;
        if (!$nextOrder) {
            return;
        }

        $step = $entity->steps()->where('step_order', $nextOrder)->first();
        if (!$step) {
            return;
        }

        $roleName = $step->role_name;
        $notificationService = app(NotificationService::class);

        $query = User::whereHas('roles', function ($q) use ($roleName) {
            $q->where('name', $roleName)
                ->where('user_roles.is_active', true);
        })->where('is_active', true);

        $entityType = $type === 'procurement' ? 'Procurement' : 'Requisition';
        $title = "{$entityType} #{$entity->id} awaiting action";
        $message = "A pharmacy {$type} is waiting at Step {$nextOrder} ({$roleName}).";

        foreach ($query->get() as $recipient) {
            $notificationService->notify(
                $recipient,
                "pharmacy_{$type}_pending",
                $title,
                $message,
                "pharmacy_{$type}",
                $entity->id,
                true
            );
        }
    }

    /**
     * Notify the creator of the entity.
     */
    private function notifyCreator($entity, string $type, string $message): void
    {
        if (!$entity->created_by) {
            return;
        }

        $notificationService = app(NotificationService::class);
        $creator = User::find($entity->created_by);
        
        if ($creator) {
            $entityType = $type === 'procurement' ? 'Procurement' : 'Requisition';
            $notificationService->notify(
                $creator,
                "pharmacy_{$type}_update",
                "{$entityType} #{$entity->id} Update",
                $message,
                "pharmacy_{$type}",
                $entity->id,
                true
            );
        }
    }

    /**
     * Notify users by role about pharmacy events.
     */
    private function notifyByRole(string $roleName, string $type, $entity, string $message): void
    {
        $notificationService = app(NotificationService::class);
        $entityType = $type === 'procurement' ? 'Procurement' : 'Requisition';
        
        $notificationService->notifyByRole(
            $roleName,
            "pharmacy_{$type}_update",
            "{$entityType} #{$entity->id} Update",
            $message,
            "pharmacy_{$type}",
            $entity->id,
            true
        );
    }
}
