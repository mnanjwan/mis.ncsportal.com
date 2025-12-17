<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'officer_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'number_of_days',
        'reason',
        'expected_date_of_delivery',
        'medical_certificate_url',
        'status',
        'rejection_reason',
        'alert_sent_72h',
        'minuted_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'expected_date_of_delivery' => 'date',
            'submitted_at' => 'datetime',
            'minuted_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'number_of_days' => 'integer',
            'alert_sent_72h' => 'boolean',
        ];
    }

    // Relationships
    public function officer()
    {
        return $this->belongsTo(Officer::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approval()
    {
        return $this->hasOne(LeaveApproval::class);
    }
}

