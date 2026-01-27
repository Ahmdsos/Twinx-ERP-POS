<?php

namespace Modules\Auth\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Resources\UserResource;
use Modules\Auth\Services\AuthService;

/**
 * AuthController - API Authentication Endpoints
 * 
 * Handles:
 * - POST /api/v1/auth/login - User login
 * - POST /api/v1/auth/logout - User logout
 * - POST /api/v1/auth/logout-all - Logout from all devices
 * - POST /api/v1/auth/refresh - Refresh token
 * - GET  /api/v1/auth/user - Get authenticated user
 */
class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {
    }

    /**
     * Login and get API token
     * 
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->validated('email'),
            $request->validated('password'),
            $request->validated('device_name', 'api')
        );

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
                'token_type' => 'Bearer',
            ],
        ], 200);
    }

    /**
     * Logout (revoke current token)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ], 200);
    }

    /**
     * Logout from all devices
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $count = $this->authService->logoutAllDevices($request->user());

        return response()->json([
            'success' => true,
            'message' => "Logged out from {$count} device(s)",
        ], 200);
    }

    /**
     * Refresh the current token
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function refresh(Request $request): JsonResponse
    {
        $deviceName = $request->input('device_name', 'api');
        $token = $this->authService->refreshToken($request->user(), $deviceName);

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 200);
    }

    /**
     * Get authenticated user info
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => new UserResource($user),
                'roles' => $this->authService->getUserRoles($user),
                'permissions' => $this->authService->getUserPermissions($user),
            ],
        ], 200);
    }
}
