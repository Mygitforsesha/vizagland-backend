<?php

namespace App\Modules\User\Services;

use App\Modules\ActivityLog\Enums\ActivityLogType;
use App\Modules\ActivityLog\Services\ActivityLogService;
use App\Modules\User\Models\User;
use App\Modules\User\Repositories\AdminUserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class AdminUserService
{
    public function __construct(
        private readonly AdminUserRepository $adminUserRepository,
        private readonly ActivityLogService $activityLogService,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function list(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->adminUserRepository->paginate($filters, $perPage);
    }

    public function show(int $userId): User
    {
        $user = $this->adminUserRepository->findByIdWithDetails($userId);

        if ($user === null) {
            throw (new ModelNotFoundException)->setModel(User::class, [$userId]);
        }

        return $user;
    }

    /**
     * @param  array<string, mixed>  $userAttributes
     * @param  array<string, mixed>  $profileAttributes
     */
    public function create(array $userAttributes, array $profileAttributes = []): User
    {
        $user = DB::transaction(function () use ($userAttributes, $profileAttributes) {
            $user = $this->adminUserRepository->createUser($userAttributes);
            $this->adminUserRepository->createProfile($user->user_id, $profileAttributes);

            return $user->fresh(['profile', 'registrationTypes']);
        });

        $this->activityLogService->log(
            type: ActivityLogType::User,
            action: 'created',
            description: "User created: {$user->user_full_name} ({$user->user_role->value})",
            entityType: 'user',
            entityId: $user->user_id,
        );

        return $user;
    }

    /**
     * @param  array<string, mixed>  $userAttributes
     * @param  array<string, mixed>  $profileAttributes
     */
    public function update(int $userId, array $userAttributes, array $profileAttributes): User
    {
        $user = DB::transaction(function () use ($userId, $userAttributes, $profileAttributes) {
            $user = $this->adminUserRepository->findById($userId);

            if ($user === null) {
                throw (new ModelNotFoundException)->setModel(User::class, [$userId]);
            }

            if ($userAttributes !== []) {
                $this->adminUserRepository->updateUser($user, $userAttributes);
            }

            if ($profileAttributes !== []) {
                $this->adminUserRepository->updateProfile($user, $profileAttributes);
            }

            return $user->fresh(['profile', 'registrationTypes']);
        });

        $this->activityLogService->log(
            type: ActivityLogType::User,
            action: 'updated',
            description: "User updated: {$user->user_full_name}",
            entityType: 'user',
            entityId: $user->user_id,
        );

        return $user;
    }

    public function updateStatus(int $userId, bool $isActive): User
    {
        $user = DB::transaction(function () use ($userId, $isActive) {
            $user = $this->adminUserRepository->findById($userId);

            if ($user === null) {
                throw (new ModelNotFoundException)->setModel(User::class, [$userId]);
            }

            return $this->adminUserRepository->updateUser($user, [
                'user_is_active' => $isActive,
            ]);
        });

        $this->activityLogService->log(
            type: ActivityLogType::User,
            action: $isActive ? 'activated' : 'deactivated',
            description: $isActive
                ? "User activated: {$user->user_full_name}"
                : "User deactivated: {$user->user_full_name}",
            entityType: 'user',
            entityId: $user->user_id,
        );

        return $user;
    }

    public function resetPassword(int $userId, string $password): void
    {
        DB::transaction(function () use ($userId, $password) {
            $user = $this->adminUserRepository->findById($userId);

            if ($user === null) {
                throw (new ModelNotFoundException)->setModel(User::class, [$userId]);
            }

            $this->adminUserRepository->updateUser($user, [
                'user_password' => $password,
            ]);

            $this->adminUserRepository->revokeTokens($user);

            $this->activityLogService->log(
                type: ActivityLogType::Authentication,
                action: 'password_reset',
                description: "Password reset for user: {$user->user_full_name}",
                entityType: 'user',
                entityId: $user->user_id,
            );
        });
    }
}
