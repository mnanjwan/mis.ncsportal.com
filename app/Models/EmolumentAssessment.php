<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmolumentAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'emolument_id',
        'assessor_id',
        'assessment_status',
        'comments',
    ];

    protected function casts(): array
    {
        return [
            'assessed_at' => 'datetime',
        ];
    }

    // Relationships
    public function emolument()
    {
        return $this->belongsTo(Emolument::class);
    }

    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessor_id');
    }

    public function validation()
    {
        return $this->hasOne(EmolumentValidation::class);
    }
}

