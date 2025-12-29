<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureSingleSession
{
    /**
     * Handle an incoming request.
     * Ensures only one active session per user - invalidates other sessions
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip check for unauthenticated users
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $currentSessionId = $request->session()->getId();

        // Check if the current session matches the user's stored session ID
        if ($user->current_session_id && $user->current_session_id !== $currentSessionId) {
            // This session is not the active one - log out
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your session has been invalidated because you logged in on another device.',
                ], 401);
            }

            return redirect()->route('login')
                ->with('error', 'Your session has been invalidated because you logged in on another device. Please log in again.');
        }

        return $next($request);
    }
}


