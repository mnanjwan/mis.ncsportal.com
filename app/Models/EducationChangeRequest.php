<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EducationChangeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'officer_id',
        'university',
        'qualification',
        'discipline',
        'year_obtained',
        'status',
        'rejection_reason',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'year_obtained' => 'integer',
            'verified_at' => 'datetime',
        ];
    }

    public function officer()
    {
        return $this->belongsTo(Officer::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'APPROVED');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'REJECTED');
    }
}

