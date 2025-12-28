<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

abstract class Controller extends BaseController
{
    /**
     * Get the authenticated user
     */
    protected function user()
    {
        return Auth::user();
    }

    /**
     * Check if the authenticated user is an officer
     */
    protected function isOfficer(): bool
    {
        $user = $this->user();
        return $user ? $user->isOfficer() : false;
    }

    /**
     * Check if the authenticated user has a specific role
     */
    protected function hasRole(string $roleName): bool
    {
        $user = $this->user();
        return $user ? $user->hasRole($roleName) : false;
    }

    /**
     * Check if the authenticated user has any of the given roles
     */
    protected function hasAnyRole(array $roleNames): bool
    {
        $user = $this->user();
        return $user ? $user->hasAnyRole($roleNames) : false;
    }

    /**
     * Check if the authenticated user has a role OR is an officer
     * Use this for features that should be accessible to officers OR users with specific roles
     */
    protected function hasRoleOrIsOfficer(string $roleName): bool
    {
        $user = $this->user();
        return $user ? $user->hasRoleOrIsOfficer($roleName) : false;
    }

    /**
     * Check if the authenticated user has any of the given roles OR is an officer
     */
    protected function hasAnyRoleOrIsOfficer(array $roleNames): bool
    {
        $user = $this->user();
        return $user ? $user->hasAnyRoleOrIsOfficer($roleNames) : false;
    }

    /**
     * Authorize that the user can access officer features
     * Throws 403 if user is not an officer
     */
    protected function authorizeOfficerAccess(): void
    {
        if (!$this->isOfficer()) {
            abort(403, 'You must be an officer to access this feature.');
        }
    }

    /**
     * Authorize that the user has a specific role
     * Throws 403 if user doesn't have the role
     */
    protected function authorizeRole(string $roleName): void
    {
        if (!$this->hasRole($roleName)) {
            abort(403, "You must have the '{$roleName}' role to access this feature.");
        }
    }

    /**
     * Authorize that the user has a role OR is an officer
     * Throws 403 if user doesn't have the role and is not an officer
     */
    protected function authorizeRoleOrOfficer(string $roleName): void
    {
        if (!$this->hasRoleOrIsOfficer($roleName)) {
            abort(403, "You must have the '{$roleName}' role or be an officer to access this feature.");
        }
    }

    /**
     * Authorize that the user has any of the given roles OR is an officer
     */
    protected function authorizeAnyRoleOrOfficer(array $roleNames): void
    {
        if (!$this->hasAnyRoleOrIsOfficer($roleNames)) {
            $roles = implode(', ', $roleNames);
            abort(403, "You must have one of these roles ({$roles}) or be an officer to access this feature.");
        }
    }
}
