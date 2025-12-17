<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReleaseLetter extends Model
{
    use HasFactory;

    protected $fillable = [
        'officer_id',
        'command_id',
        'letter_number',
        'release_date',
        'reason',
        'prepared_by',
    ];

    protected function casts(): array
    {
        return [
            'release_date' => 'date',
        ];
    }

    // Relationships
    public function officer()
    {
        return $this->belongsTo(Officer::class);
    }

    public function command()
    {
        return $this->belongsTo(Command::class);
    }

    public function preparedBy()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }
}

