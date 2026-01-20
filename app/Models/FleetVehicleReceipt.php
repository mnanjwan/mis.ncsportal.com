<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FleetVehicleReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'fleet_vehicle_id',
        'command_id',
        'date_of_allocation',
        'received_by_user_id',
        'received_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_of_allocation' => 'date',
            'received_at' => 'datetime',
        ];
    }

    public function vehicle()
    {
        return $this->belongsTo(FleetVehicle::class, 'fleet_vehicle_id');
    }

    public function command()
    {
        return $this->belongsTo(Command::class, 'command_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }
}

