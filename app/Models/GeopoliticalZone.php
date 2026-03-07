<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeopoliticalZone extends Model
{
    use HasFactory;

    protected $table = 'geopolitical_zones';

    protected $fillable = [
        'name',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function states()
    {
        return $this->hasMany(State::class, 'geopolitical_zone_id');
    }

    public function activeStates()
    {
        return $this->hasMany(State::class, 'geopolitical_zone_id')->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }
}
