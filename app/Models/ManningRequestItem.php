<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManningRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'manning_request_id',
        'rank',
        'quantity_needed',
        'sex_requirement',
        'qualification_requirement',
        'matched_officer_id',
    ];

    // Relationships
    public function manningRequest()
    {
        return $this->belongsTo(ManningRequest::class);
    }

    public function matchedOfficer()
    {
        return $this->belongsTo(Officer::class, 'matched_officer_id');
    }

    public function deploymentAssignments()
    {
        return $this->hasMany(ManningDeploymentAssignment::class);
    }
}

