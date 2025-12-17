<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'officer_id',
        'eligibility_list_item_id',
        'from_rank',
        'to_rank',
        'promotion_date',
        'approved_by_board',
        'board_meeting_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'promotion_date' => 'date',
            'board_meeting_date' => 'date',
            'approved_by_board' => 'boolean',
        ];
    }

    // Relationships
    public function officer()
    {
        return $this->belongsTo(Officer::class);
    }

    public function eligibilityListItem()
    {
        return $this->belongsTo(PromotionEligibilityListItem::class);
    }
}

