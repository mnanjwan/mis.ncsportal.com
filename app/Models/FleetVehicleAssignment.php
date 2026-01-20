<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FleetVehicleAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'fleet_vehicle_id',
        'assigned_to_command_id',
        'assigned_to_officer_id',
        'assigned_by_user_id',
        'assigned_at',
        'released_by_user_id',
        'released_at',
        'received_by_user_id',
        'received_at',
        'ended_at',
        'end_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'released_at' => 'datetime',
            'received_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function vehicle()
    {
        return $this->belongsTo(FleetVehicle::class, 'fleet_vehicle_id');
    }

    public function assignedToCommand()
    {
        return $this->belongsTo(Command::class, 'assigned_to_command_id');
    }

    public function assignedToOfficer()
    {
        return $this->belongsTo(Officer::class, 'assigned_to_officer_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public function releasedBy()
    {
        return $this->belongsTo(User::class, 'released_by_user_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    public function returnRecord()
    {
        return $this->hasOne(FleetVehicleReturn::class, 'fleet_vehicle_assignment_id');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('ended_at');
    }
}

