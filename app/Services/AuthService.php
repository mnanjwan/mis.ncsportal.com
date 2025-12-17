<?php

namespace App\Services;

use App\Models\Officer;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Authenticate user by email or service number
     */
    public function authenticate(string $email = null, string $serviceNumber = null, string $password): ?User
    {
        $user = null;

        if ($email) {
            $user = User::where('email', $email)->first();
        } elseif ($serviceNumber) {
            $officer = Officer::where('service_number', $serviceNumber)->first();
            if ($officer && $officer->user_id) {
                $user = User::find($officer->user_id);
            }
        }

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        if (!$user->is_active) {
            return null;
        }

        // Update last login
        $user->update(['last_login' => now()]);

        return $user;
    }

    /**
     * Create API token for user
     */
    public function createToken(User $user): string
    {
        return $user->createToken('auth-token')->plainTextToken;
    }

    /**
     * Revoke current token
     */
    public function revokeToken(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}

