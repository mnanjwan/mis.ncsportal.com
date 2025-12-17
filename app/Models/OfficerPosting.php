<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficerPosting extends Model
{
    use HasFactory;

    protected $fillable = [
        'officer_id',
        'command_id',
        'staff_order_id',
        'movement_order_id',
        'posting_date',
        'is_current',
        'documented_by',
    ];

    protected function casts(): array
    {
        return [
            'posting_date' => 'date',
            'documented_at' => 'datetime',
            'is_current' => 'boolean',
        ];
    }

    // Relationships
    public function officer()
    {
        return $this->belongsTo(Officer::class);
    }

    public function command()
    {
        return $this->belongsTo(Command::class);
    }

    public function staffOrder()
    {
        return $this->belongsTo(StaffOrder::class);
    }

    public function movementOrder()
    {
        return $this->belongsTo(MovementOrder::class);
    }

    public function documentedBy()
    {
        return $this->belongsTo(User::class, 'documented_by');
    }
}

