<?php

namespace App\Policies;

use App\Models\LeaveApplication;
use App\Models\User;

class LeaveApplicationPolicy
{
    /**
     * Determine if user can view any leave applications
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['HRD', 'Staff Officer', '2iC Unit Head', 'Area Controller']);
    }

    /**
     * Determine if user can view the leave application
     */
    public function view(User $user, LeaveApplication $leaveApplication): bool
    {
        // Officers can view their own applications
        if ($user->officer?->id === $leaveApplication->officer_id) {
            return true;
        }

        // Staff Officer can view applications in their command
        if ($user->hasRole('Staff Officer')) {
            return $user->officer?->present_station === $leaveApplication->officer->present_station;
        }

        // 2iC Unit Head and Area Controller can view
        if ($user->hasAnyRole(['2iC Unit Head', 'Area Controller'])) {
            return $user->officer?->present_station === $leaveApplication->officer->present_station;
        }

        // HRD can view all
        return $user->hasRole('HRD');
    }

    /**
     * Determine if user can create leave applications
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Officer');
    }

    /**
     * Determine if user can minute the leave application
     */
    public function minute(User $user, LeaveApplication $leaveApplication): bool
    {
        if (!$user->hasRole('Staff Officer')) {
            return false;
        }

        return $user->officer?->present_station === $leaveApplication->officer->present_station;
    }

    /**
     * Determine if user can approve the leave application
     */
    public function approve(User $user, LeaveApplication $leaveApplication): bool
    {
        if (!$user->hasRole('2iC Unit Head')) {
            return false;
        }

        return $user->officer?->present_station === $leaveApplication->officer->present_station;
    }
}

