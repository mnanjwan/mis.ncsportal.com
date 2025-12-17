<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CommandAccessMiddleware
{
    /**
     * Handle an incoming request.
     * Ensures user can only access resources within their command
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        // HRD and system-wide roles bypass this check
        if ($user->hasAnyRole(['HRD', 'Accounts', 'Board', 'Establishment', 'Welfare'])) {
            return $next($request);
        }

        $officer = $user->officer;

        if (!$officer || !$officer->present_station) {
            return response()->json([
                'success' => false,
                'message' => 'No command assigned',
            ], 403);
        }

        // Store command ID for use in controllers
        $request->merge(['user_command_id' => $officer->present_station]);

        return $next($request);
    }
}

