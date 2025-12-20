<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountChangeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'officer_id',
        'change_type',
        'new_account_number',
        'new_rsa_pin',
        'new_bank_name',
        'new_sort_code',
        'new_pfa_name',
        'current_account_number',
        'current_rsa_pin',
        'current_bank_name',
        'current_sort_code',
        'current_pfa_name',
        'status',
        'reason',
        'rejection_reason',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    // Relationships
    public function officer()
    {
        return $this->belongsTo(Officer::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'APPROVED');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'REJECTED');
    }
}
