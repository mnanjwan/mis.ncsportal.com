<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetirementAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'officer_id',
        'retirement_date',
        'retirement_type',
        'alert_date',
        'alert_sent',
        'alert_sent_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'retirement_date' => 'date',
            'alert_date' => 'date',
            'alert_sent' => 'boolean',
            'alert_sent_at' => 'datetime',
        ];
    }

    // Relationships
    public function officer()
    {
        return $this->belongsTo(Officer::class);
    }
}
