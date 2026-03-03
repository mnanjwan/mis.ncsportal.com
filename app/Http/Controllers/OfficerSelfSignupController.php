<?php

namespace App\Http\Controllers;

use App\Mail\OnboardingLinkMail;
use App\Models\Officer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OfficerSelfSignupController extends Controller
{
    /**
     * Show the officer self-signup form (public).
     * For both: new officers never on the platform, and officers with uncompleted onboarding.
     */
    public function showForm()
    {
        return view('auth.officer-signup');
    }

    /**
     * Process self-signup: find or create officer, create user if new, or issue continue token if existing user with uncompleted onboarding.
     * No email is sent; user is either logged in (new) or redirected with ?continue= token (existing).
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'service_number' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'name' => 'required|string|max:255',
        ]);

        $serviceNumber = trim($validated['service_number']);
        $email = $validated['email'];
        $name = trim($validated['name']);

        $officer = Officer::where('service_number', $serviceNumber)->first();

        if (!$officer) {
            $officer = $this->createMinimalOfficer($serviceNumber, $email, $name);
        } else {
            if (!$officer->is_active || $officer->is_deceased) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'This officer record is not active or is marked as deceased.')
                    ->with('error_action', 'contact_hrd')
                    ->with('error_action_hint', 'Contact HRD to verify your status before signing up.');
            }
        }

        if ($officer->user) {
            if ($officer->hasCompletedOnboarding()) {
                return redirect()->route('login')
                    ->with('info', 'An account already exists for this service number and you have completed onboarding. Sign in below with your password. If you forgot your password, use "Forgot password?".');
            }
            if (strtolower($officer->user->email) !== strtolower($email)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'The email you entered does not match the one we have on file for service number ' . $serviceNumber . '.')
                    ->with('error_action', 'email_mismatch')
                    ->with('error_action_hint', 'Enter the email address we have for you, or contact HRD to update your registered email.');
            }
            $continueToken = Str::random(64);
            Cache::put('officer_signup_continue:' . $continueToken, $officer->user_id, now()->addMinutes(15));

            return redirect()->route('onboarding.step1', ['continue' => $continueToken]);
        }

        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'This email address is already registered to another account.')
                ->with('error_action', 'email_taken')
                ->with('error_action_hint', 'If you already have an account, sign in below. Otherwise use a different email or contact HRD if you believe this is an error.');
        }

        $user = User::create([
            'email' => $email,
            'password' => Hash::make($tempPassword = str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT)),
            'temp_password' => $tempPassword,
            'is_active' => true,
            'created_by' => null,
        ]);

        $officer->update(['user_id' => $user->id]);

        $officerRole = \App\Models\Role::where('name', 'Officer')->first();
        if ($officerRole) {
            $user->roles()->attach($officerRole->id, [
                'command_id' => $officer->present_station,
                'assigned_at' => now(),
                'assigned_by' => null,
                'is_active' => true,
            ]);
        }

        $nameParts = preg_split('/\s+/', $name, 2);
        session(['signup_first_name' => $nameParts[0] ?? '']);

        // Use one-time token in URL so login survives the redirect (avoids session not persisting)
        $continueToken = Str::random(64);
        Cache::put('officer_signup_continue:' . $continueToken, $user->id, now()->addMinutes(15));

        return redirect()->route('onboarding.step1', ['continue' => $continueToken]);
    }

    private function createMinimalOfficer(string $serviceNumber, string $email, string $name): Officer
    {
        $nameParts = preg_split('/\s+/', trim($name), 2);
        $initials = !empty($nameParts) ? strtoupper(substr($nameParts[0], 0, 2)) : 'TBD';
        $surname = count($nameParts) > 1 ? $nameParts[1] : ($nameParts[0] ?? 'TBD');

        return Officer::create([
            'service_number' => $serviceNumber,
            'initials' => $initials,
            'surname' => $surname,
            'sex' => 'M',
            'date_of_birth' => '1980-01-01',
            'date_of_first_appointment' => now()->toDateString(),
            'date_of_present_appointment' => now()->toDateString(),
            'substantive_rank' => 'TBD',
            'salary_grade_level' => 'TBD',
            'state_of_origin' => 'TBD',
            'lga' => 'TBD',
            'geopolitical_zone' => 'TBD',
            'entry_qualification' => 'TBD',
            'permanent_home_address' => 'To be provided during onboarding',
            'phone_number' => '00000000000',
            'email' => $email,
            'is_active' => true,
            'is_deceased' => false,
            'created_by' => null,
        ]);
    }
}
