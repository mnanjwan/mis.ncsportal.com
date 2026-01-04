<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionTimeout
{
    /**
     * Handle an incoming request.
     * Checks if the user has been inactive for more than the session lifetime
     * and logs them out if so.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip check for unauthenticated users
        if (!Auth::check()) {
            return $next($request);
        }

        $session = $request->session();
        $sessionLifetime = config('session.lifetime', 15); // in minutes
        $lastActivity = $session->get('last_activity');

        // If last activity is not set, set it now
        if (!$lastActivity) {
            $session->put('last_activity', now()->timestamp);
            return $next($request);
        }

        // Calculate inactivity time in minutes
        $inactivityMinutes = (now()->timestamp - $lastActivity) / 60;

        // If user has been inactive for more than session lifetime, log them out
        if ($inactivityMinutes >= $sessionLifetime) {
            Auth::logout();
            $session->invalidate();
            $session->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your session has expired due to inactivity. Please log in again.',
                ], 401);
            }

            return redirect()->route('login')
                ->with('error', 'Your session has expired due to inactivity. Please log in again.');
        }

        // Update last activity time
        $session->put('last_activity', now()->timestamp);

        return $next($request);
    }
}

