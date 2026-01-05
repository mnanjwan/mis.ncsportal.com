<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManningRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'command_id',
        'requested_by',
        'status',
        'notes',
        'approved_by',
        'submitted_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'forwarded_to_hrd_at' => 'datetime',
            'fulfilled_at' => 'datetime',
        ];
    }

    // Relationships
    public function command()
    {
        return $this->belongsTo(Command::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(Officer::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(ManningRequestItem::class);
    }

    public function movementOrders()
    {
        return $this->hasMany(MovementOrder::class);
    }

    public function deploymentAssignments()
    {
        return $this->hasMany(ManningDeploymentAssignment::class);
    }
}

