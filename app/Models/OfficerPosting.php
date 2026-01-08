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
        'documented_at',
        'released_by',
        'released_at',
        'release_letter_printed',
        'release_letter_printed_at',
        'release_letter_printed_by',
        'accepted_by_new_command',
        'accepted_at',
        'accepted_by',
    ];

    protected function casts(): array
    {
        return [
            'posting_date' => 'date',
            'documented_at' => 'datetime',
            'released_at' => 'datetime',
            'is_current' => 'boolean',
            'release_letter_printed' => 'boolean',
            'release_letter_printed_at' => 'datetime',
            'accepted_by_new_command' => 'boolean',
            'accepted_at' => 'datetime',
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

    public function releasedBy()
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    public function releaseLetterPrintedBy()
    {
        return $this->belongsTo(User::class, 'release_letter_printed_by');
    }

    public function acceptedBy()
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    /**
     * Check if transfer is complete (both release letter printed and accepted)
     */
    public function isTransferComplete(): bool
    {
        return $this->release_letter_printed && $this->accepted_by_new_command;
    }

    /**
     * Get the from command (officer's current command before this posting)
     */
    public function getFromCommandAttribute()
    {
        if (!$this->officer) {
            return null;
        }
        
        // Get the previous current posting
        $previousPosting = OfficerPosting::where('officer_id', $this->officer_id)
            ->where('id', '!=', $this->id)
            ->where('is_current', true)
            ->first();
            
        return $previousPosting ? $previousPosting->command : null;
    }
}

