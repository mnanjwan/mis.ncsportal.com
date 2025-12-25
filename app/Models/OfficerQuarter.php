<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficerQuarter extends Model
{
    use HasFactory;

    protected $fillable = [
        'officer_id',
        'quarter_id',
        'allocated_date',
        'deallocated_date',
        'is_current',
        'status',
        'rejection_reason',
        'accepted_at',
        'rejected_at',
        'allocated_by',
        'request_id',
    ];

    protected function casts(): array
    {
        return [
            'allocated_date' => 'date',
            'deallocated_date' => 'date',
            'is_current' => 'boolean',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    // Helper methods
    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'ACCEPTED';
    }

    public function isRejected(): bool
    {
        return $this->status === 'REJECTED';
    }

    // Relationships
    public function officer()
    {
        return $this->belongsTo(Officer::class);
    }

    public function quarter()
    {
        return $this->belongsTo(Quarter::class);
    }

    public function allocatedBy()
    {
        return $this->belongsTo(User::class, 'allocated_by');
    }

    public function request()
    {
        return $this->belongsTo(QuarterRequest::class, 'request_id');
    }
}

