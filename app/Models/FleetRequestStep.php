<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FleetRequestStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'fleet_request_id',
        'step_order',
        'role_name',
        'action',
        'acted_by_user_id',
        'acted_at',
        'decision',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'step_order' => 'integer',
            'acted_at' => 'datetime',
        ];
    }

    public function request()
    {
        return $this->belongsTo(FleetRequest::class, 'fleet_request_id');
    }

    public function actedBy()
    {
        return $this->belongsTo(User::class, 'acted_by_user_id');
    }
}

