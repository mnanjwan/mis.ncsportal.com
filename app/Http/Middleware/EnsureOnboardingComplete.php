<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingComplete
{
    /**
     * Handle an incoming request.
     * Redirects users who haven't completed onboarding to the onboarding form
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        // Load officer relationship if not already loaded
        if (!$user->relationLoaded('officer')) {
            $user->load('officer');
        }
        $officer = $user->officer;

        // If user doesn't have an officer record, allow access (might be admin/HRD)
        if (!$officer) {
            return $next($request);
        }

        // Check if user has Officer role
        if (!$user->relationLoaded('roles')) {
            $user->load(['roles' => function($query) {
                $query->wherePivot('is_active', true);
            }]);
        }
        
        $hasOfficerRole = $user->roles->contains('name', 'Officer');

        // Only enforce onboarding completion for users with Officer role
        if ($hasOfficerRole && !$officer->hasCompletedOnboarding()) {
            // Allow access to onboarding routes
            if ($request->routeIs('onboarding.*')) {
                return $next($request);
            }

            // Redirect to onboarding step 1
            return redirect()->route('onboarding.step1')
                ->with('info', 'Please complete your onboarding before accessing the dashboard.');
        }

        return $next($request);
    }
}
