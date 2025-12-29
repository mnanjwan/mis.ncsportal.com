<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Query extends Model
{
    use HasFactory;

    protected $fillable = [
        'officer_id',
        'issued_by_user_id',
        'reason',
        'response',
        'status',
        'issued_at',
        'responded_at',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'responded_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    // Relationships
    public function officer()
    {
        return $this->belongsTo(Officer::class);
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by_user_id');
    }

    // Scopes
    public function scopePendingResponse($query)
    {
        return $query->where('status', 'PENDING_RESPONSE');
    }

    public function scopePendingReview($query)
    {
        return $query->where('status', 'PENDING_REVIEW');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'ACCEPTED');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'REJECTED');
    }

    // Helper methods
    public function isPendingResponse(): bool
    {
        return $this->status === 'PENDING_RESPONSE';
    }

    public function isPendingReview(): bool
    {
        return $this->status === 'PENDING_REVIEW';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'ACCEPTED';
    }

    public function isRejected(): bool
    {
        return $this->status === 'REJECTED';
    }
}
