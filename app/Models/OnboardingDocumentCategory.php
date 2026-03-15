<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnboardingDocumentCategory extends Model
{
    use HasFactory;

    public const APPLIES_TO_RECRUIT = 'recruit';
    public const APPLIES_TO_OFFICER = 'officer';
    public const APPLIES_TO_BOTH = 'both';

    protected $fillable = [
        'key',
        'name',
        'applies_to',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
