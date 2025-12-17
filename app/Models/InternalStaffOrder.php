<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalStaffOrder extends Model
{
    use HasFactory;

    protected $table = 'internal_staff_orders';

    protected $fillable = [
        'command_id',
        'order_number',
        'order_date',
        'prepared_by',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
        ];
    }

    // Relationships
    public function command()
    {
        return $this->belongsTo(Command::class);
    }

    public function preparedBy()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }
}

