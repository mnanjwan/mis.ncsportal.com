<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

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
        'preretirement_leave_status',
        'auto_placed_at',
        'cgc_approved_by',
        'cgc_approved_at',
        'cgc_approval_reason',
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
            'auto_placed_at' => 'datetime',
            'cgc_approved_at' => 'datetime',
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

    public function cgcApprovedBy()
    {
        return $this->belongsTo(User::class, 'cgc_approved_by');
    }
}

