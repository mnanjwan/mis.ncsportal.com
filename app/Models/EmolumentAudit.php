<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmolumentAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'emolument_id',
        'validation_id',
        'auditor_id',
        'audit_status',
        'comments',
    ];

    protected function casts(): array
    {
        return [
            'audited_at' => 'datetime',
        ];
    }

    // Relationships
    public function emolument()
    {
        return $this->belongsTo(Emolument::class);
    }

    public function validation()
    {
        return $this->belongsTo(EmolumentValidation::class);
    }

    public function auditor()
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }
}

