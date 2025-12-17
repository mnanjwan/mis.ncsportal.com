<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionEligibilityListItem extends Model
{
    use HasFactory;

    protected $table = 'promotion_eligibility_list_items';

    protected $fillable = [
        'eligibility_list_id',
        'officer_id',
        'serial_number',
        'current_rank',
        'years_in_rank',
        'date_of_first_appointment',
        'date_of_present_appointment',
        'state',
        'date_of_birth',
        'excluded_reason',
    ];

    protected function casts(): array
    {
        return [
            'date_of_first_appointment' => 'date',
            'date_of_present_appointment' => 'date',
            'date_of_birth' => 'date',
            'years_in_rank' => 'decimal:2',
        ];
    }

    // Relationships
    public function eligibilityList()
    {
        return $this->belongsTo(PromotionEligibilityList::class, 'eligibility_list_id');
    }

    public function officer()
    {
        return $this->belongsTo(Officer::class);
    }

    public function promotion()
    {
        return $this->hasOne(Promotion::class);
    }
}

