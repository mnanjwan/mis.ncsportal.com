<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FleetRequestFulfillment extends Model
{
    use HasFactory;

    protected $fillable = [
        'fleet_request_id',
        'fulfilled_quantity',
        'kiv_quantity',
        'fulfilled_by_user_id',
        'fulfilled_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'fulfilled_quantity' => 'integer',
            'kiv_quantity' => 'integer',
            'fulfilled_at' => 'datetime',
        ];
    }

    public function request()
    {
        return $this->belongsTo(FleetRequest::class, 'fleet_request_id');
    }

    public function fulfilledBy()
    {
        return $this->belongsTo(User::class, 'fulfilled_by_user_id');
    }
}

