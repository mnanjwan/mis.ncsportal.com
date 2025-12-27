<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class APERTimeline extends Model
{
    use HasFactory;

    protected $table = 'aper_timelines';

    protected $fillable = [
        'year',
        'start_date',
        'end_date',
        'is_extended',
        'extension_end_date',
        'is_active',
        'description',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'extension_end_date' => 'datetime',
            'is_extended' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function forms()
    {
        return $this->hasMany(APERForm::class, 'timeline_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Accessors
    public function getCanSubmitAttribute()
    {
        if (!$this->is_active) {
            return false;
        }

        $endDate = $this->is_extended && $this->extension_end_date 
            ? $this->extension_end_date 
            : $this->end_date;

        return now()->between($this->start_date, $endDate);
    }

    public function getDaysRemainingAttribute()
    {
        if (!$this->is_active) {
            return 0;
        }

        $endDate = $this->is_extended && $this->extension_end_date 
            ? $this->extension_end_date 
            : $this->end_date;

        return max(0, now()->diffInDays($endDate, false));
    }
}

