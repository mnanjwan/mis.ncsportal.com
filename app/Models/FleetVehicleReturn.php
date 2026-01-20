<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FleetVehicleReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'fleet_vehicle_assignment_id',
        'returned_by_officer_id',
        'received_by_user_id',
        'returned_at',
        'condition_notes',
    ];

    protected function casts(): array
    {
        return [
            'returned_at' => 'datetime',
        ];
    }

    public function assignment()
    {
        return $this->belongsTo(FleetVehicleAssignment::class, 'fleet_vehicle_assignment_id');
    }

    public function returnedByOfficer()
    {
        return $this->belongsTo(Officer::class, 'returned_by_officer_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }
}

