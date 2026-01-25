<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmacyWorkflowStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_procurement_id',
        'pharmacy_requisition_id',
        'step_order',
        'role_name',
        'action',
        'acted_by_user_id',
        'acted_at',
        'decision',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'step_order' => 'integer',
            'acted_at' => 'datetime',
        ];
    }

    // Relationships
    public function procurement()
    {
        return $this->belongsTo(PharmacyProcurement::class, 'pharmacy_procurement_id');
    }

    public function requisition()
    {
        return $this->belongsTo(PharmacyRequisition::class, 'pharmacy_requisition_id');
    }

    public function actedBy()
    {
        return $this->belongsTo(User::class, 'acted_by_user_id');
    }

    // Scopes
    public function scopeByRole($query, $roleName)
    {
        return $query->where('role_name', $roleName);
    }

    public function scopePending($query)
    {
        return $query->whereNull('acted_at');
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('acted_at');
    }

    // Helper methods
    public function isPending(): bool
    {
        return is_null($this->acted_at);
    }

    public function isCompleted(): bool
    {
        return !is_null($this->acted_at);
    }

    public function isApproved(): bool
    {
        return $this->decision === 'APPROVED';
    }

    public function isRejected(): bool
    {
        return $this->decision === 'REJECTED';
    }

    public function isForwarded(): bool
    {
        return $this->decision === 'FORWARDED';
    }

    public function isReviewed(): bool
    {
        return $this->decision === 'REVIEWED';
    }

    public function getActionLabel(): string
    {
        return match ($this->action) {
            'FORWARD' => 'Forward',
            'APPROVE' => 'Approve/Reject',
            'REVIEW' => 'Review',
            default => $this->action,
        };
    }

    public function getDecisionLabel(): string
    {
        return match ($this->decision) {
            'FORWARDED' => 'Forwarded',
            'APPROVED' => 'Approved',
            'REJECTED' => 'Rejected',
            'REVIEWED' => 'Reviewed',
            null => 'Pending',
            default => $this->decision,
        };
    }

    public function getValidDecisions(): array
    {
        return match ($this->action) {
            'FORWARD' => ['FORWARDED'],
            'APPROVE' => ['APPROVED', 'REJECTED'],
            'REVIEW' => ['REVIEWED'],
            default => [],
        };
    }
}
