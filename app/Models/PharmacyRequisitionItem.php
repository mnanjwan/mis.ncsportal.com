<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmacyRequisitionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_requisition_id',
        'pharmacy_drug_id',
        'quantity_requested',
        'quantity_issued',
        'quantity_dispensed',
    ];

    protected function casts(): array
    {
        return [
            'quantity_requested' => 'integer',
            'quantity_issued' => 'integer',
            'quantity_dispensed' => 'integer',
        ];
    }

    // Relationships
    public function requisition()
    {
        return $this->belongsTo(PharmacyRequisition::class, 'pharmacy_requisition_id');
    }

    public function drug()
    {
        return $this->belongsTo(PharmacyDrug::class, 'pharmacy_drug_id');
    }

    // Helper methods
    public function isFullyIssued(): bool
    {
        return $this->quantity_issued >= $this->quantity_requested;
    }

    public function getPendingQuantity(): int
    {
        return max(0, $this->quantity_requested - $this->quantity_issued);
    }

    /** Quantity still to dispense (issued minus already dispensed). */
    public function getRemainingToDispense(): int
    {
        return max(0, ($this->quantity_issued ?? 0) - ($this->quantity_dispensed ?? 0));
    }

    /** Whether this item has any quantity left to dispense. */
    public function hasRemainingToDispense(): bool
    {
        return $this->getRemainingToDispense() > 0;
    }
}
