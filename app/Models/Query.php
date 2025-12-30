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
        'response_deadline',
        'responded_at',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'response_deadline' => 'datetime',
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

    /**
     * Check if the query deadline has passed or reached
     */
    public function isExpired(): bool
    {
        if (!$this->response_deadline) {
            return false;
        }
        // Check if current time is at or after the deadline (prevents response at exact deadline time)
        return now()->greaterThanOrEqualTo($this->response_deadline);
    }

    /**
     * Check if query is overdue (pending response and expired)
     */
    public function isOverdue(): bool
    {
        return $this->isPendingResponse() && $this->isExpired();
    }

    /**
     * Check if query can still accept responses (deadline not reached)
     */
    public function canAcceptResponse(): bool
    {
        if (!$this->isPendingResponse()) {
            return false;
        }
        
        if (!$this->response_deadline) {
            return true; // No deadline set, can always respond
        }
        
        // Check if current time is before the deadline
        return now()->lessThan($this->response_deadline);
    }

    /**
     * Get days remaining until deadline
     */
    public function daysUntilDeadline(): ?int
    {
        if (!$this->response_deadline) {
            return null;
        }
        return now()->diffInDays($this->response_deadline, false);
    }

    /**
     * Get hours remaining until deadline
     */
    public function hoursUntilDeadline(): ?int
    {
        if (!$this->response_deadline) {
            return null;
        }
        return now()->diffInHours($this->response_deadline, false);
    }
}
