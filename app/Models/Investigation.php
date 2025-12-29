<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Investigation extends Model
{
    protected $fillable = [
        'officer_id',
        'investigation_officer_id',
        'invitation_message',
        'status',
        'notes',
        'invited_at',
        'status_changed_at',
        'resolved_at',
        'resolution_notes',
    ];

    protected function casts(): array
    {
        return [
            'invited_at' => 'datetime',
            'status_changed_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Get the officer being investigated
     */
    public function officer(): BelongsTo
    {
        return $this->belongsTo(Officer::class);
    }

    /**
     * Get the investigation officer (user who initiated/manages the investigation)
     */
    public function investigationOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'investigation_officer_id');
    }
}
