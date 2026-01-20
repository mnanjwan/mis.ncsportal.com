<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Qualification extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_normalized',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $model->name = Str::squish((string) $model->name);
            $model->name_normalized = static::normalizeName($model->name);
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function normalizeName(string $name): string
    {
        return Str::lower(Str::squish($name));
    }
}

