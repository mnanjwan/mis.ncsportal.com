<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'leave_application_id',
        'staff_officer_id',
        'dc_admin_id',
        'area_controller_id',
        'approval_status',
        'printed_by',
    ];

    protected function casts(): array
    {
        return [
            'minuted_at' => 'datetime',
            'approved_at' => 'datetime',
            'printed_at' => 'datetime',
        ];
    }

    // Relationships
    public function leaveApplication()
    {
        return $this->belongsTo(LeaveApplication::class);
    }

    public function staffOfficer()
    {
        return $this->belongsTo(User::class, 'staff_officer_id');
    }

    public function dcAdmin()
    {
        return $this->belongsTo(User::class, 'dc_admin_id');
    }

    public function areaController()
    {
        return $this->belongsTo(Officer::class, 'area_controller_id');
    }

    public function printedBy()
    {
        return $this->belongsTo(User::class, 'printed_by');
    }
}

