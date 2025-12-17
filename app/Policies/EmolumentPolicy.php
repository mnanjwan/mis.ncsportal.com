<?php

namespace App\Policies;

use App\Models\Emolument;
use App\Models\User;

class EmolumentPolicy
{
    /**
     * Determine if user can view any emoluments
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['HRD', 'Assessor', 'Validator', 'Area Controller', 'Accounts']);
    }

    /**
     * Determine if user can view the emolument
     */
    public function view(User $user, Emolument $emolument): bool
    {
        // Officers can view their own emoluments
        if ($user->officer?->id === $emolument->officer_id) {
            return true;
        }

        // Assessors can view emoluments in their command
        if ($user->hasRole('Assessor')) {
            return $user->officer?->present_station === $emolument->officer->present_station;
        }

        // Validators and Area Controllers can view
        if ($user->hasAnyRole(['Validator', 'Area Controller'])) {
            return $user->officer?->present_station === $emolument->officer->present_station;
        }

        // HRD and Accounts can view all
        return $user->hasAnyRole(['HRD', 'Accounts']);
    }

    /**
     * Determine if user can create emoluments
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Officer');
    }

    /**
     * Determine if user can assess the emolument
     */
    public function assess(User $user, Emolument $emolument): bool
    {
        if (!$user->hasRole('Assessor')) {
            return false;
        }

        // Can only assess if in same command
        return $user->officer?->present_station === $emolument->officer->present_station;
    }

    /**
     * Determine if user can validate the emolument
     */
    public function validate(User $user, Emolument $emolument): bool
    {
        if (!$user->hasAnyRole(['Validator', 'Area Controller'])) {
            return false;
        }

        // Can only validate if in same command
        return $user->officer?->present_station === $emolument->officer->present_station;
    }
}

