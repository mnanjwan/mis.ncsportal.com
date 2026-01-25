<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmacyStockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_drug_id',
        'movement_type',
        'reference_id',
        'reference_type',
        'location_type',
        'command_id',
        'quantity',
        'expiry_date',
        'batch_number',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'expiry_date' => 'date',
        ];
    }

    // Relationships
    public function drug()
    {
        return $this->belongsTo(PharmacyDrug::class, 'pharmacy_drug_id');
    }

    public function command()
    {
        return $this->belongsTo(Command::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reference()
    {
        return $this->morphTo('reference', 'reference_type', 'reference_id');
    }

    // Scopes
    public function scopeByMovementType($query, $type)
    {
        return $query->where('movement_type', $type);
    }

    public function scopeCentralStore($query)
    {
        return $query->where('location_type', 'CENTRAL_STORE');
    }

    public function scopeCommandPharmacy($query)
    {
        return $query->where('location_type', 'COMMAND_PHARMACY');
    }

    public function scopeByCommand($query, $commandId)
    {
        return $query->where('command_id', $commandId);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Helper methods
    public function isAddition(): bool
    {
        return $this->quantity > 0;
    }

    public function isSubtraction(): bool
    {
        return $this->quantity < 0;
    }

    public function getMovementTypeLabel(): string
    {
        return match ($this->movement_type) {
            'PROCUREMENT_RECEIPT' => 'Procurement Receipt',
            'REQUISITION_ISSUE' => 'Requisition Issue',
            'ADJUSTMENT' => 'Stock Adjustment',
            'DISPENSED' => 'Dispensed',
            default => $this->movement_type,
        };
    }

    public function getLocationName(): string
    {
        if ($this->location_type === 'CENTRAL_STORE') {
            return 'Central Medical Store';
        }
        return $this->command?->name ?? 'Command Pharmacy';
    }
}
