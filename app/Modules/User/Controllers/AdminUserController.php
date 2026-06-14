<?php

namespace App\Modules\User\Controllers;

use App\Constants\HttpStatus;
use App\Enums\ApiResponseStatus;
use App\Http\Controllers\Controller;
use App\Modules\User\Http\Resources\AdminUserListItemResource;
use App\Modules\User\Http\Resources\RegisteredUserResource;
use App\Modules\User\Requests\CreateAdminUserRequest;
use App\Modules\User\Requests\ListAdminUsersRequest;
use App\Modules\User\Requests\ResetAdminUserPasswordRequest;
use App\Modules\User\Requests\UpdateAdminUserRequest;
use App\Modules\User\Requests\UpdateAdminUserStatusRequest;
use App\Modules\User\Services\AdminUserService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Throwable;

class AdminUserController extends Controller
{
    public function __construct(
        private readonly AdminUserService $adminUserService,
    ) {}

    public function index(ListAdminUsersRequest $request): JsonResponse
    {
        $users = $this->adminUserService->list(
            filters: $request->filters(),
            perPage: $request->perPage(),
        );

        return response()->json([
            'status' => ApiResponseStatus::Success->value,
            'message' => 'Users retrieved successfully',
            'data' => AdminUserListItemResource::collection($users->items()),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ],
        ]);
    }

    public function show(int $user_id): JsonResponse
    {
        try {
            $user = $this->adminUserService->show($user_id);

            return $this->successResponse(
                data: new RegisteredUserResource($user),
                message: 'User retrieved successfully.',
            );
        } catch (ModelNotFoundException) {
            return $this->errorResponse(
                message: 'User not found.',
                statusCode: HttpStatus::NOT_FOUND,
            );
        }
    }

    public function store(CreateAdminUserRequest $request): JsonResponse
    {
        try {
            $user = $this->adminUserService->create(
                userAttributes: $request->userAttributes(),
                profileAttributes: $request->profileAttributes(),
            );

            return $this->successResponse(
                data: new RegisteredUserResource($user),
                message: 'User created successfully.',
                statusCode: HttpStatus::CREATED,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Failed to create user. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function update(UpdateAdminUserRequest $request, int $user_id): JsonResponse
    {
        try {
            $user = $this->adminUserService->update(
                userId: $user_id,
                userAttributes: $request->userAttributes(),
                profileAttributes: $request->profileAttributes(),
            );

            return $this->successResponse(
                data: new RegisteredUserResource($user),
                message: 'User updated successfully.',
            );
        } catch (ModelNotFoundException) {
            return $this->errorResponse(
                message: 'User not found.',
                statusCode: HttpStatus::NOT_FOUND,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Failed to update user. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function updateStatus(UpdateAdminUserStatusRequest $request, int $user_id): JsonResponse
    {
        try {
            $user = $this->adminUserService->updateStatus(
                userId: $user_id,
                isActive: (bool) $request->validated('user_is_active'),
            );

            return $this->successResponse(
                data: new RegisteredUserResource($user->load(['profile', 'registrationTypes'])),
                message: 'User status updated successfully.',
            );
        } catch (ModelNotFoundException) {
            return $this->errorResponse(
                message: 'User not found.',
                statusCode: HttpStatus::NOT_FOUND,
            );
        }
    }

    public function resetPassword(ResetAdminUserPasswordRequest $request, int $user_id): JsonResponse
    {
        try {
            $this->adminUserService->resetPassword(
                userId: $user_id,
                password: $request->validated('user_password'),
            );

            return $this->successResponse(
                message: 'User password reset successfully.',
            );
        } catch (ModelNotFoundException) {
            return $this->errorResponse(
                message: 'User not found.',
                statusCode: HttpStatus::NOT_FOUND,
            );
        }
    }
}
