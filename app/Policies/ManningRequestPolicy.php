<?php

namespace App\Policies;

use App\Models\ManningRequest;
use App\Models\User;

class ManningRequestPolicy
{
    /**
     * Determine if user can view any manning requests
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['HRD', 'Staff Officer', 'Area Controller']);
    }

    /**
     * Determine if user can view the manning request
     */
    public function view(User $user, ManningRequest $manningRequest): bool
    {
        // Staff Officer can view requests in their command
        if ($user->hasRole('Staff Officer')) {
            return $user->officer?->present_station === $manningRequest->command_id;
        }

        // Area Controller can view
        if ($user->hasRole('Area Controller')) {
            return $user->officer?->present_station === $manningRequest->command_id;
        }

        // HRD can view all
        return $user->hasRole('HRD');
    }

    /**
     * Determine if user can create manning requests
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Staff Officer');
    }

    /**
     * Determine if user can approve the manning request
     */
    public function approve(User $user, ManningRequest $manningRequest): bool
    {
        if (!$user->hasRole('Area Controller')) {
            return false;
        }

        return $user->officer?->present_station === $manningRequest->command_id;
    }
}

