<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function commands()
    {
        return $this->hasMany(Command::class);
    }

    public function activeCommands()
    {
        return $this->hasMany(Command::class)->where('is_active', true);
    }

    // Note: Zone coordinators are accessed through their command assignments
    // A zone coordinator is assigned to a command, and the command belongs to a zone
}

