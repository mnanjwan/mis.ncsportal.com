<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmacyProcurementItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_procurement_id',
        'pharmacy_drug_id',
        'drug_name',
        'unit_of_measure',
        'quantity_requested',
        'quantity_received',
        'expiry_date',
        'batch_number',
    ];

    protected function casts(): array
    {
        return [
            'quantity_requested' => 'integer',
            'quantity_received' => 'integer',
            'expiry_date' => 'date',
        ];
    }

    // Relationships
    public function procurement()
    {
        return $this->belongsTo(PharmacyProcurement::class, 'pharmacy_procurement_id');
    }

    public function drug()
    {
        return $this->belongsTo(PharmacyDrug::class, 'pharmacy_drug_id');
    }

    // Helper methods
    public function isFullyReceived(): bool
    {
        return $this->quantity_received >= $this->quantity_requested;
    }

    public function getPendingQuantity(): int
    {
        return max(0, $this->quantity_requested - $this->quantity_received);
    }

    /**
     * Get the display name (from linked drug or direct drug_name field)
     */
    public function getDisplayName(): string
    {
        if ($this->drug) {
            return $this->drug->name;
        }
        return $this->drug_name ?? 'Unknown';
    }

    /**
     * Get the display unit (from linked drug or direct unit_of_measure field)
     */
    public function getDisplayUnit(): string
    {
        if ($this->drug) {
            return $this->drug->unit_of_measure;
        }
        return $this->unit_of_measure ?? 'units';
    }
}
