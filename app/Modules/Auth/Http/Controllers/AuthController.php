<?php

namespace App\Modules\Auth\Http\Controllers;

use App\Constants\HttpStatus;
use App\Http\Controllers\Controller;
use App\Modules\Auth\Http\Requests\LoginRequest;
use App\Modules\User\Http\Resources\UserResource;
use App\Modules\User\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()
            ->where('email', $request->validated('email'))
            ->first();

        if ($user === null || ! Hash::check($request->validated('password'), $user->password)) {
            return $this->errorResponse(
                message: 'Invalid email or password.',
                statusCode: HttpStatus::UNAUTHORIZED,
            );
        }

        if (! $user->isActive()) {
            return $this->errorResponse(
                message: 'Your account is inactive. Please contact an administrator.',
                statusCode: HttpStatus::FORBIDDEN,
            );
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->successResponse(
            data: [
                'user' => new UserResource($user->fresh()),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
            message: 'Login successful.',
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(
            message: 'Logged out successfully.',
        );
    }

    public function me(Request $request): JsonResponse
    {
        return $this->successResponse(
            data: new UserResource($request->user()),
        );
    }
}
