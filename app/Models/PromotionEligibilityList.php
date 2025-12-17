<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionEligibilityList extends Model
{
    use HasFactory;

    protected $table = 'promotion_eligibility_lists';

    protected $fillable = [
        'year',
        'generated_by',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
        ];
    }

    // Relationships
    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function items()
    {
        return $this->hasMany(PromotionEligibilityListItem::class, 'eligibility_list_id');
    }
}

