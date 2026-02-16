<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyExpiredDrugRecord extends Model
{
    protected $fillable = [
        'pharmacy_drug_id',
        'location_type',
        'command_id',
        'quantity',
        'expiry_date',
        'batch_number',
        'moved_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'expiry_date' => 'date',
            'moved_at' => 'datetime',
        ];
    }

    public function drug(): BelongsTo
    {
        return $this->belongsTo(PharmacyDrug::class, 'pharmacy_drug_id');
    }

    public function command(): BelongsTo
    {
        return $this->belongsTo(Command::class);
    }
}
