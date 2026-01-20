<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Ensures user has required role(s)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Only check ACTIVE roles - reload to ensure we have fresh data
        $user->load(['roles' => function($query) {
            $query->wherePivot('is_active', true);
        }]);
        
        $userRoles = $user->roles->pluck('name')->toArray();
        
        // First, handle pipe-separated roles (e.g., "Staff Officer|Area Controller")
        // Split any roles that contain pipes
        $expandedRoles = [];
        foreach ($roles as $role) {
            if (strpos($role, '|') !== false) {
                // Split by pipe and add each role
                $expandedRoles = array_merge($expandedRoles, explode('|', $role));
            } else {
                $expandedRoles[] = $role;
            }
        }
        
        // Now handle role names with spaces that might have been split
        // If we have roles like ['HRD', 'Zone', 'Coordinator'], combine them
        $normalizedRoles = [];
        $i = 0;
        while ($i < count($expandedRoles)) {
            $role = trim($expandedRoles[$i]);
            // Check if this might be part of a multi-word role
            if ($i + 1 < count($expandedRoles) && 
                (($role === 'Zone' && trim($expandedRoles[$i + 1]) === 'Coordinator') ||
                 ($role === 'Area' && trim($expandedRoles[$i + 1]) === 'Controller') ||
                 ($role === 'Building' && trim($expandedRoles[$i + 1]) === 'Unit') ||
                 ($role === 'DC' && trim($expandedRoles[$i + 1]) === 'Admin') ||
                 ($role === 'Staff' && trim($expandedRoles[$i + 1]) === 'Officer'))) {
                $normalizedRoles[] = $role . ' ' . trim($expandedRoles[$i + 1]);
                $i += 2;
            } else {
                $normalizedRoles[] = $role;
                $i++;
            }
        }
        
        // Check if user has any of the required roles
        $hasRole = !empty(array_intersect($normalizedRoles, $userRoles));
        
        // Debug logging (remove in production)
        if (config('app.debug')) {
            \Log::debug('RoleMiddleware check', [
                'required_roles' => $normalizedRoles,
                'user_roles' => $userRoles,
                'raw_roles' => $roles,
                'expanded_roles' => $expandedRoles ?? [],
                'has_role' => $hasRole,
                'user_id' => $user->id,
            ]);
        }
        
        if (!$hasRole) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient permissions',
                    'required_roles' => $normalizedRoles,
                    'user_roles' => $userRoles,
                ], 403);
            }
            
            // User no longer has access to this route - redirect to their dashboard or log out
            $dashboardRoute = $this->getUserDashboardRoute($userRoles);
            
            if ($dashboardRoute) {
                // Redirect to their correct dashboard
                return redirect()->route($dashboardRoute)
                    ->with('info', 'Your role has been changed. You have been redirected to your dashboard.');
            } else {
                // No active roles - log them out
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')
                    ->with('info', 'Your role has been removed. Please contact HRD for access.');
            }
        }

        return $next($request);
    }

    /**
     * Get the dashboard route for user based on their active roles
     */
    private function getUserDashboardRoute(array $userRoles): ?string
    {
        // Priority order: HRD > CGC > Admin roles > Zone Coordinator > Validator > Assessor > Staff Officer > Officer
        $rolePriorities = [
            'HRD' => 'hrd.dashboard',
            'CGC' => 'cgc.dashboard',
            'Board' => 'board.dashboard',
            'Accounts' => 'accounts.dashboard',
            // Transport & Logistics (Fleet)
            'CC T&L' => 'fleet.cc-tl.dashboard',
            'DCG FATS' => 'fleet.dcg-fats.dashboard',
            'ACG TS' => 'fleet.acg-ts.dashboard',
            'CD' => 'fleet.cd.dashboard',
            'O/C T&L' => 'fleet.oc-tl.dashboard',
            'Transport Store/Receiver' => 'fleet.store-receiver.dashboard',
            'Welfare' => 'welfare.dashboard',
            'Establishment' => 'establishment.dashboard',
            'Building Unit' => 'building.dashboard',
            'Area Controller' => 'area-controller.dashboard',
            'DC Admin' => 'dc-admin.dashboard',
            'Zone Coordinator' => 'zone-coordinator.dashboard',
            'Validator' => 'validator.dashboard',
            'Assessor' => 'assessor.dashboard',
            'Staff Officer' => 'staff-officer.dashboard',
            'Officer' => 'officer.dashboard',
        ];
        
        // Find the highest priority role the user has
        foreach ($rolePriorities as $role => $route) {
            if (in_array($role, $userRoles)) {
                return $route;
            }
        }
        
        // Default to officer dashboard if no specific role found
        return 'officer.dashboard';
    }
}

