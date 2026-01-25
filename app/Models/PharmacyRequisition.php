<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmacyRequisition extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'reference_number',
        'command_id',
        'notes',
        'created_by',
        'submitted_at',
        'approved_at',
        'issued_at',
        'dispensed_at',
        'current_step_order',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'issued_at' => 'datetime',
            'dispensed_at' => 'datetime',
            'current_step_order' => 'integer',
        ];
    }

    // Relationships
    public function items()
    {
        return $this->hasMany(PharmacyRequisitionItem::class);
    }

    public function command()
    {
        return $this->belongsTo(Command::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function steps()
    {
        return $this->hasMany(PharmacyWorkflowStep::class)->orderBy('step_order');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCommand($query, $commandId)
    {
        return $query->where('command_id', $commandId);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'DRAFT');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'SUBMITTED');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'APPROVED');
    }

    // Helper methods
    public function isDraft(): bool
    {
        return $this->status === 'DRAFT';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'SUBMITTED';
    }

    public function isApproved(): bool
    {
        return $this->status === 'APPROVED';
    }

    public function isIssued(): bool
    {
        return $this->status === 'ISSUED';
    }

    public function isDispensed(): bool
    {
        return $this->status === 'DISPENSED';
    }

    public function isRejected(): bool
    {
        return $this->status === 'REJECTED';
    }

    public function getCurrentStep()
    {
        if (!$this->current_step_order) {
            return null;
        }
        return $this->steps()->where('step_order', $this->current_step_order)->first();
    }

    public function generateReferenceNumber(): string
    {
        $year = now()->format('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;
        return "REQ-{$year}-" . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}
