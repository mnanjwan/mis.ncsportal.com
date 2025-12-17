<?php

namespace App\Services;

use App\Models\MovementOrder;
use App\Models\Officer;
use App\Models\OfficerPosting;
use App\Models\StaffOrder;

class PostingService
{
    /**
     * Create staff order and update officer posting
     */
    public function createStaffOrder(int $officerId, int $toCommandId, string $effectiveDate, int $createdBy): StaffOrder
    {
        $officer = Officer::findOrFail($officerId);
        $fromCommandId = $officer->present_station;

        // Generate order number
        $orderNumber = 'SO/' . now()->year . '/' . str_pad(
            StaffOrder::whereYear('created_at', now()->year)->count() + 1,
            4,
            '0',
            STR_PAD_LEFT
        );

        $staffOrder = StaffOrder::create([
            'order_number' => $orderNumber,
            'officer_id' => $officerId,
            'from_command_id' => $fromCommandId,
            'to_command_id' => $toCommandId,
            'effective_date' => $effectiveDate,
            'order_type' => 'STAFF_ORDER',
            'created_by' => $createdBy,
        ]);

        // Create posting record
        OfficerPosting::create([
            'officer_id' => $officerId,
            'command_id' => $toCommandId,
            'staff_order_id' => $staffOrder->id,
            'posting_date' => $effectiveDate,
            'is_current' => true,
        ]);

        // Update previous posting
        if ($fromCommandId) {
            OfficerPosting::where('officer_id', $officerId)
                ->where('command_id', $fromCommandId)
                ->where('is_current', true)
                ->update(['is_current' => false]);
        }

        // Update officer's present station
        $officer->update([
            'present_station' => $toCommandId,
            'date_posted_to_station' => $effectiveDate,
        ]);

        return $staffOrder;
    }

    /**
     * Create movement order and update officer posting
     */
    public function createMovementOrder(int $officerId, int $toCommandId, string $effectiveDate, int $createdBy, ?string $reason = null): MovementOrder
    {
        $officer = Officer::findOrFail($officerId);
        $fromCommandId = $officer->present_station;

        // Generate order number
        $orderNumber = 'MO/' . now()->year . '/' . str_pad(
            MovementOrder::whereYear('created_at', now()->year)->count() + 1,
            4,
            '0',
            STR_PAD_LEFT
        );

        $movementOrder = MovementOrder::create([
            'order_number' => $orderNumber,
            'officer_id' => $officerId,
            'from_command_id' => $fromCommandId,
            'to_command_id' => $toCommandId,
            'effective_date' => $effectiveDate,
            'order_type' => 'MOVEMENT_ORDER',
            'reason' => $reason,
            'created_by' => $createdBy,
        ]);

        // Update officer's present station
        $officer->update([
            'present_station' => $toCommandId,
            'date_posted_to_station' => $effectiveDate,
        ]);

        return $movementOrder;
    }
}

