<?php

namespace App\Modules\User\Controllers;

use App\Constants\HttpStatus;
use App\Http\Controllers\Controller;
use App\Modules\User\Http\Resources\ProfileSettingsResource;
use App\Modules\User\Requests\ChangeProfilePasswordRequest;
use App\Modules\User\Requests\UpdateProfileRequest;
use App\Modules\User\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profileService,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $user = $this->profileService->show($request->user()->user_id);

        return $this->successResponse(
            data: new ProfileSettingsResource($user),
            message: 'Profile retrieved successfully.',
        );
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $user = $this->profileService->update(
                userId: $request->user()->user_id,
                userAttributes: $request->userAttributes(),
                profileAttributes: $request->profileAttributes(),
            );

            return $this->successResponse(
                data: new ProfileSettingsResource($user),
                message: 'Profile updated successfully.',
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Failed to update profile. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function changePassword(ChangeProfilePasswordRequest $request): JsonResponse
    {
        try {
            $this->profileService->changePassword(
                userId: $request->user()->user_id,
                currentPassword: $request->currentPassword(),
                newPassword: $request->newPassword(),
            );

            return $this->successResponse(
                message: 'Password changed successfully.',
            );
        } catch (RuntimeException $exception) {
            return $this->errorResponse(
                message: $exception->getMessage(),
                statusCode: HttpStatus::UNPROCESSABLE_ENTITY,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Failed to change password. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }
}
