<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quarter extends Model
{
    use HasFactory;

    protected $fillable = [
        'command_id',
        'quarter_number',
        'quarter_type',
        'is_occupied',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_occupied' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function command()
    {
        return $this->belongsTo(Command::class);
    }

    public function officerQuarters()
    {
        return $this->hasMany(OfficerQuarter::class);
    }
}

