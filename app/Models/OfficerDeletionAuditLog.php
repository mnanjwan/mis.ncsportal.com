<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficerDeletionAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_number',
        'officer_name',
        'service_number',
        'rank',
        'command',
        'deleted_by_user_id',
        'deleted_by_name',
        'deleted_by_role',
        'reason',
        'deleted_at',
    ];

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    // Relationships
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by_user_id');
    }
}

