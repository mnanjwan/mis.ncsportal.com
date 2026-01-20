<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfilePictureUpdatedAfterPromotion
{
    /**
     * Block certain officer actions until profile picture is updated after promotion.
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
            return redirect()
                ->route('officer.profile')
                ->with('error', 'Change Profile Picture hasn’t been done yet');
        }

        return $next($request);
    }
}

