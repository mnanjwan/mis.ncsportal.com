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

    protected static function booted(): void
    {
        static::saved(function (self $promotion) {
            // Trigger only when approved_by_board flips from false -> true.
            if (!$promotion->wasChanged('approved_by_board')) {
                return;
            }

            $original = (bool) ($promotion->getOriginal('approved_by_board') ?? false);
            if ($original === true || $promotion->approved_by_board !== true) {
                return;
            }

            $promotion->loadMissing(['officer.user']);
            $officer = $promotion->officer;
            if (!$officer) {
                return;
            }

            $requiredAt = $promotion->promotion_date ?? now();

            $officer->forceFill([
                'profile_picture_required_after_promotion_at' => $requiredAt,
            ])->saveQuietly();

            // Notify officer (in-app + email via queued job).
            if ($officer->user) {
                app(\App\Services\NotificationService::class)->notify(
                    $officer->user,
                    'profile_picture_update_required',
                    'Profile Picture Update Required',
                    'Your promotion has been approved. Please update your profile picture to continue using all officer services.',
                    'officer',
                    $officer->id,
                    false
                );
            }
        });
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

