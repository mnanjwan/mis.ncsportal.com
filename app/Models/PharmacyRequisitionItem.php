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
    ];

    protected function casts(): array
    {
        return [
            'quantity_requested' => 'integer',
            'quantity_issued' => 'integer',
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
}
