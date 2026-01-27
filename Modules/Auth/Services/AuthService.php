<?php

namespace Modules\Auth\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * AuthService - Handles authentication business logic
 * 
 * This service encapsulates all authentication operations:
 * - Login with email/password
 * - Token generation/revocation
 * - Password validation
 */
class AuthService
{
    /**
     * Authenticate a user and return an API token
     * 
     * @param string $email
     * @param string $password
     * @param string $deviceName Device name for token identification
     * @return array{user: User, token: string}
     * @throws ValidationException
     */
    public function login(string $email, string $password, string $deviceName = 'api'): array
    {
        // Find user by email
        $user = User::where('email', $email)->first();

        // Validate credentials
        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if user is active
        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['This account has been deactivated.'],
            ]);
        }

        // Generate Sanctum token
        $token = $user->createToken($deviceName)->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Logout the user (revoke current token)
     * 
     * @param User $user
     * @return void
     */
    public function logout(User $user): void
    {
        // Revoke the current access token
        $user->currentAccessToken()->delete();
    }

    /**
     * Logout from all devices (revoke all tokens)
     * 
     * @param User $user
     * @return int Number of tokens revoked
     */
    public function logoutAllDevices(User $user): int
    {
        $count = $user->tokens()->count();
        $user->tokens()->delete();
        return $count;
    }

    /**
     * Refresh the current token (revoke old, create new)
     * 
     * @param User $user
     * @param string $deviceName
     * @return string New token
     */
    public function refreshToken(User $user, string $deviceName = 'api'): string
    {
        // Revoke current token
        $user->currentAccessToken()->delete();

        // Create new token
        return $user->createToken($deviceName)->plainTextToken;
    }

    /**
     * Get user's permissions as a flat array
     * 
     * @param User $user
     * @return array
     */
    public function getUserPermissions(User $user): array
    {
        return $user->getAllPermissions()->pluck('name')->toArray();
    }

    /**
     * Get user's roles as a flat array
     * 
     * @param User $user
     * @return array
     */
    public function getUserRoles(User $user): array
    {
        return $user->getRoleNames()->toArray();
    }
}
