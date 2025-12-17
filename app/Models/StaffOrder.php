<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'officer_id',
        'from_command_id',
        'to_command_id',
        'effective_date',
        'order_type',
        'status',
        'description',
        'created_by',
        'is_altered',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'altered_at' => 'datetime',
            'is_altered' => 'boolean',
        ];
    }

    // Relationships
    public function officer()
    {
        return $this->belongsTo(Officer::class);
    }

    public function fromCommand()
    {
        return $this->belongsTo(Command::class, 'from_command_id');
    }

    public function toCommand()
    {
        return $this->belongsTo(Command::class, 'to_command_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function postings()
    {
        return $this->hasMany(OfficerPosting::class);
    }
}

