<?php

namespace App\Modules\User\Models;

use App\Modules\User\Concerns\HasRoles;
use App\Modules\User\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $primaryKey = 'user_id';

    public const ROLE_SUPER_ADMIN = 'super_admin';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_EMPLOYEE = 'employee';

    public const ROLE_AGENT = 'agent';

    public const ROLE_MEMBER = 'member';

    protected $rememberTokenName = 'user_remember_token';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_full_name',
        'user_email',
        'user_phone',
        'user_password',
        'user_role',
        'user_is_active',
        'user_last_login_at',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'user_password',
        'user_remember_token',
    ];

    protected function casts(): array
    {
        return [
            'user_email_verified_at' => 'datetime',
            'user_last_login_at' => 'datetime',
            'user_password' => 'hashed',
            'user_role' => UserRole::class,
            'user_is_active' => 'boolean',
        ];
    }

    public function getAuthPassword(): string
    {
        return (string) $this->user_password;
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class, 'user_id', 'user_id');
    }

    public function registrationTypes(): HasMany
    {
        return $this->hasMany(UserRegistrationType::class, 'user_id', 'user_id');
    }
}
