<?php

namespace App\Modules\User\Services;

use App\Modules\ActivityLog\Enums\ActivityLogType;
use App\Modules\ActivityLog\Services\ActivityLogService;
use App\Modules\User\Models\User;
use App\Modules\User\Repositories\ProfileRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class ProfileService
{
    public function __construct(
        private readonly ProfileRepository $profileRepository,
        private readonly ActivityLogService $activityLogService,
    ) {}

    public function show(int $userId): User
    {
        return $this->profileRepository->requireByIdWithProfile($userId);
    }

    /**
     * @param  array<string, mixed>  $userAttributes
     * @param  array<string, mixed>  $profileAttributes
     */
    public function update(int $userId, array $userAttributes, array $profileAttributes): User
    {
        $user = DB::transaction(function () use ($userId, $userAttributes, $profileAttributes) {
            $user = $this->profileRepository->requireByIdWithProfile($userId);

            if ($userAttributes !== []) {
                $this->profileRepository->updateUser($user, $userAttributes);
            }

            if ($profileAttributes !== []) {
                $this->profileRepository->updateProfile($user->fresh(), $profileAttributes);
            }

            return $this->profileRepository->requireByIdWithProfile($userId);
        });

        $this->activityLogService->log(
            type: ActivityLogType::User,
            action: 'updated',
            description: "Profile updated: {$user->user_full_name}",
            entityType: 'user',
            entityId: $user->user_id,
            user: $user,
        );

        return $user;
    }

    public function changePassword(int $userId, string $currentPassword, string $newPassword): void
    {
        $user = $this->profileRepository->requireByIdWithProfile($userId);

        if (! Hash::check($currentPassword, $user->user_password)) {
            throw new RuntimeException('The current password is incorrect.');
        }

        $this->profileRepository->updatePassword($user, Hash::make($newPassword));

        $this->activityLogService->log(
            type: ActivityLogType::Authentication,
            action: 'password_changed',
            description: "Password changed: {$user->user_full_name}",
            entityType: 'user',
            entityId: $user->user_id,
            user: $user,
        );
    }
}
