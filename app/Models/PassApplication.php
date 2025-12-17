<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PassApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'officer_id',
        'start_date',
        'end_date',
        'number_of_days',
        'reason',
        'status',
        'expiry_alert_sent',
        'minuted_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'submitted_at' => 'datetime',
            'minuted_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'number_of_days' => 'integer',
            'expiry_alert_sent' => 'boolean',
        ];
    }

    // Relationships
    public function officer()
    {
        return $this->belongsTo(Officer::class);
    }

    public function approval()
    {
        return $this->hasOne(PassApproval::class);
    }
}

