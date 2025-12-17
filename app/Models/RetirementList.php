<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetirementList extends Model
{
    use HasFactory;

    protected $table = 'retirement_list';

    protected $fillable = [
        'year',
        'generated_by',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
        ];
    }

    // Relationships
    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function items()
    {
        return $this->hasMany(RetirementListItem::class);
    }
}

