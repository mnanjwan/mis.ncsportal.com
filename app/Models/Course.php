<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
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

    // Relationships - Note: This is a string-based relationship, not a foreign key
    // Use whereHas or direct queries instead
    public function getNominationsCountAttribute()
    {
        return OfficerCourse::where('course_name', $this->name)->count();
    }
}
