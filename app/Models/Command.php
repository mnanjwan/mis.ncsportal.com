<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Command extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'location',
        'zone_id',
        'area_controller_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function areaController()
    {
        return $this->belongsTo(Officer::class, 'area_controller_id');
    }

    public function officers()
    {
        return $this->hasMany(Officer::class, 'present_station');
    }

    public function manningRequests()
    {
        return $this->hasMany(ManningRequest::class);
    }

    public function chatRooms()
    {
        return $this->hasMany(ChatRoom::class);
    }

    public function quarters()
    {
        return $this->hasMany(Quarter::class);
    }

    public function dutyRosters()
    {
        return $this->hasMany(DutyRoster::class);
    }

    public function officerPostings()
    {
        return $this->hasMany(OfficerPosting::class);
    }

    public function staffOrdersFrom()
    {
        return $this->hasMany(StaffOrder::class, 'from_command_id');
    }

    public function staffOrdersTo()
    {
        return $this->hasMany(StaffOrder::class, 'to_command_id');
    }
}

