<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfilePictureUpdatedAfterPromotion
{
    /**
     * Routes that are allowed even when profile picture update is required.
     * These are essential routes that officers must access to update their picture or logout.
     */
    protected array $allowedRoutes = [
        'officer.profile',              // View profile page (to see the upload form)
        'officer.profile.update-picture', // Upload new profile picture
        'logout',                        // Allow officers to logout
        'officer.settings',              // Access settings page
        'officer.settings.change-password', // Change password
        'notifications.show',            // View notifications (to see the promotion notification)
    ];

    /**
     * Block ALL officer actions until profile picture is updated after promotion.
     * Only essential routes (profile, logout, settings) are allowed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Only applies to users that have an officer record.
        $officer = $user->officer;
        if (!$officer) {
            return $next($request);
        }

        // Ensure we have fresh data (promotion approval may have just happened).
        $officer->refresh();

        if ($officer->needsProfilePictureUpdateAfterPromotion()) {
            // Allow access to whitelisted routes
            $currentRoute = $request->route()?->getName();
            
            if ($currentRoute && in_array($currentRoute, $this->allowedRoutes, true)) {
                return $next($request);
            }

            return redirect()
                ->route('officer.profile')
                ->with('error', 'Your promotion has been approved. Please update your profile picture to continue using the system.');
        }

        return $next($request);
    }
}
