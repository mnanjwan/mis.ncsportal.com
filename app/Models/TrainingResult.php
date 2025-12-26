<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_number',
        'officer_id',
        'officer_name',
        'training_score',
        'status',
        'rank',
        'service_number',
        'uploaded_by',
        'uploaded_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'training_score' => 'decimal:2',
            'uploaded_at' => 'datetime',
        ];
    }

    // Relationships
    public function officer()
    {
        return $this->belongsTo(Officer::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Scopes
    public function scopePassed($query)
    {
        return $query->where('status', 'PASS');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'FAIL');
    }

    public function scopeSortedByPerformance($query)
    {
        // Sort by rank only (prefer officer's substantive_rank, fallback to stored rank)
        return $query->orderByRaw('
            COALESCE(
                (SELECT substantive_rank FROM officers WHERE officers.id = training_results.officer_id LIMIT 1),
                training_results.rank
            ) ASC
        ')
            ->orderBy('appointment_number', 'asc');
    }
}
