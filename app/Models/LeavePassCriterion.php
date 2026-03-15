<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeavePassCriterion extends Model
{
    use HasFactory;

    public const TYPE_ANNUAL_LEAVE = 'annual_leave';
    public const TYPE_PASS = 'pass';

    public const BAND_GL03_BELOW = 'gl03_below';
    public const BAND_GL04_06 = 'gl04_06';
    public const BAND_GL07_ABOVE = 'gl07_above';

    public const DURATION_WORKING_DAYS = 'working_days';
    public const DURATION_CALENDAR_DAYS = 'calendar_days';

    public const RANKS = [
        'CGC',
        'DCG',
        'ACG',
        'CC',
        'DC',
        'AC',
        'CSC',
        'SC',
        'DSC',
        'ASC I',
        'ASC II',
        'IC',
        'AIC',
        'CA I',
        'CA II',
        'CA III',
    ];

    public const RANK_TO_GRADE = [
        'CGC' => 18,
        'DCG' => 17,
        'ACG' => 16,
        'CC' => 15,
        'DC' => 14,
        'AC' => 13,
        'CSC' => 12,
        'SC' => 11,
        'DSC' => 10,
        'ASC I' => 9,
        'ASC II' => 8,
        'IC' => 7,
        'AIC' => 6,
        'CA I' => 5,
        'CA II' => 4,
        'CA III' => 3,
    ];

    protected $fillable = [
        'type',
        'rank',
        'grade_band',
        'max_times_per_year',
        'duration_type',
        'max_duration_days',
        'qualification_months',
    ];

    protected function casts(): array
    {
        return [
            'max_times_per_year' => 'integer',
            'max_duration_days' => 'integer',
            'qualification_months' => 'integer',
        ];
    }

    public static function rankBand(?string $rank): string
    {
        $gl = (int) (self::RANK_TO_GRADE[$rank ?? ''] ?? 0);
        if ($gl >= 7) {
            return self::BAND_GL07_ABOVE;
        }
        if ($gl >= 4) {
            return self::BAND_GL04_06;
        }

        return self::BAND_GL03_BELOW;
    }
}
