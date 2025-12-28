<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureOfficerAccess
{
    /**
     * Handle an incoming request.
     * Ensures user is an officer to access officer-specific routes
     * This middleware allows all officers to access officer features,
     * regardless of any additional roles they may have
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }
            
            return redirect()->route('login');
        }

        // Check if user is an officer
        if (!$user->isOfficer()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be an officer to access this feature.',
                ], 403);
            }
            
            abort(403, 'You must be an officer to access this feature.');
        }

        return $next($request);
    }
}

