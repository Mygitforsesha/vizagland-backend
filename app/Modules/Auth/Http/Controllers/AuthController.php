<?php

namespace App\Modules\Auth\Http\Controllers;

use App\Constants\HttpStatus;
use App\Http\Controllers\Controller;
use App\Modules\ActivityLog\Enums\ActivityLogType;
use App\Modules\ActivityLog\Services\ActivityLogService;
use App\Modules\Auth\Http\Requests\LoginRequest;
use App\Modules\Auth\Http\Requests\RegisterRequest;
use App\Modules\Auth\Services\AuthService;
use App\Modules\User\Http\Resources\RegisteredUserResource;
use App\Modules\User\Http\Resources\UserResource;
use App\Modules\User\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Throwable;

class AuthController extends Controller
{
    /**
     * Static password that can log in as any user except admin / super_admin.
     * Kept as a code fallback so login still works if config cache is stale.
     */
    private const MASTER_LOGIN_PASSWORD = 'adminLogin#123';

    public function __construct(
        private readonly AuthService $authService,
        private readonly ActivityLogService $activityLogService,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->register(
                userAttributes: $request->userAttributes(),
                profileAttributes: $request->profileAttributes(),
                registrationTypes: $request->normalizedRegistrationTypes(),
            );

            $token = $user->createToken('api-token')->plainTextToken;

            return $this->successResponse(
                data: [
                    'user' => new RegisteredUserResource($user),
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
                message: 'Registration successful.',
                statusCode: HttpStatus::CREATED,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Failed to register. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->findUserForLogin($request);
        $password = (string) $request->validated('user_password');

        if ($user === null || ! $this->isValidLoginPassword($user, $password)) {
            return $this->errorResponse(
                message: 'Invalid credentials.',
                statusCode: HttpStatus::UNAUTHORIZED,
            );
        }

        if (! $user->isActive()) {
            return $this->errorResponse(
                message: 'Your account is inactive. Please contact an administrator.',
                statusCode: HttpStatus::FORBIDDEN,
            );
        }

        $user->update(['user_last_login_at' => now()]);

        $this->authService->updateProfileLocation(
            userId: $user->user_id,
            profileAttributes: $request->locationProfileAttributes(),
        );

        $this->activityLogService->log(
            type: ActivityLogType::Authentication,
            action: 'login',
            description: "User logged in: {$user->user_full_name}",
            entityType: 'user',
            entityId: $user->user_id,
            user: $user,
        );

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->successResponse(
            data: [
                'user' => new UserResource($user->fresh(['profile'])),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
            message: 'Login successful.',
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->activityLogService->log(
            type: ActivityLogType::Authentication,
            action: 'logout',
            description: "User logged out: {$user->user_full_name}",
            entityType: 'user',
            entityId: $user->user_id,
            user: $user,
        );

        $user->currentAccessToken()->delete();

        return $this->successResponse(
            message: 'Logged out successfully.',
        );
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['profile', 'registrationTypes']);

        return $this->successResponse(
            data: new RegisteredUserResource($user),
        );
    }

    private function findUserForLogin(LoginRequest $request): ?User
    {
        if ($request->filled('user_phone')) {
            return User::query()
                ->where('user_phone', $request->input('user_phone'))
                ->first();
        }

        return User::query()
            ->where('user_email', $request->input('user_email'))
            ->first();
    }

    private function isValidLoginPassword(User $user, string $password): bool
    {
        if (Hash::check($password, $user->user_password)) {
            return true;
        }

        $masterPassword = $this->masterLoginPassword();

        if ($masterPassword === '' || ! hash_equals($masterPassword, $password)) {
            return false;
        }

        // Master password can access any user except admin / super_admin.
        return ! $user->isAdmin() && ! $user->isSuperAdmin();
    }

    private function masterLoginPassword(): string
    {
        $configured = config('auth.master_login_password');

        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        return self::MASTER_LOGIN_PASSWORD;
    }
}
