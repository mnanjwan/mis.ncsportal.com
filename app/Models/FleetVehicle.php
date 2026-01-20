<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FleetVehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'make',
        'model',
        'year_of_manufacture',
        'vehicle_type',
        'reg_no',
        'chassis_number',
        'engine_number',
        'service_status',
        'lifecycle_status',
        'current_command_id',
        'current_officer_id',
        'reserved_fleet_request_id',
        'reserved_by_user_id',
        'reserved_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'year_of_manufacture' => 'integer',
            'reserved_at' => 'datetime',
        ];
    }

    public function currentCommand()
    {
        return $this->belongsTo(Command::class, 'current_command_id');
    }

    public function currentOfficer()
    {
        return $this->belongsTo(Officer::class, 'current_officer_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reservedForRequest()
    {
        return $this->belongsTo(FleetRequest::class, 'reserved_fleet_request_id');
    }

    public function reservedBy()
    {
        return $this->belongsTo(User::class, 'reserved_by_user_id');
    }

    public function receipts()
    {
        return $this->hasMany(FleetVehicleReceipt::class, 'fleet_vehicle_id');
    }

    public function assignments()
    {
        return $this->hasMany(FleetVehicleAssignment::class, 'fleet_vehicle_id');
    }

    public function returns()
    {
        return $this->hasManyThrough(
            FleetVehicleReturn::class,
            FleetVehicleAssignment::class,
            'fleet_vehicle_id',
            'fleet_vehicle_assignment_id'
        );
    }

    public function audits()
    {
        return $this->hasMany(FleetVehicleAudit::class, 'fleet_vehicle_id');
    }
}

