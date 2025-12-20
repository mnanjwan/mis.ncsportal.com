<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeceasedOfficer extends Model
{
    use HasFactory;

    protected $fillable = [
        'officer_id',
        'reported_by',
        'reported_at',
        'validated_by',
        'validated_at',
        'death_certificate_url',
        'date_of_death',
        'next_of_kin_data',
        'bank_name',
        'bank_account_number',
        'rsa_administrator',
        'benefits_processed',
        'benefits_processed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'reported_at' => 'datetime',
            'validated_at' => 'datetime',
            'date_of_death' => 'date',
            'benefits_processed_at' => 'datetime',
            'benefits_processed' => 'boolean',
            'next_of_kin_data' => 'array',
        ];
    }

    // Relationships
    public function officer()
    {
        return $this->belongsTo(Officer::class);
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function validatedBy()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}

