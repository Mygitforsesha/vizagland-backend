<?php

namespace App\Modules\User\Repositories;

use App\Modules\User\Models\User;
use App\Modules\User\Models\UserProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class AdminUserRepository
{
    public function findByIdWithDetails(int $userId): ?User
    {
        return User::query()
            ->with(['profile', 'registrationTypes'])
            ->where('user_id', $userId)
            ->first();
    }

    public function findById(int $userId): ?User
    {
        return User::query()
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = User::query()
            ->select([
                'user_id',
                'user_full_name',
                'user_phone',
                'user_email',
                'user_role',
                'user_is_active',
                'created_at',
            ]);

        $this->applyFilters($query, $filters);
        $query->orderByDesc('created_at')->orderByDesc('user_id');

        return $query->paginate($perPage);
    }

    /**
     * @param  Builder<User>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('user_full_name', 'like', '%'.$search.'%')
                    ->orWhere('user_phone', 'like', '%'.$search.'%')
                    ->orWhere('user_email', 'like', '%'.$search.'%');
            });
        }

        if (! empty($filters['user_role'])) {
            $query->where('user_role', $filters['user_role']);
        }

        if (isset($filters['user_is_active']) && $filters['user_is_active'] !== '') {
            $query->where('user_is_active', filter_var($filters['user_is_active'], FILTER_VALIDATE_BOOLEAN));
        }
    }

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
    public function createProfile(int $userId, array $attributes = []): UserProfile
    {
        return UserProfile::query()->create([
            'user_id' => $userId,
            ...$attributes,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateUser(User $user, array $attributes): User
    {
        $user->update($attributes);

        return $user->fresh();
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

    public function revokeTokens(User $user): void
    {
        $user->tokens()->delete();
    }
}
