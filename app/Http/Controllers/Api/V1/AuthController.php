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
        $user->load(['officer.presentStation', 'roles']);

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->toArray(),
                'officer' => $user->officer ? [
                    'id' => $user->officer->id,
                    'service_number' => $user->officer->service_number,
                    'initials' => $user->officer->initials,
                    'surname' => $user->officer->surname,
                    'rank' => $user->officer->substantive_rank,
                    'command' => $user->officer->presentStation ? [
                        'id' => $user->officer->presentStation->id,
                        'name' => $user->officer->presentStation->name,
                    ] : null,
                ] : null,
            ],
        ]);
    }
}

