<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DutyRoster extends Model
{
    use HasFactory;

    protected $fillable = [
        'command_id',
        'unit',
        'roster_period_start',
        'roster_period_end',
        'prepared_by',
        'approved_by',
        'status',
        'rejection_reason',
        'oic_officer_id',
        'second_in_command_officer_id',
        'cd_approved_at',
        'cd_approved_by',
    ];

    protected function casts(): array
    {
        return [
            'roster_period_start' => 'date',
            'roster_period_end' => 'date',
            'approved_at' => 'datetime',
            'cd_approved_at' => 'datetime',
        ];
    }

    // Relationships
    public function command()
    {
        return $this->belongsTo(Command::class);
    }

    public function preparedBy()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(Officer::class, 'approved_by');
    }

    public function assignments()
    {
        return $this->hasMany(RosterAssignment::class, 'roster_id');
    }

    public function oicOfficer()
    {
        return $this->belongsTo(Officer::class, 'oic_officer_id');
    }

    public function secondInCommandOfficer()
    {
        return $this->belongsTo(Officer::class, 'second_in_command_officer_id');
    }

    public function cdApprovedBy()
    {
        return $this->belongsTo(Officer::class, 'cd_approved_by');
    }

    /**
     * Check if roster includes any Transport officers or is a Transport-unit roster.
     * True when: roster unit is Transport/Transport and Logistics, or any assigned officer has unit Transport.
     */
    public function hasTransportOfficers(): bool
    {
        $transportUnits = ['Transport', 'Transport and Logistics'];
        if (in_array($this->unit, $transportUnits, true)) {
            return true;
        }
        return $this->assignments()
            ->whereHas('officer', fn ($q) => $q->where('unit', 'Transport'))
            ->exists();
    }
}

