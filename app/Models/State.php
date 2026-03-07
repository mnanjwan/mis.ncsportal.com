<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;

    protected $fillable = [
        'geopolitical_zone_id',
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

    public function geopoliticalZone()
    {
        return $this->belongsTo(GeopoliticalZone::class);
    }

    public function lgas()
    {
        return $this->hasMany(Lga::class, 'state_id');
    }

    public function activeLgas()
    {
        return $this->hasMany(Lga::class, 'state_id')->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }
}
