<?php

namespace App\Modules\User\Concerns;

use App\Modules\User\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;

trait HasRoles
{
    public function hasRole(UserRole|string $role): bool
    {
        $roleValue = $role instanceof UserRole ? $role->value : $role;

        return $this->user_role->value === $roleValue;
    }

    /**
     * @param  list<UserRole|string>  $roles
     */
    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(UserRole::SuperAdmin);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(UserRole::Admin);
    }

    public function isEmployee(): bool
    {
        return $this->hasRole(UserRole::Employee);
    }

    public function isAgent(): bool
    {
        return $this->hasRole(UserRole::Agent);
    }

    public function isMember(): bool
    {
        return $this->hasRole(UserRole::Member);
    }

    public function isPublicUser(): bool
    {
        return $this->hasRole(UserRole::PublicUser);
    }

    public function isActive(): bool
    {
        return (bool) $this->user_is_active;
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('user_is_active', true);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeWithRole(Builder $query, UserRole|string $role): Builder
    {
        $roleValue = $role instanceof UserRole ? $role->value : $role;

        return $query->where('user_role', $roleValue);
    }
}
