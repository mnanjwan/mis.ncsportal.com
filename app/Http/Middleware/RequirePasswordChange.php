<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // If user is not authenticated, allow the request to proceed (auth middleware will handle it)
        if (!$user) {
            return $next($request);
        }

        // Check if user has default password (temp_password)
        if ($user->temp_password) {
            // Allow access to dashboard and password change routes
            $allowedRoutes = [
                'officer.dashboard',
                'officer.settings',
                'officer.settings.change-password',
                'logout',
            ];

            $routeName = $request->route()?->getName();

            // If route is not in allowed list, redirect to dashboard
            if ($routeName && !in_array($routeName, $allowedRoutes)) {
                return redirect()->route('officer.dashboard')
                    ->with('error', 'You must change your default password before accessing this page.');
            }
        }

        return $next($request);
    }
}
