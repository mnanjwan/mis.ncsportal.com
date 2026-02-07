<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FleetVehicleModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'make',
        'vehicle_type',
        'year_of_manufacture',
    ];

    protected function casts(): array
    {
        return [
            'year_of_manufacture' => 'integer',
        ];
    }

    /**
     * Get the display name for the vehicle model
     * Format: "Make VehicleType Year" (e.g., "Toyota PickUp 2018")
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->make} {$this->vehicle_type} {$this->year_of_manufacture}";
    }

    /**
     * Relationship: Vehicles using this model
     */
    public function vehicles()
    {
        return $this->hasMany(FleetVehicle::class, 'vehicle_model_id');
    }

    /**
     * Scope: Search by make, type, or year
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('make', 'like', "%{$search}%")
              ->orWhere('vehicle_type', 'like', "%{$search}%")
              ->orWhere('year_of_manufacture', 'like', "%{$search}%");
        });
    }
}
