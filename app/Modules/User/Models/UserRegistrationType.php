<?php

namespace App\Modules\User\Models;

use App\Modules\User\Enums\RegistrationTypeCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRegistrationType extends Model
{
    protected $primaryKey = 'user_registration_type_id';

    protected $table = 'user_registration_types';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'user_registration_type_category',
        'user_registration_type_value',
    ];

    protected function casts(): array
    {
        return [
            'user_registration_type_category' => RegistrationTypeCategory::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
