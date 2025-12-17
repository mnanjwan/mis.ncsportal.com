<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficerCourse extends Model
{
    use HasFactory;

    protected $fillable = [
        'officer_id',
        'course_name',
        'course_type',
        'start_date',
        'end_date',
        'is_completed',
        'completion_date',
        'certificate_url',
        'nominated_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'completion_date' => 'date',
            'is_completed' => 'boolean',
        ];
    }

    // Relationships
    public function officer()
    {
        return $this->belongsTo(Officer::class);
    }

    public function nominatedBy()
    {
        return $this->belongsTo(User::class, 'nominated_by');
    }
}

