<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FleetRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_type',
        'status',
        'origin_command_id',
        'target_command_id',
        'requested_vehicle_type',
        'requested_make',
        'requested_model',
        'requested_year',
        'requested_quantity',
        'created_by',
        'submitted_at',
        'current_step_order',
    ];

    protected function casts(): array
    {
        return [
            'requested_year' => 'integer',
            'requested_quantity' => 'integer',
            'submitted_at' => 'datetime',
            'current_step_order' => 'integer',
        ];
    }

    public function originCommand()
    {
        return $this->belongsTo(Command::class, 'origin_command_id');
    }

    public function targetCommand()
    {
        return $this->belongsTo(Command::class, 'target_command_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function steps()
    {
        return $this->hasMany(FleetRequestStep::class, 'fleet_request_id')->orderBy('step_order');
    }

    public function fulfillment()
    {
        return $this->hasOne(FleetRequestFulfillment::class, 'fleet_request_id');
    }

    public function reservedVehicles()
    {
        return $this->hasMany(FleetVehicle::class, 'reserved_fleet_request_id');
    }
}

