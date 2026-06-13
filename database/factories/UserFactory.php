<?php

namespace Database\Factories;

use App\Modules\User\Enums\UserRole;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_full_name' => fake()->name(),
            'user_email' => fake()->unique()->safeEmail(),
            'user_phone' => fake()->unique()->numerify('##########'),
            'user_email_verified_at' => now(),
            'user_password' => static::$password ??= Hash::make('password'),
            'user_role' => UserRole::Agent,
            'user_is_active' => true,
            'user_last_login_at' => null,
            'user_remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_email_verified_at' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_is_active' => false,
        ]);
    }

    public function withRole(UserRole $role): static
    {
        return $this->state(fn (array $attributes) => [
            'user_role' => $role,
        ]);
    }
}
