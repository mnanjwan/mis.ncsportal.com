<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManningDeploymentAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'manning_deployment_id',
        'manning_request_id',
        'manning_request_item_id',
        'officer_id',
        'from_command_id',
        'to_command_id',
        'rank',
        'notes',
    ];

    // Relationships
    public function deployment()
    {
        return $this->belongsTo(ManningDeployment::class, 'manning_deployment_id');
    }

    public function manningRequest()
    {
        return $this->belongsTo(ManningRequest::class);
    }

    public function manningRequestItem()
    {
        return $this->belongsTo(ManningRequestItem::class);
    }

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
}
