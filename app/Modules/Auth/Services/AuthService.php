<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Repositories\UserRegistrationRepository;
use App\Modules\User\Enums\RegistrationTypeCategory;
use App\Modules\User\Enums\RegistrationTypeValue;
use App\Modules\User\Enums\UserRole;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\DB;

class AuthService
{
    public function __construct(
        private readonly UserRegistrationRepository $userRegistrationRepository,
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

            return $user->fresh(['profile', 'registrationTypes']);
        });
    }

    /**
     * @param  list<array{user_registration_type_category: string, user_registration_type_value: string}>  $registrationTypes
     */
    private function resolveRole(array $registrationTypes): UserRole
    {
        $roleValues = collect($registrationTypes)
            ->filter(fn (array $type) => $type['user_registration_type_category'] === RegistrationTypeCategory::Role->value)
            ->pluck('user_registration_type_value');

        if ($roleValues->contains(RegistrationTypeValue::Agent->value)) {
            return UserRole::Agent;
        }

        return UserRole::Member;
    }
}
