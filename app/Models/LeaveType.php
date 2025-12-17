<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'max_duration_days',
        'max_duration_months',
        'max_occurrences_per_year',
        'requires_medical_certificate',
        'requires_approval_level',
        'is_active',
        'description',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'max_duration_days' => 'integer',
            'max_duration_months' => 'integer',
            'max_occurrences_per_year' => 'integer',
            'requires_medical_certificate' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function leaveApplications()
    {
        return $this->hasMany(LeaveApplication::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

