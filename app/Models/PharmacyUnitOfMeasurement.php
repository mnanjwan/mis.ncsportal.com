<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmacyUnitOfMeasurement extends Model
{
    use HasFactory;

    protected $table = 'pharmacy_units_of_measurement';

    protected $fillable = ['name'];

    /**
     * Get all unit names for dropdowns (ordered).
     */
    public static function names(): array
    {
        return static::orderBy('name')->pluck('name')->values()->toArray();
    }
}
