<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TwoFactorController extends Controller
{
    /**
     * Show the 2FA setup page
     */
    public function showSetup()
    {
        $user = Auth::user();
        
        // If already enabled, redirect to dashboard
        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('dashboard')->with('info', 'Two-factor authentication is already enabled.');
        }

        // Generate secret if not exists
        if (!$user->two_factor_secret) {
            $user->two_factor_secret = $user->generateTwoFactorSecret();
            $user->save();
        }

        $qrCodeUrl = $user->getTwoFactorQrCodeUrl();
        
        return view('auth.two-factor.setup', [
            'user' => $user,
            'qrCodeUrl' => $qrCodeUrl,
            'secret' => $user->two_factor_secret,
        ]);
    }

    /**
     * Verify and enable 2FA
     */
    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();

        if (!$user->verifyTwoFactorCode($request->code)) {
            throw ValidationException::withMessages([
                'code' => ['The provided code is invalid.'],
            ]);
        }

        // Generate recovery codes
        $recoveryCodes = $user->generateRecoveryCodes();

        $user->update([
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => $recoveryCodes,
        ]);

        return redirect()->route('two-factor.recovery-codes')->with('success', 'Two-factor authentication has been enabled successfully.');
    }

    /**
     * Show recovery codes
     */
    public function showRecoveryCodes()
    {
        $user = Auth::user();
        
        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.setup');
        }

        return view('auth.two-factor.recovery-codes', [
            'recoveryCodes' => $user->two_factor_recovery_codes ?? [],
        ]);
    }

    /**
     * Regenerate recovery codes
     */
    public function regenerateRecoveryCodes()
    {
        $user = Auth::user();
        
        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.setup');
        }

        $recoveryCodes = $user->generateRecoveryCodes();
        $user->update(['two_factor_recovery_codes' => $recoveryCodes]);

        return redirect()->route('two-factor.recovery-codes')->with('success', 'Recovery codes have been regenerated.');
    }

    /**
     * Disable 2FA
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|string|current_password',
        ]);

        $user = Auth::user();

        $user->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        return redirect()->route('dashboard')->with('success', 'Two-factor authentication has been disabled.');
    }

    /**
     * Show 2FA verification page (during login)
     */
    public function showVerification()
    {
        if (!session('login.id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor.verify');
    }

    /**
     * Verify 2FA code during login
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $userId = session('login.id');
        
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Login session expired. Please try again.');
        }

        $user = \App\Models\User::find($userId);

        if (!$user || !$user->hasTwoFactorEnabled()) {
            session()->forget('login.id');
            return redirect()->route('login')->with('error', 'Invalid session.');
        }

        // Check if it's a recovery code
        if (strlen($request->code) === 10) {
            if (!$user->useRecoveryCode($request->code)) {
                throw ValidationException::withMessages([
                    'code' => ['The provided recovery code is invalid.'],
                ]);
            }
        } else {
            // Verify TOTP code
            if (!$user->verifyTwoFactorCode($request->code)) {
                throw ValidationException::withMessages([
                    'code' => ['The provided code is invalid.'],
                ]);
            }
        }

        // Clear login session data
        session()->forget('login.id');
        session()->forget('login.remember');

        // Login the user
        Auth::login($user, session('login.remember', false));
        $request->session()->regenerate();

        // Store session ID
        $user->update(['current_session_id' => $request->session()->getId()]);

        // Redirect based on role
        $user->load(['roles' => function($query) {
            $query->wherePivot('is_active', true);
        }]);

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
        $role = 'Officer';

        foreach ($rolePriorities as $priorityRole) {
            if (in_array($priorityRole, $userRoles)) {
                $role = $priorityRole;
                break;
            }
        }

        return redirect()->route($this->getDashboardRoute($role));
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
