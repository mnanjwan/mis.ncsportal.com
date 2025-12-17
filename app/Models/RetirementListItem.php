<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetirementListItem extends Model
{
    use HasFactory;

    protected $table = 'retirement_list_items';

    protected $fillable = [
        'retirement_list_id',
        'officer_id',
        'serial_number',
        'rank',
        'initials',
        'name',
        'retirement_condition',
        'date_of_birth',
        'date_of_first_appointment',
        'date_of_pre_retirement_leave',
        'retirement_date',
        'notified',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'date_of_first_appointment' => 'date',
            'date_of_pre_retirement_leave' => 'date',
            'retirement_date' => 'date',
            'notified_at' => 'datetime',
            'notified' => 'boolean',
        ];
    }

    // Relationships
    public function retirementList()
    {
        return $this->belongsTo(RetirementList::class);
    }

    public function officer()
    {
        return $this->belongsTo(Officer::class);
    }
}

