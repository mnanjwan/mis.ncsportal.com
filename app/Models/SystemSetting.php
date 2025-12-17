<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $table = 'system_settings';

    public $timestamps = false;
    
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'setting_key',
        'setting_value',
        'description',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'updated_at' => 'datetime',
        ];
    }

    // Relationships
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

