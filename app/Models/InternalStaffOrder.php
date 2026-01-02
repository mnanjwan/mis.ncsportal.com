<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalStaffOrder extends Model
{
    use HasFactory;

    protected $table = 'internal_staff_orders';

    protected $fillable = [
        'command_id',
        'officer_id',
        'order_number',
        'order_date',
        'prepared_by',
        'description',
        'current_unit',
        'current_role',
        'target_unit',
        'target_role',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    // Relationships
    public function command()
    {
        return $this->belongsTo(Command::class);
    }

    public function officer()
    {
        return $this->belongsTo(Officer::class);
    }

    public function preparedBy()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

