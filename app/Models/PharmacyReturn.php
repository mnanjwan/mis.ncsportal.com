<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmacyReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'command_id',
        'status',
        'created_by',
        'current_step_order',
        'notes',
        'submitted_at',
        'approved_at',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    // Relationships
    public function command()
    {
        return $this->belongsTo(Command::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(PharmacyReturnItem::class);
    }

    public function steps()
    {
        return $this->hasMany(PharmacyWorkflowStep::class, 'pharmacy_return_id');
    }

    // Helper methods
    public function generateReferenceNumber(): string
    {
        $prefix = 'RET-' . date('Ymd') . '-';
        $lastReturn = self::where('reference_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        $sequence = 1;
        if ($lastReturn && preg_match('/-(\d+)$/', $lastReturn->reference_number, $matches)) {
            $sequence = (int)$matches[1] + 1;
        }

        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function getCurrentStep()
    {
        if (!$this->current_step_order) {
            return null;
        }
        return $this->steps()->where('step_order', $this->current_step_order)->first();
    }
}
