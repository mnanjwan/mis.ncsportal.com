<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RosterAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'roster_id',
        'officer_id',
        'duty_date',
        'shift',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'duty_date' => 'date',
        ];
    }

    // Relationships
    public function roster()
    {
        return $this->belongsTo(DutyRoster::class, 'roster_id');
    }

    public function officer()
    {
        return $this->belongsTo(Officer::class);
    }
}

