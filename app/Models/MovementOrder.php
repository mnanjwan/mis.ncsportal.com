<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovementOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'criteria_months_at_station',
        'manning_request_id',
        'created_by',
        'status',
    ];

    // Relationships
    public function manningRequest()
    {
        return $this->belongsTo(ManningRequest::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function postings()
    {
        return $this->hasMany(OfficerPosting::class);
    }
}

