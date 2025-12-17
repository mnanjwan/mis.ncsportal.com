<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emolument extends Model
{
    use HasFactory;

    protected $fillable = [
        'officer_id',
        'timeline_id',
        'year',
        'bank_name',
        'bank_account_number',
        'pfa_name',
        'rsa_pin',
        'status',
        'notes',
        'submitted_at',
        'assessed_at',
        'validated_at',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'assessed_at' => 'datetime',
            'validated_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    // Relationships
    public function officer()
    {
        return $this->belongsTo(Officer::class);
    }

    public function timeline()
    {
        return $this->belongsTo(EmolumentTimeline::class, 'timeline_id');
    }

    public function assessment()
    {
        return $this->hasOne(EmolumentAssessment::class);
    }

    public function validation()
    {
        return $this->hasOne(EmolumentValidation::class);
    }
}

