<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Models\Officer;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseController
{
    /**
     * Login with email or service number
     */
    public function login(LoginRequest $request): JsonResponse
    {

        $user = null;

        // Try to find user by email first
        if ($request->has('email')) {
            $user = User::where('email', $request->email)->first();
        } 
        // If not found or service_number provided, try service_number
        elseif ($request->has('service_number')) {
            $officer = Officer::where('service_number', $request->service_number)->first();
            if ($officer && $officer->user_id) {
                $user = User::find($officer->user_id);
            }
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse(
                'Invalid credentials',
                null,
                401,
                'AUTHENTICATION_REQUIRED'
            );
        }

        if (!$user->is_active) {
            return $this->errorResponse(
                'Account is inactive',
                null,
                403,
                'ACCOUNT_INACTIVE'
            );
        }

        // Update last login
        $user->update(['last_login' => now()]);

        // Create token
        $token = $user->createToken('auth-token')->plainTextToken;

        // Load relationships
        $user->load(['officer.presentStation', 'roles']);

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->toArray(),
                'officer' => $user->officer ? [
                    'id' => $user->officer->id,
                    'service_number' => $user->officer->service_number,
                    'name' => $user->officer->full_name,
                    'rank' => $user->officer->substantive_rank,
                    'command' => $user->officer->presentStation ? [
                        'id' => $user->officer->presentStation->id,
                        'name' => $user->officer->presentStation->name,
                    ] : null,
                ] : null,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => now()->addHours(24)->toIso8601String(),
        ], 'Login successful');
    }

    /**
     * Logout current user
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logged out successfully');
    }

    /**
     * Refresh authentication token
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Delete old token
        $request->user()->currentAccessToken()->delete();
        
        // Create new token
        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'expires_at' => now()->addHours(24)->toIso8601String(),
        ], 'Token refreshed successfully');
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load([
            'officer.presentStation.zone',
            'officer.nextOfKin' => function($query) {
                $query->where('is_primary', true);
            },
            'roles'
        ]);

        $officerData = null;
        if ($user->officer) {
            $nextOfKin = $user->officer->nextOfKin->first();
            $officerData = [
                'id' => $user->officer->id,
                'service_number' => $user->officer->service_number,
                'initials' => $user->officer->initials,
                'surname' => $user->officer->surname,
                'substantive_rank' => $user->officer->substantive_rank,
                'date_of_birth' => $user->officer->date_of_birth?->format('Y-m-d'),
                'sex' => $user->officer->sex,
                'phone_number' => $user->officer->phone_number,
                'email' => $user->officer->email,
                'residential_address' => $user->officer->residential_address,
                'permanent_home_address' => $user->officer->permanent_home_address,
                'date_of_first_appointment' => $user->officer->date_of_first_appointment?->format('Y-m-d'),
                'rsa_number' => $user->officer->rsa_number,
                'quartered' => $user->officer->quartered,
                'bank_name' => $user->officer->bank_name,
                'bank_account_number' => $user->officer->bank_account_number,
                'profile_picture_url' => $user->officer->profile_picture_url ? asset('storage/' . $user->officer->profile_picture_url) : null,
                'command' => $user->officer->presentStation ? [
                    'id' => $user->officer->presentStation->id,
                    'name' => $user->officer->presentStation->name,
                ] : null,
                'next_of_kin' => $nextOfKin ? [
                    'name' => $nextOfKin->name,
                    'relationship' => $nextOfKin->relationship,
                    'phone_number' => $nextOfKin->phone_number,
                    'address' => $nextOfKin->address,
                ] : null,
            ];
        }

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->toArray(),
                'officer' => $officerData,
            ],
        ]);
    }
}

