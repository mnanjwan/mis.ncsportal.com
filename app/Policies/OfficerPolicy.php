<?php

namespace App\Policies;

use App\Models\Officer;
use App\Models\User;

class OfficerPolicy
{
    /**
     * Determine if user can view any officers
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['HRD', 'Staff Officer', 'Area Controller', 'Assessor', 'Validator']);
    }

    /**
     * Determine if user can view the officer
     */
    public function view(User $user, Officer $officer): bool
    {
        // Officers can view themselves
        if ($user->officer?->id === $officer->id) {
            return true;
        }

        // HRD can view all
        if ($user->hasRole('HRD')) {
            return true;
        }

        // Staff Officer can view officers in their command
        if ($user->hasRole('Staff Officer')) {
            return $user->officer?->present_station === $officer->present_station;
        }

        return false;
    }

    /**
     * Determine if user can create officers
     */
    public function create(User $user): bool
    {
        return $user->hasRole('HRD');
    }

    /**
     * Determine if user can update the officer
     */
    public function update(User $user, Officer $officer): bool
    {
        // Officers can update limited fields of their own record
        if ($user->officer?->id === $officer->id) {
            return true;
        }

        // HRD and Staff Officer can update
        return $user->hasAnyRole(['HRD', 'Staff Officer']);
    }

    /**
     * Determine if user can delete the officer
     */
    public function delete(User $user, Officer $officer): bool
    {
        return $user->hasRole('HRD');
    }
}

