<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmacyReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_return_id',
        'pharmacy_drug_id',
        'quantity',
        'batch_number',
        'expiry_date',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'quantity' => 'integer',
        ];
    }

    // Relationships
    public function pharmacyReturn()
    {
        return $this->belongsTo(PharmacyReturn::class);
    }

    public function drug()
    {
        return $this->belongsTo(PharmacyDrug::class, 'pharmacy_drug_id');
    }
}
