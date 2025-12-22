<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Officer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = null;

        // Try to find user by email first
        if (filter_var($request->username, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $request->username)->first();
        } else {
            // Try service number
            $officer = Officer::where('service_number', $request->username)->first();
            if ($officer && $officer->user_id) {
                $user = User::find($officer->user_id);
            }
        }

        if (!$user || !Hash::check($request->password, $user->password_hash ?? $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'username' => ['Your account is inactive. Please contact HRD.'],
            ]);
        }

        // Update last login
        $user->update(['last_login' => now()]);

        // Login user
        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        // Clear any old intended URL to prevent redirect issues
        $request->session()->forget('url.intended');

        // Redirect based on role (check in priority order)
        // Load ONLY ACTIVE roles
        $user->load(['roles' => function($query) {
            $query->wherePivot('is_active', true);
        }]);
        
        // Priority order: HRD > CGC > Admin roles > Zone Coordinator > Validator > Assessor > Staff Officer > Others > Officer
        $rolePriorities = [
            'HRD',
            'CGC',
            'Board',
            'Accounts',
            'Welfare',
            'Establishment',
            'Building Unit',
            'Area Controller',
            'DC Admin',
            'Zone Coordinator',
            'Validator',
            'Assessor',
            'Staff Officer',
            'Officer'
        ];
        
        $userRoles = $user->roles->pluck('name')->toArray();
        
        // Find the highest priority role the user has
        $role = 'Officer'; // Default
        foreach ($rolePriorities as $priorityRole) {
            if (in_array($priorityRole, $userRoles)) {
                $role = $priorityRole;
                break;
            }
        }
        
        return redirect()->route($this->getDashboardRoute($role));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    private function getDashboardRoute($role)
    {
        $routes = [
            'Officer' => 'officer.dashboard',
            'HRD' => 'hrd.dashboard',
            'CGC' => 'cgc.dashboard',
            'Staff Officer' => 'staff-officer.dashboard',
            'Assessor' => 'assessor.dashboard',
            'Validator' => 'validator.dashboard',
            'Area Controller' => 'area-controller.dashboard',
            'DC Admin' => 'dc-admin.dashboard',
            'Zone Coordinator' => 'zone-coordinator.dashboard',
            'Accounts' => 'accounts.dashboard',
            'Board' => 'board.dashboard',
            'Building Unit' => 'building.dashboard',
            'Establishment' => 'establishment.dashboard',
            'Welfare' => 'welfare.dashboard',
        ];

        return $routes[$role] ?? 'officer.dashboard';
    }
}

