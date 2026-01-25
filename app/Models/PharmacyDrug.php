<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmacyDrug extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'unit_of_measure',
        'category',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function procurementItems()
    {
        return $this->hasMany(PharmacyProcurementItem::class);
    }

    public function requisitionItems()
    {
        return $this->hasMany(PharmacyRequisitionItem::class);
    }

    public function stocks()
    {
        return $this->hasMany(PharmacyStock::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(PharmacyStockMovement::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Helper methods
    public function getCentralStoreStock()
    {
        return $this->stocks()
            ->where('location_type', 'CENTRAL_STORE')
            ->sum('quantity');
    }

    public function getCommandStock($commandId)
    {
        return $this->stocks()
            ->where('location_type', 'COMMAND_PHARMACY')
            ->where('command_id', $commandId)
            ->sum('quantity');
    }

    public function getTotalStock()
    {
        return $this->stocks()->sum('quantity');
    }
}
