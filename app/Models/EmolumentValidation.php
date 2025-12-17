<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmolumentValidation extends Model
{
    use HasFactory;

    protected $fillable = [
        'emolument_id',
        'assessment_id',
        'validator_id',
        'validation_status',
        'comments',
    ];

    protected function casts(): array
    {
        return [
            'validated_at' => 'datetime',
        ];
    }

    // Relationships
    public function emolument()
    {
        return $this->belongsTo(Emolument::class);
    }

    public function assessment()
    {
        return $this->belongsTo(EmolumentAssessment::class);
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validator_id');
    }
}

