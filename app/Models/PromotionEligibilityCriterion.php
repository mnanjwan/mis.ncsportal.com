<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionEligibilityCriterion extends Model
{
    use HasFactory;

    protected $table = 'promotion_eligibility_criteria';

    protected $fillable = [
        'rank',
        'years_in_rank_required',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'years_in_rank_required' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

