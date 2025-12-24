<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficerQuarter extends Model
{
    use HasFactory;

    protected $fillable = [
        'officer_id',
        'quarter_id',
        'allocated_date',
        'deallocated_date',
        'is_current',
        'allocated_by',
        'request_id',
    ];

    protected function casts(): array
    {
        return [
            'allocated_date' => 'date',
            'deallocated_date' => 'date',
            'is_current' => 'boolean',
        ];
    }

    // Relationships
    public function officer()
    {
        return $this->belongsTo(Officer::class);
    }

    public function quarter()
    {
        return $this->belongsTo(Quarter::class);
    }

    public function allocatedBy()
    {
        return $this->belongsTo(User::class, 'allocated_by');
    }

    public function request()
    {
        return $this->belongsTo(QuarterRequest::class, 'request_id');
    }
}

