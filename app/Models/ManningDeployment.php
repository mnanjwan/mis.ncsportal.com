<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManningDeployment extends Model
{
    use HasFactory;

    protected $fillable = [
        'deployment_number',
        'status',
        'created_by',
        'published_by',
        'published_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    // Relationships
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function publishedBy()
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function assignments()
    {
        return $this->hasMany(ManningDeploymentAssignment::class);
    }

    public function officers()
    {
        return $this->hasManyThrough(Officer::class, ManningDeploymentAssignment::class, 'manning_deployment_id', 'id', 'id', 'officer_id');
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'DRAFT');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'PUBLISHED');
    }

    // Helper methods
    public function isDraft()
    {
        return $this->status === 'DRAFT';
    }

    public function isPublished()
    {
        return $this->status === 'PUBLISHED';
    }

    public function getOfficersByCommand()
    {
        return $this->assignments()
            ->with(['officer', 'toCommand', 'fromCommand'])
            ->get()
            ->groupBy('to_command_id');
    }

    public function getManningLevels()
    {
        $levels = [];
        foreach ($this->assignments()->with('toCommand')->get() as $assignment) {
            $commandId = $assignment->to_command_id;
            $commandName = $assignment->toCommand->name ?? 'Unknown';
            if (!isset($levels[$commandId])) {
                $levels[$commandId] = [
                    'command_id' => $commandId,
                    'command_name' => $commandName,
                    'officers' => [],
                    'by_rank' => [],
                ];
            }
            $levels[$commandId]['officers'][] = $assignment->officer;
            $rank = $assignment->officer->substantive_rank ?? 'Unknown';
            if (!isset($levels[$commandId]['by_rank'][$rank])) {
                $levels[$commandId]['by_rank'][$rank] = 0;
            }
            $levels[$commandId]['by_rank'][$rank]++;
        }
        return $levels;
    }
}
