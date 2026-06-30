<?php

namespace App\Modules\Auth\Repositories;

use App\Modules\User\Models\User;
use App\Modules\User\Models\UserProfile;
use App\Modules\User\Models\UserRegistrationType;

class UserRegistrationRepository
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createUser(array $attributes): User
    {
        return User::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createProfile(int $userId, array $attributes): UserProfile
    {
        return UserProfile::query()->create([
            'user_id' => $userId,
            ...$attributes,
        ]);
    }

    /**
     * @param  list<array{user_registration_type_category: string, user_registration_type_value: string}>  $types
     */
    public function createRegistrationTypes(int $userId, array $types): void
    {
        foreach ($types as $type) {
            UserRegistrationType::query()->create([
                'user_id' => $userId,
                'user_registration_type_category' => $type['user_registration_type_category'],
                'user_registration_type_value' => $type['user_registration_type_value'],
            ]);
        }
    }

    public function findByPhone(string $phone): ?User
    {
        return User::query()->where('user_phone', $phone)->first();
    }

    public function findByEmail(string $email): ?User
    {
        return User::query()->where('user_email', $email)->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateProfile(int $userId, array $attributes): UserProfile
    {
        return UserProfile::query()->updateOrCreate(
            ['user_id' => $userId],
            $attributes,
        );
    }
}
