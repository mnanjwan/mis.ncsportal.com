<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmolumentTimeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'start_date',
        'end_date',
        'is_extended',
        'extension_end_date',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'extension_end_date' => 'date',
            'is_extended' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function emoluments()
    {
        return $this->hasMany(Emolument::class, 'timeline_id');
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

