<?php

namespace App\Modules\User\Repositories;

use App\Modules\User\Models\User;
use App\Modules\User\Models\UserProfile;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProfileRepository
{
    public function findByIdWithProfile(int $userId): ?User
    {
        return User::query()
            ->with(['profile', 'registrationTypes'])
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateUser(User $user, array $attributes): User
    {
        $user->update($attributes);

        return $user->fresh(['profile']);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateProfile(User $user, array $attributes): UserProfile
    {
        return UserProfile::query()->updateOrCreate(
            ['user_id' => $user->user_id],
            $attributes,
        );
    }

    public function updatePassword(User $user, string $hashedPassword): User
    {
        $user->update(['user_password' => $hashedPassword]);

        return $user->fresh(['profile']);
    }

    public function requireByIdWithProfile(int $userId): User
    {
        $user = $this->findByIdWithProfile($userId);

        if ($user === null) {
            throw (new ModelNotFoundException)->setModel(User::class, [$userId]);
        }

        return $user;
    }
}
