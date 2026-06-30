<?php

namespace App\Modules\Auth\Services;

use App\Modules\ActivityLog\Enums\ActivityLogType;
use App\Modules\ActivityLog\Services\ActivityLogService;
use App\Modules\Auth\Repositories\UserRegistrationRepository;
use App\Modules\Notification\Services\NotificationService;
use App\Modules\User\Enums\RegistrationTypeCategory;
use App\Modules\User\Enums\RegistrationTypeValue;
use App\Modules\User\Enums\UserRole;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\DB;

class AuthService
{
    public function __construct(
        private readonly UserRegistrationRepository $userRegistrationRepository,
        private readonly NotificationService $notificationService,
        private readonly ActivityLogService $activityLogService,
    ) {}

    /**
     * @param  array<string, mixed>  $userAttributes
     * @param  array<string, mixed>  $profileAttributes
     * @param  list<array{user_registration_type_category: string, user_registration_type_value: string}>  $registrationTypes
     */
    public function register(array $userAttributes, array $profileAttributes, array $registrationTypes): User
    {
        return DB::transaction(function () use ($userAttributes, $profileAttributes, $registrationTypes) {
            $user = $this->userRegistrationRepository->createUser([
                ...$userAttributes,
                'user_role' => $this->resolveRole($registrationTypes),
                'user_is_active' => true,
            ]);

            $this->userRegistrationRepository->createProfile($user->user_id, $profileAttributes);
            $this->userRegistrationRepository->createRegistrationTypes($user->user_id, $registrationTypes);

            $user = $user->fresh(['profile', 'registrationTypes']);

            $this->notificationService->notifyUserRegistered($user);

            $this->activityLogService->log(
                type: ActivityLogType::Authentication,
                action: 'registered',
                description: "User registered: {$user->user_full_name} ({$user->user_phone})",
                entityType: 'user',
                entityId: $user->user_id,
                user: $user,
            );

            return $user;
        });
    }

    /**
     * @param  array<string, mixed>  $profileAttributes
     */
    public function updateProfileLocation(int $userId, array $profileAttributes): void
    {
        if ($profileAttributes === []) {
            return;
        }

        $this->userRegistrationRepository->updateProfile($userId, $profileAttributes);
    }

    /**
     * @param  list<array{user_registration_type_category: string, user_registration_type_value: string}>  $registrationTypes
     */
    private function resolveRole(array $registrationTypes): UserRole
    {
        $roleValues = collect($registrationTypes)
            ->filter(fn (array $type) => $type['user_registration_type_category'] === RegistrationTypeCategory::Role->value)
            ->pluck('user_registration_type_value');

        $normalizedRoleValues = $roleValues
            ->map(static fn (mixed $value): string => strtolower(trim((string) $value)));

        if ($normalizedRoleValues->contains(RegistrationTypeValue::Agent->value)) {
            return UserRole::Agent;
        }

        if ($normalizedRoleValues->contains(RegistrationTypeValue::Employee->value)) {
            return UserRole::Employee;
        }

        return UserRole::Member;
    }
}
